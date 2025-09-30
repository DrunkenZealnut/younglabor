<?php
/**
 * 사용자 인증 처리 핸들러
 * User Authentication Handler
 */

// 데이터베이스 연결
function getAuthDatabase() {
    $database_config = require __DIR__ . '/../config/database.php';
    $config = $database_config['connections']['mysql'];

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        return false;
    }
}

// 테이블 이름 헬퍼 함수
function table($table_name) {
    $database_config = require __DIR__ . '/../config/database.php';
    $prefix = $database_config['prefixes']['modern'] ?? '';
    return $prefix . $table_name;
}

/**
 * 회원가입 처리
 */
function handleUserRegistration($data) {
    // CSRF 토큰 검증
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($data['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => '잘못된 요청입니다.'];
    }

    // 필수 필드 검증
    $required_fields = ['username', 'email', 'name', 'password', 'password_confirm'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => '모든 필수 항목을 입력해주세요.'];
        }
    }

    // 이용약관 동의 검증
    if (!isset($data['agree_privacy']) || !isset($data['agree_terms'])) {
        return ['success' => false, 'message' => '개인정보 처리방침과 이용약관에 동의해주세요.'];
    }

    // 데이터 검증
    $validation_result = validateUserData($data);
    if (!$validation_result['valid']) {
        return ['success' => false, 'message' => $validation_result['message']];
    }

    // 데이터베이스 연결
    $pdo = getAuthDatabase();
    if (!$pdo) {
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다.'];
    }

    try {
        // 중복 검사
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . table('users') . " WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);

        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => '이미 사용 중인 사용자명 또는 이메일입니다.'];
        }

        // 사용자 등록
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("
            INSERT INTO " . table('users') . "
            (username, email, name, password_hash, phone, verification_token, status, role, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', 'user', NOW())
        ");

        $result = $stmt->execute([
            $data['username'],
            $data['email'],
            $data['name'],
            $password_hash,
            $data['phone'] ?? null,
            $verification_token
        ]);

        if ($result) {
            // 로그 기록
            logAuthActivity($pdo, null, 'user_registered', 'User registered: ' . $data['username']);

            return ['success' => true, 'message' => '회원가입이 완료되었습니다.'];
        } else {
            return ['success' => false, 'message' => '회원가입 중 오류가 발생했습니다.'];
        }

    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다.'];
    }
}

/**
 * 로그인 처리
 */
function handleUserLogin($data) {
    // CSRF 토큰 검증
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($data['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => '잘못된 요청입니다.'];
    }

    $login_id = trim($data['login_id'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($login_id) || empty($password)) {
        return ['success' => false, 'message' => '이메일/사용자명과 비밀번호를 입력해주세요.'];
    }

    // 데이터베이스 연결
    $pdo = getAuthDatabase();
    if (!$pdo) {
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다.'];
    }

    try {
        // 사용자 조회 (이메일 또는 사용자명으로)
        $stmt = $pdo->prepare("
            SELECT * FROM " . table('users') . "
            WHERE (username = ? OR email = ?) AND status = 'active'
        ");
        $stmt->execute([$login_id, $login_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // 로그인 시도 기록
            logAuthActivity($pdo, null, 'login_failed', 'Invalid credentials for: ' . $login_id);
            return ['success' => false, 'message' => '아이디 또는 비밀번호가 올바르지 않습니다.'];
        }

        // 계정 잠금 확인
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $unlock_time = date('Y-m-d H:i:s', strtotime($user['locked_until']));
            return ['success' => false, 'message' => "계정이 잠겨있습니다. {$unlock_time}에 다시 시도해주세요."];
        }

        // 비밀번호 검증
        if (!password_verify($password, $user['password_hash'])) {
            // 로그인 실패 횟수 증가
            $login_attempts = $user['login_attempts'] + 1;
            $lock_until = null;

            // 5회 실패 시 30분 잠금
            if ($login_attempts >= 5) {
                $lock_until = date('Y-m-d H:i:s', time() + 1800); // 30분 후
            }

            $stmt = $pdo->prepare("
                UPDATE " . table('users') . "
                SET login_attempts = ?, locked_until = ?
                WHERE id = ?
            ");
            $stmt->execute([$login_attempts, $lock_until, $user['id']]);

            logAuthActivity($pdo, $user['id'], 'login_failed', 'Invalid password');

            return [
                'success' => false,
                'message' => '아이디 또는 비밀번호가 올바르지 않습니다.',
                'attempts' => $login_attempts
            ];
        }

        // 로그인 성공 처리
        session_regenerate_id(true);

        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // 로그인 성공 업데이트
        $remember_token = isset($data['remember_me']) ? bin2hex(random_bytes(32)) : null;

        $stmt = $pdo->prepare("
            UPDATE " . table('users') . "
            SET login_attempts = 0, locked_until = NULL, last_login = NOW(), remember_token = ?
            WHERE id = ?
        ");
        $stmt->execute([$remember_token, $user['id']]);

        // Remember Me 쿠키 설정
        if ($remember_token) {
            setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }

        // 로그 기록
        logAuthActivity($pdo, $user['id'], 'login_success', 'User logged in');

        return ['success' => true, 'message' => '로그인 성공'];

    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다.'];
    }
}

/**
 * 사용자 데이터 검증
 */
function validateUserData($data) {
    // 사용자명 검증 (3-20자, 영문자+숫자+언더스코어)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
        return ['valid' => false, 'message' => '사용자명은 3-20자의 영문자, 숫자, 언더스코어만 사용 가능합니다.'];
    }

    // 이메일 검증
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => '올바른 이메일 주소를 입력해주세요.'];
    }

    // 이름 검증 (2-50자)
    if (strlen($data['name']) < 2 || strlen($data['name']) > 50) {
        return ['valid' => false, 'message' => '이름은 2-50자 사이여야 합니다.'];
    }

    // 비밀번호 검증
    $password = $data['password'];
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => '비밀번호는 8자리 이상이어야 합니다.'];
    }

    if (!preg_match('/[a-zA-Z]/', $password) ||
        !preg_match('/\d/', $password) ||
        !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return ['valid' => false, 'message' => '비밀번호는 영문자, 숫자, 특수문자를 모두 포함해야 합니다.'];
    }

    // 비밀번호 확인
    if ($password !== $data['password_confirm']) {
        return ['valid' => false, 'message' => '비밀번호가 일치하지 않습니다.'];
    }

    // 전화번호 검증 (선택사항)
    if (!empty($data['phone']) && !preg_match('/^[0-9-+\s()]{10,20}$/', $data['phone'])) {
        return ['valid' => false, 'message' => '올바른 전화번호 형식을 입력해주세요.'];
    }

    return ['valid' => true];
}

