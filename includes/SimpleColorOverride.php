<?php
/**
 * Simple Color Override System
 * Legacy Mode용 독립적 색상 오버라이드 시스템
 * 
 * - hopec_site_settings의 8개 Bootstrap 색상을 CSS 변수로 매핑
 * - globals.css를 fallback으로 사용
 * - 완전 독립적이며 언제든 비활성화 가능
 * 
 * Version: 1.0.0
 * Author: Claude Code Simple Color System
 */

// Include config helpers for get_table_name function
require_once __DIR__ . '/config_helpers.php';

class SimpleColorOverride {
    
    private $enabled = false;
    private $colors = [];
    private $pdo = null;
    
    /**
     * Bootstrap 색상 → CSS 변수 매핑
     */
    private $colorMapping = [
        'primary_color'   => '--primary',
        'secondary_color' => '--secondary', 
        'success_color'   => '--accent',
        'info_color'      => '--muted',
        'warning_color'   => '--warning',
        'danger_color'    => '--destructive',
        'light_color'     => '--background',
        'dark_color'      => '--foreground'
    ];
    
    /**
     * 기본 Natural Green 색상 (fallback)
     */
    private $defaultColors = [
        'primary_color'   => '#85E546',
        'secondary_color' => '#16a34a',
        'success_color'   => '#65a30d',
        'info_color'      => '#3a7a4e',
        'warning_color'   => '#a3e635',
        'danger_color'    => '#2b5d3e',
        'light_color'     => '#fafffe',
        'dark_color'      => '#1f3b2d'
    ];
    
    public function __construct() {
        $this->initializeDatabase();
        $this->enabled = $this->isColorOverrideEnabled();
        
        if ($this->enabled) {
            $this->loadColorsFromDatabase();
        }
    }
    
    /**
     * 데이터베이스 연결 초기화
     */
    private function initializeDatabase() {
        try {
            // 기존 DatabaseManager 사용 또는 직접 PDO 연결
            if (class_exists('DatabaseManager')) {
                // DatabaseManager가 있으면 사용
                $this->pdo = DatabaseManager::getConnection();
            } else {
                // 직접 PDO 연결 (fallback)
                require_once __DIR__ . '/db.php';
                $this->pdo = $GLOBALS['pdo'] ?? null;
            }
        } catch (Exception $e) {
            error_log("SimpleColorOverride DB connection failed: " . $e->getMessage());
            $this->enabled = false;
        }
    }
    
