<?php
require __DIR__.'/support/renewal.php';require __DIR__.'/../includes/RenewalSeed.php';require __DIR__.'/../includes/WorkValidation.php';
$input=renewalSeedData()['areas'][0];$input['body']=str_repeat('가',22000);check(isset(validateWorkInput('area',$input)['errors']['body']),'accepted bodies must fit TEXT byte capacity');
echo "PASS work body storage limit\n";
