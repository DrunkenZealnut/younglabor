<?php
require_once __DIR__.'/PublicCards.php';$slug=requestText('slug');$post=null;$unavailable=false;
try{$repo=publicRepository();$post=$publicSection==='board'?$repo->findPublicBoard($slug):$repo->findPublishedBySlug('article',$slug);}catch(Throwable $e){$unavailable=true;}
if(!$post)http_response_code($unavailable?503:404);
startPublicPage($publicSection,$post['title']??'글 안내',$post['summary']??'글 안내',$publicSection.'/'.$slug);
if(!$post){renderUnavailableDetail(['data'=>null,'state'=>$unavailable?'unavailable':'ready']);endPublicPage();exit;}
renderPageIntro(editorialCategoryLabel($post['editorial_category'] ?: $post['type']),$post['title'],$post['summary']??'');
?>
<section class="section"><div class="container content-reading"><a class="content-back" href="<?php echo publicEscape(url($publicSection)); ?>">← <?php echo $publicSection==='board'?'게시판':'청년과노동'; ?></a><div class="detail-meta"><time datetime="<?php echo publicEscape($post['content_date']); ?>"><?php echo publicEscape($post['content_date']); ?></time><?php if(!empty($post['public_author'])): ?><span><?php echo publicEscape($post['public_author']); ?></span><?php endif; ?></div>
<?php if(!empty($post['file_id']) && $post['file_purpose']==='cover'): ?><div class="detail-cover"><img src="<?php echo publicEscape(url('media/'.(int)$post['file_id'])); ?>" srcset="<?php echo publicEscape(contentImageSrcset($post)); ?>" sizes="(max-width: 860px) 100vw, 860px" alt="<?php echo publicEscape($post['file_alt_text']); ?>" width="<?php echo (int)$post['file_width']; ?>" height="<?php echo (int)$post['file_height']; ?>"></div><?php endif; ?>
<?php if($post['type']==='press'): ?><p>출처 · <?php echo publicEscape($post['outlet']); ?></p><a class="content-external" href="<?php echo publicEscape($post['external_url']); ?>" target="_blank" rel="noopener noreferrer">원문 보기 · <?php echo publicEscape(externalLinkHost($post['external_url'])); ?> (외부 링크) ↗</a><?php else: ?><div class="plain-content"><?php echo renderPlainText($post['body']??''); ?></div><?php endif; ?>
<?php if(!empty($post['program_id'])): $program=loadPublicWork(static fn($r)=>$r->findPublishedProgramById((int)$post['program_id']));if($program['data']): ?><div class="resource-action"><h2>관련 프로그램</h2><?php renderProgramCard($program['data']); ?></div><?php endif; endif; ?>
</div></section>
<?php endPublicPage(); ?>
