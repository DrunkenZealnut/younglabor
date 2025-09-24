<?php
/**
 * Enhanced Pagination Component - Admin_templates 통합 버전
 * 
 * Admin_templates의 pagination 기능을 MVC 구조로 완전히 통합
 * 기존 templates_project/components/pagination.php 확장
 * 
 * 필수 변수:
 * - $pagination: 페이지네이션 데이터 (paginate() 함수 또는 수동 구성)
 * 
 * 선택 변수:
 * - $base_url: 기본 URL (기본값: 현재 페이지)
 * - $show_info: 페이지 정보 표시 여부 (기본값: true)
 * - $show_first_last: 처음/마지막 버튼 표시 여부 (기본값: true)
 * - $compact: 컴팩트 모드 (기본값: false)
 * 
 * 페이지네이션 데이터 구조:
 * [
 *   'current_page' => 현재 페이지,
 *   'total_pages' => 총 페이지 수,
 *   'total_items' => 총 아이템 수,
 *   'items_per_page' => 페이지당 아이템 수,
 *   'has_prev' => 이전 페이지 존재 여부,
 *   'has_next' => 다음 페이지 존재 여부,
 *   'prev_page' => 이전 페이지 번호,
 *   'next_page' => 다음 페이지 번호,
 *   'pages' => 표시할 페이지 번호 배열,
 *   'url_params' => URL 파라미터 배열
 * ]
 */

// 페이지네이션 데이터 검증
if (!isset($pagination) || !is_array($pagination)) {
    return;
}

// 필수 필드 기본값 설정
$current_page = $pagination['current_page'] ?? 1;
$total_pages = $pagination['total_pages'] ?? 1;
$total_items = $pagination['total_items'] ?? 0;
$items_per_page = $pagination['items_per_page'] ?? 10;

// 페이지가 1개 이하면 표시하지 않음
if ($total_pages <= 1) {
    return;
}

// 선택 변수 기본값
$show_info = $show_info ?? true;
$show_first_last = $show_first_last ?? true;
$compact = $compact ?? false;
$base_url = $base_url ?? $_SERVER['REQUEST_URI'];

// URL 파라미터 처리
$url_params = $pagination['url_params'] ?? $_GET ?? [];

// 이전/다음 페이지 계산
$has_prev = $pagination['has_prev'] ?? ($current_page > 1);
$has_next = $pagination['has_next'] ?? ($current_page < $total_pages);
$prev_page = $pagination['prev_page'] ?? ($has_prev ? $current_page - 1 : 1);
$next_page = $pagination['next_page'] ?? ($has_next ? $current_page + 1 : $total_pages);

// 페이지 번호 배열 생성 (Admin_templates 호환)
if (!isset($pagination['pages'])) {
    $range = $compact ? 2 : 3; // 현재 페이지 양쪽으로 표시할 페이지 수
    $start = max(1, $current_page - $range);
    $end = min($total_pages, $current_page + $range);
    
    // 시작이나 끝에 가까우면 더 많이 표시
    if ($current_page <= $range + 1) {
        $end = min($total_pages, ($range * 2) + 1);
    } elseif ($current_page >= $total_pages - $range) {
        $start = max(1, $total_pages - ($range * 2));
    }
    
    $pages = range($start, $end);
} else {
    $pages = $pagination['pages'];
}

// URL 생성 함수 (Admin_templates 호환)
function build_page_url($page, $base_url, $params) {
    // 현재 URL에서 쿼리 파라미터 제거
    $base = strtok($base_url, '?');
    
    // 페이지 파라미터 추가/수정
    $params['page'] = $page;
    
    return $base . '?' . http_build_query($params);
}
?>

