(function () {
    const STORAGE_KEY = 'midconnect-theme';

    function bindThemeButton(button) {
        if (!button || button.dataset.themeBound === 'true') {
            return;
        }

        button.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(nextTheme(current));
        });

        button.dataset.themeBound = 'true';
    }

    function getPreferredTheme() {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);
        updateToggleButtons(theme);
    }

    function nextTheme(currentTheme) {
        return currentTheme === 'dark' ? 'light' : 'dark';
    }

    function createThemeButton() {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'theme-toggle-btn';
        button.setAttribute('aria-label', 'Toggle dark and light theme');
        button.innerHTML = '<span aria-hidden="true">🌙</span><span>Dark Mode</span>';

        bindThemeButton(button);

        return button;
    }

    function hydrateExistingButtons() {
        document.querySelectorAll('.theme-toggle-btn').forEach(function (button) {
            bindThemeButton(button);
        });
    }

    function updateToggleButtons(theme) {
        const isDark = theme === 'dark';
        const label = isDark ? 'Light Mode' : 'Dark Mode';
        const icon = isDark ? '☀' : '🌙';

        document.querySelectorAll('.theme-toggle-btn').forEach(function (button) {
            button.innerHTML = '<span aria-hidden="true">' + icon + '</span><span>' + label + '</span>';
            button.setAttribute('title', label);
            button.setAttribute('aria-pressed', String(isDark));
        });
    }

    function mountThemeToggle() {
        if (document.querySelector('.theme-toggle-btn')) {
            return;
        }

        const navMenu = document.querySelector('.nav-menu');
        if (navMenu) {
            const item = document.createElement('li');
            item.className = 'theme-toggle-nav-item';
            item.appendChild(createThemeButton());
            navMenu.appendChild(item);
            return;
        }

        const floating = createThemeButton();
        floating.classList.add('theme-toggle-floating');
        document.body.appendChild(floating);
    }

    document.addEventListener('DOMContentLoaded', function () {
        hydrateExistingButtons();
        mountThemeToggle();
        applyTheme(getPreferredTheme());
    });
})();
