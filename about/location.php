<?php
/**
 * 오시는길 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '오시는길 | ' . app_name();
$currentSlug = 'about/location';

// Legacy mode only - CSS vars mode removed
$useCSSVars = false;

try {
    // 오시는길 콘텐츠 조회
    $row = DatabaseManager::selectOne("
        SELECT wr_subject, wr_content
        FROM hopec_location
        WHERE wr_is_comment = 0
        ORDER BY wr_id ASC
        LIMIT 1
    ");
} catch (Exception $e) {
    $row = null;
    if (is_debug()) {
        echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">';
        echo '데이터베이스 오류: ' . h($e->getMessage());
        echo '</div>';
    }
}

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';
?>
<main id="main" role="main" class="flex-1">
  <article class="max-w-5xl mx-auto px-4 py-10">
    <header class="mb-8">
      <p class="text-sm text-gray-500">About</p>
      <?php if ($useCSSVars): ?>
        <h1 class="text-3xl md:text-4xl font-bold" style="<?= $styleManager->getStyleString(['color' => 'forest-600']) ?>">오시는길</h1>
      <?php else: ?>
        <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>">오시는길</h1>
      <?php endif; ?>
    </header>

    <section class="bg-white rounded-2xl border <?= getThemeClass('border', 'border', '200') ?> shadow-sm overflow-hidden">
      <div class="p-4 md:p-6 view_bo_con b04">
        <?php echo $row ? $row['wr_content'] : '<p>콘텐츠가 없습니다.</p>'; ?>
      </div>
    </section>
  </article>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>


