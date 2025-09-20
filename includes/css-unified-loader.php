<?php
/**
 * 통합 CSS 로딩 시스템
 * 단일 진입점으로 모든 CSS 로딩을 효율적으로 관리
 * 
 * Features:
 * - Critical CSS 우선 로딩
 * - Non-critical CSS 지연 로딩
 * - 캐싱 및 압축 지원
 * - 테마별 최적화
 * - 성능 모니터링
 * 
 * Version: 2.0.0
 * Author: SuperClaude CSS Optimization System
 */

class UnifiedCSSLoader {
    
    private $config;
    private $cache;
    private $performance;
    
    // CSS 리소스 정의
    const CRITICAL_RESOURCES = [
        'fonts' => [
            'noto-sans-kr' => 'https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap'
        ],
        'core' => [
            'critical-css' => '/css/critical.css',
            'theme-variables' => '/css/theme-variables.css'
        ]
    ];
    
    const NON_CRITICAL_RESOURCES = [
        'icons' => [
            'font-awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
            'bootstrap-icons' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css'
        ],
        'frameworks' => [
            'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
        ],
        'components' => [
            'remodal' => '/js/remodal/remodal.css',
            'remodal-theme' => '/js/remodal/remodal-default-theme.css'
        ]
    ];
    
    public function __construct($config = []) {
        $this->config = array_merge([
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'minify_enabled' => true,
            'critical_threshold' => 7168, // 7KB
            'performance_tracking' => false,
            'fallback_mode' => 'cdn'
        ], $config);
        
        $this->cache = new CSSCache($this->config);
        $this->performance = new CSSPerformanceTracker();
    }
    
    /**
     * 메인 CSS 로딩 함수
     */
    public function loadCSS($pageType = 'default', $theme = 'natural-green') {
        $this->performance->start('css_loading');
        
        // 1. HTML HEAD 시작
        $this->renderHead();
        
        // 2. Critical CSS 로딩
        $this->loadCriticalCSS($pageType, $theme);
        
        // 3. Non-Critical CSS 비동기 로딩 스크립트
        $this->renderAsyncLoader();
        
        // 4. 성능 모니터링 스크립트
        if ($this->config['performance_tracking']) {
            $this->renderPerformanceTracker();
        }
        
        $this->performance->end('css_loading');
    }
    
    /**
     * HTML HEAD 기본 구조
     */
    private function renderHead() {
        ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    
    <!-- Preconnect for Critical Resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <?php
    }
    
    /**
     * Critical CSS 로딩 (인라인)
     */
    private function loadCriticalCSS($pageType, $theme) {
        $criticalCSS = $this->generateCriticalCSS($pageType, $theme);
        
        echo "<style id=\"hopec-critical-css\">\n";
        echo $criticalCSS;
        echo "\n</style>\n";
        
        // Critical 폰트 로딩
        foreach (self::CRITICAL_RESOURCES['fonts'] as $name => $url) {
            echo "<link href=\"{$url}\" rel=\"stylesheet\">\n";
        }
    }
    
    /**
     * Critical CSS 생성
     */
    private function generateCriticalCSS($pageType, $theme) {
        $cacheKey = "critical_css_{$pageType}_{$theme}";
        
        if ($this->config['cache_enabled']) {
            $cached = $this->cache->get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }
        
        $css = $this->buildCriticalCSS($pageType, $theme);
        
        if ($this->config['cache_enabled']) {
            $this->cache->set($cacheKey, $css, $this->config['cache_ttl']);
        }
        
        return $css;
    }
    
    /**
     * Critical CSS 구성
     */
    private function buildCriticalCSS($pageType, $theme) {
        $css = '';
        
        // 1. CSS Variables (테마 색상)
        $css .= $this->getThemeVariables($theme);
        
        // 2. 기본 레이아웃 스타일
        $css .= $this->getLayoutStyles();
        
        // 3. 페이지별 Critical 스타일
        $css .= $this->getPageSpecificStyles($pageType);
        
        // 4. 반응형 기본 스타일
        $css .= $this->getResponsiveStyles();
        
        return $this->config['minify_enabled'] ? $this->minifyCSS($css) : $css;
    }
    
