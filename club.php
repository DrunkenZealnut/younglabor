<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
require_once __DIR__ . '/includes/SiteContent.php';
PageTracker::track('바이브코딩동아리');

$club = siteClubRecruitment();
$clubOpen = clubRecruitmentIsOpen();
$clubName = htmlspecialchars($club['name'], ENT_QUOTES, 'UTF-8');
$deadlineLabel = htmlspecialchars(clubDeadlineLabel(), ENT_QUOTES, 'UTF-8');

$currentPage = 'club';
$pageTitle = $club['name'] . ($clubOpen ? ' 모집' : '') . ' - ' . $site['name'];
$pageDescription = $clubOpen
    ? '반도체고등학교 청소년과 함께 AI로 나만의 앱을 만드는 3개월. 코딩을 몰라도 참가할 수 있습니다.'
    : '반도체고등학교 청소년과 함께 AI로 나만의 앱을 만드는 ' . $club['name'] . ' 소식을 전합니다.';
$pageUrl = url('club');
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <div class="container">
        <a class="content-back" href="<?php echo url('/'); ?>#contact">← 함께하기</a>
        <p class="eyebrow">청소년 동아리</p>
        <h1><?php echo str_replace('동아리', '<wbr>동아리', $clubName); ?></h1>
        <p>나도 개발자! — 반도체고 청소년과 함께하는 3개월</p>
        <?php if ($clubOpen): ?>
            <p class="club-status">모집 중 · <?php echo $deadlineLabel; ?> 마감</p>
        <?php else: ?>
            <p class="club-status">모집 마감</p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">무엇을 하나요</h2>
        <div>
            <p>나에게 필요했지만 상상만 했던 앱을 직접 만들어요. 코딩을 몰라도 괜찮아요. AI에게 말로 설명하면서 만듭니다.</p>
            <ul class="club-examples">
                <li>나만의 운동관리</li>
                <li>해외축구 분석</li>
                <li>게임공략 챗봇</li>
                <li>자격증 대비 앱</li>
            </ul>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container split-intro">
        <h2 class="section-title">누가, 어떻게 참가하나요</h2>
        <dl class="club-facts">
            <div><dt>대상</dt><dd>반도체고등학교 재학생. 코딩 경험이 없어도 됩니다.</dd></div>
            <div><dt>기간</dt><dd>3개월</dd></div>
            <div><dt>지원</dt><dd>러버블(Lovable) 이용 지원, AI 전문가 자문</dd></div>
            <div><dt>계정</dt><dd>러버블 가입은 센터가 안내합니다. 만 18세 미만 참가자는 보호자 동의가 필요합니다.</dd></div>
        </dl>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">왜 센터가 하나요</h2>
        <div class="notice-panel">
            <p>AI 코딩은 학교가 따로 제공하기 어려운 경험입니다.</p>
            <p>필요한 도구를 스스로 만들다 보면 일하는 사람과 안전을 다시 보게 됩니다. 노동안전을 가르치는 대신, 학생이 만들며 익히는 동아리입니다.</p>
            <p>올해 첫 모델을 만들고, 내년에 더 많은 학교로 넓혀 가려 합니다.</p>
        </div>
    </div>
</section>

<section class="section section-alt" id="apply">
    <div class="container split-intro">
        <h2 class="section-title">신청하기</h2>
        <div class="notice-panel">
        <?php if ($clubOpen): ?>
            <p><strong><?php echo $deadlineLabel; ?>까지 신청을 받습니다.</strong> 신청자가 많으면 심사를 거쳐 선발하고, 결과는 신청서에 적은 휴대전화와 이메일로 알려드립니다.</p>
            <a class="tool-link" href="<?php echo htmlspecialchars($club['apply_url'], ENT_QUOTES, 'UTF-8'); ?>">
                신청서 작성하기
                <span aria-hidden="true">↗</span>
                <span class="tool-domain"><?php echo htmlspecialchars($club['apply_domain'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="sr-only">외부 서비스로 이동</span>
            </a>
            <p>신청은 센터가 따로 만든 신청 페이지에서 받으며, 그 페이지에 안내된 개인정보 처리 기준이 적용됩니다.</p>
        <?php else: ?>
            <p><strong>이번 모집은 마감되었습니다.</strong> 동아리 활동 소식은 활동게시판에서 전합니다.</p>
            <a class="btn-cta btn-secondary" href="<?php echo url('activity'); ?>">활동게시판 보기</a>
        <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">선생님께</h2>
        <div class="notice-panel">
            <p>동아리를 함께 지도해 주실 선생님을 찾습니다. 학교 일정에 맞춰 센터가 도구와 자문을 준비합니다.</p>
            <a class="btn-cta btn-secondary" href="<?php echo url('/'); ?>#contact">협력 문의하기</a>
        </div>
    </div>
    <div class="container">
        <div class="sponsor-box">
            <span>이 사업은</span>
            <img src="<?php echo url('assets/images/beautiful-foundation-ci.png'); ?>" width="524" height="126" loading="lazy" decoding="async" alt="아름다운재단">
            <span>지원으로 진행됩니다.</span>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
