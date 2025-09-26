<?php
// 문의사항 삭제 처리
require_once '../bootstrap.php';
require_once '../../includes/config_helpers.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// ID 파라미터 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = '잘못된 요청입니다.';
    header('Location: list.php');
    exit;
}

$inquiry_id = (int)$_GET['id'];

try {
    // 문의사항 존재 여부 확인
    $stmt = $pdo->prepare("SELECT id, name FROM " . get_table_name('inquiries') . " WHERE id = ?");
    $stmt->execute([$inquiry_id]);
    $inquiry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inquiry) {
        $_SESSION['error_message'] = '해당 문의사항을 찾을 수 없습니다.';
        header('Location: list.php');
        exit;
    }
    
    // 문의사항 삭제
    $stmt = $pdo->prepare("DELETE FROM " . get_table_name('inquiries') . " WHERE id = ?");
    $result = $stmt->execute([$inquiry_id]);
    
    if ($result) {
        $_SESSION['success_message'] = '문의사항이 성공적으로 삭제되었습니다.';
    } else {
        $_SESSION['error_message'] = '문의사항 삭제 중 오류가 발생했습니다.';
    }
    
} catch (PDOException $e) {
    error_log("문의사항 삭제 오류: " . $e->getMessage());
    $_SESSION['error_message'] = '데이터베이스 오류가 발생했습니다.';
}

// 목록 페이지로 리다이렉트
header('Location: list.php');
exit;
?>