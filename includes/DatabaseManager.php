<?php
/**
 * 임시 데이터베이스 매니저 - 그누보드 호환성 제거 후 간단한 PDO 래퍼
 */
class DatabaseManager
{
    private static $connection = null;
    
    /**
     * 초기화 - 호환성을 위해 유지하지만 실제로는 아무것도 하지 않음
     */
    public static function initialize() {
        // 호환성을 위한 빈 메서드
    }
    
    /**
     * 연결 반환
     */
    public static function getConnection() {
        global $pdo;
        
        if (!isset($pdo)) {
            $host = env('DB_HOST', 'localhost');
            $dbname = env('DB_DATABASE', '');
            $username = env('DB_USERNAME', 'root');
            $password = env('DB_PASSWORD', '');
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        return $pdo;
    }
    
    /**
     * SELECT 쿼리 실행
     */
    public static function select($sql, $params = []) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * INSERT/UPDATE/DELETE 쿼리 실행
     */
    public static function execute($sql, $params = []) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * 단일 행 조회
     */
    public static function fetch($sql, $params = []) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 테이블명 반환 (접두사 포함)
     */
    public static function getTableName($table) {
        $prefix = env('DB_PREFIX', '');
        return $prefix . $table;
    }
    
    /**
     * 단일 행 SELECT 쿼리 실행 (selectOne)
     */
    public static function selectOne($sql, $params = []) {
        return self::fetch($sql, $params);
    }
    
    /**
     * INSERT 후 마지막 ID 반환
     */
    public static function insert($sql, $params = []) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    }
    
    /**
     * UPDATE/DELETE 실행 후 영향받은 행 수 반환
     */
    public static function update($sql, $params = []) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * DELETE 실행 (update와 동일하지만 명확성을 위해)
     */
    public static function delete($sql, $params = []) {
        return self::update($sql, $params);
    }
}
?>