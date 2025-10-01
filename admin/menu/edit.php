<?php
// /admin/menu/edit.php
require_once '../bootstrap.php';

// 메뉴 ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<script>alert('올바르지 않은 접근입니다.'); location.href='list.php';</script>";
  exit;
}

$id = (int)$_GET['id'];

// 상위 메뉴 목록 불러오기 (현재 메뉴 제외)
try {
  $stmt = $pdo->prepare("SELECT id, title FROM " . table('menu') . " WHERE parent_id IS NULL AND id != ? ORDER BY sort_order");
  $stmt->execute([$id]);
  $parentMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $parentMenus = [];
  $error = $e->getMessage();
}

// 게시판 목록 불러오기
try {
  // 이미 사용 중인 게시판 ID 목록 조회 (현재 메뉴 제외)
  $usedBoardsStmt = $pdo->prepare("
      SELECT board_id 
      FROM " . table('menu') . " 
      WHERE board_id IS NOT NULL AND id != ?
  ");
  $usedBoardsStmt->execute([$id]);
  $usedBoards = $usedBoardsStmt->fetchAll(PDO::FETCH_COLUMN);
  
  // 모든 활성 게시판 목록 조회
  $stmt = $pdo->query("SELECT id, board_name FROM " . table('boards') . " WHERE is_active = 1 ORDER BY board_name");
  $boards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $boards = [];
  $usedBoards = [];
  $error = $e->getMessage();
}

// 메뉴 정보 불러오기
try {
  $stmt = $pdo->prepare("SELECT * FROM " . table('menu') . " WHERE id = ?");
  $stmt->execute([$id]);
  $menu = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$menu) {
    echo "<script>alert('존재하지 않는 메뉴입니다.'); location.href='list.php';</script>";
    exit;
  }
} catch (PDOException $e) {
  echo "<script>alert('메뉴 정보를 불러오는데 실패했습니다.'); location.href='list.php';</script>";
  exit;
}

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 한글 안전하게 처리
  $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
  $title = isset($_POST['title']) ? $_POST['title'] : '';
  $slug = isset($_POST['slug']) ? $_POST['slug'] : '';
  $position = isset($_POST['position']) ? $_POST['position'] : 'top';
  $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  $board_id = isset($_POST['board_id']) && !empty($_POST['board_id']) ? (int)$_POST['board_id'] : null;

  // 자기 자신을 부모로 설정하지 못하도록 방지
  if ($parent_id == $id) {
    echo "<script>alert('메뉴는 자기 자신을 상위 메뉴로 가질 수 없습니다.'); history.back();</script>";
    exit;
  }

  try {
    // 명시적으로 utf8mb4 설정
    $pdo->exec("SET NAMES utf8mb4");
    
    // 준비된 명령문 사용
    $stmt = $pdo->prepare("UPDATE " . table('menu') . " SET 
                            parent_id = :parent_id, 
                            title = :title, 
                            slug = :slug, 
                            position = :position, 
                            sort_order = :sort_order, 
                            is_active = :is_active,
                            board_id = :board_id
                          WHERE id = :id");
    
    $result = $stmt->execute([
      ':parent_id' => $parent_id,
      ':title' => $title,
      ':slug' => $slug,
      ':position' => $position,
      ':sort_order' => $sort_order,
      ':is_active' => $is_active,
      ':board_id' => $board_id,
      ':id' => $id
    ]);
    
    if ($result) {
      header("Location: list.php");
      exit;
    } else {
      echo "<p>메뉴 저장 중 오류가 발생했습니다.</p>";
    }
  } catch (PDOException $e) {
    echo "<p>데이터베이스 오류: " . $e->getMessage() . "</p>";
  }
} else {
  // 폼 초기값 설정
  $parent_id = $menu['parent_id'];
  $title = $menu['title'];
  $slug = $menu['slug'];
  $position = $menu['position'];
  $sort_order = $menu['sort_order'];
  $is_active = $menu['is_active'];
  $board_id = $menu['board_id'];
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>메뉴 수정 - <?= htmlspecialchars($admin_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    .board-type-card {
      cursor: pointer;
      transition: all 0.2s;
      height: 100%;
    }
    .board-type-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .board-type-card.selected {
      border: 2px solid #0d6efd;
      background-color: #f0f7ff;
    }
    .type-icon {
      font-size: 2rem;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<?php
// 현재 메뉴 설정
$current_menu = "menu";
include "../includes/sidebar.php";
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <!-- 상단 네비게이션 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= admin_url('index.php') ?>">관리자</a></li>
                <li class="breadcrumb-item"><a href="list.php">메뉴 관리</a></li>
                <li class="breadcrumb-item active">메뉴 수정</li>
            </ol>
        </nav>
        
        <div class="btn-group">
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-list"></i> 목록
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <h2>메뉴 수정</h2>
  
  <?php if (isset($error)): ?>
    <div class="alert alert-warning"><?= $error ?></div>
  <?php endif; ?>
  
  <div class="card mb-4">
    <div class="card-header bg-light">
      <strong>메뉴 ID: <?= $id ?></strong>
      <span class="float-end">생성일: <?= date('Y-m-d', strtotime($menu['created_at'])) ?></span>
    </div>
  </div>
  
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">상위 메뉴</label>
      <select name="parent_id" class="form-select" id="parent_id">
        <option value="">없음 (최상위 메뉴)</option>
        <?php foreach ($parentMenus as $parentMenu): ?>
          <option value="<?= $parentMenu['id'] ?>" <?= $parent_id == $parentMenu['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($parentMenu['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">제목</label>
      <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Slug (파일명)</label>
      <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($slug) ?>" placeholder="예: about-us, contact 등" id="slug_field">
      <small class="text-muted">하위 메뉴인 경우 연결될 파일명(slug.php)</small>
    </div>
    
    <!-- 게시판 연결 섹션 -->
    <div class="mb-3">
      <label class="form-label">연결할 게시판 (선택사항)</label>
      <div class="alert alert-info">
        <small>게시판을 선택하면 이 메뉴 클릭 시 해당 게시판으로 이동합니다. 게시판을 선택할 경우 slug는 자동으로 설정됩니다.</small>
      </div>
      
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="board_id" id="no_board" value="" <?= empty($board_id) ? 'checked' : '' ?>>
        <label class="form-check-label" for="no_board">
          게시판 연결 안함
        </label>
      </div>
      
      <div class="row">
        <?php foreach ($boards as $board): ?>
        <?php 
          $isUsed = in_array($board['id'], $usedBoards);
          $isCurrentlySelected = $board_id == $board['id'];
        ?>
        <div class="col-md-6">
          <div class="board-select-card <?= $isCurrentlySelected ? 'selected' : '' ?> <?= $isUsed ? 'disabled' : '' ?>" 
               <?= !$isUsed || $isCurrentlySelected ? 'onclick="selectBoard('.$board['id'].')"' : '' ?>>
            <input type="radio" name="board_id" id="board_<?= $board['id'] ?>" 
                   value="<?= $board['id'] ?>" class="form-check-input me-2" 
                   <?= $isCurrentlySelected ? 'checked' : '' ?> 
                   <?= $isUsed && !$isCurrentlySelected ? 'disabled' : '' ?>>
            <label for="board_<?= $board['id'] ?>" class="form-check-label">
              <?= htmlspecialchars($board['board_name']) ?>
              <?php if ($isUsed && !$isCurrentlySelected): ?>
                <span class="badge bg-danger ms-2">사용중</span>
              <?php elseif ($isCurrentlySelected): ?>
                <span class="badge bg-success ms-2">현재 선택됨</span>
              <?php endif; ?>
            </label>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="mb-3">
      <label class="form-label">위치</label>
      <select name="position" class="form-select">
        <option value="top" <?= $position === 'top' ? 'selected' : '' ?>>상단</option>
        <option value="footer" <?= $position === 'footer' ? 'selected' : '' ?>>하단</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">정렬 순서</label>
      <input type="number" name="sort_order" class="form-control" value="<?= $sort_order ?>">
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_active" class="form-check-input" <?= $is_active ? 'checked' : '' ?>>
      <label class="form-check-label">활성화</label>
    </div>
    <button type="submit" class="btn btn-primary">저장</button>
    <a href="list.php" class="btn btn-secondary">취소</a>
  </form>
</div>

<script>
function selectBoard(boardId) {
  // 모든 카드에서 선택 클래스 제거
  document.querySelectorAll('.board-select-card').forEach(card => {
    card.classList.remove('selected');
  });
  
  if (boardId) {
    // 선택한 카드에 선택 클래스 추가
    const card = document.querySelector(`.board-select-card input[value="${boardId}"]`).closest('.board-select-card');
    card.classList.add('selected');
    
    // 라디오 버튼 선택
    document.getElementById(`board_${boardId}`).checked = true;
    
    // slug 필드를 board_ + id 형식으로 자동 설정
    document.getElementById('slug_field').value = `board_${boardId}`;
    document.getElementById('slug_field').readOnly = true;
  } else {
    // 게시판 선택 안함 옵션
    document.getElementById('no_board').checked = true;
    document.getElementById('slug_field').readOnly = false;
  }
}

// 상위 메뉴 변경 시 slug 필드 상태 변경
document.getElementById('parent_id').addEventListener('change', function() {
  if (this.value === '') {
    // 최상위 메뉴일 경우 slug 필드 비활성화 (이미 게시판 연결됐으면 유지)
    if (!document.querySelector('input[name="board_id"]:checked').value) {
      document.getElementById('slug_field').value = '';
      document.getElementById('slug_field').readOnly = true;
    }
  } else {
    // 하위 메뉴일 경우 게시판 연결 없으면 slug 필드 활성화
    if (!document.querySelector('input[name="board_id"]:checked').value) {
      document.getElementById('slug_field').readOnly = false;
    }
  }
});

// 페이지 로드 시 현재 선택된 게시판에 따라 필드 설정
document.addEventListener('DOMContentLoaded', function() {
  const selectedBoardId = document.querySelector('input[name="board_id"]:checked')?.value;
  if (selectedBoardId) {
    document.getElementById('slug_field').readOnly = true;
  } else if (document.getElementById('parent_id').value === '') {
    document.getElementById('slug_field').readOnly = true;
  }
});
</script>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 