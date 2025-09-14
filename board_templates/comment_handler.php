<?php
// 댓글 생성/삭제 처리기 (AJAX 전용)
// JSON만 반환하도록 출력 버퍼/에러 핸들러를 구성
ob_start();
set_error_handler(function($severity, $message, $file, $line){
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function send_json($payload, int $status = 200) {
    while (ob_get_level() > 0) { @ob_end_clean(); }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// DB 연결 보장: 상위에서 $pdo를 넘기지 않더라도 자체적으로 연결 시도
    if (!isset($pdo) || !($pdo instanceof PDO)) {
    $rootCommon = __DIR__ . '/../_common.php';
    if (file_exists($rootCommon)) { include_once $rootCommon; }
    try {
        if (defined('G5_MYSQL_HOST') && defined('G5_MYSQL_DB')) {
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
        }
    } catch (Throwable $e) {
        send_json(['success' => false, 'error' => '데이터베이스 연결 실패'], 500);
    }
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        send_json(['success' => false, 'error' => '환경 설정 누락으로 DB 연결 불가'], 500);
    }
}

// 세션 보장 (common.php가 이미 시작했다면 패스)
if (session_status() === PHP_SESSION_NONE) { @session_start(); }

// CSRF 검증 함수 폴백
if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        if (empty($token)) return false;
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        send_json(['success' => false, 'error' => '잘못된 요청입니다. (POST만 허용)'], 405);
    }

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        send_json(['success' => false, 'error' => '유효하지 않은 요청입니다. (CSRF)'], 400);
    }

    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['create', 'delete'], true)) {
        send_json(['success' => false, 'error' => '지원하지 않는 동작입니다.'], 400);
    }

    // 현재 사용자 정보 (두 가지 세션 구조 지원)
    $current_user = null;
    if (isset($_SESSION['user_id'])) {
        $current_user = [
            'user_id' => (int)$_SESSION['user_id'],
            'username' => (string)($_SESSION['username'] ?? ''),
            'role' => (string)($_SESSION['role'] ?? 'USER')
        ];
    } elseif (isset($_SESSION['id'])) {
        $current_user = [
            'user_id' => (int)$_SESSION['id'],
            'username' => (string)($_SESSION['username'] ?? ''),
            'role' => (string)($_SESSION['role'] ?? 'USER')
        ];
    }

    if ($action === 'create') {
        // 간단한 IP 기반 레이트리밋: 1분 5회
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $rlKey = 'comments_create_' . preg_replace('/[^0-9a-f:\.]/i', '_', $clientIp);
        $rlDir = dirname(__DIR__) . '/data/tmp';
        if (!is_dir($rlDir)) { @mkdir($rlDir, 0755, true); }
        $rlFile = $rlDir . '/' . $rlKey . '.json';
        $now = time();
        $window = 60; $limit = 5;
        $bucket = ['t'=>$now, 'hits'=>0];
        if (is_file($rlFile)) {
            $json = @file_get_contents($rlFile);
            $data = $json ? json_decode($json, true) : null;
            if (is_array($data) && isset($data['t'], $data['hits'])) {
                if (($now - (int)$data['t']) < $window) {
                    $bucket = $data;
                }
            }
        }
        if (($now - (int)$bucket['t']) >= $window) { $bucket = ['t'=>$now, 'hits'=>0]; }
        if ((int)$bucket['hits'] >= $limit) {
            send_json(['success'=>false,'error'=>'요청이 너무 잦습니다. 잠시 후 다시 시도해주세요.'], 429);
        }
        $bucket['hits'] = (int)$bucket['hits'] + 1;
        @file_put_contents($rlFile, json_encode($bucket), LOCK_EX);
        $post_id = (int)($_POST['post_id'] ?? 0);
        $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $content = trim((string)($_POST['content'] ?? ''));
        $author_name = '';
        $bo_table = isset($_POST['bo_table']) ? preg_replace('/[^A-Za-z0-9_]/', '', (string)$_POST['bo_table']) : '';

        if ($post_id <= 0 || $content === '') {
            throw new Exception('필수 항목이 누락되었습니다.');
        }

        // 내용 길이 및 XSS 제거 (문자열만 허용)
        $content = strip_tags($content);
        if (mb_strlen($content, 'UTF-8') > 1000) {
            $content = mb_substr($content, 0, 1000, 'UTF-8');
        }

        // 로그인이 없어도 허용. 다만 GNUBOARD 연동 시 mb_id는 비워두고 wr_name으로 기록
        $gn_mb_id = '';
        if (!empty($_SESSION['ss_mb_id'])) { $gn_mb_id = (string)$_SESSION['ss_mb_id']; }

        $user_id = null;
        if ($current_user) {
            $user_id = $current_user['user_id'];
            $author_name = $current_user['username'] ?: '익명';
        } else {
            $author_name = trim((string)($_POST['author_name'] ?? ''));
            if ($author_name === '') { $author_name = '익명'; }
        }
        if (mb_strlen($author_name) > 100) { $author_name = mb_substr($author_name, 0, 100); }

        $comment_id = 0;
        if ($bo_table) {
            @include_once __DIR__ . '/comments_drivers/hopec_posts.php';
            if (function_exists('comments_driver_gn_create')) {
                try {
                    $comment_id = comments_driver_gn_create($pdo, [
                        'post_id' => $post_id,
                        'content' => $content,
                        'author_name' => $author_name,
                        'user_id' => $user_id,
                        'parent_id' => $parent_id,
                    ], ['bo_table' => $bo_table, 'mb_id' => $gn_mb_id]);
                } catch (Throwable $e) {
                    send_json(['success' => false, 'error' => '저장 실패: '.$e->getMessage()], 500);
                }
            }
        } else {
            @include_once __DIR__ . '/comments_drivers/internal.php';
            if (function_exists('comments_driver_internal_create')) {
                $comment_id = comments_driver_internal_create($pdo, [
                    'post_id' => $post_id,
                    'content' => $content,
                    'author_name' => $author_name,
                    'user_id' => $user_id,
                    'parent_id' => $parent_id,
                ]);
            }
        }

        send_json(['success' => true, 'comment_id' => $comment_id]);
    }

    if ($action === 'delete') {
        $comment_id = (int)($_POST['comment_id'] ?? 0);
        if ($comment_id <= 0) { throw new Exception('잘못된 댓글 ID입니다.'); }

        $s = $pdo->prepare("SELECT comment_id, post_id, user_id, author_name FROM board_comments WHERE comment_id = ? AND is_active = 1");
        $s->execute([$comment_id]);
        $comment = $s->fetch();
        if (!$comment) { throw new Exception('댓글을 찾을 수 없습니다.'); }

        $can_delete = false;
        if ($current_user) {
            if (($current_user['role'] ?? 'USER') === 'ADMIN') { $can_delete = true; }
            if ((int)$comment['user_id'] === (int)$current_user['user_id']) { $can_delete = true; }
            if ($comment['author_name'] === ($current_user['username'] ?? '')) { $can_delete = true; }
        }
        if (!$can_delete) { throw new Exception('댓글 삭제 권한이 없습니다.'); }

        $d = $pdo->prepare("UPDATE board_comments SET is_active = 0, updated_at = NOW() WHERE comment_id = ?");
        $d->execute([$comment_id]);
        send_json(['success' => true]);
    }

} catch (Throwable $e) {
    send_json(['success' => false, 'error' => '서버 오류: '.$e->getMessage()], 500);
}
?>

