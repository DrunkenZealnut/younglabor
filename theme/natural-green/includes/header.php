<?php
// Bootstrap에서 이미 환경 변수와 데이터베이스가 초기화됨
// DatabaseManager를 통한 데이터베이스 접근
try {
    $pdo = DatabaseManager::getConnection();
} catch (Exception $e) {
    error_log("DatabaseManager connection failed in header: " . $e->getMessage());
    $pdo = null;
}

// 현재 페이지 활성화 표시를 위해 쿼리 파라미터 확인
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';

// 데이터베이스에서 메뉴 구조 로드
$menus = [];
try {
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
        // PDO 연결이 없는 경우 기본 메뉴 사용
        $menus = [
            [ 'title' => '희망씨 소개', 'items' => ['희망씨는','이사장 인사말','조직도','연혁','오시는길','재정보고'] ],
            [ 'title' => '희망씨 사업', 'items' => ['국내아동지원사업','해외아동지원사업','노동인권사업','소통 및 회원사업','자원봉사안내'] ],
            [ 'title' => '희망씨 후원안내', 'items' => ['정기후원','일시후원'] ],
            [ 'title' => '커뮤니티', 'items' => ['공지사항','언론보도','소식지','갤러리','자료실','네팔나눔연대여행'] ],
        ];
    }
} catch (PDOException $e) {
    // 오류 시 기본 메뉴 구조 유지
    error_log("Menu loading error: " . $e->getMessage());
    $menus = [
        [ 'title' => '희망씨 소개', 'items' => ['희망씨는','이사장 인사말','조직도','연혁','오시는길','재정보고'] ],
        [ 'title' => '희망씨 사업', 'items' => ['국내아동지원사업','해외아동지원사업','노동인권사업','소통 및 회원사업','자원봉사안내'] ],
        [ 'title' => '희망씨 후원안내', 'items' => ['정기후원','일시후원'] ],
        [ 'title' => '커뮤니티', 'items' => ['공지사항','언론보도','소식지','갤러리','자료실','네팔나눔연대여행'] ],
    ];
}

// "희망씨 소개" 하위 메뉴 전용 링크 매핑 (신규 정적 페이지 경로로 교체)
$introBoardLinks = [
  '희망씨는' => '/about/about.php',
  '이사장 인사말' => '/about/greeting.php',
  '조직도' => '/about/org.php', // B03_2
  '연혁' => '/about/history.php', // B03_3
  '오시는길' => '/about/location.php',
  '재정보고' => '/about/finance.php', // @board_templates 기반 신규 페이지로 연결
];

// "희망씨 사업" 하위 메뉴 링크 매핑 (정적 programs 경로)
$programLinks = [
  '국내아동지원사업' => '/programs/domestic.php', // B11
  '해외아동지원사업' => '/programs/overseas.php', // B12
  '노동인권사업'   => '/programs/labor-rights.php', // B13
  '소통 및 회원사업' => '/programs/community.php', // B14
  '자원봉사안내'   => '/programs/volunteer.php', // B15
];

// "후원안내" 하위 메뉴 링크 매핑 (정적 donate 경로)
$donateLinks = [
  '정기후원' => '/donate/monthly.php',
  '일시후원' => '/donate/one-time.php',
];

