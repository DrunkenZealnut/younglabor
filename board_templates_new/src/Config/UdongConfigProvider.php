<?php

namespace BoardTemplates\Config;

use BoardTemplates\Interfaces\BoardConfigProviderInterface;
use BoardTemplates\Config\BoardTableConfig;

/**
 * UDONG 프로젝트용 설정 제공자
 * 
 * 기존 UDONG 프로젝트의 설정을 BoardConfigProviderInterface로 감싸서
 * 하위 호환성을 유지하면서 새로운 추상화 레이어를 제공합니다.
 */
class UdongConfigProvider implements BoardConfigProviderInterface
{
    private array $config = [];
    private bool $initialized = false;
    private ?BoardTableConfig $tableConfig = null;

    public function __construct()
    {
        $this->initializeConfig();
    }

    /**
     * UDONG 프로젝트의 기존 설정을 로드합니다
     */
    private function initializeConfig(): void
    {
        if ($this->initialized) {
            return;
        }

        try {
            // 기존 UDONG 설정 파일들을 로드
            $this->loadExistingUdongConfig();
            $this->initialized = true;
        } catch (Exception $e) {
            // 설정 로드 실패 시 기본값 사용
            $this->setDefaultConfig();
            $this->initialized = true;
        }
    }

    /**
     * 기존 UDONG 설정 파일들을 로드합니다
     */
    private function loadExistingUdongConfig(): void
    {
        $boardTemplatesDir = dirname(__DIR__, 2);
        
        // 기존 config.php 파일이 있다면 상수들을 로드
        $configFile = $boardTemplatesDir . '/config.php';
        if (file_exists($configFile)) {
            require_once $configFile;
        }

        // 상위 디렉터리의 config 파일들도 시도
        $parentConfigDir = dirname($boardTemplatesDir) . '/config';
        
        // database.php 로드
        if (file_exists($parentConfigDir . '/database.php')) {
            require_once $parentConfigDir . '/database.php';
        }

        // helpers.php 로드
        if (file_exists($parentConfigDir . '/helpers.php')) {
            require_once $parentConfigDir . '/helpers.php';
        }

        $this->buildConfigFromConstants();
    }

    /**
     * 기존 상수들로부터 설정 배열을 구성합니다
     */
    private function buildConfigFromConstants(): void
    {
        // 데이터베이스 설정
        $this->config['database'] = [
            'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
            'user' => defined('DB_USER') ? DB_USER : 'root',
            'password' => defined('DB_PASS') ? DB_PASS : '',
            'database' => defined('DB_NAME') ? DB_NAME : 'woodong615',
            'charset' => 'utf8mb4',
            'driver' => 'mysql'
        ];

        // 파일 설정
        $this->config['file'] = [
            'upload_base_path' => defined('BOARD_TEMPLATES_FILE_BASE_PATH') 
                ? BOARD_TEMPLATES_FILE_BASE_PATH 
                : dirname(__DIR__, 3) . '/uploads',
            'upload_base_url' => defined('BOARD_TEMPLATES_FILE_BASE_URL') 
                ? BOARD_TEMPLATES_FILE_BASE_URL 
                : '/uploads',
            'max_file_size' => 5 * 1024 * 1024, // 5MB
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'hwp'],
            'download_permission' => defined('BOARD_TEMPLATES_DOWNLOAD_OPEN') 
                ? BOARD_TEMPLATES_DOWNLOAD_OPEN 
                : true
        ];

        // 인증 설정
        $this->config['auth'] = [
            'session_name' => 'PHPSESSID',
            'csrf_token_name' => 'csrf_token',
            'login_required' => true,
            'admin_required' => false,
            'user_id_session_key' => 'user_id',
            'user_level_session_key' => 'user_level'
        ];

        // 보드 설정
        $this->config['board'] = [
            'table_prefix' => 'atti_board',
            'default_view_type' => 'table',
            'posts_per_page' => 10,
            'enable_comments' => true,
            'enable_attachments' => true,
            'enable_captcha' => false,
            'timezone' => 'Asia/Seoul'
        ];

