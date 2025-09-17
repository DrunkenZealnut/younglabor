<?php
/**
 * 최적화된 헤더 시스템
 * Critical CSS 인라인 + 지연 로딩으로 성능 최적화
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

// 필요한 클래스들 로드
require_once __DIR__ . '/critical-css-generator.php';
require_once __DIR__ . '/NaturalGreenThemeLoader.php';

// Critical CSS 생성기 초기화
$criticalGenerator = new CriticalCSSGenerator();
$cssMode = getCSSMode();

// Natural Green 테마 로드
$theme = getNaturalGreenTheme();

// 테마 URL 정의 (단일 테마)
$themeUrl = app_url('theme/natural-green');
$siteUrl = app_url();

// 현재 페이지 변수 설정
$currentPage = isset($_GET['page']) ? $_GET['page'] : (isset($currentSlug) ? $currentSlug : 'home');
$pageTitle = isset($pageTitle) ? $pageTitle : $theme->getSiteName();
$metaDescription = isset($pageDescription) ? $pageDescription : $theme->getSiteDescription();

// 성능 추적 시작
$cssMode->startPerformanceTracking();
?>
<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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

    <!-- 최적화된 CSS 로딩 시작 -->
    <meta name="css-optimization" content="hopec-v1.0">

<!-- DNS Prefetch for Critical Resources -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//fonts.gstatic.com">

<!-- Preconnect for Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Critical CSS Inline -->
<style id="hopec-critical-css">
<?php echo $criticalGenerator->generateCriticalCSS(); ?>
</style>

<!-- Font Loading with font-display: swap -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

<!-- Essential Icons (minimal Font Awesome alternatives) -->
<style id="hopec-essential-icons">
/* Essential Icons - Font Awesome alternatives */
.fa-arrow-right::before { content: "→"; }
.fa-arrow-left::before { content: "←"; }
.fa-check::before { content: "✓"; }
.fa-times::before { content: "✕"; }
.fa-plus::before { content: "+"; }
.fa-minus::before { content: "-"; }
.fa-search::before { content: "🔍"; }
.fa-home::before { content: "🏠"; }
.fa-user::before { content: "👤"; }
.fa-envelope::before { content: "✉"; }
.fa-phone::before { content: "📞"; }
.fa-menu::before { content: "☰"; }

/* Bootstrap Icons alternatives */
.bi-check::before { content: "✓"; }
.bi-x::before { content: "✕"; }
.bi-arrow-right::before { content: "→"; }
.bi-arrow-left::before { content: "←"; }
</style>

<!-- Remodal CSS (팝업 라이브러리) - Critical for popup functionality -->
<link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal.css">
<link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal-default-theme.css">

<!-- Natural Green 테마 CSS 최적화 버전 -->
<?php
// Natural Green 단일 테마 CSS 로드
renderNaturalGreenTheme();
?>

<!-- Non-Critical CSS - Async Loading -->
<script id="hopec-async-css-loader">
(function() {
    'use strict';
    
    // Async CSS Loading Function
    function loadCSS(href, before, media, callback) {
        var doc = window.document;
        var ss = doc.createElement("link");
        var ref;
        if (before) {
            ref = before;
        } else {
            var refs = (doc.body || doc.getElementsByTagName("head")[0]).childNodes;
            ref = refs[refs.length - 1];
        }
        
        var sheets = doc.styleSheets;
        
        if (callback) {
            ss.onload = callback;
        }
        
        ss.rel = "stylesheet";
        ss.href = href;
        ss.media = "only x";
        
        function ready(cb) {
            if (doc.body) {
                return cb();
            }
            setTimeout(function() {
                ready(cb);
            });
        }
        
        ready(function() {
            ref.parentNode.insertBefore(ss, (before ? ref : ref.nextSibling));
        });
        
        var onloadcssdefined = function(cb) {
            var resolvedHref = ss.href;
            var i = sheets.length;
            while (i--) {
                if (sheets[i].href === resolvedHref) {
                    return cb();
                }
            }
            setTimeout(function() {
                onloadcssdefined(cb);
            });
        };
        
        function loadCB() {
            if (ss.addEventListener) {
                ss.removeEventListener("load", loadCB);
            }
            ss.media = media || "all";
        }
        
        if (ss.addEventListener) {
            ss.addEventListener("load", loadCB);
        }
        
        ss.onloadcssdefined = onloadcssdefined;
        
        onloadcssdefined(loadCB);
        
        return ss;
    }
    
    // Load Non-Critical CSS after page load
    window.addEventListener('load', function() {
        <?php if ($cssMode->isDebugMode()): ?>
        console.log('🚀 Loading non-critical CSS...');
        <?php endif; ?>
        
        // Load full Font Awesome (non-critical)
        loadCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', null, 'all', function() {
            <?php if ($cssMode->isDebugMode()): ?>
            console.log('✅ Font Awesome loaded');
            <?php endif; ?>
        });
        
        // Load full Bootstrap Icons (non-critical)
        loadCSS('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css', null, 'all', function() {
            <?php if ($cssMode->isDebugMode()): ?>
            console.log('✅ Bootstrap Icons loaded');
            <?php endif; ?>
        });
        
        // Load extended theme CSS if exists
        var extendedCSS = '/css/hopec-extended.css?v=<?= time() ?>';
        loadCSS(extendedCSS, null, 'all', function() {
            <?php if ($cssMode->isDebugMode()): ?>
            console.log('✅ Extended CSS loaded');
            <?php endif; ?>
        });
    });
    
    // Fallback: Load essential CSS if critical CSS fails
    var criticalStyle = document.getElementById('hopec-critical-css');
    if (!criticalStyle || criticalStyle.textContent.length < 1000) {
        console.warn('⚠️ Critical CSS seems to have failed, loading fallback...');
        
        // Emergency fallback to CDN
        loadCSS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', null, 'all');
        
        // Add Tailwind as script fallback
        var tailwindScript = document.createElement('script');
        tailwindScript.src = 'https://cdn.tailwindcss.com';
        document.head.appendChild(tailwindScript);
    }
})();
</script>

