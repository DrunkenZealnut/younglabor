<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
require_once __DIR__ . '/includes/SiteContent.php';
PageTracker::track('단체소개');

$currentPage = 'about';
$pageTitle = '우리는 누구인가 - ' . $site['name'];
$pageDescription = '중소영세 제조업 청년노동자가 처음 일을 시작할 때부터 안전할 권리를 지키도록 곁에서 연결하고 기록합니다.';
$pageUrl = url('about');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <div class="container">
        <p class="eyebrow">우리는 누구인가</p>
        <h1>처음 일하는 청년 곁에 안전을 말할 사람을 만듭니다.</h1>
        <p>청년노동자인권센터는 중소영세 제조업 청년노동자와 함께하는 단체입니다.</p>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">왜 제조업 청년노동자인가</h2>
        <div>
            <p class="statement">첫 일터의 위험은 개인의 조심만으로 피할 수 없습니다.</p>
            <p>제조업에 처음 들어가는 청년은 공정과 화학물질, 보호구, 작업중지권을 충분히 배우기 어렵습니다. 작은 사업장일수록 물어볼 사람과 정보가 부족합니다. 센터는 사고가 난 뒤가 아니라 일을 시작하기 전부터 안전을 이해하고 말할 수 있게 돕습니다.</p>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container split-intro">
        <h2 class="section-title">왜 반도체고에서 시작하는가</h2>
        <div>
            <p class="statement">반도체고는 학교와 첨단 제조업의 첫 일터가 맞닿는 현장입니다.</p>
            <p>센터는 전국 6개 반도체고의 학생과 교사에게 다가가고 있습니다. 학교 방문, 교사 조직화, ‘일하는 열아홉’ 강좌와 캠페인을 통해 취업 전에 위험을 알아보는 경험을 만듭니다. 반도체 산업은 지금 가장 깊이 파고든 첫 현장이며, 여기서 배운 방법을 다른 제조업 청년에게 넓혀 갑니다.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">우리가 만들고 싶은 변화</h2>
        <div class="principle-grid">
            <article><strong>알 권리</strong><p>공정의 위험과 사용하는 물질을 이해합니다.</p></article>
            <article><strong>질문할 권리</strong><p>모르는 것을 묻고 안전한 방법을 요구합니다.</p></article>
            <article><strong>연결될 권리</strong><p>혼자 감당하지 않고 교사, 동료, 지역사회와 연결됩니다.</p></article>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">사람들</h2>
        <div class="people-grid">
            <article class="person-card"><div class="person-name"><?php echo htmlspecialchars($site['representative'], ENT_QUOTES, 'UTF-8'); ?></div><div class="person-role">대표</div><p class="person-desc">청소년노동인권 교육 경험을 바탕으로 현장과 교육을 잇습니다.</p></article>
            <article class="person-card"><div class="person-name">김동규</div><div class="person-role">운영위원</div><p class="person-desc">캠페인과 청소년 참여 활동을 맡습니다.</p></article>
            <article class="person-card"><div class="person-name">민경인</div><div class="person-role">운영위원</div><p class="person-desc">교육 프로그램을 기획합니다.</p></article>
            <article class="person-card"><div class="person-name">박동욱 · 신수현 · 이남신</div><div class="person-role">자문위원</div><p class="person-desc">산업안전, 특성화고, 지역 노동권의 경험을 보탭니다.</p></article>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">함께하는 곳들</h2>
        <div class="partner-list">
            <p>대구노동자권익센터</p>
            <p>충청노동인권센터</p>
            <p>더불어함의노동자권익센터</p>
            <?php foreach (siteSupportPartners() as $partner): ?>
                <p><?php echo htmlspecialchars($partner['program'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($partner['name'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($partner['relationship'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
