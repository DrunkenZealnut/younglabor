<?php
/**
 * 게시판 템플릿 파일 업로드 처리
 * board_templates에서 모든 문서 파일 업로드를 통합 처리
 */

require_once '../config/server_setup.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 문서 업로드는 DB 연결이 필요하지 않으므로 database/include 로드를 생략하여 응답 앞 경고를 방지
require_once '../config/helpers.php';
require_once '../includes/upload_helpers.php';

// 헤더 설정
header('Content-Type: application/json; charset=utf-8');

try {
    // POST 요청 및 CSRF 확인
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('잘못된 요청입니다.');
    }
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!verifyCSRFToken($token)) {
        throw new Exception('유효하지 않은 요청입니다. (CSRF)');
    }

    // 파일 확인 (php.ini 제한에 걸린 경우 안내)
    if (!isset($_FILES['attachments']) || empty($_FILES['attachments']['name'][0])) {
        $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
        if ($contentLength > 0) {
            $maxBytes = function_exists('getMaxUploadSize') ? getMaxUploadSize() : (10 * 1024 * 1024);
            throw new Exception('서버 업로드 제한을 초과했습니다. 최대 ' . round($maxBytes / (1024 * 1024)) . 'MB 까지 업로드할 수 있습니다.');
        }
        throw new Exception('파일이 전송되지 않았습니다.');
    }

    $files = $_FILES['attachments'];
    
    // 테이블명 결정 (POST 파라미터 또는 기본값)
    $table_name = $_POST['table_name'] ?? 'hopec_posts';
    
    // 허용된 파일 확장자
    $allowed_extensions = ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx'];
    
    // 최대 파일 크기 (10MB)
    $max_size = 10 * 1024 * 1024;
    
    // 다중 파일 업로드 처리
    $uploaded_files = handleMultipleFileUpload($files, $table_name, $allowed_extensions, $max_size);

    if (empty($uploaded_files)) {
        throw new Exception('업로드된 파일이 없습니다.');
    }

    echo json_encode([
        'files' => $uploaded_files,
        'success' => true,
        'message' => count($uploaded_files) . '개 파일이 업로드되었습니다.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ], JSON_UNESCAPED_UNICODE);
}
?> 