<?php
/**
 * 개발 환경용 자동 로그인 스크립트
 * 프로덕션에서는 절대 사용하지 말 것
 */

// 환경 확인 - localhost에서만 동작하도록 제한
$host = $_SERVER['HTTP_HOST'] ?? '';
if (!in_array($host, ['localhost:8012', 'hopec.local:8012', '127.0.0.1:8012'])) {
    die('개발 환경에서만 사용 가능합니다.');
}

// 세션 시작
session_start();

// 관리자 로그인 상태 설정
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'admin';
$_SESSION['admin_id'] = 1;
$_SESSION['last_activity'] = time();
$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>개발 환경 로그인</title></head><body>";
echo "<h1>개발 환경 자동 로그인 완료</h1>";
echo "<p>관리자로 로그인되었습니다.</p>";
echo "<ul>";
echo "<li><a href='theme_settings_improved.php'>테마 설정 페이지</a></li>";
echo "<li><a href='test_theme_system.php'>테마 시스템 테스트</a></li>";
echo "<li><a href='init_theme.php'>테마 초기화</a></li>";
echo "<li><a href='index.php'>관리자 메인</a></li>";
echo "</ul>";
echo "</body></html>";
?>