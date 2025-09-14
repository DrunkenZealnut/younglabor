<?php
/**
 * AJAX Endpoint - Dashboard Visitor Statistics
 * 대시보드 방문자 통계 데이터 업데이트
 */

require_once '../bootstrap.php';
require_once '../mvc/controllers/DashboardController.php';

// JSON 응답 헤더 설정
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // 세션 검증
    if (!isValidAdminSession()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }
    
    // DashboardController 인스턴스 생성
    $controller = new DashboardController($pdo);
    
    // 방문자 통계 AJAX 요청 처리
    $controller->getVisitorStats();
    
} catch (Exception $e) {
    // 오류 로깅
    logSecurityEvent('DASHBOARD_AJAX_ERROR', 'Visitor stats AJAX error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error occurred',
        'message' => 'Failed to load visitor statistics data'
    ]);
}
?>