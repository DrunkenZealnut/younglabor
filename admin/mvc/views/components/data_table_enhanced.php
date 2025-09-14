<?php
/**
 * Enhanced Data Table Component - Admin_templates 통합 버전
 * 
 * Admin_templates의 data_table 기능을 MVC 구조로 완전히 통합
 * 기존 templates_project/components/data_table.php 확장
 * 
 * 필수 변수:
 * - $columns: 컬럼 정의 배열 또는 $table_columns (Admin_templates 호환)
 * - $data: 테이블 데이터 배열 또는 $table_data (Admin_templates 호환)
 * 
 * 선택 변수:
 * - $table_config: 테이블 설정
 * - $row_actions: 액션 버튼 배열 또는 함수
 * - $empty_message: 데이터 없음 메시지
 * - $table_class: 추가 CSS 클래스
 * 
 * Admin_templates 호환성:
 * - $actions 함수 지원
 * - 기존 컬럼 정의 형식 지원
 * - 모든 기존 변수명 지원
 */

// Admin_templates 호환성을 위한 변수명 처리
$columns = $columns ?? $table_columns ?? [];
$data = $data ?? $table_data ?? [];
$row_actions = $row_actions ?? $actions ?? null;
$table_config = $table_config ?? [];

// 기본값 설정
$table_class = 'table table-striped table-hover ' . ($table_config['class'] ?? ($table_class ?? ''));
$empty_message = $empty_message ?? $table_config['empty_message'] ?? '데이터가 없습니다.';
$show_row_numbers = $table_config['show_row_numbers'] ?? false;
$sortable = $table_config['sortable'] ?? false;
$hover_effect = $table_config['hover_effect'] ?? true;

// 액션 처리 (함수형과 배열형 모두 지원)
$has_actions = !empty($row_actions);
$actions_is_function = is_callable($row_actions);

if (empty($columns) || !is_array($columns)) {
    echo '<!-- 테이블 컬럼이 정의되지 않았습니다. -->';
    return;
}
?>

