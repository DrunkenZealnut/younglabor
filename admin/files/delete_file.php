<?php
// /admin/files/delete_file.php - 파일 삭제 처리
require_once '../bootstrap.php';

// JSON 응답 설정
header('Content-Type: application/json; charset=utf-8');

// POST 요청만 처리
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.']);
    exit;
}

// JSON 데이터 읽기
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// CSRF 토큰 검증 (board_templates 보안 적용)
$csrf_token = $data['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다. (CSRF 토큰 오류)']);
    exit;
}

if (!isset($data['file_id']) || !is_numeric($data['file_id'])) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 파일 ID입니다.']);
    exit;
}

$file_id = (int)$data['file_id'];

try {
    // 파일 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM admin_files WHERE id = ? AND is_active = 1");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        echo json_encode(['success' => false, 'message' => '파일을 찾을 수 없습니다.']);
        exit;
    }
    
    // 파일 비활성화 (실제 삭제 대신)
    $update_stmt = $pdo->prepare("UPDATE admin_files SET is_active = 0 WHERE id = ?");
    $update_stmt->execute([$file_id]);
    
    // 실제 파일도 삭제 (선택사항)
    $file_path = '../../' . $file['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    echo json_encode(['success' => true, 'message' => '파일이 성공적으로 삭제되었습니다.']);
    
} catch (Exception $e) {
    error_log("File delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '파일 삭제 중 오류가 발생했습니다.']);
}
?>