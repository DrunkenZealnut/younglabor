<?php

namespace BoardTemplates\Config;

/**
 * Board Templates 테이블 구조 및 칼럼 정의
 * 
 * 다양한 프로젝트에서 사용할 수 있도록 테이블명과 칼럼명을 
 * 설정 가능하게 만든 클래스입니다.
 */
class BoardTableConfig
{
    private string $tablePrefix;
    private array $tableNames;
    private array $columnMappings;

    public function __construct(string $tablePrefix = 'bt_')
    {
        $this->tablePrefix = $tablePrefix;
        $this->initializeTableNames();
        $this->initializeColumnMappings();
    }

    /**
     * 테이블명 정의
     */
    private function initializeTableNames(): void
    {
        $this->tableNames = [
            // 게시판 관련 테이블
            'posts' => $this->tablePrefix . 'posts',
            'categories' => $this->tablePrefix . 'categories', 
            'attachments' => $this->tablePrefix . 'attachments',
            'comments' => $this->tablePrefix . 'comments',
            
            // 통계 및 메타 테이블
            'post_views' => $this->tablePrefix . 'post_views',
            'post_likes' => $this->tablePrefix . 'post_likes',
            'board_settings' => $this->tablePrefix . 'board_settings',
            
            // 사용자 관련 (기존 프로젝트 테이블과 연동)
            'users' => 'edu_users',  // UDONG 프로젝트 기본값
            'boards' => 'labor_rights_boards'  // UDONG 프로젝트 기본값
        ];
    }

    /**
     * 칼럼명 매핑 정의
     * 
     * 각 테이블의 칼럼명을 다른 프로젝트에서 사용하는
     * 칼럼명으로 매핑할 수 있도록 설정
     */
    private function initializeColumnMappings(): void
    {
        $this->columnMappings = [
            'posts' => [
                'id' => 'post_id',
                'category_id' => 'category_id',
                'user_id' => 'user_id',
                'title' => 'title',
                'content' => 'content', 
                'author_name' => 'author_name',
                'password' => 'password',
                'is_notice' => 'is_notice',
                'is_private' => 'is_private',
                'view_count' => 'view_count',
                'like_count' => 'like_count',
                'comment_count' => 'comment_count',
                'is_active' => 'is_active',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at'
            ],
            
            'categories' => [
                'id' => 'category_id',
                'type' => 'category_type',
                'name' => 'category_name', 
                'description' => 'description',
                'order_index' => 'order_index',
                'is_active' => 'is_active',
                'created_at' => 'created_at'
            ],
            
            'attachments' => [
                'id' => 'attachment_id',
                'post_id' => 'post_id',
                'original_name' => 'original_name',
                'stored_name' => 'stored_name',
                'file_path' => 'file_path',
                'file_size' => 'file_size',
                'file_type' => 'file_type',
                'mime_type' => 'mime_type',
                'download_count' => 'download_count',
                'is_active' => 'is_active',
                'created_at' => 'created_at'
            ],
            
            'comments' => [
                'id' => 'comment_id',
                'post_id' => 'post_id',
                'parent_id' => 'parent_id',
                'user_id' => 'user_id',
                'author_name' => 'author_name',
                'password' => 'password',
                'content' => 'content',
                'depth' => 'depth',
                'is_active' => 'is_active',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at'
            ]
        ];
    }

    /**
     * 테이블명 반환
     */
    public function getTableName(string $tableKey): string
    {
        if (!isset($this->tableNames[$tableKey])) {
            throw new \InvalidArgumentException("Unknown table key: {$tableKey}");
        }
        
        return $this->tableNames[$tableKey];
    }

    /**
     * 칼럼명 반환
     */
    public function getColumnName(string $tableKey, string $columnKey): string
    {
        if (!isset($this->columnMappings[$tableKey])) {
            throw new \InvalidArgumentException("Unknown table key: {$tableKey}");
        }
        
        if (!isset($this->columnMappings[$tableKey][$columnKey])) {
            throw new \InvalidArgumentException("Unknown column key: {$columnKey} for table: {$tableKey}");
        }
        
        return $this->columnMappings[$tableKey][$columnKey];
    }

    /**
     * SELECT 쿼리용 칼럼 리스트 반환
     */
    public function getSelectColumns(string $tableKey, array $columnKeys = []): string
    {
        if (empty($columnKeys)) {
            // 모든 칼럼 반환
            $columnKeys = array_keys($this->columnMappings[$tableKey] ?? []);
        }
        
        $columns = [];
        foreach ($columnKeys as $key) {
            $columns[] = $this->getColumnName($tableKey, $key) . ' AS ' . $key;
        }
        
        return implode(', ', $columns);
    }

    /**
     * 테이블 접두사 반환
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * 모든 테이블명 반환
     */
    public function getAllTableNames(): array
    {
        return $this->tableNames;
    }

    /**
     * 테이블명 및 칼럼 커스터마이징
     */
    public function customizeTable(string $tableKey, string $tableName): void
    {
        $this->tableNames[$tableKey] = $tableName;
    }

    public function customizeColumn(string $tableKey, string $columnKey, string $columnName): void
    {
        if (!isset($this->columnMappings[$tableKey])) {
            $this->columnMappings[$tableKey] = [];
        }
        
        $this->columnMappings[$tableKey][$columnKey] = $columnName;
    }

    /**
     * UDONG 프로젝트용 테이블 설정
     */
    public static function createForUdong(): self
    {
        $config = new self('atti_board_');
        
        // UDONG 프로젝트 특별 테이블명 설정
        $config->customizeTable('users', 'edu_users');
        $config->customizeTable('boards', 'labor_rights_boards');
        $config->customizeTable('posts', 'atti_board_posts');
        $config->customizeTable('categories', 'atti_board_categories');
        $config->customizeTable('attachments', 'atti_board_attachments');
        $config->customizeTable('comments', 'atti_board_comments');
        
        return $config;
    }

    /**
     * 새 프로젝트용 테이블 설정 (기본 bt_ 접두사)
     */
    public static function createForNewProject(string $prefix = 'bt_'): self
    {
        $config = new self($prefix);
        
        // 새 프로젝트용 기본 테이블명
        $config->customizeTable('users', $prefix . 'users');
        $config->customizeTable('boards', $prefix . 'boards');
        
        return $config;
    }

    /**
     * 환경변수 기반 테이블 설정
     */
    public static function createFromEnvironment(string $envPrefix = 'BT_'): self
    {
        $tablePrefix = $_ENV[$envPrefix . 'TABLE_PREFIX'] ?? 'bt_';
        $config = new self($tablePrefix);
        
        // 환경변수에서 커스텀 테이블명 로드
        foreach (['posts', 'categories', 'attachments', 'comments', 'users', 'boards'] as $table) {
            $envKey = $envPrefix . 'TABLE_' . strtoupper($table);
            if (isset($_ENV[$envKey])) {
                $config->customizeTable($table, $_ENV[$envKey]);
            }
        }
        
        return $config;
    }
}