<?php
/**
 * 관리자 로그아웃 처리 - 보안 강화
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그아웃 이벤트 로깅 (간소화)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $username = $_SESSION['admin_username'] ?? 'unknown';
    
    // 로그 디렉토리 생성
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    
    // 로그아웃 이벤트 로깅
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event_type' => 'SUCCESSFUL_LOGOUT',
        'description' => "Admin user {$username} logged out",
        'user_id' => $_SESSION['admin_user_id'] ?? null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $log_file = $log_dir . '/security.log';
    $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . "\n";
    @file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

// 보안 강화된 세션 삭제
if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

// 로그인 페이지로 리다이렉트
header("Location: login.php?logout=1");
exit;
?>
