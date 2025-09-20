<?php
  // 현재 페이지 활성화 표시를 위해 쿼리 파라미터 확인
  $currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
  // 메뉴 구조 (TSX 기반 구조를 단순화)
  $menus = [
    [
      'title' => '희망씨 소개',
      'items' => ['희망씨는','미션 및 비전','조직도 및 연혁','오시는길','재정보고'],
    ],
    [
      'title' => '희망씨 사업',
      'items' => ['국내아동지원사업','해외아동지원사업','노동인권사업','소통 및 회원사업','자원봉사안내'],
    ],
    [
      'title' => '희망씨 후원안내',
      'items' => ['정기후원(cms)','일시후원'],
    ],
    [
      'title' => '커뮤니티',
      'items' => ['공지사항','언론보도','소식지','갤러리','자료실','네팔나눔연대여행'],
    ],
  ];
?>
<header class="bg-white/95 border-b border-lime-200 sticky top-0 z-50 shadow-sm backdrop-blur-md" role="banner">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center group">
        <a href="?page=home" class="flex items-center focus:outline-none focus:ring-2 focus:ring-lime-400 rounded">
          <i data-lucide="leaf" class="w-7 h-7 text-lime-500 mr-2"></i>
          <span class="text-2xl text-forest-600 group-hover:text-lime-600 transition-colors">희망씨</span>
          <span class="ml-2 text-sm text-gray-500 group-hover:text-forest-600 transition-colors">사단법인</span>
        </a>
      </div>

      <nav class="hidden md:flex space-x-1" role="navigation" aria-label="주요 메뉴">
        <?php foreach ($menus as $menu): ?>
          <div class="relative group" tabindex="0">
            <button class="flex items-center space-x-1 text-forest-600 hover:text-lime-600 py-2 px-3 rounded-lg hover:bg-natural-200 transition-all" aria-haspopup="true" aria-expanded="false">
              <span><?php echo htmlspecialchars($menu['title']); ?></span>
              <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </button>
            <div class="absolute top-full left-0 bg-white/95 backdrop-blur-md border border-lime-200 rounded-xl shadow-xl py-3 min-w-[12rem] z-10 opacity-0 pointer-events-none group-focus-within:opacity-100 group-hover:opacity-100 group-hover:pointer-events-auto group-focus-within:pointer-events-auto transition-opacity" role="menu">
              <?php foreach ($menu['items'] as $index => $item): ?>
                <a
                  href="?page=<?php echo urlencode($item); ?>"
                  class="block w-full text-left px-4 py-2.5 text-sm text-forest-600 hover:bg-natural-200 hover:text-lime-600 transition-all rounded-lg mx-2"
                  role="menuitem"
                  aria-current="<?php echo ($currentPage === $item) ? 'page' : 'false'; ?>"
                >
                  <?php echo htmlspecialchars($item); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </nav>
    </div>
  </div>
</header>

