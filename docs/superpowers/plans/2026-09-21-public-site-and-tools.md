# Public Site and Safety Tools Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the public information flow around the center's identity and field work, add a first-class safety-tools page for SafeFactory and Basic Labor Consultation, and improve initial rendering performance without changing the existing administrator or committee workflows.

**Architecture:** Keep PHP server-rendered pages and introduce one pure content module for work areas, impact figures, and external tools. Shared header/footer markup owns the document landmarks, while the existing single public stylesheet owns all public-page presentation. The managed-content repository supplies the latest activity and press cards behind failure-isolating loaders; Apache supplies compression and versioned-asset caching.

**Tech Stack:** Apache, PHP 7.4+, HTML5, CSS, minimal vanilla JavaScript, shell, curl, Node.js for the existing XSS regression test

**Spec:** `docs/superpowers/specs/2026-09-21-public-site-redesign-design.md`

## Global Constraints

- Start only after `docs/audits/2026-09-21-production-inventory.md` contains the exact line `Gate: PASS` and the managed-content plan is implemented.
- Preserve `/admin/login.php`, `/committee/`, both form APIs, and all existing production data.
- Use the hero copy `제조업 청년노동자들의 안전과 노동권을 지킵니다.` exactly.
- Use the three work areas `교육`, `안전 도구`, and `연구`.
- Link SafeFactory only to `https://safefactory.kr/` and Basic Labor Consultation only to `https://laborconsult.vercel.app/`; label both `제작 중`.
- Do not iframe, hotlink images from, or fetch runtime data from either external service.
- Remove the public Pretendard network request; use the Korean system-font stack.
- Keep public content server-rendered and usable with JavaScript disabled.
- Target mobile p75 LCP &lt;= 2.5 seconds, INP &lt;= 200 milliseconds, and CLS &lt;= 0.1.

## Review Focus

- Missing or malformed external-tool data must not produce unsafe URLs or broken HTML.
- Mobile navigation must expose its open state, retain keyboard focus visibility, and remain usable without animation.
- A database outage in PageTracker or a latest-content query must not prevent static pages from rendering.
- Apache instances without `mod_expires`, `mod_headers`, or `mod_deflate` must not return 500 because every directive is module-guarded.
- Existing administrator, contact, and committee pages must keep their current routes and POST behavior.

---

### Task 1: Add the public content contract

**Files:**

- Create: `includes/SiteContent.php`
- Create: `tests/site-content.test.php`

**Interfaces:**

- Consumes: no database or request state
- Produces: `siteWorkAreas(): array`, `siteTools(): array`, and `siteSupportPartners(): array`

- [ ] **Step 1: Write the failing pure-content test**

```php
<?php
require_once __DIR__ . '/../includes/SiteContent.php';

function assertSameValue($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$areas = siteWorkAreas();
assertSameValue(['field', 'education', 'tools', 'research'], array_column($areas, 'key'), '활동 갈래 순서가 달라졌습니다.');

$tools = siteTools();
assertSameValue(2, count($tools), '안전 도구는 두 개여야 합니다.');
assertSameValue('https://safefactory.kr/', $tools[0]['url'], 'SafeFactory URL이 다릅니다.');
assertSameValue('https://laborconsult.vercel.app/', $tools[1]['url'], '노동상담 URL이 다릅니다.');
assertSameValue(['제작 중', '제작 중'], array_column($tools, 'status'), '두 도구 모두 제작 중이어야 합니다.');
foreach ($tools as $tool) {
    $parts = parse_url($tool['url']);
    assertSameValue('https', $parts['scheme'] ?? '', '도구 링크는 HTTPS여야 합니다.');
    if (!in_array($parts['host'] ?? '', ['safefactory.kr', 'laborconsult.vercel.app'], true)) exit(1);
}

$support = siteSupportPartners();
assertSameValue('아름다운재단', $support[0]['name'], '지원기관 이름이 다릅니다.');
assertSameValue('2025 공익단체 인큐베이팅 지원사업', $support[0]['program'], '지원사업 크레딧이 다릅니다.');
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/site-content.test.php`

