<?php
// 오류 출력 차단
error_reporting(0);
ini_set('display_errors', 0);

// JSON 헤더
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // CSRF 토큰 생성
    if (!isset($_SESSION['inquiry_csrf_token']) || !isset($_SESSION['inquiry_csrf_time']) || 
        (time() - $_SESSION['inquiry_csrf_time']) > 3600) {
        $_SESSION['inquiry_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['inquiry_csrf_time'] = time();
    }
    
    echo json_encode([
        'success' => true,
        'csrf_token' => $_SESSION['inquiry_csrf_token'],
        'session_id' => session_id()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '토큰 생성에 실패했습니다.'
    ], JSON_UNESCAPED_UNICODE);
}
?>