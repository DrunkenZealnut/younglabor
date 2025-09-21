<!-- 하단 시작 { -->
<div id="ft" role="contentinfo" class="bg-white border-t border-lime-200 mt-auto">
  <div id="ft_wr" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-gray-600">
    <div class="flex flex-col md:flex-row items-start md:items-start justify-between gap-8">
      <div class="flex-1 min-w-[320px]">
        <div class="flex items-center gap-3 mb-4">
          <?php if (function_exists('logo_url')): ?>
            <img src="<?= logo_url() ?>" alt="희망씨 로고" class="h-8" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
            <i data-lucide="trees" class="w-6 h-6 text-lime-500" style="display: none;" aria-hidden="true"></i>
          <?php else: ?>
            <i data-lucide="trees" class="w-6 h-6 text-lime-500" aria-hidden="true"></i>
          <?php endif; ?>
        </div>
        <div id="ft_info" class="text-sm leading-6">
          <!-- 정보 그리드 - 3열 구조로 재정렬 -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-4">
            <!-- 첫 번째 열: 소재지와 이사장 -->
            <div class="space-y-4">
              <div class="flex items-start gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">소재지</span><br>
                  <span class="text-gray-600">서울특별시 종로구 성균관로12 5층</span>
                </div>
              </div>
              
              <div class="flex items-start gap-2">
                <i data-lucide="users" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">이사장</span><br>
                  <span class="text-gray-600">김진규, 이남신</span>
                </div>
              </div>
            </div>
            
            <!-- 두 번째 열: 연락처 -->
            <div class="space-y-4">
              <div class="flex items-start gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">전화</span><br>
                  <span class="text-gray-600">02-2236-1105</span>
                </div>
              </div>
              
              <div class="flex items-start gap-2">
                <i data-lucide="printer" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">팩스</span><br>
                  <span class="text-gray-600">02-464-1105</span>
                </div>
              </div>
              
              <div class="flex items-start gap-2">
                <i data-lucide="mail" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">이메일</span><br>
                  <span class="text-gray-600">
                    <a href="mailto:hopec09131105@hopec.co.kr" class="text-forest-600 hover:text-lime-600 underline">hopec09131105@hopec.co.kr</a>
                  </span>
                </div>
              </div>
              
              <div class="flex items-start gap-2">
                <i data-lucide="message-circle" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">문의하기</span><br>
                  <span class="text-gray-600">
                    <a href="#" onclick="openInquiryModal()" class="text-forest-600 hover:text-lime-600 underline cursor-pointer">온라인 문의하기</a>
                  </span>
                </div>
              </div>
            </div>
            
            <!-- 세 번째 열: 후원 -->
            <div>
              <div class="flex items-start gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">후원계좌</span><br>
                  <span class="text-gray-600">우리은행 1005-502-430760<br>(사)희망씨</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="ft_copy" class="text-sm text-right md:text-left">
        <p>Copyright © 2019 더불어사는 삶 사단법인 희망씨.</p>
      </div>
    </div>
  </div>
</div>
<!-- } 하단 끝 -->

