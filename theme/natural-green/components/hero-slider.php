<?php
/**
 * Hero Slider Component for Natural Green Theme
 * 데이터베이스에서 활성화된 히어로 섹션을 표시하거나 기본 갤러리 슬라이더 표시
 */

// 환경변수 로더 포함
$envPath = dirname(__DIR__, 3) . '/bootstrap/env.php';
if (file_exists($envPath)) {
    require_once $envPath;
}

// 활성 히어로 섹션 확인
$activeHero = null;
$useCustomHero = false;

try {
    // 환경변수 기반 데이터베이스 연결
    $host = env('DB_HOST', 'localhost');
    $dbname = env('DB_DATABASE', 'hopec');
    $username = env('DB_USERNAME', 'root');
    $password = env('DB_PASSWORD', '');
    $charset = env('DB_CHARSET', 'utf8mb4');
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM hopec_hero_sections WHERE is_active = 1 LIMIT 1");
    $activeHero = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($activeHero && $activeHero['type'] !== 'default') {
        $useCustomHero = true;
    }
    
    // 활성 히어로의 설정이 있으면 hero_config에 병합
    if ($activeHero && $activeHero['config']) {
        $customConfig = json_decode($activeHero['config'], true);
        if (is_array($customConfig)) {
            $hero_config = array_merge(
                isset($hero_config) ? $hero_config : [],
                $customConfig
            );
        }
    }
} catch (Exception $e) {
    // 오류 발생시 기본 히어로 사용
    $useCustomHero = false;
}

// 커스텀 히어로를 사용하는 경우
if ($useCustomHero && $activeHero) {
    echo $activeHero['code'];
    return; // 커스텀 코드를 출력하고 종료
}

// 기본 히어로 슬라이더 사용
// hero-config가 로드되지 않았다면 기본값으로 로드
if (!isset($hero_config) || !is_array($hero_config)) {
    $hero_config = include __DIR__ . '/../config/hero-config.php';
}

// 테마 통합 설정 로드 (있는 경우)
$integrationPath = __DIR__ . '/../config/theme-integration.php';
if (file_exists($integrationPath) && !isset($GLOBALS['theme_config'])) {
    include_once $integrationPath;
}

// 기본 설정 병합
$defaultConfig = [
    'slide_count' => 5,
    'auto_play' => true,
    'auto_play_interval' => 8000, // 8초로 늘림 (팬닝 효과를 위해)
    'show_navigation' => false,
    'show_indicators' => true,
    'height' => '400px',
    'show_content_overlay' => true,
];

$hero_config = array_merge($defaultConfig, $hero_config);

// 데이터베이스 연결 확인 및 초기화
$heroSlides = [];
$errorMessage = '';

