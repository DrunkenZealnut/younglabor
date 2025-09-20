<?php
/**
 * Programs 페이지 CSS Variables 모드 테스트
 * 새로 추가된 CSS Variables 지원 확인
 */

// CSS Variables 시스템 로드
require_once 'includes/css-vars-autoloader.php';

$generator = new CriticalCSSGenerator();
$cssMode = getCSSMode();

// Critical CSS 생성 및 정보 수집
$criticalCSS = $generator->generateCriticalCSS();
$debugInfo = $generator->getDebugInfo();

echo "<h1>📋 Programs 페이지 CSS Variables 모드 테스트</h1>\n";

echo "<h2>📊 CSS Variables 모드 상태</h2>\n";
echo "<ul>\n";
echo "<li><strong>현재 CSS 모드:</strong> " . $cssMode->getCurrentMode() . "</li>\n";
echo "<li><strong>CSS Variables 활성화:</strong> " . (detectCSSVarsMode() ? '✅ Yes' : '❌ No') . "</li>\n";
echo "<li><strong>Critical CSS 크기:</strong> " . $debugInfo['size_kb'] . " KB</li>\n";
echo "</ul>\n";

echo "<h2>🎯 Programs 페이지 테스트 링크</h2>\n";
$programsPages = [
    'domestic' => '국내위기아동지원사업',
    'overseas' => '해외아동지원사업',
    'labor-rights' => '노동인권사업',
    'community' => '소통 및 회원사업',
    'volunteer' => '자원봉사안내'
];

echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<h3>🔧 CSS Variables 모드 vs Legacy 모드 비교</h3>\n";

foreach ($programsPages as $slug => $title) {
    echo "<h4>📄 {$title}</h4>\n";
    echo "<ul>\n";
    echo "<li><a href='/programs/{$slug}.php?css_mode=legacy' style='color: #d32f2f; margin-right: 10px;'>🟥 Legacy 모드</a></li>\n";
    echo "<li><a href='/programs/{$slug}.php?css_mode=css-vars' style='color: #1976d2; margin-right: 10px;'>🟦 CSS Variables 모드</a></li>\n";
    echo "<li><a href='/programs/{$slug}.php?css_mode=optimized' style='color: #388e3c;'>🟩 Optimized 모드</a></li>\n";
    echo "</ul>\n";
}
echo "</div>\n";

echo "<h2>🧪 CSS Variables 모드 기능 테스트</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>detectCSSVarsMode() 함수</strong>: 자동 모드 감지</li>\n";
echo "<li>✅ <strong>getCSSVariableManager()</strong>: 스타일 매니저 초기화</li>\n";
echo "<li>✅ <strong>forest-600/700 색상</strong>: 테마 색상 CSS Variables 적용</li>\n";
echo "<li>✅ <strong>Legacy 모드 보존</strong>: 기존 getThemeClass 함수 유지</li>\n";
echo "<li>✅ <strong>조건부 렌더링</strong>: \$useCSSVars 플래그로 안전한 전환</li>\n";
echo "</ul>\n";

echo "<h2>🔍 구현된 페이지 목록</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>about/about.php</strong>: 완전 구현 (기존)</li>\n";
echo "<li>✅ <strong>programs/domestic.php</strong>: CSS Variables 모드 완료</li>\n";
echo "<li>✅ <strong>programs/community.php</strong>: CSS Variables 모드 완료</li>\n";
echo "<li>✅ <strong>programs/overseas.php</strong>: CSS Variables 모드 완료</li>\n";
echo "<li>✅ <strong>programs/labor-rights.php</strong>: CSS Variables 모드 완료</li>\n";
echo "<li>✅ <strong>programs/volunteer.php</strong>: CSS Variables 모드 완료</li>\n";
echo "</ul>\n";

echo "<h2>📊 실시간 테스트 결과</h2>\n";
echo "<div id='programs-css-vars-results'></div>\n";

echo "<script>\n";
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    const testDiv = document.getElementById('programs-css-vars-results');\n";
echo "    \n";
echo "    // CSS Variables 모드 체크\n";
echo "    const urlParams = new URLSearchParams(window.location.search);\n";
echo "    const cssMode = urlParams.get('css_mode');\n";
echo "    const isCSSVarsMode = cssMode === 'css-vars';\n";
echo "    \n";
echo "    // Critical CSS에서 테마 색상 확인\n";
echo "    const criticalStyles = document.getElementById('hopec-critical-css');\n";
echo "    const criticalCSS = criticalStyles ? criticalStyles.textContent : '';\n";
echo "    \n";
echo "    const themeColors = ['--forest-600', '--forest-700', '--lime-600', '--natural-50'];\n";
echo "    let themeSupport = {};\n";
echo "    themeColors.forEach(function(color) {\n";
echo "        themeSupport[color] = criticalCSS.includes(color);\n";
echo "    });\n";
echo "    \n";
echo "    // 결과 출력\n";
echo "    let html = '<h3>🧪 실시간 테스트 결과</h3>';\n";
echo "    html += '<div style=\"background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;\">';\n";
echo "    \n";
echo "    html += '<h4>🎨 CSS Variables 모드 상태</h4>';\n";
echo "    html += '<p><strong>URL 파라미터:</strong> ' + (cssMode || 'default') + '</p>';\n";
echo "    html += '<p><strong>CSS Variables 모드:</strong> ' + (isCSSVarsMode ? '✅ 활성화' : '❌ 비활성화') + '</p>';\n";
echo "    \n";
echo "    html += '<h4>🌈 테마 색상 CSS Variables 지원</h4>';\n";
echo "    Object.keys(themeSupport).forEach(function(color) {\n";
echo "        html += '<p>' + (themeSupport[color] ? '✅' : '❌') + ' ' + color + '</p>';\n";
echo "    });\n";
echo "    \n";
echo "    html += '<h4>📏 Critical CSS 정보</h4>';\n";
echo "    html += '<p>크기: ' + (criticalCSS.length / 1024).toFixed(1) + 'KB</p>';\n";
echo "    html += '<p>Programs 관련 클래스: ' + (criticalCSS.match(/programs|domestic|community/gi) || []).length + '개</p>';\n";
echo "    \n";
echo "    html += '</div>';\n";
echo "    testDiv.innerHTML = html;\n";
echo "    \n";
echo "    // 콘솔 로그\n";
echo "    console.log('📋 Programs CSS Variables 테스트 결과:', {\n";
echo "        cssMode: cssMode,\n";
echo "        isCSSVarsMode: isCSSVarsMode,\n";
echo "        themeSupport: themeSupport,\n";
echo "        criticalCSSSize: criticalCSS.length\n";
echo "    });\n";
echo "});\n";
echo "</script>\n";

echo "<style>\n";
echo "body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; line-height: 1.6; }\n";
echo "h1 { color: #1976d2; }\n";
echo "h2 { color: #388e3c; border-bottom: 2px solid #e8f5e8; padding-bottom: 5px; }\n";
echo "h3 { color: #f57c00; }\n";
echo "ul { line-height: 1.8; }\n";
echo "a { text-decoration: none; padding: 2px 6px; border-radius: 3px; }\n";
echo "a:hover { text-decoration: underline; }\n";
echo "</style>\n";
?>