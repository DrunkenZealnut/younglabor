<?php
/**
 * MSDS 상세정보 페이지
 * 화학물질 16개 항목 상세 정보 표시
 */

require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/includes/Database.php';
require_once dirname(__DIR__) . '/includes/PageTracker.php';
PageTracker::track('MSDS 상세정보');
require_once __DIR__ . '/MsdsApiClient.php';

$client = new MsdsApiClient();

// 화학물질 ID
$chemId = $_GET['id'] ?? '';

if (empty($chemId)) {
    header('Location: ' . getMsdsUrl());
    exit;
}

// 선택된 섹션 (기본: 전체)
$selectedSection = $_GET['section'] ?? 'all';

// 기본 정보 (화학물질 목록에서 검색)
$basicInfo = null;

// 상세 정보 로드
$details = [];

if ($selectedSection === 'all') {
    // 전체 섹션 로드
    foreach (array_keys($MSDS_DETAIL_SECTIONS) as $section) {
        $result = $client->getChemicalDetail($chemId, $section);
        if ($result['success']) {
            $details[$section] = $client->organizeDetailItems($result['items']);
        }
    }
} else {
    // 특정 섹션만 로드
    $result = $client->getChemicalDetail($chemId, $selectedSection);
    if ($result['success']) {
        $details[$selectedSection] = $client->organizeDetailItems($result['items']);
    }
}

// 제품명 추출 (섹션 01에서)
$productName = '화학물질 상세정보';
if (isset($details['01'])) {
    foreach ($details['01'] as $item) {
        if (strpos($item['name'], '제품명') !== false && !empty($item['detail'])) {
            $productName = $item['detail'];
            break;
        }
    }
}

// 표준화된 구조: head.php에서 header 자동 포함
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productName); ?> - MSDS 상세정보</title>

    <!-- Pretendard 폰트 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css">
</head>
<body>

<!-- Header -->
<header class="site-header">
    <div class="header-inner">
        <a href="<?php echo function_exists('url') ? url() : '../'; ?>" class="logo"><?php echo htmlspecialchars($site['name'] ?? '청년노동자인권센터'); ?></a>
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="메뉴 열기">☰</button>
        <nav class="nav" id="nav">
            <a href="<?php echo function_exists('url') ? url() : '../'; ?>">홈</a>
            <a href="<?php echo function_exists('url') ? url() : '../'; ?>#mission">미션</a>
            <a href="<?php echo function_exists('url') ? url() : '../'; ?>#services">핵심사업</a>
            <a href="<?php echo function_exists('url') ? url('#contact') : '../#contact'; ?>">연락하기</a>
        </nav>
    </div>
</header>

