document.documentElement.classList.add('js-ready');

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const syncBodyLock = () => {
        const shouldLock = document.querySelector('.navbar.is-open, .admin-shell.is-sidebar-open') !== null;
        body.classList.toggle('ui-locked', shouldLock);
    };

    const closeOnEscape = (event, closeCallbacks) => {
        if (event.key !== 'Escape') {
            return;
        }

        closeCallbacks.forEach((callback) => callback());
    };

    const bindMediaReset = (mediaQuery, reset) => {
        const handler = (event) => {
            if (event.matches) {
                reset();
            }
        };

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', handler);
            return;
        }

        mediaQuery.addListener(handler);
    };

    const navbar = document.querySelector('.navbar');
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navPanel = document.querySelector('[data-nav-panel]');
    const desktopNavMedia = window.matchMedia('(min-width: 901px)');

    const closeNavbar = () => {
        if (!navbar || !navToggle) {
            return;
        }

        navbar.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
        syncBodyLock();
    };

    const openNavbar = () => {
        if (!navbar || !navToggle) {
            return;
        }

        navbar.classList.add('is-open');
        navToggle.setAttribute('aria-expanded', 'true');
        syncBodyLock();
    };

    if (navbar && navToggle && navPanel) {
        navToggle.addEventListener('click', () => {
            if (navbar.classList.contains('is-open')) {
                closeNavbar();
                return;
            }

            openNavbar();
        });

        navPanel.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 900) {
                    closeNavbar();
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!navbar.classList.contains('is-open')) {
                return;
            }

            if (navbar.contains(event.target)) {
                return;
            }

            closeNavbar();
        });

        bindMediaReset(desktopNavMedia, closeNavbar);
    }

    const adminShell = document.querySelector('[data-admin-shell]');
    const adminSidebar = adminShell?.querySelector('[data-admin-sidebar]');
    const adminOpenButton = adminShell?.querySelector('[data-admin-sidebar-toggle]');
    const adminCloseButton = adminShell?.querySelector('[data-admin-sidebar-close]');
    const adminBackdrop = adminShell?.querySelector('[data-admin-sidebar-backdrop]');
    const desktopAdminMedia = window.matchMedia('(min-width: 1101px)');

    const closeAdminSidebar = () => {
        if (!adminShell || !adminOpenButton) {
            return;
        }

        adminShell.classList.remove('is-sidebar-open');
        adminOpenButton.setAttribute('aria-expanded', 'false');
        syncBodyLock();
    };

    const openAdminSidebar = () => {
        if (!adminShell || !adminOpenButton) {
            return;
        }

        adminShell.classList.add('is-sidebar-open');
        adminOpenButton.setAttribute('aria-expanded', 'true');
        syncBodyLock();
    };

    if (adminShell && adminSidebar && adminOpenButton) {
        adminOpenButton.addEventListener('click', () => {
            if (adminShell.classList.contains('is-sidebar-open')) {
                closeAdminSidebar();
                return;
            }

            openAdminSidebar();
        });

        adminCloseButton?.addEventListener('click', closeAdminSidebar);
        adminBackdrop?.addEventListener('click', closeAdminSidebar);

        adminSidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 1100) {
                    closeAdminSidebar();
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!adminShell.classList.contains('is-sidebar-open')) {
                return;
            }

            if (adminSidebar.contains(event.target) || adminOpenButton.contains(event.target)) {
                return;
            }

            closeAdminSidebar();
        });

        bindMediaReset(desktopAdminMedia, closeAdminSidebar);
    }

    document.addEventListener('keydown', (event) => {
        closeOnEscape(event, [closeNavbar, closeAdminSidebar]);
    });
});
