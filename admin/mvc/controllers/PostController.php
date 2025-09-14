<?php
/**
 * PostController - 게시글 관리 컨트롤러
 * MVC 패턴 적용
 */

require_once 'BaseController.php';
require_once dirname(__DIR__) . '/models/PostModel.php';

class PostController extends BaseController 
{
    private $postModel;
    
    public function __construct($pdo) 
    {
        parent::__construct($pdo);
        $this->postModel = new PostModel($pdo);
    }
    
    /**
     * 게시글 목록
     */
    public function index() 
    {
        $this->requireAdmin();
        
        try {
            // 페이지네이션 파라미터
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            
            // 검색 파라미터
            $keyword = $this->sanitizeInput($_GET['keyword'] ?? '');
            $searchType = $_GET['search_type'] ?? 'all';
            
            // 총 게시글 수
            $total = $this->postModel->count();
            $pagination = $this->calculatePagination($total, $page, $perPage);
            
            // 게시글 목록 조회
            if ($keyword) {
                $posts = $this->postModel->search($keyword, $searchType, $perPage, $pagination['offset']);
            } else {
                $posts = $this->postModel->findAll($perPage, $pagination['offset']);
            }
            
            // 뷰 렌더링
            $this->view->render('posts/list', [
                'posts' => $posts,
                'pagination' => $pagination,
                'keyword' => $keyword,
                'search_type' => $searchType,
                'page_title' => '게시글 관리',
                'active_menu' => 'posts'
            ]);
            
        } catch (Exception $e) {
            logSecurityEvent('CONTROLLER_ERROR', 'Post list error: ' . $e->getMessage());
            $this->showError('게시글 목록을 불러오는 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 게시글 작성 폼
     */
    public function create() 
    {
        $this->requireAdmin();
        
        $this->view->render('posts/create', [
            'page_title' => '게시글 작성',
            'active_menu' => 'posts'
        ]);
    }
    
    /**
     * 게시글 저장
     */
    public function store() 
    {
        $this->requireAdmin();
        $this->verifyCSRF();
        
        try {
            $data = $this->sanitizeInput([
                'board_id' => $_POST['board_id'] ?? '',
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'author' => $_POST['author'] ?? $_SESSION['admin_username'],
                'featured_image' => '',
                'status' => $_POST['status'] ?? 'published'
            ]);
            
            // 파일 업로드 처리
            if (!empty($_FILES['featured_image']['name'])) {
                $uploadPath = dirname(__DIR__, 2) . '/uploads/posts/' . date('Y/m');
                $data['featured_image'] = $this->handleFileUpload(
                    $_FILES['featured_image'], 
                    $uploadPath,
                    ['jpg', 'jpeg', 'png', 'gif']
                );
            }
            
            $postId = $this->postModel->create($data);
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess('게시글이 성공적으로 작성되었습니다.', ['id' => $postId]);
            } else {
                $this->redirect('list.php', '게시글이 성공적으로 작성되었습니다.');
            }
            
        } catch (Exception $e) {
            logSecurityEvent('CONTROLLER_ERROR', 'Post create error: ' . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->redirect('create.php', $e->getMessage(), 'error');
            }
        }
    }
    
    /**
     * 게시글 상세 보기
     */
    public function show($id) 
    {
        $this->requireAdmin();
        
        try {
            $post = $this->postModel->findById($id);
            
            if (!$post) {
                $this->showError('게시글을 찾을 수 없습니다.', 404);
            }
            
            // 조회수 증가
            $this->postModel->incrementViews($id);
            
            $this->view->render('posts/show', [
                'post' => $post,
                'page_title' => '게시글 상세',
                'active_menu' => 'posts'
            ]);
            
        } catch (Exception $e) {
            logSecurityEvent('CONTROLLER_ERROR', 'Post show error: ' . $e->getMessage());
            $this->showError('게시글을 불러오는 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 게시글 수정 폼
     */
    public function edit($id) 
    {
        $this->requireAdmin();
        
        try {
            $post = $this->postModel->findById($id);
            
            if (!$post) {
                $this->showError('게시글을 찾을 수 없습니다.', 404);
            }
            
            $this->view->render('posts/edit', [
                'post' => $post,
                'page_title' => '게시글 수정',
                'active_menu' => 'posts'
            ]);
            
        } catch (Exception $e) {
            logSecurityEvent('CONTROLLER_ERROR', 'Post edit form error: ' . $e->getMessage());
            $this->showError('게시글을 불러오는 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 게시글 업데이트
     */
    public function update($id) 
    {
        $this->requireAdmin();
        $this->verifyCSRF();
        
        try {
            $post = $this->postModel->findById($id);
            
            if (!$post) {
                throw new Exception('게시글을 찾을 수 없습니다.');
            }
            
            $data = $this->sanitizeInput([
                'board_id' => $_POST['board_id'] ?? $post['board_id'],
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'status' => $_POST['status'] ?? 'published'
            ]);
            
            // 파일 업로드 처리
            if (!empty($_FILES['featured_image']['name'])) {
                $uploadPath = dirname(__DIR__, 2) . '/uploads/posts/' . date('Y/m');
                $data['featured_image'] = $this->handleFileUpload(
                    $_FILES['featured_image'], 
                    $uploadPath,
                    ['jpg', 'jpeg', 'png', 'gif']
                );
                
                // 기존 이미지 삭제
                if ($post['featured_image']) {
                    $oldImagePath = dirname(__DIR__, 2) . '/uploads/posts/' . $post['featured_image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }
            
            $this->postModel->update($id, $data);
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess('게시글이 성공적으로 수정되었습니다.');
            } else {
                $this->redirect('list.php', '게시글이 성공적으로 수정되었습니다.');
            }
            
        } catch (Exception $e) {
            logSecurityEvent('CONTROLLER_ERROR', 'Post update error: ' . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->redirect("edit.php?id={$id}", $e->getMessage(), 'error');
            }
        }
    }
    
    /**
     * 게시글 삭제
     */
    public function delete($id) 
    {
        $this->requireAdmin();
        $this->verifyCSRF();
        
        try {
            $post = $this->postModel->findById($id);
            
            if (!$post) {
                throw new Exception('게시글을 찾을 수 없습니다.');
            }
            
            // 첨부 이미지 삭제
            if ($post['featured_image']) {
                $imagePath = dirname(__DIR__, 2) . '/uploads/posts/' . $post['featured_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $this->postModel->delete($id);
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess('게시글이 성공적으로 삭제되었습니다.');
            } else {
                $this->redirect('list.php', '게시글이 성공적으로 삭제되었습니다.');
            }
            
        } catch (Exception $e) {
            logSecurityEvent('CONTROLLER_ERROR', 'Post delete error: ' . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->redirect('list.php', $e->getMessage(), 'error');
            }
        }
    }
    
    /**
     * 일괄 삭제
     */
    public function bulkDelete() 
    {
        $this->requireAdmin();
        $this->verifyCSRF();
        
        try {
            $ids = $_POST['ids'] ?? [];
            
            if (empty($ids) || !is_array($ids)) {
                throw new Exception('삭제할 게시글을 선택해주세요.');
            }
            
            $deletedCount = 0;
            
            foreach ($ids as $id) {
                $post = $this->postModel->findById($id);
                if ($post) {
                    // 첨부 이미지 삭제
                    if ($post['featured_image']) {
                        $imagePath = dirname(__DIR__, 2) . '/uploads/posts/' . $post['featured_image'];
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                    
                    $this->postModel->delete($id);
                    $deletedCount++;
                }
            }
            
            $message = "{$deletedCount}개의 게시글이 성공적으로 삭제되었습니다.";
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess($message, ['deleted_count' => $deletedCount]);
            } else {
                $this->redirect('list.php', $message);
            }
            
        } catch (Exception $e) {
            logSecurityEvent('CONTROLLER_ERROR', 'Post bulk delete error: ' . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->redirect('list.php', $e->getMessage(), 'error');
            }
        }
    }
}