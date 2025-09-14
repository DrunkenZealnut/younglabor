<?php
/**
 * Safe Board Theme Integration Helper - 500 에러 방지 버전
 * 
 * 기본적인 기능만 포함하여 오류 가능성을 최소화
 */

class SafeBoardThemeIntegration 
{
    private $adminThemeSettings;
    
    public function __construct()
    {
        // 복잡한 DB 연동 없이 기본값 사용
        $this->adminThemeSettings = $this->getDefaultThemeSettings();
        
        // 나중에 DB 연동 시도 (실패해도 계속 진행)
        $this->tryLoadAdminSettings();
    }
    
    private function tryLoadAdminSettings()
    {
        try {
            // 안전하게 DB 연동 시도
            if ($this->canConnectToDatabase()) {
                $this->loadAdminThemeSettings();
            }
        } catch (Exception $e) {
            // 실패해도 무시하고 기본값 사용
            error_log('Admin theme loading failed: ' . $e->getMessage());
        }
    }
    
    private function canConnectToDatabase()
    {
        try {
            // hopec 프로젝트의 DB 설정 확인
            $dbConfigPath = dirname(__DIR__) . '/includes/db.php';
            if (file_exists($dbConfigPath)) {
                return true;
            }
            
            // admin DB 설정 확인
            $adminDbPath = dirname(__DIR__) . '/admin/db.php';
            if (file_exists($adminDbPath)) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function loadAdminThemeSettings()
    {
        try {
            // hopec 프로젝트 DB 연결 시도
            $dbConfigPath = dirname(__DIR__) . '/includes/db.php';
            if (file_exists($dbConfigPath)) {
                require_once $dbConfigPath;
                if (isset($pdo)) {
                    $this->loadThemeFromDatabase($pdo);
                    return;
                }
            }
            
            // admin DB 연결 시도
            $adminDbPath = dirname(__DIR__) . '/admin/db.php';
            if (file_exists($adminDbPath)) {
                require_once $adminDbPath;
                if (isset($pdo)) {
                    $this->loadThemeFromDatabase($pdo);
                    return;
                }
            }
        } catch (Exception $e) {
            error_log('Failed to load admin theme: ' . $e->getMessage());
        }
    }
    
    private function loadThemeFromDatabase($pdo)
    {
        try {
            // hopec_site_settings 테이블에서 테마 설정 로드
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM hopec_site_settings WHERE setting_group = 'theme'");
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $key = $row['setting_key'];
                $value = $row['setting_value'];
                
                // admin 설정 키를 우리 설정으로 매핑
                if (isset($this->adminThemeSettings[$key])) {
                    $this->adminThemeSettings[$key] = $value;
                }
            }
        } catch (Exception $e) {
            error_log('Database theme loading error: ' . $e->getMessage());
        }
    }
    
    /**
     * 활성 테마에 따른 기본 설정
     */
    private function getDefaultThemeSettings()
    {
        // 현재 활성 테마 확인
        $activeTheme = $this->getActiveTheme();
        
        // Basic 테마인 경우 모노크롬 설정
        if ($activeTheme === 'basic') {
            return [
                'primary_color' => '#1f2937',      // Gray-800
                'secondary_color' => '#6b7280',    // Gray-500
                'success_color' => '#10b981',      // Emerald-500
                'info_color' => '#3b82f6',         // Blue-500
                'warning_color' => '#f59e0b',      // Amber-500
                'danger_color' => '#ef4444',       // Red-500
                'light_color' => '#ffffff',        // White
                'dark_color' => '#111827',         // Gray-900
            ];
        }
        
        // Natural-Green 테마 (기본값)
        return [
            'primary_color' => '#3a7a4e',      // Forest-500
            'secondary_color' => '#16a34a',    // Green-600
            'success_color' => '#65a30d',      // Lime-600
            'info_color' => '#3b82f6',         // Blue-600
            'warning_color' => '#f59e0b',      // Amber-500
            'danger_color' => '#dc2626',       // Red-600
            'light_color' => '#fafffe',        // Natural-50
            'dark_color' => '#1f3b2d',         // Forest-700
        ];
    }
    
    /**
     * 현재 활성 테마 가져오기
     */
    private function getActiveTheme()
    {
        // Natural Green 단일 테마 시스템으로 고정
        // 더 이상 ThemeManager를 사용하지 않고 natural-green 테마로 통일
        return 'natural-green';
    }
    
    /**
     * CSS 변수 생성
     */
    public function generateCSSVariables()
    {
        $admin = $this->adminThemeSettings;
        
        $variables = [
            '--theme-primary' => $admin['primary_color'],
            '--theme-secondary' => $admin['secondary_color'],
            '--theme-success' => $admin['success_color'],
            '--theme-info' => $admin['info_color'],
            '--theme-warning' => $admin['warning_color'],
            '--theme-error' => $admin['danger_color'],
            '--theme-light' => $admin['light_color'],
            '--theme-dark' => $admin['dark_color'],
            
            // 파생 색상
            '--theme-bg-primary' => $admin['light_color'],
            '--theme-text-primary' => $admin['dark_color'],
            '--theme-border-light' => '#e2e8f0',
        ];
        
        return $variables;
    }
    
    /**
     * CSS 출력
     */
    public function renderCSS()
    {
        $activeTheme = $this->getActiveTheme();
        
        echo "<!-- Safe Board Theme Integration: {$activeTheme} -->\n";
        
        // 1. 활성 테마의 globals.css 로드
        $themeGlobalsPath = dirname(__DIR__) . '/theme/' . $activeTheme . '/styles/globals.css';
        if (file_exists($themeGlobalsPath)) {
            $cssVersion = filemtime($themeGlobalsPath);
            $cssUrl = '/hopec/theme/' . $activeTheme . '/styles/globals.css?v=' . $cssVersion . '&board=1';
            echo '<link rel="stylesheet" href="' . $cssUrl . '" />' . "\n";
            echo "<!-- Board 테마 CSS 로드됨: {$activeTheme} -->\n";
        } else {
            echo "<!-- Board 테마 CSS 파일 없음: {$themeGlobalsPath} -->\n";
            
            // Fallback: CSS 변수로 기본 스타일링
            $variables = $this->generateCSSVariables();
            
            echo "<style id=\"safe-board-theme\">\n";
            echo ":root {\n";
            
            foreach ($variables as $property => $value) {
                echo "    {$property}: {$value};\n";
            }
            
            echo "}\n";
            echo "</style>\n";
        }
        
        // 2. 기본 CSS 파일도 로드 (있는 경우)
        $cssPath = __DIR__ . '/assets/board-theme-minimal.css';
        if (file_exists($cssPath)) {
            $cssUrl = '/hopec/board_templates/assets/board-theme-minimal.css?v=' . filemtime($cssPath);
            echo '<link rel="stylesheet" href="' . $cssUrl . '" />' . "\n";
        }
        
        // 3. 게시판 전용 추가 스타일링
        echo "<style id=\"board-theme-override\">\n";
        echo "/* 게시판 테마 적용 강제 */\n";
        echo "body { background: var(--background) !important; color: var(--foreground) !important; }\n";
        echo ".board_wrap, .board_container { background: inherit !important; }\n";
        echo ".bg-gray-50, .bg-gray-100, .bg-gray-200 { background: var(--background) !important; }\n";
        
        // Basic 테마인 경우 추가 스타일링
        if ($activeTheme === 'basic') {
            echo "/* Basic 테마 전용 게시판 스타일 */\n";
            echo "a { color: var(--primary) !important; }\n";
            echo "a:hover { color: var(--muted-foreground) !important; background-color: transparent !important; }\n";
            echo ".card a:hover, .post-card a:hover, .board-card a:hover { background-color: transparent !important; color: var(--foreground) !important; }\n";
            echo ".nav-button-hover:hover { background-color: var(--muted) !important; }\n";
            echo ".dropdown-menu a:hover { background-color: var(--muted) !important; }\n";
            echo ".btn, button, .button { background-color: var(--primary) !important; color: var(--primary-foreground) !important; }\n";
            echo ".btn:hover, button:hover, .button:hover { background-color: var(--secondary) !important; }\n";
        }
        
        echo "</style>\n";
    }
}

// 안전한 전역 함수
if (!function_exists('renderSafeBoardTheme')) {
    function renderSafeBoardTheme()
    {
        try {
            $integration = new SafeBoardThemeIntegration();
            $integration->renderCSS();
        } catch (Exception $e) {
            // 최종 폴백
            echo '<style>:root { --theme-primary: #3a7a4e; }</style>' . "\n";
            error_log('Safe board theme error: ' . $e->getMessage());
        }
    }
}
?>