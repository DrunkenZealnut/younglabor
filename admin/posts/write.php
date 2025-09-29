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

// 환경 변수 로드 (파일 업로드용)
require_once '../env_loader.php';

if (defined('APP_ENV') && APP_ENV === 'development') {
    error_log("attachment_helpers.php 파일을 로드하기 전");
}

// 경로 헬퍼 함수 로드 (get_bt_upload_path 함수 포함)
require_once __DIR__ . '/../../includes/path_helper.php';

// 첨부파일 헬퍼 함수 로드
require_once __DIR__ . '/attachment_helpers.php';

if (defined('APP_ENV') && APP_ENV === 'development') {
    error_log("attachment_helpers.php 파일을 로드한 후");

    // 디버그: 함수 존재 여부 확인
    if (!function_exists('get_bt_upload_path')) {
        error_log("get_bt_upload_path 함수가 정의되지 않았습니다!");
        error_log("attachment_helpers.php 파일 경로: " . __DIR__ . '/attachment_helpers.php');
        error_log("파일 존재 여부: " . (file_exists(__DIR__ . '/attachment_helpers.php') ? 'YES' : 'NO'));
    } else {
        error_log("get_bt_upload_path 함수가 정상적으로 로드되었습니다.");
    }
}

// 게시글 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (defined('APP_ENV') && APP_ENV === 'development') {
        error_log("Write.php POST 처리 시작");
    }
    // 폼 데이터 가져오기
    $board_id = (int)$_POST['board_id'];
    $title = trim($_POST['title']);
    $content = $_POST['content'] ?? '';
    $author = trim($_POST['author']);
    
    // 새로 추가된 필드들
    $password = trim($_POST['password'] ?? '');
    $options = $_POST['options'] ?? [];
    
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
    
    // 비밀번호 유효성 검사 (입력된 경우)
    if (!empty($password) && strlen($password) < 4) {
        $errors[] = "비밀번호는 4자 이상이어야 합니다.";
    }
    
    // 오류가 없으면 게시글 저장
    if (empty($errors)) {
        try {
            // 트랜잭션 시작
            $pdo->beginTransaction();
            
            // 선택된 게시판의 board_type 가져오기
            $selected_board = $boards[$board_id - 1];
            $board_type = $selected_board['board_type'];
            
            // 비밀번호 해싱 (입력된 경우)
            $hashed_password = '';
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            }
            
            // 공지사항 옵션 별도 처리
            $is_notice = in_array('notice', $options) ? 1 : 0;
            
            // 공지사항을 제외한 나머지 옵션 처리
            $option_string = '';
            if (!empty($options)) {
                $valid_options = ['html1', 'html2', 'secret', 'mail']; // notice 제외
                $filtered_options = array_intersect($options, $valid_options);
                $option_string = implode(',', $filtered_options);
            }
            
            // posts 테이블에 board_type으로 데이터 삽입
            $tableName = get_table_name('posts');
            $sql = "INSERT INTO {$tableName} (
                board_type, wr_subject, wr_content, wr_name, wr_datetime, wr_ip, 
                wr_num, wr_reply, wr_parent, wr_is_comment, wr_comment, wr_comment_reply, 
                ca_name, wr_option, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit, 
                wr_hit, wr_good, wr_nogood, mb_id, wr_password, wr_email, wr_homepage, 
                wr_file, wr_last, wr_facebook_user, wr_twitter_user, 
                wr_is_notice, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10
            ) VALUES (
                ?, ?, ?, ?, NOW(), ?, 
                0, '', 0, 0, 0, '', 
                '', ?, '', '', 0, 0, 
                0, 0, 0, '', ?, '', '', 
                0, '', '', '', 
                ?, '', '', '', '', '', '', '', '', ''
            )";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $board_type,
                $title, 
                $content, 
                $author, 
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $option_string,
                $hashed_password,
                $is_notice
            ]);
            
            if ($result) {
                $post_id = $pdo->lastInsertId();
                
                // wr_parent를 새로 생성된 게시글의 ID로 업데이트 (첨부파일 연결을 위해)
                $update_parent_sql = "UPDATE {$tableName} SET wr_parent = ? WHERE wr_id = ?";
                $update_parent_stmt = $pdo->prepare($update_parent_sql);
                $update_parent_stmt->execute([$post_id, $post_id]);
                
                // 첨부파일 처리
                $attachment_count = 0;
                if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
                    $attachment_count = processAttachments($post_id, $board_type, $_FILES['attachments'], $pdo);
                }
                
                // 첨부파일 개수 업데이트
                if ($attachment_count > 0) {
                    $update_sql = "UPDATE {$tableName} SET wr_file = ? WHERE wr_id = ?";
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute([$attachment_count, $post_id]);
                }
                
                $pdo->commit();
                $_SESSION['success_message'] = '게시글이 성공적으로 작성되었습니다.';
                header("Location: " . admin_url('posts/list.php'));
                exit;
            } else {
                $pdo->rollback();
                $errors[] = "게시글 저장에 실패했습니다.";
            }
            
        } catch (PDOException $e) {
            $pdo->rollback();
            error_log("Write.php PDO 오류: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            $errors[] = "데이터베이스 오류: " . $e->getMessage();
        } catch (Exception $e) {
            $pdo->rollback();
            error_log("Write.php Exception 오류: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            $errors[] = "파일 업로드 오류: " . $e->getMessage();
        }
    }
}

