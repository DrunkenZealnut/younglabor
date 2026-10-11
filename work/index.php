<?php
require_once __DIR__.'/../includes/PublicCards.php';
$r=loadPublicWork(static fn($repo)=>['areas'=>$repo->listAreas(),'programs'=>$repo->listPrograms()]);
startPublicPage('work','사업소개','학교에서 일터로, 교육과 도구, 사람과 목소리를 연결합니다.','work');
renderPageIntro('청쎈이 하는 일','권리는 배우고, 만들고,
함께할 때 자랍니다.','학교에서 일터로, 교육과 도구, 사람과 목소리를 연결합니다.');
?>
<section class="section work-section"><div class="container"><?php renderWorkTabs('all'); ?>
<?php if($r['state']!=='ready'): ?><p class="content-empty"><?php echo publicEscape(workUnavailableMessage($r['state'])); ?></p>
<?php else: ?><div class="section-heading"><h2>네 가지 사업</h2><p>각자의 일상에서 출발해 함께 바꿀 방법을 찾습니다.</p></div><div class="work-grid"><?php foreach($r['data']['areas'] as $i=>$a)renderWorkCard($a,$i); ?></div>
<section id="programs" class="program-section"><div class="section-heading"><h2>프로그램과 참여</h2><p>일정과 참여 방법은 상세 안내에서 확인하세요.</p></div><div class="program-grid"><?php foreach($r['data']['programs'] as $p)renderProgramCard($p); ?></div><?php if($r['data']['programs']===[]): ?><p class="content-empty">현재 공개된 프로그램이 없습니다.</p><?php endif; ?></section>
<div class="work-resource-links"><a href="<?php echo publicEscape(url('resources')); ?>">공개 자료 보기 ↗</a><a href="<?php echo publicEscape(url('tools')); ?>">온라인 도구 안내 ↗</a><a href="<?php echo publicEscape(url('').'#contact'); ?>">교육·협력 문의 ↗</a></div>
<?php endif; ?></div></section>
<?php endPublicPage(); ?>
