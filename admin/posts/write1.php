<?php
include '../auth.php'; // 관리자 인증 확인
require_once '../db.php'; // DB 연결

// 관리자 사용자 이름 가져오기
$admin_username = $_SESSION['admin_username'] ?? '관리자';

// 게시판 목록 가져오기 (캘린더 타입 제외)
$stmt = $pdo->query("SELECT id, board_name FROM hopec_boards WHERE board_type != 'calendar' ORDER BY board_name ASC");
$boards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 게시글 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 폼 데이터 가져오기
    $board_id = (int)$_POST['board_id'];
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $author = trim($_POST['author']);
    $is_notice = isset($_POST['is_notice']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // 폼 유효성 검사
    $errors = [];
    
    if (empty($board_id)) {
        $errors[] = "게시판을 선택해주세요.";
    }
    
    if (empty($title)) {
        $errors[] = "제목을 입력해주세요.";
    }
    
    // 본문 필수 입력 조건 제거
    // if (empty($content)) {
    //     $errors[] = "내용을 입력해주세요.";
    // }
    
    if (empty($author)) {
        $errors[] = "작성자를 입력해주세요.";
    }
    
    // 썸네일 이미지 처리
    $thumbnail = null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $upload_dir = '../../uploads/posts/';
        $thumbnail_name = date('YmdHis') . '_' . basename($_FILES['thumbnail']['name']);
        $thumbnail_path = $upload_dir . $thumbnail_name;
        $thumbnail_type = strtolower(pathinfo($thumbnail_path, PATHINFO_EXTENSION));
        
        // 이미지 타입 확인
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($thumbnail_type, $allowed_types)) {
            $errors[] = "썸네일은 JPG, JPEG, PNG, GIF 파일만 업로드 가능합니다.";
        } else {
            // 파일 업로드
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
                $thumbnail = 'uploads/posts/' . $thumbnail_name;
            } else {
                $errors[] = "썸네일 업로드에 실패했습니다.";
            }
        }
    }
    
    // 오류가 없으면 게시글 저장
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO hopec_posts 
                (board_id, category_id, title, content, author, is_notice, is_published, thumbnail, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $board_id, $category_id, $title, $content, $author, $is_notice, $is_published, $thumbnail
            ]);
            
            if ($result) {
                $post_id = $pdo->lastInsertId();
                
                // 콘텐츠에서 임시 이미지를 실제 DB에 연결
                processContentImages($content, $post_id, $pdo);
                
                // 첨부파일 처리
                if (isset($_POST['allow_attachments']) && $_POST['allow_attachments'] === 'true' && !empty($_FILES['attachments']['name'][0])) {
                    $uploads_dir = '../../uploads/posts/' . $post_id;
                    
                    // 디렉토리가 없는 경우 생성
                    if (!is_dir($uploads_dir)) {
                        mkdir($uploads_dir, 0755, true);
                    }
                    
                    $file_count = count($_FILES['attachments']['name']);
                    
                    for ($i = 0; $i < $file_count; $i++) {
                        if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                            $tmp_name = $_FILES['attachments']['tmp_name'][$i];
                            $name = $_FILES['attachments']['name'][$i];
                            $file_size = $_FILES['attachments']['size'][$i];
                            $file_type = $_FILES['attachments']['type'][$i];
                            
                            // 파일명 안전하게 처리
                            $safe_filename = preg_replace('/[^a-zA-Z0-9가-힣._-]/', '_', $name);
                            $safe_filename = time() . '_' . $safe_filename;
                            
                            // 파일 저장
                            $dest_path = $uploads_dir . '/' . $safe_filename;
                            
                            if (move_uploaded_file($tmp_name, $dest_path)) {
                                // DB에 첨부파일 정보 저장
                                $file_stmt = $pdo->prepare("
                                    INSERT INTO hopec_post_attachments 
                                    (post_id, file_name, file_path, file_size, file_type)
                                    VALUES (?, ?, ?, ?, ?)
                                ");
                                
                                $file_path = 'uploads/posts/' . $post_id . '/' . $safe_filename;
                                $file_stmt->execute([$post_id, $name, $file_path, $file_size, $file_type]);
                            }
                        }
                    }
                }
                
                // 성공 메시지 및 리디렉션
                $_SESSION['success_message'] = "게시글이 성공적으로 저장되었습니다.";
                header("Location: list.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "게시글 저장 중 오류가 발생했습니다: " . $e->getMessage();
        }
    }
}

