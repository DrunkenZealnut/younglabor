<?php
/**
 * Global Theme Integration Service
 * GlobalThemeLoader와 기존 ThemeManager를 통합하는 서비스
 */

require_once __DIR__ . '/../../theme/globals/config/theme-loader.php';
require_once __DIR__ . '/ThemeManager.php';

class GlobalThemeIntegration
{
    private $pdo;
    private $themeManager;
    private $globalThemeLoader;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->themeManager = new ThemeManager($pdo);
        $this->globalThemeLoader = new GlobalThemeLoader();
    }
    
    /**
     * 통합된 테마 목록 가져오기 (기존 테마 + 글로벌 테마)
     */
    public function getAllThemes()
    {
        $themes = [];
        
        // 1. 기존 ThemeManager의 테마들
        $existingThemes = $this->themeManager->getAvailableThemes();
        foreach ($existingThemes as $themeName => $themeInfo) {
            $themes[$themeName] = array_merge($themeInfo, [
                'type' => 'traditional',
                'source' => 'theme_folder',
                'css_file' => $themeInfo['path'] . '/styles/globals.css',
                'preview_url' => $this->getThemePreviewUrl($themeName),
                'can_delete' => $themeName !== 'natural-green' // 기본 테마는 삭제 불가
            ]);
        }
        
        // 2. GlobalThemeLoader의 글로벌 테마들
        $globalThemes = $this->globalThemeLoader->getAvailableThemes();
        foreach ($globalThemes as $themeName => $themeInfo) {
            // natural-green은 이미 추가되었으므로 스킵
            if ($themeName === 'natural-green') continue;
            
            $themes[$themeName] = [
                'name' => $themeName,
                'display_name' => $themeInfo['display_name'],
                'description' => '글로벌 테마 - ' . $themeInfo['display_name'],
                'version' => '1.0.0',
                'author' => 'Global Theme System',
                'type' => 'global',
                'source' => 'globals_css',
                'css_file' => $themeInfo['path'],
                'preview_url' => $this->getGlobalThemePreviewUrl($themeName),
                'can_delete' => true,
                'path' => dirname($themeInfo['path'])
            ];
        }
        
        return $themes;
    }
    
    /**
     * 활성 테마 설정 (통합)
     */
    public function setActiveTheme($themeName)
    {
        // 글로벌 테마인지 확인
        $globalThemes = $this->globalThemeLoader->getAvailableThemes();
        $existingThemes = $this->themeManager->getAvailableThemes();
        
        if (isset($globalThemes[$themeName]) || isset($existingThemes[$themeName])) {
            // DB에 저장
            $stmt = $this->pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = 'active_theme'");
            $result = $stmt->execute([$themeName]);
            
            // 세션에도 저장 (GlobalThemeLoader용)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['selected_theme'] = $themeName;
            
            return $result;
        }
        
        throw new Exception("테마 '{$themeName}'을 찾을 수 없습니다.");
    }
    
    /**
     * 현재 활성 테마 가져오기 (통합)
     */
    public function getActiveTheme()
    {
        // DB에서 직접 확인 (ThemeManager를 거치지 않음으로써 global theme 지원)
        $stmt = $this->pdo->prepare("SELECT setting_value FROM hopec_site_settings WHERE setting_key = 'active_theme'");
        $stmt->execute();
        $dbTheme = $stmt->fetchColumn() ?: 'natural-green';
        
        // 세션에서 확인 (GlobalThemeLoader 우선순위)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['selected_theme'])) {
            $sessionTheme = $_SESSION['selected_theme'];
            
            // 세션 테마가 유효한지 확인 (통합 테마 목록 사용)
            $allThemes = $this->getAllThemes();
            if (isset($allThemes[$sessionTheme])) {
                // DB와 세션이 다르면 DB 업데이트
                if ($dbTheme !== $sessionTheme) {
                    try {
                        $stmt = $this->pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = 'active_theme'");
                        $stmt->execute([$sessionTheme]);
                        $dbTheme = $sessionTheme;
                    } catch (Exception $e) {
                        error_log("Failed to update active theme from session: " . $e->getMessage());
                    }
                }
                return $sessionTheme;
            } else {
                // 세션에 존재하지 않는 테마가 있으면 제거
                unset($_SESSION['selected_theme']);
                error_log("Warning: Session theme '{$sessionTheme}' not found in integrated themes, cleared from session");
            }
        }
        
        // 최종 안전성 확인 (통합 테마 목록 사용)
        $allThemes = $this->getAllThemes();
        
        if (!isset($allThemes[$dbTheme])) {
            error_log("Warning: DB theme '{$dbTheme}' not found in integrated themes, using natural-green");
            
            // DB도 natural-green으로 업데이트
            try {
                $stmt = $this->pdo->prepare("UPDATE hopec_site_settings SET setting_value = 'natural-green' WHERE setting_key = 'active_theme'");
                $stmt->execute();
            } catch (Exception $e) {
                error_log("Failed to update active theme to natural-green: " . $e->getMessage());
            }
            
            return 'natural-green';
        }
        
        return $dbTheme;
    }
    
    /**
     * 새로운 글로벌 테마 등록
     */
    public function registerGlobalTheme($themeName, $cssContent)
    {
        // 테마명 검증
        if (empty($themeName) || !preg_match('/^[a-zA-Z0-9_-]+$/', $themeName)) {
            throw new Exception('유효하지 않은 테마명입니다. 영문, 숫자, 하이픈, 밑줄만 사용 가능합니다.');
        }
        
        // 기존 테마와 중복 확인
        $allThemes = $this->getAllThemes();
        if (isset($allThemes[$themeName])) {
            throw new Exception('이미 존재하는 테마명입니다.');
        }
        
        // CSS 내용 검증
        $this->validateCssContent($cssContent);
        
        // 글로벌 테마 파일 저장
        $globalThemePath = __DIR__ . '/../../theme/globals/styles/global_' . $themeName . '.css';
        
        if (file_exists($globalThemePath)) {
            throw new Exception('해당 이름의 글로벌 테마 파일이 이미 존재합니다.');
        }
        
        $result = file_put_contents($globalThemePath, $cssContent);
        
        if ($result === false) {
            throw new Exception('테마 파일 저장에 실패했습니다.');
        }
        
        return $themeName;
    }
    
    /**
     * 테마 삭제 (통합)
     */
    public function deleteTheme($themeName)
    {
        // 기본 테마는 삭제 불가
        if ($themeName === 'natural-green') {
            throw new Exception('기본 테마는 삭제할 수 없습니다.');
        }
        
        // 현재 활성 테마인 경우 삭제 불가
        if ($this->getActiveTheme() === $themeName) {
            throw new Exception('현재 사용 중인 테마는 삭제할 수 없습니다.');
        }
        
        $allThemes = $this->getAllThemes();
        if (!isset($allThemes[$themeName])) {
            throw new Exception('존재하지 않는 테마입니다.');
        }
        
        $theme = $allThemes[$themeName];
        
        // 글로벌 테마인 경우
        if ($theme['type'] === 'global') {
            $globalThemePath = __DIR__ . '/../../theme/globals/styles/global_' . $themeName . '.css';
            if (file_exists($globalThemePath)) {
                return unlink($globalThemePath);
            }
        }
        // 기존 테마인 경우
        else {
            return $this->themeManager->deleteTheme($themeName);
        }
        
        return false;
    }
    
    /**
     * 테마 미리보기 URL 생성
     */
    public function getThemePreviewUrl($themeName)
    {
        return '/admin/theme-preview.php?theme=' . urlencode($themeName);
    }
    
    /**
     * 글로벌 테마 미리보기 URL 생성
     */
    public function getGlobalThemePreviewUrl($themeName)
    {
        return '/theme-test.php?theme=' . urlencode($themeName);
    }
    
    /**
     * 테마 통계 정보
     */
    public function getThemeStats()
    {
        $allThemes = $this->getAllThemes();
        $stats = [
            'total' => count($allThemes),
            'traditional' => 0,
            'global' => 0,
            'active_theme' => $this->getActiveTheme(),
            'theme_types' => []
        ];
        
        foreach ($allThemes as $theme) {
            $stats[$theme['type']]++;
            $stats['theme_types'][] = [
                'name' => $theme['name'],
                'type' => $theme['type'],
                'display_name' => $theme['display_name']
            ];
        }
        
        return $stats;
    }
    
    /**
     * CSS 내용 검증 (ThemeManager에서 가져옴)
     */
    private function validateCssContent($cssContent)
    {
        // 악성 코드 패턴 검사
        $dangerousPatterns = [
            '/javascript:/i',
            '/data:/i',
            '/expression\s*\(/i',
            '/@import\s+url\s*\(/i',
            '/binding\s*:/i',
            '/behavior\s*:/i',
            '/<script/i',
            '/<iframe/i',
            '/vbscript:/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $cssContent)) {
                throw new Exception('CSS 파일에 허용되지 않는 내용이 포함되어 있습니다.');
            }
        }
        
        // CSS 변수 구조 검증 (:root 블록 존재 여부)
        if (!preg_match('/:root\s*{[^}]*}/s', $cssContent)) {
            throw new Exception('CSS 파일에 :root 변수 선언이 필요합니다.');
        }
        
        return true;
    }
    
    /**
     * 테마 백업 (통합)
     */
    public function backupThemes()
    {
        $backup = [
            'timestamp' => date('Y-m-d H:i:s'),
            'active_theme' => $this->getActiveTheme(),
            'all_themes' => $this->getAllThemes(),
            'global_themes' => $this->globalThemeLoader->getAvailableThemes(),
            'traditional_themes' => $this->themeManager->getAvailableThemes(),
            'theme_stats' => $this->getThemeStats()
        ];
        
        $filename = 'integrated-themes-backup-' . date('Ymd-His') . '.json';
        $backupDir = __DIR__ . '/../../uploads/theme/backups';
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $filepath = $backupDir . '/' . $filename;
        $result = file_put_contents($filepath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        if ($result === false) {
            throw new Exception('백업 파일 생성에 실패했습니다.');
        }
        
        return $filename;
    }
    
    /**
     * 테마 가져오기 (JSON 백업에서)
     */
    public function importThemes($backupFile)
    {
        if (!file_exists($backupFile)) {
            throw new Exception('백업 파일을 찾을 수 없습니다.');
        }
        
        $backupData = json_decode(file_get_contents($backupFile), true);
        
        if (!$backupData || !isset($backupData['global_themes'])) {
            throw new Exception('유효하지 않은 백업 파일입니다.');
        }
        
        $imported = 0;
        $errors = [];
        
        // 글로벌 테마들만 가져오기 (기존 테마는 폴더 구조가 복잡해서 제외)
        foreach ($backupData['global_themes'] as $themeName => $themeInfo) {
            if ($themeName === 'natural-green') continue; // 기본 테마 제외
            
            try {
                // 글로벌 테마 파일이 백업에 포함되어 있는지 확인
                // 실제로는 CSS 파일을 별도로 백업해야 함
                $imported++;
            } catch (Exception $e) {
                $errors[] = $themeName . ': ' . $e->getMessage();
            }
        }
        
        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }
}