try {
    // DatabaseManager 사용하여 갤러리 이미지 가져오기
    if (class_exists('DatabaseManager')) {
        $postsTable = DatabaseManager::getTableName('posts');
        $rawSlides = DatabaseManager::select("
            SELECT 
                wr_id, 
                wr_subject as title, 
                wr_content as content, 
                wr_datetime as created_at,
                wr_file
            FROM {$postsTable}
            WHERE wr_is_comment = 0 AND board_type = 'gallery'
            ORDER BY wr_datetime DESC 
            LIMIT " . intval($hero_config['slide_count'])
        );
        
        // HTML에서 첫 번째 이미지 추출
        $heroSlides = [];
        foreach ($rawSlides as $slide) {
            $imageUrl = '';
            
            // HTML 콘텐츠에서 첫 번째 img 태그의 src 추출
            if (!empty($slide['content'])) {
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                
                // HTML을 UTF-8로 처리하기 위해 메타 태그 추가
                $html = '<?xml encoding="utf-8" ?><div>' . $slide['content'] . '</div>';
                $dom->loadHTML($html);
                
                $images = $dom->getElementsByTagName('img');
                if ($images->length > 0) {
                    $firstImg = $images->item(0);
                    $src = $firstImg->getAttribute('src');
                    
                    if (!empty($src)) {
                        // 외부 도메인 URL을 로컬 환경에 맞게 변환
                        if (preg_match('/^https?:\/\//', $src)) {
                            // 절대 URL인 경우 - 프로덕션 도메인을 로컬 주소로 변환
                            if (env('APP_ENV') !== 'production') {
                                $production_domain = env('PRODUCTION_DOMAIN', 'hopec.co.kr');
                                $pattern = '/^https?:\/\/(www\.)?' . preg_quote($production_domain, '/') . '/';
                                $src = preg_replace($pattern, env('APP_URL', 'http://localhost'), $src);
                            }
                            $imageUrl = $src;
                        } else {
                            // 상대 경로인 경우 절대 경로로 변환
                            $baseUrl = env('APP_ENV') === 'production' 
                                ? env('PRODUCTION_URL', 'https://' . env('PRODUCTION_DOMAIN', 'hopec.co.kr')) 
                                : env('APP_URL', 'http://localhost');
                            
                            if (strpos($src, '/') === 0) {
                                $imageUrl = $baseUrl . $src;
                            } else {
                                $imageUrl = $baseUrl . '/' . $src;
                            }
                        }
                    }
                }
                
                libxml_clear_errors();
            }
            
            // 슬라이드 데이터에 추출된 이미지 URL 추가
            $heroSlides[] = [
                'wr_id' => $slide['wr_id'],
                'title' => $slide['title'],
                'content' => $slide['content'],
                'created_at' => $slide['created_at'],
                'wr_file' => $slide['wr_file'],
                'image_file' => null, // 더 이상 사용하지 않음
                'extracted_image_url' => $imageUrl
            ];
        }
    } else {
        $errorMessage = "DatabaseManager 클래스를 사용할 수 없습니다.";
    }
    
    
} catch (Exception $e) {
    $heroSlides = [];
    $errorMessage = $e->getMessage();
}
?>

