<?php
function renewalSeedData(): array {
 $published=['status'=>'published'];
 $areas=[
 ['slug'=>'education','title'=>'안전교육·교과서','summary'=>'처음 일하는 사람의 안전을 배우다','audience'=>'직업계고 학생·교사와 처음 일터에 들어서는 청년','body'=>'일을 시작하기 전 위험을 알아보고 질문할 수 있는 교육을 준비합니다. 교과서와 학습자료를 노동안전의 관점에서 살펴보고 학교와 일터에서 안전할 권리를 배우는 기반을 만듭니다.','provision'=>'교과서 기초연구와 교육·토론회, 학교에서 활용할 학습자료를 연결합니다. 공개 가능한 자료는 확인 후 안내합니다.'],
 ['slug'=>'tools','title'=>'온라인 도구','summary'=>'일터에서 권리와 안전을 챙기는 도구','audience'=>'안전·노동 정보를 찾는 청년과 수업 자료가 필요한 교사','body'=>'복잡한 일터의 정보를 더 쉽게 찾고 배울 수 있도록 온라인 학습도구를 준비합니다. 도구마다 대상과 제공 범위를 확인한 뒤 이용 방법을 안내합니다.','provision'=>'노동안전 학습도구와 정보 탐색, 교사용 자료 제작을 위한 도구의 이용 안내를 연결합니다.'],
 ['slug'=>'community','title'=>'연결·모임','summary'=>'고민을 함께 풀 사람을 만나다','audience'=>'함께 배우고 경험을 나누려는 청년·학생과 협력단체','body'=>'학교와 일터에서 겪는 고민을 혼자 풀지 않아도 되도록 사람과 모임을 연결합니다. 학생이 필요한 앱과 웹을 직접 만드는 바이브코딩동아리와 학교·지역의 만남을 이어갑니다.','provision'=>'바이브코딩동아리 안내와 실제 운영 가능한 협력 문의 경로를 제공합니다. 전문 상담·자동 전문가 연결을 약속하지 않습니다.'],
 ['slug'=>'voice','title'=>'목소리·정책','summary'=>'보이지 않는 노동을 사회의 의제로','audience'=>'제조현장 청년노동자, 시민·언론·정책 관계자','body'=>'잘 드러나지 않는 청년의 노동과 삶을 함께 보고 사회의 의제로 이어갑니다. 당사자의 경험과 칼럼, 정책·제도 제안, 강좌를 통해 질문을 나눕니다.','provision'=>'연속강좌와 공개 글, 확인된 활동 기록을 연결합니다.']
 ];
 foreach($areas as $i=>&$a) $a+= $published+['phase'=>'preparing','sort_order'=>$i+1]; unset($a);
 $funding=$published+['slug'=>'incubating-2026','title'=>'아름다운재단 공익단체 인큐베이팅 지원사업','summary'=>'학교에서 일터로, 안전하게 일할 권리를 함께 준비합니다.','body'=>'청년노동자인권센터는 아름다운재단 인큐베이팅 지원사업을 통해 직업계고 학생과 청년노동자가 안전하게 일할 권리를 배우고 실현할 기반을 마련하고자 합니다. 교과서 기초연구, 온라인 안전 학습도구, 학교 캠페인과 학생이 직접 만드는 바이브코딩동아리를 연결합니다. 학교·지역과 함께 활동을 이어갈 기반을 모색합니다.','year'=>2026,'term'=>1,'sponsor'=>'아름다운재단'];
 $units=[
 ['slug'=>'textbook-research','title'=>'노동안전보건 교과서 기초연구','summary'=>'교과서에서 안전할 권리를 찾다','body'=>'직업계고 교과서 분석, 기초연구보고서와 전문 기술교정·인터뷰, 연구를 공유하는 교육·토론회를 준비합니다.','areas'=>['education','voice'],'action_label'=>'교육프로그램 보기','action_path'=>'work/programs/2026-youth-labor'],
 ['slug'=>'safety-app','title'=>'노동안전보건 APP','summary'=>'배우고 활용하는 안전 학습도구','body'=>'반도체 교과서 기반 학습용 이미지·영상과 챗봇 등 학생·교사가 활용할 학습도구와 직접 제작 역량을 준비합니다.','areas'=>['tools'],'action_label'=>'도구 안내 보기','action_path'=>'tools'],
 ['slug'=>'school-campaign','title'=>'학교 캠페인','summary'=>'학교 안에서 만나는 노동과 안전','body'=>'학교 안에서 학생과 교사를 만나는 커피차형 캠페인과 노동안전 교육자료 안내를 추진합니다. 실제 진행 소식은 확인 후 공개합니다.','areas'=>['education','community'],'action_label'=>'학교 협력 문의','action_path'=>'#contact'],
 ['slug'=>'vibecoding','title'=>'바이브코딩동아리','registered_name'=>'AI 학습동아리','summary'=>'학생이 직접 만드는 앱과 웹','body'=>'코딩을 배우지 않아도 AI와 대화하며 필요한 앱과 웹을 직접 만들어 봅니다. 바이브코딩도구로 자기 필요에서 시작하는 제작과 학습을 함께합니다.','areas'=>['community','tools'],'action_label'=>'동아리 자세히 보기','action_path'=>'club']
 ];
 foreach($units as $i=>&$u) $u+=$published+['phase'=>'preparing','sort_order'=>$i+1]; unset($u);
 $programs=[
 $published+['slug'=>'2026-youth-labor','title'=>'“보이지 않는” 청년의 노동을 “보다”','summary'=>'직업계고에서, 지방의 공장에서, 학교 밖에서. 잘 드러나지 않던 청년들의 일과 삶을 함께 봅니다.','body'=>'2026년 10월 14일부터 11월 4일까지, 매주 수요일 저녁 7시에 네 번의 만남을 갖습니다. 특성화고 학생들의 삶, 공고생들의 일상과 고민, 조선소에서 본 지역 청년 노동과 반도체고 노동안전교과서 기초연구를 함께 살펴봅니다.','kind'=>'course','area_slug'=>'voice','audience'=>'청년의 노동과 삶, 안전교육을 함께 고민하는 누구나','venue'=>'사단법인 희망씨 · 서울 종로구 성균관로 12, 5층','fee'=>'무료','application_url'=>'https://zealot-survey.vercel.app/srcqw8uVJpFR','recruitment_status'=>'pending','recruitment_mode'=>'dated','deadline_at'=>null,'contact_email'=>'admin@younglabor.kr','organizers'=>'청년노동자인권센터 · 우리동네노동권찾기 · 사회혁신해봄협동조합 · 서울노동광장','credit'=>'이 행사는 아름다운재단 지원으로 진행됩니다.','home_featured'=>1],
 $published+['slug'=>'vibecoding-club','title'=>'바이브코딩동아리','summary'=>'코딩을 몰라도, AI와 대화하며 나에게 필요한 앱과 웹을 만듭니다.','body'=>'나에게 필요했지만 상상만 했던 앱을 직접 만들어 봅니다. 과제 자료를 정리하는 도우미, 공부 도구, 게임 공략 챗봇처럼 자기 필요에서 시작하세요. 바이브코딩도구로 원하는 것을 설명하고 함께 만들며 배웁니다.','kind'=>'club','area_slug'=>'community','audience'=>'반도체고등학교 재학생. 코딩 경험이 없어도 됩니다.','venue'=>null,'fee'=>'무료','application_url'=>'https://zealot-survey.vercel.app/RJXag60aMfMT','recruitment_status'=>'open','recruitment_mode'=>'rolling','deadline_at'=>null,'contact_email'=>'admin@younglabor.kr','organizers'=>'청년노동자인권센터','credit'=>'이 사업은 아름다운재단 지원으로 진행됩니다.','support_details'=>'바이브코딩도구 이용 지원과 제작·학습 안내','home_featured'=>1]
 ];
 $sessions=[
 ['number'=>1,'starts_at'=>'2026-10-14 19:00:00','speaker'=>'신수연','title'=>'우리는 학생이자 노동자였다','description'=>'특성화고 학생들의 삶','mode'=>'hybrid','panel'=>null],
 ['number'=>2,'starts_at'=>'2026-10-21 19:00:00','speaker'=>'연혜원','title'=>'지연된 실패를 넘어','description'=>'공고생들의 일상과 고민','mode'=>'hybrid','panel'=>null],
 ['number'=>3,'starts_at'=>'2026-10-28 19:00:00','speaker'=>'천현우','title'=>'쇳밥일지, 그 후','description'=>'조선소에서 본 지역 청년 노동자의 삶','mode'=>'online','panel'=>null],
 ['number'=>4,'starts_at'=>'2026-11-04 19:00:00','speaker'=>'박동욱','title'=>'보이지 않는 위험을 가르치다','description'=>'반도체고 노동안전교과서 기초연구 발제 및 종합토론','mode'=>'hybrid','panel'=>'토론 패널 추후 공지']
 ];
 return compact('areas','funding','units','programs','sessions');
}
function renewalInsertIfAbsent(PDO $p,string $table,array $row): array {
 $q=$p->prepare("SELECT id FROM $table WHERE slug=?");$q->execute([$row['slug']]);$id=$q->fetchColumn(); if($id) return [(int)$id,false];
 $columns=array_keys($row); $q=$p->prepare("INSERT INTO $table (".implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($columns),'?')).')');$q->execute(array_values($row));return [(int)$p->lastInsertId(),true];
}
function seedRenewal(PDO $p): void {
 $d=renewalSeedData();$p->beginTransaction();
 try {
 $areaIds=[]; foreach($d['areas'] as $a) [$areaIds[$a['slug']]]=renewalInsertIfAbsent($p,'work_areas',$a);
 [$fundingId]=renewalInsertIfAbsent($p,'work_funding',$d['funding']);
 foreach($d['units'] as $u){$areas=$u['areas'];unset($u['areas']);$u['funding_id']=$fundingId;[$id,$created]=renewalInsertIfAbsent($p,'work_units',$u);if($created) foreach($areas as $a){$q=$p->prepare('INSERT INTO work_unit_areas(unit_id,area_id) VALUES (?,?)');$q->execute([$id,$areaIds[$a]]);}}
 foreach($d['programs'] as $program){$program['work_area_id']=$areaIds[$program['area_slug']];unset($program['area_slug']);[$id,$created]=renewalInsertIfAbsent($p,'work_programs',$program);if($created && $program['kind']==='course') foreach($d['sessions'] as $s){$s['program_id']=$id;$cols=array_keys($s);$q=$p->prepare('INSERT INTO work_sessions ('.implode(',',$cols).') VALUES ('.implode(',',array_fill(0,count($cols),'?')).')');$q->execute(array_values($s));}}
 $p->commit();
 } catch(Throwable $e){$p->rollBack();throw $e;}
}
