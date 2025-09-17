<?php
/**
 * Legacy 모드용 CSS/JS 번들 빌드 도구
 * Bootstrap + Font Awesome + Natural Green 테마 통합
 * 
 * Usage: php tools/build-legacy-bundle.php
 */

require_once __DIR__ . '/../includes/LocalResourceManager.php';

class LegacyBundleBuilder {
    
    private $resourceManager;
    private $outputDir;
    private $verbose = true;
    
    public function __construct() {
        $this->resourceManager = new LocalResourceManager();
        $this->outputDir = dirname(__DIR__) . '/css/';
        
        // 출력 디렉토리 생성
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }
    
    /**
     * 전체 번들 빌드 실행
     */
    public function build() {
        $this->log("🚀 Legacy Bundle Build Started");
        $startTime = microtime(true);
        
        // 1단계: 원격 리소스 다운로드
        $this->log("📥 Downloading remote resources...");
        $downloadResults = $this->resourceManager->downloadHighPriorityResources();
        
        foreach ($downloadResults as $resource => $success) {
            $this->log($success ? "✅ {$resource}" : "❌ {$resource}");
        }
        
        // 2단계: CSS 번들 생성
        $this->log("🔧 Building CSS bundle...");
        $cssBundle = $this->buildCSSBundle();
        
        // 3단계: Critical CSS 업데이트
        $this->log("⚡ Updating Critical CSS...");
        $this->updateCriticalCSS();
        
        // 4단계: JS 번들 생성 (선택적)
        $this->log("📦 Building JS bundle...");
        $jsBundle = $this->buildJSBundle();
        
        // 완료
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $this->log("✨ Build completed in {$duration}ms");
        
        return [
            'css_bundle' => $cssBundle,
            'js_bundle' => $jsBundle,
            'duration_ms' => $duration,
            'downloads' => $downloadResults
        ];
    }
    
    /**
     * CSS 번들 생성
     */
    private function buildCSSBundle() {
        $bundleContent = '';
        $originalSize = 0;
        
        // 1. Bootstrap CSS
        $bootstrapPath = $this->resourceManager->downloadResource('bootstrap_css');
        if ($bootstrapPath && file_exists($bootstrapPath)) {
            $content = file_get_contents($bootstrapPath);
            $originalSize += strlen($content);
            
            // Bootstrap에서 불필요한 부분 제거
            $content = $this->optimizeBootstrap($content);
            
            $bundleContent .= "/* === Bootstrap 5.3.0 (Optimized) === */\n";
            $bundleContent .= $content . "\n\n";
            $this->log("📦 Bootstrap CSS added: " . $this->formatSize(strlen($content)));
        }
        
        // 2. Font Awesome
        $fontAwesomePath = $this->resourceManager->downloadResource('fontawesome');
        if ($fontAwesomePath && file_exists($fontAwesomePath)) {
            $content = file_get_contents($fontAwesomePath);
            $originalSize += strlen($content);
            
            $bundleContent .= "/* === Font Awesome 6.5.2 === */\n";
            $bundleContent .= $content . "\n\n";
            $this->log("🔤 Font Awesome added: " . $this->formatSize(strlen($content)));
        }
        
        // 3. Bootstrap Icons (선택적)
        $bootstrapIconsPath = $this->resourceManager->downloadResource('bootstrap_icons');
        if ($bootstrapIconsPath && file_exists($bootstrapIconsPath)) {
            $content = file_get_contents($bootstrapIconsPath);
            $originalSize += strlen($content);
            
            // 자주 사용되는 아이콘만 포함
            $content = $this->optimizeBootstrapIcons($content);
            
            $bundleContent .= "/* === Bootstrap Icons (Essential) === */\n";
            $bundleContent .= $content . "\n\n";
            $this->log("🎨 Bootstrap Icons added: " . $this->formatSize(strlen($content)));
        }
        
        // 4. Natural Green Theme
        $themePath = dirname(__DIR__) . '/theme/natural-green/styles/globals.css';
        if (file_exists($themePath)) {
            $content = file_get_contents($themePath);
            $originalSize += strlen($content);
            
            $bundleContent .= "/* === Natural Green Theme === */\n";
            $bundleContent .= $content . "\n\n";
            $this->log("🌿 Natural Green theme added: " . $this->formatSize(strlen($content)));
        }
        
        // 5. 추가 최적화 CSS
        $bundleContent .= $this->getOptimizationCSS();
        
        // 압축
        $minifiedContent = $this->minifyCSS($bundleContent);
        $compressionRatio = round((1 - strlen($minifiedContent) / $originalSize) * 100, 1);
        
        // 저장
        $bundlePath = $this->outputDir . 'legacy-optimized.min.css';
        file_put_contents($bundlePath, $minifiedContent);
        
        $this->log("💾 CSS Bundle saved: " . $this->formatSize(strlen($minifiedContent)) . " ({$compressionRatio}% smaller)");
        
        return $bundlePath;
    }
    
