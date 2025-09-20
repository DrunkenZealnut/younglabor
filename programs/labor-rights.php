<?php
/**
 * 노동인권사업 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '노동인권사업 | ' . app_name();
$currentSlug = 'programs/labor-rights';

// CSS Variables 모드 지원 추가 (Legacy 모드 보존)
require_once __DIR__ . '/../includes/CSSVariableThemeManager.php';
$useCSSVars = detectCSSVarsMode();

if ($useCSSVars && !isset($styleManager)) {
    $styleManager = getCSSVariableManager();
}

$subject = '노동인권사업';
$rawHtml = <<<'HTML'
<div class="g02_list_wr">
  <div class="g02_list">
    <dl class="full_dl">
      <dt>노동인권 활동가 양성사업</dt>
      <dd>
        <span class="list_in">- 희망연대노조 노동인권소모임 ‘벙글노동’</span>
        <span class="list_in">- 지역노동인권활동가 양성과정 운영 및 지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>청소년 대상 찾아가는 노동인권교육</dt>
      <dd>
        <span class="list_in">- 학교로 찾아가는 노동인권교육(중학교, 특성화고 및 일반계고)</span>
        <span class="list_in">- 찾아가는 노동인권교육(지역 공부방 및 탈학교 청소년대상)</span>
        <span class="list_in">- 노동인권캠프</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>지역사회 노동인식 개선사업</dt>
      <dd>
        <span class="list_in">- 지역아동센터협의회 등 지역사회 대상 노동인권교육</span>
        <span class="list_in">- 광진구 민방위대원 대상 노동법교육(2018)</span>
        <span class="list_in">- 노동인권캠페인</span>
        <span class="list_in">- 자립청년노동인권교안개발 및 노동인권교육</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>지역사회 연대사업</dt>
      <dd>
        <span class="list_in">- 서울 청소년노동인권 지역단위 네트워크</span>
        <span class="list_in">- 성동인권영화제</span>
        <span class="list_in">- 노동인권관련 다양한 현안 대응</span>
        <span class="list_in">- 비정규활동가지원사업</span>
        <span class="list_in">- 사회적약자지원(에너지 취약 가정 연탄나눔, 순간온수기 지원)</span>
      </dd>
    </dl>
  </div>
</div>
HTML;

// 슬라이드 이미지: programs/img 폴더의 B13_*.png 파일을 자동 수집해 사용
$imgSrcs = [];
$imgDir = __DIR__ . '/img';
$imgUrlBase = env('BASE_PATH', '') . '/programs/img';
if (is_dir($imgDir)) {
  // B13_*.png 패턴의 파일만 수집 (자연 정렬로 번호 순서 보장)
  $found = glob($imgDir . '/B13_*.png');
  if ($found !== false && !empty($found)) {
    natsort($found);
    foreach ($found as $absPath) {
      if (is_file($absPath)) {
        $imgSrcs[] = $imgUrlBase . '/' . basename($absPath);
      }
    }
  }
}

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';
?>
<main id="main" role="main" class="flex-1">
  <article class="max-w-5xl mx-auto px-4 py-10">
    <header class="mb-8">
      <p class="text-sm text-gray-500">Programs</p>
      <?php if ($useCSSVars): ?>
        <h1 class="text-3xl md:text-4xl font-bold" style="<?= $styleManager->getStyleString(['color' => 'forest-600']) ?>"><?= h($subject) ?></h1>
      <?php else: ?>
        <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>"><?= h($subject) ?></h1>
      <?php endif; ?>
      <p class="text-gray-600 mt-2">노동이 존중받는 지역사회를 위한 교육과 연대</p>
    </header>

    <?php if (!empty($imgSrcs)): ?>
    <section class="mb-8" aria-label="노동인권사업 이미지 슬라이드">
      <div class="labor-slider-container" style="position:relative;border-radius:1rem;overflow:hidden;background:#f8faf9;">
        <?php foreach ($imgSrcs as $i => $src): ?>
          <div class="labor-slider-item" style="<?= $i===0?'':'display:none;' ?>position:relative;width:100%;">
            <img src="<?= h($src) ?>" alt="노동인권사업 이미지 <?= $i+1 ?>" style="width:100%;height:auto;display:block;" loading="lazy">
          </div>
        <?php endforeach; ?>
        
        <div class="labor-slider-controls" style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 1rem;opacity:0;transition:opacity 0.3s;z-index:10;">
          <button onclick="changeLaborSlide(-1)" style="background:rgba(0,0,0,0.7);color:white;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;transition:all 0.2s;">‹</button>
          <button onclick="changeLaborSlide(1)" style="background:rgba(0,0,0,0.7);color:white;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;transition:all 0.2s;">›</button>
        </div>
      </div>
      
      <style>
        .labor-slider-container:hover .labor-slider-controls { opacity: 1; }
        .labor-slider-controls button:hover { background: rgba(0,0,0,0.9) !important; transform: scale(1.1); }
        @media (hover: none) { .labor-slider-controls { opacity: 0.8; } }
      </style>
      
      <script>
        (function() {
          let laborSlideIndex = 0;
          const laborSlides = document.querySelectorAll('.labor-slider-item');
          
          function changeLaborSlide(direction) {
            laborSlides[laborSlideIndex].style.display = 'none';
            laborSlideIndex = (laborSlideIndex + direction + laborSlides.length) % laborSlides.length;
            laborSlides[laborSlideIndex].style.display = 'block';
          }
          
          // 전역 함수로 등록
          window.changeLaborSlide = changeLaborSlide;
          
          // 자동 슬라이드
          setInterval(() => changeLaborSlide(1), 5000);
        })();
      </script>
    </section>
    <?php endif; ?>

    <?php
      $sections = [];
      if ($rawHtml && preg_match_all('/<dl[\s\S]*?<\/dl>/i', $rawHtml, $dlAll)) {
          foreach ($dlAll[0] as $dlHtml) {
              $title = '';
              if (preg_match('/<dt[^>]*>([\s\S]*?)<\/dt>/i', $dlHtml, $m)) {
                  $title = trim(strip_tags($m[1]));
              }
              $desc = '';
              $lines = [];
              if (preg_match_all('/<dd[^>]*>([\s\S]*?)<\/dd>/i', $dlHtml, $dds)) {
                  foreach ($dds[1] as $ddHtml) {
                      if ($desc === '') {
                          $beforeSpan = preg_split('/<span[^>]*class=\"[^\"]*list_in[^\"]*\"[^>]*>/i', $ddHtml, 2)[0];
                          $descCandidate = trim(preg_replace('/\s+/', ' ', strip_tags($beforeSpan)));
                          if ($descCandidate) $desc = $descCandidate;
                      }
                      if (preg_match_all('/<span[^>]*class=\"[^\"]*list_in[^\"]*\"[^>]*>([\s\S]*?)<\/span>/i', $ddHtml, $spans)) {
                          foreach ($spans[1] as $txt) {
                              $t = trim(preg_replace('/\s+/', ' ', strip_tags($txt)));
                              if ($t !== '') $lines[] = $t;
                          }
                      } else {
                          $plain = trim(preg_replace('/\s+/', ' ', strip_tags($ddHtml)));
                          if (preg_match_all('/((19|20)\d{2}년[^\n<]*)/u', $plain, $yrs)) {
                              foreach ($yrs[1] as $t) { $lines[] = trim($t); }
                          }
                      }
                  }
              }
              if ($title || $desc || $lines) {
                  $sections[] = [ 'title' => ($title ?: '사업'), 'desc' => $desc, 'lines' => $lines ];
              }
          }
      }
    ?>

    <?php if (!empty($sections)): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($sections as $sec): ?>
          <section class="bg-white rounded-2xl border border-primary-light hover:border-primary shadow-sm p-6 md:p-8 h-full max-w-full transition-all duration-300">
            <?php if ($useCSSVars): ?>
              <h2 class="text-xl font-semibold mb-2" style="<?= $styleManager->getStyleString(['color' => 'forest-700']) ?>"><?= h($sec['title']) ?></h2>
            <?php else: ?>
              <h2 class="text-xl font-semibold <?= getThemeClass('text', 'primary', '700') ?> mb-2"><?= h($sec['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($sec['desc'])): ?>
              <p class="text-gray-700 leading-7 mb-3"><?= h($sec['desc']) ?></p>
            <?php endif; ?>
            <?php if (!empty($sec['lines'])): ?>
              <div class="text-gray-800 leading-7">
                <?= implode('<br>', array_map('h', $sec['lines'])) ?>
              </div>
            <?php endif; ?>
          </section>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <section class="bg-white rounded-2xl border border-primary-light hover:border-primary shadow-sm p-6 md:p-8 transition-all duration-300">
        <?= $rawHtml ?: '<p>콘텐츠가 없습니다.</p>' ?>
      </section>
    <?php endif; ?>
  </article>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>


