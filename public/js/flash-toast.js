document.addEventListener('DOMContentLoaded', () => {
    const removeToast = (toast) => {
        if (!toast || toast.dataset.flashRemoved === 'true') {
            return;
        }

        toast.dataset.flashRemoved = 'true';
        toast.classList.add('is-hiding');

        window.setTimeout(() => {
            const stack = toast.parentElement;
            toast.remove();

            if (stack && !stack.querySelector('[data-flash-toast]')) {
                stack.remove();
            }
        }, 220);
    };

    document.querySelectorAll('[data-flash-toast]').forEach((toast) => {
        const timeout = Number(toast.getAttribute('data-flash-timeout') || 5000);
        let timerId = window.setTimeout(() => removeToast(toast), timeout);

        toast.querySelector('[data-flash-close]')?.addEventListener('click', () => {
            window.clearTimeout(timerId);
            removeToast(toast);
        });

        toast.addEventListener('mouseenter', () => {
            window.clearTimeout(timerId);
        });

        toast.addEventListener('mouseleave', () => {
            timerId = window.setTimeout(() => removeToast(toast), 1800);
        });
    });
});
