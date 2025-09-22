<?php
/**
 * 설정 및 환경변수 헬퍼 함수들
 */

if (!function_exists('env')) {
    /**
     * 환경변수 값 가져오기
     */
    function env($key, $default = null) {
        // 먼저 $_ENV에서 확인
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        } 
        // getenv() 함수로도 확인
        elseif (($value = getenv($key)) !== false) {
            // getenv에서 찾음
        } 
        // 기본값 사용
        else {
            return $default;
        }
        
        // 따옴표 제거
        if (strlen($value) > 1 && (
            ($value[0] === '"' && $value[-1] === '"') ||
            ($value[0] === "'" && $value[-1] === "'")
        )) {
            return substr($value, 1, -1);
        }
        
        return $value;
    }
}

if (!function_exists('get_org_name')) {
    /**
     * 조직명 가져오기
     */
    function get_org_name($full = false) {
        if ($full) {
            return env('ORG_NAME_FULL', '사단법인 희망씨');
        }
        return env('ORG_NAME_SHORT', '희망씨');
    }
}

if (!function_exists('get_org_description')) {
    /**
     * 조직 설명 가져오기
     */
    function get_org_description() {
        return env('ORG_DESCRIPTION', '지역사회와 함께 아래로 향한 연대 일터와 삶터를 바꾸기 위한 활동에 함께 합니다');
    }
}

if (!function_exists('get_contact_email')) {
    /**
     * 연락처 이메일 가져오기
     */
    function get_contact_email() {
        return env('CONTACT_EMAIL', env('DEFAULT_ADMIN_EMAIL', 'contact@example.com'));
    }
}

if (!function_exists('get_bank_account_holder')) {
    /**
     * 은행 계좌 예금주 가져오기
     */
    function get_bank_account_holder() {
        return env('BANK_ACCOUNT_HOLDER', get_org_name(true));
    }
}

if (!function_exists('get_app_url')) {
    /**
     * 애플리케이션 URL 가져오기
     */
    function get_app_url($path = '') {
        $baseUrl = env('APP_URL', 'http://localhost');
        $basePath = env('BASE_PATH', '');
        
        // 기본 URL과 경로 결합
        $fullUrl = rtrim($baseUrl, '/');
        
        // APP_URL에 이미 BASE_PATH가 포함되어 있는지 확인
        if ($basePath && !empty($basePath)) {
            $basePath = trim($basePath, '/');
            // APP_URL에 이미 BASE_PATH가 포함되어 있지 않은 경우에만 추가
            if (substr($fullUrl, -strlen('/' . $basePath)) !== '/' . $basePath) {
                $fullUrl .= '/' . $basePath;
            }
        }
        
        if ($path) {
            $fullUrl .= '/' . ltrim($path, '/');
        }
        
        return $fullUrl;
    }
}

if (!function_exists('get_production_url')) {
    /**
     * 프로덕션 URL 가져오기
     */
    function get_production_url($path = '') {
        $baseUrl = env('PRODUCTION_URL', env('APP_URL', 'http://localhost'));
        
        if ($path) {
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }
        
        return $baseUrl;
    }
}

if (!function_exists('get_table_name')) {
    /**
     * 테이블명 가져오기 (접두사 포함)
     */
    function get_table_name($table) {
        $prefix = env('DB_TABLE_PREFIX', 'hopec_');
        return $prefix . $table;
    }
}

if (!function_exists('is_production')) {
    /**
     * 프로덕션 환경 여부 확인
     */
    function is_production() {
        return env('APP_ENV', 'local') === 'production';
    }
}

if (!function_exists('is_local')) {
    /**
     * 로컬 환경 여부 확인
     */
    function is_local() {
        $env = env('APP_ENV', 'local');
        return $env === 'local' || $env === 'development';
    }
}

if (!function_exists('get_site_name')) {
    /**
     * 사이트명 가져오기
     */
    function get_site_name() {
        return env('DEFAULT_SITE_NAME', get_org_name(true));
    }
}

if (!function_exists('get_site_description')) {
    /**
     * 사이트 설명 가져오기
     */
    function get_site_description() {
        return env('DEFAULT_SITE_DESCRIPTION', get_org_description());
    }
}

if (!function_exists('get_mail_from_name')) {
    /**
     * 이메일 발신자명 가져오기
     */
    function get_mail_from_name() {
        return env('MAIL_FROM_NAME', get_org_name() . ' 웹사이트');
    }
}

if (!function_exists('get_base_path')) {
    /**
     * 베이스 경로 가져오기
     */
    function get_base_path() {
        return env('BASE_PATH', '');
    }
}

if (!function_exists('load_env_if_exists')) {
    /**
     * .env 파일이 있으면 로드
     */
    function load_env_if_exists($path = null) {
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        
        if (!file_exists($path)) {
            return false;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // 이미 설정된 환경변수는 덮어쓰지 않음
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
        
        return true;
    }
}