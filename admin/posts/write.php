<?php
// 게시글 작성 페이지
require_once '../bootstrap.php';

// 관리자 사용자 이름 가져오기
$admin_username = $_SESSION['admin_username'] ?? '관리자';

// 사용 가능한 게시판 테이블들과 해당 게시판명 정의
$board_tables = [
    'hopec_notices' => '공지사항',
    'hopec_press' => '언론보도', 
    'hopec_newsletter' => '소식지',
    'hopec_gallery' => '갤러리',
    'hopec_resources' => '자료실'
];

// 게시판 목록용 배열 생성
$boards = [];
$board_id = 1;
foreach ($board_tables as $table => $name) {
    $boards[] = ['id' => $board_id, 'board_name' => $name, 'table_name' => $table];
    $board_id++;
}

// 게시글 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 폼 데이터 가져오기
    $board_id = (int)$_POST['board_id'];
    $title = trim($_POST['title']);
    $content = $_POST['content'] ?? '';
    $author = trim($_POST['author']);
    
    // 기본적인 XSS 방지
    $content = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $content);
    $content = preg_replace('/javascript:/i', '', $content);
    $content = preg_replace('/on\w+\s*=/i', '', $content);
    
    // 폼 유효성 검사
    $errors = [];
    
    if (empty($board_id) || !isset($boards[$board_id - 1])) {
        $errors[] = "게시판을 선택해주세요.";
    }
    
    if (empty($title)) {
        $errors[] = "제목을 입력해주세요.";
    }
    
    if (empty($author)) {
        $errors[] = "작성자를 입력해주세요.";
    }
    
    // 오류가 없으면 게시글 저장
    if (empty($errors)) {
        try {
            // 선택된 게시판의 테이블명 가져오기
            $selected_board = $boards[$board_id - 1];
            $table_name = $selected_board['table_name'];
            
            // G5 테이블 구조에 맞게 데이터 삽입 (필수 필드만)
            $sql = "INSERT INTO {$table_name} (
                wr_subject, wr_content, wr_name, wr_datetime, wr_ip, 
                wr_num, wr_reply, wr_parent, wr_is_comment, wr_comment, wr_comment_reply, 
                ca_name, wr_option, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit, 
                wr_hit, wr_good, wr_nogood, mb_id, wr_password, wr_email, wr_homepage, 
                wr_file, wr_last, wr_facebook_user, wr_twitter_user, 
                wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10
            ) VALUES (
                ?, ?, ?, NOW(), ?, 
                0, '', 0, 0, 0, '', 
                '', '', '', '', 0, 0, 
                0, 0, 0, '', '', '', '', 
                0, '', '', '', 
                '', '', '', '', '', '', '', '', '', ''
            )";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $title, 
                $content, 
                $author, 
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = '게시글이 성공적으로 작성되었습니다.';
                header("Location: list.php");
                exit;
            } else {
                $errors[] = "게시글 저장에 실패했습니다.";
            }
            
        } catch (PDOException $e) {
            $errors[] = "데이터베이스 오류: " . $e->getMessage();
        }
    }
}

// 페이지 제목 설정
$page_title = '새 게시글 작성';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?> - 우동615 관리자</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body { min-height: 100vh; display: flex; font-family: 'Segoe UI', sans-serif; }
    .sidebar { width: 220px; background-color: #343a40; color: white; min-height: 100vh; }
    .sidebar a { color: white; padding: 12px 16px; display: block; text-decoration: none; transition: background-color 0.2s; }
    .sidebar a:hover { background-color: #495057; }
    .sidebar a.active { background-color: #0d6efd; }
    .main-content { flex-grow: 1; padding: 30px; background-color: #f8f9fa; }
    .sidebar .logo { font-weight: bold; font-size: 1.3rem; padding: 16px; border-bottom: 1px solid #495057; }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">
    <a href="/admin/index.php" class="text-white text-decoration-none">우동615 관리자</a>
  </div>
  <a href="/admin/index.php">📊 대시보드</a>
  <a href="/admin/posts/list.php" class="active">📝 게시글 관리</a>
  <a href="/admin/boards/list.php">📋 게시판 관리</a>
  <a href="/admin/menu/list.php">🧭 메뉴 관리</a>
  <a href="/admin/inquiries/list.php">📬 문의 관리</a>
  <a href="/admin/events/list.php">📅 행사 관리</a>
  <a href="/admin/files/list.php">📎 자료실 관리</a>
  <a href="/admin/settings/site_settings.php">🎨 디자인 설정</a>
  <a href="/admin/system/performance.php">⚡ 성능 모니터링</a>
  <a href="/admin/logout.php">🚪 로그아웃</a>
</div>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <!-- 메시지 표시 -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- 상단 네비게이션 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index.php">관리자</a></li>
                <li class="breadcrumb-item"><a href="list.php">게시글 관리</a></li>
                <li class="breadcrumb-item active">새 게시글 작성</li>
            </ol>
        </nav>
        
        <div class="btn-group">
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-list"></i> 목록
            </a>
        </div>
    </div>

    <!-- 게시글 작성 폼 -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title mb-0">
                <i class="bi bi-pencil-square"></i> 새 게시글 작성
            </h2>
        </div>
        
        <div class="card-body">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="board_id" class="form-label">게시판 <span class="text-danger">*</span></label>
                        <select class="form-select" id="board_id" name="board_id" required>
                            <option value="">게시판을 선택하세요</option>
                            <?php foreach ($boards as $board): ?>
                                <option value="<?= $board['id'] ?>" <?= (isset($_POST['board_id']) && $_POST['board_id'] == $board['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($board['board_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label">작성자 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="author" name="author" 
                               value="<?= htmlspecialchars($_POST['author'] ?? $admin_username) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">내용</label>
                    <textarea class="form-control" id="content" name="content" rows="15"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <div>
                        <a href="list.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 취소
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 작성 완료
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>