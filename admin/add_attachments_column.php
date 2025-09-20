<?php
// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

require 'db.php';

try {
    // allow_attachments 컬럼 추가 여부 확인
    $stmt = $pdo->query("SHOW COLUMNS FROM hopec_boards LIKE 'allow_attachments'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // allow_attachments 컬럼 추가
        $sql = "ALTER TABLE hopec_boards ADD COLUMN allow_attachments TINYINT(1) DEFAULT 1 COMMENT '첨부파일 허용 여부'";
        $pdo->exec($sql);
        echo "<p style='color:green'>첨부파일 허용 컬럼이 성공적으로 추가되었습니다!</p>";
    } else {
        echo "<p style='color:blue'>첨부파일 허용 컬럼이 이미 존재합니다.</p>";
    }
    
    echo "<p><a href='index.php'>관리자 메인으로</a></p>";
    
} catch (PDOException $e) {
    die("<p style='color:red'>데이터베이스 오류: " . $e->getMessage() . "</p>");
}
?> 