// 커뮤니티 메뉴 링크 매핑 (게시판 대체 라우팅)
$communityLinks = [
  '공지사항' => '/community/notices.php', // B31 대체
  '언론보도' => '/community/press.php',   // B32 대체
  '소식지'   => '/community/newsletter.php', // B33 대체 (추후 구현 시 갱신)
  '갤러리'   => '/community/gallery.php',
  '자료실'   => '/community/resources.php', // B35 대체
  '네팔나눔연대여행' => '/community/nepal.php', // B36 대체
  // 필요 시 다른 커뮤니티 항목도 여기서 개별 매핑 가능
];
?>
<header class="bg-white border-bottom sticky-top z-50 shadow-sm backdrop-blur-md" style="background-color: rgba(255, 255, 255, 0.95); border-color: var(--border);" role="banner">
  <div class="container-fluid max-w-7xl mx-auto px-4">
    <div class="d-flex justify-content-between align-items-center h-16">
      <div class="d-flex align-items-center">
        <a href="/" class="d-flex align-items-center text-decoration-none" style="outline: none;">
          <?php if (function_exists('logo_url')): ?>
            <img src="<?= logo_url() ?>" alt="희망씨 로고" class="h-9" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
            <i data-lucide="leaf" class="w-7 h-7" style="display: none; color: var(--primary);"></i>
          <?php else: ?>
            <i data-lucide="leaf" class="w-7 h-7" style="color: var(--primary);"></i>
          <?php endif; ?>
        </a>
      </div>
      <!-- 모바일 햄버거 버튼 -->
      <button
        type="button"
        id="mobileMenuToggle"
        class="d-md-none d-inline-flex align-items-center justify-content-center w-10 h-10 rounded border text-primary"
        style="border-color: var(--primary-light); outline: none;"
        aria-controls="mobileMenu"
        aria-expanded="false"
        aria-label="메뉴 열기">
        <i data-lucide="menu" class="w-6 h-6"></i>
      </button>

      <nav class="d-none d-md-flex gap-1 overflow-auto overflow-md-visible" role="navigation" aria-label="주요 메뉴">
        <?php foreach ($menus as $menuIndex => $menu): ?>
          <div class="relative group menu-item-<?php echo $menuIndex; ?>" tabindex="0">
            <button class="d-flex align-items-center gap-1 text-forest-600 hover:text-lime-600 py-2 px-3 rounded nav-button-hover transition-all" 
                    style="border: none; outline: none; background: transparent;"
                    aria-haspopup="true" aria-expanded="false">
              <span><?php echo htmlspecialchars($menu['title']); ?></span>
              <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </button>
            <div class="dropdown-menu" role="menu" style="position: absolute; top: 100%; left: 0; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 12px; min-width: 12rem; z-index: 50; opacity: 0; visibility: hidden; pointer-events: none; transition: all 0.2s ease; display: block !important;">
              <?php foreach ($menu['items'] as $index => $item): ?>
                <?php
                  // 새로운 데이터 구조에서 링크 생성
                  $itemTitle = is_array($item) ? $item['title'] : $item;
                  $itemSlug = is_array($item) ? $item['slug'] : null;
                  $boardId = is_array($item) && isset($item['board_id']) ? $item['board_id'] : null;
                  
                  // 게시판 ID가 있으면 게시판 링크 생성
                  if ($boardId) {
                    $href = '/board/list/' . $boardId . '/';
                  } else if ($itemSlug) {
                    // 메뉴 구조에 따른 경로 생성
                    $parentSlug = $menu['slug'];
                    $href = '/' . $parentSlug . '/' . $itemSlug . '.php';
                  } else {
                    // 레거시 링크 매핑 시스템 사용
                    if (isset($introBoardLinks[$itemTitle])) {
                      $href = $introBoardLinks[$itemTitle];
                    } else if (isset($programLinks[$itemTitle])) {
                      $href = $programLinks[$itemTitle];
                    } else if (isset($donateLinks[$itemTitle])) {
                      $href = $donateLinks[$itemTitle];
                    } else if (isset($communityLinks[$itemTitle])) {
                      $href = $communityLinks[$itemTitle];
                    } else {
                      $href = '/theme/natural-green/index.php?page=' . urlencode($itemTitle);
                    }
                  }
                ?>
                <a
                  href="<?php echo $href; ?>"
                  class="block w-full text-left px-4 py-2.5 text-sm text-forest-600 hover:text-lime-600 transition-all rounded-lg mx-2 dropdown-menu-link"
                  role="menuitem"
                  aria-current="<?php echo ($currentPage === $itemTitle) ? 'page' : 'false'; ?>"
                >
                  <?php echo htmlspecialchars($itemTitle); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </nav>
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
              // 새로운 데이터 구조에서 링크 생성 (데스크톱과 동일)
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
                } else if (isset($donateLinks[$itemTitle])) {
                  $href = $donateLinks[$itemTitle];
                } else if (isset($communityLinks[$itemTitle])) {
                  $href = $communityLinks[$itemTitle];
                } else {
                  $href = '/theme/natural-green/index.php?page=' . urlencode($itemTitle);
                }
              }
            ?>
            <li>
              <a href="<?php echo $href; ?>" class="block px-2 py-2 rounded-md text-forest-600 hover:text-lime-600 transition" aria-current="<?php echo ($currentPage === $itemTitle) ? 'page' : 'false'; ?>">
                <?php echo htmlspecialchars($itemTitle); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </nav>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    var toggleBtn = document.getElementById('mobileMenuToggle');
    var panel = document.getElementById('mobileMenu');
    var closeBtn = document.getElementById('mobileMenuClose');
    var sectionBtns = panel ? panel.querySelectorAll('[data-section]') : [];
    var previouslyFocused = null;

    function openMenu(){
      if (!panel) return;
      previouslyFocused = document.activeElement;
      panel.classList.remove('hidden');
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
      if (window.lucide && window.lucide.createIcons) window.lucide.createIcons();
      // 포커스 트랩: 첫 포커스 대상 지정
      var focusables = panel.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
      if (focusables.length) {
        focusables[0].focus();
      }
    }

    function closeMenu(){
      if (!panel) return;
      panel.classList.add('hidden');
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
      collapseAll();
      // 이전 포커스 복원
      if (previouslyFocused && previouslyFocused.focus) {
        previouslyFocused.focus();
      }
    }

    function collapseAll(){
      if (!panel) return;
      var lists = panel.querySelectorAll('ul[id^="mm-section-"]');
      lists.forEach(function(ul){ ul.classList.add('hidden'); });
      sectionBtns.forEach(function(btn){
        btn.setAttribute('aria-expanded','false');
        var icon = btn.querySelector('i');
        if (icon) icon.style.transform = '';
      });
    }

    if (toggleBtn) toggleBtn.addEventListener('click', function(){
      if (panel.classList.contains('hidden')) openMenu(); else closeMenu();
    });
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    if (panel) panel.addEventListener('click', function(e){ if (e.target === panel) closeMenu(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });

    // 포커스 트랩: Tab 순환
    if (panel) {
      panel.addEventListener('keydown', function(e){
        if (e.key !== 'Tab') return;
        var focusables = panel.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (!focusables.length) return;
        var first = focusables[0];
        var last = focusables[focusables.length - 1];
        var active = document.activeElement;
        if (e.shiftKey && active === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && active === last) { e.preventDefault(); first.focus(); }
      });
    }

    // 아코디언: 메인메뉴 클릭 시 해당 섹션만 열기
    sectionBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = btn.getAttribute('aria-controls');
        var target = id ? document.getElementById(id) : null;
        var isOpen = target && !target.classList.contains('hidden');
        collapseAll();
        if (!isOpen && target){
          target.classList.remove('hidden');
          btn.setAttribute('aria-expanded','true');
          var icon = btn.querySelector('i');
          if (icon) icon.style.transform = 'rotate(180deg)';
        }
      });
    });

    // 뷰포트 확장 시 패널 닫기
    window.addEventListener('resize', function(){
      if (window.matchMedia('(min-width: 768px)').matches) closeMenu();
    });

    // 드롭다운 메뉴 위치 동적 조정 (더 정확한 버전)
    function adjustDropdownPosition() {
      const menuItems = document.querySelectorAll('nav .relative.group');
      
      menuItems.forEach((menuItem, index) => {
        const dropdown = menuItem.querySelector('.dropdown-menu');
        if (!dropdown) return;
        
        // 각 메뉴 아이템에 대해 새로운 이벤트 리스너 설정
        const handleMouseEnter = () => {
          requestAnimationFrame(() => {
            const menuRect = menuItem.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const dropdownWidth = 192; // min-w-[12rem] = 192px
            const padding = 20; // 여백
            
            // 드롭다운이 오른쪽으로 넘어가는지 확인
            const wouldOverflow = menuRect.left + dropdownWidth > viewportWidth - padding;
            
            if (wouldOverflow) {
              // 화면 밖으로 나가면 오른쪽 정렬
              dropdown.style.left = 'auto';
              dropdown.style.right = '0';
              dropdown.classList.remove('left-0');
              dropdown.classList.add('right-0');
            } else {
              // 화면 안에 있으면 왼쪽 정렬
              dropdown.style.left = '0';
              dropdown.style.right = 'auto';
              dropdown.classList.remove('right-0');
              dropdown.classList.add('left-0');
            }
          });
        };
        
        // 기존 이벤트 리스너 제거하고 새로 추가
        menuItem.removeEventListener('mouseenter', handleMouseEnter);
        menuItem.addEventListener('mouseenter', handleMouseEnter);
      });
    }

    // 드롭다운 hover 강제 수정 함수
    function forceDropdownHoverStyles() {
      const dropdownLinks = document.querySelectorAll('.dropdown-menu a');
      
      dropdownLinks.forEach(link => {
        // 기본 스타일 강제 적용
        link.style.borderRadius = '8px';
        link.style.margin = '2px';
        link.style.overflow = 'hidden';
        link.style.transition = 'all 0.2s ease';
        link.style.boxSizing = 'border-box';
        
        // hover 이벤트 직접 처리
        link.addEventListener('mouseenter', function() {
          this.style.backgroundColor = '#e8f4e6';
          this.style.transform = 'scale(0.95)';
        });
        
        link.addEventListener('mouseleave', function() {
          this.style.backgroundColor = '';
          this.style.transform = '';
        });
      });
    }

    // 초기 위치 조정 및 리사이즈 시 재조정
    if (typeof adjustDropdownPosition === 'function') {
      adjustDropdownPosition();
      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(adjustDropdownPosition, 150);
      });
    }
    
    // 드롭다운 hover 스타일 강제 적용
    forceDropdownHoverStyles();
    
    // 모든 드롭다운 메뉴 초기 숨김 처리
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    allDropdowns.forEach(dropdown => {
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
        dropdown.style.pointerEvents = 'none';
        dropdown.style.transform = 'translateY(-5px)';
    });
    
    // 드롭다운 메뉴 호버 이벤트 추가
    const menuItems = document.querySelectorAll('.relative.group');
    menuItems.forEach(menuItem => {
        const dropdown = menuItem.querySelector('.dropdown-menu');
        if (dropdown) {
            // 초기 상태 확실히 숨김
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.pointerEvents = 'none';
            dropdown.style.transform = 'translateY(-5px)';
            
            // 마우스 엔터 이벤트
            menuItem.addEventListener('mouseenter', function() {
                dropdown.style.opacity = '1';
                dropdown.style.visibility = 'visible';
                dropdown.style.pointerEvents = 'auto';
                dropdown.style.transform = 'translateY(0)';
            });
            
            // 마우스 리브 이벤트
            menuItem.addEventListener('mouseleave', function() {
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
                dropdown.style.pointerEvents = 'none';
                dropdown.style.transform = 'translateY(-5px)';
            });
            
            // 포커스 이벤트
            menuItem.addEventListener('focusin', function() {
                dropdown.style.opacity = '1';
                dropdown.style.visibility = 'visible';
                dropdown.style.pointerEvents = 'auto';
                dropdown.style.transform = 'translateY(0)';
            });
            
            // 포커스 아웃 이벤트
            menuItem.addEventListener('focusout', function(e) {
                // 포커스가 메뉴 항목 내부에 있는지 확인
                if (!menuItem.contains(e.relatedTarget)) {
                    dropdown.style.opacity = '0';
                    dropdown.style.visibility = 'hidden';
                    dropdown.style.pointerEvents = 'none';
                    dropdown.style.transform = 'translateY(-5px)';
                }
            });
        }
    });
  });
