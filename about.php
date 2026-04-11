<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
PageTracker::track('단체소개');

$currentPage = 'about';
$pageTitle = '단체소개 - ' . $site['name'];
$pageDescription = '반도체산업 청년노동자의 노동안전보건을 위한 전문 단체, ' . $site['name'];
require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <div class="container">
            <h1>단체소개</h1>
            <p>반도체산업 청년노동자에게 안전할 권리를</p>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <h2 class="section-title fade-in">문제정의</h2>
            <div class="section-subtitle fade-in">
                <p>전국에 생겨나고 있는 반도체고교를 졸업하고 반도체회사에 취업하는 청년노동자들. 노동운동도, 기후환경운동도 이들을 위한 노동안전보건 보호장치에는 미처 관심을 갖기 어려운 상황입니다.</p>
                <br>
                <p>현장실습 중 사망사고가 발생했을 때만 잠깐 주목받는 존재들. 누군가는 관심을 갖고 노동안전보건교육을 받게 하고, 졸업해서는 위험인자를 인식하고 일할 수 있도록 해야 합니다.</p>
                <br>
                <p>최소한 건강하게 일할 권리는 우리 사회가 보장해줘야 합니다.</p>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <h2 class="section-title fade-in">설립목적</h2>
            <div class="card-grid">
                <div class="card fade-in">
                    <div class="card-number">1</div>
                    <h3 class="card-title">교육환경 조성</h3>
                    <p class="card-desc">반도체고를 비롯한 직업계고의 모든 청소년들이 노동안전보건교육을 받을 수 있는 교육환경을 조성합니다.</p>
                </div>
                <div class="card fade-in">
                    <div class="card-number">2</div>
                    <h3 class="card-title">안전할 권리 지원</h3>
                    <p class="card-desc">일하는 청년노동자들이 스스로 안전할 권리를 요구할 수 있도록 지원합니다.</p>
                </div>
                <div class="card fade-in">
                    <div class="card-number">3</div>
                    <h3 class="card-title">사회 변화</h3>
                    <p class="card-desc">일하는 청년노동자들이 안전하게 일할 수 있는 사회로의 변화를 만들어갑니다.</p>
                </div>
                <div class="card fade-in">
                    <div class="card-number">4</div>
                    <h3 class="card-title">사회적 자원 연계</h3>
                    <p class="card-desc">청년노동자들의 일과 일상의 고민을 해결하는 데 필요한 사회적 자원을 연계합니다.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title fade-in">단체 차별성</h2>
            <div class="section-subtitle fade-in">
                <p>반올림이 산재피해자 및 현장의 노동권침해 문제에 집중한다면, <?php echo htmlspecialchars($site['name']); ?>는 <strong>노동안전보건교육과 사전 예방</strong>에 집중합니다.</p>
                <br>
                <p>반올림이 축적해온 반도체 관련 전문지식과, 대표의 청소년노동인권 교육 경험을 더해 '반도체고 노동인권교육 콘텐츠' 개발을 협의하고 있습니다.</p>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <h2 class="section-title fade-in">사람들</h2>

            <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 1.5rem;" class="fade-in">대표</h3>
            <div class="people-grid fade-in" style="margin-bottom: 2.5rem;">
                <div class="person-card">
                    <div class="person-name"><?php echo htmlspecialchars($site['representative']); ?></div>
                    <div class="person-role">대표</div>
                    <div class="person-desc">청소년노동인권 교육 경험을 바탕으로 센터를 이끌고 있습니다.</div>
                </div>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 1.5rem;" class="fade-in">운영위원</h3>
            <div class="people-grid fade-in" style="margin-bottom: 2.5rem;">
                <div class="person-card">
                    <div class="person-name">김동규</div>
                    <div class="person-role">운영위원</div>
                    <div class="person-desc">캠페인 및 청소년참견위원회 담당</div>
                </div>
                <div class="person-card">
                    <div class="person-name">민경인</div>
                    <div class="person-role">운영위원</div>
                    <div class="person-desc">교육 프로그램 기획 담당</div>
                </div>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 1.5rem;" class="fade-in">자문위원</h3>
            <div class="people-grid fade-in" style="margin-bottom: 2.5rem;">
                <div class="person-card">
                    <div class="person-name">박동욱</div>
                    <div class="person-role">자문위원 (교수)</div>
                    <div class="person-desc">반도체 산업 노동안전보건 전문가. 교과서 연구 자문</div>
                </div>
                <div class="person-card">
                    <div class="person-name">신수현</div>
                    <div class="person-role">자문위원</div>
                    <div class="person-desc">특성화고 노조 활동. 학교 캠페인 자문</div>
                </div>
                <div class="person-card">
                    <div class="person-name">이남신</div>
                    <div class="person-role">자문위원 (경북노동인권센터 소장)</div>
                    <div class="person-desc">경북 지역 연계 자문</div>
                </div>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 1.5rem;" class="fade-in">협력 단체</h3>
            <div class="people-grid fade-in">
                <div class="person-card">
                    <div class="person-name">대구노동자권익센터</div>
                    <div class="person-desc">대구 지역 협력</div>
                </div>
                <div class="person-card">
                    <div class="person-name">충청노동인권센터</div>
                    <div class="person-desc">충청 지역 협력</div>
                </div>
                <div class="person-card">
                    <div class="person-name">더불어함의노동자권익센터</div>
                    <div class="person-desc">지역 협력</div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
