<?php
/**
 * CSS Variables 캐싱 성능 모니터링 도구
 * Phase 4A: 성능 분석 및 최적화
 */

require_once __DIR__ . '/includes/CSSVariablesCache.php';

// 캐시 인스턴스 가져오기
$cache = getCSSVariablesCache();

// 캐시 통계 수집
$stats = $cache->getCacheStats();
$hitRate = $cache->getCacheHitRate();

// 메모리 사용량 분석
$memoryUsage = memory_get_usage(true);
$peakMemory = memory_get_peak_usage(true);
$memoryLimit = ini_get('memory_limit');

// 캐시 효율성 테스트
function testCacheEfficiency() {
    require_once __DIR__ . '/includes/CSSVariableThemeManager.php';
    $manager = getCSSVariableManager();
    
    $testStyles = [
        ['color' => 'forest-600', 'background-color' => 'natural-50'],
        ['color' => 'lime-500', 'border-color' => 'gray-200'],
        ['background-color' => 'white', 'color' => 'forest-900'],
        ['border-color' => 'lime-200', 'color' => 'gray-600'],
        ['color' => 'forest-700', 'background-color' => 'natural-100']
    ];
    
    $results = [];
    
    // 첫 번째 실행 (캐시 미스)
    $startTime = microtime(true);
    foreach ($testStyles as $index => $style) {
        $styleString = $manager->getStyleString($style, "test_{$index}");
    }
    $firstRunTime = (microtime(true) - $startTime) * 1000;
    
    // 두 번째 실행 (캐시 히트)
    $startTime = microtime(true);
    foreach ($testStyles as $index => $style) {
        $styleString = $manager->getStyleString($style, "test_{$index}");
    }
    $secondRunTime = (microtime(true) - $startTime) * 1000;
    
    return [
        'first_run' => round($firstRunTime, 2),
        'second_run' => round($secondRunTime, 2),
        'improvement' => round((($firstRunTime - $secondRunTime) / $firstRunTime) * 100, 2),
        'test_count' => count($testStyles)
    ];
}

$cacheTest = testCacheEfficiency();

