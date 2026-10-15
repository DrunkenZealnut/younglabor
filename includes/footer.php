    </main>

    <footer class="footer">
        <div class="container">

            <div class="footer-grid">
                <div class="footer-brand">
            <nav class="footer-menu" aria-label="푸터 메뉴">
                <?php foreach(['about'=>'단체소개','work'=>'사업소개','youth-labor'=>'청년과노동','board'=>'게시판','resources'=>'자료실','tools'=>'온라인 도구','privacy'=>'개인정보 안내'] as $path=>$label): ?><a href="<?php echo publicEscape(url($path)); ?>"><?php echo $label; ?></a><?php endforeach; ?>
            </nav>
                    <div class="footer-wordmark"><?php echo htmlspecialchars('청쎈', ENT_QUOTES, 'UTF-8'); ?></div>
                    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div>
                    <div class="footer-label">Contact</div>
                    <p class="footer-text">
                        <?php if (($site['email'] ?: 'admin@younglabor.kr') !== ''): ?><a href="mailto:<?php echo htmlspecialchars($site['email'] ?: 'admin@younglabor.kr', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($site['email'] ?: 'admin@younglabor.kr', ENT_QUOTES, 'UTF-8'); ?></a><br><?php endif; ?>
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

    document.addEventListener('keydown', event => { if(event.key === 'Escape' && document.getElementById('nav').classList.contains('active')) { toggleMenu(); document.querySelector('.mobile-menu-btn').focus(); } });

    // 글자 크기·화면 폭에 따라 바뀌는 헤더 높이를 앵커·포커스 여백(--header-h, style.css의 scroll-margin-top)에 반영
    const siteHeader = document.querySelector('.header');
    if (siteHeader) {
        const setHeaderHeight = () => document.documentElement.style.setProperty('--header-h', siteHeader.getBoundingClientRect().height + 'px');
        setHeaderHeight();
        if ('ResizeObserver' in window) new ResizeObserver(setHeaderHeight).observe(siteHeader);
        else window.addEventListener('resize', setHeaderHeight);
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
