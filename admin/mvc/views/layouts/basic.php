<?php
/**
 * Basic Layout - 기본 헤더/푸터 레이아웃
 * 
 * 필수 변수:
 * - $content: 메인 컨텐츠
 * 
 * 선택 변수:
 * - $title: 페이지 제목 (기본값: '관리자 페이지')
 * - $additional_css: 추가 CSS 파일 배열
 * - $additional_js: 추가 JS 파일 배열
 * - $show_navbar: 네비게이션 바 표시 여부 (기본값: true)
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) session_start();

// 기본값 설정
$title = isset($title) ? $title : '관리자 페이지';
$additional_css = isset($additional_css) ? $additional_css : [];
$additional_js = isset($additional_js) ? $additional_js : [];
$show_navbar = isset($show_navbar) ? $show_navbar : true;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?= t_escape($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- 기본 스타일 -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
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

<?php if ($show_navbar): ?>
<!-- 상단 네비게이션 -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= t_url('index.php') ?>">희망씨 관리자</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= t_url('posts/list.php">게시글</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= t_url('inquiries/list.php">문의</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= t_url('events/list.php">행사</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= t_url('settings/site_settings.php') ?>">디자인설정</a>
        </li>
      </ul>
      <?php if (isset($_SESSION['admin_logged_in'])): ?>
        <a class="btn btn-sm btn-outline-light" href="<?= t_url('logout.php') ?>">로그아웃</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php endif; ?>

<!-- 메인 컨테이너 -->
<div class="container mt-4">
    <!-- 메시지 표시 -->
    <?php t_render_component('alerts'); ?>
    
    <!-- 메인 컨텐츠 -->
    <?= $content ?? '' ?>
</div>

<!-- 푸터 -->
<footer class="bg-light text-center py-3 mt-5">
    <small>&copy; 2025 우리동네노동권찾기 관리자</small>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 추가 JavaScript -->
<?php foreach ($additional_js as $js): ?>
  <script src="<?= $js ?>"></script>
<?php endforeach; ?>

</body>
</html>