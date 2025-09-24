<?php
// /admin/boards/create.php
require_once '../bootstrap.php';

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
      
      // 게시판 코드 생성 (이름의 영문화 또는 현재 시간 기반)
      $board_code = 'board_' . time();
      
      $stmt = $pdo->prepare("INSERT INTO hopec_boards 
                              (board_name, board_code, board_type, description, use_category, category_list, is_active, allow_attachments, write_level, reply_level) 
                              VALUES 
                              (:name, :code, :board_type, :description, :use_category, :category_list, :is_active, :allow_attachments, :write_level, :reply_level)");
      
      $result = $stmt->execute([
        ':name' => $name,
        ':code' => $board_code,
        ':board_type' => $type,
        ':description' => $description,
        ':use_category' => $use_category,
        ':category_list' => $category_list,
        ':is_active' => $is_active,
        ':allow_attachments' => $allow_attachments,
        ':write_level' => $write_level,
        ':reply_level' => $reply_level
      ]);
      
      if ($result) {
        // 성공 메시지와 함께 목록 페이지로 리디렉션
        header("Location: list.php?success=1");
        exit;
      } else {
        $errors[] = '게시판 저장 중 오류가 발생했습니다.';
      }
    } catch (PDOException $e) {
      $errors[] = '데이터베이스 오류: ' . $e->getMessage();
    }
  }
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
  <title>게시판 추가 - <?= htmlspecialchars($admin_title) ?></title>
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

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">
    <a href="<?= admin_url('index.php') ?>" class="text-white text-decoration-none"><?= htmlspecialchars($admin_title) ?></a>
  </div>
  <a href="<?= admin_url('index.php') ?>">📊 대시보드</a>
  <a href="<?= admin_url('posts/list.php') ?>">📝 게시글 관리</a>
  <a href="<?= admin_url('boards/list.php') ?>" class="active">📋 게시판 관리</a>
  <a href="<?= admin_url('menu/list.php') ?>">🧭 메뉴 관리</a>
  <a href="<?= admin_url('inquiries/list.php') ?>">📬 문의 관리</a>
  <a href="<?= admin_url('events/list.php') ?>">📅 행사 관리</a>
  <a href="<?= admin_url('files/list.php') ?>">📎 자료실 관리</a>
  <a href="<?= admin_url('settings/site_settings.php') ?>">🎨 디자인 설정</a>
  <a href="<?= admin_url('system/performance.php') ?>">⚡ 성능 모니터링</a>
  <a href="<?= admin_url('logout.php') ?>">🚪 로그아웃</a>
</div>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <!-- 상단 네비게이션 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= admin_url('index.php') ?>">관리자</a></li>
                <li class="breadcrumb-item"><a href="list.php">게시판 관리</a></li>
                <li class="breadcrumb-item active">새 게시판 추가</li>
            </ol>
        </nav>
        
        <div class="btn-group">
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-list"></i> 목록
            </a>
        </div>
    </div>

    <div class="container-fluid">
  
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <form method="POST" class="needs-validation" novalidate>
    <div class="mb-4">
      <label class="form-label fw-bold">게시판 이름</label>
      <input type="text" name="name" class="form-control" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
      <div class="form-text">사용자들에게 표시될 게시판의 이름을 입력하세요. (100자 이내)</div>
    </div>
    
    <div class="mb-4">
      <label class="form-label fw-bold">게시판 설명</label>
      <textarea name="description" class="form-control" rows="3"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
      <div class="form-text">게시판에 대한 간략한 설명을 입력하세요. (선택사항)</div>
    </div>
    
    <div class="mb-4">
      <label class="form-label fw-bold">카테고리 설정</label>
      <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" role="switch" id="use_category" name="use_category" <?= isset($use_category) && $use_category ? 'checked' : '' ?> 
          onchange="toggleCategoryInput()">
        <label class="form-check-label" for="use_category">이 게시판에서 카테고리 사용하기</label>
      </div>
      <div id="category_section" class="<?= isset($use_category) && $use_category ? '' : 'd-none' ?>">
        <textarea name="category_list" id="category_list" class="form-control" rows="3" 
          placeholder="각 카테고리를 쉼표(,)로 구분하여 입력하세요. 예: 공지,질문,자료,기타"><?= isset($category_list) ? htmlspecialchars($category_list) : '' ?></textarea>
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
            <div class="card board-type-card <?= (isset($type) && $type === $boardType) || (!isset($type) && $boardType === 'basic') ? 'selected' : '' ?>" 
                 data-type="<?= $boardType ?>" onclick="selectBoardType('<?= $boardType ?>')">
              <div class="card-body text-center">
                <div class="type-icon"><?= $iconClass ?></div>
                <h5 class="card-title"><?= $typeTitle ?></h5>
                <p class="card-text small"><?= $boardTypeDesc[$boardType] ?></p>
                <input type="radio" name="type" value="<?= $boardType ?>" class="d-none" 
                       <?= (isset($type) && $type === $boardType) || (!isset($type) && $boardType === 'basic') ? 'checked' : '' ?>>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                 <?= !isset($is_active) || $is_active ? 'checked' : '' ?>>
          <label class="form-check-label" for="is_active">게시판 활성화</label>
        </div>
        <div class="form-text">비활성화된 게시판은 사용자에게 표시되지 않습니다.</div>
      </div>
      <div class="col-md-6">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="allow_attachments" id="allow_attachments" 
                 <?= !isset($allow_attachments) || $allow_attachments ? 'checked' : '' ?>>
          <label class="form-check-label" for="allow_attachments">첨부파일 허용</label>
        </div>
        <div class="form-text">사용자가 게시글 작성 시 첨부파일을 올릴 수 있습니다.</div>
      </div>
    </div>
    
    <div class="mb-4">
      <label class="form-label fw-bold">글쓰기 권한</label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="write_level" id="write_level_0" value="0" 
               <?= !isset($write_level) || $write_level == 0 ? 'checked' : '' ?>>
        <label class="form-check-label" for="write_level_0">
          권한 없음
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="write_level" id="write_level_1" value="1"
               <?= isset($write_level) && $write_level == 1 ? 'checked' : '' ?>>
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
               <?= !isset($reply_level) || $reply_level == 0 ? 'checked' : '' ?>>
        <label class="form-check-label" for="reply_level_0">
          댓글 불가
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="reply_level" id="reply_level_1" value="1"
               <?= isset($reply_level) && $reply_level == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="reply_level_1">
          댓글 가능
        </label>
      </div>
      <div class="form-text">게시판에서 댓글 작성 가능 여부를 설정합니다.</div>
    </div>
    
    <div class="d-flex justify-content-center mt-5">
      <button type="submit" class="btn btn-primary">게시판 생성</button>
      <a href="list.php" class="btn btn-outline-secondary ms-2">취소</a>
    </div>
  </form>
</div>

<script>
  function selectBoardType(type) {
    // 모든 카드에서 선택 클래스 제거
    document.querySelectorAll('.board-type-card').forEach(card => {
      card.classList.remove('selected');
    });
    
    // 선택한 카드에 선택 클래스 추가
    const selectedCard = document.querySelector(`.board-type-card[data-type="${type}"]`);
    selectedCard.classList.add('selected');
    
    // 라디오 버튼 선택
    selectedCard.querySelector('input[type="radio"]').checked = true;
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