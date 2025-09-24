<?php
/**
 * Admin Bootstrap - 관리자 시스템 부트스트래핑
 */

// Fix URLs containing ${PROJECT_SLUG}
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($requestUri, '${PROJECT_SLUG}') !== false || 
    strpos($requestUri, '%7BPROJECT_SLUG%7D') !== false ||
    strpos($requestUri, '$%7BPROJECT_SLUG%7D') !== false) {
    
    $fixedUri = str_replace(
        ['${PROJECT_SLUG}', '%7BPROJECT_SLUG%7D', '$%7BPROJECT_SLUG%7D'],
        'hopec',
        $requestUri
    );
    
    header('Location: ' . $fixedUri);
    exit;
}

// 보안 강화된 세션 관리 (board_templates 패턴 적용)
if (session_status() === PHP_SESSION_NONE) {
    // 헤더가 전송되지 않은 경우에만 세션 설정 변경
    if (!headers_sent()) {
        // 세션 보안 설정
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // 세션 만료 시간 설정 (2시간)
        ini_set('session.gc_maxlifetime', 7200);
        ini_set('session.cookie_lifetime', 7200);
        
        session_start();
    } else {
        // 헤더가 이미 전송된 경우 기본 설정으로 세션 시작
        session_start();
    }
    
    // 세션 하이재킹 방지
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
        if (!isset($_SESSION['created_at'])) {
            $_SESSION['created_at'] = time();
        }
        
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
        
        // 비활성 세션 만료 (30분)
        if (time() - $_SESSION['last_activity'] > 1800) {
            session_unset();
            session_destroy();
            header('Location: login.php?timeout=1');
            exit;
        }
        
        // 세션 재생성 (2시간마다)
        if (time() - $_SESSION['created_at'] > 7200) {
            session_regenerate_id(true);
            $_SESSION['created_at'] = time();
        }
        
        $_SESSION['last_activity'] = time();
        
        // IP 주소 변경 감지
        if (!isset($_SESSION['user_ip'])) {
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        } elseif ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
            session_unset();
            session_destroy();
            header('Location: login.php?security=1');
            exit;
        }
        
        // User Agent 변경 감지
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            session_unset();
            session_destroy();
            header('Location: login.php?security=1');
            exit;
        }
    }
}

// 인증 확인
require_once __DIR__ . '/auth.php';

// 데이터베이스 연결
require_once __DIR__ . '/db.php';

// 환경 변수 로더
require_once __DIR__ . '/env_loader.php';

// 템플릿 시스템 로드
require_once __DIR__ . '/templates_bridge.php';

// 한글 깨짐 방지
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// CSRF 보안 시스템 (board_templates 포팅)
if (!function_exists('generateCSRFToken')) {
    /**
     * CSRF 토큰 생성 (board_templates/config/helpers.php 포팅)
     */
    function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            time() - $_SESSION['csrf_token_time'] > 3600) { // 1시간 유효
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    /**
     * CSRF 토큰 검증 (board_templates/config/helpers.php 포팅)
     */
    function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // 토큰 만료 체크 (1시간)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        // 타이밍 공격 방지를 위한 hash_equals 사용
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('csrf_field')) {
    /**
     * CSRF 토큰 hidden input 생성
     */
    function csrf_field() {
        $token = generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

// SQL 인젝션 방지 유틸리티 (board_templates 포팅)
if (!function_exists('validateTableName')) {
    /**
     * 테이블명 유효성 검증 (영문, 숫자, 언더스코어만 허용)
     */
    function validateTableName($tableName) {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName);
    }
}

if (!function_exists('validateColumnName')) {
    /**
     * 컴럼명 유효성 검증 (영문, 숫자, 언더스코어만 허용)
     */
    function validateColumnName($columnName) {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $columnName);
    }
}

if (!function_exists('validateOrderDirection')) {
    /**
     * ORDER BY 방향 검증 (ASC 또는 DESC만 허용)
     */
    function validateOrderDirection($direction) {
        return in_array(strtoupper($direction), ['ASC', 'DESC'], true);
    }
}

