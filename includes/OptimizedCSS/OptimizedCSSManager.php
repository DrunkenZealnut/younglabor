<?php
/**
 * 최적화된 CSS 로딩 시스템
 * 기존 NaturalGreenThemeLoader와 완전 분리된 병렬 시스템
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

class OptimizedCSSManager {
    private $config;
    private $criticalCSS = '';
    private $cssAssets = [];
    private $performanceMetrics = [];
    private $fallbackMode = false;
    
    public function __construct() {
        $this->config = [
            'enabled' => defined('OPTIMIZED_CSS_ENABLED') ? OPTIMIZED_CSS_ENABLED : false,
            'critical_size_limit' => 7000, // 7KB
            'performance_threshold' => 4000, // 4초
            'cache_duration' => 86400, // 1일
            'fallback_on_error' => true
        ];
        
        $this->initPerformanceMonitoring();
    }
    
    /**
     * CSS 시스템 활성화 여부 확인
     */
    public function isEnabled() {
        return $this->config['enabled'] && !$this->fallbackMode;
    }
    
    /**
     * Critical CSS 추가 - 실제 파일 생성 없이 인라인 처리
     */
    public function addCriticalCSS($css, $component = 'default') {
        // 모든 CSS를 Critical CSS로 처리하여 별도 파일 요청 없이 인라인화
        $this->criticalCSS .= "\n/* {$component} */\n" . $css;
        return true;
    }
    
    /**
     * 비동기 CSS 추가
     */
    public function addAsyncCSS($href, $media = 'all', $priority = 'normal') {
        $this->cssAssets[] = [
            'href' => $href,
            'media' => $media,
            'priority' => $priority,
            'version' => $this->getVersionHash($href)
        ];
    }
    
    /**
     * 메인 렌더링 함수
     */
    public function render() {
        if (!$this->isEnabled()) {
            $this->renderFallback();
            return;
        }
        
        echo "<!-- Optimized CSS Loading System v1.0 -->\n";
        
        // 성능 모니터링 시작
        $this->renderPerformanceStart();
        
        // Critical CSS 인라인 렌더링
        if (!empty($this->criticalCSS)) {
            $this->renderCriticalCSS();
        }
        
        // 비동기 CSS 로드
        $this->renderAsyncCSS();
        
        // 폴백 감지 스크립트
        $this->renderFallbackDetector();
        
        // 성능 모니터링 완료
        $this->renderPerformanceEnd();
    }
    
    /**
     * Critical CSS 인라인 렌더링
     */
    private function renderCriticalCSS() {
        echo "<style id=\"critical-css\" data-size=\"" . strlen($this->criticalCSS) . "\">\n";
        echo $this->criticalCSS;
        echo "\n</style>\n";
    }
    
    /**
     * 추가 CSS 인라인 렌더링 - 외부 파일 요청 없이 모든 스타일 인라인 처리
     */
    private function renderAsyncCSS() {
        // main.css 내용을 직접 인라인으로 포함
        $mainCSSPath = __DIR__ . '/../../css/optimized/main.css';
        if (file_exists($mainCSSPath)) {
            $mainCSS = file_get_contents($mainCSSPath);
            echo "<style id=\"main-css\" data-type=\"inlined-main\">\n";
            echo $mainCSS;
            echo "\n</style>\n";
        }
    }
    
    /**
     * 폴백 감지 및 자동 전환
     */
    private function renderFallbackDetector() {
        echo "<script>\n";
        echo "(function() {\n";
        echo "    var fallbackTimer = setTimeout(function() {\n";
        echo "        if (!document.querySelector('#critical-css')) {\n";
        echo "            window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'css_fallback=1';\n";
        echo "        }\n";
        echo "    }, {$this->config['performance_threshold']});\n";
        echo "    \n";
        echo "    document.addEventListener('DOMContentLoaded', function() {\n";
        echo "        clearTimeout(fallbackTimer);\n";
        echo "        window.CSS_OPTIMIZED = true;\n";
        echo "    });\n";
        echo "})();\n";
        echo "</script>\n";
    }
    
    /**
     * 성능 모니터링 시작
     */
    private function renderPerformanceStart() {
        echo "<script>\n";
        echo "window.CSS_PERF_START = performance.now();\n";
        echo "window.CSS_METRICS = {critical: 0, async: 0, total: 0};\n";
        echo "</script>\n";
    }
    
    /**
     * 성능 모니터링 완료
     */
    private function renderPerformanceEnd() {
        echo "<script>\n";
        echo "document.addEventListener('DOMContentLoaded', function() {\n";
        echo "    window.CSS_METRICS.total = performance.now() - window.CSS_PERF_START;\n";
        echo "    \n";
        echo "    // 성능 지표 서버 전송\n";
        echo "    if (navigator.sendBeacon && window.CSS_METRICS.total > 0) {\n";
        echo "        navigator.sendBeacon('/api/css-performance', JSON.stringify({\n";
        echo "            metrics: window.CSS_METRICS,\n";
        echo "            url: window.location.pathname,\n";
        echo "            timestamp: Date.now()\n";
        echo "        }));\n";
        echo "    }\n";
        echo "});\n";
        echo "</script>\n";
    }
    
    /**
     * 폴백 모드 렌더링 (기존 시스템 호출)
     */
    private function renderFallback() {
        echo "<!-- CSS Fallback: Using Legacy System -->\n";
        if (function_exists('renderNaturalGreenTheme')) {
            renderNaturalGreenTheme();
        }
    }
    
    /**
     * 버전 해시 생성
     */
    private function getVersionHash($filepath) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filepath;
        if (file_exists($fullPath)) {
            return substr(md5_file($fullPath), 0, 8);
        }
        return time();
    }
    
    /**
     * 성능 모니터링 초기화
     */
    private function initPerformanceMonitoring() {
        // 폴백 모드 감지
        if (isset($_GET['css_fallback']) && $_GET['css_fallback'] === '1') {
            $this->fallbackMode = true;
        }
    }
    
    /**
     * 디버그 정보 출력
     */
    public function getDebugInfo() {
        return [
            'enabled' => $this->isEnabled(),
            'fallback_mode' => $this->fallbackMode,
            'critical_css_size' => strlen($this->criticalCSS),
            'async_assets_count' => count($this->cssAssets),
            'config' => $this->config
        ];
    }
}

/**
 * 전역 함수들
 */
if (!function_exists('getOptimizedCSSManager')) {
    function getOptimizedCSSManager() {
        static $manager = null;
        if ($manager === null) {
            $manager = new OptimizedCSSManager();
        }
        return $manager;
    }
}

if (!function_exists('renderOptimizedCSS')) {
    function renderOptimizedCSS() {
        $manager = getOptimizedCSSManager();
        $manager->render();
    }
}