<section class="hero-section">
  <div class="relative w-full">
    <div class="relative overflow-hidden" style="border-radius: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
      <div class="hero-slider relative" style="height: <?= $hero_config['height'] ?>; min-height: 400px; border-radius: 1.5rem; overflow: hidden;">
        
        <?php if (!empty($heroSlides)): ?>
          <?php foreach ($heroSlides as $index => $slide): ?>
            <?php
            // 이미지 URL 사용 - HTML에서 추출된 URL 사용
            $imageUrl = $slide['extracted_image_url'] ?? '';
            
            // 기본 이미지 설정 - 희망씨 테마 컬러로 변경
            if (empty($imageUrl)) {
                $imageUrl = 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="500" viewBox="0 0 1200 500">
                      <defs>
                        <linearGradient id="hopecGrad' . $index . '" x1="0%" y1="0%" x2="100%" y2="100%">
                          <stop offset="0%" style="stop-color:#84cc16;stop-opacity:1" />
                          <stop offset="50%" style="stop-color:#22c55e;stop-opacity:1" />
                          <stop offset="100%" style="stop-color:#16a34a;stop-opacity:1" />
                        </linearGradient>
                      </defs>
                      <rect width="1200" height="500" fill="url(#hopecGrad' . $index . ')"/>
                      <text x="600" y="250" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white" opacity="0.8">' . env('ORG_NAME', '희망씨') . '</text>
                      <text x="600" y="300" font-family="Arial, sans-serif" font-size="24" text-anchor="middle" fill="white" opacity="0.6">' . env('ORG_FULL_NAME', '사단법인 희망씨') . '</text>
                    </svg>'
                );
            }
            
            $isActive = $index === 0;
            ?>
            
            <div class="slide <?= $isActive ? 'active' : '' ?>" 
                 data-slide-index="<?= $index ?>"
                 style="background-image: url('<?= htmlspecialchars($imageUrl) ?>') !important; background-size: cover !important; background-position: center 15% !important; background-repeat: no-repeat !important;">
              
                <!-- 투명한 오버레이 -->
                <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.4), rgba(0,0,0,0.2), rgba(0,0,0,0.1)); top: 0; left: 0; width: 100%; height: 100%; z-index: 1;"></div>
                
                <!-- 하단 고정 콘텐츠 -->
                <div class="absolute bottom-0 left-0 right-0" style="z-index: 2; background: linear-gradient(to top, rgba(0,0,0,0.6), rgba(0,0,0,0.3), transparent); padding: 3rem 2rem 2rem;">
                  <div class="text-center text-white max-w-4xl mx-auto">
                    <h1 style="font-size: 3.5rem; font-weight: bold; margin-bottom: 1.5rem; color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.7), 1px 1px 3px rgba(0,0,0,0.9); line-height: 1.2;">
                      <?= env('ORG_FULL_NAME', '사단법인 희망씨') ?>
                    </h1>
                    <p style="font-size: 1.8rem; margin-bottom: 1rem; color: white; text-shadow: 1px 1px 4px rgba(0,0,0,0.7), 0px 0px 2px rgba(0,0,0,0.9); opacity: 0.95; line-height: 1.5;">
                      이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여
                    </p>
                    <p style="font-size: 1.4rem; color: white; opacity: 0.9; text-shadow: 1px 1px 3px rgba(0,0,0,0.7), 0px 0px 2px rgba(0,0,0,0.9); line-height: 1.4;">
                      희망연대노동조합 조합원과 지역주민들이 함께 설립한 따뜻한 법인입니다
                    </p>
                  </div>
                </div>
            </div>
          <?php endforeach; ?>
          
          
          <?php if ($hero_config['show_indicators'] && count($heroSlides) > 1): ?>
          <!-- 인디케이터 -->
          <div class="hero-indicators absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
            <?php for ($i = 0; $i < count($heroSlides); $i++): ?>
            <button class="hero-indicator w-3 h-3 rounded-full transition-all duration-200" 
                    style="background-color: <?= $i === 0 ? 'rgba(255, 255, 255, 1)' : 'rgba(255, 255, 255, 0.5)' ?>;"
                    data-slide="<?= $i ?>" 
                    aria-label="슬라이드 <?= $i + 1 ?>로 이동"></button>
            <?php endfor; ?>
          </div>
          <?php endif; ?>
          
        <?php else: ?>
          <!-- 기본 슬라이드 (갤러리 게시물이 없는 경우) -->
          <div class="slide active default-slide" style="height: 100%; background: linear-gradient(135deg, #84cc16 0%, #22c55e 100%);">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.1); z-index: 1;"></div>
            <div class="absolute bottom-0 left-0 right-0" style="z-index: 2; background: linear-gradient(to top, rgba(0,0,0,0.5), rgba(0,0,0,0.2), transparent); padding: 3rem 2rem 2rem;">
              <div class="text-center text-white max-w-4xl mx-auto">
                <h1 style="font-size: 3.5rem; font-weight: bold; margin-bottom: 1.5rem; color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.7), 1px 1px 3px rgba(0,0,0,0.9); line-height: 1.2;">
                  <?= env('ORG_FULL_NAME', '사단법인 희망씨') ?>
                </h1>
                <p style="font-size: 1.8rem; margin-bottom: 1rem; color: white; text-shadow: 1px 1px 4px rgba(0,0,0,0.7), 0px 0px 2px rgba(0,0,0,0.9); opacity: 0.95; line-height: 1.5;">
                  이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여
                </p>
                <p style="font-size: 1.4rem; color: white; opacity: 0.9; text-shadow: 1px 1px 3px rgba(0,0,0,0.7), 0px 0px 2px rgba(0,0,0,0.9); line-height: 1.4;">
                  희망연대노동조합 조합원과 지역주민들이 함께 설립한 따뜻한 법인입니다
                </p>
              </div>
            </div>
          </div>
        <?php endif; ?>
        
      </div>
    </div>
  </div>
</section>

