<?php

require_once 'BaseController.php';
require_once '../models/InquiryModel.php';

/**
 * InquiryController - 문의 관리 컨트롤러
 * Admin_templates/inquiries/ 기능을 MVC 패턴으로 구현
 */
class InquiryController extends BaseController
{
    private $inquiryModel;
    
    public function __construct($container)
    {
        parent::__construct($container);
        $this->inquiryModel = new InquiryModel($this->db);
    }
    
    /**
     * 문의 목록 조회
     */
    public function index()
    {
        try {
            $page = $this->getParam('page', 1);
            $perPage = $this->getParam('per_page', 15);
            
            // 필터 파라미터 처리
            $filters = [
                'category_id' => $this->getParam('category_id'),
                'status' => $this->getParam('status'),
                'priority' => $this->getParam('priority'),
                'date_from' => $this->getParam('date_from'),
                'date_to' => $this->getParam('date_to'),
                'search' => $this->getParam('search')
            ];
            
            // 데이터 조회
            $inquiries = $this->inquiryModel->getAllWithPagination($page, $perPage, $filters);
            $totalCount = $this->inquiryModel->getCount($filters);
            $totalPages = ceil($totalCount / $perPage);
            
            // 카테고리 목록 조회
            $categories = $this->getInquiryCategories();
            
            // 통계 조회
            $statusStats = $this->inquiryModel->getStatusStats();
            $priorityStats = $this->inquiryModel->getPriorityStats();
            
            // 뷰 데이터 준비
            $viewData = [
                'page_title' => '문의 관리',
                'inquiries' => $inquiries,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_count' => $totalCount
                ],
                'filters' => $filters,
                'categories' => $categories,
                'status_options' => [
                    '접수' => '접수',
                    '처리중' => '처리중',
                    '완료' => '완료',
                    '보류' => '보류'
                ],
                'priority_options' => [
                    '낮음' => '낮음',
                    '보통' => '보통', 
                    '높음' => '높음',
                    '긴급' => '긴급'
                ],
                'status_stats' => $statusStats,
                'priority_stats' => $priorityStats
            ];
            
            return $this->render('inquiries/list', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('문의 목록 조회 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 문의 상세 조회
     */
    public function view($id)
    {
        try {
            $id = (int)$id;
            $inquiry = $this->inquiryModel->findById($id);
            
            if (!$inquiry) {
                $this->handleError('존재하지 않는 문의입니다.', 404);
                return;
            }
            
            // 카테고리 정보 추가
            if ($inquiry['category_id']) {
                $category = $this->getInquiryCategory($inquiry['category_id']);
                $inquiry['category_name'] = $category ? $category['name'] : '';
            }
            
            $viewData = [
                'page_title' => '문의 상세 - ' . $inquiry['subject'],
                'inquiry' => $inquiry,
                'status_options' => [
                    '접수' => '접수',
                    '처리중' => '처리중',
                    '완료' => '완료',
                    '보류' => '보류'
                ],
                'priority_options' => [
                    '낮음' => '낮음',
                    '보통' => '보통',
                    '높음' => '높음',
                    '긴급' => '긴급'
                ]
            ];
            
            return $this->render('inquiries/view', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('문의 상세 조회 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 문의 상태 업데이트
     */
    public function updateStatus($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/inquiries');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 기존 문의 확인
            $existingInquiry = $this->inquiryModel->findById($id);
            if (!$existingInquiry) {
                $this->handleError('존재하지 않는 문의입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 입력 데이터 수집
            $data = [
                'status' => $this->getParam('status'),
                'priority' => $this->getParam('priority')
            ];
            
            // 상태 업데이트
            if ($this->inquiryModel->update($id, $data)) {
                $this->setFlashMessage('success', '문의 상태가 성공적으로 업데이트되었습니다.');
            } else {
                $this->setFlashMessage('error', '문의 상태 업데이트 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/inquiries/view/' . $id);
            
        } catch (InvalidArgumentException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectTo('/inquiries/view/' . $id);
        } catch (Exception $e) {
            $this->handleError('문의 상태 업데이트 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 관리자 답변 등록
     */
    public function addResponse($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/inquiries');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 기존 문의 확인
            $existingInquiry = $this->inquiryModel->findById($id);
            if (!$existingInquiry) {
                $this->handleError('존재하지 않는 문의입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            $response = $this->getParam('admin_response');
            $adminId = $_SESSION['admin_id'] ?? null;
            
            if (empty($response)) {
                $this->setFlashMessage('error', '답변 내용을 입력해주세요.');
                $this->redirectTo('/inquiries/view/' . $id);
                return;
            }
            
            // 답변 등록
            if ($this->inquiryModel->addAdminResponse($id, $response, $adminId)) {
                $this->setFlashMessage('success', '답변이 성공적으로 등록되었습니다.');
                
                // 이메일 발송 (옵션)
                $this->sendResponseEmail($existingInquiry, $response);
            } else {
                $this->setFlashMessage('error', '답변 등록 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/inquiries/view/' . $id);
            
        } catch (Exception $e) {
            $this->handleError('답변 등록 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 문의 삭제
     */
    public function delete($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/inquiries');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 기존 문의 확인
            $existingInquiry = $this->inquiryModel->findById($id);
            if (!$existingInquiry) {
                $this->handleError('존재하지 않는 문의입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 문의 삭제
            if ($this->inquiryModel->delete($id)) {
                $this->setFlashMessage('success', '문의가 성공적으로 삭제되었습니다.');
            } else {
                $this->setFlashMessage('error', '문의 삭제 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/inquiries');
            
        } catch (Exception $e) {
            $this->handleError('문의 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 대량 처리 (상태 변경, 삭제)
     */
    public function bulkAction()
    {
        if (!$this->isPost()) {
            $this->redirectTo('/inquiries');
            return;
        }
        
        try {
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            $action = $this->getParam('bulk_action');
            $selectedIds = $this->getParam('selected_ids', []);
            
            if (empty($selectedIds) || !is_array($selectedIds)) {
                $this->setFlashMessage('error', '처리할 문의를 선택해주세요.');
                $this->redirectTo('/inquiries');
                return;
            }
            
            $successCount = 0;
            
            switch ($action) {
                case 'delete':
                    foreach ($selectedIds as $id) {
                        if ($this->inquiryModel->delete((int)$id)) {
                            $successCount++;
                        }
                    }
                    $this->setFlashMessage('success', $successCount . '건의 문의가 삭제되었습니다.');
                    break;
                    
                case 'status_complete':
                    foreach ($selectedIds as $id) {
                        if ($this->inquiryModel->update((int)$id, ['status' => '완료'])) {
                            $successCount++;
                        }
                    }
                    $this->setFlashMessage('success', $successCount . '건의 문의 상태가 완료로 변경되었습니다.');
                    break;
                    
                case 'status_processing':
                    foreach ($selectedIds as $id) {
                        if ($this->inquiryModel->update((int)$id, ['status' => '처리중'])) {
                            $successCount++;
                        }
                    }
                    $this->setFlashMessage('success', $successCount . '건의 문의 상태가 처리중으로 변경되었습니다.');
                    break;
                    
                default:
                    $this->setFlashMessage('error', '올바른 액션을 선택해주세요.');
                    break;
            }
            
            $this->redirectTo('/inquiries');
            
        } catch (Exception $e) {
            $this->handleError('대량 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 미처리 문의 조회 (대시보드용 API)
     */
    public function api_pending()
    {
        try {
            $limit = $this->getParam('limit', 10);
            $pendingInquiries = $this->inquiryModel->getPendingInquiries($limit);
            
            $this->jsonResponse([
                'success' => true,
                'inquiries' => $pendingInquiries
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 문의 통계 조회 (API)
     */
    public function api_stats()
    {
        try {
            $statusStats = $this->inquiryModel->getStatusStats();
            $priorityStats = $this->inquiryModel->getPriorityStats();
            
            $this->jsonResponse([
                'success' => true,
                'status_stats' => $statusStats,
                'priority_stats' => $priorityStats
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 문의 엑셀 다운로드
     */
    public function export()
    {
        try {
            // 필터 조건 수집
            $filters = [
                'category_id' => $this->getParam('category_id'),
                'status' => $this->getParam('status'),
                'priority' => $this->getParam('priority'),
                'date_from' => $this->getParam('date_from'),
                'date_to' => $this->getParam('date_to')
            ];
            
            // 모든 데이터 조회
            $inquiries = $this->inquiryModel->getAllWithPagination(1, 10000, $filters);
            
            // CSV 헤더 설정
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="inquiries_' . date('Y-m-d') . '.csv"');
            
            // UTF-8 BOM 추가 (Excel에서 한글 깨짐 방지)
            echo "\xEF\xBB\xBF";
            
            // CSV 출력
            $output = fopen('php://output', 'w');
            
            // 헤더 출력
            fputcsv($output, [
                '번호', '카테고리', '제목', '이름', '이메일', '전화번호', 
                '상태', '우선순위', '접수일시', '답변일시'
            ]);
            
            // 데이터 출력
            foreach ($inquiries as $inquiry) {
                fputcsv($output, [
                    $inquiry['id'],
                    $inquiry['category_name'] ?? '',
                    $inquiry['subject'],
                    $inquiry['name'],
                    $inquiry['email'],
                    $inquiry['phone'] ?? '',
                    $inquiry['status'],
                    $inquiry['priority'],
                    $inquiry['created_at'],
                    $inquiry['responded_at'] ?? ''
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            $this->handleError('엑셀 다운로드 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 문의 카테고리 목록 조회
     */
    private function getInquiryCategories()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name 
                FROM " . get_table_name('inquiry_categories') 
                WHERE is_active = 1 
                ORDER BY name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * 문의 카테고리 단일 조회
     */
    private function getInquiryCategory($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name 
                FROM " . get_table_name('inquiry_categories') 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * 답변 이메일 발송
     */
    private function sendResponseEmail($inquiry, $response)
    {
        // 실제 구현에서는 이메일 발송 로직 추가
        // 여기서는 예시 구조만 제공
        try {
            // 메일 발송 로직
            // mail() 함수나 PHPMailer 등 사용
            return true;
        } catch (Exception $e) {
            error_log('답변 이메일 발송 실패: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * JSON 응답 헬퍼
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}