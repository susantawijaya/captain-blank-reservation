document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const targetId = button.getAttribute('data-password-target');
        const input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            return;
        }

        const showIcon = button.querySelector('[data-password-icon="show"]');
        const hideIcon = button.querySelector('[data-password-icon="hide"]');

        const syncToggleState = () => {
            const isVisible = input.type === 'text';

            button.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
            button.setAttribute('aria-label', isVisible ? 'Sembunyikan password' : 'Tampilkan password');

            showIcon?.classList.toggle('hidden', isVisible);
            hideIcon?.classList.toggle('hidden', !isVisible);
        };

        button.addEventListener('click', () => {
            if (input.disabled) {
                return;
            }

            input.type = input.type === 'password' ? 'text' : 'password';
            syncToggleState();
        });

        syncToggleState();
    });

    document.querySelectorAll('[data-password-group]').forEach((group) => {
        const passwordInput = group.querySelector('[data-password-input]');
        const confirmationInput = group.querySelector('[data-password-confirmation]');
        const message = group.querySelector('[data-password-message]');

        if (!passwordInput || !confirmationInput || !message) {
            return;
        }

        const basePlaceholder = confirmationInput.getAttribute('placeholder') || '';

        const syncConfirmationState = () => {
            const hasPassword = passwordInput.value.length > 0;
            const hasConfirmation = confirmationInput.value.length > 0;
            const isMismatch = hasPassword && hasConfirmation && passwordInput.value !== confirmationInput.value;

            confirmationInput.disabled = !hasPassword;
            confirmationInput.placeholder = hasPassword ? basePlaceholder : 'Isi password terlebih dahulu';
            confirmationInput.setCustomValidity(isMismatch ? 'Password yang Anda masukkan tidak sesuai.' : '');
            confirmationInput.setAttribute('aria-invalid', isMismatch ? 'true' : 'false');
            message.classList.toggle('hidden', !isMismatch);

            if (!hasPassword) {
                confirmationInput.value = '';
                confirmationInput.setCustomValidity('');
                confirmationInput.setAttribute('aria-invalid', 'false');
                message.classList.add('hidden');
            }
        };

        passwordInput.addEventListener('input', syncConfirmationState);
        confirmationInput.addEventListener('input', syncConfirmationState);

        syncConfirmationState();
    });
});
