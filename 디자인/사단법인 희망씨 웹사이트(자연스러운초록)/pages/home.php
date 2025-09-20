<?php
  // 홈 화면: TSX HomePage를 정적 PHP 마크업으로 치환
?>
<section class="relative h-[420px] md:h-[520px] flex items-center justify-center overflow-hidden">
  <div class="absolute inset-0 gradient-natural"></div>
  <div class="absolute inset-0" aria-hidden="true">
    <div class="absolute top-20 left-10 w-32 h-32 bg-lime-400/20 rounded-full blur-xl floating-animation"></div>
    <div class="absolute top-40 right-20 w-24 h-24 bg-green-300/30 rounded-full blur-lg floating-animation" style="animation-delay:2s"></div>
    <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-lime-300/15 rounded-full blur-2xl floating-animation" style="animation-delay:4s"></div>
    <div class="absolute top-1/3 right-1/3 w-20 h-20 bg-green-400/20 rounded-full blur-lg floating-animation" style="animation-delay:1s"></div>
  </div>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center py-24">
    <div class="animate-in fade-in slide-in-from-bottom-8">
      <div class="flex justify-center items-center mb-8">
        <i data-lucide="leaf" class="w-12 h-12 text-lime-500 mr-4"></i>
        <h1 class="text-5xl md:text-7xl text-forest-700">사단법인 <span class="text-lime-600">희망씨</span></h1>
        <i data-lucide="tree-pine" class="w-12 h-12 text-forest-600 ml-4"></i>
      </div>

      <div class="max-w-4xl mx-auto mb-12">
        <p class="text-xl md:text-2xl text-forest-600 mb-6">이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여</p>
        <p class="text-lg md:text-xl text-gray-600">희망연대노동조합 조합원과 지역주민들이 함께 설립한 따뜻한 법인입니다</p>
      </div>

      <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
        <a href="?page=<?php echo urlencode('정기후원(cms)'); ?>" class="bg-lime-500 text-white hover:bg-lime-600 transition-colors duration-200 shadow-lg text-lg px-8 py-4 rounded-full focus:outline-none focus:ring-2 focus:ring-lime-400">후원하기</a>
        <a href="?page=<?php echo urlencode('희망씨는'); ?>" class="border-2 border-lime-500 text-lime-700 hover:bg-lime-50 transition-colors duration-200 text-lg px-8 py-4 rounded-full focus:outline-none focus:ring-2 focus:ring-lime-400">희망씨 알아보기</a>
      </div>
    </div>
  </div>

  <div class="absolute bottom-10 left-10 text-lime-400/60 floating-animation" aria-hidden="true">
    <i data-lucide="heart" class="w-12 h-12"></i>
  </div>
 </section>

<!-- 최근 활동 보기 (카드형: 이미지 + 제목) -->
<section class="py-16 bg-white" aria-labelledby="home-recent-activities">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-8">
      <div>
        <h2 id="home-recent-activities" class="text-3xl md:text-4xl text-forest-700">최근 활동 보기</h2>
        <p class="text-gray-600 mt-2">현장의 따끈한 이야기들을 카드로 확인하세요</p>
      </div>
      <a href="?page=<?php echo urlencode('소식지'); ?>" class="text-forest-600 hover:text-lime-600 transition-colors duration-150">더 보기</a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
        $recentCards = [
          ['title' => '겨울방학 아동지원 프로그램 진행', 'img' => 'https://picsum.photos/seed/a1/600/400', 'target' => '소식지'],
          ['title' => '네팔 나눔연대 현장 소식', 'img' => 'https://picsum.photos/seed/a2/600/400', 'target' => '소식지'],
          ['title' => '노동인권 교육 워크숍', 'img' => 'https://picsum.photos/seed/a3/600/400', 'target' => '소식지'],
        ];
        foreach ($recentCards as $card):
      ?>
        <a href="?page=<?php echo urlencode($card['target']); ?>" class="block bg-white rounded-xl border border-lime-200 hover-lift shadow-sm overflow-hidden focus:outline-none focus:ring-2 focus:ring-lime-400">
          <img src="<?php echo htmlspecialchars($card['img']); ?>" alt="<?php echo htmlspecialchars($card['title']); ?> 이미지" loading="lazy" class="w-full h-48 object-cover" />
          <div class="p-4">
            <h3 class="text-forest-700 text-lg"><?php echo htmlspecialchars($card['title']); ?></h3>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 공지사항 (리스트형) -->
