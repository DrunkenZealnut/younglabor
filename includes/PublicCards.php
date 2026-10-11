<?php
require_once __DIR__.'/PublicContent.php';
function renderPublicCard(array $post): void { ?>
<article class="content-card">
<?php if(!empty($post['file_id']) && ($post['file_purpose']??'')==='cover'): ?><a class="content-cover" href="<?php echo publicEscape(url(publicPostPath($post))); ?>"><img src="<?php echo publicEscape(url('media/'.(int)$post['file_id'])); ?>" srcset="<?php echo publicEscape(contentImageSrcset($post)); ?>" sizes="(max-width: 600px) 100vw, 33vw" alt="<?php echo publicEscape($post['file_alt_text']??''); ?>" loading="lazy" decoding="async"<?php if(!empty($post['file_width'])): ?> width="<?php echo (int)$post['file_width']; ?>" height="<?php echo (int)$post['file_height']; ?>"<?php endif; ?>></a><?php endif; ?>
<div class="content-meta"><span><?php echo publicEscape(editorialCategoryLabel($post['editorial_category'] ?: $post['type'])); ?></span><time datetime="<?php echo publicEscape($post['content_date']); ?>"><?php echo publicEscape($post['content_date']); ?></time></div>
<h2><a href="<?php echo publicEscape(url(publicPostPath($post))); ?>"><?php echo publicEscape($post['title']); ?></a></h2><p><?php echo publicEscape($post['summary']); ?></p>
<?php if(!empty($post['public_author'])): ?><p class="content-author"><?php echo publicEscape($post['public_author']); ?></p><?php endif; ?>
</article>
<?php }
function renderWorkCard(array $row,int $index): void { ?>
<article class="work-card"><div class="work-card-top"><span class="work-number"><?php echo sprintf('%02d',$index+1); ?></span><span class="state-label"><?php echo publicEscape(workPhaseLabel($row['phase'])); ?></span></div><h3><a href="<?php echo publicEscape(url('work/areas/'.$row['slug'])); ?>"><?php echo publicEscape($row['title']); ?></a></h3><p><?php echo publicEscape($row['summary']); ?></p><a class="arrow-link" href="<?php echo publicEscape(url('work/areas/'.$row['slug'])); ?>">자세히 보기 <span aria-hidden="true">↗</span></a></article>
<?php }
function renderProgramCard(array $p): void { ?>
<article class="program-card"><div><p class="eyebrow"><?php echo $p['kind']==='club'?'함께 만드는 경험':'2026 하반기 연속강좌'; ?></p><span class="state-label"><?php echo publicEscape(programStateLabel($p)); ?></span><h3><a href="<?php echo publicEscape(url(programPath($p))); ?>"><?php echo publicEscape($p['title']); ?></a></h3><p><?php echo publicEscape($p['summary']); ?></p></div><a class="btn-cta" href="<?php echo publicEscape(url(programPath($p))); ?>"><?php echo $p['kind']==='club' && programAcceptsApplications($p)?'동아리 참여 안내':'프로그램 자세히 보기'; ?> <span aria-hidden="true">↗</span></a></article>
<?php }
