<?php
require_once __DIR__.'/auth.php';require_once __DIR__.'/../includes/WorkRepository.php';
$error='';$groups=[];
try{$repo=new WorkRepository(Database::getInstance()->getConnection());foreach(['area'=>'사업축','unit'=>'지원사업 단위사업','funding'=>'지원사업','program'=>'프로그램'] as $kind=>$label)$groups[$kind]=['label'=>$label,'rows'=>$repo->listEntities($kind,false)];}catch(Throwable $e){$error='사업 정보를 불러오지 못했습니다. 이관 적용 상태를 확인해 주세요.';}
adminHeader();
?>
<div class="main-header"><h1>사업·프로그램 관리</h1></div>
<?php if($error): ?><div class="card"><?php echo e($error); ?></div><?php endif; ?>
<?php foreach($groups as $kind=>$group): $editor=$kind==='program'?'program-edit':($kind==='funding'?'funding-edit':'work-edit'); ?>
<section class="card" style="margin-bottom:24px"><div class="main-header"><h2><?php echo e($group['label']); ?></h2><a class="btn btn-primary" href="<?php echo e(url('admin/'.$editor.'.php?kind='.$kind)); ?>">새 항목</a></div>
<div style="overflow-x:auto"><table class="data-table"><thead><tr><th>제목</th><th>공개</th><th>운영·접수 상태</th><th>수정</th></tr></thead><tbody>
<?php foreach($group['rows'] as $row): ?><tr><td><?php echo e($row['title']); ?></td><td><?php echo $row['status']==='published'?'공개':'임시저장'; ?></td><td><?php echo e($kind==='program'?programStateLabel($repo->findProgram($row['slug'],false)): (isset($row['phase'])?workPhaseLabel($row['phase']):'')); ?></td><td><a class="btn btn-outline" href="<?php echo e(url('admin/'.$editor.'.php?kind='.$kind.'&id='.(int)$row['id'])); ?>">수정</a></td></tr><?php endforeach; ?>
</tbody></table></div></section><?php endforeach; ?>
<?php adminFooter(); ?>
