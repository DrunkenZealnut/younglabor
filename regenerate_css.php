<?php
/**
 * Regenerate Theme CSS with Correct Colors
 */

try {
    // Database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=hopec;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h1>🎨 CSS Regeneration</h1>\n";
    
    // Include ThemeService
    require_once __DIR__ . '/admin/mvc/services/ThemeService.php';
    
    // Create ThemeService instance
    $themeService = new ThemeService($pdo);
    
    // Clear cache first
    $themeService->clearThemeCache();
    echo "<p>✅ Theme cache cleared</p>\n";
    
    // Generate new CSS
    $cssFile = $themeService->generateThemeCSS();
    echo "<p>✅ Theme CSS regenerated: $cssFile</p>\n";
    echo "<p>✅ File size: " . number_format(filesize($cssFile)) . " bytes</p>\n";
    echo "<p>✅ Last modified: " . date('Y-m-d H:i:s', filemtime($cssFile)) . "</p>\n";
    
    // Verify the colors in the generated CSS
    echo "<h2>🔍 Generated CSS Verification</h2>\n";
    $cssContent = file_get_contents($cssFile);
    
    // Check key color variables
    $colorChecks = [
        '--bs-primary: #84cc16' => '메인 브랜드 색상 (Lime-500)',
        '--bs-secondary: #16a34a' => '보조 색상 (Green-600)',
        '--forest-500: #84cc16' => 'Natural-Green Forest-500 매핑',
        '--green-600: #16a34a' => 'Natural-Green Green-600 매핑'
    ];
    
    foreach ($colorChecks as $searchText => $description) {
        $found = strpos($cssContent, $searchText) !== false;
        $status = $found ? "✅" : "❌";
        echo "<p>$status $description: $searchText</p>\n";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2 style='color: #155724;'>🎉 CSS 재생성 완료!</h2>";
    echo "<p>이제 웹사이트를 새로고침하면 Natural-Green 테마 색상이 적용됩니다.</p>";
    echo "<p>CSS URL: <code>/hopec/css/theme/theme.css?v=" . filemtime($cssFile) . "</code></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ 오류 발생:</h2>\n";
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 5px;'>\n";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</div>\n";
}
?>