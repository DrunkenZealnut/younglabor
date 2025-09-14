<?php
/**
 * 메뉴 관리 목록 뷰 - MVC 버전
 * MenuController::index()에서 사용
 */

// 메뉴 트리 구조를 HTML로 변환하는 재귀 함수
function renderMenuTree($menus, $level = 0) {
    $html = '';
    
    foreach ($menus as $menu) {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $level_indicator = $level > 0 ? '└ ' : '';
        
        // 상태 배지
        $status_badge = $menu['is_active'] 
            ? '<span class="badge bg-success">활성</span>' 
            : '<span class="badge bg-secondary">비활성</span>';
            
        // 메뉴 타입 배지
        $type_color = match($menu['menu_type']) {
            'page' => 'primary',
            'link' => 'info',
            'category' => 'warning',
            default => 'secondary'
        };
        $type_badge = '<span class="badge bg-' . $type_color . '">' . 
                     ucfirst($menu['menu_type']) . '</span>';
        
        // 타겟 표시
        $target_icon = $menu['target'] === '_blank' 
            ? '<i class="bi bi-box-arrow-up-right text-muted ms-1"></i>' 
            : '';
            
        $html .= '<tr data-menu-id="' . $menu['id'] . '" data-level="' . $level . '" class="menu-row-level-' . $level . '">
            <td class="menu-hierarchy">
                ' . $indent . $level_indicator . 
                '<span class="menu-title">' . htmlspecialchars($menu['title']) . '</span>
                ' . $target_icon . '
            </td>
            <td>' . $type_badge . '</td>
            <td><small class="text-muted">' . htmlspecialchars($menu['url'] ?: '-') . '</small></td>
            <td class="text-center">' . $menu['sort_order'] . '</td>
            <td class="text-center">' . $status_badge . '</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="/admin/menus/edit/' . $menu['id'] . '" class="btn btn-outline-warning" title="수정">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="addSubmenu(' . $menu['id'] . ')" title="하위메뉴 추가">
                        <i class="bi bi-plus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="moveMenu(' . $menu['id'] . ', \'up\')" title="위로">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="moveMenu(' . $menu['id'] . ', \'down\')" title="아래로">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteMenu(' . $menu['id'] . ')" title="삭제">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>';
        
        // 하위 메뉴가 있으면 재귀 호출
        if (!empty($menu['children'])) {
            $html .= renderMenuTree($menu['children'], $level + 1);
        }
    }
    
    return $html;
}
?>

<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">메뉴 관리</h1>
        <div>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-success" onclick="toggleAllMenus(true)">
                    <i class="bi bi-check-all"></i> 전체 활성화
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="toggleAllMenus(false)">
                    <i class="bi bi-x-circle"></i> 전체 비활성화
                </button>
            </div>
            <a href="/admin/menus/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 메뉴 추가
            </a>
        </div>
    </div>

    <!-- 메뉴 위치별 탭 -->
    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="menuTabs">
                <?php foreach ($positions as $position => $label): ?>
                <li class="nav-item">
                    <button class="nav-link <?= $position === ($current_position ?? 'main') ? 'active' : '' ?>" 
                            data-position="<?= $position ?>" type="button">
                        <?= htmlspecialchars($label) ?>
                        <span class="badge bg-primary ms-1">
                            <?= count($menu_trees[$position] ?? []) ?>
                        </span>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card-body">
            <!-- 메뉴 트리 테이블 -->
            <div class="table-responsive">
                <table class="table table-hover" id="menuTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40%">메뉴명</th>
                            <th width="15%">타입</th>
                            <th width="25%">URL</th>
                            <th width="8%" class="text-center">순서</th>
                            <th width="8%" class="text-center">상태</th>
                            <th width="12%" class="text-center">관리</th>
                        </tr>
                    </thead>
                    <tbody id="menuTreeBody">
                        <!-- 메뉴 트리가 여기에 로드됩니다 -->
                    </tbody>
                </table>
                
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <i class="bi bi-list-ul display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">등록된 메뉴가 없습니다</h5>
                    <p class="text-muted">새로운 메뉴를 추가해보세요.</p>
                    <a href="/admin/menus/create" class="btn btn-primary">
                        <i class="bi bi-plus"></i> 메뉴 추가
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 메뉴 이동/정렬 도구 -->
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="bi bi-arrows-move"></i> 메뉴 정렬 도구
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-primary" onclick="sortMenus('alphabetical')">
                            <i class="bi bi-sort-alpha-down"></i> 알파벳 순 정렬
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-info" onclick="expandAll()">
                            <i class="bi bi-arrows-expand"></i> 전체 펼치기
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-secondary" onclick="collapseAll()">
                            <i class="bi bi-arrows-collapse"></i> 전체 접기
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 메뉴 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">메뉴 삭제 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="deleteContent">
                    <p>선택한 메뉴를 삭제하시겠습니까?</p>
                    <p class="text-danger small">하위 메뉴가 있는 경우 함께 삭제됩니다.</p>
                </div>
                <div id="deleteWithChildren" style="display: none;">
                    <p class="text-warning">이 메뉴에는 다음 하위 메뉴들이 있습니다:</p>
                    <ul id="childrenList" class="list-unstyled ms-3"></ul>
                    <p class="text-danger small">모든 하위 메뉴가 함께 삭제됩니다.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">삭제</button>
            </div>
        </div>
    </div>
</div>

<script>
// 전역 변수
let menuTrees = <?= json_encode($menu_trees) ?>;
let currentPosition = '<?= $current_position ?? 'main' ?>';
let targetMenuId = null;

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    initMenuTabs();
    loadMenuTree(currentPosition);
});

