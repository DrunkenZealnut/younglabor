<?php
/**
 * 개선된 테마 설정 페이지 (색상 선택기 문제 해결)
 * 
 * HTML5 색상 입력과 Bootstrap 기반의 안정적인 색상 선택 UI
 */

// 출력 버퍼링 시작 (헤더 문제 방지)
ob_start();

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 인증 확인
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    ob_end_clean();
    header("Location: login.php?expired=1");
    exit;
}

// 데이터베이스 연결
require_once '../includes/db_connect.php';

// ThemeService 직접 로드 및 초기화
require_once 'mvc/services/ThemeService.php';
$themeService = new ThemeService($pdo);
$currentSettings = $themeService->getThemeSettings();

// 현재 활성화된 탭
$active_tab = $_GET['tab'] ?? 'colors';

// 페이지 변수 설정
$page_title = '테마 설정 (개선된 버전)';
$active_menu = 'theme_settings';

// CSS/JS 라이브러리 (더 간단한 구성)
$additional_css = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'
];

$additional_js = [
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

// 메인 콘텐츠 시작
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- 설정 패널 -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-palette"></i> 테마 설정
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- 알림 영역 -->
                    <div id="alert-container"></div>
                    
                    <!-- 탭 메뉴 -->
                    <ul class="nav nav-pills nav-fill mb-4" id="theme-tabs">
                        <li class="nav-item">
                            <button class="nav-link <?= $active_tab === 'colors' ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#colors-tab"
                                    type="button">
                                <i class="bi bi-palette"></i> 색상
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= $active_tab === 'fonts' ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#fonts-tab"
                                    type="button">
                                <i class="bi bi-fonts"></i> 폰트
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= $active_tab === 'layout' ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#layout-tab"
                                    type="button">
                                <i class="bi bi-layout-text-window"></i> 레이아웃
                            </button>
                        </li>
                    </ul>
                    
                    <!-- 탭 내용 -->
                    <div class="tab-content" id="theme-content">
                        <!-- 색상 설정 탭 -->
                        <div class="tab-pane fade <?= $active_tab === 'colors' ? 'show active' : '' ?>" 
                             id="colors-tab">
                            <form id="colors-form">
                                <!-- Primary Color -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">주 색상 (Primary)</label>
                                    <div class="color-input-group">
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <div class="color-preview" 
                                                     style="width: 35px; height: 35px; background-color: <?= htmlspecialchars($currentSettings['primary_color']) ?>; border-radius: 4px; border: 2px solid #dee2e6; cursor: pointer;"
                                                     data-color-target="primary_color"></div>
                                            </span>
                                            <input type="color" 
                                                   class="form-control color-input" 
                                                   id="primary_color" 
                                                   name="colors[primary]" 
                                                   value="<?= htmlspecialchars($currentSettings['primary_color']) ?>"
                                                   style="width: 60px;">
                                            <input type="text" 
                                                   class="form-control color-text" 
                                                   value="<?= htmlspecialchars($currentSettings['primary_color']) ?>"
                                                   data-color-input="primary_color"
                                                   placeholder="#0d6efd">
                                        </div>
                                        <small class="form-text text-muted">
                                            버튼, 링크, 강조 요소의 기본 색상
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Secondary Color -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">보조 색상 (Secondary)</label>
                                    <div class="color-input-group">
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <div class="color-preview" 
                                                     style="width: 35px; height: 35px; background-color: <?= htmlspecialchars($currentSettings['secondary_color']) ?>; border-radius: 4px; border: 2px solid #dee2e6; cursor: pointer;"
                                                     data-color-target="secondary_color"></div>
                                            </span>
                                            <input type="color" 
                                                   class="form-control color-input" 
                                                   id="secondary_color" 
                                                   name="colors[secondary]" 
                                                   value="<?= htmlspecialchars($currentSettings['secondary_color']) ?>"
                                                   style="width: 60px;">
                                            <input type="text" 
                                                   class="form-control color-text" 
                                                   value="<?= htmlspecialchars($currentSettings['secondary_color']) ?>"
                                                   data-color-input="secondary_color"
                                                   placeholder="#6c757d">
                                        </div>
                                        <small class="form-text text-muted">
                                            보조 버튼, 비활성 요소의 색상
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Success Color -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">성공 색상 (Success)</label>
                                    <div class="color-input-group">
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <div class="color-preview" 
                                                     style="width: 35px; height: 35px; background-color: <?= htmlspecialchars($currentSettings['success_color']) ?>; border-radius: 4px; border: 2px solid #dee2e6; cursor: pointer;"
                                                     data-color-target="success_color"></div>
                                            </span>
                                            <input type="color" 
                                                   class="form-control color-input" 
                                                   id="success_color" 
                                                   name="colors[success]" 
                                                   value="<?= htmlspecialchars($currentSettings['success_color']) ?>"
                                                   style="width: 60px;">
                                            <input type="text" 
                                                   class="form-control color-text" 
                                                   value="<?= htmlspecialchars($currentSettings['success_color']) ?>"
                                                   data-color-input="success_color"
                                                   placeholder="#198754">
                                        </div>
                                        <small class="form-text text-muted">
                                            성공 메시지, 완료 상태 색상
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Warning Color -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">경고 색상 (Warning)</label>
                                    <div class="color-input-group">
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <div class="color-preview" 
                                                     style="width: 35px; height: 35px; background-color: <?= htmlspecialchars($currentSettings['warning_color']) ?>; border-radius: 4px; border: 2px solid #dee2e6; cursor: pointer;"
                                                     data-color-target="warning_color"></div>
                                            </span>
                                            <input type="color" 
                                                   class="form-control color-input" 
                                                   id="warning_color" 
                                                   name="colors[warning]" 
                                                   value="<?= htmlspecialchars($currentSettings['warning_color']) ?>"
                                                   style="width: 60px;">
                                            <input type="text" 
                                                   class="form-control color-text" 
                                                   value="<?= htmlspecialchars($currentSettings['warning_color']) ?>"
                                                   data-color-input="warning_color"
                                                   placeholder="#ffc107">
                                        </div>
                                        <small class="form-text text-muted">
                                            경고 메시지, 주의 사항 색상
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Danger Color -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">위험 색상 (Danger)</label>
                                    <div class="color-input-group">
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <div class="color-preview" 
                                                     style="width: 35px; height: 35px; background-color: <?= htmlspecialchars($currentSettings['danger_color']) ?>; border-radius: 4px; border: 2px solid #dee2e6; cursor: pointer;"
                                                     data-color-target="danger_color"></div>
                                            </span>
                                            <input type="color" 
                                                   class="form-control color-input" 
                                                   id="danger_color" 
                                                   name="colors[danger]" 
                                                   value="<?= htmlspecialchars($currentSettings['danger_color']) ?>"
                                                   style="width: 60px;">
                                            <input type="text" 
                                                   class="form-control color-text" 
                                                   value="<?= htmlspecialchars($currentSettings['danger_color']) ?>"
                                                   data-color-input="danger_color"
                                                   placeholder="#dc3545">
                                        </div>
                                        <small class="form-text text-muted">
                                            오류 메시지, 삭제 버튼 색상
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Info Color -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">정보 색상 (Info)</label>
                                    <div class="color-input-group">
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <div class="color-preview" 
                                                     style="width: 35px; height: 35px; background-color: <?= htmlspecialchars($currentSettings['info_color']) ?>; border-radius: 4px; border: 2px solid #dee2e6; cursor: pointer;"
                                                     data-color-target="info_color"></div>
                                            </span>
                                            <input type="color" 
                                                   class="form-control color-input" 
                                                   id="info_color" 
                                                   name="colors[info]" 
                                                   value="<?= htmlspecialchars($currentSettings['info_color']) ?>"
                                                   style="width: 60px;">
                                            <input type="text" 
                                                   class="form-control color-text" 
                                                   value="<?= htmlspecialchars($currentSettings['info_color']) ?>"
                                                   data-color-input="info_color"
                                                   placeholder="#0dcaf0">
                                        </div>
                                        <small class="form-text text-muted">
                                            정보 메시지, 도움말 색상
                                        </small>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 폰트 설정 탭 -->
                        <div class="tab-pane fade <?= $active_tab === 'fonts' ? 'show active' : '' ?>" 
                             id="fonts-tab">
                            <form id="fonts-form">
                                <div class="mb-3">
                                    <label for="body-font" class="form-label fw-bold">본문 폰트</label>
                                    <select class="form-select" id="body-font" name="fonts[body]">
                                        <option value="'Segoe UI', sans-serif" <?= $currentSettings['body_font'] === "'Segoe UI', sans-serif" ? 'selected' : '' ?>>Segoe UI</option>
                                        <option value="'Malgun Gothic', sans-serif" <?= $currentSettings['body_font'] === "'Malgun Gothic', sans-serif" ? 'selected' : '' ?>>맑은 고딕</option>
                                        <option value="'Nanum Gothic', sans-serif" <?= $currentSettings['body_font'] === "'Nanum Gothic', sans-serif" ? 'selected' : '' ?>>나눔 고딕</option>
                                        <option value="'Noto Sans KR', sans-serif" <?= $currentSettings['body_font'] === "'Noto Sans KR', sans-serif" ? 'selected' : '' ?>>Noto Sans KR</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="heading-font" class="form-label fw-bold">제목 폰트</label>
                                    <select class="form-select" id="heading-font" name="fonts[heading]">
                                        <option value="'Segoe UI', sans-serif" <?= $currentSettings['heading_font'] === "'Segoe UI', sans-serif" ? 'selected' : '' ?>>Segoe UI</option>
                                        <option value="'Malgun Gothic', sans-serif" <?= $currentSettings['heading_font'] === "'Malgun Gothic', sans-serif" ? 'selected' : '' ?>>맑은 고딕</option>
                                        <option value="'Nanum Gothic', sans-serif" <?= $currentSettings['heading_font'] === "'Nanum Gothic', sans-serif" ? 'selected' : '' ?>>나눔 고딕</option>
                                        <option value="'Noto Sans KR', sans-serif" <?= $currentSettings['heading_font'] === "'Noto Sans KR', sans-serif" ? 'selected' : '' ?>>Noto Sans KR</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="font-size" class="form-label fw-bold">기본 폰트 크기</label>
                                    <select class="form-select" id="font-size" name="fonts[size]">
                                        <option value="0.875rem" <?= $currentSettings['font_size_base'] === '0.875rem' ? 'selected' : '' ?>>작게 (0.875rem)</option>
                                        <option value="1rem" <?= $currentSettings['font_size_base'] === '1rem' ? 'selected' : '' ?>>보통 (1rem)</option>
                                        <option value="1.125rem" <?= $currentSettings['font_size_base'] === '1.125rem' ? 'selected' : '' ?>>크게 (1.125rem)</option>
                                        <option value="1.25rem" <?= $currentSettings['font_size_base'] === '1.25rem' ? 'selected' : '' ?>>아주 크게 (1.25rem)</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 레이아웃 설정 탭 -->
                        <div class="tab-pane fade <?= $active_tab === 'layout' ? 'show active' : '' ?>" 
                             id="layout-tab">
                            <form id="layout-form">
                                <div class="mb-3">
                                    <label for="container-width" class="form-label fw-bold">컨테이너 너비</label>
                                    <select class="form-select" id="container-width" name="layout[container]">
                                        <option value="standard" <?= ($currentSettings['container_width'] ?? 'standard') === 'standard' ? 'selected' : '' ?>>기본 (1140px)</option>
                                        <option value="fluid" <?= ($currentSettings['container_width'] ?? '') === 'fluid' ? 'selected' : '' ?>>유동적 (100%)</option>
                                        <option value="narrow" <?= ($currentSettings['container_width'] ?? '') === 'narrow' ? 'selected' : '' ?>>좁게 (960px)</option>
                                        <option value="wide" <?= ($currentSettings['container_width'] ?? '') === 'wide' ? 'selected' : '' ?>>넓게 (1320px)</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- 액션 버튼 -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="apply-theme">
                            <i class="bi bi-check-circle"></i> 테마 적용
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="reset-theme">
                            <i class="bi bi-arrow-clockwise"></i> 기본값으로 되돌리기
                        </button>
                        <button type="button" class="btn btn-outline-info" id="preview-frontend">
                            <i class="bi bi-box-arrow-up-right"></i> 프론트엔드 미리보기
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 미리보기 패널 -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye"></i> 실시간 미리보기
                    </h5>
                    <small class="text-muted">색상을 변경하면 즉시 반영됩니다</small>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div id="preview-container">
                        <!-- 미리보기 콘텐츠 -->
                        <div class="preview-content">
                            <!-- 네비게이션 바 미리보기 -->
                            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                                <div class="container-fluid">
                                    <a class="navbar-brand fw-bold" href="#">우리동네노동권찾기</a>
                                    <div class="navbar-nav ms-auto">
                                        <a class="nav-link active" href="#">홈</a>
                                        <a class="nav-link" href="#">서비스</a>
                                        <a class="nav-link" href="#">문의</a>
                                    </div>
                                </div>
                            </nav>
                            
                            <!-- 콘텐츠 미리보기 -->
                            <div class="container">
                                <h2>테마 미리보기</h2>
                                <p class="lead">선택한 테마가 실제로 어떻게 보이는지 확인해보세요.</p>
                                
                                <!-- 버튼 미리보기 -->
                                <div class="mb-4">
                                    <h4>버튼</h4>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <button class="btn btn-primary">Primary</button>
                                        <button class="btn btn-secondary">Secondary</button>
                                        <button class="btn btn-success">Success</button>
                                        <button class="btn btn-warning">Warning</button>
                                        <button class="btn btn-danger">Danger</button>
                                        <button class="btn btn-info">Info</button>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-outline-primary">Outline Primary</button>
                                        <button class="btn btn-outline-success">Outline Success</button>
                                    </div>
                                </div>
                                
                                <!-- 알림 미리보기 -->
                                <div class="mb-4">
                                    <h4>알림</h4>
                                    <div class="alert alert-primary">
                                        <i class="bi bi-info-circle"></i> Primary 알림입니다.
                                    </div>
                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle"></i> 작업이 성공적으로 완료되었습니다.
                                    </div>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> 주의가 필요한 상황입니다.
                                    </div>
                                    <div class="alert alert-danger">
                                        <i class="bi bi-x-circle"></i> 오류가 발생했습니다.
                                    </div>
                                </div>
                                
                                <!-- 링크 미리보기 -->
                                <div class="mb-4">
                                    <h4>링크 및 텍스트</h4>
                                    <p>
                                        이것은 일반 텍스트입니다. <a href="#">이것은 링크입니다</a>. 
                                        <strong>이것은 굵은 텍스트입니다</strong>.
                                    </p>
                                </div>
                                
                                <!-- 폼 미리보기 -->
                                <div class="mb-4">
                                    <h4>폼 요소</h4>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" placeholder="입력 필드 예제">
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" checked>
                                        <label class="form-check-label">체크박스 예제</label>
                                    </div>
                                </div>
                                
                                <!-- 카드 미리보기 -->
                                <div class="mb-4">
                                    <h4>카드</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    카드 헤더
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">카드 제목</h5>
                                                    <p class="card-text">카드 내용입니다.</p>
                                                    <a href="#" class="btn btn-primary">더 보기</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h5 class="card-title text-primary">강조된 카드</h5>
                                                    <p class="card-text">Primary 색상으로 강조된 카드입니다.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 동적 스타일 컨테이너 -->
<style id="dynamic-theme-styles">
/* 동적으로 생성되는 테마 스타일이 여기에 적용됩니다 */
</style>

<?php
$content = ob_get_clean();

// JavaScript 코드 (외부 라이브러리 없이 순수 JavaScript)
$inline_js = '
document.addEventListener("DOMContentLoaded", function() {
    console.log("테마 설정 페이지 로드됨");
    
    // 색상 입력 요소들
    const colorInputs = document.querySelectorAll(".color-input");
    const colorTexts = document.querySelectorAll(".color-text");
    const colorPreviews = document.querySelectorAll(".color-preview");
    
    // 색상 입력 이벤트 리스너
    colorInputs.forEach(function(input) {
        input.addEventListener("change", function() {
            const color = this.value;
            const id = this.id;
            
            // 미리보기 색상 업데이트
            const preview = document.querySelector(`[data-color-target="${id}"]`);
            if (preview) {
                preview.style.backgroundColor = color;
            }
            
            // 텍스트 입력 필드 업데이트
            const textInput = document.querySelector(`[data-color-input="${id}"]`);
            if (textInput) {
                textInput.value = color;
            }
            
            // 실시간 미리보기 업데이트
            updatePreview();
        });
    });
    
    // 색상 텍스트 입력 이벤트
    colorTexts.forEach(function(input) {
        input.addEventListener("input", function() {
            const color = this.value;
            const id = this.dataset.colorInput;
            
            // 색상 형식 검증
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
                // 색상 선택기 업데이트
                const colorInput = document.getElementById(id);
                if (colorInput) {
                    colorInput.value = color;
                }
                
                // 미리보기 업데이트
                const preview = document.querySelector(`[data-color-target="${id}"]`);
                if (preview) {
                    preview.style.backgroundColor = color;
                }
                
                updatePreview();
            }
        });
    });
    
    // 색상 미리보기 클릭 이벤트
    colorPreviews.forEach(function(preview) {
        preview.addEventListener("click", function() {
            const target = this.dataset.colorTarget;
            const colorInput = document.getElementById(target);
            if (colorInput) {
                colorInput.click();
            }
        });
    });
    
    // 폰트 및 레이아웃 변경 이벤트
    document.querySelectorAll("#fonts-form select, #layout-form select").forEach(function(select) {
        select.addEventListener("change", updatePreview);
    });
    
    // 실시간 미리보기 업데이트 함수
    function updatePreview() {
        console.log("미리보기 업데이트 중...");
        
        // FormData 생성
        const formData = new FormData();
        formData.append("csrf_token", "' . ($_SESSION['csrf_token'] ?? '') . '");
        
        // 색상 데이터 수집
        const colorData = {};
        document.querySelectorAll("[name^=\'colors[\']").forEach(function(input) {
            const match = input.name.match(/colors\\[(\\w+)\\]/);
            if (match && input.value) {
                colorData[match[1]] = input.value;
            }
        });
        
        // 폰트 데이터 수집
        const fontData = {};
        document.querySelectorAll("[name^=\'fonts[\']").forEach(function(input) {
            const match = input.name.match(/fonts\\[(\\w+)\\]/);
            if (match && input.value) {
                fontData[match[1]] = input.value;
            }
        });
        
        // 레이아웃 데이터 수집
        const layoutData = {};
        document.querySelectorAll("[name^=\'layout[\']").forEach(function(input) {
            const match = input.name.match(/layout\\[(\\w+)\\]/);
            if (match && input.value) {
                layoutData[match[1]] = input.value;
            }
        });
        
        // 데이터 추가
        if (Object.keys(colorData).length > 0) {
            formData.append("colors", JSON.stringify(colorData));
        }
        if (Object.keys(fontData).length > 0) {
            formData.append("fonts", JSON.stringify(fontData));
        }
        if (Object.keys(layoutData).length > 0) {
            formData.append("layout", JSON.stringify(layoutData));
        }
        
        // AJAX 요청
        fetch("api/theme_preview.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("미리보기 응답:", data);
            if (data.success) {
                // 동적 스타일 적용
                const styleElement = document.getElementById("dynamic-theme-styles");
                if (styleElement) {
                    styleElement.textContent = data.css;
                }
            } else {
                console.error("미리보기 오류:", data.error);
            }
        })
        .catch(error => {
            console.error("AJAX 오류:", error);
        });
    }
    
    // 테마 적용 버튼
    document.getElementById("apply-theme").addEventListener("click", function() {
        const button = this;
        const originalHTML = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>적용 중...\';
        
        // FormData 준비
        const formData = new FormData();
        formData.append("csrf_token", "' . ($_SESSION['csrf_token'] ?? '') . '");
        
        // 모든 데이터 수집
        const colorData = {};
        document.querySelectorAll("[name^=\'colors[\']").forEach(function(input) {
            const match = input.name.match(/colors\\[(\\w+)\\]/);
            if (match && input.value) {
                colorData[match[1]] = input.value;
            }
        });
        
        const fontData = {};
        document.querySelectorAll("[name^=\'fonts[\']").forEach(function(input) {
            const match = input.name.match(/fonts\\[(\\w+)\\]/);
            if (match && input.value) {
                fontData[match[1]] = input.value;
            }
        });
        
        const layoutData = {};
        document.querySelectorAll("[name^=\'layout[\']").forEach(function(input) {
            const match = input.name.match(/layout\\[(\\w+)\\]/);
            if (match && input.value) {
                layoutData[match[1]] = input.value;
            }
        });
        
        if (Object.keys(colorData).length > 0) {
            formData.append("colors", JSON.stringify(colorData));
        }
        if (Object.keys(fontData).length > 0) {
            formData.append("fonts", JSON.stringify(fontData));
        }
        if (Object.keys(layoutData).length > 0) {
            formData.append("layout", JSON.stringify(layoutData));
        }
        
        // 테마 적용 요청
        fetch("api/theme_apply.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalHTML;
            
            if (data.success) {
                showAlert("success", data.message);
            } else {
                showAlert("danger", data.error || "테마 적용 중 오류가 발생했습니다.");
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalHTML;
            console.error("테마 적용 오류:", error);
            showAlert("danger", "네트워크 오류가 발생했습니다.");
        });
    });
    
    // 기본값 되돌리기 버튼
    document.getElementById("reset-theme").addEventListener("click", function() {
        if (confirm("정말로 기본값으로 되돌리시겠습니까?")) {
            const defaults = {
                primary_color: "#0d6efd",
                secondary_color: "#6c757d", 
                success_color: "#198754",
                warning_color: "#ffc107",
                danger_color: "#dc3545",
                info_color: "#0dcaf0"
            };
            
            Object.keys(defaults).forEach(function(key) {
                const colorInput = document.getElementById(key);
                const textInput = document.querySelector(`[data-color-input="${key}"]`);
                const preview = document.querySelector(`[data-color-target="${key}"]`);
                
                if (colorInput) colorInput.value = defaults[key];
                if (textInput) textInput.value = defaults[key];
                if (preview) preview.style.backgroundColor = defaults[key];
            });
            
            updatePreview();
        }
    });
    
    // 프론트엔드 미리보기 버튼
    document.getElementById("preview-frontend").addEventListener("click", function() {
        window.open("/theme_demo_page.php", "_blank");
    });
    
    // 알림 표시 함수
    function showAlert(type, message) {
        const alertContainer = document.getElementById("alert-container");
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="bi bi-${type === "success" ? "check-circle" : "exclamation-triangle"}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHTML;
        
        // 3초 후 자동 숨김
        setTimeout(function() {
            const alert = alertContainer.querySelector(".alert");
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }
    
    // 초기 미리보기 로드
    setTimeout(updatePreview, 500);
});
';

// 레이아웃 렌더링
require_once 'mvc/bootstrap.php';
TemplateHelper::renderLayout('sidebar', compact(
    'page_title', 
    'active_menu', 
    'content', 
    'additional_css', 
    'additional_js', 
    'inline_js'
));
?>