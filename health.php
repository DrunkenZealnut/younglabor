<?php
// 간단 헬스체크 엔드포인트 (배포/모니터링용)
// - JSON 응답, DB 연결/쿼리 테스트, PHP/시간/호스트 정보 제공
// - 민감정보(계정/키) 노출 금지

// 캐시 방지 헤더
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$status = [
  'ok' => false,
  'time' => date('c'),
  'host' => $_SERVER['HTTP_HOST'] ?? '',
  'php' => PHP_VERSION,
  'app' => env('PROJECT_SLUG', 'hopec'),
  'db' => ['ok' => false, 'ms' => null],
];

try {
  // DB 설정 파일 직접 로드 (common.php 의존성 제거)
  $dbconfig_file = __DIR__ . '/data/dbconfig.php';
  if (file_exists($dbconfig_file)) {
    include $dbconfig_file;
    
    // PDO 직접 연결 및 테스트
    $t0 = microtime(true);
    $dsn = "mysql:host=" . env('DB_HOST', 'localhost') . 
           ";dbname=" . env('DB_DATABASE', env('PROJECT_SLUG', 'hopec')) . 
           ";charset=utf8mb4";
    
    $pdo = new PDO(
      $dsn,
      env('DB_USERNAME', 'root'),
      env('DB_PASSWORD', ''),
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3, // 3초 타임아웃
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]
    );
    
    // 간단한 연결 테스트
    $pdo->query('SELECT 1');
    
    $status['db']['ok'] = true;
    $status['db']['ms'] = (int)round((microtime(true) - $t0) * 1000);
    $status['ok'] = true;
    http_response_code(200);
  } else {
    // DB 설정 파일이 없는 경우
    $status['error'] = 'db config not found';
    http_response_code(503); // Service Unavailable
  }
} catch (PDOException $e) {
  // DB 연결 실패
  http_response_code(503);
  $status['error'] = 'database connection failed';
} catch (Throwable $e) {
  // 기타 실패
  http_response_code(500);
  $status['error'] = 'healthcheck failed';
}

echo json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>


