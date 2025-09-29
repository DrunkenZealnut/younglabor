<?php
/**
 * PROJECT_SLUG 및 데이터베이스 구조 진단 도구
 */

// Bootstrap 로드
require_once __DIR__ . '/bootstrap/app.php';

// 디버그 모드 강제 활성화
$_GET['debug'] = '1';

echo "<h1>프로젝트 구성 진단 도구</h1>";
echo "<p>현재 환경: " . (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'LOCAL' : 'SERVER') . "</p>";

echo "<h2>1. 환경변수 및 설정 정보</h2>";

// 주요 환경변수들 확인
$env_vars = [
    'PROJECT_SLUG' => env('PROJECT_SLUG', 'NOT_SET'),
    'DB_PREFIX' => env('DB_PREFIX', 'NOT_SET'),
    'DB_DATABASE' => env('DB_DATABASE', 'NOT_SET'),
    'DB_HOST' => env('DB_HOST', 'NOT_SET'),
    'DB_USERNAME' => env('DB_USERNAME', 'NOT_SET'),
    'APP_URL' => env('APP_URL', 'NOT_SET'),
    'BASE_PATH' => env('BASE_PATH', 'NOT_SET'),
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>환경변수</th><th>값</th><th>상태</th></tr>";
foreach ($env_vars as $key => $value) {
    $status = ($value === 'NOT_SET') ? "❌ 미설정" : "✅ 설정됨";
    $color = ($value === 'NOT_SET') ? "red" : "green";
    echo "<tr>";
    echo "<td><strong>{$key}</strong></td>";
    echo "<td><code>{$value}</code></td>";
    echo "<td style='color: {$color};'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>2. 데이터베이스 연결 및 구조 확인</h2>";

try {
    global $pdo;
    if (!$pdo) {
        echo "<p style='color: red;'>❌ 데이터베이스 연결 실패</p>";
        die();
    }
    
    echo "<p style='color: green;'>✅ 데이터베이스 연결 성공</p>";
    
    // 현재 데이터베이스 이름 확인
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $current_db = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>현재 데이터베이스: <strong>{$current_db['current_db']}</strong></p>";
    
    // site_settings 관련 테이블들 찾기
    echo "<h3>2.1 site_settings 관련 테이블 검색</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE '%site_settings%'");
    $settings_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($settings_tables)) {
        // prefix 없이도 검색
        $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
        $settings_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($settings_tables)) {
            echo "<p style='color: red;'>❌ site_settings 테이블을 찾을 수 없습니다</p>";
            
            // 모든 테이블 목록 표시
            echo "<h4>전체 테이블 목록:</h4>";
            $stmt = $pdo->query("SHOW TABLES");
            $all_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<ul>";
            foreach ($all_tables as $table) {
                echo "<li>{$table}";
                // settings가 포함된 테이블 강조
                if (strpos($table, 'settings') !== false) {
                    echo " <strong style='color: orange;'>← SETTINGS 관련!</strong>";
                }
                echo "</li>";
            }
            echo "</ul>";
        }
    }
    
    if (!empty($settings_tables)) {
        echo "<p style='color: green;'>✅ 발견된 settings 테이블:</p>";
        echo "<ul>";
        foreach ($settings_tables as $table) {
            echo "<li><strong>{$table}</strong></li>";
        }
        echo "</ul>";
        
        // 각 테이블의 구조와 데이터 확인
        foreach ($settings_tables as $table) {
            echo "<h4>테이블: {$table}</h4>";
            
            // 테이블 구조
            $stmt = $pdo->query("DESCRIBE `{$table}`");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>컬럼 구조:</p>";
            echo "<ul>";
            foreach ($columns as $col) {
                echo "<li><strong>{$col['Field']}</strong> ({$col['Type']}) - {$col['Key']}</li>";
            }
            echo "</ul>";
            
            // 로고/파비콘 관련 데이터 확인
            try {
                $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE setting_key LIKE '%logo%' OR setting_key LIKE '%favicon%' OR setting_key LIKE '%icon%'");
                $stmt->execute();
                $logo_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($logo_data)) {
                    echo "<p style='color: green;'>✅ 로고/파비콘 관련 데이터 발견:</p>";
                    echo "<table border='1' cellpadding='3'>";
                    echo "<tr><th>Key</th><th>Value</th></tr>";
                    foreach ($logo_data as $row) {
                        $key = $row['setting_key'] ?? 'N/A';
                        $value = $row['setting_value'] ?? 'N/A';
                        $value_display = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
                        echo "<tr><td><strong>{$key}</strong></td><td>{$value_display}</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='color: orange;'>⚠️ 로고/파비콘 관련 데이터 없음</p>";
                }
                
                // 전체 데이터 개수
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM `{$table}`");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>총 설정 항목 수: <strong>{$count['total']}</strong></p>";
                
                if ($count['total'] > 0 && $count['total'] <= 20) {
                    // 적은 수의 데이터면 모두 표시
                    $stmt = $pdo->query("SELECT * FROM `{$table}` LIMIT 20");
                    $all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<details><summary>모든 설정 데이터 보기</summary>";
                    echo "<table border='1' cellpadding='3'>";
                    if (!empty($all_data)) {
                        $headers = array_keys($all_data[0]);
                        echo "<tr>";
                        foreach ($headers as $header) {
                            echo "<th>{$header}</th>";
                        }
                        echo "</tr>";
                        
                        foreach ($all_data as $row) {
                            echo "<tr>";
                            foreach ($headers as $header) {
                                $value = $row[$header] ?? '';
                                $display_value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                                echo "<td>{$display_value}</td>";
                            }
                            echo "</tr>";
                        }
                    }
                    echo "</table></details>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ 데이터 조회 오류: {$e->getMessage()}</p>";
            }
        }
    }
    
    echo "<h2>3. 현재 코드에서 사용하는 테이블명 확인</h2>";
    
    // 실제 코드에서 사용하는 테이블명 계산
    $db_prefix = env('DB_PREFIX', '');
    $expected_table = $db_prefix . 'site_settings';
    
    echo "<p>코드에서 기대하는 테이블명: <strong>{$expected_table}</strong></p>";
    
    // 해당 테이블이 존재하는지 확인
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$expected_table]);
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "<p style='color: green;'>✅ 기대하는 테이블이 존재합니다</p>";
    } else {
        echo "<p style='color: red;'>❌ 기대하는 테이블이 존재하지 않습니다</p>";
        echo "<p style='color: orange;'>⚠️ DB_PREFIX 설정을 확인하거나 올바른 테이블명을 찾아야 합니다</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 데이터베이스 오류: " . $e->getMessage() . "</p>";
}

echo "<h2>4. 해결 방안 제시</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h3>문제 해결 단계:</h3>";
echo "<ol>";
echo "<li><strong>올바른 테이블 식별:</strong> 위에서 발견된 settings 테이블 중 로고/파비콘 데이터가 있는 테이블 확인</li>";
echo "<li><strong>DB_PREFIX 수정:</strong> .env 파일에서 DB_PREFIX를 올바른 값으로 설정</li>";
echo "<li><strong>테이블명 하드코딩:</strong> 임시로 template_helpers.php에서 정확한 테이블명 사용</li>";
echo "<li><strong>데이터 마이그레이션:</strong> 필요시 올바른 테이블로 데이터 복사</li>";
echo "</ol>";
echo "</div>";

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    details { margin: 10px 0; }
    summary { cursor: pointer; font-weight: bold; }
</style>