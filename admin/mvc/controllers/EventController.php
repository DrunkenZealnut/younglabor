<?php

require_once 'BaseController.php';
require_once '../models/EventModel.php';
require_once '../services/FileUploadService.php';

/**
 * EventController - 이벤트 관리 컨트롤러
 * Admin_templates/events/ 기능을 MVC 패턴으로 구현
 */
class EventController extends BaseController
{
    private $eventModel;
    private $fileUploadService;
    
    public function __construct($container)
    {
        parent::__construct($container);
        $this->eventModel = new EventModel($this->db);
        $this->fileUploadService = new FileUploadService([
            'upload_dir' => '../../uploads/',
            'max_file_size' => 5242880 // 5MB
        ]);
    }
    
    /**
     * 이벤트 목록 조회
     */
    public function index()
    {
        try {
            $page = $this->getParam('page', 1);
            $perPage = $this->getParam('per_page', 15);
            
            // 필터 파라미터 처리
            $filters = [
                'status' => $this->getParam('status'),
                'date_from' => $this->getParam('date_from'),
                'date_to' => $this->getParam('date_to'),
                'search' => $this->getParam('search')
            ];
            
            // 데이터 조회
            $events = $this->eventModel->getAllWithPagination($page, $perPage, $filters);
            $totalCount = $this->eventModel->getCount($filters);
            $totalPages = ceil($totalCount / $perPage);
            
            // 뷰 데이터 준비
            $viewData = [
                'page_title' => '이벤트 관리',
                'events' => $events,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_count' => $totalCount
                ],
                'filters' => $filters
            ];
            
            return $this->render('events/list', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('이벤트 목록 조회 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 상세 조회
     */
    public function view($id)
    {
        try {
            $id = (int)$id;
            $event = $this->eventModel->findById($id);
            
            if (!$event) {
                $this->handleError('존재하지 않는 이벤트입니다.', 404);
                return;
            }
            
            // 참가자 목록 조회 (별도 테이블이 있다면)
            $participants = $this->getEventParticipants($id);
            
            $viewData = [
                'page_title' => '이벤트 상세 - ' . $event['title'],
                'event' => $event,
                'participants' => $participants
            ];
            
            return $this->render('events/view', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('이벤트 상세 조회 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 생성 폼
     */
    public function create()
    {
        $viewData = [
            'page_title' => '이벤트 등록',
            'event' => [],
            'action' => 'create'
        ];
        
        return $this->render('events/form', $viewData);
    }
    
    /**
     * 이벤트 저장
     */
    public function store()
    {
        if (!$this->isPost()) {
            $this->redirectTo('/events');
            return;
        }
        
        try {
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 입력 데이터 수집
            $data = [
                'title' => $this->getParam('title'),
                'description' => $this->getParam('description'),
                'start_date' => $this->getParam('start_date'),
                'end_date' => $this->getParam('end_date'),
                'location' => $this->getParam('location'),
                'max_participants' => $this->getParam('max_participants') ? (int)$this->getParam('max_participants') : null,
                'status' => $this->getParam('status', '준비중'),
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // 썸네일 업로드 처리
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->fileUploadService->uploadEventThumbnail($_FILES['thumbnail']);
                
                if ($uploadResult['success']) {
                    $data['thumbnail_path'] = $uploadResult['file_path'];
                } else {
                    $this->setFlashMessage('error', '썸네일 업로드 실패: ' . $uploadResult['error']);
                }
            }
            
            // 이벤트 생성
            $eventId = $this->eventModel->create($data);
            
            if ($eventId) {
                $this->setFlashMessage('success', '이벤트가 성공적으로 등록되었습니다.');
                $this->redirectTo('/events/view/' . $eventId);
            } else {
                $this->setFlashMessage('error', '이벤트 등록 중 오류가 발생했습니다.');
                $this->redirectTo('/events/create');
            }
            
        } catch (InvalidArgumentException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectTo('/events/create');
        } catch (Exception $e) {
            $this->handleError('이벤트 저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 수정 폼
     */
    public function edit($id)
    {
        try {
            $id = (int)$id;
            $event = $this->eventModel->findById($id);
            
            if (!$event) {
                $this->handleError('존재하지 않는 이벤트입니다.', 404);
                return;
            }
            
            $viewData = [
                'page_title' => '이벤트 수정 - ' . $event['title'],
                'event' => $event,
                'action' => 'edit'
            ];
            
            return $this->render('events/form', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('이벤트 수정 폼 로드 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 업데이트
     */
    public function update($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/events');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 기존 이벤트 확인
            $existingEvent = $this->eventModel->findById($id);
            if (!$existingEvent) {
                $this->handleError('존재하지 않는 이벤트입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 입력 데이터 수집
            $data = [
                'title' => $this->getParam('title'),
                'description' => $this->getParam('description'),
                'start_date' => $this->getParam('start_date'),
                'end_date' => $this->getParam('end_date'),
                'location' => $this->getParam('location'),
                'max_participants' => $this->getParam('max_participants') ? (int)$this->getParam('max_participants') : null,
                'status' => $this->getParam('status')
            ];
            
            // 썸네일 업로드 처리
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->fileUploadService->uploadEventThumbnail($_FILES['thumbnail']);
                
                if ($uploadResult['success']) {
                    // 기존 썸네일 삭제
                    if (!empty($existingEvent['thumbnail_path'])) {
                        $this->fileUploadService->deleteFile($existingEvent['thumbnail_path']);
                    }
                    $data['thumbnail_path'] = $uploadResult['file_path'];
                } else {
                    $this->setFlashMessage('error', '썸네일 업로드 실패: ' . $uploadResult['error']);
                }
            }
            
            // 이벤트 업데이트
            if ($this->eventModel->update($id, $data)) {
                $this->setFlashMessage('success', '이벤트가 성공적으로 수정되었습니다.');
                $this->redirectTo('/events/view/' . $id);
            } else {
                $this->setFlashMessage('error', '이벤트 수정 중 오류가 발생했습니다.');
                $this->redirectTo('/events/edit/' . $id);
            }
            
        } catch (InvalidArgumentException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectTo('/events/edit/' . $id);
        } catch (Exception $e) {
            $this->handleError('이벤트 업데이트 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 삭제
     */
    public function delete($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/events');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 기존 이벤트 확인
            $existingEvent = $this->eventModel->findById($id);
            if (!$existingEvent) {
                $this->handleError('존재하지 않는 이벤트입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 썸네일 파일 삭제
            if (!empty($existingEvent['thumbnail_path'])) {
                $this->fileUploadService->deleteFile($existingEvent['thumbnail_path']);
            }
            
            // 이벤트 삭제
            if ($this->eventModel->delete($id)) {
                $this->setFlashMessage('success', '이벤트가 성공적으로 삭제되었습니다.');
            } else {
                $this->setFlashMessage('error', '이벤트 삭제 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/events');
            
        } catch (Exception $e) {
            $this->handleError('이벤트 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 참가자 관리
     */
    public function participants($id)
    {
        try {
            $id = (int)$id;
            $event = $this->eventModel->findById($id);
            
            if (!$event) {
                $this->handleError('존재하지 않는 이벤트입니다.', 404);
                return;
            }
            
            $participants = $this->getEventParticipants($id);
            
            $viewData = [
                'page_title' => '참가자 관리 - ' . $event['title'],
                'event' => $event,
                'participants' => $participants
            ];
            
            return $this->render('events/participants', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('참가자 목록 조회 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 참가자 추가
     */
    public function addParticipant($eventId)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/events/participants/' . $eventId);
            return;
        }
        
        try {
            $eventId = (int)$eventId;
            
            // 이벤트 존재 확인
            $event = $this->eventModel->findById($eventId);
            if (!$event) {
                $this->handleError('존재하지 않는 이벤트입니다.', 404);
                return;
            }
            
            // 참가자 수 확인
            if ($event['max_participants'] && $event['current_participants'] >= $event['max_participants']) {
                $this->setFlashMessage('error', '참가자 정원이 초과되었습니다.');
                $this->redirectTo('/events/participants/' . $eventId);
                return;
            }
            
            // 참가자 정보 수집
            $participantData = [
                'event_id' => $eventId,
                'name' => $this->getParam('name'),
                'email' => $this->getParam('email'),
                'phone' => $this->getParam('phone'),
                'memo' => $this->getParam('memo')
            ];
            
            // 참가자 추가 (별도 테이블 필요)
            if ($this->addEventParticipant($participantData)) {
                // 현재 참가자 수 업데이트
                $this->eventModel->updateParticipantsCount($eventId, true);
                $this->setFlashMessage('success', '참가자가 성공적으로 추가되었습니다.');
            } else {
                $this->setFlashMessage('error', '참가자 추가 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/events/participants/' . $eventId);
            
        } catch (Exception $e) {
            $this->handleError('참가자 추가 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 활성 이벤트 조회 (API)
     */
    public function api_active()
    {
        try {
            $events = $this->eventModel->getActiveEvents();
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'events' => $events
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * 이벤트 참가자 목록 조회 (헬퍼 메서드)
     */
    private function getEventParticipants($eventId)
    {
        // 실제 구현에서는 별도의 participants 테이블에서 조회
        // 여기서는 예시 구조만 제공
        return [];
    }
    
    /**
     * 이벤트 참가자 추가 (헬퍼 메서드)
     */
    private function addEventParticipant($data)
    {
        // 실제 구현에서는 별도의 participants 테이블에 삽입
        // 여기서는 예시 구조만 제공
        return true;
    }
}