<?php
// Navigation only (without HTML document structure)
// Natural Green 테마의 네비게이션 헤더 부분만 포함

// 환경변수 로드
$envPath = PROJECT_BASE_PATH . '/bootstrap/env.php';
if (file_exists($envPath)) {
    require_once $envPath;
    // .env 파일 로드 시도
    try {
        load_env(PROJECT_BASE_PATH . '/.env');
    } catch (Exception $e) {
        // .env 파일이 없어도 계속 진행
    }
}

// 간단한 env 함수 (없는 경우만)
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            // $_ENV에서도 확인
            $value = $_ENV[$key] ?? $default;
        }
        return $value;
    }
}

// 환경변수 기반 URL 생성 함수
if (!function_exists('app_url')) {
    function app_url($path = '') {
        // 환경변수 로드 (env 함수가 없으면 직접 처리)
        $appUrl = env('APP_URL');
        $basePath = env('BASE_PATH', '');
        $appEnv = env('APP_ENV', 'production');
        
        // env 함수가 없는 경우 fallback
        if (!$appUrl) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // 로컬 환경 감지 (localhost, .local 도메인)
            $isLocal = strpos($host, 'localhost') !== false || 
                      strpos($host, '.local') !== false || 
                      strpos($host, '127.0.0.1') !== false ||
                      $appEnv === 'local';
            
            if ($isLocal) {
                // 로컬 환경: BASE_PATH 사용
                $basePath = $basePath ?: '/hopec';
                $baseUrl = $protocol . $host . $basePath;
            } else {
                // 프로덕션 환경: 루트 기준
                $baseUrl = $protocol . $host;
            }
        } else {
            $baseUrl = rtrim($appUrl, '/');
        }
        
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
    // 직접 PDO 연결 사용
    global $pdo;
    if (!isset($pdo)) {
        // 기본 데이터베이스 연결이 없는 경우 생성
        $host = env('DB_HOST', 'localhost');
        $dbname = env('DB_DATABASE', 'hopec');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    if ($pdo) {
        // 최상위 메뉴들을 가져옴
        $stmt = $pdo->query("
            SELECT id, title, slug, position, sort_order 
            FROM " . get_table_name('menu') . " 
            WHERE parent_id IS NULL AND is_active = 1 
            ORDER BY sort_order, id
        ");
        $topMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($topMenus as $topMenu) {
            // 각 최상위 메뉴의 하위 메뉴들을 가져옴
            $stmt = $pdo->prepare("
                SELECT id, title, slug, board_id
                FROM " . get_table_name('menu') . " 
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

// 링크 매핑 정의 - app_url() 함수 사용으로 환경별 자동 처리
$introBoardLinks = [
  '희망씨는' => app_url('about/about.php'),
  '인사말' => app_url('about/greeting.php'),
  '연혁' => app_url('about/history.php'),
  '조직도' => app_url('about/org.php'),
  '재정현황' => app_url('about/finance_view.php'), // 실제 파일명으로 수정
  '오시는길' => app_url('about/location.php')
];

$programLinks = [
  '국내사업' => app_url('programs/domestic.php'),
  '해외사업' => app_url('programs/overseas.php'),
  '노동권익' => app_url('programs/labor-rights.php'),
  '지역사회' => app_url('programs/community.php'),
  '자원봉사' => app_url('programs/volunteer.php')
];

$communityLinks = [
  '공지사항' => app_url('community/notices.php'),
  '갤러리' => app_url('community/gallery.php'),
  '소식지' => app_url('community/newsletter.php'),
  '언론보도' => app_url('community/press.php'),
  '네팔소식' => app_url('community/nepal.php'),
  '자료실' => app_url('community/resources.php')
];
?>

<header class="bg-white border-bottom shadow-sm backdrop-blur-md" id="main-header" style="background-color: rgba(255, 255, 255, 0.95); border-color: var(--border); position: fixed; top: 0; left: 0; right: 0; z-index: 1050;" role="banner">
  <div class="container-xl px-3">
    <div class="d-flex align-items-center h-100" style="min-height: 5rem;">
      <!-- 로고 -->
      <div class="me-4">
        <a href="<?php echo app_url(''); ?>" 
           class="d-flex align-items-center text-decoration-none"
           aria-label="홈페이지 메인으로 이동">
          <img
            src="<?php echo app_url('assets/images/logo.png'); ?>"
            alt="<?php echo htmlspecialchars(org_logo_alt('로고')); ?>"
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
                    // 게시판 ID를 실제 목록 페이지로 매핑
                    $board_routes = [
                        1 => 'about/finance.php',           // 재정보고 목록
                        2 => 'community/notices.php',       // 공지사항 목록
                        3 => 'community/press.php',         // 언론보도 목록
                        4 => 'community/newsletter.php',    // 소식지 목록
                        5 => 'community/gallery.php',       // 갤러리 목록
                        6 => 'community/resources.php',     // 자료실 목록
                        7 => 'community/nepal.php',         // 네팔나눔연대여행 목록
                    ];
                    $href = isset($board_routes[$boardId]) ? app_url($board_routes[$boardId]) : app_url('community/notice_view.php');
                  } else if ($itemSlug) {
                    $parentSlug = $menu['slug'];
                    $href = app_url($parentSlug . '/' . $itemSlug . '.php');
                  } else {
                    if (isset($introBoardLinks[$itemTitle])) {
                      $href = $introBoardLinks[$itemTitle];
                    } else if (isset($programLinks[$itemTitle])) {
                      $href = $programLinks[$itemTitle];
                    } else if (isset($communityLinks[$itemTitle])) {
                      $href = $communityLinks[$itemTitle];
                    } else {
                      $href = app_url('theme/natural-green/index.php?page=' . urlencode($itemTitle));
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
<div id="mobileMenu" class="d-md-none fixed-top bg-white d-none" role="dialog" aria-modal="true" aria-labelledby="mobileMenuTitle" style="inset: 0; background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); z-index: 1060;">
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
        <ul id="mm-section-<?php echo $mi; ?>" class="px-6 pb-3 space-y-1" style="display: none;">
          <?php foreach ($menu['items'] as $item): ?>
            <?php
              $itemTitle = is_array($item) ? $item['title'] : $item;
              $itemSlug = is_array($item) ? $item['slug'] : null;
              $boardId = is_array($item) && isset($item['board_id']) ? $item['board_id'] : null;
              
              if ($boardId) {
                // 게시판 ID를 실제 목록 페이지로 매핑 (모바일)
                $board_routes = [
                    1 => 'about/finance.php',           // 재정보고 목록
                    2 => 'community/notices.php',       // 공지사항 목록
                    3 => 'community/press.php',         // 언론보도 목록
                    4 => 'community/newsletter.php',    // 소식지 목록
                    5 => 'community/gallery.php',       // 갤러리 목록
                    6 => 'community/resources.php',     // 자료실 목록
                    7 => 'community/nepal.php',         // 네팔나눔연대여행 목록
                ];
                $href = isset($board_routes[$boardId]) ? app_url($board_routes[$boardId]) : app_url('community/notice_view.php');
              } else if ($itemSlug) {
                $parentSlug = $menu['slug'];
                $href = app_url($parentSlug . '/' . $itemSlug . '.php');
              } else {
                if (isset($introBoardLinks[$itemTitle])) {
                  $href = $introBoardLinks[$itemTitle];
                } else if (isset($programLinks[$itemTitle])) {
                  $href = $programLinks[$itemTitle];
                } else if (isset($communityLinks[$itemTitle])) {
                  $href = $communityLinks[$itemTitle];
                } else {
                  $href = app_url('theme/natural-green/index.php?page=' . urlencode($itemTitle));
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
/* Fixed 헤더를 위한 body padding 조정 */
body {
    padding-top: 5rem !important; /* 헤더 높이만큼 padding 추가 */
}

@media (max-width: 767px) {
    body {
        padding-top: 4rem !important; /* 모바일에서는 헤더 높이가 작으므로 4rem */
    }
}

/* Fixed 헤더가 항상 최상단에 고정되도록 보장 */
#main-header {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1050 !important;
    background-color: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    transition: all 0.3s ease !important;
}

/* 네비게이션 메뉴 스타일 - 반응형 개선 */
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
    z-index: 1055 !important; /* 헤더(1050)보다 높게 설정 */
    max-width: 250px;
    white-space: nowrap;
}

.dropdown-menu a,
.dropdown-item {
    background-color: transparent;
    color: var(--forest-600);
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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

/* 모바일 메뉴 반응형 개선 */
@media (max-width: 767px) {
    .dropdown-menu {
        position: static !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        background-color: transparent !important;
    }
    
    /* 헤더 컨테이너 높이 조정 */
    header .d-flex {
        min-height: 4rem !important;
    }
    
    /* 로고 크기 조정 */
    header img {
        height: 2.5rem !important;
    }
    
    /* 모바일 서브메뉴 스타일링 - 강화된 버전 */
    #mobileMenu ul[id^="mm-section-"] {
        background-color: rgba(245, 251, 241, 0.8) !important;
        border-radius: 8px !important;
        margin-top: 8px !important;
        margin-bottom: 8px !important;
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
        padding-bottom: 0.75rem !important;
        transition: all 0.3s ease !important;
        border: 1px solid rgba(132, 204, 22, 0.2) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
    }
    
    #mobileMenu ul[id^="mm-section-"] li {
        list-style: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    #mobileMenu ul[id^="mm-section-"] li a {
        display: block !important;
        padding: 0.75rem 1rem !important;
        border-radius: 4px !important;
        transition: background-color 0.2s ease !important;
        color: var(--forest-600) !important;
        text-decoration: none !important;
        font-size: 0.95rem !important;
        line-height: 1.4 !important;
    }
    
    #mobileMenu ul[id^="mm-section-"] li a:hover,
    #mobileMenu ul[id^="mm-section-"] li a:focus {
        background-color: var(--natural-200) !important;
        color: var(--forest-700) !important;
    }
    
    /* 모바일 메뉴 버튼 호버 효과 */
    #mobileMenu button[data-section] {
        transition: background-color 0.2s ease;
    }
    
    #mobileMenu button[data-section]:hover,
    #mobileMenu button[data-section]:focus {
        background-color: rgba(132, 204, 22, 0.1);
    }
}

/* 태블릿 반응형 */
@media (min-width: 768px) and (max-width: 1023px) {
    .dropdown-menu {
        max-width: 200px;
    }
    
    .nav-button-hover {
        padding: 0.4rem 0.6rem !important;
        font-size: 0.9rem;
    }
}

/* 데스크탑 큰 화면 */
@media (min-width: 1024px) {
    .dropdown-menu {
        max-width: 280px;
    }
}
</style>

<script>
// Fixed 헤더 스크롤 이벤트 처리
window.addEventListener('scroll', function() {
    const header = document.getElementById('main-header');
    if (header) {
        // 스크롤 위치에 관계없이 헤더가 항상 최상단에 고정되도록 보장
        if (window.scrollY > 0) {
            header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
        } else {
            header.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.05)';
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
        }
    }
});

// 페이지 로드 시 헤더 위치 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 헤더 위치 강제 고정
    const header = document.getElementById('main-header');
    if (header) {
        header.style.position = 'fixed';
        header.style.top = '0px';
        header.style.left = '0px';
        header.style.right = '0px';
        header.style.zIndex = '1050';
        header.style.width = '100%';
    }

    // 드롭다운 메뉴 호버 기능
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
    
    // 모바일 서브메뉴 토글 기능 - 최적화된 버전
    function initMobileSubmenus() {
        document.addEventListener('click', function(event) {
            const target = event.target.closest('[data-section]');
            if (!target) return;
            
            event.preventDefault();
            event.stopPropagation();
            
            const sectionId = target.getAttribute('data-section');
            const submenu = document.getElementById('mm-section-' + sectionId);
            const chevron = target.querySelector('[data-lucide="chevron-down"]');
            
            if (submenu) {
                const isVisible = submenu.style.display === 'block';
                
                if (isVisible) {
                    submenu.style.display = 'none';
                    target.setAttribute('aria-expanded', 'false');
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                } else {
                    submenu.style.display = 'block';
                    target.setAttribute('aria-expanded', 'true');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            }
        });
    }
    
    // DOM 로드 후 즉시 초기화 (지연 시간 제거)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileSubmenus);
    } else {
        initMobileSubmenus();
    }
});
</script>