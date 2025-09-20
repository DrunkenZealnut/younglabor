<?php
// 통합 테마 시스템 데이터 가져오기
$allThemes = $globalThemeIntegration->getAllThemes();
$currentActiveTheme = $globalThemeIntegration->getActiveTheme();
$themeStats = $globalThemeIntegration->getThemeStats();

// 테마 설정 저장 처리
if (isset($_POST['save_theme_management'])) {
    try {
        // 활성 테마 변경
        if (isset($_POST['selected_theme']) && !empty($_POST['selected_theme'])) {
            $globalThemeIntegration->setActiveTheme($_POST['selected_theme']);
            $success_message = "테마가 '{$_POST['selected_theme']}'로 변경되었습니다.";
        }
        
        // 새로운 글로벌 테마 추가
        if (isset($_POST['new_global_theme']) && !empty($_POST['new_global_theme_name']) && !empty($_POST['new_global_theme_css'])) {
            $themeName = trim($_POST['new_global_theme_name']);
            $cssContent = $_POST['new_global_theme_css'];
            $globalThemeIntegration->registerGlobalTheme($themeName, $cssContent);
            $success_message .= " 새로운 글로벌 테마 '{$themeName}'가 등록되었습니다.";
        }
        
        $active_tab = 'themes'; // 테마 탭 활성화 유지
        
        // 데이터 새로고침
        $allThemes = $globalThemeIntegration->getAllThemes();
        $currentActiveTheme = $globalThemeIntegration->getActiveTheme();
        $themeStats = $globalThemeIntegration->getThemeStats();
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        $active_tab = 'themes';
    }
}
?>

<!-- 테마 관리 탭 (통합 시스템) -->
<div class="tab-pane fade <?= $active_tab === 'themes' ? 'show active' : '' ?>" 
     id="themes-pane" role="tabpanel" aria-labelledby="themes-tab">
     
    <!-- 통합 테마 시스템 안내 -->
    <div class="alert alert-info">
        <h6><i class="fas fa-info-circle"></i> 통합 테마 시스템</h6>
        <p class="mb-2">기존 테마와 새로운 글로벌 테마를 통합 관리합니다.</p>
        <div class="row text-center">
            <div class="col-md-3">
                <span class="badge bg-primary fs-6"><?= $themeStats['total'] ?></span>
                <div class="small">전체 테마</div>
            </div>
            <div class="col-md-3">
                <span class="badge bg-success fs-6"><?= $themeStats['traditional'] ?></span>
                <div class="small">기존 테마</div>
            </div>
            <div class="col-md-3">
                <span class="badge bg-info fs-6"><?= $themeStats['global'] ?></span>
                <div class="small">글로벌 테마</div>
            </div>
            <div class="col-md-3">
                <span class="badge bg-warning fs-6">1</span>
                <div class="small">현재 활성</div>
            </div>
        </div>
        <div class="mt-2">
            <small><strong>현재 활성 테마:</strong> <?= htmlspecialchars($currentActiveTheme) ?></small>
            <a href="../theme-management.php" class="btn btn-sm btn-outline-primary ms-2">
                <i class="fas fa-cogs"></i> 고급 테마 관리
            </a>
            <a href="/theme-test.php" target="_blank" class="btn btn-sm btn-outline-info ms-2">
                <i class="fas fa-eye"></i> 테마 테스트 페이지
            </a>
        </div>
    </div>

    <form action="site_settings.php?tab=themes" method="POST">
        <!-- 테마 선택 섹션 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-palette"></i> 테마 선택</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($allThemes as $themeName => $theme): ?>
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card theme-preview-card <?= $themeName === $currentActiveTheme ? 'border-success' : '' ?>">
                                <!-- 테마 미리보기 -->
                                <div class="theme-preview-area" style="height: 120px; position: relative; overflow: hidden;">
                                    <?php if ($theme['type'] === 'global'): ?>
                                        <!-- 글로벌 테마 미리보기 -->
                                        <div class="d-flex h-100">
                                            <div class="flex-fill" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                                            <div class="flex-fill" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);"></div>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-info">Global</span>
                                        </div>
                                    <?php else: ?>
                                        <!-- 기존 테마 미리보기 -->
                                        <div class="d-flex h-100">
                                            <div class="flex-fill" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                            <div class="flex-fill" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-success">Classic</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- 현재 활성 테마 표시 -->
                                    <?php if ($themeName === $currentActiveTheme): ?>
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> 활성
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- 테마 이름 오버레이 -->
                                    <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white text-center py-1">
                                        <small class="fw-bold"><?= htmlspecialchars($theme['display_name']) ?></small>
                                    </div>
                                </div>
                                
                                <!-- 테마 정보 -->
                                <div class="card-body p-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="selected_theme" 
                                               id="theme_<?= $themeName ?>" value="<?= htmlspecialchars($themeName) ?>"
                                               <?= $themeName === $currentActiveTheme ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="theme_<?= $themeName ?>">
                                            <strong><?= htmlspecialchars($theme['display_name']) ?></strong>
                                        </label>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?= htmlspecialchars($theme['description']) ?>
                                    </div>
                                    <div class="small text-muted">
                                        <?= ucfirst($theme['type']) ?> • v<?= htmlspecialchars($theme['version'] ?? '1.0.0') ?>
                                    </div>
                                </div>
                                
                                <!-- 테마 액션 버튼 -->
                                <div class="card-footer p-2">
                                    <div class="btn-group w-100" role="group">
                                        <a href="<?= htmlspecialchars($theme['preview_url']) ?>" 
                                           target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($theme['can_delete'] && $theme['type'] === 'global'): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="confirmDeleteTheme('<?= htmlspecialchars($themeName) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 빠른 글로벌 테마 추가 -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-plus"></i> 빠른 글로벌 테마 추가</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label for="new_global_theme_name" class="form-label">테마명</label>
                        <input type="text" class="form-control form-control-sm" 
                               id="new_global_theme_name" name="new_global_theme_name" 
                               placeholder="예: orange, pink" pattern="[a-zA-Z0-9_-]+">
                        <div class="form-text">영문, 숫자, 하이픈, 밑줄만 사용</div>
                    </div>
                    <div class="col-md-8">
                        <label for="new_global_theme_css" class="form-label">CSS 템플릿</label>
                        <select class="form-select form-select-sm" id="css_template_select" onchange="loadCssTemplate()">
                            <option value="">사용할 템플릿을 선택하세요...</option>
                            <option value="blue">파란색 테마</option>
                            <option value="red">빨간색 테마</option>
                            <option value="purple">보라색 테마</option>
                            <option value="custom">직접 입력</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="new_global_theme_css" class="form-label">CSS 내용</label>
                    <textarea class="form-control font-monospace" 
                              id="new_global_theme_css" name="new_global_theme_css" 
                              rows="8" placeholder="CSS 내용이 여기에 표시됩니다..."></textarea>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" 
                            onclick="document.getElementById('new_global_theme_css').value = ''">
                        <i class="fas fa-eraser"></i> 지우기
                    </button>
                    <input type="hidden" name="new_global_theme" value="1">
                </div>
            </div>
        </div>

        <!-- 저장 버튼 -->
        <div class="d-flex justify-content-between">
            <div>
                <button type="submit" name="save_theme_management" class="btn btn-primary">
                    <i class="fas fa-save"></i> 테마 설정 저장
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-redo"></i> 새로고침
                </button>
            </div>
            <div>
                <a href="../theme-management.php" class="btn btn-outline-info">
                    <i class="fas fa-cogs"></i> 고급 테마 관리
                </a>
            </div>
        </div>
    </form>
