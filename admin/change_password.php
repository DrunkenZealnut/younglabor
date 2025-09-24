<?php
// Admin 시스템 기본 설정
require_once 'bootstrap.php';

// admin_id 설정 (세션에서 admin_user_id 사용)
$admin_id = $_SESSION['admin_user_id'] ?? null;
if (!$admin_id && isset($_SESSION['admin_username'])) {
    // 사용자 이름으로 ID를 조회
    try {
        $stmt = $pdo->prepare("SELECT id FROM hopec_admin_user WHERE username = ?");
        $stmt->execute([$_SESSION['admin_username']]);
        $result = $stmt->fetch();
        if ($result) {
            $admin_id = $result['id'];
            $_SESSION['admin_user_id'] = $admin_id;
        }
    } catch (PDOException $e) {
        // 오류 처리
        $error = '사용자 정보를 가져오는 중 오류가 발생했습니다.';
    }
}

$message = '';
$error = '';
$success = false;

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // 필수 필드 확인
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = '모든 필드를 입력해주세요.';
    } 
    // 새 비밀번호와 확인 비밀번호 일치 확인
    elseif ($new_password !== $confirm_password) {
        $error = '새 비밀번호와 확인 비밀번호가 일치하지 않습니다.';
    } 
    else {
        try {
            // 현재 비밀번호 확인
            $stmt = $pdo->prepare("SELECT password_hash FROM hopec_admin_user WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($current_password, $admin['password_hash'])) {
                // 새 비밀번호 해시 생성
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // 비밀번호 업데이트
                $updateStmt = $pdo->prepare("UPDATE hopec_admin_user SET password_hash = ? WHERE id = ?");
                $updateStmt->execute([$hash, $admin_id]);
                
                $success = true;
                $message = '비밀번호가 성공적으로 변경되었습니다.';
            } else {
                $error = '현재 비밀번호가 일치하지 않습니다.';
            }
        } catch (PDOException $e) {
            $error = '오류가 발생했습니다: ' . $e->getMessage();
        }
    }
}

// 페이지 제목
$page_title = '비밀번호 변경 - ' . $admin_title;
$current_menu = 'change_password';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { 
            min-height: 100vh; 
            display: flex; 
            font-family: 'Segoe UI', sans-serif; 
        }
        .sidebar { 
            width: 220px; 
            min-width: 220px; 
            background-color: #343a40; 
            color: white; 
            padding: 0; 
            position: fixed; 
            height: 100vh; 
            overflow-y: auto; 
            overflow-x: hidden;
        }
        .sidebar a { 
            color: white; 
            padding: 12px 16px; 
            text-decoration: none; 
            display: block; 
            white-space: nowrap; 
            text-overflow: ellipsis; 
            overflow: hidden;
        }
        .sidebar a:hover { background-color: #495057; }
        .sidebar a.active { background-color: #0d6efd; }
        .main-content { 
            flex-grow: 1; 
            flex-basis: 0; 
            padding: 30px; 
            background-color: #f8f9fa; 
            min-width: 0; 
            margin-left: 220px;
        }
        .sidebar .logo { 
            font-weight: bold; 
            font-size: 1.3rem; 
            padding: 20px 16px; 
            border-bottom: 1px solid #495057; 
            margin-bottom: 0;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            max-width: 600px;
            margin: 0 auto;
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1 class="mb-4">🔐 비밀번호 변경</h1>
        <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">관리자 비밀번호 변경</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($message) ?>
                                <div class="text-center mt-3">
                                    <a href="<?= admin_url('index.php') ?>" class="btn btn-primary">관리자 대시보드로 돌아가기</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <?php if ($message): ?>
                                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                            <?php endif; ?>
                            
                            <form method="post" action="" id="password-form">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">현재 비밀번호</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">새 비밀번호</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">비밀번호 확인</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-match-message mt-1"></div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">비밀번호 변경</button>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="<?= admin_url('index.php') ?>" class="btn btn-link">관리자 대시보드로 돌아가기</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 비밀번호 표시/숨김 토글
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            });
            
            // 비밀번호 일치 확인
            const confirmInput = document.getElementById('confirm_password');
            const matchMessage = document.querySelector('.password-match-message');
            const newPasswordInput = document.getElementById('new_password');
            
            function checkPasswordMatch() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmInput.value;
                
                if (confirmPassword === '') {
                    matchMessage.textContent = '';
                    matchMessage.className = 'password-match-message mt-1';
                } else if (newPassword === confirmPassword) {
                    matchMessage.textContent = '비밀번호가 일치합니다.';
                    matchMessage.className = 'password-match-message mt-1 text-success';
                } else {
                    matchMessage.textContent = '비밀번호가 일치하지 않습니다.';
                    matchMessage.className = 'password-match-message mt-1 text-danger';
                }
            }
            
            newPasswordInput.addEventListener('input', checkPasswordMatch);
            confirmInput.addEventListener('input', checkPasswordMatch);
            
            // 폼 제출 전 비밀번호 유효성 확인
            const form = document.getElementById('password-form');
            form.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = confirmInput.value;
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('새 비밀번호와 확인 비밀번호가 일치하지 않습니다.');
                    return false;
                }
            });
        });
    </script>
</body>
</html> 