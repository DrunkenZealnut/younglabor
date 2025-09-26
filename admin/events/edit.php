<?php include '../auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결
require_once '../db.php';
require_once '../../includes/config_helpers.php';

// 행사 ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: list.php");
  exit;
}

$event_id = (int)$_GET['id'];

// 파일 업로드 설정
$upload_dir = '../../uploads/events/';
if (!file_exists($upload_dir)) {
  mkdir($upload_dir, 0755, true);
}

// 행사 정보 조회
try {
  $stmt = $pdo->prepare("SELECT * FROM " . get_table_name('events') . " WHERE id = ?");
  $stmt->execute([$event_id]);
  $event = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$event) {
    // 행사가 존재하지 않을 경우
    header("Location: list.php");
    exit;
  }

} catch (PDOException $e) {
  die("행사 정보 조회 중 오류가 발생했습니다: " . $e->getMessage());
}

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = isset($_POST['title']) ? trim($_POST['title']) : '';
  $description = isset($_POST['description']) ? trim($_POST['description']) : '';
  $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
  $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
  $location = isset($_POST['location']) ? trim($_POST['location']) : '';
  $max_participants = isset($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;
  $status = isset($_POST['status']) ? trim($_POST['status']) : '준비중';

  // 유효성 검사
  $errors = [];

  if (empty($title)) {
    $errors[] = '행사 제목을 입력해주세요.';
  }

  if (empty($start_date)) {
    $errors[] = '시작 일시를 입력해주세요.';
  }

  if (empty($end_date)) {
    $errors[] = '종료 일시를 입력해주세요.';
  } else if ($end_date < $start_date) {
    $errors[] = '종료 일시는 시작 일시보다 이후여야 합니다.';
  }

  if (empty($location)) {
    $errors[] = '장소를 입력해주세요.';
  }

  // 썸네일 이미지 처리
  $thumbnail_path = $event['thumbnail'];
  
  if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $temp_name = $_FILES['thumbnail']['tmp_name'];
    $name = $_FILES['thumbnail']['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
    // 이미지 타입 확인
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed_types)) {
      $errors[] = '썸네일은 JPG, JPEG, PNG, GIF 형식만 허용됩니다.';
    } else {
      // 파일명 중복 방지를 위해 고유한 파일명 생성
      $unique_name = uniqid('event_', true) . '.' . $ext;
      $target_file = $upload_dir . $unique_name;
      
      if (move_uploaded_file($temp_name, $target_file)) {
        // 이전 썸네일 파일 삭제
        if (!empty($event['thumbnail'])) {
          $old_thumbnail = '../../' . $event['thumbnail'];
          if (file_exists($old_thumbnail)) {
            unlink($old_thumbnail);
          }
        }
        
        $thumbnail_path = 'uploads/events/' . $unique_name;
      } else {
        $errors[] = '파일 업로드 중 오류가 발생했습니다.';
      }
    }
  } else if (isset($_POST['remove_thumbnail']) && $_POST['remove_thumbnail'] === '1') {
    // 썸네일 삭제 요청
    if (!empty($event['thumbnail'])) {
      $old_thumbnail = '../../' . $event['thumbnail'];
      if (file_exists($old_thumbnail)) {
        unlink($old_thumbnail);
      }
    }
    $thumbnail_path = null;
  }

  // 에러가 없으면 DB에 저장
  if (empty($errors)) {
    try {
      $sql = "UPDATE " . get_table_name('events') . " SET 
              title = ?, 
              description = ?, 
              start_date = ?, 
              end_date = ?, 
              location = ?, 
              max_participants = ?, 
              status = ?, 
              thumbnail = ?,
              updated_at = CURRENT_TIMESTAMP
              WHERE id = ?";
      
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        $title,
        $description,
        $start_date,
        $end_date,
        $location,
        $max_participants,
        $status,
        $thumbnail_path,
        $event_id
      ]);
      
      $success_message = '행사 정보가 성공적으로 수정되었습니다.';
      
      // 정보 다시 조회
      $stmt = $pdo->prepare("SELECT * FROM " . get_table_name('events') . " WHERE id = ?");
      $stmt->execute([$event_id]);
      $event = $stmt->fetch(PDO::FETCH_ASSOC);
      
    } catch (PDOException $e) {
      $errors[] = '행사 정보 수정 중 오류가 발생했습니다: ' . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>행사 정보 수정 - 관리자</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- 썸머노트 에디터 -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
  <!-- 날짜/시간 피커 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      width: 220px;
      background-color: #343a40;
      color: white;
      min-height: 100vh;
    }
    .sidebar a {
      color: white;
      padding: 12px 16px;
      display: block;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #495057;
    }
    .main-content {
      flex-grow: 1;
      padding: 30px;
      background-color: #f8f9fa;
    }
    .sidebar .logo {
      font-weight: bold;
      font-size: 1.3rem;
      padding: 16px;
      border-bottom: 1px solid #495057;
    }
    .note-editor .dropdown-toggle::after {
      display: none;
    }
    .custom-file-label::after {
      content: "찾아보기";
    }
    .thumbnail-preview {
      max-width: 200px;
      max-height: 200px;
    }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo"><?= htmlspecialchars($admin_title) ?></div>
  <a href="../index.php">📊 대시보드</a>
  <a href="../posts/list.php">📝 게시글 관리</a>
  <a href="../boards/list.php">📋 게시판 관리</a>
  <a href="../menu/list.php">🧭 메뉴 관리</a>
  <a href="../inquiries/list.php">📬 문의 관리</a>
  <a href="list.php" class="active bg-primary">📅 행사 관리</a>
  <a href="../files/list.php">📂 자료실</a>
  <a href="../settings/site_settings.php">🎨 디자인 설정</a>
  <a href="../system/performance.php">⚡ 성능 모니터링</a>
  <a href="../logout.php">🚪 로그아웃</a>
