<?php
// Navigation only (without HTML document structure)
// Natural Green 테마의 네비게이션 헤더 부분만 포함

// 기본 경로 설정
if (!defined('HOPEC_BASE_PATH')) {
    define('HOPEC_BASE_PATH', dirname(__DIR__, 3));
}

// 필수 함수들이 없으면 기본값 정의
if (!function_exists('app_url')) {
    function app_url($path = '') {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . $host;
        return $path ? $baseUrl . '/' . ltrim($path, '/') : $baseUrl;
    }
}

if (!function_exists('logo_url')) {
    function logo_url($fallback = 'logo.png') {
        return app_url('assets/images/' . $fallback);
    }
}

// 데이터베이스에서 메뉴 구조 로드
$menus = [];
try {
    // DatabaseManager가 초기화되지 않은 경우 초기화
    if (!class_exists('DatabaseManager')) {
        require_once HOPEC_BASE_PATH . '/includes/DatabaseManager.php';
        DatabaseManager::initialize();
    }
    
    $pdo = DatabaseManager::getConnection();
    
    if ($pdo) {
        // 최상위 메뉴들을 가져옴
        $stmt = $pdo->query("
            SELECT id, title, slug, position, sort_order 
            FROM hopec_menu 
            WHERE parent_id IS NULL AND is_active = 1 
            ORDER BY sort_order, id
        ");
        $topMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($topMenus as $topMenu) {
            // 각 최상위 메뉴의 하위 메뉴들을 가져옴
            $stmt = $pdo->prepare("
                SELECT id, title, slug, board_id
                FROM hopec_menu 
                WHERE parent_id = ? AND is_active = 1 
                ORDER BY sort_order, id
            ");
            $stmt->execute([$topMenu['id']]);
            $subMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 메뉴 구조 생성
            $menuData = [
                'id' => $topMenu['id'],
                'title' => $topMenu['title'],
                'slug' => $topMenu['slug'],
                'items' => []
            ];
            
            foreach ($subMenus as $subMenu) {
                $menuData['items'][] = [
                    'title' => $subMenu['title'],
                    'slug' => $subMenu['slug'],
                    'board_id' => $subMenu['board_id']
                ];
            }
            
            $menus[] = $menuData;
        }
    } else {
        throw new Exception("데이터베이스 연결 실패");
    }
} catch (Exception $e) {
    // 데이터베이스 연결 실패시 기본 메뉴 사용
    error_log("Navigation 메뉴 로딩 실패: " . $e->getMessage());
    $menus = [
        [
            'title' => '희망씨 소개',
            'slug' => 'about',
            'items' => [
                ['title' => '희망씨는', 'slug' => 'about'],
                ['title' => '이사장 인사말', 'slug' => 'greeting'],
                ['title' => '조직도', 'slug' => 'org'],
                ['title' => '연혁', 'slug' => 'history'],
                ['title' => '오시는길', 'slug' => 'location'],
                ['title' => '재정보고', 'slug' => 'finance']
            ]
        ],
        [
            'title' => '희망씨 사업',
            'slug' => 'programs',
            'items' => [
                ['title' => '국내아동지원사업', 'slug' => 'domestic'],
                ['title' => '해외아동지원사업', 'slug' => 'overseas'],
                ['title' => '노동인권사업', 'slug' => 'labor-rights'],
                ['title' => '소통 및 회원사업', 'slug' => 'community'],
                ['title' => '자원봉사안내', 'slug' => 'volunteer']
            ]
        ],
        [
            'title' => '희망씨 후원안내',
            'slug' => 'donate',
            'items' => [
                ['title' => '정기후원', 'slug' => 'monthly'],
                ['title' => '일시후원', 'slug' => 'onetime']
            ]
        ],
        [
            'title' => '커뮤니티',
            'slug' => 'community',
            'items' => [
                ['title' => '공지사항', 'slug' => 'notices'],
                ['title' => '언론보도', 'slug' => 'press'],
                ['title' => '소식지', 'slug' => 'newsletter'],
                ['title' => '갤러리', 'slug' => 'gallery'],
                ['title' => '자료실', 'slug' => 'resources'],
                ['title' => '네팔나눔연대여행', 'slug' => 'nepal']
            ]
        ]
    ];
}

// 링크 매핑 정의 (기존과 동일)
$introBoardLinks = [
  '희망씨는' => '/about/about.php',
  '인사말' => '/about/greeting.php',
  '연혁' => '/about/history.php',
  '조직도' => '/about/org.php',
  '재정현황' => '/about/finance.php',
  '오시는길' => '/about/location.php'
];

$programLinks = [
  '국내사업' => '/programs/domestic.php',
  '해외사업' => '/programs/overseas.php',
  '노동권익' => '/programs/labor-rights.php',
  '지역사회' => '/programs/community.php',
  '자원봉사' => '/programs/volunteer.php'
];

$communityLinks = [
  '공지사항' => '/community/notices.php',
  '갤러리' => '/community/gallery.php',
  '소식지' => '/community/newsletter.php',
  '언론보도' => '/community/press.php',
  '네팔소식' => '/community/nepal.php',
  '자료실' => '/community/resources.php'
];
?>

<header class="bg-white border-bottom sticky-top z-50 shadow-sm backdrop-blur-md" style="background-color: rgba(255, 255, 255, 0.95); border-color: var(--border);" role="banner">
  <div class="container-xl px-3">
    <div class="d-flex align-items-center h-100" style="min-height: 5rem;">
      <!-- 로고 -->
      <div class="me-4">
        <a href="<?php echo app_url(''); ?>" 
           class="d-flex align-items-center text-decoration-none"
           aria-label="홈페이지 메인으로 이동">
          <img
            src="<?php echo app_url('assets/images/logo.png'); ?>"
            alt="사단법인 희망씨"
            class="object-fit-contain"
            style="height: 3.5rem; width: auto; max-width: 14rem;"
            onerror="this.style.display='none';" />
        </a>
      </div>

      <!-- 데스크톱 메뉴 -->
      <nav class="d-none d-md-flex gap-1 overflow-auto overflow-md-visible ms-auto" role="navigation" aria-label="주요 메뉴">
        <?php foreach ($menus as $mi => $menu): ?>
          <div class="nav-item dropdown position-relative">
            <button class="d-flex align-items-center gap-1 text-forest-600 hover:text-lime-600 py-2 px-3 rounded nav-button-hover transition-all" 
                    style="border: none; outline: none; background: transparent;"
                    aria-haspopup="true" aria-expanded="false">
              <span><?php echo htmlspecialchars($menu['title']); ?></span>
              <i data-lucide="chevron-down" class="w-4 h-4 text-forest-500"></i>
            </button>
            
            <div class="dropdown-menu position-absolute rounded shadow-lg border py-2 z-50" 
                 style="top: 100%; left: 0; min-width: 10rem; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: opacity 0.15s ease, visibility 0.15s ease, transform 0.15s ease; pointer-events: none; border-color: var(--border);">
              <?php foreach ($menu['items'] as $item): ?>
                <?php
                  $itemTitle = is_array($item) ? $item['title'] : $item;
                  $itemSlug = is_array($item) ? $item['slug'] : null;
                  $boardId = is_array($item) && isset($item['board_id']) ? $item['board_id'] : null;
                  
                  if ($boardId) {
                    $href = '/board/list/' . $boardId . '/';
                  } else if ($itemSlug) {
                    $parentSlug = $menu['slug'];
                    $href = '/' . $parentSlug . '/' . $itemSlug . '.php';
                  } else {
                    if (isset($introBoardLinks[$itemTitle])) {
                      $href = $introBoardLinks[$itemTitle];
                    } else if (isset($programLinks[$itemTitle])) {
                      $href = $programLinks[$itemTitle];
                    } else if (isset($communityLinks[$itemTitle])) {
                      $href = $communityLinks[$itemTitle];
                    } else {
                      $href = '/theme/natural-green/index.php?page=' . urlencode($itemTitle);
                    }
                  }
                ?>
                <a href="<?php echo htmlspecialchars($href); ?>" 
                   class="d-block px-3 py-2 text-decoration-none text-forest-600 hover:text-lime-600 dropdown-item transition-colors">
                  <?php echo htmlspecialchars($itemTitle); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </nav>

      <!-- 모바일 메뉴 토글 -->
      <div class="ms-auto d-md-none">
        <button type="button" 
                id="mobileMenuToggle"
                class="d-inline-flex align-items-center justify-content-center rounded text-forest-600 bg-transparent border-0"
                style="width: 2.5rem; height: 2.5rem;"
                aria-expanded="false"
                aria-controls="mobileMenu"
                aria-label="메뉴 열기">
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
      </div>
    </div>
  </div>
</header>

<!-- 모바일 메뉴 패널 -->
<div id="mobileMenu" class="d-md-none fixed-top bg-white z-50 d-none" role="dialog" aria-modal="true" aria-labelledby="mobileMenuTitle" style="inset: 0; background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px);">
  <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom" style="border-color: var(--border);">
    <h2 id="mobileMenuTitle" class="text-lg text-forest-600">메뉴</h2>
    <button type="button" id="mobileMenuClose" class="d-inline-flex align-items-center justify-content-center rounded text-forest-600" style="width: 2.5rem; height: 2.5rem;" aria-label="메뉴 닫기">
      <i data-lucide="x" class="w-6 h-6"></i>
    </button>
  </div>
  <nav class="px-2 py-2 overflow-y-auto max-h-[calc(100svh-56px)]" role="navigation" aria-label="모바일 메뉴">
    <?php foreach ($menus as $mi => $menu): ?>
      <div class="border-b border-lime-200">
        <button
          type="button"
          class="w-100 d-flex align-items-center justify-content-between px-4 py-3 text-forest-600 border-0 bg-transparent"
          aria-expanded="false"
          aria-controls="mm-section-<?php echo $mi; ?>"
          data-section="<?php echo $mi; ?>">
          <span class="text-base font-medium"><?php echo htmlspecialchars($menu['title']); ?></span>
          <i data-lucide="chevron-down" class="w-5 h-5 transition-transform"></i>
        </button>
        <ul id="mm-section-<?php echo $mi; ?>" class="hidden px-6 pb-3 space-y-1">
          <?php foreach ($menu['items'] as $item): ?>
            <?php
              $itemTitle = is_array($item) ? $item['title'] : $item;
              $itemSlug = is_array($item) ? $item['slug'] : null;
              $boardId = is_array($item) && isset($item['board_id']) ? $item['board_id'] : null;
              
              if ($boardId) {
                $href = '/board/list/' . $boardId . '/';
              } else if ($itemSlug) {
                $parentSlug = $menu['slug'];
                $href = '/' . $parentSlug . '/' . $itemSlug . '.php';
              } else {
                if (isset($introBoardLinks[$itemTitle])) {
                  $href = $introBoardLinks[$itemTitle];
                } else if (isset($programLinks[$itemTitle])) {
                  $href = $programLinks[$itemTitle];
                } else if (isset($communityLinks[$itemTitle])) {
                  $href = $communityLinks[$itemTitle];
                } else {
                  $href = '/theme/natural-green/index.php?page=' . urlencode($itemTitle);
                }
              }
            ?>
            <li><a href="<?php echo htmlspecialchars($href); ?>" class="d-block py-2 text-forest-600 text-decoration-none"><?php echo htmlspecialchars($itemTitle); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </nav>
</div>

<style>
/* 네비게이션 메뉴 스타일 - 강화된 우선순위 */
.nav-button-hover {
    padding: 0.5rem 0.75rem !important;
    border-radius: 0.5rem !important;
    transition: background-color 0.15s ease !important;
    background-color: transparent !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.nav-button-hover:hover {
    background-color: rgba(132, 204, 22, 0.1) !important;
}

.nav-button-hover:focus {
    background-color: rgba(132, 204, 22, 0.1) !important;
    outline: 2px solid rgba(132, 204, 22, 0.5) !important;
    outline-offset: 2px !important;
}

/* 드롭다운 메뉴 기본 스타일 - CSS 변수 활용 */
.dropdown-menu {
    background-color: var(--natural-50);
    border: 1px solid var(--border);
}

.dropdown-menu a,
.dropdown-item {
    background-color: transparent;
    color: var(--forest-600);
}

.dropdown-menu a:hover,
.dropdown-item:hover {
    background-color: var(--natural-200);
    color: var(--forest-600);
}

/* 드롭다운 메뉴 호버 효과 */
.dropdown:hover .dropdown-menu {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    pointer-events: auto !important;
}

/* 텍스트 색상 변수 */
.text-forest-600 {
    color: #16a34a !important;
}

.text-lime-600 {
    color: #65a30d !important;
}

.hover\:text-lime-600:hover {
    color: #65a30d !important;
}

.hover\:bg-lime-50:hover {
    background-color: rgba(248, 250, 252, 0.9) !important; /* 매우 연한 그레이-그린 */
}

.border-lime-200 {
    border-color: #d9f99d !important;
}
</style>

<script>
// 드롭다운 메뉴 호버 기능 (header에서 이동)
document.addEventListener('DOMContentLoaded', function() {
    const dropdownItems = document.querySelectorAll('.dropdown, .nav-item.dropdown');
    
    dropdownItems.forEach(function(dropdown) {
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
        if (!dropdownMenu) return;
        
        let hoverTimeout;
        
        // 메뉴 아이템 진입 시
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            dropdownMenu.classList.add('show');
            dropdownMenu.style.display = 'block';
            dropdownMenu.style.opacity = '1';
            dropdownMenu.style.visibility = 'visible';
            dropdownMenu.style.transform = 'translateY(0)';
            dropdownMenu.style.pointerEvents = 'auto';
        });
        
        // 메뉴 아이템 이탈 시
        dropdown.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(function() {
                dropdownMenu.classList.remove('show');
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
                dropdownMenu.style.pointerEvents = 'none';
                setTimeout(function() {
                    if (!dropdownMenu.classList.contains('show')) {
                        dropdownMenu.style.display = 'none';
                    }
                }, 300);
            }, 100);
        });
        
        // 드롭다운 내부 호버 시 숨기기 방지
        dropdownMenu.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
        });
        
        dropdownMenu.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(function() {
                dropdownMenu.classList.remove('show');
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
                dropdownMenu.style.pointerEvents = 'none';
                setTimeout(function() {
                    if (!dropdownMenu.classList.contains('show')) {
                        dropdownMenu.style.display = 'none';
                    }
                }, 300);
            }, 100);
        });
    });
    
    // 모바일 메뉴 토글 기능
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.remove('d-none');
            document.body.style.overflow = 'hidden';
        });
        
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', function() {
                mobileMenu.classList.add('d-none');
                document.body.style.overflow = '';
            });
        }
        
        // 배경 클릭시 메뉴 닫기
        mobileMenu.addEventListener('click', function(e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.add('d-none');
                document.body.style.overflow = '';
            }
        });
    }
    
    console.log('🍿 Natural Green 네비게이션 로드 완료 - 드롭다운 수:', dropdownItems.length);
});
</script>