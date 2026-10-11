<?php
require_once __DIR__.'/auth.php';require_once __DIR__.'/../includes/WorkRepository.php';
$metrics=[];$programs=[];$error='';
try{$pdo=Database::getInstance()->getConnection();$r=new WorkRepository($pdo);$programs=$r->listPrograms();$metrics=['공개 콘텐츠'=>(int)$pdo->query("SELECT COUNT(*) FROM content_posts WHERE status='published'")->fetchColumn(),'접수 중 프로그램'=>count(array_filter($programs,'programAcceptsApplications')),'미읽음 문의'=>getUnreadContactCount(),'전체 문의'=>(int)$pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn()];}catch(Throwable $e){$error='일부 통계를 불러오지 못했습니다. 이관 적용 상태를 확인해 주세요.';}
adminHeader();
?>
<div class="main-header"><h1>대시보드</h1></div>
<?php if($error): ?><p class="card"><?php echo e($error); ?></p><?php endif; ?>
<div class="stats-grid"><?php foreach($metrics as $label=>$count): ?><div class="stat-card"><div class="stat-label"><?php echo e($label); ?></div><div class="stat-value"><?php echo (int)$count; ?></div></div><?php endforeach; ?></div>
<section class="card"><div class="main-header"><h2>프로그램 접수 안내</h2><a href="<?php echo e(url('admin/work.php')); ?>">사업·프로그램 관리</a></div>
<?php foreach($programs as $p): ?><p style="margin-bottom:16px"><strong><?php echo e($p['title']); ?></strong> · <?php echo e(programStateLabel($p)); ?></p><?php endforeach; ?>
<p>외부 신청 페이지의 실제 접수 내역은 해당 신청 도구에서 확인합니다.</p></section>
<?php adminFooter(); ?>
