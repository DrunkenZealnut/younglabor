<?php
require_once __DIR__.'/ContentRepository.php';
require_once __DIR__.'/WorkValidation.php';
class WorkRepository {
 private PDO $pdo;
 public function __construct(PDO $pdo){$this->pdo=$pdo;}
 private function table(string $kind): string { $tables=['area'=>'work_areas','unit'=>'work_units','funding'=>'work_funding','program'=>'work_programs'];if(!isset($tables[$kind]))throw new InvalidArgumentException('Invalid entity');return $tables[$kind]; }
 public function listEntities(string $kind,bool $publishedOnly=true): array {$table=$this->table($kind);$sort=in_array($kind,['area','unit'],true)?'sort_order,id':'id';return $this->pdo->query("SELECT * FROM $table".($publishedOnly?" WHERE status='published'":'')." ORDER BY $sort")->fetchAll();}
 public function listAreas(bool $publishedOnly=true): array {return $this->listEntities('area',$publishedOnly);}
 private function find(string $kind,string $field,$value,bool $publishedOnly): ?array {$table=$this->table($kind);$q=$this->pdo->prepare("SELECT * FROM $table WHERE $field=?".($publishedOnly?" AND status='published'":''));$q->execute([$value]);return $q->fetch()?:null;}
 public function findArea(string $slug,bool $publishedOnly=true): ?array {return $this->find('area','slug',$slug,$publishedOnly);}
 public function findFunding(int $year,bool $publishedOnly=true): ?array {return $this->find('funding','year',$year,$publishedOnly);}
 public function fundingUnits(int $fundingId,bool $publishedOnly=true): array {$q=$this->pdo->prepare('SELECT * FROM work_units WHERE funding_id=?'.($publishedOnly?" AND status='published'":'').' ORDER BY sort_order,id');$q->execute([$fundingId]);return $q->fetchAll();}
 public function findProgram(string $slug,bool $publishedOnly=true): ?array {$p=$this->find('program','slug',$slug,$publishedOnly);if($p){$q=$this->pdo->prepare('SELECT MAX(starts_at) FROM work_sessions WHERE program_id=?');$q->execute([$p['id']]);$p['last_session_at']=$q->fetchColumn()?:null;}return $p;}
 public function findPublishedProgramById(int $id): ?array {$p=$this->find('program','id',$id,true);return $p?$this->findProgram($p['slug']):null;}
 public function listPrograms(bool $publishedOnly=true): array { $rows=$this->listEntities('program',$publishedOnly);foreach($rows as &$row){$row=$this->findProgram($row['slug'],$publishedOnly);}return $rows;}
 public function sessions(int $programId,bool $publishedOnly=true): array {$q=$this->pdo->prepare('SELECT s.* FROM work_sessions s JOIN work_programs p ON p.id=s.program_id WHERE s.program_id=?'.($publishedOnly?" AND p.status='published'":'').' ORDER BY s.number');$q->execute([$programId]);return $q->fetchAll();}
 public function findAdmin(string $kind,int $id): ?array {$row=$this->find($kind,'id',$id,false);if($row && $kind==='program')$row['sessions']=$this->sessions($id,false);if($row && $kind==='unit'){$q=$this->pdo->prepare('SELECT area_id FROM work_unit_areas WHERE unit_id=?');$q->execute([$id]);$row['area_ids']=$q->fetchAll(PDO::FETCH_COLUMN);}return $row;}
 public function saveEntity(string $kind,array $values,?int $expectedRevision): int {
  $table=$this->table($kind);$fields=workFields($kind);$params=[];foreach($fields as $f)$params[$f]=$values[$f]??null;
  try {
   if(isset($values['id'])) {
    if(!$expectedRevision)throw new ContentConflictException('Revision required');
    $assign=implode(',',array_map(static fn($f)=>"$f=:$f",$fields));$params['id']=$values['id'];$params['revision']=$expectedRevision;
    $q=$this->pdo->prepare("UPDATE $table SET $assign,revision=revision+1 WHERE id=:id AND revision=:revision");$q->execute($params);if($q->rowCount()!==1)throw new ContentConflictException('Content changed');return (int)$values['id'];
   }
   if($expectedRevision!==null)throw new ContentConflictException('Unexpected revision');
   $q=$this->pdo->prepare("INSERT INTO $table (".implode(',',$fields).') VALUES ('.implode(',',array_map(static fn($f)=>":$f",$fields)).')');$q->execute($params);return (int)$this->pdo->lastInsertId();
  }catch(PDOException $e){if(($e->errorInfo[1]??0)===1062)throw new ContentSlugConflictException('Duplicate slug or year');throw $e;}
 }
}
