<?php
// 모던 부트스트랩 시스템 사용
require_once __DIR__ . '/../bootstrap/app.php';

// 페이지 메타
$pageTitle = '희망씨는 | ' . app_name();
$pageDescription = '희망씨는 더불어 사는 삶을 위하여 희망연대노동조합 조합원과 지역주민들이 함께 설립한 법인입니다.';
$currentSlug = 'about';

// 정적 콘텐츠를 코드에 직접 포함 (임시 파일 불필요)
$subject = '희망씨는';
$contentHtml = <<<'HTML'
<div class="b01_bg_wrap" data-aos="fade-up">
	<h3>희망씨를 찾아주셔서 감사드립니다.</h3>

	<p>
	희망씨는 이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여 희망연대노동조합 조합원과 지역주민들이 함께 설립한 법인입니다.<br>
	<br>
	희망씨는 모든 아동청소년이 고유한 인격체로서 존중받고 어떠한 이유로도 차별받지 않도록 아동권리실현에 앞장서는 활동을 진행합니다.<br>
	<br>
	희망씨는 노동자가 자발적 주체가 되어 나눔연대·생활문화연대를 위한 지속가능한 활동을 만들어 가는데 함께 합니다.<br>
	<br>
	희망씨는 지역사회와 함께 아래로 향한 연대 일터와 삶터를 바꾸기 위한 활동에 함께 합니다.<br>
	</p>
</div>

<h3 class="s1hd" data-aos="fade-up">사단법인 희망씨의 활동 원칙 </h3>
<div class="con_box">
	<dl data-aos="fade-up" data-aos-delay="400">
		<dt><span class="bc_green">01</span>시혜가 아닌 나눔활동</dt>
		<dd>일부 구호단체에서 진행하듯이 소위 '불쌍한 아이'를 돕는 것에 초점을 두지 않고, 서로가 가진 것을 나누는 활동으로 사업을 진행합니다.</dd>
	</dl>
	<dl data-aos="fade-up" data-aos-delay="400">
		<dt><span class="bc_yellow">02</span>봉사가 아닌 연대활동</dt>
		<dd>어느 누구의 일방적인 봉사가 아닌 건강한 노동자로 성장하는 과정에 연대하는 활동입니다.</dd>
	</dl>
	<dl data-aos="fade-up" data-aos-delay="400">
		<dt><span class="bc_blue">03</span>기부가 아닌 참여활동</dt>
		<dd>재능이나, 금전적 기부를 넘어 '더불어 사는 삶'을 만들어 가는 과정에 함께 참여하는 활동입니다.</dd>
	</dl>
	<dl data-aos="fade-up" data-aos-delay="400">
		<dt><span class="bc_green">04</span>사람중심 조직문화</dt>
		<dd>지속 가능한 활동을 위해 사람을 중심에 두고 함께 성장해 나가는 조직문화를 만들어 갑니다.</dd>
	</dl>
</div>
HTML;

// 소개 제목과 첫 문단 추출
$introHeading = '';
$introParagraph = '';
if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $contentHtml, $m)) {
    $introHeading = trim(strip_tags($m[1]));
}
if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $contentHtml, $m)) {
    $introParagraph = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<br>', "\n", $m[1]))));
}

// 활동 원칙 추출 (<dl><dt>제목</dt><dd>설명</dd>)
$principles = [];
if (preg_match_all('/<dl[^>]*>\s*<dt[^>]*>\s*(?:<span[^>]*>\d+<\/span>)?\s*([^<]+)<\/dt>\s*<dd[^>]*>(.*?)<\/dd>\s*<\/dl>/is', $contentHtml, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $dl) {
        $title = trim(strip_tags($dl[1]));
        $desc = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<br>', ' ', $dl[2]))));
        if ($title || $desc) {
            $principles[] = [ 'title' => $title, 'desc' => $desc ];
        }
    }
}

