<?php
/**
 * 향상된 테마 설정 페이지
 * 
 * 실시간 미리보기와 색상 선택기가 포함된 현대적인 테마 설정 인터페이스
 */

// 인증 및 MVC 시스템 로드
require_once 'auth.php';
require_once 'mvc/bootstrap.php';
require_once 'mvc/services/ThemeService.php';

// 테마 서비스 초기화
$themeService = new ThemeService($pdo);
$currentSettings = $themeService->getThemeSettings();

// 현재 활성화된 탭
$active_tab = $_GET['tab'] ?? 'colors';

// 페이지 변수 설정
$page_title = '테마 설정';
$active_menu = 'theme_settings';

// CSS/JS 라이브러리
$additional_css = [
    'https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'
];

$additional_js = [
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

// 메인 콘텐츠 시작
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- 설정 패널 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-palette"></i> 테마 설정
                    </h5>
                </div>
                <div class="card-body">
                    <!-- 탭 메뉴 -->
                    <ul class="nav nav-pills nav-fill mb-4" id="theme-tabs">
                        <li class="nav-item">
                            <button class="nav-link <?= $active_tab === 'colors' ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#colors-tab">
                                <i class="bi bi-palette"></i> 색상
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= $active_tab === 'fonts' ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#fonts-tab">
                                <i class="bi bi-fonts"></i> 폰트
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= $active_tab === 'layout' ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#layout-tab">
                                <i class="bi bi-layout-text-window"></i> 레이아웃
                            </button>
                        </li>
                    </ul>
                    
                    <!-- 탭 내용 -->
                    <div class="tab-content" id="theme-content">
                        <!-- 색상 설정 탭 -->
                        <div class="tab-pane fade <?= $active_tab === 'colors' ? 'show active' : '' ?>" 
                             id="colors-tab">
                             
                            <!-- Natural-Green 테마 안내 -->
                            <div class="alert alert-info mb-4" role="alert">
                                <div class="d-flex">
                                    <i class="bi bi-palette-fill me-3 flex-shrink-0" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="alert-heading mb-2">
                                            <i class="bi bi-leaf-fill text-success me-1"></i>
                                            Natural-Green 테마 통합 시스템
                                        </h6>
                                        <p class="mb-2">
                                            이 8개 색상은 <strong>Natural-Green 테마</strong>와 연동됩니다. 
                                            각 색상을 변경하면 웹사이트 전체에 즉시 반영됩니다.
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <strong>팁:</strong> Forest(숲), Lime(라임), Natural(자연) 색조를 유지하면 일관된 디자인을 만들 수 있습니다.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <form id="colors-form">
                                <!-- Primary Color - Forest-500 (메인 브랜드) -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-star-fill text-primary me-1"></i>
                                        <strong>메인 브랜드 색상</strong> <span class="badge bg-secondary">Primary · Forest-500</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="primary"
                                             style="background-color: <?= htmlspecialchars($currentSettings['primary_color']) ?>"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['primary_color']) ?>"
                                                 data-name="primary"></div>
                                            <input type="hidden" name="colors[primary]" 
                                                   value="<?= htmlspecialchars($currentSettings['primary_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        네비게이션 메뉴 활성화, 메인 제목 텍스트, 로고 색상, 
                                        "더 보기" 링크, 주요 버튼 배경
                                    </small>
                                </div>
                                
                                <!-- Secondary Color - Green-600 (보조 액션) -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-arrow-right-circle-fill text-success me-1"></i>
                                        <strong>보조 액션 색상</strong> <span class="badge bg-success">Secondary · Green-600</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="secondary"
                                             style="background-color: <?= htmlspecialchars($currentSettings['secondary_color']) ?>"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['secondary_color']) ?>"
                                                 data-name="secondary"></div>
                                            <input type="hidden" name="colors[secondary]" 
                                                   value="<?= htmlspecialchars($currentSettings['secondary_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        호버 시 링크 색상, 보조 버튼, 아이콘 강조, 
                                        서브 네비게이션, 카테고리 태그
                                    </small>
                                </div>
                                
                                <!-- Success Color - Lime-600 (성공/확인) -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        <strong>성공/확인 색상</strong> <span class="badge bg-success">Success · Lime-600</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="success"
                                             style="background-color: <?= htmlspecialchars($currentSettings['success_color']) ?>"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['success_color']) ?>"
                                                 data-name="success"></div>
                                            <input type="hidden" name="colors[success]" 
                                                   value="<?= htmlspecialchars($currentSettings['success_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        성공 알림창, "저장됨" 메시지, 완료 체크박스, 
                                        진행률 바, 성공 버튼
                                    </small>
                                </div>
                                
                                <!-- Warning Color - Lime-400 (경고/주의) -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                        <strong>경고/주의 색상</strong> <span class="badge bg-warning text-dark">Warning · Lime-400</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="warning"
                                             style="background-color: <?= htmlspecialchars($currentSettings['warning_color']) ?>"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['warning_color']) ?>"
                                                 data-name="warning"></div>
                                            <input type="hidden" name="colors[warning]" 
                                                   value="<?= htmlspecialchars($currentSettings['warning_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        "변경사항 있음" 알림, 확인 필요 메시지, 
                                        주의 배지, 임시저장 상태 표시
                                    </small>
                                </div>
                                
                                <!-- Danger Color - Forest-600 (위험/오류) -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                        <strong>위험/오류 색상</strong> <span class="badge bg-danger">Danger · Forest-600</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="danger"
                                             style="background-color: <?= htmlspecialchars($currentSettings['danger_color']) ?>"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['danger_color']) ?>"
                                                 data-name="danger"></div>
                                            <input type="hidden" name="colors[danger]" 
                                                   value="<?= htmlspecialchars($currentSettings['danger_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        에러 메시지창, "삭제" 버튼, 필수 입력 오류, 
                                        로그인 실패 알림, 취소 버튼
                                    </small>
                                </div>
                                
                                <!-- Info Color - Forest-500 (정보 표시) -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-info-circle-fill text-info me-1"></i>
                                        <strong>정보 표시 색상</strong> <span class="badge bg-info text-dark">Info · Forest-500</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="info"
                                             style="background-color: <?= htmlspecialchars($currentSettings['info_color']) ?>"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['info_color']) ?>"
                                                 data-name="info"></div>
                                            <input type="hidden" name="colors[info]" 
                                                   value="<?= htmlspecialchars($currentSettings['info_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        도움말 텍스트, 안내 메시지, 툴팁 배경, 
                                        "알아두세요" 박스, 정보 아이콘
                                    </small>
                                </div>
                                
                                <!-- Light/Dark 색상 추가 -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-sun-fill text-warning me-1"></i>
                                        <strong>밝은 배경 색상</strong> <span class="badge bg-light text-dark">Light · Natural-50</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="light"
                                             style="background-color: <?= htmlspecialchars($currentSettings['light_color']) ?>; border: 1px solid #ddd;"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['light_color']) ?>"
                                                 data-name="light"></div>
                                            <input type="hidden" name="colors[light]" 
                                                   value="<?= htmlspecialchars($currentSettings['light_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        게시글 카드 배경, 섹션 구분선, 사이드바 배경, 
                                        입력 폼 배경, 테이블 헤더
                                    </small>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-moon-fill text-dark me-1"></i>
                                        <strong>어두운 텍스트 색상</strong> <span class="badge bg-dark">Dark · Forest-700</span>
                                    </label>
                                    <div class="color-picker-container d-flex align-items-center">
                                        <div class="color-preview-box me-3" 
                                             data-name="dark"
                                             style="background-color: <?= htmlspecialchars($currentSettings['dark_color']) ?>"></div>
                                        <div class="flex-grow-1">
                                            <div class="color-picker" 
                                                 data-color="<?= htmlspecialchars($currentSettings['dark_color']) ?>"
                                                 data-name="dark"></div>
                                            <input type="hidden" name="colors[dark]" 
                                                   value="<?= htmlspecialchars($currentSettings['dark_color']) ?>">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-globe me-1"></i>
                                        <strong>실제 적용 예시:</strong> 
                                        메인 제목 텍스트, 본문 강조 글씨, 푸터 배경, 
                                        어두운 버튼, 강조 박스 텍스트
                                    </small>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 폰트 설정 탭 -->
                        <div class="tab-pane fade <?= $active_tab === 'fonts' ? 'show active' : '' ?>" 
                             id="fonts-tab">
                            <form id="fonts-form">
                                <div class="mb-3">
                                    <label for="body-font" class="form-label">본문 폰트</label>
                                    <select class="form-select" id="body-font" name="fonts[body]">
                                        <option value="'Segoe UI', sans-serif" <?= $currentSettings['body_font'] === "'Segoe UI', sans-serif" ? 'selected' : '' ?>>Segoe UI</option>
                                        <option value="'Malgun Gothic', sans-serif" <?= $currentSettings['body_font'] === "'Malgun Gothic', sans-serif" ? 'selected' : '' ?>>맑은 고딕</option>
                                        <option value="'Nanum Gothic', sans-serif" <?= $currentSettings['body_font'] === "'Nanum Gothic', sans-serif" ? 'selected' : '' ?>>나눔 고딕</option>
                                        <option value="'Noto Sans KR', sans-serif" <?= $currentSettings['body_font'] === "'Noto Sans KR', sans-serif" ? 'selected' : '' ?>>Noto Sans KR</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="heading-font" class="form-label">제목 폰트</label>
                                    <select class="form-select" id="heading-font" name="fonts[heading]">
                                        <option value="'Segoe UI', sans-serif" <?= $currentSettings['heading_font'] === "'Segoe UI', sans-serif" ? 'selected' : '' ?>>Segoe UI</option>
                                        <option value="'Malgun Gothic', sans-serif" <?= $currentSettings['heading_font'] === "'Malgun Gothic', sans-serif" ? 'selected' : '' ?>>맑은 고딕</option>
                                        <option value="'Nanum Gothic', sans-serif" <?= $currentSettings['heading_font'] === "'Nanum Gothic', sans-serif" ? 'selected' : '' ?>>나눔 고딕</option>
                                        <option value="'Noto Sans KR', sans-serif" <?= $currentSettings['heading_font'] === "'Noto Sans KR', sans-serif" ? 'selected' : '' ?>>Noto Sans KR</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="font-size" class="form-label">기본 폰트 크기</label>
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
                                    <label for="container-width" class="form-label">컨테이너 너비</label>
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
                        <button type="button" class="btn btn-primary" id="apply-theme">
                            <i class="bi bi-check-circle"></i> 테마 적용
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="reset-theme">
                            <i class="bi bi-arrow-clockwise"></i> 기본값으로 되돌리기
                        </button>
                    </div>
                    
                    <!-- 테마 프리셋 관리 섹션 -->
                    <hr class="my-4">
                    <div class="theme-presets-section">
                        <h6 class="mb-3">
                            <i class="bi bi-collection"></i> 테마 관리
                        </h6>
                        
                        <!-- 현재 테마 저장 -->
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">현재 색상을 새 테마로 저장</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-success btn-sm" id="save-current-theme">
                                    <i class="bi bi-plus-circle"></i> 현재 테마 저장
                                </button>
                            </div>
                        </div>
                        
                        <!-- 저장된 테마 불러오기 -->
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">저장된 테마 불러오기</label>
                            <div class="input-group">
                                <select class="form-select form-select-sm" id="preset-selector">
                                    <option value="">테마를 선택해주세요...</option>
                                </select>
                                <button class="btn btn-outline-primary btn-sm" type="button" id="load-preset">
                                    <i class="bi bi-download"></i> 불러오기
                                </button>
                            </div>
                        </div>
                        
                        <!-- 테마 관리 -->
                        <div class="d-grid gap-1">
                            <button type="button" class="btn btn-outline-info btn-sm" id="manage-presets" data-bs-toggle="collapse" data-bs-target="#preset-management">
                                <i class="bi bi-gear"></i> 테마 관리
                            </button>
                        </div>
                        
                        <!-- 테마 관리 패널 (접기/펼치기) -->
                        <div class="collapse mt-3" id="preset-management">
                            <div class="card card-body bg-light border-0">
                                <div class="d-grid gap-1" id="preset-management-buttons">
                                    <!-- 동적으로 생성될 관리 버튼들 -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 미리보기 패널 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye"></i> 실시간 미리보기
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" id="preview-frontend">
                            <i class="bi bi-box-arrow-up-right"></i> 프론트엔드 보기
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="preview-container">
                        <!-- 미리보기 콘텐츠 -->
                        <div class="preview-content">
                            <!-- 네비게이션 바 미리보기 -->
                            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                                <div class="container-fluid">
                                    <a class="navbar-brand" href="#">우리동네노동권찾기</a>
                                    <div class="navbar-nav ms-auto">
                                        <a class="nav-link active" href="#">홈</a>
                                        <a class="nav-link" href="#">서비스</a>
                                        <a class="nav-link" href="#">문의</a>
                                    </div>
                                </div>
                            </nav>
                            
                            <!-- 콘텐츠 미리보기 -->
                            <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <h1>테마 미리보기</h1>
                                        <p class="lead">선택한 테마가 실제로 어떻게 보이는지 확인해보세요.</p>
                                        
                                        <!-- 버튼 미리보기 -->
                                        <div class="mb-4">
                                            <h3>버튼</h3>
                                            <button class="btn btn-primary me-2">Primary</button>
                                            <button class="btn btn-secondary me-2">Secondary</button>
                                            <button class="btn btn-success me-2">Success</button>
                                            <button class="btn btn-warning me-2">Warning</button>
                                            <button class="btn btn-danger me-2">Danger</button>
                                            <button class="btn btn-info">Info</button>
                                        </div>
                                        
                                        <!-- 알림 미리보기 -->
                                        <div class="mb-4">
                                            <h3>알림</h3>
                                            <div class="alert alert-primary">Primary 알림 메시지입니다.</div>
                                            <div class="alert alert-success">Success 알림 메시지입니다.</div>
                                            <div class="alert alert-warning">Warning 알림 메시지입니다.</div>
                                            <div class="alert alert-danger">Danger 알림 메시지입니다.</div>
                                        </div>
                                        
                                        <!-- 카드 미리보기 -->
                                        <div class="mb-4">
                                            <h3>카드</h3>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            카드 제목
                                                        </div>
                                                        <div class="card-body">
                                                            <h5 class="card-title">특별한 제목 처리</h5>
                                                            <p class="card-text">카드 내용입니다. 이 부분에서 폰트와 색상이 어떻게 적용되는지 확인할 수 있습니다.</p>
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
                                        
                                        <!-- 폼 미리보기 -->
                                        <div class="mb-4">
                                            <h3>폼</h3>
                                            <form>
                                                <div class="mb-3">
                                                    <label for="example-input" class="form-label">예제 입력</label>
                                                    <input type="text" class="form-control" id="example-input" placeholder="입력해보세요">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="example-select" class="form-label">선택 옵션</label>
                                                    <select class="form-select" id="example-select">
                                                        <option>옵션 1</option>
                                                        <option>옵션 2</option>
                                                        <option>옵션 3</option>
                                                    </select>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="example-check">
                                                    <label class="form-check-label" for="example-check">
                                                        확인란 예제
                                                    </label>
                                                </div>
                                            </form>
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

