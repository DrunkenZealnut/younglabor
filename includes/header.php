<?php
/** Shared public header. */
require_once __DIR__ . '/SiteContent.php';
if (!isset($currentPage)) $currentPage = 'home';
if (!isset($pageTitle)) $pageTitle = $site['name'];
if (!isset($pageDescription)) $pageDescription = $site['slogan'];
if (!isset($pageUrl)) $pageUrl = url($currentPage === 'home' ? '' : $currentPage);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <script>document.documentElement.classList.add('js')</script>
    <link rel="stylesheet" href="<?php echo assetUrl('assets/css/style.css'); ?>">
    <style>:root { <?php echo getThemeCSSVariables($theme); ?> }</style>
</head>
<body>
    <a class="skip-link" href="#main-content">본문으로 건너뛰기</a>
    <header class="header">
        <div class="header-inner">
            <a href="<?php echo url('/'); ?>" class="logo"><?php echo htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8'); ?></a>
            <button class="mobile-menu-btn" type="button" onclick="toggleMenu()" aria-controls="nav" aria-expanded="false" aria-label="메뉴 열기">☰</button>
            <nav class="nav" id="nav" aria-label="주요 메뉴">
                <a href="<?php echo url('about'); ?>"<?php echo $currentPage === 'about' ? ' class="active" aria-current="page"' : ''; ?>>우리는 누구인가</a>
                <a href="<?php echo url('activities'); ?>"<?php echo $currentPage === 'activities' ? ' class="active" aria-current="page"' : ''; ?>>우리가 하는 일</a>
                <a href="<?php echo url('activity'); ?>"<?php echo $currentPage === 'activity' ? ' class="active" aria-current="page"' : ''; ?>>활동게시판</a>
                <a href="<?php echo url('press'); ?>"<?php echo $currentPage === 'press' ? ' class="active" aria-current="page"' : ''; ?>>언론보도</a>
                <a href="<?php echo url('resources'); ?>"<?php echo $currentPage === 'resources' ? ' class="active" aria-current="page"' : ''; ?>>자료실</a>
                <a href="<?php echo url('tools'); ?>"<?php echo $currentPage === 'tools' ? ' class="active" aria-current="page"' : ''; ?>>안전 도구</a>
                <a href="<?php echo url('/'); ?>#contact" class="nav-cta">함께하기</a>
            </nav>
        </div>
    </header>
    <main id="main-content">
