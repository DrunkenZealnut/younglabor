<?php
/**
 * CSS Variable Theme Manager
 * 
 * CSS 변수를 직접 활용하는 새로운 테마 시스템
 * 기존 getThemeClass 매핑 시스템을 대체하는 효율적인 방식
 * 
 * Version: 1.1.0 - 캐싱 시스템 통합
 * Author: 희망씨 CSS 최적화 시스템
 */

// 캐싱 시스템 로드
require_once __DIR__ . '/CSSVariablesCache.php';

class CSSVariableThemeManager {
    private static $instance = null;
    private $cssVarMapping;
    private $fallbackEnabled = true;
    private $cache;
    
    public function __construct() {
        $this->cache = getCSSVariablesCache();
        $this->initializeCSSVariables();
    }
    
    /**
     * 싱글톤 인스턴스 반환
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * CSS 변수 매핑 초기화
     * globals.css의 변수들과 직접 연결
     */
    private function initializeCSSVariables() {
        $this->cssVarMapping = [
            // 메인 컬러 변수들
            'primary' => '--primary',
            'primary-foreground' => '--primary-foreground',
            'secondary' => '--secondary',
            'secondary-foreground' => '--secondary-foreground',
            'foreground' => '--foreground',
            'background' => '--background',
            
            // 라임 컬러 계열
            'lime-200' => '--lime-200',
            'lime-300' => '--lime-300',
            'lime-400' => '--lime-400',
            'lime-500' => '--lime-500',
            'lime-600' => '--lime-600',
            
            // 포레스트 컬러 계열
            'forest-500' => '--forest-500',
            'forest-600' => '--forest-600',
            'forest-700' => '--forest-700',
            
            // 내추럴 컬러 계열
            'natural-50' => '--natural-50',
            'natural-100' => '--natural-100',
            'natural-200' => '--natural-200',
            
            // 의미론적 컬러들
            'success' => '--success',
            'warning' => '--warning',
            'error' => '--error',
            'info' => '--info',
            
            // 카드 및 컴포넌트
            'card' => '--card',
            'card-foreground' => '--card-foreground',
            'border' => '--border',
            'muted' => '--muted',
            'muted-foreground' => '--muted-foreground',
            
            // 간편 별칭들
            'white' => '--primary-foreground',  // 흰색은 primary-foreground 사용
            'text-main' => '--foreground',
            'bg-main' => '--background'
        ];
    }
    
    /**
     * CSS 변수 값 반환
     * 
     * @param string $varName 변수명 (예: 'lime-500', 'primary')
     * @return string CSS 변수 문자열 (예: 'var(--lime-500)')
     */
    public function getVar($varName) {
        if (isset($this->cssVarMapping[$varName])) {
            return "var({$this->cssVarMapping[$varName]})";
        }
        
        // 직접적인 CSS 변수명인 경우 (-- 로 시작)
        if (strpos($varName, '--') === 0) {
            return "var({$varName})";
        }
        
        // 매핑에 없는 경우 직접 변수명 생성
        return "var(--{$varName})";
    }
    
    /**
     * 여러 CSS 속성을 인라인 스타일 문자열로 변환 (캐싱 지원)
     * 
     * @param array $properties 속성 배열 ['property' => 'var-name']
     * @param string $context 캐시 컨텍스트 (기본: 'default')
     * @return string 인라인 스타일 문자열
     */
    public function getStyleString($properties, $context = 'default') {
        // 캐시에서 조회 시도
        $cachedStyle = $this->cache->getCachedStyle($properties, $context);
        if ($cachedStyle !== null) {
            return $cachedStyle;
        }
        
        // 캐시 미스 - 새로 생성
        $styles = [];
        foreach ($properties as $property => $varName) {
            $styles[] = "{$property}: {$this->getVar($varName)}";
        }
        $styleString = implode('; ', $styles);
        
        // 캐시에 저장
        $this->cache->setCachedStyle($properties, $styleString, $context);
        
        return $styleString;
    }
    
    /**
     * 배경색 CSS 변수 반환
     * 
     * @param string $colorName 색상명 (예: 'lime-500')
     * @return string CSS 변수 문자열
     */
    public function getBgVar($colorName) {
        return $this->getVar($colorName);
    }
    
    /**
     * 텍스트 색상 CSS 변수 반환
     * 
     * @param string $colorName 색상명 (예: 'forest-700')
     * @return string CSS 변수 문자열
     */
    public function getTextVar($colorName) {
        return $this->getVar($colorName);
    }
    
    /**
     * 테두리 색상 CSS 변수 반환
     * 
     * @param string $colorName 색상명 (예: 'lime-200')
     * @return string CSS 변수 문자열
     */
    public function getBorderVar($colorName) {
        return $this->getVar($colorName);
    }
    
