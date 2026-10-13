<?php
require_once __DIR__.'/../includes/PublicCards.php';$slug=requestText('slug');
$r=loadPublicWork(static fn($repo)=>$repo->findArea($slug));publicNotFoundStatus($r);$a=$r['data'];
startPublicPage('work',$a['title']??'사업 안내',$a['summary']??'사업 안내','work/areas/'.$slug);
if(!$a){renderUnavailableDetail($r);endPublicPage();exit;}
renderPageIntro('네 가지 사업',$a['title'],$a['summary']);$related=loadPublicSection('publicRepository','board',['area'=>$a['id']],1,3);$programs=loadPublicWork(static fn($repo)=>array_values(array_filter($repo->listPrograms(),static fn($p)=>(int)$p['work_area_id']===(int)$a['id'])));
?>
<section class="section"><div class="container content-reading"><a class="content-back" href="<?php echo publicEscape(url('work')); ?>">← 전체 사업</a><div class="detail-state"><span class="state-label"><?php echo publicEscape(workPhaseLabel($a['phase'])); ?></span></div><h2>누구의 곁에서</h2><p class="statement"><?php echo publicEscape($a['audience']); ?></p><div class="plain-content"><?php echo renderPlainText($a['body']??''); ?></div><h2>함께 준비하는 것</h2><div class="plain-content"><?php echo renderPlainText($a['provision']??''); ?></div>
<?php if($action=workAction($a)): ?><a class="btn-cta" href="<?php echo publicEscape($action['href']); ?>"><?php echo publicEscape($action['label']); ?></a><?php else: ?><a class="btn-cta btn-secondary" href="<?php echo publicEscape(url('').'#contact'); ?>">교육·협력 문의</a><?php endif; ?>
</div></section>
<?php if($programs['data']): ?><section class="section section-alt"><div class="container"><h2 class="section-title">연결되는 프로그램</h2><div class="program-grid"><?php foreach($programs['data'] as $p)renderProgramCard($p); ?></div></div></section><?php endif; ?>
<?php if($related['rows']): ?><section class="section"><div class="container"><h2 class="section-title">관련 소식</h2><div class="content-grid"><?php foreach($related['rows'] as $p)renderPublicCard($p); ?></div></div></section><?php endif; ?>
<?php endPublicPage(); ?>
