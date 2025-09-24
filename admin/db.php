<?php
/**
 * 환경 변수 기반 데이터베이스 연결
 * .env 파일에서 설정 정보를 가져옵니다
 */

// 환경 변수 로더 포함 (이미 로드되지 않은 경우만)
if (!function_exists('env')) {
    require_once __DIR__ . '/env_loader.php';
}

try {
    // .env 파일에서 데이터베이스 설정 가져오기
    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $dbname = env('DB_DATABASE', 'hopec');
    $username = env('DB_USERNAME', 'root');
    $password = env('DB_PASSWORD', '');
    $socket = env('DB_SOCKET', '');
    $charset = env('DB_CHARSET', 'utf8mb4');
    $collation = env('DB_COLLATION', 'utf8mb4_unicode_ci');
    $tablePrefix = env('DB_PREFIX', '');
    
    // DSN 구성
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    
    // 포트가 기본값이 아닌 경우
    if ($port && $port != '3306') {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    }
    
    // 소켓 사용 (XAMPP 환경)
    if ($socket && file_exists($socket)) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset;unix_socket=$socket";
    }
    
    // PDO 옵션
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // 문자셋 설정
    $pdo->exec("SET NAMES $charset COLLATE $collation");
    $pdo->exec("SET time_zone = '+09:00'");
    
} catch (PDOException $e) {
    $errorMsg = "DB 연결 실패: " . $e->getMessage();
    
    // 디버그 모드에서 추가 정보 표시
    if (env('APP_DEBUG', false)) {
        $errorMsg .= "\n연결 정보: $dsn";
        $errorMsg .= "\n사용자: $username";
    }
    
    die($errorMsg);
}

/**
 * 테이블명 생성 헬퍼 함수
 * 프리픽스를 자동으로 추가
 */
if (!function_exists('table')) {
    function table($tableName) {
        global $tablePrefix;
        return $tablePrefix . $tableName;
    }
}

/**
 * Admin 전용 getSiteSettings 함수
 * 테이블 프리픽스 적용
 */
function getSiteSettings($pdo, $group = null) {
    $table = table('site_settings');
    $sql = "SELECT setting_key, setting_value, setting_group FROM $table";
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
        // 테이블이 없는 경우 기본 설정값 반환
        return getDefaultAdminSettings($group);
    }
}

/**
 * Admin 전용 기본 설정값 반환
 * 환경 변수에서 기본값 가져오기
 */
function getDefaultAdminSettings($group = null) {
    $all_defaults = [
        // 일반 설정
        'site_name' => env('DEFAULT_SITE_NAME', 'Admin System'),
        'site_description' => env('DEFAULT_SITE_DESCRIPTION', 'Administrative Management System'),
        'site_logo' => '',
        'site_favicon' => '',
        'admin_email' => env('DEFAULT_ADMIN_EMAIL', 'admin@example.com'),
        
        // 테마 설정
        'primary_color' => env('THEME_PRIMARY_COLOR', '#0d6efd'),
        'secondary_color' => env('THEME_SECONDARY_COLOR', '#6c757d'),
        'success_color' => env('THEME_SUCCESS_COLOR', '#198754'),
        'info_color' => env('THEME_INFO_COLOR', '#0dcaf0'),
        'warning_color' => env('THEME_WARNING_COLOR', '#ffc107'),
        'danger_color' => env('THEME_DANGER_COLOR', '#dc3545'),
        'light_color' => env('THEME_LIGHT_COLOR', '#f8f9fa'),
        'dark_color' => env('THEME_DARK_COLOR', '#212529'),
        
        // 폰트 설정
        'body_font' => "'Segoe UI', sans-serif",
        'heading_font' => "'Segoe UI', sans-serif",
        'font_size_base' => '1rem',
        
        // 레이아웃 설정
        'navbar_layout' => 'fixed-top',
        'sidebar_layout' => 'left',
        'footer_layout' => 'standard',
        'container_width' => 'standard',
        
        // SNS 설정
        'facebook_url' => '',
        'twitter_url' => '',
        'instagram_url' => '',
        'youtube_url' => '',
        'kakaotalk_url' => '',
        
        // 기타 설정
        'google_analytics_id' => '',
        'custom_css' => '',
        'custom_js' => ''
    ];
    
    if ($group === null) {
        return $all_defaults;
    }
    
    $group_settings = [];
    foreach ($all_defaults as $key => $value) {
        // 그룹별 설정 분류
        $key_group = '';
        if (strpos($key, 'site_') === 0 || $key === 'admin_email') {
            $key_group = 'general';
        } elseif (in_array($key, ['primary_color', 'secondary_color', 'success_color', 'info_color', 'warning_color', 'danger_color', 'light_color', 'dark_color'])) {
            $key_group = 'theme';
        } elseif (strpos($key, 'font') !== false) {
            $key_group = 'font';
        } elseif (strpos($key, '_layout') !== false || strpos($key, 'container_') !== false) {
            $key_group = 'layout';
        } elseif (strpos($key, '_url') !== false) {
            $key_group = 'social';
        } else {
            $key_group = 'other';
        }
        
        if ($key_group === $group) {
            $group_settings[$key] = $value;
        }
    }
    
    return $group_settings;
}
?>