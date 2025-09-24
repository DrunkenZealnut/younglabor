<?php
/**
 * 업로드 헬퍼 함수
 * 테이블명 기반 업로드 폴더 관리
 */

// 업로드 설정 로드
function getUploadConfig() {
    static $config = null;
    if ($config === null) {
        $config_file = dirname(__DIR__) . '/config/upload.php';
        $config = file_exists($config_file) ? require $config_file : [];
        
        // 기본값 설정
        $config = array_merge([
            'base_path' => 'data',
            'file_sub_path' => 'file',
            'max_file_size' => 10485760,
            'all_allowed_extensions' => ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'],
            'create_htaccess' => true,
            'htaccess_content' => "# Prevent direct access\nOptions -Indexes\nOrder Allow,Deny\nAllow from all\n",
            'legacy_support' => true,
            'legacy_base_path' => 'uploads'
        ], $config);
    }
    return $config;
}

/**
 * 테이블명 기반 업로드 디렉토리 경로 생성
 * @param string $table_name 테이블명
 * @param string $project_root 프로젝트 루트 경로 (선택사항)
 * @return string 업로드 디렉토리 경로
 */
function getUploadDirectory($table_name, $project_root = null) {
    $config = getUploadConfig();
    
    // 테이블명 검증 및 정리
    $safe_table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);
    if (empty($safe_table_name)) {
        throw new InvalidArgumentException('유효하지 않은 테이블명입니다.');
    }
    
    // 프로젝트 루트 결정
    if ($project_root === null) {
        $project_root = dirname(__DIR__);
    }
    
    // 업로드 경로 구성: {project_root}/{base_path}/{file_sub_path}/{table_name}/
    $upload_path = $project_root . '/' . 
                   rtrim($config['base_path'], '/') . '/' . 
                   rtrim($config['file_sub_path'], '/') . '/' . 
                   $safe_table_name . '/';
    
    return $upload_path;
}

/**
 * 업로드 디렉토리 생성 및 권한 설정
 * @param string $table_name 테이블명
 * @param string $project_root 프로젝트 루트 경로 (선택사항)
 * @return string 생성된 디렉토리 경로
 */
function createUploadDirectory($table_name, $project_root = null) {
    $config = getUploadConfig();
    $upload_dir = getUploadDirectory($table_name, $project_root);
    
    // 디렉토리 생성
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('업로드 디렉토리를 생성할 수 없습니다: ' . $upload_dir);
        }
    }
    
    // 권한 확인 및 설정
    if (!is_writable($upload_dir)) {
        @chmod($upload_dir, 0755);
        if (!is_writable($upload_dir)) {
            throw new Exception('업로드 디렉토리에 쓰기 권한이 없습니다: ' . $upload_dir);
        }
    }
    
    // .htaccess 파일 생성 (보안)
    if ($config['create_htaccess']) {
        $htaccess_file = $upload_dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            @file_put_contents($htaccess_file, $config['htaccess_content']);
        }
    }
    
    return $upload_dir;
}

/**
 * 안전한 파일명 생성
 * @param string $original_filename 원본 파일명
 * @param string $table_name 테이블명
 * @return string 안전한 파일명
 */
function generateSafeFilename($original_filename, $table_name = 'file') {
    $config = getUploadConfig();
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $safe_table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);
    
    // 파일명 패턴 적용 (config에서 설정 가능)
    $pattern = $config['filename_pattern'] ?? '{table}_{timestamp}_{unique}.{ext}';
    $filename = str_replace([
        '{table}',
        '{timestamp}', 
        '{unique}',
        '{ext}'
    ], [
        $safe_table_name,
        date('YmdHis'),
        uniqid(),
        $file_extension
    ], $pattern);
    
    return $filename;
}

/**
 * 웹 접근 가능한 상대 경로 생성
 * @param string $table_name 테이블명
 * @param string $filename 파일명
 * @return string 웹 상대 경로
 */
function getWebPath($table_name, $filename) {
    $config = getUploadConfig();
    $safe_table_name = preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);
    
    return $config['web_base_path'] . '/' . 
           $config['web_file_sub_path'] . '/' . 
           $safe_table_name . '/' . $filename;
}