<div class="table-responsive">
    <table class="<?= t_escape($table_class) ?>" <?= $sortable ? 'data-sortable="true"' : '' ?>>
        <thead class="table-light">
            <tr>
                <?php if ($show_row_numbers): ?>
                    <th width="50px">#</th>
                <?php endif; ?>
                
                <?php foreach ($columns as $column): ?>
                    <?php
                    // Admin_templates 형식과 새로운 형식 모두 지원
                    if (is_string($column)) {
                        $column = ['key' => $column, 'title' => $column];
                    }
                    
                    $key = $column['key'] ?? $column[0] ?? '';
                    $title = $column['title'] ?? $column['label'] ?? $key;
                    $width = $column['width'] ?? '';
                    $sortable_column = $column['sortable'] ?? $sortable;
                    ?>
                    <th <?= $width ? 'width="' . t_escape($width) . '"' : '' ?> 
                        <?= $sortable_column ? 'data-sortable="true" data-sort-key="' . t_escape($key) . '"' : '' ?>>
                        <?= t_escape($title) ?>
                        <?php if ($sortable_column): ?>
                            <i class="bi bi-arrow-down-up text-muted ms-1" style="font-size: 0.8em;"></i>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
                
                <?php if ($has_actions): ?>
                    <th width="<?= $table_config['action_width'] ?? '120px' ?>">관리</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="<?= count($columns) + ($has_actions ? 1 : 0) + ($show_row_numbers ? 1 : 0) ?>" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-3"></i>
                            <h5 class="mb-2"><?= t_escape($empty_message) ?></h5>
                            <?php if (isset($table_config['create_button'])): ?>
                                <a href="<?= t_escape($table_config['create_button']['url']) ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-circle"></i> <?= t_escape($table_config['create_button']['text']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($data as $index => $row): ?>
                    <tr <?= $hover_effect ? 'class="table-hover-row"' : '' ?> data-row-id="<?= t_escape($row['id'] ?? $index) ?>">
                        <?php if ($show_row_numbers): ?>
                            <td class="text-muted"><?= $index + 1 ?></td>
                        <?php endif; ?>
                        
                        <?php foreach ($columns as $column): ?>
                            <?php
                            // 컬럼 키 추출
                            $key = $column['key'] ?? $column[0] ?? '';
                            $value = $row[$key] ?? '';
                            ?>
                            <td>
                                <?php
                                // 커스텀 포맷터가 있으면 사용 (Admin_templates 호환)
                                if (isset($column['format']) && is_callable($column['format'])) {
                                    echo $column['format']($value, $row);
                                } 
                                // 렌더 함수가 있으면 사용 (새로운 형식)
                                elseif (isset($column['render']) && is_callable($column['render'])) {
                                    echo $column['render']($value, $row, $index);
                                }
                                // HTML 이스케이프 처리 (기본값: true)
                                elseif (!isset($column['escape']) || $column['escape'] !== false) {
                                    echo t_escape($value);
                                } 
                                // Raw HTML 출력
                                else {
                                    echo $value;
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        
                        <?php if ($has_actions): ?>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($actions_is_function): ?>
                                        <?= $row_actions($row, $index) ?>
                                    <?php else: ?>
                                        <?php foreach ((array)$row_actions as $action): ?>
                                            <?php
                                            $url = str_replace(['{id}', '{key}'], [$row['id'] ?? '', $row[$action['key'] ?? 'id'] ?? ''], $action['url'] ?? '#');
                                            $class = $action['class'] ?? 'btn-outline-primary';
                                            $icon = isset($action['icon']) ? '<i class="bi bi-' . $action['icon'] . '"></i> ' : '';
                                            $title = $action['title'] ?? $action['text'] ?? '';
                                            $confirm = $action['confirm'] ?? false;
                                            $confirm_message = $confirm ? str_replace('{title}', addslashes($row['title'] ?? $row['name'] ?? '항목'), $action['confirm_message'] ?? '정말로 삭제하시겠습니까?') : '';
                                            ?>
                                            <a href="<?= t_escape($url) ?>" 
                                               class="btn <?= t_escape($class) ?>" 
                                               title="<?= t_escape($title) ?>"
                                               <?= $confirm ? 'onclick="return confirm(\'' . $confirm_message . '\')"' : '' ?>>
                                                <?= $icon . t_escape($title) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($sortable): ?>
<!-- 테이블 정렬 기능 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortableHeaders = document.querySelectorAll('th[data-sortable="true"]');
    
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const sortKey = this.dataset.sortKey;
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // 정렬 상태 토글
            const isAsc = !this.classList.contains('sort-desc');
            
            // 모든 헤더에서 정렬 클래스 제거
            table.querySelectorAll('th').forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
            });
            
            // 현재 헤더에 정렬 클래스 추가
            this.classList.add(isAsc ? 'sort-asc' : 'sort-desc');
            
            // 정렬 수행
            rows.sort((a, b) => {
                const aVal = a.querySelector(`td:nth-child(${Array.from(this.parentNode.children).indexOf(this) + 1})`).textContent.trim();
                const bVal = b.querySelector(`td:nth-child(${Array.from(this.parentNode.children).indexOf(this) + 1})`).textContent.trim();
                
                if (isNaN(aVal) || isNaN(bVal)) {
                    return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                } else {
                    return isAsc ? parseFloat(aVal) - parseFloat(bVal) : parseFloat(bVal) - parseFloat(aVal);
                }
            });
            
            // 정렬된 행들을 다시 추가
            rows.forEach(row => tbody.appendChild(row));
        });
    });
});
</script>

<style>
.table th[data-sortable="true"] {
    user-select: none;
    position: relative;
}

.table th.sort-asc .bi-arrow-down-up::before {
    content: "\f145"; /* bi-arrow-up */
}

.table th.sort-desc .bi-arrow-down-up::before {
    content: "\f149"; /* bi-arrow-down */
}

.table-hover-row:hover {
    background-color: rgba(0, 123, 255, 0.08) !important;
    transition: background-color 0.15s ease-in-out;
}
</style>
<?php endif; ?>