<?php
// G5_PATH는 config.php에서 정의됨

// EnvLoader 초기 로드
if (!function_exists('env')) {
    require_once(__DIR__.'/includes/EnvLoader.php');
    EnvLoader::load();
}

if (!defined('G5_URL')) {
    // 환경변수에서 URL 가져오기, 없으면 자동 감지
    $env_url = env('APP_URL');
    if ($env_url) {
        define('G5_URL', rtrim($env_url, '/'));
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('G5_URL', $protocol . '://' . $host);
    }
}

// G5_THEME_DIR은 config.php에서 정의됨

// 기본 설정 배열 초기화 (환경변수 기반)
if (!isset($config)) {
    $config = [];
}
if (!isset($config['cf_theme'])) {
    $config['cf_theme'] = env('THEME_NAME', 'natural-green');
}

// 그누보드 공통 파일 로드
include_once(__DIR__.'/common.php');

// 환경변수 기반 디버그 모드 설정
if (env('APP_DEBUG', false) || env('APP_ENV') === 'local') {
    @ini_set('display_errors', '1');
    @ini_set('display_startup_errors', '1');
    @error_reporting(E_ALL);
}

// 모바일 관련 상수들 - 삭제됨 (반응형 웹으로 통합, 사용되지 않음)
// define('G5_IS_MOBILE', false);
// define('G5_MOBILE_PATH', G5_PATH.'/mobile');
// define('G5_MOBILE_URL', G5_URL.'/mobile');
// 모바일 테마 경로 - 삭제됨 (반응형으로 통합)
// define('G5_DEVICE_BUTTON_DISPLAY', false);

// 테마 경로 헬퍼 함수 - 환경변수 기반 (캐시 최적화)
if (!function_exists('get_theme_path')) {
    function get_theme_path($sub_path = '') {
        static $theme_name = null;
        if ($theme_name === null) {
            $theme_name = env('THEME_NAME', 'natural-green');
        }
        
        $base_path = __DIR__;
        return $base_path . '/theme/' . $theme_name . ($sub_path ? '/' . ltrim($sub_path, '/') : '');
    }
}

if (!function_exists('get_theme_url')) {
    function get_theme_url($sub_path = '') {
        static $theme_name = null;
        if ($theme_name === null) {
            $theme_name = env('THEME_NAME', 'natural-green');
        }
        
        $base_url = defined('G5_URL') ? G5_URL : '';
        return $base_url . '/theme/' . $theme_name . ($sub_path ? '/' . ltrim($sub_path, '/') : '');
    }
}

// 그누보드 전역 변수 초기화 (호환성 유지)
global $g5;
if (!isset($g5['lo_location'])) {
    $g5['lo_location'] = '';
}
if (!isset($g5['lo_url'])) {
    $g5['lo_url'] = '';
}

// 레거시 호환 함수들 (새로운 시스템에서 대체 예정)
if (!function_exists('connect')) {
    function connect() { return '-'; }
}
if (!function_exists('popular')) {
    function popular($skin_dir='basic', $pop_cnt=7, $date_cnt=3) { return ''; }
}

// 기본 변수들 초기화
if (!isset($is_member)) {
    $is_member = false;
}
if (!isset($is_admin)) {
    $is_admin = false;
}
?>