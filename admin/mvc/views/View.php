<?php
/**
 * View 클래스 - 템플릿 렌더링
 * board_templates 패턴 적용
 */

class View 
{
    private $templatePath;
    private $componentsPath;
    private $layoutsPath;
    private $layout = 'sidebar';
    private $data = [];
    
    public function __construct($templatePath = null) 
    {
        $this->templatePath = $templatePath ?: dirname(__DIR__) . '/views/templates/';
        $this->componentsPath = dirname(__DIR__) . '/views/components/';
        $this->layoutsPath = dirname(__DIR__) . '/views/layouts/';
    }
    
    /**
     * 데이터 설정
     */
    public function set($key, $value) 
    {
        $this->data[$key] = $value;
    }
    
    /**
     * 여러 데이터 설정
     */
    public function setData($data) 
    {
        $this->data = array_merge($this->data, $data);
    }
    
    /**
     * 레이아웃 설정
     */
    public function setLayout($layout) 
    {
        $this->layout = $layout;
    }
    
    /**
     * 컴포넌트 렌더링 (Admin_templates 통합)
     */
    public function renderComponent($component, $data = [])
    {
        $componentFile = $this->componentsPath . $component . '.php';
        
        if (!file_exists($componentFile)) {
            throw new Exception("컴포넌트 파일을 찾을 수 없습니다: {$component}");
        }
        
        // 현재 뷰 데이터와 컴포넌트 데이터 병합
        $mergedData = array_merge($this->data, $data);
        extract($mergedData);
        
        ob_start();
        include $componentFile;
        return ob_get_clean();
    }
    
    /**
     * 데이터 테이블 렌더링 (Admin_templates 호환)
     */
    public function renderDataTable($data, $columns, $actions = [], $config = [])
    {
        return $this->renderComponent('data_table', [
            'data' => $data,
            'columns' => $columns,
            'row_actions' => $actions,
            'table_config' => $config
        ]);
    }
    
    /**
     * 알림 컴포넌트 렌더링
     */
    public function renderAlert($message, $type = 'info', $dismissible = true)
    {
        return $this->renderComponent('alerts', [
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible
        ]);
    }
    
    /**
     * 검색 폼 컴포넌트 렌더링
     */
    public function renderSearchForm($config = [])
    {
        return $this->renderComponent('search_form', $config);
    }
    
    /**
     * 페이지네이션 컴포넌트 렌더링 (Admin_templates 호환)
     */
    public function renderPagination($paginationData, $baseUrl = '')
    {
        return $this->renderComponent('pagination', [
            'pagination' => $paginationData,
            'base_url' => $baseUrl
        ]);
    }
    
    /**
     * 템플릿 렌더링
     */
    public function render($template, $data = [], $layout = null) 
    {
        // 데이터 병합
        $this->setData($data);
        
        // 레이아웃 설정
        if ($layout) {
            $this->setLayout($layout);
        }
        
        // 템플릿 경로
        $templateFile = $this->templatePath . $template . '.php';
        $layoutFile = $this->layoutsPath . $this->layout . '.php';
        
        // 템플릿 파일 존재 확인
        if (!file_exists($templateFile)) {
            throw new Exception("템플릿 파일을 찾을 수 없습니다: {$template}");
        }
        
        // 데이터를 변수로 추출
        extract($this->data);
        
        // 템플릿 콘텐츠 캡처
        ob_start();
        include $templateFile;
        $content = ob_get_clean();
        
        // 레이아웃이 있으면 레이아웃으로 래핑
        if ($this->layout && file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    /**
     * 부분 템플릿 렌더링 (레이아웃 없음)
     */
    public function renderPartial($template, $data = []) 
    {
        $templateFile = $this->templatePath . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new Exception("템플릿 파일을 찾을 수 없습니다: {$template}");
        }
        
        // 현재 데이터와 새 데이터 병합
        $mergedData = array_merge($this->data, $data);
        extract($mergedData);
        
        include $templateFile;
    }
    
    /**
     * JSON 렌더링
     */
    public function renderJson($data, $options = JSON_UNESCAPED_UNICODE) 
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, $options);
    }
    
    /**
     * 템플릿 헬퍼 메서드들
     */
    
    /**
     * HTML 이스케이프
     */
    public function escape($value) 
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * URL 생성
     */
    public function url($path) 
    {
        $baseUrl = $this->getBaseUrl();
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * 기본 URL 가져오기
     */
    private function getBaseUrl() 
    {
        // get_base_url() 함수가 없는 경우 직접 구현
        if (function_exists('get_base_url')) {
            return get_base_url();
        }
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $port = $_SERVER['SERVER_PORT'] ?? 80;
        
        // 기본 포트가 아닌 경우 포트 번호 추가
        if (($protocol === 'http' && $port != 80) || ($protocol === 'https' && $port != 443)) {
            $host .= ':' . $port;
        }
        
        return $protocol . '://' . $host;
    }
    
    /**
     * 플래시 메시지 표시
     */
    public function flashMessage() 
    {
        $message = get_flash_message();
        if ($message) {
            return "<div class='alert alert-{$message['type']} alert-dismissible fade show' role='alert'>
                        {$message['message']}
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
        }
        return '';
    }
    
    /**
     * CSRF 토큰 필드
     */
    public function csrfField() 
    {
        $token = generateCSRFToken();
        return "<input type='hidden' name='csrf_token' value='{$token}'>";
    }
    
    /**
     * 페이지네이션 렌더링
     */
    public function pagination($pagination, $baseUrl) 
    {
        if ($pagination['total_pages'] <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="페이지 네비게이션">
                    <ul class="pagination justify-content-center">';
        
        // 이전 페이지
        if ($pagination['has_prev']) {
            $html .= "<li class='page-item'>
                        <a class='page-link' href='{$baseUrl}?page={$pagination['prev_page']}'>이전</a>
                      </li>";
        }
        
        // 페이지 번호들
        $start = max(1, $pagination['current_page'] - 2);
        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $pagination['current_page']) ? 'active' : '';
            $html .= "<li class='page-item {$active}'>
                        <a class='page-link' href='{$baseUrl}?page={$i}'>{$i}</a>
                      </li>";
        }
        
        // 다음 페이지
        if ($pagination['has_next']) {
            $html .= "<li class='page-item'>
                        <a class='page-link' href='{$baseUrl}?page={$pagination['next_page']}'>다음</a>
                      </li>";
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
    
    /**
     * 날짜 포맷팅
     */
    public function formatDate($date, $format = 'Y-m-d H:i') 
    {
        return date($format, strtotime($date));
    }
    
    /**
     * 파일 크기 포맷팅
     */
    public function formatFileSize($bytes) 
    {
        if (function_exists('format_file_size')) {
            return format_file_size($bytes);
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}