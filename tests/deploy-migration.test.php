<?php
require_once __DIR__ . '/../includes/DeployMigration.php';

// 요청 시각 ±5분만 허용 (서명된 요청 재사용 차단)
$now = 1800000000;
foreach ([$now, $now - 300, $now + 300] as $requestedAt) {
    if (!deployMigrationRequestIsFresh(['requested_at' => $requestedAt], $now)) exit(1);
}
foreach ([['requested_at' => $now - 301], ['requested_at' => $now + 301], ['requested_at' => (string)$now], ['requested_at' => null], []] as $data) {
    if (deployMigrationRequestIsFresh($data, $now)) exit(1);
}

// 서명 검증 뒤, push 배포 분기 전에 이관 분기가 있어야 한다
$deploySource = file_get_contents(__DIR__ . '/../api/deploy.php');
$signatureCheck = strpos($deploySource, 'if (!hash_equals($expected, $signature))');
$migrateBranch = strpos($deploySource, "if (\$event === 'migrate')");
$pushBranch = strpos($deploySource, "if (\$event !== 'push')");
if ($signatureCheck === false || $migrateBranch === false || $pushBranch === false) exit(1);
if (!($signatureCheck < $migrateBranch && $migrateBranch < $pushBranch)) exit(1);
// push 배포 경로는 이관용 파일에 의존하지 않는다 (그 파일이 깨져도 수정본을 다시 배포할 수 있게)
$migrationRequire = strpos($deploySource, "require_once __DIR__ . '/../includes/DeployMigration.php';");
if ($migrationRequire === false || !($migrateBranch < $migrationRequire && $migrationRequire < $pushBranch)) exit(1);
if (strpos($deploySource, "ini_set('display_errors', '0');") === false) exit(1);
foreach (['deployMigrationRequestIsFresh($data, time())', "in_array(\$mode, ['status', 'apply'], true)", "respond(false, 'Migration failed. See deploy.log on the server.', 500)"] as $needle) {
    if (strpos($deploySource, $needle) === false) exit(1);
}
// 공개 Actions 로그로 예외 메시지를 보내지 않는다
if (preg_match('/respond\([^;]*getMessage\(\)/', $deploySource)) exit(1);

// 수동 실행 workflow: 기본값은 읽기 전용 status, 요청 시각을 서명 대상에 포함
$workflow = file_get_contents(__DIR__ . '/../.github/workflows/migrate.yml');
// 옛 deploy.php의 "Ignored event" 200이나 apply 후 pending을 성공으로 보지 않는다
foreach (['workflow_dispatch:', 'default: status', '- apply', '"requested_at":%s', 'X-GitHub-Event: migrate', "github.ref == 'refs/heads/main'", 'startswith("Migration ")', '.data.pending == false', 'permissions: {}'] as $needle) {
    if (strpos($workflow, $needle) === false) exit(1);
}
// 배포와 같은 concurrency 그룹을 쓰면 대기 중인 배포가 취소된다 (겹침은 서버 잠금이 막는다)
$deployGroup = preg_match('/group:\s*(\S+)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml'), $m) ? $m[1] : null;
if ($deployGroup !== null && strpos($workflow, "group: $deployGroup") !== false) exit(1);

echo "PASS deploy migration request guards\n";
