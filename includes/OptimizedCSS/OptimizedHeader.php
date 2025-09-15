<?php
/**
 * 최적화된 헤더 렌더링 시스템
 * 기존 header.php와 완전 분리된 최적화 버전
 */

class OptimizedHeader {
    private $cssManager;
    private $extractor;
    private $pageType;
    private $config;
    
    public function __construct($pageType = 'default') {
        // 의존성 로드
        require_once __DIR__ . '/OptimizedCSSManager.php';
        require_once __DIR__ . '/CriticalCSSExtractor.php';
        
        $this->cssManager = new OptimizedCSSManager();
        $this->extractor = new CriticalCSSExtractor($this->cssManager);
        $this->pageType = $pageType;
        
        $this->config = [
            'site_name' => '희망연대노동조합',
            'site_description' => '이웃과 함께하는 노동권 보호',
            'google_fonts' => 'Noto+Sans+KR:wght@300;400;500;700',
            'enable_prefetch' => true
        ];
    }
    
    /**
     * 완전한 HTML 헤더 렌더링
     */
    public function render($pageTitle = '', $pageDescription = '') {
        if (!$this->cssManager->isEnabled()) {
            return $this->renderFallbackNotice();
        }
        
        // 기본값 설정
        $pageTitle = $pageTitle ?: $this->config['site_name'];
        $pageDescription = $pageDescription ?: $this->config['site_description'];
        
        // Critical CSS 추출 및 등록
        $this->setupOptimizedCSS();
        
        // 헤더 렌더링
        $this->renderHTMLHead($pageTitle, $pageDescription);
    }
    
    /**
     * 최적화된 CSS 설정
     */
    private function setupOptimizedCSS() {
        // Critical CSS 추출
        $this->extractor->extractAndRegister();
        
        // 페이지별 맞춤 CSS 추가
        $this->extractor->addPageSpecificCSS($this->pageType);
        
        // 비동기 CSS 등록
        $this->cssManager->addAsyncCSS('/css/optimized/main.css', 'all', 'high');
        $this->cssManager->addAsyncCSS('/css/optimized/vendor.css', 'all', 'normal');
        
        // 폰트 사전 로드
        if ($this->config['enable_prefetch']) {
            $this->addFontPreloads();
        }
    }
    
    /**
     * HTML Head 렌더링
     */
    private function renderHTMLHead($pageTitle, $pageDescription) {
        echo "<!DOCTYPE html>\n";
        echo "<html lang=\"ko\">\n";
        echo "<head>\n";
        
        // 메타 태그
        $this->renderMetaTags($pageTitle, $pageDescription);
        
        // DNS 사전 연결
        $this->renderDNSPrefetch();
        
        // 폰트 사전 로드
        $this->renderFontPreloads();
        
        // 최적화된 CSS 렌더링
        $this->cssManager->render();
        
        // 성능 향상 스크립트
        $this->renderPerformanceScripts();
        
        echo "</head>\n";
        echo "<body>\n";
        
        // 성능 측정 시작
        echo "<script>window.PERF_START = performance.now();</script>\n";
    }
    
    /**
     * 메타 태그 렌더링
     */
    private function renderMetaTags($pageTitle, $pageDescription) {
        $siteUrl = 'http://localhost:8012'; // 또는 환경변수에서 가져오기
        
        echo "    <meta charset=\"utf-8\">\n";
        echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
        echo "    <title>{$pageTitle}</title>\n";
        echo "    <meta name=\"description\" content=\"{$pageDescription}\">\n";
        echo "    <link rel=\"canonical\" href=\"{$siteUrl}/\">\n";
        
        // 최적화된 캐시 정책
        echo "    <meta http-equiv=\"Cache-Control\" content=\"public, max-age=86400\">\n";
        
        // Open Graph
        echo "    <meta property=\"og:title\" content=\"{$pageTitle}\">\n";
        echo "    <meta property=\"og:description\" content=\"{$pageDescription}\">\n";
        echo "    <meta property=\"og:locale\" content=\"ko_KR\">\n";
        
        // 성능 힌트
        echo "    <meta name=\"theme-color\" content=\"#84cc16\">\n";
    }
    