Expected: FAIL because `includes/SiteContent.php` does not exist.

- [ ] **Step 3: Implement the content module**

```php
<?php
function siteWorkAreas(): array {
    return [
        ['key' => 'education', 'title' => '교육', 'summary' => '일을 시작하기 전에 위험을 알고 질문할 수 있게 합니다.'],
        ['key' => 'tools', 'title' => '안전 도구', 'summary' => '학교와 일터에서 필요한 안전·노동 정보를 쉽게 찾게 합니다.'],
        ['key' => 'research', 'title' => '연구', 'summary' => '현장 경험을 근거로 만들고 교육과 제도 개선에 연결합니다.'],
    ];
}

function siteTools(): array {
    return [
        [
            'name' => 'SafeFactory',
            'status' => '제작 중',
            'url' => 'https://safefactory.kr/',
            'domain' => 'safefactory.kr',
            'tagline' => '반도체와 산업안전을 질문하고 배우는 AI 학습 플랫폼',
            'audience' => '반도체 분야 특성화고 학생과 교사, 산업안전을 배우려는 청년',
            'description' => 'NCS 반도체 자료, 안전교육 자료, KOSHA 가이드와 MSDS를 한곳에서 검색하고 질문할 수 있습니다.',
            'cta' => 'SafeFactory 살펴보기',
        ],
        [
            'name' => '기초 노동상담',
            'status' => '제작 중',
            'url' => 'https://laborconsult.vercel.app/',
            'domain' => 'laborconsult.vercel.app',
            'tagline' => '법령과 판례의 근거를 함께 보여주는 AI 노동상담·임금계산 도구',
            'audience' => '기초 노동 문제를 확인하려는 청년노동자와 시민',
            'description' => '근로기준법, 판례와 행정해석을 근거로 상담 정보를 제공하고 28종의 임금 계산을 돕습니다.',
            'cta' => '기초 노동상담 사용해보기',
        ],
    ];
}

function siteSupportPartners(): array {
    return [[
        'name' => '아름다운재단',
        'relationship' => '지원',
        'program' => '2025 공익단체 인큐베이팅 지원사업',
    ]];
}
```

- [ ] **Step 4: Run the test and verify it passes**

Run: `php tests/site-content.test.php`

Expected: exit 0 with no output.

- [ ] **Step 5: Commit the content contract**

```bash
git add includes/SiteContent.php tests/site-content.test.php
git commit -m "feat: define public site content"
```

### Task 2: Correct the shared landmark and navigation shell

**Files:**

- Modify: `includes/header.php`
- Modify: `includes/footer.php`
- Modify: `assets/css/style.css`
- Create: `tests/public-layout.test.php`

**Interfaces:**

- Consumes: `$currentPage`, `$pageTitle`, `$pageDescription`, and `url()`
- Produces: one `<main id="main-content">` opened by the header and closed by the footer; seven-item public navigation

- [ ] **Step 1: Write the failing source-level landmark test**

```php
<?php
$header = file_get_contents(__DIR__ . '/../includes/header.php');
$footer = file_get_contents(__DIR__ . '/../includes/footer.php');
foreach (['우리는 누구인가', '우리가 하는 일', '활동게시판', '언론보도', '자료실', '안전 도구', '함께하기'] as $label) {
    if (strpos($header, $label) === false) exit(1);
}
if (substr_count($header, '<main') !== 1) exit(1);
if (strpos($header, '</main>') !== false) exit(1);
if (strpos($footer, '</main>') === false) exit(1);
if (strpos($footer, 'siteSupportPartners()') === false) exit(1);
if (strpos($header, 'aria-expanded="false"') === false) exit(1);
if (strpos($header, "document.documentElement.classList.add('js')") === false) exit(1);
if (strpos($footer, "setAttribute('aria-expanded'") === false) exit(1);
if (strpos($header, 'https://cdn.jsdelivr.net/gh/orioncactus/pretendard') !== false) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/public-layout.test.php`