        // URL 설정
        $baseUrl = $this->getBaseUrl();
        $this->config['url'] = [
            'base_url' => $baseUrl,
            'board_base_path' => '/board_templates',
            'theme_assets_url' => $baseUrl . '/board_templates/assets',
            'admin_url' => $baseUrl . '/admin'
        ];
    }

    /**
     * 기존 UDONG의 get_base_url() 함수를 모방한 URL 감지
     */
    private function getBaseUrl(): string
    {
        // 환경 감지
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        if (strpos($host, 'localhost') !== false || 
            strpos($host, '127.0.0.1') !== false) {
            // 로컬 환경
            return 'http://localhost:8081';
        } else {
            // 프로덕션 환경
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return $protocol . '://' . $host;
        }
    }

    /**
     * 기본 설정을 설정합니다
     */
    private function setDefaultConfig(): void
    {
        $this->config = [
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'database' => 'woodong615',
                'charset' => 'utf8mb4',
                'driver' => 'mysql'
            ],
            'file' => [
                'upload_base_path' => dirname(__DIR__, 3) . '/uploads',
                'upload_base_url' => '/uploads',
                'max_file_size' => 5 * 1024 * 1024,
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
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
                'table_prefix' => 'atti_board',
                'default_view_type' => 'table',
                'posts_per_page' => 10,
                'enable_comments' => true,
                'enable_attachments' => true,
                'enable_captcha' => false,
                'timezone' => 'Asia/Seoul'
            ],
            'url' => [
                'base_url' => 'http://localhost:8012/udong',
                'board_base_path' => '/board_templates',
                'theme_assets_url' => 'http://localhost:8012/udong/board_templates/assets',
                'admin_url' => 'http://localhost:8012/udong/admin'
            ]
        ];
    }

    /**
     * 데이터베이스 설정을 반환합니다
     */
    public function getDatabaseConfig(): array
    {
        return $this->config['database'] ?? [];
    }

    /**
     * 파일 설정을 반환합니다
     */
    public function getFileConfig(): array
    {
        return $this->config['file'] ?? [];
    }

    /**
     * 인증 설정을 반환합니다
     */
    public function getAuthConfig(): array
    {
        return $this->config['auth'] ?? [];
    }

    /**
     * 보드 설정을 반환합니다
     */
    public function getBoardConfig(): array
    {
        return $this->config['board'] ?? [];
    }

    /**
     * URL 설정을 반환합니다
     */
    public function getUrlConfig(): array
    {
        return $this->config['url'] ?? [];
    }

    /**
     * 점 표기법을 지원하는 설정 조회
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * 모든 설정을 반환합니다
     */
    public function getAllConfig(): array
    {
        return $this->config;
    }

    /**
     * 설정의 유효성을 검증합니다
     */
    public function validateConfig(): array
    {
        $errors = [];

        // 데이터베이스 설정 검증
        $dbConfig = $this->getDatabaseConfig();
        if (empty($dbConfig['host'])) {
            $errors[] = 'Database host is required';
        }
        if (empty($dbConfig['database'])) {
            $errors[] = 'Database name is required';
        }

        // 파일 설정 검증
        $fileConfig = $this->getFileConfig();
        if (!is_dir($fileConfig['upload_base_path'])) {
            $errors[] = 'Upload directory does not exist: ' . $fileConfig['upload_base_path'];
        }
        if (!is_writable($fileConfig['upload_base_path'])) {
            $errors[] = 'Upload directory is not writable: ' . $fileConfig['upload_base_path'];
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * 테이블 설정 객체를 반환합니다
     */
    public function getTableConfig(): BoardTableConfig
    {
        if ($this->tableConfig === null) {
            $this->tableConfig = BoardTableConfig::createForUdong();
        }
        
        return $this->tableConfig;
    }
}