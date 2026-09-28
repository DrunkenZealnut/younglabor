const assert = require('node:assert/strict');
const base = (process.env.SITE_BASE_URL || 'http://localhost:8080/younglabor').replace(/\/$/, '');
const headers = { 'user-agent': 'younglabor-a11y-bot' };

(async () => {
    for (const path of ['/', '/about', '/activities', '/activity', '/press', '/resources', '/tools', '/club']) {
        const response = await fetch(base + path, { headers });
        assert.equal(response.status, 200, `${path} must return 200`);
        assert.equal(response.redirected, false, `${path} must not redirect`);
        const html = await response.text();
        assert.equal((html.match(/<main\b/g) || []).length, 1, `${path} must have one main`);
        assert.equal((html.match(/<h1\b/g) || []).length, 1, `${path} must have one h1`);
        assert.match(html, /href="#main-content"/, `${path} must have a skip link`);
        assert.match(html, /<link rel="canonical" href="https?:\/\//, `${path} must have canonical metadata`);
        assert.doesNotMatch(html, /pretendard|fonts\.googleapis\.com/i, `${path} must not load a remote font`);
        if (path === '/tools') {
            for (const value of ['https://safefactory.kr/', 'safefactory.kr', 'https://laborconsult.vercel.app/', 'laborconsult.vercel.app']) {
                assert.ok(html.includes(value), `tools page must include ${value}`);
            }
            assert.doesNotMatch(html, /<iframe\b/i, 'tools page must not embed services');
        }
        if (path === '/club') {
            assert.doesNotMatch(html, /<iframe\b/i, 'club page must not embed the application form');
            assert.match(html, /class="sponsor-box">[\s\S]*?alt="아름다운재단"[\s\S]*?지원으로 진행됩니다\./, 'club page must credit the foundation with its logo');
            const open = html.includes('class="club-status">모집 중');
            const closed = html.includes('class="club-status">모집 마감');
            assert.ok(open !== closed, 'club page must show exactly one recruitment state');
            if (open) {
                assert.ok(html.includes('href="https://zealot-survey.vercel.app/RJXag60aMfMT"'), 'open club page must link the exact application form');
                assert.match(html, />\s*zealot-survey\.vercel\.app\s*</, 'open club page must show the form domain as visible text');
                assert.ok(html.includes('외부 서비스로 이동'), 'open club page must announce the external link');
            } else {
                assert.ok(!html.includes('zealot-survey.vercel.app'), 'closed club page must not link the application form');
                assert.ok(html.includes('이번 모집은 마감되었습니다.'), 'closed club page must say recruitment is closed');
            }
            if (process.env.CLUB_EXPECT_STATE) {
                assert.equal(open ? 'open' : 'closed', process.env.CLUB_EXPECT_STATE, 'club recruitment state differs from CLUB_EXPECT_STATE');
            }
        }
    }
})().catch((error) => { console.error(error); process.exit(1); });
