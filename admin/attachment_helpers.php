<?php
/**
 * Admin 첨부파일 처리 헬퍼 함수들
 * write.php에서 사용하는 업로드 관련 함수 제공
 */

error_log("=== attachment_helpers.php 파일이 실행되기 시작했습니다 ===");

error_log("get_bt_upload_path 함수 존재 여부: " . (function_exists('get_bt_upload_path') ? 'EXISTS' : 'NOT_EXISTS'));
if (!function_exists('get_bt_upload_path')) {
    error_log("get_bt_upload_path 함수를 정의합니다.");
    /**
     * Board Templates 업로드 경로 반환
     * @return string 업로드 기본 경로
     */
    function get_bt_upload_path() {
        // 환경 변수에서 업로드 경로 가져오기
        $upload_path = env('BOARD_TEMPLATES_FILE_BASE_PATH', __DIR__ . '/../uploads/board_templates');
        
        // 절대 경로로 변환
        if (!is_dir($upload_path)) {
            // 상대 경로인 경우 절대 경로로 변환
            $upload_path = realpath(__DIR__ . '/../') . '/uploads/board_templates';
        }
        
        return rtrim($upload_path, '/');
    }
    error_log("get_bt_upload_path 함수 정의 완료");
}

if (!function_exists('validateFileUpload')) {
    /**
     * 파일 업로드 보안 검증
     * @param array $file $_FILES 배열의 개별 파일 정보
     * @return array 검증 오류 메시지 배열 (빈 배열이면 통과)
     */
    function validateFileUpload($file) {
        $errors = [];
        
        // 기본 업로드 에러 체크
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = '파일 업로드 중 오류가 발생했습니다.';
            return $errors;
        }
        
        // 파일 크기 체크 (5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $errors[] = '파일 크기가 5MB를 초과합니다.';
        }
        
        // 빈 파일 체크
        if ($file['size'] === 0) {
            $errors[] = '빈 파일은 업로드할 수 없습니다.';
        }
        
        // 파일명 검증
        if (empty($file['name']) || strlen($file['name']) > 255) {
            $errors[] = '잘못된 파일명입니다.';
        }
        
        // 확장자 검증
        $allowedExtensions = [
            'pdf', 'doc', 'docx', 'hwp', 'hwpx', 
            'xls', 'xlsx', 'ppt', 'pptx',
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'txt', 'csv'
        ];
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = '허용되지 않는 파일 형식입니다.';
        }
        
        // MIME 타입 검증 (추가 보안)
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'text/plain',
            'text/csv',
            'application/x-hwp'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detectedMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            // MIME 타입이 허용 목록에 없고, 알려진 안전하지 않은 타입인 경우
            if (!in_array($detectedMime, $allowedMimeTypes)) {
                // 일부 예외적인 경우 허용 (HWP, 일부 이미지)
                $safeExceptions = [
                    'application/octet-stream' // HWP 파일 등
                ];
                
                if (!in_array($detectedMime, $safeExceptions)) {
                    $errors[] = '파일 형식이 안전하지 않습니다.';
                }
            }
        }
        
        // 파일명에서 위험한 문자 체크
        if (preg_match('/[<>:"|?*\x00-\x1f]/', $file['name'])) {
            $errors[] = '파일명에 허용되지 않는 문자가 포함되어 있습니다.';
        }
        
        // 실행 파일 확장자 차단
        $dangerousExtensions = [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 
            'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp'
        ];
        
        if (in_array($extension, $dangerousExtensions)) {
            $errors[] = '실행 파일은 업로드할 수 없습니다.';
        }
        
        return $errors;
    }
}

if (!function_exists('generateSafeFilename')) {
    /**
     * 안전한 파일명 생성
     * @param string $originalName 원본 파일명
     * @return string 안전한 파일명 (타임스탬프 포함)
     */
    function generateSafeFilename($originalName) {
        // 확장자 분리
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // 파일명 정리 (한글 및 특수문자 처리)
        $basename = preg_replace('/[^\w\-가-힣]/', '_', $basename);
        $basename = preg_replace('/_{2,}/', '_', $basename); // 연속 언더스코어 제거
        $basename = trim($basename, '_');
        
        // 파일명이 비어있거나 너무 긴 경우 처리
        if (empty($basename) || strlen($basename) > 100) {
            $basename = 'file';
        }
        
        // 타임스탬프와 랜덤 문자열 추가 (중복 방지)
        $timestamp = date('YmdHis');
        $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
        
        return $basename . '_' . $timestamp . '_' . $randomString . '.' . $extension;
    }
}

if (!function_exists('createUploadDirectory')) {
    /**
     * 업로드 디렉토리 생성
     * @param string $path 생성할 디렉토리 경로
     * @return bool 생성 성공 여부
     */
    function createUploadDirectory($path) {
        if (is_dir($path)) {
            return true;
        }
        
        return mkdir($path, 0755, true);
    }
}

if (!function_exists('getUploadConfig')) {
    /**
     * 업로드 설정 반환 (write.php에서 사용하는 $config 대체)
     * @return array 업로드 설정 배열
     */
    function getUploadConfig() {
        return [
            'base_path' => 'uploads',
            'file_sub_path' => 'board_templates',
            'legacy_support' => true,
            'legacy_base_path' => 'uploads',
            'legacy_path_mapping' => [
                'finance_reports' => 'finance_reports',
                'notices' => 'notices', 
                'press' => 'press',
                'newsletter' => 'newsletter',
                'gallery' => 'gallery',
                'resources' => 'resources',
                'nepal_travel' => 'nepal_travel'
            ]
        ];
    }
}

if (!function_exists('env')) {
    /**
     * 환경 변수 헬퍼 함수 (없는 경우 대비)
     * @param string $key 환경 변수 키
     * @param mixed $default 기본값
     * @return mixed 환경 변수 값 또는 기본값
     */
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}
?>