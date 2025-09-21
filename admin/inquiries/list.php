<?php
// 문의 관리 페이지
require_once '../bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 페이지네이션 설정
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($current_page - 1) * $per_page;

// 필터 파라미터 처리
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// 카테고리 목록 가져오기 (필터링용)
try {
    $stmt = $pdo->query("SELECT id, name FROM hopec_inquiry_categories WHERE is_active = 1 ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// 쿼리 빌더 시작
$query = "
    SELECT i.*, c.name as category_name 
    FROM hopec_inquiries i
    LEFT JOIN hopec_inquiry_categories c ON i.category_id = c.id
    WHERE 1=1
";
$count_query = "SELECT COUNT(*) FROM hopec_inquiries i WHERE 1=1";
$params = [];
$count_params = [];

// 필터 조건 추가
if ($category_id > 0) {
    $query .= " AND i.category_id = ?";
    $count_query .= " AND i.category_id = ?";
    $params[] = $category_id;
    $count_params[] = $category_id;
}

if (!empty($status)) {
    $query .= " AND i.status = ?";
    $count_query .= " AND i.status = ?";
    $params[] = $status;
    $count_params[] = $status;
}

if (!empty($search)) {
    $query .= " AND (i.name LIKE ? OR i.email LIKE ? OR i.message LIKE ?)";
    $count_query .= " AND (i.name LIKE ? OR i.email LIKE ? OR i.message LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
}

if (!empty($date_from)) {
    $query .= " AND DATE(i.created_at) >= ?";
    $count_query .= " AND DATE(i.created_at) >= ?";
    $params[] = $date_from;
    $count_params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(i.created_at) <= ?";
    $count_query .= " AND DATE(i.created_at) <= ?";
    $params[] = $date_to;
    $count_params[] = $date_to;
}

// 정렬 및 페이지네이션
$query .= " ORDER BY i.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

try {
    // 전체 레코드 수 조회
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($count_params);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);
    
    // 문의 목록 조회
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
    $inquiries = [];
    $total_pages = 0;
    $total_records = 0;
}

// 현재 적용된 필터를 쿼리스트링으로 유지
function buildQueryString($exclude = [], $add = []) {
    $params = $_GET;
    foreach ($exclude as $key) {
        unset($params[$key]);
    }
    $params = array_merge($params, $add);
    return http_build_query($params);
}

// 상태별 배지 색상
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'new': return 'bg-danger';
        case 'processing': return 'bg-warning';
        case 'done': return 'bg-success';
        default: return 'bg-secondary';
    }
}

