/**
 * SABIRA POSKESTREN Health — Anti-Flicker & Theme Switcher Logic
 * Standardized for Light, Dark, and System modes.
 */

(function () {
    const THEME_KEY = 'sabira_theme_preference';

    function getStoredTheme() {
        return localStorage.getItem(THEME_KEY) || 'system';
    }

    function getSystemPreference() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        const effectiveTheme = theme === 'system' ? getSystemPreference() : theme;
        if (effectiveTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        document.documentElement.setAttribute('data-theme', theme);
    }

    // Initialize immediately before first paint
    const currentTheme = getStoredTheme();
    applyTheme(currentTheme);

    // Expose global controller
    window.SabiraTheme = {
        getTheme: function () {
            return getStoredTheme();
        },
        setTheme: function (theme) {
            if (['light', 'dark', 'system'].includes(theme)) {
                localStorage.setItem(THEME_KEY, theme);
                applyTheme(theme);
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: theme } }));
            }
        },
        applyTheme: applyTheme
    };

    // React to OS system changes if set to 'system'
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        if (getStoredTheme() === 'system') {
            applyTheme('system');
        }
    });
})();