<!-- Essential JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>

<!-- Lucide Icons (lightweight alternative) -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Remodal JavaScript -->
<script src="<?= $siteUrl ?>/js/remodal/remodal.js"></script>

<!-- Natural Green 테마 JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 테마 로딩 상태 확인
    const criticalCSS = document.getElementById('hopec-critical-css');
    
    if (criticalCSS) {
        console.log('🎨 Optimized Critical CSS loaded:', criticalCSS.textContent.length + ' chars');
    }
    
    // 테마 정보 확인
    if (window.HOPEC_THEME) {
        console.log('🎨 테마 정보:', window.HOPEC_THEME);
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
        console.log('🎯 Primary 색상:', primaryColor);
    }
    
    <?php if ($cssMode->isDebugMode()): ?>
    // 디버그 모드: CSS 로딩 상태 모니터링
    console.log('🔍 CSS Debug Mode Active');
    
    // Performance metrics
    if (window.hopecCSSPerformance) {
        window.hopecCSSPerformance.criticalCSSSize = criticalCSS ? criticalCSS.textContent.length : 0;
        window.hopecCSSPerformance.criticalCSSWithinLimit = window.hopecCSSPerformance.criticalCSSSize <= 7168;
    }
    
    // CSS 로딩 완료 확인
    setTimeout(function() {
        const allStyles = document.querySelectorAll('style, link[rel="stylesheet"]');
        console.log('📊 Total CSS resources:', allStyles.length);
        
        allStyles.forEach(function(style, index) {
            if (style.tagName === 'STYLE') {
                console.log('📄 Style ' + index + ': ' + (style.id || 'inline') + ' (' + style.textContent.length + ' chars)');
            } else {
                console.log('🔗 Link ' + index + ': ' + style.href);
            }
        });
    }, 2000);
    <?php endif; ?>
});
</script>

<?= csrf_field() ?>

<?php 
// CSS 모드 디버그 정보 출력
if ($cssMode->isDebugMode()) {
    $debugInfo = $criticalGenerator->getDebugInfo();
    echo "<!-- Critical CSS Debug Info:\n";
    echo "Size: " . $debugInfo['size_kb'] . "KB\n";
    echo "Within 7KB limit: " . ($debugInfo['within_limit'] ? 'Yes' : 'No') . "\n";
    echo "Natural Green exists: " . ($debugInfo['natural_green_exists'] ? 'Yes' : 'No') . "\n";
    echo "-->\n";
    
    // 화면에 디버그 정보 표시
    $cssMode->renderDebugInfo();
}
?>

<!-- 최적화된 CSS 로딩 끝 -->

  </head>
  <body class="min-vh-100 d-flex flex-column" style="font-family: 'Noto Sans KR', sans-serif; background-color: var(--background); color: var(--foreground);">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:m-4 focus:p-2 focus:bg-white focus:border focus:border-gray-400">본문 바로가기</a>
    
    <?php 
    // Natural Green 네비게이션 헤더 포함 (최적화 버전)
    $naturalGreenNavigation = HOPEC_BASE_PATH . '/theme/natural-green/includes/navigation.php';
    
    if (file_exists($naturalGreenNavigation)) {
        try {
            include $naturalGreenNavigation;
        } catch (Exception $e) {
            // 네비게이션 로드 실패시 fallback
            echo '<!-- Navigation load error: ' . $e->getMessage() . ' -->';
            echo '<nav class="navbar navbar-expand-lg" style="background-color: var(--primary);">
                    <div class="container">
                        <a class="navbar-brand text-white" href="/">희망씨</a>
                        <div class="navbar-nav">
                            <a class="nav-link text-white" href="/about/about.php">소개</a>
                            <a class="nav-link text-white" href="/community/gallery.php">갤러리</a>
                            <a class="nav-link text-white" href="/community/newsletter.php">소식지</a>
                        </div>
                    </div>
                  </nav>';
        }
    } else {
        // Fallback: 기본 네비게이션
        echo '<!-- Navigation file not found: ' . $naturalGreenNavigation . ' -->';
        echo '<nav class="navbar navbar-expand-lg" style="background-color: var(--primary);">
                <div class="container">
                    <a class="navbar-brand text-white" href="/">희망씨</a>
                    <div class="navbar-nav">
                        <a class="nav-link text-white" href="/about/about.php">소개</a>
                        <a class="nav-link text-white" href="/community/gallery.php">갤러리</a>
                        <a class="nav-link text-white" href="/community/newsletter.php">소식지</a>
                    </div>
                </div>
              </nav>';
    }
    ?>
    <div id="wrapper"><div id="container_wr"><div id="container">
<?php

// 성능 추적 종료
$cssMode->endPerformanceTracking();