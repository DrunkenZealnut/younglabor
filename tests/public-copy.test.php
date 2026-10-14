<?php
$home=file_get_contents(__DIR__.'/../index.php');
$last=-1;
foreach(['일하는 청년이,','청쎈이 만나는 청년','네 가지 방식,','지금, 함께할 활동','청년과노동','새 소식','질문도, 제안도.'] as $copy){$at=strpos($home,$copy);if($at===false || $at<=$last)exit(1);$last=$at;}
if(str_contains($home,'숫자로 보는 활동'))exit(1);
if(!str_contains($home,"array_filter(\$programs,'programAcceptsApplications')"))exit(1);

// Value: protects=홈 첫 화면 히어로가 제목 하나와 '다음 행동' CTA를 유지 — 모집 중이면 '참여할 수 있는 활동 보기'→work#programs, 아니면 '청쎈의 활동 살펴보기'→work; fails_when=히어로 정리 중 CTA/hero-actions 블록이 지워지거나, 상태별 문구·목적지가 바뀌거나 뒤바뀜; why_new=위 검사는 문구 순서와 programAcceptsApplications 호출 문자열만 보고, 히어로 CTA를 참조하는 테스트가 없음(public-accessibility.test.js는 서버 필요, h1 개수만 확인); seam=none(히어로 템플릿 조각을 $hasOpen 두 값으로 실제 렌더링); no_claim=DB의 실제 프로그램 상태로 $hasOpen이 정해지는지, 시각 배치
require_once __DIR__.'/../config.php';require_once __DIR__.'/../includes/WorkPresenter.php';
if(!preg_match('/<section class="renewal-hero">.*?<\/section>/s',$home,$heroSource)){fwrite(STDERR,"홈 히어로 섹션을 찾지 못했습니다.\n");exit(1);}
foreach([true=>[url('work').'#programs','참여할 수 있는 활동 보기'],false=>[url('work'),'청쎈의 활동 살펴보기']] as $open=>[$href,$label]){
    $hasOpen=(bool)$open;ob_start();eval('?>'.$heroSource[0]);$hero=ob_get_clean();
    if(substr_count($hero,'<h1')!==1){fwrite(STDERR,"히어로 h1은 하나여야 합니다.\n");exit(1);}
    preg_match_all('/<a\b[^>]*\bhref="([^"]*)"[^>]*>(.*?)<\/a>/s',$hero,$links,PREG_SET_ORDER);
    $found=false;foreach($links as $link){if(html_entity_decode($link[1],ENT_QUOTES,'UTF-8')===$href && str_contains(strip_tags($link[2]),$label))$found=true;}
    if(!$found){fwrite(STDERR,"히어로 CTA가 없습니다(hasOpen=".var_export($hasOpen,true)."): {$label} → {$href}\n");exit(1);}
}
