<?php
/**
 * 직접 대시보드 - MVC 없이 직접 렌더링
 */

require_once 'bootstrap.php';

// 세션 검증
if (!isValidAdminSession()) {
    logSecurityEvent('UNAUTHORIZED_ACCESS', 'Invalid session access to dashboard');
    destroyAdminSession();
    header("Location: login.php?expired=1");
    exit;
}

// 모델 직접 사용
require_once 'mvc/models/DashboardModel.php';
$model = new DashboardModel($pdo);
$statistics = $model->getStatistics();
$recent_posts_limit = $model->getRecentPostsLimit();

// 변수 설정
$title = '대시보드';
$page_title = '관리자 대시보드';

// 컨텐츠 직접 생성
ob_start();
include 'views/dashboard/index.php';
$content = ob_get_clean();

// 레이아웃 렌더링
t_render_layout('sidebar', [
    'title' => $title,
    'content' => $content,
    'breadcrumb' => [
        ['title' => '관리자', 'url' => '']
    ]
]);
?>