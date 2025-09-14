<?php

namespace BoardTemplates\Admin;

use PDO;
use PDOException;
use Exception;

/**
 * 데이터베이스 설정 관리 클래스
 * 
 * Board Templates의 데이터베이스 관련 설정을 관리하는 클래스
 * - 설정 저장/로드
 * - 데이터베이스 연결 테스트
 * - 테이블 존재 확인
 * - 설정 검증
 */
class DatabaseSettingsManager
{
    private string $configFile;
    private string $backupDir;
    private ?array $defaultConfig;

    public function __construct()
    {
        $this->configFile = __DIR__ . '/../../config/database_settings.json';
        $this->backupDir = __DIR__ . '/../../config/backups';
        
        // 기본 설정 정의 - 지연 로딩으로 변경
        $this->defaultConfig = null;
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

    /**
     * 기본 설정 구성 반환
     */
    private function getDefaultConfiguration(): array
    {
        return [
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
            'file' => [
                'upload_base_path' => dirname(__DIR__, 3) . '/uploads',
                'upload_base_url' => '/uploads',
                'max_file_size' => 5 * 1024 * 1024, // 5MB
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'hwp'],
                'download_permission' => true
            ],
            'auth' => [
                'session_name' => 'PHPSESSID',
                'csrf_token_name' => 'csrf_token',
                'login_required' => true,
                'admin_required' => false,
                'user_id_session_key' => 'user_id',
                'user_level_session_key' => 'user_level'
            ],
            'board' => [
                'table_prefix' => 'atti_board_',
                'default_view_type' => 'table',
                'posts_per_page' => 10,
                'enable_comments' => true,
                'enable_attachments' => true,
                'enable_captcha' => false,
                'timezone' => 'Asia/Seoul'
            ],
            'columns' => [
                'posts' => [
                    'id' => 'post_id',
                    'title' => 'title',
                    'content' => 'content',
                    'author' => 'author_name',
                    'created_at' => 'created_at',
                    'category_id' => 'category_id',
                    'views' => 'view_count',
                    'is_active' => 'is_active'
                ],
                'categories' => [
                    'id' => 'category_id',
                    'name' => 'category_name',
                    'type' => 'category_type',
                    'order' => 'sort_order'
                ],
                'attachments' => [
                    'id' => 'attachment_id',
                    'post_id' => 'post_id',
                    'filename' => 'original_filename',
                    'stored_filename' => 'stored_filename',
                    'filesize' => 'file_size',
                    'file_type' => 'mime_type'
                ],
                'comments' => [
                    'id' => 'comment_id',
                    'post_id' => 'post_id',
                    'content' => 'content',
                    'author' => 'author_name',
                    'parent_id' => 'parent_id',
                    'created_at' => 'created_at'
                ]
            ],
            'url' => [
                'base_url' => '',  // 런타임에 설정
                'board_base_path' => '/board_templates',
                'theme_assets_url' => '',  // 런타임에 설정
                'admin_url' => ''  // 런타임에 설정
            ],
            'meta' => [
                'version' => '1.0.0',
                'last_updated' => date('Y-m-d H:i:s'),
                'updated_by' => $_SESSION['admin_user'] ?? 'system'
            ]
        ];
    }

    /**
     * 기본 URL 자동 감지
     */
    private function detectBaseUrl(): string
    {
        try {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            
            // 로컬 개발환경 감지
            if (strpos($host, 'localhost') !== false || 
                strpos($host, '127.0.0.1') !== false ||
                strpos($host, '.local') !== false) {
                
                // 포트 감지
                $port = $_SERVER['SERVER_PORT'] ?? '80';
                if ($port !== '80' && $port !== '443') {
                    return $protocol . '://' . $host . ':' . $port;
                }
            }
            
            return $protocol . '://' . $host;
        } catch (Exception $e) {
            // 기본값 반환
            return 'http://localhost';
        }
    }

