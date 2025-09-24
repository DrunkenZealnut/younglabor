<?php
/**
 * HOPEC 파일 다운로드 핸들러
 * hopec_board_files 테이블 구조에 맞춘 파일 다운로드 시스템
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/bootstrap/app.php';

// 입력값 검증 및 필터링
$bo_table = $_GET['bo_table'] ?? '';
$wr_id = (int)($_GET['wr_id'] ?? 0);
$bf_no = (int)($_GET['bf_no'] ?? 0);

// 기본 검증
if (empty($bo_table) || $wr_id <= 0 || $bf_no < 0) {
    http_response_code(400);
    exit('잘못된 요청입니다.');
}

// 보안: bo_table 이름 검증
if (!preg_match('/^[A-Za-z0-9_]+$/', $bo_table)) {
    http_response_code(400);
    exit('잘못된 테이블 이름입니다.');
}

try {
    // 파일 정보 조회 - 새로운 테이블별 파일 구조 지원
    $file_info = DatabaseManager::selectOne(
        "SELECT bf_source, bf_file, bf_filesize, bf_datetime, bo_table
         FROM hopec_board_files 
         WHERE bo_table = :bo_table AND wr_id = :wr_id AND bf_no = :bf_no",
        [
            ':bo_table' => $bo_table,
            ':wr_id' => $wr_id,
            ':bf_no' => $bf_no
        ]
    );
    
    if (!$file_info) {
        http_response_code(404);
        exit('파일 정보를 찾을 수 없습니다.');
    }
    
    // 원본 파일명과 저장된 파일명
    $original_name = $file_info['bf_source'];
    $stored_name = $file_info['bf_file'];
    $file_size = (int)$file_info['bf_filesize'];
    
    // 업로드 설정 로드
    require_once __DIR__ . '/includes/upload_helpers.php';
    $config = getUploadConfig();
    
    // 파일 물리 경로 생성 - 새로운 data/file/{tablename} 구조
    $table_name = $file_info['bo_table'] ?? $bo_table;
    $new_base_path = __DIR__ . '/' . $config['base_path'] . '/' . $config['file_sub_path'];
    $file_path = $new_base_path . '/' . $table_name . '/' . $stored_name;
    
    // 새로운 경로에서 파일을 찾지 못하면 레거시 경로 확인
    if (!file_exists($file_path) && $config['legacy_support']) {
        // 기존 data/{tablename} 구조 확인
        $old_data_path = __DIR__ . '/data/' . $table_name . '/' . $stored_name;
        if (file_exists($old_data_path)) {
            $file_path = $old_data_path;
        } else {
            // 기존 uploads 폴더 확인
            $legacy_base_path = __DIR__ . '/' . $config['legacy_base_path'];
            $legacy_mapping = $config['legacy_path_mapping'];
            
            $sub_dir = $legacy_mapping[$bo_table] ?? 'others';
            $legacy_file_path = $legacy_base_path . '/' . $sub_dir . '/' . $stored_name;
            
            if (file_exists($legacy_file_path)) {
                $file_path = $legacy_file_path;
            }
        }
    }
    
    // 파일 존재 확인
    if (!file_exists($file_path) || !is_readable($file_path)) {
        http_response_code(404);
        exit('파일이 존재하지 않거나 읽을 수 없습니다.');
    }
    
    // 실제 파일 크기 확인 (보안)
    $actual_size = filesize($file_path);
    if ($actual_size !== $file_size && $file_size > 0) {
        error_log("File size mismatch: expected {$file_size}, actual {$actual_size} for {$file_path}");
    }
    
    // 다운로드 권한 검사 (필요시 구현)
    // 현재는 모든 파일에 대해 다운로드 허용
    
    // MIME 타입 설정
    $mime_type = get_file_mime_type($original_name);
    
    // 출력 버퍼 완전 정리
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // 파일명 안전 처리 (한글 지원) 
    $safe_filename = str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $original_name);
    $encoded_filename = rawurlencode($original_name);
    
    // 출력 버퍼 완전 정리
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // HTTP 헤더 설정
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $safe_filename . '"; filename*=UTF-8\'\'' . $encoded_filename);
    header('Content-Length: ' . $actual_size);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // 대용량 파일 처리를 위한 청크 단위 출력
    $chunk_size = 8192; // 8KB 청크
    $handle = fopen($file_path, 'rb');
    
    if ($handle === false) {
        http_response_code(500);
        exit('파일을 읽을 수 없습니다.');
    }
    
    // 청크 단위로 파일 출력
    while (!feof($handle)) {
        $chunk = fread($handle, $chunk_size);
        if ($chunk === false) {
            break;
        }
        echo $chunk;
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }
    
    fclose($handle);
    
    // 다운로드 로그 기록 (선택사항)
    try {
        DatabaseManager::execute(
            "INSERT INTO hopec_download_logs (bo_table, wr_id, bf_no, download_ip, download_datetime) 
             VALUES (:bo_table, :wr_id, :bf_no, :ip, NOW())",
            [
                ':bo_table' => $bo_table,
                ':wr_id' => $wr_id,
                ':bf_no' => $bf_no,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]
        );
    } catch (Exception $e) {
        // 로그 기록 실패는 무시 (다운로드에 영향 없음)
        error_log("Download log failed: " . $e->getMessage());
    }
    
    exit;
    
} catch (Exception $e) {
    error_log("File download error: " . $e->getMessage());
    http_response_code(500);
    exit('파일 다운로드 중 오류가 발생했습니다.');
}

/**
 * 파일 확장자를 기반으로 MIME 타입 반환
 */
function get_file_mime_type($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $mime_types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'zip' => 'application/zip',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'hwp' => 'application/x-hwp'
    ];
    
    return $mime_types[$extension] ?? 'application/octet-stream';
}
?>