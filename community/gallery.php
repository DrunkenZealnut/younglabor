<?php
/**
 * 활동 갤러리 목록 페이지
 * hopec_posts 테이블 사용 (board_type = 'gallery')
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

// 페이지 메타 정보 설정
$pageTitle = '활동 갤러리 | ' . app_name();
$pageDescription = '희망씨의 다양한 활동 사진을 확인하세요.';
$currentSlug = 'community/gallery';

// 강력한 캐시 방지 헤더
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('ETag: "' . md5(time()) . '"');

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';

// 기본 페이지네이션 설정 (3x4 그리드)
$per_page = 12; // 3x4 레이아웃 (12개 게시글)
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// 검색 파라미터
$search_type = $_GET['search_type'] ?? 'all';
$search_keyword = trim($_GET['search'] ?? '');

try {
    // 변수 초기화
    $gallery_posts = [];
    $has_prev = false;
    $has_next = false;
    $next_cursor = null;
    $prev_cursor = null;
    
    // 검색 조건 설정
    $where_clauses = ['wr_is_comment = 0'];
    $bind_params = [];
    
    if (!empty($search_keyword)) {
        switch ($search_type) {
            case 'title':
                $where_clauses[] = 'wr_subject LIKE :keyword';
                $bind_params[':keyword'] = '%' . $search_keyword . '%';
                break;
            case 'content':
                $where_clauses[] = 'wr_content LIKE :keyword';
                $bind_params[':keyword'] = '%' . $search_keyword . '%';
                break;
            case 'author':
                $where_clauses[] = 'wr_name LIKE :keyword';
                $bind_params[':keyword'] = '%' . $search_keyword . '%';
                break;
            default:
                $where_clauses[] = '(wr_subject LIKE :keyword1 OR wr_content LIKE :keyword2 OR wr_name LIKE :keyword3)';
                $bind_params[':keyword1'] = '%' . $search_keyword . '%';
                $bind_params[':keyword2'] = '%' . $search_keyword . '%';
                $bind_params[':keyword3'] = '%' . $search_keyword . '%';
                break;
        }
    }
    
    // 총 게시글 수 조회
    $base_where = implode(' AND ', $where_clauses);
    $final_params = array_values($bind_params);
    
    $count_sql = "SELECT COUNT(*) as total FROM hopec_posts WHERE board_type = 'gallery' AND $base_where";
    $total_count = DatabaseManager::selectOne($count_sql, $final_params)['total'] ?? 0;
    $total_pages = ceil($total_count / $per_page);
    
    // 갤러리 목록 조회 (단순 쿼리 - 복잡한 GROUP BY 제거)
    $offset_safe = (int)$offset;
    $limit_safe = (int)$per_page; // 정확한 개수만 조회
    
    // 단순하고 직접적인 쿼리 (중복이 없다는 것을 확인했으므로)
    $list_sql = "SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, wr_hit
                 FROM hopec_posts 
                 WHERE board_type = 'gallery' AND $base_where
                 ORDER BY wr_datetime DESC
                 LIMIT $limit_safe OFFSET $offset_safe";
    
    $gallery_posts = DatabaseManager::select($list_sql, $final_params);
    
    // 페이지네이션 정보 (일반 방식)
    $has_prev = ($page > 1);
    $has_next = ($page < $total_pages);
    
    // 각 게시글의 대표 이미지 추출
    foreach ($gallery_posts as $index => $post) {
        $post['thumbnail'] = '';
        if (!empty($post['wr_content'])) {
            // 본문에서 첫 번째 이미지 추출
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $post['wr_content'], $matches)) {
                $post['thumbnail'] = fix_image_url($matches[1]);
            }
        }
        
        // 이미지가 없으면 기본 이미지 사용
        if (empty($post['thumbnail'])) {
            $post['thumbnail'] = '/theme/natural-green/images/placeholder-gallery.jpg';
        }
        
        // 본문 요약
        $post['summary'] = mb_substr(strip_tags($post['wr_content']), 0, 100) . '...';
        
        // 변경된 내용을 배열에 다시 저장
        $gallery_posts[$index] = $post;
    }
    
} catch (Exception $e) {
    $gallery_posts = [];
    $total_count = 0;
    $total_pages = 0;
    $has_prev = false;
    $has_next = false;
    $error_message = is_debug() ? $e->getMessage() : '데이터를 불러올 수 없습니다.';
}
?>

<main id="main" role="main" class="flex-1">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- 페이지 헤더 -->
    <div class="mb-8">
      <nav class="breadcrumb mb-4" aria-label="breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
          <li><a href="/" class="hover:text-forest-600">홈</a></li>
          <li class="before:content-['>'] before:mx-2">커뮤니티</li>
          <li class="before:content-['>'] before:mx-2 text-forest-600 font-medium">활동 갤러리</li>
        </ol>
      </nav>
      
      <h1 class="text-3xl font-bold text-forest-700 mb-2">활동 갤러리</h1>
      <p class="text-gray-600">희망씨의 다양한 활동 사진을 확인하세요.</p>
    </div>

    <!-- 검색 메뉴 -->
    <div class="flex justify-end mb-6">
      <?php include_once __DIR__ . '/../board_templates/search_menu.php'; ?>
    </div>

    <!-- 갤러리 그리드 -->
    <?php if (isset($error_message)): ?>
      <div class="bg-red-50 border border-error rounded-lg p-8 text-center text-red-600">
        <?= h($error_message) ?>
      </div>
    <?php elseif (empty($gallery_posts)): ?>
      <div class="bg-gray-50 border border-primary-light rounded-lg p-8 text-center text-gray-500">
        <?= !empty($search_keyword) ? '검색 결과가 없습니다.' : '등록된 갤러리가 없습니다.' ?>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($gallery_posts as $index => $post): ?>
        <article class="bg-white rounded-lg shadow-sm border border-primary-light hover:border-primary overflow-hidden hover:shadow-md transition-all duration-300">
          <a href="/community/gallery_view.php?wr_id=<?= $post['wr_id'] ?>" class="block">
            <!-- 이미지 (높이 증가 및 블러 배경 적용) -->
            <div class="relative h-56 bg-gray-100 overflow-hidden">
              <!-- 블러 배경 이미지 -->
              <img src="<?= h($post['thumbnail']) ?>" 
                   alt=""
                   class="absolute inset-0 w-full h-full object-cover blur-sm scale-110 opacity-60"
                   loading="lazy"
                   onerror="this.src='/theme/natural-green/images/placeholder-gallery.jpg'">
              
              <!-- 메인 이미지 -->
              <img src="<?= h($post['thumbnail']) ?>" 
                   alt="<?= h($post['wr_subject']) ?>"
                   class="relative w-full h-full object-contain z-10"
                   loading="lazy"
                   onerror="this.src='/theme/natural-green/images/placeholder-gallery.jpg'">
            </div>
            
            <!-- 콘텐츠 (패딩 및 간격 조정) -->
            <div class="p-3">
              <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2 leading-tight" title="ID: <?= $post['wr_id'] ?> | Index: <?= $index ?>">
                <?= h($post['wr_subject']) ?>
                <?php if (isset($_GET['debug'])): ?>
                  <br><small style="color: #666; font-size: 10px;">Index: <?= $index ?> | ID: <?= $post['wr_id'] ?> | DateTime: <?= $post['wr_datetime'] ?></small>
                <?php endif; ?>
              </h3>
              
              <p class="text-sm text-gray-600 mb-2 line-clamp-1 leading-snug">
                <?= h($post['summary']) ?>
              </p>
              
              <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center space-x-2">
                  <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <?= h($post['wr_name']) ?>
                  </span>
                  <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 6.928 5 10.5 5c3.571 0 6.768 2.943 8.042 7-.274 1.1-.64 2.124-1.084 3.042"></path>
                    </svg>
                    <?= number_format($post['wr_hit']) ?>
                  </span>
                </div>
                <span class="flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                  </svg>
                  <?= date('m.d', strtotime($post['wr_datetime'])) ?>
                </span>
              </div>
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
               class="inline-flex items-center justify-center w-8 h-8 text-sm text-gray-700 hover:bg-gray-100 rounded font-medium">
              «
            </a>
          <?php endif; ?>
          
          <!-- 이전 페이지 화살표 -->
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
               class="inline-flex items-center justify-center w-8 h-8 text-sm text-gray-700 hover:bg-gray-100 rounded font-medium">
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
              <span class="inline-flex items-center justify-center w-8 h-8 text-sm bg-forest-600 text-white rounded font-medium">
                <?= $i ?>
              </span>
            <?php else: ?>
              <a href="?page=<?= $i ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
                 class="inline-flex items-center justify-center w-8 h-8 text-sm text-gray-700 hover:bg-gray-100 rounded font-medium">
                <?= $i ?>
              </a>
            <?php endif; ?>
          <?php endfor; ?>
          
          <!-- 다음 페이지 화살표 -->
          <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
               class="inline-flex items-center justify-center w-8 h-8 text-sm text-gray-700 hover:bg-gray-100 rounded font-medium">
              ›
            </a>
          <?php endif; ?>
          
          <!-- 마지막 페이지 화살표 -->
          <?php if ($page < $total_pages - 2): ?>
            <a href="?page=<?= $total_pages ?><?= !empty($search_keyword) ? '&search_type=' . urlencode($search_type) . '&search=' . urlencode($search_keyword) : '' ?>" 
               class="inline-flex items-center justify-center w-8 h-8 text-sm text-gray-700 hover:bg-gray-100 rounded font-medium">
              »
            </a>
          <?php endif; ?>
        </div>
      </nav>
    </div>
    <?php endif; ?>

  </div>
</main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<style>
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