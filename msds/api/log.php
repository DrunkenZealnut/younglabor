<?php
/**
 * JavaScript 디버그 로그 API
 * 클라이언트(브라우저)에서 발생한 이벤트와 에러를 서버에 기록
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST only']);
    exit;
}

// 로그 디렉토리 설정
$logDir = __DIR__ . '/../logs';
$logFile = $logDir . '/js_' . date('Y-m-d') . '.log';

if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// 요청 데이터 파싱
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// 로그 항목 구성
$timestamp = date('Y-m-d H:i:s');
$level = strtoupper($data['level'] ?? 'INFO');
$message = $data['message'] ?? 'No message';
$isMobile = ($data['isMobile'] ?? false) ? 'MOBILE' : 'DESKTOP';
$userAgent = substr($data['userAgent'] ?? 'Unknown', 0, 100);
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$context = $data['data'] ?? [];

$logEntry = sprintf(
    "[%s] [%s] [%s] [%s] [%s] %s",
    $timestamp,
    $level,
    $isMobile,
    $ip,
    $userAgent,
    $message
);

if (!empty($context)) {
    $logEntry .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
}

$logEntry .= PHP_EOL;

// 로그 파일에 기록
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

echo json_encode(['success' => true]);
