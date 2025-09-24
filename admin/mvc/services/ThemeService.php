<?php
/**
 * ThemeService - 테마 관리 및 CSS 생성 서비스
 * 
 * 데이터베이스의 테마 설정을 기반으로 동적 CSS를 생성하고
 * 프론트엔드에 적용하는 기능을 제공
 */

class ThemeService
{
    private $pdo;
    private $cacheDir;
    private $cssDir;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->cacheDir = dirname(__DIR__, 3) . '/uploads/theme_cache/';
        $this->cssDir = dirname(__DIR__, 3) . '/css/theme/';
        
        // 디렉토리 생성
        $this->ensureDirectories();
    }
    
    /**
     * 필요한 디렉토리 생성
     */
    private function ensureDirectories()
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        if (!is_dir($this->cssDir)) {
            mkdir($this->cssDir, 0755, true);
        }
    }
    
    /**
     * 테마 설정 가져오기
     */
    public function getThemeSettings()
    {
        $stmt = $this->pdo->prepare("
            SELECT setting_key, setting_value 
            FROM hopec_site_settings 
            WHERE setting_group IN ('theme', 'font', 'layout')
        ");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // 기본값 설정
        return array_merge([
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
            'font_size_base' => '1rem',
        ], $settings);
    }
    
    /**
     * 테마 CSS 생성
     */
    public function generateThemeCSS()
    {
        $settings = $this->getThemeSettings();
        
        // CSS 템플릿 로드
        $cssTemplate = $this->getCSSTemplate();
        
        // 변수 치환
        $css = $this->replaceCSSVariables($cssTemplate, $settings);
        
        // CSS 파일 저장
        $cssFile = $this->cssDir . 'theme.css';
        file_put_contents($cssFile, $css);
        
        // 캐시 파일도 저장 (버전 관리용)
        $cacheFile = $this->cacheDir . 'theme_' . md5(json_encode($settings)) . '.css';
        file_put_contents($cacheFile, $css);
        
        return $cssFile;
    }
    
    /**
     * CSS 템플릿 가져오기
     */
    private function getCSSTemplate()
    {
        return '
:root {
    /* Bootstrap Color Variables Override */
    --bs-primary: {primary_color};
    --bs-secondary: {secondary_color};
    --bs-success: {success_color};
    --bs-info: {info_color};
    --bs-warning: {warning_color};
    --bs-danger: {danger_color};
    --bs-light: {light_color};
    --bs-dark: {dark_color};
    
    /* Custom Theme Variables */
    --theme-primary: {primary_color};
    --theme-secondary: {secondary_color};
    --theme-success: {success_color};
    --theme-info: {info_color};
    --theme-warning: {warning_color};
    --theme-danger: {danger_color};
    --theme-light: {light_color};
    --theme-dark: {dark_color};
    
    /* Natural-Green Theme Variables Integration */
    /* Admin 8색상을 Natural-Green 테마 변수로 매핑 - !important로 우선순위 보장 */
    --forest-700: {dark_color} !important;          /* Dark color → Forest-700 (어두운 텍스트/배경) */
    --forest-600: {info_color} !important;          /* Info color → Forest-600 (navbar 텍스트/메뉴) */
    --forest-500: {primary_color} !important;       /* Primary color → Forest-500 (메인 브랜드) */
    --green-600: {secondary_color} !important;      /* Secondary color → Green-600 (보조 액션) */
    --lime-600: {success_color} !important;         /* Success color → Lime-600 (성공/확인) */
    --lime-400: {warning_color} !important;         /* Warning color → Lime-400 (경고/주의) */
    --lime-500: {lime_500_color} !important;        /* Additional Lime-500 */
    --lime-300: {lime_300_color} !important;        /* Additional Lime-300 */
    --lime-200: {lime_200_color} !important;        /* Additional Lime-200 */
    --natural-50: {light_color} !important;         /* Light color → Natural-50 (밝은 배경) */
    --natural-100: {natural_100_color} !important;  /* Additional Natural-100 */
    --natural-200: {natural_200_color} !important;  /* Additional Natural-200 */
    
    /* Primary Color Variations */
    --theme-primary-light: {primary_color_light};
    --theme-primary-dark: {primary_color_dark};
    --theme-primary-rgb: {primary_color_rgb};
    
    /* Color Variations for Natural-Green Integration */
    --forest-700-rgb: {dark_color_rgb};
    --forest-600-rgb: {danger_color_rgb};
    --forest-500-rgb: {primary_color_rgb};
    --green-600-rgb: {secondary_color_rgb};
    --lime-600-rgb: {success_color_rgb};
    --lime-400-rgb: {warning_color_rgb};
    --natural-50-rgb: {light_color_rgb};
    
    /* Font Variables */
    --theme-font-family-base: {body_font};
    --theme-font-family-heading: {heading_font};
    --theme-font-size-base: {font_size_base};
}

/* Global Overrides */
body {
    font-family: var(--theme-font-family-base);
    font-size: var(--theme-font-size-base);
}

h1, h2, h3, h4, h5, h6,
.h1, .h2, .h3, .h4, .h5, .h6 {
    font-family: var(--theme-font-family-heading);
}

/* Bootstrap Components Override */
.btn-primary {
    background-color: var(--theme-primary);
    border-color: var(--theme-primary);
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: var(--theme-primary-dark);
    border-color: var(--theme-primary-dark);
}

.btn-outline-primary {
    color: var(--theme-primary);
    border-color: var(--theme-primary);
}

.btn-outline-primary:hover {
    background-color: var(--theme-primary);
    border-color: var(--theme-primary);
}

/* Navbar Theming */
.navbar-brand {
    color: var(--theme-primary) !important;
}

.nav-link.active,
.nav-link:hover {
    color: var(--theme-primary) !important;
}

/* Links */
a {
    color: var(--theme-primary);
}

a:hover {
    color: var(--theme-primary-dark);
}

/* Forms */
.form-control:focus {
    border-color: var(--theme-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--theme-primary-rgb), 0.25);
}

.form-check-input:checked {
    background-color: var(--theme-primary);
    border-color: var(--theme-primary);
}

/* Cards */
.card-header {
    background-color: rgba(var(--theme-primary-rgb), 0.1);
    border-bottom-color: var(--theme-primary);
}

/* Badges */
.badge.bg-primary {
    background-color: var(--theme-primary) !important;
}

/* Pagination */
.page-link {
    color: var(--theme-primary);
}

.page-link:hover {
    color: var(--theme-primary-dark);
    background-color: rgba(var(--theme-primary-rgb), 0.1);
}

.page-item.active .page-link {
    background-color: var(--theme-primary);
    border-color: var(--theme-primary);
}

/* Alerts */
.alert-primary {
    color: var(--theme-primary-dark);
    background-color: rgba(var(--theme-primary-rgb), 0.1);
    border-color: rgba(var(--theme-primary-rgb), 0.2);
}

/* Progress Bars */
.progress-bar {
    background-color: var(--theme-primary);
}

/* Dropdowns */
.dropdown-item:hover,
.dropdown-item:focus {
    background-color: rgba(var(--theme-primary-rgb), 0.1);
}

.dropdown-item.active {
    background-color: var(--theme-primary);
}

/* Tables */
.table-primary {
    background-color: rgba(var(--theme-primary-rgb), 0.1);
}

/* Sidebar Theming (Admin) */
.sidebar .nav-link.active {
    background-color: var(--theme-primary);
    color: white;
}

.sidebar .nav-link:hover {
    background-color: rgba(var(--theme-primary-rgb), 0.1);
}

/* Custom Components */
.labor-rights-header {
    background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-primary-dark) 100%);
}

