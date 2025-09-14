<?php
// 게시글 수정 페이지
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../bootstrap.php';

// 게시글 ID와 테이블 확인
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$table_name = isset($_GET['table']) ? $_GET['table'] : '';

if ($post_id <= 0 || empty($table_name)) {
    header("Location: list.php");
    exit;
}

// 허용된 테이블명인지 확인
$allowed_tables = [
    'hopec_notices' => '공지사항',
    'hopec_press' => '언론보도', 
    'hopec_newsletter' => '소식지',
    'hopec_gallery' => '갤러리',
    'hopec_resources' => '자료실'
];

if (!array_key_exists($table_name, $allowed_tables)) {
    header("Location: list.php");
    exit;
}

// 폼 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $author = trim($_POST['author'] ?? '');
    
    if (!empty($title)) {
        try {
            $sql = "UPDATE {$table_name} SET 
                    wr_subject = ?, wr_content = ?, wr_name = ?
                    WHERE wr_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $author, $post_id]);
            
            $_SESSION['success_message'] = '게시글이 성공적으로 수정되었습니다.';
            header("Location: view.php?id=" . $post_id . "&table=" . urlencode($table_name));
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['error_message'] = '게시글 수정 중 오류가 발생했습니다: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = '제목을 입력해주세요.';
    }
}

try {
    // 게시글 정보 조회
    $sql = "SELECT 
                wr_id as id,
                wr_subject as title,
                wr_content as content,
                wr_name as author,
                wr_hit as hit_count,
                wr_datetime as created_at,
                wr_ip as ip_address
            FROM {$table_name} 
            WHERE wr_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header("Location: list.php?error=not_found");
        exit;
    }
    
    // 게시판 이름 설정
    $board_name = $allowed_tables[$table_name];
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = '게시글을 불러올 수 없습니다: ' . $e->getMessage();
    header("Location: list.php");
    exit;
}

// 페이지 제목 설정
$page_title = $post ? '게시글 수정: ' . htmlspecialchars($post['title']) : '게시글을 찾을 수 없습니다';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= $page_title ?> - 우동615 관리자</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body { min-height: 100vh; display: flex; font-family: 'Segoe UI', sans-serif; }
    .sidebar { 
      width: 220px; 
      min-width: 220px; 
      max-width: 220px; 
      flex-shrink: 0;
      background-color: #343a40; 
      color: white; 
      min-height: 100vh; 
      overflow-x: hidden;
    }
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
    <?php 
    // 간단한 flash message 처리
    if (isset($_SESSION['success_message'])): 
    ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php 
        unset($_SESSION['success_message']);
    endif; 
    
    if (isset($_SESSION['error_message'])): 
    ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php 
        unset($_SESSION['error_message']);
    endif; 
    ?>

    <?php if (!$post): ?>
        <div class="alert alert-danger">
            <h4>게시글을 찾을 수 없습니다</h4>
            <p>요청하신 게시글이 존재하지 않거나 삭제되었습니다.</p>
            <a href="list.php" class="btn btn-primary">목록으로 돌아가기</a>
        </div>
    <?php else: ?>
        <!-- 상단 네비게이션 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/index.php">관리자</a></li>
                    <li class="breadcrumb-item"><a href="list.php">게시글 관리</a></li>
                    <li class="breadcrumb-item active">게시글 수정</li>
                </ol>
            </nav>
            
            <div class="btn-group">
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-list"></i> 목록
                </a>
                <a href="view.php?id=<?= $post['id'] ?>&table=<?= urlencode($table_name) ?>" class="btn btn-outline-primary">
                    <i class="bi bi-eye"></i> 보기
                </a>
            </div>
        </div>

        <!-- 게시글 수정 폼 -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title mb-0">
                    <i class="bi bi-pencil"></i> 게시글 수정
                </h2>
                <small class="text-muted">
                    게시판: <?= htmlspecialchars($board_name) ?> | 
                    작성일: <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?> | 
                    조회수: <?= number_format($post['hit_count'] ?? 0) ?>
                </small>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($post['title']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="author" class="form-label">작성자</label>
                            <input type="text" class="form-control" id="author" name="author" 
                                   value="<?= htmlspecialchars($post['author'] ?? '관리자') ?>">
                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="content" class="form-label">내용</label>
                        <textarea class="form-control" id="content" name="content" rows="15"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 취소
                            </a>
                            <a href="view.php?id=<?= $post['id'] ?>&table=<?= urlencode($table_name) ?>" class="btn btn-outline-info">
                                <i class="bi bi-eye"></i> 미리보기
                            </a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> 수정 완료
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 게시글 정보 -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> 게시글 정보
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>게시글 ID:</strong> <?= $post['id'] ?>
                    </div>
                    <div class="col-md-4">
                        <strong>작성일:</strong> <?= date('Y-m-d H:i:s', strtotime($post['created_at'])) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>조회수:</strong> <?= number_format($post['hit_count'] ?? 0) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>게시판:</strong> <?= htmlspecialchars($board_name) ?>
                    </div>
                </div>
                <?php if (!empty($post['ip_address'])): ?>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>작성 IP:</strong> <?= htmlspecialchars($post['ip_address']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
