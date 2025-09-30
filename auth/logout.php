<?php
/**
 * 사용자 로그아웃 처리
 * User Logout Handler
 */

// 환경 설정 로드
require_once __DIR__ . '/../bootstrap/app.php';

// 세션 시작 (아직 시작되지 않은 경우)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그인 상태 확인
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    // 이미 로그아웃 상태면 로그인 페이지로 리다이렉트
    header('Location: /auth/login.php');
    exit;
}

// 데이터베이스 연결 (로그 기록용)
try {
    $database_config = require __DIR__ . '/../config/database.php';
    $config = $database_config['connections']['mysql'];

    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // 로그아웃 로그 기록
    if (isset($_SESSION['mb_no'])) {
        $prefix = $database_config['prefixes']['modern'] ?? '';
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}member_auth_logs
            (mb_no, action, details, ip_address, user_agent, success, created_at)
            VALUES (?, 'logout', ?, ?, ?, 1, NOW())
        ");

        $details = json_encode([
            'mb_id' => $_SESSION['mb_id'] ?? '',
            'mb_name' => $_SESSION['mb_name'] ?? '',
            'logout_time' => date('Y-m-d H:i:s')
        ]);

        $stmt->execute([
            $_SESSION['mb_no'],
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
} catch (Exception $e) {
    // 로그 기록 실패해도 로그아웃은 진행
    error_log('Logout logging error: ' . $e->getMessage());
}

// Remember Me 쿠키 삭제
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// 세션 변수 모두 삭제
$_SESSION = array();

// 세션 쿠키 삭제
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 세션 파괴
session_destroy();

// 새 세션 시작 (CSRF 토큰 생성을 위해)
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// 로그인 페이지로 리다이렉트 (로그아웃 메시지 포함)
header('Location: /auth/login.php?logout=1');
exit;