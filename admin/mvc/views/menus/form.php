<?php
/**
 * 메뉴 등록/수정 폼 뷰 - MVC 버전
 * MenuController::create() 및 edit()에서 사용
 */

$is_edit = isset($menu) && !empty($menu);
$page_title = $is_edit ? '메뉴 수정' : '메뉴 등록';
$form_action = $is_edit ? '/admin/menus/update/' . $menu['id'] : '/admin/menus/store';
$submit_text = $is_edit ? '수정하기' : '등록하기';

// 기본값 설정
$menu_data = $menu ?? [
    'title' => '',
    'url' => '',
    'menu_type' => 'page',
    'position' => $_GET['position'] ?? 'main',
    'parent_id' => $_GET['parent_id'] ?? null,
    'target' => '_self',
    'icon_class' => '',
    'description' => '',
    'sort_order' => 0,
    'is_active' => 1
];

// 부모 메뉴 정보
$parent_menu = null;
if (!empty($menu_data['parent_id'])) {
    // 실제로는 부모 메뉴 정보를 컨트롤러에서 전달받아야 함
    $parent_menu = $parent_menus[$menu_data['parent_id']] ?? null;
}
?>

<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/admin/menus">메뉴 관리</a>
                    </li>
                    <li class="breadcrumb-item active"><?= $page_title ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-0"><?= $page_title ?></h1>
            <?php if ($parent_menu): ?>
            <p class="text-muted mb-0">
                <i class="bi bi-arrow-return-right"></i>
                상위 메뉴: <strong><?= htmlspecialchars($parent_menu['title']) ?></strong>
            </p>
            <?php endif; ?>
        </div>
        <div>
            <a href="/admin/menus" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <form method="POST" action="<?= $form_action ?>" id="menuForm">
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
                                <label for="title" class="form-label">메뉴 제목 *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= htmlspecialchars($menu_data['title']) ?>" 
                                       required maxlength="100">
                                <div class="form-text">사용자에게 표시될 메뉴명입니다.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="menu_type" class="form-label">메뉴 타입 *</label>
                                <select class="form-select" id="menu_type" name="menu_type" required>
                                    <option value="page" <?= $menu_data['menu_type'] === 'page' ? 'selected' : '' ?>>
                                        페이지 링크
                                    </option>
                                    <option value="link" <?= $menu_data['menu_type'] === 'link' ? 'selected' : '' ?>>
                                        외부 링크
                                    </option>
                                    <option value="category" <?= $menu_data['menu_type'] === 'category' ? 'selected' : '' ?>>
                                        카테고리 (URL 없음)
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="target" class="form-label">링크 타겟</label>
                                <select class="form-select" id="target" name="target">
                                    <option value="_self" <?= $menu_data['target'] === '_self' ? 'selected' : '' ?>>
                                        현재 창 (_self)
                                    </option>
                                    <option value="_blank" <?= $menu_data['target'] === '_blank' ? 'selected' : '' ?>>
                                        새 창 (_blank)
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-12" id="urlField">
                                <label for="url" class="form-label">URL</label>
                                <input type="url" class="form-control" id="url" name="url" 
                                       value="<?= htmlspecialchars($menu_data['url']) ?>"
                                       placeholder="https://example.com 또는 /path/to/page">
                                <div class="form-text">
                                    외부 링크는 http:// 또는 https://로 시작하고, 내부 링크는 /로 시작합니다.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="icon_class" class="form-label">아이콘 클래스</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="icon_class" name="icon_class" 
                                           value="<?= htmlspecialchars($menu_data['icon_class']) ?>"
                                           placeholder="bi bi-house">
                                    <button type="button" class="btn btn-outline-secondary" onclick="showIconPicker()">
                                        <i class="bi bi-palette"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Bootstrap Icons 클래스명 (예: bi bi-house)
                                    <span id="iconPreview" class="ms-2"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">정렬 순서</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?= $menu_data['sort_order'] ?>" min="0" max="999">
                                <div class="form-text">숫자가 작을수록 먼저 표시됩니다.</div>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">설명</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" maxlength="255"><?= htmlspecialchars($menu_data['description']) ?></textarea>
                                <div class="form-text">메뉴에 대한 간단한 설명 (선택사항)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- URL 테스트 -->
                <div class="card mb-4" id="urlTestCard" style="display: none;">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-link-45deg"></i> URL 테스트
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-primary me-3" onclick="testUrl()">
                                <i class="bi bi-play"></i> URL 테스트
                            </button>
                            <div id="urlTestResult" class="flex-grow-1"></div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                입력된 URL이 정상적으로 접근 가능한지 확인합니다.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 사이드바 (설정 및 옵션) -->
            <div class="col-lg-4">
                <!-- 위치 및 구조 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-diagram-3"></i> 메뉴 구조
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="position" class="form-label">메뉴 위치 *</label>
                                <select class="form-select" id="position" name="position" required>
                                    <?php foreach ($positions as $pos => $label): ?>
                                    <option value="<?= $pos ?>" <?= $menu_data['position'] === $pos ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="parent_id" class="form-label">상위 메뉴</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">최상위 메뉴</option>
                                    <?php if (!empty($available_parents)): ?>
                                        <?php foreach ($available_parents as $parent): ?>
                                        <option value="<?= $parent['id'] ?>" 
                                                <?= $menu_data['parent_id'] == $parent['id'] ? 'selected' : '' ?>>
                                            <?= str_repeat('└ ', $parent['level']) . htmlspecialchars($parent['title']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">
                                    선택하지 않으면 최상위 메뉴로 등록됩니다.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" 
                                           <?= $menu_data['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        메뉴 활성화
                                    </label>
                                </div>
                                <div class="form-text">
                                    비활성화된 메뉴는 사이트에서 표시되지 않습니다.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 미리보기 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-eye"></i> 미리보기
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="menuPreview" class="menu-preview-box p-3 bg-light rounded">
                            <div class="menu-item">
                                <i id="previewIcon" class="me-2"></i>
                                <span id="previewTitle">메뉴 제목</span>
                                <span id="previewTarget" class="ms-1"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> <?= $submit_text ?>
                        </button>
                        <?php if ($is_edit): ?>
                        <a href="/admin/menus/view/<?= $menu['id'] ?>" class="btn btn-outline-info">
                            <i class="bi bi-eye"></i> 미리보기
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 아이콘 선택 모달 -->
<div class="modal fade" id="iconModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">아이콘 선택</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="iconGrid">
                    <!-- 아이콘 목록이 여기에 동적 생성 -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 메뉴 타입 변경 시 URL 필드 표시/숨김
document.getElementById('menu_type').addEventListener('change', function() {
    const urlField = document.getElementById('urlField');
    const urlInput = document.getElementById('url');
    const urlTestCard = document.getElementById('urlTestCard');
    
    if (this.value === 'category') {
        urlField.style.display = 'none';
        urlInput.required = false;
        urlTestCard.style.display = 'none';
    } else {
        urlField.style.display = 'block';
        urlInput.required = true;
        urlTestCard.style.display = 'block';
    }
    updatePreview();
});

// 폼 입력 시 미리보기 업데이트
document.addEventListener('DOMContentLoaded', function() {
    const previewFields = ['title', 'icon_class', 'target'];
    previewFields.forEach(field => {
        document.getElementById(field).addEventListener('input', updatePreview);
    });
    
    // 초기 미리보기 업데이트
    updatePreview();
    
    // 메뉴 타입 초기 설정
    document.getElementById('menu_type').dispatchEvent(new Event('change'));
});

// 미리보기 업데이트
function updatePreview() {
    const title = document.getElementById('title').value || '메뉴 제목';
    const iconClass = document.getElementById('icon_class').value;
    const target = document.getElementById('target').value;
    
    document.getElementById('previewTitle').textContent = title;
    
    const iconElement = document.getElementById('previewIcon');
    if (iconClass) {
        iconElement.className = iconClass + ' me-2';
        iconElement.style.display = 'inline';
    } else {
        iconElement.style.display = 'none';
    }
    
    const targetElement = document.getElementById('previewTarget');
    if (target === '_blank') {
        targetElement.innerHTML = '<i class="bi bi-box-arrow-up-right text-muted"></i>';
    } else {
        targetElement.innerHTML = '';
    }
    
    // 아이콘 입력 필드 옆 미리보기
    const iconPreview = document.getElementById('iconPreview');
    if (iconClass) {
        iconPreview.innerHTML = `<i class="${iconClass}"></i>`;
    } else {
        iconPreview.innerHTML = '';
    }
}

// 아이콘 선택기 표시
function showIconPicker() {
    // 인기 있는 Bootstrap Icons 목록
    const popularIcons = [
        'bi-house', 'bi-person', 'bi-envelope', 'bi-telephone', 'bi-gear',
        'bi-list', 'bi-grid', 'bi-search', 'bi-plus', 'bi-pencil',
        'bi-trash', 'bi-eye', 'bi-heart', 'bi-star', 'bi-bookmark',
        'bi-calendar', 'bi-clock', 'bi-map', 'bi-camera', 'bi-image',
        'bi-file-text', 'bi-folder', 'bi-download', 'bi-upload', 'bi-share',
        'bi-arrow-left', 'bi-arrow-right', 'bi-arrow-up', 'bi-arrow-down',
        'bi-check', 'bi-x', 'bi-info-circle', 'bi-exclamation-triangle'
    ];
    
    const iconGrid = document.getElementById('iconGrid');
    iconGrid.innerHTML = popularIcons.map(icon => `
        <div class="col-2 mb-2">
            <button type="button" class="btn btn-outline-secondary w-100 icon-btn" 
                    onclick="selectIcon('${icon}')">
                <i class="bi ${icon}"></i>
            </button>
        </div>
    `).join('');
    
    const modal = new bootstrap.Modal(document.getElementById('iconModal'));
    modal.show();
}

// 아이콘 선택
function selectIcon(iconClass) {
    document.getElementById('icon_class').value = 'bi ' + iconClass;
    updatePreview();
    bootstrap.Modal.getInstance(document.getElementById('iconModal')).hide();
}

// URL 테스트
function testUrl() {
    const url = document.getElementById('url').value.trim();
    const resultDiv = document.getElementById('urlTestResult');
    
    if (!url) {
        resultDiv.innerHTML = '<span class="text-warning">URL을 입력해주세요.</span>';
        return;
    }
    
    resultDiv.innerHTML = '<span class="text-info"><i class="bi bi-arrow-repeat spin"></i> 테스트 중...</span>';
    
    // 실제 구현에서는 서버 사이드에서 URL 검증
    fetch('/admin/menus/testUrl', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            url: url,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> 접근 가능 (${data.status_code})</span>`;
        } else {
            resultDiv.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle"></i> ${data.message}</span>`;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> 테스트 실패</span>';
    });
}

// 폼 유효성 검사
document.getElementById('menuForm').addEventListener('submit', function(e) {
    const menuType = document.getElementById('menu_type').value;
    const url = document.getElementById('url').value.trim();
    const title = document.getElementById('title').value.trim();
    
    if (!title) {
        e.preventDefault();
        alert('메뉴 제목은 필수 입력 항목입니다.');
        document.getElementById('title').focus();
        return;
    }
    
    if (menuType !== 'category' && !url) {
        e.preventDefault();
        alert('URL은 필수 입력 항목입니다.');
        document.getElementById('url').focus();
        return;
    }
    
    // URL 형식 검사
    if (url && menuType === 'link') {
        const urlPattern = /^https?:\/\/.+/;
        if (!urlPattern.test(url)) {
            e.preventDefault();
            alert('외부 링크는 http:// 또는 https://로 시작해야 합니다.');
            document.getElementById('url').focus();
            return;
        }
    }
});
</script>

<style>
/* 메뉴 폼 스타일 */
.menu-preview-box {
    border: 1px solid #dee2e6;
}

.menu-item {
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.icon-btn {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-btn:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

/* 폼 검증 상태 */
.is-invalid {
    border-color: #dc3545;
}

.is-valid {
    border-color: #198754;
}

/* 스피너 애니메이션 */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .col-2 {
        width: 16.666667%;
    }
    
    .icon-btn {
        padding: 0.5rem;
        font-size: 1.2rem;
    }
}

/* 카드 호버 효과 */
.card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s ease-in-out;
}
</style>