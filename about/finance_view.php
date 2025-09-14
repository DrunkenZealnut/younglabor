<?php
/**
 * 재정보고 상세보기 페이지
 * hopec_posts 테이블 사용 (board_type = 'finance_reports')
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

// board_templates config 로드 (파일 경로 설정을 위해)
require_once __DIR__ . '/../board_templates/config.php';

// 게시글 ID 검증
$postId = (int)($_GET['id'] ?? $_GET['wr_id'] ?? 0);
if ($postId <= 0) {
    $message = '잘못된 요청입니다.';
    $pageTitle = '재정보고 | ' . app_name();
    include __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../board_templates/error.php';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

try {
    $row = DatabaseManager::selectOne(
        "SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_last, wr_hit, ca_name, wr_content, mb_id FROM hopec_posts WHERE wr_id = :id AND wr_is_comment = 0 AND board_type = :board_type",
        [':id' => $postId, ':board_type' => 'finance_reports']
    );
    
    if (!$row) {
        $message = '게시글을 찾을 수 없습니다.';
        $pageTitle = '재정보고 | ' . app_name();
        include __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../board_templates/error.php';
        include_once __DIR__ . '/../includes/footer.php';
        exit;
    }
} catch (Exception $e) {
    $message = is_debug() ? $e->getMessage() : '데이터베이스 오류가 발생했습니다.';
    $pageTitle = '재정보고 | ' . app_name();
    include __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../board_templates/error.php';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 첨부파일
$attachments = [];
try {
    $attachments_data = DatabaseManager::select(
        "SELECT 
            pf.bf_no,
            pf.bf_source as original_name,
            pf.bf_file as stored_name,
            pf.bf_filesize as file_size,
            pf.board_type as file_board_type,
            p.board_type as post_board_type
         FROM hopec_post_files pf
         INNER JOIN hopec_posts p ON pf.wr_id = p.wr_parent
         WHERE p.board_type = :post_board_type
            AND pf.board_type = :file_board_type
            AND p.wr_id = :post_id
         ORDER BY pf.bf_no ASC",
        [':post_id' => $postId, ':post_board_type' => 'finance_reports', ':file_board_type' => 'finance_reports']
    );
    
    foreach ($attachments_data as $f) {
        $attachments[] = [
            'bf_no'         => (int)$f['bf_no'],
            'original_name' => (string)$f['original_name'],
            'stored_name'   => (string)$f['stored_name'],
            'file_size'     => (int)$f['file_size'],
            'file_board_type' => (string)$f['file_board_type'],
        ];
    }
} catch (Exception $e) {
    // 첨부파일 조회 실패 시 빈 배열로 처리
    $attachments = [];
}

$post = [
    'post_id'       => (int)$row['wr_id'],
    'title'         => (string)$row['wr_subject'],
    'author_name'   => (string)$row['wr_name'],
    'created_at'    => (string)$row['wr_datetime'],
    'updated_at'    => (string)$row['wr_last'],
    'view_count'    => (int)$row['wr_hit'],
    'category_name' => (string)$row['ca_name'],
    'content'       => (string)$row['wr_content'],
    'attachments'   => $attachments,
    'is_notice'     => 0,
    'user_id'       => (string)($row['mb_id'] ?? ''),
];

$pageTitle = '재정보고 상세 | ' . app_name();
include __DIR__ . '/../includes/header.php';

$config = [
    'list_url' => '/about/finance.php',
    'enable_comments' => false,
    'gnuboard_bo_table' => 'hopec_finance_reports',
];

echo '<div class="page-spacing" style="padding-top:24px;padding-bottom:48px">';
include __DIR__ . '/../board_templates/post_detail.php';
echo '</div>';

include_once __DIR__ . '/../includes/footer.php';
?>
