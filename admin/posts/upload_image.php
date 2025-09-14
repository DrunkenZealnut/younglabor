<?php
include '../auth.php'; // 관리자 인증 확인
require_once '../env_loader.php'; // .env 파일 로더

// 제출된 요청 기록 (디버깅용)
$request_log = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'files' => isset($_FILES) ? array_keys($_FILES) : [],
    'post_data' => $_POST ?? [],
    'files_detail' => $_FILES ?? [],
    'time' => date('Y-m-d H:i:s')
];

// 응답 배열 초기화
$response = [
    'success' => false,
    'url' => '',
    'message' => '',
    'request_log' => $request_log
];

// .env에서 업로드 디렉토리 설정 가져오기
$upload_base_path = env('UPLOAD_PATH', 'data/file');
$bt_upload_path = env('BT_UPLOAD_PATH', '/Users/zealnutkim/Documents/개발/hopec/data/file');

// 게시판 정보 추출 (POST 데이터에서)
$board_table = isset($_POST['board_table']) ? $_POST['board_table'] : 'general';

// board_type을 폴더명으로 사용 (write.php의 새로운 방식과 일치)
$board_type_mapping = [
    'finance_reports' => 'finance_reports',
    'notices' => 'notices', 
    'press' => 'press',
    'newsletter' => 'newsletter',
    'gallery' => 'gallery',
    'resources' => 'resources',
    'nepal_travel' => 'nepal_travel'
];

$board_folder = isset($board_type_mapping[$board_table]) ? $board_type_mapping[$board_table] : 'general';

// 연도/월 폴더 구조 생성
$date = new DateTime();
$year = $date->format('Y');
$month = $date->format('m');

// 절대 경로 사용 (BT_UPLOAD_PATH 기반) - 게시판별 폴더 추가
$upload_dir = rtrim($bt_upload_path, '/') . "/posts/$board_folder/$year/$month/";
$relative_upload_path = "data/file/posts/$board_folder/$year/$month/";

// 디버깅 메시지 초기화
$debug_info = '';

// 초기 환경 정보 추가
$debug_info .= "=== 업로드 환경 정보 ===\n";
$debug_info .= "PHP 버전: " . phpversion() . "\n";
$debug_info .= "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
$debug_info .= "post_max_size: " . ini_get('post_max_size') . "\n";
$debug_info .= "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
$debug_info .= "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) . "\n";
$debug_info .= "현재 시간: " . date('Y-m-d H:i:s') . "\n";
$debug_info .= "요청 방법: " . $_SERVER['REQUEST_METHOD'] . "\n\n";

// 디렉토리 존재 여부 확인 및 생성
if (!is_dir($upload_dir)) {
    $debug_info .= "디렉토리가 존재하지 않아 생성 시도: {$upload_dir}\n";
    if (!mkdir($upload_dir, 0755, true)) {
        $debug_info .= "디렉토리 생성 실패\n";
        $response['message'] = '업로드 디렉토리를 생성할 수 없습니다. 권한을 확인해주세요.';
        $response['debug'] = $debug_info;
        send_json_response($response);
        exit;
    } else {
        $debug_info .= "디렉토리 생성 성공\n";
    }
}

// 디렉토리 권한 확인
if (!is_writable($upload_dir)) {
    $debug_info .= "디렉토리 쓰기 권한 없음: {$upload_dir}\n";
    $response['message'] = '업로드 디렉토리에 쓰기 권한이 없습니다.';
    $response['debug'] = $debug_info;
    send_json_response($response);
    exit;
}

// 파일 업로드 확인
if (!isset($_FILES['image'])) {
    $debug_info .= "업로드된 파일 없음 (FILES 배열에 'image' 키가 없음)\n";
    $response['message'] = '업로드된 이미지가 없습니다.';
    $response['debug'] = $debug_info;
    send_json_response($response);
    exit;
}

