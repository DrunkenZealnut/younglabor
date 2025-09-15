<?php
/**
 * 팝업 관리 인터페이스
 * site_settings.php의 popup 탭에서 사용
 */

// PopupManager 클래스 로드
require_once __DIR__ . '/../../services/PopupManager.php';

// 팝업 매니저 인스턴스 생성
$popupManager = new PopupManager($pdo);

// 액션 처리
$popup_message = '';
$popup_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $popupData = [
                    'title' => $_POST['title'] ?? '',
                    'content' => $_POST['content'] ?? '',
                    'popup_type' => $_POST['popup_type'] ?? 'notice',
                    'show_frequency' => $_POST['show_frequency'] ?? 'once',
                    'priority' => (int)($_POST['priority'] ?? 1),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                    'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                    'display_condition' => [
                        'target_pages' => $_POST['target_pages'] ?? ['home'],
                        'device_type' => $_POST['device_type'] ?? ['desktop', 'mobile'],
                        'time_range' => [
                            'start' => $_POST['time_start'] ?? '00:00',
                            'end' => $_POST['time_end'] ?? '23:59'
                        ]
                    ],
                    'style_settings' => [
                        'width' => $_POST['popup_width'] ?? '500',
                        'height' => $_POST['popup_height'] ?? 'auto',
                        'bg_color' => $_POST['bg_color'] ?? '#ffffff',
                        'border_radius' => $_POST['border_radius'] ?? '12',
                        'animation' => $_POST['animation'] ?? 'fade',
                        'overlay_color' => $_POST['overlay_color'] ?? 'rgba(0,0,0,0.5)'
                    ]
                ];
                
                $newId = $popupManager->createPopup($popupData);
                if ($newId) {
                    $popup_message = '팝업이 성공적으로 생성되었습니다.';
                } else {
                    $popup_error = '팝업 생성에 실패했습니다.';
                }
                break;
                
            case 'update':
                $popupId = (int)$_POST['popup_id'];
                $popupData = [
                    'title' => $_POST['title'] ?? '',
                    'content' => $_POST['content'] ?? '',
                    'popup_type' => $_POST['popup_type'] ?? 'notice',
                    'show_frequency' => $_POST['show_frequency'] ?? 'once',
                    'priority' => (int)($_POST['priority'] ?? 1),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                    'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                    'display_condition' => [
                        'target_pages' => $_POST['target_pages'] ?? ['home'],
                        'device_type' => $_POST['device_type'] ?? ['desktop', 'mobile'],
                        'time_range' => [
                            'start' => $_POST['time_start'] ?? '00:00',
                            'end' => $_POST['time_end'] ?? '23:59'
                        ]
                    ],
                    'style_settings' => [
                        'width' => $_POST['popup_width'] ?? '500',
                        'height' => $_POST['popup_height'] ?? 'auto',
                        'bg_color' => $_POST['bg_color'] ?? '#ffffff',
                        'border_radius' => $_POST['border_radius'] ?? '12',
                        'animation' => $_POST['animation'] ?? 'fade',
                        'overlay_color' => $_POST['overlay_color'] ?? 'rgba(0,0,0,0.5)'
                    ]
                ];
                
                if ($popupManager->updatePopup($popupId, $popupData)) {
                    $popup_message = '팝업이 성공적으로 수정되었습니다.';
                } else {
                    $popup_error = '팝업 수정에 실패했습니다.';
                }
                break;
                
            case 'delete':
                $popupId = (int)$_POST['popup_id'];
                if ($popupManager->deletePopup($popupId)) {
                    $popup_message = '팝업이 성공적으로 삭제되었습니다.';
                } else {
                    $popup_error = '팝업 삭제에 실패했습니다.';
                }
                break;
                
            case 'toggle':
                $popupId = (int)$_POST['popup_id'];
                if ($popupManager->togglePopup($popupId)) {
                    $popup_message = '팝업 상태가 성공적으로 변경되었습니다.';
                } else {
                    $popup_error = '팝업 상태 변경에 실패했습니다.';
                }
                break;
        }
    } catch (Exception $e) {
        $popup_error = '오류가 발생했습니다: ' . $e->getMessage();
    }
}

// 팝업 목록 조회
$popups = $popupManager->getAllPopups();