Expected: FAIL because the header closes `<main>`, lacks the new links, and loads Pretendard remotely.

- [ ] **Step 3: Implement the semantic shell**

Replace the public navigation with links to `about`, `activities`, `activity`, `press`, `resources`, `tools`, and `#contact`; open `<main id="main-content">` after the header and close it as the first element in `includes/footer.php`. Initialize the menu button with `aria-controls="nav" aria-expanded="false"` and update both `aria-expanded` and the label inside `toggleMenu()`. Add `document.documentElement.classList.add('js')` before the stylesheet, keep the mobile navigation visible by default, and apply the collapsed state only through `.js .nav`; without JavaScript all seven links remain available. Render `siteSupportPartners()` in the footer with separate relationship, organization, and program text; use the existing logo only with its intrinsic dimensions and `alt="아름다운재단"`.

Use this body font declaration in `assets/css/style.css`:

```css
body {
    font-family: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", "Noto Sans KR", "Malgun Gothic", sans-serif;
}
@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
}
```

- [ ] **Step 4: Run the landmark test and PHP syntax checks**

Run: `php tests/public-layout.test.php && php -l includes/header.php && php -l includes/footer.php`

Expected: all commands exit 0.

- [ ] **Step 5: Commit the shared shell**

```bash
git add includes/header.php includes/footer.php assets/css/style.css tests/public-layout.test.php
git commit -m "fix: correct public page landmarks and navigation"
```

### Task 3: Rebuild the home, identity, and work pages

**Files:**

- Modify: `index.php`
- Modify: `about.php`
- Modify: `activities.php`
- Modify: `assets/css/style.css`
- Create: `includes/HomeContent.php`
- Create: `tests/public-copy.test.php`
- Create: `tests/home-content.test.php`

**Interfaces:**

- Consumes: `siteWorkAreas()`, `siteTools()`, and `siteSupportPartners()` from Task 1; `ContentRepository::latestPublished()` from the managed-content plan
- Produces: `loadHomeContent(callable $repositoryFactory): array` with `activity`, `press`, and `unavailable` keys; the approved homepage section order and citizen-facing identity/work copy

- [ ] **Step 1: Write the failing approved-copy and section-order test**

```php
<?php
$home = file_get_contents(__DIR__ . '/../index.php');
$about = file_get_contents(__DIR__ . '/../about.php');
$activities = file_get_contents(__DIR__ . '/../activities.php');
$required = [
    '제조업 청년노동자들의 안전과 노동권을 지킵니다.',
    '현장에서 무슨 일이 있었나', '왜 이 일을 하는가', '우리가 하는 일',
    '현장에서 쓰는 안전 도구', '언론이 본 현장', '함께하기', '함께하는 곳들',
];
$last = -1;
foreach ($required as $copy) {
    $position = strpos($home, $copy);
    if ($position === false || $position <= $last) exit(1);
    $last = $position;
}
foreach (['왜 제조업 청년노동자인가', '왜 반도체고에서 시작하는가'] as $copy) {
    if (strpos($about, $copy) === false) exit(1);
}
foreach (['교육', '안전 도구', '연구'] as $copy) {
    if (strpos($activities, $copy) === false) exit(1);
}
```

Create the failure-isolation test alongside it:

```php
<?php
require __DIR__ . '/../includes/HomeContent.php';
$ok = loadHomeContent(static function () {
    return new class {
        public function latestPublished(string $type, int $limit): array { return [['type'=>$type,'limit'=>$limit]]; }
    };
});
if (count($ok['activity']) !== 1 || count($ok['press']) !== 1 || $ok['unavailable']) exit(1);
$failed = loadHomeContent(static function () { throw new RuntimeException('database unavailable'); });
if ($failed !== ['activity'=>[], 'press'=>[], 'unavailable'=>true]) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/public-copy.test.php && php tests/home-content.test.php`

