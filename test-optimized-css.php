<?php
/**
 * CSS 최적화 시스템 테스트 페이지
 * 양쪽 시스템을 비교 테스트할 수 있는 데모 페이지
 */

// 테스트 모드 강제 활성화
define('OPTIMIZED_CSS_ENABLED', true);
define('CSS_DEBUG', true);

// 최적화 시스템 로드
require_once __DIR__ . '/includes/OptimizedCSS/config.php';
require_once __DIR__ . '/includes/OptimizedCSS/OptimizedHeader.php';
require_once __DIR__ . '/includes/template_helpers.php';

// 테스트용 변수
$pageTitle = 'CSS 최적화 시스템 테스트 | 희망연대노동조합';
$pageDescription = 'CSS 로딩 최적화 테스트 - 기존 vs 최적화 시스템 성능 비교';
$pageType = isset($_GET['type']) ? $_GET['type'] : 'gallery';
$useOptimized = isset($_GET['optimized']) ? ($_GET['optimized'] === '1') : true;

// 성능 측정 시작
$startTime = microtime(true);

if ($useOptimized) {
    // 최적화된 헤더 사용
    $optimizedHeader = new OptimizedHeader($pageType);
    $optimizedHeader->render($pageTitle, $pageDescription);
} else {
    // 기존 헤더 시스템 사용 (간소화 버전)
    require_once __DIR__ . '/includes/NaturalGreenThemeLoader.php';
    $theme = getNaturalGreenTheme();
    ?>
    <!DOCTYPE html>
    <html lang="ko">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?= htmlspecialchars($pageTitle) ?></title>
        <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>" />
        
        <!-- 기존 외부 CSS -->
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        
        <?php renderNaturalGreenTheme(); ?>
        
        <script>window.CSS_LEGACY = true;</script>
      </head>
      <body>
    <?php
}

$headerTime = microtime(true) - $startTime;
?>

<!-- 메인 컨텐츠 -->
<main class="max-w-7xl mx-auto px-4 py-8">
    <!-- 성능 정보 표시 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h1 class="text-3xl font-bold text-forest-700 mb-4">
            🚀 CSS 최적화 시스템 테스트
        </h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="performance-card <?= $useOptimized ? 'bg-green-50 border-green-200' : 'bg-blue-50 border-blue-200' ?> border-2 rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">
                    현재 사용 중: <?= $useOptimized ? '🚀 최적화 시스템' : '📊 기존 시스템' ?>
                </h2>
                <p><strong>헤더 렌더링 시간:</strong> <?= round($headerTime * 1000, 2) ?>ms</p>
                <p><strong>페이지 타입:</strong> <?= htmlspecialchars($pageType) ?></p>
                <p><strong>디버그 모드:</strong> <?= CSS_DEBUG ? '✅ 활성화' : '❌ 비활성화' ?></p>
            </div>
            
            <div class="comparison-links">
                <h2 class="text-xl font-semibold mb-2">시스템 비교</h2>
                <div class="space-y-2">
                    <a href="?optimized=1&type=<?= $pageType ?>" 
                       class="btn btn-success <?= $useOptimized ? 'opacity-50' : '' ?>">
                        🚀 최적화 시스템으로 보기
                    </a>
                    <a href="?optimized=0&type=<?= $pageType ?>" 
                       class="btn btn-primary <?= !$useOptimized ? 'opacity-50' : '' ?>">
                        📊 기존 시스템으로 보기
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 페이지 타입별 테스트 컨텐츠 -->
    <div class="test-content">
        <?php if ($pageType === 'gallery'): ?>
            <!-- 갤러리 테스트 컨텐츠 -->
            <h2 class="text-2xl font-bold text-gray-800 mb-6">갤러리 레이아웃 테스트</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <article class="bg-white rounded-lg shadow-sm border border-primary-light hover:border-primary overflow-hidden hover:shadow-md transition-all duration-300">
                    <div class="relative h-48 bg-gray-100">
                        <div class="w-full h-full bg-gradient-to-br from-lime-100 to-lime-200 flex items-center justify-center">
                            <span class="text-4xl text-lime-600">🖼️</span>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">테스트 갤러리 항목 <?= $i ?></h3>
                        <p class="text-sm text-gray-600 mb-2">CSS 최적화 테스트용 샘플 컨텐츠입니다.</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fa fa-user mr-1"></i>
                                관리자
                            </span>
                            <span class="flex items-center">
                                <i class="fa fa-calendar mr-1"></i>
                                오늘
                            </span>
                        </div>
                    </div>
                </article>
                <?php endfor; ?>
            </div>
            
        <?php elseif ($pageType === 'newsletter'): ?>
            <!-- 뉴스레터 테스트 컨텐츠 -->
            <h2 class="text-2xl font-bold text-gray-800 mb-6">뉴스레터 레이아웃 테스트</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <article class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                    <div class="relative h-40 bg-gradient-to-r from-forest-100 to-forest-200 flex items-center justify-center">
                        <span class="text-3xl">📰</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">뉴스레터 제목 <?= $i ?></h3>
                        <p class="text-sm text-gray-600">CSS 로딩 최적화로 더 빠른 뉴스레터 경험을 제공합니다.</p>
                    </div>
                </article>
                <?php endfor; ?>
            </div>
            
        <?php else: ?>
            <!-- 기본 테스트 컨텐츠 -->
            <h2 class="text-2xl font-bold text-gray-800 mb-6">기본 레이아웃 테스트</h2>
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <p class="text-gray-600 mb-4">
                    이 페이지는 CSS 최적화 시스템의 성능을 테스트하기 위한 데모 페이지입니다.
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-lime-50 p-4 rounded border">
                        <h3 class="font-semibold text-lime-700">최적화 장점</h3>
                        <ul class="text-sm text-gray-600 mt-2">
                            <li>• 빠른 로딩 속도</li>
                            <li>• 적은 네트워크 요청</li>
                            <li>• 향상된 사용자 경험</li>
                        </ul>
                    </div>
                    <div class="bg-blue-50 p-4 rounded border">
                        <h3 class="font-semibold text-blue-700">기존 시스템</h3>
                        <ul class="text-sm text-gray-600 mt-2">
                            <li>• 안정성 입증</li>
                            <li>• 완전한 기능</li>
                            <li>• 호환성 보장</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- 페이지 타입 전환 -->
    <div class="mt-8 text-center">
        <h3 class="text-lg font-semibold mb-4">다른 페이지 타입 테스트</h3>
        <div class="space-x-2">
            <a href="?optimized=<?= $useOptimized ? '1' : '0' ?>&type=gallery" 
               class="btn <?= $pageType === 'gallery' ? 'btn-primary' : 'btn-outline-primary' ?>">
                갤러리
            </a>
            <a href="?optimized=<?= $useOptimized ? '1' : '0' ?>&type=newsletter" 
               class="btn <?= $pageType === 'newsletter' ? 'btn-primary' : 'btn-outline-primary' ?>">
                뉴스레터
            </a>
            <a href="?optimized=<?= $useOptimized ? '1' : '0' ?>&type=home" 
               class="btn <?= $pageType === 'home' ? 'btn-primary' : 'btn-outline-primary' ?>">
                홈페이지
            </a>
        </div>
    </div>
