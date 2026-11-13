/**
 * Global zero-clear and restore handler for numeric and quantity inputs.
 *
 * Behavior:
 * - When an input containing zero (e.g. "0", "0.0", "0.000") is clicked/focused,
 *   it is cleared so the user can immediately type a value without backspacing.
 * - If left empty and the user clicks elsewhere (or blurs), it automatically restores
 *   the zero value (preserving its original format, e.g. "0" or "0.000").
 * - Form submission safely restores any active empty cleared input before submit.
 */

export function isZero(val) {
    if (val === null || val === undefined) return false;
    const s = String(val).trim();
    if (s === '') return false;
    return /^0+(\.0+)?$/.test(s);
}

export function isZeroEligibleInput(el) {
    if (!el || el.tagName !== 'INPUT') return false;
    if (el.readOnly || el.disabled) return false;

    const type = (el.type || 'text').toLowerCase();
    const excludedTypes = [
        'checkbox', 'radio', 'hidden', 'submit', 'button', 'reset',
        'file', 'image', 'color', 'date', 'datetime-local', 'month',
        'week', 'time', 'search', 'password',
    ];
    if (excludedTypes.includes(type)) return false;

    const name = (el.name || '').toLowerCase();
    const id = (el.id || '').toLowerCase();
    if (name.includes('name') || name.includes('note') || name.includes('address') ||
        name.includes('email') || name.includes('password') || name.includes('phone') ||
        name.includes('search') || name === 'q' || id.includes('search') || id === 'q') {
        return false;
    }

    return true;
}

export function clearIfZero(input) {
    if (!isZeroEligibleInput(input)) return;
    if (isZero(input.value)) {
        input.dataset.zeroCleared = input.value;
        if (input.hasAttribute('placeholder')) {
            input.dataset.savedPlaceholder = input.getAttribute('placeholder');
            input.removeAttribute('placeholder');
        }
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

export function restoreIfEmpty(input) {
    if (!isZeroEligibleInput(input)) return;

    if (input.dataset.savedPlaceholder !== undefined) {
        input.setAttribute('placeholder', input.dataset.savedPlaceholder);
        delete input.dataset.savedPlaceholder;
    }

    if (String(input.value ?? '').trim() === '') {
        const restored = input.dataset.zeroCleared ?? (input.type === 'number' ? '0' : null);
        delete input.dataset.zeroCleared;
        if (restored !== null) {
            input.value = restored;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    } else {
        delete input.dataset.zeroCleared;
    }
}

export function registerZeroClear() {
    if (typeof document === 'undefined') return;

    // Use focusin to capture both mouse click and keyboard Tab navigation
    document.addEventListener('focusin', (e) => {
        clearIfZero(e.target);
    }, true);

    // Also handle click in case the element was already focused when clicked
    document.addEventListener('click', (e) => {
        clearIfZero(e.target);
    }, true);

    // On blur / focusout, if left empty, restore zero
    document.addEventListener('focusout', (e) => {
        restoreIfEmpty(e.target);
    }, true);

    // Before form submit, ensure any active empty cleared input is restored
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form || !form.querySelectorAll) return;
        form.querySelectorAll('input').forEach(input => {
            if (input.dataset.zeroCleared !== undefined && String(input.value ?? '').trim() === '') {
                restoreIfEmpty(input);
            }
        });
    }, true);
}

