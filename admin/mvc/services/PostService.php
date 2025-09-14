<?php
/**
 * PostService - 게시글 비즈니스 로직 레이어
 * 컨트롤러와 모델 사이의 비즈니스 로직 분리
 */

require_once dirname(__DIR__) . '/models/PostModel.php';

class PostService 
{
    private $postModel;
    private $fileService;
    
    public function __construct(PostModel $postModel, FileService $fileService = null) 
    {
        $this->postModel = $postModel;
        $this->fileService = $fileService ?? new FileService();
    }
    
    /**
     * 게시글 목록 조회 (검색 포함)
     */
    public function getPosts($page = 1, $perPage = 20, $keyword = '', $searchType = 'all') 
    {
        $offset = ($page - 1) * $perPage;
        
        if ($keyword) {
            $posts = $this->postModel->search($keyword, $searchType, $perPage, $offset);
            $total = $this->postModel->count(); // 검색 결과 카운트 구현 필요
        } else {
            $posts = $this->postModel->findAll($perPage, $offset);
            $total = $this->postModel->count();
        }
        
        return [
            'posts' => $posts,
            'total' => $total,
            'pagination' => $this->calculatePagination($total, $page, $perPage)
        ];
    }
    
    /**
     * 게시글 생성
     */
    public function createPost($data, $files = []) 
    {
        try {
            // 비즈니스 로직 검증
            $this->validatePostData($data);
            
            // 파일 업로드 처리
            if (isset($files['featured_image']) && !empty($files['featured_image']['name'])) {
                $uploadPath = $this->getUploadPath();
                $data['featured_image'] = $this->fileService->uploadImage(
                    $files['featured_image'], 
                    $uploadPath
                );
            }
            
            // 기본값 설정
            $data = $this->setDefaultValues($data);
            
            // 게시글 생성
            $postId = $this->postModel->create($data);
            
            // 생성 후 처리 (알림, 로깅 등)
            $this->afterCreate($postId, $data);
            
            return $postId;
            
        } catch (Exception $e) {
            // 파일 업로드된 것이 있으면 롤백
            if (isset($data['featured_image'])) {
                $this->fileService->deleteFile($this->getUploadPath() . '/' . $data['featured_image']);
            }
            throw $e;
        }
    }
    
    /**
     * 게시글 업데이트
     */
    public function updatePost($id, $data, $files = []) 
    {
        try {
            // 기존 게시글 확인
            $existingPost = $this->postModel->findById($id);
            if (!$existingPost) {
                throw new Exception('게시글을 찾을 수 없습니다.');
            }
            
            // 비즈니스 로직 검증
            $this->validatePostData($data);
            
            // 파일 업로드 처리
            $oldImage = null;
            if (isset($files['featured_image']) && !empty($files['featured_image']['name'])) {
                $uploadPath = $this->getUploadPath();
                $oldImage = $existingPost['featured_image'];
                $data['featured_image'] = $this->fileService->uploadImage(
                    $files['featured_image'], 
                    $uploadPath
                );
            }
            
            // 업데이트 실행
            $success = $this->postModel->update($id, $data);
            
            if ($success && $oldImage) {
                // 기존 이미지 삭제
                $this->fileService->deleteFile($this->getUploadPath() . '/' . $oldImage);
            }
            
            // 업데이트 후 처리
            $this->afterUpdate($id, $data, $existingPost);
            
            return $success;
            
        } catch (Exception $e) {
            // 새 파일이 업로드됐으면 롤백
            if (isset($data['featured_image'])) {
                $this->fileService->deleteFile($this->getUploadPath() . '/' . $data['featured_image']);
            }
            throw $e;
        }
    }
    
    /**
     * 게시글 삭제
     */
    public function deletePost($id) 
    {
        $post = $this->postModel->findById($id);
        if (!$post) {
            throw new Exception('게시글을 찾을 수 없습니다.');
        }
        
        // 삭제 전 검증
        $this->validateDelete($post);
        
        // 첨부 파일 삭제
        if ($post['featured_image']) {
            $this->fileService->deleteFile($this->getUploadPath() . '/' . $post['featured_image']);
        }
        
        // 게시글 삭제
        $success = $this->postModel->delete($id);
        
        // 삭제 후 처리
        $this->afterDelete($id, $post);
        
        return $success;
    }
    
