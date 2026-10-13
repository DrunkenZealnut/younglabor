<?php
function publicEscape($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function workPhaseLabel(string $phase): string { return ['preparing'=>'준비 중','recruiting'=>'모집 중','active'=>'운영 중','ended'=>'종료'][$phase] ?? '준비 중'; }
function workActionPathIsSafe(string $path): bool {
 return preg_match('~^(?:work(?:/[a-z0-9/-]+)?|resources(?:/[a-z0-9-]+)?|tools|club|board(?:/[a-z0-9-]+)?|youth-labor(?:/[a-z0-9-]+)?|#contact)$~D',$path)===1;
}
function publicHttpsUrl(string $url): bool {
 if (!filter_var($url,FILTER_VALIDATE_URL)) return false;
 $p=parse_url($url);return ($p['scheme']??'')==='https' && !isset($p['user']) && !isset($p['pass']) && (!isset($p['port']) || $p['port']===443);
}
function programAcceptsApplications(array $program, ?DateTimeImmutable $now = null): bool {
 if (($program['status']??'')!=='published' || ($program['recruitment_status']??'')!=='open' || !publicHttpsUrl((string)($program['application_url']??''))) return false;
 if (($program['recruitment_mode']??'')==='rolling') return true;
 $zone=new DateTimeZone('Asia/Seoul');$now=($now??new DateTimeImmutable('now',$zone))->setTimezone($zone);
 try {
 if(!empty($program['deadline_at']) && $now>new DateTimeImmutable($program['deadline_at'],$zone)) return false;
 if(!empty($program['last_session_at']) && $now>=(new DateTimeImmutable($program['last_session_at'],$zone))->setTime(0,0)->modify('+1 day')) return false;
 }catch(Throwable $e){return false;}
 return ($program['recruitment_mode']??'')==='dated';
}
function programStateLabel(array $program): string {
 if(programAcceptsApplications($program)) return ($program['recruitment_mode']??'')==='rolling' ? '상시모집' : '모집 중';
 return ($program['recruitment_status']??'')==='pending' ? '신청 안내 확인 중' : '접수 종료';
}
function programAction(array $program, ?DateTimeImmutable $now = null): ?array {
 return programAcceptsApplications($program,$now) ? ['label'=>'참가 신청','href'=>$program['application_url']] : null;
}
function programPath(array $program): string { return $program['kind']==='club' && $program['slug']==='vibecoding-club' ? 'club' : 'work/programs/'.$program['slug']; }
function workAction(array $row): ?array {
 $path=(string)($row['action_path']??'');$label=(string)($row['action_label']??'');
 return $path!=='' && $label!=='' && workActionPathIsSafe($path) ? ['label'=>$label,'href'=> $path==='#contact' ? url('').$path : url($path)] : null;
}

/** Portraits supplied in the 2026 course poster materials. Match the speaker, never the session order. */
function sessionSpeakerPhoto(string $programSlug,string $speaker): ?array {
 if($programSlug!=='2026-youth-labor')return null;
 $photos=[
  '신수연'=>['path'=>'assets/images/speakers/shin-su-yeon.png','width'=>206,'height'=>206],
  '연혜원'=>['path'=>'assets/images/speakers/yeon-hye-won.png','width'=>200,'height'=>200],
  '천현우'=>['path'=>'assets/images/speakers/cheon-hyeon-woo.png','width'=>243,'height'=>243],
  '박동욱'=>['path'=>'assets/images/speakers/park-dong-wook.png','width'=>400,'height'=>400],
 ];
 return $photos[$speaker]??null;
}
