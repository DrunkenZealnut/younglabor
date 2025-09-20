<?php
/**
 * 레이아웃 수정 테스트 스크립트
 * 가운데 정렬 및 스크롤 문제 해결 확인
 */

// 필요한 파일들 로드
require_once 'includes/critical-css-generator.php';
require_once 'includes/css-mode-manager.php';

$generator = new CriticalCSSGenerator();
$cssMode = getCSSMode();

// Critical CSS 생성 및 정보 수집
$criticalCSS = $generator->generateCriticalCSS();
$debugInfo = $generator->getDebugInfo();

echo "<h1>📐 레이아웃 수정 테스트</h1>\n";

echo "<h2>🎯 수정된 Critical CSS 확인</h2>\n";
echo "<ul>\n";
echo "<li><strong>크기:</strong> " . $debugInfo['size_kb'] . " KB</li>\n";
echo "<li><strong>권장 크기 내:</strong> " . ($debugInfo['within_limit'] ? '✅ Yes' : '❌ No') . "</li>\n";
echo "</ul>\n";

echo "<h2>🔍 핵심 레이아웃 클래스 포함 여부</h2>\n";
$checkClasses = [
    '#wrapper' => '페이지 래퍼 컨테이너',
    '#container_wr' => '컨테이너 래퍼',
    '#container {' => '메인 컨테이너',
    'margin: 0 auto' => '중앙 정렬',
    'overflow-x: hidden' => '수평 스크롤 방지',
    'max-width: 100vw' => '뷰포트 폭 제한',
    'container-xl' => 'Bootstrap XL 컨테이너',
    'overflow-md-visible' => '반응형 오버플로 제어'
];

echo "<ul>\n";
foreach ($checkClasses as $class => $description) {
    $exists = strpos($criticalCSS, $class) !== false;
    echo "<li>" . ($exists ? '✅' : '❌') . " <strong>{$class}</strong>: {$description}</li>\n";
}
echo "</ul>\n";

echo "<h2>🖥️ 브라우저 테스트</h2>\n";
echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<h3>🔧 CSS 모드 전환</h3>\n";
echo "<p><strong>현재 모드:</strong> <span style='background: #1976d2; color: white; padding: 2px 8px; border-radius: 3px;'>" . $cssMode->getCurrentMode() . "</span></p>\n";

echo "<p><strong>테스트 링크:</strong></p>\n";
echo "<ul>\n";
echo "<li><a href='/about/org.php?css_mode=legacy' style='color: #d32f2f;'>🟥 org.php - Legacy 모드</a></li>\n";
echo "<li><a href='/about/org.php?css_mode=optimized' style='color: #388e3c;'>🟩 org.php - Optimized 모드 (수정됨)</a></li>\n";
echo "<li><a href='/?css_mode=optimized' style='color: #388e3c;'>🟩 메인페이지 - Optimized 모드</a></li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>📋 수정 내용 요약</h2>\n";
echo "<ul>\n";
echo "<li>✅ 페이지 레이아웃 컨테이너 추가 (#wrapper, #container_wr, #container)</li>\n";
echo "<li>✅ 모든 컨테이너에 margin: 0 auto 중앙 정렬 적용</li>\n";
echo "<li>✅ overflow-x: hidden으로 수평 스크롤 방지</li>\n";
echo "<li>✅ body max-width: 100vw로 뷰포트 넘침 방지</li>\n";
echo "<li>✅ 네비게이션 overflow 강제 visible 처리</li>\n";
echo "<li>✅ Bootstrap container 중앙 정렬 보장</li>\n";
echo "</ul>\n";

echo "<h2>🧪 실시간 레이아웃 테스트</h2>\n";
echo "<div id='layout-test-results'></div>\n";

echo "<script>\n";
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    const testDiv = document.getElementById('layout-test-results');\n";
echo "    \n";
echo "    // 수평 스크롤 확인\n";
echo "    const hasHorizontalScrollbar = document.body.scrollWidth > window.innerWidth;\n";
echo "    \n";
echo "    // body 너비 확인\n";
echo "    const bodyStyle = getComputedStyle(document.body);\n";
echo "    const bodyWidth = bodyStyle.width;\n";
echo "    const bodyMaxWidth = bodyStyle.maxWidth;\n";
echo "    \n";
echo "    // container 중앙 정렬 확인\n";
echo "    const containers = document.querySelectorAll('.container, .container-xl, #wrapper, #container_wr, #container');\n";
echo "    let containerMargins = [];\n";
echo "    containers.forEach(function(container) {\n";
echo "        const style = getComputedStyle(container);\n";
echo "        containerMargins.push({\n";
echo "            element: container.tagName + (container.id ? '#' + container.id : '') + (container.className ? '.' + container.className.split(' ')[0] : ''),\n";
echo "            marginLeft: style.marginLeft,\n";
echo "            marginRight: style.marginRight,\n";
echo "            width: style.width,\n";
echo "            maxWidth: style.maxWidth\n";
echo "        });\n";
echo "    });\n";
echo "    \n";
echo "    // 결과 출력\n";
echo "    let html = '<h3>📊 레이아웃 테스트 결과</h3>';\n";
echo "    html += '<div style=\"background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px;\">';\n";
echo "    html += '<p><strong>수평 스크롤:</strong> ' + (hasHorizontalScrollbar ? '❌ 있음' : '✅ 없음') + '</p>';\n";
echo "    html += '<p><strong>Body 너비:</strong> ' + bodyWidth + '</p>';\n";
echo "    html += '<p><strong>Body 최대너비:</strong> ' + bodyMaxWidth + '</p>';\n";
echo "    html += '<h4>컨테이너 중앙 정렬 상태:</h4>';\n";
echo "    containerMargins.forEach(function(container) {\n";
echo "        const isCentered = container.marginLeft === 'auto' && container.marginRight === 'auto';\n";
echo "        html += '<p>' + (isCentered ? '✅' : '❌') + ' <strong>' + container.element + '</strong><br>';\n";
echo "        html += '&nbsp;&nbsp;margin: ' + container.marginLeft + ' / ' + container.marginRight + '<br>';\n";
echo "        html += '&nbsp;&nbsp;width: ' + container.width + ' (max: ' + container.maxWidth + ')</p>';\n";
echo "    });\n";
echo "    html += '</div>';\n";
echo "    \n";
echo "    testDiv.innerHTML = html;\n";
echo "    \n";
echo "    // 콘솔 로그\n";
echo "    console.log('🧪 레이아웃 테스트 결과:', {\n";
echo "        hasHorizontalScrollbar: hasHorizontalScrollbar,\n";
echo "        bodyWidth: bodyWidth,\n";
echo "        bodyMaxWidth: bodyMaxWidth,\n";
echo "        containerMargins: containerMargins\n";
echo "    });\n";
echo "});\n";
echo "</script>\n";

echo "<style>\n";
echo "body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; }\n";
echo "h1 { color: #1976d2; }\n";
echo "h2 { color: #388e3c; border-bottom: 2px solid #e8f5e8; padding-bottom: 5px; }\n";
echo "ul { line-height: 1.6; }\n";
echo "a { text-decoration: none; }\n";
echo "a:hover { text-decoration: underline; }\n";
echo "</style>\n";
?>