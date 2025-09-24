<?php
/**
 * 메뉴 관련 헬퍼 함수들
 */

if (!function_exists('get_menu')) {
    /**
     * 메뉴 조회
     */
    function get_menu($position = 'top') {
        return MenuManager::getMenus($position);
    }
}

if (!function_exists('get_menu_tree')) {
    /**
     * 계층형 메뉴 조회
     */
    function get_menu_tree($position = 'top') {
        return MenuManager::getMenuTree($position);
    }
}

if (!function_exists('render_menu')) {
    /**
     * HTML 메뉴 렌더링
     */
    function render_menu($position = 'top', $className = 'nav-menu') {
        return MenuManager::renderMenu($position, $className);
    }
}

if (!function_exists('get_breadcrumb')) {
    /**
     * 브레드크럼 조회
     */
    function get_breadcrumb($currentSlug) {
        return MenuManager::getBreadcrumb($currentSlug);
    }
}

if (!function_exists('render_breadcrumb')) {
    /**
     * 브레드크럼 HTML 렌더링
     */
    function render_breadcrumb($currentSlug, $separator = ' &gt; ') {
        $breadcrumb = get_breadcrumb($currentSlug);
        
        if (empty($breadcrumb)) {
            return '';
        }
        
        $html = '<nav class="breadcrumb">';
        $items = [];
        
        foreach ($breadcrumb as $index => $item) {
            if ($index === count($breadcrumb) - 1) {
                // 마지막 항목은 링크 없음
                $items[] = '<span class="breadcrumb-current">' . h($item['title']) . '</span>';
            } else {
                $items[] = '<a href="' . h($item['url']) . '">' . h($item['title']) . '</a>';
            }
        }
        
        $html .= implode($separator, $items);
        $html .= '</nav>';
        
        return $html;
    }
}

if (!function_exists('is_active_menu')) {
    /**
     * 활성 메뉴 확인
     */
    function is_active_menu($menuSlug, $currentPage = null) {
        if ($currentPage === null) {
            $currentPage = $_SERVER['SCRIPT_NAME'] ?? '';
        }
        
        return MenuManager::isActiveMenu($menuSlug, $currentPage);
    }
}

if (!function_exists('get_menu_url')) {
    /**
     * 메뉴 URL 생성
     */
    function get_menu_url($menu) {
        return MenuManager::getMenuUrl($menu);
    }
}

if (!function_exists('get_board_info')) {
    /**
     * 게시판 정보 조회
     */
    function get_board_info($boardId) {
        return MenuManager::getBoardInfo($boardId);
    }
}

if (!function_exists('render_nav_menu')) {
    /**
     * 네비게이션 메뉴 렌더링 (Bootstrap 스타일)
     */
    function render_nav_menu($position = 'top', $navClass = 'navbar-nav') {
        $menuTree = get_menu_tree($position);
        
        if (empty($menuTree)) {
            return '';
        }
        
        $currentPage = $_SERVER['SCRIPT_NAME'] ?? '';
        $html = '<ul class="' . $navClass . '">';
        
        foreach ($menuTree as $menu) {
            $isActive = is_active_menu($menu['slug'], $currentPage);
            $hasChildren = !empty($menu['children']);
            
            $liClass = 'nav-item';
            if ($hasChildren) {
                $liClass .= ' dropdown';
            }
            if ($isActive) {
                $liClass .= ' active';
            }
            
            $html .= '<li class="' . $liClass . '">';
            
            // 메인 메뉴 링크
            $aClass = 'nav-link';
            if ($hasChildren) {
                $aClass .= ' dropdown-toggle';
                $html .= '<a class="' . $aClass . '" href="' . get_menu_url($menu) . '" ';
                $html .= 'data-bs-toggle="dropdown" aria-expanded="false">';
            } else {
                $html .= '<a class="' . $aClass . '" href="' . get_menu_url($menu) . '">';
            }
            
            $html .= h($menu['title']) . '</a>';
            
            // 드롭다운 메뉴
            if ($hasChildren) {
                $html .= '<ul class="dropdown-menu">';
                foreach ($menu['children'] as $child) {
                    $childActive = is_active_menu($child['slug'], $currentPage);
                    $itemClass = $childActive ? 'dropdown-item active' : 'dropdown-item';
                    
                    $html .= '<li><a class="' . $itemClass . '" href="' . get_menu_url($child) . '">';
                    $html .= h($child['title']) . '</a></li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }
}

if (!function_exists('render_sidebar_menu')) {
    /**
     * 사이드바 메뉴 렌더링
     */
    function render_sidebar_menu($position = 'top') {
        $menuTree = get_menu_tree($position);
        
        if (empty($menuTree)) {
            return '';
        }
        
        $currentPage = $_SERVER['SCRIPT_NAME'] ?? '';
        $html = '<div class="sidebar-menu">';
        
        foreach ($menuTree as $menu) {
            $isActive = is_active_menu($menu['slug'], $currentPage);
            $hasChildren = !empty($menu['children']);
            
            $html .= '<div class="menu-section">';
            $html .= '<h5 class="menu-title">' . h($menu['title']) . '</h5>';
            
            if ($hasChildren) {
                $html .= '<ul class="menu-items">';
                foreach ($menu['children'] as $child) {
                    $childActive = is_active_menu($child['slug'], $currentPage);
                    $itemClass = $childActive ? 'menu-item active' : 'menu-item';
                    
                    $html .= '<li class="' . $itemClass . '">';
                    $html .= '<a href="' . get_menu_url($child) . '">' . h($child['title']) . '</a>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}