<?php
/**
 * 공통 Admin 사이드바 컴포넌트
 * 모든 관리자 페이지에서 일관된 사이드바 제공
 * 
 * 사용법:
 * <?php include __DIR__ . '/includes/sidebar.php'; ?>
 * 
 * 옵션 변수:
 * - $current_menu: 현재 활성 메뉴 ID (선택사항)
 * - $admin_title: 관리자 타이틀 (기본값: '희망씨 관리자')
 */

// 기본값 설정
$admin_title = $admin_title ?? '희망씨 관리자';
$current_menu = $current_menu ?? '';

// 현재 페이지 감지
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// 메뉴 활성화 감지 함수
if (!function_exists('is_sidebar_menu_active')) {
    function is_sidebar_menu_active($menu_id, $current_menu, $current_page, $current_dir) {
        // 명시적으로 설정된 경우
        if (!empty($current_menu)) {
            return $current_menu === $menu_id;
        }
        
        // 자동 감지
        switch ($menu_id) {
            case 'dashboard':
                return $current_page === 'index.php' && $current_dir === 'admin';
            case 'posts':
                return $current_dir === 'posts';
            case 'boards':
                return $current_dir === 'boards';
            case 'menu':
                return $current_dir === 'menu';
            case 'inquiries':
                return $current_dir === 'inquiries';
            case 'events':
                return $current_dir === 'events';
            case 'files':
                return $current_dir === 'files';
            case 'settings':
                return $current_dir === 'settings' && $current_page === 'site_settings.php';
            case 'themes':
                return $current_dir === 'settings' && $current_page === 'simple-color-settings.php';
            case 'performance':
                return $current_dir === 'system' && $current_page === 'performance.php';
            default:
                return false;
        }
    }
}

// admin_url 함수가 없는 경우 기본 구현
if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        $base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/admin');
        if (strpos($base_path, '/admin') === false) {
            $base_path .= '/admin';
        }
        return $base_path . '/' . ltrim($path, '/');
    }
}
?>

<!-- 사이드바 -->
<div class="sidebar">
    <div class="logo">
        <a href="<?= admin_url('index.php') ?>" class="text-white text-decoration-none">
            <?= htmlspecialchars($admin_title) ?>
        </a>
    </div>
    
    <a href="<?= admin_url('index.php') ?>" 
       <?= is_sidebar_menu_active('dashboard', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        📊 대시보드
    </a>
    
    <a href="<?= admin_url('posts/list.php') ?>" 
       <?= is_sidebar_menu_active('posts', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        📝 게시글 관리
    </a>
    
    <a href="<?= admin_url('boards/list.php') ?>" 
       <?= is_sidebar_menu_active('boards', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        📋 게시판 관리
    </a>
    
    <a href="<?= admin_url('menu/list.php') ?>" 
       <?= is_sidebar_menu_active('menu', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        🧭 메뉴 관리
    </a>
    
    <a href="<?= admin_url('inquiries/list.php') ?>" 
       <?= is_sidebar_menu_active('inquiries', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        📬 문의 관리
    </a>
    
    <a href="<?= admin_url('events/list.php') ?>" 
       <?= is_sidebar_menu_active('events', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        📅 행사 관리
    </a>
    
    <a href="<?= admin_url('files/list.php') ?>" 
       <?= is_sidebar_menu_active('files', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        📎 자료실 관리
    </a>
    
    <a href="<?= admin_url('settings/site_settings.php') ?>" 
       <?= is_sidebar_menu_active('settings', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        🎨 디자인 설정
    </a>
    
    <a href="<?= admin_url('settings/simple-color-settings.php') ?>" 
       <?= is_sidebar_menu_active('themes', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        🎨 테마 설정
    </a>
    
    <a href="<?= admin_url('system/performance.php') ?>" 
       <?= is_sidebar_menu_active('performance', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
        ⚡ 성능 모니터링
    </a>
    
    <a href="<?= admin_url('logout.php') ?>">
        🚪 로그아웃
    </a>
</div>