.feature-card:hover {
    border-color: var(--theme-primary);
}

.cta-section {
    background-color: var(--theme-primary);
}

/* Loading Spinner */
.spinner-border-primary {
    color: var(--theme-primary);
}

/* Custom Button Variants */
.btn-theme {
    background-color: var(--forest-500);
    border-color: var(--forest-500);
    color: white;
}

.btn-theme:hover {
    background-color: var(--forest-600);
    border-color: var(--forest-600);
    color: white;
}

/* Natural-Green Theme Integration - Use Natural-Green variables throughout */

/* Navigation using Natural-Green colors */
.navbar-brand {
    color: var(--forest-500) !important;
}

.nav-link.active,
.nav-link:hover {
    color: var(--forest-500) !important;
}

/* Buttons using Natural-Green theme */
.btn-primary {
    background-color: var(--forest-500);
    border-color: var(--forest-500);
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: var(--forest-600);
    border-color: var(--forest-600);
}

.btn-success {
    background-color: var(--lime-600);
    border-color: var(--lime-600);
}

.btn-success:hover {
    background-color: var(--lime-500);
    border-color: var(--lime-500);
}

/* Links using Natural-Green theme */
a {
    color: var(--forest-500);
}

a:hover {
    color: var(--forest-600);
}

/* Forms using Natural-Green theme */
.form-control:focus {
    border-color: var(--forest-500);
    box-shadow: 0 0 0 0.2rem rgba(var(--forest-500-rgb), 0.25);
}