// 세션 캐시 분석
$sessionCacheSize = 0;
$sessionCacheCount = 0;
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'css_vars_cache_') === 0) {
        $sessionCacheCount++;
        $sessionCacheSize += strlen(serialize($value));
    }
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Variables 캐싱 성능 모니터링</title>
    <style>
        body { font-family: -apple-system, sans-serif; padding: 2rem; background: #f8faf9; }
        .container { max-width: 1200px; margin: 0 auto; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin: 2rem 0; }
        .metric-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .metric-value { font-size: 2.5em; font-weight: bold; margin: 0.5rem 0; }
        .metric-label { color: #666; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-good { color: #16a34a; }
        .metric-warning { color: #f59e0b; }
        .metric-danger { color: #dc2626; }
        .metric-info { color: #3b82f6; }
        .progress-bar { width: 100%; height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden; margin: 1rem 0; }
        .progress-fill { height: 100%; border-radius: 5px; transition: width 0.3s ease; }
        .progress-good { background: linear-gradient(90deg, #16a34a, #22c55e); }
        .progress-warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .progress-danger { background: linear-gradient(90deg, #dc2626, #ef4444); }
        .cache-actions { background: white; padding: 1.5rem; border-radius: 12px; margin: 2rem 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; margin: 0.25rem; transition: all 0.2s; }
        .btn-primary { background: #3a7a4e; color: white; }
        .btn-primary:hover { background: #2d5f3d; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .efficiency-chart { background: white; padding: 1.5rem; border-radius: 12px; margin: 2rem 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .bar-comparison { display: flex; align-items: end; height: 200px; gap: 2rem; margin: 2rem 0; justify-content: center; }
        .bar { border-radius: 8px 8px 0 0; min-width: 80px; position: relative; display: flex; align-items: end; justify-content: center; color: white; font-weight: bold; }
        .bar-first { background: linear-gradient(to top, #dc2626, #ef4444); }
        .bar-second { background: linear-gradient(to top, #16a34a, #22c55e); }
        .bar-label { position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #333; font-size: 0.9em; }
        .realtime-stats { background: #e8f5e8; padding: 1.5rem; border-radius: 12px; margin: 2rem 0; }
        .stat-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .stat-table th, .stat-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .stat-table th { background: #f8faf9; font-weight: 600; }
        .auto-refresh { position: fixed; top: 20px; right: 20px; background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 CSS Variables 캐싱 성능 모니터링</h1>
        <p><strong>Phase 4A:</strong> 실시간 캐시 성능 분석 및 최적화 효과 측정</p>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">캐시 히트율</div>
                <div class="metric-value <?= $hitRate >= 80 ? 'metric-good' : ($hitRate >= 50 ? 'metric-warning' : 'metric-danger') ?>">
                    <?= $hitRate ?>%
                </div>
                <div class="progress-bar">
                    <div class="progress-fill <?= $hitRate >= 80 ? 'progress-good' : ($hitRate >= 50 ? 'progress-warning' : 'progress-danger') ?>" 
                         style="width: <?= $hitRate ?>%"></div>
                </div>
                <small><?= $hitRate >= 80 ? '🎯 우수' : ($hitRate >= 50 ? '⚠️ 개선 필요' : '🚨 최적화 필요') ?></small>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">메모리 캐시</div>
                <div class="metric-value metric-info"><?= $stats['memory_cache_count'] ?></div>
                <small>개 항목 캐시됨</small>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">세션 캐시</div>
                <div class="metric-value metric-info"><?= $stats['session_cache_count'] ?></div>
                <small>개 항목 (<?= number_format($sessionCacheSize) ?> bytes)</small>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">메모리 사용량</div>
                <div class="metric-value <?= $memoryUsage < 50*1024*1024 ? 'metric-good' : 'metric-warning' ?>">
                    <?= number_format($memoryUsage / 1024 / 1024, 1) ?>MB
                </div>
                <div class="progress-bar">
                    <div class="progress-fill <?= $memoryUsage < 50*1024*1024 ? 'progress-good' : 'progress-warning' ?>" 
                         style="width: <?= min(100, ($memoryUsage / (128*1024*1024)) * 100) ?>%"></div>
                </div>
                <small>최대: <?= number_format($peakMemory / 1024 / 1024, 1) ?>MB</small>
            </div>
        </div>
        
        <div class="efficiency-chart">
            <h3>⚡ 캐시 효율성 테스트 결과</h3>
            <p>동일한 CSS 스타일 생성 작업을 두 번 실행하여 캐시 효과 측정</p>
            
            <div class="bar-comparison">
                <div class="bar bar-first" style="height: <?= min(200, ($cacheTest['first_run'] / max($cacheTest['first_run'], $cacheTest['second_run'])) * 180) ?>px;">
                    <span><?= $cacheTest['first_run'] ?>ms</span>
                    <div class="bar-label">첫 번째 실행<br>(캐시 미스)</div>
                </div>
                <div class="bar bar-second" style="height: <?= min(200, ($cacheTest['second_run'] / max($cacheTest['first_run'], $cacheTest['second_run'])) * 180) ?>px;">
                    <span><?= $cacheTest['second_run'] ?>ms</span>
                    <div class="bar-label">두 번째 실행<br>(캐시 히트)</div>
                </div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: #f0f9ff; border-radius: 8px;">
                <strong>성능 향상: <?= $cacheTest['improvement'] ?>%</strong> 
                (<?= $cacheTest['test_count'] ?>개 스타일 생성 테스트)
            </div>
        </div>
        
        <div class="realtime-stats">
            <h3>📊 실시간 캐시 통계</h3>
            <table class="stat-table">
                <thead>
                    <tr>
                        <th>항목</th>
                        <th>값</th>
                        <th>설명</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>캐시 요청 수</td>
                        <td><strong><?= $_SESSION['cache_requests'] ?? 0 ?></strong></td>
                        <td>총 캐시 조회 요청 횟수</td>
                    </tr>
                    <tr>
                        <td>캐시 히트 수</td>
                        <td><strong><?= $_SESSION['cache_hits'] ?? 0 ?></strong></td>
                        <td>캐시에서 성공적으로 조회된 횟수</td>
                    </tr>
                    <tr>
                        <td>캐시 버전</td>
                        <td><strong><?= $stats['cache_version'] ?></strong></td>
                        <td>현재 캐시 시스템 버전</td>
                    </tr>
                    <tr>
                        <td>세션 ID</td>
                        <td><code><?= substr(session_id(), 0, 8) ?>...</code></td>
                        <td>현재 세션 식별자</td>
                    </tr>
                    <tr>
                        <td>PHP 메모리 제한</td>
                        <td><strong><?= $memoryLimit ?></strong></td>
                        <td>서버 메모리 제한 설정</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="cache-actions">
            <h3>🔧 캐시 관리 도구</h3>
            <p>캐시 시스템을 관리하고 성능을 최적화할 수 있습니다.</p>
            
            <a href="?action=clear_cache" class="btn btn-danger">🗑️ 캐시 초기화</a>
            <a href="?action=test_performance" class="btn btn-primary">⚡ 성능 테스트</a>
            <a href="page-performance-test.php" class="btn btn-secondary">📊 페이지 성능 테스트</a>
            <a href="performance-test.php" class="btn btn-secondary">🔍 기본 성능 테스트</a>
            
            <?php if (isset($_GET['action'])): ?>
                <div style="margin-top: 1rem; padding: 1rem; background: #f0f9ff; border-radius: 8px;">
                    <?php if ($_GET['action'] === 'clear_cache'): ?>
                        <?php 
                        $cache->clearCache();
                        echo "<strong>✅ 캐시가 초기화되었습니다!</strong><br>메모리 및 세션 캐시가 모두 정리되었습니다.";
                        ?>
                        <script>setTimeout(() => window.location.href = 'cache-performance-monitor.php', 2000);</script>
                    <?php elseif ($_GET['action'] === 'test_performance'): ?>
                        <?php
                        $testResult = testCacheEfficiency();
                        echo "<strong>🚀 성능 테스트 완료!</strong><br>";
                        echo "첫 실행: {$testResult['first_run']}ms, 두 번째 실행: {$testResult['second_run']}ms<br>";
                        echo "성능 향상: {$testResult['improvement']}%";
                        ?>
                        <script>setTimeout(() => window.location.href = 'cache-performance-monitor.php', 3000);</script>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: #fff3cd; border-radius: 12px;">
            <h4>💡 최적화 권장사항</h4>
            <ul>
                <?php if ($hitRate < 50): ?>
                    <li><strong>🚨 캐시 히트율 개선 필요:</strong> 더 많은 스타일을 캐시하도록 시스템 조정</li>
                <?php endif; ?>
                <?php if ($stats['memory_cache_count'] > 40): ?>
                    <li><strong>⚠️ 메모리 캐시 정리:</strong> 불필요한 캐시 항목 정리 권장</li>
                <?php endif; ?>
                <?php if ($sessionCacheSize > 50000): ?>
                    <li><strong>📦 세션 캐시 최적화:</strong> 캐시 데이터 압축 필요</li>
                <?php endif; ?>
                <li><strong>🔄 자동 새로고침:</strong> 실시간 모니터링을 위해 페이지가 30초마다 자동 새로고침됩니다</li>
                <li><strong>📈 지속적 모니터링:</strong> 캐시 히트율 80% 이상 유지 목표</li>
            </ul>
        </div>
        
        <div class="auto-refresh">
            <strong>🔄 자동 새로고침</strong><br>
            <small id="countdown">30초 후 새로고침</small>
        </div>
    </div>
    
    <script>
        // 자동 새로고침 카운트다운
        let countdown = 30;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown + '초 후 새로고침';
            
            if (countdown <= 0) {
                window.location.reload();
            }
        }, 1000);
        
        // 페이지 클릭 시 카운트다운 리셋
        document.addEventListener('click', () => {
            countdown = 30;
        });
    </script>
    
    <p style="margin-top: 2rem; color: #666; font-size: 0.9em; text-align: center;">
        <strong>모니터링 시작:</strong> <?= date('Y-m-d H:i:s') ?> | 
        <strong>캐시 시스템:</strong> CSS Variables Cache v<?= $stats['cache_version'] ?> |
        <strong>PHP 버전:</strong> <?= phpversion() ?>
    </p>
</body>
</html>