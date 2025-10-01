<?php
/**
 * 이메일 인증 기반 사용자 인증 처리 핸들러
 * Email-based User Authentication Handler
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
 * 간소화된 이메일 인증 회원가입 처리 (기존 members 테이블 활용)
 */
function handleUserRegistration($data) {
    // 디버깅 로그를 파일로 저장
    $debug_log = __DIR__ . '/logs/registration_debug.log';
    $log_msg = date('Y-m-d H:i:s') . " - 회원가입 시작\n";
    file_put_contents($debug_log, $log_msg, FILE_APPEND);

    // CSRF 토큰 검증
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($data['csrf_token'] ?? '')) {
        $log_msg = date('Y-m-d H:i:s') . " - CSRF 토큰 불일치\n";
        file_put_contents($debug_log, $log_msg, FILE_APPEND);
        return ['success' => false, 'message' => '잘못된 요청입니다. (CSRF)'];
    }

    $log_msg = date('Y-m-d H:i:s') . " - CSRF 토큰 검증 통과\n";
    file_put_contents($debug_log, $log_msg, FILE_APPEND);

    // 필수 필드 검증 (간소화)
    $required_fields = ['email', 'name', 'password', 'password_confirm'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $log_msg = date('Y-m-d H:i:s') . " - 필수 필드 누락: $field\n";
            file_put_contents($debug_log, $log_msg, FILE_APPEND);
            return ['success' => false, 'message' => '모든 필수 항목을 입력해주세요.'];
        }
    }

    $log_msg = date('Y-m-d H:i:s') . " - 필수 필드 검증 통과\n";
    file_put_contents($debug_log, $log_msg, FILE_APPEND);

    // 이용약관 동의 검증
    if (!isset($data['agree_privacy']) || !isset($data['agree_terms'])) {
        $log_msg = date('Y-m-d H:i:s') . " - 약관 동의 누락\n";
        file_put_contents($debug_log, $log_msg, FILE_APPEND);
        return ['success' => false, 'message' => '개인정보 처리방침과 이용약관에 동의해주세요.'];
    }

    $log_msg = date('Y-m-d H:i:s') . " - 약관 동의 검증 통과\n";
    file_put_contents($debug_log, $log_msg, FILE_APPEND);

    // 데이터 검증
    $validation_result = validateUserData($data);
    if (!$validation_result['valid']) {
        $log_msg = date('Y-m-d H:i:s') . " - 데이터 검증 실패: " . $validation_result['message'] . "\n";
        file_put_contents($debug_log, $log_msg, FILE_APPEND);
        return ['success' => false, 'message' => $validation_result['message']];
    }

    $log_msg = date('Y-m-d H:i:s') . " - 데이터 검증 통과\n";
    file_put_contents($debug_log, $log_msg, FILE_APPEND);

    // 데이터베이스 연결
    $pdo = getAuthDatabase();
    if (!$pdo) {
        $log_msg = date('Y-m-d H:i:s') . " - DB 연결 실패\n";
        file_put_contents($debug_log, $log_msg, FILE_APPEND);
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다. (DB 연결 실패)'];
    }

    $log_msg = date('Y-m-d H:i:s') . " - DB 연결 성공\n";
    file_put_contents($debug_log, $log_msg, FILE_APPEND);

    try {
        $log_msg = date('Y-m-d H:i:s') . " - 이메일 중복 검사 시작\n";
        file_put_contents($debug_log, $log_msg, FILE_APPEND);

        // 이메일 중복 검사
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . table('members') . " WHERE mb_email = ?");
        $stmt->execute([$data['email']]);

        if ($stmt->fetchColumn() > 0) {
            $log_msg = date('Y-m-d H:i:s') . " - 이메일 중복\n";
            file_put_contents($debug_log, $log_msg, FILE_APPEND);
            return ['success' => false, 'message' => '이미 사용 중인 이메일입니다.'];
        }

        $log_msg = date('Y-m-d H:i:s') . " - 이메일 중복 검사 통과\n";
        file_put_contents($debug_log, $log_msg, FILE_APPEND);

        // 다음 회원번호 생성
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(mb_no), 0) + 1 FROM " . table('members'));
        $stmt->execute();
        $next_mb_no = $stmt->fetchColumn();

        // 이메일에서 사용자명 생성 (@ 앞부분 + 숫자)
        $email_username = explode('@', $data['email'])[0];
        $username = $email_username;

        // 사용자명 중복 확인 및 유니크하게 만들기
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . table('members') . " WHERE mb_id = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() == 0) break;
            $username = $email_username . $counter;
            $counter++;
        }

        // 사용자 등록 (이메일 인증 대기 상태)
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));
        $current_time = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            INSERT INTO " . table('members') . "
            (mb_no, mb_id, mb_password, mb_name, mb_nick, mb_nick_date, mb_email, mb_level, mb_datetime, mb_ip,
             mb_email_certify2, mb_email_verified, mb_status, mb_terms_agreed, mb_privacy_agreed,
             mb_homepage, mb_tel, mb_hp, mb_signature, mb_profile, mb_memo,
             mb_sex, mb_birth, mb_certify, mb_adult, mb_dupinfo, mb_zip1, mb_zip2, mb_addr1, mb_addr2, mb_addr3, mb_addr_jibeon,
             mb_recommend, mb_point, mb_login_ip, mb_leave_date, mb_intercept_date, mb_lost_certify,
             mb_mailling, mb_sms, mb_open, mb_open_date, mb_memo_call, mb_today_login,
             mb_1, mb_2, mb_3, mb_4, mb_5, mb_6, mb_7, mb_8, mb_9, mb_10)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 0, 'pending', 1, 1,
                    '', '', '', '', '', '',
                    '', '', '', 0, '', '', '', '', '', '', '',
                    '', 0, '', '', '', '',
                    0, 0, 0, '0000-00-00', '', '0000-00-00 00:00:00',
                    '', '', '', '', '', '', '', '', '', '')
        ");

        $result = $stmt->execute([
            $next_mb_no,
            $username,
            $password_hash,
            $data['name'],
            $data['name'], // 닉네임을 이름과 동일하게 설정
            $current_time, // mb_nick_date
            $data['email'],
            $current_time,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $verification_token
        ]);

        if ($result) {
            $log_msg = date('Y-m-d H:i:s') . " - 회원 등록 성공, 이메일 발송 시도\n";
            file_put_contents($debug_log, $log_msg, FILE_APPEND);

            // 이메일 인증 메일 발송
            $email_sent = sendVerificationEmail($data['email'], $data['name'], $verification_token);

            $log_msg = date('Y-m-d H:i:s') . " - 이메일 발송 결과: " . ($email_sent ? '성공' : '실패') . "\n";
            file_put_contents($debug_log, $log_msg, FILE_APPEND);

            // 로그 기록
            logAuthActivity($pdo, $next_mb_no, 'user_registered', 'User registered: ' . $username . ' (email: ' . $data['email'] . ')');

            // 개발 환경에서는 이메일 발송 실패해도 성공으로 처리
            return [
                'success' => true,
                'message' => '회원가입이 완료되었습니다.' . (!$email_sent ? ' (이메일 발송 실패 - 개발 환경)' : ' 이메일 인증을 완료해주세요.'),
                'email' => $data['email'],
                'email_sent' => $email_sent
            ];
        } else {
            $log_msg = date('Y-m-d H:i:s') . " - 회원 등록 실패\n";
            file_put_contents($debug_log, $log_msg, FILE_APPEND);
            return ['success' => false, 'message' => '회원가입 중 오류가 발생했습니다.'];
        }

    } catch (PDOException $e) {
        $log_msg = date('Y-m-d H:i:s') . " - PDO 오류: " . $e->getMessage() . " (파일: " . $e->getFile() . ", 라인: " . $e->getLine() . ")\n";
        file_put_contents($debug_log, $log_msg, FILE_APPEND);
        error_log('Registration error: ' . $e->getMessage());
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다. (DB 쿼리 오류)'];
    }
}

