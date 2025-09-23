<?php
/**
 * 첨부파일 다운로드 핸들러
 * 보안 강화된 파일 다운로드 시스템
 */
require_once '../bootstrap.php';
require_once '../env_loader.php';

// 파일 ID 검증
$file_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$file_id) {
    http_response_code(404);
    die('파일을 찾을 수 없습니다.');
}

try {
    // 파일 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM hopec_post_files WHERE bf_no = ?");
    $stmt->execute([$file_id]);
    $file_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file_info) {
        http_response_code(404);
        die('파일을 찾을 수 없습니다.');
    }
    
    // 파일 경로 구성 (기존 + 새로운 날짜 기반 구조 모두 지원)
    $upload_path = get_bt_upload_path();
    
    $board_type = $file_info['board_type'];
    $filename = $file_info['bf_file'];
    
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
    
    // 파일 존재 확인
    if (!file_exists($file_path) || !is_readable($file_path)) {
        http_response_code(404);
        die('파일에 접근할 수 없습니다.');
    }
    
    // 보안 검증: 업로드 경로 밖의 파일 접근 방지
    $real_upload_path = realpath($upload_path);
    $real_file_path = realpath($file_path);
    
    if (strpos($real_file_path, $real_upload_path) !== 0) {
        http_response_code(403);
        die('접근이 거부되었습니다.');
    }
    
    // 다운로드 수 증가
    $update_stmt = $pdo->prepare("UPDATE hopec_post_files SET bf_download = bf_download + 1 WHERE bf_no = ?");
    $update_stmt->execute([$file_id]);
    
    // MIME 타입 결정
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    // 기본 MIME 타입 설정
    if (!$mime_type) {
        $ext = strtolower(pathinfo($file_info['bf_source'], PATHINFO_EXTENSION));
        $mime_types = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'hwp' => 'application/x-hwp',
            'hwpx' => 'application/x-hwpx',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        $mime_type = $mime_types[$ext] ?? 'application/octet-stream';
    }
    
    // 헤더 설정
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . $file_info['bf_filesize']);
    header('Content-Disposition: attachment; filename="' . addslashes($file_info['bf_source']) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    
    // 출력 버퍼 정리
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // 파일 출력
    $handle = fopen($file_path, 'rb');
    if ($handle) {
        while (!feof($handle)) {
            echo fread($handle, 8192);
            if (connection_aborted()) {
                break;
            }
        }
        fclose($handle);
    } else {
        http_response_code(500);
        die('파일을 읽을 수 없습니다.');
    }
    
} catch (PDOException $e) {
    error_log("파일 다운로드 DB 오류: " . $e->getMessage());
    http_response_code(500);
    die('시스템 오류가 발생했습니다.');
} catch (Exception $e) {
    error_log("파일 다운로드 오류: " . $e->getMessage());
    http_response_code(500);
    die('파일 다운로드 중 오류가 발생했습니다.');
}
?>