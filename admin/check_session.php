<?php
/**
 * 세션 상태 확인 스크립트
 */
session_start();

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>세션 상태 확인</title></head><body>";
echo "<h1>세션 상태 확인</h1>";

echo "<h2>세션 정보</h2>";
echo "<ul>";
echo "<li>세션 ID: " . session_id() . "</li>";
echo "<li>세션 상태: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . "</li>";
echo "</ul>";

echo "<h2>세션 데이터</h2>";
if (empty($_SESSION)) {
    echo "<p>세션 데이터가 없습니다.</p>";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<h2>인증 상태</h2>";
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
echo "<p>로그인 상태: " . ($is_logged_in ? "✅ 로그인됨" : "❌ 로그인되지 않음") . "</p>";

if (isset($_SESSION['last_activity'])) {
    $time_diff = time() - $_SESSION['last_activity'];
    echo "<p>마지막 활동: " . $time_diff . "초 전</p>";
}

echo "<h2>액션</h2>";
echo "<ul>";
echo "<li><a href='dev_login.php'>다시 로그인</a></li>";
echo "<li><a href='theme_settings_improved.php'>테마 설정 페이지</a></li>";
echo "</ul>";

echo "</body></html>";
?>