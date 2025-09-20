<?php
/**
 * 테마 캐시 및 세션 정리
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 테마 관련 세션 정리
unset($_SESSION['selected_theme']);
unset($_SESSION['theme_cache']);
unset($_SESSION['available_themes']);

// PHP 캐시 정리
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// 테마 관련 임시 파일 정리
$tempFiles = [
    __DIR__ . '/../uploads/theme/cache/*',
    __DIR__ . '/../theme/*/cache/*'
];

foreach ($tempFiles as $pattern) {
    $files = glob($pattern);
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>캐시 정리 완료</title>
    <meta http-equiv='refresh' content='2;url=settings/site_settings.php'>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; }
        .success { color: green; font-size: 18px; margin: 20px 0; }
        .info { color: #666; }
    </style>
</head>
<body>
    <h1>테마 캐시 정리 완료</h1>
    <div class='success'>✅ 테마 관련 캐시와 세션이 정리되었습니다.</div>
    <div class='info'>2초 후 관리자 설정 페이지로 이동합니다...</div>
    <div class='info'><a href='settings/site_settings.php'>바로 이동하기</a></div>
</body>
</html>";
?>