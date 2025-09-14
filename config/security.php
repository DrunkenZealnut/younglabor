<?php
/**
 * 보안 설정
 * 
 * Admin 시스템의 검증된 보안 기능을 메인 사이트로 확장
 */

return [
    /*
    |--------------------------------------------------------------------------
    | 세션 보안 설정
    |--------------------------------------------------------------------------
    */
    'session' => [
        'driver' => $_ENV['SESSION_DRIVER'] ?? 'file',
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200), // 2시간
        'name' => $_ENV['SESSION_NAME'] ?? 'HOPEC_SESSION',
        'secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'http_only' => filter_var($_ENV['SESSION_HTTP_ONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'same_site' => $_ENV['SESSION_SAME_SITE'] ?? 'strict',
        
        // 세션 보안 강화
        'regenerate_id' => true,
        'ip_check' => true,
        'user_agent_check' => true,
        'inactivity_timeout' => 1800, // 30분 비활성 타임아웃
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF 보안 설정
    |--------------------------------------------------------------------------
    */
    'csrf' => [
        'enabled' => filter_var($_ENV['CSRF_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? 'hopec_csrf_token',
        'lifetime' => (int)($_ENV['CSRF_LIFETIME'] ?? 3600), // 1시간
        'regenerate_on_use' => false,
        'auto_include' => true, // 폼에 자동으로 토큰 추가
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS 보호 설정
    |--------------------------------------------------------------------------
    */
    'xss' => [
        'enabled' => filter_var($_ENV['XSS_PROTECTION'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'escape_output' => true,
        'filter_input' => true,
        'allowed_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'a', 'img', 'div', 'span', 'blockquote'
        ],
        'allowed_attributes' => [
            'href', 'src', 'alt', 'title', 'class', 'id'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SQL Injection 방지 설정
    |--------------------------------------------------------------------------
    */
    'sql_injection' => [
        'enabled' => true,
        'validate_table_names' => true,
        'validate_column_names' => true,
        'validate_order_direction' => true,
        'prepared_statements_only' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 보안 헤더 설정
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'enabled' => filter_var($_ENV['SECURITY_HEADERS'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'x_frame_options' => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'content_security_policy' => "default-src 'self' https: http: data: blob:; img-src 'self' https: http: data: blob:; style-src 'self' 'unsafe-inline' https: http:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http:; connect-src 'self' https: http:; font-src 'self' https: http: data:; frame-ancestors 'self';",
        'referrer_policy' => 'strict-origin-when-cross-origin',
    ],

    /*
    |--------------------------------------------------------------------------
    | 파일 업로드 보안 설정
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'max_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10MB
        'allowed_image_types' => explode(',', $_ENV['ALLOWED_IMAGE_TYPES'] ?? 'jpg,jpeg,png,gif,webp'),
        'allowed_document_types' => explode(',', $_ENV['ALLOWED_DOCUMENT_TYPES'] ?? 'pdf,doc,docx,hwp,hwpx,xls,xlsx'),
        'upload_path' => $_ENV['UPLOAD_PATH'] ?? 'uploads',
        'scan_virus' => false, // 바이러스 스캔 (추후 구현)
        'validate_content' => true, // 파일 내용 검증
        'quarantine_suspicious' => true, // 의심스러운 파일 격리
    ],

    /*
    |--------------------------------------------------------------------------
    | 비밀번호 정책
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => false,
        'require_lowercase' => false,
        'require_numbers' => true,
        'require_symbols' => false,
        'hash_algorithm' => PASSWORD_DEFAULT,
        'hash_options' => ['cost' => 12],
    ],

    /*
    |--------------------------------------------------------------------------
    | 보안 로깅 설정
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => true,
        'log_file' => 'logs/security.log',
        'events' => [
            'login_success',
            'login_failure',
            'session_hijacking_attempt',
            'csrf_token_mismatch',
            'xss_attempt',
            'sql_injection_attempt',
            'suspicious_file_upload',
            'admin_access',
        ],
        'include_ip' => true,
        'include_user_agent' => true,
        'include_request_data' => false, // 민감한 데이터 포함하지 않음
    ],

    /*
    |--------------------------------------------------------------------------
    | 접근 제한 설정
    |--------------------------------------------------------------------------
    */
    'access_control' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15분
        'admin_ip_whitelist' => [], // 관리자 IP 화이트리스트
        'blocked_user_agents' => [], // 차단할 User-Agent
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 1000,
        ],
    ],
];