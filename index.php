<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($site['slogan']); ?>">
    <title><?php echo htmlspecialchars($site['name']); ?></title>

    <!-- Base URL: <?php echo htmlspecialchars($site['base_url']); ?> (<?php echo htmlspecialchars($site['environment']); ?>) -->
    <base href="<?php echo htmlspecialchars($site['base_url']); ?>/">

    <!-- Pretendard 폰트 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css">

    <style>
        :root {
            <?php echo getThemeCSSVariables($theme); ?>
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--color-text-dark);
            line-height: 1.6;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--color-background-alt);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
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

        .nav {
            display: flex;
            gap: 2rem;
        }

        .nav a {
            text-decoration: none;
            color: var(--color-text-dark);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav a:hover {
            color: var(--color-primary);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--color-text-dark);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6rem 2rem 4rem;
            overflow: hidden;
            background-image: url('<?php echo url('assets/images/hero.jpg'); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--color-background) 0%, var(--color-secondary) 100%);
            opacity: 0.5;
            z-index: 1;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('<?php echo url('assets/images/hero-bg.svg'); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.6;
            z-index: 2;
            animation: heroBackgroundMove 30s ease-in-out infinite;
        }

        @keyframes heroBackgroundMove {
            0%, 100% {
                transform: scale(1) translateY(0);
            }
            50% {
                transform: scale(1.05) translateY(-10px);
            }
        }

        .hero-content {
            max-width: 800px;
            position: relative;
            z-index: 3;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--color-primary-dark);
            margin-bottom: 2rem;
        }

        .hero-cta {
            display: inline-block;
            background: var(--color-primary);
            color: var(--color-text-light);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .hero-cta:hover {
            background: var(--color-primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(91, 192, 222, 0.3);
        }

        /* Section Common */
        .section {
            padding: 5rem 2rem;
        }

        .section-alt {
            background: var(--color-background);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-primary);
            text-align: center;
            margin-bottom: 3rem;
        }

        /* Mission Section */
        .mission-intro {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 3rem;
        }

        .mission-intro p {
            font-size: 1.25rem;
            color: var(--color-text-dark);
            line-height: 1.8;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .stat-card {
            background: var(--color-background-alt);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--color-text-dark);
            font-weight: 500;
        }

        /* Services Section */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: var(--color-background-alt);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .service-card:hover {
            transform: translateY(-10px);
            border-color: var(--color-primary);
        }

        .service-card.clickable {
            position: relative;
        }

        .service-card.clickable::before {
            content: '클릭하여 이동 →';
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 0.85rem;
            color: var(--color-primary);
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .service-card.clickable:hover::before {
            opacity: 1;
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-text-dark);
            margin-bottom: 1rem;
        }

        .service-desc {
            color: #666;
            line-height: 1.7;
        }

        /* Contact Section */
        .contact-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            align-items: center;
        }

        .contact-info h3 {
            font-size: 1.5rem;
            color: var(--color-text-dark);
            margin-bottom: 1.5rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .contact-item-icon {
            width: 40px;
            height: 40px;
            background: var(--color-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-light);
            flex-shrink: 0;
        }

        .contact-item a {
            color: var(--color-primary-dark);
            text-decoration: none;
            transition: color 0.3s;
        }

        .contact-item a:hover {
            color: var(--color-primary);
            text-decoration: underline;
        }

        .contact-form {
            background: var(--color-background-alt);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--color-text-dark);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--color-primary);
            color: var(--color-text-light);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: var(--color-primary-dark);
        }

        /* Footer */
        .footer {
            background: var(--color-primary-dark);
            color: var(--color-text-light);
            padding: 3rem 2rem;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-text {
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .footer-copyright {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.2);
            opacity: 0.8;
            font-size: 0.9rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-inner {
                padding: 1rem;
            }

            .nav {
                position: fixed;
                top: 60px;
                left: 0;
                right: 0;
                background: var(--color-background-alt);
                flex-direction: column;
                padding: 1rem;
                gap: 1rem;
                display: none;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            }

            .nav.active {
                display: flex;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero::after {
                background-size: 150%;
                opacity: 0.5;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }

        /* Scroll Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-inner">
            <a href="#hero" class="logo"><?php echo htmlspecialchars($site['name']); ?></a>
            <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
            <nav class="nav" id="nav">
                <a href="#hero">소개</a>
                <a href="#mission">미션</a>
                <a href="#services">핵심사업</a>
                <a href="#contact">연락하기</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars($site['name']); ?></h1>
            <p class="hero-subtitle"><?php echo htmlspecialchars($site['slogan']); ?></p>
            <a href="#mission" class="hero-cta">자세히 보기</a>
        </div>
    </section>

    <!-- Mission Section -->
    <section id="mission" class="section section-alt">

            <h2 class="section-title fade-in">현장기반 노동안전보건 전문단체</h2>

            <h3 class="section-title fade-in" style="font-size: 1.8rem; margin-top: 3rem; color: red;">반도체는안전하지 않다</h3>

            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <div class="stat-number">59%</div>
                    <div class="stat-label">산재피해자 중 2030 비율</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-number">4.24배</div>
                    <div class="stat-label">일반인대비 20대 유방암 유병률</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-number">70%</div>
                    <div class="stat-label">암/희귀질환 2030 비율</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-number">0.1%</div>
                    <div class="stat-label">30인 미만 기업 노조조직률</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section">
        <div class="container">
            <h2 class="section-title fade-in">핵심사업</h2>

            <div class="services-grid">
                <div class="service-card fade-in">
                    <div class="service-icon">📚</div>
                    <h3 class="service-title">노동안전보건 교과서</h3>
                    <p class="service-desc">
                        반도체고 노동안전보건 교과서 개발<br>
                        안전하게 일할 권리를 위한 최소한의 지침서
                    </p>
                </div>

                <a href="<?php echo url('msds'); ?>" class="service-card clickable fade-in">
                    <div class="service-icon">📱</div>
                    <h3 class="service-title">노동안전보건 APP</h3>
                    <p class="service-desc">
                        누구나 쉽게 접근할 수 있는 안전정보 플랫폼<br>
                        법령, 매뉴얼, 안전노하우등 최신 데이터기반
                    </p>
                </a>

                <a href="<?php echo url('committee'); ?>" class="service-card clickable fade-in">
                    <div class="service-icon">👥</div>
                    <h3 class="service-title">청소년노동안전동아리</h3>
                    <p class="service-desc">
                        청소년 당사자가 직접 참여합니다<br>
                        노동안전보건을 공부하고 알리는 활동
                    </p>
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section section-alt">
        <div class="container">
            <h2 class="section-title fade-in">연락하기</h2>

            <div class="contact-content">
                <div class="contact-info fade-in">
                    <h3>함께하고 싶으시다면 연락주세요</h3>

                    <div class="contact-item">
                        <div class="contact-item-icon">✉️</div>
                        <span><a href="mailto:<?php echo htmlspecialchars($site['email']); ?>"><?php echo htmlspecialchars($site['email']); ?></a></span>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon">👤</div>
                        <span>대표: <?php echo htmlspecialchars($site['representative']); ?></span>
                    </div>

                    <p style="margin-top: 2rem; color: #666; line-height: 1.8;">
                        청년노동자인권센터는 제조업,특히 반도체 청년노동자들의 안전한 일터를 위해 활동합니다.
                        
                    </p>
                </div>

                <div class="contact-form fade-in">
                    <form action="#" method="post" onsubmit="return handleSubmit(event)">
                        <div class="form-group">
                            <label for="name">이름</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">이메일</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="message">메시지</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>

                        <button type="submit" class="btn-submit">문의하기</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo"><?php echo htmlspecialchars($site['name']); ?></div>
            <p class="footer-text"><?php echo htmlspecialchars($site['slogan']); ?></p>
            <p class="footer-text"><?php echo htmlspecialchars($site['url']); ?></p>
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site['name']); ?>. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        function toggleMenu() {
            const nav = document.getElementById('nav');
            nav.classList.toggle('active');
        }

        // Close menu on link click
        document.querySelectorAll('.nav a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('nav').classList.remove('active');
            });
        });

        // Scroll animation
        const fadeElements = document.querySelectorAll('.fade-in');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        fadeElements.forEach(el => observer.observe(el));

        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else {
                header.style.background = 'var(--color-background-alt)';
                header.style.backdropFilter = 'none';
            }
        });

        // Form submit handler
        async function handleSubmit(e) {
            e.preventDefault();

            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            // 버튼 비활성화
            submitBtn.disabled = true;
            submitBtn.textContent = '전송 중...';

            const data = {
                name: form.querySelector('#name').value,
                email: form.querySelector('#email').value,
                message: form.querySelector('#message').value
            };

            try {
                const response = await fetch('<?php echo url("api/contact.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    form.reset();
                } else {
                    alert(result.message || '오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Contact form error:', error);
                alert('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }

            return false;
        }
    </script>
</body>
</html>
