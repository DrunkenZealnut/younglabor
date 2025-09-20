<?php
/**
 * CSS Variables vs Legacy Mode 성능 비교 테스트
 * Phase 4A: 성능 분석 및 최적화
 */

// 성능 측정 헬퍼 함수
function measurePerformance($testName, $callback) {
    $startTime = microtime(true);
    $startMemory = memory_get_usage(true);
    
    $result = $callback();
    
    $endTime = microtime(true);
    $endMemory = memory_get_usage(true);
    
    return [
        'test_name' => $testName,
        'execution_time' => round(($endTime - $startTime) * 1000, 2), // ms
        'memory_used' => $endMemory - $startMemory, // bytes
        'peak_memory' => memory_get_peak_usage(true),
        'result' => $result
    ];
}

// CSS 파일 크기 측정
function measureCSSFileSize($filePath) {
    if (file_exists($filePath)) {
        return filesize($filePath);
    }
    return 0;
}

// CSS Variables 모드 테스트
function testCSSVariablesMode() {
    // CSS Variables 시스템 로드
    require_once __DIR__ . '/includes/CSSVariableThemeManager.php';
    
    $styleManager = getCSSVariableManager();
    
    // 10개 스타일 생성 테스트
    $styles = [];
    for ($i = 0; $i < 10; $i++) {
        $styles[] = $styleManager->getStyleString([
            'color' => 'forest-600',
            'background-color' => 'natural-50',
            'border-color' => 'lime-200'
        ]);
    }
    
    return count($styles);
}

// Legacy 모드 테스트
function testLegacyMode() {
    // NaturalGreenThemeLoader 로드
    require_once __DIR__ . '/includes/NaturalGreenThemeLoader.php';
    
    // 10개 클래스 생성 테스트
    $classes = [];
    for ($i = 0; $i < 10; $i++) {
        $classes[] = getThemeClass('text', 'primary', '600') . ' ' .
                    getThemeClass('bg', 'background', '50') . ' ' .
                    getThemeClass('border', 'border', '200');
    }
    
    return count($classes);
}

