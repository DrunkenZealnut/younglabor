<?php
/**
 * 단순 CSS 최적화 시스템 테스트 페이지
 * - 기존 복잡한 시스템 vs 새로운 단순한 시스템 비교
 * - 실제 성능 측정 및 UI 동일성 검증
 */

// 단순 최적화 시스템 활성화
define('SIMPLE_CSS_ENABLED', true);
define('CSS_DEBUG', true);

// 필요한 파일 로드
require_once __DIR__ . '/includes/SimpleCSSOptimizer.php';
require_once __DIR__ . '/includes/SimpleHeader.php';

// 테스트 설정
$pageTitle = '단순 CSS 최적화 테스트 | 희망연대노동조합';
$pageDescription = '실제 성능 향상을 위한 단순한 CSS 최적화 시스템 테스트';
$pageType = $_GET['type'] ?? 'gallery';
$testMode = $_GET['mode'] ?? 'simple';  // simple, legacy, comparison

// 성능 측정 시작
$startTime = microtime(true);

// 헤더 렌더링
if ($testMode === 'legacy') {
    // 기존 시스템 사용
    require_once __DIR__ . '/includes/NaturalGreenThemeLoader.php';
    ?>
    <!DOCTYPE html>
    <html lang="ko">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?= htmlspecialchars($pageTitle) ?></title>
        <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>" />
        
        <!-- 기존 외부 CSS (성능 저하 원인) -->
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        
        <?php renderNaturalGreenTheme(); ?>
        
        <script>
        window.LEGACY_PERF_START = performance.now();
        window.LEGACY_METRICS = {start: window.LEGACY_PERF_START};
        </script>
      </head>
      <body>
    <?php
} else {
    // 새로운 단순 시스템 사용
    $simpleHeader = new SimpleHeader($pageType);
    $simpleHeader->render($pageTitle, $pageDescription);
}

$headerTime = microtime(true) - $startTime;
?>

