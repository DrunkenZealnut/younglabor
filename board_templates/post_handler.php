<?php
/**
 * 게시글 등록/수정 처리기 - younglabor_posts 통합 테이블 호환
 * 자유게시판과 자료실 모두 지원
 */

// younglabor_posts 호환성 레이어 먼저 로드
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database_helper.php';

// 서버 설정 로드 후 세션 시작 (보안 쿠키 옵션 반영)
require_once '../config/server_setup.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../config/helpers.php';
require_once '../includes/board_module.php';

/**
 * 환경에 맞는 데이터베이스 연결 반환 (config/database.php 사용)
 */
function getDatabaseConnection() {
    try {
        // config/database.php에서 정의된 상수 사용
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("데이터베이스 연결 실패: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
        
    } catch (Exception $e) {
        throw new Exception("데이터베이스 연결 실패: " . $e->getMessage());
    }
}

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

    // 호환성 데이터베이스 연결 사용
    $conn = getBoardDatabase();
    if (!$conn) {
        throw new Exception('데이터베이스 연결을 사용할 수 없습니다.');
    }
    
    // 기본 변수 설정
    $category_type = $_POST['category_type'] ?? 'FREE';
    $board_type = getBoardType($category_type); // younglabor_posts용 board_type
    
    // 리다이렉트 경로(폼에서 전달 가능)
    $redirect_detail_url = isset($_POST['redirect_detail_url']) ? trim((string)$_POST['redirect_detail_url']) : '';
    $redirect_list_url = isset($_POST['redirect_list_url']) ? trim((string)$_POST['redirect_list_url']) : '';
    $write_url = isset($_POST['write_url']) ? trim((string)$_POST['write_url']) : '';
    $edit_url = isset($_POST['edit_url']) ? trim((string)$_POST['edit_url']) : '';
    $action = isset($_POST['post_id']) && !empty($_POST['post_id']) ? 'update' : 'create';
    $post_id = $_POST['post_id'] ?? null;

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

    // 카테고리/게시판 타입 확인 (younglabor_posts 호환)
    if (USE_younglabor_POSTS) {
        // younglabor_board_config에서 게시판 타입 확인
        $category_stmt = $conn->prepare("SELECT board_type FROM younglabor_board_config WHERE board_type = ?");
        $category_stmt->execute([$board_type]);
        $category_row = $category_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category_row) {
            // 게시판 설정이 없으면 생성
            if ($category_type === 'FREE') {
                $board_name = '자유게시판';
            } elseif ($category_type === 'LIBRARY') {
                $board_name = '자료실';
            } else {
                $board_name = $category_type . ' 게시판';
            }
            
            $create_stmt = $conn->prepare("INSERT INTO younglabor_board_config (board_type, board_name, is_active) VALUES (?, ?, 1)");
            $create_stmt->execute([$board_type, $board_name]);
        }
        $category_id = getyounglaborAdapter()->getBoardTypeId($board_type); // 임시 ID
    } else {
        // 기존 board_categories 방식
        $category_stmt = $conn->prepare("SELECT category_id FROM board_categories WHERE category_type = ?");
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
            $create_stmt = $conn->prepare("INSERT INTO board_categories (category_name, category_type, is_active) VALUES (?, ?, 1)");
            $create_stmt->execute([$category_name, $category_type]);
            $category_id = (int)$conn->lastInsertId();
        } else {
            $category_id = (int)$category_row['category_id'];
        }
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
        // 게시글 수정 (호환성 레이어 사용)

        // 권한 확인 (작성자 또는 관리자만)
        $existing_post = getBoardPost((int)$post_id);
        if (!$existing_post) {
            throw new Exception('존재하지 않는 게시글입니다.');
        }
        
        $current_user_role = $_SESSION['role'] ?? 'USER';
        
        if ($existing_post['user_id'] != $current_user_id && 
            $existing_post['author_name'] !== $author_name && 
            $current_user_role !== 'ADMIN') {
            throw new Exception('수정 권한이 없습니다.');
        }

        // 게시글 업데이트 (호환성 레이어 사용)
        $updateData = [
            'title' => $title,
            'content' => $content,
            'is_notice' => (int)$is_notice
        ];
        
        $success = updateBoardPost((int)$post_id, $updateData);

        // 기존 첨부파일 삭제 처리 (자료실만)
        if ($category_type === 'LIBRARY' && isset($_POST['delete_attachments'])) {
            $delete_attachments = $_POST['delete_attachments'];
            foreach ($delete_attachments as $attachment_id) {
                // 파일 정보 조회
                $file_stmt = $conn->prepare("SELECT stored_name FROM board_attachments WHERE attachment_id = ? AND post_id = ?");
                $file_stmt->execute([(int)$attachment_id, (int)$post_id]);
                $file_row = $file_stmt->fetch(PDO::FETCH_ASSOC);
                if ($file_row) {
                    // 실제 파일 삭제
                    $file_path = '../uploads/board_documents/' . $file_row['stored_name'];
                    if (file_exists($file_path)) {
                        @unlink($file_path);
                    }
                    
                    // DB에서 첨부파일 삭제
                    $delete_stmt = $conn->prepare("DELETE FROM board_attachments WHERE attachment_id = ?");
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
        // 새 게시글 작성 (호환성 레이어 사용)
        
        $postData = [
            'user_id' => (int)$current_user_id,
            'title' => $title,
            'content' => $content,
            'author_name' => $author_name,
            'is_notice' => (int)$is_notice
        ];
        
        $success = createBoardPost($category_type, $postData);
        if (!$success) {
            throw new Exception('게시글 등록에 실패했습니다.');
        }
        
        // 새로 생성된 게시글 ID 가져오기 (younglabor_posts 호환)
        if (USE_younglabor_POSTS) {
            $final_post_id = (int)$conn->lastInsertId();
        } else {
            $final_post_id = (int)$conn->lastInsertId();
        }
        
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
                $attachment_stmt = $conn->prepare("INSERT INTO board_attachments (post_id, original_name, stored_name, file_path, file_size, file_type, mime_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
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