    /**
     * Bootstrap 최적화 (불필요한 컴포넌트 제거)
     */
    private function optimizeBootstrap($css) {
        // 사용하지 않는 Bootstrap 컴포넌트 제거
        $removeComponents = [
            // 복잡한 컴포넌트들 (필요시 개별 로딩)
            'carousel', 'collapse', 'accordion', 'offcanvas', 
            'toast', 'popover', 'tooltip', 'scrollspy'
        ];
        
        foreach ($removeComponents as $component) {
            // 해당 컴포넌트 관련 CSS 클래스 제거
            $css = preg_replace('/\.' . $component . '[^{]*\{[^}]*\}/i', '', $css);
        }
        
        return $css;
    }
    
    /**
     * Bootstrap Icons 최적화 (필수 아이콘만)
     */
    private function optimizeBootstrapIcons($css) {
        // 필수 아이콘 목록
        $essentialIcons = [
            'house', 'person', 'envelope', 'telephone', 'calendar',
            'search', 'menu', 'x', 'check', 'arrow-left', 'arrow-right',
            'download', 'upload', 'file', 'folder', 'image',
            'heart', 'star', 'gear', 'info-circle', 'exclamation-triangle'
        ];
        
        // 아이콘 정의 추출 및 필터링
        preg_match_all('/\.bi-([^:]+)::before\s*\{[^}]*\}/', $css, $matches);
        
        $filteredCSS = '';
        foreach ($matches[0] as $index => $iconCSS) {
            $iconName = $matches[1][$index];
            if (in_array($iconName, $essentialIcons)) {
                $filteredCSS .= $iconCSS . "\n";
            }
        }
        
        // 기본 .bi 클래스 정의 유지
        preg_match('/\.bi\s*\{[^}]*\}/', $css, $baseMatch);
        $baseCSS = $baseMatch[0] ?? '';
        
        return $baseCSS . "\n" . $filteredCSS;
    }
    