    /**
     * DNS 사전 연결
     */
    private function renderDNSPrefetch() {
        echo "    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
        echo "    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
        echo "    <link rel=\"dns-prefetch\" href=\"https://code.jquery.com\">\n";
    }
    
    /**
     * 폰트 사전 로드
     */
    private function renderFontPreloads() {
        echo "    <link rel=\"preload\" href=\"https://fonts.googleapis.com/css2?family={$this->config['google_fonts']}&display=swap\" as=\"style\">\n";
        echo "    <link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css2?family={$this->config['google_fonts']}&display=swap\" media=\"print\" onload=\"this.media='all'\">\n";
    }
    
    /**
     * 폰트 사전 로드 추가
     */
    private function addFontPreloads() {
        // Google Fonts를 비동기로 로드
        $this->cssManager->addAsyncCSS(
            "https://fonts.googleapis.com/css2?family={$this->config['google_fonts']}&display=swap",
            'all',
            'normal'
        );
    }
    
    /**
     * 성능 향상 스크립트
     */
    private function renderPerformanceScripts() {
        echo "    <script>\n";
        echo "        // 이미지 지연 로딩 폴리필\n";
        echo "        if ('loading' in HTMLImageElement.prototype) {\n";
        echo "            const images = document.querySelectorAll('img[loading=\"lazy\"]');\n";
        echo "            images.forEach(img => {\n";
        echo "                img.src = img.dataset.src;\n";
        echo "            });\n";
        echo "        }\n";
        echo "        \n";
        echo "        // CSS 로드 완료 감지\n";
        echo "        document.addEventListener('DOMContentLoaded', function() {\n";
        echo "            document.documentElement.classList.add('css-loaded');\n";
        echo "        });\n";
        echo "    </script>\n";
    }
    
    /**
     * 폴백 모드 안내
     */
    private function renderFallbackNotice() {
        echo "<!-- 최적화 CSS 시스템 비활성화됨 - 기존 시스템 사용 -->\n";
        return false;
    }
    
    /**
     * 디버그 정보 출력
     */
    public function renderDebugInfo() {
        if (defined('CSS_DEBUG') && CSS_DEBUG) {
            echo "<!-- CSS 최적화 시스템 디버그 정보 -->\n";
            echo "<!-- " . json_encode($this->cssManager->getDebugInfo(), JSON_PRETTY_PRINT) . " -->\n";
        }
    }
    
    /**
     * 성능 보고서 렌더링
     */
    public function renderPerformanceReport() {
        echo "<script>\n";
        echo "window.addEventListener('load', function() {\n";
        echo "    const perfData = {\n";
        echo "        loadTime: performance.now() - window.PERF_START,\n";
        echo "        cssOptimized: window.CSS_OPTIMIZED || false,\n";
        echo "        pageType: '{$this->pageType}',\n";
        echo "        timestamp: Date.now()\n";
        echo "    };\n";
        echo "    \n";
        echo "    console.log('🚀 페이지 성능:', perfData);\n";
        echo "    \n";
        echo "    // 성능 데이터 전송 (옵션)\n";
        echo "    if (navigator.sendBeacon) {\n";
        echo "        navigator.sendBeacon('/api/performance', JSON.stringify(perfData));\n";
        echo "    }\n";
        echo "});\n";
        echo "</script>\n";
    }
}

/**
 * 전역 함수
 */
if (!function_exists('renderOptimizedHeader')) {
    function renderOptimizedHeader($pageType = 'default', $pageTitle = '', $pageDescription = '') {
        $header = new OptimizedHeader($pageType);
        $header->render($pageTitle, $pageDescription);
        return $header;
    }
}