.form-check-input:checked {
    background-color: var(--forest-500);
    border-color: var(--forest-500);
}

/* Alerts using Natural-Green theme */
.alert-success {
    background-color: var(--natural-100);
    border-color: var(--lime-200);
    color: var(--forest-700);
}

.alert-info {
    background-color: var(--natural-200);
    border-color: var(--natural-100);
    color: var(--forest-700);
}

/* Cards using Natural-Green theme */
.card-header {
    background-color: var(--natural-100);
    border-bottom-color: var(--forest-500);
}

/* Tables using Natural-Green theme */
.table-success {
    background-color: var(--natural-100);
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: var(--natural-50);
}

/* Pagination using Natural-Green theme */
.page-link {
    color: var(--forest-500);
}

.page-link:hover {
    color: var(--forest-600);
    background-color: var(--natural-100);
}

.page-item.active .page-link {
    background-color: var(--forest-500);
    border-color: var(--forest-500);
}

/* Dropdowns using Natural-Green theme */
.dropdown-item:hover,
.dropdown-item:focus {
    background-color: var(--natural-100);
}

.dropdown-item.active {
    background-color: var(--forest-500);
}

/* Progress bars using Natural-Green theme */
.progress-bar {
    background-color: var(--lime-600);
}

/* Badges using Natural-Green theme */
.badge.bg-success {
    background-color: var(--lime-600) !important;
}

