<?php
/**
 * 희망씨 웹사이트 부트스트랩
 * 
 * 모던 PHP 기반 부트스트랩 시스템
 */

// PHP 버전 체크
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('PHP 7.4.0 이상이 필요합니다. 현재 버전: ' . PHP_VERSION);
}

// 오류 리포팅 설정
error_reporting(E_ALL);
ini_set('display_errors', 0); // 기본적으로는 숨김 (환경에 따라 변경)

// 프로젝트 헬퍼 로드
require_once dirname(__DIR__) . '/includes/project_helpers.php';

// 동적으로 BASE_PATH 상수 정의
$constantName = define_project_base_path(__DIR__, 1);

// 업로드 경로 헬퍼 함수
if (!function_exists('get_upload_path')) {
    /**
     * 물리적 업로드 경로 반환 (BASE_PATH와 UPLOAD_PATH 조합)
     */
    function get_upload_path() {
        $base_path = rtrim(PROJECT_BASE_PATH, '/');
        $upload_path = env('UPLOAD_PATH', 'data/file');
        return $base_path . '/' . ltrim($upload_path, '/');
    }
}

if (!function_exists('get_upload_url')) {
    /**
     * 웹 접근 업로드 URL 반환 (상대 경로)
     */
    function get_upload_url() {
        return '/' . ltrim(env('UPLOAD_URL', 'data/file'), '/');
    }
}

if (!defined('younglabor_START_TIME')) {
    define('younglabor_START_TIME', microtime(true));
}

// 환경변수 로드
require_once __DIR__ . '/env.php';

// 타임존 설정
date_default_timezone_set('Asia/Seoul');

// UTF-8 설정
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

// 세션 설정 (admin의 검증된 보안 설정 적용)
if (session_status() === PHP_SESSION_NONE) {
    // 헤더가 전송되지 않은 경우에만 세션 설정 변경
    if (!headers_sent()) {
        // 보안 강화된 세션 설정
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', env('SESSION_SECURE', false) ? 1 : 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', env('SESSION_SAME_SITE', 'Strict'));
        ini_set('session.name', env('SESSION_NAME', 'younglabor_SESSION'));
        
        // 세션 만료 시간 설정
        ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME', 7200));
        ini_set('session.cookie_lifetime', env('SESSION_LIFETIME', 7200));
        
        session_start();
    } else {
        // 헤더가 이미 전송된 경우 기본 설정으로 세션 시작
        @session_start();
    }
}

// 환경에 따른 디버그 모드 설정
if (env('APP_DEBUG', false)) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// 개발 환경 체크
$isLocalEnv = in_array(env('APP_ENV'), ['local', 'development']) ||
              in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) ||
              strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
              strpos($_SERVER['HTTP_HOST'] ?? '', '.local') !== false;

if ($isLocalEnv && env('ERROR_DISPLAY', false)) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// 설정 파일들 로드
$configPath = PROJECT_BASE_PATH . '/config';
$configs = [];

// 설정 파일들을 배열로 로드
foreach (['app', 'database', 'security', 'cache'] as $configFile) {
    $configFilePath = $configPath . '/' . $configFile . '.php';
    if (file_exists($configFilePath)) {
        $configs[$configFile] = require $configFilePath;
    }
}

// 전역 설정 변수 설정
$GLOBALS['younglabor_config'] = $configs;

// 기본 헤더 설정
header('Content-Type: text/html; charset=utf-8');
if (env('SECURITY_HEADERS', true)) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
}

