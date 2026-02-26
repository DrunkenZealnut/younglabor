<?php
/**
 * 비밀번호 재설정 (1회용 - 사용 후 반드시 삭제)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$db = Database::getInstance()->getConnection();
$error = '';
$success = false;

// 현재 admin_user 목록 조회
$stmt = $db->query("SELECT id, username, name, last_login, created_at FROM admin_user ORDER BY id DESC");
$users = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = '잘못된 요청입니다.';
    }

    $userId = (int)($_POST['user_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!$error && empty($userId)) {
        $error = '계정을 선택해주세요.';
    } elseif (!$error && strlen($password) < 8) {
        $error = '비밀번호는 8자 이상이어야 합니다.';
    } elseif (!$error && $password !== $passwordConfirm) {
        $error = '비밀번호가 일치하지 않습니다.';
    }

    if (!$error) {
        $stmt = $db->prepare("UPDATE admin_user SET password_hash = :hash, login_attempts = 0, locked_until = NULL WHERE id = :id");
        $stmt->execute([
            ':hash' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $userId,
        ]);

        if ($stmt->rowCount() > 0) {
            $success = true;
        } else {
            $error = '해당 계정을 찾을 수 없습니다.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 재설정</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
    <style>
        :root { <?php echo getThemeCSSVariables($theme); ?> }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Pretendard', sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .box { background: #fff; border-radius: 16px; padding: 40px; max-width: 480px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { font-size: 22px; margin-bottom: 8px; text-align: center; }
        .subtitle { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 28px; }
        .warn { background: #fef3c7; color: #92400e; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        select, input { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: inherit; }
        select:focus, input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(91,192,222,0.15); }
        .btn { width: 100%; padding: 12px; background: #dc2626; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; font-family: inherit; cursor: pointer; margin-top: 8px; }
        .btn:hover { background: #b91c1c; }
        .error { background: #fef2f2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
        .success { background: #f0fdf4; color: #16a34a; padding: 10px 14px; border-radius: 8px; font-size: 14px; text-align: center; }
        .success a { color: var(--color-primary-dark); font-weight: 600; }
        .user-info { font-size: 12px; color: #94a3b8; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>비밀번호 재설정</h1>
        <p class="subtitle">관리자 비밀번호를 재설정합니다</p>
        <div class="warn">사용 후 이 파일을 반드시 삭제하세요!</div>

        <?php if ($success): ?>
            <div class="success">
                비밀번호가 재설정되었습니다!<br>
                <a href="<?php echo url('admin/login.php'); ?>">로그인하기 &rarr;</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label>계정 선택</label>
                    <select name="user_id" required>
                        <option value="">-- 계정 선택 --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>">
                                <?php echo htmlspecialchars($u['username']); ?> (<?php echo htmlspecialchars($u['name'] ?? '이름없음'); ?>) - ID: <?php echo $u['id']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="user-info">총 <?php echo count($users); ?>개 계정</div>
                </div>
                <div class="form-group">
                    <label>새 비밀번호 (8자 이상)</label>
                    <input type="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label>새 비밀번호 확인</label>
                    <input type="password" name="password_confirm" required minlength="8">
                </div>
                <button type="submit" class="btn">비밀번호 재설정</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