<nav aria-label="페이지 네비게이션" class="pagination-nav mt-4">
    <ul class="pagination <?= $compact ? 'pagination-sm' : '' ?> justify-content-center">
        
        <?php if ($show_first_last && $current_page > 2): ?>
            <!-- 처음 페이지 -->
            <li class="page-item">
                <a class="page-link" href="<?= build_page_url(1, $base_url, $url_params) ?>" title="첫 페이지">
                    <i class="bi bi-chevron-double-left"></i>
                    <?php if (!$compact): ?> 처음<?php endif; ?>
                </a>
            </li>
        <?php endif; ?>
        
        <!-- 이전 페이지 -->
        <li class="page-item <?= !$has_prev ? 'disabled' : '' ?>">
            <?php if ($has_prev): ?>
                <a class="page-link" href="<?= build_page_url($prev_page, $base_url, $url_params) ?>" title="이전 페이지">
                    <i class="bi bi-chevron-left"></i>
                    <?php if (!$compact): ?> 이전<?php endif; ?>
                </a>
            <?php else: ?>
                <span class="page-link" tabindex="-1">
                    <i class="bi bi-chevron-left"></i>
                    <?php if (!$compact): ?> 이전<?php endif; ?>
                </span>
            <?php endif; ?>
        </li>
        
        <!-- 페이지 번호들 -->
        <?php foreach ($pages as $page): ?>
            <li class="page-item <?= $page == $current_page ? 'active' : '' ?>">
                <?php if ($page == $current_page): ?>
                    <span class="page-link" aria-current="page">
                        <?= $page ?>
                        <span class="visually-hidden">(현재 페이지)</span>
                    </span>
                <?php else: ?>
                    <a class="page-link" href="<?= build_page_url($page, $base_url, $url_params) ?>" title="<?= $page ?>페이지">
                        <?= $page ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        
        <!-- 다음 페이지 -->
        <li class="page-item <?= !$has_next ? 'disabled' : '' ?>">
            <?php if ($has_next): ?>
                <a class="page-link" href="<?= build_page_url($next_page, $base_url, $url_params) ?>" title="다음 페이지">
                    <?php if (!$compact): ?>다음 <?php endif; ?><i class="bi bi-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="page-link" tabindex="-1">
                    <?php if (!$compact): ?>다음 <?php endif; ?><i class="bi bi-chevron-right"></i>
                </span>
            <?php endif; ?>
        </li>
        
        <?php if ($show_first_last && $current_page < $total_pages - 1): ?>
            <!-- 마지막 페이지 -->
            <li class="page-item">
                <a class="page-link" href="<?= build_page_url($total_pages, $base_url, $url_params) ?>" title="마지막 페이지">
                    <?php if (!$compact): ?>마지막 <?php endif; ?><i class="bi bi-chevron-double-right"></i>
                </a>
            </li>
        <?php endif; ?>
        
    </ul>
    
    <?php if ($show_info && !$compact): ?>
        <!-- 페이지 정보 표시 -->
        <div class="pagination-info text-center text-muted mt-2">
            <small class="d-inline-block">
                총 <span class="fw-semibold"><?= number_format($total_items) ?></span>개 항목 중 
                <span class="fw-semibold">
                    <?= number_format(($current_page - 1) * $items_per_page + 1) ?>-<?= number_format(min($current_page * $items_per_page, $total_items)) ?>
                </span>번째 표시
                
                <span class="d-none d-sm-inline">
                    (<span class="fw-semibold"><?= $current_page ?></span>/<span class="fw-semibold"><?= $total_pages ?></span> 페이지)
                </span>
            </small>
        </div>
    <?php endif; ?>
</nav>

<style>
.pagination-nav .pagination .page-link {
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    margin: 0 2px;
    transition: all 0.15s ease-in-out;
}

.pagination-nav .pagination .page-link:hover {
    background-color: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
}

.pagination-nav .pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    font-weight: 600;
}

.pagination-nav .pagination .page-item.disabled .page-link {
    color: #adb5bd;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}

.pagination-nav .pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    margin: 0 1px;
}

.pagination-info {
    font-size: 0.85rem;
}

/* 반응형 처리 */
@media (max-width: 576px) {
    .pagination-nav .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination-nav .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        margin: 1px;
    }
    
    .pagination-info {
        font-size: 0.8rem;
    }
}

/* 키보드 네비게이션 지원 */
.pagination-nav .pagination .page-link:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    outline: 0;
}
</style>