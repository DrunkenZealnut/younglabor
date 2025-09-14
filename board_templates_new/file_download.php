<?php
// 범용 파일 다운로드 핸들러 (GNUBOARD 의존 최소화)
// 입력: GET bo_table, wr_id, no
// g5_board_file 조회 후 BOARD_TEMPLATES_FILE_BASE_PATH/bo_table/stored_name 경로에서 파일 전송

// 공통 초기화 (가능 시)
@include_once __DIR__ . '/../_common.php';

// 외부 설정
require_once __DIR__ . '/config.php';

// 보안: 입력값 필터링
$bo_table = isset($_GET['bo_table']) ? preg_replace('/[^A-Za-z0-9_\-]/', '', $_GET['bo_table']) : '';
$wr_id    = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : 0;
$no       = isset($_GET['no']) ? (int)$_GET['no'] : 0;

if ($bo_table === '' || $wr_id <= 0 || $no < 0) {
    http_response_code(400);
    echo '잘못된 요청입니다.';
    exit;
}

// DB 연결 확보
if (!isset($pdo) || !($pdo instanceof PDO)) {
    if (defined('G5_MYSQL_HOST')) {
        try {
            $pdo = new PDO(
                'mysql:host=' . G5_MYSQL_HOST . ';dbname=' . G5_MYSQL_DB . ';charset=utf8mb4',
                G5_MYSQL_USER,
                G5_MYSQL_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'DB 연결 실패';
            exit;
        }
    } else {
        http_response_code(500);
        echo 'DB 설정을 찾을 수 없습니다.';
        exit;
    }
}

// 파일 메타 조회 (GNUBOARD g5_board_file)
try {
    $stmt = $pdo->prepare('SELECT bf_source, bf_file, bf_filesize FROM ' . (defined('G5_TABLE_PREFIX') ? G5_TABLE_PREFIX : 'g5_') . 'board_file WHERE bo_table = ? AND wr_id = ? AND bf_no = ?');
    $stmt->execute([$bo_table, $wr_id, $no]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo '파일 정보를 찾을 수 없습니다.';
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo '파일 정보를 조회할 수 없습니다.';
    exit;
}

$originalName = (string)$row['bf_source'];
$storedName   = (string)$row['bf_file'];

// 파일 물리 경로
$filePath = rtrim(BOARD_TEMPLATES_FILE_BASE_PATH, '/').'/'.$bo_table.'/'.$storedName;
if (!is_file($filePath) || !file_exists($filePath)) {
    http_response_code(404);
    echo '파일이 존재하지 않습니다.';
    exit;
}

// 다운로드 권한 검사 (옵션)
if (!BOARD_TEMPLATES_DOWNLOAD_OPEN) {
    // 필요시 로그인/레벨 검사 등 구현 가능
}

// 헤더 전송 및 파일 출력
$filesize = filesize($filePath);
$encodedName = rawurlencode($originalName);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($encodedName).'"');
header('Content-Length: ' . $filesize);
header('Pragma: public');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

// 출력
readfile($filePath);
exit;

?>


