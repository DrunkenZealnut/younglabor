<?php
/**
 * 최적화된 헤더 (테스트용)
 * 기존 header.php와 완전히 분리된 독립 실행 파일
 * 
 * 사용법: include __DIR__ . '/header-optimized.php'; 대신 include __DIR__ . '/header.php';
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 최적화 CSS 시스템 로드
require_once __DIR__ . '/OptimizedCSS/config.php';
require_once __DIR__ . '/OptimizedCSS/OptimizedHeader.php';

// 기존 템플릿 헬퍼 로드 (호환성 유지)
require_once __DIR__ . '/template_helpers.php';

// 페이지 변수 설정 (기존과 동일)
$siteUrl = app_url();
$pageTitle = isset($pageTitle) ? $pageTitle : app_name();
$metaDescription = isset($pageDescription) ? $pageDescription : '희망연대노동조합 - 이웃과 함께하는 노동권 보호';

// 현재 페이지 타입 감지
$pageType = 'default';
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($currentUri, '/community/gallery') !== false) {
    $pageType = 'gallery';
} elseif (strpos($currentUri, '/community/newsletter') !== false) {
    $pageType = 'newsletter';
} elseif (strpos($currentUri, '/about/') !== false) {
    $pageType = 'about';
} elseif ($currentUri === '/' || $currentUri === '/index.php') {
    $pageType = 'home';
}

// 최적화 시스템 활성화 여부 확인
if (OPTIMIZED_CSS_FINAL) {
    // 🚀 최적화된 헤더 렌더링
    $optimizedHeader = renderOptimizedHeader($pageType, $pageTitle, $metaDescription);
    
    // 디버그 정보 출력 (개발모드)
    if (CSS_DEBUG) {
        $optimizedHeader->renderDebugInfo();
    }
    
} else {
    // 🔄 기존 헤더 시스템으로 폴백
    echo "<!-- 최적화 시스템 비활성화 - 기존 헤더 사용 -->\n";
    
    // 기존 Natural Green 테마 로드
    require_once __DIR__ . '/NaturalGreenThemeLoader.php';
    $theme = getNaturalGreenTheme();
    $themeUrl = app_url('theme/natural-green');
    $currentPage = isset($_GET['page']) ? $_GET['page'] : (isset($currentSlug) ? $currentSlug : 'home');
    
    // 기존 HTML 구조 렌더링
    ?>
    <!DOCTYPE html>
    <html lang="ko">
      <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        
        <!-- 롤백된 경우 원인 표시 -->
        <?php if (isRolledBack()): ?>
        <!-- CSS 최적화 시스템 롤백됨: <?= $_COOKIE['css_rollback_reason'] ?? 'unknown' ?> -->
        <?php endif; ?>
        
        <!-- 기존 캐시 정책 -->
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <meta http-equiv="Last-Modified" content="<?= gmdate('D, d M Y H:i:s T') ?>" />
        <meta name="cache-buster" content="<?= time() . '-' . rand(1000,9999) ?>" />
        
        <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
        <meta name="title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
        <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
        <link rel="canonical" href="<?= htmlspecialchars($siteUrl . '/', ENT_QUOTES, 'UTF-8') ?>" />
        <meta name="language" content="ko" />
        <meta property="og:locale" content="ko_KR" />
        <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
        <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
        
        <!-- 기존 외부 리소스 -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        
        <!-- Remodal CSS (팝업 라이브러리) -->
        <link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal.css">
        <link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal-default-theme.css">
        
        <!-- Remodal JavaScript (팝업 라이브러리) -->
        <script src="<?= $siteUrl ?>/js/remodal/remodal.js"></script>
        
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <?php
        // Natural Green 단일 테마 CSS 로드
        renderNaturalGreenTheme();
        ?>
        
        <!-- Natural Green 테마 시스템 -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 테마 로딩 상태 확인
            const themeCSS = document.getElementById('natural-green-theme');
            
            if (themeCSS) {
                console.log('🎨 Natural Green 테마 CSS 로드됨:', themeCSS.href);
            }
            
            // 테마 정보 확인
            if (window.HOPEC_THEME) {
                console.log('🎨 테마 정보:', window.HOPEC_THEME);
                const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
                console.log('🎨 Primary Color:', primaryColor);
            }
            
            console.log('📊 CSS 시스템: 기존 (Natural Green)');
        });
        </script>
      </head>
      <body>
    <?php
}

// 공통: 네비게이션 렌더링 (기존과 동일)
include_once __DIR__ . '/../theme/natural-green/includes/navigation.php';

// 성능 측정 (최적화 버전의 경우)
if (OPTIMIZED_CSS_FINAL && isset($optimizedHeader)) {
    $optimizedHeader->renderPerformanceReport();
}
?>