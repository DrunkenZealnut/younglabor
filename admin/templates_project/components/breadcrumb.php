<?php
// 브레드크럼 컴포넌트
if (empty($breadcrumb) || !is_array($breadcrumb)) {
    return;
}
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumb as $index => $item): ?>
            <?php if ($index === count($breadcrumb) - 1): ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($item['title']) ?>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="<?= htmlspecialchars($item['url']) ?>">
                        <?= htmlspecialchars($item['title']) ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>