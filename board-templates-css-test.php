<?php
/**
 * Board Templates CSS 테스트 스크립트
 * 게시판 연동 페이지의 스타일 문제 검증
 */

// 필요한 파일들 로드
require_once 'includes/critical-css-generator.php';
require_once 'includes/css-mode-manager.php';

$generator = new CriticalCSSGenerator();
$cssMode = getCSSMode();

// Critical CSS 생성 및 정보 수집
$criticalCSS = $generator->generateCriticalCSS();
$debugInfo = $generator->getDebugInfo();

echo "<h1>📋 Board Templates CSS 테스트</h1>\n";

echo "<h2>📊 Board Templates 지원 Critical CSS</h2>\n";
echo "<ul>\n";
echo "<li><strong>크기:</strong> " . $debugInfo['size_kb'] . " KB</li>\n";
echo "<li><strong>권장 크기 내:</strong> " . ($debugInfo['within_limit'] ? '✅ Yes' : '❌ No') . "</li>\n";
echo "</ul>\n";

echo "<h2>🎯 Board Templates 관련 클래스 확인</h2>\n";
$boardClasses = [
    // Table styling
    'table' => '기본 테이블',
    'board-table' => '게시판 테이블',
    'info-table' => '정보 테이블',
    'th' => '테이블 헤더',
    'td' => '테이블 셀',
    'tr:hover' => '테이블 행 호버',
    
    // Document styling
    'document-container' => '문서 컨테이너',
    'document-title' => '문서 제목',
    'document-section' => '문서 섹션',
    
    // Board components
    'board-surface' => '게시판 표면',
    'board-content-area' => '게시판 콘텐츠 영역',
    
    // Form styling
    'form-group' => '폼 그룹',
    'form-label' => '폼 라벨',
    'form-input' => '폼 입력',
    'form-textarea' => '폼 텍스트에어리어',
    
    // Spacing
    'space-y-6' => '세로 간격',
    'space-y-4' => '세로 간격 중간',
    'space-y-2' => '세로 간격 작음',
    
    // Text alignment
    'text-center' => '텍스트 중앙',
    'text-left' => '텍스트 왼쪽',
    'text-right' => '텍스트 오른쪽'
];

echo "<ul>\n";
foreach ($boardClasses as $class => $description) {
    $exists = strpos($criticalCSS, $class) !== false;
    echo "<li>" . ($exists ? '✅' : '❌') . " <strong>{$class}</strong>: {$description}</li>\n";
}
echo "</ul>\n";

echo "<h2>🧪 실제 Board Templates 테스트</h2>\n";
echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<h3>🔧 CSS 모드 전환</h3>\n";
echo "<p><strong>현재 모드:</strong> <span style='background: #1976d2; color: white; padding: 2px 8px; border-radius: 3px;'>" . $cssMode->getCurrentMode() . "</span></p>\n";

echo "<h4>📄 Board Templates 페이지들</h4>\n";
echo "<ul>\n";
echo "<li><a href='/board_templates/post_detail.php?id=1&css_mode=legacy' style='color: #d32f2f;'>🟥 게시글 상세 - Legacy</a></li>\n";
echo "<li><a href='/board_templates/post_detail.php?id=1&css_mode=optimized' style='color: #388e3c;'>🟩 게시글 상세 - Optimized</a></li>\n";
echo "<li><a href='/board_templates/board_list.php?css_mode=legacy' style='color: #d32f2f;'>🟥 게시판 목록 - Legacy</a></li>\n";
echo "<li><a href='/board_templates/board_list.php?css_mode=optimized' style='color: #388e3c;'>🟩 게시판 목록 - Optimized</a></li>\n";
echo "<li><a href='/board_templates/write_form.php?css_mode=legacy' style='color: #d32f2f;'>🟥 글쓰기 - Legacy</a></li>\n";
echo "<li><a href='/board_templates/write_form.php?css_mode=optimized' style='color: #388e3c;'>🟩 글쓰기 - Optimized</a></li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>📋 추가된 Board Templates 스타일</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>테이블 기본 스타일</strong>: table, th, td 기본 스타일링</li>\n";
echo "<li>✅ <strong>게시판 전용 테이블</strong>: .board-table, .info-table 클래스</li>\n";
echo "<li>✅ <strong>문서 스타일</strong>: .document-container, .document-title 등</li>\n";
echo "<li>✅ <strong>폼 스타일링</strong>: .form-group, .form-input, .form-label</li>\n";
echo "<li>✅ <strong>반응형 테이블</strong>: 모바일에서 자동 크기 조정</li>\n";
echo "<li>✅ <strong>인쇄 스타일</strong>: 프린트 시 최적화된 스타일</li>\n";
echo "<li>✅ <strong>공간 유틸리티</strong>: space-y-*, space-x-* 클래스</li>\n";
echo "<li>✅ <strong>정렬 유틸리티</strong>: text-center, text-left, text-right</li>\n";
echo "</ul>\n";

