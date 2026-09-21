<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
require_once __DIR__ . '/includes/SiteContent.php';
PageTracker::track('우리가 하는 일');

$currentPage = 'activities';
$pageTitle = '우리가 하는 일 - ' . $site['name'];
$pageDescription = '현장 조직, 교육, 안전 도구, 연구로 제조업 청년노동자의 안전할 권리를 만듭니다.';
$pageUrl = url('activities');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <div class="container">
        <p class="eyebrow">우리가 하는 일</p>
        <h1>산출물보다 현장에서 작동하는 변화를 만듭니다.</h1>
        <p>현장 조직, 교육, 안전 도구, 연구를 연결해 청년이 알고 묻고 행동할 힘을 키웁니다.</p>
    </div>
</section>

<?php foreach (siteWorkAreas() as $index => $area): ?>
<section class="section<?php echo $index % 2 === 1 ? ' section-alt' : ''; ?>" id="<?php echo htmlspecialchars($area['key'], ENT_QUOTES, 'UTF-8'); ?>">
    <div class="container activity-area">
        <div>
            <span class="area-number"><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span>
            <h2><?php echo htmlspecialchars($area['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="statement"><?php echo htmlspecialchars($area['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><?php echo htmlspecialchars($area['purpose'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div>
            <h3>지금 하는 활동</h3>
            <ul class="plain-list">
            <?php foreach ($area['activities'] as $activity): ?>
                <li><?php echo htmlspecialchars($activity, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
            </ul>
            <a class="section-link" href="<?php echo url($area['href']); ?>"><?php echo htmlspecialchars($area['link_label'], ENT_QUOTES, 'UTF-8'); ?> →</a>
        </div>
    </div>
</section>
<?php endforeach; ?>

<section class="cta-line">
    <div class="container"><a href="<?php echo url('/'); ?>#contact">교육과 현장 협력을 제안해 주세요 →</a></div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
