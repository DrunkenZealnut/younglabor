<?php
// /admin/menu/list_templated.php
require_once '../bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 필터 파라미터 처리
$position = isset($_GET['position']) ? $_GET['position'] : '';
$is_active = isset($_GET['is_active']) ? $_GET['is_active'] : '';
$menu_type = isset($_GET['menu_type']) ? $_GET['menu_type'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;

// 상위 메뉴 목록 불러오기 (필터 드롭다운용)
try {
  $parentQuery = "SELECT id, title FROM " . table('menu') . " WHERE parent_id IS NULL ORDER BY sort_order";
  $parentStmt = $pdo->query($parentQuery);
  $parentMenus = $parentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $parentMenus = [];
}

// 쿼리 빌더 시작
$query = "
    SELECT m.*, p.title as parent_title, b.board_name as board_name
    FROM " . table('menu') . " m
    LEFT JOIN " . table('menu') . " p ON m.parent_id = p.id
    LEFT JOIN " . table('boards') . " b ON m.board_id = b.id
    WHERE 1=1
";
$params = [];

// 필터 조건 추가
if ($position !== '') {
  $query .= " AND m.position = ?";
  $params[] = $position;
}

if ($is_active !== '') {
  $query .= " AND m.is_active = ?";
  $params[] = (int)$is_active;
}

if ($menu_type === 'parent') {
  $query .= " AND m.parent_id IS NULL";
} elseif ($menu_type === 'child') {
  $query .= " AND m.parent_id IS NOT NULL";
}

if ($parent_id !== null) {
  $query .= " AND (m.parent_id = ? OR m.id = ?)";
  $params[] = $parent_id;
  $params[] = $parent_id;
}

if ($search !== '') {
  $query .= " AND (m.title LIKE ? OR m.slug LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
}

// 정렬 조건 추가
$query .= " ORDER BY COALESCE(m.parent_id, m.id), m.parent_id IS NOT NULL, m.sort_order";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
    $menus = [];
}

// 상위 메뉴 정보 가져오기 (하위 메뉴 필터링 시 상위 메뉴 정보 표시용)
$parentMenuInfo = null;
if ($parent_id !== null) {
  try {
    $stmt = $pdo->prepare("SELECT title FROM " . table('menu') . " WHERE id = ?");
    $stmt->execute([$parent_id]);
    $parentMenuInfo = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    // 오류 무시
  }
}

// 현재 적용된 필터를 쿼리스트링으로 유지 (페이지네이션이나 정렬 등에 사용)
function buildQueryString($exclude = []) {
  $params = $_GET;
  foreach ($exclude as $key) {
    unset($params[$key]);
  }
  return http_build_query($params);
}

// 템플릿 변수 설정
$page_title = '메뉴 관리';
$active_menu = 'menu';
$breadcrumb = [
    ['title' => '대시보드', 'url' => admin_url('index.php')],
    ['title' => '메뉴 관리']
];

$page_actions = '<a href="create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> 새 메뉴 추가</a>';

