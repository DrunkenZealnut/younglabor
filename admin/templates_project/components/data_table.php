<?php
/**
 * Data Table Component
 * 데이터 테이블을 렌더링하는 컴포넌트
 */

// 필요한 변수들
$data = $data ?? [];
$table_config = $table_config ?? [];
$columns = $columns ?? [];
$row_actions = $row_actions ?? [];

// 기본 설정
$table_class = $table_config['class'] ?? 'table table-hover';
$striped = $table_config['striped'] ?? true;
$responsive = $table_config['responsive'] ?? true;
$empty_message = $table_config['empty_message'] ?? '데이터가 없습니다.';

if ($striped) {
    $table_class .= ' table-striped';
}
?>

<?php if ($responsive): ?><div class="table-responsive"><?php endif; ?>

<table class="<?= $table_class ?>">
    <thead>
        <tr>
            <?php foreach ($columns as $column): ?>
                <th width="<?= $column['width'] ?? 'auto' ?>">
                    <?= htmlspecialchars($column['title']) ?>
                </th>
            <?php endforeach; ?>
            <?php if (!empty($row_actions)): ?>
                <th width="200">작업</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data)): ?>
            <tr>
                <td colspan="<?= count($columns) + (!empty($row_actions) ? 1 : 0) ?>" class="text-center py-4 text-muted">
                    <?= htmlspecialchars($empty_message) ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <td>
                            <?php
                            $value = $row[$column['name']] ?? '';
                            
                            if (isset($column['callback']) && is_callable($column['callback'])) {
                                echo $column['callback']($value, $row);
                            } elseif ($column['type'] === 'badge' && isset($column['badge_map'])) {
                                $badge_class = $column['badge_map'][$value] ?? 'bg-secondary';
                                echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($value) . '</span>';
                            } elseif ($column['type'] === 'html') {
                                echo $value; // HTML 내용은 그대로 출력
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                    
                    <?php if (!empty($row_actions)): ?>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php foreach ($row_actions as $action): ?>
                                    <?php
                                    $url = str_replace(array_map(fn($k) => '{' . $k . '}', array_keys($row)), array_values($row), $action['url']);
                                    $onclick = isset($action['onclick']) ? str_replace(array_map(fn($k) => '{' . $k . '}', array_keys($row)), array_values($row), $action['onclick']) : '';
                                    ?>
                                    <?php if ($action['url'] === '#'): ?>
                                        <button type="button" class="<?= $action['class'] ?? 'btn btn-sm btn-outline-primary' ?>"
                                                onclick="<?= htmlspecialchars($onclick) ?>">
                                            <?php if (isset($action['icon'])): ?>
                                                <i class="<?= $action['icon'] ?>"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($action['text']) ?>
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars($url) ?>" 
                                           class="<?= $action['class'] ?? 'btn btn-sm btn-outline-primary' ?>">
                                            <?php if (isset($action['icon'])): ?>
                                                <i class="<?= $action['icon'] ?>"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($action['text']) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($responsive): ?></div><?php endif; ?>