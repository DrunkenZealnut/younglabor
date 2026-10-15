// Value: protects=다른 페이지의 '문의'·'참여 안내' 링크(/#contact, /work#programs)나 건너뛰기 링크로 이동하거나 글자 크기를 바꾼 뒤 앵커로 가도 대상 윗부분이 sticky 헤더 뒤에 가려지지 않고, Shift+Tab으로 돌아간 링크의 포커스 링도 가려지지 않으며, 헤더 메뉴로 포커스가 가도 페이지가 튀지 않음;
//   fails_when=style.css의 앵커·포커스 scroll-margin-top이나 footer.php의 --header-h 갱신(첫 측정·ResizeObserver)이 빠지거나 이름이 어긋나거나, 여백이 헤더보다 크게 잡히거나, html에 scroll-padding을 다시 걸어 헤더 포커스 때 페이지가 튀거나, footer 스크립트가 예외로 멈춤;
//   why_new=다른 테스트는 HTML 문자열·HTTP 상태만 보고(public-http-smoke.sh는 /#contact가 200인지만 확인) 실제 스크롤 위치를 재는 브라우저 테스트가 없음; seam=none;
//   no_claim=Chrome 헤드리스 한 엔진만 측정(Safari·Firefox·실기기 미확인), 브라우저 글자 크기는 Page.setFontSizes로 흉내 냄, 키보드는 /about의 정해진 경우만 잼, 섹션 윗선의 1px 겹침 자체는 고정하지 않음(스크롤 반올림 ±0.5px)
// 실행: SITE_BASE_URL=http://localhost:8080/younglabor node --test tests/public-anchor-offset.test.js (Chrome 필요, 다른 경로면 CHROME_BIN 지정)
const assert = require('node:assert/strict');
const { spawn } = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

const base = (process.env.SITE_BASE_URL || 'http://localhost:8080/younglabor').replace(/\/$/, '');
const chromeBin = process.env.CHROME_BIN || ['/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', '/usr/bin/google-chrome', '/usr/bin/chromium', '/usr/bin/chromium-browser'].find((file) => fs.existsSync(file));
if (!chromeBin) {
    console.error('Chrome을 찾지 못했습니다. CHROME_BIN에 실행 파일 경로를 지정하세요.');
    process.exit(1);
}

// 글자 크기 배율은 브라우저 기본 글꼴 크기(16px)를 키우는 설정을 흉내 낸다. 좁은 폭·큰 글자에서 로고가 여러 줄로 접혀 헤더가 가장 높아진다.
const cases = [
    { label: '375px · 100% · /#contact', width: 375, scale: 1, path: '/#contact', id: 'contact' },
    { label: '800px · 200% · /work#programs', width: 800, scale: 2, path: '/work#programs', id: 'programs' },
    // 가장 좁은 폭·큰 글자에서 로고가 가장 많이 접혀 헤더가 가장 높다
    { label: '320px · 200% · /#contact', width: 320, scale: 2, path: '/#contact', id: 'contact' },
    // 페이지를 연 뒤 글자를 키우면 헤더가 높아진다. 처음 잰 높이만 쓰면 이 경우가 가려진다.
    { label: '375px · 100%→200% 후 #contact', width: 375, scale: 1, path: '/', id: 'contact', thenScale: 2 },
];

// 다른 공개 테스트처럼 bot UA를 써서 PageTracker가 방문 통계에 기록하지 않게 한다
const userAgent = 'Mozilla/5.0 (younglabor-anchor-bot)';
const userDataDir = fs.mkdtempSync(path.join(os.tmpdir(), 'yl-anchor-offset-'));
const chrome = spawn(chromeBin, ['--headless=new', '--remote-debugging-port=0', `--user-data-dir=${userDataDir}`, `--user-agent=${userAgent}`, '--no-first-run', '--no-default-browser-check', '--disable-gpu', '--hide-scrollbars', 'about:blank'], { stdio: ['ignore', 'ignore', 'pipe'] });
// Chrome이 프로필 쓰기를 끝내고 종료한 뒤에 임시 프로필을 지운다
const cleanup = () => new Promise((resolve) => {
    if (chrome.exitCode !== null || chrome.signalCode !== null) return resolve();
    chrome.once('exit', resolve);
    chrome.kill();
}).then(() => fs.rmSync(userDataDir, { recursive: true, force: true, maxRetries: 5, retryDelay: 100 }));
const watchdog = setTimeout(() => { console.error('시간 초과(60초)'); cleanup().finally(() => process.exit(1)); }, 60000);

