<?php
/**
 * Labor Rights Card Component
 * 노동권 교육 관련 정보 카드 컴포넌트
 */

$title = $title ?? '제목';
$description = $description ?? '';
$icon = $icon ?? 'bi-info-circle';
$link = $link ?? '#';
$badge = $badge ?? '';
$status = $status ?? 'active';
?>

<div class="card labor-rights-card h-100 <?= $status === 'inactive' ? 'border-secondary' : '' ?>">
    <div class="card-body">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0">
                <div class="icon-box <?= $status === 'inactive' ? 'bg-secondary' : 'bg-primary' ?>">
                    <i class="bi <?= $escape($icon) ?> text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0"><?= $escape($title) ?></h5>
                    <?php if ($badge): ?>
                        <span class="badge bg-info"><?= $escape($badge) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ($description): ?>
                    <p class="card-text text-muted small"><?= $escape($description) ?></p>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="<?= $escape($link) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-right me-1"></i>자세히 보기
                    </a>
                    
                    <?php if (isset($stats) && is_array($stats)): ?>
                        <div class="text-muted small">
                            <?php foreach ($stats as $stat_key => $stat_value): ?>
                                <span class="me-2">
                                    <strong><?= number_format($stat_value) ?></strong> <?= $escape($stat_key) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.labor-rights-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #e9ecef;
}

.labor-rights-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.icon-box {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.labor-rights-card .card-title {
    color: #2d3748;
    font-weight: 600;
}

.labor-rights-card .btn {
    border-radius: 6px;
    font-weight: 500;
}
</style>