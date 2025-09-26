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
    // 사용 가능한 게시판 타입들과 해당 게시판명 정의 (write.php와 동일)
    $board_types = [
        1 => ['name' => '재정보고', 'board_type' => 'finance_reports'],
        2 => ['name' => '공지사항', 'board_type' => 'notices'],
        3 => ['name' => '언론보도', 'board_type' => 'press'],
        4 => ['name' => '소식지', 'board_type' => 'newsletter'],
        5 => ['name' => '갤러리', 'board_type' => 'gallery'],
        6 => ['name' => '자료실', 'board_type' => 'resources'],
        7 => ['name' => '네팔나눔연대여행', 'board_type' => 'nepal_travel']
    ];
    
    // 게시판 필터 옵션용 배열
    $boards = [];
    foreach ($board_types as $id => $info) {
        $boards[] = ['id' => $id, 'board_name' => $info['name'], 'board_type' => $info['board_type']];
    }
    
    // posts 테이블에서 board_type으로 통합 조회
    $tableName = get_table_name('posts');
    $where_clause = "WHERE wr_is_comment = 0";
    $params = [];
    
    // 게시판 필터가 있는 경우 해당 board_type만 조회
    if ($board_filter > 0 && isset($board_types[$board_filter])) {
        $where_clause .= " AND board_type = ?";
        $params[] = $board_types[$board_filter]['board_type'];
    }
    
    // 검색 조건 추가
    if (!empty($search_keyword)) {
        if ($search_type === 'title') {
            $where_clause .= " AND wr_subject LIKE ?";
            $params[] = '%' . $search_keyword . '%';
        } else if ($search_type === 'content') {
            $where_clause .= " AND wr_content LIKE ?";
            $params[] = '%' . $search_keyword . '%';
        } else if ($search_type === 'author') {
            $where_clause .= " AND wr_name LIKE ?";
            $params[] = '%' . $search_keyword . '%';
        }
    }
    
    // 최신 등록일시순으로 정렬 (공지사항 상단 고정)
    $sql = "SELECT DISTINCT
                wr_id as id,
                board_type,
                wr_subject as title,
                wr_content as content,
                wr_name as author,
                wr_hit as hit_count,
                wr_datetime as created_at,
                wr_is_notice as is_notice
            FROM {$tableName} 
            {$where_clause}
            ORDER BY 
                wr_is_notice DESC,
                wr_datetime DESC, wr_id DESC 
            LIMIT {$offset}, {$records_per_page}";
    
    // 게시글 조회
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $raw_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 완전한 중복 제거: 연관배열로 중복 방지
    $unique_posts = [];
    foreach ($raw_posts as $post) {
        $unique_key = $post['id']; // ID를 유니크 키로 사용
        $unique_posts[$unique_key] = $post;
    }
    
    // 배열 값만 추출하여 최종 결과 생성
    $posts = array_values($unique_posts);
    
    // board_type을 board_name으로 변환 (참조 전달 없이 안전하게)
    for ($i = 0; $i < count($posts); $i++) {
        foreach ($board_types as $info) {
            if ($info['board_type'] === $posts[$i]['board_type']) {
                $posts[$i]['board_name'] = $info['name'];
                break;
            }
        }
    }
    
    // 총 게시글 수 계산 - 단순 카운트
    $count_sql = "SELECT COUNT(*) FROM {$tableName} {$where_clause}";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);
} catch (PDOException $e) {
    $posts = [];
    $total_records = 0;
    $total_pages = 0;
    $boards = [];
}

// 삭제 기능 처리
if (isset($_GET['delete']) && isset($_GET['id']) && isset($_GET['board_type'])) {
    $post_id = (int)$_GET['id'];
    $board_type = $_GET['board_type'];
    
    // board_type 보안 검사
    $allowed_board_types = ['finance_reports', 'notices', 'press', 'newsletter', 'gallery', 'resources', 'nepal_travel'];
    
    if (in_array($board_type, $allowed_board_types)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM {$tableName} WHERE wr_id = ? AND board_type = ?");
            $stmt->execute([$post_id, $board_type]);
            
            header("Location: list.php?deleted=1");
            exit;
        } catch (PDOException $e) {
            // 오류 처리
        }
    }
}

