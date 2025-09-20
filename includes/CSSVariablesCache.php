<?php
/**
 * CSS Variables 캐싱 시스템
 * Phase 4A: 성능 최적화 - 메모리 및 세션 캐싱
 */

class CSSVariablesCache 
{
    private static $memoryCache = [];
    private static $instance = null;
    private $sessionPrefix = 'css_vars_cache_';
    private $cacheVersion = '1.0';
    
    private function __construct() {
        // 세션이 시작되지 않았다면 시작
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 캐시 키 생성
     */
    private function generateCacheKey($context) {
        $keyData = [
            'version' => $this->cacheVersion,
            'context' => $context,
            'timestamp' => date('Y-m-d-H') // 시간별 캐시 무효화
        ];
        return $this->sessionPrefix . md5(serialize($keyData));
    }
    
    /**
     * 메모리 캐시에서 조회
     */
    private function getFromMemory($key) {
        return self::$memoryCache[$key] ?? null;
    }
    
    /**
     * 메모리 캐시에 저장
     */
    private function setToMemory($key, $value) {
        // 메모리 캐시 크기 제한 (최대 50개 항목)
        if (count(self::$memoryCache) >= 50) {
            // 가장 오래된 항목 제거 (FIFO)
            array_shift(self::$memoryCache);
        }
        self::$memoryCache[$key] = $value;
    }
    
    /**
     * 세션 캐시에서 조회
     */
    private function getFromSession($key) {
        return $_SESSION[$key] ?? null;
    }
    
    /**
     * 세션 캐시에 저장
     */
    private function setToSession($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * CSS Variables 스타일 캐시 조회
     */
    public function getCachedStyle($styleConfig, $context = 'default') {
        $cacheKey = $this->generateCacheKey([
            'type' => 'style',
            'config' => $styleConfig,
            'context' => $context
        ]);
        
        // 1단계: 메모리 캐시 확인
        $cached = $this->getFromMemory($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // 2단계: 세션 캐시 확인
        $cached = $this->getFromSession($cacheKey);
        if ($cached !== null) {
            // 메모리 캐시에도 저장
            $this->setToMemory($cacheKey, $cached);
            return $cached;
        }
        
        return null;
    }
    
    /**
     * CSS Variables 스타일 캐시 저장
     */
    public function setCachedStyle($styleConfig, $styleString, $context = 'default') {
        $cacheKey = $this->generateCacheKey([
            'type' => 'style',
            'config' => $styleConfig,
            'context' => $context
        ]);
        
        // 메모리와 세션 캐시 모두에 저장
        $this->setToMemory($cacheKey, $styleString);
        $this->setToSession($cacheKey, $styleString);
        
        return $styleString;
    }
    
    /**
     * 인라인 CSS 블록 캐시 조회
     */
    public function getCachedCSS($pageType, $cssType = 'inline') {
        $cacheKey = $this->generateCacheKey([
            'type' => 'css_block',
            'page_type' => $pageType,
            'css_type' => $cssType
        ]);
        
        // 메모리 캐시 우선 확인
        $cached = $this->getFromMemory($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // 세션 캐시 확인
        $cached = $this->getFromSession($cacheKey);
        if ($cached !== null) {
            $this->setToMemory($cacheKey, $cached);
            return $cached;
        }
        
        return null;
    }
    
    /**
     * 인라인 CSS 블록 캐시 저장
     */
    public function setCachedCSS($pageType, $cssContent, $cssType = 'inline') {
        $cacheKey = $this->generateCacheKey([
            'type' => 'css_block',
            'page_type' => $pageType,
            'css_type' => $cssType
        ]);
        
        // CSS 압축 (공백 및 주석 제거)
        $compressedCSS = $this->compressCSS($cssContent);
        
        $this->setToMemory($cacheKey, $compressedCSS);
        $this->setToSession($cacheKey, $compressedCSS);
        
        return $compressedCSS;
    }
    
    /**
     * CSS 압축 (간단한 압축)
     */
    private function compressCSS($css) {
        // 주석 제거
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        
        // 불필요한 공백 제거
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/;\s*}/', '}', $css);
        $css = preg_replace('/}\s*/', '}', $css);
        $css = preg_replace('/:\s*/', ':', $css);
        $css = preg_replace('/;\s*/', ';', $css);
        
        return trim($css);
    }
    
    /**
     * 캐시 통계 조회
     */
    public function getCacheStats() {
        $sessionCacheCount = 0;
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, $this->sessionPrefix) === 0) {
                $sessionCacheCount++;
            }
        }
        
        return [
            'memory_cache_count' => count(self::$memoryCache),
            'session_cache_count' => $sessionCacheCount,
            'cache_version' => $this->cacheVersion,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * 캐시 초기화 (디버깅용)
     */
    public function clearCache() {
        // 메모리 캐시 초기화
        self::$memoryCache = [];
        
        // 세션 캐시 초기화
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, $this->sessionPrefix) === 0) {
                unset($_SESSION[$key]);
            }
        }
        
        return true;
    }
    
    /**
     * 캐시 히트율 계산
     */
    public function getCacheHitRate() {
        if (!isset($_SESSION['cache_requests'])) {
            $_SESSION['cache_requests'] = 0;
            $_SESSION['cache_hits'] = 0;
        }
        
        $requests = $_SESSION['cache_requests'];
        $hits = $_SESSION['cache_hits'];
        
        if ($requests === 0) {
            return 0;
        }
        
        return round(($hits / $requests) * 100, 2);
    }
    
    /**
     * 캐시 요청 기록
     */
    public function recordCacheRequest($isHit = false) {
        if (!isset($_SESSION['cache_requests'])) {
            $_SESSION['cache_requests'] = 0;
            $_SESSION['cache_hits'] = 0;
        }
        
        $_SESSION['cache_requests']++;
        
        if ($isHit) {
            $_SESSION['cache_hits']++;
        }
    }
}

/**
 * 전역 캐시 인스턴스 헬퍼 함수
 */
if (!function_exists('getCSSVariablesCache')) {
    function getCSSVariablesCache() {
        return CSSVariablesCache::getInstance();
    }
}

/**
 * 캐시된 스타일 조회 헬퍼
 */
if (!function_exists('getCachedStyleString')) {
    function getCachedStyleString($styleConfig, $context = 'default') {
        $cache = getCSSVariablesCache();
        $cache->recordCacheRequest();
        
        $cached = $cache->getCachedStyle($styleConfig, $context);
        if ($cached !== null) {
            $cache->recordCacheRequest(true);
            return $cached;
        }
        
        return null;
    }
}

/**
 * 캐시에 스타일 저장 헬퍼
 */
if (!function_exists('setCachedStyleString')) {
    function setCachedStyleString($styleConfig, $styleString, $context = 'default') {
        $cache = getCSSVariablesCache();
        return $cache->setCachedStyle($styleConfig, $styleString, $context);
    }
}
?>