/**
 * 로그인 처리 (기존 members 테이블 활용)
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
            SELECT * FROM " . table('members') . "
            WHERE (mb_id = ? OR mb_email = ?) AND mb_status != 'suspended'
        ");
        $stmt->execute([$login_id, $login_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // 로그인 시도 기록
            logAuthActivity($pdo, null, 'login_failed', 'Invalid credentials for: ' . $login_id);
            return ['success' => false, 'message' => '아이디 또는 비밀번호가 올바르지 않습니다.'];
        }

        // 이메일 인증 확인
        if ($user['mb_status'] == 'pending' || $user['mb_email_verified'] == 0) {
            return ['success' => false, 'message' => '이메일 인증이 완료되지 않았습니다. 이메일을 확인해주세요.'];
        }

        // 계정 잠금 확인
        if ($user['mb_locked_until'] && strtotime($user['mb_locked_until']) > time()) {
            $unlock_time = date('Y-m-d H:i:s', strtotime($user['mb_locked_until']));
            return ['success' => false, 'message' => "계정이 잠겨있습니다. {$unlock_time}에 다시 시도해주세요."];
        }

        // 비밀번호 검증
        if (!password_verify($password, $user['mb_password'])) {
            // 로그인 실패 횟수 증가
            $login_attempts = $user['mb_login_attempts'] + 1;
            $lock_until = null;

            // 5회 실패 시 30분 잠금
            if ($login_attempts >= 5) {
                $lock_until = date('Y-m-d H:i:s', time() + 1800); // 30분 후
            }

            $stmt = $pdo->prepare("
                UPDATE " . table('members') . "
                SET mb_login_attempts = ?, mb_locked_until = ?
                WHERE mb_no = ?
            ");
            $stmt->execute([$login_attempts, $lock_until, $user['mb_no']]);

            logAuthActivity($pdo, $user['mb_no'], 'login_failed', 'Invalid password');

            return [
                'success' => false,
                'message' => '아이디 또는 비밀번호가 올바르지 않습니다.',
                'attempts' => $login_attempts
            ];
        }

        // 로그인 성공 처리
        session_regenerate_id(true);

        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['mb_no'];
        $_SESSION['username'] = $user['mb_id'];
        $_SESSION['user_email'] = $user['mb_email'];
        $_SESSION['user_name'] = $user['mb_name'];
        $_SESSION['user_nick'] = $user['mb_nick'];
        $_SESSION['user_level'] = $user['mb_level'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // 로그인 성공 업데이트
        $remember_token = isset($data['remember_me']) ? bin2hex(random_bytes(32)) : null;

        $stmt = $pdo->prepare("
            UPDATE " . table('members') . "
            SET mb_login_attempts = 0, mb_locked_until = NULL, mb_today_login = NOW(),
                mb_login_ip = ?, mb_remember_token = ?, mb_last_activity = NOW()
            WHERE mb_no = ?
        ");
        $stmt->execute([
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $remember_token,
            $user['mb_no']
        ]);

        // Remember Me 쿠키 설정
        if ($remember_token) {
            setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }

        // 로그 기록
        logAuthActivity($pdo, $user['mb_no'], 'login_success', 'User logged in');

        return ['success' => true, 'message' => '로그인 성공'];

    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다.'];
    }
}

/**
 * 사용자 데이터 검증 (간소화)
 */
