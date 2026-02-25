<?php
/**
 * 관리자 인증 미들웨어
 * 보호가 필요한 모든 관리자 페이지에서 require
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// 로그인 확인
if (empty($_SESSION['admin_user_id'])) {
    header('Location: ' . url('admin/login.php'));
    exit;
}

// 세션 타임아웃 확인
if (isset($_SESSION['admin_last_activity'])) {
    if (time() - $_SESSION['admin_last_activity'] > ADMIN_SESSION_LIFETIME) {
        session_unset();
        session_destroy();
        header('Location: ' . url('admin/login.php') . '?expired=1');
        exit;
    }
}
$_SESSION['admin_last_activity'] = time();

// 세션 ID 주기적 갱신 (15분마다)
if (!isset($_SESSION['admin_session_regenerated'])) {
    $_SESSION['admin_session_regenerated'] = time();
} elseif (time() - $_SESSION['admin_session_regenerated'] > 900) {
    session_regenerate_id(true);
    $_SESSION['admin_session_regenerated'] = time();
}

// 관리자 정보 로드
$adminUser = [
    'id' => $_SESSION['admin_user_id'] ?? null,
    'username' => $_SESSION['admin_username'] ?? '',
    'name' => $_SESSION['admin_name'] ?? '',
    'role' => $_SESSION['admin_role'] ?? 'admin',
];
