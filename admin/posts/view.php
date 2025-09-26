<?php
// 게시글 상세보기 페이지
require_once '../bootstrap.php';
require_once 'attachment_helpers.php';

// 게시글 ID와 board_type 확인
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$board_type = isset($_GET['board_type']) ? $_GET['board_type'] : '';

if ($post_id <= 0 || empty($board_type)) {
    header("Location: list.php");
    exit;
}

// 허용된 board_type 확인 - posts 테이블의 board_type과 일치
$allowed_board_types = [
    'finance_reports' => '재정보고',
    'notices' => '공지사항',
    'press' => '언론보도', 
    'newsletter' => '소식지',
    'gallery' => '갤러리',
    'resources' => '자료실',
    'nepal_travel' => '네팔나눔연대여행'
];

if (!array_key_exists($board_type, $allowed_board_types)) {
    header("Location: list.php");
    exit;
}

try {
    // posts 테이블에서 게시글 정보 조회
    $tableName = get_table_name('posts');
    $sql = "SELECT 
                wr_id as id,
                board_type,
                wr_subject as title,
                wr_content as content,
                wr_name as author,
                wr_hit as hit_count,
                wr_datetime as created_at
            FROM {$tableName} 
            WHERE wr_id = ? AND board_type = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id, $board_type]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header("Location: list.php?error=not_found");
        exit;
    }
    
    // 조회수 증가
    $update_sql = "UPDATE {$tableName} SET wr_hit = wr_hit + 1 WHERE wr_id = ? AND board_type = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$post_id, $board_type]);
    
    // 게시판 이름 설정
    $board_name = $allowed_board_types[$board_type];
    
    // 첨부파일 조회 (board_type도 함께 전달)
    $attachments = getPostAttachments($post_id, $pdo, $board_type);
    
} catch (PDOException $e) {
    $post = null;
    $attachments = [];
    error_log("게시글 조회 오류: " . $e->getMessage());
}

// 페이지 제목 설정
$page_title = $post ? htmlspecialchars($post['title']) : '게시글을 찾을 수 없습니다';
?>

<!DOCTYPE html>
<html lang="ko">
<head>희망씨
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
    .post-meta { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    .post-content { 
      background-color: white; 
      padding: 30px; 
      border-radius: 5px; 
      border: 1px solid #dee2e6;
      min-height: 300px;
      line-height: 1.7;
    }
    .badge-notice { background-color: #dc3545; }
  </style>
</head>
<body>
<?php 
// 현재 메뉴 설정 (게시글 관리 활성화)
$current_menu = 'posts';
include '../includes/sidebar.php'; 
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
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
                    <li class="breadcrumb-item"><a href="<?= admin_url('index.php') ?>">관리자</a></li>
                    <li class="breadcrumb-item"><a href="list.php">게시글 관리</a></li>
                    <li class="breadcrumb-item active">게시글 상세</li>
                </ol>
            </nav>
            
            <div class="btn-group">
                <a href="list.php" class="btn btn-secondary" onclick="goBackToSearch()">
                    <i class="bi bi-list"></i> 목록
                </a>
                <a href="edit.php?id=<?= $post['id'] ?>&board_type=<?= urlencode($board_type) ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> 수정
                </a>
                <a href="list.php?delete=1&id=<?= $post['id'] ?>&board_type=<?= urlencode($board_type) ?>" class="btn btn-danger"
                   onclick="return confirm('정말 삭제하시겠습니까?')">
                    <i class="bi bi-trash"></i> 삭제
                </a>
            </div>
        </div>

        <!-- 게시글 정보 -->
        <div class="card">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <?= htmlspecialchars($post['title']) ?>
                </h1>
            </div>
            
            <div class="card-body">
                <!-- 게시글 메타 정보 -->
                <div class="post-meta">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>게시판:</strong> 
                            <span class="badge bg-secondary"><?= htmlspecialchars($board_name) ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>작성자:</strong> 
                            <?= htmlspecialchars($post['author'] ?? '관리자') ?>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <strong>작성일:</strong> 
                            <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>조회수:</strong> 
                            <?= number_format($post['hit_count'] ?? 0) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>상태:</strong> 
                            <span class="badge bg-success">게시됨</span>
                        </div>
                    </div>
                </div>

                <!-- 게시글 내용 -->
                <div class="post-content">
                    <?php if (!empty($post['content'])): ?>
                        <?= $post['content'] ?>
                    <?php else: ?>
                        <p class="text-muted fst-italic">게시글 내용이 없습니다.</p>
                    <?php endif; ?>
                </div>

                <!-- 첨부파일 섹션 -->
                <?php if (!empty($attachments)): ?>
                    <div class="mt-4">
                        <?= renderAttachmentList($attachments, true) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($post['tags'])): ?>
                    <div class="mt-3">
                        <strong>태그:</strong>
                        <?php 
                        $tags = explode(',', $post['tags']);
                        foreach ($tags as $tag): 
                        ?>
                            <span class="badge bg-light text-dark me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        게시글 ID: <?= $post['id'] ?>
                        <?php if (!empty($post['ip_address'])): ?>
                            | IP: <?= htmlspecialchars($post['ip_address']) ?>
                        <?php endif; ?>
                    </small>
                    
                    <div>
                        <a href="list.php" class="btn btn-outline-secondary btn-sm" onclick="goBackToSearch()">
                            <i class="bi bi-arrow-left"></i> 목록으로
                        </a>
                        <?php if ($post['is_notice'] ?? false): ?>
                            <span class="badge bg-danger ms-2">공지사항</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// 검색 페이지로 돌아가기 기능
function goBackToSearch() {
    const savedSearch = sessionStorage.getItem('admin_posts_search');
    if (savedSearch) {
        const searchData = JSON.parse(savedSearch);
        if (searchData.url) {
            // 저장된 검색 페이지로 돌아가기
            window.location.href = searchData.url;
            return false;
        }
    }
    // 검색 정보가 없으면 기본 목록 페이지로
    return true;
}
</script>
</body>
</html>