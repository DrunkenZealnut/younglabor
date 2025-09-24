<?php
/**
 * Dashboard Controller - MVC 구조
 * 대시보드 컨트롤러
 */

require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController
{
    private $model;
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new DashboardModel($pdo);
    }
    
    /**
     * 대시보드 메인 페이지
     */
    public function index()
    {
        // 세션 검증
        if (!$this->validateSession()) {
            $this->redirectToLogin();
            return;
        }
        
        try {
            // 설정 처리
            $recent_posts_limit = $_GET['posts_limit'] ?? 5;
            $this->model->setRecentPostsLimit($recent_posts_limit);
            
            // 데이터 가져오기
            $statistics = $this->model->getStatistics();
            
            // 뷰 데이터 준비
            $viewData = [
                'title' => '대시보드',
                'active_menu' => 'index',
                'statistics' => $statistics,
                'recent_posts_limit' => $this->model->getRecentPostsLimit()
            ];
            
            // 디버그: 데이터 확인 (뷰에서 사용할 수 있도록 뷰 데이터에 추가)
            if (isset($_GET['debug'])) {
                $viewData['debug_info'] = [
                    'statistics_keys' => array_keys($statistics),
                    'recent_posts_count' => count($statistics['recent_posts']),
                    'view_data_keys' => array_keys($viewData),
                    'controller_timestamp' => date('Y-m-d H:i:s')
                ];
            }
            
            // 뷰 렌더링
            $this->renderView('dashboard/index', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('Dashboard rendering failed', $e);
        }
    }
    
    /**
     * AJAX - 방문자 통계 업데이트
     */
    public function getVisitorStats()
    {
        header('Content-Type: application/json');
        
        if (!$this->validateSession()) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $statistics = $this->model->getStatistics();
        echo json_encode($statistics['visitor_stats']);
    }
    
    /**
     * AJAX - 최근 게시글 업데이트
     */
    public function getRecentPosts()
    {
        header('Content-Type: application/json');
        
        if (!$this->validateSession()) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $limit = $_GET['limit'] ?? 5;
        $this->model->setRecentPostsLimit($limit);
        
        $statistics = $this->model->getStatistics();
        echo json_encode([
            'recent_posts' => $statistics['recent_posts'],
            'limit' => $this->model->getRecentPostsLimit()
        ]);
    }
    
    /**
     * 세션 검증
     */
    private function validateSession()
    {
        return isValidAdminSession();
    }
    
    /**
     * 로그인 페이지로 리디렉션
     */
    private function redirectToLogin()
    {
        logSecurityEvent('UNAUTHORIZED_ACCESS', 'Invalid session access to dashboard');
        destroyAdminSession();
        header("Location: login.php?expired=1");
        exit;
    }
    
    /**
     * 뷰 렌더링
     */
    private function renderView($view, $data = [])
    {
        // 뷰에서 사용할 변수들을 명시적으로 설정
        $title = $data['title'] ?? '대시보드';
        $active_menu = $data['active_menu'] ?? 'index';
        $statistics = $data['statistics'] ?? [];
        $recent_posts_limit = $data['recent_posts_limit'] ?? 5;
        
        // 디버그 정보
        $debug_info = isset($_GET['debug']) ? $data['debug_info'] ?? [] : null;
        
        // 컨텐츠 생성
        ob_start();
        $view_file = __DIR__ . "/../../views/{$view}.php";
        
        if (file_exists($view_file)) {
            // 뷰에서 변수들을 사용할 수 있도록 include
            include $view_file;
        } else {
            echo "<div class='alert alert-danger'>뷰 파일을 찾을 수 없습니다: $view_file</div>";
        }
        
        $content = ob_get_clean();
        
        // 디버그: 컨텐츠 확인
        if (isset($_GET['debug']) && empty($content)) {
            $content = "<div class='alert alert-warning'>뷰에서 생성된 컨텐츠가 비어있습니다. 뷰 파일: $view_file</div>";
        }
        
        // 레이아웃 렌더링
        t_render_layout('sidebar', [
            'title' => $title,
            'content' => $content,
            'breadcrumb' => [
                ['title' => '관리자', 'url' => '']
            ]
        ]);
    }
    
    /**
     * 오류 처리
     */
    private function handleError($message, $exception = null)
    {
        logSecurityEvent('DASHBOARD_ERROR', $message . ($exception ? ': ' . $exception->getMessage() : ''));
        
        // 개발 환경에서는 상세 오류 표시
        if (defined('DEBUG') && DEBUG) {
            throw $exception ?: new Exception($message);
        }
        
        // 프로덕션에서는 일반적인 오류 메시지
        $this->renderView('error', [
            'title' => '오류',
            'message' => '일시적인 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'
        ]);
    }
}
?>