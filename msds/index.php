<?php
/**
 * MSDS 검색 메인 페이지
 * 물질안전보건자료 검색 시스템
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/MsdsApiClient.php';

$client = new MsdsApiClient();

// 검색 파라미터
$searchWrd = trim($_GET['search'] ?? '');
$searchCnd = (int)($_GET['type'] ?? MSDS_SEARCH_BY_NAME);
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$numOfRows = 10;

// 검색 결과
$searchResult = null;
$hasSearched = !empty($searchWrd);

if ($hasSearched) {
    $searchResult = $client->searchChemicals($searchWrd, $searchCnd, $pageNo, $numOfRows);
}

// 페이지네이션 계산
$totalPages = 0;
if ($searchResult && $searchResult['success'] && $searchResult['totalCount'] > 0) {
    $totalPages = ceil($searchResult['totalCount'] / $numOfRows);
}

// 표준화된 구조: head.php에서 header 자동 포함
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>물질안전보건자료 검색 - <?php echo htmlspecialchars($site['name'] ?? '청년노동자인권센터'); ?></title>

    <!-- Pretendard 폰트 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css">
</head>
<body>

<!-- Header -->
<header class="header">
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

        /* Header */
        .header {
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

        .header-inner {
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
            .header-inner {
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

        .msds-page {
            background: linear-gradient(135deg, var(--color-background, #E8F4F8) 0%, var(--color-secondary, #87CEEB) 100%);
            min-height: 70vh;
            padding: 5rem 0 2rem;
        }

        .msds-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .search-hero {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--color-text-dark, #333333);
        }

        .search-hero h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--color-primary, #5BC0DE);
        }

        .search-hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            color: var(--color-text-dark, #333333);
        }

        .search-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--color-primary, #5BC0DE);
            box-shadow: 0 0 0 3px rgba(91, 192, 222, 0.1);
        }

        .search-input-group {
            flex: 3;
            min-width: 300px;
        }

        .search-btn {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, var(--color-primary, #5BC0DE) 0%, var(--color-primary-dark, #3498DB) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(91, 192, 222, 0.3);
        }

        .quick-search {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .quick-search-title {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.75rem;
        }

        .quick-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .quick-tag {
            padding: 0.5rem 1rem;
            background: #f5f5f5;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
        }

        .quick-tag:hover {
            background: var(--color-primary, #5BC0DE);
            color: white;
        }

        /* 검색 결과 */
        .results-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .results-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--color-primary-dark, #3498DB);
        }

        .results-count {
            font-size: 0.9rem;
            color: #666;
        }

        .results-count strong {
            color: var(--color-primary, #5BC0DE);
        }

        .result-item {
            padding: 1.5rem;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .result-item:hover {
            border-color: var(--color-primary, #5BC0DE);
            box-shadow: 0 5px 20px rgba(43,93,62,0.1);
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .result-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-primary-dark, #3498DB);
            margin-bottom: 0.25rem;
        }

        .result-date {
            font-size: 0.8rem;
            color: #999;
        }

        .result-detail-btn {
            padding: 0.5rem 1.25rem;
            background: linear-gradient(135deg, var(--color-primary, #5BC0DE) 0%, var(--color-primary-dark, #3498DB) 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .result-detail-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 192, 222, 0.3);
        }

        .result-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.75rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 0.9rem;
            color: #333;
            font-family: 'SF Mono', 'Monaco', monospace;
        }

        /* 페이지네이션 */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: all 0.3s;
        }

        .page-link:hover {
            border-color: var(--color-primary, #5BC0DE);
            color: var(--color-primary, #5BC0DE);
        }

        .page-link.active {
            background: linear-gradient(135deg, var(--color-primary, #5BC0DE) 0%, var(--color-primary-dark, #3498DB) 100%);
            border-color: transparent;
            color: white;
        }

        .page-link.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* 빈 상태 */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            color: var(--color-primary-dark, #3498DB);
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: #666;
        }

        /* 정보 카드 */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .info-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .info-card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .info-card h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .info-card p {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        /* 데이터 출처 */
        .msds-source {
            background: rgba(0,0,0,0.2);
            color: white;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
            border-radius: 12px;
        }

        .msds-source p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin: 0;
        }

        .msds-source a {
            color: white;
            text-decoration: underline;
        }

        /* 반응형 */
        @media (max-width: 768px) {
            .search-hero h2 {
                font-size: 1.75rem;
            }

            .search-form {
                flex-direction: column;
            }

            .form-group,
            .search-input-group {
                min-width: 100%;
            }

            .result-header {
                flex-direction: column;
                gap: 1rem;
            }

            .result-detail-btn {
                align-self: flex-start;
            }
        }
        /* 카메라 분석 섹션 */
        .camera-section {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }

        .camera-section h3 {
            color: var(--color-primary-dark, #3498DB);
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .camera-section p {
            color: var(--color-text-dark, #333333);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .camera-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--color-accent, #F0A500) 0%, #f7931e 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(240, 165, 0, 0.3);
        }

        .camera-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(240, 165, 0, 0.4);
        }

        .camera-btn svg {
            width: 24px;
            height: 24px;
        }

        /* 카메라 모달 */
        .camera-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.95);
            z-index: 9999;
            padding: 0.5rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .camera-modal.active {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 600px;
            padding: 0.5rem 0;
            color: white;
            flex-shrink: 0;
        }

        .modal-header h3 {
            font-size: 1.1rem;
            margin: 0;
        }

        .modal-close {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.2);
        }

        .camera-container {
            width: 100%;
            max-width: 600px;
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
        }

        .camera-preview {
            width: 100%;
            aspect-ratio: 4/3;
            background: #000;
            display: flex;
            max-height: 50vh;
        }

        /* 모바일 카메라 모달 최적화 */
        @media (max-width: 480px) {
            .camera-modal {
                padding: 0.25rem;
            }

            .modal-header {
                padding: 0.25rem 0.5rem;
            }

            .modal-header h3 {
                font-size: 1rem;
            }

            .camera-container {
                border-radius: 8px;
            }

            .camera-preview {
                aspect-ratio: 1/1;
                max-height: 45vh;
            }
        }

        @media (max-height: 700px) {
            .camera-preview {
                aspect-ratio: 16/9;
                max-height: 40vh;
            }
        }

        .camera-preview video,
        .camera-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .camera-preview canvas {
            display: none;
        }

        /* 카메라 오버레이 결과 */
        .camera-overlay-result {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: none;
            flex-direction: column;
            padding: 1rem;
            overflow-y: auto;
            z-index: 5;
        }

        .camera-overlay-result.active {
            display: flex;
        }

        .overlay-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .overlay-header h4 {
            color: var(--color-accent, #F0A500);
            margin: 0;
            font-size: 1rem;
        }

        .overlay-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.25rem;
        }

        .overlay-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .overlay-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255,255,255,0.1);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border-left: 3px solid var(--color-accent, #F0A500);
        }

        .overlay-item .label {
            font-size: 0.75rem;
            color: #aaa;
            text-transform: uppercase;
        }

        .overlay-item .value {
            font-size: 1rem;
            color: white;
            font-weight: 600;
            text-align: right;
        }

        .overlay-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .overlay-tag {
            background: rgba(255,107,53,0.3);
            border: 1px solid var(--color-accent, #F0A500);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        .overlay-msds-btn {
            display: block;
            margin-top: auto;
            padding: 1rem;
            background: linear-gradient(135deg, var(--color-primary, #5BC0DE) 0%, var(--color-primary-dark, #3498DB) 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
        }

        .overlay-confidence {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(0,0,0,0.7);
            color: var(--color-accent, #F0A500);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .camera-placeholder {
            color: #666;
            text-align: center;
        }

        .camera-placeholder svg {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .camera-controls {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 1rem;
            background: #1a1a1a;
        }

        .control-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #333;
            border: none;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 70px;
            flex: 0 0 auto;
        }

        /* 모바일에서 카메라 컨트롤 2줄 배치 */
        @media (max-width: 480px) {
            .camera-controls {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
                padding: 0.75rem;
            }

            .control-btn {
                min-width: unset;
                padding: 0.6rem 0.5rem;
            }

            .control-btn svg {
                width: 24px;
                height: 24px;
            }

            .control-btn span {
                font-size: 0.65rem;
            }

            /* 분석 버튼을 두 번째 줄 중앙에 배치 */
            .control-btn#analyzeBtn {
                grid-column: 2;
            }
        }

        .control-btn:hover {
            background: #444;
        }

        .control-btn.primary {
            background: linear-gradient(135deg, var(--color-accent, #F0A500) 0%, #f7931e 100%);
        }

        .control-btn.primary:hover {
            transform: scale(1.05);
        }

        .control-btn svg {
            width: 28px;
            height: 28px;
        }

        .control-btn span {
            font-size: 0.75rem;
        }

        .control-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .control-btn.toggle-btn.active {
            background: linear-gradient(135deg, var(--color-primary, #5BC0DE) 0%, var(--color-primary-dark, #3498DB) 100%);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(91, 192, 222, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(91, 192, 222, 0);
            }
        }

        /* 파일 업로드 */
        .file-input {
            display: none;
        }

        /* 분석 결과 */
        .analysis-result {
            width: 100%;
            max-width: 600px;
            margin-top: 1rem;
            background: #1a1a1a;
            border-radius: 16px;
            padding: 1.5rem;
            color: white;
            display: none;
        }

        .analysis-result.active {
            display: block;
        }

        .result-section {
            margin-bottom: 1.5rem;
        }

        .result-section h4 {
            font-size: 1rem;
            color: var(--color-accent, #F0A500);
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #333;
        }

        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.75rem;
        }

        .result-item {
            background: #2a2a2a;
            padding: 0.75rem;
            border-radius: 8px;
        }

        .result-item .label {
            font-size: 0.7rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .result-item .value {
            font-size: 0.95rem;
            color: white;
            word-break: break-word;
        }

        .result-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .result-tag {
            background: #333;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #ccc;
        }

        .msds-result-item {
            background: #2a2a2a;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .msds-result-item:hover {
            border-color: var(--color-primary, #5BC0DE);
        }

        .msds-result-item h5 {
            color: white;
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
        }

        .msds-result-item .meta {
            font-size: 0.85rem;
            color: #888;
        }

        .view-detail-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--color-primary, #5BC0DE) 0%, var(--color-primary-dark, #3498DB) 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .view-detail-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 192, 222, 0.3);
        }

        /* 로딩 상태 */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 10;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255,255,255,0.2);
            border-top-color: var(--color-accent, #F0A500);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 0.9rem;
            color: #ccc;
        }

        /* 에러 메시지 */
        .error-message {
            background: #ff4444;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
        }

        .error-message.active {
            display: block;
        }

    </style>

<main id="main" role="main" class="flex-1">
    <div class="msds-page">
        <div class="msds-container">
            <?php if (!$hasSearched): ?>
            <section class="search-hero">
                <h2>화학물질 안전정보 검색</h2>
                <p>산업안전보건공단에서 제공하는 공식 MSDS 정보를 검색하세요. 화학물질의 유해성, 취급방법, 응급조치 등 16개 항목의 상세 정보를 확인할 수 있습니다.</p>
            </section>

            <!-- AI 카메라 분석 섹션 -->
            <section class="camera-section">
                <h3>AI 물질 식별</h3>
                <p>화학물질 라벨이나 MSDS를 촬영하면 AI가 자동으로 물질을 식별합니다</p>
                <button type="button" class="camera-btn" id="openCameraBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    카메라로 분석하기
                </button>
            </section>
            <?php endif; ?>

            <div class="search-card">
                <form action="" method="GET" class="search-form">
                    <div class="form-group">
                        <label for="type">검색 조건</label>
                        <select name="type" id="type">
                            <?php foreach ($MSDS_SEARCH_OPTIONS as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo $searchCnd === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group search-input-group">
                        <label for="search">검색어</label>
                        <input type="text" name="search" id="search"
                               placeholder="화학물질명, CAS No. 등을 입력하세요"
                               value="<?php echo htmlspecialchars($searchWrd); ?>"
                               required>
                    </div>

                    <button type="submit" class="search-btn">🔍 검색</button>
                </form>

                <div class="quick-search">
                    <p class="quick-search-title">빠른 검색</p>
                    <div class="quick-tags">
                        <a href="?search=벤젠&type=0" class="quick-tag">벤젠</a>
                        <a href="?search=톨루엔&type=0" class="quick-tag">톨루엔</a>
                        <a href="?search=아세톤&type=0" class="quick-tag">아세톤</a>
                        <a href="?search=메탄올&type=0" class="quick-tag">메탄올</a>
                        <a href="?search=에탄올&type=0" class="quick-tag">에탄올</a>
                        <a href="?search=염산&type=0" class="quick-tag">염산</a>
                        <a href="?search=황산&type=0" class="quick-tag">황산</a>
                        <a href="?search=71-43-2&type=1" class="quick-tag">CAS: 71-43-2</a>
                    </div>
                </div>
            </div>

            <?php if ($hasSearched): ?>
            <div class="results-card">
                <div class="results-header">
                    <h3 class="results-title">
                        "<?php echo htmlspecialchars($searchWrd); ?>" 검색 결과
                    </h3>
                    <?php if ($searchResult && $searchResult['success']): ?>
                    <span class="results-count">
                        총 <strong><?php echo number_format($searchResult['totalCount']); ?></strong>개
                    </span>
                    <?php endif; ?>
                </div>

                <?php if ($searchResult && $searchResult['success'] && !empty($searchResult['items'])): ?>
                    <?php foreach ($searchResult['items'] as $item): ?>
                    <div class="result-item">
                        <div class="result-header">
                            <div>
                                <h4 class="result-name">
                                    <?php echo htmlspecialchars($item['chemNameKor'] ?? '이름 없음'); ?>
                                </h4>
                                <span class="result-date">
                                    최종 갱신: <?php echo htmlspecialchars($item['lastDate'] ?? '-'); ?>
                                </span>
                            </div>
                            <a href="<?php echo getMsdsUrl('detail.php', ['id' => $item['chemId']]); ?>"
                               class="result-detail-btn">
                                상세보기 →
                            </a>
                        </div>
                        <div class="result-info">
                            <div class="info-item">
                                <span class="info-label">CAS No.</span>
                                <span class="info-value"><?php echo htmlspecialchars($item['casNo'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">UN No.</span>
                                <span class="info-value"><?php echo htmlspecialchars($item['unNo'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">KE No.</span>
                                <span class="info-value"><?php echo htmlspecialchars($item['keNo'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">EN No.</span>
                                <span class="info-value"><?php echo htmlspecialchars($item['enNo'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">물질 ID</span>
                                <span class="info-value"><?php echo htmlspecialchars($item['chemId'] ?? '-'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php if ($pageNo > 1): ?>
                        <a href="?search=<?php echo urlencode($searchWrd); ?>&type=<?php echo $searchCnd; ?>&page=1"
                           class="page-link">«</a>
                        <a href="?search=<?php echo urlencode($searchWrd); ?>&type=<?php echo $searchCnd; ?>&page=<?php echo $pageNo - 1; ?>"
                           class="page-link">‹</a>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $pageNo - 2);
                        $endPage = min($totalPages, $pageNo + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                        <a href="?search=<?php echo urlencode($searchWrd); ?>&type=<?php echo $searchCnd; ?>&page=<?php echo $i; ?>"
                           class="page-link <?php echo $i === $pageNo ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($pageNo < $totalPages): ?>
                        <a href="?search=<?php echo urlencode($searchWrd); ?>&type=<?php echo $searchCnd; ?>&page=<?php echo $pageNo + 1; ?>"
                           class="page-link">›</a>
                        <a href="?search=<?php echo urlencode($searchWrd); ?>&type=<?php echo $searchCnd; ?>&page=<?php echo $totalPages; ?>"
                           class="page-link">»</a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>

                <?php elseif ($searchResult && !$searchResult['success']): ?>
                    <div class="empty-state">
                        <div class="empty-icon">⚠️</div>
                        <h4 class="empty-title">검색 오류</h4>
                        <p class="empty-description"><?php echo htmlspecialchars($searchResult['message']); ?></p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🔍</div>
                        <h4 class="empty-title">검색 결과가 없습니다</h4>
                        <p class="empty-description">다른 검색어로 다시 시도해 주세요.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <!-- 검색 전 안내 카드 -->
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-icon">📋</div>
                    <h3>16개 항목 정보</h3>
                    <p>화학제품 정보, 유해성, 응급조치, 취급방법 등 법정 16개 항목의 상세 정보를 제공합니다.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">🔒</div>
                    <h3>공식 데이터</h3>
                    <p>산업안전보건공단(KOSHA)에서 제공하는 공식 물질안전보건자료입니다.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">⚡</div>
                    <h3>다양한 검색</h3>
                    <p>화학물질명, CAS No., UN No., KE No., EN No. 등 다양한 방식으로 검색할 수 있습니다.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- 데이터 출처 -->
            <div class="msds-source">
                <p>
                    데이터 출처: <a href="https://msds.kosha.or.kr" target="_blank">안전보건공단 화학물질정보시스템</a>
                    | 본 서비스는 공공데이터포털 Open API를 활용합니다.
                </p>
            </div>
        </div>
    </div>
</main>

<!-- 카메라 모달 -->
<div class="camera-modal" id="cameraModal">
    <div class="modal-header">
        <h3>MSDS 이미지 분석</h3>
        <button type="button" class="modal-close" id="closeCameraBtn">&times;</button>
    </div>

    <div class="camera-container">
        <div class="camera-preview" id="cameraPreview">
            <div class="camera-placeholder" id="cameraPlaceholder">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p>카메라를 시작하거나 이미지를 업로드하세요</p>
            </div>
            <video id="cameraVideo" autoplay playsinline style="display:none;"></video>
            <img id="capturedImage" style="display:none;" alt="캡처된 이미지">
            <canvas id="captureCanvas"></canvas>

            <!-- 카메라 위 오버레이 결과 -->
            <div class="camera-overlay-result" id="cameraOverlayResult">
                <div class="overlay-confidence" id="overlayConfidence">신뢰도: 95%</div>
                <div class="overlay-header">
                    <h4>AI 분석 결과</h4>
                    <button type="button" class="overlay-close" id="overlayCloseBtn">&times;</button>
                </div>
                <div class="overlay-content" id="overlayContent">
                    <!-- 동적으로 채워짐 -->
                </div>
            </div>
        </div>

        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner"></div>
            <p class="loading-text">AI가 이미지를 분석하고 있습니다...</p>
        </div>

        <div class="camera-controls">
            <button type="button" class="control-btn" id="startCameraBtn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>카메라</span>
            </button>

            <button type="button" class="control-btn toggle-btn" id="realtimeToggleBtn" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>실시간 분석</span>
            </button>

            <button type="button" class="control-btn primary" id="captureBtn" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>촬영</span>
            </button>

            <label class="control-btn" id="uploadBtn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>갤러리</span>
                <input type="file" class="file-input" id="fileInput" accept="image/jpeg,image/png,image/webp">
            </label>

            <button type="button" class="control-btn primary" id="analyzeBtn" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span>분석</span>
            </button>
        </div>
    </div>

    <div class="error-message" id="errorMessage"></div>

    <div class="analysis-result" id="analysisResult">
        <div class="result-section" id="visionResultSection">
            <h4>AI 분석 결과</h4>
            <div class="result-grid" id="visionResultGrid"></div>
            <div id="hazardStatements" style="margin-top: 1rem;"></div>
        </div>

        <div class="result-section" id="msdsResultSection" style="display: none;">
            <h4>MSDS 검색 결과</h4>
            <div id="msdsResultList"></div>
        </div>
    </div>
</div>

<script>
(function() {
    // 요소 참조
    const openCameraBtn = document.getElementById('openCameraBtn');
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    const cameraModal = document.getElementById('cameraModal');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const realtimeToggleBtn = document.getElementById('realtimeToggleBtn');
    const captureBtn = document.getElementById('captureBtn');
    const analyzeBtn = document.getElementById('analyzeBtn');
    const fileInput = document.getElementById('fileInput');
    const cameraVideo = document.getElementById('cameraVideo');
    const capturedImage = document.getElementById('capturedImage');
    const captureCanvas = document.getElementById('captureCanvas');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const errorMessage = document.getElementById('errorMessage');
    const analysisResult = document.getElementById('analysisResult');
    const visionResultGrid = document.getElementById('visionResultGrid');
    const hazardStatements = document.getElementById('hazardStatements');
    const msdsResultSection = document.getElementById('msdsResultSection');
    const msdsResultList = document.getElementById('msdsResultList');

    // 오버레이 요소
    const cameraOverlayResult = document.getElementById('cameraOverlayResult');
    const overlayContent = document.getElementById('overlayContent');
    const overlayConfidence = document.getElementById('overlayConfidence');
    const overlayCloseBtn = document.getElementById('overlayCloseBtn');

    let stream = null;
    let currentImageData = null;
    let realtimeAnalysisActive = false;
    let realtimeAnalysisInterval = null;
    let isAnalyzing = false;

    // 이미지 압축 함수 (서버 요청 크기 제한 대응)
    async function compressImage(dataUrl, maxWidth = 1280, quality = 0.7) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                // 최대 너비에 맞춰 리사이즈
                if (width > maxWidth) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // JPEG로 압축
                const compressedDataUrl = canvas.toDataURL('image/jpeg', quality);
                console.log(`이미지 압축: ${Math.round(dataUrl.length / 1024)}KB → ${Math.round(compressedDataUrl.length / 1024)}KB`);
                resolve(compressedDataUrl);
            };
            img.src = dataUrl;
        });
    }

    // API URL 설정
    const apiUrl = '<?php echo getMsdsUrl("api/analyze.php"); ?>';
    const detailBaseUrl = '<?php echo getMsdsUrl("detail.php"); ?>';
    const logUrl = '<?php echo getMsdsUrl("api/log.php"); ?>';

    // 모바일 디버그 로그 함수
    async function logDebug(level, message, data = {}) {
        const isMobile = /Mobile|Android|iPhone|iPad/i.test(navigator.userAgent);
        console.log(`[${level.toUpperCase()}] ${message}`, data);

        // 서버에 로그 전송
        try {
            await fetch(logUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    level,
                    message,
                    data,
                    isMobile,
                    userAgent: navigator.userAgent,
                    timestamp: new Date().toISOString()
                })
            });
        } catch (e) {
            console.error('로그 전송 실패:', e);
        }
    }

    // 전역 에러 핸들러
    window.onerror = function(msg, url, line, col, error) {
        logDebug('error', 'JavaScript Error', { msg, url, line, col, error: error?.toString() });
        return false;
    };

    // Promise rejection 핸들러
    window.onunhandledrejection = function(event) {
        logDebug('error', 'Unhandled Promise Rejection', { reason: event.reason?.toString() });
    };

    // 모달 열기
    if (openCameraBtn) {
        openCameraBtn.addEventListener('click', () => {
            cameraModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // 모달 닫기
    closeCameraBtn.addEventListener('click', closeModal);

    function closeModal() {
        cameraModal.classList.remove('active');
        document.body.style.overflow = '';
        stopCamera();
        resetState();
    }

    // ESC 키로 모달 닫기
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && cameraModal.classList.contains('active')) {
            closeModal();
        }
    });

    // 카메라 시작
    startCameraBtn.addEventListener('click', async () => {
        try {
            // 기존 스트림 정리
            stopCamera();
            resetState();

            // 카메라 권한 요청
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment', // 후면 카메라 우선
                    width: { ideal: 1280 },
                    height: { ideal: 960 }
                }
            });

            cameraVideo.srcObject = stream;
            cameraVideo.style.display = 'block';
            cameraPlaceholder.style.display = 'none';
            capturedImage.style.display = 'none';
            captureBtn.disabled = false;
            realtimeToggleBtn.disabled = false;
            analyzeBtn.disabled = true;

        } catch (err) {
            console.error('카메라 접근 오류:', err);
            showError('카메라에 접근할 수 없습니다. 권한을 확인해주세요.');
        }
    });

    // 카메라 중지
    function stopCamera() {
        stopRealtimeAnalysis();
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        cameraVideo.srcObject = null;
        cameraVideo.style.display = 'none';
    }

    // 오버레이 닫기
    overlayCloseBtn.addEventListener('click', () => {
        cameraOverlayResult.classList.remove('active');
    });

    // 상태 초기화
    function resetState() {
        stopRealtimeAnalysis();
        currentImageData = null;
        capturedImage.style.display = 'none';
        capturedImage.src = '';
        cameraPlaceholder.style.display = 'flex';
        captureBtn.disabled = true;
        realtimeToggleBtn.disabled = true;
        realtimeToggleBtn.classList.remove('active');
        analyzeBtn.disabled = true;
        analysisResult.classList.remove('active');
        errorMessage.classList.remove('active');
        loadingOverlay.classList.remove('active');
        cameraOverlayResult.classList.remove('active');
    }

    // 이미지 캡처
    captureBtn.addEventListener('click', () => {
        logDebug('info', 'Capture button clicked', { hasStream: !!stream });
        if (!stream) return;

        try {
            const context = captureCanvas.getContext('2d');
            captureCanvas.width = cameraVideo.videoWidth;
            captureCanvas.height = cameraVideo.videoHeight;
            logDebug('info', 'Canvas size set', {
                width: captureCanvas.width,
                height: captureCanvas.height
            });

            context.drawImage(cameraVideo, 0, 0);

            currentImageData = captureCanvas.toDataURL('image/jpeg', 0.8);
            logDebug('info', 'Image captured', {
                dataLength: currentImageData.length
            });

            capturedImage.src = currentImageData;
            capturedImage.style.display = 'block';
            cameraVideo.style.display = 'none';
            cameraPlaceholder.style.display = 'none';

            // 카메라 중지
            stopCamera();
            captureBtn.disabled = true;
            analyzeBtn.disabled = false;
            logDebug('info', 'Capture complete, ready for analysis');
        } catch (err) {
            logDebug('error', 'Capture failed', {
                error: err.message,
                stack: err.stack
            });
            showError('이미지 캡처에 실패했습니다.');
        }
    });

    // 파일 업로드
    fileInput.addEventListener('change', (e) => {
        logDebug('info', 'File input changed');
        const file = e.target.files[0];
        if (!file) {
            logDebug('info', 'No file selected');
            return;
        }

        logDebug('info', 'File selected', {
            name: file.name,
            type: file.type,
            size: file.size
        });

        // 파일 크기 검증 (5MB)
        if (file.size > 5 * 1024 * 1024) {
            logDebug('error', 'File too large', { size: file.size });
            showError('이미지 크기는 5MB 이하여야 합니다.');
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            logDebug('info', 'File read complete', {
                resultLength: event.target.result.length
            });
            currentImageData = event.target.result;
            capturedImage.src = currentImageData;
            capturedImage.style.display = 'block';
            cameraVideo.style.display = 'none';
            cameraPlaceholder.style.display = 'none';
            stopCamera();
            captureBtn.disabled = true;
            analyzeBtn.disabled = false;
            logDebug('info', 'Image ready for analysis');
        };
        reader.onerror = (error) => {
            logDebug('error', 'FileReader error', { error: error.toString() });
            showError('파일을 읽을 수 없습니다.');
        };
        reader.readAsDataURL(file);

        // 입력 초기화 (같은 파일 재선택 가능하도록)
        e.target.value = '';
    });

    // 이미지 분석
    analyzeBtn.addEventListener('click', async () => {
        logDebug('info', 'Analyze button clicked', { hasImageData: !!currentImageData });

        if (!currentImageData) {
            showError('분석할 이미지가 없습니다.');
            return;
        }

        loadingOverlay.classList.add('active');
        errorMessage.classList.remove('active');
        analysisResult.classList.remove('active');

        try {
            logDebug('info', 'Starting analysis', {
                apiUrl,
                imageDataLength: currentImageData.length
            });

            // 이미지 압축 (모바일은 더 작게)
            const isMobile = /Mobile|Android|iPhone|iPad/i.test(navigator.userAgent);
            const maxWidth = isMobile ? 640 : 1280;  // 모바일: 640px, 데스크탑: 1280px
            const quality = isMobile ? 0.5 : 0.7;    // 모바일: 50%, 데스크탑: 70%

            logDebug('info', 'Compressing image...', { isMobile, maxWidth, quality });
            const compressedImage = await compressImage(currentImageData, maxWidth, quality);
            const compressedSizeKB = Math.round(compressedImage.length / 1024);

            logDebug('info', 'Image compressed', {
                originalSize: currentImageData.length,
                compressedSize: compressedImage.length,
                compressedSizeKB: compressedSizeKB + 'KB'
            });

            // 모바일에서 100KB 초과시 추가 압축
            let finalImage = compressedImage;
            if (isMobile && compressedSizeKB > 100) {
                logDebug('info', 'Mobile image too large, compressing more...');
                finalImage = await compressImage(compressedImage, 480, 0.4);
                logDebug('info', 'Additional compression done', {
                    finalSize: Math.round(finalImage.length / 1024) + 'KB'
                });
            }

            // 전송할 데이터 준비
            const requestBody = JSON.stringify({ image: finalImage });
            const requestSizeKB = Math.round(requestBody.length / 1024);

            logDebug('info', 'Sending API request via XHR...', {
                requestSizeKB: requestSizeKB + 'KB',
                apiUrl: apiUrl
            });

            // XHR을 Promise로 래핑 (iOS Safari 호환성)
            const { responseText, status } = await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', apiUrl, true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.timeout = 60000; // 60초 타임아웃

                xhr.onreadystatechange = function() {
                    logDebug('debug', 'XHR state change', {
                        readyState: xhr.readyState,
                        status: xhr.readyState >= 2 ? xhr.status : 'N/A'
                    });
                };

                xhr.onload = function() {
                    logDebug('info', 'XHR response received', {
                        status: xhr.status,
                        responseLength: xhr.responseText.length,
                        responsePreview: xhr.responseText.substring(0, 50)
                    });
                    resolve({ responseText: xhr.responseText, status: xhr.status });
                };

                xhr.onerror = function() {
                    logDebug('error', 'XHR network error', {
                        readyState: xhr.readyState,
                        status: xhr.status,
                        statusText: xhr.statusText
                    });
                    reject(new Error('네트워크 오류가 발생했습니다.'));
                };

                xhr.ontimeout = function() {
                    logDebug('error', 'XHR timeout', { readyState: xhr.readyState });
                    reject(new Error('요청 시간이 초과되었습니다.'));
                };

                xhr.send(requestBody);
            });

            logDebug('info', 'Response text received', {
                length: responseText.length,
                preview: responseText.substring(0, 100)
            });

            if (status < 200 || status >= 300) {
                let errorMsg = `API 요청 실패 (${status})`;
                try {
                    const errorData = JSON.parse(responseText);
                    errorMsg = errorData.message || errorMsg;
                } catch (e) {
                    logDebug('error', 'API error response parse failed', {
                        responsePreview: responseText.substring(0, 200)
                    });
                    errorMsg += `: ${responseText.substring(0, 100)}`;
                }
                throw new Error(errorMsg);
            }

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                logDebug('error', 'JSON parse failed', {
                    responsePreview: responseText.substring(0, 200)
                });
                throw new Error('서버 응답을 파싱할 수 없습니다.');
            }

            logDebug('info', 'Analysis successful', {
                hasVision: !!result.vision,
                hasMsdsSearch: !!result.msds_search
            });

            if (!result.success) {
                throw new Error(result.message || '분석에 실패했습니다.');
            }

            displayResults(result);

        } catch (err) {
            logDebug('error', 'Analysis failed', {
                error: err.message,
                stack: err.stack
            });
            showError(err.message || '분석 중 오류가 발생했습니다.');
        } finally {
            loadingOverlay.classList.remove('active');
        }
    });

    // 결과 표시
    function displayResults(result) {
        const vision = result.vision;

        // === 카메라 오버레이에 결과 표시 ===
        displayOverlayResults(result);

        // Vision 결과 표시
        visionResultGrid.innerHTML = '';

        const fields = [
            { key: 'chemical_name_kr', label: '물질명 (한글)' },
            { key: 'chemical_name_en', label: '물질명 (영문)' },
            { key: 'cas_no', label: 'CAS No.' },
            { key: 'un_no', label: 'UN No.' },
            { key: 'manufacturer', label: '제조사' },
            { key: 'confidence', label: '신뢰도', format: (v) => Math.round(v * 100) + '%' }
        ];

        fields.forEach(field => {
            const value = vision[field.key];
            if (value !== null && value !== undefined) {
                const item = document.createElement('div');
                item.className = 'result-item';
                item.innerHTML = `
                    <div class="label">${field.label}</div>
                    <div class="value">${field.format ? field.format(value) : value}</div>
                `;
                visionResultGrid.appendChild(item);
            }
        });

        // 위험문구 표시
        hazardStatements.innerHTML = '';
        if (vision.hazard_statements && vision.hazard_statements.length > 0) {
            hazardStatements.innerHTML = `
                <div class="result-item" style="grid-column: 1/-1;">
                    <div class="label">위험문구</div>
                    <div class="result-tags">
                        ${vision.hazard_statements.map(h => `<span class="result-tag">${h}</span>`).join('')}
                    </div>
                </div>
            `;
        }

        // MSDS 검색 결과 표시
        if (result.msds_search && result.msds_search.found && result.msds_search.items.length > 0) {
            msdsResultSection.style.display = 'block';
            msdsResultList.innerHTML = '';

            result.msds_search.items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'msds-result-item';
                div.innerHTML = `
                    <h5>${item.chemNameKor || '이름 없음'}</h5>
                    <div class="meta">
                        CAS: ${item.casNo || '-'} | UN: ${item.unNo || '-'} | KE: ${item.keNo || '-'}
                    </div>
                    <a href="${detailBaseUrl}?id=${item.chemId}" class="view-detail-btn" target="_blank">
                        상세보기 →
                    </a>
                `;
                msdsResultList.appendChild(div);
            });
        } else {
            msdsResultSection.style.display = 'none';

            // 검색 제안 표시
            if (result.search_suggestions && result.search_suggestions.length > 0) {
                msdsResultSection.style.display = 'block';
                msdsResultList.innerHTML = `
                    <p style="color: #888; margin-bottom: 1rem;">MSDS 검색 결과가 없습니다. 직접 검색해보세요:</p>
                    ${result.search_suggestions.map(s => `
                        <a href="?search=${encodeURIComponent(s.value)}&type=${s.type === 'cas' ? 1 : 0}"
                           class="view-detail-btn" style="margin-right: 0.5rem;">
                            ${s.label}: ${s.value}
                        </a>
                    `).join('')}
                `;
            }
        }

        analysisResult.classList.add('active');
    }

    // 카메라 오버레이에 결과 표시
    function displayOverlayResults(result) {
        const vision = result.vision;

        // 신뢰도 표시
        const confidence = vision.confidence ? Math.round(vision.confidence * 100) : 0;
        overlayConfidence.textContent = `신뢰도: ${confidence}%`;
        overlayConfidence.style.color = confidence >= 80 ? '#4CAF50' : confidence >= 50 ? 'var(--color-accent, #F0A500)' : '#ff4444';

        // 오버레이 내용 구성
        let html = '';

        // 주요 정보 표시
        if (vision.chemical_name_kr) {
            html += `
                <div class="overlay-item">
                    <span class="label">물질명</span>
                    <span class="value">${vision.chemical_name_kr}</span>
                </div>
            `;
        }

        if (vision.chemical_name_en) {
            html += `
                <div class="overlay-item">
                    <span class="label">영문명</span>
                    <span class="value">${vision.chemical_name_en}</span>
                </div>
            `;
        }

        if (vision.cas_no) {
            html += `
                <div class="overlay-item">
                    <span class="label">CAS No.</span>
                    <span class="value" style="font-family: monospace;">${vision.cas_no}</span>
                </div>
            `;
        }

        if (vision.un_no) {
            html += `
                <div class="overlay-item">
                    <span class="label">UN No.</span>
                    <span class="value" style="font-family: monospace;">${vision.un_no}</span>
                </div>
            `;
        }

        if (vision.manufacturer) {
            html += `
                <div class="overlay-item">
                    <span class="label">제조사</span>
                    <span class="value">${vision.manufacturer}</span>
                </div>
            `;
        }

        // 위험문구 표시
        if (vision.hazard_statements && vision.hazard_statements.length > 0) {
            html += `
                <div class="overlay-item" style="flex-direction: column; align-items: flex-start;">
                    <span class="label">위험문구</span>
                    <div class="overlay-tags">
                        ${vision.hazard_statements.map(h => `<span class="overlay-tag">${h}</span>`).join('')}
                    </div>
                </div>
            `;
        }

        // MSDS 상세보기 버튼
        if (result.msds_search && result.msds_search.found && result.msds_search.items.length > 0) {
            const firstItem = result.msds_search.items[0];
            html += `
                <a href="${detailBaseUrl}?id=${firstItem.chemId}" class="overlay-msds-btn" target="_blank">
                    MSDS 상세정보 보기 →
                </a>
            `;
        } else if (vision.chemical_name_kr || vision.cas_no) {
            const searchVal = vision.cas_no || vision.chemical_name_kr;
            const searchType = vision.cas_no ? 1 : 0;
            html += `
                <a href="?search=${encodeURIComponent(searchVal)}&type=${searchType}" class="overlay-msds-btn">
                    MSDS 검색하기 →
                </a>
            `;
        }

        overlayContent.innerHTML = html;
        cameraOverlayResult.classList.add('active');
    }

    // 실시간 분석 토글
    realtimeToggleBtn.addEventListener('click', () => {
        console.log('[실시간분석] 버튼 클릭, 현재상태:', {
            realtimeAnalysisActive,
            isAnalyzing,
            hasStream: !!stream,
            videoWidth: cameraVideo.videoWidth
        });

        if (realtimeAnalysisActive) {
            stopRealtimeAnalysis();
        } else {
            startRealtimeAnalysis();
        }
    });

    // 실시간 분석 시작
    function startRealtimeAnalysis() {
        // 기존 interval이 있으면 먼저 정리
        if (realtimeAnalysisInterval) {
            clearInterval(realtimeAnalysisInterval);
            realtimeAnalysisInterval = null;
        }

        // isAnalyzing 상태 초기화
        isAnalyzing = false;

        if (!stream) {
            showError('카메라를 먼저 시작해주세요.');
            console.log('[실시간분석] 시작 실패: stream 없음');
            return;
        }

        if (!cameraVideo.videoWidth) {
            showError('카메라가 준비 중입니다. 잠시 후 다시 시도해주세요.');
            console.log('[실시간분석] 시작 실패: videoWidth 없음');
            return;
        }

        realtimeAnalysisActive = true;
        realtimeToggleBtn.classList.add('active');
        captureBtn.disabled = true;
        console.log('[실시간분석] 시작됨');

        // 첫 번째 분석 즉시 실행
        performRealtimeAnalysis();

        // 3초마다 자동 분석
        realtimeAnalysisInterval = setInterval(() => {
            if (realtimeAnalysisActive && stream) {
                performRealtimeAnalysis();
            }
        }, 3000);
    }

    // 실시간 분석 중지 (keepOverlay: 결과 창 유지 여부)
    function stopRealtimeAnalysis(keepOverlay = false) {
        console.log('[실시간분석] 중지 시작', { keepOverlay });

        realtimeAnalysisActive = false;
        realtimeToggleBtn.classList.remove('active');

        if (stream) {
            captureBtn.disabled = false;
        }

        if (realtimeAnalysisInterval) {
            clearInterval(realtimeAnalysisInterval);
            realtimeAnalysisInterval = null;
        }

        // 상태 완전 초기화
        isAnalyzing = false;

        // keepOverlay가 false일 때만 결과 창 닫기
        if (!keepOverlay) {
            cameraOverlayResult.classList.remove('active');
        }

        console.log('[실시간분석] 중지 완료');
    }

    // 실시간 분석 수행
    async function performRealtimeAnalysis() {
        // 이미 분석 중이면 스킵
        if (isAnalyzing) {
            console.log('[실시간분석] 이미 분석 중, 스킵');
            return;
        }

        // 스트림/비디오 상태 체크
        if (!stream || !cameraVideo.videoWidth) {
            console.log('[실시간분석] 스트림/비디오 준비 안됨, 스킵');
            return;
        }

        // 실시간 분석이 비활성화되었으면 스킵
        if (!realtimeAnalysisActive) {
            console.log('[실시간분석] 비활성화됨, 스킵');
            return;
        }

        isAnalyzing = true;

        try {
            // 현재 프레임 캡처 (실시간은 더 작게 압축)
            const context = captureCanvas.getContext('2d');
            const maxWidth = 640; // 모바일 호환성을 위해 더 작게
            let width = cameraVideo.videoWidth;
            let height = cameraVideo.videoHeight;

            if (width > maxWidth) {
                height = Math.round((height * maxWidth) / width);
                width = maxWidth;
            }

            captureCanvas.width = width;
            captureCanvas.height = height;
            context.drawImage(cameraVideo, 0, 0, width, height);

            const imageData = captureCanvas.toDataURL('image/jpeg', 0.5);
            console.log('[실시간분석] 이미지 캡처 완료:', Math.round(imageData.length/1024) + 'KB');

            // API 호출 (XHR - iOS Safari 호환성)
            const { responseText, status } = await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', apiUrl, true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.timeout = 30000;

                xhr.onload = function() {
                    resolve({ responseText: xhr.responseText, status: xhr.status });
                };

                xhr.onerror = function() {
                    reject(new Error('네트워크 오류'));
                };

                xhr.ontimeout = function() {
                    reject(new Error('시간 초과'));
                };

                xhr.send(JSON.stringify({ image: imageData }));
            });

            console.log('[실시간분석] 응답:', status);

            if (status < 200 || status >= 300) {
                console.error('[실시간분석] API 오류:', status);
                return;
            }

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('[실시간분석] JSON 파싱 실패:', e);
                return;
            }

            if (result.success && realtimeAnalysisActive) {
                displayOverlayResults(result);
                // 결과가 표시되면 실시간 분석 자동 중지 (결과 창은 유지)
                stopRealtimeAnalysis(true);
                console.log('[실시간분석] 결과 표시됨, 자동 중지');
            } else if (!result.success) {
                console.warn('[실시간분석] 분석 실패:', result.message);
            }

        } catch (err) {
            console.error('[실시간분석] 오류:', err);
        } finally {
            // 항상 isAnalyzing 리셋
            isAnalyzing = false;
        }
    }

    // 에러 표시
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.add('active');
    }
})();
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
