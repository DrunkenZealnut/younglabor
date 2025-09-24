<?php
/**
 * AdminTemplateIntegration - Admin_templates 완전 통합 클래스
 * 
 * Admin_templates의 모든 기능을 MVC 시스템으로 통합하는 브릿지 클래스
 * 기존 TemplateHelper.php를 완전히 대체하면서 모든 호환성 보장
 * 
 * 주요 기능:
 * 1. Admin_templates 레이아웃/컴포넌트 시스템 통합
 * 2. MVC View 클래스와 원활한 연동
 * 3. 기존 코드 100% 호환성 보장
 * 4. 확장된 컴포넌트 시스템 (enhanced 버전 포함)
 * 5. 메시지 시스템, 페이지네이션, 데이터 테이블 등 완전 통합
 */

require_once dirname(__DIR__) . '/views/View.php';

class AdminTemplateIntegration
{
    private static $viewInstance = null;
    private static $globalData = [];
    private static $templatePaths = [];
    private static $config = [
        'debug_mode' => false,
        'cache_enabled' => true,
        'performance_monitoring' => true,
        'auto_escape' => true,
        'csrf_protection' => true
    ];
    
    /**
     * 초기화
     */
    public static function init($config = [])
    {
        self::$config = array_merge(self::$config, $config);
        
        // 템플릿 경로 설정
        $basePath = dirname(__DIR__);
        self::$templatePaths = [
            'layouts' => $basePath . '/views/layouts/',
            'components' => $basePath . '/views/components/',
            'templates' => $basePath . '/views/templates/',
            'partials' => $basePath . '/views/partials/'
        ];
        
        // Admin_templates 호환 경로 추가
        $adminTemplatesPath = dirname(dirname(__DIR__)) . '/Admin_templates/templates/';
        if (is_dir($adminTemplatesPath)) {
            self::$templatePaths['admin_layouts'] = $adminTemplatesPath . 'layouts/';
            self::$templatePaths['admin_components'] = $adminTemplatesPath . 'components/';
        }
        
        // 세션 시작 (CSRF 보호용)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * View 인스턴스 가져오기
     */
    private static function getView(): View
    {
        if (self::$viewInstance === null) {
            self::$viewInstance = new View();
        }
        return self::$viewInstance;
    }
    
    /**
     * 레이아웃 렌더링 (Admin_templates 완전 호환)
     */
    public static function renderLayout($layout, $data = [], $content_file = null)
    {
        $view = self::getView();
        $mergedData = array_merge(self::$globalData, $data);
        
        // 컨텐츠 파일 처리
        if ($content_file) {
            ob_start();
            extract($mergedData);
            if (file_exists($content_file)) {
                include $content_file;
            } else {
                echo "<!-- Content file not found: {$content_file} -->";
            }
            $mergedData['content'] = ob_get_clean();
        }
        
        // 레이아웃 파일 찾기 (우선순위: MVC -> Admin_templates)
        $layoutPaths = [
            self::$templatePaths['layouts'] . $layout . '.php',
            self::$templatePaths['admin_layouts'] . $layout . '.php'
        ];
        
        $layoutFile = null;
        foreach ($layoutPaths as $path) {
            if (file_exists($path)) {
                $layoutFile = $path;
                break;
            }
        }
        
        if ($layoutFile) {
            extract($mergedData);
            include $layoutFile;
        } else {
            throw new Exception("레이아웃 파일을 찾을 수 없습니다: layouts/{$layout}");
        }
    }
    
    /**
     * 컴포넌트 렌더링 (향상된 버전 우선)
     */
    public static function renderComponent($component, $data = [])
    {
        $mergedData = array_merge(self::$globalData, $data);
        
        // Enhanced 버전 우선 검색
        $componentPaths = [
            self::$templatePaths['components'] . $component . '_enhanced.php',
            self::$templatePaths['components'] . $component . '.php',
            self::$templatePaths['admin_components'] . $component . '.php'
        ];
        
        $componentFile = null;
        foreach ($componentPaths as $path) {
            if (file_exists($path)) {
                $componentFile = $path;
                break;
            }
        }
        
        if ($componentFile) {
            extract($mergedData);
            ob_start();
            include $componentFile;
            return ob_get_clean();
        } else {
            if (self::$config['debug_mode']) {
                return "<!-- Component not found: {$component} -->";
            }
            return '';
        }
    }
    
    /**
     * 데이터 테이블 렌더링 (Admin_templates 완전 호환)
     */
    public static function renderDataTable($data, $columns, $actions = [], $config = [])
    {
        return self::renderComponent('data_table_enhanced', [
            'data' => $data,
            'columns' => $columns,
            'row_actions' => $actions,
            'table_config' => $config,
            // Admin_templates 호환성
            'table_data' => $data,
            'table_columns' => $columns,
            'actions' => is_callable($actions) ? $actions : null
        ]);
    }
    
    /**
     * 페이지네이션 렌더링 (Admin_templates 완전 호환)
     */
    public static function renderPagination($pagination, $baseUrl = '', $options = [])
    {
        return self::renderComponent('pagination_enhanced', [
            'pagination' => $pagination,
            'base_url' => $baseUrl,
            'show_info' => $options['show_info'] ?? true,
            'show_first_last' => $options['show_first_last'] ?? true,
            'compact' => $options['compact'] ?? false
        ]);
    }
    
    /**
     * 알림 렌더링 (향상된 알림 시스템)
     */
    public static function renderAlert($message = null, $type = 'info', $options = [])
    {
        $data = [];
        
        if ($message !== null) {
            $data['custom_messages'] = [$type => $message];
        }
        
        $data = array_merge($data, $options);
        
        return self::renderComponent('alerts_enhanced', $data);
    }
    
    /**
     * 검색 폼 렌더링
     */
    public static function renderSearchForm($config = [])
    {
        return self::renderComponent('search_form', $config);
    }
    
    /**
     * 브레드크럼 렌더링
     */
    public static function renderBreadcrumb($items = [])
    {
        return self::renderComponent('breadcrumb', ['breadcrumb' => $items, 'items' => $items]);
    }
    
    /**
     * 전역 데이터 설정
     */
    public static function setData($key, $value = null)
    {
        if (is_array($key)) {
            self::$globalData = array_merge(self::$globalData, $key);
        } else {
            self::$globalData[$key] = $value;
        }
    }
    
    /**
     * 전역 데이터 가져오기
     */
    public static function getData($key = null)
    {
        if ($key === null) {
            return self::$globalData;
        }
        return self::$globalData[$key] ?? null;
    }
    
    /**
     * HTML 이스케이프 (Admin_templates 호환)
     */
    public static function escape($text, $allow_html = false)
    {
        if ($text === null || $text === '') {
            return '';
        }
        
        if ($allow_html) {
            $allowed_tags = '<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><span><div>';
            return strip_tags((string)$text, $allowed_tags);
        }
        
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * URL 생성 (Admin_templates 호환)
     */
    public static function url($path = '', $params = [])
    {
        // 기본 URL 생성 로직
        if (function_exists('get_base_url')) {
            $base_url = get_base_url();
        } else {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base_url = $protocol . '://' . $host;
        }
        
        $url = rtrim($base_url, '/') . '/admin/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * 날짜 포맷팅 (Admin_templates 호환)
     */
    public static function formatDate($date, $format = 'Y-m-d H:i')
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '';
        }
        
        try {
            if (is_string($date)) {
                $date = new DateTime($date);
            }
            return $date->format($format);
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * 상대적 시간 표시 (Admin_templates 호환)
     */
    public static function timeAgo($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '';
        }
        
        try {
            if (is_string($date)) {
                $date = new DateTime($date);
            }
            
            $now = new DateTime();
            $diff = $now->getTimestamp() - $date->getTimestamp();
            
            if ($diff < 60) {
                return '방금 전';
            } elseif ($diff < 3600) {
                return floor($diff / 60) . '분 전';
            } elseif ($diff < 86400) {
                return floor($diff / 3600) . '시간 전';
            } elseif ($diff < 604800) {
                return floor($diff / 86400) . '일 전';
            } else {
                return $date->format('Y-m-d');
            }
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * CSRF 토큰 생성 (Admin_templates 호환)
     */
    public static function csrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF 필드 생성
     */
    public static function csrfField()
    {
        return '<input type="hidden" name="csrf_token" value="' . 
               self::csrfToken() . '">';
    }
    
    /**
     * 활성 메뉴 클래스 반환 (Admin_templates 호환)
     */
    public static function activeClass($menu_key, $active_menu, $class = 'active')
    {
        return $menu_key === $active_menu ? $class : '';
    }
    
    /**
     * 파일 크기 포맷팅
     */
    public static function formatFileSize($bytes)
    {
        if (function_exists('format_file_size')) {
            return format_file_size($bytes);
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 메시지 시스템 헬퍼
     */
    public static function setFlashMessage($type, $message)
    {
        $_SESSION["{$type}_message"] = $message;
    }
    
    /**
     * 성능 모니터링 정보
     */
    public static function getPerformanceInfo()
    {
        if (!self::$config['performance_monitoring']) {
            return [];
        }
        
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'included_files' => count(get_included_files())
        ];
    }
    
    /**
     * 디버그 정보 출력
     */
    public static function renderDebugInfo()
    {
        if (!self::$config['debug_mode']) {
            return '';
        }
        
        $info = self::getPerformanceInfo();
        ob_start();
        ?>
        <div class="debug-panel bg-light p-3 mt-4 border rounded">
            <h6><i class="bi bi-bug"></i> 디버그 정보</h6>
            <div class="row">
                <div class="col-md-3">
                    <small><strong>메모리 사용량:</strong><br><?= self::formatFileSize($info['memory_usage']) ?></small>
                </div>
                <div class="col-md-3">
                    <small><strong>최대 메모리:</strong><br><?= self::formatFileSize($info['memory_peak']) ?></small>
                </div>
                <div class="col-md-3">
                    <small><strong>실행 시간:</strong><br><?= number_format($info['execution_time'] * 1000, 2) ?>ms</small>
                </div>
                <div class="col-md-3">
                    <small><strong>포함된 파일:</strong><br><?= $info['included_files'] ?>개</small>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// 전역 호환성 함수들 - Admin_templates 완전 호환
if (!function_exists('html_escape')) {
    function html_escape($string) {
        return AdminTemplateIntegration::escape($string);
    }
}

if (!function_exists('t_escape')) {
    function t_escape($string) {
        return AdminTemplateIntegration::escape($string);
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $params = []) {
        return AdminTemplateIntegration::url($path, $params);
    }
}

if (!function_exists('t_url')) {
    function t_url($path = '', $params = []) {
        return AdminTemplateIntegration::url($path, $params);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return AdminTemplateIntegration::csrfField();
    }
}

if (!function_exists('t_render_component')) {
    function t_render_component($component, $data = []) {
        echo AdminTemplateIntegration::renderComponent($component, $data);
    }
}

if (!function_exists('admin_component')) {
    function admin_component($component, $data = []) {
        return AdminTemplateIntegration::renderComponent($component, $data);
    }
}

if (!function_exists('t_render_layout')) {
    function t_render_layout($layout, $data = []) {
        AdminTemplateIntegration::renderLayout($layout, $data);
    }
}

// 새로운 편의 함수들
if (!function_exists('render_data_table')) {
    function render_data_table($data, $columns, $actions = [], $config = []) {
        return AdminTemplateIntegration::renderDataTable($data, $columns, $actions, $config);
    }
}

if (!function_exists('render_pagination')) {
    function render_pagination($pagination, $baseUrl = '', $options = []) {
        return AdminTemplateIntegration::renderPagination($pagination, $baseUrl, $options);
    }
}

if (!function_exists('render_alerts')) {
    function render_alerts($message = null, $type = 'info', $options = []) {
        return AdminTemplateIntegration::renderAlert($message, $type, $options);
    }
}

// 자동 초기화
AdminTemplateIntegration::init();