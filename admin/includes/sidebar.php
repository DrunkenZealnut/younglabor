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
 * - $admin_title: 관리자 타이틀 (기본값: '{ORG_NAME} 관리자')
 */

// Configuration loader
require_once __DIR__ . '/../../includes/config_loader.php';

// 기본값 설정
$admin_title = $admin_title ?? (getOrgName('short') . ' 관리자');
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
            case 'hero':
                return $current_dir === 'settings' && $current_page === 'hero_settings.php';
            case 'performance':
                return $current_dir === 'system' && $current_page === 'performance.php';
            case 'change_password':
                return $current_page === 'change_password.php' && $current_dir === 'admin';
            default:
                return false;
        }
    }
}

// bootstrap.php에서 get_admin_url 함수를 사용
// admin_url 함수를 get_admin_url로 대체하기 위한 wrapper
if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        // bootstrap.php에서 정의된 함수들을 사용
        if (function_exists('get_admin_menu_urls') && function_exists('get_admin_url')) {
            // 특정 페이지들은 get_admin_url 사용
            $menu_mapping = [
                'index.php' => 'dashboard',
                'posts/list.php' => 'posts',
                'boards/list.php' => 'boards',
                'menu/list.php' => 'menu',
                'inquiries/list.php' => 'inquiries',
                'events/list.php' => 'events',
                'files/list.php' => 'files',
                'settings/site_settings.php' => 'settings',
                'settings/simple-color-settings.php' => 'themes',
                'settings/hero_settings.php' => 'hero',
                'system/performance.php' => 'performance',
                'change_password.php' => 'change_password',
                'logout.php' => 'logout'
            ];
            
            if (isset($menu_mapping[$path])) {
                return get_admin_url($menu_mapping[$path]);
            }
        }
        
        // 기본 fallback
        $base_path = get_base_path() ?? '/';
        return $base_path . '/admin/' . ltrim($path, '/');
    }
}
?>

<!-- 모바일 헤더 (토글 버튼 포함) -->
<div class="mobile-header d-lg-none">
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?= get_admin_url('dashboard') ?>" class="text-white text-decoration-none fw-bold">
            <?= htmlspecialchars($admin_title) ?>
        </a>
        <button class="btn btn-link text-white" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-3"></i>
        </button>
    </div>
</div>

<!-- 사이드바 오버레이 (모바일) -->
<div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

<!-- 사이드바 -->
<div class="sidebar" id="adminSidebar">
    <div class="logo d-none d-lg-block">
        <a href="<?= get_admin_url('dashboard') ?>" class="text-white text-decoration-none">
            <?= htmlspecialchars($admin_title) ?>
        </a>
    </div>

    <!-- 모바일 닫기 버튼 -->
    <div class="d-lg-none p-3 border-bottom border-secondary">
        <button class="btn btn-link text-white p-0 float-end" id="sidebarClose" type="button">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
        <div class="clearfix"></div>
    </div>

    <div class="sidebar-menu">
        <a href="<?= get_admin_url('dashboard') ?>"
           <?= is_sidebar_menu_active('dashboard', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            📊 대시보드
        </a>

        <a href="<?= get_admin_url('posts') ?>"
           <?= is_sidebar_menu_active('posts', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            📝 게시글 관리
        </a>

        <a href="<?= get_admin_url('boards') ?>"
           <?= is_sidebar_menu_active('boards', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            📋 게시판 관리
        </a>

        <a href="<?= get_admin_url('menu') ?>"
           <?= is_sidebar_menu_active('menu', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            🧭 메뉴 관리
        </a>

        <a href="<?= get_admin_url('inquiries') ?>"
           <?= is_sidebar_menu_active('inquiries', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            📬 문의 관리
        </a>

        <a href="<?= get_admin_url('events') ?>"
           <?= is_sidebar_menu_active('events', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            📅 행사 관리
        </a>

        <a href="<?= get_admin_url('files') ?>"
           <?= is_sidebar_menu_active('files', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            📎 자료실 관리
        </a>

        <a href="<?= get_admin_url('settings') ?>"
           <?= is_sidebar_menu_active('settings', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            🎨 디자인 설정
        </a>

        <a href="<?= get_admin_url('themes') ?>"
           <?= is_sidebar_menu_active('themes', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            🎨 테마 설정
        </a>

        <a href="<?= get_admin_url('hero') ?>"
           <?= is_sidebar_menu_active('hero', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            🖼️ 히어로 섹션
        </a>

        <a href="<?= get_admin_url('performance') ?>"
           <?= is_sidebar_menu_active('performance', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            ⚡ 성능 모니터링
        </a>

        <a href="<?= get_admin_url('change_password') ?>"
           <?= is_sidebar_menu_active('change_password', $current_menu, $current_page, $current_dir) ? 'class="active"' : '' ?>>
            🔐 비밀번호 변경
        </a>

        <a href="<?= get_admin_url('logout') ?>">
            🚪 로그아웃
        </a>
    </div>
</div>

<!-- Admin 반응형 CSS (헤더에 추가되어야 함) -->
<link rel="stylesheet" href="<?= get_base_path() ?>/admin/assets/css/admin-responsive.css?v=<?= time() ?>">

<!-- 반응형 사이드바 스크립트 -->
<script>
(function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // ESC 키로 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });
})();
</script>

<?php
// PROJECT_SLUG 정리용 JavaScript 출력
if (isset($GLOBALS['project_slug_js'])) {
    echo $GLOBALS['project_slug_js'];
}
?>