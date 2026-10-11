<?php
require_once __DIR__.'/ContentManager.php';require_once __DIR__.'/WorkRepository.php';
class WorkManager {
 private PDO $pdo;private WorkRepository $repository;
 public function __construct(PDO $pdo,WorkRepository $repository){$this->pdo=$pdo;$this->repository=$repository;}
 public function save(string $kind,array $input,?int $expectedRevision): int {
  $v=validateWorkInput($kind,$input);$values=$v['values'];$errors=$v['errors'];
  foreach(['work_area_id'=>'area','funding_id'=>'funding'] as $field=>$type)if(!empty($values[$field]) && !$this->repository->findAdmin($type,(int)$values[$field]))$errors[$field]='연결할 항목을 확인해 주세요.';
  foreach($values['area_ids']??[] as $id)if(!$this->repository->findAdmin('area',(int)$id))$errors['area_ids']='사업축을 확인해 주세요.';
  if(isset($values['id']) && !$this->repository->findAdmin($kind,(int)$values['id']))throw new ContentConflictException('Entity unavailable');
  if($kind==='program' && isset($values['id'])){
   $existing=$this->repository->findAdmin('program',(int)$values['id']);
   if(($existing['slug']??'')==='vibecoding-club'){
    if($values['slug']!=='vibecoding-club')$errors['slug']='기본 동아리 주소는 변경할 수 없습니다.';
    if($values['kind']!=='club')$errors['kind']='기본 동아리의 종류는 변경할 수 없습니다.';
   }
  }
  if($errors)throw new ContentValidationException($errors,$values);
  $this->pdo->beginTransaction();
  try {
   $id=$this->repository->saveEntity($kind,$values,$expectedRevision);
   if($kind==='program'){$q=$this->pdo->prepare('DELETE FROM work_sessions WHERE program_id=?');$q->execute([$id]);foreach($values['sessions'] as $s){$s['program_id']=$id;$cols=array_keys($s);$q=$this->pdo->prepare('INSERT INTO work_sessions ('.implode(',',$cols).') VALUES ('.implode(',',array_fill(0,count($cols),'?')).')');$q->execute(array_values($s));}}
   if($kind==='unit'){$q=$this->pdo->prepare('DELETE FROM work_unit_areas WHERE unit_id=?');$q->execute([$id]);foreach($values['area_ids'] as $area){$q=$this->pdo->prepare('INSERT INTO work_unit_areas(unit_id,area_id) VALUES (?,?)');$q->execute([$id,$area]);}}
   $this->pdo->commit();return $id;
  }catch(Throwable $e){$this->pdo->rollBack();throw $e;}
 }
}
