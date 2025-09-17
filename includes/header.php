<?php
// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSS 모드 매니저 로드
require_once __DIR__ . '/css-mode-manager.php';
$cssMode = getCSSMode();

// CSS 모드에 따른 분기 처리
if ($cssMode->isOptimizedMode()) {
    // 최적화된 헤더 로드
    include __DIR__ . '/header-optimized.php';
    return; // 여기서 종료
}

// Legacy 모드 성능 최적화 활성화 체크
$legacyOptimized = isset($_GET['legacy_optimized']) || 
                  (isset($_COOKIE['legacy_optimized']) && $_COOKIE['legacy_optimized'] === 'true') ||
                  (defined('LEGACY_OPTIMIZED_DEFAULT') && LEGACY_OPTIMIZED_DEFAULT);

if ($legacyOptimized) {
    // Legacy 최적화된 헤더 로드
    include __DIR__ . '/header-legacy-optimized.php';
    return; // 여기서 종료
}

// Legacy 모드: 기존 Natural Green 테마 로드
require_once __DIR__ . '/NaturalGreenThemeLoader.php';
$theme = getNaturalGreenTheme();

// 테마 URL 정의 (단일 테마)
$themeUrl = app_url('theme/natural-green');
$siteUrl = app_url();

// 현재 페이지 변수 설정
$currentPage = isset($_GET['page']) ? $_GET['page'] : (isset($currentSlug) ? $currentSlug : 'home');
$pageTitle = isset($pageTitle) ? $pageTitle : $theme->getSiteName();
$metaDescription = isset($pageDescription) ? $pageDescription : $theme->getSiteDescription();
?>
<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta http-equiv="Last-Modified" content="<?= gmdate('D, d M Y H:i:s T') ?>" />
    <meta name="cache-buster" content="<?= time() . '-' . rand(1000,9999) ?>" />
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <link rel="canonical" href="<?= htmlspecialchars($siteUrl . '/', ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="language" content="ko" />
    <meta property="og:locale" content="ko_KR" />
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Remodal CSS (팝업 라이브러리) -->
    <link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal.css">
    <link rel="stylesheet" href="<?= $siteUrl ?>/js/remodal/remodal-default-theme.css">
    
    <!-- Remodal JavaScript (팝업 라이브러리) -->
    <script src="<?= $siteUrl ?>/js/remodal/remodal.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <?php
    // Natural Green 단일 테마 CSS 로드
    renderNaturalGreenTheme();
    ?>
    
    <!-- Natural Green 테마 시스템 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 테마 로딩 상태 확인
        const themeCSS = document.getElementById('natural-green-theme');
        
        if (themeCSS) {
            console.log('🎨 Natural Green 테마 CSS 로드됨:', themeCSS.href);
        }
        
        // 테마 정보 확인
        if (window.HOPEC_THEME) {
            console.log('🎨 테마 정보:', window.HOPEC_THEME);
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
            console.log('🎯 Primary 색상:', primaryColor);
        }
    });
    </script>
    
    <?= csrf_field() ?>
  </head>
  <body class="min-vh-100 d-flex flex-column" style="font-family: 'Noto Sans KR', sans-serif; background-color: var(--background); color: var(--foreground);">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:m-4 focus:p-2 focus:bg-white focus:border focus:border-gray-400">본문 바로가기</a>
    <?php 
    // Natural Green 네비게이션 헤더만 포함 (HTML 문서 구조는 제외)
    $naturalGreenNavigation = HOPEC_BASE_PATH . '/theme/natural-green/includes/navigation.php';
    
    if (file_exists($naturalGreenNavigation)) {
        try {
            include $naturalGreenNavigation;
        } catch (Exception $e) {
            // 네비게이션 로드 실패시 fallback
            echo '<!-- Navigation load error: ' . $e->getMessage() . ' -->';
            echo '<nav class="navbar navbar-expand-lg" style="background-color: var(--primary);">
                    <div class="container">
                        <a class="navbar-brand text-white" href="/">희망씨</a>
                        <div class="navbar-nav">
                            <a class="nav-link text-white" href="/about/about.php">소개</a>
                            <a class="nav-link text-white" href="/community/gallery.php">갤러리</a>
                            <a class="nav-link text-white" href="/community/newsletter.php">소식지</a>
                        </div>
                    </div>
                  </nav>';
        }
    } else {
        // Fallback: 기본 네비게이션
        echo '<!-- Navigation file not found: ' . $naturalGreenNavigation . ' -->';
        echo '<nav class="navbar navbar-expand-lg" style="background-color: var(--primary);">
                <div class="container">
                    <a class="navbar-brand text-white" href="/">희망씨</a>
                    <div class="navbar-nav">
                        <a class="nav-link text-white" href="/about/about.php">소개</a>
                        <a class="nav-link text-white" href="/community/gallery.php">갤러리</a>
                        <a class="nav-link text-white" href="/community/newsletter.php">소식지</a>
                    </div>
                </div>
              </nav>';
    }
    ?>
    <div id="wrapper"><div id="container_wr"><div id="container">
<?php