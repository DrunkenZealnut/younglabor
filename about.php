<?php
require_once __DIR__.'/includes/PublicContent.php';
startPublicPage('about','단체소개','중소·영세 제조현장 청년의 노동권과 안전을 함께 고민하고 바꿔갑니다.','about');
renderPageIntro('','일하는 청년의 곁에서,
함께 바꿀 힘을 만듭니다.','중소·영세 제조현장 청년의 노동권과 안전을 함께 고민하고 바꿔갑니다.');
?>
<section class="section"><div class="container split-intro"><h2 class="section-title section-title-lg">우리는 청쎈입니다</h2><div class="plain-content"><p class="statement">청쎈은 청년노동자인권센터를 부르는 짧은 이름입니다.</p><p>작은 제조현장에서 일하는 청년, 그리고 그 일터로 진입하는 직업계고 학생과 졸업생의 일상과 고민을 함께합니다. 안전하게 일할 권리를 배우고 필요한 사람과 연결되며 자신의 목소리를 사회에 전할 수 있도록 활동합니다.</p></div></div></section>
<section class="section section-alt"><div class="container split-intro"><h2 class="section-title section-title-lg">왜 제조현장 청년인가</h2><div class="plain-content"><p class="statement">잘 드러나지 않는 노동에도,<br>분명한 권리가 있습니다.</p><p>청년의 노동을 이야기할 때 작은 공장과 지역 일터의 경험은 쉽게 가려집니다. 처음 일하는 청년이 위험을 알아보고 질문할 수 있도록 현장과 교육, 도구와 연구를 연결합니다.</p><p>학교에서 배운 것과 실제 일터의 조건 사이를 함께 살펴보며, 개인이 혼자 감당하기 어려운 고민을 사회의 과제로 만듭니다.</p></div></div></section>
<section class="section"><div class="container"><div class="section-heading"><h2>함께하는 원칙</h2></div><div class="principle-grid"><article><strong>당사자의 주체성</strong><p>청년을 도움받는 사람으로만 보지 않습니다. 질문하고 배우고 직접 만드는 경험을 함께합니다.</p></article><article><strong>실제로 쓸 수 있는 것</strong><p>학교와 일터에서 활용할 교육, 자료와 도구를 준비하고 제공 범위를 분명히 안내합니다.</p></article><article><strong>함께 바꿀 관계</strong><p>청년·교사·동료·지역의 사람들이 만나 경험과 질문을 나누고 변화를 모색합니다.</p></article></div></div></section>
<section class="section about-next"><div class="container split-intro"><h2>만남에서 시작합니다.</h2><div><p>교육·캠페인·동아리와 학교·지역 협력에 함께할 분들의 문의를 기다립니다.</p><div class="hero-actions"><a class="btn-cta" href="<?php echo publicEscape(url('work')); ?>">청쎈의 사업 보기 ↗</a><a class="btn-cta btn-secondary" href="<?php echo publicEscape(url('').'#contact'); ?>">협력 문의하기</a></div><p class="about-contact">대표 · <?php echo publicEscape($site['representative']); ?><br>연락 · <a href="mailto:admin@younglabor.kr">admin@younglabor.kr</a></p></div></div></section>
<?php endPublicPage(); ?>
