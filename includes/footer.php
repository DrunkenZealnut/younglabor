    </main>

    <footer class="footer" role="contentinfo">
        <div class="footer-content">
            <div>
                <div class="footer-logo"><?php echo htmlspecialchars($site['name']); ?></div>
                <p class="footer-text"><?php echo htmlspecialchars($site['slogan']); ?></p>
                <p class="footer-text">대표: <?php echo htmlspecialchars($site['representative']); ?></p>
            </div>
            <div>
                <p class="footer-text">
                    <a href="mailto:<?php echo htmlspecialchars($site['email']); ?>" style="color: inherit; text-decoration: none;">
                        <?php echo htmlspecialchars($site['email']); ?>
                    </a>
                </p>
            </div>
        </div>
        <div class="container">
            <p class="footer-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site['name']); ?></p>
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
