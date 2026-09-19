const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(
    path.join(__dirname, '..', 'admin', 'contacts.php'),
    'utf8'
);
const functionStart = source.indexOf('function openDetail(id)');
const functionEnd = source.indexOf('function closeModal()', functionStart);

assert.notEqual(functionStart, -1, 'openDetail 함수가 있어야 합니다.');
assert.notEqual(functionEnd, -1, 'openDetail 함수 끝을 찾을 수 있어야 합니다.');

const email = '"onmouseover=alert(1)//"@example.com';
const modalBody = {
    value: '',
    set innerHTML(value) {
        this.value = value;
    },
    get innerHTML() {
        return this.value;
    },
};
const emailLink = { textContent: '', href: '' };
const elements = {
    modalTitle: { textContent: '' },
    modalBody,
    inquiryEmailLink: emailLink,
    detailModal: { classList: { add() {} } },
};
const document = {
    createElement() {
        return {
            value: '',
            set textContent(value) {
                this.value = String(value ?? '');
            },
            get innerHTML() {
                return this.value
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;');
            },
        };
    },
    getElementById(id) {
        return elements[id] ?? null;
    },
};
const context = {
    document,
    markAs() {},
};
const openDetailSource = source.slice(functionStart, functionEnd);

vm.runInNewContext(`
    const inqData = ${JSON.stringify([{
        id: 1,
        name: '감사 테스트',
        email,
        phone: '',
        subject: '',
        message: '문의',
        status: 'processing',
        admin_reply: '',
        ip_address: '',
        created_at: '2026-09-19 00:00:00',
    }])};
    ${openDetailSource}
`, context);

context.openDetail(1);

assert.ok(
    !modalBody.innerHTML.includes('onmouseover='),
    '사용자 이메일이 modal HTML 속성으로 삽입되면 안 됩니다.'
);
assert.equal(emailLink.textContent, email, '이메일은 링크 텍스트로 표시되어야 합니다.');
assert.equal(emailLink.href, `mailto:${email}`, '메일 링크는 DOM 속성으로 설정되어야 합니다.');

console.log('admin contact email is rendered without HTML attribute injection');