</script>

<style>
/* 드롭다운 메뉴 위치 조정을 위한 CSS */

/* 희망씨 사업 메뉴 (인덱스 1) - 오른쪽 정렬 */
.menu-item-1 .dropdown-menu {
  left: auto !important;
  right: 0 !important;
}

/* 희망씨 후원안내 메뉴 (인덱스 2) - 오른쪽 정렬 */
.menu-item-2 .dropdown-menu {
  left: auto !important;
  right: 0 !important;
}

/* 커뮤니티 메뉴 (인덱스 3) - 오른쪽 정렬 */
.menu-item-3 .dropdown-menu {
  left: auto !important;
  right: 0 !important;
}

/* 희망씨 소개 메뉴 (인덱스 0)만 왼쪽 정렬 유지 */
.menu-item-0 .dropdown-menu {
  left: 0 !important;
  right: auto !important;
}

/* 중간 화면에서는 희망씨 사업부터 오른쪽 정렬 */
@media (max-width: 1200px) {
  .menu-item-1 .dropdown-menu,
  .menu-item-2 .dropdown-menu,
  .menu-item-3 .dropdown-menu {
    left: auto !important;
    right: 0 !important;
  }
}

/* 작은 화면에서는 모든 메뉴를 오른쪽 정렬 */
@media (max-width: 1024px) {
  .menu-item-0 .dropdown-menu,
  .menu-item-1 .dropdown-menu,
  .menu-item-2 .dropdown-menu,
  .menu-item-3 .dropdown-menu {
    left: auto !important;
    right: 0 !important;
  }
}

