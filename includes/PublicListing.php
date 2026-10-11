<?php
require_once __DIR__.'/PublicCards.php';
$section=$publicSection;$category=requestText('category');$page=filter_var(requestText('page','1'),FILTER_VALIDATE_INT)?:1;
$categories=$section==='board'?[''=>'전체','notice'=>'공지','recruitment'=>'모집','activity'=>'활동 소식','press'=>'언론 소식']:[''=>'전체','story'=>'당사자 이야기','column'=>'칼럼','policy'=>'정책·제도 제안'];
if(!isset($categories[$category]))$category='';
$data=loadPublicSection('publicRepository',$section,['category'=>$category],$page);
$title=$section==='board'?'게시판':'청년과노동';$description=$section==='board'?'새로운 소식과 함께할 활동을 전합니다.':'일하는 청년의 경험, 그리고 변화를 위한 생각을 쌓습니다.';
startPublicPage($section,$title,$description,$section.($data['page']>1?'?page='.$data['page']:''));renderPageIntro('청쎈의 기록',$title,$description);
?>
<section class="section"><div class="container"><nav class="category-filters" aria-label="<?php echo $title; ?> 분류"><?php foreach($categories as $value=>$label): ?><a href="<?php echo publicEscape(url($section.($value!==''?'?category='.$value:''))); ?>"<?php echo $category===$value?' aria-current="page"':''; ?>><?php echo $label; ?></a><?php endforeach; ?></nav>
<?php if($section==='board' && in_array($category,['','recruitment'],true)): $programs=loadPublicWork(static fn($repo)=>$repo->listPrograms());if($programs['data']): ?><div class="board-programs"><h2 class="section-title">프로그램 안내</h2><div class="program-grid"><?php foreach($programs['data'] as $p)renderProgramCard($p); ?></div></div><?php endif; endif; ?>
<?php if($data['unavailable']): ?><p class="content-empty">글을 불러오지 못했습니다. 잠시 후 다시 확인해 주세요.</p><?php elseif(!$data['rows']): ?><p class="content-empty"><?php echo $section==='board'?'이 분류에 공개된 소식이 아직 없습니다.':'공개된 글을 준비하고 있습니다.'; ?></p><?php else: ?><div class="content-grid"><?php foreach($data['rows'] as $post)renderPublicCard($post); ?></div><?php endif; ?>
<?php if($data['pages']>1): ?><nav class="content-pagination" aria-label="<?php echo $title; ?> 페이지"><?php for($i=max(1,$data['page']-3);$i<=min($data['pages'],$data['page']+3);$i++): ?><a href="<?php echo publicEscape(url($section.'?'.http_build_query(['category'=>$category,'page'=>$i]))); ?>"<?php echo $i===$data['page']?' aria-current="page"':''; ?>><?php echo $i; ?></a><?php endfor; ?></nav><?php endif; ?>
</div></section>
<?php endPublicPage(); ?>
