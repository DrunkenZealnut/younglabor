<?php
/**
 * Admin System Configuration Loader
 * 환경 변수 및 설정 관리 시스템
 */

class Config {
    private static $env = [];
    private static $loaded = false;
    
    /**
     * 환경 변수 로드
     */
    public static function load($envFile = null) {
        if (self::$loaded) {
            return;
        }
        
        // 기본 .env 파일 경로
        if ($envFile === null) {
            $envFile = dirname(__DIR__) . '/.env';
        }
        
        // .env 파일이 없으면 .env.example 사용 (개발 편의)
        if (!file_exists($envFile)) {
            $envExampleFile = dirname(__DIR__) . '/.env.example';
            if (file_exists($envExampleFile)) {
                $envFile = $envExampleFile;
            } else {
                return;
            }
        }
        
        // .env 파일 파싱
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // 주석 제거
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // KEY=VALUE 형식 파싱
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // 따옴표 제거
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                self::$env[$key] = $value;
                
                // 실제 환경 변수로도 설정 (getenv() 호환성)
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * 환경 변수 가져오기
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        // 우선순위: 1. 실제 환경변수, 2. .env 파일, 3. 기본값
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        if (isset(self::$env[$key])) {
            return self::$env[$key];
        }
        
        return $default;
    }
    
    /**
     * 모든 환경 변수 가져오기
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$env;
    }
    
    /**
     * 환경 확인
     */
    public static function isLocal() {
        return self::get('APP_ENV', 'production') === 'local';
    }
    
    public static function isProduction() {
        return self::get('APP_ENV', 'production') === 'production';
    }
    
    public static function isDebug() {
        return self::get('APP_DEBUG', 'false') === 'true';
    }
}

/**
 * 헬퍼 함수 - 환경 변수 가져오기
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        return Config::get($key, $default);
    }
}

/**
 * 헬퍼 함수 - 설정값 가져오기
 */
if (!function_exists('config')) {
    function config($key, $default = null) {
        // config 파일에서 설정 가져오기
        $parts = explode('.', $key);
        $file = array_shift($parts);
        
        $configFile = __DIR__ . '/' . $file . '.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            
            // 중첩된 키 처리 (예: database.connections.mysql)
            foreach ($parts as $part) {
                if (isset($config[$part])) {
                    $config = $config[$part];
                } else {
                    return $default;
                }
            }
            
            return $config;
        }
        
        return $default;
    }
}

// 자동 로드
Config::load();