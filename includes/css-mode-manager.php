<?php
/**
 * CSS 모드 관리 시스템
 * 기존 시스템과 최적화된 시스템 간 안전한 전환 관리
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

class CSSModeManager {
    
    const MODE_LEGACY = 'legacy';      // 기존 Bootstrap + Tailwind (유일한 모드)
    
    private $currentMode;
    private $config;
    
    public function __construct() {
        $this->config = [
            'default_mode' => self::MODE_LEGACY,  // Legacy 모드를 기본값으로 설정
            'cookie_name' => 'younglabor_css_mode',
            'cookie_lifetime' => 86400 * 7,      // 7일
            'debug_enabled' => defined('younglabor_DEBUG') && younglabor_DEBUG,
            'emergency_mode' => self::MODE_LEGACY
        ];
        
        $this->currentMode = $this->detectMode();
    }
    
    /**
     * 현재 CSS 모드 감지
     * 우선순위: URL 파라미터 > 쿠키 > 기본값
     */
    private function detectMode() {
        // 1. URL 파라미터 확인 (최우선)
        if (isset($_GET['css_mode'])) {
            $urlMode = $_GET['css_mode'];
            if ($this->isValidMode($urlMode)) {
                // 유효한 모드면 쿠키에도 저장
                $this->setCookieMode($urlMode);
                return $urlMode;
            }
        }
        
        // 2. 긴급 모드 확인
        if (isset($_GET['emergency']) && $_GET['emergency'] === 'true') {
            $this->setCookieMode(self::MODE_LEGACY);
            $this->logEmergencyMode();
            return self::MODE_LEGACY;
        }
        
        // 3. 쿠키 확인
        if (isset($_COOKIE[$this->config['cookie_name']])) {
            $cookieMode = $_COOKIE[$this->config['cookie_name']];
            if ($this->isValidMode($cookieMode)) {
                return $cookieMode;
            }
        }
        
        // 4. 기본값 반환
        return $this->config['default_mode'];
    }
    
    /**
     * 모드 유효성 검증
     */
    private function isValidMode($mode) {
        return $mode === self::MODE_LEGACY;
    }
    
    /**
     * 쿠키에 모드 저장
     */
    private function setCookieMode($mode) {
        if ($this->isValidMode($mode)) {
            setcookie(
                $this->config['cookie_name'], 
                $mode, 
                time() + $this->config['cookie_lifetime'],
                '/',
                '',
                false, // HTTPS only = false (개발환경 고려)
                true   // HTTP only = true (XSS 보안)
            );
        }
    }
    
    /**
     * 현재 모드 반환
     */
    public function getCurrentMode() {
        return self::MODE_LEGACY; // 항상 Legacy 모드
    }
    
    /**
     * 레거시 모드 여부 확인 (항상 true)
     */
    public function isLegacyMode() {
        return true;
    }
    
    /**
     * 모드 전환 URL 생성 (Legacy 모드만 지원)
     */
    public function getSwitchUrl($targetMode, $currentUrl = null) {
        // Legacy 모드만 지원
        return $currentUrl ?? $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    /**
     * 긴급 복구 URL 생성
     */
    public function getEmergencyUrl($currentUrl = null) {
        if ($currentUrl === null) {
            $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        }
        
        $urlParts = parse_url($currentUrl);
        $path = $urlParts['path'] ?? '/';
        
        return $path . '?css_mode=legacy&emergency=true';
    }
    
    /**
     * 모드 정보 배열 반환
     */
    public function getModeInfo() {
        return [
            'current_mode' => self::MODE_LEGACY,
            'is_legacy' => true,
            'emergency_url' => $this->getEmergencyUrl()
        ];
    }
    
    /**
     * 디버그 정보 출력 (Legacy 모드)
     */
    public function renderDebugInfo() {
        if (!$this->config['debug_enabled']) {
            return;
        }
        
        echo "<!-- CSS Mode: Legacy Only -->\n";
        echo "<div id=\"css-mode-debug\" style=\"position: fixed; top: 10px; right: 10px; background: #000; color: #fff; padding: 10px; z-index: 9999; font-size: 12px; border-radius: 5px;\">\n";
        echo "<strong>CSS Mode:</strong> Legacy<br>\n";
        echo "<small>Legacy 모드만 지원됩니다</small>\n";
        echo "</div>\n";
    }
    
    /**
     * 긴급 모드 활성화 로깅
     */
    private function logEmergencyMode() {
        $logMessage = sprintf(
            "[%s] Emergency CSS mode activated from IP: %s, User-Agent: %s",
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        );
        
        error_log($logMessage);
    }
    
}

// 전역 인스턴스 생성 (필요시 사용)
if (!isset($GLOBALS['cssMode'])) {
    $GLOBALS['cssMode'] = new CSSModeManager();
}

// 헬퍼 함수들 (Legacy 전용)
if (!function_exists('getCSSMode')) {
    function getCSSMode() {
        return $GLOBALS['cssMode'];
    }
}

if (!function_exists('isLegacyCSS')) {
    function isLegacyCSS() {
        return true; // 항상 Legacy 모드
    }
}