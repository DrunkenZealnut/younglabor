<?php
// 언론보도 목록 페이지 (B32)
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

$where_clauses = ['wr_is_comment = 0'];
$bind_params = [];
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
        'SELECT COUNT(*) as total FROM hopec_press WHERE ' . $where_sql,
        $bind_params
    )['total'] ?? 0;
    $total_pages = (int)max(1, ceil($total_posts / $per_page));
} catch (Exception $e) {
    $total_posts = 0;
    $total_pages = 1;
}

try {
    $rows = DatabaseManager::select(
        'SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_hit FROM hopec_press WHERE ' . $where_sql . ' ORDER BY wr_datetime DESC LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset,
        $bind_params
    );
} catch (Exception $e) {
    $rows = [];
}

$attachmentCounts = [];
if (!empty($rows)) {
    try {
        $ids = array_map(fn($r)=>(int)$r['wr_id'], $rows);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $af_results = DatabaseManager::select(
            'SELECT wr_id, COUNT(*) AS cnt FROM hopec_board_files WHERE bo_table = ? AND wr_id IN (' . $placeholders . ') GROUP BY wr_id',
            array_merge(['B32'], $ids)
        );
        foreach ($af_results as $r) { 
            $attachmentCounts[(int)$r['wr_id']] = (int)$r['cnt']; 
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
    'board_title' => '언론보도',
    'board_description' => '언론에 보도된 ' . org_name_short() . '의 소식입니다.',
    'show_write_button' => false,
    'enable_search' => true,
    'detail_url' => app_url('community/press_view.php'),
    'list_url' => app_url('community/press.php'),
    'posts_per_page' => $per_page,
    'container_max_width_class' => 'max-w-7xl', // notices.php와 동일한 폭으로 설정
    'author_col_class' => 'w-28', // 작성자 컬럼 폭 설정
    'hide_board_header' => true, // 게시판 헤더 숨김 (중복 방지)
];

$pageTitle = '언론보도 | ' . app_name();

// Legacy mode only - CSS vars mode removed
$useCSSVars = false;

include __DIR__ . '/../includes/header.php';

$current_page = $page;
?>

<main id="main" role="main" class="flex-1">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- 페이지 헤더 -->
    <div class="mb-4">
      <p class="text-sm text-gray-500">Community</p>
      <?php if ($useCSSVars): ?>
        <h1 class="text-3xl md:text-4xl font-bold" style="<?= $styleManager->getStyleString(['color' => 'forest-600']) ?>">언론보도</h1>
      <?php else: ?>
        <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>">언론보도</h1>
      <?php endif; ?>
      <p class="text-gray-600 mt-2"><?= h($config['board_description']) ?></p>
    </div>
  </div>
  
  <?php include __DIR__ . '/../board_templates/board_list.php'; ?>
</main>

<?php include_once __DIR__ . '/../includes/footer.php';
?>


