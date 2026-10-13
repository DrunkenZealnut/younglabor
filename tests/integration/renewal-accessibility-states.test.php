<?php
require __DIR__.'/../support/renewal.php';
$p=renewalTestPdo();$base=getenv('SITE_BASE_URL')?:'http://127.0.0.1:18764/younglabor';
$club=$p->query("SELECT id,recruitment_status,recruitment_mode,deadline_at FROM work_programs WHERE slug='vibecoding-club'")->fetch();check((bool)$club,'club fixture required');
$q=$p->prepare('UPDATE work_programs SET recruitment_status=?,recruitment_mode=?,deadline_at=? WHERE id=?');
$future=(new DateTimeImmutable('now',new DateTimeZone('Asia/Seoul')))->modify('+30 days')->format('Y-m-d H:i:s');
try{
 foreach([['pending','rolling',null,'closed'],['open','rolling',null,'open'],['open','dated',$future,'open'],['closed','rolling',null,'closed']] as [$status,$mode,$deadline,$expected]){
  $q->execute([$status,$mode,$deadline,$club['id']]);
  $env=['SITE_BASE_URL'=>$base,'CLUB_EXPECT_STATE'=>$expected]+getenv();
  $child=proc_open(['node',dirname(__DIR__).'/public-accessibility.test.js'],[1=>['file','php://stdout','w'],2=>['file','php://stderr','w']],$pipes,null,$env);
  check(is_resource($child),'accessibility test starts');check(proc_close($child)===0,'accessibility state '.$status.'/'.$mode);
 }
}finally{$q->execute([$club['recruitment_status'],$club['recruitment_mode'],$club['deadline_at'],$club['id']]);}
echo "PASS accessibility: pending, rolling, dated, closed; original state restored\n";
