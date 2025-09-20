<?php
/**
 * 정기후원 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '정기후원 | ' . app_name();
$currentSlug = 'donate/monthly';

// 설명 블록 (정기후원 신청은 기존 시스템의 작성 폼을 사용하도록 유도)
$monthlyIntro = '매달 꾸준한 나눔으로 아이들의 오늘과 내일을 함께 지켜주세요.';

// 매크로 방지(허니팟/시간트랩/토큰) 세션 초기화
$_SESSION['donate_guard_time'] = time();
$_SESSION['donate_guard_token'] = bin2hex(random_bytes(32));

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';
?>
<main id="main" role="main" class="flex-1">
  <article class="max-w-3xl mx-auto px-4 py-10">
    <header class="mb-6">
      <h1 class="text-3xl md:text-4xl font-bold text-forest-700">정기후원</h1>
      <p class="text-gray-600 mt-2"><?= h($monthlyIntro) ?></p>
    </header>

    <section class="bg-white rounded-2xl border border-lime-200 shadow-sm p-6 md:p-8">
      <style>
        /* 입력 영역 구분선(가독성 향상) */
        .modern-form .form-group,
        .modern-form .radio-group,
        .modern-form .form-row,
        .modern-form .amount-category,
        .modern-form .amount-section { border-top: 1px solid #dbe7c6; padding-top: 0.875rem; margin-top: 0.875rem; }
        .modern-form .form-group:first-child,
        .modern-form .amount-section .form-group:first-child { border-top: 0; padding-top: 0; margin-top: 0; }
        /* 기본 인풋: 높이 확대 + 연두색 강조 */
        .modern-form .form-input {
          border: 2px solid #bfe6a6; /* 연한 연두색 테두리 */
          box-shadow: 0 0 0 0 rgba(0,0,0,0);
          min-height: 46px; /* 터치/가독성 향상 */
          padding: 12px 14px;
          transition: border-color .2s ease, box-shadow .2s ease;
        }
        .modern-form .form-input:focus {
          outline: none; /* 기본 파란 포커스 링 제거 */
          border-color: #66b445; /* 진한 연두색 */
          box-shadow: 0 0 0 4px rgba(102, 180, 69, 0.25); /* 연두색 글로우 */
        }
        .modern-form .form-input::placeholder { color: #98b292; }

        /* 스크린리더 전용 텍스트 */
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }

        /* 라디오 카드 UI */
        .radio-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; }
        .radio-card { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #f9faf5; border: 2px solid #e5f0d8; border-radius: 0.75rem; cursor: pointer; transition: all .2s ease; }
        .radio-card:hover { border-color: #90c58b; background: #f2f9f0; }
        .radio-card input[type="radio"] { display: none; }
        .radio-custom { width: 18px; height: 18px; border: 2px solid #a3c795; border-radius: 9999px; position: relative; flex: 0 0 auto; }
        .radio-card input[type="radio"]:checked + .radio-custom { border-color: #2d7a4b; background: #2d7a4b; }
        .radio-card input[type="radio"]:checked + .radio-custom::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 8px; height: 8px; background: #fff; border-radius: 9999px; }
        .radio-text { color: #1f2937; font-weight: 600; }

        /* 금액 카드 */
        .amount-section { display: grid; gap: 1rem; }
        .amount-category { background: #fafcf7; border: 1px solid #e5f0d8; border-radius: 0.75rem; padding: 1rem; }
        .amount-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
        .amount-card { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 2px solid #e5f0d8; border-radius: 0.75rem; background: #fff; cursor: pointer; transition: all .2s ease; }
        .amount-card:hover { border-color: #90c58b; transform: translateY(-2px); }
        .amount-card input[type="radio"] { display: none; }
        .amount-card:has(input:checked) { border-color: #2d7a4b; background: #f0fbf5; }
        .custom-input-wrapper.hidden { display: none !important; }
        .custom-amount-input { width: 120px; }
        .amount-unit { font-size: 0.875rem; color: #6b7280; }

        /* 이체일 필 라디오 */
        .radio-pill { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem .875rem; background: #f3f7ef; border: 2px solid #e5f0d8; border-radius: 9999px; cursor: pointer; transition: all .2s ease; }
        .radio-pill input[type="radio"] { display: none; }
        .radio-pill:hover { border-color: #90c58b; }
        .radio-pill:has(input:checked) { border-color: #2d7a4b; background: #2d7a4b; color: #fff; }
        .pill-text { color: inherit; }

        /* 숫자 입력 스핀 버튼 제거 */
        input[type=number].form-input { -moz-appearance: textfield; }
        input[type=number].form-input::-webkit-outer-spin-button,
        input[type=number].form-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        /* 반응형 */
        @media (max-width: 768px) { .amount-options { grid-template-columns: 1fr; } .radio-group { grid-template-columns: 1fr; } }
      </style>
      <p class="text-gray-800 leading-7 mb-6">
        정기후원은 희망씨의 국내·해외 아동청소년 지원사업, 노동인권사업, 소통 및 회원사업을 꾸준히 이어갈 수 있도록 힘이 됩니다.
      </p>

      <?php
      // 1) 후원 카테고리 설정 (hopec_donate 테이블 기반)
      $categories = ['정회원', '준회원', '후원회원'];
      $bo_table = 'B21'; // 호환성을 위한 변수 유지

      // 2) 플래시 메시지 출력 (PRG 패턴)
      if (!empty($_SESSION['donate_flash'])) {
          $flash = $_SESSION['donate_flash'];
          unset($_SESSION['donate_flash']);
          echo '<div id="status_message" role="status" aria-live="polite" tabindex="-1" class="mb-4 p-4 rounded-lg border border-green-200 bg-green-50 text-green-800">'
             . h($flash)
             . '</div>';
          echo '<script>setTimeout(function(){var n=document.getElementById("status_message"); if(n){try{n.focus();}catch(e){}}},10);</script>';
      }
      ?>

      <form id="fwrite" name="fwrite" method="post" action="/donate/guard_submit.php" class="modern-form" autocomplete="off" novalidate>
        <input type="hidden" name="bo_table" value="<?= h($bo_table) ?>">
        <input type="hidden" name="form_created_at" value="<?= (int)$_SESSION['donate_guard_time'] ?>">
        <input type="hidden" name="form_token" value="<?= h($_SESSION['donate_guard_token']) ?>">
        <input type="text" name="hp_contact" value="" aria-hidden="true" tabindex="-1" style="position:absolute;left:-9999px;top:-9999px;height:1px;width:1px;opacity:0;">

        <fieldset>
          <legend class="sr-only">회원 정보</legend>
          <div class="form-group">
            <label class="form-label">회원구분 *</label>
            <div class="radio-group">
              <?php if (!empty($categories)) { foreach ($categories as $cat) { ?>
                <label class="radio-card">
                  <input type="radio" name="ca_name" value="<?= h($cat) ?>" required>
                  <span class="radio-custom"></span>
                  <span class="radio-text"><?= h($cat) ?></span>
                </label>
              <?php } } ?>
            </div>
            <p class="form-help">정회원은 총회 참석 및 선거권/피선거권이 주어집니다.</p>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="wr_name" class="form-label">이름 *</label>
              <input type="text" id="wr_name" name="wr_name" class="form-input" required>
            </div>
            <div class="form-group">
              <label for="wr_1" class="form-label">소속</label>
              <input type="text" id="wr_1" name="wr_1" class="form-input">
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend class="sr-only">계좌 정보</legend>
          <div class="form-row">
            <div class="form-group">
              <label for="wr_2" class="form-label">은행명 *</label>
              <input type="text" id="wr_2" name="wr_2" class="form-input" required>
            </div>
            <div class="form-group">
              <label for="wr_3" class="form-label">계좌번호 *</label>
              <input type="text" id="wr_3" name="wr_3" class="form-input" placeholder="- 없이 입력" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">예금주 주민번호</label>
            <div class="jumin-group">
              <input type="number" name="jumin1" id="jumin1" class="form-input jumin-input" maxlength="6" placeholder="앞 6자리" required>
              <span class="jumin-separator">-</span>
              <input type="number" name="jumin2" id="jumin2" class="form-input jumin-input" maxlength="7" placeholder="뒤 7자리">
            </div>
            <p class="form-help">소득공제를 원하시면 주민번호를 전부 입력해주세요.</p>
          </div>
        </fieldset>

        <fieldset>
          <legend class="sr-only">후원 금액</legend>
          <div class="amount-section">
            <div class="amount-category">
              <h3 class="category-title">개인</h3>
              <div class="amount-options">
                <label class="amount-card">
                  <input type="radio" name="wr_5" value="1만원" required>
                  <span class="amount-text">1만원</span>
                </label>
                <label class="amount-card">
                  <input type="radio" name="wr_5" value="2만원" required>
                  <span class="amount-text">2만원</span>
                </label>
                <label class="amount-card custom-amount">
                  <input type="radio" name="wr_5" value="기타1" required>
                  <span class="amount-text">기타</span>
                  <div class="custom-input-wrapper hidden">
                    <input type="number" name="wr_6" class="form-input custom-amount-input" placeholder="금액 입력" disabled>
                    <span class="amount-unit">원</span>
                  </div>
                </label>
              </div>
            </div>

            <div class="amount-category">
              <h3 class="category-title">단체</h3>
              <div class="amount-options">
                <label class="amount-card">
                  <input type="radio" name="wr_5" value="5만원" required>
                  <span class="amount-text">5만원</span>
                </label>
                <label class="amount-card">
                  <input type="radio" name="wr_5" value="10만원" required>
                  <span class="amount-text">10만원</span>
                </label>
                <label class="amount-card custom-amount">
                  <input type="radio" name="wr_5" value="기타2" required>
                  <span class="amount-text">기타</span>
                  <div class="custom-input-wrapper hidden">
                    <input type="number" name="wr_6" class="form-input custom-amount-input" placeholder="금액 입력" disabled>
                    <span class="amount-unit">원</span>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">이체일 *</label>
            <div class="radio-group">
              <label class="radio-pill">
                <input type="radio" name="wr_7" value="10일" required>
                <span class="pill-text">매월 10일</span>
              </label>
              <label class="radio-pill">
                <input type="radio" name="wr_7" value="25일" required>
                <span class="pill-text">매월 25일</span>
              </label>
              <label class="radio-pill">
                <input type="radio" name="wr_7" value="27일" required>
                <span class="pill-text">매월 27일</span>
              </label>
            </div>
          </div>
        </fieldset>

        <?php
        // 주소 초기값
        $wr_zip = $wr_addr1 = $wr_addr2 = $wr_addr3 = $wr_addr_jibeon = '';
        ?>
        <fieldset>
          <legend class="sr-only">연락처 및 주소</legend>
          <div class="form-group">
            <label for="wr_9" class="form-label">휴대폰번호 *</label>
            <input type="tel" id="wr_9" name="wr_9" class="form-input" placeholder="010-0000-0000" required>
          </div>
          <div class="form-group">
            <label for="wr_email" class="form-label">이메일</label>
            <input type="email" id="wr_email" name="wr_email" class="form-input" placeholder="example@email.com">
          </div>

          <div class="form-group">
            <label class="form-label">주소 *</label>
            <div class="form-row">
              <div class="form-group">
                <label for="wr_zip" class="form-label">우편번호</label>
                <div style="display:flex; gap:8px; align-items:center;">
                  <input type="text" name="wr_zip" id="wr_zip" value="<?= h($wr_zip) ?>" class="form-input" maxlength="5" required style="max-width:160px;">
                  <button type="button" class="btn btn-secondary" style="min-width:140px;" onclick="win_zip('fwrite','wr_zip','wr_addr1','wr_addr2','wr_addr3','wr_addr_jibeon');">주소 검색</button>
                </div>
              </div>
              <div class="form-group">
                <label for="wr_addr1" class="form-label">기본주소</label>
                <input type="text" name="wr_addr1" id="wr_addr1" value="<?= h($wr_addr1) ?>" class="form-input" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group" style="grid-column: 1 / -1;">
                <label for="wr_addr2" class="form-label">상세주소</label>
                <input type="text" name="wr_addr2" id="wr_addr2" value="<?= h($wr_addr2) ?>" class="form-input" required>
                <input type="hidden" name="wr_addr3" id="wr_addr3" value="<?= h($wr_addr3) ?>">
                <input type="hidden" name="wr_addr_jibeon" id="wr_addr_jibeon" value="<?= h($wr_addr_jibeon) ?>">
              </div>
            </div>
          </div>
        </fieldset>

        <div class="form-group" style="text-align:left;">
          <h3 class="section-title" style="margin-bottom:8px; font-size:var(--text-lg);">개인정보 수집·이용 동의</h3>
          <p class="form-help" style="margin-bottom:8px;">정기후원 신청 처리를 위해 개인정보를 수집·이용하며, 동의 없이 제3자에게 제공하지 않습니다.</p>
          <label style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="agree" id="agree" value="agree">
            <span>동의합니다</span>
          </label>
        </div>

        <div class="submit-buttons" style="display:flex; gap:12px; justify-content:center;">
          <button type="submit" class="btn btn-primary btn-lg submit-btn" style="border:2px solid #a3e635; border-radius:.5rem;">
            정기후원 신청하기
          </button>
        </div>
      </form>

      <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
      <script>
      // 주소 검색 헬퍼
      function win_zip(formId, zip, addr1, addr2, addr3, jibeon){
        new daum.Postcode({
          oncomplete: function(data){
            var f = document.getElementById(formId) || document.forms[formId];
            if(!f) return;
            if(f[zip]) f[zip].value = (data.zonecode||"");
            if(f[addr1]) f[addr1].value = (data.address||"");
            if(f[addr2]) f[addr2].focus();
            if(f[jibeon]) f[jibeon].value = (data.buildingName||"");
          }
        }).open();
      }

      // 기타 금액 입력 토글
      document.addEventListener('DOMContentLoaded', function(){
        const amountRadios = document.querySelectorAll('input[name="wr_5"]');
        const customInputs = document.querySelectorAll('.custom-input-wrapper');
        amountRadios.forEach(function(radio){
          radio.addEventListener('change', function(){
            customInputs.forEach(function(el){ el.classList.add('hidden'); el.querySelector('input') && (el.querySelector('input').disabled = true, el.querySelector('input').required = false); });
            if(this.value === '기타1' || this.value === '기타2'){
              const wrapper = this.closest('.amount-card').querySelector('.custom-input-wrapper');
              if(wrapper){
                wrapper.classList.remove('hidden');
                const input = wrapper.querySelector('input');
                input.disabled = false; input.required = true;
              }
            }
          });
        });

        // 휴대폰 입력 포맷팅
        const phoneInput = document.getElementById('wr_9');
        if (phoneInput) {
          phoneInput.addEventListener('input', function(e){
            let v = e.target.value.replace(/\D/g, '');
            if (v.length >= 11) v = v.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
            else if (v.length >= 7) v = v.replace(/(\d{3})(\d{4})/, '$1-$2');
            else if (v.length >= 3) v = v.replace(/(\d{3})/, '$1-');
            e.target.value = v;
          });
        }

        // 폼 검증 (개인정보 동의)
        const form = document.getElementById('fwrite');
        form.addEventListener('submit', function(e){
          const agree = document.getElementById('agree');
          if (!agree || !agree.checked){
            alert('개인정보처리방침에 동의해 주세요.');
            agree && agree.focus();
            e.preventDefault();
          }
        });
      });
      </script>
    </section>
  </article>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>