<?php
/**
 * 소식지 상세보기 페이지
 * hopec_newsletter 테이블 사용
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

// 게시글 ID 검증
$wr_id = (int)($_GET['wr_id'] ?? 0);
if ($wr_id <= 0) {
    header('Location: /community/newsletter.php');
    exit;
}

try {
    // 게시글 정보 조회 및 조회수 증가
    $newsletter_item = DatabaseManager::selectOne(
        "SELECT * FROM hopec_posts WHERE wr_id = :wr_id AND board_type = 'newsletter'",
        [':wr_id' => $wr_id]
    );
    
    if (!$newsletter_item) {
        header('Location: /community/newsletter.php');
        exit;
    }
    
    // 조회수 증가
    DatabaseManager::execute(
        "UPDATE hopec_posts SET wr_hit = wr_hit + 1 WHERE wr_id = :wr_id AND board_type = 'newsletter'",
        [':wr_id' => $wr_id]
    );
    
    // 첨부파일 조회 (있는 경우)
    $attachments = [];
    if (!empty($newsletter_item['wr_file'])) {
        $attachments = DatabaseManager::select(
            "SELECT * FROM hopec_post_files WHERE wr_id = :wr_id AND board_type = 'newsletter' ORDER BY bf_no",
            [':wr_id' => $wr_id]
        );
    }
    
} catch (Exception $e) {
    $pageTitle = '소식지 | ' . app_name();
    include_once __DIR__ . '/../includes/header.php';
    echo '<div class="max-w-7xl mx-auto px-4 py-8"><div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center text-red-600">';
    echo is_debug() ? h($e->getMessage()) : '게시글을 불러올 수 없습니다.';
    echo '</div></div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 페이지 메타 정보 설정
$pageTitle = h($newsletter_item['wr_subject']) . ' | 소식지 | ' . app_name();
$pageDescription = mb_substr(strip_tags($newsletter_item['wr_content']), 0, 150) . '...';
$currentSlug = 'community/newsletter';

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';

// 본문에서 이미지 추출
$images = [];
if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $newsletter_item['wr_content'], $matches)) {
    $images = array_map('fix_image_url', $matches[1]);
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
          <a href="/community/newsletter.php" class="hover:text-forest-600">소식지</a>
        </li>
        <li class="before:content-['>'] before:mx-2 text-forest-600 font-medium">상세보기</li>
      </ol>
    </nav>

    <!-- 게시글 내용 -->
    <article class="bg-white rounded-lg shadow-sm border <?= getThemeClass('border', 'border', '200') ?> overflow-hidden">
      <!-- 헤더 -->
      <div class="border-b bg-gray-50 px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900 mb-3"><?= h($newsletter_item['wr_subject']) ?></h1>
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between text-sm text-gray-600">
          <div class="flex items-center space-x-4">
            <span class="flex items-center">
              <i data-lucide="user" class="w-4 h-4 mr-1"></i>
              <?= h($newsletter_item['wr_name']) ?>
            </span>
            <span class="flex items-center">
              <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
              <?= date('Y년 m월 d일', strtotime($newsletter_item['wr_datetime'])) ?>
            </span>
            <span class="flex items-center">
              <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
              조회 <?= number_format($newsletter_item['wr_hit'] + 1) ?>
            </span>
          </div>
        </div>
      </div>
      
      <!-- 첨부파일 -->
      <?php if (!empty($attachments)): ?>
      <div class="border-b bg-blue-50 px-6 py-3">
        <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
          <i data-lucide="paperclip" class="w-4 h-4 mr-1"></i>
          첨부파일
        </h3>
        <ul class="space-y-1">
          <?php foreach ($attachments as $file): ?>
          <li>
            <a href="/download.php?board_type=newsletter&wr_id=<?= $wr_id ?>&bf_no=<?= $file['bf_no'] ?>" 
               class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
              <i data-lucide="download" class="w-3 h-3 mr-1"></i>
              <?= h($file['bf_source']) ?>
              <span class="text-gray-500 ml-1">(<?= format_bytes($file['bf_filesize']) ?>)</span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      
      <!-- 본문 -->
      <div class="px-6 py-8">
        <div class="prose prose-lg max-w-none">
          <?= process_content($newsletter_item['wr_content']) ?>
        </div>
      </div>
      
      <!-- 이미지 갤러리 (본문에 이미지가 있는 경우) -->
      <?php if (!empty($images) && count($images) > 1): ?>
      <div class="border-t px-6 py-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">이미지 갤러리</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php foreach ($images as $index => $image): ?>
          <div class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden cursor-pointer" 
               onclick="openLightbox(<?= $index ?>)">
            <img src="<?= h($image) ?>" 
                 alt="이미지 <?= $index + 1 ?>"
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
        <?php
        // 이전 글
        $prev_newsletter = DatabaseManager::selectOne(
            "SELECT wr_id, wr_subject FROM hopec_posts 
             WHERE board_type = 'newsletter' AND wr_is_comment = 0 AND wr_datetime < :datetime 
             ORDER BY wr_datetime DESC LIMIT 1",
            [':datetime' => $newsletter_item['wr_datetime']]
        );
        
        // 다음 글
        $next_newsletter = DatabaseManager::selectOne(
            "SELECT wr_id, wr_subject FROM hopec_posts 
             WHERE board_type = 'newsletter' AND wr_is_comment = 0 AND wr_datetime > :datetime 
             ORDER BY wr_datetime ASC LIMIT 1",
            [':datetime' => $newsletter_item['wr_datetime']]
        );
        ?>
        
        <?php if ($prev_newsletter): ?>
        <div class="border-b pb-3 mb-3">
          <div class="text-xs text-gray-500 mb-1">이전글</div>
          <a href="/community/newsletter_view.php?wr_id=<?= $prev_newsletter['wr_id'] ?>" 
             class="text-sm text-gray-700 hover:text-forest-600 line-clamp-1">
            <?= h($prev_newsletter['wr_subject']) ?>
          </a>
        </div>
        <?php endif; ?>
        
        <?php if ($next_newsletter): ?>
        <div class="pb-3">
          <div class="text-xs text-gray-500 mb-1">다음글</div>
          <a href="/community/newsletter_view.php?wr_id=<?= $next_newsletter['wr_id'] ?>" 
             class="text-sm text-gray-700 hover:text-forest-600 line-clamp-1">
            <?= h($next_newsletter['wr_subject']) ?>
          </a>
        </div>
        <?php endif; ?>
      </div>
      
      <!-- 버튼 그룹 -->
      <div class="flex items-center space-x-3">
        <a href="/community/newsletter.php" 
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
          <i data-lucide="list" class="w-4 h-4 mr-2"></i>목록
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

<!-- 라이트박스 모달 -->
<?php if (!empty($images)): ?>
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center p-4">
  <div class="relative max-w-5xl max-h-full">
    <button onclick="closeLightbox()" 
            class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
      <i data-lucide="x" class="w-8 h-8"></i>
    </button>
    
    <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
    
    <!-- 네비게이션 버튼 -->
    <button id="prev-btn" onclick="prevImage()" 
            class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 hidden">
      <i data-lucide="chevron-left" class="w-8 h-8"></i>
    </button>
    
    <button id="next-btn" onclick="nextImage()" 
            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 hidden">
      <i data-lucide="chevron-right" class="w-8 h-8"></i>
    </button>
    
    <!-- 이미지 카운터 -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm">
      <span id="current-image">1</span> / <span id="total-images"><?= count($images) ?></span>
    </div>
  </div>
</div>

<script>
const images = <?= json_encode($images) ?>;
let currentIndex = 0;

function openLightbox(index) {
  currentIndex = index;
  updateLightbox();
  document.getElementById('lightbox').classList.remove('hidden');
  document.getElementById('lightbox').classList.add('flex');
}

function closeLightbox() {
  document.getElementById('lightbox').classList.add('hidden');
  document.getElementById('lightbox').classList.remove('flex');
}

function prevImage() {
  if (currentIndex > 0) {
    currentIndex--;
    updateLightbox();
  }
}

function nextImage() {
  if (currentIndex < images.length - 1) {
    currentIndex++;
    updateLightbox();
  }
}

function updateLightbox() {
  const img = document.getElementById('lightbox-image');
  const currentSpan = document.getElementById('current-image');
  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  
  img.src = images[currentIndex];
  currentSpan.textContent = currentIndex + 1;
  
  prevBtn.classList.toggle('hidden', currentIndex === 0);
  nextBtn.classList.toggle('hidden', currentIndex === images.length - 1);
}

// ESC 키로 라이트박스 닫기
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeLightbox();
  } else if (e.key === 'ArrowLeft') {
    prevImage();
  } else if (e.key === 'ArrowRight') {
    nextImage();
  }
});
</script>
<?php endif; ?>

<script>
function shareUrl() {
  if (navigator.share) {
    navigator.share({
      title: <?= json_encode($newsletter_item['wr_subject']) ?>,
      url: window.location.href
    });
  } else {
    navigator.clipboard.writeText(window.location.href).then(() => {
      alert('링크가 복사되었습니다.');
    });
  }
}
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>


