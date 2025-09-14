<?php
/**
 * 캡차 이미지 생성 및 출력 API
 * board_templates 시스템 통합
 */

require_once __DIR__ . '/../includes/config.php';
require_once 'CaptchaManager.php';

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $captcha = new CaptchaManager();
    $image = $captcha->createImage();
    
    // PNG 이미지 출력
    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    
    imagepng($image);
    imagedestroy($image);
    
} catch (Exception $e) {
    // 에러 발생 시 빈 이미지 출력
    $error_image = imagecreate(120, 40);
    $bg = imagecolorallocate($error_image, 255, 255, 255);
    $text = imagecolorallocate($error_image, 255, 0, 0);
    imagestring($error_image, 3, 30, 15, 'ERROR', $text);
    
    header('Content-Type: image/png');
    imagepng($error_image);
    imagedestroy($error_image);
}
?>