const devtoolsPort = new Promise((resolve, reject) => {
    let log = '';
    chrome.stderr.on('data', (chunk) => {
        log += chunk;
        const match = log.match(/DevTools listening on ws:\/\/[^:]+:(\d+)\//);
        if (match) resolve(match[1]);
    });
    chrome.on('exit', (code) => reject(new Error(`Chrome이 바로 종료됐습니다(exit ${code}): ${log}`)));
});

function connect(url) {
    const ws = new WebSocket(url);
    const pending = new Map();
    const listeners = new Set();
    let nextId = 0;
    ws.addEventListener('message', (event) => {
        const message = JSON.parse(event.data);
        if (message.id && pending.has(message.id)) {
            const { resolve, reject } = pending.get(message.id);
            pending.delete(message.id);
            message.error ? reject(new Error(message.error.message)) : resolve(message.result);
        } else if (message.method) {
            listeners.forEach((listener) => listener(message));
        }
    });
    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const id = ++nextId;
        pending.set(id, { resolve, reject });
        ws.send(JSON.stringify({ id, method, params }));
    });
    const once = (method) => new Promise((resolve) => {
        const listener = (message) => { if (message.method === method) { listeners.delete(listener); resolve(message.params); } };
        listeners.add(listener);
    });
    const on = (method, handler) => listeners.add((message) => { if (message.method === method) handler(message.params); });
    return new Promise((resolve, reject) => {
        ws.addEventListener('open', () => resolve({ send, once, on, close: () => ws.close() }));
        ws.addEventListener('error', () => reject(new Error(`DevTools 연결 실패: ${url}`)));
    });
}

// 스크롤(부드러운 스크롤 포함)이 10프레임 동안 멈출 때까지 기다린다
const settle = 'new Promise((done) => { let last = -1, still = 0, frames = 0; const tick = () => { still = scrollY === last ? still + 1 : 0; last = scrollY; if (still >= 10 || ++frames > 300) done(); else requestAnimationFrame(tick); }; requestAnimationFrame(tick); })';
const measure = (id) => `(() => { const target = document.getElementById(${JSON.stringify(id)}); const header = document.querySelector('.header'); if (!header) return { missing: target ? '.header' : '.header와 #' + ${JSON.stringify(id)} + '(URL·서버 확인)' }; if (!target) return { missing: '#' + ${JSON.stringify(id)} + '(콘텐츠·DB 상태 확인)' }; return { targetTop: target.getBoundingClientRect().top, headerBottom: header.getBoundingClientRect().bottom, scrollY, maxScroll: document.documentElement.scrollHeight - innerHeight, hash: location.hash }; })()`;

// Chrome이 DevTools 주소를 알린 직후에는 첫 페이지 탭이 아직 목록에 없을 수 있다
async function pageTarget(port) {
    for (let attempt = 0; attempt < 50; attempt++) {
        const page = (await (await fetch(`http://127.0.0.1:${port}/json/list`)).json()).find((target) => target.type === 'page');
        if (page) return page;
        await new Promise((resolve) => setTimeout(resolve, 100));
    }
    throw new Error('Chrome 페이지 탭을 찾지 못했습니다');
}

