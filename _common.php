<?php
// 기본 경로 상수 안전 정의 (common.php 로드 전)
if (!defined('G5_PATH')) {
    define('G5_PATH', __DIR__);
}
if (!defined('G5_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // vhost 사용 시 사이트 루트 기준으로 설정
    $base_path = '';
    
    define('G5_URL', $protocol . '://' . $host . $base_path);
    
    // 디버그 정보 출력 (로컬 환경에서만)
    if (strpos($host, 'localhost') !== false || strpos($host, 'hopec.local') !== false) {
        echo "<!-- G5_URL Debug: " . G5_URL . " -->\n";
    }
}

// 기본 디렉토리 상수 정의
if (!defined('G5_THEME_DIR')) {
    define('G5_THEME_DIR', 'theme');
}

// 기본 설정 배열 초기화
if (!isset($config)) {
    $config = [];
}
if (!isset($config['cf_theme'])) {
    $config['cf_theme'] = 'natural-green';
}

// 그누보드 공통 파일 로드
include_once(__DIR__.'/common.php');

// 개발/점검용 오류 표시(로컬 호스트 또는 ?debug=1 시 활성)
try {
    $hostForDebug = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $remoteIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $isLocal = in_array($remoteIp, ['127.0.0.1', '::1'], true)
        || strpos($hostForDebug, 'localhost') !== false
        || strpos($hostForDebug, 'hopec.local') !== false;
    if ($isLocal || (isset($_GET['debug']) && $_GET['debug'] === '1')) {
        @ini_set('display_errors', '1');
        @ini_set('display_startup_errors', '1');
        @error_reporting(E_ALL);
    }
} catch (Throwable $e) { /* no-op */ }

// 모바일 분기 제거를 위한 안전한 상수 정의
if (!defined('G5_IS_MOBILE')) {
    define('G5_IS_MOBILE', false);
}
if (!defined('G5_MOBILE_PATH')) {
    define('G5_MOBILE_PATH', G5_PATH.'/mobile');
}
if (!defined('G5_MOBILE_URL')) {
    define('G5_MOBILE_URL', G5_URL.'/mobile');
}
if (!defined('G5_THEME_MOBILE_PATH')) {
    define('G5_THEME_MOBILE_PATH', G5_THEME_PATH.'/mobile');
}
if (!defined('G5_DEVICE_BUTTON_DISPLAY')) {
    define('G5_DEVICE_BUTTON_DISPLAY', false);
}

// 테마 경로 안전 정의
if (!defined('G5_THEME_PATH')) {
    define('G5_THEME_PATH', G5_PATH.'/theme/natural-green');
}
if (!defined('G5_THEME_URL')) {
    define('G5_THEME_URL', G5_URL.'/theme/natural-green');
}

// Undefined array key "lo_location" / "lo_url" 경고 방지
// lib/common.lib.php:2430 에서 $g5['lo_location'] 및 $g5['lo_url']을 참조하나,
// 이들이 전역으로 정의되지 않아 경고가 발생할 수 있음.
// 안전한 기본값으로 초기화하여 경고를 방지.
global $g5;
if (!isset($g5['lo_location'])) {
    $g5['lo_location'] = '';
}
if (!isset($g5['lo_url'])) {
    $g5['lo_url'] = '';
}

// bbs 폴더 삭제로 인해 필요한 임시 함수들
if (!function_exists('connect')) {
    function connect() {
        return '-'; // 임시로 '-' 반환
    }
}

if (!function_exists('popular')) {
    function popular($skin_dir='basic', $pop_cnt=7, $date_cnt=3) {
        return ''; // 임시로 빈 문자열 반환
    }
}

// 기본 변수들 초기화
if (!isset($is_member)) {
    $is_member = false;
}
if (!isset($is_admin)) {
    $is_admin = false;
}
?>