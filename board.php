<?php
/**
 * 게시판 라우터
 * URL: /board/list/{id}/ -> board.php?id={id}
 */

// 기본 설정 로드
if (!defined('HOPEC_BASE_PATH')) {
    define('HOPEC_BASE_PATH', __DIR__);
}

// 게시판 ID 확인
$board_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($board_id <= 0) {
    http_response_code(404);
    die('게시판 ID가 올바르지 않습니다.');
}

// 환경 변수 로드
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// 데이터베이스 연결
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'hopec';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('데이터베이스 연결 실패');
}

// 게시판 정보 조회
try {
    $stmt = $pdo->prepare("SELECT * FROM hopec_boards WHERE id = ? AND is_active = 1");
    $stmt->execute([$board_id]);
    $board = $stmt->fetch();
    
    if (!$board) {
        http_response_code(404);
        die('존재하지 않는 게시판입니다.');
    }
} catch (PDOException $e) {
    http_response_code(500);
    die('게시판 정보 조회 실패');
}

// 게시판 타입에 따른 라우팅
$board_code = $board['board_code'];
$board_name = $board['board_name'];

// 게시판별 매핑 (기존 시스템과 호환성 유지)
$board_routes = [
    1 => '/about/finance.php',           // 재정보고
    2 => '/community/notices.php',       // 공지사항
    3 => '/community/press.php',         // 언론보도
    4 => '/community/newsletter.php',    // 소식지
    5 => '/community/gallery.php',       // 갤러리
    6 => '/community/resources.php',     // 자료실
    7 => '/community/nepal.php',         // 네팔나눔연대여행
];

// 해당 게시판 라우트가 있는지 확인하고 리다이렉트
if (isset($board_routes[$board_id])) {
    $target_url = $board_routes[$board_id];
    
    // 파일이 존재하는지 확인
    $file_path = HOPEC_BASE_PATH . $target_url;
    if (file_exists($file_path)) {
        // 영구 리다이렉트 (301) - SEO를 위해
        header("Location: $target_url", true, 301);
        exit;
    }
}

// 기본 게시판 템플릿으로 라우팅 (board_templates 사용)
$template_path = HOPEC_BASE_PATH . '/board_templates/list_all_boards.php';
if (file_exists($template_path)) {
    // 게시판 정보를 전역 변수로 설정
    $currentBoard = $board;
    $currentBoardId = $board_id;
    
    include $template_path;
} else {
    // 최종 폴백 - 간단한 게시판 목록 표시
    ?>
    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($board_name) ?> - 희망씨</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="h3 mb-0"><?= htmlspecialchars($board_name) ?></h1>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">게시판 ID: <?= $board_id ?></p>
                            <p class="text-muted">게시판 코드: <?= htmlspecialchars($board_code) ?></p>
                            <p>이 게시판은 현재 구현 중입니다.</p>
                            <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>