    /**
     * 테마 변수 생성
     */
    private function getThemeVariables($theme) {
        return "
:root {
    /* Natural Green Theme Colors */
    --primary: #22c55e;
    --primary-dark: #16a34a;
    --primary-light: #86efac;
    --secondary: #64748b;
    --background: #ffffff;
    --foreground: #0f172a;
    --muted: #f1f5f9;
    --muted-foreground: #64748b;
    --border: #e2e8f0;
    --accent: #f8fafc;
    --accent-foreground: #0f172a;
    
    /* Typography */
    --font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-size-base: 16px;
    --line-height-base: 1.6;
    
    /* Spacing */
    --container-max-width: 1320px;
    --container-padding: 1rem;
    
    /* Transitions */
    --transition-fast: 150ms ease-in-out;
    --transition-normal: 300ms ease-in-out;
}
";
    }
    
    /**
     * 기본 레이아웃 스타일
     */
    private function getLayoutStyles() {
        return "
/* Reset & Base */
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: var(--font-family);
    font-size: var(--font-size-base);
    line-height: var(--line-height-base);
    background-color: var(--background);
    color: var(--foreground);
}

/* Container System */
.container, #container {
    width: 100%;
    max-width: var(--container-max-width);
    margin: 0 auto;
    padding: 0 var(--container-padding);
}

#wrapper { min-height: 100vh; }
#container_wr { margin: 0 auto; }

/* Navigation */
.navbar {
    background-color: var(--primary);
    padding: 1rem 0;
}
.navbar-brand {
    color: white;
    font-weight: 700;
    text-decoration: none;
}
.nav-link {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    transition: var(--transition-fast);
}
.nav-link:hover {
    color: var(--primary-light);
}

