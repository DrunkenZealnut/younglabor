<?php
/**
 * 우동615 기존 Admin 시스템과 Shared Admin Framework 통합
 * 
 * 이 파일을 기존 admin 파일들에서 include하면 
 * 자동으로 Shared Admin Framework 기능을 사용할 수 있습니다.
 */

// 프레임워크가 이미 로드되었는지 확인
if (!defined('ADMIN_FRAMEWORK_PATH')) {
    require_once __DIR__ . '/../shared_admin_framework/bootstrap.php';
}

// 희망씨 프로젝트 설정
if (!AdminFramework::config('initialized')) {
    $hopecConfig = [
        'project_name' => '희망씨 관리자',
        'base_url' => 'http://hopec.local:8012/admin',
        'theme' => 'hopec',
        'language' => 'ko',
        'initialized' => true,
        'database' => [
            'host' => 'localhost',
            'name' => 'hopec',
            'user' => 'root',
            'pass' => '',
            'charset' => 'utf8mb4'
        ],
        'features' => [
            'csrf_protection' => true,
            'xss_protection' => true,
            'debug_mode' => false,
            'performance_monitoring' => true,
            'caching' => false
        ],
        'ui' => [
            'items_per_page' => 20,
            'date_format' => 'Y-m-d H:i',
            'time_format' => 'H:i'
        ],
        'paths' => [
            'project_root' => dirname(__DIR__),
            'admin_root' => __DIR__,
            'uploads' => dirname(__DIR__) . '/uploads',
            'logs' => dirname(__DIR__) . '/logs'
        ]
    ];
    
    AdminFramework::init($hopecConfig);
}

// 기존 templates_bridge.php와의 호환성을 위한 함수 재정의
if (!function_exists('t_escape')) {
    function t_escape($string) {
        return admin_escape($string);
    }
}

if (!function_exists('t_url')) {
    function t_url($path = '', $params = []) {
        return admin_url($path, $params);
    }
}

if (!function_exists('t_render_component')) {
    function t_render_component($component, $data = []) {
        echo admin_component($component, $data);
    }
}

if (!function_exists('t_render_layout')) {
    function t_render_layout($layout, $data = []) {
        admin_render('', $data, $layout);
    }
}

// TemplateHelper 클래스 호환성
if (!class_exists('TemplateHelper')) {
    class TemplateHelper {
        public static function renderLayout($layout, $data = [], $content_file = null) {
            if ($content_file) {
                ob_start();
                extract($data);
                include $content_file;
                $data['content'] = ob_get_clean();
            }
            
            return admin_render('', $data, $layout);
        }
        
        public static function renderComponent($component, $data = []) {
            return admin_component($component, $data);
        }
        
        public static function escape($text, $allow_html = false) {
            return admin_escape($text, $allow_html);
        }
        
        public static function url($path = '', $params = []) {
            return admin_url($path, $params);
        }
        
        public static function formatDate($date, $format = 'Y-m-d H:i') {
            return date($format, strtotime($date));
        }
        
        public static function timeAgo($date) {
            if (empty($date)) return '';
            
            $time = strtotime($date);
            $diff = time() - $time;
            
            if ($diff < 60) return '방금 전';
            if ($diff < 3600) return floor($diff / 60) . '분 전';
            if ($diff < 86400) return floor($diff / 3600) . '시간 전';
            if ($diff < 604800) return floor($diff / 86400) . '일 전';
            
            return date('Y-m-d', $time);
        }
        
        public static function csrfToken() {
            return admin_csrf_token();
        }
    }
}

// 추가 헬퍼 함수들
if (!function_exists('render_admin_page')) {
    /**
     * 관리자 페이지 렌더링 (기존 코드 호환성)
     */
    function render_admin_page($layout, $title, $contentCallback = null, $data = []) {
        $pageData = array_merge([
            'title' => $title,
            'page_title' => $title
        ], $data);
        
        if ($contentCallback && is_callable($contentCallback)) {
            ob_start();
            $contentCallback();
            $pageData['content'] = ob_get_clean();
        }
        
        admin_render('', $pageData, $layout);
    }
}

if (!function_exists('render_data_table')) {
    /**
     * 데이터 테이블 렌더링 (향상된 기능)
     */
    function render_data_table($data, $columns, $actions = [], $config = []) {
        return admin_component('data_table', [
            'data' => $data,
            'columns' => $columns,
            'row_actions' => $actions,
            'table_config' => $config
        ]);
    }
}

if (!function_exists('render_pagination')) {
    /**
     * 페이지네이션 렌더링 (향상된 기능)
     */
    function render_pagination($pagination, $baseUrl = '', $options = []) {
        return admin_component('pagination', [
            'pagination' => $pagination,
            'base_url' => $baseUrl ?: $_SERVER['REQUEST_URI']
        ] + $options);
    }
}

