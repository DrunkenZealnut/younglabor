<?php
/**
 * 데이터베이스 매니저
 * 
 * 그누보드 레거시 테이블(g5_)과 모던 테이블(hopec_) 동시 지원
 * admin 시스템과 호환되는 PDO 기반 데이터베이스 관리
 */

class DatabaseManager
{
    private static $instance = null;
    private static $connection = null;
    private static $config = null;
    
    private function __construct() {}
    
    /**
     * 싱글톤 인스턴스 반환
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 데이터베이스 초기화
     */
    public static function initialize()
    {
        self::$config = $GLOBALS['hopec_config']['database'] ?? [];
        self::connect();
    }
    
    /**
     * 데이터베이스 연결
     */
    private static function connect()
    {
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        $config = self::$config['connections']['mysql'] ?? [];
        
        try {
            // 빈 비밀번호를 null로 변환
            $password = empty($config['password']) ? null : $config['password'];
            
            // 환경별 최적화된 연결 순서 결정
            $connectionMethods = self::getOptimalConnectionOrder();
            $lastError = null;
            
            foreach ($connectionMethods as $method) {
                try {
                    $dsn = self::buildDsn($method, $config);
                    if ($dsn) {
                        self::$connection = new PDO(
                            $dsn,
                            $config['username'],
                            $password,
                            $config['options'] ?? []
                        );
                        break; // 연결 성공
                    }
                } catch (PDOException $e) {
                    $lastError = $e;
                    continue; // 다음 방법 시도
                }
            }
            
            // 모든 연결 방법 실패시 예외 발생
            if (self::$connection === null) {
                throw $lastError ?: new PDOException('모든 데이터베이스 연결 방법이 실패했습니다.');
            }
            
            // 쿼리 로그 활성화
            if (self::$config['query_log'] ?? false) {
                self::enableQueryLogging();
            }
            
        } catch (PDOException $e) {
            throw new Exception('데이터베이스 연결 실패: ' . $e->getMessage());
        }
        
        return self::$connection;
    }
    
    /**
     * 데이터베이스 연결 반환
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }
    
    /**
     * 테이블명 생성 (hopec_ 프리픽스만 사용)
     */
    public static function getTableName($table)
    {
        $prefix = self::$config['prefixes']['modern'] ?? 'hopec_';
        return $prefix . $table;
    }
    
    /**
     * 테이블이 존재하는지 확인
     */
    public static function hasTable($table)
    {
        $tableName = self::getTableName($table);
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * SELECT 쿼리 실행 (단일 행)
     */
    public static function selectOne($query, $params = [])
    {
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            self::logError('SELECT 쿼리 오류', $query, $params, $e);
            throw $e;
        }
    }
    
