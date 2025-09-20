<?php
/**
 * 업로드 설정
 * 파일 업로드 관련 경로 및 설정을 중앙 집중 관리
 */

return [
    /*
    |--------------------------------------------------------------------------
    | 업로드 기본 설정
    |--------------------------------------------------------------------------
    */
    
    // 업로드 기본 경로 (프로젝트 루트 기준)
    'base_path' => $_ENV['UPLOAD_BASE_PATH'] ?? 'data',
    
    // 파일 저장 하위 경로 (기본 경로 하위)
    'file_sub_path' => $_ENV['UPLOAD_FILE_SUB_PATH'] ?? 'file',
    
    // 전체 업로드 경로: {base_path}/{file_sub_path}/{table_name}/
    // 예: data/file/hopec_posts/
    
    // 최대 파일 크기 (바이트)
    'max_file_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10MB
    
    // 허용된 파일 확장자
    'allowed_extensions' => [
        'documents' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'archives' => ['zip', 'rar', '7z']
    ],
    
    // 모든 허용된 확장자 (통합)
    'all_allowed_extensions' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'rar', '7z'],
    
    // 파일명 생성 패턴
    'filename_pattern' => '{table}_{timestamp}_{unique}.{ext}',
    
    /*
    |--------------------------------------------------------------------------
    | 테이블별 특별 설정
    |--------------------------------------------------------------------------
    */
    
    // 테이블별 허용 확장자 (없으면 기본 설정 사용)
    'table_extensions' => [
        'hopec_posts' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'],
        'hopec_library' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx'],
        'hopec_gallery' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'hopec_events' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
        'hopec_notices' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'],
        'admin_files' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'txt'],
        'hopec_editor_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    
    // 테이블별 최대 파일 크기 (바이트, 없으면 기본값 사용)
    'table_max_sizes' => [
        'hopec_gallery' => 20971520, // 20MB (이미지 갤러리)
        'hopec_editor_images' => 5242880, // 5MB (에디터 이미지)
        'admin_files' => 52428800, // 50MB (관리자 파일)
    ],
    
    /*
    |--------------------------------------------------------------------------
    | 보안 설정
    |--------------------------------------------------------------------------
    */
    
    // .htaccess 자동 생성 여부
    'create_htaccess' => true,
    
    // .htaccess 내용 템플릿
    'htaccess_content' => "# Prevent direct access to uploaded files\nOptions -Indexes\nOrder Allow,Deny\nAllow from all\n\n# Allow specific file types\n<FilesMatch \"\\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|hwp|hwpx)$\">\n    Allow from all\n</FilesMatch>\n",
    
    /*
    |--------------------------------------------------------------------------
    | 웹 접근 경로 설정
    |--------------------------------------------------------------------------
    */
    
    // 웹에서 접근할 수 있는 상대 경로 (document root 기준)
    'web_base_path' => $_ENV['UPLOAD_WEB_BASE_PATH'] ?? 'data',
    'web_file_sub_path' => $_ENV['UPLOAD_WEB_FILE_SUB_PATH'] ?? 'file',
    
    /*
    |--------------------------------------------------------------------------
    | 레거시 호환성 설정
    |--------------------------------------------------------------------------
    */
    
    // 기존 uploads 폴더와의 호환성 유지
    'legacy_support' => true,
    'legacy_base_path' => 'uploads',
    'legacy_path_mapping' => [
        'hopec_posts' => 'board_documents',
        'hopec_library' => 'board_documents', 
        'hopec_gallery' => 'gallery',
        'hopec_events' => 'events',
        'hopec_notices' => 'notices',
        'admin_files' => 'admin_files',
        'hopec_editor_images' => 'editor_images'
    ]
];
?>