<!-- 테마 저장 Modal -->
<div class="modal fade" id="saveThemeModal" tabindex="-1" aria-labelledby="saveThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveThemeModalLabel">
                    <i class="bi bi-plus-circle"></i> 새 테마 저장
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="save-theme-form">
                    <div class="mb-3">
                        <label for="theme-name" class="form-label">테마 이름 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="theme-name" name="name" required maxlength="100" 
                               placeholder="예: 내 맞춤 테마">
                        <div class="form-text">영문, 한글, 숫자, 공백 사용 가능 (최대 100자)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="theme-description" class="form-label">테마 설명 (선택사항)</label>
                        <textarea class="form-control" id="theme-description" name="description" rows="3" maxlength="255"
                                  placeholder="이 테마에 대한 간단한 설명을 입력하세요"></textarea>
                        <div class="form-text">최대 255자</div>
                    </div>
                    
                    <!-- 현재 색상 미리보기 -->
                    <div class="mb-3">
                        <label class="form-label">현재 설정된 색상</label>
                        <div class="row g-2" id="color-preview-grid">
                            <!-- JavaScript로 동적 생성 -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-success" id="confirm-save-theme">
                    <i class="bi bi-check-circle"></i> 저장
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 테마 삭제 확인 Modal -->
<div class="modal fade" id="deleteThemeModal" tabindex="-1" aria-labelledby="deleteThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteThemeModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning"></i> 테마 삭제 확인
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>정말로 <strong id="delete-theme-name"></strong> 테마를 삭제하시겠습니까?</p>
                <p class="text-muted small">이 작업은 되돌릴 수 없습니다.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-theme">
                    <i class="bi bi-trash"></i> 삭제
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 동적 스타일 컨테이너 -->
<style id="dynamic-theme-styles">
/* 동적으로 생성되는 테마 스타일이 여기에 적용됩니다 */
</style>

