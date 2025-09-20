<?php
/**
 * 이벤트 목록 뷰 - MVC 버전
 * EventController::index()에서 사용
 */

// 통계 카드 생성
$stats_html = '';
if (!empty($status_stats)) {
    $stats_html .= '<div class="row mb-4">';
    foreach ($status_stats as $stat) {
        $icon = match($stat['status']) {
            '예정' => 'bi-calendar-plus',
            '진행중' => 'bi-calendar-check',
            '완료' => 'bi-calendar-x',
            '취소' => 'bi-calendar-minus',
            default => 'bi-calendar'
        };
        $color = match($stat['status']) {
            '예정' => 'primary',
            '진행중' => 'success',
            '완료' => 'secondary',
            '취소' => 'danger',
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
    ['key' => 'id', 'title' => 'ID', 'width' => '5%', 'sortable' => true],
    ['key' => 'thumbnail_url', 'title' => '썸네일', 'width' => '10%', 'format' => function($value, $row) {
        if ($value) {
            return '<img src="' . htmlspecialchars($value) . '" alt="썸네일" class="img-thumbnail event-thumbnail" style="max-width: 60px; max-height: 60px;">';
        }
        return '<div class="bg-light d-flex align-items-center justify-content-center event-thumbnail-placeholder" style="width: 60px; height: 60px; border-radius: 0.375rem;">
            <i class="bi bi-image text-muted"></i>
        </div>';
    }],
    ['key' => 'title', 'title' => '제목', 'width' => '25%', 'escape' => true, 'truncate' => 50],
    ['key' => 'location', 'title' => '장소', 'width' => '15%', 'escape' => true, 'truncate' => 30],
    ['key' => 'event_date', 'title' => '일시', 'width' => '12%', 'format' => function($value, $row) {
        return date('Y-m-d', strtotime($value)) . '<br><small class="text-muted">' . date('H:i', strtotime($value)) . '</small>';
    }],
    ['key' => 'status', 'title' => '상태', 'width' => '8%', 'format' => function($value, $row) {
        $color = match($value) {
            '예정' => 'primary',
            '진행중' => 'success',
            '완료' => 'secondary',
            '취소' => 'danger',
            default => 'info'
        };
        return '<span class="badge bg-' . $color . '">' . htmlspecialchars($value) . '</span>';
    }],
    ['key' => 'participants', 'title' => '참가자', 'width' => '10%', 'format' => function($value, $row) {
        $current = $row['current_participants'] ?? 0;
        $max = $row['max_participants'] ?? 0;
        $percentage = $max > 0 ? ($current / $max) * 100 : 0;
        $color = $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success');
        
        return '<div class="text-center">
            <div class="fw-bold">' . number_format($current) . '/' . number_format($max) . '</div>
            <div class="progress progress-sm">
                <div class="progress-bar bg-' . $color . '" style="width: ' . min($percentage, 100) . '%"></div>
            </div>
        </div>';
    }],
    ['key' => 'registration_fee', 'title' => '참가비', 'width' => '10%', 'format' => function($value, $row) {
        return $value > 0 ? number_format($value) . '원' : '<span class="text-success">무료</span>';
    }],
    ['key' => 'created_at', 'title' => '등록일', 'width' => '10%', 'date_format' => 'Y-m-d']
];

// 액션 버튼 함수
$actions = function($row) {
    $buttons = '<a href="<?= admin_url('events/view/' . $row['id'] . '" class="btn btn-sm btn-outline-primary" title="상세보기">
        <i class="bi bi-eye"></i>
    </a>';
    
    $buttons .= ' <a href="<?= admin_url('events/edit/' . $row['id'] . '" class="btn btn-sm btn-outline-warning" title="수정">
        <i class="bi bi-pencil"></i>
    </a>';
    
    if ($row['status'] === '예정') {
        $buttons .= ' <button type="button" class="btn btn-sm btn-outline-success" onclick="changeStatus(' . $row['id'] . ', \'진행중\')" title="진행 시작">
            <i class="bi bi-play"></i>
        </button>';
    }
    
    if (in_array($row['status'], ['예정', '진행중'])) {
        $buttons .= ' <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeStatus(' . $row['id'] . ', \'완료\')" title="완료">
            <i class="bi bi-check"></i>
        </button>';
        
        $buttons .= ' <button type="button" class="btn btn-sm btn-outline-danger" onclick="changeStatus(' . $row['id'] . ', \'취소\')" title="취소">
            <i class="bi bi-x"></i>
        </button>';
    }
    
    $buttons .= ' <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEvent(' . $row['id'] . ')" title="삭제">
        <i class="bi bi-trash"></i>
    </button>';
    
    return $buttons;
};

// 대량 작업 옵션
$bulk_actions = [
    'status_scheduled' => '예정으로 변경',
    'status_ongoing' => '진행중으로 변경',
    'status_completed' => '완료로 변경',
    'status_cancelled' => '취소로 변경',
    'delete' => '삭제'
];
?>

<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">이벤트 관리</h1>
        <div>
            <a href="<?= admin_url('events/export?<?= http_build_query($filters ?? []) ?>" class="btn btn-outline-success me-2">
                <i class="bi bi-download"></i> 엑셀 다운로드
            </a>
            <a href="<?= admin_url('events/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 이벤트 등록
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
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">전체 상태</option>
                        <?php 
                        $status_options = ['예정' => '예정', '진행중' => '진행중', '완료' => '완료', '취소' => '취소'];
                        foreach ($status_options as $value => $label): 
                        ?>
                        <option value="<?= $value ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="registration_status">
                        <option value="">전체 참가</option>
                        <option value="available" <?= ($filters['registration_status'] ?? '') === 'available' ? 'selected' : '' ?>>참가 가능</option>
                        <option value="full" <?= ($filters['registration_status'] ?? '') === 'full' ? 'selected' : '' ?>>참가 마감</option>
                        <option value="free" <?= ($filters['registration_status'] ?? '') === 'free' ? 'selected' : '' ?>>무료 이벤트</option>
                        <option value="paid" <?= ($filters['registration_status'] ?? '') === 'paid' ? 'selected' : '' ?>>유료 이벤트</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" 
                           value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" placeholder="시작일">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" 
                           value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" placeholder="종료일">
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                               placeholder="제목, 장소, 내용으로 검색...">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> 검색
                        </button>
                        <?php if (!empty(array_filter($filters ?? []))): ?>
                        <a href="<?= admin_url('events" class="btn btn-outline-secondary">
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
    $data = $events;
    $table_id = 'events-table';
    $sortable = true;
    $search_enabled = false; // 위에서 별도 검색 폼 제공
    $empty_message = '등록된 이벤트가 없습니다.';
    include __DIR__ . '/../components/data_table.php';
    ?>
</div>

<!-- 상태 변경 확인 모달 -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">이벤트 상태 변경</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>이벤트 상태를 <strong id="targetStatus"></strong>로 변경하시겠습니까?</p>
                <p class="text-muted small">상태 변경 후에는 참가자들에게 알림이 발송됩니다.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" id="confirmStatus">변경</button>
            </div>
        </div>
    </div>
</div>

<!-- 이벤트 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">이벤트 삭제 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>선택한 이벤트를 삭제하시겠습니까?</p>
                <p class="text-danger small">삭제된 이벤트와 참가자 정보는 복구할 수 없습니다.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">삭제</button>
            </div>
        </div>
    </div>
</div>

<script>
let targetEventId = null;
let targetStatusValue = null;

function changeStatus(eventId, status) {
    targetEventId = eventId;
    targetStatusValue = status;
    
    const statusText = {
        '예정': '예정',
        '진행중': '진행중', 
        '완료': '완료',
        '취소': '취소'
    };
    
    document.getElementById('targetStatus').textContent = statusText[status];
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function deleteEvent(eventId) {
    targetEventId = eventId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmStatus').addEventListener('click', function() {
    if (targetEventId && targetStatusValue) {
        // CSRF 토큰을 포함한 폼 생성
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/events/updateStatus/' + targetEventId;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = targetStatusValue;
        
        form.appendChild(csrfInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
});

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (targetEventId) {
        // CSRF 토큰을 포함한 폼 생성
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/events/delete/' + targetEventId;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
});

// 페이지 로드 시 실행
document.addEventListener('DOMContentLoaded', function() {
    // 이벤트 날짜가 지난 항목 표시
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    document.querySelectorAll('#events-table tbody tr').forEach(row => {
        const dateCell = row.cells[4]; // event_date 컬럼 (0부터 시작해서 4번째)
        if (dateCell) {
            const dateText = dateCell.textContent.trim().split('\n')[0]; // 날짜만 추출
            const eventDate = new Date(dateText);
            
            if (eventDate < today) {
                row.classList.add('table-secondary');
                row.style.opacity = '0.7';
            }
        }
    });
    
    // 썸네일 이미지 로드 에러 처리
    document.querySelectorAll('.event-thumbnail').forEach(img => {
        img.addEventListener('error', function() {
            this.style.display = 'none';
            const placeholder = this.parentNode.querySelector('.event-thumbnail-placeholder');
            if (placeholder) {
                placeholder.style.display = 'flex';
            }
        });
    });
});
</script>

<style>
/* 이벤트 관리 특화 스타일 */
.event-thumbnail {
    object-fit: cover;
    transition: transform 0.2s ease;
}

.event-thumbnail:hover {
    transform: scale(1.1);
    cursor: pointer;
}

.event-thumbnail-placeholder {
    background-color: #f8f9fa;
    border: 1px dashed #dee2e6;
}

.progress.progress-sm {
    height: 4px;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}

/* 지난 이벤트 스타일 */
.table-secondary {
    background-color: rgba(108, 117, 125, 0.1) !important;
}

/* 참가자 현황 스타일 */
.progress-bar.bg-danger {
    background-color: #dc3545 !important;
}

.progress-bar.bg-warning {
    background-color: #ffc107 !important;
}

.progress-bar.bg-success {
    background-color: #198754 !important;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .event-thumbnail,
    .event-thumbnail-placeholder {
        width: 40px !important;
        height: 40px !important;
        max-width: 40px !important;
        max-height: 40px !important;
    }
    
    .table td, .table th {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .badge {
        font-size: 0.7em;
    }
}

/* 모바일에서 참가자 현황 단순화 */
@media (max-width: 576px) {
    .progress {
        display: none;
    }
}
</style>