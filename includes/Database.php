<?php
/**
 * 데이터베이스 연결 클래스
 */
class Database {
    private static $instance = null;
    private $pdo;

    private function __clone() {}

    public function __wakeup() {
        throw new \RuntimeException('Cannot unserialize singleton');
    }

    private function __construct() {
        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', '3306');
        $dbname = env('DB_NAME', 'kcsvictory');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $charset = env('DB_CHARSET', 'utf8mb4');
        $socket = env('DB_SOCKET', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        if ($socket && file_exists($socket)) {
            $dsn = "mysql:unix_socket={$socket};dbname={$dbname};charset={$charset}";
        }

        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
