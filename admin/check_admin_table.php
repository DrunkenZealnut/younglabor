<?php
/**
 * 관리자 테이블 구조 확인 및 수정 스크립트
 */

require_once __DIR__ . '/db.php';

echo "<!DOCTYPE html>\n";
echo "<html lang='ko'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <title>관리자 테이블 구조 확인</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }\n";
echo "        table { border-collapse: collapse; width: 100%; margin: 10px 0; }\n";
echo "        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }\n";
echo "        th { background-color: #f2f2f2; }\n";
echo "        .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .info { color: #0c5460; background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";

try {
    echo "<h1>관리자 테이블 구조 확인</h1>\n";
    
    // 현재 테이블 구조 확인
    echo "<h2>현재 admin_user 테이블 구조</h2>\n";
    $stmt = $pdo->query("DESCRIBE admin_user");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>\n";
    echo "<tr><th>컬럼명</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th><th>Extra</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr>\n";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>\n";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>\n";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>\n";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>\n";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>\n";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // 필요한 컬럼들 확인
    $requiredColumns = ['name', 'role', 'status', 'last_login', 'created_at', 'updated_at'];
    $existingColumns = array_column($columns, 'Field');
    
    echo "<h2>필요한 컬럼 확인 및 추가</h2>\n";
    
    $missingColumns = [];
    foreach ($requiredColumns as $columnName) {
        if (!in_array($columnName, $existingColumns)) {
            $missingColumns[] = $columnName;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "<div class='info'>누락된 컬럼들을 추가합니다: " . implode(', ', $missingColumns) . "</div>\n";
        
        // 누락된 컬럼들 추가
        $alterStatements = [];
        
        if (in_array('name', $missingColumns)) {
            $alterStatements[] = "ADD COLUMN name varchar(100) DEFAULT NULL AFTER email";
        }
        
        if (in_array('role', $missingColumns)) {
            $alterStatements[] = "ADD COLUMN role varchar(20) DEFAULT 'admin' AFTER name";
        }
        
        if (in_array('status', $missingColumns)) {
            $alterStatements[] = "ADD COLUMN status enum('active','inactive') DEFAULT 'active' AFTER role";
        }
        
        if (in_array('last_login', $missingColumns)) {
            $alterStatements[] = "ADD COLUMN last_login datetime DEFAULT NULL AFTER status";
        }
        
        if (in_array('created_at', $missingColumns)) {
            $alterStatements[] = "ADD COLUMN created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER last_login";
        }
        
        if (in_array('updated_at', $missingColumns)) {
            $alterStatements[] = "ADD COLUMN updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at";
        }
        
        // 컬럼 추가 실행
        foreach ($alterStatements as $statement) {
            $pdo->exec("ALTER TABLE hopec_admin_user " . $statement);
            echo "<div class='success'>✅ 컬럼 추가: " . htmlspecialchars($statement) . "</div>\n";
        }
        
    } else {
        echo "<div class='success'>✅ 모든 필요한 컬럼이 존재합니다.</div>\n";
    }
    
    // 현재 데이터 확인
    echo "<h2>현재 관리자 계정 데이터</h2>\n";
    $stmt = $pdo->query("SELECT * FROM hopec_admin_user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($users)) {
        echo "<table>\n";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th><th>Created At</th></tr>\n";
        foreach ($users as $user) {
            echo "<tr>\n";
            echo "<td>" . htmlspecialchars($user['id'] ?? '') . "</td>\n";
            echo "<td>" . htmlspecialchars($user['username'] ?? '') . "</td>\n";
            echo "<td>" . htmlspecialchars($user['email'] ?? '') . "</td>\n";
            echo "<td>" . htmlspecialchars($user['name'] ?? '') . "</td>\n";
            echo "<td>" . htmlspecialchars($user['role'] ?? '') . "</td>\n";
            echo "<td>" . htmlspecialchars($user['status'] ?? '') . "</td>\n";
            echo "<td>" . htmlspecialchars($user['last_login'] ?? '') . "</td>\n";
            echo "<td>" . htmlspecialchars($user['created_at'] ?? '') . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<div class='info'>관리자 계정이 없습니다.</div>\n";
    }
    
    echo "<div class='success'>\n";
    echo "<h3>✅ 테이블 구조 확인 완료</h3>\n";
    echo "<a href='fix_admin_login.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>관리자 계정 설정하러 가기</a>\n";
    echo "</div>\n";
    
} catch (PDOException $e) {
    echo "<div class='error'>\n";
    echo "<strong>❌ 데이터베이스 오류:</strong><br>\n";
    echo htmlspecialchars($e->getMessage()) . "\n";
    echo "</div>\n";
}

echo "</body>\n";
echo "</html>\n";
?>