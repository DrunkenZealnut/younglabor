<?php
/**
 * Color Settings Fix Script
 * 
 * This script fixes the admin color configuration to match the Natural-Green theme.
 * The current database values are inconsistent and need to be corrected.
 */

try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_DATABASE'] ?? '';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h1>🔧 Color Settings Fix</h1>\n";
    echo "<p>Fixing admin color settings to match Natural-Green theme...</p>\n";
    
    // 현재 잘못된 색상값들
    echo "<h2>❌ Current (Incorrect) Colors in Database:</h2>\n";
    $table_prefix = $_ENV['DB_PREFIX'] ?? '';
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM {$table_prefix}site_settings WHERE setting_group = 'theme' AND setting_key LIKE '%_color' ORDER BY setting_key");
    $stmt->execute();
    $currentColors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr style='background: #f8d7da;'><th>색상 설정</th><th>현재 값</th><th>미리보기</th></tr>\n";
    foreach ($currentColors as $color) {
        $colorName = str_replace('_color', '', $color['setting_key']);
        echo "<tr>";
        echo "<td><strong>" . ucfirst($colorName) . "</strong></td>";
        echo "<td><code>{$color['setting_value']}</code></td>";
        echo "<td><div style='width: 40px; height: 20px; background-color: {$color['setting_value']}; border: 1px solid #ccc;'></div></td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Natural-Green 테마의 올바른 색상 (theme.php 파일 기준)
    $correctColors = [
        'primary_color' => '#84cc16',    // lime-500 - 메인 브랜드 컬러
        'secondary_color' => '#16a34a',  // green-600 - 보조 컬러  
        'success_color' => '#65a30d',    // lime-600 - 성공
        'info_color' => '#3a7a4e',       // forest-500 - 정보
        'warning_color' => '#a3e635',    // lime-400 - 경고
        'danger_color' => '#dc2626',     // red-600 - 위험
        'light_color' => '#fafffe',      // natural-50 - 밝은 색
        'dark_color' => '#1f3b2d'        // forest-700 - 어두운 색
    ];
    
    echo "<h2>✅ Correct Natural-Green Theme Colors:</h2>\n";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr style='background: #d4edda;'><th>색상 설정</th><th>올바른 값</th><th>미리보기</th><th>Natural-Green 매핑</th></tr>\n";
    
    $colorDescriptions = [
        'primary_color' => 'Lime-500 (메인 브랜드)',
        'secondary_color' => 'Green-600 (보조 액션)',
        'success_color' => 'Lime-600 (성공/확인)',
        'info_color' => 'Forest-500 (정보)',
        'warning_color' => 'Lime-400 (경고/주의)',
        'danger_color' => 'Red-600 (위험/오류)',
        'light_color' => 'Natural-50 (밝은 배경)',
        'dark_color' => 'Forest-700 (어두운 텍스트)'
    ];
    
    foreach ($correctColors as $key => $value) {
        $colorName = str_replace('_color', '', $key);
        echo "<tr>";
        echo "<td><strong>" . ucfirst($colorName) . "</strong></td>";
        echo "<td><code>$value</code></td>";
        echo "<td><div style='width: 40px; height: 20px; background-color: $value; border: 1px solid #ccc;'></div></td>";
        echo "<td style='font-size: 12px; color: #666;'>{$colorDescriptions[$key]}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // 사용자 확인
    echo "<form method='post' style='margin: 20px 0; padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;'>";
    echo "<h3>🚨 Database Update Required</h3>";
    echo "<p>현재 색상 설정이 Natural-Green 테마와 맞지 않습니다. 올바른 색상으로 업데이트하시겠습니까?</p>";
    echo "<button type='submit' name='fix_colors' value='1' style='background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;'>색상 설정 수정하기</button>";
    echo " <em>이 작업은 기존 색상 설정을 Natural-Green 테마 색상으로 덮어쓰게 됩니다.</em>";
    echo "</form>";
    
    // 색상 수정 처리
    if (isset($_POST['fix_colors']) && $_POST['fix_colors'] === '1') {
        echo "<h2>🔄 Updating Color Settings...</h2>\n";
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE {$table_prefix}site_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ? AND setting_group = 'theme'");
        
        foreach ($correctColors as $key => $value) {
            $stmt->execute([$value, $key]);
            echo "<p>✅ Updated <strong>$key</strong>: $value</p>\n";
        }
        
        $pdo->commit();
        
        echo "<h2>🎨 Regenerating Theme CSS...</h2>\n";
        
        // ThemeService를 사용하여 CSS 재생성
        try {
            include_once __DIR__ . '/admin/mvc/services/ThemeService.php';
            $themeService = new ThemeService($pdo);
            $cssFile = $themeService->generateThemeCSS();
            echo "<p>✅ CSS 파일 재생성 완료: $cssFile</p>\n";
            echo "<p>✅ CSS 수정시간: " . date('Y-m-d H:i:s', filemtime($cssFile)) . "</p>\n";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ CSS 자동 재생성 실패: " . $e->getMessage() . "</p>\n";
            echo "<p>→ Admin 패널에서 수동으로 테마 설정을 저장해주세요.</p>\n";
        }
        
        echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #28a745; border-radius: 5px; margin: 20px 0;'>";
        echo "<h2 style='color: #155724;'>🎉 색상 설정 수정 완료!</h2>";
        echo "<p>✅ 데이터베이스 색상 값 업데이트 완료</p>";
        echo "<p>✅ ThemeService CSS 재생성 완료</p>";
        echo "<p>✅ 웹사이트에서 Natural-Green 테마 색상이 적용됩니다</p>";
        echo "<h3>확인 방법:</h3>";
        echo "<ol>";
        echo "<li>웹사이트 메인페이지 새로고침</li>";
        echo "<li>Admin 패널 색상 설정 확인</li>";
        echo "<li>버튼, 링크 등이 녹색 계열로 표시되는지 확인</li>";
        echo "</ol>";
        echo "</div>";
        
        // 캐시 강제 클리어 권장
        echo "<div style='background: #cce5ff; padding: 15px; border: 1px solid #007bff; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>💡 권장사항:</h3>";
        echo "<p>브라우저 캐시를 강제로 새로고침하세요 (Ctrl+F5 또는 Cmd+Shift+R)</p>";
        echo "<p>CSS 파일 URL: <code>/hopec/css/theme/theme.css?v=" . filemtime($cssFile) . "</code></p>";
        echo "</div>";
        
        // 변경 후 색상 확인
        echo "<h2>✅ Updated Colors (Verification):</h2>\n";
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM {$table_prefix}site_settings WHERE setting_group = 'theme' AND setting_key LIKE '%_color' ORDER BY setting_key");
        $stmt->execute();
        $updatedColors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 10px 0;'>\n";
        echo "<tr style='background: #d4edda;'><th>색상 설정</th><th>업데이트된 값</th><th>미리보기</th></tr>\n";
        foreach ($updatedColors as $color) {
            $colorName = str_replace('_color', '', $color['setting_key']);
            echo "<tr>";
            echo "<td><strong>" . ucfirst($colorName) . "</strong></td>";
            echo "<td><code>{$color['setting_value']}</code></td>";
            echo "<td><div style='width: 40px; height: 20px; background-color: {$color['setting_value']}; border: 1px solid #ccc;'></div></td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ 오류 발생:</h2>\n";
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 5px;'>\n";
    echo "<p><strong>오류 메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>파일:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>라인:</strong> " . $e->getLine() . "</p>\n";
    echo "</div>\n";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Color Settings Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        table { border-collapse: collapse; width: 100%; max-width: 800px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
        .btn { padding: 10px 15px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-success { background: #28a745; color: white; }
    </style>
</head>
<body>
    <!-- Content generated above -->
</body>
</html>