<?php
/**
 * Templates Bridge - 현재 프로젝트와 재사용 프레임워크 연결
 * 
 * 단계적 마이그레이션을 위한 브릿지 시스템
 * 기존 코드를 최소한으로 변경하면서 재사용 프레임워크 도입
 */

// 재사용 프레임워크 로드 비활성화 (템플릿 충돌 방지)
/*
$framework_path = __DIR__ . '/../shared_admin_framework/bootstrap.php';
if (file_exists($framework_path)) {
    require_once $framework_path;
    
    // 현재 프로젝트 설정
    $project_config = [
        'project_name' => '<?= htmlspecialchars($admin_title) ?>',
        'theme' => 'hopec',
        'lang' => 'ko',
        'template_path' => __DIR__ . '/templates_project',  // 프로젝트 전용 템플릿
        'debug' => true,
        'cache_enabled' => true,
        'performance_monitoring' => true,
        'minify_output' => false  // 개발 단계에서는 비활성화
    ];

    // 프레임워크가 있을 때만 초기화
    if (class_exists('AdminFramework')) {
        AdminFramework::init($project_config);
    }
}
*/

// 캐시 클리어 요청 처리 (shared_admin_framework 비활성화로 인해 주석 처리)
/*
if (isset($_POST['action']) && $_POST['action'] === 'clear_cache') {
    AdminFramework::clearCache();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Cache cleared successfully']);
    exit;
}
*/

// 기존 코드 호환성을 위한 브릿지 클래스 (중복 방지)
if (!class_exists('TemplateHelper')) {
class TemplateHelper
{
    /**
     * 기존 renderLayout 메서드 호환
     */
    public static function renderLayout($layout, $data = [], $content_file = null)
    {
        // 컨텐츠 파일 처리
        if ($content_file) {
            ob_start();
            include $content_file;
            $data['content'] = ob_get_clean();
        }
        
        // 직접 템플릿 파일 로드 (프로젝트 전용)
        $template_file = __DIR__ . "/templates_project/layouts/{$layout}.php";
        if (file_exists($template_file)) {
            extract($data);
            include $template_file;
        } else {
            echo "Template not found: layouts/{$layout} at {$template_file}";
        }
    }
    
    /**
     * 기존 renderComponent 메서드 호환
     */
    public static function renderComponent($component, $data = [])
    {
        // 컴포넌트 파일 경로
        $component_file = __DIR__ . "/templates_project/components/{$component}.php";
        if (file_exists($component_file)) {
            extract($data);
            ob_start();
            include $component_file;
            return ob_get_clean();
        } else {
            return "Component not found: {$component}";
        }
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
        // BASE_PATH를 사용하여 올바른 admin URL 생성
        $base_path = getenv('BASE_PATH');
        if ($base_path === false) {
            $base_path = $_ENV['BASE_PATH'] ?? '/hopec';
        }
        
        $base_url = rtrim($base_path . '/admin', '/');
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
}

// 기존 전역 함수들 (호환성)
function html_escape($string)
{
    return TemplateHelper::escape($string);
}

function admin_url($path = '', $params = [])
{
    return TemplateHelper::url($path, $params);
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . 
           TemplateHelper::csrfToken() . '">';
}

// t_escape 함수 추가 (site_settings.php 호환성)
if (!function_exists('t_escape')) {
    function t_escape($string) {
        return TemplateHelper::escape($string);
    }
}

// t_url 함수 추가 (site_settings.php 호환성)
if (!function_exists('t_url')) {
    function t_url($path = '', $params = []) {
        return TemplateHelper::url($path, $params);
    }
}

// 새로운 재사용 프레임워크 함수들도 사용 가능
// admin_render(), admin_component(), admin_theme() 등

if (!function_exists('admin_component')) {
    function admin_component($component, $data = []) {
        return TemplateHelper::renderComponent($component, $data);
    }
}

if (!function_exists('logSecurityEvent')) {
    function logSecurityEvent($event_type, $description = '', $user_id = null) {
        // 임시 더미 함수 (실제 로깅은 bootstrap.php에서 처리)
        error_log("Security Event: {$event_type} - {$description}");
    }
}

if (!function_exists('t_url')) {
    function t_url($path = '') {
        return TemplateHelper::url($path);
    }
}

if (!function_exists('t_render_layout')) {
    function t_render_layout($layout, $data = []) {
        TemplateHelper::renderLayout($layout, $data);
    }
}
}