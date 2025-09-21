<?php
/**
 * 메뉴 매니저
 * 
 * hopec_menu와 hopec_boards 테이블을 연동하여 동적 메뉴 생성
 */

class MenuManager
{
    private static $menus = null;
    private static $boards = null;
    
    /**
     * 전체 메뉴 구조 조회 (게시판 정보 포함)
     */
    public static function getMenus($position = 'top')
    {
        if (self::$menus === null) {
            self::loadMenus();
        }
        
        return array_filter(self::$menus, function($menu) use ($position) {
            return $menu['position'] === $position && $menu['is_active'] == 1;
        });
    }
    
    /**
     * 계층형 메뉴 구조 조회
     */
    public static function getMenuTree($position = 'top')
    {
        $menus = self::getMenus($position);
        
        // parent_id로 그룹핑
        $menuTree = [];
        $childMenus = [];
        
        foreach ($menus as $menu) {
            if ($menu['parent_id'] === null) {
                $menuTree[$menu['id']] = $menu;
                $menuTree[$menu['id']]['children'] = [];
            } else {
                $childMenus[$menu['parent_id']][] = $menu;
            }
        }
        
        // 자식 메뉴 연결
        foreach ($childMenus as $parentId => $children) {
            if (isset($menuTree[$parentId])) {
                // 정렬 순서대로 정렬
                usort($children, function($a, $b) {
                    return $a['sort_order'] - $b['sort_order'];
                });
                $menuTree[$parentId]['children'] = $children;
            }
        }
        
        // 정렬 순서대로 정렬
        uasort($menuTree, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });
        
        return $menuTree;
    }
    
    /**
     * 메뉴 데이터 로드
     */
    private static function loadMenus()
    {
        try {
            $query = "
                SELECT 
                    m.id,
                    m.parent_id,
                    m.title,
                    m.slug,
                    m.position,
                    m.sort_order,
                    m.is_active,
                    m.board_id,
                    b.board_name,
                    b.board_code,
                    b.board_type,
                    b.description as board_description
                FROM " . DatabaseManager::getTableName('menu') . " m
                LEFT JOIN " . DatabaseManager::getTableName('boards') . " b ON m.board_id = b.id
                ORDER BY m.sort_order ASC
            ";
            
            self::$menus = DatabaseManager::select($query);
            
        } catch (Exception $e) {
            error_log('MenuManager 로드 실패: ' . $e->getMessage());
            self::$menus = [];
        }
    }
    
    /**
     * 게시판 정보 조회
     */
    public static function getBoardInfo($boardId)
    {
        if (self::$boards === null) {
            self::loadBoards();
        }
        
        return self::$boards[$boardId] ?? null;
    }
    
    /**
     * 모든 게시판 정보 로드
     */
    private static function loadBoards()
    {
        try {
            $query = "SELECT * FROM " . DatabaseManager::getTableName('boards') . " WHERE is_active = 1";
            $boards = DatabaseManager::select($query);
            
            self::$boards = [];
            foreach ($boards as $board) {
                self::$boards[$board['id']] = $board;
            }
            
        } catch (Exception $e) {
            error_log('게시판 정보 로드 실패: ' . $e->getMessage());
            self::$boards = [];
        }
    }
    
    /**
     * 메뉴별 URL 생성
     */
    public static function getMenuUrl($menu)
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        
        // 게시판이 연결된 경우
        if (!empty($menu['board_id'])) {
            return $baseUrl . '/board.php?board_id=' . $menu['board_id'];
        }
        
        // 일반 페이지
        if (!empty($menu['slug'])) {
            // 부모 메뉴가 있는 경우 경로 구성
            if (!empty($menu['parent_id'])) {
                $parentMenu = self::getParentMenu($menu['parent_id']);
                if ($parentMenu) {
                    return $baseUrl . '/' . $parentMenu['slug'] . '/' . $menu['slug'] . '.php';
                }
            }
            
            return $baseUrl . '/' . $menu['slug'] . '.php';
        }
        
        return '#';
    }
    
    /**
     * 부모 메뉴 정보 조회
     */
    private static function getParentMenu($parentId)
    {
        if (self::$menus === null) {
            self::loadMenus();
        }
        
        foreach (self::$menus as $menu) {
            if ($menu['id'] == $parentId) {
                return $menu;
            }
        }
        
        return null;
    }
    
    /**
     * HTML 메뉴 생성
     */
    public static function renderMenu($position = 'top', $className = 'nav-menu')
    {
        $menuTree = self::getMenuTree($position);
        
        if (empty($menuTree)) {
            return '';
        }
        
        $html = '<ul class="' . $className . '">';
        
        foreach ($menuTree as $menu) {
            $html .= '<li class="menu-item">';
            $html .= '<a href="' . self::getMenuUrl($menu) . '">';
            $html .= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8');
            $html .= '</a>';
            
            // 자식 메뉴가 있는 경우
            if (!empty($menu['children'])) {
                $html .= '<ul class="submenu">';
                foreach ($menu['children'] as $child) {
                    $html .= '<li class="submenu-item">';
                    $html .= '<a href="' . self::getMenuUrl($child) . '">';
                    $html .= htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8');
                    $html .= '</a>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * 브레드크럼 생성
     */
    public static function getBreadcrumb($currentSlug)
    {
        if (self::$menus === null) {
            self::loadMenus();
        }
        
        $breadcrumb = [];
        
        // 현재 메뉴 찾기
        $currentMenu = null;
        foreach (self::$menus as $menu) {
            if ($menu['slug'] === $currentSlug) {
                $currentMenu = $menu;
                break;
            }
        }
        
        if (!$currentMenu) {
            return $breadcrumb;
        }
        
        // 상위 메뉴들 수집
        $menuPath = [$currentMenu];
        $parentId = $currentMenu['parent_id'];
        
        while ($parentId) {
            $parentMenu = self::getParentMenu($parentId);
            if ($parentMenu) {
                array_unshift($menuPath, $parentMenu);
                $parentId = $parentMenu['parent_id'];
            } else {
                break;
            }
        }
        
        // 홈 추가
        $breadcrumb[] = ['title' => '홈', 'url' => env('APP_URL', '')];
        
        // 경로 추가
        foreach ($menuPath as $menu) {
            $breadcrumb[] = [
                'title' => $menu['title'],
                'url' => self::getMenuUrl($menu)
            ];
        }
        
        return $breadcrumb;
    }
    
    /**
     * 현재 활성 메뉴 확인
     */
    public static function isActiveMenu($menuSlug, $currentPage)
    {
        // 현재 페이지의 슬러그와 메뉴 슬러그 비교
        $currentSlug = pathinfo($currentPage, PATHINFO_FILENAME);
        
        return $currentSlug === $menuSlug;
    }
    
    /**
     * 메뉴 캐시 초기화
     */
    public static function clearCache()
    {
        self::$menus = null;
        self::$boards = null;
    }
}