<?php if (!empty($heroSlides) && count($heroSlides) > 1): ?>
<script>
(function() {
    'use strict';
    
    // Hero Slider 설정
    const config = {
        autoPlay: <?= json_encode($hero_config['auto_play']) ?>,
        autoPlayInterval: <?= $hero_config['auto_play_interval'] * 2 ?>, // 슬라이드 시간을 2배로 늘림
        showNavigation: <?= json_encode($hero_config['show_navigation']) ?>,
        showIndicators: <?= json_encode($hero_config['show_indicators']) ?>,
        enablePanning: true, // 팬닝 효과 활성화
        panDuration: <?= $hero_config['auto_play_interval'] * 1.8 ?> // 팬닝 애니메이션 시간
    };
    
    let currentSlide = 0;
    let autoPlayTimer = null;
    let panningTimer = null;
    const slides = document.querySelectorAll('.hero-slider .slide');
    const indicators = document.querySelectorAll('.hero-indicator');
    const slider = document.querySelector('.hero-slider');
    
    if (slides.length <= 1) return;
    
    // 팬닝 효과 함수
    function startPanning(slideElement) {
        if (!config.enablePanning) return;
        
        clearTimeout(panningTimer);
        
        // 이미지가 실제로 커버 모드에서 잘리는지 확인
        const img = new Image();
        const bgImage = slideElement.style.backgroundImage;
        if (!bgImage || bgImage === 'none') return;
        
        // URL 추출
        const imageUrl = bgImage.replace(/^url\(['"]?(.*?)['"]?\)$/, '$1');
        
        img.onload = function() {
            const slideWidth = slideElement.offsetWidth;
            const slideHeight = slideElement.offsetHeight;
            const imgRatio = this.naturalWidth / this.naturalHeight;
            const slideRatio = slideWidth / slideHeight;
            
            // 이미지가 잘릴 때만 팬닝 효과 적용
            if (imgRatio > slideRatio) {
                // 이미지가 가로로 더 길어서 좌우가 잘림 - 좌우 팬닝
                const positions = ['left center', 'center center', 'right center'];
                let posIndex = 0;
                
                function updatePosition() {
                    slideElement.style.setProperty('background-position', positions[posIndex], 'important');
                    slideElement.style.setProperty('transition', 'background-position 3s ease-in-out', 'important');
                    posIndex = (posIndex + 1) % positions.length;
                }
                
                // 즉시 시작
                updatePosition();
                
                // 3초마다 위치 변경
                panningTimer = setInterval(updatePosition, 3000);
                
            } else if (imgRatio < slideRatio) {
                // 이미지가 세로로 더 길어서 상하가 잘림 - 상하 팬닝
                const positions = ['center top', 'center center', 'center bottom'];
                let posIndex = 0;
                
                function updatePosition() {
                    slideElement.style.setProperty('background-position', positions[posIndex], 'important');
                    slideElement.style.setProperty('transition', 'background-position 3s ease-in-out', 'important');
                    posIndex = (posIndex + 1) % positions.length;
                }
                
                // 즉시 시작
                updatePosition();
                
                // 3초마다 위치 변경
                panningTimer = setInterval(updatePosition, 3000);
            }
        };
        
        img.src = imageUrl;
    }
    
    // 팬닝 효과 중지
    function stopPanning() {
        if (panningTimer) {
            clearInterval(panningTimer);
            panningTimer = null;
        }
    }
    
    // 슬라이드 표시 함수
    function showSlide(index) {
        if (index < 0 || index >= slides.length) return;
        
        // 이전 팬닝 효과 중지
        stopPanning();
        
        // 모든 슬라이드 상태 초기화
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            slide.style.display = 'none';
            slide.style.zIndex = '1';
            slide.style.transition = 'none'; // 전환 효과 제거
        });
        
        // 선택된 슬라이드 활성화
        const activeSlide = slides[index];
        if (activeSlide) {
            activeSlide.classList.add('active');
            activeSlide.style.display = 'block';
            activeSlide.style.zIndex = '2';
            
            // 배경 이미지 강제 적용
            const bgImage = activeSlide.style.backgroundImage;
            if (bgImage && bgImage !== 'none') {
                activeSlide.style.setProperty('background-image', bgImage, 'important');
                activeSlide.style.setProperty('background-size', 'cover', 'important');
                activeSlide.style.setProperty('background-position', 'center center', 'important');
                activeSlide.style.setProperty('background-repeat', 'no-repeat', 'important');
                
                // 팬닝 효과 시작 (약간의 지연 후)
                setTimeout(() => {
                    startPanning(activeSlide);
                }, 500);
            }
            
            // 인디케이터 업데이트
            updateIndicators(index);
            currentSlide = index;
        }
    }
    
    // 인디케이터 업데이트 함수 분리
    function updateIndicators(activeIndex) {
        if (!config.showIndicators || indicators.length === 0) return;
        
        indicators.forEach((indicator, i) => {
            if (i === activeIndex) {
                indicator.style.backgroundColor = 'rgba(255, 255, 255, 1)';
                indicator.setAttribute('aria-selected', 'true');
            } else {
                indicator.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
                indicator.setAttribute('aria-selected', 'false');
            }
        });
    }
    
    // 다음 슬라이드
    function nextSlide() {
        showSlide((currentSlide + 1) % slides.length);
    }
    
    // 이전 슬라이드
    function prevSlide() {
        showSlide((currentSlide - 1 + slides.length) % slides.length);
    }
    
    // 자동 재생 시작
    function startAutoPlay() {
        if (!config.autoPlay) return;
        stopAutoPlay();
        autoPlayTimer = setInterval(nextSlide, config.autoPlayInterval);
    }
    
    // 자동 재생 중지
    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
        stopPanning(); // 팬닝도 함께 중지
    }
    
    // 네비게이션 버튼은 제거됨
    
    if (config.showIndicators) {
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                showSlide(index);
                stopAutoPlay();
                setTimeout(startAutoPlay, 3000); // 3초 후 자동재생 재시작
            });
        });
    }
    
    // 마우스 호버 시 자동재생 일시정지
    slider.addEventListener('mouseenter', stopAutoPlay);
    slider.addEventListener('mouseleave', startAutoPlay);
    
    // 키보드 네비게이션 제거됨
    
    // 터치/스와이프 기능 제거됨
    
    // 초기화
    function initializeSlider() {
        if (slides.length > 0) {
            showSlide(0);
            setTimeout(() => {
                startAutoPlay();
            }, 1000);
        }
    }
    
    // DOM 완전 로드 후 초기화
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSlider);
    } else {
        // 이미 로드된 경우 즉시 실행
        setTimeout(initializeSlider, 100);
    }
    
    // 페이지 가시성 변화에 따른 자동재생 제어
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoPlay();
        } else {
            startAutoPlay();
            // 현재 슬라이드의 팬닝 효과도 다시 시작
            const activeSlide = slides[currentSlide];
            if (activeSlide) {
                setTimeout(() => {
                    startPanning(activeSlide);
                }, 500);
            }
        }
    });
    
    // 윈도우 포커스/블러에 대한 자동재생 제어
    window.addEventListener('focus', () => {
        startAutoPlay();
        // 현재 슬라이드의 팬닝 효과도 다시 시작
        const activeSlide = slides[currentSlide];
        if (activeSlide) {
            setTimeout(() => {
                startPanning(activeSlide);
            }, 500);
        }
    });
    window.addEventListener('blur', stopAutoPlay);
    
})();
</script>
<?php endif; ?>

