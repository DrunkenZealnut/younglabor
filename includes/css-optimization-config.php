<?php
/**
 * CSS 최적화 시스템 설정 및 활성화 관리
 * 
 * 이 파일은 기존 시스템과 새로운 단순 최적화 시스템을 쉽게 전환할 수 있게 합니다.
 * 
 * 사용법:
 * 1. 이 파일을 includes/header.php 또는 템플릿 상단에서 require_once로 로드
 * 2. CSS_OPTIMIZATION_MODE 상수를 설정하여 모드 선택
 * 3. renderCSS() 함수를 호출하여 CSS 렌더링
 */

// CSS 최적화 모드 설정
if (!defined('CSS_OPTIMIZATION_MODE')) {
    /**
     * CSS 최적화 모드 옵션:
     * - 'legacy': 기존 시스템 사용 (외부 CDN 의존)
     * - 'simple': 새로운 단순 최적화 시스템 (권장)
     * - 'auto': 자동 감지 (사용자 환경에 따라)
     */
    define('CSS_OPTIMIZATION_MODE', 'simple');
}

// 디버깅 모드 (개발 환경에서만 활성화)
if (!defined('CSS_DEBUG')) {
    define('CSS_DEBUG', false);  // 운영 환경에서는 false로 설정
}

/**
 * CSS 렌더링 통합 함수
 * 
 * @param string $pageTitle 페이지 제목
 * @param string $pageDescription 페이지 설명
 * @param string $pageType 페이지 타입 (home, gallery, newsletter 등)
 */
function renderOptimizedCSS($pageTitle = '', $pageDescription = '', $pageType = 'home') {
    $mode = CSS_OPTIMIZATION_MODE;
    
    // Auto 모드인 경우 환경에 따라 자동 선택
    if ($mode === 'auto') {
        $mode = detectOptimalMode();
    }
    
    switch ($mode) {
        case 'simple':
            renderSimpleOptimizedCSS($pageTitle, $pageDescription, $pageType);
            break;
            
        case 'legacy':
        default:
            renderLegacyCSS($pageTitle, $pageDescription, $pageType);
            break;
    }
}

/**
 * 단순 최적화 시스템 렌더링
 */
function renderSimpleOptimizedCSS($pageTitle, $pageDescription, $pageType) {
    // 단순 최적화 시스템 활성화
    define('SIMPLE_CSS_ENABLED', true);
    
    // 필요한 클래스 로드
    require_once __DIR__ . '/SimpleCSSOptimizer.php';
    require_once __DIR__ . '/SimpleHeader.php';
    
    // 헤더 렌더링
    $simpleHeader = new SimpleHeader($pageType);
    $simpleHeader->render($pageTitle, $pageDescription);
    
    if (CSS_DEBUG) {
        echo "<!-- Simple CSS Optimization System Active -->\n";
        $optimizer = getSimpleCSSOptimizer();
        $debugInfo = $optimizer->getDebugInfo();
        echo "<!-- Debug Info: " . json_encode($debugInfo) . " -->\n";
    }
}

/**
 * 기존 시스템 렌더링
 */
function renderLegacyCSS($pageTitle, $pageDescription, $pageType) {
    // 기존 시스템 로드
    require_once __DIR__ . '/NaturalGreenThemeLoader.php';
    
    ?>
    <!DOCTYPE html>
    <html lang="ko">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?= htmlspecialchars($pageTitle ?: '희망연대노동조합') ?></title>
        <meta name="description" content="<?= htmlspecialchars($pageDescription ?: '노동자의 권익을 위한 희망연대노동조합') ?>" />
        
        <!-- 기존 외부 CSS -->
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        
        <?php renderNaturalGreenTheme(); ?>
        
        <?php if (CSS_DEBUG): ?>
        <!-- Debug Info -->
        <script>window.LEGACY_CSS_DEBUG = true;</script>
        <?php endif; ?>
      </head>
      <body>
    <?php
    
    if (CSS_DEBUG) {
        echo "<!-- Legacy CSS System Active -->\n";
    }
}

/**
 * 최적 모드 자동 감지
 */
function detectOptimalMode() {
    // 네트워크 속도나 사용자 환경에 따라 최적 모드 결정
    // 현재는 단순하게 단순 최적화를 기본으로 반환
    return 'simple';
    
    // 향후 확장 가능:
    // - 사용자 에이전트 검사
    // - 네트워크 상태 확인
    // - 서버 부하 상태 확인
    // - 사용자 설정 확인
}

/**
 * 성능 비교 리포트 생성
 */
function generatePerformanceReport() {
    if (!CSS_DEBUG) {
        return [];
    }
    
    return [
        'simple_optimized' => [
            'external_requests' => 1,  // Tailwind CDN만
            'css_size' => 'compressed inline',
            'icons' => 'emoji (no external)',
            'bootstrap' => 'minimal inline',
            'fonts' => 'Google Fonts (preload)',
            'estimated_load_time' => '< 100ms'
        ],
        'legacy_system' => [
            'external_requests' => 5,  // Google Fonts, Font Awesome, Bootstrap, Bootstrap Icons, Tailwind
            'css_size' => 'full external libraries',
            'icons' => 'Font Awesome (external)',
            'bootstrap' => 'full Bootstrap (external)',
            'fonts' => 'Google Fonts (render blocking)',
            'estimated_load_time' => '> 500ms'
        ]
    ];
}

/**
 * 설정 상태 확인
 */
function getCSSOptimizationStatus() {
    return [
        'mode' => CSS_OPTIMIZATION_MODE,
        'debug' => CSS_DEBUG,
        'simple_optimizer_available' => class_exists('SimpleCSSOptimizer'),
        'legacy_system_available' => function_exists('renderNaturalGreenTheme'),
        'recommendation' => 'simple'
    ];
}

// 전역 상수 정의
if (!defined('CSS_OPTIMIZATION_ACTIVE')) {
    define('CSS_OPTIMIZATION_ACTIVE', true);
}