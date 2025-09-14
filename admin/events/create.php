<?php include '../auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결
require_once '../db.php';

// 파일 업로드 설정
$upload_dir = '../../uploads/events/';
if (!file_exists($upload_dir)) {
  mkdir($upload_dir, 0755, true);
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
  $thumbnail_path = null;
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
        $thumbnail_path = 'uploads/events/' . $unique_name;
      } else {
        $errors[] = '파일 업로드 중 오류가 발생했습니다.';
      }
    }
  }

  // 에러가 없으면 DB에 저장
  if (empty($errors)) {
    try {
      $sql = "INSERT INTO hopec_events (title, description, start_date, end_date, location, max_participants, status, thumbnail)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
      
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        $title,
        $description,
        $start_date,
        $end_date,
        $location,
        $max_participants,
        $status,
        $thumbnail_path
      ]);
      
      $event_id = $pdo->lastInsertId();
      
      $success_message = '행사가 성공적으로 등록되었습니다.';
      
      // 리다이렉션
      header("Location: view.php?id=$event_id&created=1");
      exit;
    } catch (PDOException $e) {
      $errors[] = '행사 등록 중 오류가 발생했습니다: ' . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>새 행사 등록 - 관리자</title>
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
      display: none;
    }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">희망씨 관리자</div>
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
      <h2>새 행사 등록</h2>
      <p class="text-muted">희망씨 행사 정보를 등록합니다.</p>
    </div>
    <a href="list.php" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> 행사 목록으로 돌아가기
    </a>
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
  
  <!-- 행사 등록 폼 -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="create.php" method="POST" enctype="multipart/form-data">
        <div class="row mb-3">
          <div class="col-md-8">
            <div class="mb-3">
              <label for="title" class="form-label">행사 제목 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" name="title" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">행사 설명</label>
              <textarea class="form-control" id="description" name="description" rows="10"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="mb-3">
              <label for="thumbnail" class="form-label">썸네일 이미지</label>
              <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
              <small class="form-text text-muted">권장 크기: 800x600px, 최대 2MB</small>
              <div class="mt-2">
                <img id="thumbnail-preview" class="thumbnail-preview border rounded" alt="썸네일 미리보기">
              </div>
            </div>
            
            <div class="mb-3">
              <label for="start_date" class="form-label">시작 일시 <span class="text-danger">*</span></label>
              <input type="text" class="form-control flatpickr" id="start_date" name="start_date" value="<?= isset($start_date) ? htmlspecialchars($start_date) : '' ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="end_date" class="form-label">종료 일시 <span class="text-danger">*</span></label>
              <input type="text" class="form-control flatpickr" id="end_date" name="end_date" value="<?= isset($end_date) ? htmlspecialchars($end_date) : '' ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="location" class="form-label">장소 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="location" name="location" value="<?= isset($location) ? htmlspecialchars($location) : '' ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="max_participants" class="form-label">최대 참가자 수</label>
              <input type="number" class="form-control" id="max_participants" name="max_participants" min="0" value="<?= isset($max_participants) ? htmlspecialchars($max_participants) : '' ?>">
              <small class="form-text text-muted">비워두면 인원 제한이 없습니다</small>
            </div>
            
            <div class="mb-3">
              <label for="status" class="form-label">상태</label>
              <select class="form-select" id="status" name="status">
                <option value="준비중" <?= (isset($status) && $status === '준비중') ? 'selected' : '' ?>>준비중</option>
                <option value="진행예정" <?= (isset($status) && $status === '진행예정') ? 'selected' : '' ?>>진행예정</option>
                <option value="진행중" <?= (isset($status) && $status === '진행중') ? 'selected' : '' ?>>진행중</option>
                <option value="종료" <?= (isset($status) && $status === '종료') ? 'selected' : '' ?>>종료</option>
              </select>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end border-top pt-3">
          <a href="list.php" class="btn btn-outline-secondary me-2">취소</a>
          <button type="submit" class="btn btn-primary">행사 등록</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 기본 모달 (스크립트에서 동적으로 대체됨) -->
