<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
PageTracker::track('사업소개');

$currentPage = 'activities';
$pageTitle = '사업소개 - ' . $site['name'];
$pageDescription = '노동안전보건 교과서, 앱, 청소년노동안전동아리, 학교 캠페인 - ' . $site['name'];
require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <div class="container">
            <?php require __DIR__ . '/includes/symbols.php'; ?>
            <h1>사업소개</h1>
            <p>4대 핵심사업으로 청년노동자의 안전한 일터를 만들어갑니다</p>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="work-grid">
                <div class="work-card fade-in">
                    <div class="work-card-block"><span class="work-card-num">01</span></div>
                    <div class="work-card-meta">
                        <span class="work-card-label">사업 01</span>
                        <span class="work-card-title">노동안전보건 교과서</span>
                    </div>
                    <p class="work-card-desc">반도체고 학생들이 졸업 전에 반드시 알아야 할 노동안전보건 지식을 담은 교과서를 개발합니다. 현장 인터뷰와 전문가 연구를 거쳐, 인정교과서로 학교에 채택되는 것을 목표로 합니다.</p>
                </div>
                <div class="work-card fade-in">
                    <div class="work-card-block"><span class="work-card-num">02</span></div>
                    <div class="work-card-meta">
                        <span class="work-card-label">사업 02</span>
                        <span class="work-card-title">노동안전보건 앱</span>
                    </div>
                    <p class="work-card-desc">교과서가 정적이라면, 앱은 동적으로 최신 안전정보를 제공합니다. 학교뿐 아니라 산업현장에서도 활용할 수 있는 도구로, 위험인자 데이터베이스를 지속적으로 업데이트합니다.</p>
                </div>
                <div class="work-card fade-in">
                    <div class="work-card-block"><span class="work-card-num">03</span></div>
                    <div class="work-card-meta">
                        <span class="work-card-label">사업 03</span>
                        <span class="work-card-title">청소년노동안전동아리</span>
                    </div>
                    <p class="work-card-desc">반도체고 학생들이 직접 참여하는 동아리입니다. 안전 관련 자격증 공부, 학내 캠페인, 동료 학생들에게 노동안전을 알리는 활동을 합니다.</p>
                </div>
                <div class="work-card fade-in">
                    <div class="work-card-block"><span class="work-card-num">04</span></div>
                    <div class="work-card-meta">
                        <span class="work-card-label">사업 04</span>
                        <span class="work-card-title">학교 캠페인</span>
                    </div>
                    <p class="work-card-desc">전국 5개 반도체고교를 방문하여 학생들과 관계를 만들어갑니다. 입학식, 졸업식, 축제 등 학교 일정에 맞춰 세심하게 준비합니다.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <h2 class="section-title fade-in">3개년 로드맵 (2026-2028)</h2>
            <div class="timeline fade-in">
                <div class="timeline-item">
                    <div class="timeline-year">1차년도 (2026)</div>
                    <div class="timeline-desc">
                        노동안전보건교과서 사전 연구 및 연구팀 구성<br>
                        노동안전보건 앱 최소기능(MVP) 제작<br>
                        5개 반도체고교 캠페인으로 학생들과 관계 형성<br>
                        청소년노동안전동아리 모델링
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2차년도 (2027)</div>
                    <div class="timeline-desc">
                        노동안전보건교과서 제작<br>
                        노동안전보건 앱 정식 제작<br>
                        노동안전보건교안 제작 및 교육 시작<br>
                        동아리 학내 등록 추진
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">3차년도 (2028)</div>
                    <div class="timeline-desc">
                        동아리 질적 전환<br>
                        인정교과서 신청 (학교 협력)<br>
                        노동안전보건 앱 현장 확산<br>
                        노동안전보건교육 법제화 여론 형성
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="text-align: center;">
        <div class="container">
            <h2 class="section-title fade-in">함께하고 싶으시다면</h2>
            <p class="section-subtitle fade-in">청소년노동안전동아리에 참여하거나, 센터의 활동에 관심이 있으시다면 연락해주세요.</p>
            <div class="fade-in" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo url('committee'); ?>" class="btn-cta">동아리 신청하기</a>
                <a href="mailto:<?php echo htmlspecialchars($site['email']); ?>" class="btn-cta btn-secondary">이메일 보내기</a>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
