<?php
require_once __DIR__.'/ContactValidation.php';
class ContactService {
 private $saveInquiry;private $notify;
 public function __construct(callable $saveInquiry,callable $notify){$this->saveInquiry=$saveInquiry;$this->notify=$notify;}
 public function submit(array $input): array {
  $v=validateContactInput($input);
  if($v['errors'])return ['success'=>false,'message'=>'입력 내용을 확인해 주세요.','errors'=>$v['errors'],'notification'=>false];
  try{$saved=(bool)($this->saveInquiry)($v['values']);}catch(Throwable $e){$saved=false;error_log('Inquiry storage unavailable');}
  if(!$saved)return ['success'=>false,'message'=>'문의가 접수되지 않았습니다. 입력 내용을 보존한 뒤 다시 시도하거나 admin@younglabor.kr로 보내 주세요.','errors'=>[],'notification'=>false];
  try{$notified=(bool)($this->notify)($v['values']);}catch(Throwable $e){$notified=false;error_log('Inquiry notification unavailable');}
  return ['success'=>true,'message'=>'문의가 접수되었습니다. 남겨 주신 이메일로 답변을 안내합니다.','errors'=>[],'notification'=>$notified];
 }
}
