<?php
/**
 * 강제 CSS 재생성 스크립트
 * Admin 색상 설정을 반영한 theme.css 강제 재생성
 */

// 에러 출력 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // 데이터베이스 연결
    $pdo = new PDO(
        "mysql:host=localhost;dbname=woodong615;charset=utf8mb4",
        "zealnutkim",
        "1123",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    echo "<h1>강제 테마 CSS 재생성</h1>\n";
    echo "<p>타임스탬프: " . date('Y-m-d H:i:s') . "</p>\n";
    
    // ThemeService 로드
    require_once __DIR__ . '/admin/mvc/services/ThemeService.php';
    
    // ThemeService 인스턴스 생성
    $themeService = new ThemeService($pdo);
    
    echo "<h2>1. 현재 데이터베이스 색상 설정</h2>\n";
    $settings = $themeService->getThemeSettings();
    
    echo "<table border='1' cellpadding='5'>\n";
    echo "<tr><th>설정 키</th><th>값</th></tr>\n";
    foreach ($settings as $key => $value) {
        if (strpos($key, 'color') !== false) {
            echo "<tr><td>$key</td><td style='background-color:$value; color:white; padding:10px;'>$value</td></tr>\n";
        }
    }
    echo "</table>\n";
    
    echo "<h2>2. 기존 캐시 파일 정리</h2>\n";
    $cacheDir = __DIR__ . '/uploads/theme_cache/';
    if (is_dir($cacheDir)) {
        $cacheFiles = glob($cacheDir . 'theme_*.css');
        foreach ($cacheFiles as $file) {
            if (unlink($file)) {
                echo "<p>✅ 삭제: " . basename($file) . "</p>\n";
            }
        }
    }
    
    echo "<h2>3. CSS 파일 강제 재생성</h2>\n";
    
    // CSS 파일 재생성
    $cssFile = $themeService->generateThemeCSS();
    
    echo "<p>✅ CSS 파일 생성 완료: <code>$cssFile</code></p>\n";
    
    // 생성된 파일 확인
    if (file_exists($cssFile)) {
        $fileSize = filesize($cssFile);
        $lastModified = date('Y-m-d H:i:s', filemtime($cssFile));
        echo "<p>📁 파일 크기: {$fileSize} bytes</p>\n";
        echo "<p>🕒 수정 시간: {$lastModified}</p>\n";
        
        // CSS 내용 미리보기 (처음 20줄)
        echo "<h3>생성된 CSS 미리보기 (처음 20줄)</h3>\n";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 300px;'>\n";
        $lines = file($cssFile);
        for ($i = 0; $i < min(20, count($lines)); $i++) {
            echo htmlspecialchars($lines[$i]);
        }
        echo "</pre>\n";
        
        // Admin 관리 변수 확인
        $cssContent = file_get_contents($cssFile);
        echo "<h3>Admin 관리 변수 적용 상태</h3>\n";
        $adminVariables = [
            '--forest-500' => '메인 브랜드 색상',
            '--green-600' => '보조 액션 색상', 
            '--lime-600' => '성공 색상',
            '--lime-400' => '경고 색상'
        ];
        
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>변수명</th><th>설명</th><th>적용 상태</th><th>값</th></tr>\n";
        foreach ($adminVariables as $varName => $description) {
            if (preg_match("/{$varName}:\s*([^;!]+)(?:\s*!important)?/", $cssContent, $matches)) {
                $value = trim($matches[1]);
                $hasImportant = strpos($cssContent, "$varName:") !== false && strpos($cssContent, "!important") !== false;
                $status = $hasImportant ? "✅ !important 적용됨" : "⚠️ !important 없음";
                echo "<tr><td>$varName</td><td>$description</td><td>$status</td><td style='background-color:$value; color:white;'>$value</td></tr>\n";
            } else {
                echo "<tr><td>$varName</td><td>$description</td><td>❌ 변수 없음</td><td>-</td></tr>\n";
            }
        }
        echo "</table>\n";
        
    } else {
        echo "<p>❌ 파일 생성 실패</p>\n";
    }
    
    echo "<h2>4. 브라우저 캐시 클리어 안내</h2>\n";
    echo "<div style='background: #fffacd; padding: 15px; border-left: 5px solid #ffd700;'>\n";
    echo "<h4>브라우저에서 다음 작업을 수행하세요:</h4>\n";
    echo "<ol>\n";
    echo "<li><strong>Chrome/Safari</strong>: Cmd+Shift+R (Mac) 또는 Ctrl+Shift+R (Windows)</li>\n";
    echo "<li><strong>Firefox</strong>: Cmd+Shift+R (Mac) 또는 Ctrl+F5 (Windows)</li>\n";
    echo "<li>개발자 도구 → Network 탭 → 'Disable cache' 체크</li>\n";
    echo "<li>개발자 도구 → Application/Storage → Clear storage</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
    echo "<h2>✅ 작업 완료</h2>\n";
    echo "<p><a href='/hopec' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>웹사이트에서 결과 확인</a></p>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>\n";
    echo "<pre style='background: #ffebee; padding: 10px; border-left: 5px solid #f44336;'>\n";
    echo htmlspecialchars($e->getMessage()) . "\n";
    echo htmlspecialchars($e->getTraceAsString()) . "\n";
    echo "</pre>\n";
}
?>