// CSS 인라인 생성 테스트
function testInlineCSSGeneration() {
    require_once __DIR__ . '/includes/css-optimization-config.php';
    
    ob_start();
    renderCSSVariableModeClasses('programs');
    $css = ob_get_clean();
    
    return strlen($css);
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Variables vs Legacy Mode 성능 비교</title>
    <style>
        body { font-family: -apple-system, sans-serif; padding: 2rem; background: #f8faf9; }
        .container { max-width: 1200px; margin: 0 auto; }
        .test-results { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 2rem 0; }
        .result-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .metric { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #eee; }
        .metric:last-child { border-bottom: none; }
        .metric-value { font-weight: 600; color: #3a7a4e; }
        .better { color: #16a34a; font-weight: bold; }
        .worse { color: #dc2626; font-weight: bold; }
        .summary { background: #e8f5e8; padding: 1.5rem; border-radius: 8px; margin: 2rem 0; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8faf9; font-weight: 600; }
        .file-size { font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 CSS Variables vs Legacy Mode 성능 비교</h1>
        <p><strong>Phase 4A:</strong> 성능 분석 및 최적화 - 실시간 성능 측정</p>
        
        <div class="test-results">
            <div class="result-card">
                <h3>🎨 CSS Variables 모드</h3>
                <?php
                $cssVarsResult = measurePerformance('CSS Variables Mode', 'testCSSVariablesMode');
                ?>
                <div class="metric">
                    <span>실행 시간:</span>
                    <span class="metric-value"><?= $cssVarsResult['execution_time'] ?>ms</span>
                </div>
                <div class="metric">
                    <span>메모리 사용:</span>
                    <span class="metric-value"><?= number_format($cssVarsResult['memory_used']) ?> bytes</span>
                </div>
                <div class="metric">
                    <span>최대 메모리:</span>
                    <span class="metric-value"><?= number_format($cssVarsResult['peak_memory'] / 1024 / 1024, 2) ?>MB</span>
                </div>
                <div class="metric">
                    <span>생성된 스타일:</span>
                    <span class="metric-value"><?= $cssVarsResult['result'] ?>개</span>
                </div>
            </div>
            
            <div class="result-card">
                <h3>🏛️ Legacy 모드</h3>
                <?php
                $legacyResult = measurePerformance('Legacy Mode', 'testLegacyMode');
                ?>
                <div class="metric">
                    <span>실행 시간:</span>
                    <span class="metric-value"><?= $legacyResult['execution_time'] ?>ms</span>
                </div>
                <div class="metric">
                    <span>메모리 사용:</span>
                    <span class="metric-value"><?= number_format($legacyResult['memory_used']) ?> bytes</span>
                </div>
                <div class="metric">
                    <span>최대 메모리:</span>
                    <span class="metric-value"><?= number_format($legacyResult['peak_memory'] / 1024 / 1024, 2) ?>MB</span>
                </div>
                <div class="metric">
                    <span>생성된 클래스:</span>
                    <span class="metric-value"><?= $legacyResult['result'] ?>개</span>
                </div>
            </div>
        </div>
        
        <div class="summary">
            <h3>📊 성능 비교 결과</h3>
            <?php
            $timeComparison = $cssVarsResult['execution_time'] < $legacyResult['execution_time'] ? 'better' : 'worse';
            $memoryComparison = $cssVarsResult['memory_used'] < $legacyResult['memory_used'] ? 'better' : 'worse';
            $timeDiff = abs($cssVarsResult['execution_time'] - $legacyResult['execution_time']);
            $memoryDiff = abs($cssVarsResult['memory_used'] - $legacyResult['memory_used']);
            ?>
            <p><strong>실행 시간:</strong> CSS Variables가 <span class="<?= $timeComparison ?>"><?= $timeDiff ?>ms <?= $timeComparison === 'better' ? '빠름' : '느림' ?></span></p>
            <p><strong>메모리 사용:</strong> CSS Variables가 <span class="<?= $memoryComparison ?>"><?= number_format($memoryDiff) ?> bytes <?= $memoryComparison === 'better' ? '적게 사용' : '많이 사용' ?></span></p>
        </div>
        
        <h3>📁 CSS 파일 크기 분석</h3>
        <table>
            <thead>
                <tr>
                    <th>파일</th>
                    <th>크기</th>
                    <th>설명</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cssFiles = [
                    '/theme/natural-green/styles/globals.css' => 'Natural Green 전역 CSS',
                    '/css/theme.css' => '메인 테마 CSS',
                    '/includes/css-optimization-config.php' => 'CSS 최적화 설정 (PHP)',
                    '/includes/CSSVariableThemeManager.php' => 'CSS Variables 매니저 (PHP)',
                    '/includes/NaturalGreenThemeLoader.php' => 'Legacy 테마 로더 (PHP)'
                ];
                
                foreach ($cssFiles as $file => $description) {
                    $fullPath = __DIR__ . $file;
                    $size = measureCSSFileSize($fullPath);
                    $sizeKB = $size > 0 ? number_format($size / 1024, 2) : '0';
                    echo "<tr>";
                    echo "<td><code>" . basename($file) . "</code></td>";
                    echo "<td class='file-size'>{$sizeKB} KB</td>";
                    echo "<td>{$description}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
        <h3>🎯 인라인 CSS 생성 테스트</h3>
        <div class="result-card">
            <?php
            $inlineCSSResult = measurePerformance('Inline CSS Generation', 'testInlineCSSGeneration');
            ?>
            <div class="metric">
                <span>생성 시간:</span>
                <span class="metric-value"><?= $inlineCSSResult['execution_time'] ?>ms</span>
            </div>
            <div class="metric">
                <span>CSS 크기:</span>
                <span class="metric-value"><?= number_format($inlineCSSResult['result'] / 1024, 2) ?>KB</span>
            </div>
            <div class="metric">
                <span>메모리 사용:</span>
                <span class="metric-value"><?= number_format($inlineCSSResult['memory_used']) ?> bytes</span>
            </div>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #fff3cd; border-radius: 8px;">
            <h4>⚡ 최적화 권장사항</h4>
            <ul>
                <li><strong>캐싱 구현:</strong> CSS Variables 스타일 결과를 세션 레벨에서 캐싱</li>
                <li><strong>지연 로딩:</strong> 필요한 페이지에서만 CSS Variables 시스템 로드</li>
                <li><strong>CSS 압축:</strong> 인라인 CSS 생성 시 불필요한 공백 제거</li>
                <li><strong>조건부 로딩:</strong> 사용자가 CSS Variables 모드를 사용할 때만 관련 리소스 로드</li>
            </ul>
        </div>
        
        <p style="margin-top: 2rem; color: #666; font-size: 0.9em;">
            <strong>테스트 환경:</strong> PHP <?= phpversion() ?>, 메모리 제한: <?= ini_get('memory_limit') ?><br>
            <strong>측정 시간:</strong> <?= date('Y-m-d H:i:s') ?>
        </p>
    </div>
</body>
</html>