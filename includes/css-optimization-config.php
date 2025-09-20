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
     * - 'css-vars': CSS 변수 직접 활용 시스템 (신규)
     * - 'auto': 자동 감지 (사용자 환경에 따라)
     */
    define('CSS_OPTIMIZATION_MODE', 'legacy');
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
    // CSS Mode Manager를 통해 현재 모드 확인
    if (function_exists('getCSSMode')) {
        $cssMode = getCSSMode();
        if ($cssMode->isCSSVarsMode()) {
            $mode = 'css-vars';
        } elseif ($cssMode->isOptimizedMode()) {
            $mode = 'simple';
        } else {
            $mode = 'legacy';
        }
    } else {
        // 폴백: CSS_OPTIMIZATION_MODE 사용
        $mode = defined('CSS_OPTIMIZATION_MODE') ? CSS_OPTIMIZATION_MODE : 'legacy';
        
        // Auto 모드인 경우 환경에 따라 자동 선택
        if ($mode === 'auto') {
            $mode = detectOptimalMode();
        }
    }
    
    switch ($mode) {
        case 'css-vars':
            renderCSSVariableMode($pageTitle, $pageDescription, $pageType);
            break;
            
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
 * CSS 변수 모드 렌더링
 */
function renderCSSVariableMode($pageTitle, $pageDescription, $pageType) {
    // CSS 변수 매니저 로드
    require_once __DIR__ . '/CSSVariableThemeManager.php';
    
    // URL 파라미터로 임시 테스트 지원
    if (isset($_GET['css_mode']) && $_GET['css_mode'] === 'css-vars') {
        if (!defined('CSS_OPTIMIZATION_MODE') || CSS_OPTIMIZATION_MODE !== 'css-vars') {
            define('CSS_VARS_TEST_MODE', true);
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="ko">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?= htmlspecialchars($pageTitle ?: '희망연대노동조합') ?></title>
        <meta name="description" content="<?= htmlspecialchars($pageDescription ?: '노동자의 권익을 위한 희망연대노동조합') ?>" />
        
        <!-- CSS 변수 모드: 최소한의 외부 리소스만 로드 -->
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- globals.css의 CSS 변수들을 Critical CSS로 인라인 삽입 -->
        <?php renderCSSVariablesInline(); ?>
        
        <!-- CSS 변수 모드용 최적화된 클래스 정의 -->
        <?php renderCSSVariableModeClasses($pageType); ?>
        
        <!-- CSS 변수 모드 활성화 JavaScript -->
        <script>
        window.CSS_VARS_MODE = true;
        window.CSS_OPTIMIZATION_MODE = 'css-vars';
        <?php if (CSS_DEBUG): ?>
        console.log('CSS Variables Mode Active');
        <?php endif; ?>
        </script>
        
        <?php if (CSS_DEBUG): ?>
        <!-- CSS 변수 모드 디버그 정보 -->
        <script>
        window.CSS_VARS_DEBUG = <?= json_encode(getCSSVariableManager()->getDebugInfo()) ?>;
        console.log('CSS Variables Debug Info:', window.CSS_VARS_DEBUG);
        </script>
        <?php endif; ?>
      </head>
      <body class="min-vh-100 d-flex flex-column" style="font-family: 'Noto Sans KR', sans-serif;">
    <?php
    
    if (CSS_DEBUG) {
        echo "<!-- CSS Variables Mode Active -->\n";
        echo "<!-- Available Variables: " . count(getCSSVariableManager()->getDebugInfo()['available_vars']) . " -->\n";
    }
}

/**
 * CSS 변수들을 인라인으로 렌더링
 */
function renderCSSVariablesInline() {
    $globalsCSS = __DIR__ . '/../theme/natural-green/styles/globals.css';
    
    echo "<style id=\"css-variables-inline\">\n";
    echo ":root {\n";
    
    // globals.css에서 CSS 변수들 추출하여 인라인으로 삽입
    if (file_exists($globalsCSS)) {
        $cssContent = file_get_contents($globalsCSS);
        
        // :root 섹션에서 CSS 변수들 추출
        if (preg_match('/:root\s*\{([^}]+)\}/s', $cssContent, $matches)) {
            $rootVars = $matches[1];
            
            // 각 CSS 변수를 개별적으로 파싱
            if (preg_match_all('/--[^:;]+:[^;]+;/m', $rootVars, $varMatches)) {
                foreach ($varMatches[0] as $varDeclaration) {
                    echo "  " . trim($varDeclaration) . "\n";
                }
            }
        }
    } else {
        // 폴백: 기본 CSS 변수들 직접 정의
        echo "  /* 폴백 CSS 변수들 */\n";
        echo "  --primary: oklch(0.855 0.165 130.5);\n";
        echo "  --primary-foreground: oklch(1 0 0);\n";
        echo "  --lime-500: oklch(0.722 0.193 120.75);\n";
        echo "  --lime-600: oklch(0.582 0.155 120.75);\n";
        echo "  --forest-700: oklch(0.225 0.058 152.48);\n";
        echo "  --natural-100: oklch(0.967 0.015 135.62);\n";
        echo "  --natural-200: oklch(0.932 0.025 135.62);\n";
        echo "  --border: rgba(132, 204, 22, 0.15);\n";
    }
    
    echo "}\n";
    echo "</style>\n";
}

/**
 * CSS 변수 모드용 최적화된 클래스 정의
 * 175줄 → 50줄로 축소, 조건부 로딩 구현
 */
function renderCSSVariableModeClasses($pageType = 'default') {
    // 캐싱 시스템 로드
    require_once __DIR__ . '/CSSVariablesCache.php';
    $cache = getCSSVariablesCache();
    
    // 캐시에서 CSS 조회
    $cachedCSS = $cache->getCachedCSS($pageType, 'inline');
    if ($cachedCSS !== null) {
        echo "<style id=\"css-variables-mode-classes\">\n";
        echo "/* 캐시된 CSS (최적화됨) */\n";
        echo $cachedCSS;
        echo "\n</style>\n";
        return;
    }
    
    // 캐시 미스 - 새로 생성하여 캐시에 저장
    ob_start();
    echo "/* CSS Variables 모드 클래스 (페이지 타입: {$pageType}) */\n";
    
    // 핵심 CSS 변수 클래스들 (모든 페이지에서 필요)
    echo "/* 핵심 색상 클래스 */\n";
    echo ".text-forest-700 { color: var(--forest-700) !important; }\n";
    echo ".text-lime-500 { color: var(--lime-500) !important; }\n";
    echo ".text-white { color: var(--white, #ffffff) !important; }\n";
    echo ".text-title { color: var(--title-color) !important; }\n";
    echo ".bg-white { background-color: var(--white, #ffffff) !important; }\n";
    echo ".bg-natural-100 { background-color: var(--natural-100) !important; }\n";
    echo ".bg-lime-500 { background-color: var(--lime-500) !important; }\n";
    echo ".border-primary { border-color: var(--primary) !important; }\n";
    
    // 기본 유틸리티 (모든 페이지에서 필요)
    echo "\n/* 기본 유틸리티 */\n";
    echo ".transition-all { transition: all 0.3s ease !important; }\n";
    echo ".hover-lift:hover { transform: translateY(-2px) !important; }\n";
    echo "body { font-family: 'Noto Sans KR', sans-serif !important; }\n";
    
    // 조건부 로딩: 페이지 타입별 추가 CSS
    if ($pageType === 'navigation' || $pageType === 'header') {
        echo "\n/* 네비게이션 전용 클래스 */\n";
        echo ".container-xl { max-width: 1320px; margin: 0 auto; padding: 0 1rem; }\n";
        echo ".d-flex { display: flex !important; }\n";
        echo ".align-items-center { align-items: center !important; }\n";
        echo ".justify-content-between { justify-content: space-between !important; }\n";
        echo "header { background-color: rgba(255, 255, 255, 0.95) !important; backdrop-filter: blur(12px) !important; }\n";
    }
    
    if ($pageType === 'gallery' || $pageType === 'grid') {
        echo "\n/* 그리드 레이아웃 전용 클래스 */\n";
        echo ".grid { display: grid !important; }\n";
        echo ".grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)) !important; }\n";
        echo ".gap-6 { gap: 1.5rem !important; }\n";
        echo "@media (min-width: 768px) { .md\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; } }\n";
        echo "@media (min-width: 1024px) { .lg\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; } }\n";
    }
    
    if ($pageType === 'about' || $pageType === 'content') {
        echo "\n/* 콘텐츠 페이지 전용 클래스 */\n";
        echo ".text-gray-600 { color: var(--gray-600, #6b7280) !important; }\n";
        echo ".bg-gray-50 { background-color: var(--gray-50, #f9fafb) !important; }\n";
        echo ".hover\\:bg-natural-100:hover { background-color: var(--natural-100) !important; }\n";
        echo ".shadow-md { box-shadow: var(--shadow-md) !important; }\n";
        echo ".rounded-lg { border-radius: var(--radius, 0.5rem) !important; }\n";
    }
    
    // 생성된 CSS를 캐시에 저장
    $generatedCSS = ob_get_clean();
    $cachedCSS = $cache->setCachedCSS($pageType, $generatedCSS, 'inline');
    
    echo "<style id=\"css-variables-mode-classes\">\n";
    echo "/* 최적화된 CSS (페이지 타입: {$pageType}) */\n";
    echo $cachedCSS;
    echo "\n</style>\n";
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
        'css_vars_mode' => [
            'external_requests' => 2,  // Google Fonts, Tailwind CDN만
            'css_size' => 'CSS variables inline + globals.css',
            'icons' => 'SVG inline (no external)',
            'bootstrap' => 'CSS variables only',
            'fonts' => 'Google Fonts (preload)',
            'theme_system' => 'CSS variables direct',
            'estimated_load_time' => '< 50ms'
        ],
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
        'css_vars_available' => file_exists(__DIR__ . '/CSSVariableThemeManager.php'),
        'simple_optimizer_available' => class_exists('SimpleCSSOptimizer'),
        'legacy_system_available' => function_exists('renderNaturalGreenTheme'),
        'globals_css_exists' => file_exists(__DIR__ . '/../theme/natural-green/styles/globals.css'),
        'recommendation' => 'css-vars',  // CSS 변수 모드 권장 (현재 기본값)
        'test_url' => '?css_mode=css-vars',  // 테스트용 URL 파라미터
        'fallback_available' => true
    ];
}

// 전역 상수 정의
if (!defined('CSS_OPTIMIZATION_ACTIVE')) {
    define('CSS_OPTIMIZATION_ACTIVE', true);
}