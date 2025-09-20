<?php
/**
 * 데이터베이스 관리 메인 페이지 (PHP 5.5 호환)
 * 그누보드5 기반 희망씨 웹사이트
 */

// 데이터베이스 설정 (직접 연결)
define('G5_MYSQL_HOST', '127.0.0.1');
define('G5_MYSQL_USER', 'hopec');
define('G5_MYSQL_PASSWORD', 'hopec2!@');
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

// 로그아웃 처리
if (isset($_GET['logout'])) {
    unset($_SESSION['db_admin_logged']);
    session_destroy();
    header('Location: db_manager.php');
    exit;
}

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
        <title>데이터베이스 관리 도구 - 관리자 인증</title>
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
            .password_hint { background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 12px; color: #856404; }
        </style>
    </head>
    <body>
        <div class="login_box">
            <h2>🛠️ 데이터베이스 관리 도구</h2>
            <div class="info">
                <strong>희망씨 웹사이트 데이터베이스 관리</strong><br>
                안전한 백업을 위한 관리자 도구입니다.
            </div>
            <div class="password_hint">
                <strong>💡 관리자 비밀번호:</strong> hopec2024!
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

/**
 * 시스템 정보 조회
 */
function get_system_info() {
    $info = array();
    
    // PHP 정보
    $info['php_version'] = PHP_VERSION;
    $info['php_sapi'] = php_sapi_name();
    
    // MySQL 정보
    $connection = new mysqli(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB, 3306);
    if (!$connection->connect_error) {
        $info['mysql_version'] = $connection->server_info;
        $connection->close();
    } else {
        $info['mysql_version'] = '연결 실패';
    }
    
    // 서버 정보
    $info['server_software'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'PHP Built-in Server';
    $info['document_root'] = $_SERVER['DOCUMENT_ROOT'];
    
    // 디스크 공간 정보
    $info['disk_free'] = disk_free_space('./');
    $info['disk_total'] = disk_total_space('./');
    
    return $info;
}

/**
 * 백업 파일 목록 조회
 */
function get_recent_backups($limit = 5) {
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
    
    return array_slice($files, 0, $limit);
}

$system_info = get_system_info();
$recent_backups = get_recent_backups();

$page_title = '데이터베이스 관리';
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
    <h1>🛠️ 데이터베이스 관리 도구</h1>
    
    <div class="local_desc01">
        <p>
            <strong>희망씨 웹사이트 데이터베이스 관리 도구</strong><br>
            PHP 버전 업데이트 전 안전한 데이터베이스 백업을 위한 도구입니다.
        </p>
    </div>

    <!-- 주요 기능 -->
    <div class="function_cards">
        <div class="card backup_card">
            <div class="card_icon">🗃️</div>
            <h3>데이터베이스 백업</h3>
            <p>전체 데이터베이스를 안전하게 백업합니다.<br>백업 파일은 자동으로 다운로드됩니다.</p>
            <a href="./db_backup.php" class="btn btn_primary">백업 시작</a>
        </div>
    </div>

    <!-- 시스템 정보 -->
    <div class="tbl_wrap">
        <h2>📊 시스템 정보</h2>
        <table class="system_info_table">
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 70%;">
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row">PHP 버전</th>
                    <td>
                        <?php echo $system_info['php_version']; ?>
                        <?php if (version_compare($system_info['php_version'], '7.0.0', '<')): ?>
                        <span class="version_warning">⚠️ 구버전</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">MySQL 버전</th>
                    <td><?php echo $system_info['mysql_version']; ?></td>
                </tr>
                <tr>
                    <th scope="row">웹서버</th>
                    <td><?php echo $system_info['server_software']; ?></td>
                </tr>
                <tr>
                    <th scope="row">데이터베이스명</th>
                    <td><?php echo G5_MYSQL_DB; ?></td>
                </tr>
                <tr>
                    <th scope="row">남은 디스크 공간</th>
                    <td>
                        <?php echo number_format($system_info['disk_free'] / 1024 / 1024 / 1024, 2); ?> GB 
                        / <?php echo number_format($system_info['disk_total'] / 1024 / 1024 / 1024, 2); ?> GB
                    </td>
                </tr>
                <tr>
                    <th scope="row">현재 시간</th>
                    <td><?php echo date('Y-m-d H:i:s'); ?> (한국시간)</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 최근 백업 파일 -->
    <div class="tbl_wrap">
        <h2>📁 최근 백업 파일 (최대 5개)</h2>
        <?php if (count($recent_backups) > 0): ?>
        <table>
            <colgroup>
                <col style="width: 40%;">
                <col style="width: 20%;">
                <col style="width: 25%;">
                <col style="width: 15%;">
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
                <?php foreach ($recent_backups as $file): ?>
                <tr>
                    <td><?php echo htmlspecialchars($file['name']); ?></td>
                    <td><?php echo number_format($file['size'] / 1024, 1); ?> KB</td>
                    <td><?php echo date('Y-m-d H:i', $file['date']); ?></td>
                    <td>
                        <a href="./db_backup_download.php?file=<?php echo urlencode($file['name']); ?>" class="btn_03">다운로드</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="empty_list">백업된 파일이 없습니다. 먼저 백업을 수행해주세요.</p>
        <?php endif; ?>
    </div>

    <!-- 안전 가이드 -->
    <div class="local_desc02">
        <h3>🔒 안전한 데이터베이스 관리 가이드</h3>
        <div class="guide_grid">
            <div class="guide_item">
                <strong>백업 전 확인사항</strong>
                <ul>
                    <li>사이트 이용이 적은 시간대 선택</li>
                    <li>충분한 디스크 공간 확보</li>
                    <li>백업 중 사이트 접근 제한</li>
                </ul>
            </div>
            <div class="guide_item">
                <strong>보안 관리</strong>
                <ul>
                    <li>백업 파일 외부 보관</li>
                    <li>정기적인 백업 수행</li>
                    <li>오래된 백업 파일 정리</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 링크 버튼 -->
    <div class="btn_confirm">
        <a href="?logout=1" class="btn_cancel" onclick="return confirm('로그아웃 하시겠습니까?')">로그아웃</a>
        <a href="./" class="btn_cancel">사이트 메인으로</a>
    </div>
</div>

<style>
/* 메인 페이지 전용 스타일 */
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
    text-align: center;
}

.local_desc01 {
    background: #e7f3ff;
    border: 1px solid #b3d7ff;
    border-radius: 5px;
    padding: 15px;
    margin: 20px 0;
    line-height: 1.5;
    text-align: center;
}

.local_desc02 {
    background: #fff8dc;
    border: 1px solid #f0e68c;
    border-radius: 5px;
    padding: 20px;
    margin: 30px 0;
}

.local_desc02 h3 {
    color: #b8860b;
    margin-bottom: 15px;
    font-size: 14px;
    text-align: center;
}

.function_cards {
    display: flex;
    gap: 20px;
    margin: 30px 0;
    justify-content: center;
}

.card {
    flex: 1;
    max-width: 300px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.card_icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.card h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 16px;
}

.card p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.4;
    min-height: 40px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    font-weight: bold;
    transition: background-color 0.2s;
}

