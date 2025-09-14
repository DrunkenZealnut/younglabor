<?php
/**
 * 관리자 로그인 처리 - 보안 강화
 */
require_once 'bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

try {
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        throw new Exception('아이디와 비밀번호를 입력해주세요.');
    }

    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        throw new Exception('아이디와 비밀번호를 모두 입력해주세요.');
    }

    // 디버깅 로그
    error_log("Login attempt for username: " . $username);
    
    // bootstrap.php의 authenticateAdmin 함수 사용
    $auth_result = authenticateAdmin($username, $password);
    error_log("Authentication result: " . ($auth_result ? 'SUCCESS' : 'FAILED'));
    
    if ($auth_result) {
        // 세션 확인
        error_log("Session after login: " . print_r($_SESSION, true));
        
        // 로그인 성공
        header("Location: index.php");
        exit;
    } else {
        // 로그인 실패
        error_log("Login failed for username: " . $username);
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
    
} catch (Exception $e) {
    // 오류 로깅
    error_log("Login process error: " . $e->getMessage());
    if (function_exists('logSecurityEvent')) {
        logSecurityEvent('LOGIN_ERROR', "Login error: " . $e->getMessage());
    }
    header("Location: login.php?error=system_error&msg=" . urlencode($e->getMessage()));
    exit;
}
?>
