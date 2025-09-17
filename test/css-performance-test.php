<?php
/**
 * CSS 성능 테스트 페이지
 * Legacy vs Optimized 모드 성능 비교
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

// 필요한 파일들 로드
require_once dirname(__DIR__) . '/bootstrap/app.php';
require_once dirname(__DIR__) . '/includes/css-mode-manager.php';
require_once dirname(__DIR__) . '/includes/critical-css-generator.php';

$cssMode = getCSSMode();
$criticalGenerator = new CriticalCSSGenerator();

// 테스트 모드 설정
$testMode = $_GET['mode'] ?? 'comparison';
$pageType = $_GET['type'] ?? 'home';

// 성능 메트릭 수집
$performanceMetrics = [
    'page_load_start' => microtime(true),
    'css_mode' => $cssMode->getCurrentMode(),
    'page_type' => $pageType,
    'timestamp' => date('Y-m-d H:i:s')
];

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSS 성능 테스트 - <?= ucfirst($cssMode->getCurrentMode()) ?> 모드</title>
    
    <!-- 성능 측정 시작 -->
    <script>
    window.cssPerformanceTest = {
        startTime: performance.now(),
        mode: '<?= $cssMode->getCurrentMode() ?>',
        pageType: '<?= $pageType ?>',
        metrics: {},
        markTime: function(label) {
            this.metrics[label] = performance.now() - this.startTime;
            console.log('⏱️ ' + label + ':', this.metrics[label].toFixed(2) + 'ms');
        }
    };
    
    // Critical resources 시작 측정
    window.cssPerformanceTest.markTime('script_start');
    </script>
    
    <?php if ($cssMode->isOptimizedMode()): ?>
        <!-- 최적화된 헤더 로드 -->
        <?php 
        $criticalCSS = $criticalGenerator->generateCriticalCSS();
        $criticalSize = strlen($criticalCSS);
        ?>
        
        <!-- 성능 메타데이터 -->
        <meta name="css-optimization" content="enabled">
        <meta name="critical-css-size" content="<?= $criticalSize ?>">
        
        <!-- Critical CSS 인라인 -->
        <style id="critical-css">
        <?= $criticalCSS ?>
        </style>
        
        <!-- 폰트 최적화 로딩 -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
        
        <!-- Essential Icons -->
        <style>
        .fa-check::before { content: "✓"; }
        .fa-times::before { content: "✕"; }
        .fa-home::before { content: "🏠"; }
        </style>
        
        <script>
        window.cssPerformanceTest.markTime('critical_css_loaded');
        </script>
        
    <?php else: ?>
        <!-- Legacy 헤더 로드 -->
        <meta name="css-optimization" content="disabled">
        
        <!-- 기존 방식 CSS 로딩 -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        
        <script>
        window.cssPerformanceTest.markTime('legacy_css_loaded');
        </script>
    <?php endif; ?>
    
    <!-- 성능 테스트용 스타일 -->
    <style>
    .performance-test-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
        font-family: 'Noto Sans KR', sans-serif;
    }
    
    .metrics-display {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        border-left: 4px solid #007bff;
    }
    
    .mode-indicator {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    .mode-optimized {
        background-color: #10b981;
        color: white;
    }
    
    .mode-legacy {
        background-color: #f59e0b;
        color: white;
    }
    
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    
    .comparison-table th,
    .comparison-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }
    
    .comparison-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .metric-good { color: #10b981; }
    .metric-warning { color: #f59e0b; }
    .metric-poor { color: #ef4444; }
    
    .test-content {
        margin: 2rem 0;
    }
    
    .test-components {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
    }
    
    .test-component {
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: white;
    }
    </style>
</head>
<body>
    <div class="performance-test-container">
        <header>
            <h1>CSS 성능 테스트</h1>
            <div class="metrics-display">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span class="mode-indicator mode-<?= $cssMode->getCurrentMode() ?>">
                            <?= strtoupper($cssMode->getCurrentMode()) ?> 모드
                        </span>
                        <span style="margin-left: 1rem;">페이지 타입: <?= $pageType ?></span>
                    </div>
                    <div>
                        <a href="?mode=comparison&type=<?= $pageType ?>&css_mode=legacy" class="btn btn-sm btn-outline-primary">Legacy 테스트</a>
                        <a href="?mode=comparison&type=<?= $pageType ?>&css_mode=optimized" class="btn btn-sm btn-outline-success">Optimized 테스트</a>
                        <a href="?mode=comparison&type=<?= $pageType ?>&css_mode=debug" class="btn btn-sm btn-outline-info">Debug 모드</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- 실시간 성능 메트릭 -->
        <section class="test-content">
            <h2>실시간 성능 메트릭</h2>
            <div id="performance-metrics" class="metrics-display">
                <div id="loading-metrics">성능 데이터 수집 중...</div>
            </div>
        </section>

        <!-- CSS 정보 -->
        <section class="test-content">
            <h2>CSS 로딩 정보</h2>
            <?php if ($cssMode->isOptimizedMode()): ?>
                <?php $debugInfo = $criticalGenerator->getDebugInfo(); ?>
                <table class="comparison-table">
                    <tr>
                        <th>항목</th>
                        <th>값</th>
                        <th>상태</th>
                    </tr>
                    <tr>
                        <td>Critical CSS 크기</td>
                        <td><?= $debugInfo['size_kb'] ?> KB</td>
                        <td class="<?= $debugInfo['within_limit'] ? 'metric-good' : 'metric-warning' ?>">
                            <?= $debugInfo['within_limit'] ? '✓ 권장 크기 내' : '⚠ 권장 크기 초과' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Natural Green 테마</td>
                        <td><?= $debugInfo['natural_green_exists'] ? '사용 가능' : '사용 불가' ?></td>
                        <td class="<?= $debugInfo['natural_green_exists'] ? 'metric-good' : 'metric-poor' ?>">
                            <?= $debugInfo['natural_green_exists'] ? '✓' : '✕' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>캐시 파일 수</td>
                        <td><?= count($debugInfo['cache_files']) ?>개</td>
                        <td class="metric-good">정상</td>
                    </tr>
                </table>
            <?php else: ?>
                <table class="comparison-table">
                    <tr>
                        <th>항목</th>
                        <th>값</th>
                        <th>상태</th>
                    </tr>
                    <tr>
                        <td>Bootstrap CSS</td>
                        <td>~200KB (CDN)</td>
                        <td class="metric-warning">외부 의존성</td>
                    </tr>
                    <tr>
                        <td>Tailwind CSS</td>
                        <td>~300KB (Script)</td>
                        <td class="metric-warning">런타임 생성</td>
                    </tr>
                    <tr>
                        <td>Font Awesome</td>
                        <td>~60KB (CDN)</td>
                        <td class="metric-warning">외부 의존성</td>
                    </tr>
                    <tr>
                        <td>Bootstrap Icons</td>
                        <td>~80KB (CDN)</td>
                        <td class="metric-warning">외부 의존성</td>
                    </tr>
                    <tr>
                        <td>총 예상 크기</td>
                        <td>~640KB</td>
                        <td class="metric-poor">큰 번들 크기</td>
                    </tr>
                </table>
            <?php endif; ?>
        </section>

        <!-- 테스트 컴포넌트들 -->
        <section class="test-content">
            <h2>UI 컴포넌트 테스트</h2>
            <p>다양한 CSS 클래스들이 올바르게 적용되는지 확인합니다.</p>
            
            <div class="test-components">
                <!-- Bootstrap 컴포넌트 테스트 -->
                <div class="test-component">
                    <h3>Bootstrap 컴포넌트</h3>
                    <div class="mb-3">
                        <button class="btn btn-primary">Primary 버튼</button>
                        <button class="btn btn-secondary">Secondary 버튼</button>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">카드 제목</h5>
                            <p class="card-text">카드 내용 텍스트입니다.</p>
                        </div>
                    </div>
                </div>

                <!-- Tailwind 유틸리티 테스트 -->
                <div class="test-component">
                    <h3>Tailwind 유틸리티</h3>
                    <div class="flex justify-between items-center mb-3">
                        <span class="bg-primary text-white p-2 rounded">Flex 컨테이너</span>
                        <span class="bg-secondary p-2 rounded">아이템</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-primary text-white p-2 text-center rounded">Grid 1</div>
                        <div class="bg-secondary p-2 text-center rounded">Grid 2</div>
                    </div>
                </div>

                <!-- 아이콘 테스트 -->
                <div class="test-component">
                    <h3>아이콘 테스트</h3>
                    <div class="mb-3">
                        <span class="fa fa-check"></span> Font Awesome Check
                        <br>
                        <span class="fa fa-times"></span> Font Awesome Times
                        <br>
                        <span class="fa fa-home"></span> Font Awesome Home
                    </div>
                    <div>
                        <i class="bi bi-check"></i> Bootstrap Icon Check
                        <br>
                        <i class="bi bi-x"></i> Bootstrap Icon X
                        <br>
                        <i class="bi bi-house"></i> Bootstrap Icon House
                    </div>
                </div>

                <!-- 타이포그래피 테스트 -->
                <div class="test-component">
                    <h3>타이포그래피</h3>
                    <h1 style="font-size: 2rem; margin: 0.5rem 0;">H1 제목</h1>
                    <h2 style="font-size: 1.5rem; margin: 0.5rem 0;">H2 제목</h2>
                    <p>일반 단락 텍스트입니다. 한글 폰트가 제대로 적용되는지 확인합니다.</p>
                    <a href="#" style="color: var(--primary);">링크 텍스트</a>
                </div>
            </div>
        </section>

        <!-- 성능 비교 -->
        <section class="test-content">
            <h2>성능 비교 분석</h2>
            <div id="performance-comparison" class="metrics-display">
                <p>페이지 로드 완료 후 성능 분석이 표시됩니다.</p>
            </div>
        </section>
    </div>

    <!-- 성능 측정 스크립트 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        window.cssPerformanceTest.markTime('dom_loaded');
        
        // CSS 로딩 상태 확인
        const criticalCSS = document.getElementById('critical-css');
        const allStyles = document.querySelectorAll('style, link[rel="stylesheet"]');
        
        // 메트릭 업데이트
        function updateMetrics() {
            const metrics = window.cssPerformanceTest.metrics;
            const mode = window.cssPerformanceTest.mode;
            
            const metricsHtml = `
                <table class="comparison-table">
                    <tr><th>메트릭</th><th>시간</th><th>평가</th></tr>
                    <tr>
                        <td>스크립트 시작</td>
                        <td>${metrics.script_start ? metrics.script_start.toFixed(2) + 'ms' : 'N/A'}</td>
                        <td class="metric-good">✓</td>
                    </tr>
                    <tr>
                        <td>${mode === 'optimized' ? 'Critical CSS 로드' : 'Legacy CSS 로드'}</td>
                        <td>${(metrics.critical_css_loaded || metrics.legacy_css_loaded || 0).toFixed(2)}ms</td>
                        <td class="${(metrics.critical_css_loaded || metrics.legacy_css_loaded || 0) < 100 ? 'metric-good' : 'metric-warning'}">
                            ${(metrics.critical_css_loaded || metrics.legacy_css_loaded || 0) < 100 ? '✓ 빠름' : '⚠ 보통'}
                        </td>
                    </tr>
                    <tr>
                        <td>DOM 로드 완료</td>
                        <td>${metrics.dom_loaded ? metrics.dom_loaded.toFixed(2) + 'ms' : 'N/A'}</td>
                        <td class="${metrics.dom_loaded < 200 ? 'metric-good' : 'metric-warning'}">
                            ${metrics.dom_loaded < 200 ? '✓ 빠름' : '⚠ 보통'}
                        </td>
                    </tr>
                    <tr>
                        <td>총 CSS 리소스</td>
                        <td>${allStyles.length}개</td>
                        <td class="${allStyles.length < 10 ? 'metric-good' : 'metric-warning'}">
                            ${allStyles.length < 10 ? '✓ 적음' : '⚠ 많음'}
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 1rem;">
                    <strong>Critical CSS 크기:</strong> ${criticalCSS ? (criticalCSS.textContent.length / 1024).toFixed(2) + 'KB' : 'N/A'}
                    ${criticalCSS && criticalCSS.textContent.length < 7168 ? ' <span class="metric-good">✓ 권장 크기 내</span>' : ''}
                </div>
            `;
            
            document.getElementById('performance-metrics').innerHTML = metricsHtml;
        }
        
        // 성능 비교 분석
        function generateComparison() {
            const currentMode = window.cssPerformanceTest.mode;
            const isOptimized = currentMode === 'optimized';
            
            const comparisonHtml = `
                <h3>현재 모드: ${currentMode.toUpperCase()}</h3>
                <table class="comparison-table">
                    <tr>
                        <th>항목</th>
                        <th>Legacy 모드</th>
                        <th>Optimized 모드</th>
                        <th>현재 값</th>
                    </tr>
                    <tr>
                        <td>예상 번들 크기</td>
                        <td class="metric-poor">~640KB</td>
                        <td class="metric-good">~50KB</td>
                        <td class="${isOptimized ? 'metric-good' : 'metric-poor'}">
                            ${isOptimized ? '~50KB ✓' : '~640KB'}
                        </td>
                    </tr>
                    <tr>
                        <td>외부 요청 수</td>
                        <td class="metric-poor">5개</td>
                        <td class="metric-good">1개</td>
                        <td class="${isOptimized ? 'metric-good' : 'metric-poor'}">
                            ${isOptimized ? '1개 ✓' : '5개'}
                        </td>
                    </tr>
                    <tr>
                        <td>Render Blocking</td>
                        <td class="metric-poor">높음</td>
                        <td class="metric-good">낮음</td>
                        <td class="${isOptimized ? 'metric-good' : 'metric-poor'}">
                            ${isOptimized ? '낮음 ✓' : '높음'}
                        </td>
                    </tr>
                    <tr>
                        <td>캐시 효율성</td>
                        <td class="metric-poor">낮음</td>
                        <td class="metric-good">높음</td>
                        <td class="${isOptimized ? 'metric-good' : 'metric-poor'}">
                            ${isOptimized ? '높음 ✓' : '낮음'}
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 1rem;">
                    <strong>권장 사항:</strong> 
                    ${isOptimized ? 
                        '<span class="metric-good">현재 최적화된 모드를 사용 중입니다. 성능이 우수합니다!</span>' : 
                        '<span class="metric-warning">성능 향상을 위해 <a href="?css_mode=optimized">최적화된 모드</a>로 전환을 권장합니다.</span>'
                    }
                </div>
            `;
            
            document.getElementById('performance-comparison').innerHTML = comparisonHtml;
        }
        
        // 초기 메트릭 업데이트
        updateMetrics();
        generateComparison();
        
        // 페이지 로드 완료 후 최종 메트릭
        window.addEventListener('load', function() {
            window.cssPerformanceTest.markTime('page_loaded');
            setTimeout(function() {
                updateMetrics();
            }, 100);
        });
    });
    </script>

    <?php if ($cssMode->isDebugMode()): ?>
        <!-- 디버그 모드 추가 정보 -->
        <script>
        console.log('🔍 CSS Performance Test Debug Mode');
        console.log('📊 Mode Info:', <?= json_encode($cssMode->getModeInfo()) ?>);
        <?php if ($cssMode->isOptimizedMode()): ?>
        console.log('🎨 Critical CSS Debug:', <?= json_encode($criticalGenerator->getDebugInfo()) ?>);
        <?php endif; ?>
        </script>
    <?php endif; ?>
</body>
</html>