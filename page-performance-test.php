<?php
/**
 * 페이지별 로딩 시간 측정 도구
 * Phase 4A: 성능 분석 및 최적화
 */

// 확장된 10개 페이지 목록
$testPages = [
    'programs/domestic.php' => '국내위기아동지원사업',
    'programs/overseas.php' => '해외위기아동지원사업',
    'programs/labor-rights.php' => '노동인권지원사업',
    'programs/community.php' => '지역사회사업',
    'programs/volunteer.php' => '자원봉사',
    'about/about.php' => '단체소개',
    'about/history.php' => '연혁',
    'about/location.php' => '찾아오는 길',
    'about/org.php' => '조직도',
    'index.php' => '메인 페이지'
];

// 페이지 로딩 시간 측정
function measurePageLoadTime($pagePath, $cssMode = null) {
    $url = 'http://localhost/hopec/' . $pagePath;
    if ($cssMode) {
        $url .= '?css_mode=' . $cssMode;
    }
    
    $startTime = microtime(true);
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Performance Test)'
        ]
    ]);
    
    $content = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    
    return [
        'load_time' => round(($endTime - $startTime) * 1000, 2), // ms
        'content_size' => $content ? strlen($content) : 0,
        'success' => $content !== false
    ];
}

// CSS 파일 수집 및 분석
function analyzeCSSFiles($pagePath, $cssMode = null) {
    $url = 'http://localhost/hopec/' . $pagePath;
    if ($cssMode) {
        $url .= '?css_mode=' . $cssMode;
    }
    
    $content = @file_get_contents($url);
    if (!$content) return [];
    
    $cssFiles = [];
    $inlineCSS = [];
    
    // 외부 CSS 파일 추출
    if (preg_match_all('/<link[^>]+href=["\']([^"\']+\.css[^"\']*)["\'][^>]*>/i', $content, $matches)) {
        foreach ($matches[1] as $cssFile) {
            $cssFiles[] = $cssFile;
        }
    }
    
    // 인라인 CSS 추출
    if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $content, $matches)) {
        foreach ($matches[1] as $css) {
            $inlineCSS[] = strlen(trim($css));
        }
    }
    
    return [
        'external_css' => $cssFiles,
        'inline_css_sizes' => $inlineCSS,
        'total_inline_size' => array_sum($inlineCSS)
    ];
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>페이지별 로딩 시간 측정</title>
    <style>
        body { font-family: -apple-system, sans-serif; padding: 2rem; background: #f8faf9; }
        .container { max-width: 1400px; margin: 0 auto; }
        .comparison-table { width: 100%; border-collapse: collapse; margin: 2rem 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .comparison-table th, .comparison-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .comparison-table th { background: #f8faf9; font-weight: 600; }
        .performance-better { color: #16a34a; font-weight: bold; }
        .performance-worse { color: #dc2626; font-weight: bold; }
        .performance-neutral { color: #666; }
        .chart-container { background: white; padding: 1.5rem; border-radius: 8px; margin: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .bar-chart { display: flex; align-items: end; height: 200px; gap: 0.5rem; margin: 1rem 0; }
        .bar { background: linear-gradient(to top, #3a7a4e, #65a30d); border-radius: 4px 4px 0 0; min-width: 30px; position: relative; }
        .bar-legacy { background: linear-gradient(to top, #6b7280, #9ca3af); }
        .bar-label { position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); font-size: 0.8em; white-space: nowrap; }
        .legend { display: flex; gap: 2rem; margin: 1rem 0; }
        .legend-item { display: flex; align-items: center; gap: 0.5rem; }
        .legend-color { width: 20px; height: 20px; border-radius: 4px; }
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin: 2rem 0; }
        .summary-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .metric-large { font-size: 2em; font-weight: bold; color: #3a7a4e; }
        .progress-bar { width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; margin: 0.5rem 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #3a7a4e, #65a30d); border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 페이지별 로딩 시간 측정</h1>
        <p><strong>Phase 4A:</strong> 확장된 10개 페이지 성능 비교 - CSS Variables vs Legacy 모드</p>
        
        <?php
        echo "<p><strong>측정 시작:</strong> " . date('Y-m-d H:i:s') . "</p>";
        
        $results = [];
        $totalCSSVarsTime = 0;
        $totalLegacyTime = 0;
        $cssVarsWins = 0;
        $legacyWins = 0;
        
        // 각 페이지별 성능 측정
        foreach ($testPages as $page => $title) {
            echo "<div style='margin: 0.5rem 0; padding: 0.5rem; background: #e8f5e8; border-radius: 4px;'>";
            echo "🔍 측정 중: {$title} ({$page})...";
            echo "</div>";
            
            // CSS Variables 모드 측정
            $cssVarsResult = measurePageLoadTime($page, 'css-vars');
            $cssVarsCSS = analyzeCSSFiles($page, 'css-vars');
            
            // Legacy 모드 측정
            $legacyResult = measurePageLoadTime($page);
            $legacyCSS = analyzeCSSFiles($page);
            
            $results[$page] = [
                'title' => $title,
                'css_vars' => $cssVarsResult,
                'legacy' => $legacyResult,
                'css_vars_css' => $cssVarsCSS,
                'legacy_css' => $legacyCSS
            ];
            
            if ($cssVarsResult['success'] && $legacyResult['success']) {
                $totalCSSVarsTime += $cssVarsResult['load_time'];
                $totalLegacyTime += $legacyResult['load_time'];
                
                if ($cssVarsResult['load_time'] < $legacyResult['load_time']) {
                    $cssVarsWins++;
                } else {
                    $legacyWins++;
                }
            }
        }
        ?>
        
        <div class="summary-cards">
            <div class="summary-card">
                <h3>🎨 CSS Variables 모드</h3>
                <div class="metric-large"><?= round($totalCSSVarsTime, 1) ?>ms</div>
                <p>총 로딩 시간</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= min(100, ($totalCSSVarsTime / max($totalCSSVarsTime, $totalLegacyTime)) * 100) ?>%"></div>
                </div>
                <p><strong><?= $cssVarsWins ?></strong>개 페이지에서 승리</p>
            </div>
            
            <div class="summary-card">
                <h3>🏛️ Legacy 모드</h3>
                <div class="metric-large"><?= round($totalLegacyTime, 1) ?>ms</div>
                <p>총 로딩 시간</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= min(100, ($totalLegacyTime / max($totalCSSVarsTime, $totalLegacyTime)) * 100) ?>%"></div>
                </div>
                <p><strong><?= $legacyWins ?></strong>개 페이지에서 승리</p>
            </div>
            
            <div class="summary-card">
                <h3>⚡ 성능 개선</h3>
                <?php
                $improvement = (($totalLegacyTime - $totalCSSVarsTime) / $totalLegacyTime) * 100;
                $improvementClass = $improvement > 0 ? 'performance-better' : 'performance-worse';
                ?>
                <div class="metric-large <?= $improvementClass ?>"><?= round(abs($improvement), 1) ?>%</div>
                <p><?= $improvement > 0 ? '성능 향상' : '성능 저하' ?></p>
            </div>
            
            <div class="summary-card">
                <h3>📈 평균 로딩 시간</h3>
                <div class="metric-large"><?= round(($totalCSSVarsTime + $totalLegacyTime) / 2 / count($testPages), 1) ?>ms</div>
                <p>페이지당 평균</p>
            </div>
        </div>
        
        <div class="chart-container">
            <h3>📊 페이지별 로딩 시간 비교</h3>
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(to top, #3a7a4e, #65a30d);"></div>
                    <span>CSS Variables 모드</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(to top, #6b7280, #9ca3af);"></div>
                    <span>Legacy 모드</span>
                </div>
            </div>
            
            <?php
            $maxTime = 0;
            foreach ($results as $result) {
                if ($result['css_vars']['success'] && $result['legacy']['success']) {
                    $maxTime = max($maxTime, $result['css_vars']['load_time'], $result['legacy']['load_time']);
                }
            }
            ?>
            
            <div class="bar-chart">
                <?php foreach ($results as $page => $result): ?>
                    <?php if ($result['css_vars']['success'] && $result['legacy']['success']): ?>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                            <div class="bar" style="height: <?= ($result['css_vars']['load_time'] / $maxTime) * 180 ?>px; width: 25px;">
                                <div class="bar-label"><?= round($result['css_vars']['load_time'], 1) ?>ms</div>
                            </div>
                            <div class="bar bar-legacy" style="height: <?= ($result['legacy']['load_time'] / $maxTime) * 180 ?>px; width: 25px;">
                                <div class="bar-label" style="bottom: -45px;"><?= round($result['legacy']['load_time'], 1) ?>ms</div>
                            </div>
                            <div style="margin-top: 50px; font-size: 0.7em; writing-mode: vertical-rl; text-orientation: mixed;">
                                <?= substr($result['title'], 0, 8) ?>...
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>페이지</th>
                    <th>CSS Variables 모드</th>
                    <th>Legacy 모드</th>
                    <th>차이</th>
                    <th>콘텐츠 크기</th>
                    <th>인라인 CSS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $page => $result): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($result['title']) ?></strong><br>
                            <small><?= htmlspecialchars($page) ?></small>
                        </td>
                        <td>
                            <?php if ($result['css_vars']['success']): ?>
                                <strong><?= $result['css_vars']['load_time'] ?>ms</strong>
                            <?php else: ?>
                                <span style="color: #dc2626;">❌ 로드 실패</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($result['legacy']['success']): ?>
                                <strong><?= $result['legacy']['load_time'] ?>ms</strong>
                            <?php else: ?>
                                <span style="color: #dc2626;">❌ 로드 실패</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($result['css_vars']['success'] && $result['legacy']['success']): ?>
                                <?php
                                $diff = $result['css_vars']['load_time'] - $result['legacy']['load_time'];
                                $diffClass = $diff < 0 ? 'performance-better' : ($diff > 0 ? 'performance-worse' : 'performance-neutral');
                                $diffSymbol = $diff < 0 ? '⚡' : ($diff > 0 ? '🐌' : '➖');
                                ?>
                                <span class="<?= $diffClass ?>">
                                    <?= $diffSymbol ?> <?= round(abs($diff), 1) ?>ms
                                </span>
                            <?php else: ?>
                                <span class="performance-neutral">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>
                                CSS Vars: <?= number_format($result['css_vars']['content_size']) ?> bytes<br>
                                Legacy: <?= number_format($result['legacy']['content_size']) ?> bytes
                            </small>
                        </td>
                        <td>
                            <small>
                                CSS Vars: <?= number_format($result['css_vars_css']['total_inline_size']) ?> bytes<br>
                                Legacy: <?= number_format($result['legacy_css']['total_inline_size']) ?> bytes
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: #fff3cd; border-radius: 8px;">
            <h4>🚀 성능 최적화 실행 계획</h4>
            <ul>
                <?php if ($totalCSSVarsTime < $totalLegacyTime): ?>
                    <li><strong>✅ CSS Variables 모드가 더 빠름</strong> - 사용자에게 CSS Variables 모드 사용 권장</li>
                <?php else: ?>
                    <li><strong>⚠️ Legacy 모드가 더 빠름</strong> - CSS Variables 시스템 최적화 필요</li>
                <?php endif; ?>
                <li><strong>캐싱 구현:</strong> 인라인 CSS 생성 결과를 브라우저 세션에 캐싱</li>
                <li><strong>CSS 압축:</strong> 불필요한 공백과 주석 제거</li>
                <li><strong>지연 로딩:</strong> 필요한 경우에만 CSS Variables 시스템 로드</li>
                <li><strong>코드 스플리팅:</strong> 페이지별 필요한 CSS만 로드</li>
            </ul>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #e8f5e8; border-radius: 8px;">
            <h4>📋 다음 단계</h4>
            <ol>
                <li><strong>캐싱 시스템 구현</strong> - CSS Variables 결과 메모리 캐싱</li>
                <li><strong>CSS 압축 최적화</strong> - 인라인 CSS 크기 50% 감소 목표</li>
                <li><strong>조건부 로딩</strong> - 사용자 선호도 기반 시스템 로드</li>
                <li><strong>성능 모니터링</strong> - 실시간 성능 추적 시스템 구축</li>
            </ol>
        </div>
        
        <p style="margin-top: 2rem; color: #666; font-size: 0.9em;">
            <strong>측정 완료:</strong> <?= date('Y-m-d H:i:s') ?> | 
            <strong>총 측정 시간:</strong> <?= count($testPages) * 2 ?> 요청 |
            <strong>서버:</strong> <?= $_SERVER['SERVER_NAME'] ?? 'localhost' ?>
        </p>
    </div>
</body>
</html>