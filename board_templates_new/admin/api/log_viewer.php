<?php
/**
 * 로그 뷰어 API 엔드포인트
 */

header('Content-Type: application/json; charset=utf-8');

session_start();

// 기본 인증 체크
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 개발 환경에서는 자동 로그인
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
        strpos($_SERVER['HTTP_HOST'], '.local') !== false) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '인증이 필요합니다.']);
        exit;
    }
}

require_once __DIR__ . '/../includes/DatabaseSettingsManager.php';

use BoardTemplates\Admin\DatabaseSettingsManager;

try {
    $settingsManager = new DatabaseSettingsManager();
    
    // GET 요청 파라미터 처리
    $logType = $_GET['type'] ?? 'database';
    $limit = intval($_GET['limit'] ?? 100);
    $level = $_GET['level'] ?? 'all';
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $result = $settingsManager->getLogs($logType, $limit, $level, $startDate, $endDate);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($input['action'] === 'clear_logs') {
                $logType = $input['log_type'] ?? 'database';
                $result = $settingsManager->clearLogs($logType);
            } else {
                throw new Exception('지원되지 않는 액션입니다.');
            }
            break;
            
        default:
            throw new Exception('지원되지 않는 HTTP 메서드입니다.');
    }
    
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'],
        'data' => $result['data'] ?? null,
        'total_count' => $result['total_count'] ?? 0
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>