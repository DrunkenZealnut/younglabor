<?php
/**
 * Critical CSS 캐시 클리어 스크립트
 * CSS 수정 후 캐시를 강제로 클리어하여 변경사항을 즉시 적용
 */

require_once __DIR__ . '/includes/critical-css-generator.php';

// Critical CSS 생성기 초기화
$generator = new CriticalCSSGenerator();

// 캐시 클리어
$clearedFiles = $generator->clearCache();

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<meta charset='utf-8'>\n";
echo "<title>CSS 캐시 클리어</title>\n";
echo "</head>\n<body>\n";
echo "<h1>Critical CSS 캐시 클리어 완료</h1>\n";
echo "<p>클리어된 캐시 파일 수: {$clearedFiles}</p>\n";

// 새로운 Critical CSS 생성 및 확인
$newCSS = $generator->generateCriticalCSS();
$debugInfo = $generator->getDebugInfo();

echo "<h2>새로운 Critical CSS 정보</h2>\n";
echo "<ul>\n";
echo "<li>크기: {$debugInfo['size_kb']} KB</li>\n";
echo "<li>권장 크기 내: " . ($debugInfo['within_limit'] ? '예' : '아니오') . "</li>\n";
echo "<li>Natural Green 테마 존재: " . ($debugInfo['natural_green_exists'] ? '예' : '아니오') . "</li>\n";
echo "</ul>\n";

echo "<h3>페이지 새로고침 안내</h3>\n";
echo "<p>캐시가 클리어되었습니다. 이제 웹사이트 페이지들을 새로고침하여 변경사항을 확인해주세요.</p>\n";
echo "<p><a href='/'>홈페이지로 이동</a></p>\n";

echo "</body>\n</html>";
?>