<div id="modalContainer"></div>

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
    lang: 'ko-KR',
    height: 300,
    placeholder: '행사 설명을 입력하세요',
    focus: true,
    styleTags: [
      'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
    ],
    toolbar: [
      // 텍스트 스타일 관련
      ['style', ['style']],
      ['font', ['bold', 'underline', 'italic', 'clear']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      // 삽입 관련
      ['insert', ['link', 'picture', 'video', 'table', 'hr']],
      // 이미지 직접 업로드 버튼 추가
      ['custom', ['directUpload']],
      // 기타 도구
      ['view', ['fullscreen', 'codeview', 'help']]
    ],
    // 이미지 업로드 처리
    callbacks: {
      onImageUpload: function(files) {
        for (let i = 0; i < files.length; i++) {
          uploadImage(files[i], this);
        }
      }
    },
    // 커스텀 버튼 추가
    buttons: {
      directUpload: function(context) {
        const ui = $.summernote.ui;
        const button = ui.button({
          className: 'custom-btn',
          contents: '<i class="bi bi-cloud-upload"></i> 이미지 업로드',
          tooltip: '이미지 직접 업로드',
          click: function() {
            openDirectUploadModal();
          }
        });
        return button.render();
      }
    }
  });
  
  // 이미지 업로드 함수
  function uploadImage(file, editor) {
    const formData = new FormData();
    formData.append('image', file);
    
    $.ajax({
      url: '../posts/upload_image.php', // 게시글 이미지 업로드 스크립트 사용
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response) {
        try {
          if (typeof response === 'string') {
            response = JSON.parse(response);
          }
          
          if (response.success) {
            // 응답에서 받은 URL - 항상 상대 경로 사용
            let imageUrl = response.url;
            console.log('원본 응답 URL:', imageUrl);
            
            // 이미지 URL은 무조건 상대 경로로 사용 (uploads/posts/...)
            // admin/posts 경로가 포함된 경우 제거
            if (imageUrl.includes('/admin/posts/uploads/')) {
              imageUrl = imageUrl.replace('/admin/posts/uploads/', '/uploads/');
            } else if (imageUrl.includes('admin/posts/uploads/')) {
              imageUrl = imageUrl.replace('admin/posts/uploads/', 'uploads/');
            }
            
            // 절대 URL 생성 (현재 페이지 기준)
            const baseUrl = window.location.origin + 
              (window.location.hostname === 'localhost' ? '' : '');
            
            // 슬래시로 시작하는지 확인
            if (imageUrl.startsWith('/')) {
              imageUrl = imageUrl.substring(1); // 앞의 슬래시 제거
            }
            
            // 최종 이미지 URL 생성
            const finalImageUrl = baseUrl + '/' + imageUrl;
            console.log('최종 이미지 URL (절대 경로):', finalImageUrl);
            
            // 에디터에 삽입할 때는 절대 URL 사용
            $(editor).summernote('insertImage', finalImageUrl);
          } else {
            alert('이미지 업로드 실패: ' + response.message);
            console.error('업로드 실패 상세 정보:', response.debug);
          }
        } catch (e) {
          alert('응답 처리 중 오류가 발생했습니다: ' + e.message);
          console.error('원본 응답:', response);
        }
      },
      error: function(xhr, status, error) {
        alert('이미지 업로드 중 오류가 발생했습니다: ' + error);
        console.error('AJAX 오류:', xhr.responseText);
      }
    });
  }
  
  // 직접 업로드 모달 열기
  function openDirectUploadModal() {
    // iframe을 사용하여 모달 내에 직접 업로드 페이지 로드
    let modalHtml = `
      <div class="modal fade" id="directUploadModal" tabindex="-1" aria-labelledby="directUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="directUploadModalLabel">이미지 직접 업로드</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <iframe src="../posts/direct_upload.php" style="width:100%; height:600px; border:none;"></iframe>
            </div>
          </div>
        </div>
      </div>
    `;
    
    // 기존 모달 제거 후 새로 추가
    $('#directUploadModal').remove();
    $('#modalContainer').html(modalHtml);
    
    // 모달 표시
    const modal = new bootstrap.Modal(document.getElementById('directUploadModal'));
    modal.show();
  }
  
  // 직접 업로드 모달에서 이미지 삽입하기 위한 함수
  window.insertDirectUploadImage = function(url) {
    console.log('원본 직접 업로드 URL:', url);
    
    // 이미지 URL 정리 - 항상 상대 경로 형태로 변환
    // admin/posts 경로가 포함된 경우 제거
    if (url.includes('/admin/posts/uploads/')) {
      url = url.replace('/admin/posts/uploads/', '/uploads/');
    } else if (url.includes('admin/posts/uploads/')) {
      url = url.replace('admin/posts/uploads/', 'uploads/');
    }
    
    // ../../ 형태의 상대 경로 처리
    if (url.startsWith('../../')) {
      url = url.substring(6); // '../../' 제거
    }
    
    // 절대 URL 생성 (현재 페이지 기준)
    const baseUrl = window.location.origin + 
      (window.location.hostname === 'localhost' ? '' : '');
    
    // 슬래시로 시작하는지 확인
    if (url.startsWith('/')) {
      url = url.substring(1); // 앞의 슬래시 제거
    }
    
    // 최종 이미지 URL 생성
    const finalImageUrl = baseUrl + '/' + url;
    console.log('최종 직접 업로드 URL (절대 경로):', finalImageUrl);
    
    // 에디터에 이미지 삽입 - 항상 절대 URL 사용
    $('#description').summernote('insertImage', finalImageUrl);
  };
  
  // 직접 업로드 모달 닫기 함수
  window.closeDirectUpload = function() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('directUploadModal'));
    if (modal) {
      modal.hide();
    }
  };
  
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
    } else {
      $('#thumbnail-preview').attr('src', '').css('display', 'none');
    }
  });
});
</script>
</body>
</html> 