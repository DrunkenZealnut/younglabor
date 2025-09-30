<?php
/**
 * 인증 및 권한 관리 미들웨어
 * Authentication and Authorization Middleware
 */

require_once __DIR__ . '/email_auth_handler.php';

/**
 * 로그인 필수 체크
 */
function requireLogin($redirect_url = '/auth/login.php') {
    // Remember Me 자동 로그인 시도
    if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        handleRememberMeLogin();
    }

    // 여전히 로그인되지 않은 경우
    if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        // 현재 URL을 세션에 저장 (로그인 후 리다이렉트용)
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'] ?? '/';

        // 로그인 페이지로 리다이렉트
        if (!headers_sent()) {
            header('Location: ' . $redirect_url . '?access_denied=1');
            exit;
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($redirect_url) . "?access_denied=1';</script>";
            exit;
        }
    }

    // 세션 활동 시간 업데이트
    $_SESSION['last_activity'] = time();
}

/**
 * 이메일 인증 필수 체크
 */
function requireEmailVerification($redirect_url = '/auth/verify.php') {
    requireLogin(); // 먼저 로그인 체크

    // 이메일 인증 상태 확인
    $pdo = getAuthDatabase();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                SELECT mb_email_verified FROM " . table('members') . "
                WHERE mb_no = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $is_verified = $stmt->fetchColumn();

            if (!$is_verified) {
                if (!headers_sent()) {
                    header('Location: ' . $redirect_url . '?email_not_verified=1');
                    exit;
                } else {
                    echo "<script>window.location.href='" . htmlspecialchars($redirect_url) . "?email_not_verified=1';</script>";
                    exit;
                }
            }
        } catch (PDOException $e) {
            error_log('Email verification check error: ' . $e->getMessage());
        }
    }
}

/**
 * 최소 레벨 요구사항 체크
 */
function requireLevel($min_level, $redirect_url = '/auth/login.php') {
    requireEmailVerification(); // 이메일 인증도 체크

    $user_level = $_SESSION['user_level'] ?? 1;

    if ($user_level < $min_level) {
        if (!headers_sent()) {
            header('Location: ' . $redirect_url . '?insufficient_level=1');
            exit;
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($redirect_url) . "?insufficient_level=1';</script>";
            exit;
        }
    }
}

/**
 * 관리자 권한 체크
 */
function requireAdmin($redirect_url = '/auth/login.php') {
    requireLevel(8, $redirect_url); // 레벨 8 이상 (부관리자)
}

/**
 * 게시판 접근 권한 체크
 */
function requireBoardPermission($board_id, $permission = 'read', $redirect_url = '/auth/login.php') {
    // 게스트도 읽기 가능한 게시판인지 확인
    if ($permission === 'read' && isBoardPublic($board_id)) {
        return true;
    }

    // 로그인 필요
    requireEmailVerification();

    // 권한 확인
    if (!hasPermission($permission, $board_id)) {
        if (!headers_sent()) {
            header('Location: ' . $redirect_url . '?access_denied=1&board=' . urlencode($board_id));
            exit;
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($redirect_url) . "?access_denied=1';</script>";
            exit;
        }
    }
}

/**
 * 게시판이 공개인지 확인
 */
function isBoardPublic($board_id) {
    $pdo = getAuthDatabase();
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT anonymous_read FROM " . table('board_settings') . "
            WHERE board_id = ? AND is_active = 1
        ");
        $stmt->execute([$board_id]);
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Board public check error: ' . $e->getMessage());
        return false;
    }
}

/**
 * CSRF 토큰 생성
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF 토큰 검증
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 현재 사용자 정보 가져오기
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'nick' => $_SESSION['user_nick'] ?? null,
        'level' => $_SESSION['user_level'] ?? 1,
        'login_time' => $_SESSION['login_time'] ?? null,
        'is_auto_login' => $_SESSION['auto_login'] ?? false
    ];
}

/**
 * 사용자가 로그인되어 있는지 확인
 */
function isLoggedIn() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

/**
 * 관리자인지 확인
 */
function isAdmin() {
    return isLoggedIn() && ($_SESSION['user_level'] ?? 1) >= 8;
}

/**
 * 세션 타임아웃 체크
 */
function checkSessionTimeout($timeout_minutes = 120) {
    if (isset($_SESSION['last_activity'])) {
        $timeout_seconds = $timeout_minutes * 60;
        if (time() - $_SESSION['last_activity'] > $timeout_seconds) {
            // 세션 만료
            session_destroy();
            session_start();
            return true;
        }
    }
    return false;
}

/**
 * 보안 헤더 설정
 */
function setSecurityHeaders() {
    if (!headers_sent()) {
        // XSS 보호
        header('X-XSS-Protection: 1; mode=block');

        // Content Type Sniffing 방지
        header('X-Content-Type-Options: nosniff');

        // Frame 옵션 (클릭재킹 방지)
        header('X-Frame-Options: SAMEORIGIN');

        // HTTPS 강제 (HTTPS 환경에서만)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}

/**
 * 미들웨어 초기화 (자동 실행)
 */
function initializeMiddleware() {
    // 보안 헤더 설정
    setSecurityHeaders();

    // CSRF 토큰 생성
    generateCSRFToken();

    // 세션 타임아웃 체크
    if (checkSessionTimeout()) {
        // 타임아웃된 경우 처리
        if (isset($_SESSION['login_redirect'])) {
            unset($_SESSION['login_redirect']);
        }
    }

    // Remember Me 자동 로그인 시도 (로그인되지 않은 경우)
    if (!isLoggedIn()) {
        handleRememberMeLogin();
    }
}

// 미들웨어 자동 초기화
initializeMiddleware();
?>