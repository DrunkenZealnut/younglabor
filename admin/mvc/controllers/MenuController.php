<?php

require_once 'BaseController.php';
require_once '../models/MenuModel.php';

/**
 * MenuController - 메뉴 관리 컨트롤러
 * Admin_templates/menu/ 기능을 MVC 패턴으로 구현
 */
class MenuController extends BaseController
{
    private $menuModel;
    
    public function __construct($container)
    {
        parent::__construct($container);
        $this->menuModel = new MenuModel($this->db);
    }
    
    /**
     * 메뉴 목록 조회
     */
    public function index()
    {
        try {
            // 필터 파라미터 처리
            $filters = [
                'position' => $this->getParam('position'),
                'is_active' => $this->getParam('is_active'),
                'search' => $this->getParam('search')
            ];
            
            // 데이터 조회
            $menus = $this->menuModel->getAllForAdmin($filters);
            $parentMenus = $this->menuModel->getParentMenus(false); // 비활성 포함
            
            // 사용 가능한 게시판 목록 조회
            $availableBoards = $this->getAvailableBoards();
            
            // 뷰 데이터 준비
            $viewData = [
                'page_title' => '메뉴 관리',
                'menus' => $menus,
                'parent_menus' => $parentMenus,
                'available_boards' => $availableBoards,
                'filters' => $filters,
                'positions' => [
                    'top' => '상단 메뉴',
                    'footer' => '하단 메뉴',
                    'side' => '사이드 메뉴'
                ]
            ];
            
            return $this->render('menus/list', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('메뉴 목록 조회 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 메뉴 생성 폼
     */
    public function create()
    {
        $viewData = [
            'page_title' => '메뉴 추가',
            'menu' => [],
            'parent_menus' => $this->menuModel->getParentMenus(false),
            'available_boards' => $this->getAvailableBoards(),
            'positions' => [
                'top' => '상단 메뉴',
                'footer' => '하단 메뉴', 
                'side' => '사이드 메뉴'
            ],
            'targets' => [
                '_self' => '현재 창',
                '_blank' => '새 창'
            ],
            'action' => 'create'
        ];
        
        return $this->render('menus/form', $viewData);
    }
    
    /**
     * 메뉴 저장
     */
    public function store()
    {
        if (!$this->isPost()) {
            $this->redirectTo('/menus');
            return;
        }
        
        try {
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 입력 데이터 수집
            $data = [
                'parent_id' => $this->getParam('parent_id') ?: null,
                'title' => $this->getParam('title'),
                'slug' => $this->getParam('slug'),
                'url' => $this->getParam('url'),
                'position' => $this->getParam('position', 'top'),
                'sort_order' => $this->getParam('sort_order') ?: 0,
                'is_active' => $this->getParam('is_active') ? 1 : 0,
                'board_id' => $this->getParam('board_id') ?: null,
                'icon' => $this->getParam('icon'),
                'target' => $this->getParam('target', '_self')
            ];
            
            // 메뉴 생성
            $menuId = $this->menuModel->create($data);
            
            if ($menuId) {
                $this->setFlashMessage('success', '메뉴가 성공적으로 추가되었습니다.');
                $this->redirectTo('/menus');
            } else {
                $this->setFlashMessage('error', '메뉴 추가 중 오류가 발생했습니다.');
                $this->redirectTo('/menus/create');
            }
            
        } catch (InvalidArgumentException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectTo('/menus/create');
        } catch (Exception $e) {
            $this->handleError('메뉴 저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 메뉴 수정 폼
     */
    public function edit($id)
    {
        try {
            $id = (int)$id;
            $menu = $this->menuModel->findById($id);
            
            if (!$menu) {
                $this->handleError('존재하지 않는 메뉴입니다.', 404);
                return;
            }
            
            $viewData = [
                'page_title' => '메뉴 수정 - ' . $menu['title'],
                'menu' => $menu,
                'parent_menus' => $this->menuModel->getParentMenus(false),
                'available_boards' => $this->getAvailableBoards(),
                'positions' => [
                    'top' => '상단 메뉴',
                    'footer' => '하단 메뉴',
                    'side' => '사이드 메뉴'
                ],
                'targets' => [
                    '_self' => '현재 창',
                    '_blank' => '새 창'
                ],
                'action' => 'edit'
            ];
            
            return $this->render('menus/form', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('메뉴 수정 폼 로드 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 메뉴 업데이트
     */
    public function update($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/menus');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 기존 메뉴 확인
            $existingMenu = $this->menuModel->findById($id);
            if (!$existingMenu) {
                $this->handleError('존재하지 않는 메뉴입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 입력 데이터 수집
            $data = [
                'parent_id' => $this->getParam('parent_id') ?: null,
                'title' => $this->getParam('title'),
                'slug' => $this->getParam('slug'),
                'url' => $this->getParam('url'),
                'position' => $this->getParam('position'),
                'sort_order' => $this->getParam('sort_order') ?: 0,
                'is_active' => $this->getParam('is_active') ? 1 : 0,
                'board_id' => $this->getParam('board_id') ?: null,
                'icon' => $this->getParam('icon'),
                'target' => $this->getParam('target', '_self')
            ];
            
            // 메뉴 업데이트
            if ($this->menuModel->update($id, $data)) {
                $this->setFlashMessage('success', '메뉴가 성공적으로 수정되었습니다.');
            } else {
                $this->setFlashMessage('error', '메뉴 수정 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/menus');
            
        } catch (InvalidArgumentException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectTo('/menus/edit/' . $id);
        } catch (Exception $e) {
            $this->handleError('메뉴 업데이트 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 메뉴 삭제
     */
    public function delete($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/menus');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 기존 메뉴 확인
            $existingMenu = $this->menuModel->findById($id);
            if (!$existingMenu) {
                $this->handleError('존재하지 않는 메뉴입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 메뉴 삭제
            if ($this->menuModel->delete($id)) {
                $this->setFlashMessage('success', '메뉴가 성공적으로 삭제되었습니다.');
            } else {
                $this->setFlashMessage('error', '메뉴 삭제 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/menus');
            
        } catch (RuntimeException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectTo('/menus');
        } catch (Exception $e) {
            $this->handleError('메뉴 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 메뉴 순서 업데이트 (AJAX)
     */
    public function updateOrder()
    {
        if (!$this->isPost()) {
            $this->jsonResponse(['success' => false, 'error' => '잘못된 요청입니다.']);
            return;
        }
        
        try {
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            $menuData = json_decode($this->getParam('menu_data'), true);
            
            if (!is_array($menuData)) {
                throw new InvalidArgumentException('올바른 메뉴 데이터가 아닙니다.');
            }
            
            // 순서 업데이트
            $this->menuModel->updateSortOrder($menuData);
            
            $this->jsonResponse(['success' => true, 'message' => '메뉴 순서가 성공적으로 업데이트되었습니다.']);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * 메뉴 트리 구조 조회 (API)
     */
    public function api_tree()
    {
        try {
            $position = $this->getParam('position');
            $activeOnly = $this->getParam('active_only', true);
            
            $menuTree = $this->menuModel->getMenuTree($position, $activeOnly);
            
            $this->jsonResponse([
                'success' => true,
                'menu_tree' => $menuTree
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 슬러그 중복 확인 (AJAX)
     */
    public function checkSlug()
    {
        try {
            $slug = $this->getParam('slug');
            $excludeId = $this->getParam('exclude_id');
            
            if (empty($slug)) {
                $this->jsonResponse(['available' => false, 'message' => '슬러그를 입력해주세요.']);
                return;
            }
            
            $exists = $this->menuModel->isSlugExists($slug, $excludeId);
            
            $this->jsonResponse([
                'available' => !$exists,
                'message' => $exists ? '이미 사용 중인 슬러그입니다.' : '사용 가능한 슬러그입니다.'
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'available' => false,
                'message' => '슬러그 확인 중 오류가 발생했습니다.'
            ]);
        }
    }
    
    /**
     * 메뉴 복사
     */
    public function duplicate($id)
    {
        if (!$this->isPost()) {
            $this->redirectTo('/menus');
            return;
        }
        
        try {
            $id = (int)$id;
            
            // 원본 메뉴 조회
            $originalMenu = $this->menuModel->findById($id);
            if (!$originalMenu) {
                $this->handleError('존재하지 않는 메뉴입니다.', 404);
                return;
            }
            
            // CSRF 토큰 검증
            $this->validateCsrfToken();
            
            // 복사할 데이터 준비
            $data = $originalMenu;
            unset($data['id']);
            unset($data['created_at']);
            unset($data['updated_at']);
            
            $data['title'] = $data['title'] . ' (복사본)';
            $data['slug'] = $data['slug'] ? $data['slug'] . '_copy' : '';
            $data['is_active'] = 0; // 복사본은 비활성 상태로
            
            // 메뉴 복사
            $newMenuId = $this->menuModel->create($data);
            
            if ($newMenuId) {
                $this->setFlashMessage('success', '메뉴가 성공적으로 복사되었습니다.');
            } else {
                $this->setFlashMessage('error', '메뉴 복사 중 오류가 발생했습니다.');
            }
            
            $this->redirectTo('/menus');
            
        } catch (Exception $e) {
            $this->handleError('메뉴 복사 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 사용 가능한 게시판 목록 조회
     */
    private function getAvailableBoards()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, board_name 
                FROM " . get_table_name('boards') 
                WHERE is_active = 1 
                ORDER BY board_name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
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