</main>

<!-- 성능 측정 스크립트 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const perfData = {
        loadTime: performance.now(),
        system: <?= $useOptimized ? "'optimized'" : "'legacy'" ?>,
        pageType: '<?= $pageType ?>',
        headerTime: <?= $headerTime * 1000 ?>,
        cssOptimized: <?= $useOptimized ? 'true' : 'false' ?>,
        timestamp: Date.now()
    };
    
    console.log('📊 성능 측정 결과:', perfData);
    
    // 성능 비교를 위한 데이터 저장
    const perfHistory = JSON.parse(localStorage.getItem('css_perf_comparison') || '[]');
    perfHistory.push(perfData);
    if (perfHistory.length > 50) perfHistory.shift(); // 최근 50개만 보관
    localStorage.setItem('css_perf_comparison', JSON.stringify(perfHistory));
    
    // 페이지에 성능 정보 표시
    setTimeout(function() {
        const totalTime = performance.now();
        const perfInfo = document.createElement('div');
        perfInfo.className = 'fixed bottom-4 right-4 bg-black text-white p-3 rounded shadow-lg text-sm';
        perfInfo.innerHTML = `
            <strong><?= $useOptimized ? '🚀 최적화' : '📊 기존' ?> 시스템</strong><br>
            총 로딩: ${Math.round(totalTime)}ms<br>
            헤더: ${Math.round(<?= $headerTime * 1000 ?>)}ms
        `;
        document.body.appendChild(perfInfo);
        
        // 5초 후 자동 제거
        setTimeout(() => perfInfo.remove(), 5000);
    }, 1000);
});

// 페이지 전환시 성능 비교
window.addEventListener('beforeunload', function() {
    const finalPerf = performance.now();
    console.log(`🏁 페이지 완료: ${Math.round(finalPerf)}ms`);
});
</script>

<!-- 추가 스타일 -->
<style>
.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    margin: 0.25rem;
    border: 1px solid #ccc;
    border-radius: 0.25rem;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-success {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.btn-outline-primary {
    background: transparent;
    color: #007bff;
    border-color: #007bff;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.performance-card {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from { transform: translateY(-10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

</body>
</html>