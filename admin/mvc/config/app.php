<?php
/**
 * 애플리케이션 설정 파일
 */

return [
    // 애플리케이션 기본 정보
    'app' => [
        'name' => '우리동네노동권찾기 관리자',
        'version' => '2.0.0',
        'environment' => 'development', // development, production
        'debug' => true,
        'timezone' => 'Asia/Seoul'
    ],
    
    // 데이터베이스 설정
    'database' => [
        'default' => 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => $_ENV['DB_DATABASE'] ?? ($_ENV['PROJECT_SLUG'] ?? 'hopec'),
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            ]
        ]
    ],
    
    // 보안 설정
    'security' => [
        'csrf_token_lifetime' => 3600, // 1시간
        'session_lifetime' => 7200,    // 2시간
        'max_login_attempts' => 5,
        'login_lockout_duration' => 900, // 15분
        'password_hash_algorithm' => PASSWORD_DEFAULT,
        'secure_cookies' => false, // HTTPS 환경에서는 true
        'same_site_cookies' => 'Strict'
    ],
    
    // 파일 업로드 설정
    'upload' => [
        'max_file_size' => 5 * 1024 * 1024, // 5MB
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'hwp', 'hwpx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx'],
        'upload_paths' => [
            'posts' => '/uploads/posts/',
            'documents' => '/uploads/board_documents/',
            'images' => '/uploads/editor_images/',
            'settings' => '/uploads/settings/'
        ]
    ],
    
    // 페이징 설정
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100
    ],
    
    // 캐시 설정
    'cache' => [
        'enabled' => true,
        'driver' => 'file', // file, redis, memcached
        'lifetime' => 3600,
        'path' => __DIR__ . '/../cache/'
    ],
    
    // 로깅 설정
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
        'path' => __DIR__ . '/../logs/',
        'max_files' => 30,
        'channels' => [
            'security' => 'security.log',
            'application' => 'application.log',
            'performance' => 'performance.log'
        ]
    ],
    
    // 이메일 설정
    'mail' => [
        'driver' => 'smtp', // smtp, sendmail, mail
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => '',
        'password' => '',
        'from' => [
            'address' => 'admin@younglabor.kr',
            'name' => '청년노동자인권센터'
        ]
    ],
    
    // 성능 설정
    'performance' => [
        'query_cache_enabled' => true,
        'template_cache_enabled' => true,
        'minify_html' => false, // 프로덕션에서 true
        'compress_response' => false, // 프로덕션에서 true
        'lazy_loading' => true
    ],
    
    // API 설정
    'api' => [
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'window' => 3600 // 1시간
        ],
        'cors' => [
            'enabled' => false,
            'allowed_origins' => ['https://hopec.org'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'allowed_headers' => ['Content-Type', 'Authorization']
        ]
    ],
    
    // 개발 도구 설정
    'development' => [
        'query_log' => true,
        'show_exceptions' => true,
        'debug_bar' => false
    ]
];