function validateUserData($data) {
    // 이메일 검증
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => '올바른 이메일 주소를 입력해주세요.'];
    }

    // 이름 검증 (2-20자)
    if (strlen($data['name']) < 2 || strlen($data['name']) > 20) {
        return ['valid' => false, 'message' => '이름은 2-20자 사이여야 합니다.'];
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

    return ['valid' => true];
}

/**
 * 이메일 인증 메일 발송 (PHPMailer 사용)
 */
function sendVerificationEmail($email, $name, $token) {
    require_once __DIR__ . '/../includes/email_helpers.php';

    try {
        // 인증 URL 생성
        $site_url = $_ENV['APP_URL'] ?? 'http://localhost';
        $verification_url = $site_url . '/auth/verify.php?token=' . $token;

        $mail = getMailer();
        if (!$mail) {
            error_log('Failed to initialize PHPMailer');
            return false;
        }

        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = '[청년노동자인권센터] 이메일 인증을 완료해주세요';

        $orgName = $_ENV['ORG_NAME_FULL'] ?? '청년노동자인권센터';
        $contactEmail = $_ENV['CONTACT_EMAIL'] ?? 'admin@younglabor.kr';

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Malgun Gothic', sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #84cc16; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; }
                .button { display: inline-block; padding: 15px 30px; background: #84cc16; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$orgName}</h1>
                    <p>회원가입 이메일 인증</p>
                </div>
                <div class='content'>
                    <p>안녕하세요, <strong>{$name}</strong>님!</p>
                    <p>{$orgName}에 가입해주셔서 감사합니다.</p>
                    <p>아래 버튼을 클릭하여 이메일 인증을 완료해주세요:</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$verification_url}' class='button'>이메일 인증하기</a>
                    </div>

                    <p style='text-align: center; color: #666; font-size: 14px;'>
                        또는 아래 링크를 복사하여 브라우저에 붙여넣으세요:<br>
                        <a href='{$verification_url}' style='color: #84cc16;'>{$verification_url}</a>
                    </p>

                    <p style='text-align: center; color: #999; font-size: 12px; margin-top: 20px;'>
                        이 인증 링크는 <strong>24시간</strong> 동안 유효합니다.
                    </p>

                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>

                    <p style='font-size: 12px; color: #999;'>
                        본인이 가입하지 않았다면 이 메일을 무시하세요.<br>
                        문의사항이 있으시면 {$contactEmail}로 연락주세요.
                    </p>
                </div>
                <div class='footer'>
                    <p>{$orgName}</p>
                    <p>{$site_url}</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "
{$orgName} 회원가입 이메일 인증

안녕하세요, {$name}님!

{$orgName}에 가입해주셔서 감사합니다.

아래 링크를 클릭하여 이메일 인증을 완료해주세요:
{$verification_url}

이 인증 링크는 24시간 동안 유효합니다.
본인이 가입하지 않았다면 이 메일을 무시하세요.

{$orgName}
{$site_url}
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Email sending error: ' . $e->getMessage());
        return false;
    }
}

