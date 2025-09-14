<?php
/**
 * 게시판 템플릿 이미지 업로드 처리
 * board_templates에서 모든 이미지 업로드를 통합 처리
 */

// 의존성 주입 시스템 로드
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 서비스 컨테이너에서 설정 가져오기
$container = $GLOBALS['board_service_container'];
$configProvider = $container->get('config');
$fileConfig = $configProvider->getFileConfig();
$authConfig = $configProvider->getAuthConfig();

// 헤더 설정
header('Content-Type: application/json; charset=utf-8');

try {
    // POST 요청 및 CSRF 확인 (게스트도 CSRF가 유효하면 업로드 허용)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('잘못된 요청입니다.');
    }
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!verifyCSRFToken($token)) {
        throw new Exception('유효하지 않은 요청입니다. (CSRF)');
    }

    // 파일 확인 (php.ini 제한에 걸린 경우 대응)
    if (!isset($_FILES['file'])) {
        $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
        if ($contentLength > 0) {
            $maxBytes = function_exists('getMaxUploadSize') ? getMaxUploadSize() : (2 * 1024 * 1024);
            throw new Exception('서버 업로드 제한을 초과했습니다. 최대 ' . round($maxBytes / (1024 * 1024)) . 'MB 까지 업로드할 수 있습니다.');
        }
        throw new Exception('파일이 전송되지 않았습니다.');
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => '파일이 너무 큽니다.',
            UPLOAD_ERR_FORM_SIZE => '파일이 너무 큽니다.',
            UPLOAD_ERR_PARTIAL => '파일이 부분적으로만 업로드되었습니다.',
            UPLOAD_ERR_NO_FILE => '파일이 업로드되지 않았습니다.',
            UPLOAD_ERR_NO_TMP_DIR => '임시 폴더가 없습니다.',
            UPLOAD_ERR_CANT_WRITE => '디스크에 쓸 수 없습니다.',
            UPLOAD_ERR_EXTENSION => '확장에 의해 업로드가 중단되었습니다.'
        ];
        throw new Exception(($error_messages[$_FILES['file']['error']] ?? '파일 업로드 오류가 발생했습니다.') . ' (코드: ' . $_FILES['file']['error'] . ')');
    }

    $file = $_FILES['file'];

    // 파일 크기 검증 (최대 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('파일 크기는 10MB를 초과할 수 없습니다.');
    }

    // 실제 이미지 파일인지 확인 (getimagesize + MIME 확인)
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        throw new Exception('올바른 이미지 파일이 아닙니다.');
    }
    // fileinfo 확장 모듈이 있을 때만 MIME 추가 검증
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowed_mimes = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array($mimeType, $allowed_mimes, true)) {
            throw new Exception('지원하지 않는 이미지 형식입니다. (JPG, PNG, GIF, WebP만 가능)');
        }
    }

    // 허용된 이미지 형식 확인
    $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
    if (!in_array($image_info[2], $allowed_types)) {
        throw new Exception('지원하지 않는 이미지 형식입니다. (JPG, PNG, GIF, WebP만 가능)');
    }

    // 업로드 디렉토리 설정
    $base_dir = dirname(__DIR__);
    $upload_dir = $base_dir . '/uploads/editor_images/';

    // 디렉토리 생성 및 권한 설정
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('업로드 디렉토리를 생성할 수 없습니다.');
        }
    }

    // 디렉토리 쓰기 권한 확인
    if (!is_writable($upload_dir)) {
        @chmod($upload_dir, 0755);
        if (!is_writable($upload_dir)) {
            throw new Exception('업로드 디렉토리에 쓰기 권한이 없습니다.');
        }
    }

    // 안전한 파일명 생성
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (empty($file_extension)) {
        // 이미지 타입에 따른 확장자 설정
        $extensions = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png', 
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp'
        ];
        $file_extension = $extensions[$image_info[2]] ?? 'jpg';
    }

    $safe_filename = 'editor_' . date('YmdHis') . '_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $safe_filename;

    // 파일 이동
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('파일 저장에 실패했습니다.');
    }

    // 파일 권한 설정
    @chmod($file_path, 0644);

    // 웹 경로 반환 (상대 경로)
    $web_path = '../uploads/editor_images/' . $safe_filename;

    echo json_encode([
        'url' => $web_path,
        'success' => true
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ], JSON_UNESCAPED_UNICODE);
}
?> 