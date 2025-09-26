<?php
/**
 * Board Theme Integration Helper
 * 
 * Admin 테마 설정을 board_templates의 board-theme.css와 연동하여
 * 세밀한 색상 제어를 제공하는 통합 시스템
 * 
 * @version 3.0 - Enhanced CSS Variable Integration
 */

// ThemeService 로드 - 오류 방지를 위한 안전한 로드
$themeServicePath = dirname(__DIR__) . '/admin/mvc/services/ThemeService.php';
$dbPath = dirname(__DIR__) . '/includes/db.php';

// 파일 존재 확인 후 로드
if (file_exists($themeServicePath)) {
    require_once $themeServicePath;
}

if (file_exists($dbPath)) {
    require_once $dbPath;
}

class BoardThemeIntegration 
{
    private $themeService;
    private $adminThemeSettings;
    
    public function __construct()
    {
        try {
            // PDO 연결 확인
            global $pdo;
            
            // ThemeService 클래스 존재 확인
            if (!class_exists('ThemeService')) {
                throw new Exception('ThemeService class not found');
            }
            
            // PDO 연결 확인
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                // db.php에서 PDO 연결 시도
                if (function_exists('getDbConnection')) {
                    $pdo = getDbConnection();
                } else {
                    throw new Exception('Database connection not available');
                }
            }
            
            $this->themeService = new ThemeService($pdo);
            $this->adminThemeSettings = $this->themeService->getThemeSettings();
            
        } catch (Exception $e) {
            // 폴백: 기본 테마 설정 사용
            error_log('BoardThemeIntegration Error: ' . $e->getMessage());
            $this->adminThemeSettings = $this->getDefaultThemeSettings();
        }
    }
    
    /**
     * 기본 테마 설정 (admin 연동 실패 시 폴백)
     */
    private function getDefaultThemeSettings()
    {
        return [
            'primary_color' => '#3a7a4e',      // Natural-Green Forest-500
            'secondary_color' => '#16a34a',    // Green-600
            'success_color' => '#65a30d',      // Lime-600
            'info_color' => '#3a7a4e',         // Forest-500
            'warning_color' => '#a3e635',      // Lime-400
            'danger_color' => '#2b5d3e',       // Forest-600
            'light_color' => '#fafffe',        // Natural-50
            'dark_color' => '#1f3b2d',         // Forest-700
        ];
    }
    
    /**
     * Admin 테마 색상을 board-theme.css 변수로 매핑
     */
    public function generateBoardThemeVariables()
    {
        $admin = $this->adminThemeSettings;
        
        // 색상 변형 생성
        $primaryRgb = $this->hexToRgb($admin['primary_color']);
        $secondaryRgb = $this->hexToRgb($admin['secondary_color']);
        $successRgb = $this->hexToRgb($admin['success_color']);
        $infoRgb = $this->hexToRgb($admin['info_color']);
        $warningRgb = $this->hexToRgb($admin['warning_color']);
        $dangerRgb = $this->hexToRgb($admin['danger_color']);
        
        // 색상 밝기/어둡기 변형
        $primaryLight = $this->lightenColor($admin['primary_color'], 20);
        $primaryDark = $this->darkenColor($admin['primary_color'], 15);
        $secondaryLight = $this->lightenColor($admin['secondary_color'], 15);
        $secondaryDark = $this->darkenColor($admin['secondary_color'], 15);
        
        // board-theme.css와 호환되는 CSS 변수 생성
        return [
            // 기본 Admin 색상
            '--primary-color' => $admin['primary_color'],
            '--secondary-color' => $admin['secondary_color'],
            '--accent-color' => $secondaryLight,
            '--text-primary' => $admin['dark_color'],
            '--text-secondary' => $this->darkenColor($admin['dark_color'], -30), // 더 밝게
            '--background' => $admin['light_color'],
            '--border-color' => $primaryLight,
            
            // 테마별 배경색 (board-theme.css 호환)
            '--theme-bg-primary' => $admin['light_color'],
            '--theme-bg-secondary' => $this->lightenColor($admin['primary_color'], 40),
            '--theme-bg-accent' => $admin['primary_color'],
            
            // 테두리 색상 (세밀한 제어)
            '--theme-border-light' => $primaryLight,
            '--theme-border-medium' => $admin['primary_color'],
            '--theme-border-strong' => $primaryDark,
            '--theme-border-primary' => $admin['primary_color'],
            
            // 갤러리 카드 전용 테두리 (배경과의 대비 최적화)
            '--theme-card-border' => $primaryDark,
            '--theme-card-border-hover' => $this->darkenColor($admin['primary_color'], 25),
            
            // 텍스트 색상
            '--theme-text-primary' => $admin['dark_color'],
            '--theme-text-secondary' => $this->lightenColor($admin['dark_color'], 30),
            '--theme-text-muted' => $this->lightenColor($admin['dark_color'], 50),
            
            // 상태 색상 (Admin 설정 기반)
            '--theme-success' => $admin['success_color'],
            '--theme-warning' => $admin['warning_color'],
            '--theme-error' => $admin['danger_color'],
            '--theme-info' => $admin['info_color'],
            
            // 프라이머리 색상 변형
            '--theme-primary' => $admin['primary_color'],
            '--theme-primary-light' => $primaryLight,
            '--theme-primary-dark' => $primaryDark,
            '--theme-primary-rgb' => $primaryRgb,
            
            // 세컨더리 색상 변형
            '--theme-secondary' => $admin['secondary_color'],
            '--theme-secondary-light' => $secondaryLight,
            '--theme-secondary-dark' => $secondaryDark,
            '--theme-secondary-rgb' => $secondaryRgb,
            
            // 그림자 (프라이머리 색상 기반)
            '--theme-shadow-sm' => "0 1px 2px 0 rgba({$primaryRgb}, 0.1)",
            '--theme-shadow-md' => "0 4px 6px -1px rgba({$primaryRgb}, 0.15)",
            '--theme-shadow-lg' => "0 10px 15px -3px rgba({$primaryRgb}, 0.2)",
            
            // 테마 설정 기반 둥근 모서리 및 폰트
            '--theme-radius' => $admin['border_radius'] ?? '16px',
            '--theme-radius-sm' => '8px',
            '--theme-radius-lg' => '24px',
            '--font-size' => $admin['font_size_base'] ?? '16px',
            '--line-height' => '1.6',
            
            // Natural-Green 테마와의 호환성 유지
            '--forest-700' => $admin['dark_color'],
            '--forest-600' => $admin['info_color'],
            '--forest-500' => $admin['primary_color'],
            '--green-600' => $admin['secondary_color'],
            '--lime-600' => $admin['success_color'],
            '--lime-400' => $admin['warning_color'],
            '--natural-50' => $admin['light_color'],
        ];
    }
    
    /**
     * 동적 CSS 변수를 HTML에 삽입하는 스타일 태그 생성
     */
    public function renderThemeVariables()
    {
        $variables = $this->generateBoardThemeVariables();
        
        echo "<style id=\"board-theme-integration\">\n";
        echo ":root {\n";
        
        foreach ($variables as $property => $value) {
            echo "    {$property}: {$value};\n";
        }
        
        echo "}\n";
        
        // 추가적인 동적 스타일링
        echo $this->generateAdditionalStyles();
        
        echo "</style>\n";
    }
    
    /**
     * 추가적인 동적 스타일 생성
     */
    private function generateAdditionalStyles()
    {
        $admin = $this->adminThemeSettings;
        
        return "
/* 동적 생성 스타일 - Admin 테마 연동 */
.board-surface {
    --container-width: 1200px;
    --section-spacing: 60px;
}

/* 상태별 배경색 (color-mix 지원 브라우저용) */
@supports (background-color: color-mix(in srgb, red, blue)) {
    .board-surface .bg-red-100 {
        background-color: color-mix(in srgb, var(--theme-error), transparent 85%) !important;
    }
    .board-surface .bg-red-50 {
        background-color: color-mix(in srgb, var(--theme-error), transparent 95%) !important;
    }
    .board-surface .bg-green-100 {
        background-color: color-mix(in srgb, var(--theme-success), transparent 85%) !important;
    }
    .board-surface .bg-green-50 {
        background-color: color-mix(in srgb, var(--theme-success), transparent 95%) !important;
    }
    .board-surface .bg-blue-100 {
        background-color: color-mix(in srgb, var(--theme-info), transparent 85%) !important;
    }
    .board-surface .bg-blue-50 {
        background-color: color-mix(in srgb, var(--theme-info), transparent 95%) !important;
    }
}

/* 폴백 스타일 (color-mix 미지원 브라우저용) */
@supports not (background-color: color-mix(in srgb, red, blue)) {
    .board-surface .bg-red-100 {
        background-color: " . $this->lightenColor($admin['danger_color'], 45) . " !important;
    }
    .board-surface .bg-red-50 {
        background-color: " . $this->lightenColor($admin['danger_color'], 50) . " !important;
    }
    .board-surface .bg-green-100 {
        background-color: " . $this->lightenColor($admin['success_color'], 45) . " !important;
    }
    .board-surface .bg-green-50 {
        background-color: " . $this->lightenColor($admin['success_color'], 50) . " !important;
    }
    .board-surface .bg-blue-100 {
        background-color: " . $this->lightenColor($admin['info_color'], 45) . " !important;
    }
    .board-surface .bg-blue-50 {
        background-color: " . $this->lightenColor($admin['info_color'], 50) . " !important;
    }
}

/* 호버 효과 강화 */
.board-surface a:hover,
.board-surface button:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.board-surface .gallery-card-border:hover {
    box-shadow: var(--theme-shadow-lg) !important;
    transform: translateY(-2px) !important;
}
";
    }
    
    /**
     * board-theme.css 파일 로드
     */
    public function loadBoardThemeCSS()
    {
        $cssPath = __DIR__ . '/../board_templates_new/assets/board-theme.css';
        $cssUrl = '';
        
        // 상대 경로로 CSS URL 생성
        if (file_exists($cssPath)) {
            $cssUrl = '/younglabor/board_templates_new/assets/board-theme.css?v=' . filemtime($cssPath);
            echo '<link rel="stylesheet" href="' . htmlspecialchars($cssUrl) . '" />' . "\n";
            echo '<!-- Board Theme CSS 로드됨: ' . date('H:i:s', filemtime($cssPath)) . ' -->' . "\n";
        } else {
            echo '<!-- Board Theme CSS 파일 없음: ' . $cssPath . ' -->' . "\n";
        }
    }
    
    /**
     * 완전한 테마 통합 렌더링
     */
    public function render()
    {
        // 1. board-theme.css 로드
        $this->loadBoardThemeCSS();
        
        // 2. 동적 CSS 변수 적용
        $this->renderThemeVariables();
        
        // 3. 디버그 정보 (개발 모드에서만)
        if (isset($_GET['debug_theme'])) {
            $this->renderDebugInfo();
        }
    }
    
    /**
     * 디버그 정보 렌더링
     */
    private function renderDebugInfo()
    {
        echo "<!-- Theme Integration Debug Info\n";
        echo "Admin Theme Settings:\n";
        print_r($this->adminThemeSettings);
        echo "\nGenerated CSS Variables:\n";
        print_r($this->generateBoardThemeVariables());
        echo "-->\n";
    }
    
    // 유틸리티 함수들
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
}

// 전역 함수로 간편 사용
if (!function_exists('renderBoardTheme')) {
    function renderBoardTheme()
    {
        try {
            $integration = new BoardThemeIntegration();
            $integration->render();
        } catch (Exception $e) {
            // 오류 발생 시 기본 CSS 로드
            error_log('renderBoardTheme Error: ' . $e->getMessage());
            echo '<link rel="stylesheet" href="/younglabor/board_templates/assets/board-theme-enhanced.css?v=' . time() . '" />' . "\n";
        }
    }
}

// 테마 변수만 가져오기
if (!function_exists('getBoardThemeVariables')) {
    function getBoardThemeVariables()
    {
        try {
            $integration = new BoardThemeIntegration();
            return $integration->generateBoardThemeVariables();
        } catch (Exception $e) {
            error_log('getBoardThemeVariables Error: ' . $e->getMessage());
            return [];
        }
    }
}