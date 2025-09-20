<?php
// 공통 초기화 (절대경로 사용)
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '조직도 | ' . app_name();
$co_id = 'org';

// CSS Variables 모드 지원 추가 (Legacy 모드 보존)
require_once __DIR__ . '/../includes/CSSVariableThemeManager.php';
$useCSSVars = detectCSSVarsMode();

if ($useCSSVars && !isset($styleManager)) {
    $styleManager = getCSSVariableManager();
}

include __DIR__ . '/../includes/header.php';
?>
<main id="container" role="main" class="<?= getThemeClass('bg', 'gray', '50') ?> min-h-screen">
  <article class="max-w-7xl mx-auto px-4 py-10">
    <header class="mb-8">
      <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>">About</p>
      <?php if ($useCSSVars): ?>
        <h1 class="text-3xl md:text-4xl font-bold" style="<?= $styleManager->getStyleString(['color' => 'forest-600']) ?>">조직도</h1>
      <?php else: ?>
        <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>">조직도</h1>
      <?php endif; ?>
    </header>

    <style>
      .role-badge { position: absolute; top: -10px; left: 50%; transform: translateX(-50%); padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
      .org-box { padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-align: center; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transition: all 0.3s ease; }
      .org-box:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    </style>

    <section class="mb-20" aria-labelledby="org-structure">
      <div class="w-full max-w-4xl mx-auto grid grid-cols-[1fr_auto_1fr] gap-y-4 items-center justify-items-center">
        <!-- 총회 (중앙 열) -->
        <div></div>
        <div class="org-box <?= getThemeClass('bg', 'primary', '600') ?> <?= getThemeClass('text', 'white') ?> w-56">총회</div>
        <div></div>

        <!-- 세로선 -->
        <div></div>
        <div class="w-0.5 h-8 bg-gray-300" aria-hidden="true"></div>
        <div></div>

        <!-- 이사회 (중앙) + 감사(우측) -->
        <div></div>
        <div class="org-box <?= getThemeClass('bg', 'primary', '100') ?> <?= getThemeClass('text', 'primary', '800') ?> w-56">이사회</div>
        <div class="justify-self-start flex items-center gap-4">
          <span class="h-0.5 w-12 bg-gray-300" aria-hidden="true"></span>
          <div class="org-box <?= getThemeClass('bg', 'secondary', '500') ?> <?= getThemeClass('text', 'white') ?> w-56">감사</div>
        </div>

        <!-- 세로선 -->
        <div></div>
        <div class="w-0.5 h-8 bg-gray-300" aria-hidden="true"></div>
        <div></div>

        <!-- 이사장 (중앙) + 자문단(우측) -->
        <div></div>
        <div class="org-box <?= getThemeClass('bg', 'primary', '200') ?> <?= getThemeClass('text', 'primary', '800') ?> w-56">이사장</div>
        <div class="justify-self-start flex items-center gap-4">
          <span class="h-0.5 w-12 bg-gray-300" aria-hidden="true"></span>
          <div class="org-box <?= getThemeClass('bg', 'secondary', '500') ?> <?= getThemeClass('text', 'white') ?> w-56">자문단</div>
        </div>

        <!-- 세로선 -->
        <div></div>
        <div class="w-0.5 h-8 bg-gray-300" aria-hidden="true"></div>
        <div></div>

        <!-- 상임이사 (중앙) -->
        <div></div>
        <div class="org-box <?= getThemeClass('bg', 'primary', '300') ?> <?= getThemeClass('text', 'primary', '800') ?> w-56">상임이사</div>
        <div></div>

        <!-- 하단으로 내려가는 세로선 -->
        <div></div>
        <div class="w-0.5 h-10 bg-gray-300" aria-hidden="true"></div>
        <div></div>
      </div>

      <!-- 하단 베이스라인 및 사업국 -->
      <div class="relative w-full pt-6">
        <div class="w-full h-0.5 bg-gray-300 mb-8" aria-hidden="true"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
          <div class="relative flex justify-center">
            <span class="absolute -top-8 w-0.5 h-8 bg-gray-300" aria-hidden="true"></span>
            <div class="org-box <?= getThemeClass('bg', 'secondary', '500') ?> <?= getThemeClass('text', 'white') ?> w-full text-center">사무국</div>
          </div>
          <div class="relative flex justify-center">
            <span class="absolute -top-8 w-0.5 h-8 bg-gray-300" aria-hidden="true"></span>
            <div class="org-box <?= getThemeClass('bg', 'secondary', '500') ?> <?= getThemeClass('text', 'white') ?> w-full text-center">아동복지사업국</div>
          </div>
          <div class="relative flex justify-center">
            <span class="absolute -top-8 w-0.5 h-8 bg-gray-300" aria-hidden="true"></span>
            <div class="org-box <?= getThemeClass('bg', 'secondary', '500') ?> <?= getThemeClass('text', 'white') ?> w-full text-center">청소년사업국</div>
          </div>
          <div class="relative flex justify-center">
            <span class="absolute -top-8 w-0.5 h-8 bg-gray-300" aria-hidden="true"></span>
            <div class="org-box <?= getThemeClass('bg', 'secondary', '500') ?> <?= getThemeClass('text', 'white') ?> w-full text-center">노동인권사업국</div>
          </div>
          <div class="relative flex justify-center">
            <span class="absolute -top-8 w-0.5 h-8 bg-gray-300" aria-hidden="true"></span>
            <div class="org-box <?= getThemeClass('bg', 'secondary', '500') ?> <?= getThemeClass('text', 'white') ?> w-full text-center">네팔위기아동지원사업국</div>
          </div>
        </div>
      </div>
    </section>

    <!-- 공동이사장 섹션 -->
    <section class="mb-16" aria-labelledby="co-chairs">
      <h2 id="co-chairs" class="text-3xl font-bold <?= getThemeClass('text', 'foreground') ?> border-b-4 <?= getThemeClass('border', 'primary', '500') ?> pb-2 mb-8 inline-block">공동이사장</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- 이남신 -->
        <div class="<?= getThemeClass('bg', 'white') ?> rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="relative w-24 h-24 <?= getThemeClass('bg', 'primary', '100') ?> rounded-full mx-auto mb-4 flex items-center justify-center">
            <i class="fas fa-user-tie text-4xl <?= getThemeClass('text', 'primary', '500') ?>" aria-hidden="true"></i>
          </div>
          <h3 class="text-2xl font-bold <?= getThemeClass('text', 'foreground') ?>">이남신</h3>
          <p class="<?= getThemeClass('text', 'primary', '600') ?> font-semibold mt-1">Co-Chairperson</p>
          <div class="mt-4 text-left <?= getThemeClass('text', 'muted-foreground') ?> space-y-2">
            <p><i class="fas fa-check-circle <?= getThemeClass('text', 'primary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 한국비정규노동센터 공동대표</p>
            <p><i class="fas fa-history <?= getThemeClass('text', 'primary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 서울노동권익센터 소장</p>
          </div>
        </div>
        <!-- 김진규 -->
        <div class="<?= getThemeClass('bg', 'white') ?> rounded-xl shadow-lg p-6 text-center transform hover:scale-105 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="relative w-24 h-24 <?= getThemeClass('bg', 'primary', '100') ?> rounded-full mx-auto mb-4 flex items-center justify-center">
            <i class="fas fa-user-tie text-4xl <?= getThemeClass('text', 'primary', '500') ?>" aria-hidden="true"></i>
          </div>
          <h3 class="text-2xl font-bold <?= getThemeClass('text', 'foreground') ?>">김진규</h3>
          <p class="<?= getThemeClass('text', 'primary', '600') ?> font-semibold mt-1">Co-Chairperson</p>
          <div class="mt-4 text-left <?= getThemeClass('text', 'muted-foreground') ?> space-y-2">
            <p><i class="fas fa-check-circle <?= getThemeClass('text', 'primary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 희망연대노조 공동위원장</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 이사 섹션 -->
    <section class="mb-16" aria-labelledby="directors">
      <h2 id="directors" class="text-3xl font-bold <?= getThemeClass('text', 'foreground') ?> border-b-4 <?= getThemeClass('border', 'secondary', '500') ?> pb-2 mb-8 inline-block">이사</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <div class="bg-org-chairman rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">상임이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">김은선</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> (사)희망씨 나눔연대국장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">공군자</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 세계노동운동사연구회 이사</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 서울노동광장 공동대표</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">김진억</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 민주노총 서울본부 본부장</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 희망연대노조 공동위원장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">김창수</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 우리동네노동권찾기 대표</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">김태진</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 경기북부노동공제회 이사</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 희망연대노조 공동위원장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">박재범</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 딜라이브지부 사무국장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">여민희</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 전국학습지노조 사무처장</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 재능교육지부 지부장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">윤진영</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 희망연대본부 전략조직실장</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 희망연대노조 공동위원장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">이선옥</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 사과농부</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 시민모임 즐거운교육상상 운영위원</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">이종삼</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 희망연대본부 대전지역사회연대위원회 부위원장</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 한마음지부 지부장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">최성근</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> SK브로드밴드비정규직지부 수석부지부장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">하상수</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 경기중부비정규직센터 대표</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 기아자동차노조 위원장</p>
        </div>
        <div class="bg-org-director rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">이사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">현정희</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'secondary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 평등사회교육원 이사</p>
          <p class="text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-landmark <?= getThemeClass('text', 'secondary', '400') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(전)</span> 공공운수노조 위원장</p>
        </div>
      </div>
    </section>

    <!-- 감사 섹션 -->
    <section class="mb-8" aria-labelledby="auditors">
      <h2 id="auditors" class="text-3xl font-bold <?= getThemeClass('text', 'foreground') ?> border-b-4 <?= getThemeClass('border', 'primary', '500') ?> pb-2 mb-8 inline-block">감사</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-org-auditor rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">감사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">박미경</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'primary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 전태일재단 기획실장</p>
        </div>
        <div class="bg-org-auditor rounded-xl shadow-md p-6 relative pt-10 transform hover:-translate-y-1 transition-transform duration-300 border <?= getThemeClass('border', 'gray', '200') ?>">
          <div class="role-badge role-badge-primary">감사</div>
          <h3 class="text-xl font-bold <?= getThemeClass('text', 'foreground') ?>">최진수</h3>
          <p class="mt-3 text-sm <?= getThemeClass('text', 'muted-foreground') ?>"><i class="fas fa-building <?= getThemeClass('text', 'primary', '500') ?> mr-2" aria-hidden="true"></i><span class="font-semibold">(현)</span> 민주노총 서울본부 노동법률지원센터 법규국장</p>
        </div>
      </div>
    </section>
  </article>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>


