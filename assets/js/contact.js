(() => {
    const form = document.getElementById('contact-form');
    if (!form) return;
    const result = document.getElementById('contact-result');
    const button = form.querySelector('button[type="submit"]');
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (button.disabled) return;
        button.disabled = true;
        result.textContent = '문의 접수 중…';
        form.querySelectorAll('[aria-invalid]').forEach(field => { field.removeAttribute('aria-invalid'); field.removeAttribute('aria-describedby'); });
        form.querySelectorAll('.contact-field-error').forEach(el => el.remove());
        try {
            const response = await fetch(form.action, {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(Object.fromEntries(new FormData(form)))
            });
            const data = await response.json();
            result.textContent = data.message || '접수 결과를 확인하지 못했습니다. 다시 확인해 주세요.';
            for (const [key, message] of Object.entries(data.errors || {})) {
                const field = form.elements.namedItem(key);
                if (!field) continue;
                const error = document.createElement('p');
                error.id = `${key}-error`; error.className = 'contact-field-error'; error.textContent = message;
                field.setAttribute('aria-invalid', 'true'); field.setAttribute('aria-describedby', error.id);
                field.after(error);
            }
            if (response.ok && data.success === true) form.reset();
            const first = form.querySelector('[aria-invalid="true"]');
            (first || result).focus();
        } catch (error) {
            result.textContent = '접수 결과를 확인하지 못했습니다. 입력 내용은 유지됩니다. 잠시 후 다시 확인하거나 admin@younglabor.kr로 문의해 주세요.';
            result.focus();
        } finally { button.disabled = false; }
    });
})();