Expected: FAIL because the approved structure, copy, and failure-isolating loader are absent.

- [ ] **Step 3: Implement the approved page structure**

Implement the loader as follows, then require it and `includes/SiteContent.php` before the shared header:

```php
function loadHomeContent(callable $repositoryFactory): array {
    try {
        $repository = $repositoryFactory();
        return [
            'activity' => $repository->latestPublished('activity', 3),
            'press' => $repository->latestPublished('press', 3),
            'unavailable' => false,
        ];
    } catch (Throwable $error) {
        error_log('Home content unavailable');
        return ['activity'=>[], 'press'=>[], 'unavailable'=>true];
    }
}
```

Render the home sections in the exact tested order. Use loops over the four static content functions and the two latest arrays; do not duplicate those arrays in templates. When `unavailable` is true, render `최근 소식을 불러오지 못했습니다. 각 게시판에서 다시 확인해 주세요.` while keeping every later section available.

Replace the current grant-output framing in `activities.php` with four work-area sections, each containing its purpose, current activities, and a link to related activity posts or resources. Rewrite `about.php` around identity, manufacturing youth, the semiconductor-school starting point, intended change, people, and partners. Remove comparison-led copy about other organizations.

- [ ] **Step 4: Run copy and syntax tests**

Run: `php tests/public-copy.test.php && php tests/home-content.test.php && php -l includes/HomeContent.php && php -l index.php && php -l about.php && php -l activities.php`

Expected: all commands exit 0.

- [ ] **Step 5: Commit the public information architecture**

```bash
git add index.php about.php activities.php assets/css/style.css includes/HomeContent.php tests/public-copy.test.php tests/home-content.test.php
git commit -m "feat: refocus public pages on field impact"
```

### Task 4: Add the safety-tools page

**Files:**

- Create: `tools.php`
- Modify: `assets/css/style.css`
- Create: `tests/tools-page.test.php`

**Interfaces:**

- Consumes: `siteTools(): array`
- Produces: `/tools` with two local-rendered service cards and same-window external links

- [ ] **Step 1: Write the failing page contract test**

```php
<?php
$source = file_get_contents(__DIR__ . '/../tools.php');
foreach (['siteTools()', '현장에서 생긴 질문을, 누구나 사용할 수 있는 도구로 만듭니다.', 'AI 답변과 계산 결과는 참고용이며 법적 효력이 없습니다.'] as $needle) {
    if (strpos($source, $needle) === false) exit(1);
}
if (stripos($source, '<iframe') !== false) exit(1);
if (strpos($source, 'target="_blank"') !== false) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/tools-page.test.php`

Expected: FAIL because `tools.php` does not exist.

- [ ] **Step 3: Implement the page with escaped local content**

```php
<?php foreach (siteTools() as $tool): ?>
<article class="tool-card">
    <span class="tool-status"><?php echo htmlspecialchars($tool['status'], ENT_QUOTES, 'UTF-8'); ?></span>
    <h2><?php echo htmlspecialchars($tool['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <p class="tool-tagline"><?php echo htmlspecialchars($tool['tagline'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($tool['description'], ENT_QUOTES, 'UTF-8'); ?></p>
    <a class="tool-link" href="<?php echo htmlspecialchars($tool['url'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars($tool['cta'], ENT_QUOTES, 'UTF-8'); ?>
        <span aria-hidden="true">↗</span>
        <span class="tool-domain"><?php echo htmlspecialchars($tool['domain'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="sr-only">외부 서비스로 이동</span>
    </a>
</article>
<?php endforeach; ?>
```

Add the approved page heading, audience text, labor-consultation warning, and external privacy/terms notice around this loop. Add `.tool-grid`, `.tool-card`, `.tool-status`, `.tool-link`, and `.sr-only` styles to the shared stylesheet.

