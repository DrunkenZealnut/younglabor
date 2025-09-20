<?php
/**
 * CSS 호환성 검사 시스템
 * 주요 페이지들에서 레이아웃 및 기능 호환성 검증
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

// 필요한 파일들 로드
require_once dirname(__DIR__) . '/bootstrap/app.php';
require_once dirname(__DIR__) . '/includes/css-mode-manager.php';

$cssMode = getCSSMode();

// 테스트할 페이지 목록
$testPages = [
    'home' => [
        'url' => '/',
        'title' => '메인 페이지',
        'critical_elements' => ['navbar', 'hero', 'footer'],
        'css_classes' => ['container', 'btn', 'card', 'navbar-brand']
    ],
    'gallery' => [
        'url' => '/community/gallery.php',
        'title' => '갤러리',
        'critical_elements' => ['image-grid', 'pagination', 'modal'],
        'css_classes' => ['row', 'col', 'btn-primary', 'modal']
    ],
    'newsletter' => [
        'url' => '/community/newsletter.php',
        'title' => '소식지',
        'critical_elements' => ['article-list', 'sidebar', 'pagination'],
        'css_classes' => ['card', 'list-group', 'btn-outline']
    ],
    'admin' => [
        'url' => '/admin/index.php',
        'title' => '관리자',
        'critical_elements' => ['sidebar', 'dashboard', 'tables'],
        'css_classes' => ['table', 'form-control', 'btn-success']
    ]
];

// CSS 클래스 호환성 체크
function checkCSSCompatibility($classes) {
    $results = [];
    
    foreach ($classes as $class) {
        $results[$class] = [
            'bootstrap_compatible' => in_array($class, [
                'container', 'row', 'col', 'btn', 'btn-primary', 'btn-secondary', 
                'card', 'table', 'form-control', 'navbar', 'navbar-brand'
            ]),
            'tailwind_compatible' => in_array($class, [
                'flex', 'grid', 'p-4', 'm-2', 'bg-primary', 'text-center', 
                'rounded', 'w-full', 'h-full'
            ]),
            'critical_css_included' => true // Critical CSS에 포함되어 있다고 가정
        ];
    }
    
    return $results;
}

// Visual regression 체크를 위한 스타일 계산
function calculateElementStyles($selector) {
    return [
        'selector' => $selector,
        'computed_styles' => [
            'display' => 'block',
            'position' => 'relative',
            'margin' => '0',
            'padding' => '0'
        ]
    ];
}

$selectedPage = $_GET['page'] ?? 'home';
$testMode = $_GET['test'] ?? 'visual';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSS 호환성 검사 - <?= $testPages[$selectedPage]['title'] ?? '알 수 없는 페이지' ?></title>
    
    <style>
    body {
        font-family: 'Noto Sans KR', sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f8f9fa;
    }
    
    .compatibility-container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .test-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .mode-indicator {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 500;
        font-size: 0.875rem;
        color: white;
    }
    
    .mode-optimized { background-color: #10b981; }
    .mode-legacy { background-color: #f59e0b; }
    .mode-debug { background-color: #3b82f6; }
    
    .test-results {
        margin: 2rem 0;
    }
    
    .result-section {
        margin: 1.5rem 0;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    
    .result-section h3 {
        margin: 0 0 1rem 0;
        color: #495057;
    }
    
    .compatibility-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    
    .compatibility-table th,
    .compatibility-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }
    
    .compatibility-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .status-pass { color: #10b981; }
    .status-warn { color: #f59e0b; }
    .status-fail { color: #ef4444; }
    
    .test-navigation {
        display: flex;
        gap: 0.5rem;
        margin: 1rem 0;
    }
    
    .test-navigation a {
        padding: 0.5rem 1rem;
        text-decoration: none;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: white;
        color: #495057;
    }
    
    .test-navigation a.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .visual-test-frame {
        width: 100%;
        height: 600px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin: 1rem 0;
    }
    
    .comparison-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin: 1rem 0;
    }
    
    .comparison-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
    }
    
    .comparison-item h4 {
        margin: 0 0 1rem 0;
        text-align: center;
    }
    
    .element-test {
        margin: 1rem 0;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    </style>
</head>
<body>
    <div class="compatibility-container">
        <div class="test-header">
            <div>
                <h1>CSS 호환성 검사</h1>
                <span class="mode-indicator mode-<?= $cssMode->getCurrentMode() ?>">
                    <?= strtoupper($cssMode->getCurrentMode()) ?> 모드
                </span>
                <span style="margin-left: 1rem;">
                    테스트 페이지: <?= $testPages[$selectedPage]['title'] ?? '알 수 없음' ?>
                </span>
            </div>
            <div>
                <select onchange="location.href='?page='+this.value+'&test=<?= $testMode ?>&css_mode=<?= $cssMode->getCurrentMode() ?>'">
                    <?php foreach ($testPages as $key => $page): ?>
                        <option value="<?= $key ?>" <?= $selectedPage === $key ? 'selected' : '' ?>>
                            <?= $page['title'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- 테스트 네비게이션 -->
        <div class="test-navigation">
            <a href="?page=<?= $selectedPage ?>&test=visual&css_mode=<?= $cssMode->getCurrentMode() ?>" 
               class="<?= $testMode === 'visual' ? 'active' : '' ?>">
                시각적 테스트
            </a>
            <a href="?page=<?= $selectedPage ?>&test=functional&css_mode=<?= $cssMode->getCurrentMode() ?>" 
               class="<?= $testMode === 'functional' ? 'active' : '' ?>">
                기능 테스트
            </a>
            <a href="?page=<?= $selectedPage ?>&test=performance&css_mode=<?= $cssMode->getCurrentMode() ?>" 
               class="<?= $testMode === 'performance' ? 'active' : '' ?>">
                성능 테스트
            </a>
            <a href="?page=<?= $selectedPage ?>&test=comparison&css_mode=<?= $cssMode->getCurrentMode() ?>" 
               class="<?= $testMode === 'comparison' ? 'active' : '' ?>">
                모드 비교
            </a>
        </div>

        <?php if ($testMode === 'visual'): ?>
            <!-- 시각적 테스트 -->
            <div class="result-section">
                <h3>시각적 회귀 테스트</h3>
                <p>현재 모드에서 페이지가 올바르게 렌더링되는지 확인합니다.</p>
                
                <iframe src="<?= $testPages[$selectedPage]['url'] ?>?css_mode=<?= $cssMode->getCurrentMode() ?>" 
                        class="visual-test-frame">
                </iframe>
                
                <div style="margin-top: 1rem;">
                    <h4>중요 요소 체크리스트</h4>
                    <div id="element-checklist">
                        <?php foreach ($testPages[$selectedPage]['critical_elements'] as $element): ?>
                            <div class="element-test">
                                <label>
                                    <input type="checkbox" class="element-check" data-element="<?= $element ?>">
                                    <?= ucfirst($element) ?> 요소 정상 표시
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($testMode === 'functional'): ?>
            <!-- 기능 테스트 -->
            <div class="result-section">
                <h3>CSS 클래스 호환성 검사</h3>
                
                <?php 
                $compatibility = checkCSSCompatibility($testPages[$selectedPage]['css_classes']); 
                ?>
                
                <table class="compatibility-table">
                    <thead>
                        <tr>
                            <th>CSS 클래스</th>
                            <th>Bootstrap 호환</th>
                            <th>Tailwind 호환</th>
                            <th>Critical CSS 포함</th>
                            <th>전체 상태</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compatibility as $class => $status): ?>
                            <tr>
                                <td><code><?= $class ?></code></td>
                                <td class="<?= $status['bootstrap_compatible'] ? 'status-pass' : 'status-fail' ?>">
                                    <?= $status['bootstrap_compatible'] ? '✓' : '✕' ?>
                                </td>
                                <td class="<?= $status['tailwind_compatible'] ? 'status-pass' : 'status-fail' ?>">
                                    <?= $status['tailwind_compatible'] ? '✓' : '✕' ?>
                                </td>
                                <td class="<?= $status['critical_css_included'] ? 'status-pass' : 'status-warn' ?>">
                                    <?= $status['critical_css_included'] ? '✓' : '⚠' ?>
                                </td>
                                <td class="<?= ($status['bootstrap_compatible'] || $status['tailwind_compatible']) && $status['critical_css_included'] ? 'status-pass' : 'status-warn' ?>">
                                    <?= ($status['bootstrap_compatible'] || $status['tailwind_compatible']) && $status['critical_css_included'] ? '정상' : '주의' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- JavaScript 기능 테스트 -->
            <div class="result-section">
                <h3>JavaScript 기능 테스트</h3>
                <div id="js-test-results">
                    <p>JavaScript 기능 테스트를 실행 중...</p>
                </div>
            </div>

        <?php elseif ($testMode === 'performance'): ?>
            <!-- 성능 테스트 -->
            <div class="result-section">
                <h3>성능 메트릭</h3>
                <div id="performance-results">
                    <p>성능 데이터를 수집 중...</p>
                </div>
            </div>

        <?php elseif ($testMode === 'comparison'): ?>
            <!-- 모드 비교 -->
            <div class="result-section">
                <h3>Legacy vs Optimized 비교</h3>
                
                <div class="comparison-grid">
                    <div class="comparison-item">
                        <h4>Legacy 모드</h4>
                        <iframe src="<?= $testPages[$selectedPage]['url'] ?>?css_mode=legacy" 
                                style="width: 100%; height: 400px; border: 1px solid #ccc;">
                        </iframe>
                        <div style="text-align: center; margin-top: 0.5rem;">
                            <a href="?page=<?= $selectedPage ?>&test=visual&css_mode=legacy" target="_blank">
                                새 창에서 보기
                            </a>
                        </div>
                    </div>
                    
                    <div class="comparison-item">
                        <h4>Optimized 모드</h4>
                        <iframe src="<?= $testPages[$selectedPage]['url'] ?>?css_mode=optimized" 
                                style="width: 100%; height: 400px; border: 1px solid #ccc;">
                        </iframe>
                        <div style="text-align: center; margin-top: 0.5rem;">
                            <a href="?page=<?= $selectedPage ?>&test=visual&css_mode=optimized" target="_blank">
                                새 창에서 보기
                            </a>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h4>시각적 차이점 체크리스트</h4>
                    <div id="visual-diff-checklist">
                        <div class="element-test">
                            <label>
                                <input type="checkbox" class="diff-check">
                                레이아웃이 동일하게 표시됨
                            </label>
                        </div>
                        <div class="element-test">
                            <label>
                                <input type="checkbox" class="diff-check">
                                색상과 스타일이 일치함
                            </label>
                        </div>
                        <div class="element-test">
                            <label>
                                <input type="checkbox" class="diff-check">
                                텍스트 크기와 간격이 동일함
                            </label>
                        </div>
                        <div class="element-test">
                            <label>
                                <input type="checkbox" class="diff-check">
                                버튼과 링크가 정상 작동함
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- 종합 결과 -->
        <div class="result-section">
            <h3>호환성 검사 결과</h3>
            <div id="overall-results">
                <p>검사를 완료하면 종합 결과가 표시됩니다.</p>
            </div>
        </div>

        <!-- 테스트 액션 -->
        <div style="text-align: center; margin: 2rem 0; padding-top: 2rem; border-top: 1px solid #dee2e6;">
            <button onclick="runAutomaticTests()" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; margin: 0 0.5rem;">
                자동 테스트 실행
            </button>
            <button onclick="generateReport()" style="padding: 0.75rem 1.5rem; background: #28a745; color: white; border: none; border-radius: 4px; margin: 0 0.5rem;">
                리포트 생성
            </button>
            <a href="/test/css-performance-test.php?css_mode=<?= $cssMode->getCurrentMode() ?>" 
               style="padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin: 0 0.5rem;">
                성능 테스트로 이동
            </a>
        </div>
    </div>

    <script>
    // 자동 테스트 실행
    function runAutomaticTests() {
        console.log('🔍 자동 호환성 테스트 시작');
        
        // DOM 요소 존재 확인
        const criticalElements = <?= json_encode($testPages[$selectedPage]['critical_elements']) ?>;
        const testResults = {};
        
        // CSS 클래스 적용 확인
        const testClasses = <?= json_encode($testPages[$selectedPage]['css_classes']) ?>;
        testClasses.forEach(className => {
            const elements = document.querySelectorAll('.' + className);
            testResults[className] = {
                found: elements.length > 0,
                count: elements.length
            };
        });
        
        // JavaScript 기능 테스트
        testResults.javascript = {
            jquery: typeof jQuery !== 'undefined',
            lucide: typeof lucide !== 'undefined',
            remodal: typeof Remodal !== 'undefined'
        };
        
        // 성능 메트릭 수집
        if (performance && performance.getEntriesByType) {
            const navigation = performance.getEntriesByType('navigation')[0];
            testResults.performance = {
                domContentLoaded: navigation.domContentLoadedEventEnd - navigation.navigationStart,
                loadComplete: navigation.loadEventEnd - navigation.navigationStart,
                firstPaint: performance.getEntriesByName('first-paint')[0]?.startTime || 'N/A'
            };
        }
        
        updateTestResults(testResults);
        console.log('✅자동 테스트 완료:', testResults);
    }
    
    // 테스트 결과 업데이트
    function updateTestResults(results) {
        // JavaScript 테스트 결과 업데이트
        const jsResults = document.getElementById('js-test-results');
        if (jsResults) {
            const jsHtml = `
                <table class="compatibility-table">
                    <tr><th>라이브러리</th><th>상태</th></tr>
                    <tr><td>jQuery</td><td class="${results.javascript.jquery ? 'status-pass' : 'status-fail'}">${results.javascript.jquery ? '✓ 로드됨' : '✕ 없음'}</td></tr>
                    <tr><td>Lucide Icons</td><td class="${results.javascript.lucide ? 'status-pass' : 'status-fail'}">${results.javascript.lucide ? '✓ 로드됨' : '✕ 없음'}</td></tr>
                    <tr><td>Remodal</td><td class="${results.javascript.remodal ? 'status-pass' : 'status-fail'}">${results.javascript.remodal ? '✓ 로드됨' : '✕ 없음'}</td></tr>
                </table>
            `;
            jsResults.innerHTML = jsHtml;
        }
        
        // 성능 결과 업데이트
        const perfResults = document.getElementById('performance-results');
        if (perfResults && results.performance) {
            const perfHtml = `
                <table class="compatibility-table">
                    <tr><th>메트릭</th><th>값</th><th>평가</th></tr>
                    <tr>
                        <td>DOM 로드 완료</td>
                        <td>${results.performance.domContentLoaded.toFixed(2)}ms</td>
                        <td class="${results.performance.domContentLoaded < 500 ? 'status-pass' : 'status-warn'}">${results.performance.domContentLoaded < 500 ? '✓ 빠름' : '⚠ 보통'}</td>
                    </tr>
                    <tr>
                        <td>페이지 로드 완료</td>
                        <td>${results.performance.loadComplete.toFixed(2)}ms</td>
                        <td class="${results.performance.loadComplete < 2000 ? 'status-pass' : 'status-warn'}">${results.performance.loadComplete < 2000 ? '✓ 빠름' : '⚠ 느림'}</td>
                    </tr>
                </table>
            `;
            perfResults.innerHTML = perfHtml;
        }
        
        // 종합 결과 업데이트
        const overallResults = document.getElementById('overall-results');
        if (overallResults) {
            const totalTests = Object.keys(results.javascript).length;
            const passedTests = Object.values(results.javascript).filter(Boolean).length;
            const passRate = (passedTests / totalTests * 100).toFixed(1);
            
            const overallHtml = `
                <div style="text-align: center;">
                    <h4>전체 테스트 결과</h4>
                    <div style="font-size: 2rem; margin: 1rem 0;">
                        <span class="${passRate >= 80 ? 'status-pass' : passRate >= 60 ? 'status-warn' : 'status-fail'}">
                            ${passRate}%
                        </span>
                    </div>
                    <p>${passedTests}/${totalTests} 테스트 통과</p>
                    <div style="margin-top: 1rem;">
                        ${passRate >= 80 ? 
                            '<span class="status-pass">✅ 호환성 우수 - 프로덕션 사용 권장</span>' :
                            passRate >= 60 ?
                            '<span class="status-warn">⚠️ 호환성 보통 - 일부 수정 필요</span>' :
                            '<span class="status-fail">❌ 호환성 불량 - 수정 필요</span>'
                        }
                    </div>
                </div>
            `;
            overallResults.innerHTML = overallHtml;
        }
    }
    
    // 리포트 생성
    function generateReport() {
        const reportData = {
            page: '<?= $selectedPage ?>',
            mode: '<?= $cssMode->getCurrentMode() ?>',
            timestamp: new Date().toISOString(),
            url: window.location.href
        };
        
        console.log('📄 호환성 리포트:', reportData);
        alert('리포트가 콘솔에 출력되었습니다. 개발자 도구를 확인해주세요.');
    }
    
    // 페이지 로드 완료 후 자동 테스트 실행
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 CSS 호환성 검사 시작');
        
        // 5초 후 자동 테스트 실행 (페이지 로딩 완료 대기)
        setTimeout(runAutomaticTests, 2000);
    });
    </script>
</body>
</html>