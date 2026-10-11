<?php
require __DIR__.'/support/renewal.php';check(is_file(__DIR__.'/../includes/PublicContent.php'),'Public loader required');require __DIR__.'/../includes/PublicContent.php';
$x=loadPublicSection(static fn()=>throw new RuntimeException('private database details'),'board',[],1);
check($x['unavailable'] && $x['rows']===[],'failure safely distinguished');
$x=loadPublicSection(static fn()=>new class {function countPublicSection($s,$f){return 0;}function listPublicSection($s,$f,$l,$o){return [];}},'board',[],999);
check(!$x['unavailable'] && $x['page']===1,'empty result is not an outage');
echo "PASS public loader empty/failure distinction\n";