<section class="py-16 bg-natural-100" aria-labelledby="home-notices">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-6">
      <h2 id="home-notices" class="text-3xl text-forest-700">공지사항</h2>
      <a href="?page=<?php echo urlencode('공지사항'); ?>" class="text-forest-600 hover:text-lime-600 transition-colors duration-150">더 보기</a>
    </div>
    <div class="divide-y">
      <?php
        $homeNotices = [
          ['title' => '2024년 정기총회 개최 안내', 'date' => '2024-02-15', 'important' => true],
          ['title' => '겨울방학 아동지원 프로그램 참가자 모집', 'date' => '2024-01-20', 'important' => false],
          ['title' => '후원금 사용내역 공개', 'date' => '2024-01-10', 'important' => false],
          ['title' => '네팔 나눔연대여행 참가자 모집', 'date' => '2023-12-28', 'important' => false],
          ['title' => '연말연시 사무실 운영 안내', 'date' => '2023-12-20', 'important' => false],
        ];
        foreach ($homeNotices as $n):
      ?>
        <a href="?page=<?php echo urlencode('공지사항'); ?>" class="flex items-start justify-between py-4 group focus:outline-none focus:ring-2 focus:ring-lime-400 px-1 -mx-1 rounded">
          <div class="flex items-start gap-3">
            <?php if ($n['important']): ?><span class="bg-gradient-to-r from-red-400 to-pink-400 text-white text-xs px-2.5 py-1 rounded-full">중요</span><?php endif; ?>
            <span class="text-forest-700 group-hover:text-lime-600 transition-colors"><?php echo htmlspecialchars($n['title']); ?></span>
          </div>
          <div class="flex items-center text-sm text-gray-500">
            <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
            <time datetime="<?php echo htmlspecialchars($n['date']); ?>"><?php echo htmlspecialchars($n['date']); ?></time>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 갤러리 (카드형: 이미지 + 제목) -->
<section class="py-16 bg-white" aria-labelledby="home-gallery">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-8">
      <h2 id="home-gallery" class="text-3xl md:text-4xl text-forest-700">갤러리</h2>
      <a href="?page=<?php echo urlencode('갤러리'); ?>" class="text-forest-600 hover:text-lime-600 transition-colors duration-150">더 보기</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
        $gallery = [
          ['title' => '현장 스케치 01', 'img' => 'https://picsum.photos/seed/g1/600/400'],
          ['title' => '행사 포토 02', 'img' => 'https://picsum.photos/seed/g2/600/400'],
          ['title' => '소통의 시간 03', 'img' => 'https://picsum.photos/seed/g3/600/400'],
          ['title' => '연대의 순간 04', 'img' => 'https://picsum.photos/seed/g4/600/400'],
        ];
        foreach ($gallery as $g):
      ?>
        <a href="?page=<?php echo urlencode('갤러리'); ?>" class="block bg-white rounded-xl border border-lime-200 hover-lift shadow-sm overflow-hidden focus:outline-none focus:ring-2 focus:ring-lime-400">
          <img src="<?php echo htmlspecialchars($g['img']); ?>" alt="<?php echo htmlspecialchars($g['title']); ?> 이미지" loading="lazy" class="w-full h-40 object-cover" />
          <div class="p-4">
            <h3 class="text-forest-700 text-base"><?php echo htmlspecialchars($g['title']); ?></h3>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

 

