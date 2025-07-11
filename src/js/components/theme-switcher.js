/**
 * Theme Switcher Component
 * Handles dark/light mode switching functionality
 * Consolidates: theme-switcher.js
 */

import { getCookie, setCookie, addEvent } from '../core/utils.js';

export class ThemeSwitcher {
    constructor(core) {
        this.core = core;
        this.elements = {};
        this.events = [];
        this.currentTheme = 'dark'; // Default theme
    }

    /**
     * Initialize theme switcher
     */
    init() {
        console.log('ThemeSwitcher: Initializing...');
        
        // Cache DOM elements
        this.cacheElements();
        
        // Apply initial theme
        this.applyInitialTheme();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Listen for system preference changes
        this.setupSystemPreferenceListener();
        
        // Register with core
        this.core.registerComponent('themeSwitcher', this);
        
        console.log('ThemeSwitcher: Initialization complete');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            toggleButtons: document.querySelectorAll('.ph-nav__theme-toggle, #theme-toggle'),
            body: document.body,
            html: document.documentElement
        };
    }

    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Theme toggle button clicks
        this.elements.toggleButtons.forEach(button => {
            this.events.push(
                addEvent(button, 'click', this.handleThemeToggle.bind(this))
            );
        });
    }

    /**
     * Handle theme toggle button click
     * @param {Event} e - Click event
     */
    handleThemeToggle(e) {
        e.preventDefault();
        
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
        
        this.core.emit('theme:changed', {
            from: this.currentTheme === 'dark' ? 'light' : 'dark',
            to: newTheme
        });
    }

    /**
     * Apply initial theme based on cookie or system preference
     */
    applyInitialTheme() {
        const cookieTheme = getCookie('dark_mode');
        let initialTheme = 'dark'; // Default to dark mode

        if (cookieTheme) {
            initialTheme = cookieTheme;
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            initialTheme = 'light'; // Respect system light mode if no cookie
        }

        this.setTheme(initialTheme);
    }

    /**
     * Set theme and update UI
     * @param {string} theme - Theme name ('dark' or 'light')
     */
    setTheme(theme) {
        const validThemes = ['dark', 'light'];
        if (!validThemes.includes(theme)) {
            console.warn(`Invalid theme: ${theme}. Using 'dark' as fallback.`);
            theme = 'dark';
        }

        this.currentTheme = theme;

        // Update HTML element attribute (primary selector for CSS)
        this.elements.html.setAttribute('data-current-mode', theme);
        
        // Update body attributes for backward compatibility
        this.elements.body.setAttribute('data-theme', theme);
        this.elements.body.classList.toggle('dark-mode', theme === 'dark');

        // Update toggle button states
        this.updateToggleButtons(theme);

        // Save preference to cookie (30 days expiration)
        setCookie('dark_mode', theme, 30);

        // Emit theme change event
        this.core.emit('theme:applied', { theme });

        if (this.core.config.debug) {
            console.log(`Theme set to: ${theme}`);
        }
    }

    /**
     * Update toggle button states and icons
     * @param {string} theme - Current theme
     */
    updateToggleButtons(theme) {
        this.elements.toggleButtons.forEach(button => {
            // Update data attribute
            button.setAttribute('data-current-mode', theme);
            
            // Update icons if they exist
            const sunIcon = button.querySelector('.theme-icon--sun');
            const moonIcon = button.querySelector('.theme-icon--moon');
            
            if (sunIcon && moonIcon) {
                if (theme === 'light') {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                } else {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                }
            }

            // Update button text if it contains text
            const buttonText = button.querySelector('.theme-toggle-text');
            if (buttonText) {
                buttonText.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            }
        });
    }

    /**
     * Get current theme
     * @returns {string} Current theme name
     */
    getCurrentTheme() {
        return this.currentTheme;
    }

    /**
     * Toggle theme (public method)
     */
    toggle() {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    /**
     * Set up system preference change listener
     */
    setupSystemPreferenceListener() {
        if (window.matchMedia) {
            const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            const handleSystemPreferenceChange = (e) => {
                // Only apply system preference if user hasn't set a preference (no cookie)
                if (!getCookie('dark_mode')) {
                    const systemTheme = e.matches ? 'dark' : 'light';
                    this.setTheme(systemTheme);
                    
                    this.core.emit('theme:system-preference-changed', {
                        systemPreference: systemTheme
                    });
                }
            };

            // Modern browsers
            if (darkModeMediaQuery.addEventListener) {
                darkModeMediaQuery.addEventListener('change', handleSystemPreferenceChange);
            } else {
                // Fallback for older browsers
                darkModeMediaQuery.addListener(handleSystemPreferenceChange);
            }

            // Store reference for cleanup
            this.systemPreferenceQuery = darkModeMediaQuery;
            this.systemPreferenceHandler = handleSystemPreferenceChange;
        }
    }

    /**
     * Force theme without saving to cookie (for temporary changes)
     * @param {string} theme - Theme to apply temporarily
     */
    forceTheme(theme) {
        const oldTheme = this.currentTheme;
        this.currentTheme = theme;

        // Update UI without saving cookie
        this.elements.html.setAttribute('data-current-mode', theme);
        this.elements.body.setAttribute('data-theme', theme);
        this.elements.body.classList.toggle('dark-mode', theme === 'dark');
        this.updateToggleButtons(theme);

        this.core.emit('theme:forced', { from: oldTheme, to: theme });
    }

    /**
     * Reset theme to system preference or default
     */
    resetToDefault() {
        // Clear cookie
        setCookie('dark_mode', '', -1);
        
        // Apply system preference or default
        let defaultTheme = 'dark';
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            defaultTheme = 'light';
        }
        
        this.setTheme(defaultTheme);
        this.core.emit('theme:reset', { theme: defaultTheme });
    }

    /**
     * Check if dark mode is enabled
     * @returns {boolean} True if dark mode is active
     */
    isDarkMode() {
        return this.currentTheme === 'dark';
    }

    /**
     * Check if light mode is enabled
     * @returns {boolean} True if light mode is active
     */
    isLightMode() {
        return this.currentTheme === 'light';
    }

    /**
     * Get system color scheme preference
     * @returns {string} System preference ('dark', 'light', or 'no-preference')
     */
    getSystemPreference() {
        if (!window.matchMedia) {
            return 'no-preference';
        }
        
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
            return 'light';
        }
        
        return 'no-preference';
    }

    /**
     * Cleanup method
     */
    cleanup() {
        // Remove event listeners
        this.events.forEach(cleanup => cleanup());
        this.events = [];

        // Remove system preference listener
        if (this.systemPreferenceQuery && this.systemPreferenceHandler) {
            if (this.systemPreferenceQuery.removeEventListener) {
                this.systemPreferenceQuery.removeEventListener('change', this.systemPreferenceHandler);
            } else {
                // Fallback for older browsers
                this.systemPreferenceQuery.removeListener(this.systemPreferenceHandler);
            }
        }

        console.log('ThemeSwitcher: Cleaned up');
    }
}
