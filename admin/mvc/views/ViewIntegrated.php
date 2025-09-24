<?php
/**
 * ViewIntegrated - Admin_templates 완전 통합 View 클래스
 * 
 * 기존 View.php를 확장하여 Admin_templates의 모든 기능을 완전히 통합
 * AdminTemplateIntegration 클래스와 협력하여 최대 호환성 제공
 */

require_once __DIR__ . '/../helpers/AdminTemplateIntegration.php';

class ViewIntegrated extends View
{
    private $adminIntegration;
    private $layoutsPath;
    private $componentsPath;
    private $templateData = [];
    
    public function __construct($templatePath = null)
    {
        parent::__construct($templatePath);
        
        // AdminTemplateIntegration 초기화
        $this->adminIntegration = AdminTemplateIntegration::class;
        
        // 경로 설정
        $basePath = dirname(__DIR__);
        $this->layoutsPath = $basePath . '/views/layouts/';
        $this->componentsPath = $basePath . '/views/components/';
    }
    
    /**
     * 레이아웃 렌더링 (Admin_templates 완전 호환)
     */
    public function render($template, $data = [], $layout = null)
    {
        // 데이터 병합
        $this->setData($data);
        $mergedData = array_merge($this->templateData, $data);
        
        // AdminTemplateIntegration 사용하여 렌더링
        if ($layout) {
            AdminTemplateIntegration::renderLayout($layout, $mergedData);
        } else {
            // 템플릿만 렌더링
            $this->renderTemplate($template, $mergedData);
        }
    }
    
    /**
     * 컴포넌트 렌더링 (향상된 버전 우선)
     */
    public function renderComponent($component, $data = [])
    {
        return AdminTemplateIntegration::renderComponent($component, $data);
    }
    
    /**
     * 데이터 테이블 렌더링 (Admin_templates 완전 호환)
     */
    public function renderDataTable($data, $columns, $actions = [], $config = [])
    {
        return AdminTemplateIntegration::renderDataTable($data, $columns, $actions, $config);
    }
    
    /**
     * 페이지네이션 렌더링 (Admin_templates 완전 호환)
     */
    public function renderPagination($paginationData, $baseUrl = '', $options = [])
    {
        return AdminTemplateIntegration::renderPagination($paginationData, $baseUrl, $options);
    }
    
    /**
     * 알림 렌더링 (향상된 알림 시스템)
     */
    public function renderAlert($message = null, $type = 'info', $options = [])
    {
        return AdminTemplateIntegration::renderAlert($message, $type, $options);
    }
    
    /**
     * 검색 폼 렌더링
     */
    public function renderSearchForm($config = [])
    {
        return AdminTemplateIntegration::renderSearchForm($config);
    }
    
    /**
     * 브레드크럼 렌더링
     */
    public function renderBreadcrumb($items = [])
    {
        return AdminTemplateIntegration::renderBreadcrumb($items);
    }
    
    /**
     * 데이터 설정 (AdminTemplateIntegration과 동기화)
     */
    public function setData($data)
    {
        parent::setData($data);
        $this->templateData = array_merge($this->templateData, $data);
        AdminTemplateIntegration::setData($data);
    }
    
    /**
     * 단일 데이터 설정 (AdminTemplateIntegration과 동기화)
     */
    public function set($key, $value)
    {
        parent::set($key, $value);
        $this->templateData[$key] = $value;
        AdminTemplateIntegration::setData($key, $value);
    }
    
    /**
     * 템플릿 직접 렌더링 (내부 사용)
     */
    private function renderTemplate($template, $data = [])
    {
        $templateFile = $this->templatePath . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new Exception("템플릿 파일을 찾을 수 없습니다: {$template}");
        }
        
