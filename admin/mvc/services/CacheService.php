<?php
/**
 * CacheService - 캐싱 서비스
 * 성능 최적화를 위한 캐시 시스템
 */

class CacheService 
{
    private $cacheEnabled;
    private $cachePath;
    private $defaultLifetime;
    
    public function __construct($config = []) 
    {
        $this->cacheEnabled = $config['enabled'] ?? true;
        $this->cachePath = $config['path'] ?? __DIR__ . '/../cache/';
        $this->defaultLifetime = $config['lifetime'] ?? 3600;
        
        $this->ensureCacheDirectory();
    }
    
    /**
     * 캐시 데이터 가져오기
     */
    public function get($key, $default = null) 
    {
        if (!$this->cacheEnabled) {
            return $default;
        }
        
        $filePath = $this->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return $default;
        }
        
        $data = file_get_contents($filePath);
        $cacheData = unserialize($data);
        
        // 만료 확인
        if ($cacheData['expires'] < time()) {
            $this->forget($key);
            return $default;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * 캐시 데이터 저장
     */
    public function put($key, $data, $lifetime = null) 
    {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        $lifetime = $lifetime ?? $this->defaultLifetime;
        $filePath = $this->getCacheFilePath($key);
        
        // 캐시 디렉토리 존재 확인 및 생성
        $cacheDir = dirname($filePath);
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
                error_log("캐시 디렉토리 생성 실패: {$cacheDir}");
                return false;
            }
        }
        
        // 쓰기 권한 확인
        if (!is_writable($cacheDir)) {
            error_log("캐시 디렉토리 쓰기 권한 없음: {$cacheDir}");
            return false;
        }
        
        $cacheData = [
            'data' => $data,
            'expires' => time() + $lifetime,
            'created' => time()
        ];
        
        $result = file_put_contents($filePath, serialize($cacheData), LOCK_EX);
        if ($result === false) {
            error_log("캐시 파일 쓰기 실패: {$filePath}");
            return false;
        }
        
        return true;
    }
    
    /**
     * 캐시 데이터 가져오기 (없으면 콜백 실행 후 저장)
     */
    public function remember($key, $callback, $lifetime = null) 
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->put($key, $value, $lifetime);
        
        return $value;
    }
    
    /**
     * 캐시 데이터 삭제
     */
    public function forget($key) 
    {
        $filePath = $this->getCacheFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * 모든 캐시 삭제
     */
    public function flush() 
    {
        $files = glob($this->cachePath . '*.cache');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * 만료된 캐시 정리
     */
    public function gc() 
    {
        $files = glob($this->cachePath . '*.cache');
        $deleted = 0;
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $cacheData = unserialize($data);
            
            if ($cacheData['expires'] < time()) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
    
    /**
     * 캐시 통계
     */
    public function stats() 
    {
        $files = glob($this->cachePath . '*.cache');
        $totalSize = 0;
        $totalCount = count($files);
        $expiredCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = file_get_contents($file);
            $cacheData = unserialize($data);
            
            if ($cacheData['expires'] < time()) {
                $expiredCount++;
            }
        }
        
        return [
            'total_files' => $totalCount,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'expired_files' => $expiredCount,
            'cache_path' => $this->cachePath,
            'cache_enabled' => $this->cacheEnabled
        ];
    }
    
    /**
     * 캐시 키 존재 확인
     */
    public function has($key) 
    {
        return $this->get($key) !== null;
    }
    
    /**
     * 캐시 파일 경로 생성
     */
    protected function getCacheFilePath($key) 
    {
        $hash = md5($key);
        return $this->cachePath . $hash . '.cache';
    }
    
    /**
     * 캐시 디렉토리 생성
     */
    protected function ensureCacheDirectory() 
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    /**
     * 바이트를 읽기 쉬운 형태로 변환
     */
    protected function formatBytes($bytes) 
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

/**
 * 캐시 헬퍼 함수들
 */

/**
 * 캐시 서비스 인스턴스 반환
 */
function cache() 
{
    static $cache = null;
    
    if ($cache === null) {
        $config = config('cache', []);
        $cache = new CacheService($config);
    }
    
    return $cache;
}

/**
 * 캐시에서 데이터 가져오기
 */
function cache_get($key, $default = null) 
{
    return cache()->get($key, $default);
}

/**
 * 캐시에 데이터 저장
 */
function cache_put($key, $data, $lifetime = null) 
{
    return cache()->put($key, $data, $lifetime);
}

/**
 * 캐시 기억하기 (없으면 콜백 실행)
 */
function cache_remember($key, $callback, $lifetime = null) 
{
    return cache()->remember($key, $callback, $lifetime);
}