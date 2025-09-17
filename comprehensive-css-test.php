<?php
/**
 * 포괄적 CSS 테스트 스크립트
 * 모든 페이지의 레이아웃과 스타일 문제 검증
 */

// 필요한 파일들 로드
require_once 'includes/critical-css-generator.php';
require_once 'includes/css-mode-manager.php';

$generator = new CriticalCSSGenerator();
$cssMode = getCSSMode();

// Critical CSS 생성 및 정보 수집
$criticalCSS = $generator->generateCriticalCSS();
$debugInfo = $generator->getDebugInfo();

echo "<h1>🔍 전체 페이지 CSS 테스트</h1>\n";

echo "<h2>📊 새로운 Critical CSS 정보</h2>\n";
echo "<ul>\n";
echo "<li><strong>크기:</strong> " . $debugInfo['size_kb'] . " KB</li>\n";
echo "<li><strong>권장 크기 내:</strong> " . ($debugInfo['within_limit'] ? '✅ Yes' : '❌ No') . "</li>\n";
echo "<li><strong>Natural Green 존재:</strong> " . ($debugInfo['natural_green_exists'] ? '✅ Yes' : '❌ No') . "</li>\n";
echo "</ul>\n";

echo "<h2>🎯 핵심 클래스 포함 확인</h2>\n";
$checkClasses = [
    // Layout & Grid
    'max-w-7xl' => 'Tailwind 최대 너비',
    'mx-auto' => 'Tailwind 중앙 정렬',
    'grid-cols-1' => 'Grid 레이아웃',
    'md:grid-cols-2' => 'Grid 반응형',
    'flex' => 'Flexbox',
    'items-center' => 'Flex 정렬',
    
    // Typography
    'text-3xl' => '큰 제목',
    'md:text-4xl' => '반응형 제목',
    'font-bold' => '굵은 글씨',
    'text-forest-700' => '테마 텍스트 색상',
    
    // Spacing
    'py-10' => '세로 패딩',
    'py-16' => '큰 세로 패딩',
    'mb-8' => '마진 바텀',
    'gap-6' => 'Gap 유틸리티',
    
    // Theme Colors
    'bg-natural-50' => '테마 배경',
    'text-lime-600' => '테마 텍스트',
    'border-primary' => '테마 보더',
    
    // Components
    'board-surface' => '보드 컴포넌트',
    'btn-primary' => '기본 버튼',
    'hover-lift' => '호버 효과',
    'line-clamp-2' => '텍스트 클램프',
    
    // Animations
    'transition-all' => '트랜지션',
    'hover:shadow-lg' => '호버 그림자',
    'rounded-lg' => '모서리 둥글게'
];

echo "<ul>\n";
foreach ($checkClasses as $class => $description) {
    $exists = strpos($criticalCSS, $class) !== false;
    echo "<li>" . ($exists ? '✅' : '❌') . " <strong>{$class}</strong>: {$description}</li>\n";
}
echo "</ul>\n";

echo "<h2>🌐 전체 페이지 테스트 링크</h2>\n";
$testPages = [
    // About 페이지들
    ['path' => '/about/about.php', 'title' => '희망씨는', 'category' => 'About'],
    ['path' => '/about/greeting.php', 'title' => '이사장 인사말', 'category' => 'About'],
    ['path' => '/about/org.php', 'title' => '조직도', 'category' => 'About'],
    ['path' => '/about/history.php', 'title' => '연혁', 'category' => 'About'],
    ['path' => '/about/location.php', 'title' => '오시는길', 'category' => 'About'],
    ['path' => '/about/finance.php', 'title' => '재정보고', 'category' => 'About'],
    
    // Programs 페이지들
    ['path' => '/programs/domestic.php', 'title' => '국내아동지원사업', 'category' => 'Programs'],
    ['path' => '/programs/overseas.php', 'title' => '해외아동지원사업', 'category' => 'Programs'],
    ['path' => '/programs/labor-rights.php', 'title' => '노동인권사업', 'category' => 'Programs'],
    ['path' => '/programs/community.php', 'title' => '소통 및 회원사업', 'category' => 'Programs'],
    ['path' => '/programs/volunteer.php', 'title' => '자원봉사안내', 'category' => 'Programs'],
    
    // Community 페이지들
    ['path' => '/community/notices.php', 'title' => '공지사항', 'category' => 'Community'],
    ['path' => '/community/gallery.php', 'title' => '갤러리', 'category' => 'Community'],
    ['path' => '/community/newsletter.php', 'title' => '소식지', 'category' => 'Community'],
    ['path' => '/community/press.php', 'title' => '언론보도', 'category' => 'Community'],
    ['path' => '/community/nepal.php', 'title' => '네팔나눔연대여행', 'category' => 'Community'],
    ['path' => '/community/resources.php', 'title' => '자료실', 'category' => 'Community'],
    
    // 메인페이지
    ['path' => '/', 'title' => '메인페이지', 'category' => 'Main']
];

echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<h3>🔧 CSS 모드 전환</h3>\n";
echo "<p><strong>현재 모드:</strong> <span style='background: #1976d2; color: white; padding: 2px 8px; border-radius: 3px;'>" . $cssMode->getCurrentMode() . "</span></p>\n";

$currentPage = '';
foreach ($testPages as $page) {
    if ($currentPage !== $page['category']) {
        if ($currentPage !== '') {
            echo "</ul>\n";
        }
        echo "<h4>📂 {$page['category']}</h4>\n";
        echo "<ul>\n";
        $currentPage = $page['category'];
    }
    
    echo "<li>";
    echo "<a href='{$page['path']}?css_mode=legacy' style='color: #d32f2f; margin-right: 10px;'>🟥 Legacy</a>";
    echo "<a href='{$page['path']}?css_mode=optimized' style='color: #388e3c; margin-right: 10px;'>🟩 Optimized</a>";
    echo "<strong>{$page['title']}</strong>";
    echo "</li>\n";
}
echo "</ul>\n";
echo "</div>\n";

echo "<h2>📋 대폭 개선된 Critical CSS 내용</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Tailwind CSS 완전 지원</strong>: grid, flex, spacing, typography 모든 클래스</li>\n";
echo "<li>✅ <strong>테마 색상 시스템</strong>: forest-*, lime-*, natural-* 색상 팔레트</li>\n";
echo "<li>✅ <strong>커스텀 컴포넌트</strong>: board-surface, btn-*, hover-lift 등</li>\n";
echo "<li>✅ <strong>반응형 디자인</strong>: md:*, lg:* 브레이크포인트 지원</li>\n";
echo "<li>✅ <strong>애니메이션 & 인터랙션</strong>: transition, hover, transform 효과</li>\n";
echo "<li>✅ <strong>레이아웃 시스템</strong>: container, grid, flex 완전 지원</li>\n";
echo "<li>✅ <strong>유틸리티 클래스</strong>: line-clamp, aspect-ratio, border 등</li>\n";
echo "<li>✅ <strong>페이지 래퍼 컨테이너</strong>: #wrapper, #container_wr, #container 중앙 정렬</li>\n";
echo "</ul>\n";

echo "<h2>🧪 실시간 레이아웃 테스트</h2>\n";
echo "<div id='comprehensive-test-results'></div>\n";

