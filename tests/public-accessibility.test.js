const assert = require('node:assert/strict');
const base = process.env.SITE_BASE_URL || 'http://localhost:8080/younglabor';

(async () => {
    for (const path of ['/', '/about', '/activities', '/activity', '/press', '/resources', '/tools', '/club']) {
        const response = await fetch(base + path);
        assert.equal(response.status, 200, `${path} must return 200`);
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
            assert.ok(html.includes('지원으로 진행됩니다.'), 'club page must credit the foundation');
            if (html.includes('zealot-survey.vercel.app')) {
                assert.ok(html.includes('https://zealot-survey.vercel.app/RJXag60aMfMT'), 'club page must link the exact application form');
                assert.match(html, />\s*zealot-survey\.vercel\.app\s*</, 'club page must show the form domain as visible text');
                assert.ok(html.includes('외부 서비스로 이동'), 'club page must announce the external link');
            }
        }
    }
})().catch((error) => { console.error(error); process.exit(1); });
