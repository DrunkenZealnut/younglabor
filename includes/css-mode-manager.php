<?php
/**
 * CSS 모드 관리 시스템
 * 기존 시스템과 최적화된 시스템 간 안전한 전환 관리
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

class CSSModeManager {
    
    const MODE_LEGACY = 'legacy';      // 기존 Bootstrap + Tailwind
    const MODE_OPTIMIZED = 'optimized'; // 최적화된 통합 시스템
    const MODE_DEBUG = 'debug';        // 개발자 디버그 모드
    
    private $currentMode;
    private $config;
    
    public function __construct() {
        $this->config = [
            'default_mode' => self::MODE_LEGACY,  // 안전한 기본값으로 복구
            'cookie_name' => 'hopec_css_mode',
            'cookie_lifetime' => 86400 * 7,      // 7일
            'debug_enabled' => defined('HOPEC_DEBUG') && HOPEC_DEBUG,
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
        $validModes = [self::MODE_LEGACY, self::MODE_OPTIMIZED, self::MODE_DEBUG];
        return in_array($mode, $validModes);
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
        return $this->currentMode;
    }
    
    /**
     * 최적화 모드 여부 확인
     */
    public function isOptimizedMode() {
        return $this->currentMode === self::MODE_OPTIMIZED;
    }
    
    /**
     * 레거시 모드 여부 확인
     */
    public function isLegacyMode() {
        return $this->currentMode === self::MODE_LEGACY;
    }
    
    /**
     * 디버그 모드 여부 확인
     */
    public function isDebugMode() {
        return $this->currentMode === self::MODE_DEBUG;
    }
    
    /**
     * 모드 전환 URL 생성
     */
    public function getSwitchUrl($targetMode, $currentUrl = null) {
        if (!$this->isValidMode($targetMode)) {
            return null;
        }
        
        if ($currentUrl === null) {
            $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        }
        
        // 기존 css_mode 파라미터 제거
        $urlParts = parse_url($currentUrl);
        $path = $urlParts['path'] ?? '/';
        
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $params);
            unset($params['css_mode']);
            $params['css_mode'] = $targetMode;
            $query = http_build_query($params);
        } else {
            $query = 'css_mode=' . $targetMode;
        }
        
        return $path . '?' . $query;
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
            'current_mode' => $this->currentMode,
            'is_optimized' => $this->isOptimizedMode(),
            'is_legacy' => $this->isLegacyMode(),
            'is_debug' => $this->isDebugMode(),
            'switch_urls' => [
                'legacy' => $this->getSwitchUrl(self::MODE_LEGACY),
                'optimized' => $this->getSwitchUrl(self::MODE_OPTIMIZED),
                'debug' => $this->getSwitchUrl(self::MODE_DEBUG)
            ],
            'emergency_url' => $this->getEmergencyUrl()
        ];
    }
    
    /**
     * 디버그 정보 출력 (개발 모드에서만)
     */
    public function renderDebugInfo() {
        if (!$this->config['debug_enabled']) {
            return;
        }
        
        $info = $this->getModeInfo();
        
        echo "<!-- CSS Mode Debug Info -->\n";
        echo "<div id=\"css-mode-debug\" style=\"position: fixed; top: 10px; right: 10px; background: #000; color: #fff; padding: 10px; z-index: 9999; font-size: 12px; border-radius: 5px;\">\n";
        echo "<strong>CSS Mode:</strong> {$info['current_mode']}<br>\n";
        echo "<a href=\"{$info['switch_urls']['legacy']}\" style=\"color: #ff6b6b;\">Legacy</a> | ";
        echo "<a href=\"{$info['switch_urls']['optimized']}\" style=\"color: #51cf66;\">Optimized</a> | ";
        echo "<a href=\"{$info['switch_urls']['debug']}\" style=\"color: #74c0fc;\">Debug</a><br>";
        echo "<small><a href=\"{$info['emergency_url']}\" style=\"color: #ffa502;\">Emergency Reset</a></small>\n";
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
    
    /**
     * 성능 메트릭 수집 시작
     */
    public function startPerformanceTracking() {
        if ($this->isDebugMode()) {
            echo "<script>\n";
            echo "window.hopecCSSPerformance = {\n";
            echo "  mode: '{$this->currentMode}',\n";
            echo "  startTime: performance.now(),\n";
            echo "  metrics: {}\n";
            echo "};\n";
            echo "</script>\n";
        }
    }
    
    /**
     * 성능 메트릭 수집 종료
     */
    public function endPerformanceTracking() {
        if ($this->isDebugMode()) {
            echo "<script>\n";
            echo "if (window.hopecCSSPerformance) {\n";
            echo "  window.hopecCSSPerformance.endTime = performance.now();\n";
            echo "  window.hopecCSSPerformance.totalTime = window.hopecCSSPerformance.endTime - window.hopecCSSPerformance.startTime;\n";
            echo "  console.log('🎨 CSS Mode Performance:', window.hopecCSSPerformance);\n";
            echo "}\n";
            echo "</script>\n";
        }
    }
}

// 전역 인스턴스 생성 (필요시 사용)
if (!isset($GLOBALS['cssMode'])) {
    $GLOBALS['cssMode'] = new CSSModeManager();
}

// 헬퍼 함수들
if (!function_exists('getCSSMode')) {
    function getCSSMode() {
        return $GLOBALS['cssMode'];
    }
}

if (!function_exists('isOptimizedCSS')) {
    function isOptimizedCSS() {
        return getCSSMode()->isOptimizedMode();
    }
}

if (!function_exists('isLegacyCSS')) {
    function isLegacyCSS() {
        return getCSSMode()->isLegacyMode();
    }
}