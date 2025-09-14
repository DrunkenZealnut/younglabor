<?php
/**
 * 템플릿 관련 헬퍼 함수들
 */

if (!function_exists('app_config')) {
    /**
     * 애플리케이션 설정 조회
     */
    function app_config($key = null, $default = null) {
        $config = $GLOBALS['hopec_config']['app'] ?? [];
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
}

if (!function_exists('app_name')) {
    /**
     * 애플리케이션 이름
     */
    function app_name() {
        return app_config('name', '희망씨');
    }
}

if (!function_exists('app_url')) {
    /**
     * 애플리케이션 URL
     */
    function app_url($path = '') {
        $url = rtrim(env('APP_URL', ''), '/');
        return $path ? $url . '/' . ltrim($path, '/') : $url;
    }
}

if (!function_exists('fix_image_url')) {
    /**
     * 이미지 URL 수정 - 프로덕션 URL을 로컬 URL로 변환
     */
    function fix_image_url($imageUrl) {
        if (empty($imageUrl)) {
            return '';
        }
        
        // 프로덕션 URL 패턴들을 현재 APP_URL로 교체
        $productionUrls = [
            'http://hopec.co.kr',
            'https://hopec.co.kr',
            'http://www.hopec.co.kr',
            'https://www.hopec.co.kr'
        ];
        
        $currentUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
        
        foreach ($productionUrls as $prodUrl) {
            if (strpos($imageUrl, $prodUrl) === 0) {
                return str_replace($prodUrl, $currentUrl, $imageUrl);
            }
        }
        
        // 상대경로인 경우 현재 URL과 결합
        if (strpos($imageUrl, 'http') !== 0) {
            return $currentUrl . '/' . ltrim($imageUrl, '/');
        }
        
        return $imageUrl;
    }
}

if (!function_exists('is_debug')) {
    /**
     * 디버그 모드 확인
     */
    function is_debug() {
        return app_config('debug', false);
    }
}

if (!function_exists('include_template')) {
    /**
     * 템플릿 파일 포함
     */
    function include_template($template, $variables = []) {
        // 변수들을 로컬 스코프에 추출
        if (!empty($variables)) {
            extract($variables, EXTR_SKIP);
        }
        
        $templateFile = HOPEC_BASE_PATH . '/templates/' . $template . '.php';
        
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            if (is_debug()) {
                echo "<!-- Template not found: {$templateFile} -->";
            }
        }
    }
}

if (!function_exists('render_template')) {
    /**
     * 템플릿 렌더링 (출력하지 않고 반환)
     */
    function render_template($template, $variables = []) {
        ob_start();
        include_template($template, $variables);
        return ob_get_clean();
    }
}