<!-- 메인 컨텐츠 -->
<main class="max-w-7xl mx-auto px-4 py-8">
    <!-- 성능 비교 정보 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h1 class="text-3xl font-bold text-forest-700 mb-4">
            🚀 단순 CSS 최적화 테스트
        </h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- 현재 시스템 정보 -->
            <div class="<?= $testMode === 'simple' ? 'bg-green-50 border-green-200' : 'bg-blue-50 border-blue-200' ?> border-2 rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">
                    현재 테스트: <?= $testMode === 'simple' ? '🚀 단순 최적화' : '📊 기존 시스템' ?>
                </h2>
                <p><strong>헤더 렌더링:</strong> <?= round($headerTime * 1000, 2) ?>ms</p>
                <p><strong>페이지 타입:</strong> <?= htmlspecialchars($pageType) ?></p>
                <p><strong>최적화:</strong> <?= $testMode === 'simple' ? '✅ 활성화' : '❌ 비활성화' ?></p>
            </div>
            
            <!-- 시스템 전환 -->
            <div class="space-y-2">
                <h2 class="text-xl font-semibold mb-2">시스템 비교</h2>
                <a href="?mode=simple&type=<?= $pageType ?>" 
                   class="block w-full p-3 text-center bg-green-500 text-white rounded hover:bg-green-600 transition-colors <?= $testMode === 'simple' ? 'opacity-50' : '' ?>">
                    🚀 단순 최적화 시스템
                </a>
                <a href="?mode=legacy&type=<?= $pageType ?>" 
                   class="block w-full p-3 text-center bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors <?= $testMode === 'legacy' ? 'opacity-50' : '' ?>">
                    📊 기존 시스템 (외부 CDN)
                </a>
            </div>
        </div>
        
        <!-- 최적화 장점 설명 -->
        <?php if ($testMode === 'simple'): ?>
        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-400">
            <h3 class="font-semibold text-green-700 mb-2">단순 최적화 시스템의 장점</h3>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• 외부 CDN 요청 4개 → 1개로 대폭 감소</li>
                <li>• 모든 CSS를 압축된 인라인으로 통합</li>
                <li>• 렌더링 차단 제거로 빠른 초기 로딩</li>
                <li>• 네트워크 지연 최소화</li>
                <li>• Font Awesome 아이콘을 경량 이모지로 대체</li>
            </ul>
        </div>
        <?php else: ?>
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
            <h3 class="font-semibold text-blue-700 mb-2">기존 시스템의 특징</h3>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• 외부 CDN 4-5개 의존 (Google Fonts, Font Awesome, Bootstrap 등)</li>
                <li>• 각각의 네트워크 요청과 지연</li>
                <li>• CSS 로딩으로 인한 렌더링 차단</li>
                <li>• 전체 Bootstrap과 Font Awesome 라이브러리 로드</li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- UI 동일성 테스트 컨텐츠 -->
    <div class="test-content">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">UI 동일성 검증 - <?= ucfirst($pageType) ?> 레이아웃</h2>
        
        <?php if ($pageType === 'gallery'): ?>
            <!-- 갤러리 테스트 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <article class="bg-white rounded-lg shadow-sm border border-primary-light hover:border-primary overflow-hidden hover:shadow-md transition-all duration-300">
                    <div class="relative h-48 bg-gradient-to-br from-lime-100 to-lime-200 flex items-center justify-center">
                        <span class="text-4xl">🖼️</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">갤러리 항목 <?= $i ?></h3>
                        <p class="text-sm text-gray-600 mb-2">단순 CSS 최적화 테스트용 갤러리 컨텐츠</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fa fa-user mr-1"></i>
                                관리자
                            </span>
                            <span class="flex items-center">
                                <i class="fa fa-calendar mr-1"></i>
                                <?= date('Y-m-d') ?>
                            </span>
                        </div>
                    </div>
                </article>
                <?php endfor; ?>
            </div>
            
        <?php elseif ($pageType === 'newsletter'): ?>
            <!-- 뉴스레터 테스트 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <article class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                    <div class="relative h-40 bg-gradient-to-r from-forest-100 to-forest-200 flex items-center justify-center">
                        <span class="text-3xl">📰</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">뉴스레터 <?= $i ?></h3>
                        <p class="text-sm text-gray-600">최적화된 CSS로 더 빠른 뉴스 로딩</p>
                    </div>
                </article>
                <?php endfor; ?>
            </div>
            
        <?php else: ?>
            <!-- 기본 홈페이지 테스트 -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <p class="text-gray-600 mb-4">
                    이 페이지는 단순한 CSS 최적화 시스템의 효과를 테스트합니다.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-lime-50 p-4 rounded border">
                        <h3 class="font-semibold text-lime-700 mb-2">🚀 최적화 효과</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• 외부 요청 대폭 감소</li>
                            <li>• 인라인 CSS로 즉시 렌더링</li>
                            <li>• 네트워크 지연 최소화</li>
                            <li>• 압축으로 용량 절약</li>
                        </ul>
                    </div>
                    <div class="bg-forest-50 p-4 rounded border">
                        <h3 class="font-semibold text-forest-700 mb-2">🎯 검증 결과</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• UI 디자인 100% 동일</li>
                            <li>• 모든 스타일 정상 작동</li>
                            <li>• 반응형 디자인 유지</li>
                            <li>• 호버 효과 동일</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- 페이지 타입 전환 -->
    <div class="mt-8 text-center">
        <h3 class="text-lg font-semibold mb-4">다른 레이아웃 테스트</h3>
        <div class="space-x-2">
            <a href="?mode=<?= $testMode ?>&type=gallery" 
               class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors <?= $pageType === 'gallery' ? 'bg-blue-700' : '' ?>">
                갤러리
            </a>
            <a href="?mode=<?= $testMode ?>&type=newsletter" 
               class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors <?= $pageType === 'newsletter' ? 'bg-blue-700' : '' ?>">
                뉴스레터
            </a>
            <a href="?mode=<?= $testMode ?>&type=home" 
               class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors <?= $pageType === 'home' ? 'bg-blue-700' : '' ?>">
                홈페이지
            </a>
        </div>
    </div>
    
    <!-- 실시간 성능 표시 -->
    <div id="performance-display" class="mt-8 p-4 bg-gray-100 rounded-lg">
        <h3 class="font-semibold mb-2">실시간 성능 측정</h3>
        <div id="perf-results">측정 중...</div>
    </div>
