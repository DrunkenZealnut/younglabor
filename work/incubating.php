<?php
require_once __DIR__.'/../includes/PublicCards.php';$year=filter_var(requestText('year','2026'),FILTER_VALIDATE_INT)?:0;
$r=loadPublicWork(static function($repo)use($year){$f=$repo->findFunding($year);if(!$f)return null;$f['units']=$repo->fundingUnits((int)$f['id']);$f['course']=$repo->findProgram('2026-youth-labor');return $f;});publicNotFoundStatus($r);$f=$r['data'];
startPublicPage('work',$f['title']??'지원사업 안내',$f['summary']??'지원사업 안내','work/incubating/'.$year);
if(!$f){renderUnavailableDetail($r);endPublicPage();exit;}
renderPageIntro('사업소개 · '.$f['year'].'년 · '.$f['term'].'년차',$f['title'],$f['summary']);
?>
<section class="section work-section"><div class="container"><?php renderWorkTabs('funding'); ?><div class="funding-intro"><div class="plain-content"><?php echo renderPlainText($f['body']??''); ?></div><p class="sponsor-box"><span>지원</span><img src="<?php echo publicEscape(assetUrl('assets/images/beautiful-foundation-ci.png')); ?>" width="524" height="126" alt="<?php echo publicEscape($f['sponsor']); ?>"></p></div>
<div class="section-heading"><h2>네 가지 단위사업</h2><p>연구에서 학습도구, 학교와 학생의 경험으로 이어집니다.</p></div><div class="funding-grid">
<?php foreach($f['units'] as $i=>$u): ?><article class="unit-card"><div class="work-card-top"><span class="work-number"><?php echo sprintf('%02d',$i+1); ?></span><span class="state-label"><?php echo publicEscape(workPhaseLabel($u['phase'])); ?></span></div><h3><?php echo publicEscape($u['title']); ?></h3><?php if(!empty($u['registered_name']) && $u['registered_name']!==$u['title']): ?><p class="registered-name">사업 등록 명칭: <?php echo publicEscape($u['registered_name']); ?></p><?php endif; ?><strong><?php echo publicEscape($u['summary']); ?></strong><p><?php echo publicEscape($u['body']); ?></p><?php if($act=workAction($u)): ?><a class="arrow-link" href="<?php echo publicEscape($act['href']); ?>"><?php echo publicEscape($act['label']); ?> ↗</a><?php endif; ?></article><?php endforeach; ?>
</div>
<?php if($f['course']): ?><div class="program-section"><h2 class="section-title">연구를 함께 나누는 교육</h2><?php renderProgramCard($f['course']); ?></div><?php endif; ?>
<div class="collaboration-panel"><h2>학교와 지역에서 함께</h2><p>학교와 지역의 조건에 맞게 교육·캠페인·현장 청년노동자의 목소리를 연결할 협력 방식을 모색합니다.</p><a class="btn-cta" href="<?php echo publicEscape(url('').'#contact'); ?>">학교·지역 협력 문의 ↗</a></div>
<div class="work-resource-links"><a href="<?php echo publicEscape(url('resources')); ?>">공개 자료 살펴보기 ↗</a><a href="<?php echo publicEscape(url('board')); ?>">활동 소식 보기 ↗</a></div>
</div></section>
<?php endPublicPage(); ?>