    /**
     * 일괄 삭제
     */
    public function bulkDeletePosts($ids) 
    {
        if (empty($ids)) {
            throw new Exception('삭제할 게시글을 선택해주세요.');
        }
        
        $deletedCount = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            try {
                $this->deletePost($id);
                $deletedCount++;
            } catch (Exception $e) {
                $errors[] = "게시글 ID {$id}: " . $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            logSecurityEvent('BULK_DELETE_PARTIAL_FAILURE', implode('; ', $errors));
        }
        
        return [
            'deleted_count' => $deletedCount,
            'errors' => $errors
        ];
    }
    
    /**
     * 게시글 조회 (조회수 증가)
     */
    public function getPost($id, $incrementView = false) 
    {
        $post = $this->postModel->findById($id);
        
        if (!$post) {
            throw new Exception('게시글을 찾을 수 없습니다.');
        }
        
        if ($incrementView) {
            $this->postModel->incrementViews($id);
        }
        
        return $post;
    }
    
    /**
     * 최근 게시글 조회
     */
    public function getRecentPosts($limit = 5) 
    {
        return $this->postModel->findRecent($limit);
    }
    
    /**
     * 데이터 검증
     */
    protected function validatePostData($data) 
    {
        if (empty($data['title'])) {
            throw new InvalidArgumentException('제목을 입력해주세요.');
        }
        
        if (empty($data['content'])) {
            throw new InvalidArgumentException('내용을 입력해주세요.');
        }
        
        if (strlen($data['title']) > 200) {
            throw new InvalidArgumentException('제목은 200자를 초과할 수 없습니다.');
        }
        
        // 스팸 검사
        if ($this->isSpam($data)) {
            throw new Exception('스팸으로 감지된 내용입니다.');
        }
    }
    
    /**
     * 삭제 검증
     */
    protected function validateDelete($post) 
    {
        // 비즈니스 로직에 따른 삭제 제한
        // 예: 중요한 공지사항이나 고정 게시글 삭제 방지
        if (isset($post['is_pinned']) && $post['is_pinned']) {
            throw new Exception('고정 게시글은 삭제할 수 없습니다.');
        }
    }
    
    /**
     * 기본값 설정
     */
    protected function setDefaultValues($data) 
    {
        $defaults = [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'views' => 0,
            'status' => 'published',
            'author' => $_SESSION['admin_username'] ?? 'admin'
        ];
        
        return array_merge($defaults, $data);
    }
    
    /**
     * 페이지네이션 계산
     */
    protected function calculatePagination($total, $page, $perPage) 
    {
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        return [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'offset' => $offset,
            'total_items' => $total,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => max(1, $page - 1),
            'next_page' => min($totalPages, $page + 1)
        ];
    }
    
    /**
     * 업로드 경로 반환
     */
    protected function getUploadPath() 
    {
        return dirname(__DIR__, 2) . '/uploads/posts/' . date('Y/m');
    }
    
    /**
     * 스팸 검사
     */
    protected function isSpam($data) 
    {
        // 간단한 스팸 검사 로직
        $spamKeywords = ['스팸', '광고', '홍보'];
        $content = strtolower($data['title'] . ' ' . $data['content']);
        
        foreach ($spamKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 생성 후 처리
     */
    protected function afterCreate($postId, $data) 
    {
        // 로깅
        logSecurityEvent('POST_CREATED', "Post ID: {$postId}, Title: {$data['title']}");
        
        // 추가 비즈니스 로직 (알림, 인덱싱 등)
        // 예: 검색 엔진에 인덱싱 요청
        // 예: 구독자들에게 알림 발송
    }
    
    /**
     * 업데이트 후 처리
     */
    protected function afterUpdate($postId, $newData, $oldData) 
    {
        // 로깅
        logSecurityEvent('POST_UPDATED', "Post ID: {$postId}");
        
        // 변경사항 추적
        $changes = array_diff_assoc($newData, $oldData);
        if (!empty($changes)) {
            // 변경 이력 저장
        }
    }
    
    /**
     * 삭제 후 처리
     */
    protected function afterDelete($postId, $postData) 
    {
        // 로깅
        logSecurityEvent('POST_DELETED', "Post ID: {$postId}, Title: {$postData['title']}");
        
        // 관련 데이터 정리 (댓글, 좋아요 등)
        // 검색 인덱스에서 제거
    }
}