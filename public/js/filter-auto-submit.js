document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-auto-filter-form]').forEach((form) => {
        const delay = Number(form.getAttribute('data-auto-submit-delay') || 300);
        let timerId = null;

        const scheduleSubmit = () => {
            window.clearTimeout(timerId);
            timerId = window.setTimeout(() => {
                form.requestSubmit();
            }, delay);
        };

        form.querySelectorAll('[data-auto-filter-input]').forEach((input) => {
            input.addEventListener('input', scheduleSubmit);
        });

        form.querySelectorAll('[data-auto-filter-change]').forEach((input) => {
            input.addEventListener('change', () => {
                window.clearTimeout(timerId);
                form.requestSubmit();
            });
        });
    });
});