// 페이지 제목 설정
$page_title = '게시글 관리';

// BASE_PATH 환경 변수 가져오기
$base_path = get_base_path();
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
<?php 
// 현재 메뉴 설정 (게시글 관리 활성화)
$current_menu = 'posts';
include '../includes/sidebar.php'; 
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📝 게시글 관리</h2>
        <div>
            <a href="write.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> 새 게시글 작성
            </a>
        </div>
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
                                        <a href="view.php?id=<?= $post['id'] ?>&board_type=<?= urlencode($post['board_type']) ?>" 
                                           class="text-decoration-none" onclick="saveSearchState()">
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
                                            <a href="edit.php?id=<?= $post['id'] ?>&board_type=<?= urlencode($post['board_type']) ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="list.php?delete=1&id=<?= $post['id'] ?>&board_type=<?= urlencode($post['board_type']) ?>" class="btn btn-outline-danger"
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

                <!-- 페이지네이션 - board_templates 방식 적용 -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="페이지 네비게이션" class="mt-4">
                        <div class="d-flex justify-content-center">
                            <ul class="pagination">
                                <?php
                                // board_templates와 동일한 페이지 범위 계산
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $start_page + 4);
                                $start_page = max(1, $end_page - 4);
                                
                                // URL 파라미터 구성 (board_templates 방식)
                                $url_params = $_GET;
                                unset($url_params['page']);
                                $query_string = !empty($url_params) ? '&' . http_build_query($url_params) : '';
                                ?>
                                
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?= $query_string ?>" title="첫 페이지">
                                            <i class="bi bi-chevron-double-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $query_string ?>" title="이전 페이지">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i === $page): ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?= $i ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $i ?><?= $query_string ?>"><?= $i ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $query_string ?>" title="다음 페이지">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $total_pages ?><?= $query_string ?>" title="마지막 페이지">
                                            <i class="bi bi-chevron-double-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// 검색 상태 저장 및 복원 기능
function saveSearchState() {
    const searchData = {
        board_id: '<?= $board_filter ?>',
        search_type: '<?= $search_type ?>',
        search_keyword: '<?= htmlspecialchars($search_keyword, ENT_QUOTES) ?>',
        page: '<?= $page ?>',
        url: cleanProjectSlugFromUrl(window.location.href)
    };
    sessionStorage.setItem('admin_posts_search', JSON.stringify(searchData));
}

// 페이지 로드 시 검색 상태 복원
document.addEventListener('DOMContentLoaded', function() {
    // 뒤로가기로 온 경우 검색 상태 복원
    if (performance.navigation.type === performance.navigation.TYPE_BACK_FORWARD) {
        const savedSearch = sessionStorage.getItem('admin_posts_search');
        if (savedSearch) {
            const searchData = JSON.parse(savedSearch);
            
            // 현재 URL과 저장된 URL이 다르면 검색 페이지로 복원
            if (searchData.url && window.location.href !== searchData.url) {
                // 저장된 검색 조건으로 리다이렉트
                window.location.href = searchData.url;
                return;
            }
        }
    }
    
    // 검색 폼 변경 시 자동 저장
    const searchForm = document.querySelector('form[action="list.php"]');
    if (searchForm) {
        searchForm.addEventListener('change', saveSearchState);
    }
});

// 검색 초기화 시 세션 스토리지도 클리어
document.addEventListener('click', function(e) {
    if (e.target.closest('a[href="list.php"]') && e.target.textContent.includes('검색 초기화')) {
        sessionStorage.removeItem('admin_posts_search');
    }
});
</script>
</body>
</html>