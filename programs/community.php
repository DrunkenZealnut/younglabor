<?php
/**
 * 소통 및 회원사업 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '소통 및 회원사업 | ' . app_name();
$currentSlug = 'programs/community';

$subject = '소통 및 회원사업';
$rawHtml = <<<'HTML'
<div class="g02_list_wr">
  <div class="g02_list">
    <dl class="full_dl">
      <dt>가족캠프</dt>
      <dd>
        <span class="list_in">- 비정규노동자 가족캠프</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>아버지학교</dt>
      <dd>
        <span class="list_in">- 비정규노동자 아버지학교</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>회원강좌</dt>
      <dd>
        <span class="list_in">- 수어동아리, 타로강좌, 마크라메강좌, 글쓰기강좌, 사진강좌 등 다양한 회원강좌</span>
      </dd>
    </dl>
  </div>

  <div class="g02_list">
    <dl class="full_dl">
      <dt>노동자힐링캠프</dt>
      <dd>
        <span class="list_in">- 노동자힐링캠프</span>
      </dd>
    </dl>
  </div>
</div>
HTML;

// 슬라이드 이미지: programs/img 폴더의 B14_*.png 파일을 자동 수집해 사용
$imgSrcs = [];
$imgDir = __DIR__ . '/img';
$imgUrlBase = '/programs/img';
if (is_dir($imgDir)) {
  $found = glob($imgDir . '/B14_*.png');
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
      <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>">소통 및 회원사업</h1>
      <p class="text-gray-600 mt-2">함께 배우고 서로 돌보는 회원 공동체</p>
    </header>

    <?php if (!empty($imgSrcs)): ?>
    <section class="mb-8" aria-label="소통 및 회원사업 이미지 슬라이드">
      <style>
        .slider{position:relative;border-radius:1rem;overflow:hidden;background:#f8faf9}
        .slide{display:none;width:100%;height:auto}
        .slide.active{display:block}
        .slider .nav{position:absolute;inset:0;display:flex;justify-content:space-between;align-items:center;padding:0 .5rem;opacity:0;pointer-events:none;transition:opacity .2s ease}
        .slider:hover .nav,.slider:focus-within .nav{opacity:1;pointer-events:auto}
      </style>
      <div id="slider-b14" class="slider">
        <?php foreach ($imgSrcs as $i => $src): ?>
          <img src="<?= h($src) ?>" alt="소통 및 회원사업 이미지 <?= $i+1 ?>" class="slide <?= $i===0?'active':'' ?>" loading="lazy">
        <?php endforeach; ?>
        <div class="nav" aria-hidden="true">
          <button type="button" class="prev" aria-label="이전">‹</button>
          <button type="button" class="next" aria-label="다음">›</button>
        </div>
      </div>
      <script>
        (function(){
          var el=document.getElementById('slider-b14'); if(!el) return;
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


