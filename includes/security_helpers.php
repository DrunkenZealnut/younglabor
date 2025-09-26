<?php
/**
 * 보안 관련 헬퍼 함수들
 * 
 * Admin 시스템의 보안 함수들을 메인 사이트로 확장
 */

if (!function_exists('csrf_token')) {
    /**
     * CSRF 토큰 생성
     */
    function csrf_token() {
        return SecurityManager::generateCSRFToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * CSRF 토큰 hidden input 생성
     */
    function csrf_field() {
        return SecurityManager::csrfField();
    }
}

if (!function_exists('verify_csrf')) {
    /**
     * CSRF 토큰 검증
     */
    function verify_csrf($token) {
        return SecurityManager::verifyCSRFToken($token);
    }
}

if (!function_exists('escape')) {
    /**
     * XSS 방지 출력 이스케이프
     */
    function escape($data) {
        return SecurityManager::escapeOutput($data);
    }
}

if (!function_exists('h')) {
    /**
     * htmlspecialchars 단축 함수
     */
    function h($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * 입력 데이터 정리
     */
    function sanitize_input($input, $maxLength = 1000) {
        return SecurityManager::sanitizeInput($input, $maxLength);
    }
}

if (!function_exists('validate_table_name')) {
    /**
     * 테이블명 유효성 검증 (admin bootstrap.php에서 포팅)
     */
    function validate_table_name($tableName) {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName);
    }
}

if (!function_exists('validate_column_name')) {
    /**
     * 컬럼명 유효성 검증 (admin bootstrap.php에서 포팅)
     */
    function validate_column_name($columnName) {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $columnName);
    }
}

if (!function_exists('validate_order_direction')) {
    /**
     * ORDER BY 방향 검증 (admin bootstrap.php에서 포팅)
     */
    function validate_order_direction($direction) {
        return in_array(strtoupper($direction), ['ASC', 'DESC'], true);
    }
}

if (!function_exists('sanitize_search_input')) {
    /**
     * 검색 입력 정리 (admin bootstrap.php에서 포팅)
     */
    function sanitize_search_input($input, $maxLength = 200) {
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return mb_substr($input, 0, $maxLength, 'UTF-8');
    }
}

if (!function_exists('build_where_clause')) {
    /**
     * 안전한 WHERE 절 구성 (admin bootstrap.php에서 포팅)
     */
    function build_where_clause($conditions) {
        $validConditions = [];
        foreach ($conditions as $condition) {
            if (is_string($condition) && !empty(trim($condition))) {
                $validConditions[] = trim($condition);
            }
        }
        return empty($validConditions) ? '1=1' : implode(' AND ', $validConditions);
    }
}

if (!function_exists('log_security_event')) {
    /**
     * 보안 이벤트 로깅
     */
    function log_security_event($eventType, $description = '', $userId = null) {
        SecurityManager::logSecurityEvent($eventType, $description, $userId);
    }
}

if (!function_exists('is_secure_request')) {
    /**
     * HTTPS 요청 확인
     */
    function is_secure_request() {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 클라이언트 IP 주소 획득
     */
    function get_client_ip() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

if (!function_exists('generate_random_string')) {
    /**
     * 랜덤 문자열 생성
     */
    function generate_random_string($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('hash_password')) {
    /**
     * 비밀번호 해싱
     */
    function hash_password($password) {
        $config = $GLOBALS['younglabor_config']['security']['password'] ?? [];
        $algorithm = $config['hash_algorithm'] ?? PASSWORD_DEFAULT;
        $options = $config['hash_options'] ?? ['cost' => 12];
        
        return password_hash($password, $algorithm, $options);
    }
}

if (!function_exists('verify_password')) {
    /**
     * 비밀번호 검증
     */
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
}