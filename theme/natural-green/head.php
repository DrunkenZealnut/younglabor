<?php if (!defined('_HOPEC_')) exit; ?>
<?php 
// Configuration loader 및 Natural Green 단일 테마 로더 사용
require_once __DIR__ . '/../../includes/config_loader.php';
require_once __DIR__ . '/../../includes/NaturalGreenThemeLoader.php';
$theme = getNaturalGreenTheme();
?>
<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php
      // 환경변수 기반 메타 데이터 설정 (g5 배열 의존성 제거)
      $pageTitle = getIntegratedSetting('site_name', getOrgName('full'));
      $metaDescription = getIntegratedSetting('site_description', getOrgName('full') . ' 공식 웹사이트');
      $reqUri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '/';
      $canonical = app_url() . '/';
    ?>
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="language" content="ko" />
    <meta property="og:locale" content="ko_KR" />
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <?php 
    // Natural Green 단일 테마 CSS 로드
    renderNaturalGreenTheme();
    ?>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <?php
    // 기존 보드 CSS는 유지 (필요한 경우)
    if (file_exists(__DIR__ . '/css/default_board.css')): 
    ?>
    <link rel="stylesheet" href="<?php echo app_url('theme/natural-green'); ?>/css/default_board.css" />
    <?php endif; ?>
    
    <?php
    // 반응형 홈페이지 CSS 추가 로드 (홈페이지 전용)
    $currentPage = isset($_GET['page']) ? trim($_GET['page']) : 'home';
    if ($currentPage === 'home' && file_exists(__DIR__ . '/assets/css/responsive-home.css')): 
    ?>
    <link rel="stylesheet" href="<?php echo app_url('theme/natural-green'); ?>/assets/css/responsive-home.css?v=<?php echo filemtime(__DIR__ . '/assets/css/responsive-home.css'); ?>" />
    <?php endif; ?>
    
    <?php if (!empty($GLOBALS['analytics_id'])): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $GLOBALS['analytics_id'] ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= $GLOBALS['analytics_id'] ?>');
    </script>
    <?php endif; ?>
    <style>
      /* 레이아웃 필수 유틸리티 */
      .min-h-screen{min-height:100vh}
      .flex{display:flex}
      .flex-col{flex-direction:column}
      .flex-1{flex:1 1 auto}
      
      /* 테마 적용 확인을 위한 시각적 표시 */
      body::after {
        content: "현재 테마: " attr(data-theme);
        position: fixed;
        top: 10px;
        left: 10px;
        background: var(--primary);
        color: var(--primary-foreground);
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 10000;
        pointer-events: none;
        opacity: 0.8;
      }
    </style>
    
    <script>
      // 테마 로딩 상태 실시간 추적
      document.addEventListener('DOMContentLoaded', function() {
        const themeLink = document.getElementById('natural-green-theme');
        if (themeLink) {
          console.log('🎨 Natural Green 테마 CSS 로드됨:', themeLink.href);
          console.log('🎯 Primary 색상:', getComputedStyle(document.documentElement).getPropertyValue('--primary'));
        }
        
        // 테마 정보가 있으면 사용, 없으면 CSS 변수 직접 확인
        if (window.HOPEC_THEME) {
          console.log('🎨 테마 정보:', window.HOPEC_THEME);
          document.body.setAttribute('data-theme', window.HOPEC_THEME.display_name.toUpperCase());
        } else {
          // Fallback: CSS 변수 확인
          const currentTheme = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
          console.log('🎨 현재 테마 primary 색상:', currentTheme);
          
          if (currentTheme.includes('#84cc16') || currentTheme.includes('rgb(132, 204, 22)')) {
            document.body.setAttribute('data-theme', 'NATURAL-GREEN');
          } else {
            document.body.setAttribute('data-theme', 'DETECTED: ' + currentTheme);
          }
        }
        
        // 드롭다운 메뉴 호버 기능 강화 v3.0
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
            }, 100); // 100ms 지연으로 안정성 확보
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
        
        console.log('🍿 드롭다운 메뉴 호버 기능 활성화됨 - 아이템 수:', dropdownItems.length);
        
        // 모바일 메뉴 토글 기능 강화
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const mobileMenu = document.getElementById('mobileMenu');
        
        if (mobileMenuToggle && mobileMenu) {
          mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.remove('d-none');
            mobileMenu.style.display = 'block';
            document.body.style.overflow = 'hidden';
            // 접근성을 위한 포커스 이동
            const firstFocusableElement = mobileMenu.querySelector('button, a');
            if (firstFocusableElement) {
              setTimeout(() => firstFocusableElement.focus(), 100);
            }
          });
          
          if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
          }
          
          // 배경 클릭시 메뉴 닫기
          mobileMenu.addEventListener('click', function(e) {
            if (e.target === mobileMenu) {
              closeMobileMenu();
            }
          });
          
          // ESC 키로 메뉴 닫기
          document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !mobileMenu.classList.contains('d-none')) {
              closeMobileMenu();
            }
          });
          
          function closeMobileMenu() {
            mobileMenu.classList.add('d-none');
            mobileMenu.style.display = 'none';
            document.body.style.overflow = '';
            // 포커스를 메뉴 토글 버튼으로 되돌리기
            mobileMenuToggle.focus();
          }
        }
        
        console.log('📱 모바일 메뉴 기능은 navigation.php에서 처리됨');
      });
    </script>
  </head>
  <body class="min-h-screen flex flex-col bg-[#FEFEFE]" style="font-family: 'Noto Sans KR', sans-serif;">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:m-4 focus:p-2 focus:bg-white focus:border focus:border-lime-400">본문 바로가기</a>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <div id="wrapper"><div id="container_wr"><div id="container">

