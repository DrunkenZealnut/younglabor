<?php
require __DIR__.'/support/renewal.php';check(is_file(__DIR__.'/../includes/WorkPresenter.php'),'Work presenter required');require __DIR__.'/../includes/WorkPresenter.php';
$p=['status'=>'published','recruitment_status'=>'open','recruitment_mode'=>'rolling','deadline_at'=>null,'application_url'=>'https://zealot-survey.vercel.app/RJXag60aMfMT'];
foreach(['2026-10-16','2030-01-01'] as $t) check(programAcceptsApplications($p,new DateTimeImmutable($t)),'rolling remains open');
check(!programAcceptsApplications(array_replace($p,['recruitment_status'=>'closed'])),'explicit closure');
check(!programAcceptsApplications(array_replace($p,['status'=>'draft'])),'draft hidden');
check(!programAcceptsApplications(array_replace($p,['recruitment_status'=>'pending'])),'unverified unavailable');
check(!programAcceptsApplications(array_replace($p,['application_url'=>'javascript:alert(1)'])),'unsafe links rejected');
$d=array_replace($p,['recruitment_mode'=>'dated','deadline_at'=>'2026-10-15 23:59:59']);
check(programAcceptsApplications($d,new DateTimeImmutable('2026-10-15 23:59:59',new DateTimeZone('Asia/Seoul'))),'inclusive closing boundary');
check(!programAcceptsApplications($d,new DateTimeImmutable('2026-10-16 00:00:00',new DateTimeZone('Asia/Seoul'))),'closed after boundary');
echo "PASS rolling, explicit closure, deadline boundary, safe link\n";
