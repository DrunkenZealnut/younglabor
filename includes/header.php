<?php
/** Shared public header. */
require_once __DIR__ . '/SiteContent.php';
require_once __DIR__ . '/WorkPresenter.php';
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
            <a href="<?php echo url('/'); ?>" class="logo"><span class="logo-short">청쎈<span aria-hidden="true" class="logo-dot">.</span></span><span class="logo-full">청년노동자<br>인권센터</span></a>
            <button class="mobile-menu-btn" type="button" onclick="toggleMenu()" aria-controls="nav" aria-expanded="false" aria-label="메뉴 열기">☰</button>
            <nav class="nav" id="nav" aria-label="주요 메뉴">
                <?php foreach (['about'=>'단체소개','work'=>'사업소개','youth-labor'=>'청년과노동','board'=>'게시판'] as $path=>$label): ?>
                <a href="<?php echo publicEscape(url($path)); ?>"<?php echo $currentPage===$path?' class="active" aria-current="page"':''; ?>><?php echo $label; ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>
    <main id="main-content">