/**
 * 첨부파일 처리 함수 (개선된 .env 경로 + board_type + 날짜 기반)
 */
function processAttachments($post_id, $board_type, $files, $pdo) {
    $upload_count = 0;
    $upload_path = get_bt_upload_path();
    $allowed_types = explode(',', env('ALLOWED_DOCUMENT_TYPES', 'pdf,doc,docx,hwp,hwpx,xls,xlsx'));
    $allowed_images = explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp'));
    $max_size = (int)env('UPLOAD_MAX_SIZE', 5242880); // 5MB
    
    // board_type별 폴더 매핑
    $folder_mapping = [
        'finance_reports' => 'finance_reports',
        'notices' => 'notices', 
        'press' => 'press',
        'newsletter' => 'newsletter',
        'gallery' => 'gallery',
        'resources' => 'resources',
        'nepal_travel' => 'nepal_travel'
    ];
    
    $folder_name = $folder_mapping[$board_type] ?? $board_type;
    $upload_dir = "{$upload_path}/{$folder_name}/";
    
    // 각 파일 처리
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue; // 파일이 업로드되지 않은 경우 건너뛰기
        }
        
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // 업로드 오류가 있는 경우 건너뛰기
        }
        
        // 개별 파일 정보 구성 (validateFileUpload 함수용)
        $single_file = [
            'name' => $files['name'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'size' => $files['size'][$i],
            'type' => $files['type'][$i],
            'error' => $files['error'][$i]
        ];
        
        // 파일 보안 검증
        $validation_errors = validateFileUpload($single_file);
        if (!empty($validation_errors)) {
            error_log("파일 업로드 검증 실패: " . implode(', ', $validation_errors));
            continue; // 검증 실패한 파일은 건너뛰기
        }
        
        $original_name = $files['name'][$i];
        $tmp_name = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        // 디렉토리 생성 (파일이 실제로 업로드될 때만)
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("업로드 디렉토리 생성 실패: {$upload_dir}");
            }
        }
        
        // 안전한 파일명 생성 (타임스탬프 포함)
        $new_filename = generateSafeFilename($original_name);
        $file_path = $upload_dir . $new_filename;
        
        // 상대 경로 계산 (board_type/파일명)
        $relative_path = "{$folder_name}/{$new_filename}";
        
        // 파일 이동
        if (move_uploaded_file($tmp_name, $file_path)) {
            // 파일 정보 DB 저장
            $bf_type = in_array($ext, $allowed_images) ? 1 : 0; // 이미지면 1, 일반파일이면 0
            
            // 이미지 크기 정보
            $width = 0; $height = 0;
            if ($bf_type === 1 && function_exists('getimagesize')) {
                $image_info = @getimagesize($file_path);
                if ($image_info !== false) {
                    $width = $image_info[0];
                    $height = $image_info[1];
                }
            }
            
            $fileTableName = get_table_name('post_files');
            $file_sql = "INSERT INTO {$fileTableName} (
                wr_id, board_type, bf_source, bf_file, bf_content, bf_filesize, 
                bf_width, bf_height, bf_type, bf_download, bf_datetime
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $file_stmt = $pdo->prepare($file_sql);
            $file_result = $file_stmt->execute([
                $post_id, $board_type, $original_name, $new_filename, 
                '', $file_size, $width, $height, $bf_type, 0
            ]);
            
            if ($file_result) {
                $upload_count++;
            }
        }
    }
    
    return $upload_count;
}

// BASE_PATH 환경 변수 가져오기 (bootstrap.php에서 제공하는 함수 사용)
$base_path = get_base_path();

