<?php
/**
 * GitHub Webhook 자동 배포 엔드포인트
 * GitHub push 이벤트 수신 → 최신 코드 다운로드 → 배포
 */

// 설정 로드
require_once __DIR__ . '/../config.php';

$CONFIG = [
    'secret' => env('DEPLOY_SECRET', ''),
    'repo' => 'DrunkenZealnut/younglabor',
    'branch' => 'main',
    'deploy_dir' => dirname(__DIR__),
    'log_file' => dirname(__DIR__) . '/deploy.log',
    // 배포 대상에서 항상 제외 (복사도, 삭제도 하지 않음)
    'exclude' => [
        '.env', '.env.local', '.env.production',
        '.git', '.github', '.gitignore',
        'CLAUDE.md', '.claude',
        'deploy.log',
    ],
    // git에서 삭제된 파일을 프로덕션에서도 정리할지 여부 (기본 비활성 — 검토 후 .env에서 켤 것)
    'prune' => env('DEPLOY_PRUNE_REMOVED', 'false') === 'true',
];

// 로깅
function deployLog($msg) {
    global $CONFIG;
    $time = date('Y-m-d H:i:s');
    file_put_contents($CONFIG['log_file'], "[$time] $msg\n", FILE_APPEND);
}

// JSON 응답
function respond($success, $message, $code = 200, array $data = []) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => (object) $data,
    ]);
    exit;
}

// $path가 $prefixes 중 하나로 시작하는지 (제외 목록 체크용)
function startsWithAny($path, array $prefixes) {
    foreach ($prefixes as $prefix) {
        if (strpos($path, $prefix) === 0) {
            return true;
        }
    }
    return false;
}

// 간이 .gitignore 파서: 주석/빈 줄/네거티브 패턴(!) 제외한 라인 목록 반환
// 네거티브 패턴은 별도 처리하지 않음 — 해당 파일은 git에 추적되어 있어 $expectedFiles에 자연히 포함되므로 안전함
function parseGitignore($path) {
    if (!is_file($path)) {
        return [];
    }
    $patterns = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '!') === 0) {
            continue;
        }
        $patterns[] = rtrim($line, '/');
    }
    return $patterns;
}

// 상대경로가 .gitignore 패턴 중 하나에 매치되는지 (디렉토리/글롭 패턴 단순 매칭)
function matchesGitignorePattern($relativePath, array $patterns) {
    foreach ($patterns as $pattern) {
        // 디렉토리/경로 접두 매칭 (예: data/file/notices, logs)
        if (strpos($relativePath, $pattern) === 0) {
            return true;
        }
        // 파일명 글롭 매칭 (예: *.log, test-*.php)
        if (fnmatch($pattern, basename($relativePath)) || fnmatch($pattern, $relativePath)) {
            return true;
        }
    }
    return false;
}

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'POST only', 405);
}

// GitHub Webhook 시그니처 검증
$payload = file_get_contents('php://input');
$secret = $CONFIG['secret'];

if (empty($secret)) {
    deployLog("REJECTED: DEPLOY_SECRET not configured");
    respond(false, 'Deploy secret not configured', 403);
}

$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    deployLog("REJECTED: Invalid signature from {$_SERVER['REMOTE_ADDR']}");
    respond(false, 'Invalid signature', 403);
}

// 이벤트 확인
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'unknown';
$data = json_decode($payload, true);

if ($event !== 'push') {
    respond(true, "Ignored event: $event");
}

// main 브랜치만 배포
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/' . $CONFIG['branch']) {
    respond(true, "Ignored branch: $ref");
}

$pusher = $data['pusher']['name'] ?? 'unknown';
deployLog("Deploy started - push by $pusher");

// GitHub에서 최신 코드 ZIP 다운로드
$zipUrl = "https://api.github.com/repos/{$CONFIG['repo']}/zipball/{$CONFIG['branch']}";
$tmpZip = tempnam(sys_get_temp_dir(), 'deploy_');
$tmpDir = sys_get_temp_dir() . '/deploy_' . uniqid();

$fp = fopen($tmpZip, 'w');
if (!$fp) {
    deployLog("FAILED: Cannot create temp file $tmpZip");
    respond(false, 'Cannot create temp file', 500);
}

$ch = curl_init($zipUrl);
curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        'User-Agent: younglabor-deploy',
        'Accept: application/vnd.github.v3+json',
    ],
    CURLOPT_FILE => $fp,
    CURLOPT_TIMEOUT => 120,
]);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);
fclose($fp);

