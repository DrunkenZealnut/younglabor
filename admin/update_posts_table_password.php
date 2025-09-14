<?php
// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 현재 파일의 디렉토리 경로를 기준으로 상대 경로 지정
$admin_dir = __DIR__;
require_once $admin_dir . '/db.php';

try {
    // 1. 비밀번호 필드 추가 확인
    $checkPasswordStmt = $pdo->query("SHOW COLUMNS FROM hopec_posts LIKE 'password'");
    $passwordExists = $checkPasswordStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$passwordExists) {
        // 비밀번호 필드 추가
        $sql1 = "ALTER TABLE hopec_posts 
                ADD COLUMN password VARCHAR(255) NULL COMMENT '게시글 비밀번호(선택사항)'";
        
        $pdo->exec($sql1);
        echo "<p style='color:green'>hopec_posts 테이블에 password 필드가 성공적으로 추가되었습니다!</p>";
    } else {
        echo "<p style='color:blue'>password 필드가 이미 존재합니다.</p>";
    }
    
    // 2. 수정일 필드 추가 확인
    $checkUpdatedStmt = $pdo->query("SHOW COLUMNS FROM hopec_posts LIKE 'updated_at'");
    $updatedExists = $checkUpdatedStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$updatedExists) {
        // 수정일 필드 추가
        $sql2 = "ALTER TABLE hopec_posts 
                ADD COLUMN updated_at DATETIME NULL COMMENT '게시글 수정일시'";
        
        $pdo->exec($sql2);
        echo "<p style='color:green'>hopec_posts 테이블에 updated_at 필드가 성공적으로 추가되었습니다!</p>";
    } else {
        echo "<p style='color:blue'>updated_at 필드가 이미 존재합니다.</p>";
    }
    
    // 3. 수정 횟수 필드 추가 확인
    $checkEditCountStmt = $pdo->query("SHOW COLUMNS FROM hopec_posts LIKE 'edit_count'");
    $editCountExists = $checkEditCountStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editCountExists) {
        // 수정 횟수 필드 추가
        $sql3 = "ALTER TABLE hopec_posts 
                ADD COLUMN edit_count INT DEFAULT 0 COMMENT '게시글 수정 횟수'";
        
        $pdo->exec($sql3);
        echo "<p style='color:green'>hopec_posts 테이블에 edit_count 필드가 성공적으로 추가되었습니다!</p>";
    } else {
        echo "<p style='color:blue'>edit_count 필드가 이미 존재합니다.</p>";
    }
    
    // 4. IP 주소 필드 추가 확인 (추가 보안)
    $checkIpStmt = $pdo->query("SHOW COLUMNS FROM hopec_posts LIKE 'ip_address'");
    $ipExists = $checkIpStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ipExists) {
        // IP 주소 필드 추가
        $sql4 = "ALTER TABLE hopec_posts 
                ADD COLUMN ip_address VARCHAR(45) NULL COMMENT '작성자 IP 주소'";
        
        $pdo->exec($sql4);
        echo "<p style='color:green'>hopec_posts 테이블에 ip_address 필드가 성공적으로 추가되었습니다!</p>";
    } else {
        echo "<p style='color:blue'>ip_address 필드가 이미 존재합니다.</p>";
    }
    
    // 5. 비공개 글 여부 필드 추가 확인
    $checkPrivateStmt = $pdo->query("SHOW COLUMNS FROM hopec_posts LIKE 'is_private'");
    $privateExists = $checkPrivateStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$privateExists) {
        // 비공개 글 여부 필드 추가
        $sql5 = "ALTER TABLE hopec_posts 
                ADD COLUMN is_private TINYINT(1) DEFAULT 0 COMMENT '비공개 글 여부 (0: 공개, 1: 비공개)'";
        
        $pdo->exec($sql5);
        echo "<p style='color:green'>hopec_posts 테이블에 is_private 필드가 성공적으로 추가되었습니다!</p>";
    } else {
        echo "<p style='color:blue'>is_private 필드가 이미 존재합니다.</p>";
    }
    
    echo "<p><a href='index.php'>관리자 메인으로</a></p>";
    
} catch (PDOException $e) {
    die("<p style='color:red'>테이블 수정 실패: " . $e->getMessage() . "</p>");
}
?> 