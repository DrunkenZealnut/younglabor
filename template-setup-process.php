<?php
/**
 * Template Setup Process
 * 템플릿 설정 처리 로직
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// POST 데이터 검증
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

// 필수 필드 검증
$required_fields = [
    'app_name', 'app_url', 'site_name', 'db_host', 'db_database', 
    'db_username', 'admin_username', 'admin_email', 'admin_password'
];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die("Required field missing: $field");
    }
}

// 비밀번호 확인
if ($_POST['admin_password'] !== $_POST['admin_password_confirm']) {
    die('Passwords do not match');
}

try {
    // 1. .env 파일 생성
    createEnvFile($_POST);
    
    // 2. 데이터베이스 연결 테스트 및 테이블 생성
    setupDatabase($_POST);
    
    // 3. admin 설정 파일 업데이트
    updateAdminConfig($_POST);
    
    // 4. theme 설정 파일 업데이트
    updateThemeConfig($_POST);
    
    // 5. 관리자 계정 생성
    createAdminUser($_POST);
    
    // 6. 초기 사이트 설정 삽입
    insertSiteSettings($_POST);
    
    // 성공 페이지로 리다이렉트
    header('Location: template-setup-success.php');
    exit;
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    $error_file = $e->getFile();
    $error_line = $e->getLine();
    
    // 에러 로그 작성
    error_log("Template Setup Error: $error_message in $error_file:$error_line");
    
    // 에러 페이지 표시
    showErrorPage($error_message);
}

/**
 * .env 파일 생성
 */
function createEnvFile($data) {
    $env_content = "# Application Configuration
APP_NAME=\"{$data['app_name']}\"
APP_ENV={$data['app_env']}
APP_DEBUG=" . ($data['app_env'] === 'local' ? 'true' : 'false') . "
APP_URL={$data['app_url']}

# Site Configuration
DEFAULT_SITE_NAME=\"{$data['site_name']}\"
DEFAULT_SITE_DESCRIPTION=\"{$data['site_description']}\"
DEFAULT_ADMIN_EMAIL={$data['admin_email']}

# Database Configuration
DB_CONNECTION=mysql
DB_HOST={$data['db_host']}
DB_PORT=" . (!empty($data['db_port']) ? $data['db_port'] : '3306') . "
DB_DATABASE={$data['db_database']}
DB_USERNAME={$data['db_username']}
DB_PASSWORD=\"{$data['db_password']}\"
DB_PREFIX=" . (!empty($data['db_prefix']) ? $data['db_prefix'] : '') . "
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Security Settings
SESSION_LIFETIME=7200
SESSION_TIMEOUT=1800
CSRF_TOKEN_LIFETIME=3600

# Upload Settings
UPLOAD_MAX_SIZE=10485760
UPLOAD_PATH=uploads/

# Theme Settings (will be updated based on selection)
THEME_PRIMARY_COLOR=#84cc16
THEME_SECONDARY_COLOR=#16a34a
THEME_SUCCESS_COLOR=#65a30d
THEME_INFO_COLOR=#3a7a4e
THEME_WARNING_COLOR=#a3e635
THEME_DANGER_COLOR=#dc2626
THEME_LIGHT_COLOR=#fafffe
THEME_DARK_COLOR=#1f3b2d

# Logging
LOG_LEVEL=info
LOG_PATH=logs/
";

    // 선택된 테마에 따른 색상 설정
    $theme_colors = getThemeColors($data['selected_theme']);
    foreach ($theme_colors as $key => $value) {
        $env_content = preg_replace("/THEME_{$key}=.*/", "THEME_{$key}=$value", $env_content);
    }
    
    $env_file = __DIR__ . '/.env';
    if (file_put_contents($env_file, $env_content) === false) {
        throw new Exception('Failed to create .env file');
    }
}

/**
 * 테마별 색상 설정
 */