<style>
    :root {
        <?php
        if (isset($theme) && function_exists('getThemeCSSVariables')) {
            echo getThemeCSSVariables($theme);
        }
        ?>
    }

    body {
        font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
        margin: 0;
        padding: 0;
        background: var(--color-background, #E8F4F8);
    }

    /* Site Header */
    .site-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .site-header .header-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-primary, #5BC0DE);
        text-decoration: none;
    }

    .nav {
        display: flex;
        gap: 2rem;
    }

    .nav a {
        text-decoration: none;
        color: var(--color-text-dark, #333333);
        font-weight: 500;
        transition: color 0.3s;
    }

    .nav a:hover {
        color: var(--color-primary, #5BC0DE);
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--color-text-dark, #333333);
        padding: 0.5rem;
    }

    /* Mobile Navigation */
    @media (max-width: 768px) {
        .site-header .header-inner {
            padding: 0.75rem 1rem;
        }

        .logo {
            font-size: 1.1rem;
        }

        .nav {
            position: fixed;
            top: 56px;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            flex-direction: column;
            padding: 1rem;
            gap: 0;
            display: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            max-height: calc(100vh - 56px);
            overflow-y: auto;
        }

        .nav.active {
            display: flex;
        }

        .nav a {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: block;
        }

        .nav a:last-child {
            border-bottom: none;
        }

        .mobile-menu-btn {
            display: block;
        }
    }

    .msds-detail-page {
        background: var(--color-background-alt, #FFFFFF);
        min-height: 70vh;
        padding-top: 56px;
    }

    .msds-detail-header {
        background: linear-gradient(135deg, var(--color-primary, #5BC0DE) 0%, var(--color-primary-dark, #3498DB) 100%);
        color: white;
        padding: 1.5rem 0;
    }

    .msds-detail-header .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .back-link {
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        opacity: 0.9;
        transition: opacity 0.3s;
    }

    .back-link:hover {
        opacity: 1;
    }

    .header-title h1 {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }

    .header-title p {
        font-size: 0.85rem;
        opacity: 0.8;
        margin: 0;
    }

    .print-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: var(--color-primary, #5BC0DE);
        border: 2px solid white;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .print-btn:hover {
        background: transparent;
        color: white;
    }

    .msds-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .msds-detail-main {
        display: flex;
        gap: 2rem;
        padding: 2rem 0;
    }

    /* 사이드바 네비게이션 */
    .sidebar {
        width: 280px;
        flex-shrink: 0;
    }

    .sidebar-sticky {
        position: sticky;
        top: 100px;
    }

    .nav-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .nav-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .msds-nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .msds-nav-item {
        margin-bottom: 0.25rem;
        width: 100%;
    }

    .msds-nav-link {
        display: block;
        padding: 0.75rem 1rem;
        color: #666;
        text-decoration: none;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .msds-nav-link:hover {
        background: rgba(58,122,78,0.1);
        color: var(--color-primary, #5BC0DE);
    }

    .msds-nav-link.active {
        background: linear-gradient(135deg, var(--theme-hero-bg-start, #1f3b2d) 0%, var(--theme-hero-bg-end, #3a7a4e) 100%);
        color: white;
    }

    .msds-nav-num {
        display: inline-block;
        width: 24px;
        height: 24px;
        line-height: 24px;
        text-align: center;
        background: rgba(0,0,0,0.1);
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }

    .msds-nav-link.active .msds-nav-num {
        background: rgba(255,255,255,0.2);
    }

    /* 컨텐츠 영역 */
    .content {
        flex: 1;
        min-width: 0;
    }

    .section-card {
        background: white;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, var(--theme-hero-bg-start, #1f3b2d) 0%, var(--theme-hero-bg-end, #3a7a4e) 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }

    .section-body {
        padding: 1.5rem;
    }

    .detail-item {
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-item.level-1 {
        padding-left: 0;
    }

    .detail-item.level-2 {
        padding-left: 1.5rem;
    }

    .detail-item.level-3 {
        padding-left: 3rem;
    }

    .detail-name {
        font-weight: 600;
        color: var(--color-primary-dark, #3498DB);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-name .level-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--color-primary, #5BC0DE);
    }

    .detail-item.level-2 .level-indicator {
        background: var(--color-secondary, #87CEEB);
    }

    .detail-item.level-3 .level-indicator {
        background: var(--color-accent, #F0A500);
    }

    .detail-value {
        color: #444;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.8;
    }

    .detail-value:empty::after {
        content: '자료없음';
        color: #999;
        font-style: italic;
    }

    /* 빈 상태 */
    .empty-section {
        padding: 3rem;
        text-align: center;
        color: #999;
    }

    /* 데이터 출처 */
    .msds-source {
        background: rgba(0,0,0,0.05);
        color: #666;
        padding: 1.5rem;
        margin-top: 2rem;
        text-align: center;
        border-radius: 12px;
    }

    .msds-source p {
        font-size: 0.85rem;
        margin: 0;
    }

    .msds-source a {
        color: var(--color-primary, #5BC0DE);
    }

    /* 반응형 */
    @media (max-width: 992px) {
        .msds-detail-main {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
        }

        .sidebar-sticky {
            position: static;
        }

        .nav-card {
            overflow: hidden;
        }

        .msds-nav-list {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .msds-nav-item {
            margin-bottom: 0;
            flex: 0 0 auto;
            width: auto;
        }

        .msds-nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .msds-nav-num {
            width: 20px;
            height: 20px;
            line-height: 20px;
            font-size: 0.7rem;
            margin-right: 0.25rem;
        }
    }

    @media (max-width: 768px) {
        .msds-detail-header .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }

    @media print {
        .site-header, .sidebar, .site-footer, .print-btn, .msds-detail-header {
            display: none !important;
        }

        .msds-detail-main {
            padding: 0;
        }

        .content {
            width: 100%;
        }

        .section-card {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
            margin-bottom: 1rem;
        }
    }
</style>

<main id="main" role="main" class="flex-1">
    <div class="msds-detail-page">
        <!-- 서브 헤더 -->
        <div class="msds-detail-header">
            <div class="header-content">
                <a href="<?php echo getMsdsUrl(); ?>" class="back-link">
                    ← 검색으로 돌아가기
                </a>
                <div class="header-title">
                    <h1><?php echo htmlspecialchars($productName); ?></h1>
                    <p>물질 ID: <?php echo htmlspecialchars($chemId); ?></p>
                </div>
                <button onclick="window.print()" class="print-btn">
                    인쇄
                </button>
            </div>
        </div>

        <div class="msds-detail-container">
            <div class="msds-detail-main">
                <aside class="sidebar">
                    <div class="sidebar-sticky">
                        <nav class="nav-card">
                            <h3 class="nav-title">MSDS 항목</h3>
                            <ul class="msds-nav-list">
                                <li class="msds-nav-item">
                                    <a href="?id=<?php echo urlencode($chemId); ?>&section=all"
                                       class="msds-nav-link <?php echo $selectedSection === 'all' ? 'active' : ''; ?>">
                                        <span class="msds-nav-num">All</span>
                                        전체 보기
                                    </a>
                                </li>
                                <?php foreach ($MSDS_DETAIL_SECTIONS as $num => $title): ?>
                                <li class="msds-nav-item">
                                    <a href="?id=<?php echo urlencode($chemId); ?>&section=<?php echo $num; ?>"
                                       class="msds-nav-link <?php echo $selectedSection === $num ? 'active' : ''; ?>">
                                        <span class="msds-nav-num"><?php echo (int)$num; ?></span>
                                        <?php echo htmlspecialchars(mb_substr($title, 0, 12)); ?><?php echo mb_strlen($title) > 12 ? '...' : ''; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </div>
                </aside>

                <div class="content">
                    <?php if (empty($details)): ?>
                    <div class="section-card">
                        <div class="empty-section">
                            <p>상세 정보를 불러올 수 없습니다.</p>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php foreach ($details as $sectionNum => $items): ?>
                        <div class="section-card" id="section-<?php echo $sectionNum; ?>">
                            <div class="section-header">
                                <span class="section-number"><?php echo (int)$sectionNum; ?></span>
                                <h2 class="section-title">
                                    <?php echo htmlspecialchars($MSDS_DETAIL_SECTIONS[$sectionNum] ?? "섹션 $sectionNum"); ?>
                                </h2>
                            </div>
                            <div class="section-body">
                                <?php if (empty($items)): ?>
                                <div class="empty-section">
                                    <p>이 항목에 대한 정보가 없습니다.</p>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                    <div class="detail-item level-<?php echo $item['level']; ?>">
                                        <div class="detail-name">
                                            <span class="level-indicator"></span>
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($item['detail'])); ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- 데이터 출처 -->
                    <div class="msds-source">
                        <p>
                            데이터 출처: <a href="https://msds.kosha.or.kr" target="_blank">안전보건공단 화학물질정보시스템</a>
                            | 본 정보는 참고용이며, 정확한 정보는 공식 MSDS를 확인하세요.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // 스무스 스크롤
    document.querySelectorAll('.msds-nav-link[href*="section="]').forEach(link => {
        if (new URL(link.href).searchParams.get('section') !== 'all') {
            link.addEventListener('click', function(e) {
                const section = new URL(this.href).searchParams.get('section');
                const target = document.getElementById('section-' + section);
                if (target && document.querySelectorAll('.section-card').length > 1) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.pushState(null, '', this.href);
                }
            });
        }
    });
</script>

<!-- Mobile Menu Script -->
<script>
(function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const nav = document.getElementById('nav');

    // Toggle menu
    mobileMenuBtn.addEventListener('click', function() {
        nav.classList.toggle('active');
        // Update button text
        this.textContent = nav.classList.contains('active') ? '✕' : '☰';
        this.setAttribute('aria-label', nav.classList.contains('active') ? '메뉴 닫기' : '메뉴 열기');
    });

    // Close menu when clicking on a link
    nav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            nav.classList.remove('active');
            mobileMenuBtn.textContent = '☰';
            mobileMenuBtn.setAttribute('aria-label', '메뉴 열기');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!nav.contains(e.target) && !mobileMenuBtn.contains(e.target) && nav.classList.contains('active')) {
            nav.classList.remove('active');
            mobileMenuBtn.textContent = '☰';
            mobileMenuBtn.setAttribute('aria-label', '메뉴 열기');
        }
    });
})();
</script>

<?php
// 표준화된 구조: footer와 tail은 tail.php에서 자동 포함됨
?>

</body>
</html>
