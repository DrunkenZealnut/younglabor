<?php
// 게시글 작성 페이지
require_once '../bootstrap.php';

// 관리자 사용자 이름 가져오기
$admin_username = $_SESSION['admin_username'] ?? '관리자';

// CSRF 토큰 생성 보장
if (!isset($_SESSION['csrf_token'])) {
    generateCSRFToken();
}

// 사용 가능한 게시판들과 board_type 매핑
$board_types = [
    1 => ['name' => '재정보고', 'board_type' => 'finance_reports'],
    2 => ['name' => '공지사항', 'board_type' => 'notices'],
    3 => ['name' => '언론보도', 'board_type' => 'press'],
    4 => ['name' => '소식지', 'board_type' => 'newsletter'],
    5 => ['name' => '갤러리', 'board_type' => 'gallery'],
    6 => ['name' => '자료실', 'board_type' => 'resources'],
    7 => ['name' => '네팔나눔연대여행', 'board_type' => 'nepal_travel']
];

// 게시판 목록용 배열 생성
$boards = [];
foreach ($board_types as $id => $info) {
    $boards[] = ['id' => $id, 'board_name' => $info['name'], 'board_type' => $info['board_type']];
}

// 게시글 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 폼 데이터 가져오기
    $board_id = (int)$_POST['board_id'];
    $title = trim($_POST['title']);
    $content = $_POST['content'] ?? '';
    $author = trim($_POST['author']);
    
    // 기본적인 XSS 방지
    $content = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $content);
    $content = preg_replace('/javascript:/i', '', $content);
    $content = preg_replace('/on\w+\s*=/i', '', $content);
    
    // 폼 유효성 검사
    $errors = [];
    
    if (empty($board_id) || !isset($boards[$board_id - 1])) {
        $errors[] = "게시판을 선택해주세요.";
    }
    
    if (empty($title)) {
        $errors[] = "제목을 입력해주세요.";
    }
    
    if (empty($author)) {
        $errors[] = "작성자를 입력해주세요.";
    }
    
    // 오류가 없으면 게시글 저장
    if (empty($errors)) {
        try {
            // 선택된 게시판의 board_type 가져오기
            $selected_board = $boards[$board_id - 1];
            $board_type = $selected_board['board_type'];
            
            // hopec_posts 테이블에 board_type으로 데이터 삽입
            $sql = "INSERT INTO hopec_posts (
                board_type, wr_subject, wr_content, wr_name, wr_datetime, wr_ip, 
                wr_num, wr_reply, wr_parent, wr_is_comment, wr_comment, wr_comment_reply, 
                ca_name, wr_option, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit, 
                wr_hit, wr_good, wr_nogood, mb_id, wr_password, wr_email, wr_homepage, 
                wr_file, wr_last, wr_facebook_user, wr_twitter_user, 
                wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10
            ) VALUES (
                ?, ?, ?, ?, NOW(), ?, 
                0, '', 0, 0, 0, '', 
                '', '', '', '', 0, 0, 
                0, 0, 0, '', '', '', '', 
                0, '', '', '', 
                '', '', '', '', '', '', '', '', '', ''
            )";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $board_type,
                $title, 
                $content, 
                $author, 
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = '게시글이 성공적으로 작성되었습니다.';
                header("Location: list.php");
                exit;
            } else {
                $errors[] = "게시글 저장에 실패했습니다.";
            }
            
        } catch (PDOException $e) {
            $errors[] = "데이터베이스 오류: " . $e->getMessage();
        }
    }
}

