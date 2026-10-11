<?php
require_once __DIR__.'/auth.php';require_once __DIR__.'/../includes/WorkManager.php';
$error='';$errors=[];$conflict=false;$repo=null;$existing=null;$values=[];$id=null;$revision=null;
try {
 $pdo=Database::getInstance()->getConnection();$repo=new WorkRepository($pdo);
 if(isset($_GET['id'])){$id=filter_var($_GET['id'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);if(!$id)throw new OutOfBoundsException('not found');$existing=$repo->findAdmin($entityKind,$id);if(!$existing)throw new OutOfBoundsException('not found');}
 $values=$existing??array_fill_keys(workFields($entityKind),'');
 $values+=['sessions'=>[],'area_ids'=>[]];
 if(!$existing)$values=array_replace($values,['status'=>'draft','phase'=>'preparing','kind'=>'course','recruitment_status'=>'pending','recruitment_mode'=>'dated','contact_email'=>'admin@younglabor.kr','year'=>2026,'term'=>1,'sort_order'=>0]);
 $revision=$existing?(int)$existing['revision']:null;
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verifyCsrfToken()){$error='요청을 확인할 수 없습니다. 다시 시도해 주세요.';http_response_code(400);}
  else {
   $input=$_POST;if($existing)$input['id']=$id;else unset($input['id']);
   $submittedRevision=$existing?filter_var($_POST['expected_revision']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]):null;
   $validation=validateWorkInput($entityKind,$input);$values=array_replace($values,$validation['values']);$revision=$submittedRevision?:$revision;
   try {
    if($existing && !$submittedRevision)throw new ContentConflictException('revision required');
    $saved=(new WorkManager($pdo,$repo))->save($entityKind,$input,$existing?(int)$submittedRevision:null);
    $editor=$entityKind==='program'?'program-edit':($entityKind==='funding'?'funding-edit':'work-edit');
    header('Location: '.url('admin/'.$editor.'.php?kind='.$entityKind.'&id='.$saved.'&saved=1'));exit;
   }catch(ContentValidationException $e){$errors=$e->errors();$error='입력 내용을 확인해 주세요.';}
   catch(ContentConflictException $e){$error='다른 변경과 충돌했습니다. 최신 내용을 다시 열어 주세요.';$conflict=true;}
   catch(Throwable $e){$error='저장하지 못했습니다. 입력 내용을 보존했습니다. 다시 시도해 주세요.';}
  }
 }
}catch(OutOfBoundsException $e){http_response_code(404);$error='항목을 찾을 수 없습니다.';}
catch(Throwable $e){http_response_code(503);$error='사업 정보를 불러오지 못했습니다. 이관 적용 상태를 확인해 주세요.';}
$labels=['slug'=>'주소 이름','title'=>'제목','summary'=>'한 문장 소개','body'=>'본문','status'=>'공개 여부','home_featured'=>'홈에 노출','audience'=>'대상','provision'=>'실제로 제공하는 내용','phase'=>'운영 상태','action_label'=>'행동 버튼 이름','action_path'=>'연결 경로','sort_order'=>'표시 순서','funding_id'=>'연결 지원사업','registered_name'=>'사업 등록 명칭','year'=>'연도','term'=>'차수','sponsor'=>'지원 주체','kind'=>'프로그램 종류','work_area_id'=>'연결 사업축','venue'=>'현장 장소','fee'=>'참가비','application_url'=>'신청 HTTPS 주소','recruitment_status'=>'접수 상태','recruitment_mode'=>'모집 방식','deadline_at'=>'모집 마감 (상시모집은 비움)','contact_email'=>'문의 이메일','organizers'=>'주최','credit'=>'지원 문구','support_details'=>'지원 내용'];
$options=['status'=>['draft'=>'임시저장','published'=>'공개'],'phase'=>['preparing'=>'준비 중','recruiting'=>'모집 중','active'=>'운영 중','ended'=>'종료'],'kind'=>['course'=>'교육프로그램','club'=>'동아리'],'recruitment_status'=>['pending'=>'확인 대기','open'=>'접수 중','closed'=>'접수 종료'],'recruitment_mode'=>['dated'=>'기간 모집','rolling'=>'상시모집']];
if($repo){$options['work_area_id']=[''=>'연결 없음'];foreach($repo->listAreas(false) as $a)$options['work_area_id'][$a['id']]=$a['title'];$options['funding_id']=[];foreach($repo->listEntities('funding',false) as $a)$options['funding_id'][$a['id']]=$a['title'];}
adminHeader();
?>
<style>.work-form{max-width:1050px}.edit-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.edit-field{display:grid;gap:8px;margin-bottom:12px}.edit-field.wide{grid-column:1/-1}.edit-field input,.edit-field select,.edit-field textarea{width:100%;font:inherit;padding:12px;border:1px solid #cbd5e1}.edit-field textarea{min-height:100px}.edit-error{color:#b91c1c}.session-editor{border:1px solid #cbd5e1;padding:20px;margin:16px 0}.session-editor legend{font-weight:700}.work-form input[type=checkbox]{width:auto}.work-form :focus-visible{outline:3px solid #430086;outline-offset:2px}@media(max-width:700px){.edit-grid{grid-template-columns:1fr}}</style>
<div class="main-header"><h1><?php echo $existing?'항목 수정':'새 사업·프로그램'; ?></h1><a href="<?php echo e(url('admin/work.php')); ?>">목록</a></div>
<?php if($error): ?><div class="card edit-error" role="alert"><?php echo e($error); ?><?php if($conflict): ?> <a href="<?php echo e(url('admin/'.($entityKind==='program'?'program-edit':($entityKind==='funding'?'funding-edit':'work-edit')).'.php?kind='.$entityKind.'&id='.$id)); ?>">최신 내용 다시 열기</a><?php endif; ?></div><?php endif; ?>
<?php if(isset($_GET['saved'])): ?><p role="status">저장했습니다.</p><?php endif; ?>
<?php if($repo && (!$id || $existing)): ?>
<form method="post" class="card work-form"><?php echo csrfField(); ?>
<?php if($existing): ?><input type="hidden" name="expected_revision" value="<?php echo (int)$revision; ?>"><?php endif; ?>
<div class="edit-grid">
<?php foreach(workFields($entityKind) as $f): $v=$values[$f]??'';$wide=in_array($f,['summary','body','provision','support_details'],true); ?>
<div class="edit-field<?php echo $wide?' wide':''; ?>"><label for="work-<?php echo $f; ?>"><?php echo e($labels[$f]); ?></label>
<?php if(isset($options[$f])): ?><select id="work-<?php echo $f; ?>" name="<?php echo $f; ?>"><?php foreach($options[$f] as $key=>$label): ?><option value="<?php echo e((string)$key); ?>"<?php echo (string)$v===(string)$key?' selected':''; ?>><?php echo e($label); ?></option><?php endforeach; ?></select>
<?php elseif($f==='home_featured'): ?><input id="work-<?php echo $f; ?>" type="checkbox" name="<?php echo $f; ?>" value="1"<?php echo $v?' checked':''; ?>>
<?php elseif($wide || in_array($f,['audience','venue','organizers','credit'],true)): ?><textarea id="work-<?php echo $f; ?>" name="<?php echo $f; ?>"><?php echo e((string)$v); ?></textarea>
<?php else: ?><input id="work-<?php echo $f; ?>" name="<?php echo $f; ?>" type="<?php echo $f==='contact_email'?'email':($f==='application_url'?'url':(in_array($f,['year','term','sort_order'],true)?'number':($f==='deadline_at'?'datetime-local':'text'))); ?>" value="<?php echo e($f==='deadline_at'?str_replace(' ','T',(string)$v):(string)$v); ?>"<?php echo in_array($f,['slug','title','contact_email'],true)?' required':''; ?>><?php endif; ?>
<?php if(isset($errors[$f])): ?><p class="edit-error"><?php echo e($errors[$f]); ?></p><?php endif; ?>
<?php if($f==='action_path'): ?><small>예: club, tools, resources, work/programs/2026-youth-labor, #contact</small><?php endif; ?>
</div><?php endforeach; ?>
</div>
<?php if($entityKind==='unit'): ?><fieldset><legend>연결 사업축</legend><?php foreach($repo->listAreas(false) as $a): ?><label style="display:block"><input type="checkbox" name="area_ids[]" value="<?php echo (int)$a['id']; ?>"<?php echo in_array($a['id'],$values['area_ids']??[])?' checked':''; ?>> <?php echo e($a['title']); ?></label><?php endforeach; ?></fieldset><?php endif; ?>
<?php if($entityKind==='program'): ?>
<h2>회차 안내</h2><p>동아리는 회차를 비워 둘 수 있습니다. 기간만으로 개최 완료·자료 공개 상태를 표시하지 않습니다.</p>
<?php if(isset($errors['sessions'])): ?><p class="edit-error"><?php echo e($errors['sessions']); ?></p><?php endif; ?>
<div id="session-editors">
<?php function renderSessionEditor(int $i,array $s): void { $labels=['number'=>'회차 번호','starts_at'=>'일시','speaker'=>'강사','title'=>'회차 제목','description'=>'소개','panel'=>'패널 안내']; ?>
<fieldset class="session-editor"><legend>회차</legend><div class="edit-grid"><?php foreach($labels as $f=>$label): ?><div class="edit-field"><label for="session-<?php echo $i.'-'.$f; ?>"><?php echo $label; ?></label><input id="session-<?php echo $i.'-'.$f; ?>" name="sessions[<?php echo $i; ?>][<?php echo $f; ?>]" type="<?php echo $f==='starts_at'?'datetime-local':($f==='number'?'number':'text'); ?>" value="<?php echo e($f==='starts_at'?str_replace(' ','T',(string)($s[$f]??'')):(string)($s[$f]??'')); ?>"></div><?php endforeach; ?>
<div class="edit-field"><label for="session-<?php echo $i; ?>-mode">참여 방식</label><select id="session-<?php echo $i; ?>-mode" name="sessions[<?php echo $i; ?>][mode]"><option value="hybrid">현장 + 온라인</option><option value="online"<?php echo ($s['mode']??'')==='online'?' selected':''; ?>>온라인</option></select></div></div><button type="button" class="btn btn-outline" onclick="this.closest('fieldset').remove()">회차 제거</button></fieldset>
<?php } foreach($values['sessions']??[] as $i=>$s)renderSessionEditor($i,$s); ?>
</div><template id="session-template"><?php renderSessionEditor(99999,[]); ?></template><button type="button" class="btn btn-outline" id="add-session">회차 추가</button>
<script>let sessionIndex=<?php echo count($values['sessions']??[]); ?>;document.getElementById('add-session').addEventListener('click',()=>{const html=document.getElementById('session-template').innerHTML.replaceAll('99999',String(sessionIndex++));document.getElementById('session-editors').insertAdjacentHTML('beforeend',html);});const mode=document.getElementById('work-recruitment_mode'),deadline=document.getElementById('work-deadline_at');function syncDeadline(){deadline.disabled=mode.value==='rolling';if(deadline.disabled)deadline.value='';}mode.addEventListener('change',syncDeadline);syncDeadline();</script>
<?php endif; ?>
<div style="margin-top:24px"><button class="btn btn-primary" type="submit"<?php echo $conflict?' disabled':''; ?>>저장</button></div></form><?php endif; ?>
<?php adminFooter(); ?>
