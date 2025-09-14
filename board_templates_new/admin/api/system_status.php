<?php
/**
 * 시스템 상태 API 엔드포인트
 */

header('Content-Type: application/json; charset=utf-8');

// CORS 헤더 설정 (필요시)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

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
    $status = $settingsManager->getSystemStatus();
    
    echo json_encode([
        'success' => true,
        'status' => $status
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>