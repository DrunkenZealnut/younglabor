<?php
function inquiryTypeLabel(string $type): string {return ['general'=>'일반 문의','education'=>'교육·자료','cooperation'=>'학교·지역 협력','participation'=>'프로그램 참여'][$type]??'일반 문의';}
function validateContactInput(array $input): array {
 $values=[];$errors=[];
 foreach(['inquiry_type','name','email','message'] as $field){$raw=$input[$field]??'';if(!is_string($raw)){$errors[$field]='값을 확인해 주세요.';$raw='';}$values[$field]=trim($raw);}
 if(!in_array($values['inquiry_type'],['general','education','cooperation','participation'],true))$errors['inquiry_type']='문의 유형을 선택해 주세요.';
 if(mb_strlen($values['name'])>100)$errors['name']='이름은 100자 이하로 입력해 주세요.';
 if(!filter_var($values['email'],FILTER_VALIDATE_EMAIL) || strlen($values['email'])>254)$errors['email']='답변받을 이메일을 확인해 주세요.';
 if($values['message']==='' || mb_strlen($values['message'])>3000)$errors['message']='내용은 1~3000자로 입력해 주세요.';
 return compact('values','errors');
}
