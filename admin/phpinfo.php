<?php
// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 인증 확인
require_once __DIR__ . '/auth.php';

// PHP 정보 표시
phpinfo();
?>