function getThemeColors($theme) {
    $themes = [
        'natural-green' => [
            'PRIMARY_COLOR' => '#84cc16',
            'SECONDARY_COLOR' => '#16a34a',
            'SUCCESS_COLOR' => '#65a30d',
            'INFO_COLOR' => '#3a7a4e',
            'WARNING_COLOR' => '#a3e635',
            'DANGER_COLOR' => '#dc2626',
            'LIGHT_COLOR' => '#fafffe',
            'DARK_COLOR' => '#1f3b2d'
        ],
        'ocean-blue' => [
            'PRIMARY_COLOR' => '#0ea5e9',
            'SECONDARY_COLOR' => '#0284c7',
            'SUCCESS_COLOR' => '#0891b2',
            'INFO_COLOR' => '#0369a1',
            'WARNING_COLOR' => '#38bdf8',
            'DANGER_COLOR' => '#dc2626',
            'LIGHT_COLOR' => '#f0f9ff',
            'DARK_COLOR' => '#0c4a6e'
        ],
        'sunset-orange' => [
            'PRIMARY_COLOR' => '#f97316',
            'SECONDARY_COLOR' => '#ea580c',
            'SUCCESS_COLOR' => '#c2410c',
            'INFO_COLOR' => '#9a3412',
            'WARNING_COLOR' => '#fb923c',
            'DANGER_COLOR' => '#dc2626',
            'LIGHT_COLOR' => '#fff7ed',
            'DARK_COLOR' => '#7c2d12'
        ],
        'royal-purple' => [
            'PRIMARY_COLOR' => '#8b5cf6',
            'SECONDARY_COLOR' => '#7c3aed',
            'SUCCESS_COLOR' => '#6d28d9',
            'INFO_COLOR' => '#5b21b6',
            'WARNING_COLOR' => '#a78bfa',
            'DANGER_COLOR' => '#dc2626',
            'LIGHT_COLOR' => '#faf5ff',
            'DARK_COLOR' => '#4c1d95'
        ]
    ];
    
    return $themes[$theme] ?? $themes['natural-green'];
}

/**
 * 데이터베이스 설정 및 테이블 생성
 */
