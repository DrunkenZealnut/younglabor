<?php
/**
 * 관리자 로그인 페이지
 */
require_once __DIR__ . '/config.php';

// 이미 로그인되어 있으면 대시보드로
if (!empty($_SESSION['admin_user_id'])) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$error = '';
$expired = isset($_GET['expired']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '사용자명과 비밀번호를 입력해주세요.';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM admin_user WHERE username = :username AND is_active = 1 AND status = 'active' ORDER BY id DESC LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = '사용자명 또는 비밀번호가 올바르지 않습니다.';
        } elseif ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            $error = "계정이 잠겨있습니다. {$remaining}분 후 다시 시도해주세요.";
        } elseif (!password_verify($password, $user['password_hash'])) {
            // 잠금 만료 후 첫 시도면 카운터 리셋
            $attempts = $user['login_attempts'];
            if ($user['locked_until'] && strtotime($user['locked_until']) <= time()) {
                $attempts = 0;
            }
            $attempts += 1;
            $lockUntil = null;
            if ($attempts >= ADMIN_MAX_LOGIN_ATTEMPTS) {
                $lockUntil = date('Y-m-d H:i:s', time() + ADMIN_LOCKOUT_DURATION);
            }
            $stmt = $db->prepare("UPDATE admin_user SET login_attempts = :attempts, locked_until = :lock WHERE id = :id");
            $stmt->execute([':attempts' => $attempts, ':lock' => $lockUntil, ':id' => $user['id']]);

            if ($lockUntil) {
                $lockMinutes = (int)(ADMIN_LOCKOUT_DURATION / 60);
                $error = "로그인 시도 횟수를 초과하여 계정이 {$lockMinutes}분간 잠겼습니다.";
            } else {
                $error = '사용자명 또는 비밀번호가 올바르지 않습니다.';
            }
        } else {
            // 로그인 성공
            session_regenerate_id(true);
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['name'] ?? $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_last_activity'] = time();
            $_SESSION['admin_session_regenerated'] = time();

            // 로그인 시도 횟수 초기화 + 마지막 로그인 기록
            $stmt = $db->prepare("UPDATE admin_user SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);

            header('Location: ' . url('admin/dashboard.php'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인 - <?php echo htmlspecialchars($site['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
    <style>
        :root { <?php echo getThemeCSSVariables($theme); ?> }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Pretendard', sans-serif;
            background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .login-header { text-align: center; margin-bottom: 32px; }
        .login-header h1 { font-size: 22px; color: #1e293b; margin-bottom: 6px; }
        .login-header p { font-size: 14px; color: #64748b; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
        }
        input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(91,192,222,0.15); }
        .btn {
            width: 100%;
            padding: 12px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: var(--color-primary-dark); }
        .error-msg { background: #fef2f2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 18px; }
        .info-msg { background: #fffbeb; color: #d97706; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 18px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #64748b; font-size: 13px; text-decoration: none; }
        .back-link a:hover { color: var(--color-primary-dark); }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <h1>관리자 로그인</h1>
            <p><?php echo htmlspecialchars($site['name']); ?></p>
        </div>

        <?php if ($expired): ?>
            <div class="info-msg">세션이 만료되었습니다. 다시 로그인해주세요.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>사용자명</label>
                <input type="text" name="username" required autofocus autocomplete="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>비밀번호</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">로그인</button>
        </form>

        <div class="back-link">
            <a href="<?php echo url(''); ?>">&larr; 사이트로 돌아가기</a>
        </div>
    </div>
</body>
</html>