    /**
     * 현재 설정 로드
     */
    public function loadCurrentSettings(): array
    {
        // 기본 설정 지연 로딩
        if ($this->defaultConfig === null) {
            $this->defaultConfig = $this->getDefaultConfiguration();
        }
        
        if (!file_exists($this->configFile)) {
            $defaultConfig = $this->defaultConfig;
            // URL 동적 설정
            $baseUrl = $this->detectBaseUrl();
            $defaultConfig['url']['base_url'] = $baseUrl;
            $defaultConfig['url']['theme_assets_url'] = $baseUrl . '/board_templates/assets';
            $defaultConfig['url']['admin_url'] = $baseUrl . '/admin';
            return $defaultConfig;
        }

        $content = file_get_contents($this->configFile);
        if ($content === false) {
            $defaultConfig = $this->defaultConfig;
            // URL 동적 설정
            $baseUrl = $this->detectBaseUrl();
            $defaultConfig['url']['base_url'] = $baseUrl;
            $defaultConfig['url']['theme_assets_url'] = $baseUrl . '/board_templates/assets';
            $defaultConfig['url']['admin_url'] = $baseUrl . '/admin';
            return $defaultConfig;
        }

        $config = json_decode($content, true);
        if ($config === null) {
            $defaultConfig = $this->defaultConfig;
            // URL 동적 설정
            $baseUrl = $this->detectBaseUrl();
            $defaultConfig['url']['base_url'] = $baseUrl;
            $defaultConfig['url']['theme_assets_url'] = $baseUrl . '/board_templates/assets';
            $defaultConfig['url']['admin_url'] = $baseUrl . '/admin';
            return $defaultConfig;
        }

        // 기본 설정과 병합 (새로운 설정 항목 추가 시 호환성 보장)
        $mergedConfig = array_merge_recursive($this->defaultConfig, $config);
        
        // URL 동적 설정
        if (empty($mergedConfig['url']['base_url'])) {
            $baseUrl = $this->detectBaseUrl();
            $mergedConfig['url']['base_url'] = $baseUrl;
            $mergedConfig['url']['theme_assets_url'] = $baseUrl . '/board_templates/assets';
            $mergedConfig['url']['admin_url'] = $baseUrl . '/admin';
        }
        
        return $mergedConfig;
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
                'host' => $this->sanitizeInput($data['db_host'] ?? 'localhost'),
                'port' => (int)($data['db_port'] ?? 3306),
                'user' => $this->sanitizeInput($data['db_user'] ?? ''),
                'password' => $data['db_password'] ?? '', // 비밀번호는 sanitize 하지 않음
                'database' => $this->sanitizeInput($data['db_name'] ?? ''),
                'charset' => $this->sanitizeInput($data['db_charset'] ?? 'utf8mb4'),
                'driver' => 'mysql'
            ];
            
            // 메타 정보 업데이트
            $currentConfig['meta']['last_updated'] = date('Y-m-d H:i:s');
            $currentConfig['meta']['updated_by'] = $_SESSION['admin_user'] ?? 'admin';
            
