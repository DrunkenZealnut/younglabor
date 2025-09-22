<?php
// 재정보고 목록 페이지 (B37)
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
$bind_params = [':board_type' => 'finance_reports'];
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
        'SELECT COUNT(*) as total FROM ' . get_table_name('posts') . ' WHERE ' . $where_sql,
        $bind_params
    )['total'] ?? 0;
    $total_pages = (int)max(1, ceil($total_posts / $per_page));
} catch (Exception $e) {
    $total_posts = 0;
    $total_pages = 1;
}

try {
    $rows = DatabaseManager::select(
        'SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_hit, wr_file FROM ' . get_table_name('posts') . ' WHERE ' . $where_sql . ' ORDER BY wr_datetime DESC LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset,
        $bind_params
    );
} catch (Exception $e) {
    $rows = [];
}

// 첫부파일 개수는 wr_file 필드 사용 (데이터 불일치 문제 해결)
$attachmentCounts = [];
foreach ($rows as $r) {
    $wr_id = (int)$r['wr_id'];
    $attachmentCounts[$wr_id] = (int)$r['wr_file'];
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
    'board_title' => '재정보고',
    'board_description' => '투명하고 건전한 재정 운영을 위해 정기적으로 재정 현황을 공개합니다.',
    'show_write_button' => false,
    'enable_search' => true,
    'detail_url' => app_url('about/finance_view.php'),
    'list_url' => app_url('about/finance.php'),
    'posts_per_page' => $per_page,
    'container_max_width_class' => 'max-w-7xl', // notices.php와 동일한 폭으로 설정
    'author_col_class' => 'w-28 hidden sm:table-cell', // 작성자 컬럼 폭 설정
];

$pageTitle = '재정보고 | ' . app_name();
include __DIR__ . '/../includes/header.php';

$current_page = $page;
?>

<main id="main" role="main" class="flex-1">
<?php include __DIR__ . '/../board_templates/board_list.php'; ?>
</main>

<?php include_once __DIR__ . '/../includes/footer.php';
?>