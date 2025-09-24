<?php
// 페이지네이션 컴포넌트
if (!isset($current_page) || !isset($total_pages) || $total_pages <= 1) {
    return;
}

$base_url = $base_url ?? '';
$params = $params ?? [];
$range = 5; // 표시할 페이지 수

$start_page = max(1, $current_page - floor($range / 2));
$end_page = min($total_pages, $start_page + $range - 1);

if ($end_page - $start_page + 1 < $range) {
    $start_page = max(1, $end_page - $range + 1);
}
?>

<nav aria-label="페이지 네비게이션">
    <ul class="pagination justify-content-center">
        <!-- 이전 페이지 -->
        <?php if ($current_page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $base_url ?>?<?= http_build_query(array_merge($params, ['page' => $current_page - 1])) ?>">
                    이전
                </a>
            </li>
        <?php endif; ?>
        
        <!-- 첫 페이지 -->
        <?php if ($start_page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $base_url ?>?<?= http_build_query(array_merge($params, ['page' => 1])) ?>">1</a>
            </li>
            <?php if ($start_page > 2): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- 페이지 번호들 -->
        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>?<?= http_build_query(array_merge($params, ['page' => $i])) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <!-- 마지막 페이지 -->
        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="<?= $base_url ?>?<?= http_build_query(array_merge($params, ['page' => $total_pages])) ?>">
                    <?= $total_pages ?>
                </a>
            </li>
        <?php endif; ?>
        
        <!-- 다음 페이지 -->
        <?php if ($current_page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $base_url ?>?<?= http_build_query(array_merge($params, ['page' => $current_page + 1])) ?>">
                    다음
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>