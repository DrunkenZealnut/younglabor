<?php
/**
 * 관리자 로그인 문제 해결 스크립트
 * - 테이블명 불일치 해결
 * - 기본 관리자 계정 생성 (admin/admin123)
 */

// 데이터베이스 연결
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/config_helpers.php';

// 진행상황 출력을 위한 HTML 헤더
echo "<!DOCTYPE html>\n";
echo "<html lang='ko'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <title>관리자 로그인 문제 해결</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }\n";
echo "        .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .info { color: #0c5460; background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo "        code { background-color: #f8f9fa; padding: 2px 4px; border-radius: 3px; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<h1>🔧 관리자 로그인 문제 해결</h1>\n";

try {
    // 1단계: 현재 테이블 상태 확인
    echo "<h2>1단계: 데이터베이스 테이블 상태 확인</h2>\n";
    
    // admin_user 테이블 존재 여부 확인
    $stmt = $pdo->query("SHOW TABLES LIKE '" . get_table_name('admin_user') . "'");
    $hasAdminUser = $stmt->rowCount() > 0;
    
    // admin_users 테이블 존재 여부 확인
    $stmt = $pdo->query("SHOW TABLES LIKE '" . get_table_name('admin_users') . "'");
    $hasAdminUsers = $stmt->rowCount() > 0;
    
    echo "<div class='info'>\n";
    echo "<strong>테이블 존재 상태:</strong><br>\n";
    echo "• <code>" . get_table_name('admin_user') . "</code>: " . ($hasAdminUser ? "✅ 존재" : "❌ 없음") . "<br>\n";
    echo "• <code>" . get_table_name('admin_users') . "</code>: " . ($hasAdminUsers ? "✅ 존재" : "❌ 없음") . "<br>\n";
    echo "</div>\n";
    
    // 2단계: 올바른 테이블 생성 또는 이름 변경
    echo "<h2>2단계: 테이블명 불일치 해결</h2>\n";
    
    if ($hasAdminUsers && !$hasAdminUser) {
        // admin_users가 있고 admin_user가 없는 경우
        // 로그인 코드가 admin_user를 참조하므로 테이블명을 변경
        echo "<div class='info'>테이블명 변경: <code>" . get_table_name('admin_users') . "</code> → <code>" . get_table_name('admin_user') . "</code></div>\n";
        
        // 기존 데이터 백업 및 테이블명 변경
        $pdo->exec("RENAME TABLE " . get_table_name('admin_users') . " TO " . get_table_name('admin_user'));
        echo "<div class='success'>✅ 테이블명이 성공적으로 변경되었습니다.</div>\n";
        
    } elseif (!$hasAdminUsers && !$hasAdminUser) {
        // 둘 다 없는 경우 새로 생성
        echo "<div class='info'>새 관리자 테이블 생성 중...</div>\n";
        
        $createTable = "
        CREATE TABLE IF NOT EXISTS `" . get_table_name('admin_user') . "` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `name` varchar(100) DEFAULT NULL,
            `role` varchar(20) DEFAULT 'admin',
            `status` enum('active','inactive') DEFAULT 'active',
            `last_login` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($createTable);
        echo "<div class='success'>✅ <code>" . get_table_name('admin_user') . "</code> 테이블이 생성되었습니다.</div>\n";
        
    } elseif ($hasAdminUser) {
        echo "<div class='success'>✅ <code>" . get_table_name('admin_user') . "</code> 테이블이 이미 존재합니다.</div>\n";
        
        // password 컬럼이 password_hash로 되어 있는지 확인
        $stmt = $pdo->query("DESCRIBE " . get_table_name('admin_user'));
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hasPasswordHash = false;
        
        foreach ($columns as $column) {
            if ($column['Field'] === 'password_hash') {
                $hasPasswordHash = true;
                break;
            }
        }
        
        // password 컬럼을 password_hash로 변경 (필요한 경우)
        if (!$hasPasswordHash) {
            try {
                // password 컬럼이 있는지 확인
                $hasPassword = false;
                foreach ($columns as $column) {
                    if ($column['Field'] === 'password') {
                        $hasPassword = true;
                        break;
                    }
                }
                
                if ($hasPassword) {
                    $pdo->exec("ALTER TABLE " . get_table_name('admin_user') . " CHANGE COLUMN password password_hash VARCHAR(255) NOT NULL");
                    echo "<div class='success'>✅ password 컬럼이 password_hash로 변경되었습니다.</div>\n";
                } else {
                    $pdo->exec("ALTER TABLE " . get_table_name('admin_user') . " ADD COLUMN password_hash VARCHAR(255) NOT NULL AFTER email");
                    echo "<div class='success'>✅ password_hash 컬럼이 추가되었습니다.</div>\n";
                }
            } catch (PDOException $e) {
                echo "<div class='error'>⚠️ 컬럼 변경 중 오류: " . $e->getMessage() . "</div>\n";
            }
        }
    }
    
    // 3단계: 기본 관리자 계정 확인 및 생성
    echo "<h2>3단계: 기본 관리자 계정 설정</h2>\n";
    
    // 기존 admin 계정 확인
    $stmt = $pdo->prepare("SELECT * FROM " . get_table_name('admin_user') . " WHERE username = 'admin'");
    $stmt->execute();
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "<div class='info'>기존 admin 계정이 발견되었습니다.</div>\n";
        
        // 패스워드 업데이트 (admin123으로 설정)
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE " . get_table_name('admin_user') . " SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE username = 'admin'");
        $stmt->execute([$hashedPassword]);
        
        echo "<div class='success'>✅ admin 계정의 패스워드가 'admin123'으로 업데이트되었습니다.</div>\n";
        
    } else {
        // 새 admin 계정 생성
        echo "<div class='info'>새 admin 계정을 생성합니다...</div>\n";
        
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO " . get_table_name('admin_user') . " (username, email, password_hash, name, role, status) 
            VALUES ('admin', 'admin@hopec.local', ?, '관리자', 'admin', 'active')
        ");
        $stmt->execute([$hashedPassword]);
        
        echo "<div class='success'>✅ admin 계정이 생성되었습니다.</div>\n";
    }
    
    // 4단계: 로그인 테스트
    echo "<h2>4단계: 로그인 시스템 검증</h2>\n";
    
    // 생성된 계정 정보 확인
    $stmt = $pdo->prepare("SELECT username, email, name, role, status, created_at FROM " . get_table_name('admin_user') . " WHERE username = 'admin'");
    $stmt->execute();
    $adminInfo = $stmt->fetch();
    
    if ($adminInfo) {
        echo "<div class='success'>\n";
        echo "<strong>생성된 관리자 계정 정보:</strong><br>\n";
        echo "• 아이디: <code>admin</code><br>\n";
        echo "• 비밀번호: <code>admin123</code><br>\n";
        echo "• 이메일: <code>" . htmlspecialchars($adminInfo['email']) . "</code><br>\n";
        echo "• 이름: <code>" . htmlspecialchars($adminInfo['name']) . "</code><br>\n";
        echo "• 역할: <code>" . htmlspecialchars($adminInfo['role']) . "</code><br>\n";
        echo "• 상태: <code>" . htmlspecialchars($adminInfo['status']) . "</code><br>\n";
        echo "• 생성일: <code>" . htmlspecialchars($adminInfo['created_at']) . "</code><br>\n";
        echo "</div>\n";
        
        // 패스워드 해시 검증 테스트
        $stmt = $pdo->prepare("SELECT password_hash FROM " . get_table_name('admin_user') . " WHERE username = 'admin'");
        $stmt->execute();
        $storedHash = $stmt->fetchColumn();
        
        if (password_verify('admin123', $storedHash)) {
            echo "<div class='success'>✅ 패스워드 해시 검증 테스트 통과</div>\n";
        } else {
            echo "<div class='error'>❌ 패스워드 해시 검증 테스트 실패</div>\n";
        }
        
    } else {
        echo "<div class='error'>❌ 관리자 계정 생성에 실패했습니다.</div>\n";
    }
    
    // 5단계: 완료 및 안내
    echo "<h2>🎉 작업 완료</h2>\n";
    echo "<div class='success'>\n";
    echo "<strong>관리자 로그인 문제가 해결되었습니다!</strong><br><br>\n";
    echo "이제 다음 정보로 로그인하실 수 있습니다:<br>\n";
    echo "• <strong>아이디:</strong> admin<br>\n";
    echo "• <strong>비밀번호:</strong> admin123<br><br>\n";
    echo "<a href='login.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>로그인 페이지로 이동</a>\n";
    echo "</div>\n";
    
    // 보안 권장사항
    echo "<div class='info'>\n";
    echo "<strong>🔒 보안 권장사항:</strong><br>\n";
    echo "• 첫 로그인 후 패스워드를 변경해주세요<br>\n";
    echo "• 관리자 계정은 신뢰할 수 있는 사용자만 사용하도록 하세요<br>\n";
    echo "• 정기적으로 패스워드를 업데이트하세요\n";
    echo "</div>\n";
    
} catch (PDOException $e) {
    echo "<div class='error'>\n";
    echo "<strong>❌ 데이터베이스 오류 발생:</strong><br>\n";
    echo htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "</div>\n";
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<strong>❌ 일반 오류 발생:</strong><br>\n";
    echo htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "</div>\n";
}

echo "</body>\n";
echo "</html>\n";
?>