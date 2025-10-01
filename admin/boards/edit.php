<?php
// /admin/boards/edit.php
require_once '../bootstrap.php';

// ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<script>alert('올바르지 않은 접근입니다.'); location.href='list.php';</script>";
  exit;
}

$id = (int)$_GET['id'];

// 게시판 데이터 불러오기
try {
  $tableName = get_table_name('boards');
  $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE id = ?");
  $stmt->execute([$id]);
  $board = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$board) {
    echo "<script>alert('존재하지 않는 게시판입니다.'); location.href='list.php';</script>";
    exit;
  }
} catch (PDOException $e) {
  echo "<script>alert('게시판 정보를 불러오는데 실패했습니다.'); location.href='list.php';</script>";
  exit;
}

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 폼 데이터 가져오기
  $name = isset($_POST['name']) ? trim($_POST['name']) : '';
  $description = isset($_POST['description']) ? trim($_POST['description']) : '';
  $type = isset($_POST['type']) ? $_POST['type'] : 'basic';
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  $allow_attachments = isset($_POST['allow_attachments']) ? 1 : 0;
  $use_category = isset($_POST['use_category']) ? 1 : 0;
  $category_list = isset($_POST['category_list']) ? trim($_POST['category_list']) : '';
  $write_level = isset($_POST['write_level']) ? (int)$_POST['write_level'] : 0;
  $reply_level = isset($_POST['reply_level']) ? (int)$_POST['reply_level'] : 0;
  
  // 유효성 검사
  $errors = [];
  
  if (empty($name)) {
    $errors[] = '게시판 이름은 필수 입력 항목입니다.';
  }
  
  if (!in_array($type, ['basic', 'gallery', 'faq', 'calendar'])) {
    $errors[] = '유효하지 않은 게시판 유형입니다.';
  }
  
  // 오류가 없으면 저장
  if (empty($errors)) {
    try {
      // 명시적으로 UTF-8 설정
      $pdo->exec("SET NAMES utf8mb4");
      
      $stmt = $pdo->prepare("UPDATE {$tableName} 
                            SET board_name = :name, 
                                description = :description, 
                                board_type = :board_type,
                                is_active = :is_active,
                                allow_attachments = :allow_attachments,
                                use_category = :use_category,
                                category_list = :category_list,
                                write_level = :write_level,
                                reply_level = :reply_level
                            WHERE id = :id");
      
      $result = $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':board_type' => $type,
        ':is_active' => $is_active,
        ':allow_attachments' => $allow_attachments,
        ':use_category' => $use_category,
        ':category_list' => $category_list,
        ':write_level' => $write_level,
        ':reply_level' => $reply_level,
        ':id' => $id
      ]);
      
      if ($result) {
        // 성공 메시지와 함께 목록 페이지로 리디렉션
        header("Location: list.php?updated=1");
        exit;
      } else {
        $errors[] = '게시판 수정 중 오류가 발생했습니다.';
      }
    } catch (PDOException $e) {
      $errors[] = '데이터베이스 오류: ' . $e->getMessage();
    }
  }
} else {
  // GET 요청일 경우 게시판 데이터로 변수 초기화
  $name = $board['board_name'];
  $description = $board['description'];
  $type = $board['board_type'] ?? 'basic';
  $is_active = $board['is_active'];
  $allow_attachments = $board['allow_attachments'] ?? 0;
  $use_category = $board['use_category'] ?? 0;
  $category_list = $board['category_list'] ?? '';
  $write_level = $board['write_level'] ?? 0;
  $reply_level = $board['reply_level'] ?? 0;
}

