<?php
require __DIR__.'/../support/renewal.php';check(is_file(__DIR__.'/../../includes/ContactService.php'),'Contact service required');require __DIR__.'/../../includes/ContactService.php';
$p=renewalTestPdo();$before=(int)$p->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
$s=new ContactService(static function($v)use($p){$q=$p->prepare("INSERT INTO inquiries(inquiry_type,name,email,message) VALUES (?,?,?,?)");return $q->execute([$v['inquiry_type'],$v['name'],$v['email'],$v['message']]);},static fn($v)=>false);
$r=$s->submit(['inquiry_type'=>'cooperation','name'=>'','email'=>'fixture@example.org','message'=>'비공개 테스트 문의']);check($r['success'],'saved despite notification failure');check((int)$p->query('SELECT COUNT(*) FROM inquiries')->fetchColumn()===$before+1,'stored exactly once');$p->exec("DELETE FROM inquiries WHERE email='fixture@example.org'");echo "PASS private inquiry saved once without SMTP\n";
