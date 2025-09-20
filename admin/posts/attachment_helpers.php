<?php
/**
 * 첨부파일 관련 헬퍼 함수들
 */

/**
 * 게시글의 첨부파일 목록 조회
 */
function getPostAttachments($post_id, $pdo, $board_type = null) {
    try {
        if ($board_type) {
            // Frontend와 동일한 방식: pf.wr_id = p.wr_parent 조인 사용
            $stmt = $pdo->prepare("
                SELECT 
                    pf.bf_no,
                    pf.bf_source,
                    pf.bf_file,
                    pf.bf_filesize,
                    pf.bf_download,
                    pf.bf_type,
                    pf.bf_width,
                    pf.bf_height,
                    pf.bf_datetime,
                    pf.board_type as file_board_type,
                    p.board_type as post_board_type
                FROM hopec_post_files pf
                INNER JOIN hopec_posts p ON pf.wr_id = p.wr_parent
                WHERE p.board_type = ? 
                  AND pf.board_type = ? 
                  AND p.wr_id = ?
                ORDER BY pf.bf_no ASC
            ");
            $stmt->execute([$board_type, $board_type, $post_id]);
        } else {
            // 기존 호환성을 위한 fallback (직접 매칭)
            $stmt = $pdo->prepare("
                SELECT bf_no, bf_source, bf_file, bf_filesize, bf_download, 
                       bf_type, bf_width, bf_height, bf_datetime, board_type
                FROM hopec_post_files 
                WHERE wr_id = ? 
                ORDER BY bf_no ASC
            ");
            $stmt->execute([$post_id]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("첨부파일 조회 오류: " . $e->getMessage());
        return [];
    }
}

/**
 * 파일 크기를 읽기 쉬운 형태로 변환
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

/**
 * 파일 확장자에 따른 아이콘 반환
 */
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $icons = [
        'pdf' => 'bi-file-earmark-pdf text-danger',
        'doc' => 'bi-file-earmark-word text-primary',
        'docx' => 'bi-file-earmark-word text-primary',
        'hwp' => 'bi-file-earmark-text text-info',
        'hwpx' => 'bi-file-earmark-text text-info',
        'xls' => 'bi-file-earmark-excel text-success',
        'xlsx' => 'bi-file-earmark-excel text-success',
        'jpg' => 'bi-file-earmark-image text-warning',
        'jpeg' => 'bi-file-earmark-image text-warning',
        'png' => 'bi-file-earmark-image text-warning',
        'gif' => 'bi-file-earmark-image text-warning',
        'webp' => 'bi-file-earmark-image text-warning'
    ];
    
    return $icons[$ext] ?? 'bi-file-earmark text-secondary';
}

/**
 * 첨부파일 목록을 HTML로 렌더링
 */
function renderAttachmentList($attachments, $show_download = true) {
    if (empty($attachments)) {
        return '<p class="text-muted">첨부파일이 없습니다.</p>';
    }
    
    $html = '<div class="attachment-list">';
    $html .= '<h5><i class="bi bi-paperclip"></i> 첨부파일 (' . count($attachments) . '개)</h5>';
    $html .= '<div class="list-group list-group-flush">';
    
    foreach ($attachments as $file) {
        $icon_class = getFileIcon($file['bf_source']);
        $file_size = formatFileSize($file['bf_filesize']);
        $download_count = $file['bf_download'];
        
        $html .= '<div class="list-group-item d-flex justify-content-between align-items-center">';
        $html .= '<div class="d-flex align-items-center">';
        $html .= '<i class="' . $icon_class . ' me-2 fs-4"></i>';
        $html .= '<div>';
        $html .= '<div class="fw-medium">' . htmlspecialchars($file['bf_source']) . '</div>';
        $html .= '<small class="text-muted">' . $file_size . ' • 다운로드 ' . $download_count . '회</small>';
        $html .= '</div></div>';
        
        if ($show_download) {
            $html .= '<a href="download_attachment.php?id=' . $file['bf_no'] . '" class="btn btn-outline-primary btn-sm">';
            $html .= '<i class="bi bi-download"></i> 다운로드</a>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div></div>';
    return $html;
}

/**
 * 파일 보안 검증 함수
 */
function validateFileUpload($file) {
    $errors = [];
    
    // 파일 크기 검증
    $max_size = (int)env('UPLOAD_MAX_SIZE', 5242880); // 5MB
    if ($file['size'] > $max_size) {
        $errors[] = '파일 크기가 ' . formatFileSize($max_size) . '를 초과합니다.';
    }
    
    // 파일 확장자 검증
    $allowed_types = explode(',', env('ALLOWED_DOCUMENT_TYPES', 'pdf,doc,docx,hwp,hwpx,xls,xlsx'));
    $allowed_images = explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp'));
    $all_allowed = array_merge($allowed_types, $allowed_images);
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $all_allowed)) {
        $errors[] = '허용되지 않은 파일 형식입니다: ' . $ext;
    }
    
    // 파일명 검증 (위험한 문자 제거)
    if (preg_match('/[<>:"|?*]/', $file['name'])) {
        $errors[] = '파일명에 허용되지 않은 문자가 포함되어 있습니다.';
    }
    
    // MIME 타입 검증 (실제 파일 내용과 확장자 일치 확인)
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $expected_mimes = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp']
        ];
        
        if (isset($expected_mimes[$ext])) {
            if (!in_array($detected_mime, $expected_mimes[$ext])) {
                $errors[] = '파일 내용과 확장자가 일치하지 않습니다.';
            }
        }
    }
    
    // 바이러스 스캔 (향후 확장 가능)
    // TODO: ClamAV 또는 기타 바이러스 스캐너 연동
    
    return $errors;
}

/**
 * 안전한 파일명 생성
 */
function generateSafeFilename($original_name) {
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $date_str = date('Ymd_His');
    $unique_id = substr(md5(uniqid(rand(), true)), 0, 8);
    
    return "{$date_str}_{$unique_id}.{$ext}";
}

/**
 * 첨부파일 삭제
 */
function deleteAttachment($file_id, $pdo) {
    try {
        // 파일 정보 조회 (board_type도 함께 가져오기)
        $stmt = $pdo->prepare("SELECT bf_file, board_type FROM hopec_post_files WHERE bf_no = ?");
        $stmt->execute([$file_id]);
        $file_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($file_info) {
            // 물리적 파일 삭제
            $upload_path = rtrim(env('BT_UPLOAD_PATH', '/Users/zealnutkim/Documents/개발/hopec/data/file'), '/');
            $filename = $file_info['bf_file'];
            $board_type = $file_info['board_type'];
            
            // board_type에 따른 폴더명 매핑
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
            
            // 새로운 구조 확인: bf_file에 경로가 포함되어 있는지 확인
            if (strpos($filename, '/') !== false) {
                // 새로운 구조: board_type/날짜/파일명이 bf_file에 저장됨
                $file_path = $upload_path . '/' . $filename;
            } else {
                // 기존 구조: board_type/파일명
                $file_path = $upload_path . '/' . $folder_name . '/' . $filename;
            }
            
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // DB 레코드 삭제
            $delete_stmt = $pdo->prepare("DELETE FROM hopec_post_files WHERE bf_no = ?");
            return $delete_stmt->execute([$file_id]);
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("첨부파일 삭제 오류: " . $e->getMessage());
        return false;
    }
}
?>