// 필터 폼 컴포넌트
ob_start();
?>
<!-- 필터 폼 -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">위치</label>
        <select name="position" class="form-select form-select-sm">
          <option value="">전체</option>
          <option value="top" <?= $position === 'top' ? 'selected' : '' ?>>상단</option>
          <option value="footer" <?= $position === 'footer' ? 'selected' : '' ?>>하단</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">활성 여부</label>
        <select name="is_active" class="form-select form-select-sm">
          <option value="">전체</option>
          <option value="1" <?= $is_active === '1' ? 'selected' : '' ?>>활성</option>
          <option value="0" <?= $is_active === '0' ? 'selected' : '' ?>>비활성</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">메뉴 유형</label>
        <select name="menu_type" class="form-select form-select-sm">
          <option value="">전체</option>
          <option value="parent" <?= $menu_type === 'parent' ? 'selected' : '' ?>>상위 메뉴</option>
          <option value="child" <?= $menu_type === 'child' ? 'selected' : '' ?>>하위 메뉴</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">상위 메뉴 선택</label>
        <select name="parent_id" class="form-select form-select-sm">
          <option value="">전체 메뉴</option>
          <?php foreach ($parentMenus as $parentMenu): ?>
            <option value="<?= $parentMenu['id'] ?>" <?= $parent_id === (int)$parentMenu['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($parentMenu['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-9">
        <label class="form-label">검색</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="제목 또는 slug" value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-sm">필터 적용</button>
        <a href="list_templated.php" class="btn btn-outline-secondary btn-sm ms-2">필터 초기화</a>
      </div>
    </form>
  </div>
</div>

<!-- 현재 적용된 필터 표시 -->
<?php if($position || $is_active !== '' || $menu_type || $search || $parent_id): ?>
<div class="mb-3">
  <span class="fw-bold">적용된 필터:</span>
  <?php if($position): ?>
    <span class="badge bg-info me-1">
      위치: <?= $position === 'top' ? '상단' : '하단' ?>
      <a href="?<?= buildQueryString(['position']) ?>" class="text-white text-decoration-none ms-1">✕</a>
    </span>
  <?php endif; ?>

  <?php if($is_active !== ''): ?>
    <span class="badge bg-info me-1">
      상태: <?= $is_active === '1' ? '활성' : '비활성' ?>
      <a href="?<?= buildQueryString(['is_active']) ?>" class="text-white text-decoration-none ms-1">✕</a>
    </span>
  <?php endif; ?>

  <?php if($menu_type): ?>
    <span class="badge bg-info me-1">
      유형: <?= $menu_type === 'parent' ? '상위 메뉴' : '하위 메뉴' ?>
      <a href="?<?= buildQueryString(['menu_type']) ?>" class="text-white text-decoration-none ms-1">✕</a>
    </span>
  <?php endif; ?>

  <?php if($parent_id && $parentMenuInfo): ?>
    <span class="badge bg-primary me-1">
      상위 메뉴: <?= htmlspecialchars($parentMenuInfo['title']) ?>
      <a href="?<?= buildQueryString(['parent_id']) ?>" class="text-white text-decoration-none ms-1">✕</a>
    </span>
  <?php endif; ?>

  <?php if($search): ?>
    <span class="badge bg-info me-1">
      검색: <?= htmlspecialchars($search) ?>
      <a href="?<?= buildQueryString(['search']) ?>" class="text-white text-decoration-none ms-1">✕</a>
    </span>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- 메뉴 목록 테이블 -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th width="5%">#</th>
            <th width="20%">제목</th>
            <th width="15%">구분</th>
            <th width="15%">Slug</th>
            <th width="10%">위치</th>
            <th width="8%">정렬순서</th>
            <th width="12%">연결 게시판</th>
            <th width="8%">상태</th>
            <th width="7%">관리</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($menus)): ?>
            <tr>
              <td colspan="9" class="text-center py-4">
                <?= ($position || $is_active !== '' || $menu_type || $search || $parent_id) ? '필터 조건에 맞는 메뉴가 없습니다.' : '등록된 메뉴가 없습니다.' ?>
              </td>
            </tr>
          <?php else: ?>
            <?php 
            // 하위 메뉴 개수 계산
            $childCounts = [];
            foreach ($menus as $menu) {
              if (!is_null($menu['parent_id'])) {
                if (!isset($childCounts[$menu['parent_id']])) {
                  $childCounts[$menu['parent_id']] = 0;
                }
                $childCounts[$menu['parent_id']]++;
              }
            }
            ?>
            
            <?php foreach ($menus as $menu): ?>
              <?php 
              $isParent = is_null($menu['parent_id']); 
              $isHighlighted = $parent_id && $menu['id'] == $parent_id;
              $rowClass = $isParent ? '' : 'table-secondary';
              if ($isHighlighted) $rowClass .= ' table-primary';
              ?>
              <tr class="<?= $rowClass ?>">
                <td><?= $menu['id'] ?></td>
                <td>
                  <?php if (!$isParent): ?>
                    <span class="text-muted me-1">└</span>
                  <?php endif; ?>
                  <?= htmlspecialchars($menu['title']) ?>
                  <?php if ($isParent && isset($childCounts[$menu['id']]) && $childCounts[$menu['id']] > 0): ?>
                    <span class="badge bg-secondary ms-1"><?= $childCounts[$menu['id']] ?>개 하위메뉴</span>
                  <?php endif; ?>
                </td>
                <td><?= $menu['parent_id'] ? htmlspecialchars($menu['parent_title']) : '-' ?></td>
                <td><?= htmlspecialchars($menu['slug'] ?: '-') ?></td>
                <td>
                  <span class="badge bg-<?= $menu['position'] === 'top' ? 'primary' : 'info' ?>">
                    <?= $menu['position'] === 'top' ? '상단' : '하단' ?>
                  </span>
                </td>
                <td><?= $menu['sort_order'] ?></td>
                <td>
                  <?php if (!empty($menu['board_name'])): ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($menu['board_name']) ?></span>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-<?= $menu['is_active'] ? 'success' : 'danger' ?>">
                    <?= $menu['is_active'] ? '사용' : '숨김' ?>
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="edit.php?id=<?= $menu['id'] ?>" class="btn btn-outline-primary" title="수정">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <?php if ($isParent && isset($childCounts[$menu['id']]) && $childCounts[$menu['id']] > 0): ?>
                      <a href="?parent_id=<?= $menu['id'] ?>" class="btn btn-outline-info" title="하위메뉴 보기">
                        <i class="bi bi-list-nested"></i>
                      </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-danger" title="삭제"
                      onclick="confirmDelete(<?= $menu['id'] ?>, '<?= htmlspecialchars(addslashes($menu['title'])) ?>')">
                      <i class="bi bi-trash"></i>
                    </button>
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

<style>
.submenu {
    margin-left: 3rem;
    border-left: 3px solid #6c757d;
    padding-left: 1rem;
    background-color: #f8f9fa;
}
</style>

<script>
function confirmDelete(menuId, menuTitle) {
  if (confirm(`"${menuTitle}" 메뉴를 삭제하시겠습니까?\n하위 메뉴가 있는 경우 함께 삭제됩니다.`)) {
    window.location.href = `delete.php?id=${menuId}`;
  }
}
</script>

<?php
$filter_content = ob_get_clean();

$main_content = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🧭 메뉴 관리</h2>
    <a href="create.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> 새 메뉴 추가
    </a>
</div>
' . $filter_content;

if (isset($error)) {
    $_SESSION['error_message'] = $error;
}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php 
// 현재 메뉴 설정 (메뉴 관리 활성화)
$current_menu = 'menu';
include '../includes/sidebar.php'; 
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
  <?= $main_content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>