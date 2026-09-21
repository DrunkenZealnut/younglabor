<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
require_once __DIR__ . '/includes/SiteContent.php';
PageTracker::track('안전 도구');

$currentPage = 'tools';
$pageTitle = '안전 도구 - ' . $site['name'];
$pageDescription = '산업안전 학습과 기초 노동상담을 시민이 쉽게 이용할 수 있도록 만든 도구를 소개합니다.';
$pageUrl = url('tools');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <div class="container">
        <p class="eyebrow">안전 도구</p>
        <h1>현장에서 생긴 질문을, 누구나 사용할 수 있는 도구로 만듭니다.</h1>
        <p>학생, 교사, 청년노동자와 시민이 안전과 노동 기준을 직접 찾아볼 수 있게 돕습니다.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="tool-grid">
        <?php foreach (siteTools() as $tool): ?>
            <article class="tool-card">
                <span class="tool-status"><?php echo htmlspecialchars($tool['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                <h2><?php echo htmlspecialchars($tool['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="tool-tagline"><?php echo htmlspecialchars($tool['tagline'], ENT_QUOTES, 'UTF-8'); ?></p>
                <dl class="tool-details">
                    <div><dt>누가 쓰나요</dt><dd><?php echo htmlspecialchars($tool['audience'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <div><dt>무엇을 할 수 있나요</dt><dd><?php echo htmlspecialchars($tool['description'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                </dl>
                <a class="tool-link" href="<?php echo htmlspecialchars($tool['url'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($tool['cta'], ENT_QUOTES, 'UTF-8'); ?>
                    <span aria-hidden="true">↗</span>
                    <span class="tool-domain"><?php echo htmlspecialchars($tool['domain'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="sr-only">외부 서비스로 이동</span>
                </a>
            </article>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container split-intro">
        <h2 class="section-title">이용 전에 확인해 주세요</h2>
        <div class="notice-panel">
            <p><strong>AI 답변과 계산 결과는 참고용이며 법적 효력이 없습니다.</strong></p>
            <p>두 서비스는 현재 제작 중이므로 내용과 기능이 바뀔 수 있습니다. 긴급하거나 구체적인 권리구제가 필요하면 노동청, 법률구조기관 또는 노무 전문가에게 상담하세요.</p>
            <p>외부 서비스로 이동하면 각 서비스의 개인정보 처리방침과 이용약관이 적용됩니다.</p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
