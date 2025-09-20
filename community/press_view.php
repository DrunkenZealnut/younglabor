<?php
// 언론보도 상세 페이지 (B32)
// - GNUBOARD 의존 최소화, DB 스키마 변경 없음, @board_templates 활용

require_once __DIR__ . '/../bootstrap/app.php';

// 데이터베이스 연결은 bootstrap에서 자동 처리됨

try {
    // DEBUG: URL 파라미터 확인
    if (function_exists('is_debug') && is_debug()) {
        error_log('DEBUG press_view.php - REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'NONE'));
        error_log('DEBUG press_view.php - GET params: ' . print_r($_GET, true));
        error_log('DEBUG press_view.php - Query String: ' . ($_SERVER['QUERY_STRING'] ?? 'NONE'));
    }
    
    // 파라미터 검증
    $postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($postId <= 0) {
        $message = '잘못된 요청입니다.' . (function_exists('is_debug') && is_debug() ? ' (받은 ID: ' . ($_GET['id'] ?? 'NONE') . ')' : '');
        $pageTitle = '언론보도 | ' . app_name();
        include __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../board_templates/error.php';
        include_once __DIR__ . '/../includes/footer.php';
        exit;
    }

    // 게시글 조회
    $row = DatabaseManager::selectOne(
        "SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_last, wr_hit, ca_name, wr_content, mb_id FROM hopec_press WHERE wr_id = :id AND wr_is_comment = 0",
        [':id' => $postId]
    );

    if (!$row) {
        $message = '게시글을 찾을 수 없습니다.';
        $pageTitle = '언론보도 | ' . app_name();
        include __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../board_templates/error.php';
        include_once __DIR__ . '/../includes/footer.php';
        exit;
    }

    // 첨부파일
    $attachments = [];
    try {
        if (function_exists('is_debug') && is_debug()) {
            error_log('DEBUG press_view.php - Searching attachments for postId: ' . $postId);
        }
        
        $attachments_data = DatabaseManager::select(
            'SELECT bf_no, bf_source, bf_file, bf_filesize FROM hopec_post_files WHERE board_type = ? AND wr_id = ? ORDER BY bf_no ASC',
            ['press', $postId]
        );
        
        if (function_exists('is_debug') && is_debug()) {
            error_log('DEBUG press_view.php - Found ' . count($attachments_data) . ' attachments: ' . print_r($attachments_data, true));
        }
        
        foreach ($attachments_data as $f) {
            $no = (int)$f['bf_no'];
            $attachments[] = [
                'bf_no'         => $no,
                'original_name' => (string)$f['bf_source'],
                'stored_name'   => (string)$f['bf_file'],
                'file_size'     => (int)$f['bf_filesize'],
            ];
        }
    } catch (Exception $e) {
        if (function_exists('is_debug') && is_debug()) {
            error_log('DEBUG press_view.php - Attachment query failed: ' . $e->getMessage());
        }
        // 첨부파일 조회 실패 시 빈 배열로 처리
        $attachments = [];
    }

    // 템플릿 데이터
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

    // 언론보도는 댓글 비활성화
    $config = [
        'list_url' => app_url('community/press.php'),
        'enable_comments' => false,
        'gnuboard_bo_table' => 'hopec_press',
    ];
} catch (Exception $e) {
    $message = '데이터베이스 오류가 발생했습니다.';
    $pageTitle = '언론보도 | ' . app_name();
    include __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../board_templates/error.php';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 출력
$pageTitle = '언론보도 상세 | ' . app_name();
include __DIR__ . '/../includes/header.php';

echo '<div class="page-spacing" style="padding-top:24px;padding-bottom:48px">';
include __DIR__ . '/../board_templates/post_detail.php';
echo '</div>';

include_once __DIR__ . '/../includes/footer.php';
?>


