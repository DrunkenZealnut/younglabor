<?php
/**
 * 통합 헤더 시스템
 * 단일 진입점으로 모든 헤더 로딩을 관리
 * 기존의 복잡한 분기 처리를 단순화
 * 
 * Version: 2.0.0
 * Author: SuperClaude CSS Optimization System
 */

// 통합 CSS 로더 사용
require_once __DIR__ . '/css-unified-loader.php';

// 기본 설정
$pageType = $_GET['page'] ?? (isset($currentSlug) ? $currentSlug : 'home');
$theme = $config['cf_theme'] ?? 'natural-green';
$pageTitle = isset($pageTitle) ? $pageTitle : '희망연대노동조합';
$pageDescription = isset($pageDescription) ? $pageDescription : '노동자의 권익을 위한 희망연대노동조합';

// 통합 CSS 로딩
loadUnifiedCSS($pageType, $theme);
?>

<!-- 페이지 메타데이터 -->
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta name="language" content="ko">
<meta property="og:locale" content="ko_KR">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">

<!-- 캐시 제어 (개발 환경) -->
<?php if (defined('HOPEC_DEBUG') && HOPEC_DEBUG): ?>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<?php endif; ?>

<!-- CSRF 토큰 -->
<?= csrf_field() ?>

</head>
<body>
    <!-- 접근성 개선 -->
    <a href="#main" class="sr-only focus:not-sr-only">본문 바로가기</a>
    
    <!-- 네비게이션 -->
    <?php include __DIR__ . '/navigation-unified.php'; ?>
    
    <!-- 메인 컨테이너 시작 -->
    <div id="wrapper">
        <div id="container_wr">
            <div id="container">
                <main id="main">