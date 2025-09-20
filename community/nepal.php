<?php
// 네팔나눔연대여행 게시판 (B36)
// - 카드형 목록, 첫 이미지 썸네일 자동 추출

require_once __DIR__ . '/../bootstrap/app.php';

// 데이터베이스 연결은 bootstrap에서 자동 처리됨

try {

// 페이지네이션/검색
$per_page = 12; // 카드형 1/2/4열 맞춤
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $per_page;

$search_type = isset($_GET['search_type']) ? strtolower(trim((string)$_GET['search_type'])) : 'all';
if (!in_array($search_type, ['all','title','content','author'], true)) { $search_type = 'all'; }
$search_keyword = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
if ($search_keyword !== '') { $search_keyword = mb_substr($search_keyword, 0, 100, 'UTF-8'); }

$where_clauses = ['wr_is_comment = 0', 'board_type = :board_type'];
$bind_params = [':board_type' => 'nepal_travel'];
if ($search_keyword !== '') {
    $kw = '%' . $search_keyword . '%';
    if ($search_type === 'title') { $where_clauses[] = 'wr_subject LIKE :kw'; $bind_params[':kw'] = $kw; }
    elseif ($search_type === 'content') { $where_clauses[] = 'wr_content LIKE :kw'; $bind_params[':kw'] = $kw; }
    elseif ($search_type === 'author') { $where_clauses[] = 'wr_name LIKE :kw'; $bind_params[':kw'] = $kw; }
    else { $where_clauses[] = '(wr_subject LIKE :kw1 OR wr_content LIKE :kw2 OR wr_name LIKE :kw3)'; $bind_params += [':kw1'=>$kw,':kw2'=>$kw,':kw3'=>$kw]; }
}

$where_sql = implode(' AND ', $where_clauses);

    // 총 개수
    $total_result = DatabaseManager::selectOne(
        'SELECT COUNT(*) as count FROM hopec_posts WHERE ' . $where_sql,
        $bind_params
    );
    $total_posts = (int)$total_result['count'];
    $total_pages = (int)max(1, ceil($total_posts / $per_page));

    // 목록 조회 (콘텐츠 포함)
    $list_params = array_merge($bind_params, [':limit' => $per_page, ':offset' => $offset]);
    $rows = DatabaseManager::select(
        'SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_hit, wr_content, wr_file FROM hopec_posts WHERE ' . $where_sql . ' ORDER BY wr_datetime DESC LIMIT :limit OFFSET :offset',
        $list_params
    );

// 첫 이미지 추출
if (!function_exists('extract_first_image_src')) {
    function extract_first_image_src($html) {
        $src = null;
        if (is_string($html) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
            $candidate = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            if (preg_match('#^(https?:)?//#i', $candidate) || str_starts_with($candidate, '/') || stripos($candidate, 'data:image') === 0) {
                $src = $candidate;
            }
        }
        return $src;
    }
}

    // 첨부파일 개수
    $attachmentCounts = [];
    if (!empty($rows)) {
        $ids = array_map(fn($r)=>(int)$r['wr_id'], $rows);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge(['hopec_nepal_travel'], $ids);
        // wr_file 필드 사용으로 변경
        foreach ($rows as $r) {
            $attachmentCounts[(int)$r['wr_id']] = (int)$r['wr_file'];
        }
    }

    // 템플릿 데이터
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
            'thumbnail_url' => extract_first_image_src($r['wr_content'] ?? ''),
        ];
    }

    // 설정: 카드형 4:3 비율
    $config = [
        'board_title' => '네팔나눔연대여행',
        'board_description' => '네팔 나눔연대 여행 소식을 사진과 함께 전합니다.',
        'show_write_button' => false,
        'enable_search' => true,
        'detail_url' => app_url('community/nepal_view.php'),
        'list_url' => app_url('community/nepal.php'),
        'posts_per_page' => $per_page,
        'view_mode' => 'card',
        'grid_cols_class' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
        'card_aspect_ratio' => '4/3',
        'search_position' => 'right',
    ];
} catch (Exception $e) {
    $message = '데이터베이스 오류가 발생했습니다.';
    $pageTitle = '네팔나눔연대여행 | ' . app_name();
    include __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../board_templates/error.php';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = '네팔나눔연대여행 | ' . app_name();
include __DIR__ . '/../includes/header.php';

$current_page = $page;
$search_type = $search_type;
$search_keyword = $search_keyword;
?>

<main id="main" role="main" class="flex-1">
<?php include __DIR__ . '/../board_templates/board_list.php'; ?>
</main>

<?php include_once __DIR__ . '/../includes/footer.php';
?>