// 이미지 파일 업로드 에러 확인
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE => '업로드된 파일이 php.ini의 upload_max_filesize 지시어를 초과했습니다.',
        UPLOAD_ERR_FORM_SIZE => '업로드된 파일이 HTML 폼에서 지정한 MAX_FILE_SIZE를 초과했습니다.',
        UPLOAD_ERR_PARTIAL => '파일이 일부만 업로드되었습니다.',
        UPLOAD_ERR_NO_FILE => '파일이 업로드되지 않았습니다.',
        UPLOAD_ERR_NO_TMP_DIR => '임시 폴더가 없습니다.',
        UPLOAD_ERR_CANT_WRITE => '디스크에 파일을 쓸 수 없습니다.',
        UPLOAD_ERR_EXTENSION => 'PHP 확장에 의해 파일 업로드가 중지되었습니다.'
    ];
    
    $error_message = isset($upload_errors[$_FILES['image']['error']]) 
                     ? $upload_errors[$_FILES['image']['error']] 
                     : '알 수 없는 업로드 오류';
    
    $debug_info .= "파일 업로드 에러: " . $error_message . "\n";
    $response['message'] = $error_message;
    $response['debug'] = $debug_info;
    send_json_response($response);
    exit;
}

// 파일 정보
$tmp_name = $_FILES['image']['tmp_name'];
$name = basename($_FILES['image']['name']);
$size = $_FILES['image']['size'];
$type = $_FILES['image']['type'];

$debug_info .= "파일 정보:\n";
$debug_info .= "- 임시 파일명: {$tmp_name}\n";
$debug_info .= "- 원본 파일명: {$name}\n";
$debug_info .= "- 크기: {$size} bytes\n";
$debug_info .= "- MIME 타입: {$type}\n";

// 파일 확장자 및 타입 확인
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($type, $allowed_types)) {
    $debug_info .= "허용되지 않는 파일 타입: {$type}\n";
    $response['message'] = '허용되지 않는 이미지 형식입니다. JPEG, PNG, GIF 형식만 업로드 가능합니다.';
} elseif ($size > $max_size) {
    $debug_info .= "파일 크기 초과: {$size} > {$max_size}\n";
    $response['message'] = '파일 크기가 너무 큽니다. 최대 5MB까지 업로드 가능합니다.';
} else {
    // 파일명 생성 - 날짜 시간 형식과 고유 ID 조합
    $date_str = date('YmdHis'); // 년월일시분초 형식
    $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $unique_id = uniqid();
    $new_filename = "{$date_str}_{$unique_id}.{$file_ext}";
    $target_file = $upload_dir . $new_filename;
    
    $debug_info .= "저장할 파일 경로: {$target_file}\n";
    
    // 파일 업로드
    if (move_uploaded_file($tmp_name, $target_file)) {
        $debug_info .= "파일 업로드 성공\n";
        $response['success'] = true;
        
        // 업로드 성공 시 .env 설정 기반으로 URL 생성
        $bt_upload_url = env('BT_UPLOAD_URL', '/data/file');
        $url_path = rtrim($bt_upload_url, '/') . "/posts/$board_folder/$year/$month/" . $new_filename;
        $response['url'] = $url_path;
        
        // 다양한 URL 형식 제공
        $response['urls'] = [
            'relative' => $relative_upload_path . $new_filename,      // 기본 상대 경로
            'root_relative' => $url_path,                             // 루트 기준 경로
            'project_relative' => $url_path,                          // 프로젝트 기준 경로
            'admin_relative' => '../../' . $relative_upload_path . $new_filename, // admin 기준 경로
        ];
        
        $response['absolute_path'] = realpath($target_file);          // 디버깅용 절대 경로
        $response['message'] = '이미지가 성공적으로 업로드되었습니다.';
    } else {
        $debug_info .= "파일 업로드 실패\n";
        $response['message'] = '이미지 업로드 중 오류가 발생했습니다.';
    }
}

// 디버그 정보 추가
$response['debug'] = $debug_info;

// JSON 응답 전송
send_json_response($response);

/**
 * JSON 응답을 안전하게 전송하는 함수
 */
function send_json_response($data) {
    // 출력 버퍼 비우기
    if (ob_get_length()) ob_clean();
    
    // JSON 응답 헤더 설정
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    // JSON 인코딩
    $json = json_encode($data);
    
    // JSON 인코딩 오류 확인
    if ($json === false) {
        $error = [
            'success' => false,
            'message' => 'JSON 인코딩 오류: ' . json_last_error_msg(),
            'originalData' => print_r($data, true)
        ];
        echo json_encode($error);
    } else {
        echo $json;
    }
    
    // 출력 버퍼 비우고 종료
    flush();
    exit;
}
?> 