<?php
/**
 * 단순 최적화 헤더 시스템
 * - 외부 CDN 최소화로 네트워크 지연 제거
 * - 인라인 CSS로 렌더링 차단 제거
 * - 실제 성능 향상에 집중
 */

require_once __DIR__ . '/SimpleCSSOptimizer.php';

class SimpleHeader {
    private $pageType;
    private $optimizer;
    
    public function __construct($pageType = 'home') {
        $this->pageType = $pageType;
        $this->optimizer = new SimpleCSSOptimizer();
    }
    
    /**
     * 헤더 렌더링
     */
    public function render($title = '', $description = '') {
        ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?: '희망연대노동조합') ?></title>
    <meta name="description" content="<?= htmlspecialchars($description ?: '노동자의 권익을 위한 희망연대노동조합') ?>">
    
    <!-- DNS 사전 연결 (성능 최적화) -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.tailwindcss.com">
    
    <?php 
    // 최적화된 CSS 렌더링
    $this->optimizer->render(); 
    ?>
    
    <!-- 성능 측정 시작 -->
    <script>
    window.SIMPLE_PERF_START = performance.now();
    window.SIMPLE_METRICS = {start: window.SIMPLE_PERF_START};
    </script>
</head>
<body>
        <?php
    }
    
    /**
     * 푸터 렌더링 (성능 측정 포함)
     */
    public function renderFooter() {
        ?>
<!-- 성능 측정 완료 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.SIMPLE_METRICS.domReady = performance.now();
    window.SIMPLE_METRICS.totalTime = window.SIMPLE_METRICS.domReady - window.SIMPLE_METRICS.start;
    
    console.log('🚀 Simple CSS Optimizer 성능:', {
        '총 시간': Math.round(window.SIMPLE_METRICS.totalTime) + 'ms',
        'DOM Ready': Math.round(window.SIMPLE_METRICS.domReady) + 'ms',
        '시스템': 'Simple Optimized'
    });
    
    // 간단한 성능 표시
    if (window.SIMPLE_METRICS.totalTime < 500) {
        console.log('✅ 성능 우수: ' + Math.round(window.SIMPLE_METRICS.totalTime) + 'ms');
    }
});
</script>
</body>
</html>
        <?php
    }
    
    /**
     * 디버그 정보 출력
     */
    public function renderDebugInfo() {
        if (defined('CSS_DEBUG') && CSS_DEBUG) {
            $debugInfo = $this->optimizer->getDebugInfo();
            echo "<!-- Simple CSS Debug Info: " . json_encode($debugInfo) . " -->\n";
        }
    }
}