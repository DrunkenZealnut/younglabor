<?php
// 공지사항 목록 페이지 (B31)
require_once __DIR__ . '/../bootstrap/app.php';

// 데이터베이스 연결은 bootstrap에서 자동 처리됨

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $per_page;

$search_type = isset($_GET['search_type']) ? strtolower(trim((string)$_GET['search_type'])) : 'all';
if (!in_array($search_type, ['all','title','content','author'], true)) { $search_type = 'all'; }
$search_keyword = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
if ($search_keyword !== '') { $search_keyword = mb_substr($search_keyword, 0, 100, 'UTF-8'); }

$where_clauses = ['wr_is_comment = 0', 'board_type = :board_type'];
$bind_params = [':board_type' => 'notices'];
if ($search_keyword !== '') {
    $kw = '%' . $search_keyword . '%';
    if ($search_type === 'title') { $where_clauses[] = 'wr_subject LIKE :kw'; $bind_params[':kw'] = $kw; }
    elseif ($search_type === 'content') { $where_clauses[] = 'wr_content LIKE :kw'; $bind_params[':kw'] = $kw; }
    elseif ($search_type === 'author') { $where_clauses[] = 'wr_name LIKE :kw'; $bind_params[':kw'] = $kw; }
    else { $where_clauses[] = '(wr_subject LIKE :kw1 OR wr_content LIKE :kw2 OR wr_name LIKE :kw3)'; $bind_params += [':kw1'=>$kw,':kw2'=>$kw,':kw3'=>$kw]; }
}

$where_sql = implode(' AND ', $where_clauses);

try {
    $total_posts = DatabaseManager::selectOne(
        'SELECT COUNT(*) as total FROM hopec_posts WHERE ' . $where_sql,
        $bind_params
    )['total'] ?? 0;
    $total_pages = (int)max(1, ceil($total_posts / $per_page));
} catch (Exception $e) {
    $total_posts = 0;
    $total_pages = 1;
}

try {
    $list_params = $bind_params;
    $list_params[':limit'] = $per_page;
    $list_params[':offset'] = $offset;
    
    $rows = DatabaseManager::select(
        'SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_hit, wr_file FROM hopec_posts WHERE ' . $where_sql . ' ORDER BY wr_datetime DESC LIMIT :limit OFFSET :offset',
        $list_params
    );
} catch (Exception $e) {
    $rows = [];
}

$attachmentCounts = [];
if (!empty($rows)) {
    try {
        $ids = array_map(fn($r)=>(int)$r['wr_id'], $rows);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        // wr_file 필드 사용으로 변경
        foreach ($rows as $r) {
            $attachmentCounts[(int)$r['wr_id']] = (int)$r['wr_file'];
        }
    } catch (Exception $e) {
        $attachmentCounts = [];
    }
}

$posts = [];
foreach ($rows as $r) {
    $wr_id = (int)$r['wr_id'];
    $posts[] = [
        'post_id' => $wr_id,
        'title' => (string)$r['wr_subject'],
        'author_name' => (string)$r['wr_name'],
        'created_at' => (string)$r['wr_datetime'],
        'view_count' => (int)$r['wr_hit'],
        'attachment_count' => (int)($attachmentCounts[$wr_id] ?? 0),
        'is_notice' => 0,
    ];
}

$config = [
    'board_title' => '공지사항',
    'board_description' => '희망씨의 새로운 소식과 중요한 공지사항을 확인하세요.',
    'show_write_button' => false,
    'enable_search' => true,
    'detail_url' => app_url('community/notice_view.php'),
    'list_url' => app_url('community/notices.php'),
    'posts_per_page' => $per_page,
    'container_max_width_class' => 'max-w-7xl', // 게시판 폭을 넓게 설정
    'author_col_class' => 'w-28', // 작성자 컬럼 폭 설정
];

$pageTitle = '공지사항 | ' . app_name();
include __DIR__ . '/../includes/header.php';

$current_page = $page;
?>

<main id="main" role="main" class="flex-1" style="padding-bottom: 100px;">
<?php include __DIR__ . '/../board_templates/board_list.php'; ?>
</main>

<?php include_once __DIR__ . '/../includes/footer.php';
?>