<?php
/**
 * 이벤트 등록/수정 폼 뷰 - MVC 버전
 * EventController::create() 및 edit()에서 사용
 */

$is_edit = isset($event) && !empty($event);
$page_title = $is_edit ? '이벤트 수정' : '이벤트 등록';
$form_action = $is_edit ? '/admin/events/update/' . $event['id'] : '/admin/events/store';
$submit_text = $is_edit ? '수정하기' : '등록하기';

// 기본값 설정
$event_data = $event ?? [
    'title' => '',
    'description' => '',
    'content' => '',
    'location' => '',
    'event_date' => '',
    'registration_start_date' => '',
    'registration_end_date' => '',
    'max_participants' => '',
    'registration_fee' => 0,
    'status' => '예정',
    'thumbnail_url' => '',
    'contact_info' => '',
    'tags' => '',
    'is_featured' => 0,
    'allow_registration' => 1
];
?>

<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/admin/events">이벤트 관리</a>
                    </li>
                    <li class="breadcrumb-item active"><?= $page_title ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-0"><?= $page_title ?></h1>
        </div>
        <div>
            <a href="/admin/events" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <form method="POST" action="<?= $form_action ?>" enctype="multipart/form-data" id="eventForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <?php if ($is_edit): ?>
        <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        
        <div class="row">
            <!-- 메인 정보 -->
            <div class="col-lg-8">
                <!-- 기본 정보 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle"></i> 기본 정보
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="title" class="form-label">이벤트 제목 *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= htmlspecialchars($event_data['title']) ?>" 
                                       required maxlength="255">
                                <div class="form-text">최대 255자까지 입력 가능합니다.</div>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">간단한 설명 *</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" required maxlength="500"><?= htmlspecialchars($event_data['description']) ?></textarea>
                                <div class="form-text">이벤트 목록에 표시될 간단한 설명입니다. (최대 500자)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="location" class="form-label">장소 *</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?= htmlspecialchars($event_data['location']) ?>" 
                                       required maxlength="255">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="event_date" class="form-label">이벤트 일시 *</label>
                                <input type="datetime-local" class="form-control" id="event_date" name="event_date" 
                                       value="<?= $event_data['event_date'] ? date('Y-m-d\TH:i', strtotime($event_data['event_date'])) : '' ?>" 
                                       required>
                            </div>
                            
                            <div class="col-12">
                                <label for="tags" class="form-label">태그</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       value="<?= htmlspecialchars($event_data['tags']) ?>"
                                       placeholder="태그1, 태그2, 태그3">
                                <div class="form-text">쉼표(,)로 구분하여 입력하세요.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 상세 내용 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-text"></i> 상세 내용
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="content" class="form-label">이벤트 상세 내용</label>
                            <textarea class="form-control" id="content" name="content" 
                                      rows="15"><?= htmlspecialchars($event_data['content']) ?></textarea>
                            <div class="form-text">이벤트의 상세한 내용을 입력하세요. HTML 태그 사용 가능합니다.</div>
                        </div>
                    </div>
                </div>

                <!-- 연락처 정보 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-telephone"></i> 연락처 정보
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="contact_info" class="form-label">연락처</label>
                            <textarea class="form-control" id="contact_info" name="contact_info" 
                                      rows="3" placeholder="문의사항이 있으시면 아래로 연락해주세요.
