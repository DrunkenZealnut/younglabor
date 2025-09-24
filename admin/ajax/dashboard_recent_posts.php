<?php
/**
 * AJAX Endpoint - Dashboard Recent Posts
 * 대시보드 최근 게시글 데이터 업데이트
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
    
    // 최근 게시글 AJAX 요청 처리
    $controller->getRecentPosts();
    
} catch (Exception $e) {
    // 오류 로깅
    logSecurityEvent('DASHBOARD_AJAX_ERROR', 'Recent posts AJAX error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error occurred',
        'message' => 'Failed to load recent posts data'
    ]);
}
?>