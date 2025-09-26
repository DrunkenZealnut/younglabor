<?php
/**
 * 보안 매니저
 * 
 * Admin 시스템의 검증된 보안 기능을 메인 사이트로 확장
 */

class SecurityManager
{
    private static $initialized = false;
    private static $config = null;
    
    /**
     * 보안 시스템 초기화
     */
    public static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        
        self::$config = $GLOBALS['younglabor_config']['security'] ?? [];
        
        // 보안 헤더 설정
        self::setSecurityHeaders();
        
        // 전역변수 보호
        self::protectGlobalVariables();
        
        // 세션 보안 설정
        self::initializeSession();
        
        self::$initialized = true;
    }
    
    /**
     * 보안 헤더 설정
     */
    private static function setSecurityHeaders()
    {
        if (headers_sent()) {
            return;
        }
        
        // P3P 헤더 (IE 호환성 - common.php에서 이전)
        header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');
        
        $headers = self::$config['headers'] ?? [];
        
        if (!(self::$config['headers']['enabled'] ?? true)) {
            return;
        }
        
        if (!empty($headers['x_frame_options'])) {
            header('X-Frame-Options: ' . $headers['x_frame_options']);
        }
        
        if (!empty($headers['x_content_type_options'])) {
            header('X-Content-Type-Options: ' . $headers['x_content_type_options']);
        }
        
        if (!empty($headers['x_xss_protection'])) {
            header('X-XSS-Protection: ' . $headers['x_xss_protection']);
        }
        
        if (!empty($headers['referrer_policy'])) {
            header('Referrer-Policy: ' . $headers['referrer_policy']);
        }
        
        // HTTPS에서만 HSTS 헤더 설정
        if (!empty($_SERVER['HTTPS']) && !empty($headers['strict_transport_security'])) {
            header('Strict-Transport-Security: ' . $headers['strict_transport_security']);
        }
        
        if (!empty($headers['content_security_policy'])) {
            header('Content-Security-Policy: ' . $headers['content_security_policy']);
        }
    }
    
    /**
     * 전역변수 보호 (common.php에서 이전)
     */
    private static function protectGlobalVariables()
    {
        // extract($_GET); 명령으로 인한 보안 취약점 방지
        $ext_arr = [
            'PHP_SELF', '_ENV', '_GET', '_POST', '_FILES', '_SERVER', 
            '_COOKIE', '_SESSION', '_REQUEST', 'HTTP_ENV_VARS', 
            'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 
            'HTTP_SERVER_VARS', 'HTTP_COOKIE_VARS', 'HTTP_SESSION_VARS', 
            'GLOBALS'
        ];
        
        foreach ($ext_arr as $var) {
            // POST, GET 으로 선언된 전역변수가 있다면 unset() 시킴
            unset($_GET[$var], $_POST[$var]);
        }
    }
    
    /**
     * 세션 보안 초기화 (Admin 시스템의 보안 설정 적용)
     */
    private static function initializeSession()
    {
        $sessionConfig = self::$config['session'] ?? [];
        
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['younglabor_user'])) {
            // 세션 하이재킹 방지
            self::validateSession();
        }
    }
    
    /**
     * 세션 유효성 검증 (Admin bootstrap.php 로직 적용)
     */
    private static function validateSession()
    {
        $sessionConfig = self::$config['session'] ?? [];
        
        if (!isset($_SESSION['younglabor_created_at'])) {
            $_SESSION['younglabor_created_at'] = time();
        }
        
        if (!isset($_SESSION['younglabor_last_activity'])) {
            $_SESSION['younglabor_last_activity'] = time();
        }
        
        // 비활성 세션 만료 체크
        $inactivityTimeout = $sessionConfig['inactivity_timeout'] ?? 1800;
        if (time() - $_SESSION['younglabor_last_activity'] > $inactivityTimeout) {
            self::destroySession();
            return false;
        }
        
        // IP 주소 변경 감지
        if ($sessionConfig['ip_check'] ?? true) {
            if (!isset($_SESSION['younglabor_user_ip'])) {
                $_SESSION['younglabor_user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
            } elseif ($_SESSION['younglabor_user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
                self::logSecurityEvent('SESSION_IP_CHANGE', 'IP 주소 변경 감지');
                self::destroySession();
                return false;
            }
        }
        
        // User Agent 변경 감지
        if ($sessionConfig['user_agent_check'] ?? true) {
            if (!isset($_SESSION['younglabor_user_agent'])) {
                $_SESSION['younglabor_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            } elseif ($_SESSION['younglabor_user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
                self::logSecurityEvent('SESSION_AGENT_CHANGE', 'User Agent 변경 감지');
                self::destroySession();
                return false;
            }
        }
        
        // 세션 재생성 (2시간마다)
        if ($sessionConfig['regenerate_id'] ?? true) {
            $lifetime = $sessionConfig['lifetime'] ?? 7200;
            if (time() - $_SESSION['younglabor_created_at'] > $lifetime) {
                session_regenerate_id(true);
                $_SESSION['younglabor_created_at'] = time();
            }
        }
        
        $_SESSION['younglabor_last_activity'] = time();
        
        return true;
    }
    
    /**
     * CSRF 토큰 생성 (Admin bootstrap.php 로직)
     */
    public static function generateCSRFToken()
    {
        $csrfConfig = self::$config['csrf'] ?? [];
        
        if (!($csrfConfig['enabled'] ?? true)) {
            return '';
        }
        
        $tokenName = $csrfConfig['token_name'] ?? 'younglabor_csrf_token';
        $lifetime = $csrfConfig['lifetime'] ?? 3600;
        
        if (!isset($_SESSION[$tokenName]) || 
            !isset($_SESSION[$tokenName . '_time']) || 
            time() - $_SESSION[$tokenName . '_time'] > $lifetime) {
            
            $_SESSION[$tokenName] = bin2hex(random_bytes(32));
            $_SESSION[$tokenName . '_time'] = time();
        }
        
        return $_SESSION[$tokenName];
    }
    
    /**
     * CSRF 토큰 검증 (Admin bootstrap.php 로직)
     */
    public static function verifyCSRFToken($token)
    {
        $csrfConfig = self::$config['csrf'] ?? [];
        
        if (!($csrfConfig['enabled'] ?? true)) {
            return true;
        }
        
        $tokenName = $csrfConfig['token_name'] ?? 'younglabor_csrf_token';
        $lifetime = $csrfConfig['lifetime'] ?? 3600;
        
        if (!isset($_SESSION[$tokenName]) || !isset($_SESSION[$tokenName . '_time'])) {
            return false;
        }
        
        // 토큰 만료 체크
        if (time() - $_SESSION[$tokenName . '_time'] > $lifetime) {
            unset($_SESSION[$tokenName]);
            unset($_SESSION[$tokenName . '_time']);
            return false;
        }
        
        // 타이밍 공격 방지
        return hash_equals($_SESSION[$tokenName], $token);
    }
    
    /**
     * CSRF 토큰 hidden input 생성
     */
    public static function csrfField()
    {
        $csrfConfig = self::$config['csrf'] ?? [];
        
        if (!($csrfConfig['enabled'] ?? true)) {
            return '';
        }
        
        $token = self::generateCSRFToken();
        $tokenName = $csrfConfig['token_name'] ?? 'younglabor_csrf_token';
        
        return '<input type="hidden" name="' . $tokenName . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * XSS 방지 필터링
     */
    public static function escapeOutput($data)
    {
        $xssConfig = self::$config['xss'] ?? [];
        
        if (!($xssConfig['enabled'] ?? true)) {
            return $data;
        }
        
        if (is_array($data)) {
            return array_map([self::class, 'escapeOutput'], $data);
        }
        
        return htmlspecialchars((string)$data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * 입력 데이터 정리
     */
    public static function sanitizeInput($input, $maxLength = 1000)
    {
        if (is_array($input)) {
            return array_map(function($item) use ($maxLength) {
                return self::sanitizeInput($item, $maxLength);
            }, $input);
        }
        
        $input = trim((string)$input);
        $input = mb_substr($input, 0, $maxLength, 'UTF-8');
        
        return $input;
    }
    
    /**
     * 세션 완전 삭제 (Admin bootstrap.php 로직)
     */
    public static function destroySession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            session_destroy();
        }
    }
    
    /**
     * 보안 이벤트 로깅 (Admin bootstrap.php 로직)
     */
    public static function logSecurityEvent($eventType, $description = '', $userId = null)
    {
        $loggingConfig = self::$config['logging'] ?? [];
        
        if (!($loggingConfig['enabled'] ?? true)) {
            return;
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $eventType,
            'description' => $description,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        
        $logFile = PROJECT_BASE_PATH . '/' . ($loggingConfig['log_file'] ?? 'logs/security.log');
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 파일 업로드 보안 검증
     */
    public static function validateFileUpload($file)
    {
        $uploadConfig = self::$config['file_upload'] ?? [];
        
        // 파일 크기 검증
        $maxSize = $uploadConfig['max_size'] ?? 10485760; // 10MB
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => '파일 크기가 너무 큽니다.'];
        }
        
        // 파일 확장자 검증
        $allowedImageTypes = $uploadConfig['allowed_image_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedDocTypes = $uploadConfig['allowed_document_types'] ?? ['pdf', 'doc', 'docx', 'hwp'];
        $allowedTypes = array_merge($allowedImageTypes, $allowedDocTypes);
        
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes)) {
            return ['success' => false, 'message' => '허용되지 않는 파일 형식입니다.'];
        }
        
        // MIME 타입 검증
        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/x-hwp', 'application/haansofthwp'
        ];
        
        if (!in_array($file['type'], $allowedMimeTypes)) {
            return ['success' => false, 'message' => 'MIME 타입이 일치하지 않습니다.'];
        }
        
        return ['success' => true];
    }
}