/**
 * 이메일 인증 처리
 */
function handleEmailVerification($token) {
    if (empty($token)) {
        return ['success' => false, 'message' => '인증 토큰이 없습니다.'];
    }

    $pdo = getAuthDatabase();
    if (!$pdo) {
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다.'];
    }

    try {
        // 토큰으로 사용자 찾기
        $stmt = $pdo->prepare("
            SELECT * FROM " . table('members') . "
            WHERE mb_email_certify2 = ? AND mb_email_verified = 0
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => '유효하지 않거나 이미 인증된 토큰입니다.'];
        }

        // 토큰 만료 확인 (24시간)
        $created_time = strtotime($user['mb_datetime']);
        if (time() - $created_time > 86400) {
            return ['success' => false, 'message' => '인증 토큰이 만료되었습니다. 다시 가입해주세요.'];
        }

        // 이메일 인증 완료
        $stmt = $pdo->prepare("
            UPDATE " . table('members') . "
            SET mb_email_verified = 1, mb_email_certify = NOW(), mb_status = 'active',
                mb_level = 2, mb_email_certify2 = ''
            WHERE mb_no = ?
        ");
        $result = $stmt->execute([$user['mb_no']]);

        if ($result) {
            // 로그 기록
            logAuthActivity($pdo, $user['mb_no'], 'email_verified', 'Email verification completed');

            return ['success' => true, 'message' => '이메일 인증이 완료되었습니다. 로그인해주세요.'];
        } else {
            return ['success' => false, 'message' => '인증 처리 중 오류가 발생했습니다.'];
        }

    } catch (PDOException $e) {
        error_log('Email verification error: ' . $e->getMessage());
        return ['success' => false, 'message' => '시스템 오류가 발생했습니다.'];
    }
}

/**
 * 인증 활동 로그 기록
 */
function logAuthActivity($pdo, $user_id, $action, $details = '') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . table('member_auth_logs') . "
            (mb_no, action, details, ip_address, user_agent, created_at)
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
            SELECT * FROM " . table('members') . "
            WHERE mb_remember_token = ? AND mb_status = 'active' AND mb_email_verified = 1
        ");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 자동 로그인 세션 생성
            session_regenerate_id(true);

            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['mb_no'];
            $_SESSION['username'] = $user['mb_id'];
            $_SESSION['user_email'] = $user['mb_email'];
            $_SESSION['user_name'] = $user['mb_name'];
            $_SESSION['user_nick'] = $user['mb_nick'];
            $_SESSION['user_level'] = $user['mb_level'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['auto_login'] = true;

            // 새로운 remember token 생성
            $new_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE " . table('members') . " SET mb_remember_token = ? WHERE mb_no = ?");
            $stmt->execute([$new_token, $user['mb_no']]);

            setcookie('remember_token', $new_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);

            logAuthActivity($pdo, $user['mb_no'], 'auto_login', 'Remember me login');

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

    $user_level = $_SESSION['user_level'] ?? 1;

    // 관리자 레벨 (9)은 모든 권한 보유
    if ($user_level >= 9) {
        return true;
    }

    // 레벨별 기본 권한 체크
    $level_permissions = [
        1 => ['read'],
        2 => ['read', 'write', 'comment'],
        3 => ['read', 'write', 'comment', 'upload'],
        4 => ['read', 'write', 'comment', 'upload', 'recommend'],
        5 => ['read', 'write', 'comment', 'upload', 'recommend', 'special'],
        8 => ['read', 'write', 'comment', 'upload', 'recommend', 'special', 'moderate']
    ];

    $user_permissions = $level_permissions[$user_level] ?? ['read'];

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
            WHERE mb_no = ? AND board_id = ? AND permission_type = ? AND is_active = 1
        ");
        $stmt->execute([$user_id, $board_id, $permission]);

        return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        error_log('Board permission check error: ' . $e->getMessage());
        return false;
    }
}
?>