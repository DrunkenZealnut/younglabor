<?php
/**
 * Natural Green Theme Bootstrap
 * 테마에 필요한 모든 의존성을 로드하는 통합 부트스트랩 파일
 */

// 중복 실행 방지
if (defined('THEME_BOOTSTRAP_LOADED')) {
    return;
}
define('THEME_BOOTSTRAP_LOADED', true);

// 그누보드 호환성을 위한 상수 정의
if (!defined('_GNUBOARD_')) {
    define('_GNUBOARD_', true);
}

// 기본 경로 설정
if (!defined('HOPEC_BASE_PATH')) {
    define('HOPEC_BASE_PATH', dirname(__DIR__, 2));
}

// env 함수 정의 (bootstrap/env.php가 로드되지 않은 경우)
if (!function_exists('env')) {
    function env($key, $default = null) {
        // $_ENV에서 먼저 확인
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // getenv()로 확인
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // .env 파일 로드 시도
        static $envLoaded = false;
        if (!$envLoaded) {
            $envFiles = [
                HOPEC_BASE_PATH . '/.env',
                HOPEC_BASE_PATH . '/.env.local',
                dirname(__DIR__, 2) . '/.env'
            ];
            
            foreach ($envFiles as $envFile) {
                if (file_exists($envFile)) {
                    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                            list($envKey, $envValue) = explode('=', $line, 2);
                            $envKey = trim($envKey);
                            $envValue = trim($envValue, '"\'');
                            $_ENV[$envKey] = $envValue;
                            putenv("$envKey=$envValue");
                        }
                    }
                    break;
                }
            }
            $envLoaded = true;
            
            // 재시도
            if (isset($_ENV[$key])) {
                return $_ENV[$key];
            }
        }
        
        return $default;
    }
}

// 데이터베이스 설정 및 초기화
try {
    // DatabaseManager 로드
    if (!class_exists('DatabaseManager')) {
        require_once HOPEC_BASE_PATH . '/includes/DatabaseManager.php';
    }
    
    // 데이터베이스 설정 구성
    $GLOBALS['hopec_config'] = [
        'database' => [
            'connections' => [
                'mysql' => [
                    'host' => env('DB_HOST', 'localhost'),
                    'port' => env('DB_PORT', 3306),
                    'database' => env('DB_DATABASE', 'hopec'),
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                    'charset' => 'utf8mb4',
                    'socket' => env('DB_SOCKET', ''),
                    'options' => [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                ]
            ],
            'prefixes' => [
                'modern' => env('DB_PREFIX', 'hopec_')
            ],
            'query_log' => env('DB_QUERY_LOG', false)
        ],
        'app' => [
            'name' => '사단법인 희망씨',
            'url' => env('APP_URL', 'http://hopec.local:8012'),
            'env' => env('APP_ENV', 'local'),
            'debug' => env('APP_DEBUG', true)
        ]
    ];
    
    // DatabaseManager 초기화
    DatabaseManager::initialize();
    
} catch (Exception $e) {
    // 데이터베이스 연결 실패 시 로그 기록
    error_log('DatabaseManager 초기화 실패: ' . $e->getMessage());
}

// 헬퍼 함수들 로드
$helperFiles = [
    HOPEC_BASE_PATH . '/includes/template_helpers.php',
    HOPEC_BASE_PATH . '/includes/theme_helpers.php'
];

foreach ($helperFiles as $helperFile) {
    if (file_exists($helperFile)) {
        require_once $helperFile;
    }
}

// 테마 관련 전역 변수 설정
global $activeTheme;
$activeTheme = 'natural-green';

// Natural Green 단일 테마 시스템 함수들
if (!function_exists('get_natural_green_theme')) {
    function get_natural_green_theme() {
        static $theme = null;
        if ($theme === null) {
            if (!class_exists('NaturalGreenThemeLoader')) {
                require_once HOPEC_BASE_PATH . '/includes/NaturalGreenThemeLoader.php';
            }
            $theme = getNaturalGreenTheme();
        }
        return $theme;
    }
}

// Natural Green 테마 설정을 사용하는 함수들
if (!function_exists('getIntegratedSetting')) {
    function getIntegratedSetting($key, $default = '') {
        $theme = get_natural_green_theme();
        
        // 키에 따라 테마 설정 반환
        switch ($key) {
            case 'site_name':
                return $theme->getSiteName();
            case 'site_description':
                return $theme->getSiteDescription();
            case 'title':
                return $theme->getSiteTitle();
            case 'primary_color':
                return $theme->getPrimaryColor();
            default:
                return $theme->getConfig($key, $default);
        }
    }
}

// 그누보드 호환성을 위한 전역 변수들 (Natural Green 테마 설정 사용)
$theme = get_natural_green_theme();
$GLOBALS['g5'] = [
    'title' => $theme->getSiteName(),
    'meta_description' => $theme->getSiteDescription()
];

// 세션 시작 (필요한 경우)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 오류 보고 설정 (개발 환경)
if (env('APP_ENV', 'local') !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// 타임존 설정
date_default_timezone_set('Asia/Seoul');

// UTF-8 인코딩 설정
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

// 부트스트랩 완료 플래그
define('THEME_READY', true);
?>