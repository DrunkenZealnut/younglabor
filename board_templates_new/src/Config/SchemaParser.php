<?php

namespace BoardTemplates\Config;

use BoardTemplates\Config\BoardTableConfig;

/**
 * SQL 스키마 파서
 * 
 * SQL CREATE 문이나 데이터베이스 스키마를 분석하여
 * BoardTableConfig를 자동 생성하는 클래스
 */
class SchemaParser
{
    private array $tablePatterns = [
        'posts' => ['post', 'article', 'content', 'board'],
        'categories' => ['category', 'cat', 'type', 'classification'],
        'attachments' => ['attachment', 'file', 'upload', 'media'],
        'comments' => ['comment', 'reply', 'response'],
        'users' => ['user', 'member', 'account', 'auth'],
        'boards' => ['board', 'forum', 'section']
    ];
    
    private array $columnPatterns = [
        'id' => ['id', 'idx', 'seq', 'no'],
        'title' => ['title', 'subject', 'name', 'heading'],
        'content' => ['content', 'body', 'text', 'message', 'description'],
        'author' => ['author', 'writer', 'user', 'creator'],
        'created_at' => ['created_at', 'create_time', 'reg_date', 'insert_date'],
        'updated_at' => ['updated_at', 'modify_time', 'mod_date', 'update_date']
    ];

    /**
     * SQL 파일에서 스키마를 파싱하여 BoardTableConfig 생성
     */
    public function parseFromSqlFile(string $sqlFilePath): BoardTableConfig
    {
        if (!file_exists($sqlFilePath)) {
            throw new \InvalidArgumentException("SQL file not found: {$sqlFilePath}");
        }
        
        $sqlContent = file_get_contents($sqlFilePath);
        return $this->parseFromSqlString($sqlContent);
    }

    /**
     * SQL 문자열에서 스키마 파싱
     */
    public function parseFromSqlString(string $sqlContent): BoardTableConfig
    {
        $tables = $this->extractTables($sqlContent);
        $tablePrefix = $this->detectTablePrefix(array_keys($tables));
        
        $config = new BoardTableConfig($tablePrefix);
        
        foreach ($tables as $tableName => $columns) {
            $tableType = $this->classifyTable($tableName, $columns);
            if ($tableType) {
                $cleanTableName = $this->removePrefix($tableName, $tablePrefix);
                $config->customizeTable($tableType, $tableName);
                
                // 컬럼 매핑
                foreach ($columns as $columnName => $columnInfo) {
                    $columnType = $this->classifyColumn($columnName, $columnInfo);
                    if ($columnType) {
                        $config->customizeColumn($tableType, $columnType, $columnName);
                    }
                }
            }
        }
        
        return $config;
    }

    /**
     * 데이터베이스 연결에서 직접 스키마 조회
     */
    public function parseFromDatabase(\PDO $pdo, string $databaseName = null): BoardTableConfig
    {
        if ($databaseName === null) {
            // 현재 데이터베이스명 조회
            $stmt = $pdo->query("SELECT DATABASE()");
            $databaseName = $stmt->fetchColumn();
        }
        
        $tables = $this->extractTablesFromDatabase($pdo, $databaseName);
        $tablePrefix = $this->detectTablePrefix(array_keys($tables));
        
        $config = new BoardTableConfig($tablePrefix);
        
        foreach ($tables as $tableName => $columns) {
            $tableType = $this->classifyTable($tableName, $columns);
            if ($tableType) {
                $config->customizeTable($tableType, $tableName);
                
                foreach ($columns as $columnName => $columnInfo) {
                    $columnType = $this->classifyColumn($columnName, $columnInfo);
                    if ($columnType) {
                        $config->customizeColumn($tableType, $columnType, $columnName);
                    }
                }
            }
        }
        
        return $config;
    }

    /**
     * SQL에서 테이블 정보 추출
     */
    private function extractTables(string $sqlContent): array
    {
        $tables = [];
        
        // CREATE TABLE 문 매칭
        preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\((.*?)\);/ims', $sqlContent, $matches);
        
        for ($i = 0; $i < count($matches[1]); $i++) {
            $tableName = $matches[1][$i];
            $tableContent = $matches[2][$i];
            
            $columns = $this->parseColumns($tableContent);
            $tables[$tableName] = $columns;
        }
        
        return $tables;
    }

    /**
     * 테이블 컬럼 파싱
     */
    private function parseColumns(string $tableContent): array
    {
        $columns = [];
        $lines = explode(',', $tableContent);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 컬럼 정의 라인 매칭
            if (preg_match('/^`?(\w+)`?\s+(\w+)(?:\(([^)]+)\))?\s*(.*)/i', $line, $matches)) {
                $columnName = $matches[1];
                $dataType = $matches[2];
                $length = $matches[3] ?? null;
                $attributes = $matches[4] ?? '';
                
                $columns[$columnName] = [
                    'type' => strtolower($dataType),
                    'length' => $length,
                    'attributes' => strtolower($attributes),
                    'nullable' => !stripos($attributes, 'NOT NULL'),
                    'primary' => stripos($attributes, 'PRIMARY KEY') !== false,
                    'auto_increment' => stripos($attributes, 'AUTO_INCREMENT') !== false
                ];
            }
        }
        
