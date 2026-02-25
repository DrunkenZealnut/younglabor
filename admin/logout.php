<?php
/**
 * 관리자 로그아웃
 */
require_once __DIR__ . '/config.php';

session_unset();
session_destroy();

header('Location: ' . url('admin/login.php'));
exit;
