<?php
// 범용 파일 다운로드 핸들러 - hopec_posts 통합 호환
// 입력: GET post_id, attachment_id
// hopec_post_files 테이블 조회 후 파일 전송

// 모든 출력 버퍼 정리 (파일 전송 전에 먼저 실행)
while (ob_get_level()) {
    ob_end_clean();
}

// 에러 출력을 버퍼에 저장 (헤더 전송 후 에러가 발생하지 않도록)
ob_start();

// hopec_posts 호환성 레이어 로드
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database_helper.php';

// 파라미터 처리
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$attachment_id = isset($_GET['attachment_id']) ? (int)$_GET['attachment_id'] : 0;

// 하위 호환: 기존 형식 지원 (wr_id, no)
if ($post_id <= 0 && isset($_GET['wr_id'])) {
    $post_id = (int)$_GET['wr_id'];
}
if ($attachment_id <= 0 && isset($_GET['no'])) {
    $attachment_id = (int)$_GET['no'];
}

// 입력값 검증
if ($post_id <= 0 || $attachment_id < 0) {
    ob_end_clean();
    http_response_code(400);
    echo '잘못된 요청입니다.';
    exit;
}

// 데이터베이스 연결
$pdo = getBoardDatabase();
if (!$pdo) {
    ob_end_clean();
    http_response_code(500);
    echo 'DB 연결 실패';
    exit;
}

// 파일 메타 조회 (hopec_post_files + hopec_posts 조인)
try {
    // 게시물의 board_type을 얻기 위해 hopec_posts와 조인 (wr_parent 기반, board_type 매칭)
    $stmt = $pdo->prepare('SELECT pf.bf_source as original_name, pf.bf_file as stored_name, pf.bf_filesize as file_size, p.board_type 
                           FROM hopec_post_files pf 
                           JOIN hopec_posts p ON pf.wr_id = p.wr_parent AND pf.board_type = p.board_type
                           WHERE p.wr_id = ? AND pf.bf_no = ?');
    $stmt->execute([$post_id, $attachment_id]);
    
    $row = $stmt->fetch();
    if (!$row) {
        ob_end_clean();
        http_response_code(404);
        echo '파일 정보를 찾을 수 없습니다.';
        exit;
    }
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo '파일 정보를 조회할 수 없습니다: ' . $e->getMessage();
    exit;
}

$originalName = (string)$row['original_name'];
$storedName   = (string)$row['stored_name'];
$fileSize     = (int)$row['file_size'];

// 파일 물리 경로 구성 - 다중 경로 시도
$post_board_type = $row['board_type'] ?? '';
if (empty($post_board_type)) {
    ob_end_clean();
    http_response_code(500);
    echo 'board_type 정보가 없습니다.';
    exit;
}

// 설정된 파일 베이스 경로 사용
$baseDir = rtrim(BOARD_TEMPLATES_FILE_BASE_PATH, '/');

// 1차: 게시물의 board_type 경로 시도
$filePath = $baseDir . '/' . $post_board_type . '/' . $storedName;

// 파일이 없으다면 다른 board_type 디렉토리에서 찾기
if (!is_file($filePath) || !file_exists($filePath)) {
    $searchDirs = ['finance_reports', 'resources', 'notices', 'newsletter', 'gallery', 'nepal_travel'];
    $found = false;
    
    foreach ($searchDirs as $dir) {
        if ($dir === $post_board_type) continue; // 이미 시도한 경로
        
        $testPath = $baseDir . '/' . $dir . '/' . $storedName;
        if (is_file($testPath) && file_exists($testPath)) {
            $filePath = $testPath;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        ob_end_clean();
        http_response_code(404);
        echo '파일이 존재하지 않습니다: ' . $storedName;
        exit;
    }
}

// 다운로드 권한 검사 (옵션)
if (!BOARD_TEMPLATES_DOWNLOAD_OPEN) {
    // 필요시 로그인/레벨 검사 등 구현 가능
}

// 파일 확장자에 따른 적절한 Content-Type 설정
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'hwp' => 'application/x-hwp',
    'hwpx' => 'application/x-hwp',
    'txt' => 'text/plain',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed'
];

$contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

// 헤더 전송 및 파일 출력
$filesize = filesize($filePath);
$encodedName = rawurlencode($originalName);

// URL 파라미터로 표시 방식 제어 (기본은 다운로드)
$view_inline = isset($_GET['inline']) && $_GET['inline'] === '1';
if ($view_inline && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
    $disposition = 'inline';
} else {
    $disposition = 'attachment';
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);
header('Content-Disposition: ' . $disposition . '; filename="'.basename($encodedName).'"');
header('Content-Length: ' . $filesize);
header('Pragma: public');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

// 출력 버퍼 정리 및 헤더 전송 준비
ob_end_clean();

// 출력
readfile($filePath);
exit;

?>