전화: 02-1234-5678
이메일: contact@example.com"><?= htmlspecialchars($event_data['contact_info']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 사이드바 (설정 및 옵션) -->
            <div class="col-lg-4">
                <!-- 상태 및 옵션 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-gear"></i> 상태 및 옵션
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="status" class="form-label">상태</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="예정" <?= $event_data['status'] === '예정' ? 'selected' : '' ?>>예정</option>
                                    <option value="진행중" <?= $event_data['status'] === '진행중' ? 'selected' : '' ?>>진행중</option>
                                    <option value="완료" <?= $event_data['status'] === '완료' ? 'selected' : '' ?>>완료</option>
                                    <option value="취소" <?= $event_data['status'] === '취소' ? 'selected' : '' ?>>취소</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_featured" 
                                           name="is_featured" value="1" 
                                           <?= $event_data['is_featured'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        추천 이벤트로 표시
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="allow_registration" 
                                           name="allow_registration" value="1" 
                                           <?= $event_data['allow_registration'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="allow_registration">
                                        온라인 참가 신청 허용
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 참가 정보 -->
                <div class="card mb-4" id="registrationCard">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-people"></i> 참가 정보
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="max_participants" class="form-label">최대 참가자 수</label>
                                <input type="number" class="form-control" id="max_participants" 
                                       name="max_participants" value="<?= $event_data['max_participants'] ?>" 
                                       min="1" max="999">
                                <div class="form-text">0이면 인원 제한 없음</div>
                            </div>
                            
                            <div class="col-12">
                                <label for="registration_fee" class="form-label">참가비 (원)</label>
                                <input type="number" class="form-control" id="registration_fee" 
                                       name="registration_fee" value="<?= $event_data['registration_fee'] ?>" 
                                       min="0" step="1000">
                                <div class="form-text">0이면 무료 이벤트</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="registration_start_date" class="form-label">신청 시작</label>
                                <input type="datetime-local" class="form-control" id="registration_start_date" 
                                       name="registration_start_date" 
                                       value="<?= $event_data['registration_start_date'] ? date('Y-m-d\TH:i', strtotime($event_data['registration_start_date'])) : '' ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="registration_end_date" class="form-label">신청 마감</label>
                                <input type="datetime-local" class="form-control" id="registration_end_date" 
                                       name="registration_end_date" 
                                       value="<?= $event_data['registration_end_date'] ? date('Y-m-d\TH:i', strtotime($event_data['registration_end_date'])) : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 썸네일 이미지 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-image"></i> 썸네일 이미지
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($event_data['thumbnail_url'])): ?>
                        <div class="current-thumbnail mb-3">
                            <img src="<?= htmlspecialchars($event_data['thumbnail_url']) ?>" 
                                 alt="현재 썸네일" class="img-fluid rounded" 
                                 style="max-height: 200px;">
                            <div class="mt-2">
                                <small class="text-muted">현재 썸네일</small>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                        onclick="removeThumbnail()">삭제</button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" 
                                   accept="image/*" onchange="previewThumbnail(this)">
                            <div class="form-text">JPG, PNG, GIF 파일만 가능 (최대 5MB)</div>
                        </div>
                        
                        <div id="thumbnailPreview" class="mt-2" style="display: none;">
                            <img id="previewImage" class="img-fluid rounded" style="max-height: 200px;">
                            <div class="mt-2">
                                <small class="text-muted">미리보기</small>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                        onclick="cancelPreview()">취소</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <div class="card">
                    <div class="card-body d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> <?= $submit_text ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// 참가 신청 허용 토글
document.getElementById('allow_registration').addEventListener('change', function() {
    const registrationCard = document.getElementById('registrationCard');
    if (this.checked) {
        registrationCard.style.opacity = '1';
        registrationCard.querySelectorAll('input').forEach(input => input.disabled = false);
    } else {
        registrationCard.style.opacity = '0.5';
        registrationCard.querySelectorAll('input').forEach(input => input.disabled = true);
    }
});

// 썸네일 미리보기
function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // 파일 크기 체크 (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('파일 크기는 5MB 이하여야 합니다.');
            input.value = '';
            return;
        }
        
        // 이미지 파일 체크
        if (!file.type.startsWith('image/')) {
            alert('이미지 파일만 업로드 가능합니다.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('thumbnailPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function cancelPreview() {
    document.getElementById('thumbnail').value = '';
    document.getElementById('thumbnailPreview').style.display = 'none';
}

function removeThumbnail() {
    if (confirm('현재 썸네일을 삭제하시겠습니까?')) {
        // 숨겨진 필드로 삭제 표시
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_thumbnail';
        input.value = '1';
        document.getElementById('eventForm').appendChild(input);
        
        // UI에서 제거
        document.querySelector('.current-thumbnail').style.display = 'none';
    }
}

// 폼 유효성 검사
document.getElementById('eventForm').addEventListener('submit', function(e) {
    const eventDate = new Date(document.getElementById('event_date').value);
    const regStartDate = document.getElementById('registration_start_date').value;
    const regEndDate = document.getElementById('registration_end_date').value;
    
    // 신청 기간 유효성 검사
    if (regStartDate && regEndDate) {
        const startDate = new Date(regStartDate);
        const endDate = new Date(regEndDate);
        
        if (startDate >= endDate) {
            e.preventDefault();
            alert('신청 시작일은 마감일보다 빨라야 합니다.');
            return;
        }
        
        if (endDate > eventDate) {
            e.preventDefault();
            alert('신청 마감일은 이벤트 일시보다 빨라야 합니다.');
            return;
        }
    }
    
    // 필수 필드 검사
    const requiredFields = ['title', 'description', 'location', 'event_date'];
    for (const field of requiredFields) {
        const element = document.getElementById(field);
        if (!element.value.trim()) {
            e.preventDefault();
            alert(`${element.previousElementSibling.textContent.replace(' *', '')}은(는) 필수 입력 항목입니다.`);
            element.focus();
            return;
        }
    }
});

// 페이지 로드 시 초기 설정
document.addEventListener('DOMContentLoaded', function() {
    // 참가 신청 허용 초기 상태 설정
    const allowRegistrationToggle = document.getElementById('allow_registration');
    allowRegistrationToggle.dispatchEvent(new Event('change'));
    
    // 현재 시간 이후로만 이벤트 일시 설정 가능
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const nowString = now.toISOString().slice(0, 16);
    document.getElementById('event_date').min = nowString;
    
    // CKEditor 또는 다른 에디터 초기화
    // 실제 프로젝트에서는 사용하는 에디터에 맞게 수정
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('content');
    }
});
</script>

<style>
/* 이벤트 폼 스타일 */
.card {
    transition: box-shadow 0.2s ease-in-out;
}

.card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.current-thumbnail img,
#previewImage {
    max-width: 100%;
    height: auto;
    border: 1px solid #dee2e6;
}

/* 참가 정보 카드 비활성화 스타일 */
#registrationCard[style*="opacity: 0.5"] .form-label {
    color: #6c757d;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .col-lg-4 .card {
        margin-bottom: 1rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
}

/* 필수 필드 표시 */
.form-label::after {
    content: "";
}

.form-label[for="title"]::after,
.form-label[for="description"]::after,
.form-label[for="location"]::after,
.form-label[for="event_date"]::after {
    content: " *";
    color: #dc3545;
}

/* 폼 검증 상태 */
.is-invalid {
    border-color: #dc3545;
}

.is-valid {
    border-color: #198754;
}

/* 로딩 상태 */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>