<?php
/**
 * younglabor 통합 라이트박스 HTML 템플릿
 * gallery_view.php, nepal_view.php, newsletter_view.php 공용 템플릿
 * 
 * @version 1.0.0
 * @author younglabor Development Team
 * @description 재사용 가능한 라이트박스 모달 HTML 구조
 * 
 * 사용법:
 * <?php include __DIR__ . '/../includes/lightbox-template.php'; ?>
 */
?>

<!-- 라이트박스 모달 -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center p-4">
  <div class="relative max-w-5xl max-h-full">
    
    <!-- 닫기 버튼 -->
    <button onclick="closeLightbox()" 
            class="lightbox-close-btn absolute top-4 right-4 bg-red-600 hover:bg-red-700 text-white rounded-full w-10 h-10 shadow-lg transition-all duration-200 hover:scale-110 flex items-center justify-center text-xl font-bold z-10"
            aria-label="라이트박스 닫기">
      ×
    </button>
    
    <!-- 메인 이미지 -->
    <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
    
    <!-- 이전 버튼 -->
    <button id="prev-btn" 
            onclick="prevImage()" 
            class="lightbox-nav-btn absolute left-4 top-1/2 transform -translate-y-1/2 bg-forest-600 hover:bg-forest-700 text-white rounded-full w-12 h-12 shadow-xl transition-all duration-200 hover:scale-110 flex items-center justify-center text-2xl font-bold"
            aria-label="이전 이미지">
      ‹
    </button>
    
    <!-- 다음 버튼 -->
    <button id="next-btn" 
            onclick="nextImage()" 
            class="lightbox-nav-btn absolute right-4 top-1/2 transform -translate-y-1/2 bg-forest-600 hover:bg-forest-700 text-white rounded-full w-12 h-12 shadow-xl transition-all duration-200 hover:scale-110 flex items-center justify-center text-2xl font-bold"
            aria-label="다음 이미지">
      ›
    </button>
    
    <!-- 이미지 카운터 -->
    <div class="lightbox-counter absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-forest-600 bg-opacity-90 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg">
      <span id="current-image">1</span> / <span id="total-images">1</span>
    </div>
    
  </div>
</div>

<?php
// 라이트박스 필요 리소스 로드 체크
$lightbox_resources_loaded = $lightbox_resources_loaded ?? false;

if (!$lightbox_resources_loaded) {
    $lightbox_resources_loaded = true;
    
    // CSS 로드
    if (file_exists(__DIR__ . '/../css/lightbox.css')) {
        echo '<link rel="stylesheet" href="' . app_url('css/lightbox.css') . '?v=' . filemtime(__DIR__ . '/../css/lightbox.css') . '">' . PHP_EOL;
    }
    
    // JavaScript 로드
    if (file_exists(__DIR__ . '/../js/lightbox.js')) {
        echo '<script src="' . app_url('js/lightbox.js') . '?v=' . filemtime(__DIR__ . '/../js/lightbox.js') . '"></script>' . PHP_EOL;
    }
}
?>