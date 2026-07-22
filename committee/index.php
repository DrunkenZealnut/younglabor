<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/PageTracker.php';
PageTracker::track('청소년노동안전동아리 신청');

$currentPage = 'committee';
$pageTitle = '청소년노동안전동아리 신청 - ' . $site['name'];
$pageDescription = '청소년노동안전동아리 신청 - ' . $site['name'];
require_once __DIR__ . '/../includes/header.php';
?>
    <style>
        /* 동아리 신청 페이지 전용 (공통 토큰 상속) */
        .page-content {
            max-width: 720px;
            margin: 0 auto;
            padding: clamp(3rem, 6vw, 5rem) var(--space-gutter) 4rem;
        }

        .page-hero {
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: var(--fs-display);
            font-weight: 600;
            letter-spacing: -0.03em;
            line-height: 1.08;
            color: var(--color-ink-pure);
            margin: 2rem 0 1.25rem;
        }

        .page-desc {
            font-size: var(--fs-caption);
            color: var(--color-gray);
            line-height: 1.8;
        }

        /* Info Box — 헤어라인 + 라임 태그 */
        .info-box {
            border-top: var(--hairline);
            padding: 1.5rem 0 0;
            margin-bottom: 2.5rem;
        }

        .info-box h3 {
            display: inline-block;
            background: var(--color-lime);
            color: var(--color-ink-pure);
            font-size: 0.9375rem;
            font-weight: 500;
            padding: 0.2rem 0.6rem;
            margin-bottom: 1rem;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box ul li {
            padding: 0.3rem 0 0.3rem 1.25rem;
            position: relative;
            color: var(--color-gray);
            font-size: var(--fs-caption);
        }

        .info-box ul li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--color-ink);
        }

        /* 후원 안내 */
        .sponsor-box {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 2.5rem;
            padding: 1rem 0;
            border-top: var(--hairline);
            border-bottom: var(--hairline);
        }

        .sponsor-box span {
            font-size: var(--fs-caption);
            color: var(--color-gray);
        }

        .sponsor-box img {
            height: 28px;
            width: auto;
        }

        /* Form — 공통 언더라인 시스템 상속, 페이지 고유분만 정의 */
        .form-group label .required {
            color: var(--color-purple);
            margin-left: 2px;
        }

        .form-group label .optional {
            color: var(--color-gray);
            font-weight: 400;
            font-size: 0.85rem;
            margin-left: 4px;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23111' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0 center;
        }

        .form-group textarea {
            min-height: 150px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .btn-submit {
            margin-top: 0.5rem;
        }

        /* Success Message */
        .success-message {
            display: none;
            padding: 3rem 0;
        }

        .success-message.show {
            display: block;
        }

        .success-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        .success-message h2 {
            font-size: var(--fs-h2);
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--color-ink-pure);
            margin-bottom: 1rem;
        }

        .success-message p {
            color: var(--color-gray);
            font-size: 1.05rem;
            margin-bottom: 2rem;
        }

        .btn-home {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--color-ink);
            color: var(--color-text-light);
            text-decoration: none;
            border-radius: 0;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-home:hover {
            background: var(--color-purple);
        }

        /* Mobile */
        @media (max-width: 799px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Page Content -->
    <div class="page-content">
        <!-- Hero -->
        <div class="page-hero">
            <?php require __DIR__ . '/../includes/symbols.php'; ?>
            <h1 class="page-title">청소년노동안전동아리</h1>
            <p class="page-desc">
                청소년 당사자가 직접 참여하여<br>
                노동안전보건을 공부하고 알리는 활동에 함께해주세요.
            </p>
        </div>

        <!-- Info -->
        <div class="info-box">
            <h3>청소년노동안전동아리란?</h3>
            <ul>
                <li>반도체산업현장에서 일할 때 필요한 산업안전보건지식을 자격증준비를 통해 공부합니다.</li>
                <li>즐거운 동아리프로그램을 직접 기획하고 참여하면서 슬기로운 사회생활을 준비합니다.</li>
                <li>방학에는 반도체관련 특강, 관련시설 견학등 특별프로그램에 참여할 수 있습니다.</li>
                <li>모임운영에 들어가는 식사비나 음료비등을 지원합니다.</li>
            </ul>
            <p style="margin-top: 1rem; font-size: 0.9rem; color: var(--color-gray); font-style: italic;">* 신청자가 많을 경우 어쩔 수 없이 단체가 정한 기준에 따른 심사를 거칠 예정입니다. 양해 바랍니다.</p>
        </div>

        <!-- 후원 안내 -->
        <div class="sponsor-box">
            <span>이 사업은</span>
            <img src="<?php echo url('assets/images/beautiful-foundation-ci.png'); ?>" alt="아름다운재단">
            <span>지원으로 운영합니다.</span>
        </div>

        <!-- Application Form -->
        <div class="apply-form" id="applyForm">
            <form id="committeeForm" onsubmit="return handleApply(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">이름 <span class="required">*</span></label>
                        <input type="text" id="name" name="name" placeholder="홍길동" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="phone">연락처 <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="010-1234-5678" required maxlength="20">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="school">학교 <span class="required">*</span></label>
                        <input type="text" id="school" name="school" placeholder="OO고등학교" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="major">전공 <span class="required">*</span></label>
                        <input type="text" id="major" name="major" placeholder="반도체과, 전자과, 화학공학과 등" required maxlength="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="grade">학년 <span class="required">*</span></label>
                        <select id="grade" name="grade" required>
                            <option value="">학년 선택</option>
                            <option value="고1">1학년</option>
                            <option value="고2">2학년</option>
                            <option value="고3">3학년</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email">이메일 <span class="optional">(선택)</span></label>
                        <input type="email" id="email" name="email" placeholder="example@email.com" maxlength="100">
                    </div>
                </div>

                <div class="form-group">
                    <label for="motivation">참여동기(질문, 요청사항, 하고싶은 말 가능) <span class="required">*</span></label>
                    <textarea id="motivation" name="motivation" placeholder="청소년노동안전동아리에 참여하고 싶은 이유를 자유롭게 작성해주세요." required maxlength="2000"></textarea>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">신청하기</button>
            </form>
        </div>

        <!-- Success -->
        <div class="success-message" id="successMessage">
            <div class="success-icon">🎉</div>
            <h2>신청이 완료되었습니다!</h2>
            <p>검토 후 입력하신 연락처로 안내드리겠습니다.<br>관심을 가져주셔서 감사합니다.</p>
            <a href="<?php echo url(''); ?>" class="btn-home">메인으로 돌아가기</a>
        </div>
    </div>

    <script>
        async function handleApply(e) {
            e.preventDefault();

            const form = e.target;
            const btn = document.getElementById('submitBtn');
            const originalText = btn.textContent;

            btn.disabled = true;
            btn.textContent = '신청 중...';

            const data = {
                name: form.querySelector('#name').value,
                school: form.querySelector('#school').value,
                grade: form.querySelector('#grade').value,
                major: form.querySelector('#major').value,
                phone: form.querySelector('#phone').value,
                email: form.querySelector('#email').value,
                motivation: form.querySelector('#motivation').value,
            };

            try {
                const response = await fetch('<?php echo url("api/committee.php"); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('applyForm').style.display = 'none';
                    document.getElementById('successMessage').classList.add('show');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return false;
                } else {
                    alert(result.message || '오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('신청 오류:', error);
                alert('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
            }

            btn.disabled = false;
            btn.textContent = originalText;
            return false;
        }
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>