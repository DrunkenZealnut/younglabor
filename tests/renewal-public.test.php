<?php
require __DIR__.'/support/renewal.php';check(is_file(__DIR__.'/../includes/PublicContent.php'),'Public loader required');require __DIR__.'/../includes/PublicContent.php';
$x=loadPublicSection(static fn()=>throw new RuntimeException('private database details'),'board',[],1);
check($x['unavailable'] && $x['rows']===[],'failure safely distinguished');
$x=loadPublicSection(static fn()=>new class {function countPublicSection($s,$f){return 0;}function listPublicSection($s,$f,$l,$o){return [];}},'board',[],999);
check(!$x['unavailable'] && $x['page']===1,'empty result is not an outage');
echo "PASS public loader empty/failure distinction\n";

// Value: protects=공용 페이지 머리 renderPageIntro — 상단 라벨을 넘기는 페이지(사업소개·동아리·게시판·개인정보·안내 화면)는 라벨이 이스케이프된 채 h1 앞에 그대로 나오고, 빈 라벨(단체소개)은 라벨 요소 없이 h1로 시작하되 제목·설명은 유지; fails_when=조건문이 뒤집히거나 endif가 어긋나 다른 페이지 라벨이 사라지거나 빈 라벨일 때 h1/설명까지 빠짐, 빈 <p class="eyebrow">가 다시 찍혀 h1이 첫 요소가 아니게 됨, 라벨 이스케이프가 빠짐; why_new=renderPageIntro를 실제로 렌더링하는 테스트가 없음(이 파일은 loadPublicSection만, public-accessibility.test.js는 서버가 필요하고 h1 개수만 확인); seam=none(실제 함수를 두 입력으로 렌더링해 DOM으로 확인); no_claim=각 페이지가 어떤 라벨 문자열을 넘기는지, CSS 여백·제목 크기의 시각 결과
$intro=static function(string $eyebrow,string $title,string $description): array {
 ob_start();renderPageIntro($eyebrow,$title,$description);$html=ob_get_clean();
 $dom=new DOMDocument();libxml_use_internal_errors(true);$dom->loadHTML('<meta charset="utf-8">'.$html);libxml_clear_errors();
 $xp=new DOMXPath($dom);$container=$xp->query('//section[contains(concat(" ",normalize-space(@class)," ")," page-header ")]/div')->item(0);
 check($container instanceof DOMElement,'page header container rendered');
 $children=[];foreach($container->childNodes as $node)if($node instanceof DOMElement)$children[]=[$node->tagName,$node->getAttribute('class'),$node->textContent];
 return $children;
};
$label='사업소개 · <2026> & "1"년차';
$kids=$intro($label,'청년노동 기초 강좌','강좌 설명');
check(($kids[0]??null)===['p','eyebrow',$label],'non-empty eyebrow rendered first and escaped');
check(($kids[1][0]??'')==='h1' && $kids[1][2]==='청년노동 기초 강좌' && count(array_filter($kids,static fn($k)=>$k[0]==='h1'))===1,'title h1 follows eyebrow');
$title="일하는 청년의 곁에서,\n함께 바꿀 힘을 만듭니다.";
$kids=$intro('',$title,'단체 설명');
check(!array_filter($kids,static fn($k)=>in_array('eyebrow',explode(' ',$k[1]),true)),'empty eyebrow renders no eyebrow element');
check(($kids[0]??null)===['h1','',$title],'without eyebrow the h1 is the first element');
check(($kids[1]??null)===['p','','단체 설명'],'description kept without eyebrow');
$kids=$intro("  \t",$title,'단체 설명');
check(($kids[0][0]??'')==='h1' && !array_filter($kids,static fn($k)=>in_array('eyebrow',explode(' ',$k[1]),true)),'whitespace-only eyebrow renders no eyebrow element');
echo "PASS page intro eyebrow optional\n";
