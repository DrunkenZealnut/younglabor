<?php
require __DIR__.'/../support/renewal.php';check(is_file(__DIR__.'/../../includes/WorkManager.php'),'Work manager required');require __DIR__.'/../../includes/WorkManager.php';
$p=renewalTestPdo();$r=new WorkRepository($p);$m=new WorkManager($p,$r);$row=$r->findProgram('2026-youth-labor',false);$row['sessions']=$r->sessions((int)$row['id'],false);$before=$row;
$bad=$row;$bad['title']='부분 저장 금지';$bad['sessions'][0]['mode']='invalid';
try{$m->save('program',$bad,(int)$row['revision']);throw new RuntimeException('validation failed to reject');}catch(ContentValidationException $e){}
check($r->findProgram('2026-youth-labor',false)['title']===$before['title'],'invalid sessions leave parent unchanged');
$m->save('program',$row,(int)$row['revision']);
try{$m->save('program',$row,(int)$row['revision']);throw new RuntimeException('conflict not caught');}catch(ContentConflictException $e){}
check(count($r->sessions((int)$row['id']))===4,'four sessions persist');
$row=$r->findProgram('2026-youth-labor',false);$row['sessions']=$r->sessions((int)$row['id'],false);$row['status']='draft';$m->save('program',$row,(int)$row['revision']);check($r->findProgram('2026-youth-labor')===null && $r->sessions((int)$row['id'])===[],'draft parent and children hidden');
$row=$r->findProgram('2026-youth-labor',false);$row['sessions']=$r->sessions((int)$row['id'],false);$row['status']='published';$m->save('program',$row,(int)$row['revision']);
echo "PASS work save, rollback, conflict, draft privacy\n";
