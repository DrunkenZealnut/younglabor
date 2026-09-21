const assert = require('node:assert/strict');
const base = process.env.SITE_BASE_URL || 'http://localhost:8080/younglabor';

(async () => {
    for (const path of ['/', '/about', '/activities', '/activity', '/press', '/resources', '/tools']) {
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
    }
})().catch((error) => { console.error(error); process.exit(1); });
