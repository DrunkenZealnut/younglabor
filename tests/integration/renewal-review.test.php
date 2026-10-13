<?php
require __DIR__.'/../support/renewal.php';require __DIR__.'/../../includes/WorkManager.php';
$p=renewalTestPdo();$r=new WorkRepository($p);$base=getenv('SITE_BASE_URL')?:'http://127.0.0.1:18764/younglabor';$mode=$argv[1]??'all';$club=$r->findProgram('vibecoding-club');$id=(int)$club['id'];
if(in_array($mode,['identity','all'],true)){
 $input=$r->findAdmin('program',$id);$input['slug']='other-club';$caught=false;
 try{(new WorkManager($p,$r))->save('program',$input,(int)$input['revision']);}catch(ContentValidationException $e){$caught=isset($e->errors()['slug']);}
 finally{$q=$p->prepare('UPDATE work_programs SET slug=? WHERE id=?');$q->execute(['vibecoding-club',$id]);}
 check($caught,'canonical club identity must not silently break');
}
if(in_array($mode,['home','all'],true)){
 $q=$p->prepare('UPDATE work_programs SET home_featured=0 WHERE id=?');$q->execute([$id]);
 try{check(!str_contains(file_get_contents($base.'/'),'href="'.$base.'/club"'),'home hidden program must be omitted');check(str_contains(file_get_contents($base.'/club'),'상시모집'),'detail stays public');}
 finally{$q=$p->prepare('UPDATE work_programs SET home_featured=? WHERE id=?');$q->execute([$club['home_featured'],$id]);}
}
if(in_array($mode,['funding','all'],true)){
 $html=file_get_contents($base.'/work/incubating/2026');check((bool)preg_match('/상시모집<\/span>[\s\S]*?<h3>바이브코딩동아리/',$html),'funding card shares rolling recruitment');
 $q=$p->prepare('UPDATE work_programs SET recruitment_status=? WHERE id=?');$q->execute(['closed',$id]);
 try{check(str_contains(file_get_contents($base.'/work/incubating/2026'),'접수 종료'),'funding shares explicit closure');check(!str_contains(file_get_contents($base.'/club'),'href="'.$club['application_url'].'"'),'closed detail removes application');}
 finally{$q->execute([$club['recruitment_status'],$id]);}
}
if($mode==='all'){
 $area=$r->findArea('education');$q=$p->prepare('UPDATE work_areas SET home_featured=0 WHERE id=?');$q->execute([$area['id']]);
 try{check(!str_contains(file_get_contents($base.'/'),'href="'.$base.'/work/areas/education"'),'home area selection respected');}
 finally{$q=$p->prepare('UPDATE work_areas SET home_featured=? WHERE id=?');$q->execute([$area['home_featured'],$area['id']]);}
 $other=$r->findAdmin('program',$id);unset($other['id']);$other['slug']='review-other-club';$other['title']='별도 동아리 검수';$other['application_url']='https://zealot-survey.vercel.app/srcqw8uVJpFR';
 $otherId=(new WorkManager($p,$r))->save('program',$other,null);
 try{$html=file_get_contents($base.'/work/programs/review-other-club');check(str_contains($html,'별도 동아리 검수') && str_contains($html,'href="'.$other['application_url'].'"'),'distinct club detail and application');}
 finally{$q=$p->prepare('DELETE FROM work_programs WHERE id=?');$q->execute([$otherId]);}
}
echo "PASS review regression: $mode\n";
