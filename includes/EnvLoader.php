<?php

/**
 * .env 파일 로더
 * 환경 변수를 로드하고 관리
 */
class EnvLoader
{
    private static $loaded = false;
    private static $env = [];
    
    /**
     * .env 파일 로드
     */
    public static function load($envPath = null)
    {
        if (self::$loaded) {
            return;
        }
        
        if (!$envPath) {
            $envPath = __DIR__ . '/../.env';
        }
        
        if (!file_exists($envPath)) {
            throw new Exception('.env file not found: ' . $envPath);
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 주석 제거
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            // 키=값 형태 파싱
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // 따옴표 제거
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // 환경 변수 설정
                self::$env[$key] = $value;
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * 환경 변수 값 가져오기
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$env[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * 모든 환경 변수 가져오기
     */
    public static function all()
    {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$env;
    }
    
    /**
     * 환경 변수 존재 여부 확인
     */
    public static function has($key)
    {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$env[$key]);
    }
    
    /**
     * 애플리케이션 이름 확인
     */
    public static function isValidApp($appName = null)
    {
        if (!$appName) {
            $appName = self::get('APP_NAME');
        }
        
        return !empty($appName);
    }
}

/**
 * 편의 함수: env()
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        return EnvLoader::get($key, $default);
    }
}