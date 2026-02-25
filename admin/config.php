<?php
/**
 * 관리자 패널 설정
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';

// 관리자 상수
define('ADMIN_SESSION_LIFETIME', 3600);
define('ADMIN_SESSION_NAME', 'ylabor_admin');
define('ADMIN_MAX_LOGIN_ATTEMPTS', 5);
define('ADMIN_LOCKOUT_DURATION', 900);
define('ADMIN_CSRF_TOKEN_NAME', '_csrf_token');

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isProduction() ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', ADMIN_SESSION_LIFETIME);
    session_name(ADMIN_SESSION_NAME);
    session_start();
}
