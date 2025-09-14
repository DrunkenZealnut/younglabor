<?php
// 게시판 목록 페이지

require_once '../bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 필터 파라미터 처리
$type = $_GET['type'] ?? '';
$is_active = $_GET['is_active'] ?? '';
$search = trim($_GET['search'] ?? '');

// 쿼리 빌더 시작 - 게시글 수까지 포함하여 조회
$query = "SELECT b.*, 
          (SELECT COUNT(*) FROM hopec_posts p WHERE p.board_id = b.id AND p.is_published = 1) as post_count
          FROM hopec_boards b WHERE 1=1";
$params = [];

// 필터 조건 추가
if ($type !== '') {
    $query .= " AND b.board_type = ?";
    $params[] = $type;
}

if ($is_active !== '') {
    $query .= " AND b.is_active = ?";
    $params[] = (int)$is_active;
}

if ($search !== '') {
    $query .= " AND (b.board_name LIKE ? OR b.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 정렬 조건 추가
$query .= " ORDER BY b.sort_order ASC, b.id ASC";

try {
    // 게시판 목록 조회
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $boards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    $boards = [];
}

// 현재 적용된 필터를 쿼리스트링으로 유지
function buildQueryString($exclude = []) {
  $params = $_GET;
  foreach ($exclude as $key) {
    unset($params[$key]);
  }
  return http_build_query($params);
}

// 성공 메시지 처리
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $_SESSION['success_message'] = '게시판이 성공적으로 추가되었습니다.';
}

if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $_SESSION['success_message'] = '게시판이 성공적으로 수정되었습니다.';
}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $_SESSION['success_message'] = '게시판이 성공적으로 삭제되었습니다.';
}

// 게시판 타입 매핑
function getBoardTypeLabel($type) {
    $types = [
        'basic' => '기본',
        'gallery' => '갤러리',
        'faq' => 'FAQ',
        'calendar' => '일정',
        'notice' => '공지'
    ];
    return $types[$type] ?? $type;
}

// 게시판 타입 배지 클래스
function getBoardTypeBadgeClass($type) {
    $classes = [
        'basic' => 'bg-secondary',
        'gallery' => 'bg-info',
        'faq' => 'bg-warning text-dark',
        'calendar' => 'bg-success',
        'notice' => 'bg-primary'
    ];
    return $classes[$type] ?? 'bg-secondary';
}

// 컨텐츠 생성
ob_start();
?>

<!-- 알림 메시지 -->
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!-- 페이지 헤더 -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📋 게시판 관리</h2>
    <div class="btn-toolbar">
        <a href="create.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> 새 게시판 추가
        </a>
    </div>
</div>

<!-- 검색 폼 -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="list.php">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">활성 여부</label>
                    <select name="is_active" class="form-select">
                        <option value="">전체</option>
                        <option value="1" <?= $is_active === '1' ? 'selected' : '' ?>>활성</option>
                        <option value="0" <?= $is_active === '0' ? 'selected' : '' ?>>비활성</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">검색</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="게시판 이름 또는 설명" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> 검색
                    </button>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 데이터 테이블 -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">게시판 이름</th>
                        <th width="12%">고유 코드</th>
                        <th width="10%">유형</th>
                        <th width="20%">설명</th>
                        <th width="8%">게시글 수</th>
                        <th width="8%">정렬 순서</th>
                        <th width="8%">첨부파일</th>
                        <th width="8%">상태</th>
                        <th width="6%">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($boards)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <?= $search ? '필터 조건에 맞는 게시판이 없습니다.' : '등록된 게시판이 없습니다.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($boards as $board): ?>
                            <tr>
                                <td><?= $board['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($board['board_name']) ?></strong>
                                    <?php 
                                    $baseUrl = function_exists('get_base_url') ? get_base_url() : '';
                                    if ($baseUrl): 
                                    ?>
                                        <a href="<?= $baseUrl ?>/board/list/<?= $board['id'] ?>/" 
                                           class="btn btn-sm btn-outline-primary ms-2" 
                                           target="_blank" title="게시판 보기">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $displayCode = $board['board_code'];
                                    if (strpos($displayCode, 'board_') === 0) {
                                        $displayCode = substr($displayCode, 6);
                                    }
                                    ?>
                                    <code class="text-primary"><?= htmlspecialchars($displayCode) ?></code>
                                </td>
                                <td>
                                    <span class="badge <?= getBoardTypeBadgeClass($board['board_type']) ?>">
                                        <?= getBoardTypeLabel($board['board_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $description = $board['description'] ?? '';
                                    if (strlen($description) > 50) {
                                        echo htmlspecialchars(mb_substr($description, 0, 50)) . '...';
                                    } else {
                                        echo htmlspecialchars($description);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $count = (int)($board['post_count'] ?? 0);
                                    $badgeClass = $count > 0 ? 'bg-primary' : 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= number_format($count) ?></span>
                                </td>
                                <td><?= $board['sort_order'] ?? 0 ?></td>
                                <td>
                                    <?php if ($board['allow_attachments']): ?>
                                        <span class="badge bg-success">허용</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">비허용</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($board['is_active']): ?>
                                        <span class="badge bg-success">활성</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">비활성</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $board['id'] ?>" 
                                           class="btn btn-outline-info" title="수정">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $board['id'] ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="return confirm('정말 삭제하시겠습니까?')" title="삭제">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$page_title = '게시판 관리';
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
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">
    <a href="/admin/index.php" class="text-white text-decoration-none">희망씨 관리자</a>
  </div>
  <a href="/admin/index.php">📊 대시보드</a>
  <a href="/admin/posts/list.php">📝 게시글 관리</a>
  <a href="/admin/boards/list.php" class="active">📋 게시판 관리</a>
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
  <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>