- [ ] **Step 4: Run the contract, syntax, and live-link checks**

Run:

```bash
php tests/tools-page.test.php
php -l tools.php
for url in https://safefactory.kr/ https://laborconsult.vercel.app/; do curl -L -sS --max-time 20 -o /dev/null -w '%{http_code} %{url_effective}\n' "$url"; done
```

Expected: PHP checks exit 0 and both link checks currently report HTTP 200. A future non-200 is recorded as a warning and does not alter local rendering.

- [ ] **Step 5: Commit the tools page**

```bash
git add tools.php assets/css/style.css tests/tools-page.test.php
git commit -m "feat: showcase public safety tools"
```

### Task 5: Add safe asset versioning, cache policy, and compression

**Files:**

- Modify: `config.php`
- Modify: `includes/header.php`
- Modify: `.htaccess`
- Delete: `assets/images/hero.jpg`
- Modify: `committee/index.php`
- Create: `tests/public-performance.test.php`

**Interfaces:**

- Consumes: a repository-relative public asset path
- Produces: `assetUrl(string $path): string` with a file modification version; module-guarded Apache performance directives

- [ ] **Step 1: Write the failing asset and server-policy test**

```php
<?php
require_once __DIR__ . '/../config.php';
$url = assetUrl('assets/css/style.css');
if (!preg_match('/\/assets\/css\/style\.css\?v=\d+$/', $url)) exit(1);
$header = file_get_contents(__DIR__ . '/../includes/header.php');
if (strpos($header, "assetUrl('assets/css/style.css')") === false) exit(1);
$rules = file_get_contents(__DIR__ . '/../.htaccess');
foreach (['<IfModule mod_expires.c>', '<IfModule mod_headers.c>', '<IfModule mod_deflate.c>'] as $directive) {
    if (strpos($rules, $directive) === false) exit(1);
}
if (file_exists(__DIR__ . '/../assets/images/hero.jpg')) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/public-performance.test.php`

Expected: FAIL because `assetUrl()` and the guarded policies do not exist and the unused image remains.

- [ ] **Step 3: Implement deterministic asset URLs**

```php
function assetUrl(string $path): string {
    $relative = ltrim($path, '/');
    $absolute = __DIR__ . '/' . $relative;
    $version = is_file($absolute) ? (string)filemtime($absolute) : '1';
    return url($relative) . '?v=' . rawurlencode($version);
}
```

Use `assetUrl('assets/css/style.css')` in the header. Add width `524`, height `126`, `loading="lazy"`, and `decoding="async"` to the Beautiful Foundation image in `committee/index.php`.

- [ ] **Step 4: Add module-guarded Apache policies**

```apache
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
</IfModule>
<IfModule mod_headers.c>
  <FilesMatch "\.(?:css|js|png|jpe?g|svg|webp|avif)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
  </FilesMatch>
</IfModule>
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/json image/svg+xml
</IfModule>
```

Remove the unreferenced image with `git rm assets/images/hero.jpg`; Git history remains the recovery source.

- [ ] **Step 5: Verify policy syntax and tests locally**

Run:

```bash
php tests/public-performance.test.php
php -l config.php
curl -sSI http://localhost:8080/younglabor/assets/css/style.css | rg -i '^(HTTP/|cache-control:|content-encoding:)'
```

Expected: tests and lint exit 0; CSS returns 200 and a cache-control header. Compression may depend on the local request's `Accept-Encoding`.

- [ ] **Step 6: Commit the performance policy**

```bash
git add config.php includes/header.php .htaccess committee/index.php tests/public-performance.test.php
git add -u assets/images/hero.jpg
git commit -m "perf: optimize public asset delivery"
```

### Task 6: Add public smoke, accessibility, and performance gates

**Files:**