        extract($data);
        include $templateFile;
    }
    
    /**
     * Admin_templates 스타일 컴포넌트 출력 (echo)
     */
    public function component($component, $data = [])
    {
        echo $this->renderComponent($component, $data);
    }
    
    /**
     * Admin_templates 스타일 알림 출력
     */
    public function alerts($message = null, $type = 'info', $options = [])
    {
        echo $this->renderAlert($message, $type, $options);
    }
    
    /**
     * Admin_templates 스타일 데이터 테이블 출력
     */
    public function dataTable($data, $columns, $actions = [], $config = [])
    {
        echo $this->renderDataTable($data, $columns, $actions, $config);
    }
    
    /**
     * Admin_templates 스타일 페이지네이션 출력
     */
    public function pagination($paginationData, $baseUrl = '', $options = [])
    {
        echo $this->renderPagination($paginationData, $baseUrl, $options);
    }
    
    /**
     * 레이아웃과 함께 페이지 렌더링 (완전 호환 메서드)
     */
    public function renderPage($layout, $title, $contentCallback = null, $data = [])
    {
        // 기본 데이터 설정
        $pageData = array_merge([
            'title' => $title,
            'page_title' => $title,
            'show_breadcrumb' => true,
            'breadcrumb' => []
        ], $data);
        
        // 컨텐츠 콜백이 있으면 실행
        if ($contentCallback && is_callable($contentCallback)) {
            ob_start();
            $contentCallback($this);
            $pageData['content'] = ob_get_clean();
        }
        
        // AdminTemplateIntegration을 통해 렌더링
        AdminTemplateIntegration::renderLayout($layout, $pageData);
    }
    
    /**
     * 관리자 페이지 렌더링 (편의 메서드)
     */
    public function renderAdminPage($title, $contentCallback = null, $data = [], $layout = 'sidebar')
    {
        $this->renderPage($layout, $title, $contentCallback, $data);
    }
    
    /**
     * JSON 응답 (API 호환성)
     */
    public function renderJson($data, $options = JSON_UNESCAPED_UNICODE)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, $options);
    }
    
    /**
     * 리다이렉트 (메시지와 함께)
     */
    public function redirect($url, $message = null, $type = 'success')
    {
        if ($message) {
            AdminTemplateIntegration::setFlashMessage($type, $message);
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * HTML 이스케이프 (Admin_templates 호환)
     */
    public function escape($value, $allow_html = false)
    {
        return AdminTemplateIntegration::escape($value, $allow_html);
    }
    
    /**
     * URL 생성 (Admin_templates 호환)
     */
    public function url($path = '', $params = [])
    {
        return AdminTemplateIntegration::url($path, $params);
    }
    
    /**
     * 날짜 포맷팅 (Admin_templates 호환)
     */
    public function formatDate($date, $format = 'Y-m-d H:i')
    {
        return AdminTemplateIntegration::formatDate($date, $format);
    }
    
    /**
     * 상대적 시간 (Admin_templates 호환)
     */
    public function timeAgo($date)
    {
        return AdminTemplateIntegration::timeAgo($date);
    }
    
    /**
     * CSRF 토큰 필드
     */
    public function csrfField()
    {
        return AdminTemplateIntegration::csrfField();
    }
    
    /**
     * 파일 크기 포맷팅
     */
    public function formatFileSize($bytes)
    {
        return AdminTemplateIntegration::formatFileSize($bytes);
    }
    
    /**
     * 플래시 메시지 설정
     */
    public function setFlashMessage($type, $message)
    {
        AdminTemplateIntegration::setFlashMessage($type, $message);
    }
    
    /**
     * 성능 정보 가져오기
     */
    public function getPerformanceInfo()
    {
        return AdminTemplateIntegration::getPerformanceInfo();
    }
    
    /**
     * 디버그 정보 렌더링
     */
    public function renderDebugInfo()
    {
        return AdminTemplateIntegration::renderDebugInfo();
    }
    
    /**
     * 매직 메서드로 Admin_templates 헬퍼 함수 호출
     */
    public function __call($method, $args)
    {
        // AdminTemplateIntegration 클래스의 메서드 호출
        if (method_exists(AdminTemplateIntegration::class, $method)) {
            return call_user_func_array([AdminTemplateIntegration::class, $method], $args);
        }
        
        // 부모 클래스 메서드 호출
        if (method_exists(parent::class, $method)) {
            return call_user_func_array([parent::class, $method], $args);
        }
        
        throw new Exception("메서드를 찾을 수 없습니다: {$method}");
    }
}

// 전역 뷰 인스턴스 (편의성)
if (!function_exists('view')) {
    function view() {
        static $instance = null;
        if ($instance === null) {
            $instance = new ViewIntegrated();
        }
        return $instance;
    }
}