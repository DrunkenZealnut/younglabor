<?php
/**
 * 관리자 인증 검증 - 보안 강화 버전
 */

// 디버깅 로그 함수
function debug_log_auth($message, $data = null) {
    $log_file = __DIR__ . '/../logs/auth_debug.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'data' => $data,
        'session_id' => session_id() ?: 'NO_SESSION'
    ];
    @file_put_contents($log_file, json_encode($entry) . "\n", FILE_APPEND);
}

// 보안 강화된 세션 관리 (bootstrap.php와 동일한 설정)
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        // 세션 보안 설정 (bootstrap.php와 동일)
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // 세션 만료 시간 설정 (2시간)
        ini_set('session.gc_maxlifetime', 7200);
        ini_set('session.cookie_lifetime', 7200);
        
        session_start();
    } else {
        session_start();
    }
}

debug_log_auth("auth.php 시작 - 세션 검증 중", [
    'admin_logged_in' => $_SESSION['admin_logged_in'] ?? 'NOT_SET',
    'session_data' => array_keys($_SESSION),
    'last_activity' => $_SESSION['last_activity'] ?? 'NOT_SET'
]);

// 기본 세션 검증
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    debug_log_auth("세션 검증 실패 - 로그인 페이지로 리다이렉트", [
        'admin_logged_in' => $_SESSION['admin_logged_in'] ?? 'NOT_SET',
        'session_keys' => array_keys($_SESSION)
    ]);
    
    if (!headers_sent()) {
        header("Location: login.php?expired=1");
        exit;
    } else {
        echo "<script>window.location.href='login.php?expired=1';</script>";
        exit;
    }
}

// 세션 만료 체크 (30분)
if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity'] > 1800)) {
    // 세션 만료
    if (!headers_sent()) {
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    } else {
        echo "<script>window.location.href='login.php?timeout=1';</script>";
        exit;
    }
}

// IP 변경 체크 (세션 탈취 방지)
if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
    if (!headers_sent()) {
        session_destroy();
        header("Location: login.php?security=1");
        exit;
    } else {
        echo "<script>window.location.href='login.php?security=1';</script>";
        exit;
    }
}

// 세션 활동 시간 업데이트
$_SESSION['last_activity'] = time();

// 세션 ID 재생성 (15분마다)
if (!isset($_SESSION['regenerated']) || (time() - $_SESSION['regenerated'] > 900)) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}
?>
