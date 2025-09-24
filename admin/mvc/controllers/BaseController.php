<?php
/**
 * BaseController - MVC 패턴 기본 컨트롤러 클래스
 * board_templates 보안 패턴 적용
 */

abstract class BaseController 
{
    protected $pdo;
    protected $view;
    
    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
        $this->view = new View();
    }
    
    /**
     * CSRF 토큰 검증
     */
    protected function verifyCSRF() 
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!verifyCSRFToken($token)) {
            logSecurityEvent('CSRF_ATTACK', 'Invalid CSRF token in ' . $_SERVER['REQUEST_URI']);
            $this->jsonError('유효하지 않은 요청입니다. (CSRF 토큰 오류)');
        }
    }
    
    /**
     * 관리자 권한 확인
     */
    protected function requireAdmin() 
    {
        if (!isValidAdminSession()) {
            logSecurityEvent('UNAUTHORIZED_ACCESS', 'Non-admin access attempt');
            if ($this->isAjaxRequest()) {
                $this->jsonError('관리자 권한이 필요합니다.', 403);
            } else {
                header('Location: ../login.php?expired=1');
                exit;
            }
        }
    }
    
    /**
     * 입력 데이터 정리 및 검증
     */
    protected function sanitizeInput($data) 
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return clean_input($data);
    }
    
    /**
     * AJAX 요청 감지
     */
    protected function isAjaxRequest() 
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * JSON 성공 응답
     */
    protected function jsonSuccess($message = '성공', $data = null) 
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * JSON 오류 응답
     */
    protected function jsonError($message = '오류가 발생했습니다.', $code = 400) 
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 리디렉션 (플래시 메시지 포함)
     */
    protected function redirect($url, $message = null, $type = 'success') 
    {
        if ($message) {
            set_flash_message($type, $message);
        }
        header("Location: {$url}");
        exit;
    }
    
    /**
     * 페이지네이션 계산
     */
    protected function calculatePagination($total, $page, $perPage = 20) 
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
     * 파일 업로드 처리
     */
    protected function handleFileUpload($file, $uploadPath, $allowedTypes = []) 
    {
        try {
            // board_templates의 업로드 보안 패턴 적용
            if (empty($allowedTypes)) {
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'hwp'];
            }
            
            // 파일 검증
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('파일 업로드 중 오류가 발생했습니다.');
            }
            
            // 파일 크기 확인 (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('파일 크기는 5MB를 초과할 수 없습니다.');
            }
            
            // 실제 파일 타입 확인
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, $allowedTypes)) {
                throw new Exception('허용되지 않은 파일 형식입니다.');
            }
            
            // 안전한 파일명 생성
            $filename = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadPath . '/' . $filename;
            
            // 디렉토리 생성
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // 파일 이동
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('파일 저장 중 오류가 발생했습니다.');
            }
            
            return $filename;
            
        } catch (Exception $e) {
            logSecurityEvent('FILE_UPLOAD_ERROR', $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 에러 페이지 표시
     */
    protected function showError($message, $code = 500) 
    {
        http_response_code($code);
        $this->view->render('error', [
            'message' => $message,
            'code' => $code
        ]);
        exit;
    }
}