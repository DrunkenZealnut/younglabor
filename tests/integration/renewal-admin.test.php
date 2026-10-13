<?php
require __DIR__.'/../support/renewal.php';require __DIR__.'/../../includes/WorkRepository.php';
$p=renewalTestPdo();$base=getenv('SITE_BASE_URL')?:'http://127.0.0.1:18763/younglabor';
$cookie=tempnam(sys_get_temp_dir(),'yl-admin-');
function adminRequest(string $path,?array $post=null): array {global $base,$cookie;$c=curl_init($base.'/'.$path);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_COOKIEFILE=>$cookie,CURLOPT_FOLLOWLOCATION=>false]);if($post!==null)curl_setopt_array($c,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($post)]);$body=curl_exec($c);$code=curl_getinfo($c,CURLINFO_RESPONSE_CODE);$redirect=curl_getinfo($c,CURLINFO_REDIRECT_URL);curl_close($c);return [$code,$body,$redirect];}
check(adminRequest('admin/login.php',['username'=>'renewal-test','password'=>'local-test-password'])[0]===302,'test admin login');
foreach(['admin','admin/'] as $entry){$result=adminRequest($entry);check($result[0]===302 && $result[2]===$base.'/admin/dashboard.php','authenticated entry reaches dashboard: '.$entry);}
$r=new WorkRepository($p);$row=$r->findProgram('2026-youth-labor',false);$id=(int)$row['id'];$before=(int)$row['revision'];
[$code,$html]=adminRequest('admin/program-edit.php?id='.$id);check($code===200 && str_contains($html,'name="sessions[0][speaker]"'),'program session form');preg_match('/name="_csrf_token" value="([a-f0-9]+)"/',$html,$m);$token=$m[1]??'';check($token!=='','csrf token rendered');
$input=$r->findAdmin('program',$id);$input['expected_revision']=$before;$input['_csrf_token']='bad';
adminRequest('admin/program-edit.php?id='.$id,$input);check((int)$r->findAdmin('program',$id)['revision']===$before,'bad csrf does not save');
$input['_csrf_token']=$token;$input['recruitment_status']='closed';check(adminRequest('admin/program-edit.php?id='.$id,$input)[0]===302,'valid program save');check($r->findAdmin('program',$id)['recruitment_status']==='closed','closure stored');
[$code,$html]=adminRequest('admin/program-edit.php?id='.$id,$input);check($code===200 && str_contains($html,'충돌'),'stale revision rejected');
$input=$r->findAdmin('program',$id);$input['expected_revision']=$input['revision'];$input['_csrf_token']=$token;$input['recruitment_status']='pending';adminRequest('admin/program-edit.php?id='.$id,$input);
check(adminRequest('admin/api/committee-action.php',['action'=>'delete','id'=>1])[0]===410,'old action retired');
[$code,$html]=adminRequest('admin/dashboard.php');check($code===200 && !str_contains($html,'동아리 신청'),'old metrics removed');
unlink($cookie);echo "PASS admin sessions, CSRF, save, conflict, retired API\n";