echo "<h2>🔍 테이블 스타일 시연</h2>\n";
echo "<div style='background: #ffffff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; margin: 15px 0;'>\n";
echo "<h3 style='text-align: center; margin-bottom: 20px;'>동신이용자정보 제공내역 확인서</h3>\n";

echo "<h4>고객사항</h4>\n";
echo "<table class='info-table' style='width: 100%; border: 2px solid #374151; border-collapse: collapse; margin: 1rem 0;'>\n";
echo "<tr>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>고객명</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>김**</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>전화대수</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>010-4264-3758</th>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>생년월일</td>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>19**-10-09</td>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>연락처</td>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>010-4264-3758</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>성별</td>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>남</td>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>주소</td>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>(영천시소)</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>신 청 일</td>\n";
echo "<td colspan='3' style='border: 1px solid #374151; padding: 0.5rem 0.75rem;'>2025년 09월 16일</td>\n";
echo "</tr>\n";
echo "</table>\n";

echo "<h4>결과 통지</h4>\n";
echo "<table class='info-table' style='width: 100%; border: 2px solid #374151; border-collapse: collapse; margin: 1rem 0;'>\n";
echo "<tr>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>성명</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>제공 일자</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>요청 기관</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>공문서번호</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>요청 근거</th>\n";
echo "<th style='border: 1px solid #374151; padding: 0.5rem 0.75rem; background-color: #f9fafb; font-weight: 600;'>제공 내역</th>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td colspan='6' style='border: 1px solid #374151; padding: 2rem; text-align: center; color: #6b7280;'>제공내역 없음</td>\n";
echo "</tr>\n";
echo "</table>\n";

echo "<div style='text-align: center; margin: 2rem 0;'>\n";
echo "<p>2025년 09월 17일</p>\n";
echo "<p style='margin-top: 1rem;'><strong>수식회사 케이티</strong></p>\n";
echo "</div>\n";
echo "</div>\n";

echo "<h2>🧪 실시간 스타일 테스트</h2>\n";
echo "<div id='board-templates-test-results'></div>\n";

echo "<script>\n";
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    const testDiv = document.getElementById('board-templates-test-results');\n";
echo "    \n";
echo "    // Critical CSS에서 board-related 클래스 확인\n";
echo "    const criticalStyles = document.getElementById('hopec-critical-css');\n";
echo "    const criticalCSS = criticalStyles ? criticalStyles.textContent : '';\n";
echo "    \n";
echo "    const boardClasses = ['table', 'board-table', 'info-table', 'document-container', 'form-input', 'space-y-6'];\n";
echo "    let boardSupport = {};\n";
echo "    boardClasses.forEach(function(cls) {\n";
echo "        boardSupport[cls] = criticalCSS.includes(cls);\n";
echo "    });\n";
echo "    \n";
echo "    // 테이블 스타일 적용 확인\n";
echo "    const tables = document.querySelectorAll('.info-table');\n";
echo "    let tableInfo = [];\n";
echo "    tables.forEach(function(table, index) {\n";
echo "        const style = getComputedStyle(table);\n";
echo "        tableInfo.push({\n";
echo "            index: index + 1,\n";
echo "            borderCollapse: style.borderCollapse,\n";
echo "            border: style.border,\n";
echo "            width: style.width\n";
echo "        });\n";
echo "    });\n";
echo "    \n";
echo "    // 결과 출력\n";
echo "    let html = '<h3>📊 Board Templates 테스트 결과</h3>';\n";
echo "    html += '<div style=\"background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;\">';\n";
echo "    \n";
echo "    html += '<h4>🎨 Board Classes 지원</h4>';\n";
echo "    Object.keys(boardSupport).forEach(function(cls) {\n";
echo "        html += '<p>' + (boardSupport[cls] ? '✅' : '❌') + ' ' + cls + '</p>';\n";
echo "    });\n";
echo "    \n";
echo "    html += '<h4>📋 테이블 스타일 적용</h4>';\n";
echo "    tableInfo.forEach(function(table) {\n";
echo "        html += '<p>테이블 ' + table.index + ':<br>';\n";
echo "        html += '&nbsp;&nbsp;border-collapse: ' + table.borderCollapse + '<br>';\n";
echo "        html += '&nbsp;&nbsp;border: ' + table.border + '<br>';\n";
echo "        html += '&nbsp;&nbsp;width: ' + table.width + '</p>';\n";
echo "    });\n";
echo "    \n";
echo "    html += '<h4>📏 Critical CSS 정보</h4>';\n";
echo "    html += '<p>Board Templates 관련 크기: ' + (criticalCSS.match(/table|board-|form-|document-|space-/g) || []).length + '개 클래스</p>';\n";
echo "    \n";
echo "    html += '</div>';\n";
echo "    testDiv.innerHTML = html;\n";
echo "    \n";
echo "    // 콘솔 로그\n";
echo "    console.log('📋 Board Templates 테스트 결과:', {\n";
echo "        boardSupport: boardSupport,\n";
echo "        tableInfo: tableInfo,\n";
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