<?php
/**
 * 환경변수 로더
 * 
 * .env 파일을 읽어서 환경변수로 설정
 */

if (!function_exists('load_env')) {
    /**
     * .env 파일 로드
     */
    function load_env($path = null) {
        $envFile = $path ?: __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            // .env 파일이 없으면 .env.example을 참조하도록 안내
            if (file_exists($envFile . '.example')) {
                throw new Exception(".env 파일이 없습니다. .env.example 파일을 .env로 복사해주세요.");
            }
            return false;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // 주석 라인 무시
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // = 기호로 키와 값 분리
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // 따옴표 제거
                $value = trim($value, '"\'');
                
                // 환경변수가 이미 설정되어 있지 않으면 설정
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
        
        // 두 번째 패스: 변수 치환 처리
        foreach ($_ENV as $key => $value) {
            if (is_string($value) && strpos($value, '${') !== false) {
                $_ENV[$key] = resolve_env_variables($value, $_ENV);
                putenv("$key=" . $_ENV[$key]);
            }
        }
        
        return true;
    }
}

if (!function_exists('resolve_env_variables')) {
    /**
     * 환경변수 값에서 ${VARIABLE} 형태의 변수를 치환
     */
    function resolve_env_variables($value, $env_vars) {
        return preg_replace_callback('/\$\{([^}]+)\}/', function($matches) use ($env_vars) {
            $var_name = $matches[1];
            return $env_vars[$var_name] ?? $matches[0]; // 변수가 없으면 원본 유지
        }, $value);
    }
}

if (!function_exists('env')) {
    /**
     * 환경변수 값 조회
     */
    function env($key, $default = null) {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // boolean 값 변환
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }
        
        // null 값 변환
        if (strtolower($value) === 'null') {
            return null;
        }
        
        // 숫자 값 변환
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        return $value;
    }
}

// .env 파일 자동 로드
load_env();