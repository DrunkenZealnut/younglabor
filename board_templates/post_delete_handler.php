<?php
/**
 * 게시판 템플릿 게시글 삭제 처리
 * 자유게시판과 자료실 통합 삭제 처리
 */

require_once '../config/server_setup.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../config/helpers.php';

// 한글 인코딩 설정
header('Content-Type: text/html; charset=utf-8');

// 오류 표시 활성화 (디버그용)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // 메서드 및 CSRF 검증
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo "<script>alert('잘못된 요청 방법입니다.'); history.back();</script>";
        exit;
    }
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        echo "<script>alert('유효하지 않은 요청입니다. (CSRF)'); history.back();</script>";
        exit;
    }

    // 현재 사용자 정보 가져오기 (두 가지 세션 구조 지원)
    $current_user = null;
    if (isset($_SESSION['user_id'])) {
        $current_user = [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'role' => $_SESSION['role'] ?? 'USER'
        ];
    } elseif (isset($_SESSION['id'])) {
        $current_user = [
            'user_id' => $_SESSION['id'],
            'username' => $_SESSION['username'] ?? '',
            'role' => $_SESSION['role'] ?? 'USER'
        ];
    }

    if (!$current_user) {
        echo "<script>alert('로그인이 필요합니다.'); history.back();</script>";
        exit;
    }

    // POST 전용: 필수 파라미터
    $post_id = $_POST['post_id'] ?? null;
    $board_type = $_POST['board_type'] ?? 'FREE';

    if (!$post_id) {
        echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
        exit;
    }

    // config/database.php의 PDO 연결 사용
    $sql = "SELECT bp.post_id, bp.title, bp.user_id, bp.author_name, bc.category_type
            FROM board_posts bp 
            LEFT JOIN board_categories bc ON bp.category_id = bc.category_id 
            WHERE bp.post_id = ? AND bp.is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        throw new Exception('존재하지 않는 게시글입니다.');
    }

    // 삭제 권한 확인
    $can_delete = false;
    
    // 작성자 본인이거나 관리자인 경우
    if ($current_user['role'] === 'ADMIN' || 
        $post['user_id'] == $current_user['user_id'] || 
        $post['author_name'] === $current_user['username']) {
        $can_delete = true;
    }

    if (!$can_delete) {
        throw new Exception('삭제 권한이 없습니다.');
    }

    // 트랜잭션 시작
    $pdo->beginTransaction();

    // 첨부파일 정보 조회 및 삭제
    $attachment_sql = "SELECT stored_name FROM board_attachments WHERE post_id = ?";
    $attachment_stmt = $pdo->prepare($attachment_sql);
    $attachment_stmt->execute([$post_id]);
    $attachments = $attachment_stmt->fetchAll();

    foreach ($attachments as $attachment) {
        $file_path = '../uploads/board_documents/' . $attachment['stored_name'];
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }

    // 첨부파일 DB 레코드 삭제
    $delete_attachment_sql = "DELETE FROM board_attachments WHERE post_id = ?";
    $delete_attachment_stmt = $pdo->prepare($delete_attachment_sql);
    $delete_attachment_stmt->execute([$post_id]);

    // 게시글 삭제 (실제 삭제 대신 is_active = 0으로 설정)
    $delete_post_sql = "UPDATE board_posts SET is_active = 0 WHERE post_id = ?";
    $delete_post_stmt = $pdo->prepare($delete_post_sql);
    $delete_post_stmt->execute([$post_id]);

    // 트랜잭션 커밋
    $pdo->commit();

    // 리다이렉트 URL 결정: 요청 파라미터 우선, 없으면 카테고리 기반 기본값
    $redirect_url = $_POST['redirect_url'] ?? null;
    if (!$redirect_url) {
        $redirect_url = '../boards/free_board.php';
        if ($post['category_type'] === 'LIBRARY') {
            $redirect_url = '../boards/library.php';
        }
    }
    if (!headers_sent()) {
        // 플래시 메시지(선택): 세션으로 성공 메시지를 전달할 수도 있음
        $_SESSION['flash_message'] = [ 'type' => 'success', 'message' => '게시글이 삭제되었습니다.' ];
        header('Location: ' . $redirect_url);
        exit;
    } else {
        echo "<script>window.location.href = '" . $redirect_url . "';</script>";
    }

} catch (Exception $e) {
    // 롤백 처리 (PDO가 정의된 경우에만)
    if (isset($pdo)) {
        $pdo->rollback();
    }
    
    error_log("게시글 삭제 오류: " . $e->getMessage());
    
    echo "<script>
        alert('게시글 삭제 중 오류가 발생했습니다: " . addslashes($e->getMessage()) . "');
        history.back();
    </script>";
} catch (PDOException $e) {
    // PDO 관련 오류 처리
    if (isset($pdo)) {
        $pdo->rollback();
    }
    
    error_log("데이터베이스 오류: " . $e->getMessage());
    
    echo "<script>
        alert('데이터베이스 오류가 발생했습니다.');
        history.back();
    </script>";
}

?> 