<style>
/* 색상 미리보기 박스 스타일 */
.color-preview-box {
    width: 40px;
    height: 40px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    transition: border-color 0.2s ease;
    flex-shrink: 0;
}

.color-preview-box:hover {
    border-color: #0d6efd;
}

.color-picker-container {
    min-height: 50px;
}

/* Pickr 버튼 스타일 조정 */
.color-picker .pcr-button {
    width: 100%;
    height: 40px;
    border-radius: 8px;
}
</style>

<?php
$content = ob_get_clean();

// JavaScript 코드
$inline_js = '
document.addEventListener("DOMContentLoaded", function() {
    // 색상 선택기 초기화
    const colorPickers = {};
    
    document.querySelectorAll(".color-picker").forEach(function(element) {
        const colorName = element.dataset.name;
        const initialColor = element.dataset.color;
        
        const pickr = Pickr.create({
            el: element,
            theme: "classic",
            default: initialColor,
            components: {
                preview: true,
                hue: true,
                interaction: {
                    hex: true,
                    rgba: true,
                    input: true,
                    save: true
                }
            }
        });
        
        // 실시간 색상 변경 이벤트 (색상을 선택하는 즉시 반영)
        pickr.on("change", function(color) {
            const hexColor = color.toHEXA().toString();
            const input = document.querySelector(`input[name="colors[${colorName}]"]`);
            if (input) {
                input.value = hexColor;
            }
            
            // 미리보기 박스 색상 즉시 업데이트
            const previewBox = document.querySelector(`[data-name="${colorName}"].color-preview-box`);
            if (previewBox) {
                previewBox.style.backgroundColor = hexColor;
            }
            
            // 변경사항 표시
            markAsChanged();
            
            // 실시간 미리보기 업데이트
            updatePreview();
        });
        
        // 저장 버튼 클릭 시 (기존 기능 유지)
        pickr.on("save", function(color) {
            const hexColor = color.toHEXA().toString();
            const input = document.querySelector(`input[name="colors[${colorName}]"]`);
            if (input) {
                input.value = hexColor;
            }
            
            // 미리보기 박스 색상 업데이트
            const previewBox = document.querySelector(`[data-name="${colorName}"].color-preview-box`);
            if (previewBox) {
                previewBox.style.backgroundColor = hexColor;
            }
            
            // 변경사항 표시
            markAsChanged();
            
            // 실시간 미리보기 업데이트
            updatePreview();
            pickr.hide();
        });
        
        // 미리보기 박스 클릭 시 컬러픽커 열기
        const previewBox = document.querySelector(`[data-name="${colorName}"].color-preview-box`);
        if (previewBox) {
            previewBox.addEventListener("click", function() {
                pickr.show();
            });
        }
        
        colorPickers[colorName] = pickr;
    });
    
    // 폼 변경 이벤트 리스너
    document.querySelectorAll("#fonts-form select, #layout-form select").forEach(function(element) {
        element.addEventListener("change", function() {
            markAsChanged();
            updatePreview();
        });
    });
    
    // 저장 버튼 상태 관리
    let hasUnsavedChanges = false;
    const saveButton = document.getElementById("apply-theme");
    
    // 초기에는 저장 버튼 비활성화
    saveButton.disabled = true;
    saveButton.innerHTML = '<i class="bi bi-check-circle"></i> 테마 적용 (변경사항 없음)';
    
    // 변경사항 감지 함수
    function markAsChanged() {
        if (!hasUnsavedChanges) {
            hasUnsavedChanges = true;
            saveButton.disabled = false;
            saveButton.innerHTML = '<i class="bi bi-check-circle"></i> 테마 적용';
            saveButton.classList.add('btn-warning');
            saveButton.classList.remove('btn-primary');
        }
    }
    
    // 변경사항 저장 완료 함수
    function markAsSaved() {
        hasUnsavedChanges = false;
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="bi bi-check-circle"></i> 테마 적용 (저장됨)';
        saveButton.classList.remove('btn-warning');
        saveButton.classList.add('btn-primary');
    }

    // 실시간 미리보기 업데이트
    function updatePreview() {
        const formData = new FormData();
        
        // CSRF 토큰 추가
        formData.append("csrf_token", "' . ($_SESSION['csrf_token'] ?? '') . '");
        
        // 색상 데이터 수집
        const colorData = {};
        document.querySelectorAll("[name^=\'colors[\']").forEach(function(input) {
            const match = input.name.match(/colors\[(\w+)\]/);
            if (match) {
                colorData[match[1]] = input.value;
            }
        });
        formData.append("colors", JSON.stringify(colorData));
        
        // 폰트 데이터 수집
        const fontData = {};
        document.querySelectorAll("[name^=\'fonts[\']").forEach(function(input) {
            const match = input.name.match(/fonts\[(\w+)\]/);
            if (match) {
                fontData[match[1]] = input.value;
            }
        });
        formData.append("fonts", JSON.stringify(fontData));
        
        // AJAX 요청
        fetch("api/theme_preview.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 동적 스타일 적용
                const styleElement = document.getElementById("dynamic-theme-styles");
                styleElement.textContent = data.css;
            }
        })
        .catch(error => {
            console.error("미리보기 업데이트 오류:", error);
        });
    }
    
    // 테마 적용 버튼
    document.getElementById("apply-theme").addEventListener("click", function() {
        const button = this;
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>적용 중...\';
        
        const formData = new FormData();
        
        // CSRF 토큰 추가
        formData.append("csrf_token", "' . ($_SESSION['csrf_token'] ?? '') . '");
        
        // 모든 폼 데이터 수집
        const colorData = {};
        document.querySelectorAll("[name^=\'colors[\']").forEach(function(input) {
            const match = input.name.match(/colors\[(\w+)\]/);
            if (match) {
                colorData[match[1]] = input.value;
            }
        });
        formData.append("colors", JSON.stringify(colorData));
        
        const fontData = {};
        document.querySelectorAll("[name^=\'fonts[\']").forEach(function(input) {
            const match = input.name.match(/fonts\[(\w+)\]/);
            if (match) {
                fontData[match[1]] = input.value;
            }
        });
        formData.append("fonts", JSON.stringify(fontData));
        
        const layoutData = {};
        document.querySelectorAll("[name^=\'layout[\']").forEach(function(input) {
            const match = input.name.match(/layout\[(\w+)\]/);
            if (match) {
                layoutData[match[1]] = input.value;
            }
        });
        formData.append("layout", JSON.stringify(layoutData));
        
        // AJAX 요청
        fetch("api/theme_apply.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalText;
            
            if (data.success) {
                // 성공 메시지 표시
                const alert = document.createElement("div");
                alert.className = "alert alert-success alert-dismissible fade show mt-3";
                alert.innerHTML = `
                    <i class="bi bi-check-circle"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.querySelector(".card-body").insertBefore(alert, document.querySelector(".d-grid"));
                
                // 저장 완료 상태로 변경
                markAsSaved();
                
                // 페이지 새로고침 (선택사항)
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // 오류 메시지 표시
                const alert = document.createElement("div");
                alert.className = "alert alert-danger alert-dismissible fade show mt-3";
                alert.innerHTML = `
                    <i class="bi bi-exclamation-triangle"></i> ${data.error}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.querySelector(".card-body").insertBefore(alert, document.querySelector(".d-grid"));
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalText;
            console.error("테마 적용 오류:", error);
        });
    });
    
    // 프론트엔드 미리보기 버튼
    document.getElementById("preview-frontend").addEventListener("click", function() {
        window.open("/", "_blank");
    });
    
    // 기본값 되돌리기 버튼
    document.getElementById("reset-theme").addEventListener("click", function() {
        if (confirm("정말로 기본값으로 되돌리시겠습니까?")) {
            // 기본 색상값으로 재설정
            const defaults = {
                primary: "#0d6efd",
                secondary: "#6c757d",
                success: "#198754",
                info: "#0dcaf0", 
                warning: "#ffc107",
                danger: "#dc3545"
            };
            
            Object.keys(defaults).forEach(colorName => {
                const input = document.querySelector(`input[name="colors[${colorName}]"]`);
                if (input) {
                    input.value = defaults[colorName];
                }
                
                // 미리보기 박스 색상 업데이트
                const previewBox = document.querySelector(`[data-name="${colorName}"].color-preview-box`);
                if (previewBox) {
                    previewBox.style.backgroundColor = defaults[colorName];
                }
                
                if (colorPickers[colorName]) {
                    colorPickers[colorName].setColor(defaults[colorName]);
                }
            });
            
            updatePreview();
        }
    });
    
    // 초기 미리보기 로드
    updatePreview();
    
    // =========== 테마 프리셋 관리 기능 ===========
    
    // 전역 변수
    let currentPresets = [];
    let currentDeletePresetId = null;
    
    // 테마 프리셋 목록 로드
    function loadThemePresets() {
        fetch("api/theme_presets.php?action=list", {
            method: "GET",
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentPresets = data.data;
                updatePresetSelector();
                updatePresetManagementButtons();
            } else {
                console.error("테마 프리셋 로드 실패:", data.error);
            }
        })
        .catch(error => {
            console.error("테마 프리셋 로드 오류:", error);
        });
    }
    
    // 프리셋 선택기 업데이트
    function updatePresetSelector() {
        const selector = document.getElementById("preset-selector");
        selector.innerHTML = '<option value="">테마를 선택해주세요...</option>';
        
        currentPresets.forEach(function(preset) {
            const option = document.createElement("option");
            option.value = preset.id;
            option.textContent = preset.preset_name + (preset.preset_description ? " - " + preset.preset_description : "");
            option.setAttribute("data-colors", JSON.stringify(preset.colors));
            selector.appendChild(option);
        });
    }
    
    // 프리셋 관리 버튼 업데이트
    function updatePresetManagementButtons() {
        const container = document.getElementById("preset-management-buttons");
        container.innerHTML = "";
        
        if (currentPresets.length === 0) {
            container.innerHTML = '<p class="text-muted small text-center mb-0">저장된 테마가 없습니다.</p>';
            return;
        }
        
        currentPresets.forEach(function(preset) {
            const button = document.createElement("button");
            button.className = "btn btn-outline-secondary btn-sm d-flex justify-content-between align-items-center";
            button.innerHTML = `
                <span>
                    <i class="bi bi-palette"></i> ${preset.preset_name}
                    ${preset.created_by === 'system' ? '<small class="badge bg-info ms-1">기본</small>' : ''}
                </span>
                ${preset.created_by !== 'system' ? '<i class="bi bi-trash text-danger"></i>' : ''}
            `;
            
            // 삭제 버튼 클릭 이벤트
            if (preset.created_by !== 'system') {
                button.addEventListener("click", function() {
                    currentDeletePresetId = preset.id;
                    document.getElementById("delete-theme-name").textContent = preset.preset_name;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteThemeModal'));
                    deleteModal.show();
                });
            } else {
                button.addEventListener("click", function() {
                    showAlert("시스템 기본 테마는 삭제할 수 없습니다.", "info");
                });
            }
            
            container.appendChild(button);
        });
    }
    
    // 현재 테마 저장 버튼
    document.getElementById("save-current-theme").addEventListener("click", function() {
        // 현재 색상 데이터 수집
        const colorData = {};
        document.querySelectorAll("[name^=\'colors[\']").forEach(function(input) {
            const match = input.name.match(/colors\[(\w+)\]/);
            if (match) {
                colorData[match[1]] = input.value;
            }
        });
        
        // 색상 미리보기 생성
        updateColorPreviewGrid(colorData);
        
        // Modal 표시
        const modal = new bootstrap.Modal(document.getElementById('saveThemeModal'));
        modal.show();
    });
    
    // 색상 미리보기 그리드 업데이트
    function updateColorPreviewGrid(colors) {
        const grid = document.getElementById("color-preview-grid");
        grid.innerHTML = "";
        
        const colorNames = {
            primary: "Primary",
            secondary: "Secondary", 
            success: "Success",
            info: "Info",
            warning: "Warning",
            danger: "Danger",
            light: "Light",
            dark: "Dark"
        };
        
        Object.keys(colorNames).forEach(function(colorType) {
            if (colors[colorType]) {
                const col = document.createElement("div");
                col.className = "col-3";
                col.innerHTML = `
                    <div class="text-center">
                        <div class="color-preview-mini mb-1" style="background-color: ${colors[colorType]}; width: 30px; height: 30px; border-radius: 4px; margin: 0 auto; border: 1px solid #dee2e6;"></div>
                        <small class="text-muted">${colorNames[colorType]}</small>
                    </div>
                `;
                grid.appendChild(col);
            }
        });
    }
    
    // 테마 저장 확인 버튼
    document.getElementById("confirm-save-theme").addEventListener("click", function() {
        const form = document.getElementById("save-theme-form");
        const formData = new FormData(form);
        
        // 현재 색상 데이터 추가
        const colorData = {};
        document.querySelectorAll("[name^=\'colors[\']").forEach(function(input) {
            const match = input.name.match(/colors\[(\w+)\]/);
            if (match) {
                colorData[match[1]] = input.value;
            }
        });
        
        formData.append("action", "save_current");
        formData.append("csrf_token", "' . ($_SESSION['csrf_token'] ?? '') . '");
        formData.append("colors", JSON.stringify(colorData));
        
        const button = this;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
        
        fetch("api/theme_presets.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalText;
            
            if (data.success) {
                // Modal 닫기
                const modal = bootstrap.Modal.getInstance(document.getElementById('saveThemeModal'));
                modal.hide();
                
                // 폼 리셋
                form.reset();
                
                // 성공 메시지
                showAlert(data.message, "success");
                
                // 프리셋 목록 새로고침
                loadThemePresets();
            } else {
                showAlert(data.error, "danger");
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalText;
            console.error("테마 저장 오류:", error);
            showAlert("테마 저장 중 오류가 발생했습니다.", "danger");
        });
    });
    
    // 프리셋 불러오기 버튼
    document.getElementById("load-preset").addEventListener("click", function() {
        const selector = document.getElementById("preset-selector");
        const presetId = selector.value;
        
        if (!presetId) {
            showAlert("테마를 선택해주세요.", "warning");
            return;
        }
        
        const button = this;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>불러오는 중...';
        
        const formData = new FormData();
        formData.append("action", "load");
        formData.append("id", presetId);
        formData.append("csrf_token", "' . ($_SESSION['csrf_token'] ?? '') . '");
        
        fetch("api/theme_presets.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalText;
            
            if (data.success) {
                // 색상 값들을 UI에 적용
                Object.keys(data.colors).forEach(function(colorType) {
                    const input = document.querySelector(`input[name="colors[${colorType}]"]`);
                    if (input) {
                        input.value = data.colors[colorType];
                    }
                    
                    // 미리보기 박스 색상 업데이트
                    const previewBox = document.querySelector(`[data-name="${colorType}"].color-preview-box`);
                    if (previewBox) {
                        previewBox.style.backgroundColor = data.colors[colorType];
                    }
                    
                    // 컬러픽커 업데이트
                    if (colorPickers[colorType]) {
                        colorPickers[colorType].setColor(data.colors[colorType]);
                    }
                });
                
                // 변경사항 표시
                markAsChanged();
                
                // 미리보기 업데이트
                updatePreview();
                
                // 성공 메시지
                showAlert(`"${data.preset_name}" 테마가 적용되었습니다.`, "success");
            } else {
                showAlert(data.error, "danger");
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalText;
            console.error("테마 불러오기 오류:", error);
            showAlert("테마 불러오기 중 오류가 발생했습니다.", "danger");
        });
    });
    
    // 테마 삭제 확인 버튼
    document.getElementById("confirm-delete-theme").addEventListener("click", function() {
        if (!currentDeletePresetId) return;
        
        const button = this;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>삭제 중...';
        
        fetch(`api/theme_presets.php?action=delete&id=${currentDeletePresetId}`, {
            method: "DELETE"
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalText;
            
            if (data.success) {
                // Modal 닫기
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteThemeModal'));
                modal.hide();
                
                // 성공 메시지
                showAlert(data.message, "success");
                
                // 프리셋 목록 새로고침
                loadThemePresets();
                
                currentDeletePresetId = null;
            } else {
                showAlert(data.error, "danger");
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalText;
            console.error("테마 삭제 오류:", error);
            showAlert("테마 삭제 중 오류가 발생했습니다.", "danger");
        });
    });
    
    // 알림 메시지 표시 함수
    function showAlert(message, type = "info") {
        const alertContainer = document.querySelector(".card-body");
        const alert = document.createElement("div");
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i> 
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // 기존 알림 제거
        const existingAlerts = alertContainer.querySelectorAll(".alert");
        existingAlerts.forEach(function(existingAlert) {
            existingAlert.remove();
        });
        
        // 새 알림 추가
        alertContainer.insertBefore(alert, alertContainer.firstChild);
        
        // 3초 후 자동 제거
        setTimeout(function() {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }
    
    // 초기 프리셋 목록 로드
    loadThemePresets();
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