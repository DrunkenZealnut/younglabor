<?php
require __DIR__.'/support/renewal.php';
$base=getenv('SITE_BASE_URL')?:'http://127.0.0.1:18763/younglabor';
foreach(['admin','admin/','admin/work.php','admin/work-edit.php','admin/program-edit.php','admin/funding-edit.php'] as $path){$c=curl_init($base.'/'.$path);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false]);curl_exec($c);check(curl_getinfo($c,CURLINFO_RESPONSE_CODE)===302,'anonymous admin requires login: '.$path);curl_close($c);}
echo "PASS admin authentication gates\n";
