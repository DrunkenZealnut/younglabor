<?php
/**
 * Direct Database Setup Script
 * Admin 인증 없이 직접 데이터베이스 설정을 수행합니다.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>hopec_site_settings 직접 설정</h1>\n";

// 데이터베이스 연결 정보 (다양한 연결 방법 시도)
$db_configs = [
    [
        'host' => 'localhost',
        'dbname' => 'hopec',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'socket' => null
    ],
    [
        'host' => '127.0.0.1',
        'dbname' => 'hopec', 
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'socket' => null
    ],
    [
        'host' => 'localhost',
        'dbname' => 'hopec',
        'username' => 'root', 
        'password' => '',
        'charset' => 'utf8mb4',
        'socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock'
    ]
];

$pdo = null;
$connected = false;

// 여러 연결 방법 시도
foreach ($db_configs as $i => $db_config) {
    try {
        echo "<p>연결 시도 " . ($i + 1) . ": {$db_config['host']}</p>\n";
        
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        if ($db_config['socket']) {
            $dsn .= ";unix_socket={$db_config['socket']}";
        }
        
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        echo "<p style='color: green;'>✅ 연결 성공: {$db_config['host']}</p>\n";
        $connected = true;
        break;
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ 연결 실패 " . ($i + 1) . ": " . $e->getMessage() . "</p>\n";
        continue;
    }
}

if (!$connected) {
    throw new Exception("모든 데이터베이스 연결 방법이 실패했습니다.");
}

try {
    
    echo "<p style='color: green;'>✅ 데이터베이스 연결 성공</p>\n";
    
    // 테이블 존재 확인
    $stmt = $pdo->query("SHOW TABLES LIKE 'hopec_site_settings'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>✅ hopec_site_settings 테이블 존재</p>\n";
        
        // 기존 데이터 확인
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM hopec_site_settings");
        $count = $stmt->fetch()['count'];
        echo "<p>기존 레코드 수: $count</p>\n";
        
        if ($count > 0) {
            echo "<p style='color: orange;'>⚠️ 기존 데이터가 있습니다. 업데이트 방식으로 진행합니다.</p>\n";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ hopec_site_settings 테이블이 없습니다. 생성합니다.</p>\n";
    }
    
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    // 1. 테이블 생성 (존재하지 않는 경우)
    echo "<h2>1단계: 테이블 생성</h2>\n";
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `hopec_site_settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `setting_key` varchar(100) NOT NULL,
      `setting_value` text,
      `setting_group` varchar(50) DEFAULT 'general',
      `setting_description` varchar(255) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_setting_key` (`setting_key`),
      KEY `idx_setting_group` (`setting_group`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTableSQL);
    echo "<p>✅ 테이블 생성/확인 완료</p>\n";
    
    // 2. Natural-Green 테마 색상 데이터 삽입
    echo "<h2>2단계: Natural-Green 테마 색상 설정</h2>\n";
    
    $colorSettings = [
        ['primary_color', '#3a7a4e', 'theme', 'Primary brand color - Forest-500'],
        ['secondary_color', '#16a34a', 'theme', 'Secondary action color - Green-600'],
        ['success_color', '#65a30d', 'theme', 'Success/confirmation color - Lime-600'],
        ['info_color', '#3a7a4e', 'theme', 'Information display color - Forest-500'],
        ['warning_color', '#a3e635', 'theme', 'Warning/caution color - Lime-400'],
        ['danger_color', '#2b5d3e', 'theme', 'Error/danger color - Forest-600'],
        ['light_color', '#fafffe', 'theme', 'Light background color - Natural-50'],
        ['dark_color', '#1f3b2d', 'theme', 'Dark text/background color - Forest-700'],
        ['body_font', "'Noto Sans KR', 'Segoe UI', sans-serif", 'theme', 'Main body font family'],
        ['heading_font', "'Noto Sans KR', 'Segoe UI', sans-serif", 'theme', 'Heading font family'],
        ['font_size_base', '1rem', 'theme', 'Base font size'],
        ['site_title', '사단법인 희망씨', 'general', 'Site title'],
        ['site_description', '사단법인 희망씨 공식 웹사이트', 'general', 'Site description'],
        ['theme_name', 'Natural-Green', 'theme', 'Active theme name'],
        ['theme_version', '1.0.0', 'theme', 'Theme version']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO `hopec_site_settings` (`setting_key`, `setting_value`, `setting_group`, `setting_description`) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            `setting_value` = VALUES(`setting_value`),
            `setting_description` = VALUES(`setting_description`),
            `updated_at` = CURRENT_TIMESTAMP
    ");
    
    foreach ($colorSettings as $setting) {
        $stmt->execute($setting);
        echo "<p>✅ {$setting[0]}: {$setting[1]}</p>\n";
    }
    
    // 트랜잭션 커밋
    $pdo->commit();
    
    echo "<h2 style='color: green;'>🎉 Natural-Green 테마 색상 설정 완료!</h2>\n";
    
    // 설정 결과 확인
    echo "<h3>설정된 테마 색상 미리보기:</h3>\n";
    $stmt = $pdo->prepare("
        SELECT setting_key, setting_value, setting_description 
        FROM hopec_site_settings 
        WHERE setting_group = 'theme' 
        AND setting_key LIKE '%_color' 
        ORDER BY 
            CASE setting_key
                WHEN 'primary_color' THEN 1
                WHEN 'secondary_color' THEN 2
                WHEN 'success_color' THEN 3
                WHEN 'info_color' THEN 4
                WHEN 'warning_color' THEN 5
                WHEN 'danger_color' THEN 6
                WHEN 'light_color' THEN 7
                WHEN 'dark_color' THEN 8
                ELSE 9
            END
    ");
    $stmt->execute();
    $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; margin: 20px 0; font-family: Arial, sans-serif;'>\n";
    echo "<tr style='background-color: #f5f5f5;'><th>색상명</th><th>색상값</th><th>미리보기</th><th>Natural-Green 매핑</th></tr>\n";
    
    $naturalColorMap = [
        'primary_color' => 'Forest-500 (메인 브랜드)',
        'secondary_color' => 'Green-600 (보조 액션)',
        'success_color' => 'Lime-600 (성공)',
        'info_color' => 'Forest-500 (정보)',
        'warning_color' => 'Lime-400 (경고)',
        'danger_color' => 'Forest-600 (위험)',
        'light_color' => 'Natural-50 (밝은 배경)',
        'dark_color' => 'Forest-700 (어두운 텍스트)'
    ];
    
    foreach ($colors as $color) {
        $colorKey = $color['setting_key'];
        $colorName = str_replace('_color', '', $colorKey);
        $colorValue = $color['setting_value'];
        $naturalMapping = $naturalColorMap[$colorKey] ?? '';
        
        echo "<tr>\n";
        echo "<td><strong>" . ucfirst($colorName) . "</strong></td>\n";
        echo "<td><code style='background: #f8f9fa; padding: 4px; border-radius: 3px;'>$colorValue</code></td>\n";
        echo "<td><div style='width: 50px; height: 30px; background-color: $colorValue; border: 1px solid #ccc; border-radius: 4px;'></div></td>\n";
        echo "<td style='font-size: 12px; color: #666;'>$naturalMapping</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // 통계 정보
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM hopec_site_settings");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>✅ Phase 1 완료!</h3>\n";
    echo "<p><strong>총 설정 항목:</strong> $total 개</p>\n";
    echo "<p><strong>완료 단계:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ hopec_site_settings 테이블 생성 완료</li>\n";
    echo "<li>✅ Natural-Green 테마 색상 8개로 초기화 완료</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>\n";
    echo "<h3>🔄 다음 단계 (Phase 2):</h3>\n";
    echo "<ul>\n";
    echo "<li>⏳ ThemeService CSS 템플릿에 Natural-Green 변수 추가</li>\n";
    echo "<li>⏳ 색상 변수 매핑 구현 (Forest, Lime, Natural 변수들)</li>\n";
    echo "<li>⏳ CSS 로딩 순서 최적화</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    // 트랜잭션 롤백
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    echo "<h2 style='color: red;'>❌ 오류 발생:</h2>\n";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;'>\n";
    echo "<p><strong>오류 메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>파일:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>라인:</strong> " . $e->getLine() . "</p>\n";
    echo "</div>\n";
}
?>