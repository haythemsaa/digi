/**
 * Theme Switcher
 * Handles dark/light mode toggle with localStorage persistence
 *
 * @author Pakiparc Team
 * @version 1.0
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'pakiparc-theme';
    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';

    class ThemeSwitcher {
        constructor() {
            this.currentTheme = this.getStoredTheme() || this.getPreferredTheme();
            this.init();
        }

        /**
         * Initialize theme switcher
         */
        init() {
            // Apply theme immediately to avoid flash
            this.applyTheme(this.currentTheme);

            // Create toggle button
            this.createToggleButton();

            // Listen for system theme changes
            this.watchSystemTheme();

            // Remove no-transition class after initial load
            setTimeout(() => {
                document.body.classList.remove('no-transition');
            }, 100);
        }

        /**
         * Get stored theme from localStorage
         */
        getStoredTheme() {
            return localStorage.getItem(STORAGE_KEY);
        }

        /**
         * Get preferred theme from system
         */
        getPreferredTheme() {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return THEME_DARK;
            }
            return THEME_LIGHT;
        }

        /**
         * Apply theme to document
         */
        applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            this.currentTheme = theme;
            localStorage.setItem(STORAGE_KEY, theme);

            // Update toggle button icon
            this.updateToggleIcon();

            // Dispatch custom event
            window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));

            // Update Chart.js if present
            this.updateCharts();
        }

        /**
         * Toggle between light and dark theme
         */
        toggleTheme() {
            const newTheme = this.currentTheme === THEME_DARK ? THEME_LIGHT : THEME_DARK;
            this.applyTheme(newTheme);
        }

        /**
         * Create theme toggle button
         */
        createToggleButton() {
            // Check if button already exists
            if (document.querySelector('.theme-toggle')) {
                return;
            }

            const button = document.createElement('button');
            button.className = 'theme-toggle';
            button.setAttribute('aria-label', 'Toggle theme');
            button.innerHTML = '<i class="fas fa-moon"></i>';

            button.addEventListener('click', () => {
                this.toggleTheme();
                // Add rotation animation
                button.querySelector('i').style.transform = 'rotate(360deg)';
                setTimeout(() => {
                    button.querySelector('i').style.transform = '';
                }, 300);
            });

            document.body.appendChild(button);
        }

        /**
         * Update toggle button icon
         */
        updateToggleIcon() {
            const button = document.querySelector('.theme-toggle i');
            if (button) {
                button.className = this.currentTheme === THEME_DARK ? 'fas fa-sun' : 'fas fa-moon';
            }
        }

        /**
         * Watch for system theme changes
         */
        watchSystemTheme() {
            if (!window.matchMedia) return;

            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');

            darkModeQuery.addEventListener('change', (e) => {
                // Only auto-switch if user hasn't manually set a preference
                if (!this.getStoredTheme()) {
                    this.applyTheme(e.matches ? THEME_DARK : THEME_LIGHT);
                }
            });
        }

        /**
         * Update Chart.js charts for dark theme
         */
        updateCharts() {
            if (typeof Chart === 'undefined') return;

            const isDark = this.currentTheme === THEME_DARK;

            // Set Chart.js global defaults for dark theme
            if (Chart.defaults) {
                Chart.defaults.color = isDark ? '#e4e6eb' : '#666';
                Chart.defaults.borderColor = isDark ? '#3a3f47' : '#ddd';

                if (Chart.defaults.plugins && Chart.defaults.plugins.legend) {
                    Chart.defaults.plugins.legend.labels.color = isDark ? '#e4e6eb' : '#666';
                }
            }

            // Update existing charts
            if (Chart.instances) {
                Chart.instances.forEach(chart => {
                    chart.update();
                });
            }
        }

        /**
         * Get current theme
         */
        getCurrentTheme() {
            return this.currentTheme;
        }

        /**
         * Check if dark mode is active
         */
        isDarkMode() {
            return this.currentTheme === THEME_DARK;
        }
    }

    // Initialize theme switcher when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.themeSwitcher = new ThemeSwitcher();
        });
    } else {
        window.themeSwitcher = new ThemeSwitcher();
    }

    // Expose API
    window.PakiparcTheme = {
        toggle: () => window.themeSwitcher.toggleTheme(),
        setTheme: (theme) => window.themeSwitcher.applyTheme(theme),
        getTheme: () => window.themeSwitcher.getCurrentTheme(),
        isDark: () => window.themeSwitcher.isDarkMode()
    };

})();

/**
 * Usage examples:
 *
 * // Toggle theme programmatically
 * PakiparcTheme.toggle();
 *
 * // Set specific theme
 * PakiparcTheme.setTheme('dark');
 * PakiparcTheme.setTheme('light');
 *
 * // Get current theme
 * const currentTheme = PakiparcTheme.getTheme();
 *
 * // Check if dark mode
 * if (PakiparcTheme.isDark()) {
 *     console.log('Dark mode is active');
 * }
 *
 * // Listen for theme changes
 * window.addEventListener('themechange', (e) => {
 *     console.log('Theme changed to:', e.detail.theme);
 * });
 */