if (!function_exists('sanitizeSearchInput')) {
    /**
     * 검색 입력 정리 (XSS 및 SQL 인젝션 방지)
     */
    function sanitizeSearchInput($input, $maxLength = 200) {
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return mb_substr($input, 0, $maxLength, 'UTF-8');
    }
}

if (!function_exists('buildWhereClause')) {
    /**
     * 안전한 WHERE 절 구성
     */
    function buildWhereClause($conditions) {
        $validConditions = [];
        foreach ($conditions as $condition) {
            if (is_string($condition) && !empty(trim($condition))) {
                $validConditions[] = trim($condition);
            }
        }
        return empty($validConditions) ? '1=1' : implode(' AND ', $validConditions);
    }
}

// 세션 관리 유틸리티 (board_templates 패턴 적용)
if (!function_exists('isValidAdminSession')) {
    /**
     * 관리자 세션 유효성 검증
     */
    function isValidAdminSession() {
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            return false;
        }
        
        // 세션 만료 체크
        if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity'] > 1800)) {
            return false;
        }
        
        // IP 변경 체크
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
            return false;
        }
        
        return true;
    }
}

if (!function_exists('destroyAdminSession')) {
    /**
     * 관리자 세션 완전 삭제
     */
    function destroyAdminSession() {
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
}

if (!function_exists('refreshAdminSession')) {
    /**
     * 관리자 세션 갱신
     */
    function refreshAdminSession() {
        if (isValidAdminSession()) {
            session_regenerate_id(true);
            $_SESSION['last_activity'] = time();
            $_SESSION['created_at'] = time();
            return true;
        }
        return false;
    }
}

if (!function_exists('createAdminSession')) {
    /**
     * 관리자 세션 생성
     */
    function createAdminSession($user_data) {
        // 보안 강화된 세션 설정
        session_regenerate_id(true);
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user_data['username'];
        $_SESSION['admin_user_id'] = $user_data['id'];
        $_SESSION['admin_role'] = $user_data['role'] ?? 'admin';
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // 보안 이벤트 로깅
        logSecurityEvent('SUCCESSFUL_LOGIN', "Admin user {$user_data['username']} logged in", $user_data['id']);
        
        return true;
    }
}

if (!function_exists('authenticateAdmin')) {
    /**
     * 관리자 인증 처리
     */
    function authenticateAdmin($username, $password) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM hopec_admin_user WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                return createAdminSession($user);
            } else {
                // 로그인 실패 로깅
                logSecurityEvent('FAILED_LOGIN', "Failed login attempt for username: {$username}");
                return false;
            }
        } catch (PDOException $e) {
            logSecurityEvent('LOGIN_ERROR', "Database error during login: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('logSecurityEvent')) {
    /**
     * 보안 이벤트 로깅
     */
    function logSecurityEvent($event_type, $description = '', $user_id = null) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $event_type,
            'description' => $description,
            'user_id' => $user_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        
        $log_file = __DIR__ . '/../logs/security.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
}

// 기본 유틸리티 함수들
if (!function_exists('t_escape')) {
    function t_escape($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

// t_url 함수는 templates_bridge.php에서 제공됨

// Admin 전용 템플릿 함수들 (templates_bridge.php에서 TemplateHelper 클래스 제공)
// t_render_layout과 t_render_component 함수는 templates_bridge.php에서 제공됨

// URL 생성 헬퍼 함수들
if (!function_exists('admin_url')) {
    /**
     * 관리자 URL 생성 함수
     * @param string $path 관리자 경로 (예: 'posts/list.php')
     * @return string 완전한 관리자 URL
     */
    function admin_url($path = '') {
        // 더 안전한 방법으로 BASE_PATH 가져오기
        $base_path = getenv('BASE_PATH');
        if ($base_path === false) {
            $base_path = $_ENV['BASE_PATH'] ?? '/hopec';
        }
        
        $path = ltrim($path, '/');
        return $base_path . '/admin/' . $path;
    }
}

if (!function_exists('get_base_path')) {
    /**
     * BASE_PATH 환경 변수 가져오기
     * @return string BASE_PATH 값
     */
    function get_base_path() {
        // 더 안전한 방법으로 BASE_PATH 가져오기
        $base_path = getenv('BASE_PATH');
        if ($base_path === false) {
            $base_path = $_ENV['BASE_PATH'] ?? '/hopec';
        }
        return $base_path;
    }
}

if (!function_exists('get_app_name')) {
    /**
     * APP_NAME 환경 변수 가져오기
     * @return string APP_NAME 값
     */
    function get_app_name() {
        $app_name = getenv('APP_NAME');
        if ($app_name === false) {
            $app_name = $_ENV['APP_NAME'] ?? '희망씨';
        }
        return $app_name;
    }
}

if (!function_exists('get_base_url')) {
    /**
     * 기본 URL 생성 함수 (프로토콜 + 호스트 + BASE_PATH)
     * @return string 완전한 기본 URL
     */
    function get_base_url() {
        // 프로토콜 감지
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        
        // 호스트 정보
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // 포트 정보 (표준 포트가 아닌 경우에만 포함)
        $port = $_SERVER['SERVER_PORT'] ?? 80;
        $isStandardPort = ($protocol === 'https' && $port == 443) || ($protocol === 'http' && $port == 80);
        
        // 포트가 이미 호스트에 포함되어 있는지 확인
        if (!$isStandardPort && strpos($host, ':') === false) {
            $host .= ':' . $port;
        }
        
        // BASE_PATH 가져오기
        $base_path = get_base_path();
        
        return $protocol . '://' . $host . $base_path;
    }
}

// 전역 변수로 앱 이름과 관리자 제목 설정
$app_name = get_app_name();
$admin_title = $app_name . ' 관리자';

// PROJECT_SLUG 정리용 JavaScript 함수 추가
if (!defined('PROJECT_SLUG_JS_ADDED')) {
    define('PROJECT_SLUG_JS_ADDED', true);
    $project_slug_js = '
<script>
// PROJECT_SLUG 패턴을 정리하는 전역 함수
function cleanProjectSlugFromUrl(url) {
    if (!url) return url;
    return url.replace(/\$\{PROJECT_SLUG\}/g, "hopec")
              .replace(/%7BPROJECT_SLUG%7D/g, "hopec") 
              .replace(/\$%7BPROJECT_SLUG%7D/g, "hopec");
}

// 현재 페이지 URL이 PROJECT_SLUG를 포함하고 있다면 즉시 리디렉트
if (window.location.href.indexOf("PROJECT_SLUG") !== -1) {
    const cleanUrl = cleanProjectSlugFromUrl(window.location.href);
    if (cleanUrl !== window.location.href) {
        window.location.replace(cleanUrl);
    }
}
</script>';
    
    // 페이지 하단에 출력될 수 있도록 전역 변수에 저장
    $GLOBALS['project_slug_js'] = $project_slug_js;
}

// 중앙 집중화된 Admin 메뉴 URL 설정
if (!function_exists('get_admin_menu_urls')) {
    /**
     * Admin 메뉴 URL들을 중앙에서 관리
     * @return array 메뉴 ID와 URL 매핑
     */
    function get_admin_menu_urls() {
        $base_path = get_base_path(); // /hopec
        
        return [
            'dashboard' => $base_path . '/admin/',
            'posts' => $base_path . '/admin/posts/list.php',
            'boards' => $base_path . '/admin/boards/list.php', 
            'menu' => $base_path . '/admin/menu/list.php',
            'inquiries' => $base_path . '/admin/inquiries/list.php',
            'events' => $base_path . '/admin/events/list.php',
            'files' => $base_path . '/admin/files/list.php',
            'settings' => $base_path . '/admin/settings/site_settings.php',
            'themes' => $base_path . '/admin/settings/simple-color-settings.php',
            'hero' => $base_path . '/admin/settings/hero_settings.php',
            'performance' => $base_path . '/admin/system/performance.php',
            'change_password' => $base_path . '/admin/change_password.php',
            'logout' => $base_path . '/admin/logout.php'
        ];
    }
}

if (!function_exists('get_admin_url')) {
    /**
     * 특정 Admin 메뉴의 URL 가져오기
     * @param string $menu_id 메뉴 ID
     * @return string URL
     */
    function get_admin_url($menu_id) {
        $urls = get_admin_menu_urls();
        return $urls[$menu_id] ?? '#';
    }
}
?>