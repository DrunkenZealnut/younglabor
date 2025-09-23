<!-- 하단 시작 { -->
<div id="ft" role="contentinfo" class="bg-white border-t border-lime-200 mt-auto">
  <div id="ft_wr" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-gray-600">
    <div class="flex flex-col h-full">
      <!-- 메인 콘텐츠 영역 -->
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-4">
          <img
            src="<?php echo app_url('assets/images/logo.png'); ?>"
            alt="<?php echo htmlspecialchars(org_logo_alt('푸터 로고')); ?>"
            class="object-fit-contain"
            style="height: 2rem; width: auto; max-width: 10rem;"
            onerror="this.style.display='none';" />
        </div>
        <div id="ft_info" class="text-sm leading-6">
          <!-- 정보 그리드 - 4열 구조로 재정렬 -->
          <div class="flex flex-wrap gap-x-8 gap-y-6 justify-between items-start w-full">
            <!-- 첫 번째 열: 소재지와 이사장 -->
            <div class="space-y-4 min-w-0 flex-shrink-0">
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
            <div class="space-y-4 min-w-0 flex-shrink-0">
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
            </div>
            
            <!-- 세 번째 열: 후원 -->
            <div class="min-w-0 flex-shrink-0">
              <div class="flex items-start gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">후원계좌</span><br>
                  <span class="text-gray-600">우리은행 1005-502-430760<br><?= get_org_name(true) ?></span>
                </div>
              </div>
            </div>
            
            <!-- 네 번째 열: 문의하기 -->
            <div class="space-y-4 min-w-0 flex-shrink-0">
              <div class="flex items-start gap-2">
                <i data-lucide="message-circle" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">문의하기</span><br>
                  <span class="text-gray-600">
                    <a href="#" onclick="openInquiryModal()" class="text-forest-600 hover:text-lime-600 underline cursor-pointer">온라인 문의하기</a>
                  </span>
                </div>
              </div>
              
              <div class="flex items-start gap-2">
                <i data-lucide="external-link" class="w-4 h-4 text-lime-500 mt-0.5 flex-shrink-0"></i>
                <div>
                  <span class="font-medium text-forest-700">국민권익위원회</span><br>
                  <span class="text-gray-600">
                    <a href="https://www.acrc.go.kr/" target="_blank" rel="noopener noreferrer" class="text-forest-600 hover:text-lime-600 underline">www.acrc.go.kr</a>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- 저작권 - 맨 아래에 별도 배치 -->
      <div id="ft_copy" class="text-sm text-right mt-8 pt-4 border-t border-gray-200">
        <p>Copyright © 2019 더불어사는 삶 <?= get_org_name(true) ?>.</p>
      </div>
    </div>
  </div>
</div>
<!-- } 하단 끝 -->

