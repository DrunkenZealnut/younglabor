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
        'SELECT COUNT(*) as count FROM ' . get_table_name('posts') . ' WHERE ' . $where_sql,
        $bind_params
    );
    $total_posts = (int)$total_result['count'];
    $total_pages = (int)max(1, ceil($total_posts / $per_page));

    // 목록 조회 (콘텐츠 포함)
    $rows = DatabaseManager::select(
        'SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_hit, wr_content, wr_file, wr_is_notice FROM ' . get_table_name('posts') . ' WHERE ' . $where_sql . ' ORDER BY wr_is_notice DESC, wr_datetime DESC LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset,
        $bind_params
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
        $params = array_merge(['younglabor_nepal_travel'], $ids);
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
            'is_notice' => (int)($r['wr_is_notice'] ?? 0),
            'thumbnail_url' => extract_first_image_src($r['wr_content'] ?? ''),
            'summary' => mb_substr(strip_tags($r['wr_content'] ?? ''), 0, 100) . '...',
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

// Legacy mode only - CSS vars mode removed
$useCSSVars = false;

include __DIR__ . '/../includes/header.php';

$current_page = $page;
$search_type = $search_type;
$search_keyword = $search_keyword;
?>

<main id="main" role="main" class="flex-1">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- 페이지 헤더 -->
    <div class="mb-8">
      <p class="text-sm text-gray-500">Community</p>
      <?php if ($useCSSVars): ?>
        <h1 class="text-3xl md:text-4xl font-bold" style="<?= $styleManager->getStyleString(['color' => 'forest-600']) ?>">네팔나눔연대여행</h1>
      <?php else: ?>
        <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>">네팔나눔연대여행</h1>
      <?php endif; ?>
      <p class="text-gray-600 mt-2">네팔 나눔연대 여행 소식을 사진과 함께 전합니다.</p>
    </div>

    <!-- 검색 메뉴 -->
    <div class="flex justify-end mb-6">
      <?php include_once __DIR__ . '/../board_templates/search_menu.php'; ?>
    </div>

    <!-- 갤러리 그리드 -->
    <?php if (empty($posts)): ?>
      <div class="bg-gray-50 border border-primary-light rounded-lg p-8 text-center text-gray-500">
        <?= !empty($search_keyword) ? '검색 결과가 없습니다.' : '등록된 게시글이 없습니다.' ?>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($posts as $index => $post): ?>
        <article class="bg-white rounded-lg shadow-sm border border-primary-light hover:border-primary overflow-hidden hover:shadow-md transition-all duration-300"<?= $post['is_notice'] ? ' style="background-color: var(--primary);"' : '' ?>>
          <a href="<?= app_url('community/nepal_view.php?wr_id=' . $post['post_id']) ?>" class="block">
            <!-- 이미지 (높이 증가 및 블러 배경 적용) -->
            <div class="relative h-56 bg-gray-100 overflow-hidden">
              <?php if (!empty($post['thumbnail_url'])): ?>
                <!-- 블러 배경 이미지 -->
                <img src="<?= htmlspecialchars($post['thumbnail_url']) ?>" 
                     alt=""
                     class="absolute inset-0 w-full h-full object-cover blur-sm scale-110 opacity-60"
                     loading="lazy">
                
                <!-- 메인 이미지 -->
                <img src="<?= htmlspecialchars($post['thumbnail_url']) ?>" 
                     alt="<?= htmlspecialchars($post['title']) ?>"
                     class="relative w-full h-full object-contain z-10"
                     loading="lazy">
              <?php else: ?>
                <!-- 기본 이미지 -->
                <div class="w-full h-full bg-gradient-to-br from-natural-100 to-natural-200 flex items-center justify-center">
                  <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              <?php endif; ?>
              
              <!-- 공지사항 배지 -->
              <?php if ($post['is_notice']): ?>
                <div class="absolute top-2 left-2">
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-white text-gray-800 shadow-sm">
                    공지
                  </span>
                </div>
              <?php endif; ?>
            </div>
            
            <!-- 콘텐츠 (패딩 및 간격 조정) -->
            <div class="p-3">
              <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2 leading-tight">
                <?= htmlspecialchars($post['title']) ?>
              </h3>
              
              <p class="text-sm text-gray-600 mb-2 line-clamp-1 leading-snug">
                <?= htmlspecialchars($post['summary']) ?>
              </p>
              
              <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center space-x-2">
                  <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <?= htmlspecialchars($post['author_name']) ?>
                  </span>
                  <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 6.928 5 10.5 5c3.571 0 6.768 2.943 8.042 7-.274 1.1-.64 2.124-1.084 3.042"></path>
                    </svg>
                    <?= number_format($post['view_count']) ?>
                  </span>
                </div>
                <span class="flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                  </svg>
                  <?= date('m.d', strtotime($post['created_at'])) ?>
                </span>
              </div>
              
              <?php if ($post['attachment_count'] > 0): ?>
                <div class="mt-2 flex items-center text-xs text-gray-500">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                  </svg>
                  첨부파일 <?= $post['attachment_count'] ?>개
                </div>
              <?php endif; ?>
            </div>
          </a>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-8 flex justify-center">
      <nav aria-label="페이지 네비게이션">
        <div class="flex items-center space-x-1">
          <!-- 첫 페이지 화살표 -->
          <?php if ($page > 3): ?>
            <a href="?page=1<?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
               class="inline-flex items-center justify-center w-10 h-10 text-sm bg-forest-100 text-forest-700 hover:bg-forest-600 hover:text-white border border-forest-200 hover:border-forest-600 rounded-lg font-bold transition-all duration-200 text-lg">
              ≪
            </a>
          <?php endif; ?>
          
          <!-- 이전 페이지 화살표 -->
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
               class="inline-flex items-center justify-center w-10 h-10 text-sm bg-forest-100 text-forest-700 hover:bg-forest-600 hover:text-white border border-forest-200 hover:border-forest-600 rounded-lg font-bold transition-all duration-200 text-lg">
              ‹
            </a>
          <?php endif; ?>
          
          <!-- 페이지 번호들 (5개) -->
          <?php
          $start_page = max(1, $page - 2);
          $end_page = min($total_pages, $start_page + 4);
          
          // 끝 페이지가 5개 미만이면 시작 페이지 조정
          if ($end_page - $start_page < 4) {
              $start_page = max(1, $end_page - 4);
          }
          
          for ($i = $start_page; $i <= $end_page; $i++): ?>
            <?php if ($i == $page): ?>
              <span class="inline-flex items-center justify-center w-10 h-10 text-sm bg-forest-600 text-white border border-forest-600 rounded-lg font-bold shadow-lg">
                <?= $i ?>
              </span>
            <?php else: ?>
              <a href="?page=<?= $i ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
                 class="inline-flex items-center justify-center w-10 h-10 text-sm bg-forest-100 text-forest-700 hover:bg-forest-600 hover:text-white border border-forest-200 hover:border-forest-600 rounded-lg font-medium transition-all duration-200">
                <?= $i ?>
              </a>
            <?php endif; ?>
          <?php endfor; ?>
          
          <!-- 다음 페이지 화살표 -->
          <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
               class="inline-flex items-center justify-center w-10 h-10 text-sm bg-forest-100 text-forest-700 hover:bg-forest-600 hover:text-white border border-forest-200 hover:border-forest-600 rounded-lg font-bold transition-all duration-200 text-lg">
              ›
            </a>
          <?php endif; ?>
          
          <!-- 마지막 페이지 화살표 -->
          <?php if ($page < $total_pages - 2): ?>
            <a href="?page=<?= $total_pages ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
               class="inline-flex items-center justify-center w-10 h-10 text-sm bg-forest-100 text-forest-700 hover:bg-forest-600 hover:text-white border border-forest-200 hover:border-forest-600 rounded-lg font-bold transition-all duration-200 text-lg">
              ≫
            </a>
          <?php endif; ?>
        </div>
      </nav>
    </div>
    <?php endif; ?>

  </div>
</main>

<style>
/* 페이징 버튼 - 테마 색상 변수 사용 */
.pagination-btn {
  background-color: var(--natural-100);
  color: var(--forest-700);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  width: 2.5rem;
  height: 2.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  cursor: pointer;
}

.pagination-btn:hover {
  background-color: var(--forest-600);
  color: white;
  border-color: var(--forest-600);
  transform: scale(1.05);
}

.pagination-current {
  background-color: var(--forest-600) !important;
  color: white !important;
  border-color: var(--forest-600) !important;
  box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.2);
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php';
?>


