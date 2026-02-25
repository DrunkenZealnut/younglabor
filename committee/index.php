<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="청소년 참견위원회 신청 - <?php echo htmlspecialchars($site['name']); ?>">
    <title>청소년 참견위원회 신청 - <?php echo htmlspecialchars($site['name']); ?></title>
    <base href="<?php echo htmlspecialchars($site['base_url']); ?>/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css">
    <style>
        :root {
            <?php echo getThemeCSSVariables($theme); ?>
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--color-text-dark);
            line-height: 1.6;
            background: var(--color-background);
            min-height: 100vh;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
            text-decoration: none;
        }

        .back-link {
            text-decoration: none;
            color: var(--color-primary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--color-primary-dark);
        }

        /* Page Content */
        .page-content {
            max-width: 720px;
            margin: 0 auto;
            padding: 7rem 2rem 4rem;
        }

        .page-hero {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-hero-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: 1rem;
        }

        .page-desc {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
        }

        /* Info Box */
        .info-box {
            background: var(--color-background-alt);
            border-left: 4px solid var(--color-accent);
            border-radius: 0 12px 12px 0;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .info-box h3 {
            color: var(--color-accent);
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box ul li {
            padding: 0.3rem 0;
            padding-left: 1.5rem;
            position: relative;
            color: #555;
        }

        .info-box ul li::before {
            content: '>';
            position: absolute;
            left: 0;
            color: var(--color-primary);
            font-weight: 700;
        }

        /* Form */
        .apply-form {
            background: var(--color-background-alt);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-text-dark);
            font-size: 0.95rem;
        }

        .form-group label .required {
            color: #e74c3c;
            margin-left: 2px;
        }

        .form-group label .optional {
            color: #999;
            font-weight: 400;
            font-size: 0.85rem;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fff;
            height: auto;
        }

        .form-group select {
            height: 3.1rem;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(91, 192, 222, 0.15);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #bbb;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--color-primary);
            color: var(--color-text-light);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(91, 192, 222, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Success Message */
        .success-message {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
        }

        .success-message.show {
            display: block;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .success-message h2 {
            font-size: 1.8rem;
            color: var(--color-primary);
            margin-bottom: 1rem;
        }

        .success-message p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .btn-home {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--color-primary);
            color: var(--color-text-light);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-home:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            background: var(--color-primary-dark);
            color: var(--color-text-light);
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
        }

        .footer-copyright {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .page-content {
                padding: 6rem 1rem 3rem;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .apply-form {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .header-inner {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-inner">
            <a href="<?php echo url(''); ?>" class="logo"><?php echo htmlspecialchars($site['name']); ?></a>
            <a href="<?php echo url(''); ?>#services" class="back-link">← 돌아가기</a>
        </div>
    </header>

    <!-- Page Content -->
    <div class="page-content">
        <!-- Hero -->
        <div class="page-hero">
            <div class="page-hero-icon">👥</div>
            <h1 class="page-title">청소년 참견위원회</h1>
            <p class="page-desc">
                청소년 당사자가 직접 참여하여<br>
                노동안전보건을 공부하고 알리는 활동에 함께해주세요.
            </p>
        </div>

        <!-- Info -->
        <div class="info-box">
            <h3>참견위원회란?</h3>
            <ul>
                <li>청소년이 노동안전보건 현장에 직접 참여합니다</li>
                <li>안전한 일터를 만들기 위한 공부와 활동을 합니다</li>
                <li>또래 청소년에게 노동권리를 알리는 역할을 합니다</li>
            </ul>
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
                    <label for="motivation">참여동기 <span class="required">*</span></label>
                    <textarea id="motivation" name="motivation" placeholder="참견위원회에 참여하고 싶은 이유를 자유롭게 작성해주세요." required maxlength="2000"></textarea>
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

    <!-- Footer -->
    <footer class="footer">
        <p class="footer-copyright">
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site['name']); ?>. All rights reserved.
        </p>
    </footer>

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
                } else {
                    alert(result.message || '오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('신청 오류:', error);
                alert('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }

            return false;
        }
    </script>
</body>
</html>
