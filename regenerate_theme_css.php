<?php
/**
 * 테마 CSS 수동 재생성 스크립트
 */

// 데이터베이스 연결
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=hopec;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h1>테마 CSS 재생성</h1>\n";
    
    // ThemeService 로드
    require_once __DIR__ . '/admin/mvc/services/ThemeService.php';
    
    // ThemeService 인스턴스 생성
    $themeService = new ThemeService($pdo);
    
    echo "<h2>1. 현재 데이터베이스 색상 설정</h2>\n";
    $settings = $themeService->getThemeSettings();
    
    $colorKeys = ['primary_color', 'secondary_color', 'success_color', 'info_color', 
                  'warning_color', 'danger_color', 'light_color', 'dark_color'];
                  
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>\n";
    echo "<tr style='background: #f0f0f0;'><th>색상 키</th><th>현재 값</th><th>미리보기</th></tr>\n";
    
    foreach ($colorKeys as $key) {
        if (isset($settings[$key])) {
            $color = $settings[$key];
            echo "<tr>\n";
            echo "<td><strong>$key</strong></td>\n";
            echo "<td><code>$color</code></td>\n";
            echo "<td><div style='width: 40px; height: 20px; background-color: $color; border: 1px solid #ccc; display: inline-block;'></div></td>\n";
            echo "</tr>\n";
        }
    }
    echo "</table>\n";
    
    echo "<h2>2. CSS 파일 재생성</h2>\n";
    
    // CSS 파일 재생성
    $cssFile = $themeService->generateThemeCSS();
    
    echo "<p>✅ CSS 파일 생성 완료: <code>$cssFile</code></p>\n";
    
    // 파일 크기 확인
    $fileSize = filesize($cssFile);
    echo "<p><strong>파일 크기:</strong> " . number_format($fileSize) . " bytes</p>\n";
    
    echo "<h2>3. 생성된 CSS 내용 확인</h2>\n";
    
    // CSS 내용 일부 미리보기
    $cssContent = file_get_contents($cssFile);
    
    // CSS 변수들 추출
    preg_match_all('/--([a-z0-9-]+):\s*([^;]+);/', $cssContent, $matches, PREG_SET_ORDER);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>\n";
    echo "<tr style='background: #f0f0f0;'><th>CSS 변수</th><th>값</th><th>미리보기</th></tr>\n";
    
    $importantVars = ['bs-primary', 'bs-secondary', 'forest-500', 'green-600', 'lime-600'];
    
    foreach ($matches as $match) {
        $varName = $match[1];
        $varValue = trim($match[2]);
        
        // 중요한 변수들만 표시
        if (in_array($varName, $importantVars) || strpos($varName, 'theme-') === 0) {
            echo "<tr>\n";
            echo "<td><code>--$varName</code></td>\n";
            echo "<td><code>$varValue</code></td>\n";
            
            // 색상값인 경우 미리보기 표시
            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $varValue)) {
                echo "<td><div style='width: 40px; height: 20px; background-color: $varValue; border: 1px solid #ccc; display: inline-block;'></div></td>\n";
            } else {
                echo "<td>-</td>\n";
            }
            echo "</tr>\n";
        }
    }
    echo "</table>\n";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>✅ 테마 CSS 재생성 완료!</h3>\n";
    echo "<p><strong>다음 단계:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>1. 프론트엔드 페이지를 새로고침하세요</li>\n";
    echo "<li>2. 브라우저 캐시를 강제 새로고침하세요 (Ctrl+F5 또는 Cmd+Shift+R)</li>\n";
    echo "<li>3. 변경된 색상이 반영되었는지 확인하세요</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ 오류 발생:</h2>\n";
    echo "<p><strong>오류 메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>파일:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>라인:</strong> " . $e->getLine() . "</p>\n";
}
?>