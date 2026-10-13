<?php
require __DIR__.'/support/renewal.php';check(is_file(__DIR__.'/../includes/ContactService.php'),'Contact service required');require __DIR__.'/../includes/ContactService.php';
$input=['inquiry_type'=>'general','email'=>'person@example.org','message'=>'문의','name'=>''];
$s=new ContactService(static fn($v)=>false,static fn($v)=>throw new RuntimeException('must not mail'));check(!$s->submit($input)['success'],'save failure never reports success');
$s=new ContactService(static fn($v)=>true,static fn($v)=>false);check($s->submit($input)['success'],'mail failure after saved inquiry is received');
check(!$s->submit(array_replace($input,['email'=>['evil']]))['success'],'array email rejected');
check(!$s->submit(array_replace($input,['message'=>str_repeat('가',3001)]))['success'],'maximum length enforced');
check(!$s->submit(array_replace($input,['inquiry_type'=>'bad']))['success'],'invalid category rejected');
check($s->submit($input)['success'],'anonymous inquiry accepted');
echo "PASS inquiry validation and separate save/notification results\n";
