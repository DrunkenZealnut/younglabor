<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/ContentRepository.php';
require_once __DIR__ . '/../includes/ContentPresenter.php';

$slug = (string)($_GET['slug'] ?? '');
$post = null;
if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
    try {
        $repository = new ContentRepository(Database::getInstance()->getConnection());
        $post = $repository->findPublishedBySlug('activity', $slug);
    } catch (Throwable $error) {
        $post = null;
    }
}
if ($post === null) {
    http_response_code(404);
    $currentPage = 'activity';
    $pageTitle = '활동을 찾을 수 없습니다 - ' . $site['name'];
    $pageDescription = '요청한 활동 소식을 찾을 수 없습니다.';
    $pageUrl = url('activity/' . rawurlencode($slug));
    require_once __DIR__ . '/../includes/header.php';
    echo '<section class="section"><div class="container"><p class="content-empty">요청한 활동 소식을 찾을 수 없습니다.</p></div></section>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
$currentPage = 'activity';
$pageTitle = $post['title'] . ' - ' . $site['name'];
$pageDescription = (string)$post['summary'];
$pageUrl = url('activity/' . $post['slug']);
$jsonLd = [
    '@context'=>'https://schema.org', '@type'=>'Article', 'headline'=>$post['title'],
    'description'=>$post['summary'], 'datePublished'=>$post['content_date'],
    'dateModified'=>$post['updated_at'], 'mainEntityOfPage'=>$pageUrl,
    'publisher'=>['@type'=>'Organization','name'=>$site['name'],'url'=>$site['base_url']],
];
if (!empty($post['file_id'])) $jsonLd['image'] = url('media/' . (int)$post['file_id']);
require_once __DIR__ . '/../includes/header.php';
?>
<script type="application/ld+json"><?php echo json_encode($jsonLd, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<article class="content-detail">
    <header class="page-header"><div class="container"><a class="content-back" href="<?php echo htmlspecialchars(url('activity'), ENT_QUOTES, 'UTF-8'); ?>">← 활동게시판</a><h1><?php echo htmlspecialchars($post['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1><p><?php echo htmlspecialchars((string)$post['summary'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p><div class="content-meta"><time datetime="<?php echo htmlspecialchars($post['content_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($post['content_date'], ENT_QUOTES, 'UTF-8'); ?></time><span>수정 <?php echo htmlspecialchars(substr($post['updated_at'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></span></div></div></header>
    <div class="section"><div class="container content-reading">
        <?php if (!empty($post['file_id'])): ?><figure class="detail-cover"><img src="<?php echo htmlspecialchars(url('media/' . (int)$post['file_id']), ENT_QUOTES, 'UTF-8'); ?>" srcset="<?php echo htmlspecialchars(contentImageSrcset($post), ENT_QUOTES, 'UTF-8'); ?>" sizes="(max-width: 900px) 100vw, 860px" alt="<?php echo htmlspecialchars((string)$post['file_alt_text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"<?php if (!empty($post['file_width']) && !empty($post['file_height'])): ?> width="<?php echo (int)$post['file_width']; ?>" height="<?php echo (int)$post['file_height']; ?>"<?php endif; ?> decoding="async"></figure><?php endif; ?>
        <div class="plain-content"><?php echo renderPlainText((string)$post['body']); ?></div>
    </div></div>
</article>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
