<?php
// 게시글 수정 페이지
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../bootstrap.php';

// CSRF 토큰 생성 보장
if (!isset($_SESSION['csrf_token'])) {
    generateCSRFToken();
}

// 게시글 ID와 board_type 확인
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$board_type = isset($_GET['board_type']) ? $_GET['board_type'] : '';

if ($post_id <= 0 || empty($board_type)) {
    header("Location: list.php");
    exit;
}

// board_type 매핑 (write.php와 동일)
$board_types = [
    'finance_reports' => '재정보고',
    'notices' => '공지사항', 
    'press' => '언론보도',
    'newsletter' => '소식지',
    'gallery' => '갤러리',
    'resources' => '자료실',
    'nepal_travel' => '네팔나눔연대여행'
];

if (!array_key_exists($board_type, $board_types)) {
    header("Location: list.php");
    exit;
}

// 폼 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $author = trim($_POST['author'] ?? '');
    
    if (!empty($title)) {
        try {
            $sql = "UPDATE hopec_posts SET 
                    wr_subject = ?, wr_content = ?, wr_name = ?
                    WHERE wr_id = ? AND board_type = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $author, $post_id, $board_type]);
            
            $_SESSION['success_message'] = '게시글이 성공적으로 수정되었습니다.';
            header("Location: view.php?id=" . $post_id . "&board_type=" . urlencode($board_type));
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['error_message'] = '게시글 수정 중 오류가 발생했습니다: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = '제목을 입력해주세요.';
    }
}

try {
    // 게시글 정보 조회 (hopec_posts 테이블에서 board_type으로)
    $sql = "SELECT 
                wr_id as id,
                wr_subject as title,
                wr_content as content,
                wr_name as author,
                wr_hit as hit_count,
                wr_datetime as created_at,
                wr_ip as ip_address
            FROM hopec_posts 
            WHERE wr_id = ? AND board_type = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id, $board_type]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header("Location: list.php?error=not_found");
        exit;
    }
    
    // 게시판 이름 설정
    $board_name = $board_types[$board_type];
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = '게시글을 불러올 수 없습니다: ' . $e->getMessage();
    header("Location: list.php");
    exit;
}

// 페이지 제목 설정
$page_title = $post ? '게시글 수정: ' . htmlspecialchars($post['title']) : '게시글을 찾을 수 없습니다';
?>

<!DOCTYPE html>
<html lang="ko">
<head>희망씨
  <meta charset="UTF-8">
  <title><?= $page_title ?> - 우동615 관리자</title>
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
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">희망씨
  <div class="logo">
    <a href="/admin/index.php" class="text-white text-decoration-none">우동615 관리자</a>
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
    <?php 
    // 간단한 flash message 처리
    if (isset($_SESSION['success_message'])): 
    ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php 
        unset($_SESSION['success_message']);
    endif; 
    
    if (isset($_SESSION['error_message'])): 
    ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php 
        unset($_SESSION['error_message']);
    endif; 
    ?>

    <?php if (!$post): ?>
        <div class="alert alert-danger">
            <h4>게시글을 찾을 수 없습니다</h4>
            <p>요청하신 게시글이 존재하지 않거나 삭제되었습니다.</p>
            <a href="list.php" class="btn btn-primary">목록으로 돌아가기</a>
        </div>
    <?php else: ?>
        <!-- 상단 네비게이션 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/index.php">관리자</a></li>
                    <li class="breadcrumb-item"><a href="list.php">게시글 관리</a></li>
                    <li class="breadcrumb-item active">게시글 수정</li>
                </ol>
            </nav>
            
            <div class="btn-group">
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-list"></i> 목록
                </a>
                <a href="view.php?id=<?= $post['id'] ?>&board_type=<?= urlencode($board_type) ?>" class="btn btn-outline-primary">
                    <i class="bi bi-eye"></i> 보기
                </a>
            </div>
        </div>

        <!-- 게시글 수정 폼 -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title mb-0">
                    <i class="bi bi-pencil"></i> 게시글 수정
                </h2>
                <small class="text-muted">
                    게시판: <?= htmlspecialchars($board_name) ?> | 
                    작성일: <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?> | 
                    조회수: <?= number_format($post['hit_count'] ?? 0) ?>
                </small>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($post['title']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="author" class="form-label">작성자</label>
                            <input type="text" class="form-control" id="author" name="author" 
                                   value="<?= htmlspecialchars($post['author'] ?? '관리자') ?>">
                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="content" class="form-label">내용</label>
                        <textarea class="form-control" id="content" name="content" rows="15"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 취소
                            </a>
                            <a href="view.php?id=<?= $post['id'] ?>&board_type=<?= urlencode($board_type) ?>" class="btn btn-outline-info">
                                <i class="bi bi-eye"></i> 미리보기
                            </a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> 수정 완료
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 게시글 정보 -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> 게시글 정보
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>게시글 ID:</strong> <?= $post['id'] ?>
                    </div>
                    <div class="col-md-4">
                        <strong>작성일:</strong> <?= date('Y-m-d H:i:s', strtotime($post['created_at'])) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>조회수:</strong> <?= number_format($post['hit_count'] ?? 0) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>게시판:</strong> <?= htmlspecialchars($board_name) ?>
                    </div>
                </div>
                <?php if (!empty($post['ip_address'])): ?>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>작성 IP:</strong> <?= htmlspecialchars($post['ip_address']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
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
    // CSRF 토큰 설정 (PHP session에서 가져오기)
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

        // 현재 테이블 정보 가져오기 (URL에서 추출)
        const urlParams = new URLSearchParams(window.location.search);
        const currentTable = urlParams.get('table') || 'general';
        
        var formData = new FormData();
        formData.append('image', file);  // 'file'에서 'image'로 수정
        formData.append('board_table', currentTable);  // 게시판 테이블 정보 추가
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