<style>
/* Hero Slider 추가 스타일 - Cache Busted <?= time() ?> - Image Fix Version */

/* 강력한 배경 제거 - 최우선 적용 */
* {
    box-sizing: border-box;
}

/* 기본 페이지 배경은 테마 배경색 사용 */
html, body {
    background: var(--background) !important;
    background-color: var(--background) !important;
    background-image: none !important;
}

/* 메인 컨텐츠와 섹션도 테마 배경색 사용 */
main {
    background: var(--background) !important;
    background-color: var(--background) !important;
}

/* 히어로 섹션도 테마 배경색 사용 */
.hero-section {
    background: var(--background) !important;
    background-color: var(--background) !important;
    background-image: none !important;
}

/* Tailwind 및 테마 회색 클래스들 강제 오버라이드 */
.bg-gray-50, .bg-gray-100, .bg-gray-200, .bg-gray-300, 
.bg-natural-50, .bg-natural-100, .bg-natural-200 {
    background-color: var(--background) !important;
}

.hero-section {
    background: var(--background) !important;
    background-color: var(--background) !important;
    background-image: none !important;
    overflow: visible;
    margin: 2rem auto 2rem auto; /* 상하단 여백 추가 */
    max-width: 1200px;
    padding: 0 2rem;
    position: relative;
    z-index: 1;
}

