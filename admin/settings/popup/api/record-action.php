<?php
/**
 * 팝업 액션 기록 API
 * 사용자의 팝업 상호작용을 기록합니다.
 */

header('Content-Type: application/json; charset=utf-8');

// CORS 헤더 (필요한 경우)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 기본 설정 로드
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../services/PopupManager.php';

try {
    // POST 요청만 허용
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST 요청만 허용됩니다.');
    }
    
    // JSON 데이터 파싱
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('유효하지 않은 JSON 데이터입니다.');
    }
    
    // 필수 파라미터 확인
    if (!isset($data['popup_id']) || !isset($data['action'])) {
        throw new Exception('popup_id와 action 파라미터가 필요합니다.');
    }
    
    $popupId = (int)$data['popup_id'];
    $action = $data['action'];
    
    // 유효한 액션인지 확인
    $validActions = ['viewed', 'closed', 'clicked', 'ignored'];
    if (!in_array($action, $validActions)) {
        throw new Exception('유효하지 않은 액션입니다.');
    }
    
    // 사용자 정보 수집
    $userIP = $_SERVER['REMOTE_ADDR'] ?? '';
    $sessionId = session_id() ?: 'unknown';
    $pageUrl = $data['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    
    // 세션 시작 (아직 시작되지 않은 경우)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        $sessionId = session_id();
    }
    
    // 팝업 매니저 초기화
    $popupManager = new PopupManager($pdo);
    
    // 액션 기록
    $result = $popupManager->recordPopupView($popupId, $userIP, $sessionId, $action, $pageUrl);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => '액션이 성공적으로 기록되었습니다.',
            'data' => [
                'popup_id' => $popupId,
                'action' => $action,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('액션 기록에 실패했습니다.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>