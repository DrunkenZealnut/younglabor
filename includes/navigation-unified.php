<?php
/**
 * 통합 네비게이션 시스템
 * 
 * Version: 2.0.0
 * Author: SuperClaude CSS Optimization System
 */

// 로고 URL 함수 정의 (없을 경우)
if (!function_exists('logo_url')) {
    function logo_url() {
        return '/assets/images/logo.png';
    }
}

// 사이트 URL
$siteUrl = defined('G5_URL') ? G5_URL : '';
$siteName = $config['cf_title'] ?? '희망연대노동조합';
?>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <!-- 로고/브랜드 -->
        <a class="navbar-brand" href="<?= $siteUrl ?>">
            <img src="<?= logo_url() ?>" alt="<?= htmlspecialchars($siteName) ?>" 
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display: none;"><?= htmlspecialchars($siteName) ?></span>
        </a>
        
        <!-- 모바일 토글 버튼 -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- 네비게이션 메뉴 -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php
                // 메뉴 데이터 조회
                if (function_exists('sql_query') && isset($g5['menu_table'])) {
                    $sql = "SELECT * FROM {$g5['menu_table']} 
                            WHERE me_use = '1' AND length(me_code) = '2' 
                            ORDER BY me_order, me_id";
                    $result = sql_query($sql, false);
                    
                    while ($row = sql_fetch_array($result)) {
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link" href="' . htmlspecialchars($row['me_link']) . '" ';
                        if ($row['me_target']) echo 'target="_' . htmlspecialchars($row['me_target']) . '"';
                        echo '>' . htmlspecialchars($row['me_name']) . '</a>';
                        echo '</li>';
                    }
                } else {
                    // 기본 메뉴 (데이터베이스 연결 없을 때)
                    $defaultMenu = [
                        ['name' => '소개', 'link' => '/about/about.php'],
                        ['name' => '프로그램', 'link' => '/programs/domestic.php'],
                        ['name' => '커뮤니티', 'link' => '/community/gallery.php'],
                        ['name' => '후원', 'link' => '/donate/one-time.php']
                    ];
                    
                    foreach ($defaultMenu as $item) {
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link" href="' . htmlspecialchars($item['link']) . '">';
                        echo htmlspecialchars($item['name']);
                        echo '</a></li>';
                    }
                }
                ?>
            </ul>
            
            <!-- 검색 (간단한 형태) -->
            <form class="d-flex search-form" role="search">
                <input class="form-control form-control-sm me-2" type="search" 
                       placeholder="검색..." aria-label="검색">
                <button class="btn btn-outline-light btn-sm" type="submit">검색</button>
            </form>
        </div>
    </div>
</nav>

<!-- 네비게이션 관련 스타일 -->
<style>
/* Navigation specific styles */
.navbar {
    background-color: var(--primary) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar-brand {
    font-weight: 700;
    color: white !important;
}

.navbar-brand img {
    height: 40px;
    width: auto;
}

.nav-link {
    color: white !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    transition: var(--transition-fast);
}

.nav-link:hover,
.nav-link:focus {
    color: var(--primary-light) !important;
    background-color: rgba(255,255,255,0.1);
    border-radius: 4px;
}

.navbar-toggler {
    border-color: rgba(255,255,255,0.3);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.85%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.search-form {
    min-width: 200px;
}

.search-form .form-control {
    background-color: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.3);
    color: white;
}

.search-form .form-control::placeholder {
    color: rgba(255,255,255,0.7);
}

.search-form .form-control:focus {
    background-color: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.5);
    color: white;
}

/* 모바일 대응 */
@media (max-width: 768px) {
    .search-form {
        margin-top: 1rem;
        min-width: auto;
    }
    
    .nav-link {
        padding: 0.75rem 1rem !important;
    }
}
</style>

<!-- Bootstrap JavaScript (네비게이션 토글을 위해 필요) -->
<script>
// Bootstrap Collapse 기능을 위한 간단한 구현
document.addEventListener('DOMContentLoaded', function() {
    const toggler = document.querySelector('.navbar-toggler');
    const collapse = document.querySelector('.navbar-collapse');
    
    if (toggler && collapse) {
        toggler.addEventListener('click', function() {
            collapse.classList.toggle('show');
        });
        
        // 외부 클릭 시 메뉴 닫기
        document.addEventListener('click', function(e) {
            if (!toggler.contains(e.target) && !collapse.contains(e.target)) {
                collapse.classList.remove('show');
            }
        });
    }
});
</script>