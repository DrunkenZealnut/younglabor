<?php
/**
 * 단순화된 테스트용 처리 파일
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0); // JSON 응답을 위해 비활성화

try {
    // POST 요청만 허용
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST 요청만 허용됩니다.');
    }
    
    // 파일 업로드 확인
    if (!isset($_FILES['sqlFile'])) {
        throw new Exception('파일이 업로드되지 않았습니다.');
    }
    
    if ($_FILES['sqlFile']['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => '파일이 너무 큽니다 (php.ini 설정)',
            UPLOAD_ERR_FORM_SIZE => '파일이 너무 큽니다 (폼 설정)',
            UPLOAD_ERR_PARTIAL => '파일이 부분적으로만 업로드되었습니다',
            UPLOAD_ERR_NO_FILE => '파일이 선택되지 않았습니다',
            UPLOAD_ERR_NO_TMP_DIR => '임시 디렉토리가 없습니다',
            UPLOAD_ERR_CANT_WRITE => '파일을 쓸 수 없습니다',
            UPLOAD_ERR_EXTENSION => 'PHP 확장에 의해 차단되었습니다'
        ];
        
        $errorMessage = $errors[$_FILES['sqlFile']['error']] ?? '알 수 없는 업로드 오류';
        throw new Exception($errorMessage);
    }
    
    // 기본 변수들
    $uploadedFile = $_FILES['sqlFile'];
    $projectName = $_POST['projectName'] ?? '새 프로젝트';
    $tablePrefix = $_POST['tablePrefix'] ?? '';
    $theme = $_POST['theme'] ?? 'bootstrap';
    $language = $_POST['language'] ?? 'ko';
    
    // 파일 검증
    $allowedExtensions = ['sql', 'txt'];
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('SQL 또는 TXT 파일만 업로드할 수 있습니다.');
    }
    
    if ($uploadedFile['size'] > 10 * 1024 * 1024) { // 10MB
        throw new Exception('파일 크기가 10MB를 초과할 수 없습니다.');
    }
    
    // 파일 내용 읽기
    $sqlContent = file_get_contents($uploadedFile['tmp_name']);
    if ($sqlContent === false || empty(trim($sqlContent))) {
        throw new Exception('SQL 파일 내용을 읽을 수 없거나 파일이 비어있습니다.');
    }
    
    // 기본적인 SQL 검증
    $sqlContent = trim($sqlContent);
    if (!preg_match('/CREATE\s+TABLE/i', $sqlContent)) {
        throw new Exception('유효한 CREATE TABLE 문이 포함된 SQL 파일이 아닙니다.');
    }
    
    // 임시 토큰 생성
    $token = uniqid('board_gen_', true);
    $tempDir = __DIR__ . '/temp/' . $token;
    
    if (!is_dir(__DIR__ . '/temp')) {
        mkdir(__DIR__ . '/temp', 0755, true);
    }
    
    if (!mkdir($tempDir, 0755, true)) {
        throw new Exception('임시 디렉토리를 생성할 수 없습니다.');
    }
    
    // 간단한 테이블 추출 (정규표현식 사용)
    preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\(/i', $sqlContent, $matches);
    $foundTables = $matches[1] ?? [];
    
    if (empty($foundTables)) {
        throw new Exception('SQL에서 테이블을 찾을 수 없습니다.');
    }
    
    // 간단한 메타데이터 생성
    $metadata = [
        'token' => $token,
        'project_name' => $projectName,
        'table_prefix' => $tablePrefix,
        'theme' => $theme,
        'language' => $language,
        'generated_at' => date('Y-m-d H:i:s'),
        'original_filename' => $uploadedFile['name'],
        'tables' => array_combine($foundTables, $foundTables),
        'generated_files' => ['index.php', 'config.php'],
        'download_ready' => true,
        'sql_content' => $sqlContent
    ];
    
    // 메타데이터 저장
    file_put_contents($tempDir . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // 간단한 index.php 생성
    $indexContent = "<?php\n// 생성된 게시판 시스템\necho '<h1>{$projectName}</h1>';\necho '<p>발견된 테이블: " . implode(', ', $foundTables) . "</p>';\n?>";
    file_put_contents($tempDir . '/index.php', $indexContent);
    
    // 간단한 config.php 생성
    $configContent = "<?php\n// 설정 파일\ndefine('PROJECT_NAME', '{$projectName}');\ndefine('TABLE_PREFIX', '{$tablePrefix}');\n?>";
    file_put_contents($tempDir . '/config.php', $configContent);
    
    // 성공 응답
    $response = [
        'success' => true,
        'token' => $token,
        'message' => '게시판이 성공적으로 생성되었습니다.',
        'tables_found' => count($foundTables),
        'files_generated' => 2,
        'download_url' => 'download.php?token=' . $token,
        'debug_info' => [
            'found_tables' => $foundTables,
            'temp_dir' => $tempDir,
            'files_created' => [
                'index.php' => file_exists($tempDir . '/index.php') ? 'yes' : 'no',
                'config.php' => file_exists($tempDir . '/config.php') ? 'yes' : 'no',
                'metadata.json' => file_exists($tempDir . '/metadata.json') ? 'yes' : 'no'
            ]
        ]
    ];
    
    // 로그 파일에도 기록
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logDir . '/simple_process.log', 
        date('Y-m-d H:i:s') . " - SUCCESS: " . json_encode($response) . "\n", 
        FILE_APPEND | LOCK_EX
    );
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
            'post_size' => $_POST ? count($_POST) : 0,
            'files_size' => $_FILES ? count($_FILES) : 0,
            'php_version' => phpversion(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'temp_dir_writable' => is_writable(__DIR__ . '/temp') ? 'yes' : 'no'
        ]
    ]);
}
?>