/**
 * 파일 업로드 처리
 * @param array $file $_FILES 배열의 한 항목
 * @param string $table_name 테이블명
 * @param array $allowed_extensions 허용된 확장자 배열 (null이면 설정에서 가져옴)
 * @param int $max_size 최대 파일 크기 (0이면 설정에서 가져옴)
 * @return array 업로드 결과
 */
function handleFileUpload($file, $table_name, $allowed_extensions = null, $max_size = 0) {
    $config = getUploadConfig();
    
    // 테이블별 설정 가져오기
    if ($allowed_extensions === null) {
        $allowed_extensions = $config['table_extensions'][$table_name] ?? $config['all_allowed_extensions'];
    }
    
    if ($max_size === 0) {
        $max_size = $config['table_max_sizes'][$table_name] ?? $config['max_file_size'];
    }
    
    // 파일 검증
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('파일 업로드 오류: ' . $file['error']);
    }
    
    $original_filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    
    // 파일 크기 검증
    if ($file_size > $max_size) {
        throw new Exception('파일 크기가 최대 허용 크기(' . round($max_size / 1048576, 1) . 'MB)를 초과합니다.');
    }
    
    // 파일 확장자 검증
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('허용되지 않는 파일 형식입니다. 허용 형식: ' . implode(', ', $allowed_extensions));
    }
    
    // 업로드 디렉토리 생성
    $upload_dir = createUploadDirectory($table_name);
    
    // 안전한 파일명 생성
    $safe_filename = generateSafeFilename($original_filename, $table_name);
    $file_path = $upload_dir . $safe_filename;
    
    // 파일 이동
    if (!move_uploaded_file($tmp_name, $file_path)) {
        throw new Exception('파일 저장에 실패했습니다.');
    }
    
    // 파일 권한 설정
    @chmod($file_path, 0644);
    
    return [
        'original_filename' => $original_filename,
        'stored_filename' => $safe_filename,
        'file_path' => $file_path,
        'relative_path' => getWebPath($table_name, $safe_filename),
        'file_size' => $file_size,
        'file_extension' => $file_extension,
        'table_name' => $table_name
    ];
}

/**
 * 다중 파일 업로드 처리
 * @param array $files $_FILES['field_name'] 배열
 * @param string $table_name 테이블명
 * @param array $allowed_extensions 허용된 확장자 배열
 * @param int $max_size 최대 파일 크기 (바이트)
 * @return array 업로드 결과 배열
 */
function handleMultipleFileUpload($files, $table_name, $allowed_extensions = null, $max_size = 10485760) {
    $uploaded_files = [];
    
    if (!isset($files['name']) || !is_array($files['name'])) {
        throw new Exception('유효하지 않은 파일 데이터입니다.');
    }
    
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue; // 파일이 선택되지 않은 경우 건너뛰기
        }
        
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // 오류가 있는 파일은 건너뛰기
        }
        
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        
        try {
            $uploaded_files[] = handleFileUpload($file, $table_name, $allowed_extensions, $max_size);
        } catch (Exception $e) {
            // 개별 파일 오류는 로그에 기록하고 계속 진행
            error_log("File upload error for {$file['name']}: " . $e->getMessage());
        }
    }
    
    return $uploaded_files;
}

/**
 * 파일 삭제
 * @param string $table_name 테이블명
 * @param string $filename 파일명
 * @return bool 삭제 성공 여부
 */
function deleteUploadedFile($table_name, $filename) {
    $upload_dir = getUploadDirectory($table_name);
    $file_path = $upload_dir . $filename;
    
    if (file_exists($file_path)) {
        return @unlink($file_path);
    }
    
    return false;
}

/**
 * 업로드 디렉토리 목록 조회
 * @param string $project_root 프로젝트 루트 경로 (선택사항)
 * @return array 테이블명 배열
 */
function getUploadDirectories($project_root = null) {
    $config = getUploadConfig();
    
    if ($project_root === null) {
        $project_root = dirname(__DIR__);
    }
    
    $file_base_path = $project_root . '/' . 
                      rtrim($config['base_path'], '/') . '/' . 
                      rtrim($config['file_sub_path'], '/') . '/';
    
    $directories = [];
    
    if (!is_dir($file_base_path)) {
        return $directories;
    }
    
    $items = scandir($file_base_path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $full_path = $file_base_path . $item;
        if (is_dir($full_path)) {
            $directories[] = $item;
        }
    }
    
    return $directories;
}
?>