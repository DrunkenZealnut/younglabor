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
    'exclude' => [
        '.env', '.env.local', '.env.production',
        '.git', '.github', '.gitignore',
        'CLAUDE.md', '.claude',
        'deploy.log',
    ],
];

// 로깅
function deployLog($msg) {
    global $CONFIG;
    $time = date('Y-m-d H:i:s');
    file_put_contents($CONFIG['log_file'], "[$time] $msg\n", FILE_APPEND);
}

// JSON 응답
function respond($success, $message, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'POST only', 405);
}

// GitHub Webhook 시그니처 검증
$payload = file_get_contents('php://input');
$secret = $CONFIG['secret'];

if (!empty($secret)) {
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

    if (!hash_equals($expected, $signature)) {
        deployLog("REJECTED: Invalid signature from {$_SERVER['REMOTE_ADDR']}");
        respond(false, 'Invalid signature', 403);
    }
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

// 파일 복사 (제외 목록 제외)
$deployDir = $CONFIG['deploy_dir'];
$copied = 0;
$errors = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $relativePath = substr($item->getPathname(), strlen($srcDir) + 1);

    // 제외 목록 체크
    $skip = false;
    foreach ($CONFIG['exclude'] as $exc) {
        if (strpos($relativePath, $exc) === 0) {
            $skip = true;
            break;
        }
    }
    if ($skip) continue;

    $target = $deployDir . '/' . $relativePath;

    if ($item->isDir()) {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
    } else {
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
$msg = "Deployed $copied files" . ($errorCount > 0 ? ", $errorCount errors" : "");
deployLog("SUCCESS: $msg");

if ($errorCount > 0) {
    deployLog("Errors: " . implode(', ', $errors));
}

respond(true, $msg);
