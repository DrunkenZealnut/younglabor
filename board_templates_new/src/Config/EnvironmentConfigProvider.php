<?php

namespace BoardTemplates\Config;

use BoardTemplates\Interfaces\BoardConfigProviderInterface;

/**
 * 환경변수 기반 설정 제공자
 * 
 * .env 파일이나 시스템 환경변수에서 설정을 로드하여
 * 12-Factor App 원칙에 따라 설정을 관리합니다.
 * 프로덕션 환경에서 안전하게 설정을 관리할 수 있습니다.
 */
class EnvironmentConfigProvider implements BoardConfigProviderInterface
{
    private array $config = [];
    private array $envVars = [];
    private string $envPrefix;

    /**
     * 생성자
     * 
     * @param string $envPrefix 환경변수 접두사 (기본: BT_)
     * @param string|null $envFile .env 파일 경로 (null이면 자동 감지)
     */
    public function __construct(string $envPrefix = 'BT_', ?string $envFile = null)
    {
        $this->envPrefix = $envPrefix;
        $this->loadEnvironmentVariables($envFile);
        $this->buildConfigFromEnvironment();
    }

    /**
     * 환경변수를 로드합니다
     */
    private function loadEnvironmentVariables(?string $envFile): void
    {
        // 시스템 환경변수 로드
        $this->envVars = $_ENV + getenv();

        // .env 파일 로드
        if ($envFile === null) {
            $envFile = $this->findEnvFile();
        }

        if ($envFile && file_exists($envFile)) {
            $this->loadDotEnvFile($envFile);
        }
    }

