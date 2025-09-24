<?php
/**
 * Application Configuration
 * 애플리케이션 전반 설정
 */

return [
    // 애플리케이션 정보
    'name' => env('APP_NAME', '청년노동자인권센터'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', false) === 'true',
    'url' => env('APP_URL', 'http://localhost:8080/younglabor'),
    
    // 기본 사이트 설정
    'defaults' => [
        'site_name' => env('DEFAULT_SITE_NAME', '청년노동자인권센터'),
        'site_description' => env('DEFAULT_SITE_DESCRIPTION', '청년노동자는 평등하다'),
        'admin_email' => env('DEFAULT_ADMIN_EMAIL', 'admin@younglabor.kr'),
    ],
    
    // 보안 설정
    'security' => [
        'session_lifetime' => env('SESSION_LIFETIME', 7200),
        'session_timeout' => env('SESSION_TIMEOUT', 1800),
        'csrf_token_lifetime' => env('CSRF_TOKEN_LIFETIME', 3600),
    ],
    
    // 파일 업로드 설정
    'upload' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 10485760), // 10MB
        'path' => env('UPLOAD_PATH', 'uploads/'),
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xlsx'],
    ],
    
    // 테마 설정
    'theme' => [
        'primary_color' => env('THEME_PRIMARY_COLOR', '#f2f4f8'),
        'secondary_color' => env('THEME_SECONDARY_COLOR', '#7493af'),
        'success_color' => env('THEME_SUCCESS_COLOR', '#32e290'),
        'info_color' => env('THEME_INFO_COLOR', '#2f6a46'),
        'warning_color' => env('THEME_WARNING_COLOR', '#ffc107'),
        'danger_color' => env('THEME_DANGER_COLOR', '#dc3545'),
        'light_color' => env('THEME_LIGHT_COLOR', '#f8f9fa'),
        'dark_color' => env('THEME_DARK_COLOR', '#212529'),
    ],
    
    // 로깅 설정
    'logging' => [
        'level' => env('LOG_LEVEL', 'info'),
        'path' => env('LOG_PATH', '../logs/'),
    ],
];