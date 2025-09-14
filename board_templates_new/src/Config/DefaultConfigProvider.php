<?php

namespace BoardTemplates\Config;

use BoardTemplates\Interfaces\BoardConfigProviderInterface;

/**
 * 기본 설정 제공자
 * 
 * 새로운 프로젝트에서 board_templates를 사용할 때
 * 최소한의 설정으로 시작할 수 있도록 도와주는 기본 설정 제공자입니다.
 */
class DefaultConfigProvider implements BoardConfigProviderInterface
{
    private array $config;
    private array $customConfig;

    /**
     * 생성자
     * 
     * @param array $customConfig 사용자 정의 설정
     */
    public function __construct(array $customConfig = [])
    {
        $this->customConfig = $customConfig;
        $this->initializeDefaultConfig();
        $this->mergeCustomConfig();
    }

    /**
     * 기본 설정을 초기화합니다
     */
    private function initializeDefaultConfig(): void
    {
        $this->config = [
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'database' => 'board_templates',
                'charset' => 'utf8mb4',
                'driver' => 'pdo_mysql'
            ],
            'file' => [
                'upload_base_path' => $this->getDefaultUploadPath(),
                'upload_base_url' => '/uploads',
                'max_file_size' => 5 * 1024 * 1024, // 5MB
                'allowed_extensions' => [
                    'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
                    'archives' => ['zip', 'rar', '7z']
                ],
                'download_permission' => false // 기본적으로 권한 체크 활성화
            ],
            'auth' => [
                'session_name' => 'BOARD_TEMPLATES_SESSION',
                'csrf_token_name' => 'csrf_token',
                'login_required' => true,
                'admin_required' => false,
                'user_id_session_key' => 'user_id',
                'user_level_session_key' => 'user_level'
            ],
            'board' => [
                'table_prefix' => 'bt_',
                'default_view_type' => 'table',
                'posts_per_page' => 15,
                'enable_comments' => true,
                'enable_attachments' => true,
                'enable_captcha' => true,
                'timezone' => 'UTC'
            ],
            'url' => [
                'base_url' => $this->detectBaseUrl(),
                'board_base_path' => '/board_templates',
                'theme_assets_url' => null, // 자동 감지
                'admin_url' => '/admin'
            ],
            'captcha' => [
                'enabled' => true,
                'width' => 200,
                'height' => 50,
                'font_size' => 18,
                'characters' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
                'length' => 5,
                'background_color' => [255, 255, 255],
                'text_color' => [0, 0, 0],
                'noise_level' => 3
            ],
            'pagination' => [
                'page_numbers_count' => 10,
                'show_first_last' => true,
                'show_prev_next' => true,
                'css_classes' => [
                    'container' => 'pagination',
                    'page' => 'page-item',
                    'active' => 'active',
                    'disabled' => 'disabled'
                ]
            ]
        ];
    }

    /**
     * 사용자 정의 설정을 기본 설정과 병합합니다
     */
    private function mergeCustomConfig(): void
    {
        $this->config = $this->arrayMergeRecursive($this->config, $this->customConfig);
        
        // URL 설정 후처리
        if ($this->config['url']['theme_assets_url'] === null) {
            $this->config['url']['theme_assets_url'] = 
                $this->config['url']['base_url'] . $this->config['url']['board_base_path'] . '/assets';
        }
    }

    /**
     * 재귀적 배열 병합
     */
    private function arrayMergeRecursive(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = $this->arrayMergeRecursive($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }

    /**
     * 기본 업로드 경로를 감지합니다
     */
    private function getDefaultUploadPath(): string
    {
        // 여러 가능한 경로를 시도
        $possiblePaths = [
            dirname(__DIR__, 3) . '/uploads',           // ../../../uploads
            dirname(__DIR__, 2) . '/uploads',           // ../../uploads  
            dirname(__DIR__, 4) . '/public/uploads',    // ../../../../public/uploads
            sys_get_temp_dir() . '/board_templates'     // 시스템 임시 디렉터리
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path) && is_writable($path)) {
                return $path;
            }
        }

        // 모두 실패하면 현재 디렉터리 기준
        $defaultPath = dirname(__DIR__, 2) . '/uploads';
        
        // 디렉터리가 없으면 생성 시도
        if (!is_dir($defaultPath)) {
            @mkdir($defaultPath, 0755, true);
        }

        return $defaultPath;
    }

    /**
     * 기본 URL을 자동 감지합니다
     */
    private function detectBaseUrl(): string
    {
        if (php_sapi_name() === 'cli') {
            return 'http://localhost';
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https' : 'http';
            
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        return $protocol . '://' . $host;
    }

    /**
     * 데이터베이스 설정을 반환합니다
     */
    public function getDatabaseConfig(): array
    {
        return $this->config['database'];
    }

    /**
     * 파일 설정을 반환합니다
     */
    public function getFileConfig(): array
    {
        return $this->config['file'];
    }

    /**
     * 인증 설정을 반환합니다
     */
    public function getAuthConfig(): array
    {
        return $this->config['auth'];
    }

    /**
     * 보드 설정을 반환합니다
     */
    public function getBoardConfig(): array
    {
        return $this->config['board'];
    }

    /**
     * URL 설정을 반환합니다
     */
    public function getUrlConfig(): array
    {
        return $this->config['url'];
    }

    /**
     * 점 표기법을 지원하는 설정 조회
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * 설정 값을 설정합니다 (런타임 변경용)
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $segment) {
            if (!is_array($config)) {
                $config = [];
            }
            if (!array_key_exists($segment, $config)) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
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
        if (empty($dbConfig['user'])) {
            $errors[] = 'Database user is required';
        }

        // 파일 설정 검증
        $fileConfig = $this->getFileConfig();
        $uploadPath = $fileConfig['upload_base_path'];
        
        if (!file_exists($uploadPath)) {
            $errors[] = "Upload directory does not exist: {$uploadPath}";
        } elseif (!is_dir($uploadPath)) {
            $errors[] = "Upload path is not a directory: {$uploadPath}";
        } elseif (!is_writable($uploadPath)) {
            $errors[] = "Upload directory is not writable: {$uploadPath}";
        }

        if ($fileConfig['max_file_size'] <= 0) {
            $errors[] = 'Max file size must be greater than 0';
        }

        // 보드 설정 검증
        $boardConfig = $this->getBoardConfig();
        if (empty($boardConfig['table_prefix'])) {
            $errors[] = 'Table prefix is required';
        }
        if ($boardConfig['posts_per_page'] <= 0) {
            $errors[] = 'Posts per page must be greater than 0';
        }

        // URL 설정 검증
        $urlConfig = $this->getUrlConfig();
        if (empty($urlConfig['base_url'])) {
            $errors[] = 'Base URL is required';
        } elseif (!filter_var($urlConfig['base_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Base URL must be a valid URL';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * 설정을 파일로 내보냅니다
     */
    public function exportConfig(string $filename = null): string
    {
        if ($filename === null) {
            $filename = dirname(__DIR__, 2) . '/config_export_' . date('Y-m-d_H-i-s') . '.json';
        }

        $configJson = json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $configJson);

        return $filename;
    }

    /**
     * 파일에서 설정을 가져옵니다
     */
    public static function importConfig(string $filename): self
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("Config file not found: {$filename}");
        }

        $configJson = file_get_contents($filename);
        $config = json_decode($configJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON in config file: " . json_last_error_msg());
        }

        return new self($config);
    }
}