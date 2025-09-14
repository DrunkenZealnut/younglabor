<?php
/**
 * 테이블 검증 API 엔드포인트
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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST 요청만 지원됩니다.');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input['action'] !== 'validate_tables') {
        throw new Exception('잘못된 액션입니다.');
    }
    
    $settingsManager = new DatabaseSettingsManager();
    $result = $settingsManager->validateTablesExist();
    
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'],
        'all_exist' => $result['all_exist'] ?? false,
        'tables' => $result['tables'] ?? []
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>