<?php
/**
 * Education Progress Component
 * 교육 진행률 표시 컴포넌트
 */

$title = $title ?? '교육 과정';
$current = $current ?? 0;
$total = $total ?? 100;
$percentage = $total > 0 ? round(($current / $total) * 100, 1) : 0;
$color = $color ?? 'primary';
$show_numbers = $show_numbers ?? true;
$size = $size ?? 'md'; // sm, md, lg
?>

<div class="education-progress-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 text-dark"><?= $escape($title) ?></h6>
        <?php if ($show_numbers): ?>
            <small class="text-muted"><?= number_format($current) ?> / <?= number_format($total) ?></small>
        <?php endif; ?>
    </div>
    
    <div class="progress progress-<?= $size ?> mb-2">
        <div class="progress-bar bg-<?= $color ?>" 
             role="progressbar" 
             style="width: <?= $percentage ?>%"
             aria-valuenow="<?= $percentage ?>" 
             aria-valuemin="0" 
             aria-valuemax="100">
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center">
        <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?>">
            <?= $percentage ?>% 완료
        </span>
        
        <?php if (isset($deadline)): ?>
            <small class="text-muted">
                <i class="bi bi-calendar-event me-1"></i>
                <?= date('Y-m-d', strtotime($deadline)) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

<style>
.education-progress-card {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.progress-sm {
    height: 6px;
}

.progress-md {
    height: 8px;
}

.progress-lg {
    height: 12px;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.6s ease;
}
</style>