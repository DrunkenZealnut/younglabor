<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/ContentRepository.php';
require_once __DIR__ . '/../includes/ContentPresenter.php';
require_once __DIR__ . '/../includes/ContentValidation.php';
require_once __DIR__ . '/../includes/ContentStorage.php';
require_once __DIR__ . '/../includes/ContentManager.php';

$db = Database::getInstance()->getConnection();
$repository = new ContentRepository($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $_SESSION['content_flash'] = ['type'=>'error', 'message'=>'요청을 확인할 수 없습니다. 다시 시도해 주세요.'];
    } elseif (($_POST['action'] ?? '') === 'retry_cleanup') {
        try {
            $storage = new ContentStorage(contentStoragePath());
            $manager = new ContentManager($db, $repository, $storage);
            $completed = $manager->retryPendingCleanup();
            $_SESSION['content_flash'] = ['type'=>'success', 'message'=>'파일 정리 재시도 완료: ' . $completed . '건'];
        } catch (Throwable $error) {
            $_SESSION['content_flash'] = ['type'=>'error', 'message'=>'파일 정리를 재시도하지 못했습니다.'];
        }
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $revision = filter_var($_POST['expected_revision'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        try {
            if ($id === false || $revision === false) {
                throw new ContentConflictException('Invalid delete request.');
            }
            $storage = new ContentStorage(contentStoragePath());
            $manager = new ContentManager($db, $repository, $storage);
            $manager->delete((int)$id, (int)$revision);
            $_SESSION['content_flash'] = ['type'=>'success', 'message'=>'콘텐츠를 삭제했습니다.'];
        } catch (Throwable $error) {
            $_SESSION['content_flash'] = ['type'=>'error', 'message'=>'삭제하지 못했습니다. 새로고침 후 다시 시도해 주세요.'];
        }
    }
    header('Location: ' . url('admin/content.php'));
    exit;
}

$allowedTypes = ['activity','press','resource'];
$allowedStatuses = ['draft','published'];
$type = in_array($_GET['type'] ?? '', $allowedTypes, true) ? (string)$_GET['type'] : '';
$status = in_array($_GET['status'] ?? '', $allowedStatuses, true) ? (string)$_GET['status'] : '';
$search = mb_substr(trim((string)($_GET['search'] ?? '')), 0, 100, 'UTF-8');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$filters = ['type'=>$type, 'status'=>$status, 'search'=>$search];
$totalCount = $repository->countAdmin($filters);
$totalPages = max(1, (int)ceil($totalCount / $perPage));
$page = min($page, $totalPages);
$rows = $repository->listAdmin($filters, $perPage, ($page - 1) * $perPage);
$flash = $_SESSION['content_flash'] ?? null;
unset($_SESSION['content_flash']);

adminHeader();
?>
<div class="main-header">
    <h1>콘텐츠 관리</h1>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <form method="post"><?php echo csrfField(); ?><input type="hidden" name="action" value="retry_cleanup"><button class="btn btn-outline" type="submit">파일 정리 재시도</button></form>
        <a class="btn btn-primary" href="<?php echo e(url('admin/content-edit.php')); ?>">새 콘텐츠</a>
    </div>
</div>

<?php if ($flash): ?>
<div class="card" style="margin-bottom:16px;border-left:4px solid <?php echo $flash['type'] === 'success' ? '#22c55e' : '#ef4444'; ?>">
    <?php echo e((string)$flash['message']); ?>
</div>
<?php endif; ?>

<div class="toolbar">
    <form method="get" style="display:flex;gap:12px;flex:1;flex-wrap:wrap;align-items:center">
        <input type="text" name="search" placeholder="제목 또는 요약 검색" value="<?php echo e($search); ?>">
        <select name="type">
            <option value="">전체 유형</option>
            <?php foreach ($allowedTypes as $option): ?>
                <option value="<?php echo e($option); ?>"<?php echo $type === $option ? ' selected' : ''; ?>><?php echo e(contentTypeLabel($option)); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">전체 상태</option>
            <option value="draft"<?php echo $status === 'draft' ? ' selected' : ''; ?>>임시저장</option>
            <option value="published"<?php echo $status === 'published' ? ' selected' : ''; ?>>공개</option>
        </select>
        <button class="btn btn-primary btn-sm" type="submit">검색</button>
        <a class="btn btn-outline btn-sm" href="<?php echo e(url('admin/content.php')); ?>">초기화</a>
    </form>
    <span style="font-size:13px;color:#64748b">총 <?php echo number_format($totalCount); ?>건</span>
</div>

<div class="card">
<?php if ($rows === []): ?>
    <p style="text-align:center;padding:40px 0;color:#94a3b8">등록된 콘텐츠가 없습니다.</p>
<?php else: ?>
    <div style="overflow-x:auto">
    <table class="data-table">
        <thead><tr><th>유형</th><th>제목</th><th>상태</th><th>기준일</th><th>수정일</th><th>관리</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?php echo e(contentTypeLabel((string)$row['type'])); ?></td>
            <td><strong><?php echo e((string)$row['title']); ?></strong><br><small style="color:#94a3b8"><?php echo e((string)$row['slug']); ?></small></td>
            <td><?php echo $row['status'] === 'published' ? '<span style="color:#15803d">공개</span>' : '<span style="color:#a16207">임시저장</span>'; ?></td>
            <td><?php echo e((string)$row['content_date']); ?></td>
            <td><?php echo e((string)$row['updated_at']); ?></td>
            <td style="white-space:nowrap">
                <a class="btn btn-outline btn-sm" href="<?php echo e(url('admin/content-edit.php?id=' . (int)$row['id'])); ?>">수정</a>
                <form method="post" style="display:inline" onsubmit="return confirm('이 콘텐츠를 삭제할까요?');">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                    <input type="hidden" name="expected_revision" value="<?php echo (int)$row['revision']; ?>">
                    <button class="btn btn-danger btn-sm" type="submit">삭제</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
<div class="pagination">
<?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
    <?php $query = http_build_query(['page'=>$i,'type'=>$type,'status'=>$status,'search'=>$search]); ?>
    <?php if ($i === $page): ?><span class="active"><?php echo $i; ?></span><?php else: ?><a href="?<?php echo e($query); ?>"><?php echo $i; ?></a><?php endif; ?>
<?php endfor; ?>
</div>
<?php endif; ?>
</div>
<?php adminFooter(); ?>
