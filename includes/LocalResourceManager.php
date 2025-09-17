<?php
/**
 * 로컬 리소스 관리 시스템
 * CDN 리소스를 로컬로 다운로드하여 캐시하는 시스템
 * 
 * Version: 1.0.0
 * Author: SuperClaude Performance Optimization System
 */

class LocalResourceManager {
    
    private $localDir;
    private $cacheLifetime = 86400 * 7; // 7일
    private $resources;
    
    public function __construct($baseDir = null) {
        $this->localDir = ($baseDir ?: dirname(__DIR__)) . '/assets/cached/';
        
        // 캐시 디렉토리 생성
        if (!file_exists($this->localDir)) {
            mkdir($this->localDir, 0755, true);
            mkdir($this->localDir . 'css/', 0755, true);
            mkdir($this->localDir . 'js/', 0755, true);
            mkdir($this->localDir . 'fonts/', 0755, true);
        }
        
        $this->initializeResources();
    }
    
    /**
     * 관리할 리소스 목록 정의
     */
    private function initializeResources() {
        $this->resources = [
            'bootstrap_css' => [
                'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                'local' => 'css/bootstrap.min.css',
                'type' => 'css',
                'priority' => 'high'
            ],
            'bootstrap_js' => [
                'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                'local' => 'js/bootstrap.bundle.min.js',
                'type' => 'js',
                'priority' => 'medium'
            ],
            'fontawesome' => [
                'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
                'local' => 'css/fontawesome.min.css',
                'type' => 'css',
                'priority' => 'high'
            ],
            'bootstrap_icons' => [
                'url' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css',
                'local' => 'css/bootstrap-icons.css',
                'type' => 'css',
                'priority' => 'medium'
            ],
            'noto_sans' => [
                'url' => 'https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap',
                'local' => 'css/noto-sans-kr.css',
                'type' => 'css',
                'priority' => 'high'
            ]
        ];
    }
    
    /**
     * 리소스 다운로드 및 캐시
     */
    public function downloadResource($resourceKey) {
        if (!isset($this->resources[$resourceKey])) {
            return false;
        }
        
        $resource = $this->resources[$resourceKey];
        $localPath = $this->localDir . $resource['local'];
        
        // 캐시된 파일이 있고 유효한지 확인
        if (file_exists($localPath) && 
            (time() - filemtime($localPath)) < $this->cacheLifetime) {
            return $localPath;
        }
        
        // 원격 리소스 다운로드
        $content = $this->fetchRemoteResource($resource['url']);
        if ($content === false) {
            return false;
        }
        
        // CSS 파일의 경우 상대 경로 처리
        if ($resource['type'] === 'css') {
            $content = $this->processCssContent($content, $resource['url']);
        }
        
        // 로컬에 저장
        $dir = dirname($localPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($localPath, $content);
        
        return $localPath;
    }
    
    /**
     * 모든 우선순위 높은 리소스 다운로드
     */
    public function downloadHighPriorityResources() {
        $results = [];
        
        foreach ($this->resources as $key => $resource) {
            if ($resource['priority'] === 'high') {
                $result = $this->downloadResource($key);
                $results[$key] = $result !== false;
            }
        }
        
        return $results;
    }
    
    /**
     * 원격 리소스 가져오기
     */
    private function fetchRemoteResource($url) {
        // cURL을 사용하여 리소스 가져오기
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; HopecResourceManager/1.0)',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $content : false;
    }
    
