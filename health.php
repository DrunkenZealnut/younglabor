<?php
// 간단 헬스체크 엔드포인트 (배포/모니터링용)
// - JSON 응답, DB 연결/쿼리 테스트, PHP/시간/호스트 정보 제공
// - 민감정보(계정/키) 노출 금지

header('Content-Type: application/json; charset=utf-8');

$status = [
  'ok' => false,
  'time' => date('c'),
  'host' => $_SERVER['HTTP_HOST'] ?? '',
  'php' => PHP_VERSION,
  'app' => 'hopec',
  'db' => ['ok' => false, 'ms' => null],
];

try {
  // 공통 로드 (DB 상수/연결 함수)
  @include_once __DIR__ . '/_common.php';

  // DB 연결 핑(간단 SELECT 1)
  $t0 = microtime(true);
  @sql_query('SELECT 1');
  $status['db']['ok'] = true;
  $status['db']['ms'] = (int)round((microtime(true) - $t0) * 1000);
  $status['ok'] = true;
  http_response_code(200);
} catch (Throwable $e) {
  // 실패 시 500과 간략 메시지
  http_response_code(500);
  $status['error'] = 'healthcheck failed';
}

echo json_encode($status, JSON_UNESCAPED_UNICODE);
exit;
?>


