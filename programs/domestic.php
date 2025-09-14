<?php
/**
 * 국내위기아동지원사업 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '국내위기아동지원사업 | ' . app_name();
$currentSlug = 'programs/domestic';

// 정적 원문 HTML을 직접 포함 (임시파일 제거)
$subject = '국내위기아동지원사업';
$rawHtml = <<<'HTML'
<div class="g02_list_wr">
  <div class="g02_list">
    <dl class="full_dl">
      <dt>입학지원(교복지원)</dt>
      <dd>
        <div>복지사각지대 가정의 중,고등 신입생 청소년에 대한 지원</div>
        <span class="list_in">- 2016년 10명 교복지원</span>
        <span class="list_in">- 2017년 28명 교복지원</span>
        <span class="list_in">- 2018년 36명 교복지원</span>
        <span class="list_in">- 2019년 32명 교복지원</span>
        <span class="list_in">- 2020년 39명 입학지원</span>
        <span class="list_in">- 2021년 27명 입학지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>생리대지원</dt>
      <dd>
        <div>장애 여성(청소년 및 청년) 및 복지사각지대에 있는 아동청소년에 대한 1년 사용 분량의 생리대와 생필품(물티슈, 핸드크림, 파우치, 손소독제 등), 성장도서 지원</div>
        <span class="list_in">- 2017년 71명 생리대지원</span>
        <span class="list_in">- 2018년 95명 생리대지원</span>
        <span class="list_in">- 2019년 155명 생리대지원</span>
        <span class="list_in">- 2020년 228명 생리대지원</span>
        <span class="list_in">- 2021년 224명 생리대지원</span>
        <span class="list_in">- 2022년 259명 생리대지원</span>
        <span class="list_in">- 2023년 221명 생리대지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>아동청소년건강지원(심리정서지원/건강지원)</dt>
      <dd>
        <div>아동청소년 심리상담 지원 및 각종 치료비 지원, 취약계층 가정에 대한 건강증진식품 지원 등</div>
        <span class="list_in">- 2017년 19명 지원</span>
        <span class="list_in">- 2018년 10명 지원</span>
        <span class="list_in">- 2019년 13명 지원</span>
        <span class="list_in">- 2020년 24명 지원</span>
        <span class="list_in">- 2021년 163명 지원</span>
        <span class="list_in">- 2022년 136명 지원</span>
        <span class="list_in">- 2024년 16명 지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>교육문화(양육)지원</dt>
      <dd>
        <div>복지사각지대에 있는 아동청소년에 대한 교육비지원, 문화활동비 지원, 문화체험지원 등</div>
        <span class="list_in">- 2017년 7명 지원</span>
        <span class="list_in">- 2018년 9명 지원</span>
        <span class="list_in">- 2019년 5명 지원</span>
        <span class="list_in">- 2020년 41명 지원</span>
        <span class="list_in">- 2021년 4명 지원</span>
        <span class="list_in">- 2022년 11명 지원</span>
        <span class="list_in">- 2023년 8명 지원</span>
        <span class="list_in">- 2024년 4명 지원, 청소년 모임 5명 참여</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>이주배경아동청소년지원</dt>
      <dd>
        <div>이주배경아동을 위한 한글교실, 이주배경청소년과 선배노동자가 함께 어울리는 문화활동, 이주배경가정의 양육자를 위한 양육자 자조모임 운영</div>
        <span class="list_in">- 2023년 청소년 17명, 26가정 지원</span>
        <span class="list_in">- 2024년 청소년 22명, 양육자 21명 지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>학교 밖 청소년지원사업 (일터 체험)</dt>
      <dd>
        <div>노동조합과 함께 노동현장 체험을 통해 학교 밖 청소년의 진로 경험을 높이는 활동</div>
        <span class="list_in">- 2023년 청소년 69명 참여</span>
        <span class="list_in">- 2024년 청소년 39명 참여</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>자립준비청(소)년지원</dt>
      <dd>
        <div>보호종료 청소년들이 안정적으로 자립할 수 있도록 노동인권 교육, 금융교육, 캠프, 선배 노동자와의 만남 등 다양한 활동지원과 자조모임 운영</div>
        <span class="list_in">- 2022년 청소년 9명 지원</span>
        <span class="list_in">- 2023년 청소년 8명, 청년 7명 지원</span>
        <span class="list_in">- 2024년 청소년 8명, 청년 9명 지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>긴급지원</dt>
      <dd>
        <div>긴급한 위기 상황에 놓인 가정에 대한 지원</div>
        <span class="list_in">- 2017년 2명 지원</span>
        <span class="list_in">- 2018년 6명 지원</span>
        <span class="list_in">- 2019년 2명 지원</span>
        <span class="list_in">- 2020년 4가정 지원</span>
        <span class="list_in">- 2021년 10명, 3개 기관, 37가정 지원</span>
        <span class="list_in">- 2022년 19가정, 3개기관 지원</span>
        <span class="list_in">- 2023년 16가정 지원</span>
        <span class="list_in">- 2024년 9가정(10명), 2개 기관 지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>주거지원(환경개선지원)</dt>
      <dd>
        <div>취약계층 가정의 주거환경 개선 및 주거비 지원, 아동청소년이 생활하는 시설(기관)에 대한 환경개선 사업 등</div>
        <span class="list_in">- 2017년 2명 지원</span>
        <span class="list_in">- 2018년 11개 기관, 3명 지원</span>
        <span class="list_in">- 2019년 9개 기관, 6개 가정 지원</span>
        <span class="list_in">- 2020년 3개 기관, 9개 가정 지원</span>
        <span class="list_in">- 2021년 12개 가정 지원</span>
        <span class="list_in">- 2022년 5개 기관, 10개 가정 지원</span>
        <span class="list_in">- 2023년 5개 가정 지원</span>
        <span class="list_in">- 2024년 3가정, 5개 기관 지원</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>노동자가족지원사업</dt>
      <dd>
        <div>노동자 가정이 일상을 회복할 수 있도록 도울 수 있는 캠프, 소풍 사업 등</div>
        <span class="list_in">- 2022년 노동자가정 14가정 지원</span>
        <span class="list_in">- 2023년 노동자가정 20가정 지원</span>
        <span class="list_in">- 2024년 노동자가정 22가정 지원</span>
      </dd>
    </dl>
  </div>
</div>
HTML;

// 슬라이드 이미지: programs/img 폴더의 B11_*.png 파일을 자동 수집해 사용
$imgSrcs = [];
$imgDir = __DIR__ . '/img';
$imgUrlBase = '/programs/img';
if (is_dir($imgDir)) {
  // B11_*.png 패턴의 파일만 수집 (자연 정렬로 번호 순서 보장)
  $found = glob($imgDir . '/B11_*.png');
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
      <p class="text-gray-600 mt-2">아이들의 생존-발달-성장을 연대와 지원으로 함께합니다</p>
    </header>

    <?php if (!empty($imgSrcs)): ?>
    <section class="mb-8" aria-label="국내아동지원사업 이미지 슬라이드">
      <style>
        .slider{position:relative;border-radius:1rem;overflow:hidden;background:#f8faf9}
        .slide{display:none;width:100%;height:auto}
        .slide.active{display:block}
        .slider .nav{position:absolute;inset:0;display:flex;justify-content:space-between;align-items:center;padding:0 .5rem;opacity:0;pointer-events:none;transition:opacity .2s ease}
        .slider:hover .nav,.slider:focus-within .nav{opacity:1;pointer-events:auto}
      </style>
      <div id="slider-b11" class="slider">
        <?php foreach ($imgSrcs as $i => $src): ?>
          <img src="<?= h($src) ?>" alt="국내아동지원사업 이미지 <?= $i+1 ?>" class="slide <?= $i===0?'active':'' ?>" loading="lazy">
        <?php endforeach; ?>
        <div class="nav" aria-hidden="true">
          <button type="button" class="prev" aria-label="이전">‹</button>
          <button type="button" class="next" aria-label="다음">›</button>
        </div>
      </div>
      <script>
        (function(){
          var el=document.getElementById('slider-b11'); if(!el) return;
          var slides=el.querySelectorAll('.slide'); if(!slides.length) return; var idx=0;
          function show(n){slides[idx].classList.remove('active'); idx=(n+slides.length)%slides.length; slides[idx].classList.add('active');}
          el.querySelector('.prev').addEventListener('click',function(){show(idx-1)});
          el.querySelector('.next').addEventListener('click',function(){show(idx+1)});
          setInterval(function(){show(idx+1)}, 5000);
        })();
      </script>
    </section>
    <?php endif; ?>

    <?php
      // 원문에서 <dl>을 사업 단위로 추출하여, 각 사업 안의 연도 항목을 <br>로 구분해 출력
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
                      // 설명(첫 div 또는 span 전 텍스트)
                      if ($desc === '') {
                          $beforeSpan = preg_split('/<span[^>]*class=\"[^\"]*list_in[^\"]*\"[^>]*>/i', $ddHtml, 2)[0];
                          $descCandidate = trim(preg_replace('/\s+/', ' ', strip_tags($beforeSpan)));
                          if ($descCandidate) $desc = $descCandidate;
                      }
                      // 연도 라인(span.list_in)
                      if (preg_match_all('/<span[^>]*class=\"[^\"]*list_in[^\"]*\"[^>]*>([\s\S]*?)<\/span>/i', $ddHtml, $spans)) {
                          foreach ($spans[1] as $txt) {
                              $t = trim(preg_replace('/\s+/', ' ', strip_tags($txt)));
                              if ($t !== '') $lines[] = $t;
                          }
                      } else {
                          // span이 없으면 텍스트에서 "YYYY년 ..." 패턴 추출
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
          <section class="bg-white rounded-2xl border <?= getThemeClass('border', 'border', '200') ?> shadow-sm p-6 md:p-8 h-full max-w-full">
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
      <section class="bg-white rounded-2xl border <?= getThemeClass('border', 'border', '200') ?> shadow-sm p-6 md:p-8">
        <?= $rawHtml ?: '<p>콘텐츠가 없습니다.</p>' ?>
      </section>
    <?php endif; ?>
  </article>
</main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>


