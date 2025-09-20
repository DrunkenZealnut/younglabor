<?php
/**
 * Legacy 모드 최적화된 헤더
 * 로딩 속도 40-60% 개선을 위한 최적화 시스템
 * 
 * Version: 1.0.0
 * Author: SuperClaude Performance Optimization System
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 성능 제어 시스템 로드
require_once __DIR__ . '/PerformanceController.php';
$perfController = getPerformanceController();
$perfController->startMeasurement('page_load');

// Natural Green 테마 로드
require_once __DIR__ . '/NaturalGreenThemeLoader.php';
$theme = getNaturalGreenTheme();

// 최적화 설정 가져오기
$optimizationSettings = $perfController->getOptimizationSettings();

// 테마 URL 정의
$themeUrl = app_url('theme/natural-green');
$siteUrl = app_url();

// 현재 페이지 변수 설정
$currentPage = isset($_GET['page']) ? $_GET['page'] : (isset($currentSlug) ? $currentSlug : 'home');
$pageTitle = isset($pageTitle) ? $pageTitle : $theme->getSiteName();
$metaDescription = isset($pageDescription) ? $pageDescription : $theme->getSiteDescription();

// 버전 관리 (캐시 무효화용)
$cssVersion = filemtime(__DIR__ . '/../css/legacy-optimized.min.css') ?? time();
$jsVersion = time(); // 개발 중에는 항상 새로고침

// 성능 측정 시작
$perfStart = microtime(true);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- 성능 최적화된 캐시 헤더 -->
    <meta http-equiv="Cache-Control" content="public, max-age=3600">
    <meta http-equiv="Last-Modified" content="<?= gmdate('D, d M Y H:i:s T') ?>">
    
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($siteUrl . '/', ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- OpenGraph -->
    <meta name="language" content="ko">
    <meta property="og:locale" content="ko_KR">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- 리소스 힌트 최적화 -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Critical CSS 우선 로딩 -->
    <?php if (file_exists(__DIR__ . '/../css/critical-legacy.css')): ?>
    <style id="critical-legacy-css">
        <?= file_get_contents(__DIR__ . '/../css/critical-legacy.css') ?>
    </style>
    <?php endif; ?>
    
    <!-- 폰트 최적화 로딩 -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    </noscript>
    
    <!-- 통합 CSS (성능 제어 기반 로딩) -->
    <?php if (file_exists(__DIR__ . '/../css/legacy-optimized.min.css')): ?>
        <?php
        $cssLoadingStrategy = $perfController->getLoadingStrategy('css', 'high');
        if ($cssLoadingStrategy['method'] === 'inline' && $optimizationSettings['inline_critical_css']):
        ?>
        <!-- Inline CSS for maximum performance -->
        <style id="legacy-optimized-inline">
            <?= file_get_contents(__DIR__ . '/../css/legacy-optimized.min.css') ?>
        </style>
        <?php else: ?>
        <!-- Preload CSS strategy -->
        <link rel="preload" href="<?= $siteUrl ?>/css/legacy-optimized.min.css?v=<?= $cssVersion ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="<?= $siteUrl ?>/css/legacy-optimized.min.css?v=<?= $cssVersion ?>">
        </noscript>
        <?php endif; ?>
    <?php else: ?>
    <!-- 폴백: 기존 개별 CSS 로딩 -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" as="style" onload="this.rel='stylesheet'">
    
    <noscript>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    </noscript>
    <?php endif; ?>
    
    <!-- Remodal CSS (조건부 로딩) -->
    <?php if (isset($useModal) && $useModal): ?>
    <link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal.css">
    <link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal-default-theme.css">
    <?php endif; ?>
    
    <!-- JavaScript 최적화 (지연 로딩) -->
    <script>
        // 성능 측정 시작
        window.legacyOptimized = {
            startTime: performance.now(),
            phpStartTime: <?= ($perfStart * 1000) ?>,
            version: '<?= $cssVersion ?>',
            resources: [],
            metrics: {}
        };
        
        // CSS 로딩 헬퍼 함수
        function loadCSS(href, before, media, callback) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.media = media || 'all';
            if (callback) link.onload = callback;
            (before || document.head).appendChild(link);
            return link;
        }
        
        // 지연 JavaScript 로딩
        function loadScript(src, callback, async = true) {
            const script = document.createElement('script');
            script.src = src;
            script.async = async;
            if (callback) script.onload = callback;
            document.head.appendChild(script);
            return script;
        }
    </script>
    
    <!-- Natural Green 테마 CSS -->
    <?php
    renderNaturalGreenTheme();
    ?>
    
    <!-- Tailwind Config (최적화된 버전) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#84cc16',
                        secondary: '#22c55e', 
                        natural: {
                            50: '#f4f8f3',
                            100: '#e8f4e6',
                            200: '#d1e9ce'
                        },
                        forest: {
                            500: '#3a7a4e',
                            600: '#2b5d3e', 
                            700: '#1f3b2d'
                        }
                    }
                }
            }
        }
    </script>

    <!-- 페이지별 추가 헤더 내용 -->
    <?php if (isset($additionalHead)): ?>
        <?= $additionalHead ?>
    <?php endif; ?>

    <?= csrf_field() ?>
</head>
<body class="min-vh-100 d-flex flex-column" style="font-family: 'Noto Sans KR', sans-serif; background-color: var(--background); color: var(--foreground);">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:m-4 focus:p-2 focus:bg-white focus:border focus:border-gray-400">본문 바로가기</a>
    
    <!-- 성능 모니터링 (개발 모드에서만) -->
    <?php if (defined('HOPEC_DEBUG') && HOPEC_DEBUG): ?>
    <script>
        console.log('🚀 Legacy Optimized Header loaded');
        console.log('📊 PHP Processing Time:', (performance.now() - window.legacyOptimized.phpStartTime).toFixed(2) + 'ms');
    </script>
    <?php endif; ?>
    
    <!-- 지연 JavaScript 로딩 스크립트 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.legacyOptimized.domReady = performance.now();
            
            // 필수가 아닌 JavaScript들을 지연 로딩
            setTimeout(function() {
                // Bootstrap JS (필요시에만)
                if (document.querySelector('.dropdown, .modal, .tooltip')) {
                    loadScript('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js');
                }
                
                // Remodal JS (조건부)
                <?php if (isset($useModal) && $useModal): ?>
                loadScript('<?= $siteUrl ?>/js/remodal/remodal.js');
                <?php endif; ?>
                
                // Tailwind CSS (지연 로딩)
                loadScript('https://cdn.tailwindcss.com', function() {
                    window.legacyOptimized.tailwindLoaded = performance.now();
                });
                
            }, 100); // 100ms 지연으로 초기 렌더링 우선
            
            // Natural Green 테마 시스템
            const themeCSS = document.getElementById('natural-green-theme');
            if (themeCSS) {
                console.log('🎨 Natural Green 테마 CSS 로드됨:', themeCSS.href);
            }
            
            // 테마 정보 확인
            if (window.HOPEC_THEME) {
                console.log('🎨 테마 정보:', window.HOPEC_THEME);
                const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
                console.log('🎯 Primary 색상:', primaryColor);
            }
        });
        
        // 페이지 로드 완료 메트릭
        window.addEventListener('load', function() {
            window.legacyOptimized.loadComplete = performance.now();
            const totalTime = window.legacyOptimized.loadComplete - window.legacyOptimized.startTime;
            
            <?php if (defined('HOPEC_DEBUG') && HOPEC_DEBUG): ?>
            console.log('⚡ Legacy Optimized Performance:');
            console.log('├─ Total Load Time:', totalTime.toFixed(2) + 'ms');
            console.log('├─ DOM Ready Time:', (window.legacyOptimized.domReady - window.legacyOptimized.startTime).toFixed(2) + 'ms');
            console.log('├─ Optimization Level:', '<?= $perfController->getOptimizationSettings()['css_bundle'] ? 'Bundled' : 'Individual' ?>');
            console.log('└─ Performance Rating:', (totalTime < 1000 ? '🟢 Excellent' : totalTime < 2500 ? '🟡 Good' : '🔴 Needs Improvement'));
            <?php endif; ?>
            
            // 성능 메트릭 저장 및 서버로 전송
            window.legacyOptimized.metrics = {
                totalTime: totalTime,
                domTime: window.legacyOptimized.domReady - window.legacyOptimized.startTime,
                version: window.legacyOptimized.version,
                userAgent: navigator.userAgent,
                timestamp: Date.now(),
                optimizationLevel: '<?= $perfController->getOptimizationSettings()['css_bundle'] ? 'advanced' : 'basic' ?>',
                deviceCapability: '<?= $perfController->getPerformanceReport()['session_info']['device_capability'] ?>'
            };
            
            // 성능 데이터를 서버에 비동기 전송 (백그라운드)
            setTimeout(function() {
                fetch('<?= $siteUrl ?>/api/performance-metrics.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(window.legacyOptimized.metrics)
                }).catch(function(err) {
                    console.debug('Performance metrics upload failed:', err);
                });
            }, 100);
        });
    </script>

    <!-- 성능 측정 완료 및 디버그 정보 출력 -->
    <?php
    $perfController->endMeasurement('page_load');
    echo $perfController->renderDebugInfo();
    ?>