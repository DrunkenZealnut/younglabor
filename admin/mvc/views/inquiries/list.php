<?php
/**
 * 문의 목록 뷰 - MVC 버전
 * InquiryController::index()에서 사용
 */

// 필터링 UI
$filter_html = '';
if (!empty($categories)) {
    $filter_html .= '<div class="col-md-2">
        <select class="form-select" name="category_id">
            <option value="">전체 카테고리</option>';
    foreach ($categories as $category) {
        $selected = ($filters['category_id'] == $category['id']) ? 'selected' : '';
        $filter_html .= '<option value="' . $category['id'] . '" ' . $selected . '>' . htmlspecialchars($category['name']) . '</option>';
    }
    $filter_html .= '</select></div>';
}

$filter_html .= '
<div class="col-md-2">
    <select class="form-select" name="status">
        <option value="">전체 상태</option>';
foreach ($status_options as $value => $label) {
    $selected = ($filters['status'] == $value) ? 'selected' : '';
    $filter_html .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
}
$filter_html .= '</select></div>

<div class="col-md-2">
    <select class="form-select" name="priority">
        <option value="">전체 우선순위</option>';
foreach ($priority_options as $value => $label) {
    $selected = ($filters['priority'] == $value) ? 'selected' : '';
    $filter_html .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
}
$filter_html .= '</select></div>';

// 통계 카드
$stats_html = '';
if (!empty($status_stats)) {
    $stats_html .= '<div class="row mb-4">';
    foreach ($status_stats as $stat) {
        $icon = match($stat['status']) {
            '접수' => 'bi-inbox',
            '처리중' => 'bi-hourglass-split',
            '완료' => 'bi-check-circle',
            '보류' => 'bi-pause-circle',
            default => 'bi-question-circle'
        };
        $color = match($stat['status']) {
            '접수' => 'primary',
            '처리중' => 'warning',
            '완료' => 'success',
            '보류' => 'secondary',
            default => 'info'
        };
        
        $stats_html .= '
        <div class="col-md-3 mb-3">
            <div class="card border-' . $color . '">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="' . $icon . ' text-' . $color . ' fs-3 me-2"></i>
                        <h4 class="mb-0">' . number_format($stat['count']) . '</h4>
                    </div>
                    <h6 class="card-title text-' . $color . '">' . $stat['status'] . '</h6>
                </div>
            </div>
        </div>';
    }
    $stats_html .= '</div>';
}

// 데이터 테이블 컬럼 정의
$columns = [
    ['key' => 'id', 'title' => 'ID', 'width' => '8%', 'sortable' => true],
    ['key' => 'category_name', 'title' => '카테고리', 'width' => '12%', 'escape' => true],
    ['key' => 'subject', 'title' => '제목', 'width' => '25%', 'escape' => true, 'truncate' => 50],
    ['key' => 'name', 'title' => '이름', 'width' => '10%', 'escape' => true],
    ['key' => 'status', 'title' => '상태', 'width' => '10%', 'format' => function($value, $row) {
        $color = match($value) {
            '접수' => 'primary',
            '처리중' => 'warning',
            '완료' => 'success',
            '보류' => 'secondary',
            default => 'info'
        };
        return '<span class="badge bg-' . $color . '">' . htmlspecialchars($value) . '</span>';
    }],
    ['key' => 'priority', 'title' => '우선순위', 'width' => '10%', 'format' => function($value, $row) {
        $color = match($value) {
            '긴급' => 'danger',
            '높음' => 'warning',
            '보통' => 'info',
            '낮음' => 'secondary',
            default => 'light'
        };
        return '<span class="badge bg-' . $color . '">' . htmlspecialchars($value) . '</span>';
    }],
    ['key' => 'created_at', 'title' => '접수일시', 'width' => '15%', 'date_format' => 'Y-m-d H:i'],
    ['key' => 'responded_at', 'title' => '답변일시', 'width' => '10%', 'format' => function($value, $row) {
        return $value ? date('Y-m-d H:i', strtotime($value)) : '<span class="text-muted">미답변</span>';
    }]
];

// 액션 버튼 함수
$actions = function($row) {
    $buttons = '<a href="<?= admin_url('inquiries/view/' . $row['id'] . '" class="btn btn-sm btn-outline-primary" title="상세보기">
        <i class="bi bi-eye"></i>
    </a>';
    
    if ($row['status'] !== '완료') {
        $buttons .= ' <a href="<?= admin_url('inquiries/view/' . $row['id'] . '#response-form" class="btn btn-sm btn-outline-success" title="답변하기">
            <i class="bi bi-reply"></i>
        </a>';
    }
    
    $buttons .= ' <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteInquiry(' . $row['id'] . ')" title="삭제">
        <i class="bi bi-trash"></i>
    </button>';
    
    return $buttons;
};

// 대량 작업 옵션
$bulk_actions = [
    'status_processing' => '처리중으로 변경',
    'status_complete' => '완료로 변경',
    'delete' => '삭제'
];
?>

<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">문의 관리</h1>
        <div>
            <a href="<?= admin_url('inquiries/export?<?= http_build_query($filters) ?>" class="btn btn-outline-success">
                <i class="bi bi-download"></i> 엑셀 다운로드
            </a>
        </div>
    </div>

    <!-- 통계 카드 -->
    <?= $stats_html ?>

    <!-- 필터 폼 -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel"></i> 필터 및 검색
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <?= $filter_html ?>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" placeholder="시작일">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" placeholder="종료일">
                </div>
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                               placeholder="제목, 내용, 이름, 이메일로 검색...">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> 검색
                        </button>
                        <?php if (!empty(array_filter($filters))): ?>
                        <a href="<?= admin_url('inquiries" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i> 초기화
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 데이터 테이블 -->
    <?php
    $data = $inquiries;
    $table_id = 'inquiries-table';
    $sortable = true;
    $search_enabled = false; // 위에서 별도 검색 폼 제공
    $empty_message = '등록된 문의가 없습니다.';
    include __DIR__ . '/../components/data_table.php';
    ?>
</div>

<!-- 문의 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">문의 삭제 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>선택한 문의를 삭제하시겠습니까?</p>
                <p class="text-danger small">삭제된 문의는 복구할 수 없습니다.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">삭제</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteInquiryId = null;

function deleteInquiry(id) {
    deleteInquiryId = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteInquiryId) {
        // CSRF 토큰을 포함한 폼 생성
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/inquiries/delete/' + deleteInquiryId;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
});

// 상태별 색상 업데이트
document.addEventListener('DOMContentLoaded', function() {
    // 테이블 행에 마우스 오버 시 하이라이트
    const tableRows = document.querySelectorAll('#inquiries-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // 우선순위가 '긴급'인 행 강조
    const urgentRows = document.querySelectorAll('[data-priority="긴급"]');
    urgentRows.forEach(row => {
        row.classList.add('table-danger');
    });
});
</script>

<style>
/* 문의 관리 특화 스타일 */
.table-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.card .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.badge {
    font-size: 0.8em;
}

/* 반응형 테이블 개선 */
@media (max-width: 768px) {
    .table-responsive {
        border: none;
    }
    
    .table td, .table th {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
}

/* 통계 카드 호버 효과 */
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}
</style>