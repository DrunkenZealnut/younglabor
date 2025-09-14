<?php
  // 단일 콘텐츠 렌더러: ?page= 값에 따라 다른 섹션 출력
  $page = isset($_GET['page']) ? $_GET['page'] : '';
  function backToHomeLink() {
    return '<a href="?page=home" class="text-forest-600 hover:text-lime-600 hover:bg-natural-200 rounded-full px-6 py-3 transition-all inline-flex items-center focus:outline-none focus:ring-2 focus:ring-lime-400"><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>홈으로 돌아가기</a>';
  }
?>

<div class="min-h-screen bg-natural-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <?php echo backToHomeLink(); ?>
    </div>

    <?php if ($page === '희망씨는'): ?>
      <div class="space-y-8">
        <div class="text-center animate-in fade-in slide-in-from-top">
          <div class="flex justify-center items-center mb-4">
            <i data-lucide="leaf" class="w-8 h-8 text-lime-500 mr-2"></i>
            <h1 class="text-5xl text-forest-700">희망씨는</h1>
            <i data-lucide="sprout" class="w-8 h-8 text-lime-500 ml-2"></i>
          </div>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">더불어 사는 삶을 위해 설립된 따뜻한 사단법인입니다</p>
        </div>

        <div class="border border-lime-200 shadow-xl bg-white rounded-2xl p-6 space-y-6">
          <div class="p-6 bg-natural-100 rounded-xl border-l-4 border-lime-400">
            <p class="text-forest-700">이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여 희망연대노동조합 조합원과 지역주민들이 함께 설립한 법인입니다.</p>
          </div>
          <div class="p-6 bg-gradient-to-r from-pink-50 to-rose-50 rounded-xl border-l-4 border-pink-400">
            <p class="text-forest-700">모든 아동청소년이 고유한 인격체로서 존중받고 어떠한 이유로도 차별받지 않도록 아동권리실현에 앞장서는 활동을 진행합니다.</p>
          </div>
          <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border-l-4 border-blue-400">
            <p class="text-forest-700">노동자가 자발적 주체가 되어 나눔연대·생활문화연대를 위한 지속가능한 활동을 만들어 가는데 함께 합니다.</p>
          </div>
          <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-l-4 border-green-400">
            <p class="text-forest-700">지역사회와 함께 아래로 향한 연대 일터와 삶터를 바꾸기 위한 활동에 함께 합니다.</p>
          </div>
        </div>
      </div>

    <?php elseif ($page === '미션 및 비전'): ?>
      <div class="space-y-8">
        <div class="text-center animate-in fade-in slide-in-from-top">
          <h1 class="text-5xl text-forest-700 mb-4">미션 및 비전</h1>
          <p class="text-xl text-gray-600">희망씨가 추구하는 건강한 가치와 목표</p>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
          <div class="border border-lime-200 shadow-xl bg-white rounded-2xl p-6">
            <div class="flex items-center space-x-3 mb-4">
              <i data-lucide="heart" class="w-8 h-8 text-pink-500"></i>
              <h2 class="text-2xl text-forest-600">미션</h2>
            </div>
            <ul class="space-y-4 text-lg">
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="leaf" class="w-5 h-5 text-lime-500"></i><span>아동청소년의 권리 실현</span></li>
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="leaf" class="w-5 h-5 text-lime-500"></i><span>노동자의 자발적 참여를 통한 연대 활동</span></li>
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="leaf" class="w-5 h-5 text-lime-500"></i><span>지역사회와의 상생 협력</span></li>
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="leaf" class="w-5 h-5 text-lime-500"></i><span>사회적 약자를 위한 지원 활동</span></li>
            </ul>
          </div>
          <div class="border border-lime-200 shadow-xl bg-white rounded-2xl p-6">
            <div class="flex items-center space-x-3 mb-4">
              <i data-lucide="globe" class="w-8 h-8 text-lime-500"></i>
              <h2 class="text-2xl text-forest-600">비전</h2>
            </div>
            <ul class="space-y-4 text-lg">
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="sprout" class="w-5 h-5 text-green-500"></i><span>모든 아동이 차별받지 않는 사회</span></li>
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="sprout" class="w-5 h-5 text-green-500"></i><span>경쟁이 아닌 상생의 공동체</span></li>
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="sprout" class="w-5 h-5 text-green-500"></i><span>지속가능한 나눔과 연대</span></li>
              <li class="flex items-center space-x-3 p-3 bg-natural-100 rounded-lg"><i data-lucide="sprout" class="w-5 h-5 text-green-500"></i><span>더불어 사는 건강한 사회</span></li>
            </ul>
          </div>
        </div>
      </div>

    <?php elseif ($page === '정기후원(cms)'): ?>
      <div class="space-y-8">
        <div class="text-center animate-in fade-in slide-in-from-top">
          <div class="flex justify-center items-center mb-4">
            <i data-lucide="star" class="w-8 h-8 text-lime-500 mr-2"></i>
            <h1 class="text-5xl text-forest-700">정기후원</h1>
            <i data-lucide="leaf" class="w-8 h-8 text-lime-500 ml-2"></i>
          </div>
          <p class="text-xl text-gray-600">매월 일정 금액으로 지속적인 나눔에 참여해보세요</p>
        </div>
        <div class="max-w-4xl mx-auto border border-lime-200 shadow-2xl bg-white rounded-2xl">
          <div class="text-center pb-2 p-8">
            <div class="w-20 h-20 bg-gradient-to-br from-lime-400 to-lime-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <i data-lucide="heart" class="w-10 h-10 text-white"></i>
            </div>
            <h2 class="text-3xl text-forest-600">정기후원 안내</h2>
            <p class="text-lg text-gray-600 mt-2">CMS 자동이체를 통한 편리하고 안전한 후원</p>
          </div>
          <div class="space-y-8 p-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <?php
                $options = [
                  ['amount' => '월 1만원', 'title' => '아이돌보미', 'emoji' => '🌸', 'bg' => 'from-pink-400 to-rose-400'],
                  ['amount' => '월 2만원', 'title' => '희망지킴이', 'emoji' => '🌟', 'bg' => 'from-lime-400 to-lime-500'],
                  ['amount' => '월 3만원', 'title' => '나눔파트너', 'emoji' => '🤝', 'bg' => 'from-blue-400 to-cyan-400'],
                  ['amount' => '월 5만원', 'title' => '희망후원자', 'emoji' => '💚', 'bg' => 'from-green-500 to-emerald-700'],
                ];
                foreach ($options as $o):
              ?>
                <div class="text-center p-6 bg-natural-100 rounded-xl border-2 border-lime-200 hover:border-lime-400 hover-lift transition-all">
                  <div class="w-12 h-12 bg-gradient-to-br <?php echo $o['bg']; ?> rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl"><?php echo $o['emoji']; ?></span>
                  </div>
                  <p class="font-medium text-lg text-forest-700"><?php echo htmlspecialchars($o['amount']); ?></p>
                  <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($o['title']); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="bg-gradient-to-r from-lime-100 to-green-100 p-6 rounded-xl border-l-4 border-lime-400">
              <h3 class="font-medium text-forest-700 mb-3 text-lg flex items-center"><i data-lucide="star" class="w-5 h-5 mr-2 text-lime-500"></i>정기후원 혜택</h3>
              <ul class="text-forest-600 space-y-2">
                <li class="flex items-center space-x-2"><i data-lucide="leaf" class="w-4 h-4 text-lime-500"></i><span>연말정산 기부금 공제 혜택</span></li>
                <li class="flex items-center space-x-2"><i data-lucide="leaf" class="w-4 h-4 text-lime-500"></i><span>정기 활동 소식지 발송</span></li>
                <li class="flex items-center space-x-2"><i data-lucide="leaf" class="w-4 h-4 text-lime-500"></i><span>희망씨 행사 초대</span></li>
                <li class="flex items-center space-x-2"><i data-lucide="leaf" class="w-4 h-4 text-lime-500"></i><span>감사패 및 감사장 증정</span></li>
              </ul>
            </div>
            <a href="#" class="block text-center w-full bg-gradient-to-r from-lime-500 to-green-500 hover:from-lime-600 hover:to-green-600 text-white hover:scale-105 transition-all shadow-xl text-lg py-6 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400">정기후원 신청하기</a>
          </div>
        </div>
      </div>

    <?php elseif ($page === '공지사항'): ?>
      <div class="space-y-8">
        <div class="text-center animate-in fade-in slide-in-from-top">
          <h1 class="text-5xl text-forest-700 mb-4">공지사항</h1>
          <p class="text-xl text-gray-600">희망씨의 최신 소식과 공지를 확인하세요</p>
        </div>
        <div class="space-y-4">
          <?php
            $notices = [
              ['title' => '2024년 정기총회 개최 안내', 'date' => '2024-02-15', 'important' => true, 'emoji' => '🏛️'],
              ['title' => '겨울방학 아동지원 프로그램 참가자 모집', 'date' => '2024-01-20', 'important' => false, 'emoji' => '❄️'],
              ['title' => '후원금 사용내역 공개', 'date' => '2024-01-10', 'important' => false, 'emoji' => '💰'],
              ['title' => '네팔 나눔연대여행 참가자 모집', 'date' => '2023-12-28', 'important' => false, 'emoji' => '🏔️'],
              ['title' => '연말연시 사무실 운영 안내', 'date' => '2023-12-20', 'important' => false, 'emoji' => '🎊'],
            ];
            foreach ($notices as $i => $n):
          ?>
            <div class="border border-lime-200 shadow-md bg-white rounded-xl p-6 hover-lift">
              <div class="flex justify-between items-start">
                <div class="flex items-start space-x-4">
                  <div class="text-2xl"><?php echo $n['emoji']; ?></div>
                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                      <?php if ($n['important']): ?><span class="bg-gradient-to-r from-red-400 to-pink-400 text-white text-xs px-3 py-1 rounded-full">중요</span><?php endif; ?>
                      <h3 class="font-medium text-lg text-forest-700"><?php echo htmlspecialchars($n['title']); ?></h3>
                    </div>
                    <p class="text-gray-500 text-sm flex items-center"><i data-lucide="calendar" class="w-4 h-4 mr-1"></i><?php echo $n['date']; ?></p>
                  </div>
                </div>
                <div class="text-gray-300"><i data-lucide="arrow-left" class="w-5 h-5 rotate-180"></i></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    <?php else: ?>
      <div class="text-center py-16">
        <div class="animate-in fade-in slide-in-from-bottom">
          <div class="w-32 h-32 bg-gradient-to-br from-lime-400 to-lime-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="leaf" class="w-16 h-16 text-white"></i>
          </div>
          <h1 class="text-4xl text-forest-700 mb-4"><?php echo htmlspecialchars($page); ?></h1>
          <p class="text-xl text-gray-600 mb-8">해당 페이지는 곧 만나실 수 있습니다!</p>
          <?php echo backToHomeLink(); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

