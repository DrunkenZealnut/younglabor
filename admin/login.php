<?php 
// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // 세션 시작 (bootstrap.php와 동일한 보안 설정)
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
    }
    
    // 데이터베이스 연결만 로드
    require_once 'db.php';
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        header('Location: login.php?error=empty_fields');
        exit;
    }
    
    // 직접 인증 처리 (bootstrap 우회)
    try {
        $stmt = $pdo->prepare("SELECT * FROM " . table('admin_user') . " WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // 세션 생성
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'] ?? 'admin';
            $_SESSION['created_at'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            header('Location: index.php');
            exit;
        } else {
            header('Location: login.php?error=invalid_credentials');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: login.php?error=system_error');
        exit;
    }
} else {
    // 로그인 폼 표시 시에도 동일한 세션 보안 설정
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
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>관리자 로그인</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
  <h3 class="text-center mb-4">관리자 로그인</h3>
  
  <?php if (isset($_GET['logout'])): ?>
    <div class="alert alert-success" role="alert">
      성공적으로 로그아웃되었습니다.
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['timeout'])): ?>
    <div class="alert alert-warning" role="alert">
      비활성으로 인해 세션이 만료되었습니다. 다시 로그인해주세요.
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['security'])): ?>
    <div class="alert alert-danger" role="alert">
      보안상의 이유로 세션이 종료되었습니다. 다시 로그인해주세요.
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['expired'])): ?>
    <div class="alert alert-info" role="alert">
      세션이 만료되었습니다. 다시 로그인해주세요.
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['error'])): ?>
    <?php 
    $error_msg = '';
    switch($_GET['error']) {
      case 'invalid_credentials':
        $error_msg = '아이디 또는 비밀번호가 올바르지 않습니다.';
        break;
      case 'empty_fields':
        $error_msg = '아이디와 비밀번호를 모두 입력해주세요.';
        break;
      case 'system_error':
        $error_msg = '시스템 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
        break;
      default:
        $error_msg = '로그인 중 오류가 발생했습니다.';
    }
    ?>
    <div class="alert alert-danger" role="alert">
      <?= htmlspecialchars($error_msg) ?>
    </div>
  <?php endif; ?>
  
  <form method="POST" action="login.php">
    <div class="mb-3">
      <label for="username" class="form-label">아이디</label>
      <input type="text" id="username" name="username" class="form-control" autocomplete="username" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">비밀번호</label>
      <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">로그인</button>
  </form>
</div>
</body>
</html>