/* 추가 안전장치 - 모든 드롭다운에 대해 최소 너비 제한 */
.dropdown-menu {
  min-width: 12rem;
  max-width: 18rem;
}

/* 매우 작은 화면에서는 중앙 정렬 */
@media (max-width: 768px) {
  .dropdown-menu {
    left: 50% !important;
    right: auto !important;
    transform: translateX(-50%) !important;
    min-width: 10rem;
  }
}

/* 간단한 hover 오버플로우 방지 */
@media (min-width: 768px) {
  .max-w-7xl {
    padding-right: 1.5rem !important;
  }
}

/* 메뉴 아이템 호버 효과 */
.relative.group:hover .dropdown-menu,
.relative.group:focus-within .dropdown-menu {
  opacity: 1 !important;
  visibility: visible !important;
  pointer-events: auto !important;
  transform: translateY(0) !important;
}

/* 드롭다운 메뉴 링크 - 안정적인 호버 효과 */
.dropdown-menu a, 
.dropdown-menu a:link, 
.dropdown-menu a:visited {
  border-radius: 0.5rem !important;
  margin: 0.125rem !important;
  padding: 0.5rem 1rem !important;
  display: block !important;
  transition: all 0.15s ease !important;
  position: relative !important;
}

.dropdown-menu a:hover, 
.dropdown-menu a:focus {
  background-color: rgba(var(--primary-rgb, 132, 204, 22), 0.1) !important;
  color: var(--primary) !important;
  text-decoration: none !important;
  transform: none !important;
}

