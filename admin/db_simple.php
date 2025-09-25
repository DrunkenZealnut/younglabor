<?php
/**
 * 간단한 데이터베이스 연결 (테마 프리셋용)
 * 기존 db.php의 문제를 해결한 단순화 버전
 */

try {
    // 직접적인 데이터베이스 연결 설정
    $host = 'localhost';
    $dbname = function_exists('env') ? env('PROJECT_SLUG', 'hopec') : 'hopec';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';
    
    // PDO 연결 (문자셋 문제 해결)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // 문자셋 설정 (별도로 실행하여 오류 방지)
    $pdo->exec("SET NAMES $charset");
    $pdo->exec("SET time_zone = '+09:00'");
    
} catch (PDOException $e) {
    die("DB 연결 실패: " . $e->getMessage());
}
?>