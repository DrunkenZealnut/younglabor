<?php
require __DIR__.'/support/renewal.php';check(is_file(__DIR__.'/../includes/WorkValidation.php'),'Work validation required');require __DIR__.'/../includes/WorkValidation.php';require __DIR__.'/../includes/RenewalSeed.php';
$p=renewalSeedData()['programs'][0];$p['sessions']=renewalSeedData()['sessions'];
check(validateWorkInput('program',$p)['errors']===[],'course valid');
$p['sessions'][1]['number']=1;check(isset(validateWorkInput('program',$p)['errors']['sessions']),'duplicate session rejected');
$p['title']=['bad'];check(isset(validateWorkInput('program',$p)['errors']['title']),'array rejected without warning');
$p['recruitment_status']='unknown';check(isset(validateWorkInput('program',$p)['errors']['recruitment_status']),'unknown status');
$a=renewalSeedData()['areas'][0];$a['action_path']='//evil.example';check(isset(validateWorkInput('area',$a)['errors']['action_path']),'protocol-relative path rejected');
echo "PASS work validation\n";