</div>

<!-- CSS 템플릿 스크립트 -->
<script>
const cssTemplates = {
    blue: `@custom-variant dark (&:is(.dark *));

:root {
  --font-size: 14px;
  --background: #f8fbff;
  --foreground: oklch(0.145 0 0);
  --primary: #0066cc;
  --primary-foreground: oklch(1 0 0);
  --secondary: oklch(0.95 0.0058 264.53);
  --secondary-foreground: #0066cc;
  --muted: #f1f7ff;
  --accent: #e6f3ff;
  --destructive: #d4183d;
  --border: rgba(0, 102, 204, 0.15);
  --radius: 0.625rem;
}`,
    red: `@custom-variant dark (&:is(.dark *));

:root {
  --font-size: 14px;
  --background: #fef8f8;
  --foreground: oklch(0.145 0 0);
  --primary: #dc2626;
  --primary-foreground: oklch(1 0 0);
  --secondary: oklch(0.95 0.0058 264.53);
  --secondary-foreground: #dc2626;
  --muted: #fef2f2;
  --accent: #fee2e2;
  --destructive: #dc2626;
  --border: rgba(220, 38, 38, 0.15);
  --radius: 0.625rem;
}`,
    purple: `@custom-variant dark (&:is(.dark *));

:root {
  --font-size: 14px;
  --background: #faf7ff;
  --foreground: oklch(0.145 0 0);
  --primary: #7c3aed;
  --primary-foreground: oklch(1 0 0);
  --secondary: oklch(0.95 0.0058 264.53);
  --secondary-foreground: #7c3aed;
  --muted: #f3f0ff;
  --accent: #e4d4f4;
  --destructive: #dc2626;
  --border: rgba(124, 58, 237, 0.15);
  --radius: 0.625rem;
}`
};

function loadCssTemplate() {
    const select = document.getElementById('css_template_select');
    const textarea = document.getElementById('new_global_theme_css');
    const nameInput = document.getElementById('new_global_theme_name');
    
    if (select.value && cssTemplates[select.value]) {
        textarea.value = cssTemplates[select.value];
        
        // 테마명이 비어있으면 자동으로 설정
        if (!nameInput.value.trim()) {
            nameInput.value = select.value;
        }
    } else if (select.value === 'custom') {
        textarea.value = '';
        textarea.placeholder = '직접 CSS를 입력하세요...';
    }
}

function confirmDeleteTheme(themeName) {
    if (confirm(`정말로 '${themeName}' 테마를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.`)) {
        // 삭제 요청 전송
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../theme-management.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_theme';
        
        const themeInput = document.createElement('input');
        themeInput.type = 'hidden';
        themeInput.name = 'delete_theme_name';
        themeInput.value = themeName;
        
        form.appendChild(actionInput);
        form.appendChild(themeInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// 테마 변경 시 즉시 저장 옵션
document.querySelectorAll('input[name="selected_theme"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (confirm('선택한 테마를 즉시 적용하시겠습니까?')) {
            this.closest('form').submit();
        }
    });
});
</script>

<style>
.theme-preview-card {
    transition: all 0.3s ease;
}

.theme-preview-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.theme-preview-card.border-success {
    box-shadow: 0 0 0 2px #198754;
}

.theme-preview-area {
    border-bottom: 1px solid #dee2e6;
}
</style>