<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/ContentRepository.php';
require_once __DIR__ . '/../includes/ContentPresenter.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$rows = [];
$total = 0;
$unavailable = false;
try {
    $repository = new ContentRepository(Database::getInstance()->getConnection());
    $total = $repository->countPublished('activity');
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $pages);
    $rows = $repository->listPublished('activity', $perPage, ($page - 1) * $perPage);
} catch (Throwable $error) {
    $pages = 1;
    $unavailable = true;
}
$currentPage = 'activity';
$pageTitle = '활동게시판 - ' . $site['name'];
$pageDescription = '청년노동자의 안전할 권리를 위해 현장에서 이어 온 활동을 전합니다.';
$pageUrl = url('activity' . ($page > 1 ? '?page=' . $page : ''));
require_once __DIR__ . '/../includes/header.php';
?>
<section class="page-header"><div class="container"><div class="content-kicker">현장에서 전하는 소식</div><h1>활동게시판</h1><p><?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?></p></div></section>
<section class="section"><div class="container">
<?php if ($unavailable): ?>
    <p class="content-empty">최근 활동을 불러오지 못했습니다. 잠시 후 다시 확인해 주세요.</p>
<?php elseif ($rows === []): ?>
    <p class="content-empty">공개된 활동 소식이 아직 없습니다.</p>
<?php else: ?>
    <div class="content-grid">
    <?php foreach ($rows as $row): ?>
        <article class="content-card">
            <?php if (!empty($row['file_id']) && $row['file_purpose'] === 'cover'): ?><a href="<?php echo htmlspecialchars(url('activity/' . $row['slug']), ENT_QUOTES, 'UTF-8'); ?>" class="content-cover"><img src="<?php echo htmlspecialchars(url('media/' . (int)$row['file_id']), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)$row['file_alt_text'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy"></a><?php endif; ?>
            <div class="content-meta"><time datetime="<?php echo htmlspecialchars($row['content_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['content_date'], ENT_QUOTES, 'UTF-8'); ?></time></div>
            <h2><a href="<?php echo htmlspecialchars(url('activity/' . $row['slug']), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></a></h2>
            <p><?php echo htmlspecialchars((string)$row['summary'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
        </article>
    <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?><nav class="content-pagination" aria-label="활동게시판 페이지"><?php for ($i=1;$i<=$pages;$i++): ?><a href="?page=<?php echo $i; ?>"<?php echo $i === $page ? ' aria-current="page"' : ''; ?>><?php echo $i; ?></a><?php endfor; ?></nav><?php endif; ?>
<?php endif; ?>
</div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
