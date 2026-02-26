<?php
/**
 * 문의 상태 변경 API
 */
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// CSRF 검증
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessionToken = $_SESSION[ADMIN_CSRF_TOKEN_NAME] ?? '';
if ($sessionToken === '' || $csrfToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$action = $input['action'] ?? '';
$note = trim($input['note'] ?? '');

// 선택 삭제
if ($action === 'bulk_delete') {
    $ids = $input['ids'] ?? [];
    $ids = array_filter(array_map('intval', $ids), fn($v) => $v > 0);
    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '삭제할 항목을 선택해주세요.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    try {
        $db = Database::getInstance()->getConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("DELETE FROM inquiries WHERE id IN ({$placeholders})");
        $stmt->execute(array_values($ids));
        $deleted = $stmt->rowCount();
        echo json_encode(['success' => true, 'message' => "{$deleted}건이 삭제되었습니다."], JSON_UNESCAPED_UNICODE);
    } catch (\Throwable $e) {
        error_log('Contact bulk delete error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '삭제 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$validActions = ['new', 'processing', 'done', 'closed'];
if ($id <= 0 || !in_array($action, $validActions, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $updates = ['status = :status', 'updated_at = NOW()'];
    $params = [':status' => $action, ':id' => $id];

    if ($note !== '') {
        $updates[] = 'admin_reply = :note';
        $params[':note'] = $note;
    }

    if ($action === 'done') {
        $updates[] = 'replied_at = NOW()';
        $updates[] = 'replied_by = :replied_by';
        $params[':replied_by'] = $adminUser['username'];
    }

    $sql = "UPDATE inquiries SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '해당 문의를 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $statusLabels = ['new' => '미읽음', 'processing' => '처리중', 'done' => '답변완료', 'closed' => '보관'];
    echo json_encode([
        'success' => true,
        'message' => "상태가 '{$statusLabels[$action]}'(으)로 변경되었습니다."
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    error_log('Contact action error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '처리 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
}
