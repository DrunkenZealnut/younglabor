<?php
/**
 * 자원봉사안내 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '자원봉사안내 | ' . app_name();
$currentSlug = 'programs/volunteer';

$subject = '자원봉사안내';

// CSS Variables 모드 지원 추가 (Legacy 모드 보존)
require_once __DIR__ . '/../includes/CSSVariableThemeManager.php';
$useCSSVars = detectCSSVarsMode();

if ($useCSSVars && !isset($styleManager)) {
    $styleManager = getCSSVariableManager();
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
      <p class="text-gray-600 mt-2">함께하는 나눔, 기록되는 시간</p>
    </header>

    <section class="bg-white rounded-2xl border border-primary-light hover:border-primary shadow-sm p-6 md:p-8 transition-all duration-300">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6 items-start">
        <div class="md:col-span-2">
          <img src="/programs/img/b15_img.png" alt="자원봉사안내 이미지" class="w-full h-auto rounded-lg border border-primary-light" />
        </div>
        <div class="md:col-span-3">
          <?php if ($useCSSVars): ?>
            <h2 class="text-2xl font-semibold mb-3" style="<?= $styleManager->getStyleString(['color' => 'forest-700']) ?>">자원봉사안내</h2>
          <?php else: ?>
            <h2 class="text-2xl font-semibold <?= getThemeClass('text', 'primary', '700') ?> mb-3">자원봉사안내</h2>
          <?php endif; ?>
          <p class="text-gray-700 leading-relaxed">
            사단법인 희망씨는 1365자원봉사센터에 등록되어 있는 단체이며, 나눔활동을 통해 자원봉사시간을 부여하고 있습니다.
          </p>
          <p class="text-gray-700 leading-relaxed mt-3">
            <?php if ($useCSSVars): ?>
              <a href="https://www.1365.go.kr" target="_blank" rel="noopener" class="underline" style="<?= $styleManager->getStyleString(['color' => 'forest-700']) ?> hover: <?= $styleManager->getStyleString(['color' => 'lime-600']) ?>">www.1365.go.kr</a>
            <?php else: ?>
              <a href="https://www.1365.go.kr" target="_blank" rel="noopener" class="<?= getThemeClass('text', 'primary', '700') ?> underline hover:<?= getThemeClass('text', 'secondary', '600') ?>">www.1365.go.kr</a>
            <?php endif; ?>
            로 들어가셔서 회원가입을 한 후, 간단한 개인정보를 희망씨에 알려주시면 나눔활동에 대한 자원봉사시간을 부여해 드립니다.
          </p>
          <div class="mt-5">
            <?php if ($useCSSVars): ?>
              <a href="https://www.1365.go.kr/" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-primary hover:border-primary-hover hover:bg-primary/10 focus:outline-none focus:ring-2 focus:ring-primary transition-all duration-300" style="<?= $styleManager->getStyleString(['color' => 'forest-700']) ?>">
            <?php else: ?>
              <a href="https://www.1365.go.kr/" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-primary hover:border-primary-hover hover:bg-primary/10 <?= getThemeClass('text', 'primary', '700') ?> focus:outline-none focus:ring-2 focus:ring-primary transition-all duration-300">
            <?php endif; ?>
              <span>www.1365.go.kr</span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4" aria-hidden="true">
                <path d="M13.5 4.5h6v6m0-6L10.5 13.5m9-9H15a6 6 0 00-6 6v1.5" stroke="currentColor" stroke-width="1.5" fill="none" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </section>
  </article>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>


