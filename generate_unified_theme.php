<?php
/**
 * 통합 테마 CSS 생성기
 * Physical Theme + Natural Green Globals.css 통합
 */

require_once __DIR__ . '/includes/physical_theme_manager.php';

$physicalThemeManager = new PhysicalThemeManager();
$currentTheme = $physicalThemeManager->getCurrentTheme();

echo "<h1>통합 테마 CSS 생성기</h1>";

// 1. 현재 테마 파일들 분석
$themeCssPath = __DIR__ . '/css/theme.css';
$globalsPath = __DIR__ . '/theme/natural-green/styles/globals.css';

echo "<h2>1. 현재 파일 상태</h2>";
echo "현재 활성 테마: <strong>{$currentTheme}</strong><br>";
echo "theme.css 존재: " . (file_exists($themeCssPath) ? "✅" : "❌") . "<br>";
echo "globals.css 존재: " . (file_exists($globalsPath) ? "✅" : "❌") . "<br>";

if (isset($_POST['generate_unified'])) {
    echo "<h2>2. 통합 CSS 생성 중...</h2>";
    
    // 백업 생성
    $backupDir = __DIR__ . '/css/backup_' . date('YmdHis');
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    if (file_exists($themeCssPath)) {
        copy($themeCssPath, $backupDir . '/theme_original.css');
        echo "✅ 기존 theme.css 백업 완료<br>";
    }
    
    // 통합 CSS 생성
    $unifiedCSS = generateUnifiedCSS($currentTheme, $themeCssPath, $globalsPath);
    
    if (file_put_contents($themeCssPath, $unifiedCSS)) {
        echo "✅ 통합 theme.css 생성 완료<br>";
        echo "📁 백업 위치: {$backupDir}/<br>";
        
        // 파일 크기 확인
        $newSize = filesize($themeCssPath);
        echo "📊 새 파일 크기: " . number_format($newSize) . " bytes<br>";
        
        // CSS 변수 개수 확인
        $varCount = substr_count($unifiedCSS, '--');
        echo "🎨 CSS 변수 개수: {$varCount}개<br>";
        
    } else {
        echo "❌ CSS 파일 생성 실패<br>";
    }
}

/**
 * 통합 CSS 생성 함수
 */
function generateUnifiedCSS($currentTheme, $themeCssPath, $globalsPath) {
    $css = "/* Unified Theme CSS - {$currentTheme} */\n";
    $css .= "/* Generated: " . date('Y-m-d H:i:s') . " */\n";
    $css .= "/* Combines Physical Theme + Natural Green Globals */\n\n";
    
    // 1. 물리적 테마 CSS 변수 읽기
    $themeVars = [];
    if (file_exists($themeCssPath)) {
        $themeContent = file_get_contents($themeCssPath);
        if (preg_match('/:root\s*\{([^}]+)\}/', $themeContent, $matches)) {
            $themeVars = extractCSSVariables($matches[1]);
        }
    }
    
    // 2. Globals CSS 변수 및 스타일 읽기
    $globalsVars = [];
    $globalStyles = '';
    if (file_exists($globalsPath)) {
        $globalsContent = file_get_contents($globalsPath);
        
        // CSS 변수 추출
        if (preg_match('/:root\s*\{([^}]+)\}/', $globalsContent, $matches)) {
            $globalsVars = extractCSSVariables($matches[1]);
        }
        
        // 다른 스타일들 추출 (변수 제외)
        $globalStyles = preg_replace('/:root\s*\{[^}]+\}/', '', $globalsContent);
        $globalStyles = preg_replace('/@custom-variant[^;]+;/', '', $globalStyles);
        $globalStyles = trim($globalStyles);
    }
    
    // 3. 변수 병합 (물리적 테마 우선)
    $mergedVars = array_merge($globalsVars, $themeVars);
    
    // 4. CSS 구성
    $css .= ":root {\n";
    foreach ($mergedVars as $name => $value) {
        $css .= "    --{$name}: {$value};\n";
    }
    $css .= "}\n\n";
    
    // 5. Tailwind 기반 유틸리티 클래스들
    $css .= "/* Tailwind CSS Utilities */\n";
    $css .= $globalStyles . "\n\n";
    
    // 6. 기본 스타일 추가
    $css .= getEnhancedBaseStyles();
    
    return $css;
}

/**
 * CSS 변수 추출 함수
 */
function extractCSSVariables($cssBlock) {
    $vars = [];
    preg_match_all('/--([^:]+):\s*([^;]+);/', $cssBlock, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $name = trim($match[1]);
        $value = trim($match[2]);
        $vars[$name] = $value;
    }
    
    return $vars;
}

/**
 * 향상된 기본 스타일
 */
function getEnhancedBaseStyles() {
    return "
/* Enhanced Base Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: var(--background);
    color: var(--foreground);
    line-height: 1.6;
    margin: 0;
    padding: 0;
}

/* 버튼 스타일 */
.btn-primary {
    background-color: var(--primary);
    border-color: var(--primary);
    color: var(--primary-foreground);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: var(--primary-hover, var(--primary));
    border-color: var(--primary-hover, var(--primary));
    transform: translateY(-1px);
}

/* 카드 스타일 */
.card {
    background-color: var(--card);
    color: var(--card-foreground);
    border: 1px solid var(--border);
    border-radius: var(--radius, 0.5rem);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

/* 폼 요소 */
.form-control {
    background-color: var(--input);
    border: 1px solid var(--input-border, var(--border));
    color: var(--foreground);
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
}

/* 유틸리티 클래스 */
.text-primary { color: var(--primary) !important; }
.text-muted { color: var(--muted-foreground) !important; }
.bg-primary { background-color: var(--primary) !important; }
.bg-muted { background-color: var(--muted) !important; }
.border { border: 1px solid var(--border) !important; }
.border-primary { border-color: var(--primary) !important; }

/* 반응형 헬퍼 */
.container-fluid {
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
}

@media (min-width: 768px) {
    .container-fluid {
        padding-left: 30px;
        padding-right: 30px;
    }
}

/* 애니메이션 */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* 로딩 상태 표시 */
.theme-loading {
    position: relative;
}

.theme-loading::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid var(--muted);
    border-top: 2px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
";
}

?>

<h2>통합 CSS 생성</h2>
<form method="post" style="margin: 20px 0;">
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
        <h4>생성될 통합 CSS 구성:</h4>
        <ul>
            <li>✅ <?= $currentTheme ?> 테마의 CSS 변수</li>
            <li>✅ Natural Green의 Tailwind CSS 유틸리티</li>
            <li>✅ 향상된 기본 스타일</li>
            <li>✅ 반응형 및 애니메이션 효과</li>
        </ul>
        <p><strong>결과:</strong> 단일 통합 CSS 파일로 충돌 없는 테마 로딩</p>
    </div>
    
    <input type="submit" name="generate_unified" value="통합 CSS 생성하기" 
           style="background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
</form>

<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 20px;">
    <h4>⚠️ 주의사항:</h4>
    <ul>
        <li>기존 theme.css 파일은 자동으로 백업됩니다</li>
        <li>생성된 CSS는 모든 테마에서 호환됩니다</li>
        <li>문제 발생 시 백업 파일로 복원 가능합니다</li>
    </ul>
</div>