if ($curlError) {
    deployLog("FAILED: cURL error - $curlError");
    @unlink($tmpZip);
    respond(false, "Download error: $curlError", 500);
}

if ($httpCode !== 200) {
    deployLog("FAILED: GitHub API returned $httpCode");
    @unlink($tmpZip);
    respond(false, "GitHub download failed: $httpCode", 500);
}

// ZIP 해제
$zip = new ZipArchive();
if ($zip->open($tmpZip) !== true) {
    deployLog("FAILED: Cannot open zip");
    @unlink($tmpZip);
    respond(false, 'Cannot open zip', 500);
}

$zip->extractTo($tmpDir);
$zip->close();
@unlink($tmpZip);

// ZIP 내부 폴더명 찾기 (GitHub ZIP은 repo-branch-hash/ 형태)
$extracted = glob("$tmpDir/*", GLOB_ONLYDIR);
if (empty($extracted)) {
    deployLog("FAILED: Empty zip");
    respond(false, 'Empty zip', 500);
}
$srcDir = $extracted[0];

// 파일 복사 (제외 목록 제외) + 삭제 동기화용 "현재 저장소 파일 목록" 수집
$deployDir = $CONFIG['deploy_dir'];
$copied = 0;
$errors = [];
$expectedFiles = []; // relativePath => true (제외 목록을 뺀, 이번 배포에 존재해야 하는 파일)

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $relativePath = substr($item->getPathname(), strlen($srcDir) + 1);

    // 제외 목록 체크
    if (startsWithAny($relativePath, $CONFIG['exclude'])) {
        continue;
    }

    $target = $deployDir . '/' . $relativePath;

    if ($item->isDir()) {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
    } else {
        $expectedFiles[$relativePath] = true;

        $dir = dirname($target);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (copy($item->getPathname(), $target)) {
            $copied++;
        } else {
            $errors[] = $relativePath;
        }
    }
}

// 삭제 동기화 (옵트인, DEPLOY_PRUNE_REMOVED=true 일 때만)
// git에서 제거된 파일을 프로덕션에서도 제거. 단 아래는 항상 보존:
//   1) $CONFIG['exclude'] 목록 (.env*, .git, deploy.log 등)
//   2) 이번 배포본의 .gitignore에 매치되는 경로 (사용자 업로드 data/file/notices/* 등)
$pruned = 0;
$pruneErrors = [];

if ($CONFIG['prune']) {
    $gitignorePatterns = parseGitignore($srcDir . '/.gitignore');

    $prodIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($deployDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($prodIterator as $item) {
        $relativePath = substr($item->getPathname(), strlen($deployDir) + 1);

        if (startsWithAny($relativePath, $CONFIG['exclude'])) {
            continue;
        }
        if (matchesGitignorePattern($relativePath, $gitignorePatterns)) {
            continue;
        }

        if ($item->isDir()) {
            // 파일 삭제 후 비어있는 디렉토리만 정리 (보호 대상이 남아있으면 자동으로 스킵됨)
            $isEmpty = (count(scandir($item->getPathname())) === 2);
            if ($isEmpty) {
                @rmdir($item->getPathname());
            }
            continue;
        }

        if (!isset($expectedFiles[$relativePath])) {
            if (@unlink($item->getPathname())) {
                $pruned++;
                deployLog("PRUNED: $relativePath");
            } else {
                $pruneErrors[] = $relativePath;
            }
        }
    }
}

// 임시 디렉토리 정리
$cleanIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($cleanIterator as $item) {
    $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
}
rmdir($tmpDir);

// 결과
$errorCount = count($errors);
$msg = "Deployed $copied files";
if ($CONFIG['prune']) {
    $msg .= ", pruned $pruned files";
}
if ($errorCount > 0) {
    $msg .= ", $errorCount copy errors";
}
if (count($pruneErrors) > 0) {
    $msg .= ", " . count($pruneErrors) . " prune errors";
}
deployLog("SUCCESS: $msg");

if ($errorCount > 0) {
    deployLog("Copy errors: " . implode(', ', $errors));
}
if (count($pruneErrors) > 0) {
    deployLog("Prune errors: " . implode(', ', $pruneErrors));
}

respond(true, $msg, 200, ['copied' => $copied, 'pruned' => $pruned]);