    /**
     * CSS 내용 처리 (상대 경로를 절대 경로로 변환)
     */
    private function processCssContent($content, $baseUrl) {
        // CSS 내의 상대 URL을 절대 URL로 변환
        $baseUrl = dirname($baseUrl) . '/';
        
        // url() 함수 내의 상대 경로 처리
        $content = preg_replace_callback(
            '/url\([\'"]?\.\.?\/([^\'")]+)[\'"]?\)/i',
            function($matches) use ($baseUrl) {
                return 'url(' . $baseUrl . $matches[1] . ')';
            },
            $content
        );
        
        // @import 규칙 처리
        $content = preg_replace_callback(
            '/@import\s+[\'"]\.\.?\/([^\'")]+)[\'"];?/i',
            function($matches) use ($baseUrl) {
                return '@import "' . $baseUrl . $matches[1] . '";';
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * 로컬 리소스 URL 반환
     */
    public function getLocalUrl($resourceKey, $siteUrl = '') {
        if (!isset($this->resources[$resourceKey])) {
            return false;
        }
        
        $resource = $this->resources[$resourceKey];
        $localPath = $this->localDir . $resource['local'];
        
        // 로컬 파일이 없으면 원본 URL 반환
        if (!file_exists($localPath)) {
            return $resource['url'];
        }
        
        // 파일이 오래되었으면 백그라운드에서 업데이트 스케줄
        if ((time() - filemtime($localPath)) > $this->cacheLifetime) {
            $this->scheduleBackgroundUpdate($resourceKey);
        }
        
        $relativePath = str_replace(dirname(__DIR__) . '/', '', $localPath);
        return rtrim($siteUrl, '/') . '/' . $relativePath;
    }
    
    /**
     * 백그라운드 업데이트 스케줄링 (향후 구현)
     */
    private function scheduleBackgroundUpdate($resourceKey) {
        // 비동기 업데이트를 위한 큐 시스템
        // 현재는 로그만 남김
        error_log("Scheduling background update for resource: {$resourceKey}");
    }
    
    /**
     * 캐시 클리어
     */
    public function clearCache() {
        $files = glob($this->localDir . '*/*');
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * 캐시 상태 확인
     */
    public function getCacheStatus() {
        $status = [];
        
        foreach ($this->resources as $key => $resource) {
            $localPath = $this->localDir . $resource['local'];
            $exists = file_exists($localPath);
            
            $status[$key] = [
                'exists' => $exists,
                'size' => $exists ? filesize($localPath) : 0,
                'modified' => $exists ? filemtime($localPath) : 0,
                'age_hours' => $exists ? round((time() - filemtime($localPath)) / 3600, 1) : 0,
                'url' => $resource['url'],
                'local_path' => $resource['local']
            ];
        }
        
        return $status;
    }
    
    /**
     * 통합 CSS 번들 생성
     */
    public function createCssBundle() {
        $cssResources = ['bootstrap_css', 'fontawesome', 'bootstrap_icons'];
        $bundleContent = '';
        
        foreach ($cssResources as $key) {
            $localPath = $this->downloadResource($key);
            if ($localPath && file_exists($localPath)) {
                $bundleContent .= "/* === {$key} === */\n";
                $bundleContent .= file_get_contents($localPath);
                $bundleContent .= "\n\n";
            }
        }
        
        // Natural Green 테마 CSS 추가
        $themeCSS = dirname(__DIR__) . '/theme/natural-green/styles/globals.css';
        if (file_exists($themeCSS)) {
            $bundleContent .= "/* === Natural Green Theme === */\n";
            $bundleContent .= file_get_contents($themeCSS);
        }
        
        // 압축
        $bundleContent = $this->minifyCSS($bundleContent);
        
        // 저장
        $bundlePath = dirname(__DIR__) . '/css/legacy-optimized.min.css';
        file_put_contents($bundlePath, $bundleContent);
        
        return $bundlePath;
    }
    
    /**
     * CSS 압축
     */
    private function minifyCSS($css) {
        // 주석 제거
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // 불필요한 공백 제거
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        $css = str_replace([': ', ' {', '{ ', ' }', '} ', '; '], [':', '{', '{', '}', '}', ';'], $css);
        
        return trim($css);
    }
}

// 전역 인스턴스 생성
if (!isset($GLOBALS['localResourceManager'])) {
    $GLOBALS['localResourceManager'] = new LocalResourceManager();
}

// 헬퍼 함수
if (!function_exists('getLocalResourceManager')) {
    function getLocalResourceManager() {
        return $GLOBALS['localResourceManager'];
    }
}