<?php
// Value: protects=migrate 요청은 서명·요청 시각·모드 검사를 통과해야만 DB에 닿고, status는 DB를 바꾸지 않으며, 오류 상세는 응답(공개 Actions 로그)에 실리지 않음
// fails_when=status/apply 선택이 뒤바뀌거나 가드·서버 잠금이 빠지거나 예외 메시지를 응답에 싣거나 push 경로가 새 파일에 의존하게 되는 경우
// why_new=deploy-migration.test.php는 소스 문자열만, integration/deploy-migration은 함수만 직접 호출; seam=none
// no_claim=카페24 Apache/PHP 설정, 실제 Actions runner, push ZIP 배포 흐름은 검증하지 않음
require __DIR__.'/../support/renewal.php';
$root=dirname(__DIR__,2);
$name='younglabor_deploy_endpoint_test';
$dbPort=getenv('TEST_DB_PORT') ?: '33317';
$secret='endpoint-test-secret';
$server=new PDO("mysql:host=127.0.0.1;port=$dbPort;charset=utf8mb4",'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$server->exec("DROP DATABASE IF EXISTS $name");$server->exec("CREATE DATABASE $name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
// deploy.php는 저장소 루트의 deploy.log에 기록하므로 테스트 전 내용을 되돌린다
$logFile="$root/deploy.log";$logBefore=is_file($logFile) ? file_get_contents($logFile) : null;
$tmp=sys_get_temp_dir().'/deploy-endpoint-'.getmypid();@mkdir($tmp);
file_put_contents("$tmp/router.php",'<?php function loadEnv($path){return false;}'
 .'$_ENV["DEPLOY_SECRET"]='.var_export($secret,true).';$_ENV["DB_HOST"]="127.0.0.1";$_ENV["DB_PORT"]=$_GET["db_port"]??'.var_export($dbPort,true).';'
 .'$_ENV["DB_NAME"]='.var_export($name,true).';$_ENV["DB_USERNAME"]="root";$_ENV["DB_PASSWORD"]="";$_ENV["DB_SOCKET"]="";'
 .'require '.var_export("$root/api/deploy.php",true).';');
$probe=stream_socket_server('tcp://127.0.0.1:0');$port=(int)substr(strrchr(stream_socket_get_name($probe,false),':'),1);fclose($probe);
$proc=proc_open([PHP_BINARY,'-S',"127.0.0.1:$port","$tmp/router.php"],[1=>['file','/dev/null','w'],2=>['file','/dev/null','w']],$pipes);
function deployRequest(int $port,string $body,string $event,?string $signature,string $query=''): array {
 global $secret;
 $headers=["Content-Type: application/json","X-GitHub-Event: $event","X-Hub-Signature-256: ".($signature ?? 'sha256='.hash_hmac('sha256',$body,$secret))];
 $response=@file_get_contents("http://127.0.0.1:$port/$query",false,stream_context_create(['http'=>['method'=>'POST','header'=>implode("\r\n",$headers),'content'=>$body,'ignore_errors'=>true,'timeout'=>60]]));
 preg_match('/\s(\d{3})\s/',$http_response_header[0] ?? '',$m);
 return [(int)($m[1] ?? 0),(string)$response,json_decode((string)$response,true)];
}
function migrateBody(string $mode,int $requestedAt): string { return json_encode(['mode'=>$mode,'requested_at'=>$requestedAt]); }
function renewalTableCount(PDO $p): int { return (int)$p->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name<>'inquiries'")->fetchColumn(); }
try {
 for($i=0;$i<50 && !@fsockopen('127.0.0.1',$port);$i++) usleep(100000);
 putenv("TEST_DB_NAME=$name");$p=renewalTestPdo();
 $p->exec("CREATE TABLE inquiries(id INT AUTO_INCREMENT PRIMARY KEY,message TEXT NOT NULL) ENGINE=InnoDB");

 [$code,,$json]=deployRequest($port,migrateBody('status',time()),'migrate',null);
 check($code===200 && $json['message']==='Migration pending' && $json['data']['pending']===true,'status on empty DB reports pending');
 check(renewalTableCount($p)===0,'status must not create tables');

 [$code]=deployRequest($port,migrateBody('apply',time()-301),'migrate',null);
 check($code===403,'stale request rejected');
 [$code]=deployRequest($port,migrateBody('apply',time()),'migrate','sha256='.str_repeat('0',64));
 check($code===403,'bad signature rejected');
 [$code]=deployRequest($port,'"x"','migrate',null);
 check($code===403,'non-object payload rejected');
 [$code]=deployRequest($port,migrateBody('drop',time()),'migrate',null);
 check($code===400,'unknown mode rejected');
 check(renewalTableCount($p)===0,'rejected requests must not touch the DB');

 [$code,$raw]=deployRequest($port,migrateBody('status',time()),'migrate',null,'?db_port=1');
 check($code===500 && json_decode($raw,true)['message']==='Migration failed. See deploy.log on the server.','DB failure returns generic 500');
 foreach(['SQLSTATE','127.0.0.1',$name,$root] as $secretDetail) check(strpos($raw,$secretDetail)===false,'500 body must not leak '.$secretDetail);

 [$code,,$json]=deployRequest($port,'{"zen":"ok"}','ping',null);
 check($code===200 && $json['message']==='Ignored event: ping','ping path still works');

 // 배포나 이관이 진행 중(deploy.log 잠금)이면 다른 요청은 서버에 닿지 않고 409
 $busy=fopen($logFile,'c');flock($busy,LOCK_EX);
 [$code]=deployRequest($port,migrateBody('apply',time()),'migrate',null);
 flock($busy,LOCK_UN);fclose($busy);
 check($code===409 && renewalTableCount($p)===0,'concurrent request rejected while locked');

 [$code,,$json]=deployRequest($port,migrateBody('apply',time()),'migrate',null);
 check($code===200 && $json['message']==='Migration up to date' && $json['data']['pending']===false,'apply completes migration');
 check($json['data']['counts']===['work_areas'=>4,'work_programs'=>2,'work_sessions'=>4],'apply seeds initial content');
 [$code,,$json]=deployRequest($port,migrateBody('status',time()),'migrate',null);
 check($code===200 && $json['data']['pending']===false,'status after apply is up to date');
 echo "PASS deploy endpoint migrate event over HTTP\n";
} finally {
 proc_terminate($proc);proc_close($proc);
 if($logBefore===null) @unlink($logFile); else file_put_contents($logFile,$logBefore);
 @unlink("$tmp/router.php");@rmdir($tmp);
 $server->exec("DROP DATABASE IF EXISTS $name");
}
