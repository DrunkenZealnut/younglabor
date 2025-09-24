<?php
/**
 * 일시후원 페이지
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

$pageTitle = '일시후원 | ' . app_name();
$currentSlug = 'donate/one-time';

// Legacy mode only - CSS vars mode removed
$useCSSVars = false;

// 일시후원 카테고리 설정 (hopec_donate 테이블 기반)
$bo_table = 'B22R'; // 호환성을 위한 변수 유지
$categories = ['개인후원', '단체후원', '기업후원']; // 일시후원 카테고리
$board = null; // hopec_donate 테이블을 직접 사용

// 카드 → 실제 카테고리 매핑 (DB 카테고리 명칭과 매칭)
$catMap = [
  '희망울타리후원'    => '희망울타리후원',
  '네팔아동학교보내기 후원' => '네팔아동학교보내기후원',
  '희망씨 운영비 후원'   => '희망씨후원',
  '희망씨 공간마련 후원'  => '희망씨아띠후원',
];

// 헤더 출력
include_once __DIR__ . '/../includes/header.php';
?>
<main id="main" role="main" class="flex-1">
  <article class="max-w-5xl mx-auto px-4 py-10">
    <header class="mb-8">
      <p class="text-sm text-gray-500">Donate</p>
      <?php if ($useCSSVars): ?>
        <h1 class="text-3xl md:text-4xl font-bold" style="<?= $styleManager->getStyleString(['color' => 'forest-600']) ?>">일시후원</h1>
      <?php else: ?>
        <h1 class="text-3xl md:text-4xl font-bold <?= getThemeClass('text', 'primary', '600') ?>">일시후원</h1>
      <?php endif; ?>
      <p class="text-gray-600 mt-2">원하시는 분야를 선택하여 일시후원에 참여해 주세요</p>
    </header>

    <section class="bg-white rounded-2xl border border-lime-200 shadow-sm p-6 md:p-8">
      <?php if (!empty($_SESSION['donate_flash'])) { $flash = $_SESSION['donate_flash']; unset($_SESSION['donate_flash']); ?>
        <div id="status_message" role="status" aria-live="polite" tabindex="-1" class="mb-4 p-4 rounded-lg border border-green-200 bg-green-50 text-green-800">
          <?= h($flash) ?>
        </div>
        <script>setTimeout(function(){var n=document.getElementById('status_message'); if(n){try{n.focus();}catch(e){}}},10);</script>
      <?php } ?>
      <style>
        .donate-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem}
        @media (max-width:768px){.donate-grid{grid-template-columns:1fr}}
        .donate-card{display:flex;flex-direction:column;gap:.5rem;border:2px solid #e5f0d8;background:#fafcf7;border-radius:1rem;padding:1rem;cursor:pointer;transition:all .2s ease}
        .donate-card:hover{border-color:#90c58b;background:#f2f9f0}
        .donate-card.selected{border-color:#2d7a4b;box-shadow:0 0 0 4px rgba(45,122,75,.12)}
        .donate-title{font-weight:700;color:#1d3b2a}
        .donate-desc{color:#475a4d;line-height:1.7}

        /* monthly.php와 동일한 폼 스타일 */
        .modern-form .form-group,
        .modern-form .radio-group,
        .modern-form .form-row,
        .modern-form .amount-category,
        .modern-form .amount-section{border-top:1px solid #dbe7c6;padding-top:.875rem;margin-top:.875rem}
        .modern-form .form-group:first-child,
        .modern-form .amount-section .form-group:first-child{border-top:0;padding-top:0;margin-top:0}
        .modern-form .form-input{border:2px solid #bfe6a6;box-shadow:0 0 0 0 rgba(0,0,0,0);min-height:46px;padding:12px 14px;transition:border-color .2s ease,box-shadow .2s ease}
        .modern-form .form-input:focus{outline:none;border-color:#66b445;box-shadow:0 0 0 4px rgba(102,180,69,.25)}
        .modern-form .form-input::placeholder{color:#98b292}
        .form-label{display:block;margin-bottom:.375rem;color:#1f2937;font-weight:600}
        .modern-form .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        @media (max-width:768px){.modern-form .form-row{grid-template-columns:1fr}}
        input[type=number].form-input{-moz-appearance:textfield}
        input[type=number].form-input::-webkit-outer-spin-button,
        input[type=number].form-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
      </style>

      <div class="donate-grid" id="donate-cards" aria-label="후원 분야 선택">
        <p class="text-forest-700" style="grid-column:1/-1; margin:0 0 8px 0; font-weight:600;">일시후원 종류를 선택해 주세요</p>
        <div class="donate-card" data-cat="<?= isset($catMap['희망울타리후원']) ? h($catMap['희망울타리후원']) : '' ?>">
          <div class="donate-title">희망울타리후원</div>
          <div class="donate-desc">국내의 아동청소년들이 차별받지 않고 건강하게 성장 할 수 있도록 주거환경, 교복, 생리대, 문화, 심리정서 등의 다양한 지원을 하고 있습니다.</div>
        </div>
        <div class="donate-card" data-cat="<?= isset($catMap['네팔아동학교보내기 후원']) ? h($catMap['네팔아동학교보내기 후원']) : '' ?>">
          <div class="donate-title">네팔아동학교보내기 후원</div>
          <div class="donate-desc">네팔의 아이들이 계급적 경제적으로 차별받지 않고 학교를 다닐 수 있도록 교육, 건강, 가정의 자립 등에 다양한 지원을 하고 있습니다.</div>
        </div>
        <div class="donate-card" data-cat="<?= isset($catMap['희망씨 운영비 후원']) ? h($catMap['희망씨 운영비 후원']) : '' ?>">
          <div class="donate-title">희망씨 운영비 후원</div>
          <div class="donate-desc">사단법인 희망씨가 아동청소년들의 성장과 더불어 사는 삶의 실천을 위한 제약없는 운영을 위해 후원해주세요.</div>
        </div>
        <div class="donate-card" data-cat="<?= isset($catMap['희망씨 공간마련 후원']) ? h($catMap['희망씨 공간마련 후원']) : '' ?>">
          <div class="donate-title">희망씨 공간마련 후원</div>
          <div class="donate-desc">아동청소년들이 자유롭게 꿈을 펼치고, 노동과 지역이 만나 연대하고, 더불어 사는 삶을 실천 할 수 있는 공간 마련을 위한 후원을 해주세요.</div>
        </div>
      </div>

      <hr class="my-8" />

      <?php
      // one-time 폼을 페이지 내에 직접 구성 (hopec_donate 테이블 직접 사용)
      // 게시판 설정 불필요, 직접 처리
      
      // monthly.php와 동일한 가드(허니팟/시간트랩/토큰) 세팅
      $_SESSION['donate_guard_time'] = time();
      $_SESSION['donate_guard_token'] = bin2hex(random_bytes(32));
      $default_ca = isset($_GET['sca']) && $_GET['sca'] ? h($_GET['sca']) : (isset($categories[0]) ? $categories[0] : '');
      $width = '100%'; // 폼 너비 설정
      ?>

      <form id="fwrite" name="fwrite" method="post" action="/donate/guard_onetime_submit.php" class="modern-form" autocomplete="off" style="width:<?= h($width) ?>">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="w" value="">
        <input type="hidden" name="wr_subject" value="onetime-<?php echo date('YmdHis'); ?>">
        <input type="hidden" name="html" value="html1">
        <input type="hidden" name="secret" value="secret">
        <input type="hidden" name="ca_name" id="ca_name" value="<?= h($default_ca) ?>">
        <input type="hidden" name="form_created_at" value="<?= (int)$_SESSION['donate_guard_time'] ?>">
        <input type="hidden" name="form_token" value="<?= h($_SESSION['donate_guard_token']) ?>">
        <input type="text" name="hp_contact" value="" aria-hidden="true" tabindex="-1" style="position:absolute;left:-9999px;top:-9999px;height:1px;width:1px;opacity:0;">

        <div class="form-row form-group">
          <div class="form-group">
            <label for="wr_name" class="form-label">이름 *</label>
            <input type="text" id="wr_name" name="wr_name" class="form-input" required>
          </div>
          <div class="form-group">
            <label for="wr_2" class="form-label">은행명 *</label>
            <input type="text" id="wr_2" name="wr_2" class="form-input" required>
          </div>
        </div>

        <div class="form-row form-group">
          <div class="form-group">
            <label for="wr_3" class="form-label">계좌번호 *</label>
            <input type="text" id="wr_3" name="wr_3" class="form-input" placeholder="- 없이 입력" required>
          </div>
          <div class="form-group">
            <label class="form-label">예금주 주민번호</label>
            <div class="jumin-group">
              <input type="number" name="jumin1" id="jumin1" class="form-input jumin-input" maxlength="6" placeholder="앞 6자리" required>
              <span class="jumin-separator">-</span>
              <input type="number" name="jumin2" id="jumin2" class="form-input jumin-input" maxlength="7" placeholder="뒤 7자리">
            </div>
          </div>
        </div>

        <div class="form-row form-group">
          <div class="form-group">
            <label for="wr_6" class="form-label">이체금액(원) *</label>
            <input type="number" id="wr_6" name="wr_6" class="form-input" required>
          </div>
          <div class="form-group">
            <label for="wr_9" class="form-label">휴대폰번호 *</label>
            <input type="tel" id="wr_9" name="wr_9" class="form-input" placeholder="010-0000-0000" required>
          </div>
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
                <input type="text" name="wr_zip" id="wr_zip" value="" class="form-input" maxlength="5" required style="max-width:160px;">
                <button type="button" class="btn btn-secondary" style="min-width:140px;" onclick="win_zip('fwrite','wr_zip','wr_addr1','wr_addr2','wr_addr3','wr_addr_jibeon');">주소 검색</button>
              </div>
            </div>
            <div class="form-group">
              <label for="wr_addr1" class="form-label">기본주소</label>
              <input type="text" name="wr_addr1" id="wr_addr1" value="" class="form-input" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group" style="grid-column: 1 / -1;">
              <label for="wr_addr2" class="form-label">상세주소</label>
              <input type="text" name="wr_addr2" id="wr_addr2" value="" class="form-input" required>
              <input type="hidden" name="wr_addr3" id="wr_addr3" value="">
              <input type="hidden" name="wr_addr_jibeon" id="wr_addr_jibeon" value="">
            </div>
          </div>
        </div>

        <div class="form-group" style="text-align:left;">
          <h3 class="section-title" style="margin-bottom:8px; font-size:var(--text-lg);">※개인정보동의서</h3>
          <p class="form-help" style="margin-bottom:8px;">개인정보는 CMS 가입외의 용도로는 사용하지 않으며, 본인동의 없이는 제3자에게 제공하지 않습니다</p>
          <label style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="agree" id="agree" value="agree">
            <span>동의합니다</span>
          </label>
        </div>

        <div class="submit-buttons" style="display:flex; gap:12px; justify-content:center;">
          <button type="submit" class="btn btn-primary btn-lg submit-btn" style="border:2px solid #a3e635; border-radius:.5rem;">일시후원 신청하기</button>
        </div>
      </form>

      <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
      <script>
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
      // 휴대폰 입력 포맷팅
      (function(){
        var phoneInput = document.getElementById('wr_9');
        if (phoneInput) {
          phoneInput.addEventListener('input', function(e){
            let v = e.target.value.replace(/\D/g, '');
            if (v.length >= 11) v = v.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
            else if (v.length >= 7) v = v.replace(/(\d{3})(\d{4})/, '$1-$2');
            else if (v.length >= 3) v = v.replace(/(\d{3})/, '$1-');
            e.target.value = v;
          });
        }
        var form = document.getElementById('fwrite');
        form.addEventListener('submit', function(e){
          var agree = document.getElementById('agree');
          if (!agree || !agree.checked){
            alert('개인정보처리방침에 동의해 주세요.');
            agree && agree.focus();
            e.preventDefault();
          }
        });
      })();
      </script>

      <script>
      // 카드 선택 → 카테고리 셀렉트 반영 + 폼으로 스크롤
      document.addEventListener('DOMContentLoaded', function(){
        var grid = document.getElementById('donate-cards');
        if(!grid) return;
        grid.addEventListener('click', function(e){
          var card = e.target.closest('.donate-card');
          if(!card) return;
          var cat = card.getAttribute('data-cat') || '';
          document.querySelectorAll('.donate-card').forEach(function(c){ c.classList.remove('selected'); });
          card.classList.add('selected');
          var sel = document.getElementById('ca_name');
          if(sel){
            // 존재하는 옵션 중 매칭되면 선택
            var found = false;
            for (var i=0;i<sel.options.length;i++){
              if(sel.options[i].value === cat){ sel.selectedIndex = i; found = true; break; }
            }
            if(!found && sel.options.length>0){ sel.selectedIndex = 0; }
          }
          var form = document.getElementById('fwrite');
          if(form){ form.scrollIntoView({behavior:'smooth', block:'start'}); }
        });
      });
      </script>
    </section>
  </article>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>


