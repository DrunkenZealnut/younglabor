<?php
/**
 * 게시글 삭제 처리 - CSRF 보호 적용
 */
require_once '../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // POST 요청 확인
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('잘못된 요청 방법입니다.');
    }
    
    // JSON 데이터 수신
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('잘못된 JSON 데이터입니다.');
    }
    
    // CSRF 토큰 검증
    $csrf_token = $data['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        throw new Exception('유효하지 않은 요청입니다. (CSRF 토큰 오류)');
    }
    
    // 게시글 ID 검증
    $post_id = $data['id'] ?? 0;
    if (!is_numeric($post_id) || $post_id <= 0) {
        throw new Exception('유효하지 않은 게시글 ID입니다.');
    }
    
    $post_id = (int)$post_id;

    // 게시글 정보 가져오기 (첨부파일 및 이미지 삭제를 위해)
    $stmt = $pdo->prepare("SELECT * FROM hopec_posts WHERE wr_id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        throw new Exception('해당 게시글을 찾을 수 없습니다.');
    }
    
    // 게시글 내용에서 이미지 파일 찾기
    preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $post['wr_content'], $matches);
    
    // 업로드된 이미지 삭제
    if (isset($matches[1]) && is_array($matches[1])) {
        foreach ($matches[1] as $image_src) {
            if (strpos($image_src, 'uploads/posts/') !== false) {
                $image_path = '../../' . $image_src;
                if (file_exists($image_path) && is_file($image_path)) {
                    unlink($image_path);
                }
            }
        }
    }
    
    // 썸네일 이미지 삭제
    if (!empty($post['thumbnail']) && file_exists('../../' . $post['thumbnail'])) {
        unlink('../../' . $post['thumbnail']);
    }
    
    // 게시글 첨부파일 삭제
    $stmt = $pdo->prepare("SELECT * FROM hopec_post_attachments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($attachments as $attachment) {
        if (file_exists('../../' . $attachment['file_path'])) {
            unlink('../../' . $attachment['file_path']);
        }
    }
    
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    // 첨부파일 삭제
    $stmt = $pdo->prepare("DELETE FROM hopec_post_attachments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    
    // 게시글 삭제
    $stmt = $pdo->prepare("DELETE FROM hopec_posts WHERE wr_id = ?");
    $stmt->execute([$post_id]);
    
    // 트랜잭션 완료
    $pdo->commit();
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'message' => '게시글이 성공적으로 삭제되었습니다.',
        'deleted_post' => $post['wr_subject']
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    // 오류 발생 시 롤백
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '게시글 삭제 중 데이터베이스 오류가 발생했습니다.'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 