function setupDatabase($data) {
    $dsn = "mysql:host={$data['db_host']};port=" . (!empty($data['db_port']) ? $data['db_port'] : '3306') . ";dbname={$data['db_database']};charset=utf8mb4";
    
    try {
        $pdo = new PDO($dsn, $data['db_username'], $data['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        // 테이블 생성 SQL 실행
        $sql_file = __DIR__ . '/template-sql/database-schema.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            
            // 테이블 프리픽스 적용
            $prefix = !empty($data['db_prefix']) ? $data['db_prefix'] : '';
            $sql_content = str_replace('hopec_', $prefix, $sql_content);
            
            // SQL 문들을 분리하여 실행
            $statements = array_filter(array_map('trim', explode(';', $sql_content)));
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
        }
        
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
    
    return $pdo;
}

/**
 * admin 설정 파일 업데이트
 */
function updateAdminConfig($data) {
    // config.php는 이미 환경변수 기반이므로 업데이트 불필요
    
    // .env.example 파일을 admin 폴더에 복사
    $env_example_content = file_get_contents(__DIR__ . '/.env');
    $admin_env_example = __DIR__ . '/admin/.env.example';
    
    // 민감한 정보 제거
    $env_example_content = preg_replace('/DB_PASSWORD=".*"/', 'DB_PASSWORD=""', $env_example_content);
    $env_example_content = preg_replace('/DEFAULT_ADMIN_EMAIL=.*/', 'DEFAULT_ADMIN_EMAIL=admin@example.com', $env_example_content);
    
    file_put_contents($admin_env_example, $env_example_content);
}

/**
 * theme 설정 파일 업데이트
 */
function updateThemeConfig($data) {
    $theme_config_file = __DIR__ . '/theme/natural-green/config/theme.php';
    
    if (file_exists($theme_config_file)) {
        $theme_config_content = file_get_contents($theme_config_file);
        
        // 사이트 정보 업데이트
        if (!empty($data['theme_site_title'])) {
            $theme_config_content = preg_replace(
                "/'site_name' => '.*'/",
                "'site_name' => '{$data['theme_site_title']}'",
                $theme_config_content
            );
            $theme_config_content = preg_replace(
                "/'title' => '.*'/",
                "'title' => '{$data['theme_site_title']}'",
                $theme_config_content
            );
        }
        
        if (!empty($data['theme_site_content'])) {
            $theme_config_content = preg_replace(
                "/'content' => '.*'/",
                "'content' => '{$data['theme_site_content']}'",
                $theme_config_content
            );
        }
        
        // 색상 설정 업데이트
        $theme_colors = getThemeColors($data['selected_theme']);
        foreach ($theme_colors as $key => $value) {
            $config_key = strtolower($key);
            $theme_config_content = preg_replace(
                "/'$config_key' => '#[a-fA-F0-9]{6}'/",
                "'$config_key' => '$value'",
                $theme_config_content
            );
        }
        
        file_put_contents($theme_config_file, $theme_config_content);
    }
}

/**
 * 관리자 계정 생성
 */
function createAdminUser($data) {
    $pdo = setupDatabase($data);
    $prefix = !empty($data['db_prefix']) ? $data['db_prefix'] : '';
    
    $password_hash = password_hash($data['admin_password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO {$prefix}admin_users (username, email, password, name, role, created_at) 
            VALUES (?, ?, ?, ?, 'admin', NOW())
            ON DUPLICATE KEY UPDATE 
            email = VALUES(email), 
            password = VALUES(password), 
            name = VALUES(name),
            updated_at = NOW()";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['admin_username'],
        $data['admin_email'],
        $password_hash,
        !empty($data['admin_name']) ? $data['admin_name'] : $data['admin_username']
    ]);
}

/**
 * 초기 사이트 설정 삽입
 */
function insertSiteSettings($data) {
    $pdo = setupDatabase($data);
    $prefix = !empty($data['db_prefix']) ? $data['db_prefix'] : '';
    
    $theme_colors = getThemeColors($data['selected_theme']);
    
    $settings = [
        ['site', 'site_name', $data['site_name']],
        ['site', 'site_description', $data['site_description']],
        ['site', 'admin_email', $data['admin_email']],
        ['theme', 'selected_theme', $data['selected_theme']],
        ['theme', 'primary_color', $theme_colors['PRIMARY_COLOR']],
        ['theme', 'secondary_color', $theme_colors['SECONDARY_COLOR']],
        ['theme', 'success_color', $theme_colors['SUCCESS_COLOR']],
        ['theme', 'info_color', $theme_colors['INFO_COLOR']],
        ['theme', 'warning_color', $theme_colors['WARNING_COLOR']],
        ['theme', 'danger_color', $theme_colors['DANGER_COLOR']],
        ['theme', 'light_color', $theme_colors['LIGHT_COLOR']],
        ['theme', 'dark_color', $theme_colors['DARK_COLOR']],
    ];
    
    $sql = "INSERT INTO {$prefix}site_settings (setting_group, setting_key, setting_value, created_at) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            updated_at = NOW()";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
}

/**
 * 에러 페이지 표시
 */
function showErrorPage($error_message) {
    ?>
    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>설정 오류</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-body text-center p-5">
                            <div class="text-danger mb-4">
                                <i class="bi bi-exclamation-triangle" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="text-danger mb-3">설정 중 오류가 발생했습니다</h2>
                            <div class="alert alert-danger text-start">
                                <strong>오류 메시지:</strong><br>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                            <div class="mt-4">
                                <a href="template-setup.php" class="btn btn-primary">
                                    <i class="bi bi-arrow-left"></i> 다시 시도
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>