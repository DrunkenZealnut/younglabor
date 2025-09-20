<?php
/**
 * CSS Variables 모드 전용 Autoloader
 * 테스트 파일들의 중복 require 패턴 통합
 * 
 * Version: 1.0.0
 * Author: CSS 구조 개선 프로젝트
 */

// 중복 방지
if (defined('CSS_VARS_AUTOLOADER_LOADED')) {
    return;
}
define('CSS_VARS_AUTOLOADER_LOADED', true);

// CSS Variables 관련 핵심 파일들 로드
$cssVarsDir = __DIR__;

// 1. CSS 모드 관리자
if (!class_exists('CSSModeManager')) {
    require_once $cssVarsDir . '/css-mode-manager.php';
}

// 2. CSS Variables 테마 매니저
if (!class_exists('CSSVariableThemeManager')) {
    require_once $cssVarsDir . '/CSSVariableThemeManager.php';
}

// 3. CSS 최적화 설정
if (!function_exists('renderOptimizedCSS')) {
    require_once $cssVarsDir . '/css-optimization-config.php';
}

// 4. Critical CSS 생성기 (선택적)
if (!class_exists('CriticalCSSGenerator')) {
    require_once $cssVarsDir . '/critical-css-generator.php';
}

// 5. CSS 폴백 시스템 (선택적)
if (!class_exists('CSSFallback')) {
    require_once $cssVarsDir . '/css-fallback.php';
}

// CSS Variables 모드 상태 확인 및 초기화
function initCSSVarsMode() {
    // CSS 모드 매니저 초기화
    $cssMode = getCSSMode();
    
    // CSS Variables 모드 활성화 확인
    if ($cssMode->isCSSVarsMode() || detectCSSVarsMode()) {
        // CSS Variables 매니저 초기화
        $cssVarsManager = getCSSVariableManager();
        
        // 디버그 모드일 경우 상태 출력
        if (defined('CSS_DEBUG') && CSS_DEBUG) {
            echo "<!-- CSS Variables Mode Autoloader: Active -->\n";
            echo "<!-- CSS Variables Available: " . count($cssVarsManager->getDebugInfo()['available_vars']) . " -->\n";
        }
        
        return true;
    }
    
    return false;
}

// 자동 초기화 (파일 로드시 실행)
$cssVarsActive = initCSSVarsMode();

// 전역 상태 변수 설정
if (!defined('CSS_VARS_MODE_ACTIVE')) {
    define('CSS_VARS_MODE_ACTIVE', $cssVarsActive);
}