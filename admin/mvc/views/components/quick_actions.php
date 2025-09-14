<?php
/**
 * Quick Actions Component  
 * 관리자 빠른 작업 버튼들
 */

$actions = $actions ?? [
    ['title' => '새 게시글', 'icon' => 'bi-plus-circle', 'url' => 'posts/create.php', 'color' => 'primary'],
    ['title' => '게시판 관리', 'icon' => 'bi-layout-text-window', 'url' => 'boards/list.php', 'color' => 'success'],
    ['title' => '사용자 관리', 'icon' => 'bi-people', 'url' => 'users/list.php', 'color' => 'info'],
    ['title' => '설정', 'icon' => 'bi-gear', 'url' => 'settings/site_settings.php', 'color' => 'secondary']
];

$columns = $columns ?? 4;
$size = $size ?? 'md'; // sm, md, lg
?>

<div class="quick-actions-grid">
    <h5 class="mb-3">
        <i class="bi bi-lightning-charge me-2 text-warning"></i>
        빠른 작업
    </h5>
    
    <div class="row g-3">
        <?php foreach ($actions as $action): ?>
            <div class="col-md-<?= 12 / $columns ?>">
                <a href="<?= $escape($action['url']) ?>" class="quick-action-btn text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-3">
                            <div class="icon-circle bg-<?= $action['color'] ?> bg-opacity-10 mb-3">
                                <i class="bi <?= $escape($action['icon']) ?> text-<?= $action['color'] ?> fs-4"></i>
                            </div>
                            <h6 class="card-title text-dark mb-0"><?= $escape($action['title']) ?></h6>
                            <?php if (isset($action['description'])): ?>
                                <small class="text-muted"><?= $escape($action['description']) ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($action['badge'])): ?>
                            <div class="card-footer bg-transparent text-center py-2">
                                <span class="badge bg-<?= $action['color'] ?> bg-opacity-20 text-<?= $action['color'] ?>">
                                    <?= $escape($action['badge']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.quick-actions-grid .quick-action-btn {
    display: block;
    transition: transform 0.2s ease;
}

.quick-actions-grid .quick-action-btn:hover {
    transform: translateY(-3px);
}

.quick-actions-grid .card {
    border-radius: 12px;
    transition: all 0.2s ease;
}

.quick-actions-grid .card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.quick-actions-grid .card-title {
    font-size: 0.9rem;
    font-weight: 600;
}
</style>