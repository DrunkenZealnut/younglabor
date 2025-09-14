<?php
/**
 * 게시판 템플릿 파일 업로드 처리
 * board_templates에서 모든 문서 파일 업로드를 통합 처리
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
    $uploaded_files = [];
    
    // 허용된 파일 확장자
    $allowed_extensions = ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx'];
    
    // 업로드 디렉토리 설정
    $base_dir = dirname(__DIR__);
    $upload_dir = $base_dir . '/uploads/board_documents/';

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

    // 여러 파일 처리
    $finfo = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // 오류가 있는 파일은 건너뛰기
        }

        $original_filename = $files['name'][$i];
        $tmp_name = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];

        // 파일 크기 검증 (10MB)
        if ($file_size > 10 * 1024 * 1024) {
            throw new Exception($original_filename . '의 파일 크기가 10MB를 초과합니다.');
        }

        // 파일 확장자 검증
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception($original_filename . '은(는) 지원하지 않는 파일 형식입니다.');
        }
        // MIME 검증
        $detected_mime = $finfo ? $finfo->file($tmp_name) : null;
        $allowed_mimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        if ($detected_mime && !in_array($detected_mime, $allowed_mimes, true)) {
            // hwp/hwpx는 서버 MIME 식별이 어려워 확장자로 허용
            if (!in_array($file_extension, ['hwp', 'hwpx'], true)) {
                throw new Exception($original_filename . '은(는) 허용되지 않는 파일 유형입니다.');
            }
        }

        // 안전한 파일명 생성
        $safe_filename = 'board_' . date('YmdHis') . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $safe_filename;

        // 파일 이동
        if (!move_uploaded_file($tmp_name, $file_path)) {
            throw new Exception($original_filename . ' 파일 저장에 실패했습니다.');
        }

        // 파일 권한 설정
        @chmod($file_path, 0644);

        // 업로드된 파일 정보 저장
        $uploaded_files[] = [
            'original_filename' => $original_filename,
            'stored_filename' => $safe_filename,
            'file_path' => $file_path,
            'file_size' => $file_size,
            'file_extension' => $file_extension
        ];
    }

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