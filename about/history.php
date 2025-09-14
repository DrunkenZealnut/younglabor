<?php
/**
 * 연혁 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '연혁 | ' . app_name();
$currentSlug = 'about/history';

try {
    // 연혁 데이터 조회
    $sql = "
     SELECT
       ca_name AS year,
       CASE wr_subject WHEN '연중' THEN -1 ELSE wr_subject END AS month_val,
       wr_subject,
       wr_content
     FROM hopec_history
     WHERE wr_is_comment = 0
     ORDER BY year DESC, month_val DESC
    ";
    $result = DatabaseManager::select($sql);
    
    $items = [];
    foreach ($result as $row) {
        $items[] = $row;
    }
} catch (Exception $e) {
    $items = [];
    if (is_debug()) {
        echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">';
        echo '데이터베이스 오류: ' . h($e->getMessage());
        echo '</div>';
    }
}

// 연도별 그룹화
$grouped_by_year = [];
foreach ($items as $row) {
  $year = (string)$row['year'];
  if (!isset($grouped_by_year[$year])) {
    $grouped_by_year[$year] = [];
  }
  $monthLabel = htmlspecialchars($row['wr_subject']); // '연중' 포함
  $contentText = trim(preg_replace('/\s+/', ' ', strip_tags($row['wr_content'])));
  $grouped_by_year[$year][] = [
    'month' => $monthLabel,
    'content' => $contentText,
  ];
}

// 최신 연도가 먼저 오도록 정렬
$years = array_keys($grouped_by_year);
rsort($years, SORT_NUMERIC);

// 기본 선택 연도 (쿼리 파라미터 year 우선)
$paramYear = isset($_GET['year']) ? preg_replace('/[^0-9]/', '', $_GET['year']) : '';
$activeYear = in_array($paramYear, $years, true) ? $paramYear : (isset($years[0]) ? $years[0] : '');

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';
?>
<main id="main" role="main" class="flex-1">
  <article class="max-w-5xl mx-auto px-4 py-10">
    <header class="mb-8">
      <p class="text-sm text-gray-500">About</p>
      <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>">연혁</h1>
    </header>

    <?php if (empty($grouped_by_year)): ?>
      <p class="text-gray-600">등록된 연혁이 없습니다.</p>
    <?php else: ?>
      <nav class="mb-8 flex flex-wrap gap-2 items-center" aria-label="연도 선택" role="tablist">
        <?php foreach ($years as $y): ?>
          <button type="button"
                  class="year-btn inline-flex items-center justify-center px-3 py-2 rounded-lg border <?php echo ($y===$activeYear ? getThemeClass('bg', 'secondary', '50') . ' ' . getThemeClass('border', 'secondary', '400') . ' ' . getThemeClass('text', 'primary', '700') : 'bg-white ' . getThemeClass('border', 'border', '200') . ' ' . getThemeClass('text', 'primary', '600')); ?> hover:<?= getThemeClass('bg', 'secondary', '100') ?> focus:outline-none focus:ring-2 focus:<?= getThemeClass('ring', 'secondary', '400') ?>"
                  data-year="<?php echo htmlspecialchars($y); ?>"
                  role="tab"
                  aria-selected="<?php echo ($y===$activeYear?'true':'false'); ?>"
                  aria-controls="panel-<?php echo htmlspecialchars($y); ?>">
            <?php echo htmlspecialchars($y); ?>
          </button>
        <?php endforeach; ?>
      </nav>

      <?php foreach ($years as $year): $entries = $grouped_by_year[$year]; ?>
        <section id="panel-<?php echo htmlspecialchars($year); ?>" class="mb-12 <?php echo ($year===$activeYear?'':'hidden'); ?>" role="tabpanel" aria-labelledby="tab-<?php echo htmlspecialchars($year); ?>">
          <div class="flex items-start gap-6">
            <div class="shrink-0 <?= getThemeClass('text', 'primary', '700') ?> font-extrabold" style="font-size: clamp(28px, 4vw, 40px); line-height: 1;"><?php echo htmlspecialchars($year); ?></div>
            <ul class="flex-1">
              <?php foreach ($entries as $entry): ?>
                <li class="grid timeline-row grid-cols-[var(--date-col,_max-content)_1.5rem_1fr] gap-4 py-2">
                  <div class="date-cell text-right font-semibold text-gray-700 tabular-nums whitespace-nowrap pr-2 md:pr-3"><?php echo htmlspecialchars($entry['month']); ?></div>
                  <div class="relative" aria-hidden="true">
                    <span class="absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-px <?= getThemeClass('bg', 'secondary', '300') ?>"></span>
                    <span class="relative z-10 block mt-2 w-2 h-2 rounded-full <?= getThemeClass('bg', 'primary', '600') ?>"></span>
                  </div>
                  <div class="text-gray-800 leading-7"><?php echo nl2br(htmlspecialchars($entry['content'])); ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </section>
      <?php endforeach; ?>
    <?php endif; ?>
  </article>
</main>
<script>
  (function(){
    try{
      // 테마 클래스를 JavaScript 변수로 전달
      var themeClasses = {
        activeBackground: '<?= getThemeClass("bg", "secondary", "50") ?>',
        activeBorder: '<?= getThemeClass("border", "secondary", "400") ?>',
        activeText: '<?= getThemeClass("text", "primary", "700") ?>',
        inactiveBackground: 'bg-white',
        inactiveBorder: '<?= getThemeClass("border", "border", "200") ?>',
        inactiveText: '<?= getThemeClass("text", "primary", "600") ?>'
      };
      
      var buttons = document.querySelectorAll('.year-btn');
      function computeDateColumn(year){
        var panel = document.getElementById('panel-'+year);
        if(!panel) return;
        var ul = panel.querySelector('ul.flex-1');
        if(!ul) return;
        var max = 0;
        panel.querySelectorAll('.date-cell').forEach(function(el){
          var w = el.getBoundingClientRect().width || 0;
          if (w > max) max = w;
        });
        // 여유 간격(+16px) 포함하여 연도 내 모든 행이 동일 기준으로 정렬되도록 고정 폭 설정
        ul.style.setProperty('--date-col', (Math.ceil(max) + 16) + 'px');
      }
      function activate(year){
        buttons.forEach(function(btn){
          var y = btn.getAttribute('data-year');
          var selected = (y === year);
          btn.setAttribute('aria-selected', selected ? 'true' : 'false');
          
          // 모든 테마 관련 클래스 제거
          btn.classList.remove(themeClasses.activeBackground, themeClasses.activeBorder, themeClasses.activeText);
          btn.classList.remove(themeClasses.inactiveBackground, themeClasses.inactiveBorder, themeClasses.inactiveText);
          
          // 선택 상태에 따라 테마 클래스 적용
          if (selected) {
            btn.classList.add(themeClasses.activeBackground, themeClasses.activeBorder, themeClasses.activeText);
          } else {
            btn.classList.add(themeClasses.inactiveBackground, themeClasses.inactiveBorder, themeClasses.inactiveText);
          }
          
          document.getElementById('panel-'+y)?.classList.toggle('hidden', !selected);
        });
        if (history && history.replaceState) {
          var url = new URL(window.location.href);
          url.searchParams.set('year', year);
          history.replaceState(null, '', url.toString());
        }
        computeDateColumn(year);
      }
      buttons.forEach(function(btn){
        btn.addEventListener('click', function(){ activate(btn.getAttribute('data-year')); });
      });
      // 초기 활성 연도 계산 및 폭 설정
      var currentBtn = Array.from(buttons).find(function(b){ return b.getAttribute('aria-selected') === 'true'; });
      if (currentBtn) computeDateColumn(currentBtn.getAttribute('data-year'));
      // 리사이즈 시 현재 활성 연도 기준으로 재계산
      window.addEventListener('resize', function(){
        var current = Array.from(buttons).find(function(b){ return b.getAttribute('aria-selected') === 'true'; });
        if (current) computeDateColumn(current.getAttribute('data-year'));
      });
    }catch(e){}
  })();
</script>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>


