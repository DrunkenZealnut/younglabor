<?php
// 자연스러운초록 테마 메인 페이지
// 테마 bootstrap 로드 (DatabaseManager, 헬퍼 함수 등 초기화)
require_once __DIR__ . '/bootstrap.php';

// HOPEC 프레임워크 초기화
if (!defined('_HOPEC_')) {
  $frameworkBootstrap = __DIR__ . '/../../includes/bootstrap.php';
  if (file_exists($frameworkBootstrap)) {
    include_once $frameworkBootstrap;
  }
  
  // 템플릿 헬퍼 함수 로드
  $templateHelpers = __DIR__ . '/../../includes/template_helpers.php';
  if (file_exists($templateHelpers)) {
    include_once $templateHelpers;
  }
}

// 간단 라우팅 (디자인 원본 유지)
$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

// 직접 테마 경로로 홈(page=home)에 접근한 경우, 루트로 리다이렉트하여 URL 정규화
// (루트 index.php 포함을 통해 들어오는 경우에는 SCRIPT_NAME이 루트이므로 리다이렉트 되지 않음)
if ($page === 'home' && isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/theme/') !== false) {
  header('Location: /', true, 302);
  exit;
}

// 메뉴 → 템플릿 파일 매핑 (존재 시 해당 템플릿을 로드, 없으면 content.php)
$pageTemplates = [
  '희망씨는' => 'about.php',
  '미션 및 비전' => 'mission.php',
  '이사장 인사말' => 'greeting.php',
  '조직도' => 'org.php',
  '연혁' => 'history.php',
  '오시는길' => 'contact.php',
  '재정보고' => 'finance.php',
  '국내아동지원사업' => 'domestic.php',
  '해외아동지원사업' => 'overseas.php',
  '노동인권사업' => 'labor-rights.php',
  '소통 및 회원사업' => 'community.php',
  '자원봉사안내' => 'volunteer.php',
  '정기후원(cms)' => 'donation-regular.php',
  '일시후원' => 'donation-onetime.php',
  // 공지사항은 신규 라우트로 연결 (테마 내부 단일 템플릿 대신 실 페이지 사용)
  '공지사항' => null,
  '언론보도' => 'press.php',
  '소식지' => 'newsletter.php',
  '갤러리' => 'gallery.php',
  '자료실' => 'resources.php',
  '네팔나눔연대여행' => 'nepal.php',
];
?>
<?php include_once __DIR__ . '/head.php'; ?>
    
    <?php // 본문 시작 ?>
    
    
    <?php // 라우팅 본문 출력 ?>
    
    
    
    <main id="main" class="flex-1">
      <?php
        if ($page === 'home') {
          include __DIR__ . '/pages/home.php';
        } else {
          include __DIR__ . '/pages/content.php';
        }
      ?>
    </main>
    
    <script>
      try {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
        }
      } catch(e){}
      document.addEventListener('click', function(e){
        const target = e.target.closest('a');
        if(!target) return;
        const href = target.getAttribute('href') || '';
        if (href.includes('index.php?page=')) {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    </script>
<?php include_once __DIR__ . '/tail.php'; ?>

