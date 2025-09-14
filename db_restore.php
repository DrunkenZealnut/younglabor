<?php
/**
 * 데이터베이스 복원 페이지 (PHP 5.5 호환)
 * 그누보드5 기반 희망씨 웹사이트
 */

// 데이터베이스 설정 (직접 연결)
define('G5_MYSQL_HOST', '127.0.0.1');
define('G5_MYSQL_USER', 'hopec');
define('G5_MYSQL_PASSWORD', 'hopec2024');
define('G5_MYSQL_DB', 'hopec');

// 데이터 경로 설정
if (!defined('G5_DATA_PATH')) {
    define('G5_DATA_PATH', './data');
}

// 기본 보안 설정
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Seoul');

// 간단한 관리자 인증 (비밀번호 기반)
session_start();

// 로그인 체크
if (!isset($_SESSION['db_admin_logged']) || $_SESSION['db_admin_logged'] !== true) {
    // 로그인 폼 표시
    if (isset($_POST['admin_password'])) {
        $admin_password = 'hopec2024!'; // 관리자 비밀번호 설정
        if ($_POST['admin_password'] === $admin_password) {
            $_SESSION['db_admin_logged'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_error = '비밀번호가 틀렸습니다.';
        }
    }
    
    // 로그인 폼 출력
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>데이터베이스 복원 - 관리자 인증</title>
        <style>
            body { font-family: "맑은 고딕", sans-serif; background: #f5f5f5; margin: 0; padding: 50px; }
            .login_box { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .login_box h2 { text-align: center; color: #333; margin-bottom: 30px; }
            .form_group { margin-bottom: 20px; }
            .form_group label { display: block; margin-bottom: 5px; font-weight: bold; }
            .form_group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
            .btn_login { width: 100%; padding: 12px; background: #428bca; color: white; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; }
            .btn_login:hover { background: #357ebd; }
            .error { color: #d9534f; text-align: center; margin-bottom: 15px; }
            .info { background: #e7f3ff; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="login_box">
            <h2>🔐 관리자 인증</h2>
            <div class="info">
                <strong>데이터베이스 복원 도구</strong><br>
                관리자 비밀번호를 입력하여 접근하세요.
            </div>
            <?php if (isset($login_error)): ?>
            <div class="error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form_group">
                    <label for="admin_password">관리자 비밀번호:</label>
                    <input type="password" id="admin_password" name="admin_password" required autofocus>
                </div>
                <button type="submit" class="btn_login">로그인</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 기본 함수들 정의
function alert($msg) {
    echo "<script>alert('" . addslashes($msg) . "');</script>";
}

function goto_url($url) {
    echo "<script>location.href='" . $url . "';</script>";
}

function get_token() {
    if (!isset($_SESSION['token'])) {
        $_SESSION['token'] = md5(uniqid(rand(), true));
    }
    return '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
}

function check_token() {
    return isset($_POST['token']) && isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token'];
}

// 복원 실행 요청 처리
if (isset($_POST['action']) && $_POST['action'] === 'restore') {
    // CSRF 토큰 확인
    if (!check_token()) {
        alert('잘못된 접근입니다.');
        exit;
    }
    
    // 파일 업로드 처리
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $restore_result = perform_database_restore($_FILES['backup_file']);
            if ($restore_result['success']) {
                alert('데이터베이스 복원이 완료되었습니다.\\n\\n복원된 쿼리: ' . $restore_result['queries'] . '개');
                goto_url('./db_backup.php');
            } else {
                alert('복원 중 오류가 발생했습니다: ' . $restore_result['error']);
            }
        } catch (Exception $e) {
            alert('복원 실패: ' . $e->getMessage());
        }
    } else {
        alert('백업 파일을 선택해주세요.');
    }
}

/**
 * 데이터베이스 복원 실행 함수
 */
function perform_database_restore($uploaded_file) {
    // 파일 검증
    $file_info = pathinfo($uploaded_file['name']);
    if (strtolower($file_info['extension']) !== 'sql') {
        return array('success' => false, 'error' => 'SQL 파일만 업로드 가능합니다.');
    }
    
    if ($uploaded_file['size'] > 50 * 1024 * 1024) { // 50MB 제한
        return array('success' => false, 'error' => '파일 크기가 너무 큽니다. (최대 50MB)');
    }
    
    // MySQL 연결
    $connection = new mysqli(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB, 3306);
    if ($connection->connect_error) {
        return array('success' => false, 'error' => '데이터베이스 연결 실패: ' . $connection->connect_error);
    }
    
    // UTF-8 설정
    $connection->set_charset("utf8mb4");
    $connection->query("SET foreign_key_checks = 0");
    
    // SQL 파일 읽기
    $sql_content = file_get_contents($uploaded_file['tmp_name']);
    if ($sql_content === false) {
        $connection->close();
        return array('success' => false, 'error' => '백업 파일을 읽을 수 없습니다.');
    }
    
    // SQL 구문 분리 및 실행
    $queries = split_sql_queries($sql_content);
    $executed_queries = 0;
    $failed_queries = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query) || substr($query, 0, 2) === '--') {
            continue; // 주석이나 빈 줄 건너뛰기
        }
        
        $result = $connection->query($query);
        if ($result) {
            $executed_queries++;
        } else {
            $failed_queries++;
            // 에러 로그 (중요하지 않은 에러는 무시)
            $error = $connection->error;
            if (strpos($error, 'Table') === false || strpos($error, 'already exists') === false) {
                error_log("DB Restore Error: " . $error . " Query: " . substr($query, 0, 100));
            }
        }
    }
    
    $connection->query("SET foreign_key_checks = 1");
    $connection->close();
    
    if ($failed_queries > 0 && $executed_queries === 0) {
        return array('success' => false, 'error' => '모든 쿼리 실행에 실패했습니다.');
    }
    
    return array(
        'success' => true,
        'queries' => $executed_queries,
        'failed' => $failed_queries
    );
}

/**
 * SQL 쿼리 분리 함수
 */
function split_sql_queries($sql_content) {
    // 세미콜론으로 분리하되, 문자열 내부의 세미콜론은 제외
    $queries = array();
    $current_query = '';
    $in_string = false;
    $string_char = '';
    
    for ($i = 0; $i < strlen($sql_content); $i++) {
        $char = $sql_content[$i];
        
        if (!$in_string) {
            if ($char === "'" || $char === '"') {
                $in_string = true;
                $string_char = $char;
            } elseif ($char === ';') {
                $queries[] = $current_query;
                $current_query = '';
                continue;
            }
        } else {
            if ($char === $string_char && ($i === 0 || $sql_content[$i-1] !== '\\')) {
                $in_string = false;
            }
        }
        
        $current_query .= $char;
    }
    
    if (!empty(trim($current_query))) {
        $queries[] = $current_query;
    }
    
    return $queries;
}

/**
 * 백업 파일 목록 조회
 */
function get_backup_files() {
    $backup_dir = G5_DATA_PATH . '/backup/';
    $files = array();
    
    if (!file_exists($backup_dir)) {
        return $files;
    }
    
    $handle = opendir($backup_dir);
    if ($handle) {
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..' && substr($file, -4) === '.sql') {
                $file_path = $backup_dir . $file;
                $files[] = array(
                    'name' => $file,
                    'size' => filesize($file_path),
                    'date' => filemtime($file_path)
                );
            }
        }
        closedir($handle);
    }
    
    // 날짜순 정렬 (최신순)
    usort($files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
    
    return $files;
}

$backup_files = get_backup_files();

$page_title = '데이터베이스 복원';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - 희망씨</title>
</head>
<body>

<div class="container">
    <h1>🔄 데이터베이스 복원 도구</h1>
    
<div class="local_desc01 local_desc">
    <p>
        <strong>데이터베이스 복원 도구</strong><br>
        백업된 SQL 파일을 사용하여 데이터베이스를 복원할 수 있습니다.<br>
        <span style="color: #dc3545; font-weight: bold;">⚠️ 주의: 복원 시 기존 데이터가 덮어씌워질 수 있습니다!</span>
    </p>
</div>

<div class="tbl_head01 tbl_wrap">
    <h2>서버 백업 파일 목록</h2>
    <?php if (count($backup_files) > 0): ?>
    <table>
        <colgroup>
            <col class="grid_3">
            <col class="grid_2">
            <col class="grid_2">
            <col class="grid_1">
        </colgroup>
        <thead>
            <tr>
                <th scope="col">파일명</th>
                <th scope="col">크기</th>
                <th scope="col">백업 일시</th>
                <th scope="col">다운로드</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($backup_files as $file): ?>
            <tr>
                <td><?php echo htmlspecialchars($file['name']); ?></td>
                <td><?php echo number_format($file['size'] / 1024, 1); ?> KB</td>
                <td><?php echo date('Y-m-d H:i:s', $file['date']); ?></td>
                <td>
                    <a href="./db_backup_download.php?file=<?php echo urlencode($file['name']); ?>" class="btn btn_03">다운로드</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="empty_list">서버에 저장된 백업 파일이 없습니다.</p>
    <?php endif; ?>
</div>

<div class="local_desc02 local_desc">
    <h3>복원 시 주의사항</h3>
    <ul>
        <li><strong>복원 전 반드시 현재 데이터베이스를 백업하세요!</strong></li>
        <li>복원 중에는 사이트 이용을 완전히 중단해 주세요.</li>
        <li>복원 과정에서 기존 데이터가 모두 삭제될 수 있습니다.</li>
        <li>SQL 파일만 업로드 가능하며, 최대 50MB까지 지원합니다.</li>
        <li>복원 후에는 사이트가 정상 작동하는지 확인하세요.</li>
        <li>복원 실패 시 즉시 이전 백업으로 재복원하세요.</li>
    </ul>
</div>

<form name="restore_form" method="post" enctype="multipart/form-data" autocomplete="off">
    <?php echo get_token(); ?>
    <input type="hidden" name="action" value="restore">
    
    <div class="tbl_frm01 tbl_wrap">
        <h2>백업 파일 업로드 및 복원</h2>
        <table>
            <colgroup>
                <col class="grid_2">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="backup_file">백업 파일 선택</label></th>
                    <td>
                        <input type="file" name="backup_file" id="backup_file" accept=".sql" required>
                        <div class="file_help">
                            SQL 파일만 업로드 가능 (최대 50MB)
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="데이터베이스 복원 시작" class="btn_submit btn_danger" 
               onclick="return confirm('⚠️ 경고 ⚠️\\n\\n데이터베이스 복원을 진행하시겠습니까?\\n\\n※ 복원 시 기존 데이터가 모두 삭제될 수 있습니다!\\n※ 복원 전 현재 데이터베이스를 백업했는지 확인하세요!\\n\\n정말로 진행하시겠습니까?');">
        <a href="./db_backup.php" class="btn_cancel">백업 페이지로</a>
        <a href="./db_manager.php" class="btn_cancel">관리 메인으로</a>
    </div>
</form>

<style>
/* 루트 페이지용 스타일 - 그누보드 기본 스타일과 호환 */
body {
    font-family: "맑은 고딕", "Malgun Gothic", "돋움", Dotum, Arial, sans-serif;
    font-size: 12px;
    color: #333;
    margin: 0;
    padding: 20px;
    background: #f8f9fa;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

h1 {
    color: #333;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.local_desc01 {
    background: #e7f3ff;
    border: 1px solid #b3d7ff;
    border-radius: 5px;
    padding: 15px;
    margin: 20px 0;
    line-height: 1.5;
}

.local_desc02 {
    background: #ffe6e6;
    border: 1px solid #ffb3b3;
    border-radius: 5px;
    padding: 15px;
    margin: 20px 0;
}

.local_desc02 h3 {
    color: #cc0000;
    margin-bottom: 10px;
    font-size: 14px;
}

.local_desc02 ul {
    margin: 0;
    padding-left: 20px;
}

.local_desc02 li {
    margin-bottom: 5px;
    color: #990000;
}

.tbl_wrap {
    margin: 20px 0;
}

.tbl_wrap h2 {
    background: #f1f1f1;
    padding: 10px;
    margin: 0 0 10px 0;
    font-size: 14px;
    border: 1px solid #ddd;
}

table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #ddd;
}

table th,
table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

table th {
    background: #f8f9fa;
    font-weight: bold;
}

.btn_confirm {
    text-align: center;
    margin: 30px 0;
}

.btn_submit {
    background-color: #428bca;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 13px;
    min-height: 40px;
    margin-right: 10px;
}

.btn_submit:hover {
    background-color: #357ebd;
}

.btn_danger {
    background-color: #d9534f !important;
}

.btn_danger:hover {
    background-color: #c9302c !important;
}

.btn_cancel {
    background-color: #999;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 3px;
    display: inline-block;
    font-size: 13px;
    min-height: 40px;
    line-height: 1.5;
}

.btn_cancel:hover {
    background-color: #777;
    color: white;
}

.file_help {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
}

.empty_list {
    text-align: center;
    padding: 30px;
    color: #999;
}

.btn_03 {
    background-color: #5cb85c;
    color: white;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 11px;
}

.btn_03:hover {
    background-color: #449d44;
    color: white;
}
</style>

<script>
// 파일 선택 시 검증
document.getElementById('backup_file').onchange = function() {
    var file = this.files[0];
    if (file) {
        // 파일 확장자 검증
        var fileName = file.name.toLowerCase();
        if (!fileName.endsWith('.sql')) {
            alert('SQL 파일만 업로드 가능합니다.');
            this.value = '';
            return;
        }
        
        // 파일 크기 검증 (50MB)
        if (file.size > 50 * 1024 * 1024) {
            alert('파일 크기가 너무 큽니다. (최대 50MB)');
            this.value = '';
            return;
        }
        
        // 파일 정보 표시
        document.querySelector('.file_help').innerHTML = 
            '선택된 파일: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    }
};

// 폼 제출 전 추가 확인
document.restore_form.onsubmit = function() {
    var fileInput = document.getElementById('backup_file');
    if (!fileInput.files[0]) {
        alert('백업 파일을 선택해주세요.');
        return false;
    }
    
    // 복원 진행 중 메시지 표시
    var submit_btn = document.querySelector('.btn_submit');
    submit_btn.disabled = true;
    submit_btn.value = '복원 진행중... 절대 페이지를 닫지 마세요!';
    
    return true;
};
</script>

</div> <!-- container -->

</body>
</html>