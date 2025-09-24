<?php
/**
 * 문의 상세보기 뷰 - MVC 버전
 * InquiryController::view()에서 사용
 */

// 상태와 우선순위 배지 생성 함수
function getStatusBadge($status) {
    $color = match($status) {
        '접수' => 'primary',
        '처리중' => 'warning',
        '완료' => 'success',
        '보류' => 'secondary',
        default => 'info'
    };
    return '<span class="badge bg-' . $color . ' fs-6">' . htmlspecialchars($status) . '</span>';
}

function getPriorityBadge($priority) {
    $color = match($priority) {
        '긴급' => 'danger',
        '높음' => 'warning',
        '보통' => 'info',
        '낮음' => 'secondary',
        default => 'light'
    };
    return '<span class="badge bg-' . $color . ' fs-6">' . htmlspecialchars($priority) . '</span>';
}

$inquiry_data = $inquiry;
?>

<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= admin_url('inquiries">문의 관리</a>
                    </li>
                    <li class="breadcrumb-item active">문의 상세</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">문의 상세 - #<?= $inquiry_data['id'] ?></h1>
        </div>
        <div>
            <a href="<?= admin_url('inquiries" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <div class="row">
        <!-- 문의 내용 -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-2"><?= htmlspecialchars($inquiry_data['subject']) ?></h4>
                            <div class="text-muted">
                                <small>
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($inquiry_data['name']) ?>
                                    <i class="bi bi-envelope ms-2"></i> <?= htmlspecialchars($inquiry_data['email']) ?>
                                    <?php if (!empty($inquiry_data['phone'])): ?>
                                    <i class="bi bi-telephone ms-2"></i> <?= htmlspecialchars($inquiry_data['phone']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <div class="text-end">
                            <?= getStatusBadge($inquiry_data['status']) ?>
                            <?= getPriorityBadge($inquiry_data['priority']) ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="inquiry-content">
                        <?= nl2br(htmlspecialchars($inquiry_data['content'])) ?>
                    </div>
                    
                    <?php if (!empty($inquiry_data['attachment_path'])): ?>
                    <hr>
                    <div class="attachments">
                        <h6><i class="bi bi-paperclip"></i> 첨부파일</h6>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark"></i>
                                    <?= htmlspecialchars($inquiry_data['attachment_name'] ?? basename($inquiry_data['attachment_path'])) ?>
                                </div>
                                <a href="<?= htmlspecialchars($inquiry_data['attachment_path']) ?>" 
                                   class="btn btn-sm btn-outline-primary" download>
                                    <i class="bi bi-download"></i> 다운로드
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="bi bi-clock"></i> 접수일시: <?= date('Y년 m월 d일 H:i', strtotime($inquiry_data['created_at'])) ?>
                        <?php if ($inquiry_data['ip_address']): ?>
                        <span class="ms-3"><i class="bi bi-globe"></i> IP: <?= htmlspecialchars($inquiry_data['ip_address']) ?></span>
                        <?php endif; ?>
                    </small>
                </div>
            </div>

            <!-- 관리자 답변 섹션 -->
            <?php if (!empty($inquiry_data['admin_response'])): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-reply"></i> 관리자 답변</h5>
                </div>
                <div class="card-body">
                    <div class="admin-response">
                        <?= nl2br(htmlspecialchars($inquiry_data['admin_response'])) ?>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="bi bi-clock"></i> 답변일시: <?= date('Y년 m월 d일 H:i', strtotime($inquiry_data['responded_at'])) ?>
                        <?php if (!empty($inquiry_data['responded_by'])): ?>
                        <span class="ms-3"><i class="bi bi-person-badge"></i> 답변자: 관리자</span>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
            <?php endif; ?>

            <!-- 답변 작성 폼 -->
            <?php if ($inquiry_data['status'] !== '완료'): ?>
            <div class="card" id="response-form">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-reply"></i> 답변 작성</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/inquiries/addResponse/<?= $inquiry_data['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label for="admin_response" class="form-label">답변 내용 *</label>
                            <textarea class="form-control" id="admin_response" name="admin_response" 
                                      rows="8" required placeholder="고객에게 전달할 답변을 작성해주세요..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="email_notification" class="form-label">
                                    <input type="checkbox" class="form-check-input me-2" 
                                           id="email_notification" name="email_notification" value="1" checked>
                                    이메일 알림 발송
                                </label>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-send"></i> 답변 등록
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- 사이드바 (문의 정보 및 액션) -->
        <div class="col-lg-4">
            <!-- 문의 정보 카드 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> 문의 정보</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>상태:</strong>
                                <?= getStatusBadge($inquiry_data['status']) ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>우선순위:</strong>
                                <?= getPriorityBadge($inquiry_data['priority']) ?>
                            </div>
                        </div>
                        <?php if (!empty($inquiry_data['category_name'])): ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>카테고리:</strong>
                                <span class="badge bg-info"><?= htmlspecialchars($inquiry_data['category_name']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>접수일:</strong>
                                <span><?= date('Y-m-d', strtotime($inquiry_data['created_at'])) ?></span>
                            </div>
                        </div>
                        <?php if ($inquiry_data['responded_at']): ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>답변일:</strong>
                                <span><?= date('Y-m-d', strtotime($inquiry_data['responded_at'])) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 상태 변경 카드 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear"></i> 상태 관리</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/inquiries/updateStatus/<?= $inquiry_data['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">상태 변경</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach ($status_options as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $inquiry_data['status'] === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">우선순위 변경</label>
                            <select class="form-select" id="priority" name="priority">
                                <?php foreach ($priority_options as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $inquiry_data['priority'] === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check2"></i> 변경사항 저장
                        </button>
                    </form>
                </div>
            </div>

            <!-- 액션 버튼들 -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-tools"></i> 관리 작업</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="mailto:<?= htmlspecialchars($inquiry_data['email']) ?>?subject=Re: <?= htmlspecialchars($inquiry_data['subject']) ?>" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-envelope"></i> 이메일 보내기
                    </a>
                    
                    <?php if (!empty($inquiry_data['phone'])): ?>
                    <a href="tel:<?= htmlspecialchars($inquiry_data['phone']) ?>" class="btn btn-outline-success">
                        <i class="bi bi-telephone"></i> 전화하기
                    </a>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-outline-danger" onclick="deleteInquiry()">
                        <i class="bi bi-trash"></i> 문의 삭제
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">문의 삭제 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>이 문의를 삭제하시겠습니까?</p>
                <p class="text-danger small">삭제된 문의는 복구할 수 없습니다.</p>
                <p class="fw-bold">문의 제목: <?= htmlspecialchars($inquiry_data['subject']) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">삭제</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteInquiry() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    // CSRF 토큰을 포함한 폼 생성
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/inquiries/delete/<?= $inquiry_data['id'] ?>';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
});

// 답변 폼 자동 높이 조절
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('admin_response');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
    
    // URL 해시가 #response-form이면 답변 폼으로 스크롤
    if (window.location.hash === '#response-form') {
        document.getElementById('response-form').scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<style>
/* 문의 상세 페이지 스타일 */
.inquiry-content {
    line-height: 1.8;
    font-size: 1.05rem;
    white-space: pre-wrap;
}

.admin-response {
    line-height: 1.8;
    font-size: 1.05rem;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 4px solid #198754;
}

.badge {
    font-size: 0.85em;
}

.card-header h4 {
    color: #495057;
}

.attachments .list-group-item {
    background-color: #f8f9fa;
    border: 1px dashed #dee2e6;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .d-flex.justify-content-between > div:last-child {
        margin-top: 0.5rem;
    }
    
    .card-body .row .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .card-body .row .col-md-6.text-end {
        text-align: left !important;
    }
}

/* 프린트 스타일 */
@media print {
    .btn, .card-footer, #response-form, .breadcrumb {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>