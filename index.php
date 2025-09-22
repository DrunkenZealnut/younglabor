<?php
/**
 * 희망씨 메인 홈페이지
 * 
 * 그누보드 의존성 제거 및 모던 PHP 아키텍처 적용
 */

// 간단한 라우팅 처리 (board/list/{id} URL)
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$parsed_url = parse_url($request_uri);
$path = $parsed_url['path'] ?? '';

// /hopec/ 접두사 제거 (로컬 환경)
if (strpos($path, '/hopec/') === 0) {
    $path = substr($path, 6); // "/hopec/" 제거
}

// board/list/{id} 패턴 매칭
if (preg_match('/^board\/list\/(\d+)\/?$/', $path, $matches)) {
    $board_id = (int)$matches[1];
    $_GET['id'] = $board_id; // board.php에서 사용할 수 있도록 설정
    
    // board.php로 라우팅
    if (file_exists(__DIR__ . '/board.php')) {
        include __DIR__ . '/board.php';
        exit;
    }
}

// 헬퍼 함수 로드
require_once __DIR__ . '/includes/config_helpers.php';
load_env_if_exists();

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/bootstrap/app.php';

// 인덱스 페이지 플래그
if (!defined('_INDEX_')) {
    define('_INDEX_', true);
}

// 페이지 메타 정보 설정
$pageTitle = get_org_name(true);
$pageDescription = get_org_name(true) . ' - ' . get_org_description();
$currentSlug = 'home';

// 헤더 출력 (여기서 activeTheme이 설정됨)
include_once __DIR__ . '/includes/header.php';

// 테마별 CSS는 physical theme system으로 관리하고, 
// 페이지 구조는 natural-green을 기본으로 사용
$themeHome = __DIR__ . '/theme/natural-green/pages/home.php';
if (file_exists($themeHome)) {
    include $themeHome;
} else {
    // 모던 폴백 페이지
    ?>
    <main role="main" class="main-content">
            <div class="container">
            <div class="hero-section">
                <h1 class="hero-title"><?= h(app_name()) ?></h1>
                <p class="hero-subtitle">청소년 노동인권과 지역사회 연대를 위한 비영리 단체</p>
            </div>
            
            <div class="content-section">
                <div class="row">
                    <div class="col-md-8">
                        <h2><?= get_org_name() ?> 소개</h2>
                        <p><?= get_org_name(true) ?>는 청소년 노동인권 교육과 지역사회 연대 활동을 통해 더 나은 사회를 만들어가는 비영리 단체입니다.</p>
                        
                        <h3>주요 활동</h3>
                        <ul>
                            <li>청소년 노동인권 교육</li>
                            <li>지역사회 연대 활동</li>
                            <li>네팔 나눔연대여행</li>
                            <li>희망공간 아띠 운영</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="notice-section">
                            <h3>최근 소식</h3>
                            
                            <?php
                            // 최근 공지사항 조회 (hopec_notices 테이블 사용)
                            try {
                                $notices = DatabaseManager::select(
                                    "SELECT wr_id as id, wr_subject as title, wr_datetime as created_at FROM hopec_notices ORDER BY wr_datetime DESC LIMIT 5"
                                );
                                
                                if (!empty($notices)) {
                                    echo '<ul class="notice-list">';
                                    foreach ($notices as $notice) {
                                        echo '<li>';
                                        echo '<a href="' . app_url('community/notice_view.php?wr_id=' . $notice['id']) . '">';
                                        echo h(mb_substr($notice['title'], 0, 30));
                                        echo '</a>';
                                        echo '<span class="date">' . date('Y.m.d', strtotime($notice['created_at'])) . '</span>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<p>등록된 공지사항이 없습니다.</p>';
                                }
                            } catch (Exception $e) {
                                if (is_debug()) {
                                    echo '<p>오류: ' . h($e->getMessage()) . '</p>';
                                } else {
                                    echo '<p>공지사항을 불러올 수 없습니다.</p>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <style>
    .main-content {
        padding: 2rem 0;
    }
    
    .hero-section {
        text-align: center;
        padding: 3rem 0;
        margin-bottom: 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.5rem;
    }
    
    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 0;
    }
    
    .content-section {
        padding: 2rem 0;
    }
    
    .notice-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-top: 2rem;
    }
    
    .notice-list {
        list-style: none;
        padding: 0;
    }
    
    .notice-list li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .notice-list li:last-child {
        border-bottom: none;
    }
    
    .notice-list a {
        color: #333;
        text-decoration: none;
        flex: 1;
    }
    
    .notice-list a:hover {
        color: #007bff;
    }
    
    .notice-list .date {
        font-size: 0.875rem;
        color: #999;
        margin-left: 0.5rem;
    }
    </style>
    <?php
}

// 푸터 출력
include_once __DIR__ . '/includes/footer.php';
?>