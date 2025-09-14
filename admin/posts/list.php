<?php
// 게시글 관리 페이지 - 완전한 기능 구현
require_once '../bootstrap.php';

// 페이지네이션 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// 검색 조건
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'title';
$search_keyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';
$board_filter = isset($_GET['board_id']) ? (int)$_GET['board_id'] : 0;

// 여러 게시판 테이블을 통합하여 조회하는 쿼리
try {
    // 사용 가능한 게시판 테이블들과 해당 게시판명 정의
    $board_tables = [
        'hopec_notices' => '공지사항',
        'hopec_press' => '언론보도',
        'hopec_newsletter' => '소식지',
        'hopec_gallery' => '갤러리',
        'hopec_resources' => '자료실'
    ];
    
    // 게시판 필터 옵션용 배열
    $boards = [];
    $board_id = 1;
    foreach ($board_tables as $table => $name) {
        $boards[] = ['id' => $board_id, 'board_name' => $name, 'table_name' => $table];
        $board_id++;
    }
    
    // UNION을 사용하여 모든 게시판의 게시글을 합쳐서 조회
    $union_parts = [];
    
    // 게시판 필터가 있는 경우 해당 테이블만 조회
    $tables_to_query = $board_tables;
    if ($board_filter > 0) {
        $selected_board = $boards[$board_filter - 1] ?? null;
        if ($selected_board) {
            $tables_to_query = [$selected_board['table_name'] => $selected_board['board_name']];
        }
    }
    
    foreach ($tables_to_query as $table_name => $board_name) {
        // 검색 조건 생성
        $where_conditions = ["1=1"];
        
        if (!empty($search_keyword)) {
            if ($search_type === 'title') {
                $where_conditions[] = "wr_subject LIKE '%{$search_keyword}%'";
            } else if ($search_type === 'content') {
                $where_conditions[] = "wr_content LIKE '%{$search_keyword}%'";
            } else if ($search_type === 'author') {
                $where_conditions[] = "wr_name LIKE '%{$search_keyword}%'";
            }
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $union_parts[] = "
            SELECT 
                wr_id as id,
                '{$board_name}' as board_name,
                wr_subject as title,
                wr_content as content,
                wr_name as author,
                wr_hit as hit_count,
                wr_datetime as created_at,
                0 as is_notice,
                '{$table_name}' as source_table
            FROM {$table_name} 
            WHERE {$where_clause}
        ";
    }
    
    if (empty($union_parts)) {
        $posts = [];
        $total_records = 0;
        $total_pages = 0;
    } else {
        $sql = "(" . implode(") UNION ALL (", $union_parts) . ") ORDER BY created_at DESC LIMIT {$offset}, {$records_per_page}";
        
        // 게시글 조회
        $stmt = $pdo->query($sql);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
        // 총 게시글 수 계산을 위한 COUNT 쿼리 생성
        $count_union_parts = [];
        foreach ($tables_to_query as $table_name => $board_name) {
            $where_conditions = ["1=1"];
            
            if (!empty($search_keyword)) {
                if ($search_type === 'title') {
                    $where_conditions[] = "wr_subject LIKE '%{$search_keyword}%'";
                } else if ($search_type === 'content') {
                    $where_conditions[] = "wr_content LIKE '%{$search_keyword}%'";
                } else if ($search_type === 'author') {
                    $where_conditions[] = "wr_name LIKE '%{$search_keyword}%'";
                }
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            $count_union_parts[] = "SELECT COUNT(*) as cnt FROM {$table_name} WHERE {$where_clause}";
        }
        
        $count_sql = "SELECT SUM(cnt) as total FROM ((" . implode(") UNION ALL (", $count_union_parts) . ")) as combined";
        $stmt = $pdo->query($count_sql);
        $total_records = $stmt->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);
    }
} catch (PDOException $e) {
    $posts = [];
    $total_records = 0;
    $total_pages = 0;
    $boards = [];
}

// 삭제 기능 처리
if (isset($_GET['delete']) && isset($_GET['id']) && isset($_GET['table'])) {
    $post_id = (int)$_GET['id'];
    $table_name = $_GET['table'];
    
    // 테이블명 보안 검사
    $allowed_tables = ['hopec_notices', 'hopec_press', 'hopec_newsletter', 'hopec_gallery', 'hopec_resources'];
    
    if (in_array($table_name, $allowed_tables)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM {$table_name} WHERE wr_id = ?");
            $stmt->execute([$post_id]);
            
            header("Location: list.php?deleted=1");
            exit;
        } catch (PDOException $e) {
            // 오류 처리
        }
    }
}

