<?php

namespace BoardTemplates\Admin;

use PDO;
use PDOException;
use Exception;

/**
 * 간단한 데이터베이스 설정 관리 클래스
 */
class DatabaseSettingsManager
{
    private string $configFile;
    private string $backupDir;

    public function __construct()
    {
        $this->configFile = __DIR__ . '/../../config/database_settings.json';
        $this->backupDir = __DIR__ . '/../../config/backups';
    }

    /**
     * 현재 설정 로드
     */
    public function loadCurrentSettings(): array
    {
        $defaultConfig = [
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
                'user' => 'root',
                'password' => '',
                'database' => 'woodong615',
                'charset' => 'utf8mb4',
                'driver' => 'mysql'
            ],
            'tables' => [
                'posts' => 'atti_board_posts',
                'categories' => 'atti_board_categories',
                'attachments' => 'atti_board_attachments',
                'comments' => 'atti_board_comments',
                'users' => 'edu_users',
                'boards' => 'labor_rights_boards'
            ],
            'board' => [
                'table_prefix' => 'atti_board_',
            ],
            'columns' => [
                'posts' => [
                    'id' => 'post_id',
                    'title' => 'title',
                    'content' => 'content',
                    'author' => 'author_name',
                    'created_at' => 'created_at',
                    'category_id' => 'category_id'
                ],
                'categories' => [
                    'name' => 'category_name',
                    'type' => 'category_type'
                ],
                'attachments' => [
                    'filename' => 'original_filename',
                    'filesize' => 'file_size'
                ]
            ]
        ];

        if (!file_exists($this->configFile)) {
            return $defaultConfig;
        }

        $content = file_get_contents($this->configFile);
        if ($content === false) {
            return $defaultConfig;
        }

        $config = json_decode($content, true);
        if ($config === null) {
            return $defaultConfig;
        }

