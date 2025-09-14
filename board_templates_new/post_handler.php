<?php
/**
 * 게시글 등록/수정 처리기
 * 자유게시판과 자료실 모두 지원
 */

// 의존성 주입 시스템 로드
require_once __DIR__ . '/config.php';

// 세션 시작 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 서비스 컨테이너에서 필요한 의존성 가져오기
$container = $GLOBALS['board_service_container'];
$repository = $container->get('repository');
$configProvider = $container->get('config');
$authConfig = $configProvider->getAuthConfig();
$fileConfig = $configProvider->getFileConfig();

require_once 'captcha_helper.php';

// 한글 인코딩 설정 (헤더가 전송되지 않았을 때만)
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

try {
    // 메서드 및 CSRF 검증
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('잘못된 요청 방법입니다.');
    }
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('유효하지 않은 요청입니다. (CSRF)');
    }

    // PDO 사용 (config/database.php 전역 $pdo)
    if (!isset($pdo)) {
        throw new Exception('데이터베이스 연결을 사용할 수 없습니다.');
    }
    $conn = $pdo; // 하위 호환 변수명 유지
    
    // 기본 변수 설정
    $category_type = $_POST['category_type'] ?? 'FREE';
    $board_id = $_POST['board_id'] ?? null;
    $action = isset($_POST['post_id']) && !empty($_POST['post_id']) ? 'update' : 'create';
    $post_id = $_POST['post_id'] ?? null;
    
    // 캡차 검증 (새 글 작성 시만)
    if ($action === 'create' && is_captcha_required($board_id, $category_type)) {
        $captcha_code = $_POST['captcha_code'] ?? '';
        if (empty($captcha_code)) {
            throw new Exception(get_captcha_message('error_empty'));
        }
        if (!verify_captcha($captcha_code)) {
            throw new Exception(get_captcha_message('error_invalid'));
        }
    }
    
    // 리다이렉트 경로(폼에서 전달 가능)
    $redirect_detail_url = isset($_POST['redirect_detail_url']) ? trim((string)$_POST['redirect_detail_url']) : '';
    $redirect_list_url = isset($_POST['redirect_list_url']) ? trim((string)$_POST['redirect_list_url']) : '';
    $write_url = isset($_POST['write_url']) ? trim((string)$_POST['write_url']) : '';
    $edit_url = isset($_POST['edit_url']) ? trim((string)$_POST['edit_url']) : '';

    // 로그인 확인
    $current_user_id = null;
    if (isset($_SESSION['user_id'])) {
        $current_user_id = $_SESSION['user_id'];
    } elseif (isset($_SESSION['id'])) {
        $current_user_id = $_SESSION['id'];
    }

    if (!$current_user_id) {
        throw new Exception('로그인이 필요합니다.');
    }

    // 카테고리 확인 및 생성 (PDO)
    $category_stmt = $conn->prepare("SELECT category_id FROM atti_board_categories WHERE category_type = ?");
    $category_stmt->execute([$category_type]);
    $category_row = $category_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category_row) {
        // 카테고리가 없으면 생성 (is_active = 1로 설정)
        if ($category_type === 'FREE') {
            $category_name = '자유게시판';
        } elseif ($category_type === 'LIBRARY') {
            $category_name = '자료실';
        } else {
            $category_name = $category_type . ' 게시판';
        }
        $create_stmt = $conn->prepare("INSERT INTO atti_board_categories (category_name, category_type, is_active) VALUES (?, ?, 1)");
        $create_stmt->execute([$category_name, $category_type]);
        $category_id = (int)$conn->lastInsertId();
    } else {
        $category_id = (int)$category_row['category_id'];
    }

    // 입력 데이터 검증 및 정리
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author_name = trim($_SESSION['username'] ?? $_SESSION['user']['username'] ?? '');
    $is_notice = isset($_POST['is_notice']) ? 1 : 0;

    if (empty($title)) {
        throw new Exception('제목을 입력해주세요.');
    }
    // 내용 검증 제거 - 내용 없이도 등록 가능
    // if (empty($content)) {
    //     throw new Exception('내용을 입력해주세요.');
    // }
    if (empty($author_name)) {
        throw new Exception('작성자 정보를 확인할 수 없습니다.');
    }

    if ($action === 'update' && $post_id) {
        // 게시글 수정

        // 권한 확인 (작성자 또는 관리자만)
        $check_stmt = $conn->prepare("SELECT user_id, author_name FROM atti_board_posts WHERE post_id = ? AND is_active = 1");
        $check_stmt->execute([(int)$post_id]);
        $existing_post = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing_post) {
            throw new Exception('존재하지 않는 게시글입니다.');
        }
        $current_user_role = $_SESSION['role'] ?? 'USER';
        
        if ($existing_post['user_id'] != $current_user_id && 
            $existing_post['author_name'] !== $author_name && 
            $current_user_role !== 'ADMIN') {
            throw new Exception('수정 권한이 없습니다.');
        }

        // 게시글 업데이트
        $update_stmt = $conn->prepare("UPDATE atti_board_posts SET title = ?, content = ?, is_notice = ?, updated_at = NOW() WHERE post_id = ?");
        $update_stmt->execute([$title, $content, (int)$is_notice, (int)$post_id]);

        // 기존 첨부파일 삭제 처리 (자료실만)
        if ($category_type === 'LIBRARY' && isset($_POST['delete_attachments'])) {
            $delete_attachments = $_POST['delete_attachments'];
            foreach ($delete_attachments as $attachment_id) {
                // 파일 정보 조회
                $file_stmt = $conn->prepare("SELECT stored_name FROM atti_board_attachments WHERE attachment_id = ? AND post_id = ?");
                $file_stmt->execute([(int)$attachment_id, (int)$post_id]);
                $file_row = $file_stmt->fetch(PDO::FETCH_ASSOC);
                if ($file_row) {
                    // 실제 파일 삭제
                    $file_path = '../uploads/board_documents/' . $file_row['stored_name'];
                    if (file_exists($file_path)) {
                        @unlink($file_path);
                    }
                    
                    // DB에서 첨부파일 삭제
                    $delete_stmt = $conn->prepare("DELETE FROM atti_board_attachments WHERE attachment_id = ?");
                    $delete_stmt->execute([(int)$attachment_id]);
                }
            }
        }

        $success_message = '게시글이 수정되었습니다.';
        if ($redirect_detail_url) {
            $redirect_page = $redirect_detail_url;
        } else {
            $redirect_page = '../boards/' . ($category_type === 'FREE' ? 'free_board_detail.php' : 'library_detail.php');
        }
        $final_post_id = $post_id;

    } else {
        // 새 게시글 작성
        
        $insert_stmt = $conn->prepare("INSERT INTO atti_board_posts (category_id, user_id, title, content, author_name, is_notice, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $insert_stmt->execute([(int)$category_id, (int)$current_user_id, $title, $content, $author_name, (int)$is_notice]);
        $final_post_id = (int)$conn->lastInsertId();
        $success_message = '게시글이 등록되었습니다.';
        if ($redirect_detail_url) {
            $redirect_page = $redirect_detail_url;
        } else {
            $redirect_page = '../boards/' . ($category_type === 'FREE' ? 'free_board_detail.php' : 'library_detail.php');
        }
    }

    // 첨부파일 처리 (자료실만)
    if ($category_type === 'LIBRARY' && isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        $files = $_FILES['attachments'];
        
        // 허용된 파일 확장자
        $allowed_extensions = ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
        $allowed_mimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
            'application/x-rar-compressed',
            'text/plain',
            // hwp/hwpx는 서버에 따라 별도 MIME이 다를 수 있어 확장자 + 기본 검증 조합
        ];
        
        // 업로드 디렉토리 설정
        $upload_dir = '../uploads/board_documents/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if (empty($files['name'][$i])) continue;
            
            $filename = $files['name'][$i];
            $tmp_name = $files['tmp_name'][$i];
            $file_size = $files['size'][$i];
            // finfo로 MIME 검증
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected_mime = $finfo->file($tmp_name);
            
            // 파일 확장자 검증
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_extensions)) {
                throw new Exception($filename . '은(는) 지원하지 않는 파일 형식입니다.');
            }
            // MIME 타입 검증 (일부 형식은 변형 MIME 허용 고려)
            if ($detected_mime && !in_array($detected_mime, $allowed_mimes, true)) {
                // hwp/hwpx 임시 허용: 확장자만 통과시키되 추가 검사 필요할 수 있음
                if (!in_array($file_ext, ['hwp', 'hwpx'], true)) {
                    throw new Exception($filename . '은(는) 허용되지 않는 파일 유형입니다.');
                }
            }
            
            // 파일 크기 제한 (10MB)
            if ($file_size > 10 * 1024 * 1024) {
                throw new Exception($filename . '은(는) 파일 크기가 10MB를 초과합니다.');
            }
            
            // 안전한 파일명 생성
            $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($filename, PATHINFO_FILENAME));
            $stored_name = 'library_' . $final_post_id . '_' . time() . '_' . $i . '_' . $safe_filename . '.' . $file_ext;
            $full_path = $upload_dir . $stored_name;
            
            if (move_uploaded_file($tmp_name, $full_path)) {
                @chmod($full_path, 0644);
                
                // 파일 타입 결정 (IMAGE 또는 DOCUMENT)
                $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $document_file_type = in_array($file_ext, $image_extensions) ? 'IMAGE' : 'DOCUMENT';
                
                // DB에 첨부파일 정보 저장
                $attachment_stmt = $conn->prepare("INSERT INTO atti_board_attachments (post_id, original_name, stored_name, file_path, file_size, file_type, mime_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $file_path = 'board_documents/' . $stored_name;
                $attachment_stmt->execute([(int)$final_post_id, $filename, $stored_name, $file_path, (int)$file_size, $document_file_type, $detected_mime ?: 'application/octet-stream']);
            }
        }
    }

    // mysqli 닫기 없음(Pdo 사용)

    // 성공 시 리다이렉트
    $_SESSION['success_message'] = $success_message;
    
    if (!headers_sent()) {
        header("Location: {$redirect_page}?id={$final_post_id}");
        exit;
    } else {
        // 헤더가 이미 전송된 경우 JavaScript로 리다이렉트
        echo "<script>
            alert('{$success_message}');
            location.href = '{$redirect_page}?id={$final_post_id}';
        </script>";
        exit;
    }

} catch (Exception $e) {
    // 오류 시 적절한 페이지로 돌아가기
    $_SESSION['error_message'] = $e->getMessage();
    
    if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
        // 수정 중 오류 - 수정 페이지로 돌아가기
        if ($edit_url) {
            $redirect_url = $edit_url;
            $redirect_param = (strpos($edit_url, '?') === false ? '?' : '&') . 'id=' . urlencode((string)$_POST['post_id']);
        } else {
            $redirect_url = '../boards/' . ($category_type === 'FREE' ? 'free_board_edit.php' : 'library_edit.php');
            $redirect_param = '?id=' . $_POST['post_id'];
        }
    } else {
        // 새 글 작성 중 오류 - 작성 페이지로 돌아가기
        if ($write_url) {
            $redirect_url = $write_url;
            $redirect_param = '';
        } else {
            $redirect_url = '../boards/' . ($category_type === 'FREE' ? 'free_board_write.php' : 'library_write.php');
            $redirect_param = '';
        }
    }
    
    if (!headers_sent()) {
        header("Location: {$redirect_url}{$redirect_param}");
        exit;
    } else {
        // 헤더가 이미 전송된 경우 JavaScript로 리다이렉트
        $error_message = htmlspecialchars($e->getMessage());
        echo "<script>
            alert('오류: {$error_message}');
            location.href = '{$redirect_url}{$redirect_param}';
        </script>";
        exit;
    }
}

?> 