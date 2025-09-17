<?php
/**
 * Critical CSS 테스트 스크립트
 * 수정된 CSS 크기와 내용 확인
 */

// 필요한 파일들 로드
require_once 'includes/critical-css-generator.php';
require_once 'includes/css-mode-manager.php';

$generator = new CriticalCSSGenerator();

// Critical CSS 생성 및 정보 수집
$criticalCSS = $generator->generateCriticalCSS();
$debugInfo = $generator->getDebugInfo();

echo "<h1>Critical CSS 수정 결과</h1>\n";

echo "<h2>📊 크기 정보</h2>\n";
echo "<ul>\n";
echo "<li><strong>크기:</strong> " . $debugInfo['size_kb'] . " KB</li>\n";
echo "<li><strong>권장 크기 내:</strong> " . ($debugInfo['within_limit'] ? '✅ Yes' : '❌ No') . "</li>\n";
echo "<li><strong>Natural Green 존재:</strong> " . ($debugInfo['natural_green_exists'] ? '✅ Yes' : '❌ No') . "</li>\n";
echo "</ul>\n";

echo "<h2>🔍 Critical CSS 내용 미리보기</h2>\n";
echo "<details><summary>CSS 코드 보기</summary>\n";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px;'>\n";
echo htmlspecialchars(substr($criticalCSS, 0, 3000));
if (strlen($criticalCSS) > 3000) {
    echo "\n\n... (생략된 내용: " . (strlen($criticalCSS) - 3000) . " 글자)\n";
}
echo "</pre></details>\n";

echo "<h2>🎯 주요 클래스 포함 여부 확인</h2>\n";
$checkClasses = [
    'navbar-expand-lg' => 'Bootstrap 반응형 네비게이션',
    'd-md-flex' => '반응형 display',
    'd-md-none' => '반응형 hide',
    'dropdown-menu' => '드롭다운 메뉴',
    'container-xl' => '컨테이너 확장',
    'sticky-top' => 'Sticky position',
    'text-forest-600' => '테마 색상',
    'backdrop-blur-md' => 'Backdrop filter'
];

echo "<ul>\n";
foreach ($checkClasses as $class => $description) {
    $exists = strpos($criticalCSS, $class) !== false;
    echo "<li>" . ($exists ? '✅' : '❌') . " <strong>{$class}</strong>: {$description}</li>\n";
}
echo "</ul>\n";

echo "<h2>⚡ 네비게이션 테스트</h2>\n";
echo "<p>Critical CSS로 네비게이션이 제대로 렌더링되는지 확인:</p>\n";

// CSS 모드 테스트 링크
$currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
$cssMode = getCSSMode();

echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<h3>🔧 CSS 모드 전환</h3>\n";
echo "<p><strong>현재 모드:</strong> <span style='background: #1976d2; color: white; padding: 2px 8px; border-radius: 3px;'>" . $cssMode->getCurrentMode() . "</span></p>\n";

echo "<p><strong>테스트 링크:</strong></p>\n";
echo "<ul>\n";
echo "<li><a href='?css_mode=legacy' style='color: #d32f2f;'>🟥 Legacy 모드 (안전한 기본값)</a></li>\n";
echo "<li><a href='?css_mode=optimized' style='color: #388e3c;'>🟩 Optimized 모드 (수정된 Critical CSS)</a></li>\n";
echo "<li><a href='?css_mode=debug' style='color: #1976d2;'>🟦 Debug 모드 (개발자 정보)</a></li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>📋 수정 내용 요약</h2>\n";
echo "<ul>\n";
echo "<li>✅ Bootstrap navbar-expand-lg 반응형 클래스 추가</li>\n";
echo "<li>✅ 필수 display 유틸리티 (d-md-flex, d-md-none) 추가</li>\n";
echo "<li>✅ 드롭다운 메뉴 스타일 완성</li>\n";
echo "<li>✅ container-xl 지원 추가</li>\n";
echo "<li>✅ Tailwind 호환 클래스들 추가</li>\n";
echo "<li>✅ 네비게이션 색상 시스템 (text-forest-600) 추가</li>\n";
echo "<li>✅ Position 및 레이아웃 유틸리티 추가</li>\n";
echo "</ul>\n";

echo "<style>\n";
echo "body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; }\n";
echo "h1 { color: #1976d2; }\n";
echo "h2 { color: #388e3c; border-bottom: 2px solid #e8f5e8; padding-bottom: 5px; }\n";
echo "ul { line-height: 1.6; }\n";
echo "a { text-decoration: none; }\n";
echo "a:hover { text-decoration: underline; }\n";
echo "</style>\n";
?>