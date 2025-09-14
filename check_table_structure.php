<?php
/**
 * Check existing hopec_site_settings table structure
 */

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=hopec;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h1>hopec_site_settings 테이블 구조 확인</h1>\n";
    
    // 테이블 구조 확인
    $stmt = $pdo->query("DESCRIBE hopec_site_settings");
    $columns = $stmt->fetchAll();
    
    echo "<h2>현재 테이블 구조:</h2>\n";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>\n";
    echo "<tr style='background: #f0f0f0;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    
    foreach ($columns as $column) {
        echo "<tr>\n";
        echo "<td>{$column['Field']}</td>\n";
        echo "<td>{$column['Type']}</td>\n";
        echo "<td>{$column['Null']}</td>\n";
        echo "<td>{$column['Key']}</td>\n";
        echo "<td>{$column['Default']}</td>\n";
        echo "<td>{$column['Extra']}</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // 기존 데이터 확인
    echo "<h2>기존 데이터:</h2>\n";
    $stmt = $pdo->query("SELECT * FROM hopec_site_settings ORDER BY setting_key");
    $data = $stmt->fetchAll();
    
    if (count($data) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>\n";
        echo "<tr style='background: #f0f0f0;'>";
        foreach (array_keys($data[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>\n";
        
        foreach ($data as $row) {
            echo "<tr>\n";
            foreach ($row as $value) {
                $displayValue = strlen($value) > 30 ? substr($value, 0, 30) . '...' : $value;
                echo "<td>" . htmlspecialchars($displayValue) . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>데이터가 없습니다.</p>\n";
    }
    
    // 필요한 컬럼이 있는지 확인
    $requiredColumns = ['setting_description'];
    $existingColumns = array_column($columns, 'Field');
    
    echo "<h2>컬럼 확인:</h2>\n";
    foreach ($requiredColumns as $reqCol) {
        $exists = in_array($reqCol, $existingColumns);
        $status = $exists ? '✅ 존재' : '❌ 없음';
        echo "<p><strong>$reqCol:</strong> $status</p>\n";
        
        if (!$exists) {
            echo "<p style='color: orange;'>→ ALTER TABLE 명령이 필요합니다.</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>오류: " . $e->getMessage() . "</p>\n";
}
?>