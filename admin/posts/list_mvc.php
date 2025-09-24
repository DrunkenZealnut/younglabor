<?php
/**
 * 게시글 관리 - MVC 패턴 적용 버전
 */

// MVC Bootstrap으로 애플리케이션 실행
require_once '../mvc/bootstrap.php';

// 액션 파라미터 처리
$action = $_GET['action'] ?? 'index';
$params = [];

// ID 파라미터가 필요한 액션들
if (in_array($action, ['show', 'edit', 'delete']) && isset($_GET['id'])) {
    $params[] = $_GET['id'];
}

try {
    // MVC 애플리케이션 실행
    runMVCApplication(PostController::class, $action, $params);
    
} catch (Exception $e) {
    // 개발 환경에서 에러 표시
    if (isDevelopmentEnvironment()) {
        echo "<div class='alert alert-danger'>";
        echo "<h4>MVC Application Error</h4>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre><strong>Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        // 프로덕션 환경에서는 간단한 메시지
        echo "<div class='alert alert-danger'>게시글을 불러올 수 없습니다.</div>";
    }
}
?>