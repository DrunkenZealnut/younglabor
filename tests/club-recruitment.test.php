<?php
require_once __DIR__.'/../includes/SiteContent.php';
require __DIR__.'/support/renewal.php';
$club=['status'=>'published','recruitment_status'=>'open','recruitment_mode'=>'rolling','application_url'=>'https://zealot-survey.vercel.app/RJXag60aMfMT','deadline_at'=>null];
foreach(['2026-10-16','2030-01-01'] as $date)check(clubRecruitmentIsOpen(new DateTimeImmutable($date),$club),'rolling recruitment stays open');
$club['recruitment_status']='closed';check(!clubRecruitmentIsOpen(null,$club),'operator closure stops applications');
$club['recruitment_status']='open';$club['status']='draft';check(!clubRecruitmentIsOpen(null,$club),'draft cannot recruit');
$unavailable=siteClubRecruitment(static function(){throw new RuntimeException('offline');});check(!clubRecruitmentIsOpen(null,$unavailable),'unavailable data cannot recruit');
echo "PASS rolling club state and unavailable fallback\n";
