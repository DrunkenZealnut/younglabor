<?php
/**
 * CSS 안전장치 및 폴백 시스템
 * 최적화된 CSS 시스템의 오류 감지 및 자동 복구
 * 
 * Version: 1.0.0
 * Author: SuperClaude CSS Optimization System
 */

class CSSFallback {
    
    private $errorLog = [];
    private $fallbackTriggered = false;
    private $emergencyMode = false;
    
    const ERROR_CRITICAL_CSS_MISSING = 'critical_css_missing';
    const ERROR_CRITICAL_CSS_TOO_SMALL = 'critical_css_too_small';
    const ERROR_NATURAL_GREEN_MISSING = 'natural_green_missing';
    const ERROR_JAVASCRIPT_FAILURE = 'javascript_failure';
    const ERROR_PERFORMANCE_DEGRADATION = 'performance_degradation';
    
    public function __construct() {
        // 긴급 모드 감지
        if (isset($_GET['emergency']) && $_GET['emergency'] === 'true') {
            $this->activateEmergencyMode();
        }
        
        // 기존 오류 로그 확인
        $this->loadErrorHistory();
    }
    
    /**
     * Critical CSS 상태 검증
     */
    public function validateCriticalCSS($criticalCSS) {
        $errors = [];
        
        // 1. CSS 존재 여부 확인
        if (empty($criticalCSS)) {
            $errors[] = self::ERROR_CRITICAL_CSS_MISSING;
            $this->logError(self::ERROR_CRITICAL_CSS_MISSING, 'Critical CSS is empty or null');
        }
        
        // 2. CSS 크기 확인 (최소 1KB 이상이어야 함)
        if (strlen($criticalCSS) < 1024) {
            $errors[] = self::ERROR_CRITICAL_CSS_TOO_SMALL;
            $this->logError(self::ERROR_CRITICAL_CSS_TOO_SMALL, 'Critical CSS size: ' . strlen($criticalCSS) . ' bytes');
        }
        
        // 3. 필수 CSS 변수 존재 확인
        $requiredVariables = ['--primary', '--background', '--foreground'];
        foreach ($requiredVariables as $variable) {
            if (strpos($criticalCSS, $variable) === false) {
                $errors[] = 'missing_variable_' . str_replace('--', '', $variable);
                $this->logError('missing_css_variable', "Required CSS variable {$variable} not found");
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'size' => strlen($criticalCSS),
            'size_kb' => round(strlen($criticalCSS) / 1024, 2)
        ];
    }
    
    /**
     * Natural Green 테마 상태 검증
     */
    public function validateNaturalGreenTheme() {
        $themePath = dirname(__DIR__) . '/theme/natural-green/styles/globals.css';
        $loaderPath = dirname(__DIR__) . '/includes/NaturalGreenThemeLoader.php';
        
        $errors = [];
        
        if (!file_exists($themePath)) {
            $errors[] = self::ERROR_NATURAL_GREEN_MISSING;
            $this->logError(self::ERROR_NATURAL_GREEN_MISSING, "Theme file not found: {$themePath}");
        }
        
        if (!file_exists($loaderPath)) {
            $errors[] = 'natural_green_loader_missing';
            $this->logError('natural_green_loader_missing', "Loader file not found: {$loaderPath}");
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'theme_path' => $themePath,
            'loader_path' => $loaderPath,
            'theme_exists' => file_exists($themePath),
            'loader_exists' => file_exists($loaderPath)
        ];
    }
    
    /**
     * 폴백 모드 활성화
     */
    public function activateFallback($reason = 'unknown') {
        if ($this->fallbackTriggered) {
            return; // 이미 활성화됨
        }
        
        $this->fallbackTriggered = true;
        $this->logError('fallback_activated', "Fallback activated: {$reason}");
        
        // 쿠키에 폴백 상태 저장
        setcookie('css_fallback_active', 'true', time() + 3600, '/');
        setcookie('css_fallback_reason', $reason, time() + 3600, '/');
        
        // 관리자에게 알림 (로그)
        error_log("HOPEC CSS Fallback Activated: {$reason} at " . date('Y-m-d H:i:s'));
        
        return true;
    }
    
    /**
     * 긴급 모드 활성화
     */
    public function activateEmergencyMode() {
        $this->emergencyMode = true;
        $this->fallbackTriggered = true;
        
        // 강제로 Legacy 모드로 전환
        setcookie('hopec_css_mode', 'legacy', time() + 86400 * 7, '/');
        setcookie('css_emergency_mode', 'true', time() + 3600, '/');
        
        $this->logError('emergency_mode_activated', 'Emergency mode activated by user request');
        
        // 관리자 이메일 알림 (구현 시)
        $this->notifyAdministrator('긴급 모드 활성화', 'CSS 최적화 시스템이 긴급 모드로 전환되었습니다.');
        
        return true;
    }
    
    /**
     * 자동 복구 시도
     */
    public function attemptAutoRecovery() {
        $recoverySteps = [];
        
        // 1. Critical CSS 재생성 시도
        try {
            require_once __DIR__ . '/critical-css-generator.php';
            $generator = new CriticalCSSGenerator();
            $newCSS = $generator->generateCriticalCSS();
            
            if ($this->validateCriticalCSS($newCSS)['valid']) {
                $recoverySteps[] = 'critical_css_regenerated';
            }
        } catch (Exception $e) {
            $this->logError('recovery_failed', 'Failed to regenerate critical CSS: ' . $e->getMessage());
        }
        
        // 2. 캐시 클리어 시도
        try {
            if (class_exists('CriticalCSSGenerator')) {
                $generator = new CriticalCSSGenerator();
                $cleared = $generator->clearCache();
                $recoverySteps[] = "cache_cleared_{$cleared}_files";
            }
        } catch (Exception $e) {
            $this->logError('recovery_failed', 'Failed to clear cache: ' . $e->getMessage());
        }
        
        // 3. Natural Green 테마 확인
        $themeValidation = $this->validateNaturalGreenTheme();
        if ($themeValidation['valid']) {
            $recoverySteps[] = 'natural_green_verified';
        }
        
        return [
            'success' => !empty($recoverySteps),
            'steps' => $recoverySteps,
            'timestamp' => time()
        ];
    }
    
    /**
     * 폴백 HTML 생성
     */
    public function generateFallbackHTML() {
        return '
        <!-- CSS 폴백 시스템 활성화됨 -->
        <meta name="css-fallback" content="active">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- 폴백 알림 -->
        <script>
        console.warn("⚠️ CSS 최적화 시스템이 폴백 모드로 전환되었습니다.");
        console.log("🔧 복구를 시도하려면 다음 URL을 방문하세요: /test/css-performance-test.php");
        </script>
        ';
    }
    
    /**
     * 클라이언트 사이드 오류 감지 JavaScript
     */
    public function generateErrorDetectionJS() {
        return '
        <script>
        (function() {
            "use strict";
            
            // CSS 로딩 오류 감지
            let cssErrorDetected = false;
            
            // Critical CSS 존재 및 크기 확인
            function checkCriticalCSS() {
                const criticalCSS = document.getElementById("hopec-critical-css");
                
                if (!criticalCSS) {
                    console.error("❌ Critical CSS 요소를 찾을 수 없습니다.");
                    return false;
                }
                
                if (criticalCSS.textContent.length < 1000) {
                    console.error("❌ Critical CSS가 너무 작습니다:", criticalCSS.textContent.length, "bytes");
                    return false;
                }
                
                console.log("✅ Critical CSS 정상:", criticalCSS.textContent.length, "bytes");
                return true;
            }
            
            // CSS 변수 확인
            function checkCSSVariables() {
                const root = document.documentElement;
                const requiredVars = ["--primary", "--background", "--foreground"];
                
                for (const variable of requiredVars) {
                    const value = getComputedStyle(root).getPropertyValue(variable);
                    if (!value || value.trim() === "") {
                        console.error("❌ CSS 변수 누락:", variable);
                        return false;
                    }
                }
                
                console.log("✅ CSS 변수 정상");
                return true;
            }
            
            // 폰트 로딩 확인
            function checkFontLoading() {
                const bodyStyle = getComputedStyle(document.body);
                const fontFamily = bodyStyle.fontFamily;
                
                if (fontFamily.indexOf("Noto Sans KR") === -1) {
                    console.warn("⚠️ Noto Sans KR 폰트가 로드되지 않았습니다.");
                    return false;
                }
                
                console.log("✅ 폰트 로딩 정상");
                return true;
            }
            
            // 종합 검사 실행
            function runCSSSafetyCheck() {
                const checks = [
                    checkCriticalCSS(),
                    checkCSSVariables(),
                    checkFontLoading()
                ];
                
                const passedChecks = checks.filter(Boolean).length;
                const totalChecks = checks.length;
                
                console.log(`📊 CSS 안전성 검사: ${passedChecks}/${totalChecks} 통과`);
                
                if (passedChecks < totalChecks) {
                    console.warn("⚠️ CSS 시스템에 문제가 감지되었습니다.");
                    
                    // 폴백 모드로 전환 제안
                    if (passedChecks === 0) {
                        console.error("❌ 심각한 CSS 오류 감지. 폴백 모드 권장.");
                        
                        // 자동 폴백 활성화 (옵션)
                        if (confirm("CSS 로딩에 실패했습니다. 안전 모드로 전환하시겠습니까?")) {
                            window.location.href = "?css_mode=legacy&emergency=true";
                        }
                    }
                }
                
                return passedChecks === totalChecks;
            }
            
            // 페이지 로드 완료 후 검사 실행
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(runCSSSafetyCheck, 1000);
            });
            
            // 전역 오류 핸들러
            window.addEventListener("error", function(event) {
                if (event.target && event.target.tagName === "LINK" && event.target.rel === "stylesheet") {
                    console.error("❌ CSS 파일 로딩 실패:", event.target.href);
                    cssErrorDetected = true;
                }
            });
            
            // 네트워크 오류 감지
            window.addEventListener("online", function() {
                if (cssErrorDetected) {
                    console.log("🔄 네트워크 복구됨. 페이지 새로고침을 권장합니다.");
                }
            });
            
        })();
        </script>
        ';
    }
    
    /**
     * 오류 로깅
     */
    private function logError($type, $message) {
        $this->errorLog[] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'url' => $_SERVER['REQUEST_URI'] ?? ''
        ];
        
        // 파일에 로그 저장
        $logFile = dirname(__DIR__) . '/logs/css-errors.log';
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = sprintf(
            "[%s] %s: %s (IP: %s, URL: %s)\n",
            date('Y-m-d H:i:s'),
            $type,
            $message,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? 'unknown'
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 오류 기록 로드
     */
    private function loadErrorHistory() {
        $logFile = dirname(__DIR__) . '/logs/css-errors.log';
        
        if (file_exists($logFile)) {
            $logs = file($logFile, FILE_IGNORE_NEW_LINES);
            $recentLogs = array_slice($logs, -10); // 최근 10개만
            
            // 최근 1시간 내 오류 빈도 확인
            $oneHourAgo = time() - 3600;
            $recentErrors = 0;
            
            foreach ($recentLogs as $log) {
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $log, $matches)) {
                    $logTime = strtotime($matches[1]);
                    if ($logTime > $oneHourAgo) {
                        $recentErrors++;
                    }
                }
            }
            
            // 오류 빈도가 높으면 자동으로 폴백 모드 권장
            if ($recentErrors > 5) {
                $this->logError('high_error_frequency', "High error frequency detected: {$recentErrors} errors in last hour");
            }
        }
    }
    
    /**
     * 관리자 알림
     */
    private function notifyAdministrator($subject, $message) {
        // 이메일 알림 (실제 구현에서는 이메일 설정 필요)
        $adminEmail = 'admin@hopec.or.kr'; // 실제 관리자 이메일로 변경
        
        $fullMessage = sprintf(
            "HOPEC CSS 시스템 알림\n\n%s\n\n시간: %s\nIP: %s\nURL: %s\n\n자세한 내용은 로그를 확인해주세요.",
            $message,
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? 'unknown'
        );
        
        // 실제 환경에서는 mail() 함수나 다른 이메일 서비스 사용
        // mail($adminEmail, $subject, $fullMessage);
        
        // 개발 환경에서는 로그로 대체
        error_log("Admin Notification: {$subject} - {$message}");
    }
    
    /**
     * 상태 정보 반환
     */
    public function getStatus() {
        return [
            'fallback_triggered' => $this->fallbackTriggered,
            'emergency_mode' => $this->emergencyMode,
            'error_count' => count($this->errorLog),
            'recent_errors' => array_slice($this->errorLog, -5),
            'cookies' => [
                'fallback_active' => $_COOKIE['css_fallback_active'] ?? null,
                'fallback_reason' => $_COOKIE['css_fallback_reason'] ?? null,
                'emergency_mode' => $_COOKIE['css_emergency_mode'] ?? null
            ]
        ];
    }
}

// 전역 폴백 시스템 초기화
if (!isset($GLOBALS['cssFallback'])) {
    $GLOBALS['cssFallback'] = new CSSFallback();
}

// 헬퍼 함수들
if (!function_exists('getCSSFallback')) {
    function getCSSFallback() {
        return $GLOBALS['cssFallback'];
    }
}

if (!function_exists('isCSS FallbackActive')) {
    function isCSSFallbackActive() {
        return getCSSFallback()->getStatus()['fallback_triggered'];
    }
}

if (!function_exists('activateCSS EmergencyMode')) {
    function activateCSSEmergencyMode() {
        return getCSSFallback()->activateEmergencyMode();
    }
}