        return $columns;
    }

    /**
     * 데이터베이스에서 테이블 정보 조회
     */
    private function extractTablesFromDatabase(\PDO $pdo, string $databaseName): array
    {
        $tables = [];
        
        // 테이블 목록 조회
        $stmt = $pdo->prepare("
            SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
        ");
        $stmt->execute([$databaseName]);
        $tableNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach ($tableNames as $tableName) {
            // 각 테이블의 컬럼 정보 조회
            $stmt = $pdo->prepare("
                SELECT 
                    COLUMN_NAME,
                    DATA_TYPE,
                    COLUMN_TYPE,
                    IS_NULLABLE,
                    COLUMN_KEY,
                    EXTRA
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ");
            $stmt->execute([$databaseName, $tableName]);
            $columns = [];
            
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $columns[$row['COLUMN_NAME']] = [
                    'type' => $row['DATA_TYPE'],
                    'column_type' => $row['COLUMN_TYPE'],
                    'nullable' => $row['IS_NULLABLE'] === 'YES',
                    'primary' => $row['COLUMN_KEY'] === 'PRI',
                    'auto_increment' => strpos($row['EXTRA'], 'auto_increment') !== false
                ];
            }
            
            $tables[$tableName] = $columns;
        }
        
        return $tables;
    }

    /**
     * 테이블 접두사 감지
     */
    private function detectTablePrefix($tableNames): string
    {
        if (empty($tableNames)) {
            return '';
        }
        
        $tableNames = array_values($tableNames);
        $firstTable = $tableNames[0];
        
        // 공통 접두사 찾기
        $commonPrefix = '';
        $prefixLength = 0;
        
        for ($i = 0; $i < strlen($firstTable); $i++) {
            $char = $firstTable[$i];
            $isCommon = true;
            
            foreach ($tableNames as $tableName) {
                if ($i >= strlen($tableName) || $tableName[$i] !== $char) {
                    $isCommon = false;
                    break;
                }
            }
            
            if (!$isCommon) {
                break;
            }
            
            $commonPrefix .= $char;
            if ($char === '_') {
                $prefixLength = $i + 1;
            }
        }
        
        return substr($commonPrefix, 0, $prefixLength);
    }

    /**
     * 테이블 분류 (posts, categories 등)
     */
    private function classifyTable(string $tableName, array $columns): ?string
    {
        $cleanName = strtolower($this->removePrefix($tableName, $this->detectTablePrefix([$tableName])));
        
        foreach ($this->tablePatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($cleanName, $pattern) !== false) {
                    // 컬럼으로 재검증
                    if ($this->validateTableType($type, $columns)) {
                        return $type;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * 컬럼 분류
     */
    private function classifyColumn(string $columnName, array $columnInfo): ?string
    {
        $cleanName = strtolower($columnName);
        
        foreach ($this->columnPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($cleanName, $pattern) !== false) {
                    return $type;
                }
            }
        }
        
        return null;
    }

    /**
     * 테이블 타입 검증
     */
    private function validateTableType(string $type, array $columns): bool
    {
        $columnNames = array_keys($columns);
        $lowerColumnNames = array_map('strtolower', $columnNames);
        
        switch ($type) {
            case 'posts':
                return in_array('title', $lowerColumnNames) || in_array('content', $lowerColumnNames);
            case 'categories':
                return in_array('name', $lowerColumnNames) || in_array('category_name', $lowerColumnNames);
            case 'comments':
                return in_array('content', $lowerColumnNames) || in_array('comment', $lowerColumnNames);
            case 'attachments':
                return in_array('filename', $lowerColumnNames) || in_array('file_path', $lowerColumnNames);
            default:
                return true;
        }
    }

    /**
     * 테이블명에서 접두사 제거
     */
    private function removePrefix(string $tableName, string $prefix): string
    {
        if (empty($prefix)) {
            return $tableName;
        }
        
        return (strlen($prefix) > 0 && strpos($tableName, $prefix) === 0) 
            ? substr($tableName, strlen($prefix)) 
            : $tableName;
    }

    /**
     * 스키마 분석 결과를 설정 파일로 저장
     */
    public function exportToConfigFile(BoardTableConfig $config, string $filePath): void
    {
        $configArray = [
            'table_prefix' => $config->getTablePrefix(),
            'tables' => $config->getAllTableNames(),
            'generated_at' => date('Y-m-d H:i:s'),
            'generator' => 'BoardTemplates SchemaParser v1.0'
        ];
        
        $phpCode = "<?php\n\nreturn " . var_export($configArray, true) . ";\n";
        file_put_contents($filePath, $phpCode);
    }

    /**
     * 스키마 정보를 JSON으로 출력
     */
    public function toJson(BoardTableConfig $config): string
    {
        return json_encode([
            'table_prefix' => $config->getTablePrefix(),
            'tables' => $config->getAllTableNames(),
            'generated_at' => date('c')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}