(async () => {
    const port = await devtoolsPort;
    const page = await connect((await pageTarget(port)).webSocketDebuggerUrl);
    const evaluate = async (expression) => {
        const { result, exceptionDetails } = await page.send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
        if (exceptionDetails) throw new Error(`페이지 평가 실패: ${exceptionDetails.exception?.description || exceptionDetails.text}`);
        return result.value;
    };
    const exceptions = [];
    page.on('Runtime.exceptionThrown', ({ exceptionDetails }) => exceptions.push(exceptionDetails.exception?.description || exceptionDetails.text));
    await page.send('Page.enable');
    await page.send('Runtime.enable');
    const fontSizes = (scale) => page.send('Page.setFontSizes', { fontSizes: { standard: 16 * scale, fixed: 13 * scale } });
    // text를 주면 문자 입력까지 보내 Enter가 링크를 실행하게 한다
    const keyPress = async (key, code, keyCode, modifiers = 0, text) => {
        await page.send('Input.dispatchKeyEvent', { type: text ? 'keyDown' : 'rawKeyDown', key, code, windowsVirtualKeyCode: keyCode, modifiers, ...(text ? { text } : {}) });
        await page.send('Input.dispatchKeyEvent', { type: 'keyUp', key, code, windowsVirtualKeyCode: keyCode, modifiers });
    };

    const navigate = async (url) => {
        const loaded = page.once('Page.loadEventFired');
        const { errorText } = await page.send('Page.navigate', { url });
        if (errorText) throw new Error(`${url} 열기 실패: ${errorText}`);
        await loaded;
    };
    // 서버가 꺼졌거나 주소가 틀리면 오류 페이지를 재지 않도록 먼저 상태를 확인한다
    for (const pagePath of new Set([...cases.map((test) => test.path), '/about'].map((url) => url.split('#')[0]))) {
        const response = await fetch(base + pagePath, { headers: { 'user-agent': userAgent } });
        assert.equal(response.status, 200, `${base + pagePath}가 200을 반환해야 합니다(SITE_BASE_URL·서버 확인)`);
    }
    const noExceptions = (label) => assert.deepEqual(exceptions.splice(0), [], `${label}: 공개 페이지 스크립트에서 예외가 났습니다`);
    const openPage = async (width, scale, pagePath) => {
        // 앞 경우와 해시만 다른 주소면 같은 문서 안 이동이라 load 이벤트가 없다. 빈 페이지를 거쳐 매번 새로 연다.
        await navigate('about:blank');
        await page.send('Emulation.setDeviceMetricsOverride', { width, height: 800, deviceScaleFactor: 1, mobile: false });
        await fontSizes(scale);
        await navigate(base + pagePath);
        await evaluate(settle);
    };

    for (const test of cases) {
        await openPage(test.width, test.scale, test.path);
        if (test.thenScale) {
            await fontSizes(test.thenScale);
            await evaluate(settle);
            await evaluate(`location.hash = ${JSON.stringify(test.id)}`);
            await evaluate(settle);
        }
        const result = await evaluate(measure(test.id));
        noExceptions(test.label);
        assert.equal(result.missing, undefined, `${test.label}: ${result.missing} 요소가 없습니다`);
        assert.ok(result.scrollY > 0, `${test.label}: #${test.id}로 스크롤되지 않았습니다`);
        // 페이지 끝에 막혀 스크롤이 멈추면 대상 위치로 여백을 판단할 수 없다
        assert.ok(result.scrollY < result.maxScroll - 1, `${test.label}: 페이지 끝에 막혀 #${test.id} 위치를 잴 수 없습니다`);
        const gap = result.targetTop - result.headerBottom;
        // 섹션 윗선이 헤더 아랫선과 한 줄로 겹치도록 1px 덜 띄우므로(style.css) 1px까지는 겹침을 허용한다
        assert.ok(gap >= -1.5, `${test.label}: #${test.id} 윗부분 ${(-gap).toFixed(1)}px가 헤더 뒤에 가려집니다`);
        // 여백이 헤더 높이보다 크게 잡히는 회귀도 막는다
        assert.ok(gap <= 1.5, `${test.label}: #${test.id}가 헤더보다 ${gap.toFixed(1)}px 아래에 멈춥니다`);
    }

    // 건너뛰기 링크: Enter로 main으로 이동해도 본문 첫 부분이 헤더 뒤에 가려지지 않아야 한다
    for (const [width, scale] of [[375, 1], [800, 2]]) {
        const label = `${width}px · ${scale * 100}% · /about 건너뛰기 링크`;
        await openPage(width, scale, '/about');
        await evaluate(`document.querySelector('.skip-link').focus()`);
        await keyPress('Enter', 'Enter', 13, 0, '\r');
        await evaluate(settle);
        const result = await evaluate(measure('main-content'));
        noExceptions(label);
        assert.equal(result.missing, undefined, `${label}: ${result.missing} 요소가 없습니다`);
        assert.equal(result.hash, '#main-content', `${label}: 건너뛰기 링크가 실행되지 않았습니다`);
        const gap = result.targetTop - result.headerBottom;
        assert.ok(gap >= -1.5, `${label}: main 윗부분 ${(-gap).toFixed(1)}px가 헤더 뒤에 가려집니다`);
    }

    // 키보드: Shift+Tab으로 헤더 뒤에 있던 요소로 돌아가도 포커스 링(outline + offset) 전체가 헤더 아래에 보여야 한다
    // Value(문의 폼 행): protects=id가 있는 홈 문의 폼 칸(#inquiry_type 등)도 Shift+Tab 때 포커스 링이 헤더에 가려지지 않음;
    //   fails_when=포커스 규칙의 우선순위가 앵커 규칙(main [id], 헤더 높이 − 1px)보다 낮아져(:where 등) id 있는 칸이 1px 덜 띄워짐;
    //   why_new=/about 행의 대상 링크에는 id가 없어 두 scroll-margin 규칙이 겹치는 경우를 지나지 않음; seam=none;
    //   no_claim=제출 뒤 contact.js가 첫 오류 칸에 focus()하는 흐름(서버 응답 필요)은 재지 않음, Chrome만 측정
    const focusChecks = [
        { label: '800px · 200% · /about Shift+Tab', width: 800, scale: 2, path: '/about', scope: 'main' },
        { label: '375px · 100% · / 문의 폼 Shift+Tab', width: 375, scale: 1, path: '/', scope: '#contact-form' },
    ];
    for (const check of focusChecks) {
        await openPage(check.width, check.scale, check.path);
        const focusables = ['a[href]', 'button', 'input', 'select', 'textarea'].map((tag) => `${check.scope} ${tag}`).join(', ');
        const prepared = await evaluate(`(() => { const items = [...document.querySelectorAll(${JSON.stringify(focusables)})].filter((el) => el.offsetParent); if (items.length < 2) return { ready: false, reason: '포커스할 요소가 2개 미만' }; items[0].dataset.anchorTarget = '1'; items[1].focus({ preventScroll: true }); document.documentElement.style.scrollBehavior = 'auto'; const headerBottom = document.querySelector('.header').getBoundingClientRect().bottom; scrollTo(0, items[0].getBoundingClientRect().top + scrollY - headerBottom + 20); return { ready: items[0].getBoundingClientRect().top < headerBottom, reason: '앞 요소를 헤더 뒤로 보내지 못함' }; })()`);
        assert.ok(prepared.ready, `${check.label}: 준비 실패(${prepared.reason})`);
        await keyPress('Tab', 'Tab', 9, 8);
        await evaluate(settle);
        const ring = await evaluate(`(() => { const style = getComputedStyle(document.activeElement); return { isTarget: document.activeElement.dataset.anchorTarget === '1', ringTop: document.activeElement.getBoundingClientRect().top - (parseFloat(style.outlineWidth) || 0) - (parseFloat(style.outlineOffset) || 0), headerBottom: document.querySelector('.header').getBoundingClientRect().bottom }; })()`);
        noExceptions(check.label);
        assert.ok(ring.isTarget, `${check.label}: Shift+Tab이 헤더 뒤 요소로 이동하지 않았습니다`);
        const ringHidden = ring.headerBottom - ring.ringTop;
        assert.ok(ringHidden <= 0.5, `${check.label}: 포커스 링 윗부분 ${ringHidden.toFixed(1)}px가 헤더 뒤에 가려집니다`);
    }

    // 헤더 안 요소로 포커스가 가도 페이지가 튀지 않아야 한다(html에 scroll-padding을 걸면 헤더도 가려진 영역으로 봐서 튄다)
    const headerFocusChecks = [
        { label: '1440px · 100% · /about Shift+Tab으로 헤더 메뉴', width: 1440, prepare: `[...document.querySelectorAll('main a[href], main button')].filter((el) => el.offsetParent)[0].focus({ preventScroll: true })`, key: ['Tab', 'Tab', 9, 8], expect: `!!document.activeElement.closest('.header')` },
        { label: '375px · 100% · /about 모바일 메뉴 Escape', width: 375, prepare: `document.querySelector('.mobile-menu-btn').click(); document.querySelector('#nav a').focus()`, key: ['Escape', 'Escape', 27], expect: `document.activeElement === document.querySelector('.mobile-menu-btn')` },
    ];
    for (const check of headerFocusChecks) {
        await openPage(check.width, 1, '/about');
        const before = await evaluate(`(() => { document.documentElement.style.scrollBehavior = 'auto'; scrollTo(0, 1200); ${check.prepare}; return scrollY; })()`);
        assert.ok(before > 300, `${check.label}: 준비 실패(페이지가 짧아 스크롤하지 못함)`);
        await keyPress(...check.key);
        await evaluate(settle);
        const after = await evaluate(`({ scrollY, moved: ${check.expect} })`);
        noExceptions(check.label);
        assert.ok(after.moved, `${check.label}: 포커스가 기대한 헤더 요소로 가지 않았습니다`);
        assert.ok(Math.abs(after.scrollY - before) <= 1, `${check.label}: 페이지가 ${(before - after.scrollY).toFixed(0)}px 움직였습니다`);
    }
    page.close();
})().then(() => { clearTimeout(watchdog); return cleanup(); }, (error) => { console.error(error); clearTimeout(watchdog); return cleanup().finally(() => process.exit(1)); });
