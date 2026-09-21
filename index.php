<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
require_once __DIR__ . '/includes/ContentRepository.php';
require_once __DIR__ . '/includes/SiteContent.php';
require_once __DIR__ . '/includes/HomeContent.php';
PageTracker::track('메인페이지');

$homeContent = loadHomeContent(static function () {
    return new ContentRepository(Database::getInstance()->getConnection());
});
$currentPage = 'home';
$pageTitle = $site['name'] . ' - 중소영세 제조업 청년노동자의 안전할 권리';
$pageDescription = '중소영세 제조업 청년노동자가 처음 일을 시작할 때부터 안전할 권리를 지킬 수 있도록 현장, 교육, 도구와 연구를 잇습니다.';
$pageUrl = url('');
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <p class="eyebrow">청년노동자인권센터</p>
        <h1 class="hero-title">중소영세 제조업 청년노동자와 함께, 처음 일하는 몸을 지킵니다.</h1>
        <p class="hero-lead">학교에서 일터로 향하는 청년 곁에서 위험을 알아보는 힘, 질문할 사람, 실제로 쓸 수 있는 안전 정보를 만듭니다.</p>
        <div class="hero-actions">
            <a href="<?php echo url('about'); ?>" class="btn-cta">센터 알아보기</a>
            <a href="<?php echo url('activity'); ?>" class="btn-cta btn-secondary">현장 활동 보기</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">현장에서 무슨 일이 있었나</h2>
        <p class="section-subtitle">학생과 교사를 만나고, 안전을 이야기하고, 다음 행동을 기록합니다.</p>
        <?php if ($homeContent['unavailable']): ?>
            <p class="content-empty">최근 소식을 불러오지 못했습니다. 각 게시판에서 다시 확인해 주세요.</p>
        <?php elseif ($homeContent['activity'] === []): ?>
            <p class="content-empty">첫 현장 기록을 준비하고 있습니다.</p>
        <?php else: ?>
            <div class="content-grid">
            <?php foreach ($homeContent['activity'] as $item): ?>
                <article class="content-card">
                    <div class="content-meta"><time datetime="<?php echo htmlspecialchars($item['content_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['content_date'], ENT_QUOTES, 'UTF-8'); ?></time></div>
                    <h3><a href="<?php echo htmlspecialchars(url('activity/' . $item['slug']), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h3>
                    <p><?php echo htmlspecialchars((string)$item['summary'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a class="section-link" href="<?php echo url('activity'); ?>">활동게시판 전체 보기 →</a>
    </div>
</section>

<section class="section section-alt">
    <div class="container split-intro">
        <h2 class="section-title">왜 이 일을 하는가</h2>
        <div>
            <p class="statement">처음 취업한 청년은 공정의 위험과 몸의 변화를 알아차리기 어렵습니다.</p>
            <p>중소영세 제조업에서는 안전 정보를 충분히 배우고 질문할 기회가 더 적습니다. 센터는 사고가 난 뒤의 관심에 머물지 않고, 학교와 첫 일터 사이에서 예방의 기반을 만듭니다.</p>
            <a class="section-link" href="<?php echo url('about'); ?>">센터가 시작된 이유 →</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">우리가 하는 일</h2>
        <p class="section-subtitle">현장에서 관계를 만들고, 배움을 돕고, 도구와 근거를 남깁니다.</p>
        <div class="work-grid">
        <?php foreach (siteWorkAreas() as $index => $area): ?>
            <article class="work-card">
                <div class="work-card-block"><span class="work-card-num"><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span></div>
                <div class="work-card-meta"><span class="work-card-title"><?php echo htmlspecialchars($area['title'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <p class="work-card-desc"><?php echo htmlspecialchars($area['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
        <?php endforeach; ?>
        </div>
        <a class="section-link" href="<?php echo url('activities'); ?>">활동 방식 자세히 보기 →</a>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">현장에서 쓰는 안전 도구</h2>
        <p class="section-subtitle">복잡한 산업안전과 노동 정보를 시민이 직접 확인할 수 있게 만듭니다.</p>
        <div class="tool-grid compact">
        <?php foreach (siteTools() as $tool): ?>
            <article class="tool-card">
                <span class="tool-status"><?php echo htmlspecialchars($tool['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                <h3><?php echo htmlspecialchars($tool['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($tool['tagline'], ENT_QUOTES, 'UTF-8'); ?></p>
                <a class="tool-link" href="<?php echo htmlspecialchars($tool['url'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tool['cta'], ENT_QUOTES, 'UTF-8'); ?> ↗</a>
            </article>
        <?php endforeach; ?>
        </div>
        <a class="section-link" href="<?php echo url('tools'); ?>">도구와 이용 안내 보기 →</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">언론이 본 현장</h2>
        <?php if (!$homeContent['unavailable'] && $homeContent['press'] !== []): ?>
            <div class="content-list">
            <?php foreach ($homeContent['press'] as $item): ?>
                <article class="content-list-item">
                    <div class="content-meta"><span><?php echo htmlspecialchars((string)$item['outlet'], ENT_QUOTES, 'UTF-8'); ?></span><time datetime="<?php echo htmlspecialchars($item['content_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($item['content_date'], ENT_QUOTES, 'UTF-8'); ?></time></div>
                    <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars((string)$item['summary'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="content-empty"><?php echo $homeContent['unavailable'] ? '최근 소식을 불러오지 못했습니다. 각 게시판에서 다시 확인해 주세요.' : '관련 보도를 준비하고 있습니다.'; ?></p>
        <?php endif; ?>
        <a class="section-link" href="<?php echo url('press'); ?>">언론보도 전체 보기 →</a>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">숫자로 보는 활동</h2>
        <div class="impact-grid">
        <?php foreach (siteImpactStats() as $stat): ?>
            <div class="impact-stat"><strong><?php echo htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8'); ?></strong><span><?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?></span></div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="contact">
    <div class="container">
        <h2 class="section-title">함께하기</h2>
        <div class="contact-content">
            <div class="contact-info">
                <h3>현장의 이야기, 교육과 협력 제안을 기다립니다.</h3>
                <p>청년노동자의 안전한 첫 일터를 함께 만들고 싶다면 연락해 주세요.</p>
                <?php if ($site['email'] !== ''): ?>
                    <p class="contact-item"><a href="mailto:<?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?></a></p>
                <?php else: ?>
                    <p class="contact-item">문의 폼으로 연락해 주세요.</p>
                <?php endif; ?>
                <a class="btn-cta btn-secondary" href="<?php echo url('committee'); ?>">청소년 동아리 신청</a>
            </div>
            <div class="contact-form">
                <form action="<?php echo url('api/contact.php'); ?>" method="post" onsubmit="return handleSubmit(event)">
                    <div class="form-group"><label for="name">이름</label><input type="text" id="name" name="name" required maxlength="50"></div>
                    <div class="form-group"><label for="email">이메일</label><input type="email" id="email" name="email" required maxlength="100"></div>
                    <div class="form-group"><label for="message">메시지</label><textarea id="message" name="message" required maxlength="3000"></textarea></div>
                    <button type="submit" class="btn-submit">문의하기</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">함께하는 곳들</h2>
        <?php foreach (siteSupportPartners() as $partner): ?>
            <p class="partner-credit"><?php echo htmlspecialchars($partner['program'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($partner['name'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($partner['relationship'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endforeach; ?>
    </div>
</section>

<script>
async function handleSubmit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(Object.fromEntries(new FormData(form)))
        });
        const result = await response.json();
        alert(result.message);
        if (result.success) form.reset();
    } catch (error) {
        alert('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
    } finally {
        button.disabled = false;
    }
    return false;
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
