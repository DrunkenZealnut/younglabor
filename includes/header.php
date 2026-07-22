<?php
/**
 * 공통 헤더
 * 사용법: 각 페이지에서 $currentPage 변수 설정 후 include
 * 예: $currentPage = 'about'; require_once __DIR__ . '/includes/header.php';
 */
if (!isset($currentPage)) $currentPage = 'home';
if (!isset($pageTitle)) $pageTitle = $site['name'];
if (!isset($pageDescription)) $pageDescription = $site['slogan'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($site['base_url']); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=2">

    <style>
        :root {
            <?php echo getThemeCSSVariables($theme); ?>
        }
    </style>
</head>
<body>
    <header class="header" role="banner">
        <div class="header-inner">
            <a href="<?php echo url('/'); ?>" class="logo"><?php echo htmlspecialchars($site['name']); ?></a>
            <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="메뉴 열기">☰</button>
            <nav class="nav" id="nav" role="navigation" aria-label="메인 네비게이션">
                <a href="<?php echo url('about'); ?>"<?php echo $currentPage === 'about' ? ' class="active"' : ''; ?>>단체소개</a>
                <a href="<?php echo url('activities'); ?>"<?php echo $currentPage === 'activities' ? ' class="active"' : ''; ?>>사업소개</a>
                <a href="<?php echo url('news'); ?>"<?php echo $currentPage === 'news' ? ' class="active"' : ''; ?>>소식</a>
                <a href="<?php echo url('committee'); ?>" class="nav-cta">동아리 신청</a>
            </nav>
        </div>
    </header>

    <main role="main">
