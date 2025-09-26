<?php
/**
 * TemplateHelper - MVC와 기존 Admin_templates 브릿지
 * 
 * 기존 templates_bridge.php 기능을 MVC 패턴으로 마이그레이션하는 헬퍼 클래스
 * Admin_templates 기능을 완전히 MVC 뷰 시스템에 통합
 */

class TemplateHelper
{
    private static $viewInstance = null;
    
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
     * 레이아웃 렌더링 (기존 templates_bridge 호환)
     */
    public static function renderLayout($layout, $data = [], $content_file = null)
    {
        $view = self::getView();
        
        // 컨텐츠 파일 처리
        if ($content_file) {
            ob_start();
            extract($data);
            include $content_file;
            $data['content'] = ob_get_clean();
        }
        
        $view->setLayout($layout);
        $view->setData($data);
        
        // 레이아웃 파일 직접 렌더링
        $layoutFile = dirname(__DIR__) . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            extract($data);
            include $layoutFile;
        } else {
            throw new Exception("레이아웃 파일을 찾을 수 없습니다: layouts/{$layout}");
        }
    }
    
    /**
     * 컴포넌트 렌더링 (기존 templates_bridge 호환)
     */
    public static function renderComponent($component, $data = [])
    {
        $view = self::getView();
        return $view->renderComponent($component, $data);
    }
    
    /**
     * 데이터 테이블 렌더링 (Admin_templates 호환)
     */
    public static function renderDataTable($data, $columns, $actions = [], $config = [])
    {
        $view = self::getView();
        return $view->renderDataTable($data, $columns, $actions, $config);
    }
    
    /**
     * 페이지네이션 렌더링 (Admin_templates 호환)
     */
    public static function renderPagination($pagination, $baseUrl = '')
    {
        $view = self::getView();
        return $view->renderPagination($pagination, $baseUrl);
    }
    
    /**
     * 알림 렌더링
     */
    public static function renderAlert($message, $type = 'info', $dismissible = true)
    {
        $view = self::getView();
        return $view->renderAlert($message, $type, $dismissible);
    }
    
    /**
     * 검색 폼 렌더링
     */
    public static function renderSearchForm($config = [])
    {
        $view = self::getView();
        return $view->renderSearchForm($config);
    }
    
    /**
     * HTML 이스케이프 (기존 호환)
     */
    public static function escape($text, $allow_html = false)
    {
        if ($allow_html) {
            $allowed_tags = '<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code>';
            return strip_tags((string)$text, $allowed_tags);
        }
        
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * URL 생성 (기존 호환)
     */
    public static function url($path = '', $params = [])
    {
        $base_url = rtrim('/admin', '/');
        $url = $base_url . '/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * 날짜 포맷팅 (기존 호환)
     */
    public static function formatDate($date, $format = 'Y-m-d H:i')
    {
        if (empty($date)) return '';
        
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
    
    /**
     * 상대적 시간 (기존 호환)
     */
    public static function timeAgo($date)
    {
        if (empty($date)) return '';
        
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
    }
    
    /**
     * CSRF 토큰 (기존 호환)
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
     * 플래시 메시지 렌더링
     */
    public static function flashMessage()
    {
        if (function_exists('get_flash_message')) {
            $message = get_flash_message();
            if ($message) {
                return self::renderAlert($message['message'], $message['type'], true);
            }
        }
        return '';
    }
    
    /**
     * 브레드크럼 렌더링
     */
    public static function renderBreadcrumb($items = [])
    {
        $view = self::getView();
        return $view->renderComponent('breadcrumb', ['items' => $items]);
    }
    
    /**
     * 노동권 카드 렌더링
     */
    public static function renderLaborRightsCard($data = [])
    {
        $view = self::getView();
        return $view->renderComponent('admin_card', $data);
    }
    
    /**
     * 교육 진행도 렌더링
     */
    public static function renderEducationProgress($data = [])
    {
        $view = self::getView();
        return $view->renderComponent('education_progress', $data);
    }
    
    /**
     * 퀵 액션 렌더링
     */
    public static function renderQuickActions($actions = [])
    {
        $view = self::getView();
        return $view->renderComponent('quick_actions', ['actions' => $actions]);
    }
    
    /**
     * 성능 디버그 렌더링
     */
    public static function renderPerformanceDebug($data = [])
    {
        $view = self::getView();
        return $view->renderComponent('performance_debug', $data);
    }
}

// 전역 호환성 함수들 (기존 templates_bridge.php 호환)
if (!function_exists('html_escape')) {
    function html_escape($string)
    {
        return TemplateHelper::escape($string);
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $params = [])
    {
        return TemplateHelper::url($path, $params);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return TemplateHelper::csrfField();
    }
}

if (!function_exists('admin_component')) {
    function admin_component($component, $data = []) {
        return TemplateHelper::renderComponent($component, $data);
    }
}

if (!function_exists('t_url')) {
    function t_url($path = '') {
        return TemplateHelper::url($path);
    }
}

if (!function_exists('t_render_layout')) {
    function t_render_layout($layout, $data = []) {
        return TemplateHelper::renderLayout($layout, $data);
    }
}