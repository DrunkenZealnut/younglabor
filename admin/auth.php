<?php
/**
 * 관리자 인증 검증 - 보안 강화 버전
 */

// 세션 시작 (중복된 경우도 처리)
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        session_start();
    } else {
        session_start();
    }
}

// 기본 세션 검증
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    if (!headers_sent()) {
        header("Location: login.php?expired=1");
        exit;
    } else {
        echo "<script>window.location.href='login.php?expired=1';</script>";
        exit;
    }
}

// 세션 만료 체크 (30분)
if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity'] > 1800)) {
    // 세션 만료
    if (!headers_sent()) {
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    } else {
        echo "<script>window.location.href='login.php?timeout=1';</script>";
        exit;
    }
}

// IP 변경 체크 (세션 탈취 방지)
if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
    if (!headers_sent()) {
        session_destroy();
        header("Location: login.php?security=1");
        exit;
    } else {
        echo "<script>window.location.href='login.php?security=1';</script>";
        exit;
    }
}

// 세션 활동 시간 업데이트
$_SESSION['last_activity'] = time();

// 세션 ID 재생성 (15분마다)
if (!isset($_SESSION['regenerated']) || (time() - $_SESSION['regenerated'] > 900)) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}
?>