// 자동 로더 설정 (추후 Composer 대체 예정)
spl_autoload_register(function($className) {
    // 네임스페이스별 디렉토리 매핑
    $namespaceMap = [
        'younglabor\\' => PROJECT_BASE_PATH . '/src/',
        'younglabor\\Core\\' => PROJECT_BASE_PATH . '/src/Core/',
        'younglabor\\Database\\' => PROJECT_BASE_PATH . '/src/Database/',
        'younglabor\\Security\\' => PROJECT_BASE_PATH . '/src/Security/',
        'younglabor\\Menu\\' => PROJECT_BASE_PATH . '/src/Menu/',
        'younglabor\\Board\\' => PROJECT_BASE_PATH . '/src/Board/',
    ];
    
    foreach ($namespaceMap as $namespace => $directory) {
        if (strpos($className, $namespace) === 0) {
            $fileName = $directory . str_replace('\\', '/', substr($className, strlen($namespace))) . '.php';
            if (file_exists($fileName)) {
                require_once $fileName;
                return;
            }
        }
    }
    
    // 기본 includes 디렉토리에서 클래스 파일 찾기
    $classFile = PROJECT_BASE_PATH . '/includes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// 핵심 클래스들 로드 (순서 중요)
$coreClasses = [
    'DatabaseManager',
    'SecurityManager', 
    'ConfigManager',
    'MenuManager',
    'BoardManager'
];

foreach ($coreClasses as $class) {
    $classFile = PROJECT_BASE_PATH . '/includes/' . $class . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
}

// 보안 초기화
if (class_exists('SecurityManager')) {
    SecurityManager::initialize();
}

// 디버그 로거 초기화 (헬퍼 로드 전에)
$debugLoggerFile = PROJECT_BASE_PATH . '/includes/DebugLogger.php';
if (file_exists($debugLoggerFile)) {
    require_once $debugLoggerFile;
    $debugLogger = DebugLogger::getInstance();
    debug_log('Bootstrap: Debug Logger 초기화 완료');
}

// 유틸리티 함수 로드
$helperFiles = [
    'config_helpers.php',
    'security_helpers.php',
    'database_helpers.php', 
    'template_helpers.php',
    'menu_helpers.php',
];

debug_log('Bootstrap: 헬퍼 파일 로드 시작', ['files' => $helperFiles]);

foreach ($helperFiles as $helper) {
    $helperFile = PROJECT_BASE_PATH . '/includes/' . $helper;
    if (file_exists($helperFile)) {
        debug_log("Bootstrap: 헬퍼 파일 로드 중 - $helper");
        require_once $helperFile;
        debug_log("Bootstrap: 헬퍼 파일 로드 완료 - $helper");
    } else {
        debug_log("Bootstrap: 헬퍼 파일 없음 - $helper", ['path' => $helperFile]);
    }
}

// 레거시 호환성 코드 제거 완료 - 모던 아키텍처 사용

// 데이터베이스 연결 초기화
debug_log('Bootstrap: 데이터베이스 연결 시작');

if (class_exists('DatabaseManager')) {
    try {
        debug_log('Bootstrap: DatabaseManager 사용하여 연결 초기화');
        DatabaseManager::initialize();
        // 전역 PDO 변수 설정 (레거시 호환성)
        $GLOBALS['pdo'] = DatabaseManager::getConnection();
        debug_log('Bootstrap: DatabaseManager 연결 성공');
    } catch (Exception $e) {
        debug_log('Bootstrap: DatabaseManager 연결 실패', ['error' => $e->getMessage()]);
        if (env('APP_DEBUG')) {
            die('데이터베이스 연결 실패: ' . $e->getMessage());
        } else {
            error_log('Database connection failed: ' . $e->getMessage());
            die('일시적인 시스템 오류가 발생했습니다.');
        }
    }
} else {
    // DatabaseManager가 없는 경우 직접 PDO 연결
    try {
        $dbConfigPath = PROJECT_BASE_PATH . '/data/dbconfig.php';
        if (file_exists($dbConfigPath)) {
            include $dbConfigPath;
            
            // 그누보드 설정에서 변수 추출
            $db_host = env('DB_HOST', 'localhost');
            $db_user = env('DB_USERNAME', 'root');
            $db_pass = env('DB_PASSWORD', '');
            $db_name = env('DB_DATABASE', '');
            
            // 빈 비밀번호를 null로 변환
            $password = empty($db_pass) ? null : $db_pass;
            
            // 환경별 연결 시도 (DatabaseManager와 동일한 로직)
            $connected = false;
            $isProduction = env('APP_ENV') === 'production';
            
            // 프로덕션 환경에서는 TCP 연결만 시도
            if ($isProduction) {
                $GLOBALS['pdo'] = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $password);
                $GLOBALS['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $connected = true;
            } else {
                // 개발 환경: 소켓 연결 시도 후 TCP 연결
                $sockets = [
                    env('DB_SOCKET_XAMPP', '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock'),
                    env('DB_SOCKET_LINUX', '/var/run/mysqld/mysqld.sock')
                ];
                
                foreach ($sockets as $socket_path) {
                    if (file_exists($socket_path)) {
                        try {
                            $GLOBALS['pdo'] = new PDO("mysql:unix_socket=$socket_path;dbname=$db_name;charset=utf8mb4", $db_user, $password);
                            $GLOBALS['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $connected = true;
                            break;
                        } catch (PDOException $e) {
                            continue;
                        }
                    }
                }
                
                // 소켓 연결 실패시 TCP 연결 시도
                if (!$connected) {
                    $GLOBALS['pdo'] = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $password);
                    $GLOBALS['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
            }
        }
    } catch (Exception $e) {
        if (env('APP_DEBUG')) {
            die('데이터베이스 연결 실패: ' . $e->getMessage());
        } else {
            error_log('Database connection failed: ' . $e->getMessage());
        }
    }
}

// 성능 로깅 (디버그 모드)
if (env('PERFORMANCE_DEBUG', false)) {
    register_shutdown_function(function() {
        $executionTime = microtime(true) - younglabor_START_TIME;
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
        error_log(sprintf('[PERFORMANCE] Time: %.4fs, Memory: %.2fMB', $executionTime, $memoryUsage));
    });
}

// Organization Helper 로드
require_once __DIR__ . '/../includes/organization_helper.php';

// 전역 템플릿 변수 설정
$GLOBALS['younglabor_app'] = [
    'name' => env('APP_NAME', '희망씨'),
    'url' => env('APP_URL'),
    'version' => '2.0.0',
    'charset' => 'UTF-8',
    'timezone' => 'Asia/Seoul',
    'debug' => env('APP_DEBUG', false),
];