if (!function_exists('set_admin_message')) {
    /**
     * 관리자 메시지 설정
     */
    function set_admin_message($type, $message) {
        $_SESSION["{$type}_message"] = $message;
    }
}

if (!function_exists('get_admin_alerts')) {
    /**
     * 관리자 알림 표시
     */
    function get_admin_alerts() {
        return admin_component('alerts');
    }
}

// 데이터베이스 연결 (기존 시스템과 호환)
if (!function_exists('get_admin_db')) {
    function get_admin_db() {
        static $pdo = null;
        
        if ($pdo === null) {
            $config = AdminFramework::config('database');
            $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";
            
            try {
                $pdo = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                if (AdminFramework::config('features.debug_mode')) {
                    throw $e;
                } else {
                    error_log("Database connection failed: " . $e->getMessage());
                    die("데이터베이스 연결에 실패했습니다.");
                }
            }
        }
        
        return $pdo;
    }
}

// 보안 헬퍼
if (!function_exists('verify_admin_csrf')) {
    function verify_admin_csrf($token = null) {
        $token = $token ?: ($_POST['csrf_token'] ?? '');
        return hash_equals(admin_csrf_token(), $token);
    }
}

if (!function_exists('require_admin_auth')) {
    function require_admin_auth() {
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header('Location: login.php');
            exit;
        }
    }
}

// 파일 업로드 헬퍼
if (!function_exists('handle_admin_upload')) {
    function handle_admin_upload($file, $directory = 'uploads', $allowedTypes = ['jpg', 'png', 'gif', 'pdf']) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => '파일이 선택되지 않았습니다.'];
        }
        
        $uploadDir = AdminFramework::config('paths.uploads') . '/' . $directory . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'error' => '허용되지 않는 파일 형식입니다.'];
        }
        
        $fileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'success' => true,
                'filename' => $fileName,
                'path' => $filePath,
                'url' => admin_url($directory . '/' . $fileName)
            ];
        } else {
            return ['success' => false, 'error' => '파일 업로드에 실패했습니다.'];
        }
    }
}

// 로깅 헬퍼
if (!function_exists('log_admin_action')) {
    function log_admin_action($action, $details = '') {
        $logFile = AdminFramework::config('paths.logs', dirname(__DIR__) . '/logs') . '/admin.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['admin_user_id'] ?? 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $logEntry = "[{$timestamp}] User:{$userId} IP:{$ip} Action:{$action} Details:{$details}" . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// 성능 모니터링 (옵션)
if (AdminFramework::config('features.performance_monitoring')) {
    register_shutdown_function(function() {
        $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $memoryUsage = memory_get_peak_usage(true);
        
        if ($executionTime > 1.0 || $memoryUsage > 50 * 1024 * 1024) { // 1초 또는 50MB 초과시 로그
            log_admin_action('performance_warning', 
                "Execution time: " . number_format($executionTime * 1000, 2) . "ms, " .
                "Memory: " . number_format($memoryUsage / 1024 / 1024, 2) . "MB"
            );
        }
    });
}

// 개발 모드에서 디버그 정보 표시
if (AdminFramework::config('features.debug_mode')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // 디버그 정보를 HTML 주석으로 출력
    register_shutdown_function(function() {
        $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $memoryUsage = memory_get_peak_usage(true);
        $includeCount = count(get_included_files());
        
        echo "<!-- \n";
        echo "DEBUG INFO:\n";
        echo "Execution Time: " . number_format($executionTime * 1000, 2) . "ms\n";
        echo "Memory Usage: " . number_format($memoryUsage / 1024 / 1024, 2) . "MB\n";
        echo "Files Included: {$includeCount}\n";
        echo "Framework Version: " . AdminFramework::version() . "\n";
        echo "-->\n";
    });
}

// 자동으로 alerts 컴포넌트 표시를 위한 출력 버퍼링
if (!headers_sent()) {
    ob_start(function($content) {
        // </body> 태그 바로 전에 성능 정보 삽입 (디버그 모드)
        if (AdminFramework::config('features.debug_mode') && strpos($content, '</body>') !== false) {
            $debugInfo = "
            <div style='position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; font-family: monospace; z-index: 9999;'>
                <div>⚡ " . number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . "ms</div>
                <div>🧠 " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB</div>
                <div>📁 " . count(get_included_files()) . " files</div>
            </div>";
            
            $content = str_replace('</body>', $debugInfo . '</body>', $content);
        }
        
        return $content;
    });
}

// 통합 완료 로그
if (AdminFramework::config('features.debug_mode')) {
    error_log("Shared Admin Framework integrated successfully for Udong615 project");
}