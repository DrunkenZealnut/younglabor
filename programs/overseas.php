<?php
/**
 * 해외위기아동지원사업 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '해외위기아동지원사업 | ' . app_name();
$currentSlug = 'programs/overseas';

$subject = '해외위기아동지원사업';
$rawHtml = <<<'HTML'
<div class="g02_list_wr">
  <div class="g02_list">
    <dl class="full_dl">
      <dt>포카라노동자희망학교건축 및 증축, 운영비지원</dt>
      <dd>
        <span class="list_in">- 2014년 ~ 현재 포카라학교 운영비지원</span>
        <span class="list_in">- 2014년 포카라학교 1층 건축</span>
        <span class="list_in">- 2016년 포카라학교 2층 증축</span>
        <span class="list_in">- 2018년 포카라학교 도서실 설치</span>
        <span class="list_in">- 2019년 포카라학교 화장실 설치 및 유지보수</span>
        <span class="list_in">- 2019년 포카라정부 공식학교로 등록</span>
        <span class="list_in">- 2024년 포카라학교 개교 10주년 기념행사</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>뻘벗슈러믹학교 급식비지원</dt>
      <dd>
        <span class="list_in">- 2013년 ~ 현재 뻘벗학교 급식비지원</span>
        <span class="list_in">- 2017년 교복지원 및 학교시설개선지원</span>
        <span class="list_in">- 2018년 태양열 설치 및 샤워시설설치지원</span>
        <span class="list_in">- 2019년 교복지원</span>
        <span class="list_in">- 2020년 코로나19로 인한 긴급 쌀지원(2회차, 총 246가구)지원</span>
        <span class="list_in">- 2021년 교복지원</span>
        <span class="list_in">- 2022년 ~ 현재 화섬노조봉제인지회·봉제인공제회 교복지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>네팔 뻘벗학교 아동결연</dt>
      <dd>
        <span class="list_in">- 2018년 시범사업 4명 아동결연</span>
        <span class="list_in">- 2019년 11명 아동결연</span>
        <span class="list_in">- 2020년 15명 아동결연</span>
        <span class="list_in">- 2021년 19명 아동결연</span>
        <span class="list_in">- 2022년 25명 아동결연</span>
        <span class="list_in">- 2023년 30명 아동결연</span>
        <span class="list_in">- 2024년 31명 아동결연</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>네팔나눔연대여행</dt>
      <dd>
        <span class="list_in">- 2012년 1기 네팔나눔연대여행</span>
        <span class="list_in">- 2013년 2기 네팔나눔연대여행</span>
        <span class="list_in">- 2015년 3기 네팔나눔연대여행</span>
        <span class="list_in">- 2016년 4기 네팔나눔연대여행</span>
        <span class="list_in">- 2017년 5기 네팔나눔연대여행</span>
        <span class="list_in">- 2018년 6,7기 네팔나눔연대여행</span>
        <span class="list_in">- 2019년 8기 네팔나눔연대여행</span>
        <span class="list_in">- 2020년 온라인 네팔나눔연대여행</span>
        <span class="list_in">- 2021년 네팔비대면여행</span>
        <span class="list_in">- 2022년 9기 네팔나눔연대여행</span>
        <span class="list_in">- 2023년 10기 네팔나눔연대여행</span>
        <span class="list_in">- 2024년 11기 네팔나눔연대여행</span>
      </dd>
    </dl>
  </div>
</div>
HTML;

// 슬라이드 이미지: programs/img 폴더의 B12_*.png 파일을 자동 수집해 사용
$imgSrcs = [];
$imgDir = __DIR__ . '/img';
$imgUrlBase = '/programs/img';
if (is_dir($imgDir)) {
  // B12_*.png 패턴의 파일만 수집 (자연 정렬로 번호 순서 보장)
  $found = glob($imgDir . '/B12_*.png');
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
      <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>"><?= h($subject) ?></h1>
      <p class="text-gray-600 mt-2">네팔 아이들의 건강한 성장을 돕는 따뜻한 연대</p>
    </header>

    <?php if (!empty($imgSrcs)): ?>
    <section class="mb-8" aria-label="해외아동지원사업 이미지 슬라이드">
      <div class="image-slider-container" style="position:relative;border-radius:1rem;overflow:hidden;background:#f8faf9;">
        <?php foreach ($imgSrcs as $i => $src): ?>
          <div class="slider-item" style="<?= $i===0?'':'display:none;' ?>position:relative;width:100%;">
            <img src="<?= h($src) ?>" alt="해외아동지원사업 이미지 <?= $i+1 ?>" style="width:100%;height:auto;display:block;" loading="lazy">
          </div>
        <?php endforeach; ?>
        
        <div class="slider-controls" style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 1rem;opacity:0;transition:opacity 0.3s;z-index:10;">
          <button onclick="changeSlide(-1)" style="background:rgba(0,0,0,0.7);color:white;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;transition:all 0.2s;">‹</button>
          <button onclick="changeSlide(1)" style="background:rgba(0,0,0,0.7);color:white;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;transition:all 0.2s;">›</button>
        </div>
      </div>
      
      <style>
        .image-slider-container:hover .slider-controls { opacity: 1; }
        .slider-controls button:hover { background: rgba(0,0,0,0.9) !important; transform: scale(1.1); }
        @media (hover: none) { .slider-controls { opacity: 0.8; } }
      </style>
      
      <script>
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.slider-item');
        
        function changeSlide(direction) {
          slides[currentSlideIndex].style.display = 'none';
          currentSlideIndex = (currentSlideIndex + direction + slides.length) % slides.length;
          slides[currentSlideIndex].style.display = 'block';
        }
        
        // 자동 슬라이드
        setInterval(() => changeSlide(1), 5000);
      </script>
    </section>
    <?php endif; ?>

    <?php
      // 원문에서 <dl>을 사업 단위로 추출하여, 각 사업 안의 연도 항목을 <br>로 구분해 출력 (B11과 동일 포맷)
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
            <h2 class="text-xl font-semibold <?= getThemeClass('text', 'primary', '700') ?> mb-2"><?= h($sec['title']) ?></h2>
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


