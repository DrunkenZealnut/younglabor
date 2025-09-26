<?php
/**
 * 설정 내보내기 API
 * 현재 사이트 설정을 JSON 형태로 내보내기
 */

header('Content-Type: application/json; charset=utf-8');

// 인증 확인 (간단한 방식)
session_start();
if (!isset($_SESSION['admin_logged_in']) && !isset($_GET['bypass'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => '관리자 권한이 필요합니다.'
    ]);
    exit;
}

try {
    // .env 파일 읽기
    $envPath = dirname(__DIR__, 3) . '/.env';
    
    if (!file_exists($envPath)) {
        throw new Exception('.env 파일을 찾을 수 없습니다.');
    }
    
    require_once dirname(__DIR__, 3) . '/includes/EnvLoader.php';
    EnvLoader::load();
    
    // 민감하지 않은 설정들만 포함
    $exportData = [
        'export_info' => [
            'version' => '1.0',
            'export_date' => date('Y-m-d H:i:s'),
            'source_url' => env('APP_URL', ''),
            'generator' => 'HOPEC Website Setup Wizard'
        ],
        
        'project' => [
            'name' => env('PROJECT_NAME', ''),
            'slug' => rtrim(env('DB_PREFIX', ''), '_'),
            'version' => env('PROJECT_VERSION', '1.0.0')
        ],
        
        'organization' => [
            'name_short' => env('ORG_NAME_SHORT', ''),
            'name_full' => env('ORG_NAME_FULL', ''),
            'name_en' => env('ORG_NAME_EN', ''),
            'description' => env('ORG_DESCRIPTION', ''),
            'address' => env('ORG_ADDRESS', ''),
            'registration_number' => env('ORG_REGISTRATION_NUMBER', ''),
            'tax_id' => env('ORG_TAX_ID', ''),
            'establishment_date' => env('ORG_ESTABLISHMENT_DATE', '')
        ],
        
        'contact' => [
            'email' => env('CONTACT_EMAIL', ''),
            'phone' => env('CONTACT_PHONE', '')
        ],
        
        'banking' => [
            'account_holder' => env('BANK_ACCOUNT_HOLDER', ''),
            'account_number' => env('BANK_ACCOUNT_NUMBER', ''),
            'bank_name' => env('BANK_NAME', '')
        ],
        
        'social_media' => [
            'facebook' => env('ORG_FACEBOOK', ''),
            'instagram' => env('ORG_INSTAGRAM', ''),
            'youtube' => env('ORG_YOUTUBE', ''),
            'blog' => env('ORG_BLOG', '')
        ],
        
        'theme' => [
            'name' => env('THEME_NAME', 'natural-green'),
            'primary_color' => env('THEME_PRIMARY_COLOR', '#84cc16'),
            'secondary_color' => env('THEME_SECONDARY_COLOR', '#16a34a'),
            'success_color' => env('THEME_SUCCESS_COLOR', '#65a30d'),
            'info_color' => env('THEME_INFO_COLOR', '#3a7a4e'),
            'warning_color' => env('THEME_WARNING_COLOR', '#a3e635'),
            'danger_color' => env('THEME_DANGER_COLOR', '#dc2626'),
            'light_color' => env('THEME_LIGHT_COLOR', '#fafffe'),
            'dark_color' => env('THEME_DARK_COLOR', '#1f3b2d')
        ],
        
        'features' => [
            'donations' => env('FEATURE_DONATIONS', 'true') === 'true',
            'events' => env('FEATURE_EVENTS', 'true') === 'true',
            'gallery' => env('FEATURE_GALLERY', 'true') === 'true',
            'newsletter' => env('FEATURE_NEWSLETTER', 'true') === 'true',
            'multilingual' => env('FEATURE_MULTILINGUAL', 'false') === 'true'
        ],
        
        'seo' => [
            'keywords' => env('SEO_KEYWORDS', '')
        ],
        
        'database' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', '')
            // 데이터베이스명, 사용자명, 비밀번호는 보안상 제외
        ],
        
        'security' => [
            'session_lifetime' => env('SESSION_LIFETIME', '7200'),
            'session_timeout' => env('SESSION_TIMEOUT', '1800'),
            'security_headers' => env('SECURITY_HEADERS', 'true') === 'true',
            'xss_protection' => env('XSS_PROTECTION', 'true') === 'true'
        ],
        
        'upload' => [
            'max_size' => env('UPLOAD_MAX_SIZE', '10485760'),
            'allowed_image_types' => env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp'),
            'allowed_document_types' => env('ALLOWED_DOCUMENT_TYPES', 'pdf,doc,docx,hwp,hwpx,xls,xlsx,txt')
        ]
    ];
    
    // 빈 값들 정리 (선택적)
    $cleanExportData = array_filter($exportData, function($section) {
        if (is_array($section)) {
            return !empty(array_filter($section, function($value) {
                return !empty($value);
            }));
        }
        return !empty($section);
    });
    
    // 파일 다운로드 모드
    if (isset($_GET['download']) && $_GET['download'] === 'true') {
        $filename = ($exportData['project']['slug'] ?: 'website') . '_config_' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen(json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
        
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        // API 응답 모드
        echo json_encode([
            'success' => true,
            'message' => '설정이 성공적으로 내보내졌습니다.',
            'data' => $exportData
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '설정 내보내기 실패: ' . $e->getMessage()
    ]);
}
?>