/* Accessibility */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
";
    }
    
    /**
     * 페이지별 특화 스타일
     */
    private function getPageSpecificStyles($pageType) {
        $styles = [
            'home' => "
/* Hero Section */
.hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 4rem 0;
    text-align: center;
}
.hero h1 { font-size: 2.5rem; margin-bottom: 1rem; }
.hero p { font-size: 1.25rem; opacity: 0.9; }
",
            'about' => "
/* About Page */
.content-section { padding: 2rem 0; }
.content-section h2 { color: var(--primary); margin-bottom: 1.5rem; }
",
            'community' => "
/* Community Page */
.gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.gallery-item { border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
"
        ];
        
        return $styles[$pageType] ?? '';
    }
    
    /**
     * 반응형 스타일
     */
    private function getResponsiveStyles() {
        return "
/* Responsive Design */
@media (max-width: 768px) {
    .container { padding: 0 0.75rem; }
    .hero h1 { font-size: 2rem; }
    .hero p { font-size: 1rem; }
    .navbar { padding: 0.75rem 0; }
}

@media (max-width: 480px) {
    .container { padding: 0 0.5rem; }
    .hero { padding: 2rem 0; }
    .hero h1 { font-size: 1.75rem; }
}
";
    }
    
    /**
     * 비동기 CSS 로더 스크립트
     */
    private function renderAsyncLoader() {
        ?>
<script id="hopec-async-loader">
(function() {
    'use strict';
    
    // CSS 로딩 함수
    function loadCSS(href, callback) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.onload = callback || null;
        link.onerror = function() {
            console.warn('Failed to load CSS:', href);
            if (callback) callback();
        };
        document.head.appendChild(link);
        return link;
    }
    
    // Non-critical CSS 로딩
    window.addEventListener('load', function() {
        const resources = <?php echo json_encode(self::NON_CRITICAL_RESOURCES); ?>;
        let loadedCount = 0;
        let totalCount = 0;
        
        // 총 리소스 개수 계산
        Object.values(resources).forEach(category => {
            totalCount += Object.keys(category).length;
        });
        
        function onResourceLoaded() {
            loadedCount++;
            if (loadedCount === totalCount) {
                console.log('✅ All non-critical CSS loaded');
                document.body.classList.add('css-fully-loaded');
            }
        }
        
        // 리소스 로딩
        Object.entries(resources).forEach(([categoryName, category]) => {
            Object.entries(category).forEach(([name, url]) => {
                setTimeout(() => {
                    loadCSS(url, onResourceLoaded);
                }, Math.random() * 200); // 스태거링
            });
        });
    });
    
    // 폴백 처리
    setTimeout(function() {
        if (!document.body.classList.contains('css-fully-loaded')) {
            console.warn('⚠️ CSS loading timeout, enabling fallback');
            document.body.classList.add('css-fallback-mode');
        }
    }, 5000);
})();
</script>
        <?php
    }
    
    /**
     * 성능 추적기
     */
    private function renderPerformanceTracker() {
        ?>
<script id="hopec-performance-tracker">
(function() {
    if (!performance.mark) return;
    
    const metrics = {
        start: performance.now(),
        criticalCSSSize: document.getElementById('hopec-critical-css')?.textContent.length || 0,
        events: []
    };
    
    // 이벤트 추적
    ['DOMContentLoaded', 'load'].forEach(event => {
        window.addEventListener(event, function() {
            metrics.events.push({
                event: event,
                time: performance.now() - metrics.start
            });
        });
    });
    
    // 결과 로깅
    window.addEventListener('load', function() {
        setTimeout(() => {
            metrics.end = performance.now();
            metrics.total = metrics.end - metrics.start;
            
            console.log('📊 CSS Performance Metrics:', metrics);
            
            // 서버로 전송 (선택적)
            if (window.HOPEC_ANALYTICS_ENABLED) {
                fetch('/api/performance-metrics.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(metrics)
                }).catch(() => {}); // 무시
            }
        }, 1000);
    });
})();
</script>
        <?php
    }
    
    /**
     * CSS 압축
     */
    private function minifyCSS($css) {
        // 주석 제거
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // 불필요한 공백 제거
        $css = preg_replace('/\s+/', ' ', $css);
        
        // 블록 주변 공백 정리
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/;\s*}/', '}', $css);
        $css = preg_replace('/;\s*/', ';', $css);
        
        return trim($css);
    }
}

/**
 * CSS 캐시 관리
 */
class CSSCache {
    private $cacheDir;
    
    public function __construct($config) {
        $this->cacheDir = $config['cache_dir'] ?? (__DIR__ . '/../cache/css/');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public function get($key) {
        $file = $this->cacheDir . md5($key) . '.css';
        if (file_exists($file) && (time() - filemtime($file)) < 3600) {
            return file_get_contents($file);
        }
        return false;
    }
    
    public function set($key, $content, $ttl = 3600) {
        $file = $this->cacheDir . md5($key) . '.css';
        file_put_contents($file, $content);
        touch($file, time());
    }
}

/**
 * 성능 추적기
 */
class CSSPerformanceTracker {
    private $metrics = [];
    
    public function start($operation) {
        $this->metrics[$operation] = ['start' => microtime(true)];
    }
    
    public function end($operation) {
        if (isset($this->metrics[$operation])) {
            $this->metrics[$operation]['end'] = microtime(true);
            $this->metrics[$operation]['duration'] = 
                $this->metrics[$operation]['end'] - $this->metrics[$operation]['start'];
        }
    }
    
    public function getMetrics() {
        return $this->metrics;
    }
}

// 전역 인스턴스
if (!isset($GLOBALS['unifiedCSSLoader'])) {
    $GLOBALS['unifiedCSSLoader'] = new UnifiedCSSLoader([
        'cache_enabled' => !defined('HOPEC_DEBUG') || !HOPEC_DEBUG,
        'performance_tracking' => defined('HOPEC_DEBUG') && HOPEC_DEBUG
    ]);
}

// 헬퍼 함수
function loadUnifiedCSS($pageType = 'default', $theme = 'natural-green') {
    return $GLOBALS['unifiedCSSLoader']->loadCSS($pageType, $theme);
}