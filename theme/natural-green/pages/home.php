<?php
// 홈페이지 메인 페이지 - 모던 아키텍처 버전
?>


<main id="main" role="main" class="flex-1" style="background: transparent !important;">
  <!-- 히어로 섹션 - 테마 컴포넌트 -->
  <?php
  // 테마 Hero Slider 설정 로드
  $hero_config = include __DIR__ . '/../config/hero-config.php';
  
  // 페이지별 커스터마이징 (필요시)
  // $hero_config['slide_count'] = 6;  // 홈페이지만 6개 슬라이드
  // $hero_config['auto_play_interval'] = 5000;  // 홈페이지만 5초 간격
  
  // Hero Slider 컴포넌트 포함
  include __DIR__ . '/../components/hero-slider.php';
  ?>

  <!-- 최근 활동 보기 섹션 -->
  <section class="py-12" style="background: var(--background); min-height: 600px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-center text-forest-700 mb-4">
        <a href="<?= app_url('community/gallery.php') ?>" class="hover:text-lime-600 transition-colors cursor-pointer no-underline">최근 활동 보기</a>
      </h2>
      <p class="text-center text-gray-600 mb-8">현장의 따끈한 이야기들을 카드로 확인하세요</p>
      
      <div class="flex justify-end mb-4">
        <a href="<?= app_url('community/gallery.php') ?>" class="text-forest-600 hover:text-lime-600">
          더 보기 <i data-lucide="arrow-right" class="inline w-4 h-4"></i>
        </a>
      </div>
      
      <!-- 갤러리 카드 그리드 -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        // 갤러리에서 최신 게시물 조회
        try {
            global $pdo;
            $table_name = get_table_name('posts');
            $stmt = $pdo->prepare("SELECT wr_id as id, wr_subject as title, wr_content as content, wr_datetime as created_at FROM {$table_name} WHERE board_type = 'gallery' AND wr_is_comment = 0 ORDER BY wr_datetime DESC LIMIT 3");
            $stmt->execute();
            $galleryPosts = $stmt->fetchAll();
            
            foreach ($galleryPosts as $post) {
                // 본문에서 첫 번째 이미지 추출
                $imageUrl = '';
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $post['content'], $matches)) {
                    $imageUrl = fix_image_url($matches[1]);
                }
                
                // 기본 이미지 설정 (임시 주석처리 - 무한루프 방지)
                // if (empty($imageUrl)) {
                //     $imageUrl = '/theme/natural-green/assets/images/default-gallery.jpg';
                // }
                ?>
                <a href="<?= app_url('community/gallery_view.php?wr_id=' . $post['id']) ?>" class="block rounded-lg shadow-md border border-primary-light hover:border-primary overflow-hidden hover:shadow-lg transition-all duration-300 h-96 cursor-pointer" style="background: var(--card);">
                  <div class="relative overflow-hidden h-64 bg-gradient-to-br from-natural-100 to-natural-200">
                    <!-- 블러 배경 -->
                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                         alt="" 
                         class="absolute inset-0 w-full h-full object-cover filter blur-md scale-110 opacity-60">
                         <!-- onerror="this.src='/theme/natural-green/assets/images/default-gallery.jpg'" -->
                    <!-- 메인 이미지 -->
                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                         alt="<?= htmlspecialchars($post['title']) ?>" 
                         class="absolute inset-0 w-full h-full object-contain">
                         <!-- onerror="this.src='/theme/natural-green/assets/images/default-gallery.jpg'" -->
                  </div>
                  <div class="p-4 h-32 flex flex-col justify-between">
                    <h3 class="font-semibold text-title mb-2 text-base leading-tight">
                      <?= htmlspecialchars(truncate_text($post['title'], 60)) ?>
                    </h3>
                    <p class="text-sm text-gray-500 mt-auto">
                      <?= format_date($post['created_at'], 'Y년 m월 d일') ?>
                    </p>
                  </div>
                </a>
                <?php
            }
            
            // 게시물이 없는 경우
            if (empty($galleryPosts)) {
                for ($i = 0; $i < 3; $i++) {
                    ?>
                    <div class="rounded-lg shadow-md border border-primary-light overflow-hidden h-96" style="background: var(--card);">
                      <div class="bg-gradient-to-br from-natural-100 to-natural-200 h-64">
                        <div class="flex items-center justify-center h-full text-gray-400">
                          <i data-lucide="image" class="w-16 h-16"></i>
                        </div>
                      </div>
                      <div class="p-4 h-32 flex flex-col justify-between">
                        <h3 class="font-semibold text-gray-400 text-base">활동 준비 중</h3>
                        <p class="text-sm text-gray-400 mt-auto">곧 새로운 소식을 전해드리겠습니다</p>
                      </div>
                    </div>
                    <?php
                }
            }
        } catch (Exception $e) {
            // 오류 발생 시 기본 카드 표시
            for ($i = 0; $i < 3; $i++) {
                ?>
                <div class="rounded-lg shadow-md border border-primary-light overflow-hidden h-96" style="background: var(--card);">
                  <div class="bg-gradient-to-br from-natural-100 to-natural-200 h-64">
                    <div class="flex items-center justify-center h-full text-gray-400">
                      <i data-lucide="image" class="w-16 h-16"></i>
                    </div>
                  </div>
                  <div class="p-4 h-32 flex flex-col justify-between">
                    <h3 class="font-semibold text-gray-400 text-base">활동 준비 중</h3>
                    <p class="text-sm text-gray-400 mt-auto">곧 새로운 소식을 전해드리겠습니다</p>
                  </div>
                </div>
                <?php
            }
        }
        ?>
      </div>
    </div>
  </section>

  <!-- 공지사항 & 후원안내 섹션 -->
  <section class="py-8" style="background: var(--background); min-height: 400px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid md:grid-cols-2 gap-8">
        <!-- 공지사항 -->
        <div class="rounded-lg shadow-md border border-primary-light hover:border-primary p-6 transition-all duration-300" style="background: var(--card);">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-forest-700">공지사항</h3>
            <a href="<?= app_url('community/notices.php') ?>" class="text-sm text-forest-600 hover:text-lime-600 font-medium">
              더보기 <i data-lucide="plus" class="inline w-4 h-4"></i>
            </a>
          </div>
          
          <ul class="space-y-3">
            <?php
            try {
                $notices = DatabaseManager::select(
                    "SELECT wr_id as id, wr_subject as title, wr_datetime as created_at FROM " . get_table_name('posts') . " WHERE board_type = 'notices' AND wr_is_comment = 0 ORDER BY wr_datetime DESC LIMIT 5"
                );
                
                foreach ($notices as $notice) {
                    ?>
                    <li class="flex justify-between items-start py-1.5 border-b border-primary-light/30 last:border-b-0">
                      <a href="<?= app_url('community/notice_view.php?wr_id=' . $notice['id']) ?>" 
                         class="flex-1 text-gray-700 hover:text-forest-600 pr-4">
                        <?= htmlspecialchars($notice['title']) ?>
                      </a>
                      <span class="text-sm text-gray-500 whitespace-nowrap">
                        <?= date('m.d', strtotime($notice['created_at'])) ?>
                      </span>
                    </li>
                    <?php
                }
                
                if (empty($notices)) {
                    echo '<li class="text-gray-400 py-6 text-center">등록된 공지사항이 없습니다.</li>';
                }
            } catch (Exception $e) {
                echo '<li class="text-gray-400 py-6 text-center">공지사항을 불러올 수 없습니다.</li>';
            }
            ?>
          </ul>
        </div>

        <!-- 후원 안내 -->
        <div class="gradient-brand rounded-lg shadow-md p-6 text-white hover-lift transition-all duration-300">
          <h3 class="text-2xl font-bold mb-4">함께하는 희망씨</h3>
          <p class="mb-4">
            희망씨와 함께 더불어 사는 삶을 만들어가요.<br>
            여러분의 작은 관심이 큰 희망이 됩니다.
          </p>
          
          <div class="space-y-3">
            <a href="<?= app_url('donate/monthly.php') ?>" 
               class="block bg-white text-forest-700 rounded-lg px-4 py-3 text-center font-semibold hover:bg-natural-100 transition">
              정기후원 참여하기
            </a>
            <a href="<?= app_url('donate/one-time.php') ?>" 
               class="block bg-white/20 text-white rounded-lg px-4 py-3 text-center font-semibold hover:bg-white/30 transition">
              일시후원 참여하기
            </a>
          </div>
          
          <div class="mt-4 text-sm">
            <p class="font-semibold mb-1">후원계좌</p>
            <p>우리은행 1005-502-430760</p>
            <p>(예금주: 사단법인 희망씨)</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 소식지 섹션 -->
  <section class="py-12" style="background: var(--background); min-height: 600px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-center text-forest-700 mb-4">
        <a href="<?= app_url('community/newsletter.php') ?>" class="hover:text-lime-600 transition-colors cursor-pointer underline decoration-transparent hover:decoration-lime-600 hover:underline-offset-4">희망씨 소식지</a>
      </h2>
      <p class="text-center text-gray-600 mb-8">희망씨의 다양한 활동과 소식을 전해드립니다</p>
      
      <div class="flex justify-end mb-4">
        <a href="<?= app_url('community/newsletter.php') ?>" class="text-forest-600 hover:text-lime-600">
          더 보기 <i data-lucide="arrow-right" class="inline w-4 h-4"></i>
        </a>
      </div>
      
      <!-- 소식지 카드 그리드 -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        // 소식지에서 최신 게시물 조회
        try {
            $newsletterPosts = DatabaseManager::select(
                "SELECT wr_id as id, wr_subject as title, wr_content as content, wr_datetime as created_at FROM " . get_table_name('posts') . " WHERE board_type = 'newsletter' AND wr_is_comment = 0 ORDER BY wr_datetime DESC LIMIT 3"
            );
            
            foreach ($newsletterPosts as $post) {
                // 본문에서 첫 번째 이미지 추출
                $imageUrl = '';
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $post['content'], $matches)) {
                    $imageUrl = fix_image_url($matches[1]);
                }
                
                // 기본 이미지 설정 (임시 주석처리 - 무한루프 방지)
                // if (empty($imageUrl)) {
                //     $imageUrl = '/theme/natural-green/assets/images/default-newsletter.jpg';
                // }
                ?>
                <a href="<?= app_url('community/newsletter_view.php?wr_id=' . $post['id']) ?>" class="block rounded-lg shadow-md border border-primary-light hover:border-primary overflow-hidden hover:shadow-lg transition-all duration-300 h-96 cursor-pointer" style="background: var(--card);">
                  <div class="relative overflow-hidden h-64 bg-gradient-to-br from-natural-100 to-natural-200">
                    <!-- 블러 배경 -->
                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                         alt="" 
                         class="absolute inset-0 w-full h-full object-cover filter blur-md scale-110 opacity-60">
                         <!-- onerror="this.src='/theme/natural-green/assets/images/default-newsletter.jpg'" -->
                    <!-- 메인 이미지 -->
                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                         alt="<?= htmlspecialchars($post['title']) ?>" 
                         class="absolute inset-0 w-full h-full object-contain">
                         <!-- onerror="this.src='/theme/natural-green/assets/images/default-newsletter.jpg'" -->
                  </div>
                  <div class="p-4 h-32 flex flex-col justify-between">
                    <h3 class="font-semibold text-title mb-2 text-base leading-tight hover:text-lime-600 transition-colors">
                      <?= htmlspecialchars(truncate_text($post['title'], 50)) ?>
                    </h3>
                    <p class="text-sm text-gray-500 mt-auto">
                      <?= format_date($post['created_at'], 'Y년 m월 d일') ?>
                    </p>
                  </div>
                </a>
                <?php
            }
            
            // 게시물이 없는 경우
            if (empty($newsletterPosts)) {
                for ($i = 0; $i < 3; $i++) {
                    ?>
                    <div class="rounded-lg shadow-md border border-primary-light overflow-hidden h-96" style="background: var(--card);">
                      <div class="bg-gradient-to-br from-natural-100 to-natural-200 h-64">
                        <div class="flex items-center justify-center h-full text-gray-400">
                          <i data-lucide="newspaper" class="w-16 h-16"></i>
                        </div>
                      </div>
                      <div class="p-4 h-32 flex flex-col justify-between">
                        <h3 class="font-semibold text-gray-400 text-base">소식지 준비 중</h3>
                        <p class="text-sm text-gray-400 mt-auto">곧 새로운 소식지를 전해드리겠습니다</p>
                      </div>
                    </div>
                    <?php
                }
            }
        } catch (Exception $e) {
            // 오류 발생 시 기본 카드 표시
            for ($i = 0; $i < 3; $i++) {
                ?>
                <div class="rounded-lg shadow-md border border-primary-light overflow-hidden h-96" style="background: var(--card);">
                  <div class="bg-gradient-to-br from-natural-100 to-natural-200 h-64">
                    <div class="flex items-center justify-center h-full text-gray-400">
                      <i data-lucide="newspaper" class="w-16 h-16"></i>
                    </div>
                  </div>
                  <div class="p-4 h-32 flex flex-col justify-between">
                    <h3 class="font-semibold text-gray-400 text-base">소식지 준비 중</h3>
                    <p class="text-sm text-gray-400 mt-auto">곧 새로운 소식지를 전해드리겠습니다</p>
                  </div>
                </div>
                <?php
            }
        }
        ?>
      </div>
    </div>
  </section>

