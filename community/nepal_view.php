<?php
// 네팔나눔연대여행 상세 (B36) - 댓글 비활성화
require_once __DIR__ . '/../bootstrap/app.php';

// 데이터베이스 연결은 bootstrap에서 자동 처리됨

try {
    $postId = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    if ($postId <= 0) {
        $message = '잘못된 요청입니다.';
        $pageTitle = '네팔나눔연대여행 | ' . app_name();
        include __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../board_templates/error.php';
        include_once __DIR__ . '/../includes/footer.php';
        exit;
    }

    $row = DatabaseManager::selectOne(
        "SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_last, wr_hit, ca_name, wr_content, mb_id FROM hopec_posts WHERE wr_id = :id AND wr_is_comment = 0 AND board_type = :board_type",
        [':id' => $postId, ':board_type' => 'nepal_travel']
    );
    
    if (!$row) {
        $message = '게시글을 찾을 수 없습니다.';
        $pageTitle = '네팔나눔연대여행 | ' . app_name();
        include __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../board_templates/error.php';
        include_once __DIR__ . '/../includes/footer.php';
        exit;
    }

    // 첨부파일
    $attachments_data = DatabaseManager::select(
        'SELECT bf_no, bf_source, bf_file, bf_filesize, board_type as file_board_type 
         FROM hopec_post_files 
         WHERE wr_id = ? 
         ORDER BY bf_no ASC',
        [$postId]
    );
    
    $attachments = [];
    foreach ($attachments_data as $f) {
        $no = (int)$f['bf_no'];
        $attachments[] = [
            'bf_no'         => $no,
            'original_name' => (string)$f['bf_source'],
            'stored_name'   => (string)$f['bf_file'],
            'file_size'     => (int)$f['bf_filesize'],
        ];
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

    $config = [
        'list_url' => app_url('community/nepal.php'),
        'enable_comments' => true,
        // GNUBOARD 원댓글 폴백 소스 지정 (wr_parent=post_id)
        'gnuboard_bo_table' => 'nepal',
        'show_navigation_buttons' => true,  // 네비게이션 버튼 표시
    ];
} catch (Exception $e) {
    $message = '데이터베이스 오류가 발생했습니다.';
    $pageTitle = '네팔나눔연대여행 | ' . app_name();
    include __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../board_templates/error.php';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = '네팔나눔연대여행 상세 | ' . app_name();
include __DIR__ . '/../includes/header.php';

echo '<div class="page-spacing" style="padding-top:24px;padding-bottom:48px">';
include __DIR__ . '/../board_templates/post_detail.php';
echo '</div>';

include_once __DIR__ . '/../includes/footer.php';
?>