        return array_merge_recursive($defaultConfig, $config);
    }

    /**
     * 데이터베이스 설정 저장
     */
    public function saveDatabaseSettings(array $data): array
    {
        try {
            $currentConfig = $this->loadCurrentSettings();
            
            // 데이터베이스 설정 업데이트
            $currentConfig['database'] = [
                'host' => trim($data['db_host'] ?? 'localhost'),
                'port' => (int)($data['db_port'] ?? 3306),
                'user' => trim($data['db_user'] ?? ''),
                'password' => $data['db_password'] ?? '',
                'database' => trim($data['db_name'] ?? ''),
                'charset' => trim($data['db_charset'] ?? 'utf8mb4'),
                'driver' => 'mysql'
            ];
            
            // 디렉토리 생성
            $this->ensureDirectoriesExist();
            
            // 설정 파일 저장
            $result = file_put_contents(
                $this->configFile, 
                json_encode($currentConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            if ($result === false) {
                throw new Exception('설정 파일 저장에 실패했습니다.');
            }

            return ['success' => true, 'message' => '데이터베이스 설정이 저장되었습니다.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 테이블 설정 저장
     */
    public function saveTableSettings(array $data): array
    {
        try {
            $currentConfig = $this->loadCurrentSettings();
            
            // 테이블 접두사 업데이트
            if (isset($data['table_prefix'])) {
                $prefix = trim($data['table_prefix']);
                if (!preg_match('/^[a-zA-Z0-9_]*$/', $prefix)) {
                    throw new Exception('테이블 접두사는 영문, 숫자, 언더스코어만 사용할 수 있습니다.');
                }
                $currentConfig['board']['table_prefix'] = $prefix;
            }
            
            // 테이블명 업데이트
            $tableNames = ['posts', 'categories', 'attachments', 'comments', 'users', 'boards'];
            foreach ($tableNames as $tableName) {
                $key = 'table_' . $tableName;
                if (isset($data[$key])) {
                    $value = trim($data[$key]);
                    if (empty($value)) {
                        throw new Exception("{$tableName} 테이블명은 필수입니다.");
                    }
                    $currentConfig['tables'][$tableName] = $value;
                }
            }
            
            // 디렉토리 생성
            $this->ensureDirectoriesExist();
            
            // 설정 파일 저장
            $result = file_put_contents(
                $this->configFile, 
                json_encode($currentConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            if ($result === false) {
                throw new Exception('설정 파일 저장에 실패했습니다.');
            }

            return ['success' => true, 'message' => '테이블 설정이 저장되었습니다.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 컬럼 매핑 설정 저장
     */
    public function saveColumnSettings(array $data): array
    {
        try {
            $config = $this->loadCurrentSettings();
            
            // 컬럼 설정 업데이트 - 게시글 테이블
            if (!isset($config['columns']['posts'])) {
                $config['columns']['posts'] = [];
            }
            $config['columns']['posts']['id'] = trim($data['posts_id'] ?? 'post_id');
            $config['columns']['posts']['title'] = trim($data['posts_title'] ?? 'title');
            $config['columns']['posts']['content'] = trim($data['posts_content'] ?? 'content');
            $config['columns']['posts']['author'] = trim($data['posts_author'] ?? 'author_name');
            $config['columns']['posts']['created_at'] = trim($data['posts_created_at'] ?? 'created_at');
            $config['columns']['posts']['category_id'] = trim($data['posts_category_id'] ?? 'category_id');
            
            // 컬럼 설정 업데이트 - 카테고리 테이블
            if (!isset($config['columns']['categories'])) {
                $config['columns']['categories'] = [];
            }
            $config['columns']['categories']['name'] = trim($data['categories_name'] ?? 'category_name');
            $config['columns']['categories']['type'] = trim($data['categories_type'] ?? 'category_type');
            
            // 컬럼 설정 업데이트 - 첨부파일 테이블
            if (!isset($config['columns']['attachments'])) {
                $config['columns']['attachments'] = [];
            }
            $config['columns']['attachments']['filename'] = trim($data['attachments_filename'] ?? 'original_filename');
            $config['columns']['attachments']['filesize'] = trim($data['attachments_filesize'] ?? 'file_size');
            
            // 디렉토리 생성
            $this->ensureDirectoriesExist();
            
            // 설정 파일 저장
            $result = file_put_contents(
                $this->configFile,
                json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            
            if ($result === false) {
                throw new Exception('설정 파일 저장에 실패했습니다.');
            }
            
            return [
                'success' => true,
                'message' => '컬럼 매핑 설정이 저장되었습니다.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 데이터베이스 연결 테스트
     */
    public function testDatabaseConnection(array $config): array
    {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;charset=%s",
                $config['db_host'] ?? 'localhost',
                (int)($config['db_port'] ?? 3306),
                $config['db_charset'] ?? 'utf8mb4'
            );
            
            // 데이터베이스명이 있으면 추가
            if (!empty($config['db_name'])) {
                $dsn .= ";dbname=" . $config['db_name'];
            }
            
            $pdo = new PDO(
                $dsn,
                $config['db_user'] ?? '',
                $config['db_password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5 // 5초 타임아웃
                ]
            );
            
            // 연결 테스트 쿼리
            $stmt = $pdo->query('SELECT VERSION() as version, NOW() as current_time');
            $result = $stmt->fetch();
            
            return [
                'success' => true, 
                'message' => '데이터베이스 연결에 성공했습니다.',
                'details' => [
                    'mysql_version' => $result['version'],
                    'current_time' => $result['current_time'],
                    'charset' => $config['db_charset'] ?? 'utf8mb4'
                ]
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false, 
                'message' => '데이터베이스 연결에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 기본 설정으로 초기화
     */
    public function resetToDefaults(): array
    {
        try {
            // 기본 설정으로 초기화 (설정 파일 삭제)
            if (file_exists($this->configFile)) {
                unlink($this->configFile);
            }

            return ['success' => true, 'message' => '기본 설정으로 초기화되었습니다.'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 필요한 디렉토리들이 존재하는지 확인하고 생성
     */
    private function ensureDirectoriesExist(): void
    {
        $dirs = [
            dirname($this->configFile),
            $this->backupDir
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
}
?>