// 페이지 제목 설정
$page_title = '새 게시글 작성';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?> - 희망씨 관리자</title>
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
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">
    <a href="/admin/index.php" class="text-white text-decoration-none">희망씨 관리자</a>
  </div>
  <a href="/admin/index.php">📊 대시보드</a>
  <a href="/admin/posts/list.php" class="active">📝 게시글 관리</a>
  <a href="/admin/boards/list.php">📋 게시판 관리</a>
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
    <!-- 메시지 표시 -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- 상단 네비게이션 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index.php">관리자</a></li>
                <li class="breadcrumb-item"><a href="list.php">게시글 관리</a></li>
                <li class="breadcrumb-item active">새 게시글 작성</li>
            </ol>
        </nav>
        
        <div class="btn-group">
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-list"></i> 목록
            </a>
        </div>
    </div>

    <!-- 게시글 작성 폼 -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title mb-0">
                <i class="bi bi-pencil-square"></i> 새 게시글 작성
            </h2>
        </div>
        
        <div class="card-body">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="board_id" class="form-label">게시판 <span class="text-danger">*</span></label>
                        <select class="form-select" id="board_id" name="board_id" required>
                            <option value="">게시판을 선택하세요</option>
                            <?php foreach ($boards as $board): ?>
                                <option value="<?= $board['id'] ?>" <?= (isset($_POST['board_id']) && $_POST['board_id'] == $board['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($board['board_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label">작성자 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="author" name="author" 
                               value="<?= htmlspecialchars($_POST['author'] ?? $admin_username) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">내용</label>
                    <textarea class="form-control" id="content" name="content" rows="15"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <div>
                        <a href="list.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 취소
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 작성 완료
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Summernote Editor Integration -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<style>
/* Summernote Admin Theme Integration */
.note-editor.note-frame {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.note-editor.note-frame.note-focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.note-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0.375rem 0.375rem 0 0;
    padding: 0.75rem;
}

.note-btn-group {
    margin-right: 0.25rem;
}

.note-btn {
    padding: 0.375rem 0.5rem;
    border-radius: 0.25rem;
    border: none;
    background: transparent;
    transition: all 0.15s ease-in-out;
}

.note-btn:hover {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.note-btn.active {
    background: #0d6efd;
    color: white;
}

.note-editing-area {
    min-height: 350px;
}

.note-editable {
    padding: 1.5rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 0.95rem;
    line-height: 1.7;
    color: #495057;
}

.note-editable h1, .note-editable h2, .note-editable h3,
.note-editable h4, .note-editable h5, .note-editable h6 {
    margin-bottom: 0.75rem;
    color: #212529;
}

.note-editable p {
    margin-bottom: 1rem;
}

.note-editable img {
    max-width: 100%;
    height: auto;
    border-radius: 0.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.note-modal .modal-dialog {
    max-width: 90%;
}

/* Loading toast styling */
.toast.show {
    background: #0d6efd;
    color: white;
    border: none;
    border-radius: 0.375rem;
}

.toast-body {
    padding: 0.75rem 1rem;
}

/* Admin responsive adjustments */
@media (max-width: 768px) {
    .note-toolbar {
        padding: 0.5rem 0.25rem;
    }
    
    .note-btn-group {
        margin-bottom: 0.25rem;
        margin-right: 0.125rem;
    }
    
    .note-btn {
        padding: 0.25rem 0.375rem;
        font-size: 0.875rem;
    }
    
    .note-editable {
        padding: 1rem;
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF 토큰 설정
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    // Summernote 초기화
    $('#content').summernote({
        height: 350,
        lang: 'ko-KR',
        placeholder: '내용을 입력하세요...',
        fontNames: [
            '맑은 고딕', 'Noto Sans KR', 'Noto Serif KR', 
            'Nanum Gothic', 'Nanum Myeongjo', 'Gothic A1', 
            'IBM Plex Sans KR', 'Pretendard', 'Arial', 
            'Helvetica', 'Tahoma', 'Verdana', 'Georgia', 
            'Times New Roman', 'Courier New', 'sans-serif', 
            'serif', 'monospace'
        ],
        fontNamesIgnoreCheck: [
            '맑은 고딕', 'Noto Sans KR', 'Noto Serif KR', 
            'Nanum Gothic', 'Nanum Myeongjo', 'Gothic A1', 
            'IBM Plex Sans KR', 'Pretendard', 'Arial', 
            'Helvetica', 'Tahoma', 'Verdana', 'Georgia', 
            'Times New Roman', 'Courier New', 'sans-serif', 
            'serif', 'monospace'
        ],
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color', 'forecolor', 'backcolor']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                for (let i = 0; i < files.length; i++) {
                    uploadImage(files[i]);
                }
            },
            onDrop: function(e) {
                var dataTransfer = e.originalEvent.dataTransfer;
                if (dataTransfer && dataTransfer.files && dataTransfer.files.length) {
                    e.preventDefault();
                    for (let i = 0; i < dataTransfer.files.length; i++) {
                        uploadImage(dataTransfer.files[i]);
                    }
                }
            }
        }
    });
    
    // 이미지 업로드 함수
    function uploadImage(file) {
        // 파일 크기 체크 (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('파일 크기는 5MB를 초과할 수 없습니다.');
            return;
        }
        
        // 파일 형식 체크
        if (!file.type.match(/^image\//)) {
            alert('이미지 파일만 업로드할 수 있습니다.');
            return;
        }

        // 현재 선택된 게시판 정보 가져오기 (새로운 board_type 방식)
        const selectedBoardId = document.getElementById('board_id').value;
        const boardTypes = <?php echo json_encode($board_types); ?>;
        const selectedBoardType = selectedBoardId > 0 && boardTypes[selectedBoardId] ? boardTypes[selectedBoardId].board_type : 'general';
        
        var formData = new FormData();
        formData.append('image', file);  // 'file'에서 'image'로 수정 (upload_image.php에서 $_FILES['image'] 사용)
        formData.append('board_table', selectedBoardType);  // 게시판 타입 정보 추가
        formData.append('csrf_token', csrfToken);
        
        // 로딩 표시
        const loadingToast = $('<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999"><div class="toast show" role="alert"><div class="toast-body">이미지 업로드 중...</div></div></div>');
        $('body').append(loadingToast);
        
        $.ajax({
            url: '/admin/posts/upload_image.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                loadingToast.remove();
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data && data.success && data.url) {
                        $('#content').summernote('insertImage', data.url);
                    } else {
                        alert('이미지 업로드 실패: ' + (data.error || '알 수 없는 오류'));
                    }
                } catch (e) {
                    console.error('Response parsing error:', e);
                    alert('이미지 업로드 응답 처리 중 오류가 발생했습니다.');
                }
            },
            error: function(xhr, status, error) {
                loadingToast.remove();
                console.error('Upload error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    readyState: xhr.readyState,
                    statusCode: xhr.status
                });
                alert('Upload error: ' + error + '\n' + xhr.responseText);
            }
        });
    }
    
    // 폼 제출 전 검증
    $('form').on('submit', function(e) {
        const title = $('input[name="title"]').val().trim();
        if (!title) {
            alert('제목을 입력해주세요.');
            e.preventDefault();
            return false;
        }
        
        const author = $('input[name="author"]').val().trim();
        if (!author) {
            alert('작성자를 입력해주세요.');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>