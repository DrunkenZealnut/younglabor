<?php
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){header('Allow: POST, OPTIONS');http_response_code(204);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'POST 요청만 허용됩니다.'],JSON_UNESCAPED_UNICODE);exit;}
require_once __DIR__.'/../config.php';require_once __DIR__.'/../includes/Database.php';require_once __DIR__.'/../includes/Mailer.php';require_once __DIR__.'/../includes/Privacy.php';require_once __DIR__.'/../includes/ContactService.php';
$contentType=$_SERVER['CONTENT_TYPE']??'';$input=str_contains($contentType,'application/json')?json_decode(file_get_contents('php://input'),true):$_POST;
if(!is_array($input)){http_response_code(400);echo json_encode(['success'=>false,'message'=>'문의 형식을 확인해 주세요.'],JSON_UNESCAPED_UNICODE);exit;}
$service=new ContactService(
 static function(array $v): bool {
  $pdo=Database::getInstance()->getConnection();$q=$pdo->prepare("INSERT INTO inquiries(inquiry_type,name,email,message,status,ip_address,user_agent,created_at) VALUES (:type,:name,:email,:message,'new',:ip,:ua,NOW())");
  return $q->execute([':type'=>$v['inquiry_type'],':name'=>$v['name'],':email'=>$v['email'],':message'=>$v['message'],':ip'=>maskIpAddress($_SERVER['REMOTE_ADDR']??''),':ua'=>substr($_SERVER['HTTP_USER_AGENT']??'',0,512)]);
 },
 static function(array $v): bool {
  $recipient=env('SITE_EMAIL','admin@younglabor.kr');$mailer=new Mailer();if(!$recipient || !$mailer->isConfigured())return false;
  $name=htmlspecialchars($v['name']?:'이름 미기재',ENT_QUOTES,'UTF-8');$email=htmlspecialchars($v['email'],ENT_QUOTES,'UTF-8');$message=htmlspecialchars('['.inquiryTypeLabel($v['inquiry_type'])."]\n".$v['message'],ENT_QUOTES,'UTF-8');
  return $mailer->send($recipient,'[청쎈] '.inquiryTypeLabel($v['inquiry_type']).' 문의',Mailer::buildContactEmailBody($name,$email,$message),$v['email']);
 }
);
$result=$service->submit($input);http_response_code($result['success']?200:($result['errors']?400:503));unset($result['notification']);echo json_encode($result,JSON_UNESCAPED_UNICODE);