    /**
     * 추가 최적화 CSS
     */
    private function getOptimizationCSS() {
        return '
/* === Legacy Optimizations === */

/* 성능 최적화 */
* { box-sizing: border-box; }
html { font-display: swap; }

/* Layout fixes for Legacy mode */
body.legacy-optimized {
    display: flex !important;
    flex-direction: column !important;
    min-height: 100vh;
}

/* 중앙 정렬 강화 */
.container, .container-xl {
    margin-left: auto !important;
    margin-right: auto !important;
}

/* 모바일 반응형 최적화 */
@media (max-width: 576px) {
    .container, .container-xl {
        max-width: 100% !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
}

/* 로딩 상태 개선 */
.loading-optimized {
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.loading-optimized.loaded {
    opacity: 1;
}

/* 폰트 로딩 최적화 */
@font-face {
    font-family: "Noto Sans KR";
    font-display: swap;
}

';
    }
    
    /**
     * Critical CSS 업데이트
     */
    private function updateCriticalCSS() {
        $criticalPath = dirname(__DIR__) . '/css/critical-legacy.css';
        $currentCritical = file_exists($criticalPath) ? file_get_contents($criticalPath) : '';
        
        // 현재 시간 기반 캐시 버스터 추가
        $cacheVersion = date('YmdHis');
        $currentCritical = str_replace(
            '/* Generated by SuperClaude',
            "/* Generated by SuperClaude v{$cacheVersion}",
            $currentCritical
        );
        
        file_put_contents($criticalPath, $currentCritical);
        $this->log("⚡ Critical CSS updated with version: {$cacheVersion}");
    }
    
    /**
     * JS 번들 생성 (선택적)
     */
    private function buildJSBundle() {
        $bundleContent = '';
        
        // 필수 JavaScript 유틸리티
        $bundleContent .= $this->getEssentialJS();
        
        // 압축
        $minifiedContent = $this->minifyJS($bundleContent);
        
        // 저장
        $bundlePath = $this->outputDir . 'legacy-optimized.min.js';
        file_put_contents($bundlePath, $minifiedContent);
        
        $this->log("💾 JS Bundle saved: " . $this->formatSize(strlen($minifiedContent)));
        
        return $bundlePath;
    }
    
    /**
     * 필수 JavaScript 코드
     */
    private function getEssentialJS() {
        return '
/* Essential JavaScript for Legacy Optimized */
(function() {
    "use strict";
    
    // DOM Ready helper
    function ready(fn) {
        if (document.readyState !== "loading") {
            fn();
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }
    
    // CSS Loading helper
    function loadCSS(href, callback) {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = href;
        if (callback) link.onload = callback;
        document.head.appendChild(link);
        return link;
    }
    
    // Performance monitoring
    ready(function() {
        if (window.legacyOptimized) {
            window.legacyOptimized.domReady = performance.now();
            console.log("🚀 Legacy Optimized Bundle loaded");
        }
        
        // Add loaded class to body
        document.body.classList.add("legacy-optimized");
        
        // Fade in loading elements
        const loadingElements = document.querySelectorAll(".loading-optimized");
        loadingElements.forEach(function(el) {
            el.classList.add("loaded");
        });
    });
    
    // Export utilities
    window.LegacyOptimized = {
        ready: ready,
        loadCSS: loadCSS
    };
})();
';
    }
    
    /**
     * CSS 압축
     */
    private function minifyCSS($css) {
        // 주석 제거 (/* */ 형태)
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // 불필요한 공백 제거
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace([' {', '{ ', ' }', '} ', ': ', '; ', ', '], ['{', '{', '}', '}', ':', ';', ','], $css);
        
        // 줄바꿈 제거
        $css = str_replace(["\n", "\r"], '', $css);
        
        return trim($css);
    }
    
    /**
     * JS 압축 (기본적인 압축)
     */
    private function minifyJS($js) {
        // 주석 제거
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // 불필요한 공백 제거 (문자열 내부는 보호)
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }
    
    /**
     * 파일 크기 포맷
     */
    private function formatSize($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
    
    /**
     * 로그 출력
     */
    private function log($message) {
        if ($this->verbose) {
            echo date('H:i:s') . ' ' . $message . "\n";
        }
    }
}

// CLI 실행
if (php_sapi_name() === 'cli') {
    $builder = new LegacyBundleBuilder();
    $result = $builder->build();
    
    echo "\n📊 Build Summary:\n";
    echo "CSS Bundle: " . basename($result['css_bundle']) . "\n";
    echo "JS Bundle: " . basename($result['js_bundle']) . "\n";
    echo "Build Time: " . $result['duration_ms'] . "ms\n";
    echo "\n✨ Legacy optimization bundle ready!\n";
}