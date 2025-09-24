<?php
/**
 * 게시판 템플릿 이미지 업로드 처리
 * board_templates에서 모든 이미지 업로드를 통합 처리
 */

require_once '../config/server_setup.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 이미지 업로드에는 DB 연결이 필요하지 않으므로 데이터베이스 로드 제외 (불필요한 경고 출력 방지)
require_once '../config/helpers.php';
require_once '../includes/board_module.php';
require_once '../includes/upload_helpers.php';

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
    
    // 테이블명 결정 (POST 파라미터 또는 기본값)
    $table_name = $_POST['table_name'] ?? 'hopec_editor_images';

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

    // 허용된 이미지 확장자
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // 최대 파일 크기 (10MB)
    $max_size = 10 * 1024 * 1024;
    
    // 파일 업로드 처리
    $uploaded_file = handleFileUpload($file, $table_name, $allowed_extensions, $max_size);
    
    // 웹 경로 반환 (상대 경로)
    $web_path = '../' . $uploaded_file['relative_path'];

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