// 편집할 팝업 데이터 조회
$editPopup = null;
if (isset($_GET['edit'])) {
    $editPopup = $popupManager->getPopup((int)$_GET['edit']);
}
?>

<div class="popup-manager">
    <?php if ($popup_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?= $popup_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($popup_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?= $popup_error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 팝업 목록 -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-window-stack"></i> 팝업 목록
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showPopupEditor()">
                        <i class="bi bi-plus-lg"></i> 새 팝업
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($popups)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-window-stack display-4"></i>
                            <p class="mt-2">생성된 팝업이 없습니다.</p>
                            <button type="button" class="btn btn-primary" onclick="showPopupEditor()">
                                첫 팝업 만들기
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>제목</th>
                                        <th>타입</th>
                                        <th>상태</th>
                                        <th>조회수</th>
                                        <th>생성일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popups as $popup): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($popup['title']) ?></strong>
                                                <?php if ($popup['priority'] > 1): ?>
                                                    <span class="badge bg-warning text-dark">우선순위 <?= $popup['priority'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $typeLabels = [
                                                    'notice' => '공지사항',
                                                    'promotion' => '프로모션',
                                                    'announcement' => '안내사항',
                                                    'custom' => '사용자 정의'
                                                ];
                                                echo $typeLabels[$popup['popup_type']] ?? $popup['popup_type'];
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                
                                                switch ($popup['status']) {
                                                    case 'active':
                                                        $statusClass = 'bg-success';
                                                        $statusText = '활성';
                                                        break;
                                                    case 'inactive':
                                                        $statusClass = 'bg-secondary';
                                                        $statusText = '비활성';
                                                        break;
                                                    case 'scheduled':
                                                        $statusClass = 'bg-info';
                                                        $statusText = '예약';
                                                        break;
                                                    case 'expired':
                                                        $statusClass = 'bg-danger';
                                                        $statusText = '만료';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= number_format($popup['view_count'] ?? 0) ?></span>
                                                <?php if ($popup['click_count'] > 0): ?>
                                                    <small class="text-success d-block">클릭: <?= number_format($popup['click_count']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('Y-m-d H:i', strtotime($popup['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="editPopup(<?= $popup['id'] ?>)" title="편집">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="previewPopup(<?= $popup['id'] ?>)" title="미리보기">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="popup_id" value="<?= $popup['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-warning" 
                                                                title="<?= $popup['is_active'] ? '비활성화' : '활성화' ?>">
                                                            <i class="bi bi-<?= $popup['is_active'] ? 'pause' : 'play' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deletePopup(<?= $popup['id'] ?>)" title="삭제">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 팝업 편집기 -->
        <div class="col-md-4">
            <div id="popup-editor" class="card" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0" id="editor-title">새 팝업 만들기</h6>
                    <button type="button" class="btn-close" onclick="hidePopupEditor()"></button>
                </div>
                <div class="card-body">
                    <form id="popup-form" method="post">
                        <input type="hidden" name="action" value="create" id="form-action">
                        <input type="hidden" name="popup_id" value="" id="form-popup-id">
                        
                        <!-- 기본 정보 -->
                        <div class="mb-3">
                            <label for="title" class="form-label">팝업 제목 *</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">팝업 내용 *</label>
                            <textarea class="form-control" name="content" id="content" rows="8" required 
                                      placeholder="팝업에 표시할 내용을 입력하세요."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="popup_type" class="form-label">팝업 타입</label>
                                    <select class="form-select" name="popup_type" id="popup_type">
                                        <option value="notice">공지사항</option>
                                        <option value="promotion">프로모션</option>
                                        <option value="announcement">안내사항</option>
                                        <option value="custom">사용자 정의</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="show_frequency" class="form-label">표시 빈도</label>
                                    <select class="form-select" name="show_frequency" id="show_frequency">
                                        <option value="once">한 번만</option>
                                        <option value="daily">매일</option>
                                        <option value="weekly">매주</option>
                                        <option value="always">항상</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 표시 조건 -->
                        <h6 class="border-bottom pb-2 mb-3">표시 조건</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">대상 페이지</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="target_pages[]" value="home" id="page_home" checked>
                                <label class="form-check-label" for="page_home">홈페이지</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="target_pages[]" value="all" id="page_all">
                                <label class="form-check-label" for="page_all">모든 페이지</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">디바이스 타입</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="device_type[]" value="desktop" id="device_desktop" checked>
                                <label class="form-check-label" for="device_desktop">데스크톱</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="device_type[]" value="mobile" id="device_mobile" checked>
                                <label class="form-check-label" for="device_mobile">모바일</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="device_type[]" value="tablet" id="device_tablet">
                                <label class="form-check-label" for="device_tablet">태블릿</label>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="time_start" class="form-label">시작 시간</label>
                                    <input type="time" class="form-control" name="time_start" id="time_start" value="00:00">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="time_end" class="form-label">종료 시간</label>
                                    <input type="time" class="form-control" name="time_end" id="time_end" value="23:59">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">시작 날짜</label>
                                    <input type="datetime-local" class="form-control" name="start_date" id="start_date">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">종료 날짜</label>
                                    <input type="datetime-local" class="form-control" name="end_date" id="end_date">
                                </div>
                            </div>
                        </div>
                        
                        <!-- 스타일 설정 -->
                        <h6 class="border-bottom pb-2 mb-3">스타일 설정</h6>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="popup_width" class="form-label">너비 (px)</label>
                                    <input type="number" class="form-control" name="popup_width" id="popup_width" value="500" min="300" max="1000">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="bg_color" class="form-label">배경색</label>
                                    <input type="color" class="form-control form-control-color" name="bg_color" id="bg_color" value="#ffffff">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="animation" class="form-label">애니메이션</label>
                                    <select class="form-select" name="animation" id="animation">
                                        <option value="fade">페이드</option>
                                        <option value="slide">슬라이드</option>
                                        <option value="bounce">바운스</option>
                                        <option value="none">없음</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">우선순위</label>
                                    <input type="number" class="form-control" name="priority" id="priority" value="1" min="1" max="10">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    즉시 활성화
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> 저장
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="previewCurrentPopup()">
                                <i class="bi bi-eye"></i> 미리보기
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 미리보기 모달 (Bootstrap Modal 사용) -->
<div class="modal fade" id="popup-preview-modal" tabindex="-1" aria-labelledby="popup-preview-title" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popup-preview-title">팝업 미리보기</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="preview-content">
          <!-- 미리보기 내용이 여기에 표시됩니다 -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>

<!-- Summernote 에디터 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">

<!-- jQuery 먼저 로드 -->
<script>
if (typeof jQuery === 'undefined') {
    var jqueryScript = document.createElement('script');
    jqueryScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
    jqueryScript.onload = function() {
        console.log('jQuery 로드 완료');
        loadSummernote();
    };
    document.head.appendChild(jqueryScript);
} else {
    console.log('jQuery 이미 로드됨');
    loadSummernote();
}

function loadSummernote() {
    // Summernote 스크립트 로드
    var summernoteScript = document.createElement('script');
    summernoteScript.src = 'https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js';
    summernoteScript.onload = function() {
        console.log('Summernote 로드 완료');
        
        // 한국어 언어팩 로드
        var koScript = document.createElement('script');
        koScript.src = 'https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js';
        koScript.onload = function() {
            console.log('Summernote 한국어 팩 로드 완료');
            console.log('모든 라이브러리 로드 완료 - Summernote 사용 준비됨');
        };
        document.head.appendChild(koScript);
    };
    document.head.appendChild(summernoteScript);
}
</script>

<script>
// Summernote 초기화
let contentEditor = null;

function initSummernote() {
    // jQuery와 Summernote 로드 확인
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.summernote === 'undefined') {
        console.log('jQuery 또는 Summernote가 아직 로드되지 않았습니다. 재시도합니다...');
        setTimeout(initSummernote, 200);
        return;
    }
    
    try {
        // 기존 에디터 정리
        if (contentEditor) {
            $('#content').summernote('destroy');
            contentEditor = null;
        }
        
        // Summernote 초기화
        $('#content').summernote({
            height: 300,
            lang: 'ko-KR',
            placeholder: '팝업에 표시할 내용을 입력하세요...',
            fontNames: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
            fontNamesIgnoreCheck: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'strikethrough', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']]
            ],
            callbacks: {
                onInit: function() {
                    console.log('✅ Summernote 에디터가 성공적으로 초기화되었습니다.');
                },
                onImageUpload: function(files) {
                    // 이미지 업로드 처리
                    for (let i = 0; i < files.length; i++) {
                        uploadImage(files[i]);
                    }
                }
            }
        });
        
        contentEditor = $('#content').data('summernote');
        console.log('Summernote 에디터 초기화 완료');
        
    } catch (error) {
        console.error('Summernote 초기화 오류:', error);
        // 오류 발생시 기본 textarea로 폴백
        $('#content').show();
    }
}

function uploadImage(file) {
    const formData = new FormData();
    formData.append('upload', file);
    
    fetch('/admin/posts/upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#content').summernote('insertImage', data.url);
        } else {
            alert('이미지 업로드에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('이미지 업로드 오류:', error);
        alert('이미지 업로드 중 오류가 발생했습니다.');
    });
}

// 팝업 편집기 표시/숨김
function showPopupEditor() {
    console.log('팝업 편집기 표시 시작');
    
    document.getElementById('popup-editor').style.display = 'block';
    document.getElementById('editor-title').textContent = '새 팝업 만들기';
    document.getElementById('form-action').value = 'create';
    document.getElementById('popup-form').reset();
    document.getElementById('is_active').checked = true;
    
    // Summernote 초기화 (딜레이를 두어 DOM과 라이브러리 준비 후 실행)
    setTimeout(function() {
        console.log('Summernote 초기화 시도');
        initSummernote();
    }, 500);
}

function hidePopupEditor() {
    console.log('팝업 편집기 숨김 시작');
    
    // Summernote 에디터 정리
    try {
        if (contentEditor && typeof jQuery !== 'undefined' && jQuery.fn.summernote) {
            $('#content').summernote('destroy');
            contentEditor = null;
            console.log('Summernote 에디터 정리 완료');
        }
    } catch (error) {
        console.error('Summernote 정리 중 오류:', error);
        contentEditor = null;
    }
    
    document.getElementById('popup-editor').style.display = 'none';
}

// 팝업 편집
function editPopup(popupId) {
    window.location.href = '?tab=popup&edit=' + popupId;
}

// 팝업 삭제
function deletePopup(popupId) {
    if (confirm('정말 이 팝업을 삭제하시겠습니까?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="popup_id" value="${popupId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// 팝업 미리보기
function previewPopup(popupId) {
    fetch(`/admin/settings/popup/api/preview.php?id=${popupId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('popup-preview-title').textContent = data.title;
                document.getElementById('preview-content').innerHTML = data.content;
                const modal = new bootstrap.Modal(document.getElementById('popup-preview-modal'));
                modal.show();
            } else {
                alert('미리보기를 불러올 수 없습니다.');
            }
        })
        .catch(error => {
            console.error('미리보기 오류:', error);
            alert('미리보기 중 오류가 발생했습니다.');
        });
}

// 현재 입력된 내용으로 미리보기
function previewCurrentPopup() {
    const title = document.getElementById('title').value || '미리보기';
    let content = '';
    
    // Summernote 에디터에서 내용 가져오기
    if (contentEditor) {
        content = $('#content').summernote('code') || '내용을 입력해주세요.';
    } else {
        content = document.getElementById('content').value || '내용을 입력해주세요.';
    }
    
    document.getElementById('popup-preview-title').textContent = title;
    document.getElementById('preview-content').innerHTML = content;
    
    const modal = new bootstrap.Modal(document.getElementById('popup-preview-modal'));
    modal.show();
}

// 폼 제출 전 Summernote 내용 동기화
document.addEventListener('DOMContentLoaded', function() {
    const popupForm = document.getElementById('popup-form');
    if (popupForm) {
        popupForm.addEventListener('submit', function(e) {
            // Summernote 내용을 textarea에 동기화
            if (contentEditor) {
                const summernoteContent = $('#content').summernote('code');
                document.getElementById('content').value = summernoteContent;
            }
        });
    }
    
    // 페이지 로드시 이미 편집기가 표시되어 있다면 Summernote 초기화
    const popupEditor = document.getElementById('popup-editor');
    if (popupEditor && popupEditor.style.display !== 'none') {
        setTimeout(function() {
            initSummernote();
        }, 300);
    }
    
    // 글로벌 함수로 직접 테스트할 수 있도록 윈도우에 추가
    window.testSummernote = function() {
        console.log('수동 Summernote 테스트 시작');
        initSummernote();
    };
    
    // 새 팝업 버튼 클릭시 에디터 표시
    const newPopupBtns = document.querySelectorAll('button[onclick="showPopupEditor()"]');
    newPopupBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('새 팝업 버튼 클릭됨');
            showPopupEditor();
        });
    });
});

// 편집할 팝업 데이터 로드
<?php if ($editPopup): ?>
document.addEventListener('DOMContentLoaded', function() {
    const popup = <?= json_encode($editPopup) ?>;
    const conditions = popup.display_condition ? JSON.parse(popup.display_condition) : {};
    const styles = popup.style_settings ? JSON.parse(popup.style_settings) : {};
    
    // 편집기 표시
    document.getElementById('popup-editor').style.display = 'block';
    document.getElementById('editor-title').textContent = '팝업 편집';
    document.getElementById('form-action').value = 'update';
    document.getElementById('form-popup-id').value = popup.id;
    
    // 기본 정보
    document.getElementById('title').value = popup.title || '';
    document.getElementById('content').value = popup.content || '';
    document.getElementById('popup_type').value = popup.popup_type || 'notice';
    document.getElementById('show_frequency').value = popup.show_frequency || 'once';
    document.getElementById('priority').value = popup.priority || 1;
    document.getElementById('is_active').checked = popup.is_active == 1;
    
    // Summernote 초기화 및 내용 설정
    setTimeout(function() {
        initSummernote();
        // 에디터가 로드된 후 내용 설정
        setTimeout(function() {
            if (contentEditor && popup.content) {
                $('#content').summernote('code', popup.content);
            }
        }, 500);
    }, 100);
    
    // 날짜
    if (popup.start_date) {
        document.getElementById('start_date').value = popup.start_date.replace(' ', 'T');
    }
    if (popup.end_date) {
        document.getElementById('end_date').value = popup.end_date.replace(' ', 'T');
    }
    
    // 표시 조건
    if (conditions.target_pages) {
        document.querySelectorAll('input[name="target_pages[]"]').forEach(cb => {
            cb.checked = conditions.target_pages.includes(cb.value);
        });
    }
    
    if (conditions.device_type) {
        document.querySelectorAll('input[name="device_type[]"]').forEach(cb => {
            cb.checked = conditions.device_type.includes(cb.value);
        });
    }
    
    if (conditions.time_range) {
        document.getElementById('time_start').value = conditions.time_range.start || '00:00';
        document.getElementById('time_end').value = conditions.time_range.end || '23:59';
    }
    
    // 스타일 설정
    if (styles.width) {
        document.getElementById('popup_width').value = styles.width;
    }
    if (styles.bg_color) {
        document.getElementById('bg_color').value = styles.bg_color;
    }
    if (styles.animation) {
        document.getElementById('animation').value = styles.animation;
    }
});
<?php endif; ?>
</script>

<style>
.popup-manager .table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.popup-manager .badge {
    font-size: 0.75em;
}

.popup-manager .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.popup-manager #popup-editor {
    position: sticky;
    top: 20px;
}

.popup-manager .form-check-input:checked {
    background-color: #84cc16;
    border-color: #84cc16;
}

.popup-manager .btn-primary {
    background-color: #84cc16;
    border-color: #84cc16;
}

.popup-manager .btn-primary:hover {
    background-color: #22c55e;
    border-color: #22c55e;
}

/* Summernote 에디터 커스터마이징 */
.popup-manager .note-editor {
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
}

.popup-manager .note-editor.note-frame {
    margin-bottom: 0;
}

.popup-manager .note-toolbar {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
    padding: 8px;
}

.popup-manager .note-btn-group {
    margin-right: 5px;
}

.popup-manager .note-btn {
    background: transparent;
    border: 1px solid transparent;
    color: #5a5c69;
    padding: 4px 8px;
    margin: 1px;
    border-radius: 3px;
}

.popup-manager .note-btn:hover {
    background-color: #e2e6ea;
    border-color: #dae0e5;
}

.popup-manager .note-btn.active {
    background-color: #84cc16;
    border-color: #84cc16;
    color: white;
}

.popup-manager .note-editable {
    min-height: 300px;
    padding: 10px;
    font-family: 'Noto Sans KR', '맑은 고딕', sans-serif;
    font-size: 14px;
    line-height: 1.6;
}

.popup-manager .note-editable:focus {
    outline: none;
}
</style>