    /**
     * 색상 오버라이드 활성화 여부 확인
     */
    private function isColorOverrideEnabled() {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_value 
                FROM " . get_table_name('site_settings') . " 
                WHERE setting_key = 'color_override_enabled'
            ");
            $stmt->execute();
            $result = $stmt->fetchColumn();
            
            return $result === '1' || $result === 'true';
        } catch (Exception $e) {
            error_log("SimpleColorOverride enable check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 데이터베이스에서 색상 로드
     */
    private function loadColorsFromDatabase() {
        if (!$this->pdo) return;
        
        try {
            $colorKeys = array_keys($this->colorMapping);
            $placeholders = str_repeat('?,', count($colorKeys) - 1) . '?';
            
            $stmt = $this->pdo->prepare("
                SELECT setting_key, setting_value 
                FROM " . get_table_name('site_settings') . " 
                WHERE setting_key IN ($placeholders)
            ");
            $stmt->execute($colorKeys);
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // 유효한 색상만 저장
            foreach ($results as $key => $value) {
                if ($this->isValidColor($value)) {
                    $this->colors[$key] = $value;
                } else {
                    // 유효하지 않으면 기본값 사용
                    $this->colors[$key] = $this->defaultColors[$key] ?? '#000000';
                }
            }
            
        } catch (Exception $e) {
            error_log("SimpleColorOverride color loading failed: " . $e->getMessage());
            $this->colors = $this->defaultColors;
        }
    }
    
    /**
     * 색상값 유효성 검사
     */
    private function isValidColor($color) {
        // HEX 색상 형식 검사 (#RRGGBB 또는 #RGB)
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }
    
    /**
     * CSS 오버라이드 생성
     */
    public function generateOverrideCSS() {
        if (!$this->enabled || empty($this->colors)) {
            return '';
        }
        
        $css = "<style id=\"color-override\">\n:root {\n";
        
        foreach ($this->colorMapping as $dbKey => $cssVar) {
            if (isset($this->colors[$dbKey])) {
                $css .= "  {$cssVar}: {$this->colors[$dbKey]} !important;\n";
            }
        }
        
        $css .= "}\n</style>\n";
        return $css;
    }
    
    /**
     * 색상 오버라이드 활성화
     */
    public function enableOverride() {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO " . get_table_name('site_settings') . " (setting_key, setting_value, setting_group, setting_description)
                VALUES ('color_override_enabled', '1', 'theme', '색상 오버라이드 활성화')
                ON DUPLICATE KEY UPDATE setting_value = '1', updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute();
            
            $this->enabled = true;
            return true;
        } catch (Exception $e) {
            error_log("SimpleColorOverride enable failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 색상 오버라이드 비활성화 (globals.css로 복원)
     */
    public function disableOverride() {
        if (!$this->pdo) return false;
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE " . get_table_name('site_settings') . " 
                SET setting_value = '0', updated_at = CURRENT_TIMESTAMP
                WHERE setting_key = 'color_override_enabled'
            ");
            $stmt->execute();
            
            $this->enabled = false;
            return true;
        } catch (Exception $e) {
            error_log("SimpleColorOverride disable failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 색상 업데이트
     */
    public function updateColor($colorKey, $colorValue) {
        if (!$this->pdo || !isset($this->colorMapping[$colorKey])) {
            return false;
        }
        
        if (!$this->isValidColor($colorValue)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE " . get_table_name('site_settings') . " 
                SET setting_value = ?, updated_at = CURRENT_TIMESTAMP
                WHERE setting_key = ?
            ");
            $stmt->execute([$colorValue, $colorKey]);
            
            // 메모리에서도 업데이트
            $this->colors[$colorKey] = $colorValue;
            return true;
        } catch (Exception $e) {
            error_log("SimpleColorOverride color update failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 현재 색상 조회
     */
    public function getColors() {
        return $this->colors;
    }
    
    /**
     * 기본 색상으로 복원
     */
    public function resetToDefaults() {
        if (!$this->pdo) return false;
        
        try {
            $this->pdo->beginTransaction();
            
            foreach ($this->defaultColors as $key => $value) {
                $stmt = $this->pdo->prepare("
                    UPDATE " . get_table_name('site_settings') . " 
                    SET setting_value = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE setting_key = ?
                ");
                $stmt->execute([$value, $key]);
            }
            
            $this->pdo->commit();
            $this->colors = $this->defaultColors;
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("SimpleColorOverride reset failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 시스템 상태 확인
     */
    public function getStatus() {
        return [
            'enabled' => $this->enabled,
            'database_connected' => $this->pdo !== null,
            'colors_loaded' => count($this->colors),
            'mapping_count' => count($this->colorMapping)
        ];
    }
    
    /**
     * 디버그 정보 출력 (개발용)
     */
    public function renderDebugInfo() {
        if (!defined('HOPEC_DEBUG') || !HOPEC_DEBUG) {
            return '';
        }
        
        $status = $this->getStatus();
        $html = "<!-- Simple Color Override Debug -->\n";
        $html .= "<div id=\"color-override-debug\" style=\"position: fixed; top: 10px; left: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 9999;\">\n";
        $html .= "<strong>Color Override:</strong> " . ($status['enabled'] ? 'ON' : 'OFF') . "<br>\n";
        $html .= "<strong>Colors:</strong> " . $status['colors_loaded'] . "/" . $status['mapping_count'] . "<br>\n";
        $html .= "<strong>DB:</strong> " . ($status['database_connected'] ? 'OK' : 'FAIL') . "<br>\n";
        $html .= "<small>더블클릭하여 숨기기</small>\n";
        $html .= "</div>\n";
        $html .= "<script>document.getElementById('color-override-debug')?.addEventListener('dblclick', function(){ this.style.display='none'; });</script>\n";
        
        return $html;
    }
}

// 전역 인스턴스 생성 (필요시 사용)
if (!isset($GLOBALS['simpleColorOverride'])) {
    $GLOBALS['simpleColorOverride'] = new SimpleColorOverride();
}

// 헬퍼 함수
if (!function_exists('getSimpleColorOverride')) {
    function getSimpleColorOverride() {
        return $GLOBALS['simpleColorOverride'];
    }
}