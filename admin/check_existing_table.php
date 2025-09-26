<?php
/**
 * 기존 heme_presets 테이블 구조 확인
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/env_loader.php';

try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_DATABASE') . ";charset=" . env('DB_CHARSET'),
        env('DB_USERNAME'),
        env('DB_PASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h3>현재 theme_presets 테이블 정보</h3>";
    
    // 테이블 존재 확인
    $stmt = $pdo->query("SHOW TABLES LIKE 'theme_presets'");
    if (!$stmt->fetch()) {
        echo "<div style='color: orange;'>❌ theme_presets 테이블이 존재하지 않습니다. manual_theme_setup.sql을 그대로 실행하세요.</div>";
        exit;
    }
    
    echo "<div style='color: green;'>✅ theme_presets 테이블 존재</div><br>";
    
    // 테이블 구조 확인
    echo "<h4>1. 현재 테이블 구조:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>컬럼명</th><th>타입</th><th>NULL</th><th>기본값</th><th>Extra</th></tr>";
    
    $stmt = $pdo->query("DESCRIBE theme_presets");
    $currentColumns = [];
    
    while ($row = $stmt->fetch()) {
        $currentColumns[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // 필요한 컬럼 정의
    $requiredColumns = [
        'id' => 'int(11) NOT NULL AUTO_INCREMENT',
        'preset_name' => 'varchar(100) NOT NULL',
        'preset_colors' => 'text NOT NULL',
        'preset_description' => 'varchar(255) DEFAULT NULL',
        'created_by' => 'varchar(50) DEFAULT "admin"',
        'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        'is_active' => 'tinyint(1) DEFAULT 1',
        'sort_order' => 'int(11) DEFAULT 0'
    ];
    
    // 누락된 컬럼 찾기
    $missingColumns = array_diff(array_keys($requiredColumns), $currentColumns);
    
    echo "<h4>2. 분석 결과:</h4>";
    
    if (empty($missingColumns)) {
        echo "<div style='color: green;'>✅ 모든 필요한 컬럼이 존재합니다.</div>";
    } else {
        echo "<div style='color: orange;'>⚠️ 누락된 컬럼: " . implode(', ', $missingColumns) . "</div>";
        
        echo "<h4>3. 누락된 컬럼 추가 SQL:</h4>";
        echo "<textarea style='width: 100%; height: 150px;'>";
        foreach ($missingColumns as $column) {
            echo "ALTER TABLE hopec_theme_presets ADD COLUMN `$column` {$requiredColumns[$column]};\n";
        }
        echo "</textarea>";
    }
    
    // 기존 데이터 확인
    echo "<h4>4. 기존 데이터:</h4>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM hopec_theme_presets");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<div style='color: blue;'>📊 기존 데이터: {$count}개</div>";
        
        $stmt = $pdo->query("SELECT id, preset_name, created_by FROM hopec_theme_presets ORDER BY id");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>테마명</th><th>생성자</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr><td>{$row['id']}</td><td>{$row['preset_name']}</td><td>{$row['created_by']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: orange;'>📊 기존 데이터가 없습니다. 기본 테마를 추가하는 것이 좋겠습니다.</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ 오류: " . $e->getMessage() . "</div>";
}
?>