.badge.bg-primary {
    background-color: var(--forest-500) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    body {
        font-size: calc(var(--theme-font-size-base) * 0.9);
    }
}
';
    }
    
    /**
     * CSS 변수 치환
     */
    private function replaceCSSVariables($template, $settings)
    {
        // 기본 색상 변형 계산
        $settings['primary_color_rgb'] = $this->hexToRgb($settings['primary_color']);
        $settings['primary_color_light'] = $this->lightenColor($settings['primary_color'], 20);
        $settings['primary_color_dark'] = $this->darkenColor($settings['primary_color'], 20);
        
        // Natural-Green 테마 추가 색상들 (실제 Natural-Green 테마에서 가져온 값들)
        $settings['lime_500_color'] = '#84cc16';     // 실제 Natural-Green의 Lime-500
        $settings['lime_300_color'] = '#bef264';     // 실제 Natural-Green의 Lime-300  
        $settings['lime_200_color'] = '#d9f99d';     // 실제 Natural-Green의 Lime-200
        $settings['natural_100_color'] = '#f4f8f3';  // 실제 Natural-Green의 Natural-100
        $settings['natural_200_color'] = '#e8f4e6';  // 실제 Natural-Green의 Natural-200
        
        // 모든 주요 색상들의 RGB 값 계산
        $colorKeys = ['primary_color', 'secondary_color', 'success_color', 'info_color', 
                      'warning_color', 'danger_color', 'light_color', 'dark_color'];
        
        foreach ($colorKeys as $colorKey) {
            if (isset($settings[$colorKey])) {
                $baseKey = str_replace('_color', '', $colorKey);
                $settings[$baseKey . '_color_rgb'] = $this->hexToRgb($settings[$colorKey]);
            }
        }
        
        // Natural-Green 추가 색상들의 RGB 값도 계산
        $additionalColors = ['lime_500_color', 'lime_300_color', 'lime_200_color', 
                            'natural_100_color', 'natural_200_color'];
        
        foreach ($additionalColors as $colorKey) {
            if (isset($settings[$colorKey])) {
                $baseKey = str_replace('_color', '', $colorKey);
                $settings[$baseKey . '_rgb'] = $this->hexToRgb($settings[$colorKey]);
            }
        }
        
        // 변수 치환
        foreach ($settings as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
    
    /**
     * HEX 색상을 RGB로 변환
     */
    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "$r, $g, $b";
    }
    
    /**
     * 색상 밝게 하기
     */
    private function lightenColor($hex, $percent)
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = min(255, $r + (255 - $r) * $percent / 100);
        $g = min(255, $g + (255 - $g) * $percent / 100);
        $b = min(255, $b + (255 - $b) * $percent / 100);
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * 색상 어둡게 하기
     */
    private function darkenColor($hex, $percent)
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, $r * (100 - $percent) / 100);
        $g = max(0, $g * (100 - $percent) / 100);
        $b = max(0, $b * (100 - $percent) / 100);
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * 테마 설정 업데이트 후 CSS 재생성
     */
    public function updateTheme($settings)
    {
        $stmt = $this->pdo->prepare("
            UPDATE hopec_site_settings 
            SET setting_value = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE setting_key = ?
        ");
        
        foreach ($settings as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        
        // CSS 재생성
        return $this->generateThemeCSS();
    }
    
    /**
     * 테마 캐시 클리어
     */
    public function clearThemeCache()
    {
        $files = glob($this->cacheDir . 'theme_*.css');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * 테마 CSS URL 가져오기
     */
    public function getThemeCSS()
    {
        $cssFile = $this->cssDir . 'theme.css';
        
        // CSS 파일이 없으면 생성
        if (!file_exists($cssFile)) {
            $this->generateThemeCSS();
        }
        
        // 수정 시간 확인하여 갱신 필요한지 체크
        $settingsHash = md5(json_encode($this->getThemeSettings()));
        $cacheFile = $this->cacheDir . 'theme_' . $settingsHash . '.css';
        
        if (!file_exists($cacheFile) || filemtime($cacheFile) < filemtime($cssFile)) {
            $this->generateThemeCSS();
        }
        
        return '/css/theme/theme.css?v=' . filemtime($cssFile);
    }
    
    /**
     * 테마 프리뷰 CSS 생성 (실시간 미리보기용)
     */
    public function generatePreviewCSS($settings)
    {
        $cssTemplate = $this->getCSSTemplate();
        $css = $this->replaceCSSVariables($cssTemplate, $settings);
        
        return $css;
    }
    
    /**
     * 테마 백업
     */
    public function backupCurrentTheme()
    {
        $settings = $this->getThemeSettings();
        $backupFile = $this->cacheDir . 'theme_backup_' . date('Y-m-d_H-i-s') . '.json';
        
        file_put_contents($backupFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $backupFile;
    }
    
    /**
     * 테마 복원
     */
    public function restoreTheme($backupFile)
    {
        if (!file_exists($backupFile)) {
            throw new Exception('백업 파일을 찾을 수 없습니다.');
        }
        
        $settings = json_decode(file_get_contents($backupFile), true);
        
        if (!$settings) {
            throw new Exception('백업 파일이 손상되었습니다.');
        }
        
        return $this->updateTheme($settings);
    }
    
    /**
     * 테마 프리셋 저장
     */
    public function saveThemePreset($name, $colors, $description = null, $createdBy = 'admin')
    {
        // 이름 중복 확인
        $stmt = $this->pdo->prepare("SELECT id FROM hopec_theme_presets WHERE preset_name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            throw new Exception('이미 존재하는 테마 이름입니다.');
        }
        
        // 색상 데이터 검증
        $requiredColors = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'light', 'dark'];
        foreach ($requiredColors as $colorType) {
            if (!isset($colors[$colorType])) {
                throw new Exception("필수 색상이 누락되었습니다: {$colorType}");
            }
            
            // 색상 값 검증
            if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $colors[$colorType])) {
                throw new Exception("유효하지 않은 색상 값입니다: {$colors[$colorType]}");
            }
        }
        
        // 다음 sort_order 값 계산
        $stmt = $this->pdo->query("SELECT MAX(sort_order) as max_order FROM hopec_theme_presets");
        $maxOrder = $stmt->fetchColumn() ?: 0;
        $nextOrder = $maxOrder + 1;
        
        // 프리셋 저장
        $stmt = $this->pdo->prepare("
            INSERT INTO hopec_theme_presets 
            (preset_name, preset_colors, preset_description, created_by, sort_order) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $name,
            json_encode($colors, JSON_UNESCAPED_UNICODE),
            $description,
            $createdBy,
            $nextOrder
        ]);
        
        if (!$result) {
            throw new Exception('테마 프리셋 저장에 실패했습니다.');
        }
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * 테마 프리셋 목록 조회
     */
    public function getThemePresets($activeOnly = true)
    {
        $sql = "SELECT id, preset_name, preset_colors, preset_description, created_by, created_at, is_active, sort_order 
                FROM hopec_theme_presets";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, created_at ASC";
        
        $stmt = $this->pdo->query($sql);
        $presets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // JSON 색상 데이터 파싱
        foreach ($presets as &$preset) {
            $preset['colors'] = json_decode($preset['preset_colors'], true);
            $preset['created_at_formatted'] = date('Y-m-d H:i', strtotime($preset['created_at']));
        }
        
        return $presets;
    }
    
    /**
     * 특정 테마 프리셋 조회
     */
    public function getThemePreset($presetId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, preset_name, preset_colors, preset_description, created_by, created_at, is_active, sort_order 
            FROM hopec_theme_presets 
            WHERE id = ? AND is_active = 1
        ");
        
        $stmt->execute([$presetId]);
        $preset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$preset) {
            throw new Exception('테마 프리셋을 찾을 수 없습니다.');
        }
        
        $preset['colors'] = json_decode($preset['preset_colors'], true);
        $preset['created_at_formatted'] = date('Y-m-d H:i', strtotime($preset['created_at']));
        
        return $preset;
    }
    
    /**
     * 테마 프리셋 불러오기 (현재 테마에 적용)
     */
    public function loadThemePreset($presetId)
    {
        $preset = $this->getThemePreset($presetId);
        $colors = $preset['colors'];
        
        // 색상을 _color 접미사가 있는 키로 변환
        $settings = [];
        foreach ($colors as $colorType => $colorValue) {
            $settings[$colorType . '_color'] = $colorValue;
        }
        
        // 테마 업데이트
        return $this->updateTheme($settings);
    }
    
    /**
     * 테마 프리셋 업데이트
     */
    public function updateThemePreset($presetId, $name = null, $colors = null, $description = null)
    {
        // 존재하는 프리셋인지 확인
        $preset = $this->getThemePreset($presetId);
        
        $updates = [];
        $params = [];
        
        if ($name !== null) {
            // 이름 중복 확인 (자신 제외)
            $stmt = $this->pdo->prepare("SELECT id FROM hopec_theme_presets WHERE preset_name = ? AND id != ?");
            $stmt->execute([$name, $presetId]);
            
            if ($stmt->fetch()) {
                throw new Exception('이미 존재하는 테마 이름입니다.');
            }
            
            $updates[] = "preset_name = ?";
            $params[] = $name;
        }
        
        if ($colors !== null) {
            // 색상 데이터 검증
            $requiredColors = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'light', 'dark'];
            foreach ($requiredColors as $colorType) {
                if (!isset($colors[$colorType])) {
                    throw new Exception("필수 색상이 누락되었습니다: {$colorType}");
                }
                
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $colors[$colorType])) {
                    throw new Exception("유효하지 않은 색상 값입니다: {$colors[$colorType]}");
                }
            }
            
            $updates[] = "preset_colors = ?";
            $params[] = json_encode($colors, JSON_UNESCAPED_UNICODE);
        }
        
        if ($description !== null) {
            $updates[] = "preset_description = ?";
            $params[] = $description;
        }
        
        if (empty($updates)) {
            throw new Exception('업데이트할 내용이 없습니다.');
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $presetId;
        
        $sql = "UPDATE hopec_theme_presets SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        if (!$stmt->execute($params)) {
            throw new Exception('테마 프리셋 업데이트에 실패했습니다.');
        }
        
        return true;
    }
    
    /**
     * 테마 프리셋 삭제 (soft delete)
     */
    public function deleteThemePreset($presetId)
    {
        // 존재하는 프리셋인지 확인
        $preset = $this->getThemePreset($presetId);
        
        // system 생성 프리셋은 삭제 불가
        if ($preset['created_by'] === 'system') {
            throw new Exception('시스템 기본 테마는 삭제할 수 없습니다.');
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE hopec_theme_presets 
            SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        
        if (!$stmt->execute([$presetId])) {
            throw new Exception('테마 프리셋 삭제에 실패했습니다.');
        }
        
        return true;
    }
    
    /**
     * 테마 프리셋 순서 변경
     */
    public function updatePresetOrder($presetId, $newOrder)
    {
        $stmt = $this->pdo->prepare("
            UPDATE hopec_theme_presets 
            SET sort_order = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND is_active = 1
        ");
        
        return $stmt->execute([$newOrder, $presetId]);
    }
    
    /**
     * 현재 설정된 색상으로 테마 프리셋 생성 (현재 색상 저장)
     */
    public function saveCurrentThemeAsPreset($name, $description = null, $createdBy = 'admin')
    {
        $currentSettings = $this->getThemeSettings();
        
        // 현재 색상 추출
        $colors = [];
        $colorKeys = ['primary_color', 'secondary_color', 'success_color', 'info_color', 
                      'warning_color', 'danger_color', 'light_color', 'dark_color'];
        
        foreach ($colorKeys as $key) {
            $colorType = str_replace('_color', '', $key);
            $colors[$colorType] = $currentSettings[$key] ?? '#000000';
        }
        
        return $this->saveThemePreset($name, $colors, $description, $createdBy);
    }
}