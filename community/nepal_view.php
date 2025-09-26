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
        "SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_last, wr_hit, ca_name, wr_content, mb_id FROM " . get_table_name('posts') . " WHERE wr_id = :id AND wr_is_comment = 0 AND board_type = :board_type",
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


    // 이전글/다음글 조회
    $prev_post = null;
    $next_post = null;
    
    try {
        // 이전 글 (현재 글보다 이전에 작성된 글 중 가장 최근 글)
        $prev_post = DatabaseManager::selectOne(
            "SELECT wr_id, wr_subject FROM " . get_table_name('posts') . " 
             WHERE board_type = 'nepal_travel' AND wr_is_comment = 0 AND wr_datetime < :datetime 
             ORDER BY wr_datetime DESC LIMIT 1",
            [':datetime' => $row['wr_datetime']]
        );
        
        // 다음 글 (현재 글보다 이후에 작성된 글 중 가장 오래된 글)
        $next_post = DatabaseManager::selectOne(
            "SELECT wr_id, wr_subject FROM " . get_table_name('posts') . " 
             WHERE board_type = 'nepal_travel' AND wr_is_comment = 0 AND wr_datetime > :datetime 
             ORDER BY wr_datetime ASC LIMIT 1",
            [':datetime' => $row['wr_datetime']]
        );
    } catch (Exception $e) {
        error_log('이전글/다음글 조회 오류 (nepal_view.php): ' . $e->getMessage());
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
        'is_notice'     => 0,
        'user_id'       => (string)($row['mb_id'] ?? ''),
        'prev_post'     => $prev_post,
        'next_post'     => $next_post,
    ];

    $config = [
        'list_url' => app_url('community/nepal.php'),
        'enable_comments' => false, // 댓글 기능 비활성화
        'show_navigation_buttons' => false, // 네비게이션 버튼 숨김 (하단에 직접 구현)
    ];
} catch (Exception $e) {
    $message = '데이터베이스 오류가 발생했습니다.';
    $pageTitle = '네팔나눔연대여행 | ' . app_name();
    include __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../board_templates/error.php';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 조회수 증가
DatabaseManager::execute(
    "UPDATE " . get_table_name('posts') . " SET wr_hit = wr_hit + 1 WHERE wr_id = :wr_id AND board_type = 'nepal_travel'",
    [':wr_id' => $postId]
);

// 페이지 메타 정보 설정
$pageTitle = htmlspecialchars($row['wr_subject']) . ' | 네팔나눔연대여행 | ' . app_name();
$pageDescription = mb_substr(strip_tags($row['wr_content']), 0, 150) . '...';
$currentSlug = 'community/nepal';

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';

// 본문에서 이미지 추출
$images = [];
if (preg_match_all('/<img[^>]+src=["\'](([^"\']++))["\'][^>]*>/i', $row['wr_content'], $matches)) {
    $images = $matches[1];
}
?>

<main id="main" role="main" class="flex-1">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- 브레드크럼 -->
    <nav class="breadcrumb mb-6" aria-label="breadcrumb">
      <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="/" class="hover:text-forest-600">홈</a></li>
        <li class="before:content-['>'] before:mx-2">커뮤니티</li>
        <li class="before:content-['>'] before:mx-2">
          <a href="<?= app_url('community/nepal.php') ?>" class="hover:text-forest-600">네팔나눔연대여행</a>
        </li>
        <li class="before:content-['>'] before:mx-2 text-forest-600 font-medium">상세보기</li>
      </ol>
    </nav>

    <!-- 게시글 내용 -->
    <article class="bg-white rounded-lg shadow-sm border border-primary-light hover:border-primary overflow-hidden transition-all duration-300">
      <!-- 헤더 -->
      <div class="border-b border-primary-light bg-gray-50 px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($row['wr_subject']) ?></h1>
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between text-sm text-gray-600">
          <div class="flex items-center space-x-4">
            <span class="flex items-center">
              <i data-lucide="user" class="w-4 h-4 mr-1"></i>
              <?= htmlspecialchars($row['wr_name']) ?>
            </span>
            <span class="flex items-center">
              <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
              <?= date('Y년 m월 d일', strtotime($row['wr_datetime'])) ?>
            </span>
            <span class="flex items-center">
              <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
              조회 <?= number_format($row['wr_hit'] + 1) ?>
            </span>
          </div>
        </div>
      </div>
      
      
      <!-- 본문 -->
      <div class="px-6 py-8">
        <div class="prose prose-lg max-w-none">
          <?= $row['wr_content'] ?>
        </div>
      </div>
      
      <!-- 이미지 갤러리 (본문에 이미지가 있는 경우) -->
      <?php if (!empty($images)): ?>
      <div class="border-t px-6 py-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">이미지 갤러리</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
          <?php foreach ($images as $index => $image): ?>
          <div class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden cursor-pointer" 
               onclick="openLightbox(<?= $index ?>)">
            <img src="<?= htmlspecialchars($image) ?>" 
                 alt="갤러리 이미지 <?= $index + 1 ?>"
                 class="w-full h-32 object-cover hover:scale-105 transition-transform"
                 loading="lazy">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </article>

    <!-- 하단 네비게이션 -->
    <div class="mt-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <!-- 이전/다음 글 네비게이션 -->
      <div class="flex-1">        
        <?php if ($prev_post): ?>
        <div class="border-b pb-3 mb-3">
          <div class="text-xs text-gray-500 mb-1">이전글</div>
          <a href="<?= app_url('community/nepal_view.php?wr_id=' . $prev_post['wr_id']) ?>" 
             class="text-sm text-gray-700 hover:text-forest-600 line-clamp-1">
            <?= htmlspecialchars($prev_post['wr_subject']) ?>
          </a>
        </div>
        <?php endif; ?>
        
        <?php if ($next_post): ?>
        <div class="pb-3">
          <div class="text-xs text-gray-500 mb-1">다음글</div>
          <a href="<?= app_url('community/nepal_view.php?wr_id=' . $next_post['wr_id']) ?>" 
             class="text-sm text-gray-700 hover:text-forest-600 line-clamp-1">
            <?= htmlspecialchars($next_post['wr_subject']) ?>
          </a>
        </div>
        <?php endif; ?>
      </div>
      
      <!-- 버튼 그룹 -->
      <div class="flex items-center space-x-3">
        <a href="<?= app_url('community/nepal.php') ?>" 
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
          <i data-lucide="grid-3x3" class="w-4 h-4 mr-2"></i>목록
        </a>
        
        <button onclick="window.print()" 
                class="px-4 py-2 bg-forest-100 text-forest-700 rounded-lg hover:bg-forest-200 transition-colors">
          <i data-lucide="printer" class="w-4 h-4 mr-2"></i>인쇄
        </button>
        
        <button onclick="shareUrl()" 
                class="px-4 py-2 bg-lime-100 text-lime-700 rounded-lg hover:bg-lime-200 transition-colors">
          <i data-lucide="share-2" class="w-4 h-4 mr-2"></i>공유
        </button>
      </div>
    </div>
  </div>
</main>

<!-- 통합 라이트박스 모달 -->
<?php if (!empty($images)): ?>
<?php include __DIR__ . '/../includes/lightbox-template.php'; ?>

<script>
// 통합 라이트박스 시스템 초기화
const images = <?= json_encode($images) ?>;
document.addEventListener('DOMContentLoaded', function() {
  // younglaborLightbox 인스턴스 생성
  inityounglaborLightbox(images, {
    enableKeyboard: true,
    enableNavigation: true,
    showCounter: true
  });
});
</script>
<?php endif; ?>

<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
function shareUrl() {
  if (navigator.share) {
    navigator.share({
      title: <?= json_encode($row['wr_subject']) ?>,
      url: window.location.href
    });
  } else {
    navigator.clipboard.writeText(window.location.href).then(() => {
      alert('링크가 복사되었습니다.');
    });
  }
}

// 파일 크기 포맷팅 함수
function formatFileSize(bytes) {
    if (bytes <= 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const exp = Math.floor(Math.log(bytes) / Math.log(1024));
    const size = bytes / Math.pow(1024, exp);
    return Math.round(size * 100) / 100 + ' ' + units[exp];
}
</script>

<?php
// 파일 크기 포맷팅 함수 (PHP)
function formatFileSize($bytes) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $exp = floor(log($bytes, 1024));
    $exp = max(0, min($exp, count($units) - 1));
    $size = $bytes / pow(1024, $exp);
    return round($size, 2) . ' ' . $units[$exp];
}

?>

<!-- 라이트박스 CSS는 lightbox-template.php에서 자동 로드됨 -->

<?php
include_once __DIR__ . '/../includes/footer.php';
?>


