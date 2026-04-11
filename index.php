<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
PageTracker::track('메인페이지');

$currentPage = 'home';
$pageTitle = $site['name'] . ' - ' . $site['slogan'];
$pageDescription = $site['slogan'];
require_once __DIR__ . '/includes/header.php';
?>

    <style>
        /* 홈페이지 전용 스타일 */
        .hero {
            min-height: 90vh;
            display: flex;
            align-items: center;
            padding: 8rem 2rem 4rem;
            background: linear-gradient(135deg, var(--color-background) 0%, #fff 50%, var(--color-background) 100%);
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--color-text-dark);
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-title span {
            color: var(--color-primary-dark);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 2.5rem;
            max-width: 600px;
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .hero {
                min-height: auto;
                padding: 6rem 1.5rem 3rem;
            }
            .hero-title {
                font-size: 2.2rem;
            }
            .hero-subtitle {
                font-size: 1.1rem;
            }
        }
    </style>

    <!-- 히어로 -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title fade-in">
                <?php echo htmlspecialchars($site['name']); ?>
            </h1>
            <p class="hero-subtitle fade-in">
                <?php echo htmlspecialchars($site['slogan']); ?>
            </p>
            <div class="hero-actions fade-in">
                <a href="<?php echo url('about'); ?>" class="btn-cta">단체 알아보기</a>
                <a href="<?php echo url('committee'); ?>" class="btn-cta btn-secondary">동아리 신청하기</a>
            </div>
        </div>
    </section>

    <!-- 핵심사업 -->
    <section class="section">
        <div class="container">
            <h2 class="section-title fade-in">핵심사업</h2>
            <p class="section-subtitle fade-in">현장 기반 노동안전보건 전문단체로서 4가지 핵심사업을 수행합니다.</p>
            <div class="card-grid">
                <a href="<?php echo url('activities'); ?>" class="card card-link fade-in">
                    <div class="card-number">1</div>
                    <h3 class="card-title">노동안전보건 교과서</h3>
                    <p class="card-desc">반도체고 노동안전보건 교과서 개발. 안전하게 일할 권리를 위한 최소한의 지침서</p>
                </a>

                <a href="<?php echo url('activities'); ?>" class="card card-link fade-in">
                    <div class="card-number">2</div>
                    <h3 class="card-title">노동안전보건 앱</h3>
                    <p class="card-desc">누구나 쉽게 접근할 수 있는 안전정보 플랫폼. 최신 데이터 기반의 위험인자 정보 제공</p>
                </a>

                <a href="<?php echo url('committee'); ?>" class="card card-link fade-in">
                    <div class="card-number">3</div>
                    <h3 class="card-title">청소년노동안전동아리</h3>
                    <p class="card-desc">청소년 당사자가 직접 참여합니다. 노동안전보건을 공부하고 알리는 활동</p>
                </a>

                <a href="<?php echo url('activities'); ?>" class="card card-link fade-in">
                    <div class="card-number">4</div>
                    <h3 class="card-title">학교 캠페인</h3>
                    <p class="card-desc">전국 5개 반도체고교 방문. 학생들과 관계를 만들어가는 세심한 캠페인 활동</p>
                </a>
            </div>
        </div>
    </section>

    <!-- 연락하기 -->
    <section class="section section-alt">
        <div class="container">
            <h2 class="section-title fade-in">연락하기</h2>
            <div class="contact-content">
                <div class="contact-info fade-in">
                    <h3>함께하고 싶으시다면 연락주세요</h3>
                    <div class="contact-item">
                        <span>✉️ <a href="mailto:<?php echo htmlspecialchars($site['email']); ?>"><?php echo htmlspecialchars($site['email']); ?></a></span>
                    </div>
                    <div class="contact-item">
                        <span>👤 대표: <?php echo htmlspecialchars($site['representative']); ?></span>
                    </div>
                    <p style="margin-top: 1.5rem; color: #666; line-height: 1.8; font-size: 0.95rem;">
                        <?php echo htmlspecialchars($site['name']); ?>는 제조업, 특히 반도체 청년노동자들의 안전한 일터를 위해 활동합니다.
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

    <script>
    async function handleSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '전송 중...';

        try {
            const response = await fetch('<?php echo url("api/contact.php"); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: form.querySelector('#name').value,
                    email: form.querySelector('#email').value,
                    message: form.querySelector('#message').value
                })
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) form.reset();
        } catch (error) {
            alert('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
        return false;
    }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
