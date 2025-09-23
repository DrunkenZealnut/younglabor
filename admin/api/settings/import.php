<?php
/**
 * 설정 가져오기 API
 * JSON 형태의 설정 파일을 업로드하여 현재 사이트에 적용
 */

header('Content-Type: application/json; charset=utf-8');

// 인증 확인
session_start();
if (!isset($_SESSION['admin_logged_in']) && !isset($_GET['bypass'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => '관리자 권한이 필요합니다.'
    ]);
    exit;
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'POST 요청만 허용됩니다.'
    ]);
    exit;
}

try {
    $importData = null;
    $sourceType = '';
    
    // JSON 파일 업로드 처리
    if (isset($_FILES['config_file']) && $_FILES['config_file']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['config_file'];
        
        // 파일 타입 검증
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $uploadedFile['tmp_name']);
        finfo_close($fileInfo);
        
        if ($mimeType !== 'application/json' && $mimeType !== 'text/plain') {
            throw new Exception('JSON 파일만 업로드할 수 있습니다.');
        }
        
        $jsonContent = file_get_contents($uploadedFile['tmp_name']);
        $importData = json_decode($jsonContent, true);
        $sourceType = 'file';
        
    } elseif (isset($_POST['config_json'])) {
        // 직접 JSON 입력 처리
        $importData = json_decode($_POST['config_json'], true);
        $sourceType = 'text';
        
    } else {
        throw new Exception('설정 파일 또는 JSON 데이터가 제공되지 않았습니다.');
    }
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 형식이 올바르지 않습니다: ' . json_last_error_msg());
    }
    
    // 설정 데이터 유효성 검사
    if (!isset($importData['project']) || !isset($importData['organization'])) {
        throw new Exception('올바른 설정 파일 형식이 아닙니다.');
    }
    
    // .env 파일 경로
    $envPath = dirname(__DIR__, 3) . '/.env';
    $envExamplePath = dirname(__DIR__, 3) . '/.env.example';
    
    // 현재 .env 백업
    if (file_exists($envPath)) {
        $backupPath = dirname(__DIR__, 3) . '/.env.backup.' . date('Y-m-d-H-i-s');
        copy($envPath, $backupPath);
    }
    
    // .env.example을 기본 템플릿으로 사용
    if (file_exists($envExamplePath)) {
        $envContent = file_get_contents($envExamplePath);
    } else {
        throw new Exception('.env.example 파일을 찾을 수 없습니다.');
    }
    
    // 설정값 매핑 및 적용
    $mappings = [
        // 프로젝트 정보
        'PROJECT_NAME' => $importData['project']['name'] ?? '',
        'PROJECT_SLUG' => $importData['project']['slug'] ?? '',
        'PROJECT_VERSION' => $importData['project']['version'] ?? '1.0.0',
        
        // 조직 정보
        'ORG_NAME_SHORT' => $importData['organization']['name_short'] ?? '',
        'ORG_NAME_FULL' => $importData['organization']['name_full'] ?? '',
        'ORG_NAME_EN' => $importData['organization']['name_en'] ?? '',
        'ORG_DESCRIPTION' => $importData['organization']['description'] ?? '',
        'ORG_ADDRESS' => $importData['organization']['address'] ?? '',
        'ORG_REGISTRATION_NUMBER' => $importData['organization']['registration_number'] ?? '',
        'ORG_TAX_ID' => $importData['organization']['tax_id'] ?? '',
        'ORG_ESTABLISHMENT_DATE' => $importData['organization']['establishment_date'] ?? '',
        
        // 연락처
        'CONTACT_EMAIL' => $importData['contact']['email'] ?? '',
        'CONTACT_PHONE' => $importData['contact']['phone'] ?? '',
        
        // 은행 정보
        'BANK_ACCOUNT_HOLDER' => $importData['banking']['account_holder'] ?? '',
        'BANK_ACCOUNT_NUMBER' => $importData['banking']['account_number'] ?? '',
        'BANK_NAME' => $importData['banking']['bank_name'] ?? '',
        
        // 소셜 미디어
        'ORG_FACEBOOK' => $importData['social_media']['facebook'] ?? '',
        'ORG_INSTAGRAM' => $importData['social_media']['instagram'] ?? '',
        'ORG_YOUTUBE' => $importData['social_media']['youtube'] ?? '',
        'ORG_BLOG' => $importData['social_media']['blog'] ?? '',
        
        // 테마
        'THEME_NAME' => $importData['theme']['name'] ?? 'natural-green',
        'THEME_PRIMARY_COLOR' => $importData['theme']['primary_color'] ?? '#84cc16',
        'THEME_SECONDARY_COLOR' => $importData['theme']['secondary_color'] ?? '#16a34a',
        'THEME_SUCCESS_COLOR' => $importData['theme']['success_color'] ?? '#65a30d',
        'THEME_INFO_COLOR' => $importData['theme']['info_color'] ?? '#3a7a4e',
        'THEME_WARNING_COLOR' => $importData['theme']['warning_color'] ?? '#a3e635',
        'THEME_DANGER_COLOR' => $importData['theme']['danger_color'] ?? '#dc2626',
        'THEME_LIGHT_COLOR' => $importData['theme']['light_color'] ?? '#fafffe',
        'THEME_DARK_COLOR' => $importData['theme']['dark_color'] ?? '#1f3b2d',
        
        // 기능
        'FEATURE_DONATIONS' => isset($importData['features']['donations']) ? ($importData['features']['donations'] ? 'true' : 'false') : 'true',
        'FEATURE_EVENTS' => isset($importData['features']['events']) ? ($importData['features']['events'] ? 'true' : 'false') : 'true',
        'FEATURE_GALLERY' => isset($importData['features']['gallery']) ? ($importData['features']['gallery'] ? 'true' : 'false') : 'true',
        'FEATURE_NEWSLETTER' => isset($importData['features']['newsletter']) ? ($importData['features']['newsletter'] ? 'true' : 'false') : 'true',
        'FEATURE_MULTILINGUAL' => isset($importData['features']['multilingual']) ? ($importData['features']['multilingual'] ? 'true' : 'false') : 'false',
        
        // SEO
        'SEO_KEYWORDS' => $importData['seo']['keywords'] ?? 'nonprofit, organization, community',
        
        // 데이터베이스 (보안에 민감하지 않은 것들만)
        'DB_HOST' => $importData['database']['host'] ?? 'localhost',
        'DB_PORT' => $importData['database']['port'] ?? '3306',
        'DB_CHARSET' => $importData['database']['charset'] ?? 'utf8mb4',
        'DB_COLLATION' => $importData['database']['collation'] ?? 'utf8mb4_unicode_ci',
        'DB_PREFIX' => $importData['database']['prefix'] ?? '',
        
        // 보안
        'SESSION_LIFETIME' => $importData['security']['session_lifetime'] ?? '7200',
        'SESSION_TIMEOUT' => $importData['security']['session_timeout'] ?? '1800',
        'SECURITY_HEADERS' => isset($importData['security']['security_headers']) ? ($importData['security']['security_headers'] ? 'true' : 'false') : 'true',
        'XSS_PROTECTION' => isset($importData['security']['xss_protection']) ? ($importData['security']['xss_protection'] ? 'true' : 'false') : 'true',
        
        // 업로드
        'UPLOAD_MAX_SIZE' => $importData['upload']['max_size'] ?? '10485760',
        'ALLOWED_IMAGE_TYPES' => $importData['upload']['allowed_image_types'] ?? 'jpg,jpeg,png,gif,webp',
        'ALLOWED_DOCUMENT_TYPES' => $importData['upload']['allowed_document_types'] ?? 'pdf,doc,docx,hwp,hwpx,xls,xlsx,txt'
    ];
    
    // 동적 도메인 설정
    if (!empty($importData['project']['slug'])) {
        $mappings['PRODUCTION_DOMAIN'] = $importData['project']['slug'] . '.org';
        $mappings['PRODUCTION_URL'] = 'https://' . $importData['project']['slug'] . '.org';
    }
    
    // .env 파일 업데이트
    foreach ($mappings as $key => $value) {
        if ($value !== '' && $value !== null) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                // 키가 없으면 추가
                $envContent .= "\n{$replacement}";
            }
        }
    }
    
    // .env 파일 저장
    if (file_put_contents($envPath, $envContent) === false) {
        throw new Exception('.env 파일을 저장할 수 없습니다.');
    }
    
    // 가져온 항목 통계
    $importedCount = 0;
    $skippedCount = 0;
    
    foreach ($mappings as $key => $value) {
        if (!empty($value)) {
            $importedCount++;
        } else {
            $skippedCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => '설정이 성공적으로 가져와졌습니다.',
        'details' => [
            'source_type' => $sourceType,
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
            'backup_created' => isset($backupPath) ? basename($backupPath) : null,
            'source_info' => [
                'version' => $importData['export_info']['version'] ?? 'unknown',
                'export_date' => $importData['export_info']['export_date'] ?? 'unknown',
                'source_url' => $importData['export_info']['source_url'] ?? 'unknown'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '설정 가져오기 실패: ' . $e->getMessage()
    ]);
}
?>