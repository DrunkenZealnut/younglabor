<?php
/**
 * 관리자 메인 → 대시보드로 리다이렉트
 */
require_once __DIR__ . '/config.php';

if (empty($_SESSION['admin_user_id'])) {
    header('Location: ' . url('admin/login.php'));
} else {
    header('Location: ' . url('admin/dashboard.php'));
}
exit;