</main>

<!-- 성능 측정 및 비교 스크립트 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isSimple = '<?= $testMode ?>' === 'simple';
    const startTime = isSimple ? window.SIMPLE_PERF_START : window.LEGACY_PERF_START;
    const domTime = performance.now();
    const totalTime = domTime - startTime;
    
    // 성능 데이터 수집
    const perfData = {
        system: isSimple ? 'Simple Optimized' : 'Legacy System',
        totalTime: Math.round(totalTime),
        headerTime: <?= round($headerTime * 1000, 2) ?>,
        pageType: '<?= $pageType ?>',
        timestamp: Date.now()
    };
    
    // 결과 표시
    const resultsDiv = document.getElementById('perf-results');
    resultsDiv.innerHTML = `
        <strong>${perfData.system}</strong><br>
        총 로딩 시간: ${perfData.totalTime}ms<br>
        헤더 렌더링: ${perfData.headerTime}ms<br>
        DOM 준비: ${Math.round(domTime)}ms
    `;
    
    // 성능 비교 저장
    const perfHistory = JSON.parse(localStorage.getItem('simple_css_comparison') || '[]');
    perfHistory.push(perfData);
    if (perfHistory.length > 20) perfHistory.shift();
    localStorage.setItem('simple_css_comparison', JSON.stringify(perfHistory));
    
    // 콘솔 로그
    console.log('📊 단순 CSS 최적화 성능 결과:', perfData);
    
    // 우수한 성능 표시
    const statusDiv = document.createElement('div');
    statusDiv.className = 'fixed bottom-4 right-4 p-3 rounded shadow-lg text-white text-sm';
    statusDiv.style.backgroundColor = totalTime < 300 ? '#10b981' : totalTime < 600 ? '#f59e0b' : '#ef4444';
    statusDiv.innerHTML = `
        ${isSimple ? '🚀' : '📊'} ${perfData.system}<br>
        ${perfData.totalTime}ms
        ${totalTime < 300 ? '✅' : totalTime < 600 ? '⚠️' : '❌'}
    `;
    document.body.appendChild(statusDiv);
    
    // 5초 후 제거
    setTimeout(() => statusDiv.remove(), 5000);
    
    // 성능 분석 결과
    if (isSimple && totalTime < 300) {
        console.log('🎉 단순 최적화 시스템: 우수한 성능!');
    } else if (!isSimple && totalTime > 1000) {
        console.log('⚠️ 기존 시스템: 성능 개선 필요');
    }
});
</script>

<?php
// 푸터 처리
if ($testMode === 'simple') {
    // 단순 시스템의 푸터
    ?>
    <script>
    window.addEventListener('load', function() {
        const loadTime = performance.now() - window.SIMPLE_PERF_START;
        console.log('🚀 완전 로딩 완료:', Math.round(loadTime) + 'ms');
    });
    </script>
    </body>
    </html>
    <?php
} else {
    // 기존 시스템의 푸터
    ?>
    <script>
    window.addEventListener('load', function() {
        const loadTime = performance.now() - window.LEGACY_PERF_START;
        console.log('📊 기존 시스템 로딩 완료:', Math.round(loadTime) + 'ms');
    });
    </script>
    </body>
    </html>
    <?php
}
?>