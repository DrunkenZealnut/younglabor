<?php
/**
 * 최초 관리자 계정 생성
 * admin_user 테이블이 비어있을 때만 접근 가능
 */
require_once __DIR__ . '/config.php';

$db = Database::getInstance()->getConnection();

// 이미 계정이 있으면 로그인으로 리다이렉트
$stmt = $db->query("SELECT COUNT(*) FROM admin_user");
if ($stmt->fetchColumn() > 0) {
    header('Location: ' . url('admin/login.php'));
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if (empty($username) || empty($password) || empty($name)) {
        $error = '모든 필드를 입력해주세요.';
    } elseif (strlen($password) < 8) {
        $error = '비밀번호는 8자 이상이어야 합니다.';
    } elseif ($password !== $passwordConfirm) {
        $error = '비밀번호가 일치하지 않습니다.';
    } else {
        $stmt = $db->prepare("
            INSERT INTO admin_user (username, password_hash, email, name, role, status, is_active)
            VALUES (:username, :password_hash, :email, :name, 'admin', 'active', 1)
        ");
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':email' => '',
            ':name' => $name,
        ]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 계정 생성</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
    <style>
        :root { <?php echo getThemeCSSVariables($theme); ?> }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Pretendard', sans-serif;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .setup-box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        h1 { font-size: 22px; margin-bottom: 8px; text-align: center; }
        .subtitle { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 28px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
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
            margin-top: 8px;
        }
        .btn:hover { background: var(--color-primary-dark); }
        .error { background: #fef2f2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
        .success { background: #f0fdf4; color: #16a34a; padding: 10px 14px; border-radius: 8px; font-size: 14px; text-align: center; }
        .success a { color: var(--color-primary-dark); font-weight: 600; }
    </style>
</head>
<body>
    <div class="setup-box">
        <h1>관리자 계정 생성</h1>
        <p class="subtitle">최초 1회만 실행 가능합니다</p>

        <?php if ($success): ?>
            <div class="success">
                계정이 생성되었습니다!<br>
                <a href="<?php echo url('admin/login.php'); ?>">로그인하기 &rarr;</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>사용자명</label>
                    <input type="text" name="username" required autocomplete="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>이름</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>비밀번호 (8자 이상)</label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>비밀번호 확인</label>
                    <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn">계정 생성</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
