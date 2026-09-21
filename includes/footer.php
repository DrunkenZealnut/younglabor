    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <?php require __DIR__ . '/symbols.php'; ?>
                <div>
                    <div class="footer-label">Contact</div>
                    <p class="footer-text">
                        <?php if ($site['email'] !== ''): ?><a href="mailto:<?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?></a><br><?php endif; ?>
                        대표: <?php echo htmlspecialchars($site['representative'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div>
                    <div class="footer-label">Support</div>
                    <?php foreach (siteSupportPartners() as $partner): ?>
                        <div class="footer-support">
                            <span><?php echo htmlspecialchars($partner['relationship'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <img src="<?php echo url('assets/images/beautiful-foundation-ci.png'); ?>" width="524" height="126" loading="lazy" decoding="async" alt="아름다운재단">
                            <span><?php echo htmlspecialchars($partner['program'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <nav class="footer-menu" aria-label="푸터 메뉴">
                <a href="<?php echo url('about'); ?>">우리는 누구인가</a>
                <a href="<?php echo url('activities'); ?>">우리가 하는 일</a>
                <a href="<?php echo url('activity'); ?>">활동게시판</a>
                <a href="<?php echo url('press'); ?>">언론보도</a>
                <a href="<?php echo url('resources'); ?>">자료실</a>
                <a href="<?php echo url('tools'); ?>">안전 도구</a>
            </nav>
            <div class="footer-wordmark"><?php echo htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            <p class="footer-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </footer>

    <script>
    function toggleMenu() {
        const nav = document.getElementById('nav');
        const btn = document.querySelector('.mobile-menu-btn');
        const isOpen = nav.classList.toggle('active');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        btn.setAttribute('aria-label', isOpen ? '메뉴 닫기' : '메뉴 열기');
        btn.textContent = isOpen ? '✕' : '☰';
    }

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in').forEach((element) => observer.observe(element));
    } else {
        document.querySelectorAll('.fade-in').forEach((element) => element.classList.add('visible'));
    }
    </script>
</body>
</html>