/**
 * 인증 활동 로그 기록
 */
function logAuthActivity($pdo, $user_id, $action, $details = '') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . table('auth_logs') . "
            (user_id, action, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log('Failed to log auth activity: ' . $e->getMessage());
    }
}

/**
 * 로그아웃 처리
 */
function handleUserLogout() {
    $pdo = getAuthDatabase();

    if ($pdo && isset($_SESSION['user_id'])) {
        logAuthActivity($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
    }

    // Remember Me 쿠키 삭제
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }

    // 세션 데이터 삭제
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

/**
 * Remember Me 토큰으로 자동 로그인
 */
function handleRememberMeLogin() {
    if (!isset($_COOKIE['remember_token']) || isset($_SESSION['user_logged_in'])) {
        return false;
    }

    $pdo = getAuthDatabase();
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM " . table('users') . "
            WHERE remember_token = ? AND status = 'active'
        ");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 자동 로그인 세션 생성
            session_regenerate_id(true);

            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['auto_login'] = true;

            // 새로운 remember token 생성
            $new_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE " . table('users') . " SET remember_token = ? WHERE id = ?");
            $stmt->execute([$new_token, $user['id']]);

            setcookie('remember_token', $new_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);

            logAuthActivity($pdo, $user['id'], 'auto_login', 'Remember me login');

            return true;
        }
    } catch (PDOException $e) {
        error_log('Remember me login error: ' . $e->getMessage());
    }

    return false;
}

/**
 * 사용자 권한 확인
 */
function hasPermission($required_permission, $board_id = null) {
    if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        return false;
    }

    $user_role = $_SESSION['user_role'] ?? 'guest';

    // 관리자는 모든 권한 보유
    if ($user_role === 'admin') {
        return true;
    }

    // 기본 권한 체크
    $role_permissions = [
        'user' => ['read', 'write_own', 'comment'],
        'moderator' => ['read', 'write', 'comment', 'moderate'],
        'guest' => ['read']
    ];

    $user_permissions = $role_permissions[$user_role] ?? [];

    if (in_array($required_permission, $user_permissions)) {
        return true;
    }

    // 게시판별 특별 권한 확인 (필요 시 구현)
    if ($board_id) {
        return checkBoardPermission($_SESSION['user_id'], $board_id, $required_permission);
    }

    return false;
}

/**
 * 게시판별 권한 확인
 */
function checkBoardPermission($user_id, $board_id, $permission) {
    $pdo = getAuthDatabase();
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT permission_type FROM " . table('board_permissions') . "
            WHERE user_id = ? AND board_id = ? AND permission_type = ?
        ");
        $stmt->execute([$user_id, $board_id, $permission]);

        return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        error_log('Board permission check error: ' . $e->getMessage());
        return false;
    }
}
?>