    /**
     * SELECT 쿼리 실행
     */
    public static function select($query, $params = [])
    {
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            self::logError('SELECT 쿼리 오류', $query, $params, $e);
            throw $e;
        }
    }
    
    /**
     * 일반 쿼리 실행
     */
    public static function execute($query, $params = [])
    {
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            self::logError('쿼리 실행 오류', $query, $params, $e);
            throw $e;
        }
    }
    
    /**
     * INSERT 쿼리 실행
     */
    public static function insert($table, $data)
    {
        $tableName = self::getTableName($table);
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
        
        $query = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $tableName,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );
        
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            self::logError('INSERT 쿼리 오류', $query, $data, $e);
            throw $e;
        }
    }
    
    /**
     * UPDATE 쿼리 실행
     */
    public static function update($table, $data, $where)
    {
        $tableName = self::getTableName($table);
        $setClause = [];
        
        foreach (array_keys($data) as $column) {
            $setClause[] = "`{$column}` = :{$column}";
        }
        
        $query = sprintf(
            "UPDATE `%s` SET %s WHERE %s",
            $tableName,
            implode(', ', $setClause),
            $where
        );
        
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            self::logError('UPDATE 쿼리 오류', $query, $data, $e);
            throw $e;
        }
    }
    
    /**
     * DELETE 쿼리 실행
     */
    public static function delete($table, $where, $params = [])
    {
        $tableName = self::getTableName($table);
        $query = sprintf("DELETE FROM `%s` WHERE %s", $tableName, $where);
        
        $pdo = self::getConnection();
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            self::logError('DELETE 쿼리 오류', $query, $params, $e);
            throw $e;
        }
    }
    
    /**
     * 트랜잭션 시작
     */
    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * 트랜잭션 커밋
     */
    public static function commit()
    {
        return self::getConnection()->commit();
    }
    
    /**
     * 트랜잭션 롤백
     */
    public static function rollback()
    {
        return self::getConnection()->rollBack();
    }
    
    /**
     * 쿼리 로깅 활성화
     */
    private static function enableQueryLogging()
    {
        // 추후 구현 예정
    }
    
    /**
     * 오류 로깅
     */
    private static function logError($message, $query, $params, $exception)
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'query' => $query,
            'params' => $params,
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
        
        $logFile = HOPEC_BASE_PATH . '/logs/database.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 안전한 테이블명 검증 (admin bootstrap.php에서 포팅)
     */
    public static function validateTableName($tableName)
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName);
    }
    
    /**
     * 안전한 컬럼명 검증 (admin bootstrap.php에서 포팅)
     */
    public static function validateColumnName($columnName)
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $columnName);
    }
    
    /**
     * 환경별 최적화된 연결 순서 결정
     */
    private static function getOptimalConnectionOrder()
    {
        // 환경 감지
        $isXampp = self::isXamppEnvironment();
        $isLinux = PHP_OS_FAMILY === 'Linux';
        $isProduction = env('APP_ENV') === 'production';
        
        if ($isProduction) {
            // 프로덕션: TCP 연결 우선
            return ['tcp', 'socket_linux', 'socket_xampp'];
        } elseif ($isXampp) {
            // XAMPP 환경: 소켓 우선
            return ['socket_xampp', 'tcp', 'socket_linux'];
        } elseif ($isLinux) {
            // 리눅스 환경: 리눅스 소켓 우선
            return ['socket_linux', 'tcp', 'socket_xampp'];
        } else {
            // 기타 환경: TCP 우선
            return ['tcp', 'socket_linux', 'socket_xampp'];
        }
    }
    
    /**
     * XAMPP 환경 감지
     */
    private static function isXamppEnvironment()
    {
        // XAMPP 설치 경로 확인
        $xamppPaths = [
            '/Applications/XAMPP/xamppfiles',  // macOS
            'C:\\xampp',                       // Windows
            '/opt/lampp'                       // Linux XAMPP
        ];
        
        foreach ($xamppPaths as $path) {
            if (is_dir($path)) {
                return true;
            }
        }
        
        // 환경변수나 서버 소프트웨어로 감지
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        return stripos($serverSoftware, 'xampp') !== false ||
               getenv('XAMPP_ROOT') !== false;
    }
    
    /**
     * 연결 방식별 DSN 생성
     */
    private static function buildDsn($method, $config)
    {
        switch ($method) {
            case 'tcp':
                return sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                );
                
            case 'socket_xampp':
                $socket = env('DB_SOCKET_XAMPP', '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
                if (file_exists($socket)) {
                    return sprintf(
                        'mysql:unix_socket=%s;dbname=%s;charset=%s',
                        $socket,
                        $config['database'],
                        $config['charset']
                    );
                }
                return null;
                
            case 'socket_linux':
                $socket = env('DB_SOCKET_LINUX', '/var/run/mysqld/mysqld.sock');
                if (file_exists($socket)) {
                    return sprintf(
                        'mysql:unix_socket=%s;dbname=%s;charset=%s',
                        $socket,
                        $config['database'],
                        $config['charset']
                    );
                }
                return null;
                
            default:
                return null;
        }
    }
}