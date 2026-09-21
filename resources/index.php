<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/ContentRepository.php';
require_once __DIR__ . '/../includes/ContentPresenter.php';
$page=max(1,(int)($_GET['page']??1)); $perPage=12; $rows=[]; $total=0; $unavailable=false;
try { $repository=new ContentRepository(Database::getInstance()->getConnection()); $total=$repository->countPublished('resource'); $pages=max(1,(int)ceil($total/$perPage)); $page=min($page,$pages); $rows=$repository->listPublished('resource',$perPage,($page-1)*$perPage); } catch(Throwable $error) { $pages=1; $unavailable=true; }
$currentPage='resources'; $pageTitle='자료실 - '.$site['name']; $pageDescription='교육, 연구, 현장 안내에 활용할 수 있는 자료를 나눕니다.'; $pageUrl=url('resources'.($page>1?'?page='.$page:''));
require_once __DIR__ . '/../includes/header.php';
?>
<section class="page-header"><div class="container"><div class="content-kicker">함께 쓰는 지식</div><h1>자료실</h1><p><?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?></p></div></section>
<section class="section"><div class="container">
<?php if($unavailable): ?><p class="content-empty">자료를 불러오지 못했습니다. 잠시 후 다시 확인해 주세요.</p>
<?php elseif($rows===[]): ?><p class="content-empty">공개된 자료가 아직 없습니다.</p>
<?php else: ?><div class="content-list"><?php foreach($rows as $row): ?><article class="content-list-item"><div class="content-meta"><span><?php echo htmlspecialchars(resourceCategoryLabel((string)$row['resource_category']), ENT_QUOTES, 'UTF-8'); ?></span><time datetime="<?php echo htmlspecialchars($row['content_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['content_date'], ENT_QUOTES, 'UTF-8'); ?></time></div><h2><a href="<?php echo htmlspecialchars(url('resources/'.$row['slug']), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h2><p><?php echo htmlspecialchars((string)$row['summary'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p></article><?php endforeach; ?></div><?php if($pages>1): ?><nav class="content-pagination" aria-label="자료실 페이지"><?php for($i=1;$i<=$pages;$i++): ?><a href="?page=<?php echo $i; ?>"<?php echo $i===$page?' aria-current="page"':''; ?>><?php echo $i; ?></a><?php endfor; ?></nav><?php endif; ?><?php endif; ?>
</div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
