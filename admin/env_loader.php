<?php
/**
 * 간단한 .env 파일 로더
 */

if (!function_exists('loadEnv')) {
    function loadEnv($filePath = null) {
        if ($filePath === null) {
            $filePath = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($filePath)) {
            throw new Exception('.env 파일을 찾을 수 없습니다: ' . $filePath);
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env_vars = [];
        
        // First pass: collect all variables
        foreach ($lines as $line) {
            // 주석 제거
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // KEY=VALUE 형태 파싱
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                
                $key = trim($key);
                $value = trim($value);
                
                // 따옴표 제거 (양쪽 끝의 따옴표만 제거)
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                $env_vars[$key] = $value;
            }
        }
        
        // Second pass: substitute variables and set environment
        foreach ($env_vars as $key => $value) {
            // Replace ${VAR} patterns with actual values
            $value = preg_replace_callback('/\$\{([A-Z_]+)\}/', function($matches) use ($env_vars) {
                return isset($env_vars[$matches[1]]) ? $env_vars[$matches[1]] : $matches[0];
            }, $value);
            
            // 환경변수 설정
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            $value = isset($_ENV[$key]) ? $_ENV[$key] : $default;
        }
        
        // boolean 값 처리
        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'null':
                case '(null)':
                    return null;
            }
        }
        
        return $value;
    }
}

// .env 파일 로드
try {
    loadEnv();
} catch (Exception $e) {
    // 개발환경에서만 오류 표시
    if (defined('ADMIN_DEBUG') && ADMIN_DEBUG) {
        die('환경 설정 로드 실패: ' . $e->getMessage());
    }
}
?>