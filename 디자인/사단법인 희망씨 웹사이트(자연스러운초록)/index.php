<?php
  // 간단한 라우팅: ?page= 값으로 페이지 전환
  $page = isset($_GET['page']) ? trim($_GET['page']) : 'home';
  // 접근 허용 페이지 화이트리스트 (보안)
  $allowedPages = [
    'home',
    '희망씨는',
    '미션 및 비전',
    '조직도 및 연혁',
    '오시는길',
    '재정보고',
    '국내아동지원사업',
    '해외아동지원사업',
    '노동인권사업',
    '소통 및 회원사업',
    '자원봉사안내',
    '정기후원(cms)',
    '일시후원',
    '공지사항',
    '언론보도',
    '소식지',
    '갤러리',
    '자료실',
    '네팔나눔연대여행',
  ];
  if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
  }
?>
<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>사단법인 희망씨</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <!-- Tailwind CDN: 유틸리티 클래스 사용 (개발/시연 목적) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome (브랜드 아이콘용) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkfAM6NcnT1QjO2lUF6ZP5QBJ1z9aE6U2JX1Vt1xN6D+9j4Yx04ec5R1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- 애니메이션 유틸리티 대체용 간단 스타일 (필요 클래스 최소치) -->
    <style>
      .animate-in { opacity: 0; transform: translateY(8px); animation: fadeUp .6s forwards; }
      .fade-in { opacity: 0; animation: fade .8s forwards; }
      .slide-in-from-bottom-4 { transform: translateY(1rem); }
      .slide-in-from-bottom-8 { transform: translateY(2rem); }
      .slide-in-from-top { transform: translateY(-1rem); }
      .slide-in-from-left { transform: translateX(-1rem); }
      .slide-in-from-right { transform: translateX(1rem); }
      @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
      @keyframes fade { to { opacity: 1; } }
    </style>
    <!-- 프로젝트 고유 CSS -->
    <link rel="stylesheet" href="./styles/globals.css" />
    <!-- Lucide 아이콘 CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>
  <body class="min-h-screen flex flex-col bg-[#FEFEFE]" style="font-family: 'Noto Sans KR', sans-serif;">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:m-4 focus:p-2 focus:bg-white focus:border focus:border-lime-400">본문 바로가기</a>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main id="main" class="flex-1">
      <?php
        if ($page === 'home') {
          include __DIR__ . '/pages/home.php';
        } else {
          // 단일 콘텐츠 스위치 페이지
          include __DIR__ . '/pages/content.php';
        }
      ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
      // Lucide 아이콘 렌더링
      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      // 내비게이션에서 앵커 클릭 시 스크롤 상단 이동(접근성 보조)
      document.addEventListener('click', function(e){
        const target = e.target.closest('a');
        if(!target) return;
        if (target.getAttribute('href') && target.getAttribute('href').startsWith('?page=')) {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    </script>
  </body>
</html>

