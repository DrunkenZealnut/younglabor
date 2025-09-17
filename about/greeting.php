<?php
// 모던 부트스트랩 시스템 사용
require_once __DIR__ . '/../bootstrap/app.php';

// 페이지 메타
$pageTitle = '이사장 인사말 | ' . app_name();
$pageDescription = '사단법인 희망씨 이사장 인사말';
$currentSlug = 'greeting';

// 헤더 포함
include __DIR__ . '/../includes/header.php';
?>
<main id="container" role="main">
  <article aria-labelledby="greeting-title" class="max-w-4xl mx-auto px-4 py-10">
    <header class="mb-8">
      <p class="text-sm text-gray-500">About</p>
      <h1 id="greeting-title" class="text-3xl md:text-4xl font-bold text-forest-600">이사장 인사말</h1>
    </header>

    <section class="relative rounded-2xl p-6 md:p-8 mb-8 overflow-hidden shadow-2xl hover:shadow-3xl transition-all duration-500 hover:-translate-y-2" 
             style="background: linear-gradient(135deg, var(--primary) 0%, color-mix(in srgb, var(--primary) 80%, transparent) 50%, color-mix(in srgb, var(--primary) 60%, transparent) 100%); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.2);" 
             aria-labelledby="headline">
      <!-- 장식 요소 -->
      <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10 transform translate-x-16 -translate-y-16" style="background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);"></div>
      <div class="absolute bottom-0 left-0 w-24 h-24 rounded-full opacity-5 transform -translate-x-12 translate-y-12" style="background: radial-gradient(circle, rgba(255,255,255,0.4) 0%, transparent 70%);"></div>
      
      <!-- 콘텐츠 -->
      <div class="relative z-10">
        <h2 id="headline" class="text-2xl md:text-3xl font-extrabold text-white drop-shadow-lg">희망씨가 희망입니다.</h2>
      </div>
      
      <!-- 하단 그라데이션 오버레이 -->
      <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
    </section>

    <section class="prose prose-lg max-w-none leading-8 text-gray-700">
      <p>안녕하세요.</p>
      <p>희망씨의 멋진 4대 활동원칙을 되뇌어봅니다.</p>
      <p><strong>시혜가 아닌 나눔, 봉사가 아닌 연대, 기부가 아닌 참여, 사람 중심 조직문화.</strong></p>
      <p>불평등과 경쟁이 일상화된 우리 사회에서 더불어 사는 삶을 지향하면서 나눔과 연대, 참여를 10여년 넘게 실천해온 희망씨의 족적은 의미가 남다릅니다.</p>
      <p>진정한 공동체는 서로 기대고 돌보면서 상생하는 관계가 바탕이 되어야 지속가능합니다. 특히 사회적 지원과 뒷받침이 필수인 아동청소년이 건강하게 성장할 수 있어야 합니다.</p>

      <p>희망씨는 그간 다양한 활동을 통해 노동자가 주체가 된 연대문화를 확산하고 모든 아동청소년이 존중받을 수 있도록 힘써왔고 상당한 성과를 거뒀습니다. 국내뿐 아니라 바다건너 네팔아동들이 건강하게 성장 할 수 있도록 지원해왔습니다.</p>

      <p>올해는 희망씨가 한 단계 더 도약하는 각별한 의미가 있는 해입니다. 대학로가 위치한 혜화동에 노동 중심 청소년 복합공간 ‘희망공간 아띠’를 마련하기로 했기 때문입니다. 희망씨가 사업을 더욱 활발하게 펼칠 수 있는 독자적인 공간을 갖는 것은 그 자체로 커다란 발전입니다. 이 숙원사업에 뜻을 같이 하는 분들이 십시일반으로 힘을 보태주셨습니다. 이렇게 수많은 분들의 땀과 수고를 밑거름으로 희망씨의 오늘이 있을 수 있었습니다.</p>

      <p>시작이 반이라고 했지요. 저희도 그간 애쓴 분들의 노고에 누가 되지 않도록, 희망씨의 창립 취지와 목적을 달성하는데 기여할 수 있도록 최선을 다하겠습니다.</p>

      <p class="mt-8 font-semibold text-forest-600">공동이사장 김진규, 이남신</p>
    </section>
  </article>
</main>
<?php 
// 푸터 포함
include __DIR__ . '/../includes/footer.php'; 
?>


