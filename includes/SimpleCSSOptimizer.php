<?php
/**
 * 단순하고 효과적인 CSS 최적화 시스템
 * - 외부 CDN 제거로 네트워크 지연 최소화
 * - 모든 CSS를 하나의 압축된 인라인 스타일로 통합
 * - 실제 성능 향상에 집중
 */

class SimpleCSSOptimizer {
    private $unifiedCSS = '';
    private $isEnabled = false;
    
    public function __construct() {
        $this->isEnabled = defined('SIMPLE_CSS_ENABLED') ? SIMPLE_CSS_ENABLED : false;
        if ($this->isEnabled) {
            $this->buildUnifiedCSS();
        }
    }
    
    /**
     * 모든 CSS를 하나로 통합 및 압축
     */
    private function buildUnifiedCSS() {
        $cssFiles = [
            __DIR__ . '/../css/theme.css'  // 현재 완성된 테마
        ];
        
        foreach ($cssFiles as $file) {
            if (file_exists($file)) {
                $css = file_get_contents($file);
                $this->unifiedCSS .= $this->minifyCSS($css);
            }
        }
        
        // 필수 외부 폰트만 유지 (구글 폰트는 성능상 필요)
        $this->addCriticalExternalFonts();
        
        // Font Awesome을 로컬 아이콘으로 대체
        $this->addLocalIcons();
        
        // Bootstrap 필수 부분만 추가
        $this->addMinimalBootstrap();
    }
    
    /**
     * CSS 압축 (공백, 주석 제거)
     */
    private function minifyCSS($css) {
        // 주석 제거
        $css = preg_replace('!/\*.*?\*/!s', '', $css);
        // 불필요한 공백 제거
        $css = preg_replace('/\s+/', ' ', $css);
        // 세미콜론 전후 공백 제거
        $css = str_replace(['; ', ' ;'], ';', $css);
        // 중괄호 전후 공백 제거
        $css = str_replace(['{ ', ' {', '} ', ' }'], ['{', '{', '}', '}'], $css);
        return trim($css);
    }
    
    /**
     * 필수 폰트만 추가 (최적화된 로딩)
     */
    private function addCriticalExternalFonts() {
        $fontCSS = '@import url("https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap");';
        $this->unifiedCSS = $fontCSS . $this->unifiedCSS;
    }
    
    /**
     * Font Awesome 대신 경량 로컬 아이콘
     */
    private function addLocalIcons() {
        $iconCSS = '
        .fa, .fas { font-style: normal; font-weight: 900; }
        .fa-user:before { content: "👤"; }
        .fa-calendar:before { content: "📅"; }
        .fa-home:before { content: "🏠"; }
        .fa-search:before { content: "🔍"; }
        .fa-menu:before { content: "☰"; }
        .fa-times:before { content: "✕"; }
        .fa-arrow-right:before { content: "→"; }
        .fa-arrow-left:before { content: "←"; }
        .fa-download:before { content: "⬇"; }
        .fa-upload:before { content: "⬆"; }
        .fa-edit:before { content: "✏"; }
        .fa-delete:before { content: "🗑"; }
        .fa-plus:before { content: "+"; }
        .fa-minus:before { content: "-"; }
        ';
        $this->unifiedCSS .= $this->minifyCSS($iconCSS);
    }
    
    /**
     * Bootstrap의 필수 유틸리티만 추가
     */
    private function addMinimalBootstrap() {
        $bootstrapCSS = '
        .container, .container-fluid { width: 100%; padding: 0 15px; margin: 0 auto; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .col, .col-1, .col-2, .col-3, .col-4, .col-6, .col-12 { flex: 1; padding: 0 15px; }
        .col-1 { flex: 0 0 8.33%; max-width: 8.33%; }
        .col-2 { flex: 0 0 16.66%; max-width: 16.66%; }
        .col-3 { flex: 0 0 25%; max-width: 25%; }
        .col-4 { flex: 0 0 33.33%; max-width: 33.33%; }
        .col-6 { flex: 0 0 50%; max-width: 50%; }
        .col-12 { flex: 0 0 100%; max-width: 100%; }
        .d-none { display: none !important; }
        .d-block { display: block !important; }
        .d-flex { display: flex !important; }
        .justify-content-center { justify-content: center !important; }
        .align-items-center { align-items: center !important; }
        .text-center { text-align: center !important; }
        .m-0 { margin: 0 !important; }
        .p-0 { padding: 0 !important; }
        .mt-2 { margin-top: 0.5rem !important; }
        .mb-2 { margin-bottom: 0.5rem !important; }
        .p-2 { padding: 0.5rem !important; }
        .p-3 { padding: 1rem !important; }
        .p-4 { padding: 1.5rem !important; }
        @media (max-width: 768px) {
            .col, .col-1, .col-2, .col-3, .col-4, .col-6, .col-12 { flex: 0 0 100%; max-width: 100%; }
        }
        ';
        $this->unifiedCSS .= $this->minifyCSS($bootstrapCSS);
    }
    
    /**
     * 최적화된 CSS 렌더링
     */
    public function render() {
        if (!$this->isEnabled) {
            $this->renderFallback();
            return;
        }
        
        echo "<!-- Simple CSS Optimizer v1.0 -->\n";
        echo "<style id=\"unified-css\">\n";
        echo $this->unifiedCSS;
        echo "\n</style>\n";
        
        // Tailwind CSS는 CDN으로 유지 (설정이 복잡하므로)
        echo '<script src="https://cdn.tailwindcss.com"></script>' . "\n";
    }
    
    /**
     * 폴백 모드 (기존 시스템)
     */
    private function renderFallback() {
        echo "<!-- Fallback: Legacy CSS System -->\n";
        if (function_exists('renderNaturalGreenTheme')) {
            renderNaturalGreenTheme();
        }
    }
    
    /**
     * 활성화 상태 확인
     */
    public function isEnabled() {
        return $this->isEnabled;
    }
    
    /**
     * 디버그 정보
     */
    public function getDebugInfo() {
        return [
            'enabled' => $this->isEnabled,
            'unified_css_size' => strlen($this->unifiedCSS) . ' bytes',
            'compression_ratio' => $this->calculateCompressionRatio(),
            'external_requests_eliminated' => 4  // FontAwesome, Bootstrap CSS, Bootstrap Icons, 기타
        ];
    }
    
    /**
     * 압축률 계산
     */
    private function calculateCompressionRatio() {
        if (file_exists(__DIR__ . '/../css/theme.css')) {
            $originalSize = filesize(__DIR__ . '/../css/theme.css');
            $compressedSize = strlen($this->unifiedCSS);
            return round((1 - $compressedSize / $originalSize) * 100, 1) . '%';
        }
        return 'N/A';
    }
}

/**
 * 전역 함수
 */
function renderSimpleOptimizedCSS() {
    static $optimizer = null;
    if ($optimizer === null) {
        $optimizer = new SimpleCSSOptimizer();
    }
    $optimizer->render();
}

function getSimpleCSSOptimizer() {
    static $optimizer = null;
    if ($optimizer === null) {
        $optimizer = new SimpleCSSOptimizer();
    }
    return $optimizer;
}