// 상태 한글 표시
function getStatusText($status) {
    switch ($status) {
        case 'new': return '신규';
        case 'processing': return '처리중';
        case 'done': return '완료';
        default: return '알 수 없음';
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>문의 관리</title>
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
    .filter-form {
        background-color: #f8f9fa;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    .filter-badge {
        margin-right: 5px;
        cursor: pointer;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .attachment-icon {
        color: #6c757d;
        font-size: 1.2rem;
    }
    .status-badge {
        cursor: pointer;
    }
  </style>
</head>
<body>
<?php 
// 현재 메뉴 설정 (문의 관리 활성화)
$current_menu = 'inquiries';
include '../includes/sidebar.php'; 
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📬 문의 관리</h2>
        <div>
            <a href="../inquiry_categories/list.php" class="btn btn-outline-primary me-2">카테고리 관리</a>
        </div>
    </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-warning"><?= $error ?></div>
        <?php endif; ?>

        <!-- 필터 폼 -->
        <div class="filter-form">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">카테고리</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">전체 카테고리</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">상태</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">모든 상태</option>
                        <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>신규</option>
                        <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>처리중</option>
                        <option value="done" <?= $status === 'done' ? 'selected' : '' ?>>완료</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">시작일</label>
                    <input type="date" class="form-control form-control-sm" name="date_from" value="<?= $date_from ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">종료일</label>
                    <input type="date" class="form-control form-control-sm" name="date_to" value="<?= $date_to ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">검색</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="이름, 이메일, 내용" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">필터 적용</button>
                    <a href="list.php" class="btn btn-outline-secondary btn-sm">필터 초기화</a>
                </div>
            </form>
        </div>

        <!-- 적용된 필터 표시 -->
        <?php if ($category_id || $status || $search || $date_from || $date_to): ?>
            <div class="mb-3">
                <span class="fw-bold">적용된 필터:</span>
                
                <?php if ($category_id): ?>
                    <?php 
                    $selected_category = array_filter($categories, function($c) use ($category_id) {
                        return $c['id'] == $category_id;
                    });
                    $selected_category = reset($selected_category);
                    ?>
                    <?php if ($selected_category): ?>
                        <span class="badge bg-info filter-badge">
                            카테고리: <?= htmlspecialchars($selected_category['name']) ?>
                            <a href="?<?= buildQueryString(['category_id']) ?>" class="text-white text-decoration-none">✕</a>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($status): ?>
                    <span class="badge bg-info filter-badge">
                        상태: <?= getStatusText($status) ?>
                        <a href="?<?= buildQueryString(['status']) ?>" class="text-white text-decoration-none">✕</a>
                    </span>
                <?php endif; ?>

                <?php if ($date_from): ?>
                    <span class="badge bg-info filter-badge">
                        시작일: <?= $date_from ?>
                        <a href="?<?= buildQueryString(['date_from']) ?>" class="text-white text-decoration-none">✕</a>
                    </span>
                <?php endif; ?>

                <?php if ($date_to): ?>
                    <span class="badge bg-info filter-badge">
                        종료일: <?= $date_to ?>
                        <a href="?<?= buildQueryString(['date_to']) ?>" class="text-white text-decoration-none">✕</a>
                    </span>
                <?php endif; ?>

                <?php if ($search): ?>
                    <span class="badge bg-info filter-badge">
                        검색: <?= htmlspecialchars($search) ?>
                        <a href="?<?= buildQueryString(['search']) ?>" class="text-white text-decoration-none">✕</a>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- 결과 수 표시 -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="mb-0">총 <?= number_format($total_records) ?>개의 문의</p>
        </div>

        <!-- 문의 목록 테이블 -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="10%">카테고리</th>
                        <th width="12%">이름</th>
                        <th width="12%">이메일</th>
                        <th width="30%">내용</th>
                        <th width="5%">첨부</th>
                        <th width="10%">상태</th>
                        <th width="10%">등록일</th>
                        <th width="6%">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="9" class="text-center">문의 내역이 없습니다.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td><?= $inquiry['id'] ?></td>
                                <td><?= htmlspecialchars($inquiry['category_name']) ?></td>
                                <td><?= htmlspecialchars($inquiry['name']) ?></td>
                                <td><?= htmlspecialchars($inquiry['email']) ?></td>
                                <td>
                                    <?= mb_strlen($inquiry['message']) > 50 ? htmlspecialchars(mb_substr($inquiry['message'], 0, 50)) . '...' : htmlspecialchars($inquiry['message']) ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($inquiry['attachment_path'])): ?>
                                        <i class="bi bi-paperclip attachment-icon"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <span class="badge status-badge <?= getStatusBadgeClass($inquiry['status']) ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?= getStatusText($inquiry['status']) ?>
                                        </span>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="update_status.php?id=<?= $inquiry['id'] ?>&status=new&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">신규</a></li>
                                            <li><a class="dropdown-item" href="update_status.php?id=<?= $inquiry['id'] ?>&status=processing&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">처리중</a></li>
                                            <li><a class="dropdown-item" href="update_status.php?id=<?= $inquiry['id'] ?>&status=done&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">완료</a></li>
                                        </ul>
                                    </div>
                                </td>
                                <td><?= date('Y-m-d', strtotime($inquiry['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="view.php?id=<?= $inquiry['id'] ?>" class="btn btn-sm btn-outline-primary">상세</a>
                                        <a href="delete.php?id=<?= $inquiry['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= buildQueryString(['page'], ['page' => $current_page - 1]) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link" aria-hidden="true">&laquo;</span>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= buildQueryString(['page'], ['page' => 1]) ?>">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= buildQueryString(['page'], ['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= buildQueryString(['page'], ['page' => $total_pages]) ?>"><?= $total_pages ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= buildQueryString(['page'], ['page' => $current_page + 1]) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link" aria-hidden="true">&raquo;</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 