.btn_primary {
    background-color: #428bca;
    color: white;
}

.btn_primary:hover {
    background-color: #357ebd;
    color: white;
}

.btn_danger {
    background-color: #d9534f;
    color: white;
}

.btn_danger:hover {
    background-color: #c9302c;
    color: white;
}

.tbl_wrap {
    margin: 30px 0;
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

.version_warning {
    color: #d9534f;
    font-weight: bold;
    font-size: 11px;
}

.empty_list {
    text-align: center;
    padding: 30px;
    color: #999;
}

.table_footer {
    padding: 10px;
    text-align: right;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-top: none;
}

.table_footer a {
    color: #428bca;
    text-decoration: none;
    font-size: 12px;
}

.guide_grid {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.guide_item {
    flex: 1;
    min-width: 200px;
}

.guide_item strong {
    display: block;
    color: #8b4513;
    margin-bottom: 8px;
    font-size: 13px;
}

.guide_item ul {
    margin: 0;
    padding-left: 15px;
}

.guide_item li {
    margin-bottom: 3px;
    color: #8b4513;
    font-size: 11px;
}

.btn_confirm {
    text-align: center;
    margin: 30px 0;
}

.btn_cancel {
    background-color: #999;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 3px;
    display: inline-block;
    font-size: 13px;
    margin: 0 5px;
}

.btn_cancel:hover {
    background-color: #777;
    color: white;
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

/* 반응형 */
@media (max-width: 768px) {
    .function_cards {
        flex-direction: column;
    }
    
    .guide_grid {
        flex-direction: column;
    }
    
    .container {
        padding: 20px;
        margin: 10px;
    }
}
</style>

</body>
</html>