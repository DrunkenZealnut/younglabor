<?php
// 빠른 관리자 세션 생성
session_start();

// 관리자 세션 직접 생성
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'admin';
$_SESSION['admin_user_id'] = 1;
$_SESSION['admin_role'] = 'admin';
$_SESSION['created_at'] = time();
$_SESSION['last_activity'] = time();
$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'localhost';
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'test';

echo '<h2>✅ 관리자 세션이 생성되었습니다!</h2>';
echo '<p><strong>이제 다음 링크들에 접근할 수 있습니다:</strong></p>';
echo '<ul>';
echo '<li><a href="settings/site_settings.php" style="color: red; font-weight: bold;">🎨 디자인 설정 페이지</a></li>';
echo '<li><a href="index.php">📊 관리자 대시보드</a></li>';
echo '<li><a href="test_template.php">🧪 템플릿 테스트</a></li>';
echo '</ul>';

echo '<hr>';
echo '<h3>세션 정보:</h3>';
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
?>