/* 네비게이션 버튼 - 안정적인 패딩과 호버 */
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
  background-color: rgba(var(--primary-rgb, 132, 204, 22), 0.1) !important;
  color: var(--primary) !important;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
}

.nav-button-hover:focus {
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
}
</style>

<script>
// 깜빡임 방지를 위한 안정적인 호버 효과
document.addEventListener('DOMContentLoaded', function() {
  // 추가적인 깜빡임 방지 스타일 적용
  const antiFlickerStyle = document.createElement('style');
  antiFlickerStyle.id = 'anti-flicker-styles';
  antiFlickerStyle.textContent = `
    /* 깜빡임 방지 - 안정적인 transition */
    .nav-button-hover,
    .dropdown-menu a {
      transition: background-color 0.15s ease !important;
      will-change: background-color;
    }
    
    /* 호버 상태 통일 */
    .nav-button-hover:hover {
      background-color: rgba(var(--primary-rgb), 0.08) !important;
    }
    
    .dropdown-menu a:hover,
    .dropdown-menu a:focus {
      background-color: rgba(var(--primary-rgb), 0.08) !important;
      color: var(--primary) !important;
    }
    
    /* 중복 이벤트 방지 */
    .nav-button-hover,
    .dropdown-menu a {
      pointer-events: auto !important;
    }
  `;
  
  // 기존 깜빡임 방지 스타일 제거하고 새로 추가
  const existingStyle = document.getElementById('anti-flicker-styles');
  if (existingStyle) {
    existingStyle.remove();
  }
  document.head.appendChild(antiFlickerStyle);
  
  console.log('깜빡임 방지 스타일 적용 완료');
});
</script>