.hero-slider {
    overflow: hidden;
    border-radius: 1.5rem;
}

.hero-slider .slide {
    transition: opacity 0.8s ease-in-out;
}

.hero-slider .slide.active {
    /* active slide styles */
}

/* 팬닝 애니메이션을 위한 스타일 */
.hero-slider .slide.panning {
    background-attachment: scroll !important;
}

.hero-indicator {
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.hero-indicator:hover {
    transform: scale(1.3);
    border-color: rgba(255, 255, 255, 0.8);
}

/* 반응형 텍스트 크기 조정 */
@media (max-width: 1024px) {
    .hero-slider .slide h1 {
        font-size: 2.5rem !important;
    }
    .hero-slider .slide p:first-of-type {
        font-size: 1.5rem !important;
    }
    
    /* 태블릿에서는 이미지를 조금 더 위쪽으로 */
    .hero-slider .slide,
    .hero-section .hero-slider .slide {
        background-position: center 10% !important;
    }
}

@media (max-width: 768px) {
    .hero-section {
        padding: 0 1rem !important;
        margin: 1.5rem auto 1.5rem auto !important;
    }
    
    .hero-slider {
        min-height: 300px !important;
        border-radius: 1rem !important;
    }
    
    /* 모바일에서는 사람이 더 잘 보이도록 상단 5% 위치 */
    .hero-slider .slide,
    .hero-section .hero-slider .slide {
        background-position: center 5% !important;
    }
    
    .hero-slider .slide h1 {
        font-size: 2rem !important;
        margin-bottom: 1.5rem !important;
    }
    
    .hero-slider .slide p:first-of-type {
        font-size: 1.25rem !important;
        margin-bottom: 2rem !important;
    }
    
    .hero-slider .slide p:last-of-type {
        font-size: 1rem !important;
        padding: 0.75rem 1.5rem !important;
    }
    
    .hero-indicator {
        width: 12px;
        height: 12px;
    }
}

@media (max-width: 480px) {
    .hero-section {
        padding: 0 0.75rem !important;
        margin: 1rem auto 1rem auto !important;
    }
    
    .hero-slider {
        border-radius: 0.75rem !important;
    }
    
    .hero-slider .slide h1 {
        font-size: 1.75rem !important;
    }
    
    .hero-slider .slide p:first-of-type {
        font-size: 1.125rem !important;
    }
}

/* 접근성을 위한 포커스 표시 */
.hero-indicator:focus {
    outline: 3px solid #ffffff;
    outline-offset: 3px;
    box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.3);
}

/* 슬라이드 레이아웃 - 올바른 레이아웃 */
.hero-slider {
    position: relative;
    height: 400px;
    min-height: 400px;
    overflow: hidden;
    background: transparent !important;
    border-radius: 1.5rem;
    display: block;
    /* 중요: 다른 컨텐츠를 가리지 않도록 static 유지 */
}

.hero-slider .slide,
.hero-section .hero-slider .slide {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-size: cover !important;
    background-position: center 15% !important;
    background-repeat: no-repeat !important;
    background-attachment: scroll !important;
    display: none !important;
    transition: opacity 0.8s ease-in-out !important;
    z-index: 1 !important;
    overflow: hidden !important;
}

.hero-slider .slide.active,
.hero-section .hero-slider .slide.active {
    display: block !important;
    opacity: 1 !important;
    z-index: 2 !important;
    visibility: visible !important;
}

.hero-slider .slide:first-child {
    display: block;
    opacity: 1;
    z-index: 2;
}

/* 텍스트 그림자 효과 강화 */
.drop-shadow-lg {
    text-shadow: 0 4px 6px rgba(0, 0, 0, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2);
}

.drop-shadow-md {
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
}
</style>