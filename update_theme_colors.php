<?php
/**
 * Update existing hopec_site_settings table with Natural-Green theme colors
 * This script works with the existing table structure
 */

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=hopec;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h1>Natural-Green 테마 색상 업데이트</h1>\n";
    
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    // 1. setting_description 컬럼 추가 (존재하지 않는 경우)
    echo "<h2>1단계: 테이블 구조 업데이트</h2>\n";
    try {
        $pdo->exec("ALTER TABLE hopec_site_settings ADD COLUMN setting_description varchar(255) DEFAULT NULL AFTER setting_group");
        echo "<p>✅ setting_description 컬럼 추가 완료</p>\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>✅ setting_description 컬럼 이미 존재</p>\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Natural-Green 테마 색상 설정 (기존 테이블 구조에 맞춤)
    echo "<h2>2단계: Natural-Green 테마 색상 업데이트</h2>\n";
    
    // 색상 매핑: Bootstrap 8색상 → Natural-Green 테마
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
        ['theme_name', 'Natural-Green', 'theme', 'Active theme name'],
        ['theme_version', '1.0.0', 'theme', 'Theme version']
    ];
    
    // INSERT ... ON DUPLICATE KEY UPDATE 쿼리 사용
    $stmt = $pdo->prepare("
        INSERT INTO hopec_site_settings (setting_key, setting_value, setting_group, setting_description) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            setting_description = VALUES(setting_description),
            updated_at = CURRENT_TIMESTAMP
    ");
    
    foreach ($colorSettings as $setting) {
        $stmt->execute($setting);
        echo "<p>✅ <strong>{$setting[0]}:</strong> {$setting[1]} <span style='color: #666;'>({$setting[3]})</span></p>\n";
    }
    
    // 트랜잭션 커밋
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    
    echo "<h2 style='color: green;'>🎉 Natural-Green 테마 색상 업데이트 완료!</h2>\n";
    
    // 업데이트된 색상 확인
    echo "<h3>업데이트된 테마 색상:</h3>\n";
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
    echo "<tr style='background-color: #f5f5f5;'><th>색상명</th><th>이전 값</th><th>새로운 값</th><th>미리보기</th><th>Natural-Green 매핑</th></tr>\n";
    
    // 이전 값과 비교를 위한 매핑
    $previousColors = [
        'primary_color' => '#AAB4E9',
        'secondary_color' => '#16a34a',
        'success_color' => '#65a30d', 
        'info_color' => '#3a7a4e',
        'warning_color' => '#a3e635',
        'danger_color' => '#746B6B',
        'light_color' => '#566A6691',
        'dark_color' => '#1f3b2d'
    ];
    
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
        $newValue = $color['setting_value'];
        $previousValue = $previousColors[$colorKey] ?? 'N/A';
        $naturalMapping = $naturalColorMap[$colorKey] ?? '';
        $changed = $previousValue !== $newValue;
        
        echo "<tr>\n";
        echo "<td><strong>" . ucfirst($colorName) . "</strong></td>\n";
        echo "<td><code style='background: #f0f0f0; padding: 4px; border-radius: 3px;'>$previousValue</code></td>\n";
        echo "<td><code style='background: " . ($changed ? '#e8f5e8' : '#f8f9fa') . "; padding: 4px; border-radius: 3px; font-weight: " . ($changed ? 'bold' : 'normal') . ";'>$newValue</code></td>\n";
        echo "<td><div style='width: 50px; height: 30px; background-color: $newValue; border: 1px solid #ccc; border-radius: 4px;'></div></td>\n";
        echo "<td style='font-size: 12px; color: #666;'>$naturalMapping</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // ThemeService CSS 재생성 호출
    echo "<h2>3단계: CSS 재생성</h2>\n";
    
    try {
        // admin bootstrap 없이 ThemeService 사용
        $cssDir = dirname(__DIR__) . '/hopec/css/theme/';
        if (!is_dir($cssDir)) {
            mkdir($cssDir, 0755, true);
        }
        
        // 간단한 CSS 생성 (ThemeService 대신)
        $cssContent = generateSimpleThemeCSS($colors);
        $cssFile = $cssDir . 'theme.css';
        file_put_contents($cssFile, $cssContent);
        
        echo "<p>✅ 테마 CSS 파일 재생성 완료: $cssFile</p>\n";
        
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ CSS 재생성 실패: " . $e->getMessage() . "</p>\n";
        echo "<p>→ 수동으로 admin에서 테마 설정을 저장해주세요.</p>\n";
    }
    
    // 통계 정보
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM hopec_site_settings");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>✅ Phase 1 완료!</h3>\n";
    echo "<p><strong>총 설정 항목:</strong> $total 개</p>\n";
    echo "<p><strong>완료된 작업:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ hopec_site_settings 테이블 구조 업데이트 (setting_description 컬럼 추가)</li>\n";
    echo "<li>✅ Natural-Green 테마 색상 8개로 업데이트 완료</li>\n";
    echo "<li>✅ 기본 테마 CSS 파일 생성</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>\n";
    echo "<h3>🔄 다음 단계 (Phase 2):</h3>\n";
    echo "<ul>\n";
    echo "<li>⏳ ThemeService CSS 템플릿에 Natural-Green 변수 추가</li>\n";
    echo "<li>⏳ 색상 변수 매핑 완성 (Forest, Lime, Natural 변수들)</li>\n";
    echo "<li>⏳ Admin UI에서 테마 설정 확인</li>\n";
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

/**
 * 간단한 테마 CSS 생성 함수
 */
function generateSimpleThemeCSS($colors) {
    $colorMap = [];
    foreach ($colors as $color) {
        $key = str_replace('_color', '', $color['setting_key']);
        $colorMap[$key] = $color['setting_value'];
    }
    
    $css = "
:root {
    /* Bootstrap Color Variables Override */
    --bs-primary: {$colorMap['primary']};
    --bs-secondary: {$colorMap['secondary']};
    --bs-success: {$colorMap['success']};
    --bs-info: {$colorMap['info']};
    --bs-warning: {$colorMap['warning']};
    --bs-danger: {$colorMap['danger']};
    --bs-light: {$colorMap['light']};
    --bs-dark: {$colorMap['dark']};
    
    /* Custom Theme Variables */
    --theme-primary: {$colorMap['primary']};
    --theme-secondary: {$colorMap['secondary']};
    --theme-success: {$colorMap['success']};
    --theme-info: {$colorMap['info']};
    --theme-warning: {$colorMap['warning']};
    --theme-danger: {$colorMap['danger']};
    --theme-light: {$colorMap['light']};
    --theme-dark: {$colorMap['dark']};
    
    /* Natural-Green Theme Variables Integration */
    --forest-700: {$colorMap['dark']};    /* Dark color → Forest-700 */
    --forest-600: {$colorMap['danger']};  /* Danger color → Forest-600 */
    --forest-500: {$colorMap['primary']}; /* Primary color → Forest-500 */
    --green-600: {$colorMap['secondary']}; /* Secondary color → Green-600 */
    --lime-600: {$colorMap['success']};   /* Success color → Lime-600 */
    --lime-400: {$colorMap['warning']};   /* Warning color → Lime-400 */
    --natural-50: {$colorMap['light']};   /* Light color → Natural-50 */
    
    /* Primary Color Variations */
    --theme-primary-light: " . lightenColor($colorMap['primary'], 20) . ";
    --theme-primary-dark: " . darkenColor($colorMap['primary'], 20) . ";
    --theme-primary-rgb: " . hexToRgb($colorMap['primary']) . ";
}

/* Natural-Green Integration - Bootstrap components use Natural-Green variables */
.btn-primary {
    background-color: var(--forest-500);
    border-color: var(--forest-500);
}

.btn-primary:hover {
    background-color: var(--forest-600);
    border-color: var(--forest-600);
}

.nav-link.active,
.nav-link:hover {
    color: var(--forest-500) !important;
}

a {
    color: var(--forest-500);
}

a:hover {
    color: var(--forest-600);
}

/* Forms */
.form-control:focus {
    border-color: var(--forest-500);
    box-shadow: 0 0 0 0.2rem rgba(var(--theme-primary-rgb), 0.25);
}

.form-check-input:checked {
    background-color: var(--forest-500);
    border-color: var(--forest-500);
}
";
    
    return $css;
}

function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));  
    $b = hexdec(substr($hex, 4, 2));
    return "$r, $g, $b";
}

function lightenColor($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = min(255, $r + (255 - $r) * $percent / 100);
    $g = min(255, $g + (255 - $g) * $percent / 100);
    $b = min(255, $b + (255 - $b) * $percent / 100);
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function darkenColor($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = max(0, $r * (100 - $percent) / 100);
    $g = max(0, $g * (100 - $percent) / 100);
    $b = max(0, $b * (100 - $percent) / 100);
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
?>