// 헤더 포함
include __DIR__ . '/../includes/header.php';
?>
<main id="container" role="main">
  <article aria-labelledby="about-title" class="max-w-5xl mx-auto px-4 py-8">
    <header class="mb-8">
      <p class="text-sm text-gray-500">About</p>
      <h1 id="about-title" class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?> flex items-center gap-2">
        <i class="fa fa-leaf" aria-hidden="true"></i>
        <?php echo htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'); ?>
      </h1>
    </header>

    <?php if ($introHeading || $introParagraph): ?>
    <section class="rounded-2xl <?= getThemeClass('bg', 'secondary', '100') ?> p-6 md:p-8 mb-10 hover-lift" aria-labelledby="intro-title">
      <?php if ($introHeading): ?>
        <h2 id="intro-title" class="text-2xl font-semibold <?= getThemeClass('text', 'primary', '700') ?> mb-3"><?php echo htmlspecialchars($introHeading, ENT_QUOTES, 'UTF-8'); ?></h2>
      <?php endif; ?>
      <?php if ($introParagraph): ?>
        <p class="leading-7 text-gray-700"><?php echo nl2br(htmlspecialchars($introParagraph, ENT_QUOTES, 'UTF-8')); ?></p>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- 미션/비전 카드 (mission.php 내용 삽입) -->
    <section class="mb-12" aria-labelledby="mv-title">
      <h2 id="mv-title" class="text-xl font-bold <?= getThemeClass('text', 'primary', '700') ?> mb-4">미션 및 비전</h2>
      <style>
        @keyframes fadeInUp { from {opacity:0; transform: translateY(40px);} to {opacity:1; transform:none;} }
        .fade-in-up { animation: fadeInUp 0.8s ease-out forwards; }
        .initial-hidden { opacity: 0; }
        .animation-delay-200 { animation-delay: .2s; }
        .animation-delay-400 { animation-delay: .4s; }
      </style>
      <div class="w-full max-w-4xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="initial-hidden fade-in-up animation-delay-200 bg-white/70 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg p-8 text-center transition-all duration-300 hover:shadow-2xl hover:-translate-y-2">
            <div class="flex justify-center items-center gap-4 mb-6">
              <svg class="w-10 h-10 <?= getThemeClass('text', 'primary', '600') ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 15.91a4.5 4.5 0 1 1-6.364-6.364 4.5 4.5 0 0 1 6.364 6.364Z" />
              </svg>
              <h3 class="text-3xl font-bold text-gray-800">미션</h3>
            </div>
            <p class="text-lg text-gray-600 leading-relaxed">희망씨는 아동청소년이 차별받지 않는 세상을 위해 노동자와 함께 합니다.</p>
          </div>

          <div class="initial-hidden fade-in-up animation-delay-400 bg-white/70 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg p-8 text-center transition-all duration-300 hover:shadow-2xl hover:-translate-y-2">
            <div class="flex justify-center items-center gap-4 mb-6">
              <svg class="w-10 h-10 <?= getThemeClass('text', 'secondary', '600') ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 01-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 013.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 013.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 01-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.898 20.543L16.5 21.75l-.398-1.207a3.375 3.375 0 00-2.455-2.456L12.75 18l1.207-.398a3.375 3.375 0 002.455-2.456L16.5 14.25l.398 1.207a3.375 3.375 0 002.456 2.456L20.25 18l-1.207.398a3.375 3.375 0 00-2.456 2.456z" />
              </svg>
              <h3 class="text-3xl font-bold text-gray-800">비전</h3>
            </div>
            <p class="text-lg text-gray-600 leading-relaxed">구분과 격차가 없는 더불어 사는 세상을 꿈꿉니다.</p>
          </div>
        </div>
      </div>
    </section>

    <?php if (!empty($principles)): ?>
    <section aria-labelledby="principles-title" class="mb-12">
      <h2 id="principles-title" class="text-xl font-bold <?= getThemeClass('text', 'primary', '700') ?> mb-4">사단법인 희망씨의 활동 원칙</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <?php foreach ($principles as $index => $p): ?>
          <div class="bg-white rounded-xl border <?= getThemeClass('border', 'border', '200') ?> p-6 hover-lift" role="listitem">
            <div class="flex items-start gap-3">
              <div class="shrink-0 w-10 h-10 rounded-full <?= getThemeClass('bg', 'secondary', '100') ?> <?= getThemeClass('text', 'primary', '700') ?> flex items-center justify-center font-bold"><?php echo $index+1; ?></div>
              <div>
                <h3 class="text-lg font-semibold <?= getThemeClass('text', 'primary', '600') ?> mb-1"><?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="text-gray-700 leading-7"><?php echo htmlspecialchars($p['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!$introHeading && !$introParagraph && empty($principles)): ?>
      <section class="text-gray-600">원본 콘텐츠를 불러오는 데 문제가 있습니다.</section>
    <?php endif; ?>
  </article>
</main>
<?php 
// 푸터 포함
include __DIR__ . '/../includes/footer.php'; 
?>


