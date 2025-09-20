<?php
/**
 * 임시 ThemeManager 클래스
 * site_settings.php의 500 에러를 해결하기 위한 최소한의 구현
 */

class ThemeManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function clearAllCache() {
        // 캐시 클리어 로직 (임시로 true 반환)
        return true;
    }
    
    public function setActiveTheme($theme) {
        // 활성 테마 설정 (기본 구현)
        try {
            $stmt = $this->pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = 'active_theme'");
            return $stmt->execute([$theme]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateThemeConfigOverride($overrides) {
        // 테마 설정 오버라이드 업데이트 (임시 구현)
        return true;
    }
    
    public function saveDynamicCSS() {
        // 동적 CSS 저장 (임시 구현)
        return true;
    }
    
    public function registerNewTheme($file, $name) {
        // 새 테마 등록 (임시 구현)
        return $name;
    }
    
    public function deleteTheme($theme) {
        // 테마 삭제 (임시 구현)
        return true;
    }
    
    public function getAvailableThemes() {
        // 사용 가능한 테마 목록 반환
        return [
            'natural-green' => [
                'name' => 'Natural Green',
                'version' => '1.0.0',
                'description' => '자연스러운 초록 테마'
            ]
        ];
    }
    
    public function getActiveTheme() {
        // 활성 테마 반환
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM hopec_site_settings WHERE setting_key = 'active_theme' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result ?: 'natural-green';
        } catch (PDOException $e) {
            return 'natural-green';
        }
    }
    
    public function getMergedThemeConfig($theme = null) {
        // 병합된 테마 설정 반환
        return [
            'primary_color' => '#84cc16',
            'secondary_color' => '#22c55e',
            'background_color' => '#ffffff',
            'text_color' => '#333333'
        ];
    }
    
    public function getThemeConfigOverride() {
        // 테마 설정 오버라이드 반환
        return [];
    }
    
    public function validateThemeStructure($theme) {
        // 테마 구조 검증 (임시 구현)
        return [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];
    }
    
    public function getThemesDir() {
        // 테마 디렉토리 경로 반환
        return realpath(__DIR__ . '/../../theme');
    }
    
    public function getThemePreviewUrl($theme) {
        // 테마 미리보기 URL 반환
        return '/admin/theme_preview.php?theme=' . urlencode($theme);
    }
}
?>