    /**
     * .env 파일을 찾습니다
     */
    private function findEnvFile(): ?string
    {
        $possiblePaths = [
            dirname(__DIR__, 2) . '/.env',        // board_templates/.env
            dirname(__DIR__, 3) . '/.env',        // project/.env
            dirname(__DIR__, 4) . '/.env',        // parent/.env
            getcwd() . '/.env'                    // current working directory
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * .env 파일을 파싱합니다
     */
    private function loadDotEnvFile(string $envFile): void
    {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 주석 무시
            if (strpos($line, '#') === 0) {
                continue;
            }

            // KEY=VALUE 형식 파싱
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // 따옴표 제거
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }

                $this->envVars[$key] = $value;
                
                // 시스템 환경변수로도 설정 (기존 값이 없을 때만)
                if (!getenv($key)) {
                    putenv("{$key}={$value}");
                }
            }
        }
    }

    /**
     * 환경변수로부터 설정을 구성합니다
     */
    private function buildConfigFromEnvironment(): void
    {
        $this->config = [
            'database' => [
                'host' => $this->getEnv('DB_HOST', 'localhost'),
                'user' => $this->getEnv('DB_USER', 'root'),
                'password' => $this->getEnv('DB_PASSWORD', ''),
                'database' => $this->getEnv('DB_DATABASE', 'board_templates'),
                'charset' => $this->getEnv('DB_CHARSET', 'utf8mb4'),
                'driver' => $this->getEnv('DB_DRIVER', 'pdo_mysql'),
                'port' => (int)$this->getEnv('DB_PORT', '3306'),
                'options' => [
                    'connect_timeout' => (int)$this->getEnv('DB_CONNECT_TIMEOUT', '10'),
                    'init_command' => $this->getEnv('DB_INIT_COMMAND', 'SET sql_mode="STRICT_TRANS_TABLES"')
                ]
            ],
            'file' => [
                'upload_base_path' => $this->getEnv('UPLOAD_PATH', $this->getDefaultUploadPath()),
                'upload_base_url' => $this->getEnv('UPLOAD_URL', '/uploads'),
                'max_file_size' => $this->parseFileSize($this->getEnv('MAX_FILE_SIZE', '5M')),
                'allowed_extensions' => $this->parseAllowedExtensions(
                    $this->getEnv('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,hwp')
                ),
                'download_permission' => $this->getBool('DOWNLOAD_PERMISSION_CHECK', true),
                'upload_subdirs' => $this->getBool('UPLOAD_CREATE_SUBDIRS', true),
                'image_max_width' => (int)$this->getEnv('IMAGE_MAX_WIDTH', '1920'),
                'image_max_height' => (int)$this->getEnv('IMAGE_MAX_HEIGHT', '1080'),
                'image_quality' => (int)$this->getEnv('IMAGE_QUALITY', '85')
            ],
            'auth' => [
                'session_name' => $this->getEnv('SESSION_NAME', 'BT_SESSION'),
                'csrf_token_name' => $this->getEnv('CSRF_TOKEN_NAME', 'csrf_token'),
                'login_required' => $this->getBool('LOGIN_REQUIRED', true),
                'admin_required' => $this->getBool('ADMIN_REQUIRED', false),
                'user_id_session_key' => $this->getEnv('USER_ID_SESSION_KEY', 'user_id'),
                'user_level_session_key' => $this->getEnv('USER_LEVEL_SESSION_KEY', 'user_level'),
                'session_lifetime' => (int)$this->getEnv('SESSION_LIFETIME', '7200'), // 2시간
                'password_min_length' => (int)$this->getEnv('PASSWORD_MIN_LENGTH', '8'),
                'max_login_attempts' => (int)$this->getEnv('MAX_LOGIN_ATTEMPTS', '5'),
                'lockout_duration' => (int)$this->getEnv('LOCKOUT_DURATION', '900') // 15분
            ],
            'board' => [
                'table_prefix' => $this->getEnv('TABLE_PREFIX', 'bt_'),
                'default_view_type' => $this->getEnv('DEFAULT_VIEW_TYPE', 'table'),
                'posts_per_page' => (int)$this->getEnv('POSTS_PER_PAGE', '15'),
                'enable_comments' => $this->getBool('ENABLE_COMMENTS', true),
                'enable_attachments' => $this->getBool('ENABLE_ATTACHMENTS', true),
                'enable_captcha' => $this->getBool('ENABLE_CAPTCHA', true),
                'timezone' => $this->getEnv('TIMEZONE', 'UTC'),
                'max_comment_depth' => (int)$this->getEnv('MAX_COMMENT_DEPTH', '5'),
                'auto_approve_comments' => $this->getBool('AUTO_APPROVE_COMMENTS', false),
                'enable_search' => $this->getBool('ENABLE_SEARCH', true),
                'search_min_length' => (int)$this->getEnv('SEARCH_MIN_LENGTH', '2')
            ],
            'url' => [
                'base_url' => $this->getEnv('BASE_URL', $this->detectBaseUrl()),
                'board_base_path' => $this->getEnv('BOARD_BASE_PATH', '/board_templates'),
                'theme_assets_url' => $this->getEnv('THEME_ASSETS_URL', ''),
                'admin_url' => $this->getEnv('ADMIN_URL', '/admin'),
                'cdn_url' => $this->getEnv('CDN_URL', ''),
                'force_https' => $this->getBool('FORCE_HTTPS', false)
            ],
            'cache' => [
                'enabled' => $this->getBool('CACHE_ENABLED', false),
                'driver' => $this->getEnv('CACHE_DRIVER', 'file'),
                'ttl' => (int)$this->getEnv('CACHE_TTL', '3600'),
                'prefix' => $this->getEnv('CACHE_PREFIX', 'bt_'),
                'path' => $this->getEnv('CACHE_PATH', sys_get_temp_dir() . '/board_templates_cache')
            ],
            'logging' => [
                'enabled' => $this->getBool('LOG_ENABLED', true),
                'level' => $this->getEnv('LOG_LEVEL', 'warning'), // debug, info, warning, error
                'file' => $this->getEnv('LOG_FILE', dirname(__DIR__, 2) . '/logs/board_templates.log'),
                'max_files' => (int)$this->getEnv('LOG_MAX_FILES', '10'),
                'max_size' => $this->parseFileSize($this->getEnv('LOG_MAX_SIZE', '10M'))
            ]
        ];

        // URL 후처리
        if (empty($this->config['url']['theme_assets_url'])) {
            $this->config['url']['theme_assets_url'] = 
                $this->config['url']['base_url'] . $this->config['url']['board_base_path'] . '/assets';
        }
    }

    /**
     * 환경변수 값을 가져옵니다
     */
    private function getEnv(string $key, string $default = ''): string
    {
        // 접두사가 붙은 키를 먼저 확인
        $prefixedKey = $this->envPrefix . $key;
        
        if (isset($this->envVars[$prefixedKey])) {
            return (string)$this->envVars[$prefixedKey];
        }

        // 접두사 없는 키 확인
        if (isset($this->envVars[$key])) {
            return (string)$this->envVars[$key];
        }

        return $default;
    }

    /**
     * 불린 환경변수를 파싱합니다
     */
    private function getBool(string $key, bool $default = false): bool
    {
        $value = strtolower($this->getEnv($key, $default ? 'true' : 'false'));
        
        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }

    /**
     * 파일 크기 문자열을 바이트로 변환합니다
     */
    private function parseFileSize(string $size): int
    {
        $size = trim($size);
        $unit = strtolower(substr($size, -1));
        $value = (int)$size;

        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }

    /**
     * 허용된 확장자 문자열을 배열로 변환합니다
     */
    private function parseAllowedExtensions(string $extensions): array
    {
        return array_map('trim', explode(',', strtolower($extensions)));
    }

    /**
     * 기본 업로드 경로를 반환합니다
     */
    private function getDefaultUploadPath(): string
    {
        return dirname(__DIR__, 2) . '/uploads';
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

    // Interface 메서드들 구현
    public function getDatabaseConfig(): array
    {
        return $this->config['database'];
    }

    public function getFileConfig(): array
    {
        return $this->config['file'];
    }

    public function getAuthConfig(): array
    {
        return $this->config['auth'];
    }

    public function getBoardConfig(): array
    {
        return $this->config['board'];
    }

    public function getUrlConfig(): array
    {
        return $this->config['url'];
    }

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

    public function getAllConfig(): array
    {
        return $this->config;
    }

    public function validateConfig(): array
    {
        $errors = [];

        // 데이터베이스 설정 검증
        $dbConfig = $this->getDatabaseConfig();
        if (empty($dbConfig['host'])) {
            $errors[] = 'Database host is required (DB_HOST)';
        }
        if (empty($dbConfig['database'])) {
            $errors[] = 'Database name is required (DB_DATABASE)';
        }

        // 파일 설정 검증
        $fileConfig = $this->getFileConfig();
        $uploadPath = $fileConfig['upload_base_path'];
        
        if (!is_dir($uploadPath)) {
            $errors[] = "Upload directory does not exist: {$uploadPath} (UPLOAD_PATH)";
        } elseif (!is_writable($uploadPath)) {
            $errors[] = "Upload directory is not writable: {$uploadPath} (UPLOAD_PATH)";
        }

        // 보안 관련 검증
        $authConfig = $this->getAuthConfig();
        if ($authConfig['password_min_length'] < 6) {
            $errors[] = 'Password minimum length should be at least 6 characters (PASSWORD_MIN_LENGTH)';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * 현재 환경변수를 .env 파일 형태로 내보냅니다
     */
    public function exportEnvTemplate(string $filename = null): string
    {
        if ($filename === null) {
            $filename = dirname(__DIR__, 2) . '/.env.example';
        }

        $template = $this->generateEnvTemplate();
        file_put_contents($filename, $template);

        return $filename;
    }

    /**
     * .env 템플릿을 생성합니다
     */
    private function generateEnvTemplate(): string
    {
        return <<<ENV
# Board Templates Environment Configuration

# Database Configuration
{$this->envPrefix}DB_HOST=localhost
{$this->envPrefix}DB_USER=root
{$this->envPrefix}DB_PASSWORD=
{$this->envPrefix}DB_DATABASE=board_templates
{$this->envPrefix}DB_CHARSET=utf8mb4
{$this->envPrefix}DB_DRIVER=pdo_mysql
{$this->envPrefix}DB_PORT=3306

# File Upload Configuration
{$this->envPrefix}UPLOAD_PATH=/path/to/uploads
{$this->envPrefix}UPLOAD_URL=/uploads
{$this->envPrefix}MAX_FILE_SIZE=5M
{$this->envPrefix}ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx
{$this->envPrefix}DOWNLOAD_PERMISSION_CHECK=true

# Authentication Configuration
{$this->envPrefix}SESSION_NAME=BT_SESSION
{$this->envPrefix}LOGIN_REQUIRED=true
{$this->envPrefix}ADMIN_REQUIRED=false
{$this->envPrefix}PASSWORD_MIN_LENGTH=8
{$this->envPrefix}MAX_LOGIN_ATTEMPTS=5

# Board Configuration
{$this->envPrefix}TABLE_PREFIX=bt_
{$this->envPrefix}DEFAULT_VIEW_TYPE=table
{$this->envPrefix}POSTS_PER_PAGE=15
{$this->envPrefix}ENABLE_COMMENTS=true
{$this->envPrefix}ENABLE_ATTACHMENTS=true
{$this->envPrefix}ENABLE_CAPTCHA=true
{$this->envPrefix}TIMEZONE=UTC

# URL Configuration
{$this->envPrefix}BASE_URL=http://localhost
{$this->envPrefix}BOARD_BASE_PATH=/board_templates
{$this->envPrefix}ADMIN_URL=/admin
{$this->envPrefix}FORCE_HTTPS=false

# Cache Configuration
{$this->envPrefix}CACHE_ENABLED=false
{$this->envPrefix}CACHE_DRIVER=file
{$this->envPrefix}CACHE_TTL=3600

# Logging Configuration
{$this->envPrefix}LOG_ENABLED=true
{$this->envPrefix}LOG_LEVEL=warning
{$this->envPrefix}LOG_MAX_FILES=10
{$this->envPrefix}LOG_MAX_SIZE=10M
ENV;
    }
}