</div>

<!-- 본문 -->
<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2>행사 정보 수정</h2>
      <p class="text-muted">ID: <?= $event_id ?> / 최종 수정일: <?= date('Y년 m월 d일 H:i', strtotime($event['updated_at'])) ?></p>
    </div>
    <div>
      <a href="view.php?id=<?= $event_id ?>" class="btn btn-outline-primary me-2">
        <i class="bi bi-eye"></i> 상세 보기
      </a>
      <a href="list.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> 목록으로
      </a>
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
  
  <?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
  <?php endif; ?>
  
  <!-- 행사 수정 폼 -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="edit.php?id=<?= $event_id ?>" method="POST" enctype="multipart/form-data">
        <div class="row mb-3">
          <div class="col-md-8">
            <div class="mb-3">
              <label for="title" class="form-label">행사 제목 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">행사 설명</label>
              <textarea class="form-control" id="description" name="description" rows="10"><?= htmlspecialchars($event['description']) ?></textarea>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="mb-3">
              <label for="thumbnail" class="form-label">썸네일 이미지</label>
              
              <?php if (!empty($event['thumbnail'])): ?>
                <div class="mb-2">
                  <img src="../../<?= htmlspecialchars($event['thumbnail']) ?>" alt="현재 썸네일" class="thumbnail-preview border rounded">
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="remove_thumbnail" name="remove_thumbnail" value="1">
                    <label class="form-check-label" for="remove_thumbnail">
                      썸네일 삭제
                    </label>
                  </div>
                </div>
              <?php endif; ?>
              
              <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
              <small class="form-text text-muted">새 이미지를 선택하면 기존 이미지가 대체됩니다. 권장 크기: 800x600px, 최대 2MB</small>
              
              <?php if (empty($event['thumbnail'])): ?>
                <div class="mt-2">
                  <img id="thumbnail-preview" class="thumbnail-preview border rounded" style="display: none;" alt="썸네일 미리보기">
                </div>
              <?php endif; ?>
            </div>
            
            <div class="mb-3">
              <label for="start_date" class="form-label">시작 일시 <span class="text-danger">*</span></label>
              <input type="text" class="form-control flatpickr" id="start_date" name="start_date" value="<?= htmlspecialchars($event['start_date']) ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="end_date" class="form-label">종료 일시 <span class="text-danger">*</span></label>
              <input type="text" class="form-control flatpickr" id="end_date" name="end_date" value="<?= htmlspecialchars($event['end_date']) ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="location" class="form-label">장소 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($event['location']) ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="max_participants" class="form-label">최대 참가자 수</label>
              <input type="number" class="form-control" id="max_participants" name="max_participants" min="0" value="<?= htmlspecialchars($event['max_participants'] ?? '') ?>">
              <small class="form-text text-muted">비워두면 인원 제한이 없습니다</small>
            </div>
            
            <div class="mb-3">
              <label for="status" class="form-label">상태</label>
              <select class="form-select" id="status" name="status">
                <option value="준비중" <?= ($event['status'] === '준비중') ? 'selected' : '' ?>>준비중</option>
                <option value="진행예정" <?= ($event['status'] === '진행예정') ? 'selected' : '' ?>>진행예정</option>
                <option value="진행중" <?= ($event['status'] === '진행중') ? 'selected' : '' ?>>진행중</option>
                <option value="종료" <?= ($event['status'] === '종료') ? 'selected' : '' ?>>종료</option>
              </select>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end border-top pt-3">
          <a href="list.php" class="btn btn-outline-secondary me-2">취소</a>
          <button type="submit" class="btn btn-primary">변경사항 저장</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- jQuery, Popper.js, Bootstrap Javascript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- 썸머노트 에디터 -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-ko-KR.min.js"></script>
<!-- Flatpickr (날짜/시간 피커) -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ko.js"></script>

<script>
$(document).ready(function() {
  // 썸머노트 에디터 초기화
  $('#description').summernote({
    height: 300,
    lang: 'ko-KR',
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'underline', 'clear']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'picture']],
      ['view', ['fullscreen', 'codeview', 'help']]
    ],
    callbacks: {
      onImageUpload: function(files) {
        // 이미지 업로드 처리 (실제로는 서버로 전송 후 URL을 받아 삽입)
        alert('이미지 업로드 기능은 별도로 구현해야 합니다.');
      }
    }
  });
  
  // Flatpickr 초기화 (한국어, 날짜+시간)
  $(".flatpickr").flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    locale: "ko",
    time_24hr: true
  });
  
  // 썸네일 미리보기
  $('#thumbnail').change(function() {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        $('#thumbnail-preview').attr('src', e.target.result).css('display', 'block');
      }
      reader.readAsDataURL(file);
    }
  });
  
  // 썸네일 삭제 체크박스 처리
  $('#remove_thumbnail').change(function() {
    if ($(this).is(':checked')) {
      $('#thumbnail').prop('disabled', true);
    } else {
      $('#thumbnail').prop('disabled', false);
    }
  });
});
</script>
</body>
</html> 