// 탭 초기화
function initMenuTabs() {
    const tabs = document.querySelectorAll('#menuTabs .nav-link');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const position = this.dataset.position;
            switchTab(position);
        });
    });
}

// 탭 전환
function switchTab(position) {
    // 탭 상태 업데이트
    document.querySelectorAll('#menuTabs .nav-link').forEach(tab => {
        tab.classList.toggle('active', tab.dataset.position === position);
    });
    
    // 메뉴 트리 로드
    currentPosition = position;
    loadMenuTree(position);
}

// 메뉴 트리 로드
function loadMenuTree(position) {
    const tbody = document.getElementById('menuTreeBody');
    const emptyState = document.getElementById('emptyState');
    
    const menus = menuTrees[position] || [];
    
    if (menus.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
    } else {
        tbody.innerHTML = renderMenuTreeHtml(menus);
        emptyState.style.display = 'none';
        initMenuRowEvents();
    }
}

// 메뉴 트리 HTML 생성
function renderMenuTreeHtml(menus, level = 0) {
    let html = '';
    
    menus.forEach(menu => {
        const indent = '&nbsp;'.repeat(level * 4);
        const levelIndicator = level > 0 ? '└ ' : '';
        
        const statusBadge = menu.is_active 
            ? '<span class="badge bg-success">활성</span>'
            : '<span class="badge bg-secondary">비활성</span>';
            
        const typeColors = { page: 'primary', link: 'info', category: 'warning' };
        const typeBadge = `<span class="badge bg-${typeColors[menu.menu_type] || 'secondary'}">${menu.menu_type.toUpperCase()}</span>`;
        
        const targetIcon = menu.target === '_blank' 
            ? '<i class="bi bi-box-arrow-up-right text-muted ms-1"></i>'
            : '';
            
        html += `
        <tr data-menu-id="${menu.id}" data-level="${level}" class="menu-row-level-${level}">
            <td class="menu-hierarchy">
                ${indent}${levelIndicator}
                <span class="menu-title">${escapeHtml(menu.title)}</span>
                ${targetIcon}
            </td>
            <td>${typeBadge}</td>
            <td><small class="text-muted">${escapeHtml(menu.url || '-')}</small></td>
            <td class="text-center">${menu.sort_order}</td>
            <td class="text-center">${statusBadge}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="/admin/menus/edit/${menu.id}" class="btn btn-outline-warning" title="수정">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="addSubmenu(${menu.id})" title="하위메뉴 추가">
                        <i class="bi bi-plus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="moveMenu(${menu.id}, 'up')" title="위로">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="moveMenu(${menu.id}, 'down')" title="아래로">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteMenu(${menu.id})" title="삭제">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
        
        if (menu.children && menu.children.length > 0) {
            html += renderMenuTreeHtml(menu.children, level + 1);
        }
    });
    
    return html;
}

// 메뉴 행 이벤트 초기화
function initMenuRowEvents() {
    // 드래그 앤 드롭 기능 (여기서는 기본 구현만)
    const rows = document.querySelectorAll('#menuTreeBody tr');
    rows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('.btn-group')) {
                this.classList.toggle('table-active');
            }
        });
    });
}

// 하위 메뉴 추가
function addSubmenu(parentId) {
    window.location.href = `/admin/menus/create?parent_id=${parentId}&position=${currentPosition}`;
}

// 메뉴 이동
function moveMenu(menuId, direction) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    formData.append('direction', direction);
    
    fetch(`/admin/menus/move/${menuId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 시 페이지 새로고침 또는 트리 다시 로드
            location.reload();
        } else {
            alert(data.message || '메뉴 이동 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('메뉴 이동 중 오류가 발생했습니다.');
    });
}

// 메뉴 삭제
function deleteMenu(menuId) {
    targetMenuId = menuId;
    
    // 하위 메뉴 확인
    const hasChildren = checkHasChildren(menuId);
    
    if (hasChildren) {
        const children = getChildrenList(menuId);
        document.getElementById('deleteContent').style.display = 'none';
        document.getElementById('deleteWithChildren').style.display = 'block';
        
        const childrenList = document.getElementById('childrenList');
        childrenList.innerHTML = children.map(child => 
            `<li><i class="bi bi-arrow-return-right"></i> ${escapeHtml(child.title)}</li>`
        ).join('');
    } else {
        document.getElementById('deleteContent').style.display = 'block';
        document.getElementById('deleteWithChildren').style.display = 'none';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// 하위 메뉴 존재 확인
function checkHasChildren(menuId) {
    const findInTree = (menus) => {
        for (const menu of menus) {
            if (menu.id == menuId) {
                return menu.children && menu.children.length > 0;
            }
            if (menu.children && menu.children.length > 0) {
                const result = findInTree(menu.children);
                if (result !== undefined) return result;
            }
        }
    };
    
    return findInTree(menuTrees[currentPosition] || []) || false;
}

// 하위 메뉴 목록 가져오기
function getChildrenList(menuId) {
    const findInTree = (menus) => {
        for (const menu of menus) {
            if (menu.id == menuId) {
                return menu.children || [];
            }
            if (menu.children && menu.children.length > 0) {
                const result = findInTree(menu.children);
                if (result) return result;
            }
        }
    };
    
    return findInTree(menuTrees[currentPosition] || []) || [];
}

// 메뉴 삭제 확인
document.getElementById('confirmDelete').addEventListener('click', function() {
    if (targetMenuId) {
        const formData = new FormData();
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
        
        fetch(`/admin/menus/delete/${targetMenuId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '메뉴 삭제 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('메뉴 삭제 중 오류가 발생했습니다.');
        });
    }
});

// 전체 메뉴 활성화/비활성화
function toggleAllMenus(isActive) {
    if (confirm(`현재 위치의 모든 메뉴를 ${isActive ? '활성화' : '비활성화'}하시겠습니까?`)) {
        const formData = new FormData();
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
        formData.append('position', currentPosition);
        formData.append('is_active', isActive ? '1' : '0');
        
        fetch('/admin/menus/toggleAll', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '작업 중 오류가 발생했습니다.');
            }
        });
    }
}

// 메뉴 정렬
function sortMenus(type) {
    if (confirm('메뉴를 정렬하시겠습니까? 현재 순서가 변경됩니다.')) {
        const formData = new FormData();
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
        formData.append('position', currentPosition);
        formData.append('sort_type', type);
        
        fetch('/admin/menus/sort', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '정렬 중 오류가 발생했습니다.');
            }
        });
    }
}

// 전체 펼치기/접기
function expandAll() {
    const rows = document.querySelectorAll('#menuTreeBody tr');
    rows.forEach(row => row.style.display = 'table-row');
}

function collapseAll() {
    const rows = document.querySelectorAll('#menuTreeBody tr[data-level]');
    rows.forEach(row => {
        if (parseInt(row.dataset.level) > 0) {
            row.style.display = 'none';
        }
    });
}

// HTML 이스케이프
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<style>
/* 메뉴 관리 특화 스타일 */
.menu-hierarchy {
    font-family: 'Courier New', monospace;
}

.menu-row-level-0 {
    font-weight: 600;
}

.menu-row-level-1 {
    background-color: rgba(13, 110, 253, 0.05);
}

.menu-row-level-2 {
    background-color: rgba(13, 110, 253, 0.1);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.table-active {
    background-color: rgba(13, 110, 253, 0.2) !important;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

/* 드래그 앤 드롭 스타일 (추후 구현) */
.dragging {
    opacity: 0.5;
}

.drop-target {
    border-top: 2px solid #0d6efd;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .table-responsive {
        border: none;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 2px;
    }
    
    .menu-hierarchy {
        font-size: 0.9rem;
    }
}

/* 로딩 상태 */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* 애니메이션 */
.table tbody tr {
    transition: background-color 0.2s ease;
}

.btn {
    transition: all 0.2s ease;
}
</style>