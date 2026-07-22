    </main>

    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                <?php require __DIR__ . '/symbols.php'; ?>
                <div>
                    <div class="footer-label">Contact</div>
                    <p class="footer-text">
                        <a href="mailto:<?php echo htmlspecialchars($site['email']); ?>"><?php echo htmlspecialchars($site['email']); ?></a><br>
                        대표: <?php echo htmlspecialchars($site['representative']); ?>
                    </p>
                </div>
                <div>
                    <div class="footer-label">Menu</div>
                    <nav class="footer-menu" aria-label="푸터 메뉴">
                        <a href="<?php echo url('about'); ?>">단체소개</a>
                        <a href="<?php echo url('activities'); ?>">사업소개</a>
                        <a href="<?php echo url('news'); ?>">소식</a>
                        <a href="<?php echo url('committee'); ?>">동아리 신청</a>
                    </nav>
                </div>
            </div>
            <div class="footer-wordmark"><?php echo htmlspecialchars($site['name']); ?></div>
            <p class="footer-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site['name']); ?> &middot; <?php echo htmlspecialchars($site['slogan']); ?></p>
        </div>
    </footer>

    <script>
    function toggleMenu() {
        const nav = document.getElementById('nav');
        const btn = document.querySelector('.mobile-menu-btn');
        const isOpen = nav.classList.toggle('active');
        btn.setAttribute('aria-label', isOpen ? '메뉴 닫기' : '메뉴 열기');
        btn.textContent = isOpen ? '✕' : '☰';
    }

    // 스크롤 애니메이션
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>