// 콘텐츠 내의 이미지 처리 함수
function processContentImages($content, $post_id, $pdo) {
    // temp_ 로 시작하는 임시 이미지 파일 찾기
    preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $content, $matches);
    
    if (isset($matches[1]) && is_array($matches[1])) {
        foreach ($matches[1] as $image_src) {
            // 임시 이미지인 경우에만 처리
            if (strpos($image_src, 'uploads/temp/') !== false) {
                $temp_file = '../../' . $image_src;
                $new_file = str_replace('temp/', 'posts/', $temp_file);
                
                // 디렉토리 확인 및 생성
                $new_dir = dirname($new_file);
                if (!is_dir($new_dir)) {
                    mkdir($new_dir, 0755, true);
                }
                
                // 파일 이동
                if (file_exists($temp_file) && is_file($temp_file)) {
                    rename($temp_file, $new_file);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>새 게시글 작성 - 관리자</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- Summernote CSS -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
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
    /* 썸머노트 에디터 스타일 */
    .note-editor {
      margin-bottom: 0;
    }
    .note-editor.note-frame {
      border: 1px solid #ced4da;
    }
    .note-editor .note-toolbar {
      background-color: #f8f9fa;
    }
    .note-editor .note-toolbar .note-btn {
      background-color: #fff;
      border-color: #ced4da;
    }
    .note-editor .note-toolbar .note-btn:hover {
      background-color: #e9ecef;
    }
    .note-editor .note-toolbar .custom-btn {
      padding: 5px 10px;
      font-size: 14px;
    }
    /* 썸머노트 에디터 높이 조정 */
    .note-editable {
      min-height: 300px;
    }
    .thumbnail-preview {
      max-width: 150px;
      max-height: 150px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">희망씨 관리자</div>
  <a href="../index.php">📊 대시보드</a>
  <a href="list.php" class="active" style="background-color: #495057;">📝 게시글 관리</a>
  <a href="../boards/list.php">📋 게시판 관리</a>
  <a href="../menu/list.php">🧭 메뉴 관리</a>
  <a href="../inquiries/list.php">📬 문의 관리</a>
  <a href="../events/list.php">📅 행사 관리</a>
  <a href="../files/list.php">📂 자료실</a>
  <a href="../settings/site_settings.php">🎨 디자인 설정</a>
  <a href="../system/performance.php">⚡ 성능 모니터링</a>
  <a href="../logout.php">🚪 로그아웃</a>
</div>

<!-- 본문 -->
<div class="main-content">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="../index.php">대시보드</a></li>
      <li class="breadcrumb-item"><a href="list.php">게시글 관리</a></li>
      <li class="breadcrumb-item active" aria-current="page">새 게시글 작성</li>
    </ol>
  </nav>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>새 게시글 작성</h2>
    <a href="list.php" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> 목록으로
    </a>
  </div>
  
  <!-- 오류 메시지 표시 -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <!-- 게시글 작성 폼 -->
  <div class="card">
    <div class="card-body">
      <form method="post" enctype="multipart/form-data">
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="board_id" class="form-label">게시판 선택</label>
            <select class="form-select" id="board_id" name="board_id" required>
              <option value="">-- 게시판 선택 --</option>
              <?php foreach ($boards as $board): ?>
                <option value="<?= $board['id'] ?>"><?= htmlspecialchars($board['board_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label for="author" class="form-label">작성자</label>
            <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($admin_username) ?>" required>
          </div>
        </div>
        
        <div id="category_container" class="mb-3 d-none">
          <label for="category_id" class="form-label">카테고리</label>
          <select class="form-select" id="category_id" name="category_id">
            <option value="">-- 카테고리 선택 --</option>
          </select>
        </div>
        
        <div class="mb-3">
          <label for="title" class="form-label">제목</label>
          <input type="text" class="form-control" id="title" name="title" required>
        </div>
        
        <div class="mb-3">
          <label for="content" class="form-label">내용</label>
          <div class="mb-2">
            <button type="button" class="btn btn-outline-primary btn-sm" id="direct-upload-btn">
              <i class="bi bi-image"></i> 이미지 업로드
            </button>
          </div>
          <textarea id="content" name="content" class="summernote"></textarea>
        </div>
        
        <div class="mb-3">
          <label for="thumbnail" class="form-label">썸네일 이미지</label>
          <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
          <div class="form-text">썸네일로 사용할 이미지를 선택하세요. (선택사항)</div>
        </div>
        
        <!-- 첨부파일 영역 (게시판 설정에 따라 표시) -->
        <div class="mb-3" id="attachments_container" style="display: block;">
          <label for="attachments" class="form-label">첨부파일</label>
          <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
          <div class="form-text">
            첨부할 파일을 선택하세요. 여러 파일을 한번에 선택할 수 있습니다. (선택사항)<br>
            첨부 가능한 파일: 문서(pdf, doc, docx, hwp, txt), 이미지(jpg, jpeg, png, gif), 압축파일(zip, rar) / 최대 10MB
          </div>
          <input type="hidden" name="allow_attachments" id="allow_attachments" value="true">
        </div>
        
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="is_notice" name="is_notice">
          <label class="form-check-label" for="is_notice">
            공지사항으로 등록
          </label>
        </div>
        
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="is_published" name="is_published" checked>
          <label class="form-check-label" for="is_published">
            바로 발행하기 (체크 해제시 임시저장)
          </label>
        </div>
        
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-light me-2" id="cancel-btn">취소</button>
          <button type="submit" class="btn btn-primary">저장</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 기본 모달 (스크립트에서 동적으로 대체됨) -->
<div id="modalContainer"></div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-ko-KR.min.js"></script>
<script>
  $(document).ready(function() {
    // 페이지 로드 시 첨부파일 영역 상태 확인
    console.log('페이지 로드: 첨부파일 영역 상태 =', $('#attachments_container').is(':visible') ? '표시됨' : '숨겨짐');
    
    // 썸머노트 에디터 초기화
    $('.summernote').summernote({
      lang: 'ko-KR', // 한글 설정
      height: 400,
      placeholder: '내용을 입력하세요',
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
        url: 'upload_image.php', // 이미지 업로드 처리 스크립트
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
              // 에디터에 이미지 삽입 (상대 경로 처리)
              let imageUrl = response.url;
              
              // 다양한 URL 형식 지원 (새 API)
              if (response.urls && response.urls.admin_relative) {
                imageUrl = response.urls.admin_relative;
              } else if (imageUrl.startsWith('uploads/')) {
                // 이전 버전 호환성 유지 - 상대 경로 변환
                imageUrl = '../../' + imageUrl;
              }
              
              $(editor).summernote('insertImage', imageUrl, function($image) {
                $image.css('max-width', '100%');
              });
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
                <iframe src="direct_upload.php" style="width:100%; height:600px; border:none;"></iframe>
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
      $('.summernote').summernote('insertImage', url, function($image) {
        $image.css('max-width', '100%');
      });
    };
    
    // 직접 업로드 모달 닫기 함수
    window.closeDirectUpload = function() {
      const modal = bootstrap.Modal.getInstance(document.getElementById('directUploadModal'));
      if (modal) {
        modal.hide();
      }
    };
    
    // 취소 버튼
    $('#cancel-btn').click(function() {
      if (confirm('작성 중인 내용이 저장되지 않습니다. 정말 취소하시겠습니까?')) {
        window.location.href = 'list.php';
      }
    });
    
    // 게시판 선택 변경 이벤트 직접 연결
    $('#board_id').on('change', function(e) {
      // 이벤트 전파 중지 및 폼 제출 방지
      e.preventDefault();
      e.stopPropagation();
      
      const boardId = $(this).val();
      console.log('게시판 선택 변경:', boardId);
      
      // 게시판 ID가 있는 경우에만 AJAX 요청 실행
      if (boardId) {
        // 페이지 로드 시 첨부파일 영역 상태 확인
        console.log('게시판 선택 전 첨부파일 영역 상태:', $('#attachments_container').css('display'));
        
        $.ajax({
          url: 'get_board_info.php',
          type: 'GET',
          data: { board_id: boardId },
          dataType: 'json',
          success: function(response) {
            console.log('응답 받음:', response);
            
            // 첨부파일 허용 여부 확인 및 처리
            if (response.allow_attachments == 1) {
              $('#attachments_container').css('display', 'block');
              $('#allow_attachments').val('true');
              console.log('첨부파일 허용됨 - 영역 표시');
            } else {
              $('#attachments_container').css('display', 'none');
              $('#allow_attachments').val('false');
              console.log('첨부파일 허용 안됨 - 영역 숨김');
            }
            
            // 카테고리 처리
            loadCategories(boardId);
          },
          error: function(xhr, status, error) {
            console.error('게시판 정보 로딩 실패:', error);
            alert('게시판 정보를 가져오는 중 오류가 발생했습니다: ' + error);
          }
        });
      } else {
        // 게시판 선택이 없는 경우 첨부파일 영역 숨김
        $('#attachments_container').css('display', 'none');
        $('#allow_attachments').val('false');
        $('#category_container').addClass('d-none');
      }
      
      return false;
    });
    
    // 게시판 변경 시 카테고리 목록 로드
    function loadCategories(boardId) {
      if (!boardId) {
        $('#category_container').addClass('d-none');
        return;
      }
      
      $.ajax({
        url: 'get_categories.php',
        type: 'GET',
        data: { board_id: boardId },
        dataType: 'json',
        success: function(response) {
          console.log('카테고리 응답:', response);
          
          if (response.use_category && response.categories && response.categories.length > 0) {
            // 카테고리 목록 업데이트
            var categorySelect = $('#category_id');
            categorySelect.empty();
            categorySelect.append('<option value="">-- 카테고리 선택 --</option>');
            
            $.each(response.categories, function(index, category) {
              categorySelect.append('<option value="' + category.id + '">' + category.name + '</option>');
            });
            
            $('#category_container').removeClass('d-none');
          } else {
            $('#category_container').addClass('d-none');
          }
        },
        error: function(xhr, status, error) {
          console.error('카테고리 로딩 실패:', error);
          console.error('상태:', status);
          console.error('응답:', xhr.responseText);
          $('#category_container').addClass('d-none');
        }
      });
    }
  });
</script>
</body>
</html> 