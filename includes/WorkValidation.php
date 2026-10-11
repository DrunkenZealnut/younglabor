<?php
require_once __DIR__.'/ContentValidation.php';
require_once __DIR__.'/WorkPresenter.php';
function workFields(string $kind): array {
 $common=['slug','title','summary','body','status','home_featured'];
 $specific=[
 'area'=>['audience','provision','phase','action_label','action_path','sort_order'],
 'unit'=>['funding_id','registered_name','phase','action_label','action_path','sort_order'],
 'funding'=>['year','term','sponsor'],
 'program'=>['kind','work_area_id','audience','venue','fee','application_url','recruitment_status','recruitment_mode','deadline_at','contact_email','organizers','credit','support_details']
 ];
 if(!isset($specific[$kind])) throw new InvalidArgumentException('Invalid work type');
 return array_merge($common,$specific[$kind]);
}
function validateWorkInput(string $kind, array $input): array {
 $values=[];$errors=[];
 foreach(workFields($kind) as $field){
  $raw=$input[$field]??'';
  if(!is_scalar($raw) && $raw!==null){$errors[$field]='값을 확인해 주세요.';$raw='';}
  $values[$field]=trim((string)$raw);
 }
 if($values['title']==='' || mb_strlen($values['title'])>160) $errors['title']='제목은 1~160자로 입력해 주세요.';
 if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D',$values['slug']) || strlen($values['slug'])>180) $errors['slug']='영문 소문자·숫자·단일 하이픈을 사용해 주세요.';
 if($values['summary']==='' || mb_strlen($values['summary'])>2000) $errors['summary']='소개는 1~2000자로 입력해 주세요.';
 if(mb_strlen($values['body'])>30000) $errors['body']='본문은 30000자 이하로 입력해 주세요.';
 if(!in_array($values['status'],['draft','published'],true)) $errors['status']='공개 상태를 선택해 주세요.';
 $values['home_featured']=!empty($input['home_featured']) ? 1 : 0;
 foreach(['sort_order','year','term','funding_id','work_area_id'] as $field) if(array_key_exists($field,$values)) {
  if($field==='work_area_id' && $values[$field]===''){$values[$field]=null;continue;}
  $n=filter_var($values[$field]==='' && $field==='sort_order' ? '0' : $values[$field],FILTER_VALIDATE_INT);
  if($n===false || $n<($field==='sort_order'?0:1) || ($field==='year' && ($n<2000 || $n>2200)) || ($field==='term' && $n>100)) $errors[$field]='올바른 숫자를 입력해 주세요.';
  $values[$field]=$n===false?0:$n;
 }
 if(isset($values['phase']) && !in_array($values['phase'],['preparing','recruiting','active','ended'],true)) $errors['phase']='운영 상태를 선택해 주세요.';
 if(isset($values['action_path']) && $values['action_path']!=='' && !workActionPathIsSafe($values['action_path'])) $errors['action_path']='사이트 안의 안내 경로를 입력해 주세요.';
 foreach(['audience','provision','venue','organizers','credit','support_details'] as $f) if(isset($values[$f]) && mb_strlen($values[$f])>5000) $errors[$f]='5000자 이하로 입력해 주세요.';
 foreach(['registered_name','action_label','sponsor','fee'] as $f) if(isset($values[$f]) && mb_strlen($values[$f])>($f==='registered_name'||$f==='sponsor'?160:100)) $errors[$f]='글자 수를 줄여 주세요.';
 if($kind==='funding' && $values['sponsor']==='') $errors['sponsor']='지원 주체를 입력해 주세요.';
 if($kind==='program') {
  if(!in_array($values['kind'],['course','club'],true)) $errors['kind']='프로그램 종류를 선택해 주세요.';
  if(!in_array($values['recruitment_status'],['pending','open','closed'],true)) $errors['recruitment_status']='접수 상태를 선택해 주세요.';
  if(!in_array($values['recruitment_mode'],['dated','rolling'],true)) $errors['recruitment_mode']='모집 방식을 선택해 주세요.';
  if($values['application_url']!=='' && (strlen($values['application_url'])>2048 || !contentExternalUrlIsSafe($values['application_url']))) $errors['application_url']='공개 HTTPS 신청 주소를 입력해 주세요.';
  if($values['recruitment_status']==='open' && $values['application_url']==='') $errors['application_url']='접수 중에는 신청 주소가 필요합니다.';
  if(!filter_var($values['contact_email'],FILTER_VALIDATE_EMAIL) || strlen($values['contact_email'])>254) $errors['contact_email']='문의 이메일을 확인해 주세요.';
  $values['deadline_at']=str_replace('T',' ',$values['deadline_at']);
  if($values['recruitment_mode']==='rolling') $values['deadline_at']=null;
  elseif($values['deadline_at']==='') $values['deadline_at']=null;
  elseif(!workDateTimeValid($values['deadline_at'])) $errors['deadline_at']='올바른 날짜와 시각을 입력해 주세요.';
  $sessions=$input['sessions']??[];$values['sessions']=[];$numbers=[];
  if(!is_array($sessions) || count($sessions)>50){$errors['sessions']='회차 정보를 확인해 주세요.';$sessions=[];}
  foreach($sessions as $s) {
   if(!is_array($s)){$errors['sessions']='회차 정보를 확인해 주세요.';continue;}
   $clean=[];foreach(['number','starts_at','speaker','title','description','mode','panel'] as $f){$raw=$s[$f]??'';if(!is_scalar($raw) && $raw!==null){$errors['sessions']='회차 값을 확인해 주세요.';$raw='';}$clean[$f]=trim((string)$raw);}
   $clean['starts_at']=str_replace('T',' ',$clean['starts_at']);
   $n=filter_var($clean['number'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>50]]);
   if(!$n || isset($numbers[$n]) || !workDateTimeValid($clean['starts_at']) || $clean['speaker']==='' || mb_strlen($clean['speaker'])>160 || $clean['title']==='' || mb_strlen($clean['title'])>160 || !in_array($clean['mode'],['online','hybrid'],true) || mb_strlen($clean['description'])>5000 || mb_strlen($clean['panel'])>5000) $errors['sessions']='회차 번호·일시·강사·제목·방식을 확인해 주세요. 회차 번호는 중복할 수 없습니다.';
   $numbers[$n?:0]=true;$clean['number']=$n?:0;$values['sessions'][]=$clean;
  }
 }
 if(array_key_exists('id',$input)){$id=filter_var($input['id'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);if(!$id)$errors['id']='항목을 찾을 수 없습니다.';$values['id']=$id?:0;}
 if($kind==='unit'){$raw=$input['area_ids']??[];if(!is_array($raw)){$errors['area_ids']='사업축을 선택해 주세요.';$raw=[];}$values['area_ids']=[];foreach($raw as $id){$n=filter_var($id,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);if(!$n)$errors['area_ids']='사업축을 확인해 주세요.';else $values['area_ids'][]=$n;}$values['area_ids']=array_values(array_unique($values['area_ids']));}
 return compact('values','errors');
}
function workDateTimeValid(string $value): bool {
 foreach(['!Y-m-d H:i:s','!Y-m-d H:i'] as $format){$d=DateTimeImmutable::createFromFormat($format,$value,new DateTimeZone('Asia/Seoul'));$e=DateTimeImmutable::getLastErrors();if($d && ($e===false || !$e['warning_count'] && !$e['error_count']) && $d->format(substr($format,1))===$value)return true;}return false;
}