            // 백업 생성
            $this->createBackup();
            
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
                $prefix = $this->sanitizeInput($data['table_prefix']);
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
                    $value = $this->sanitizeInput($data[$key]);
                    if (empty($value)) {
                        throw new Exception("{$tableName} 테이블명은 필수입니다.");
                    }
                    $currentConfig['tables'][$tableName] = $value;
                }
            }
            
            // 메타 정보 업데이트
            $currentConfig['meta']['last_updated'] = date('Y-m-d H:i:s');
            $currentConfig['meta']['updated_by'] = $_SESSION['admin_user'] ?? 'admin';
            
            // 백업 생성
            $this->createBackup();
            
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
     * 테이블 존재 확인
     */
    public function validateTablesExist(?array $config = null): array
    {
        try {
            $config = $config ?? $this->loadCurrentSettings();
            
            // 데이터베이스 연결
            $pdo = $this->createPdoConnection($config['database']);
            
            $results = [];
            $allTablesExist = true;
            
            foreach ($config['tables'] as $key => $tableName) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$tableName]);
                
                $exists = $stmt->rowCount() > 0;
                $results[$key] = [
                    'table_name' => $tableName,
                    'exists' => $exists,
                    'status' => $exists ? 'OK' : 'NOT_FOUND'
                ];
                
                if (!$exists) {
                    $allTablesExist = false;
                }
            }
            
            return [
                'success' => true,
                'all_exist' => $allTablesExist,
                'tables' => $results,
                'message' => $allTablesExist ? '모든 테이블이 존재합니다.' : '일부 테이블이 존재하지 않습니다.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '테이블 확인 중 오류가 발생했습니다: ' . $e->getMessage()
            ];
        }
    }

    /**
     * PDO 연결 생성
     */
    private function createPdoConnection(array $dbConfig): PDO
    {
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset']
        );
        
        return new PDO(
            $dsn,
            $dbConfig['user'],
            $dbConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    /**
     * 설정 백업 생성
     */
    public function createBackup(): array
    {
        try {
            if (!file_exists($this->configFile)) {
                throw new Exception('백업할 설정 파일이 존재하지 않습니다.');
            }

            $backupFileName = 'database_settings_' . date('Y-m-d_H-i-s') . '.json';
            $backupPath = $this->backupDir . '/' . $backupFileName;
            
            $result = copy($this->configFile, $backupPath);
            
            if (!$result) {
                throw new Exception('백업 파일 생성에 실패했습니다.');
            }
            
            // 오래된 백업 파일 정리 (30개 초과시)
            $this->cleanupOldBackups(30);
            
            return [
                'success' => true,
                'message' => '백업이 생성되었습니다.',
                'backup_file' => $backupFileName
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 오래된 백업 파일 정리
     */
    private function cleanupOldBackups(int $maxBackups): void
    {
        $files = glob($this->backupDir . '/database_settings_*.json');
        
        if (count($files) <= $maxBackups) {
            return;
        }
        
        // 파일을 수정 시간 기준으로 정렬 (오래된 것부터)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // 초과분 삭제
        $filesToDelete = array_slice($files, 0, count($files) - $maxBackups);
        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }

    /**
     * 기본 설정으로 초기화
     */
    public function resetToDefaults(): array
    {
        try {
            // 현재 설정 백업
            if (file_exists($this->configFile)) {
                $this->createBackup();
            }
            
            // 기본 설정으로 초기화
            $result = file_put_contents(
                $this->configFile, 
                json_encode($this->defaultConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            if ($result === false) {
                throw new Exception('기본 설정 파일 생성에 실패했습니다.');
            }

            return ['success' => true, 'message' => '기본 설정으로 초기화되었습니다.'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 설정 내보내기
     */
    public function exportSettings(): array
    {
        try {
            $config = $this->loadCurrentSettings();
            
            // 민감한 정보 제거 (비밀번호 등)
            $safeConfig = $config;
            if (isset($safeConfig['database']['password'])) {
                $safeConfig['database']['password'] = ''; // 비밀번호는 내보내지 않음
            }
            
            $exportData = [
                'export_date' => date('Y-m-d H:i:s'),
                'export_version' => '1.0',
                'config' => $safeConfig
            ];
            
            return [
                'success' => true,
                'data' => json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'filename' => 'board_templates_config_' . date('Y-m-d_H-i-s') . '.json'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 설정 가져오기
     */
    public function importSettings(string $jsonData): array
    {
        try {
            $importData = json_decode($jsonData, true);
            
            if ($importData === null) {
                throw new Exception('유효하지 않은 JSON 형식입니다.');
            }
            
            if (!isset($importData['config'])) {
                throw new Exception('설정 데이터가 없습니다.');
            }
            
            // 현재 설정 백업
            $this->createBackup();
            
            // 가져온 설정과 기본 설정 병합
            $newConfig = array_merge_recursive($this->defaultConfig, $importData['config']);
            
            // 메타 정보 업데이트
            $newConfig['meta']['last_updated'] = date('Y-m-d H:i:s');
            $newConfig['meta']['updated_by'] = $_SESSION['admin_user'] ?? 'admin';
            $newConfig['meta']['imported_from'] = $importData['export_date'] ?? 'unknown';
            
            // 설정 파일 저장
            $result = file_put_contents(
                $this->configFile, 
                json_encode($newConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            if ($result === false) {
                throw new Exception('설정 파일 저장에 실패했습니다.');
            }

            return ['success' => true, 'message' => '설정을 가져왔습니다.'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 시스템 상태 점검
     */
    public function getSystemStatus(): array
    {
        $status = [
            'overall' => 'ok',
            'checks' => []
        ];
        
        try {
            // 설정 파일 존재 확인
            $status['checks']['config_file'] = [
                'name' => '설정 파일',
                'status' => file_exists($this->configFile) ? 'ok' : 'warning',
                'message' => file_exists($this->configFile) ? '설정 파일이 존재합니다' : '설정 파일이 없습니다'
            ];
            
            // 데이터베이스 연결 테스트
            $config = $this->loadCurrentSettings();
            $dbTest = $this->testDatabaseConnection($config['database']);
            
            $status['checks']['database'] = [
                'name' => '데이터베이스 연결',
                'status' => $dbTest['success'] ? 'ok' : 'error',
                'message' => $dbTest['message'],
                'details' => $dbTest['details'] ?? null
            ];
            
            // 테이블 존재 확인
            if ($dbTest['success']) {
                $tableCheck = $this->validateTablesExist($config);
                $status['checks']['tables'] = [
                    'name' => '테이블 존재 확인',
                    'status' => $tableCheck['all_exist'] ? 'ok' : 'warning',
                    'message' => $tableCheck['message'],
                    'details' => $tableCheck['tables'] ?? null
                ];
            }
            
            // 업로드 디렉토리 확인
            $uploadPath = $config['file']['upload_base_path'];
            $uploadWritable = is_dir($uploadPath) && is_writable($uploadPath);
            
            $status['checks']['upload_directory'] = [
                'name' => '업로드 디렉토리',
                'status' => $uploadWritable ? 'ok' : 'warning',
                'message' => $uploadWritable ? '업로드 디렉토리가 쓰기 가능합니다' : '업로드 디렉토리가 존재하지 않거나 쓰기 권한이 없습니다',
                'path' => $uploadPath
            ];
            
            // 백업 디렉토리 확인
            $backupWritable = is_dir($this->backupDir) && is_writable($this->backupDir);
            
            $status['checks']['backup_directory'] = [
                'name' => '백업 디렉토리',
                'status' => $backupWritable ? 'ok' : 'warning',
                'message' => $backupWritable ? '백업 디렉토리가 쓰기 가능합니다' : '백업 디렉토리가 존재하지 않거나 쓰기 권한이 없습니다',
                'path' => $this->backupDir
            ];
            
            // 전체 상태 결정
            foreach ($status['checks'] as $check) {
                if ($check['status'] === 'error') {
                    $status['overall'] = 'error';
                    break;
                } elseif ($check['status'] === 'warning' && $status['overall'] === 'ok') {
                    $status['overall'] = 'warning';
                }
            }
            
        } catch (Exception $e) {
            $status['overall'] = 'error';
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }

    /**
     * 입력 데이터 정화
     */
    private function sanitizeInput(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * 백업 복원
     * 
     * @param array $backupData 백업 데이터
     * @return array 복원 결과
     */
    public function restoreBackup(array $backupData): array
    {
        try {
            // 현재 설정 백업
            $currentBackup = $this->createBackup();
            
            if (!$currentBackup['success']) {
                throw new Exception('현재 설정 백업 실패');
            }
            
            // 백업 데이터 검증
            if (!isset($backupData['config']) || !is_array($backupData['config'])) {
                throw new Exception('유효하지 않은 백업 데이터입니다.');
            }
            
            // 백업 데이터를 JSON 문자열로 변환
            $jsonData = json_encode([
                'export_date' => $backupData['created_at'] ?? date('Y-m-d H:i:s'),
                'config' => $backupData['config']
            ]);
            
            // 설정 복원
            $result = $this->importSettings($jsonData);
            
            if ($result['success']) {
                $this->logAction('backup_restored', "백업 복원: " . ($backupData['backup_id'] ?? 'unknown'));
                
                return [
                    'success' => true,
                    'message' => '백업이 성공적으로 복원되었습니다.',
                    'data' => [
                        'restored_backup' => $backupData['backup_id'] ?? null,
                        'safety_backup' => $currentBackup['backup_file']
                    ]
                ];
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $this->logAction('restore_failed', "백업 복원 실패: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 백업 목록 조회
     * 
     * @return array 백업 목록
     */
    public function listBackups(): array
    {
        try {
            $backups = [];
            $files = glob($this->backupDir . '/database_settings_*.json');
            
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data) {
                    $backups[] = [
                        'backup_id' => basename($file, '.json'),
                        'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                        'file_size' => filesize($file),
                        'file_path' => $file
                    ];
                }
            }
            
            // 생성일 기준 내림차순 정렬
            usort($backups, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            return [
                'success' => true,
                'message' => '백업 목록을 조회했습니다.',
                'data' => $backups
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 백업 삭제
     * 
     * @param string $backupId 백업 ID
     * @return array 삭제 결과
     */
    public function deleteBackup(string $backupId): array
    {
        try {
            $backupFile = $this->backupDir . '/' . $backupId . '.json';
            
            if (!file_exists($backupFile)) {
                throw new Exception('백업 파일을 찾을 수 없습니다.');
            }
            
            if (unlink($backupFile)) {
                $this->logAction('backup_deleted', "백업 삭제: {$backupId}");
                
                return [
                    'success' => true,
                    'message' => '백업이 성공적으로 삭제되었습니다.'
                ];
            } else {
                throw new Exception('백업 파일을 삭제할 수 없습니다.');
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 로그 조회
     * 
     * @param string $logType 로그 타입
     * @param int $limit 조회 개수
     * @param string $level 로그 레벨
     * @param string|null $startDate 시작 날짜
     * @param string|null $endDate 종료 날짜
     * @return array 로그 데이터
     */
    public function getLogs(string $logType = 'database', int $limit = 100, string $level = 'all', ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $logFile = $this->getLogFilePath($logType);
            
            if (!file_exists($logFile)) {
                return [
                    'success' => true,
                    'message' => '로그 파일이 없습니다.',
                    'data' => [],
                    'total_count' => 0
                ];
            }
            
            $logs = [];
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = array_reverse($lines); // 최신 로그가 위로
            
            foreach ($lines as $line) {
                $logEntry = json_decode($line, true);
                if (!$logEntry) continue;
                
                // 날짜 필터링
                if ($startDate && strtotime($logEntry['timestamp']) < strtotime($startDate)) continue;
                if ($endDate && strtotime($logEntry['timestamp']) > strtotime($endDate . ' 23:59:59')) continue;
                
                // 레벨 필터링
                if ($level !== 'all' && $logEntry['level'] !== $level) continue;
                
                $logs[] = $logEntry;
                
                if (count($logs) >= $limit) break;
            }
            
            return [
                'success' => true,
                'message' => '로그를 조회했습니다.',
                'data' => $logs,
                'total_count' => count($lines)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 로그 삭제
     * 
     * @param string $logType 로그 타입
     * @return array 삭제 결과
     */
    public function clearLogs(string $logType = 'database'): array
    {
        try {
            $logFile = $this->getLogFilePath($logType);
            
            if (file_exists($logFile)) {
                if (unlink($logFile)) {
                    $this->logAction('logs_cleared', "로그 삭제: {$logType}");
                    
                    return [
                        'success' => true,
                        'message' => '로그가 성공적으로 삭제되었습니다.'
                    ];
                } else {
                    throw new Exception('로그 파일을 삭제할 수 없습니다.');
                }
            } else {
                return [
                    'success' => true,
                    'message' => '삭제할 로그 파일이 없습니다.'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 액션 로그 기록
     * 
     * @param string $action 액션 타입
     * @param string $message 로그 메시지
     * @param string $level 로그 레벨 (info, warning, error)
     */
    private function logAction(string $action, string $message, string $level = 'info'): void
    {
        try {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => $action,
                'message' => $message,
                'level' => $level,
                'user' => $_SESSION['admin_user'] ?? 'system',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            $logFile = $this->getLogFilePath('database');
            $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
            
            file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            // 로그 기록 실패시 무시 (로그 기록 때문에 메인 기능이 실패하면 안 됨)
        }
    }

    /**
     * 로그 파일 경로 반환
     * 
     * @param string $logType 로그 타입
     * @return string 로그 파일 경로
     */
    private function getLogFilePath(string $logType): string
    {
        $logDir = dirname($this->configFile) . '/logs';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        return $logDir . '/' . $logType . '.log';
    }

    /**
     * 컬럼 매핑 설정 저장
     * 
     * @param array $data POST 데이터
     * @return array 저장 결과
     */
    public function saveColumnSettings(array $data): array
    {
        try {
            $config = $this->loadCurrentSettings();
            
            // 컬럼 설정 업데이트 - 게시글 테이블
            if (!isset($config['columns']['posts'])) {
                $config['columns']['posts'] = [];
            }
            $config['columns']['posts']['id'] = $this->sanitizeInput($data['posts_id'] ?? 'post_id');
            $config['columns']['posts']['title'] = $this->sanitizeInput($data['posts_title'] ?? 'title');
            $config['columns']['posts']['content'] = $this->sanitizeInput($data['posts_content'] ?? 'content');
            $config['columns']['posts']['author'] = $this->sanitizeInput($data['posts_author'] ?? 'author_name');
            $config['columns']['posts']['created_at'] = $this->sanitizeInput($data['posts_created_at'] ?? 'created_at');
            $config['columns']['posts']['category_id'] = $this->sanitizeInput($data['posts_category_id'] ?? 'category_id');
            
            // 컬럼 설정 업데이트 - 카테고리 테이블
            if (!isset($config['columns']['categories'])) {
                $config['columns']['categories'] = [];
            }
            $config['columns']['categories']['name'] = $this->sanitizeInput($data['categories_name'] ?? 'category_name');
            $config['columns']['categories']['type'] = $this->sanitizeInput($data['categories_type'] ?? 'category_type');
            
            // 컬럼 설정 업데이트 - 첨부파일 테이블
            if (!isset($config['columns']['attachments'])) {
                $config['columns']['attachments'] = [];
            }
            $config['columns']['attachments']['filename'] = $this->sanitizeInput($data['attachments_filename'] ?? 'original_filename');
            $config['columns']['attachments']['filesize'] = $this->sanitizeInput($data['attachments_filesize'] ?? 'file_size');
            
            // 메타 정보 업데이트
            $config['meta']['last_updated'] = date('Y-m-d H:i:s');
            $config['meta']['updated_by'] = $_SESSION['admin_user'] ?? 'admin';
            
            // 백업 생성
            $this->createBackup();
            
            // 설정 파일 저장
            $result = file_put_contents(
                $this->configFile,
                json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            
            if ($result === false) {
                throw new Exception('설정 파일 저장에 실패했습니다.');
            }
            
            $this->logAction('columns_saved', '컬럼 매핑 설정 저장');
            
            return [
                'success' => true,
                'message' => '컬럼 매핑 설정이 저장되었습니다.'
            ];
            
        } catch (Exception $e) {
            $this->logAction('columns_save_failed', '컬럼 매핑 설정 저장 실패: ' . $e->getMessage(), 'error');
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}