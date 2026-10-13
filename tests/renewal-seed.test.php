<?php
require __DIR__.'/support/renewal.php';
check(is_file(__DIR__.'/../includes/RenewalSeed.php'),'Seed must exist');
require __DIR__.'/../includes/RenewalSeed.php';
$d=renewalSeedData();
check(count($d['areas'])===4,'four areas');
check($d['programs'][0]['slug']==='2026-youth-labor','course route');
check($d['programs'][0]['recruitment_status']==='pending','unverified course not open');
check($d['programs'][1]['recruitment_mode']==='rolling' && $d['programs'][1]['deadline_at']===null,'club never expires');
check(array_column($d['sessions'],'mode')===['online','online','online','hybrid'],'sessions 1-3 online; session 4 hybrid');
echo "PASS seed program conditions\n";