// 페이지 제목 설정
$page_title = '새 게시글 작성';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars($admin_title) ?></title>
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
    <a href="<?= admin_url('index.php') ?>" class="text-white text-decoration-none"><?= htmlspecialchars($admin_title) ?></a>
  </div>
  <a href="<?= admin_url('index.php') ?>">📊 대시보드</a>
  <a href="<?= admin_url('posts/list.php') ?>" class="active">📝 게시글 관리</a>
  <a href="<?= admin_url('boards/list.php') ?>">📋 게시판 관리</a>
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
                <li class="breadcrumb-item"><a href="<?= admin_url('index.php') ?>">관리자</a></li>
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
            <form method="POST" action="" enctype="multipart/form-data">
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

                <!-- 게시글 옵션 섹션 -->
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-gear"></i> 게시글 옵션
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">비밀번호 보호</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="게시글을 보호할 비밀번호 (선택사항)">
                                    <small class="text-muted">입력시 비밀번호가 필요한 보호글이 됩니다.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">게시글 설정</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="option_html1" name="options[]" value="html1" checked>
                                        <label class="form-check-label" for="option_html1">
                                            <i class="bi bi-code-slash"></i> HTML 사용
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="option_secret" name="options[]" value="secret"
                                               <?= (isset($_POST['options']) && in_array('secret', $_POST['options'])) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="option_secret">
                                            <i class="bi bi-lock-fill"></i> 비밀글
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="option_mail" name="options[]" value="mail"
                                               <?= (isset($_POST['options']) && in_array('mail', $_POST['options'])) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="option_mail">
                                            <i class="bi bi-envelope"></i> 메일 수신
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="option_notice" name="options[]" value="notice"
                                               <?= (isset($_POST['options']) && in_array('notice', $_POST['options'])) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="option_notice">
                                            <i class="bi bi-megaphone-fill text-warning"></i> 공지사항
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 첨부파일 섹션 -->
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-paperclip"></i> 첨부파일
                                <small class="text-muted">(최대 5개, 각 5MB 이하)</small>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="file-upload-container">
                                <div class="file-upload-item mb-2">
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="attachments[]" accept=".pdf,.doc,.docx,.hwp,.hwpx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp">
                                        <button type="button" class="btn btn-outline-success" onclick="addFileUpload()" title="파일 추가">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                허용 형식: PDF, Word(doc, docx), 한글(hwp, hwpx), Excel(xls, xlsx), 이미지(jpg, png, gif, webp)
                            </small>
                        </div>
                    </div>
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
    // 기본 설정
    const basePath = '<?= get_base_path() ?>';
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
            url: '<?= admin_url('posts/upload_image.php') ?>',
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
        
        // 비밀번호 길이 검증 (입력된 경우)
        const password = $('input[name="password"]').val();
        if (password && password.length < 4) {
            alert('비밀번호는 4자 이상이어야 합니다.');
            e.preventDefault();
            return false;
        }
        
        // 파일 크기 검증
        const maxSize = 5 * 1024 * 1024; // 5MB
        const fileInputs = document.querySelectorAll('input[type="file"][name="attachments[]"]');
        for (const input of fileInputs) {
            if (input.files.length > 0) {
                const file = input.files[0];
                if (file.size > maxSize) {
                    alert(`파일 "${file.name}"의 크기가 5MB를 초과합니다.`);
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
});

// 파일 업로드 필드 추가/제거 함수
function addFileUpload() {
    const container = document.getElementById('file-upload-container');
    const items = container.querySelectorAll('.file-upload-item');
    
    if (items.length >= 5) {
        alert('최대 5개의 파일만 업로드할 수 있습니다.');
        return;
    }
    
    const newItem = document.createElement('div');
    newItem.className = 'file-upload-item mb-2';
    newItem.innerHTML = `
        <div class="input-group">
            <input type="file" class="form-control" name="attachments[]" accept=".pdf,.doc,.docx,.hwp,.hwpx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp">
            <button type="button" class="btn btn-outline-danger" onclick="removeFileUpload(this)" title="파일 제거">
                <i class="bi bi-dash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newItem);
    
    // 첫 번째 항목의 + 버튼을 - 버튼으로 변경
    const firstItem = container.querySelector('.file-upload-item:first-child');
    const firstButton = firstItem.querySelector('.btn-outline-success');
    if (firstButton && items.length === 1) {
        firstButton.className = 'btn btn-outline-danger';
        firstButton.setAttribute('onclick', 'removeFileUpload(this)');
        firstButton.setAttribute('title', '파일 제거');
        firstButton.innerHTML = '<i class="bi bi-dash"></i>';
    }
}

function removeFileUpload(button) {
    const container = document.getElementById('file-upload-container');
    const items = container.querySelectorAll('.file-upload-item');
    
    if (items.length <= 1) {
        // 최소 1개는 유지하되, 파일 선택을 초기화
        const input = button.closest('.file-upload-item').querySelector('input[type="file"]');
        input.value = '';
        return;
    }
    
    // 현재 항목 제거
    const item = button.closest('.file-upload-item');
    item.remove();
    
    // 항목이 하나만 남은 경우 + 버튼으로 변경
    const remainingItems = container.querySelectorAll('.file-upload-item');
    if (remainingItems.length === 1) {
        const lastButton = remainingItems[0].querySelector('.btn');
        lastButton.className = 'btn btn-outline-success';
        lastButton.setAttribute('onclick', 'addFileUpload()');
        lastButton.setAttribute('title', '파일 추가');
        lastButton.innerHTML = '<i class="bi bi-plus"></i>';
    }
}

// 파일 선택 시 크기 검증
document.addEventListener('change', function(e) {
    if (e.target.matches('input[type="file"][name="attachments[]"]')) {
        const file = e.target.files[0];
        if (file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert(`파일 "${file.name}"의 크기가 5MB를 초과합니다. 다른 파일을 선택해주세요.`);
                e.target.value = '';
                return;
            }
            
            // 파일 형식 검증
            const allowedTypes = ['pdf', 'doc', 'docx', 'hwp', 'hwpx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
            const ext = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(ext)) {
                alert(`허용되지 않은 파일 형식입니다. (${ext})`);
                e.target.value = '';
                return;
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>