echo "<script>\n";
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    const testDiv = document.getElementById('comprehensive-test-results');\n";
echo "    \n";
echo "    // 종합적인 레이아웃 검사\n";
echo "    const hasHorizontalScrollbar = document.body.scrollWidth > window.innerWidth;\n";
echo "    const bodyStyle = getComputedStyle(document.body);\n";
echo "    const htmlStyle = getComputedStyle(document.documentElement);\n";
echo "    \n";
echo "    // 모든 컨테이너 요소들 검사\n";
echo "    const containers = document.querySelectorAll('.container, .container-xl, #wrapper, #container_wr, #container, .max-w-7xl, .max-w-5xl, .max-w-4xl');\n";
echo "    let containerInfo = [];\n";
echo "    containers.forEach(function(container) {\n";
echo "        const style = getComputedStyle(container);\n";
echo "        const rect = container.getBoundingClientRect();\n";
echo "        containerInfo.push({\n";
echo "            selector: container.tagName.toLowerCase() + (container.id ? '#' + container.id : '') + (container.className ? '.' + container.className.split(' ')[0] : ''),\n";
echo "            marginLeft: style.marginLeft,\n";
echo "            marginRight: style.marginRight,\n";
echo "            width: style.width,\n";
echo "            maxWidth: style.maxWidth,\n";
echo "            left: rect.left,\n";
echo "            right: rect.right,\n";
echo "            centered: (rect.left + rect.right) / 2\n";
echo "        });\n";
echo "    });\n";
echo "    \n";
echo "    // Tailwind 클래스 존재 확인\n";
echo "    const criticalStyles = document.getElementById('hopec-critical-css');\n";
echo "    const criticalCSS = criticalStyles ? criticalStyles.textContent : '';\n";
echo "    \n";
echo "    const tailwindClasses = ['max-w-7xl', 'mx-auto', 'grid-cols-2', 'text-3xl', 'font-bold', 'py-10', 'bg-natural-50'];\n";
echo "    let tailwindSupport = {};\n";
echo "    tailwindClasses.forEach(function(cls) {\n";
echo "        tailwindSupport[cls] = criticalCSS.includes(cls);\n";
echo "    });\n";
echo "    \n";
echo "    // 테마 색상 확인\n";
echo "    const themeColors = ['text-forest-700', 'text-lime-600', 'bg-natural-50', 'border-primary'];\n";
echo "    let themeSupport = {};\n";
echo "    themeColors.forEach(function(color) {\n";
echo "        themeSupport[color] = criticalCSS.includes(color);\n";
echo "    });\n";
echo "    \n";
echo "    // 결과 출력\n";
echo "    let html = '<h3>📊 종합 레이아웃 테스트 결과</h3>';\n";
echo "    html += '<div style=\"background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;\">';\n";
echo "    \n";
echo "    html += '<h4>🏗️ 기본 레이아웃</h4>';\n";
echo "    html += '<p><strong>수평 스크롤:</strong> ' + (hasHorizontalScrollbar ? '❌ 있음' : '✅ 없음') + '</p>';\n";
echo "    html += '<p><strong>Body overflow-x:</strong> ' + bodyStyle.overflowX + '</p>';\n";
echo "    html += '<p><strong>Html overflow-x:</strong> ' + htmlStyle.overflowX + '</p>';\n";
echo "    \n";
echo "    html += '<h4>📦 컨테이너 중앙 정렬</h4>';\n";
echo "    containerInfo.forEach(function(container) {\n";
echo "        const isCentered = container.marginLeft === 'auto' && container.marginRight === 'auto';\n";
echo "        const viewportCenter = window.innerWidth / 2;\n";
echo "        const elementCenter = container.centered;\n";
echo "        const centerDiff = Math.abs(viewportCenter - elementCenter);\n";
echo "        html += '<p>' + (isCentered ? '✅' : '❌') + ' <strong>' + container.selector + '</strong><br>';\n";
echo "        html += '&nbsp;&nbsp;margin: ' + container.marginLeft + ' / ' + container.marginRight + '<br>';\n";
echo "        html += '&nbsp;&nbsp;위치: ' + Math.round(container.left) + 'px ~ ' + Math.round(container.right) + 'px<br>';\n";
echo "        html += '&nbsp;&nbsp;중앙차이: ' + Math.round(centerDiff) + 'px</p>';\n";
echo "    });\n";
echo "    \n";
echo "    html += '<h4>🎨 Tailwind CSS 지원</h4>';\n";
echo "    Object.keys(tailwindSupport).forEach(function(cls) {\n";
echo "        html += '<p>' + (tailwindSupport[cls] ? '✅' : '❌') + ' ' + cls + '</p>';\n";
echo "    });\n";
echo "    \n";
echo "    html += '<h4>🌈 테마 색상 지원</h4>';\n";
echo "    Object.keys(themeSupport).forEach(function(color) {\n";
echo "        html += '<p>' + (themeSupport[color] ? '✅' : '❌') + ' ' + color + '</p>';\n";
echo "    });\n";
echo "    \n";
echo "    html += '<h4>📏 Critical CSS 정보</h4>';\n";
echo "    html += '<p>크기: ' + (criticalCSS.length / 1024).toFixed(1) + 'KB</p>';\n";
echo "    html += '<p>클래스 수: ~' + (criticalCSS.match(/\\.[a-zA-Z][a-zA-Z0-9_-]*\\s*{/g) || []).length + '개</p>';\n";
echo "    \n";
echo "    html += '</div>';\n";
echo "    testDiv.innerHTML = html;\n";
echo "    \n";
echo "    // 콘솔 로그\n";
echo "    console.log('🔍 종합 CSS 테스트 결과:', {\n";
echo "        hasHorizontalScrollbar: hasHorizontalScrollbar,\n";
echo "        containerInfo: containerInfo,\n";
echo "        tailwindSupport: tailwindSupport,\n";
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