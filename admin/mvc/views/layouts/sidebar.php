<?php
/**
 * Sidebar Layout - 사이드바가 있는 관리자 레이아웃
 * 
 * 필수 변수:
 * - $title: 페이지 제목
 * - $content: 메인 컨텐츠 (버퍼로 생성됨)
 * 
 * 선택 변수:
 * - $page_title: HTML title (기본값: $title)
 * - $breadcrumb: 브레드크럼 배열
 * - $additional_css: 추가 CSS 파일
 * - $additional_js: 추가 JS 파일
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) session_start();

// 기본값 설정
$page_title = isset($page_title) ? $page_title : ($title ?? '관리자 페이지');
$additional_css = isset($additional_css) ? $additional_css : [];
$additional_js = isset($additional_js) ? $additional_js : [];
$breadcrumb = isset($breadcrumb) ? $breadcrumb : [];

// 현재 메뉴 활성화 처리
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// 디버그 모드에서 현재 경로 정보 출력 (개발 중에만)
if (isset($_GET['debug_menu'])) {
    echo "<!-- DEBUG: current_page = $current_page, current_dir = $current_dir, PHP_SELF = {$_SERVER['PHP_SELF']} -->";
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= t_escape($page_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  
  <!-- 기본 스타일 -->
  <style>
    body {
      min-height: 100vh;
      display: flex;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      width: 220px;
      background-color: #343a40;
      color: white;
      min-height: 100vh;
    }
    .sidebar a {
      color: white;
      padding: 12px 16px;
      display: block;
      text-decoration: none;
      transition: background-color 0.2s;
    }
    .sidebar a:hover {
      background-color: #495057;
    }
    .sidebar a.active {
      background-color: #0d6efd;
    }
    .main-content {
      flex-grow: 1;
      padding: 30px;
      background-color: #f8f9fa;
    }
    .sidebar .logo {
      font-weight: bold;
      font-size: 1.3rem;
      padding: 16px;
      border-bottom: 1px solid #495057;
    }
    .table-hover tbody tr:hover {
      background-color: rgba(0, 123, 255, 0.05);
    }
  </style>
  
  <!-- 추가 CSS -->
  <?php foreach ($additional_css as $css): ?>
    <link rel="stylesheet" href="<?= $css ?>">
  <?php endforeach; ?>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">
    <a href="<?= t_url('index.php') ?>" class="text-white text-decoration-none">
      희망씨 관리자
    </a>
  </div>
  
  <?php
  // 메뉴 활성화 로직
  function is_menu_active($menu_dir, $menu_page = '') {
    global $current_dir, $current_page;
    
    if (!empty($menu_page)) {
      return $current_dir === $menu_dir && $current_page === $menu_page;
    }
    
    return $current_dir === $menu_dir || 
           ($menu_dir === 'index' && $current_page === 'index.php');
  }
  ?>
  
  <a href="<?= t_url('index.php') ?>" <?= is_menu_active('index') ? 'class="active"' : '' ?>>
    📊 대시보드
  </a>
  <a href="<?= t_url('posts/list.php') ?>" <?= is_menu_active('posts') ? 'class="active"' : '' ?>>
    📝 게시글 관리
  </a>
  <a href="<?= t_url('boards/list.php') ?>" <?= is_menu_active('boards') ? 'class="active"' : '' ?>>
    📋 게시판 관리
  </a>
  <a href="<?= t_url('menu/list.php') ?>" <?= is_menu_active('menu') ? 'class="active"' : '' ?>>
    🧭 메뉴 관리
  </a>
  <a href="<?= t_url('inquiries/list.php') ?>" <?= is_menu_active('inquiries') ? 'class="active"' : '' ?>>
    📬 문의 관리
  </a>
  <a href="<?= t_url('events/list.php') ?>" <?= is_menu_active('events') ? 'class="active"' : '' ?>>
    📅 행사 관리
  </a>
  <a href="<?= t_url('files/list.php') ?>" <?= is_menu_active('files') ? 'class="active"' : '' ?>>
    📎 자료실 관리
  </a>
  <a href="<?= t_url('settings/site_settings.php') ?>" <?= is_menu_active('settings') ? 'class="active"' : '' ?>>
    🎨 디자인 설정
  </a>
  <a href="<?= t_url('settings/simple-color-settings.php') ?>" <?= is_menu_active('settings', 'simple-color-settings.php') ? 'class="active"' : '' ?>>
    🎨 테마 설정
  </a>
  <a href="<?= t_url('system/performance.php') ?>" <?= is_menu_active('system') ? 'class="active"' : '' ?>>
    ⚡ 성능 모니터링
  </a>
  <a href="<?= t_url('logout.php') ?>">
    🚪 로그아웃
  </a>
</div>

<!-- 메인 컨텐츠 -->
<div class="main-content">
  <!-- 브레드크럼 -->
  <?php if (!empty($breadcrumb)): ?>
    <?php t_render_component('breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <?php endif; ?>
  
  <!-- 페이지 헤더 -->
  <?php if (!isset($show_page_header) || $show_page_header !== false): ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= t_escape($title ?? '페이지') ?></h2>
    <?php if (isset($page_actions)): ?>
      <div class="page-actions">
        <?= $page_actions ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <!-- 메시지 표시 -->
  <?php t_render_component('alerts'); ?>
  
  <!-- 메인 컨텐츠 -->
  <?php if (isset($_GET['debug'])): ?>
    <div class="alert alert-warning">
      <h5>🔧 레이아웃 디버그 정보</h5>
      <p>컨텐츠 길이: <?= strlen($content ?? '') ?> 문자</p>
      <p>컨텐츠 있음: <?= !empty($content) ? '✅ 예' : '❌ 아니요' ?></p>
      <p>현재 시간: <?= date('Y-m-d H:i:s') ?></p>
    </div>
  <?php endif; ?>
  
  <?= $content ?? '<div class="alert alert-danger">컨텐츠가 없습니다.</div>' ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 메뉴 디버깅 스크립트 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== 메뉴 디버깅 시작 ===');
    
    // 사이드바의 모든 링크 찾기
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    console.log('찾은 메뉴 링크 수:', sidebarLinks.length);
    
    sidebarLinks.forEach(function(link, index) {
        const originalHTML = link.innerHTML;
        console.log(`메뉴 ${index}: "${link.textContent}" - HTML: "${originalHTML}"`);
        
        // 각 링크에 MutationObserver 추가 (변경 감지용)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    console.log('🚨 메뉴 변경 감지:', {
                        target: mutation.target,
                        type: mutation.type,
                        oldValue: mutation.oldValue,
                        newValue: link.innerHTML,
                        textContent: link.textContent
                    });
                }
            });
        });
        
        observer.observe(link, {
            childList: true,
            subtree: true,
            characterData: true,
            characterDataOldValue: true
        });
        
        // 클릭 이벤트 모니터링
        link.addEventListener('click', function(e) {
            console.log('🖱️ 메뉴 클릭:', link.textContent, 'URL:', link.href);
        });
    });
});
</script>


<!-- 추가 JavaScript -->
<?php foreach ($additional_js as $js): ?>
  <script src="<?= $js ?>"></script>
<?php endforeach; ?>

</body>
</html>