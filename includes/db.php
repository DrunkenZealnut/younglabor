<?php
/**
 * Unified Database Connection
 * 통합 데이터베이스 연결 (Modern config 기반)
 */

// 에러 리포팅 설정 (개발 환경에서만)
$isLocalHost = (isset($_SERVER['HTTP_HOST']) && 
                (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                 strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));

if ($isLocalHost) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// EnvLoader 사용하여 환경변수 로드
require_once(__DIR__ . '/EnvLoader.php');
EnvLoader::load();

$config_path = __DIR__ . '/../config/database.php';
if (file_exists($config_path)) {
    $db_config = require $config_path;
    $mysql_config = $db_config['connections']['mysql'];
    
    // PDO 연결 생성
    try {
        $dsn = "mysql:host={$mysql_config['host']};port={$mysql_config['port']};dbname={$mysql_config['database']};charset={$mysql_config['charset']}";
        // 빈 비밀번호를 null로 변환
        $password = empty($mysql_config['password']) ? null : $mysql_config['password'];
        $pdo = new PDO($dsn, $mysql_config['username'], $password, $mysql_config['options']);
    } catch (PDOException $e) {
        if ($isLocalHost) {
            die('Database connection failed: ' . $e->getMessage());
        } else {
            die('Database connection failed');
        }
    }
} else {
    // Fallback to environment variables if config not found
    $db_host = env('DB_HOST', 'localhost');
    $db_user = env('DB_USERNAME', 'root');
    $db_pass = env('DB_PASSWORD', '');
    $db_name = env('DB_DATABASE', env('PROJECT_SLUG', 'hopec'));
    $db_charset = env('DB_CHARSET', 'utf8mb4');

    // PDO 연결 생성
    try {
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$db_charset} COLLATE utf8mb4_unicode_ci"
        ]);
    
        // 문자셋 설정
        $pdo->exec("SET CHARACTER SET {$db_charset}");
        $pdo->exec("SET COLLATION_CONNECTION='utf8mb4_unicode_ci'");
        
    } catch (PDOException $e) {
        // 프로덕션 환경에서는 상세 오류를 숨김
        if ($isLocalHost) {
            die("DB 연결 실패: " . $e->getMessage());
        } else {
            die("데이터베이스 연결에 실패했습니다.");
        }
    }
}

/**
 * 프론트엔드용 사이트 설정 가져오기
 */
function getFrontendSiteSettings($pdo, $group = null) {
    $sql = "SELECT setting_key, setting_value FROM " . get_table_name('site_settings');
    $params = [];
    
    if ($group) {
        $sql .= " WHERE setting_group = ?";
        $params[] = $group;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $settings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($settings_data as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $settings;
    } catch (PDOException $e) {
        // 테이블이 없거나 오류 시 기본값 반환
        return [
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6c757d',
            'success_color' => '#198754',
            'info_color' => '#0dcaf0',
            'warning_color' => '#ffc107',
            'danger_color' => '#dc3545',
            'light_color' => '#f8f9fa',
            'dark_color' => '#212529',
            'body_font' => "'Segoe UI', sans-serif",
            'heading_font' => "'Segoe UI', sans-serif",
            'font_size_base' => '1rem'
        ];
    }
}
?>