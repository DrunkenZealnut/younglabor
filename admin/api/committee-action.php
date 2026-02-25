<?php
/**
 * 동아리 신청 상태 변경 API
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
if (!hash_equals($_SESSION[ADMIN_CSRF_TOKEN_NAME] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$action = $input['action'] ?? '';
$note = trim($input['note'] ?? '');

$validActions = ['reviewed', 'accepted', 'rejected', 'pending'];
if ($id <= 0 || !in_array($action, $validActions, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        UPDATE committee_applications
        SET status = :status, admin_note = :note, reviewed_at = NOW(), updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':status' => $action,
        ':note' => $note ?: null,
        ':id' => $id,
    ]);

    $statusLabels = ['reviewed' => '검토됨', 'accepted' => '승인', 'rejected' => '거절', 'pending' => '대기중'];
    echo json_encode([
        'success' => true,
        'message' => "상태가 '{$statusLabels[$action]}'(으)로 변경되었습니다."
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    error_log('Committee action error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '처리 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
}
