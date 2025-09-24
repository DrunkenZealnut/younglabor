<?php
/**
 * 관리자 파일 업로드 처리기 - board_templates 보안 패턴 적용
 * CSRF 보호 + 강화된 파일 검증 + 안전한 저장
 */
require_once '../bootstrap.php';
require_once '../../includes/upload_helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // POST 요청 확인
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('잘못된 요청입니다.');
    }
    
    // CSRF 토큰 검증 (일반 POST에서는 폼 필드로 전송)
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        throw new Exception('유효하지 않은 요청입니다. (CSRF 토큰 오류)');
    }
    
    // 파일 확인
    if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
        $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
        if ($contentLength > 0) {
            $maxBytes = 10 * 1024 * 1024; // 10MB
            throw new Exception('서버 업로드 제한을 초과했습니다. 최대 ' . round($maxBytes / (1024 * 1024)) . 'MB 까지 업로드할 수 있습니다.');
        }
        throw new Exception('파일이 전송되지 않았습니다.');
    }
    
    $files = $_FILES['files'];
    
    // 테이블명 결정 (POST 파라미터 또는 기본값)
    $table_name = $_POST['table_name'] ?? 'admin_files';
    
    // 허용된 파일 확장자
    $allowed_extensions = ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'txt'];
    
    // 최대 파일 크기 (10MB)
    $max_size = 10 * 1024 * 1024;
    
    // 다중 파일 업로드 처리 - 최대 10개 제한
    $file_count = is_array($files['name']) ? count($files['name']) : 1;
    if ($file_count > 10) {
        throw new Exception('최대 10개 파일까지만 업로드할 수 있습니다.');
    }
    
    $uploaded_files = handleMultipleFileUpload($files, $table_name, $allowed_extensions, $max_size);
    
    // DB에 파일 정보 저장 (admin_files 테이블)
    if (!empty($uploaded_files)) {
        try {
            // 테이블이 없으면 생성
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_files (
                id INT AUTO_INCREMENT PRIMARY KEY,
                original_filename VARCHAR(255) NOT NULL,
                stored_filename VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size INT NOT NULL,
                file_type ENUM('DOCUMENT', 'IMAGE') NOT NULL,
                mime_type VARCHAR(100),
                description TEXT,
                category_id INT DEFAULT NULL,
                is_public TINYINT(1) DEFAULT 0,
                uploaded_by INT DEFAULT NULL,
                table_name VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active TINYINT(1) DEFAULT 1
            )");
            
            $stmt = $pdo->prepare("INSERT INTO admin_files (original_filename, stored_filename, file_path, file_size, file_type, mime_type, description, category_id, is_public, uploaded_by, table_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            $uploaded_by = $_SESSION['user_id'] ?? null;
            
            foreach ($uploaded_files as &$file_info) {
                // 파일 타입 결정
                $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $document_file_type = in_array($file_info['file_extension'], $image_extensions) ? 'IMAGE' : 'DOCUMENT';
                
                $stmt->execute([
                    $file_info['original_filename'],
                    $file_info['stored_filename'], 
                    $file_info['relative_path'],
                    $file_info['file_size'],
                    $document_file_type,
                    'application/octet-stream', // MIME 타입은 업로드 헬퍼에서 처리
                    $description,
                    $category_id,
                    $is_public,
                    $uploaded_by,
                    $table_name
                ]);
                
                $file_info['file_type'] = $document_file_type;
            }
            
        } catch (Exception $e) {
            // DB 저장 실패 시 업로드된 파일들 삭제
            foreach ($uploaded_files as $file_info) {
                if (file_exists($file_info['file_path'])) {
                    @unlink($file_info['file_path']);
                }
            }
            throw new Exception('파일 정보 저장에 실패했습니다: ' . $e->getMessage());
        }
    }
    
    if (empty($uploaded_files)) {
        throw new Exception('업로드된 파일이 없습니다.');
    }
    
    echo json_encode([
        'success' => true,
        'message' => count($uploaded_files) . '개 파일이 업로드되었습니다.',
        'uploaded_count' => count($uploaded_files),
        'files' => $uploaded_files,
        'table_name' => $table_name
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>