- Create: `tests/public-http-smoke.sh`
- Create: `tests/public-accessibility.test.js`
- Create: `tests/page-tracker-failure.test.php`
- Modify: `includes/header.php`
- Modify: `includes/footer.php`
- Modify: `index.php`
- Modify: `about.php`
- Modify: `activities.php`
- Modify: `tools.php`
- Modify: `activity/index.php`
- Modify: `activity/view.php`
- Modify: `press/index.php`
- Modify: `resources/index.php`
- Modify: `resources/view.php`

**Interfaces:**

- Consumes: `SITE_BASE_URL`, defaulting to `http://localhost:8080/younglabor`
- Produces: repeatable HTTP, landmark, metadata, external-link, and transfer-budget checks

- [ ] **Step 1: Write the failing HTTP smoke script**

```bash
#!/usr/bin/env bash
set -euo pipefail
base_url="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
for path in / /about /activities /activity /press /resources /tools /committee/ /admin/login.php; do
  code="$(curl -sS -o /tmp/yl-smoke-body -w '%{http_code}' "$base_url$path")"
  test "$code" = 200
done
home_bytes="$(curl -sS "$base_url/" | wc -c | tr -d ' ')"
css_bytes="$(curl -sS "$base_url/assets/css/style.css" | wc -c | tr -d ' ')"
test "$home_bytes" -lt 81920
test "$css_bytes" -lt 40960
```

- [ ] **Step 2: Write the failing DOM accessibility test**

```js
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
```

- [ ] **Step 3: Run both checks and verify the missing requirements fail**

Run: `bash tests/public-http-smoke.sh && node tests/public-accessibility.test.js`

Expected: at least the accessibility test fails until skip links, canonical URLs, and visible domains are complete.

- [ ] **Step 4: Add the PageTracker failure-isolation test**

```php
<?php
class Database {
    public static function getInstance() { throw new RuntimeException('database unavailable'); }
}
require_once __DIR__ . '/../includes/PageTracker.php';
$_SERVER['HTTP_USER_AGENT'] = 'Public smoke browser';
$_SERVER['REQUEST_URI'] = '/';
PageTracker::track('홈');
echo "tracker failure isolated\n";
```

Run: `php tests/page-tracker-failure.test.php`

Expected: exit 0 with `tracker failure isolated`; the expected error may appear on STDERR but must not escape as an exception.

- [ ] **Step 5: Add the missing accessibility and metadata markup**

Add a first-focusable `본문으로 건너뛰기` link, canonical metadata derived from `$pageUrl`, clear focus-visible CSS, exactly one page `<h1>`, and visible domain labels on tool cards. Keep every core link and paragraph in server-rendered HTML.

- [ ] **Step 6: Run the complete public regression suite**

Run:

```bash
php tests/site-content.test.php
php tests/public-layout.test.php
php tests/public-copy.test.php
php tests/home-content.test.php
php tests/tools-page.test.php
php tests/public-performance.test.php
php tests/page-tracker-failure.test.php
bash tests/public-http-smoke.sh
node tests/public-accessibility.test.js
node tests/admin-contact-xss.test.js
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

Expected: every command exits 0 and every tracked PHP file reports no syntax errors.

- [ ] **Step 7: Measure the mobile baseline and result**

Run: `npx --yes lighthouse@12.8.2 http://localhost:8080/younglabor/ --only-categories=performance,accessibility,seo --form-factor=mobile --output=json --output-path=/tmp/younglabor-lighthouse.json --chrome-flags='--headless'`

Expected: save the score and LCP/CLS values in the PR description. Treat failure to reach LCP &lt;= 2.5 seconds or CLS &lt;= 0.1 as a release blocker; record INP from production field data after deployment because a one-shot Lighthouse run cannot establish p75 INP.

- [ ] **Step 8: Commit the public quality gates**

```bash
git add tests/public-http-smoke.sh tests/public-accessibility.test.js tests/page-tracker-failure.test.php includes/header.php includes/footer.php index.php about.php activities.php tools.php activity press resources assets/css/style.css
git commit -m "test: add public site quality gates"
```