// 게시판 유형별 설명
$boardTypeDesc = [
  'basic' => '일반적인 게시판입니다. 제목, 내용, 작성자 정보를 포함합니다.',
  'gallery' => '이미지 중심의 갤러리형 게시판입니다. 썸네일이 표시됩니다.',
  'faq' => '자주 묻는 질문과 답변을 위한 게시판입니다. 질문/답변 쌍으로 구성됩니다.',
  'calendar' => '일정/이벤트를 등록하고 달력 형태로 볼 수 있는 게시판입니다.'
];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>게시판 수정 - <?= htmlspecialchars($admin_title) ?></title>
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
$current_menu = "boards";
include "../includes/sidebar.php";
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <!-- 상단 네비게이션 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= admin_url('index.php') ?>">관리자</a></li>
                <li class="breadcrumb-item"><a href="list.php">게시판 관리</a></li>
                <li class="breadcrumb-item active">게시판 수정</li>
            </ol>
        </nav>
        
        <div class="btn-group">
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-list"></i> 목록
            </a>
        </div>
    </div>

    <div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>게시판 수정</h2>
    <div>
      <a href="list.php" class="btn btn-outline-primary me-2">게시판 목록으로</a>
      <a href="../index.php') ?>" class="btn btn-secondary">관리자 메인으로</a>
    </div>
  </div>
  
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <div class="card mb-4">
    <div class="card-header bg-light">
      <strong>게시판 ID: <?= $id ?></strong>
      <span class="float-end">생성일: <?= date('Y-m-d', strtotime($board['created_at'])) ?></span>
    </div>
  </div>
  
  <form method="POST" class="needs-validation" novalidate>
    <div class="mb-4">
      <label class="form-label fw-bold">게시판 이름</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
      <div class="form-text">사용자들에게 표시될 게시판의 이름을 입력하세요. (100자 이내)</div>
    </div>
    
    <div class="mb-4">
      <label class="form-label fw-bold">게시판 설명</label>
      <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($description) ?></textarea>
      <div class="form-text">게시판에 대한 간략한 설명을 입력하세요. (선택사항)</div>
    </div>
    
    <div class="mb-4">
      <label class="form-label fw-bold">카테고리 설정</label>
      <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" role="switch" id="use_category" name="use_category" <?= $use_category ? 'checked' : '' ?> 
          onchange="toggleCategoryInput()">
        <label class="form-check-label" for="use_category">이 게시판에서 카테고리 사용하기</label>
      </div>
      <div id="category_section" class="<?= $use_category ? '' : 'd-none' ?>">
        <textarea name="category_list" id="category_list" class="form-control" rows="3" 
          placeholder="각 카테고리를 쉼표(,)로 구분하여 입력하세요. 예: 공지,질문,자료,기타"><?= htmlspecialchars($category_list) ?></textarea>
        <div class="form-text">각 카테고리를 쉼표(,)로 구분하여 입력하세요. (예: 공지,질문,자료,기타)</div>
      </div>
    </div>
    
    <div class="mb-4">
      <label class="form-label fw-bold">게시판 유형</label>
      <div class="row row-cols-1 row-cols-md-4 g-4 mb-3">
        <?php foreach (['basic', 'gallery', 'faq', 'calendar'] as $boardType): ?>
          <?php 
            $iconClass = '';
            switch($boardType) {
              case 'basic': $iconClass = '📄'; break;
              case 'gallery': $iconClass = '🖼️'; break;
              case 'faq': $iconClass = '❓'; break;
              case 'calendar': $iconClass = '📅'; break;
            }
            
            $typeTitle = '';
            switch($boardType) {
              case 'basic': $typeTitle = '일반 게시판'; break;
              case 'gallery': $typeTitle = '갤러리'; break;
              case 'faq': $typeTitle = 'FAQ'; break;
              case 'calendar': $typeTitle = '일정'; break;
            }
          ?>
          <div class="col">
            <div class="card board-type-card <?= $type === $boardType ? 'selected' : '' ?>" 
                 data-type="<?= $boardType ?>" onclick="selectBoardType('<?= $boardType ?>')">
              <div class="card-body text-center">
                <div class="type-icon"><?= $iconClass ?></div>
                <h5 class="card-title"><?= $typeTitle ?></h5>
                <p class="card-text small"><?= $boardTypeDesc[$boardType] ?></p>
                <input type="radio" name="type" value="<?= $boardType ?>" class="d-none" 
                       <?= $type === $boardType ? 'checked' : '' ?>>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle-fill"></i> 주의: 게시판 유형을 변경하면 기존 게시글의 표시 방식이 달라질 수 있습니다.
      </div>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                 <?= $is_active ? 'checked' : '' ?>>
          <label class="form-check-label" for="is_active">게시판 활성화</label>
        </div>
        <div class="form-text">비활성화된 게시판은 사용자에게 표시되지 않습니다.</div>
      </div>
      <div class="col-md-6">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="allow_attachments" id="allow_attachments" 
                 <?= $allow_attachments ? 'checked' : '' ?>>
          <label class="form-check-label" for="allow_attachments">첨부파일 허용</label>
        </div>
        <div class="form-text">사용자가 게시글 작성 시 첨부파일을 올릴 수 있습니다.</div>
      </div>
    </div>
    
    <div class="mb-4">
      <label class="form-label fw-bold">글쓰기 권한</label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="write_level" id="write_level_0" value="0" 
               <?= $write_level == 0 ? 'checked' : '' ?>>
        <label class="form-check-label" for="write_level_0">
          권한 없음
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="write_level" id="write_level_1" value="1"
               <?= $write_level == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="write_level_1">
          권한 있음
        </label>
      </div>
      <div class="form-text">게시판의 글쓰기 권한을 설정합니다.</div>
    </div>

    <div class="mb-4">
      <label class="form-label fw-bold">댓글 기능</label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="reply_level" id="reply_level_0" value="0" 
               <?= $reply_level == 0 ? 'checked' : '' ?>>
        <label class="form-check-label" for="reply_level_0">
          댓글 불가
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="reply_level" id="reply_level_1" value="1"
               <?= $reply_level == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="reply_level_1">
          댓글 가능
        </label>
      </div>
      <div class="form-text">게시판에서 댓글 작성 가능 여부를 설정합니다.</div>
    </div>
    
    <div class="d-flex justify-content-center mt-5">
      <button type="submit" class="btn btn-primary">저장</button>
      <a href="list.php" class="btn btn-outline-secondary ms-2">취소</a>
    </div>
  </form>
</div>

<script>
  function selectBoardType(type) {
    document.querySelectorAll('.board-type-card').forEach(card => {
      card.classList.remove('selected');
    });
    document.querySelector(`.board-type-card[data-type="${type}"]`).classList.add('selected');
    document.querySelector(`input[name="type"][value="${type}"]`).checked = true;
  }
  
  function toggleCategoryInput() {
    const useCategory = document.getElementById('use_category').checked;
    const categorySection = document.getElementById('category_section');
    
    if (useCategory) {
      categorySection.classList.remove('d-none');
    } else {
      categorySection.classList.add('d-none');
    }
  }
  
  // 폼 유효성 검사
  (() => {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 