</main>

<!-- 반응형 CSS 로드 -->
<link rel="stylesheet" href="<?= app_url('theme/natural-green/assets/css/responsive-home.css') ?>">

<script>
// Lucide 아이콘 초기화
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide && window.lucide.createIcons) {
        window.lucide.createIcons();
    }
    
    // 히어로 슬라이더 초기화
    initHeroSlider();
});

function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slider .slide');
    const prevBtn = document.querySelector('.slider-btn.prev');
    const nextBtn = document.querySelector('.slider-btn.next');
    const indicators = document.querySelectorAll('.indicator');
    
    if (slides.length <= 1) return; // 슬라이드가 1개 이하면 기능 비활성화
    
    let currentSlide = 0;
    let autoSlideInterval;
    
    // 슬라이드 표시 함수
    function showSlide(index) {
        slides.forEach(slide => slide.style.display = 'none');
        indicators.forEach(indicator => indicator.classList.remove('bg-white'));
        indicators.forEach(indicator => indicator.classList.add('bg-white/50'));
        
        if (slides[index]) {
            slides[index].style.display = 'block';
        }
        if (indicators[index]) {
            indicators[index].classList.remove('bg-white/50');
            indicators[index].classList.add('bg-white');
        }
        
        currentSlide = index;
    }
    
    // 다음 슬라이드
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }
    
    // 이전 슬라이드
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }
    
    // 자동 슬라이드
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000); // 5초마다 자동 전환
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
        }
    }
    
    // 이벤트 리스너
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
        });
    }
    
    // 인디케이터 클릭
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            stopAutoSlide();
            showSlide(index);
            startAutoSlide();
        });
    });
    
    // 마우스 오버/아웃 시 자동 슬라이드 제어
    const slider = document.querySelector('.hero-slider');
    if (slider) {
        slider.addEventListener('mouseenter', stopAutoSlide);
        slider.addEventListener('mouseleave', startAutoSlide);
    }
    
    // 자동 슬라이드 시작
    startAutoSlide();
}
</script>