    /**
     * CSS 변수 모드 활성화 여부 확인
     * 
     * @return bool
     */
    public function isEnabled() {
        return defined('CSS_OPTIMIZATION_MODE') && CSS_OPTIMIZATION_MODE === 'css-vars';
    }
    
    /**
     * 브라우저 CSS 변수 지원 여부 확인
     * 
     * @return bool
     */
    public function isCSSVariableSupported() {
        // 기본적으로 모든 현대 브라우저가 지원한다고 가정
        // 필요시 JavaScript를 통한 실제 지원 체크 추가 가능
        return true;
    }
    
    /**
     * 기존 시스템으로 폴백
     * 
     * @param string $type 타입 (bg, text, border)
     * @param string $category 카테고리
     * @param string $shade 음영
     * @return string 기존 방식의 클래스명
     */
    public function fallbackToLegacy($type, $category, $shade = null) {
        if (function_exists('getThemeClass')) {
            return getThemeClass($type, $category, $shade);
        }
        return '';
    }
    
    /**
     * 호환성 함수: 기존 getThemeClass와 유사한 인터페이스
     * 
     * @param string $type 타입 (bg, text, border)
     * @param string $category 카테고리 (primary, secondary, etc.)
     * @param string $shade 음영 (100, 200, 500, etc.)
     * @return string 인라인 스타일 문자열
     */
    public function getCompatStyle($type, $category, $shade = null) {
        $varName = $category;
        if ($shade) {
            $varName = "{$category}-{$shade}";
        }
        
        $property = '';
        switch ($type) {
            case 'bg':
                $property = 'background-color';
                break;
            case 'text':
                $property = 'color';
                break;
            case 'border':
                $property = 'border-color';
                break;
            default:
                return '';
        }
        
        return "{$property}: {$this->getVar($varName)}";
    }
    
    /**
     * 디버그 정보 반환
     * 
     * @return array 디버그 정보
     */
    public function getDebugInfo() {
        return [
            'enabled' => $this->isEnabled(),
            'css_variable_support' => $this->isCSSVariableSupported(),
            'fallback_enabled' => $this->fallbackEnabled,
            'mapped_variables' => count($this->cssVarMapping),
            'mode' => defined('CSS_OPTIMIZATION_MODE') ? CSS_OPTIMIZATION_MODE : 'undefined',
            'available_vars' => array_keys($this->cssVarMapping)
        ];
    }
}

/**
 * 전역 헬퍼 함수들
 */

if (!function_exists('getCSSVariableManager')) {
    function getCSSVariableManager() {
        return CSSVariableThemeManager::getInstance();
    }
}

if (!function_exists('getThemeVar')) {
    /**
     * CSS 변수 반환 (전역 함수)
     * 
     * @param string $varName 변수명
     * @return string CSS 변수 문자열
     */
    function getThemeVar($varName) {
        $manager = getCSSVariableManager();
        if ($manager->isEnabled()) {
            return $manager->getVar($varName);
        }
        
        // CSS 변수 모드가 비활성화된 경우 기존 방식 사용
        return '';
    }
}

if (!function_exists('getThemeStyle')) {
    /**
     * 인라인 스타일 문자열 생성 (전역 함수)
     * 
     * @param array $properties 속성 배열
     * @return string 인라인 스타일 문자열
     */
    function getThemeStyle($properties) {
        $manager = getCSSVariableManager();
        if ($manager->isEnabled()) {
            return $manager->getStyleString($properties);
        }
        
        return '';
    }
}

if (!function_exists('getThemeVarCompat')) {
    /**
     * 기존 getThemeClass와 호환되는 함수 (CSS 변수 모드용)
     * 
     * @param string $type 타입
     * @param string $category 카테고리
     * @param string $shade 음영
     * @return string 인라인 스타일 또는 클래스명
     */
    function getThemeVarCompat($type, $category, $shade = null) {
        $manager = getCSSVariableManager();
        if ($manager->isEnabled()) {
            return $manager->getCompatStyle($type, $category, $shade);
        }
        
        // 폴백: 기존 방식 사용
        return $manager->fallbackToLegacy($type, $category, $shade);
    }
}

if (!function_exists('detectCSSVarsMode')) {
    /**
     * CSS Variables 모드 감지 헬퍼 함수
     * about.php의 중복 로직을 통합
     * 
     * @return bool CSS Variables 모드 활성화 여부
     */
    function detectCSSVarsMode() {
        return (isset($_GET['css_mode']) && $_GET['css_mode'] === 'css-vars') || 
               (defined('CSS_OPTIMIZATION_MODE') && CSS_OPTIMIZATION_MODE === 'css-vars') ||
               (defined('CSS_VARS_TEST_MODE') && CSS_VARS_TEST_MODE);
    }
}