if (!function_exists('asset_url')) {
    /**
     * Asset URL 생성
     */
    function asset_url($path) {
        return app_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('css_url')) {
    /**
     * CSS URL 생성
     */
    function css_url($filename) {
        return asset_url('css/' . $filename);
    }
}

if (!function_exists('js_url')) {
    /**
     * JS URL 생성
     */
    function js_url($filename) {
        return asset_url('js/' . $filename);
    }
}

if (!function_exists('img_url')) {
    /**
     * 이미지 URL 생성
     */
    function img_url($filename) {
        return asset_url('images/' . $filename);
    }
}

if (!function_exists('logo_url')) {
    /**
     * 로고 이미지 URL 생성 - admin 설정에서 가져오기
     */
    function logo_url($fallback_filename = 'logo.png') {
        // admin 설정에서 site_logo 값 가져오기
        try {
            // 우선 전역 $pdo 사용 시도
            global $pdo;
            $db_connection = null;
            
            if (isset($pdo)) {
                $db_connection = $pdo;
            } else {
                // DB 연결이 없는 경우 직접 연결
                $basePath = $_SERVER['DOCUMENT_ROOT'];
                if (file_exists($basePath . '/data/dbconfig.php')) {
                    include_once $basePath . '/data/dbconfig.php';
                    $db_connection = new PDO(
                        "mysql:host=" . G5_MYSQL_HOST . ";dbname=" . G5_MYSQL_DB . ";charset=utf8mb4",
                        G5_MYSQL_USER,
                        G5_MYSQL_PASSWORD,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                } elseif (file_exists($basePath . '/config.php')) {
                    include_once $basePath . '/config.php';
                    // Fallback: MySQL 상수가 정의되지 않은 경우 기본값 사용
                    if (!defined('G5_MYSQL_HOST')) {
                        define('G5_MYSQL_HOST', 'localhost');
                        define('G5_MYSQL_USER', 'root');
                        define('G5_MYSQL_PASSWORD', '');
                        define('G5_MYSQL_DB', 'hopec');
                    }
                    $db_connection = new PDO(
                        "mysql:host=" . G5_MYSQL_HOST . ";dbname=" . G5_MYSQL_DB . ";charset=utf8mb4",
                        G5_MYSQL_USER,
                        G5_MYSQL_PASSWORD,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                }
            }
            
            if ($db_connection) {
                $stmt = $db_connection->prepare("SELECT setting_value FROM hopec_site_settings WHERE setting_key = 'site_logo'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && !empty($result['setting_value'])) {
                    // 상대 경로로 저장된 경우 절대 URL로 변환
                    $logo_path = $result['setting_value'];
                    if (!preg_match('/^https?:\/\//', $logo_path)) {
                        // 루트 경로 기준으로 URL 생성
                        $logo_url = '/' . ltrim($logo_path, '/');
                        
                        // 브라우저 캐시 방지를 위해 파일 수정 시간을 쿼리 파라미터로 추가
                        $file_path = $_SERVER['DOCUMENT_ROOT'] . $logo_url;
                        if (file_exists($file_path)) {
                            $logo_url .= '?v=' . filemtime($file_path);
                        }
                        
                        return $logo_url;
                    }
                    return $logo_path;
                }
            }
        } catch (Exception $e) {
            // DB 오류 시 fallback 사용
            error_log("logo_url() DB error: " . $e->getMessage());
        }
        
        // fallback: 기본 이미지 경로
        return img_url($fallback_filename);
    }
}

if (!function_exists('favicon_url')) {
    /**
     * 파비콘 이미지 URL 생성 - admin 설정에서 가져오기
     */
    function favicon_url($fallback_filename = 'favicon.ico') {
        // admin 설정에서 site_favicon 값 가져오기
        try {
            // 우선 전역 $pdo 사용 시도
            global $pdo;
            $db_connection = null;
            
            if (isset($pdo)) {
                $db_connection = $pdo;
            } else {
                // DB 연결이 없는 경우 직접 연결
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
                    include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
                }
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/dbconfig.php')) {
                    include_once $_SERVER['DOCUMENT_ROOT'] . '/data/dbconfig.php';
                    $db_connection = new PDO(
                        "mysql:host=" . G5_MYSQL_HOST . ";dbname=" . G5_MYSQL_DB . ";charset=utf8mb4",
                        G5_MYSQL_USER,
                        G5_MYSQL_PASSWORD,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                }
            }
            
            if ($db_connection) {
                $stmt = $db_connection->prepare("SELECT setting_value FROM hopec_site_settings WHERE setting_key = 'site_favicon'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && !empty($result['setting_value'])) {
                    // 상대 경로로 저장된 경우 절대 URL로 변환
                    $favicon_path = $result['setting_value'];
                    if (!preg_match('/^https?:\/\//', $favicon_path)) {
                        // 루트 경로 기준으로 URL 생성
                        $favicon_url = '/' . ltrim($favicon_path, '/');
                        
                        // 브라우저 캐시 방지를 위해 파일 수정 시간을 쿼리 파라미터로 추가
                        $file_path = $_SERVER['DOCUMENT_ROOT'] . $favicon_url;
                        if (file_exists($file_path)) {
                            $favicon_url .= '?v=' . filemtime($file_path);
                        }
                        
                        return $favicon_url;
                    }
                    return $favicon_path;
                }
            }
        } catch (Exception $e) {
            // DB 오류 시 fallback 사용
            error_log("favicon_url() DB error: " . $e->getMessage());
        }
        
        // fallback: 기본 파비콘 경로
        return '/' . $fallback_filename;
    }
}

if (!function_exists('format_date')) {
    /**
     * 날짜 포맷팅
     */
    function format_date($date, $format = 'Y-m-d H:i:s') {
        if (empty($date)) {
            return '';
        }
        
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('time_ago')) {
    /**
     * 상대적 시간 표시 (예: 1시간 전)
     */
    function time_ago($datetime) {
        if (empty($datetime)) {
            return '';
        }
        
        $time = time() - strtotime($datetime);
        
        if ($time < 60) {
            return '방금 전';
        } elseif ($time < 3600) {
            return floor($time/60) . '분 전';
        } elseif ($time < 86400) {
            return floor($time/3600) . '시간 전';
        } elseif ($time < 2592000) {
            return floor($time/86400) . '일 전';
        } elseif ($time < 31536000) {
            return floor($time/2592000) . '개월 전';
        } else {
            return floor($time/31536000) . '년 전';
        }
    }
}

if (!function_exists('truncate_text')) {
    /**
     * 텍스트 자르기
     */
    function truncate_text($text, $length = 100, $suffix = '...') {
        $text = strip_tags($text);
        
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
    }
}

if (!function_exists('format_number')) {
    /**
     * 숫자 포맷팅
     */
    function format_number($number, $decimals = 0) {
        return number_format($number, $decimals);
    }
}

if (!function_exists('format_file_size')) {
    /**
     * 파일 크기 포맷팅
     */
    function format_file_size($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('current_page')) {
    /**
     * 현재 페이지 경로
     */
    function current_page() {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
}

if (!function_exists('is_current_page')) {
    /**
     * 현재 페이지인지 확인
     */
    function is_current_page($path) {
        $currentPath = parse_url(current_page(), PHP_URL_PATH);
        return $currentPath === $path;
    }
}

if (!function_exists('redirect')) {
    /**
     * 리다이렉트
     */
    function redirect($url, $statusCode = 302) {
        if (!headers_sent()) {
            header("Location: $url", true, $statusCode);
            exit;
        }
    }
}

if (!function_exists('json_response')) {
    /**
     * JSON 응답
     */
    function json_response($data, $statusCode = 200) {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('get_meta_title')) {
    /**
     * 페이지 타이틀 생성
     */
    function get_meta_title($pageTitle = '') {
        $siteName = app_name();
        
        if (empty($pageTitle)) {
            return $siteName;
        }
        
        return $pageTitle . ' | ' . $siteName;
    }
}

if (!function_exists('format_bytes')) {
    /**
     * 파일 크기를 바이트 단위로 포맷팅 (format_file_size와 동일한 기능)
     */
    function format_bytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('process_content')) {
    /**
     * 게시글 내용 처리 - 이미지 URL 수정 및 컨텐츠 정리
     */
    function process_content($content) {
        if (empty($content)) {
            return '';
        }
        
        // 이미지 URL 수정 - 프로덕션 URL을 현재 환경에 맞게 변경
        $content = preg_replace_callback(
            '/<img([^>]*?)src=["\']([^"\']+)["\']([^>]*?)>/i',
            function($matches) {
                $beforeSrc = $matches[1];
                $imageSrc = $matches[2];
                $afterSrc = $matches[3];
                
                // fix_image_url 함수 사용
                $fixedSrc = fix_image_url($imageSrc);
                
                return '<img' . $beforeSrc . 'src="' . htmlspecialchars($fixedSrc, ENT_QUOTES) . '"' . $afterSrc . '>';
            },
            $content
        );
        
        // 기본적인 HTML 정리
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // 빈 단락 제거
        $content = preg_replace('/<p[^>]*>[\s&nbsp;]*<\/p>/i', '', $content);
        
        return $content;
    }
}

if (!function_exists('getThemeClass')) {
    /**
     * 테마 CSS 클래스 생성 (Natural Green 테마용)
     */
    function getThemeClass($type, $base = '', $variant = '') {
        // Natural Green 테마의 기본 클래스들
        $classes = [
            'border' => [
                '200' => 'border-gray-200',
                '300' => 'border-gray-300',
                'primary' => 'border-lime-500',
            ],
            'bg' => [
                'primary' => 'bg-lime-500',
                'secondary' => 'bg-green-500',
                'natural' => 'bg-natural-50',
            ],
            'text' => [
                'primary' => 'text-lime-600',
                'secondary' => 'text-green-600',
                'forest' => 'text-forest-700',
            ]
        ];
        
        if (isset($classes[$type][$variant])) {
            return $classes[$type][$variant];
        }
        
        // 기본값 반환
        return $base . ($variant ? '-' . $variant : '');
    }
}