// 페이지 제목 설정
$page_title = '게시글 관리';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?></title>
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
    .table th { border-top: none; }
    .badge-notice { background-color: #dc3545; }
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📝 게시글 관리</h2>
        <a href="write.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> 새 게시글 작성
        </a>
    </div>

    <!-- 검색 폼 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="list.php" class="row g-3">
                <?php if (!empty($boards)): ?>
                <div class="col-md-3">
                    <label class="form-label">게시판</label>
                    <select name="board_id" class="form-select">
                        <option value="">전체 게시판</option>
                        <?php foreach ($boards as $board): ?>
                            <option value="<?= $board['id'] ?>" <?= $board_filter == $board['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($board['board_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="col-md-3">
                    <label class="form-label">검색 조건</label>
                    <select name="search_type" class="form-select">
                        <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>제목</option>
                        <option value="content" <?= $search_type === 'content' ? 'selected' : '' ?>>내용</option>
                        <option value="author" <?= $search_type === 'author' ? 'selected' : '' ?>>작성자</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">검색어</label>
                    <input type="text" name="search_keyword" class="form-control" 
                           placeholder="검색어를 입력하세요" value="<?= htmlspecialchars($search_keyword) ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> 검색
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 게시글 목록 -->
    <div class="card">
        <div class="card-body">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">총 <?= number_format($total_records) ?>개의 게시글</span>
                <?php if (!empty($search_keyword)): ?>
                    <a href="list.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> 검색 초기화
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">게시글이 없습니다</h4>
                    <p class="text-muted">새로운 게시글을 작성해보세요.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">번호</th>
                                <?php if (!empty($boards)): ?>
                                    <th style="width: 120px;">게시판</th>
                                <?php endif; ?>
                                <th>제목</th>
                                <th style="width: 120px;">작성자</th>
                                <th style="width: 120px;">작성일</th>
                                <th style="width: 80px;">조회수</th>
                                <th style="width: 100px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <?php if ($post['is_notice'] ?? false): ?>
                                            <span class="badge badge-notice">공지</span>
                                        <?php else: ?>
                                            <?= number_format($post['id']) ?>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <?php if (!empty($boards)): ?>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($post['board_name'] ?? '미분류') ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                    
                                    <td>
                                        <a href="view.php?id=<?= $post['id'] ?>&table=<?= $post['source_table'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </td>
                                    
                                    <td><?= htmlspecialchars($post['author'] ?? '관리자') ?></td>
                                    
                                    <td>
                                        <?= date('Y-m-d', strtotime($post['created_at'])) ?>
                                    </td>
                                    
                                    <td>
                                        <?= number_format($post['hit_count'] ?? 0) ?>
                                    </td>
                                    
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit.php?id=<?= $post['id'] ?>&table=<?= $post['source_table'] ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="list.php?delete=1&id=<?= $post['id'] ?>&table=<?= $post['source_table'] ?>" class="btn btn-outline-danger"
                                               onclick="return confirm('정말 삭제하시겠습니까?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="페이지 네비게이션">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>&board_id=<?= $board_filter ?>">이전</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>&board_id=<?= $board_filter ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>&board_id=<?= $board_filter ?>">다음</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>