/**
 * CustomTube Core
 * Main initialization and global configuration
 */

import { AjaxHandler } from './ajax.js';
import { debounce, throttle } from './utils.js';

export default class CustomTubeCore {
    constructor() {
        this.config = {
            ajaxUrl: window.customtubeData?.ajaxurl || '/wp-admin/admin-ajax.php',
            nonce: window.customtubeData?.nonce || '',
            debug: window.customtubeData?.debug || false,
            version: window.customtubeData?.css_version || '1.0.0'
        };

        // Initialize AJAX handler
        this.ajax = new AjaxHandler(this.config);
        
        // Global state
        this.state = {
            isInitialized: false,
            currentPage: this.getCurrentPage(),
            isMobile: this.isMobileDevice(),
            components: new Map()
        };

        // Event emitter for inter-component communication
        this.events = new EventTarget();
    }

    /**
     * Initialize the core system
     */
    init() {
        if (this.state.isInitialized) {
            console.warn('CustomTube: Already initialized');
            return;
        }

        console.log('CustomTube Core: Initializing...');

        // Set up global error handling
        this.setupErrorHandling();

        // Set up performance monitoring if debug mode is on
        if (this.config.debug) {
            this.setupPerformanceMonitoring();
        }

        // Add global utility methods to window for backward compatibility
        this.setupGlobalMethods();

        // Set up responsive breakpoint detection
        this.setupResponsiveDetection();

        // Mark as initialized
        this.state.isInitialized = true;

        // Emit initialization complete event
        this.emit('core:initialized', this.state);

        console.log('CustomTube Core: Initialization complete');
    }

    /**
     * Register a component with the core system
     * @param {string} name - Component name
     * @param {Object} component - Component instance
     */
    registerComponent(name, component) {
        this.state.components.set(name, component);
        this.emit('component:registered', { name, component });
    }

    /**
     * Get a registered component
     * @param {string} name - Component name
     * @returns {Object|null} Component instance or null if not found
     */
    getComponent(name) {
        return this.state.components.get(name) || null;
    }

    /**
     * Event emitter methods
     */
    emit(event, data = null) {
        this.events.dispatchEvent(new CustomEvent(event, { detail: data }));
    }

    on(event, callback) {
        this.events.addEventListener(event, callback);
    }

    off(event, callback) {
        this.events.removeEventListener(event, callback);
    }

    /**
     * Get current page context
     * @returns {string} Page context identifier
     */
    getCurrentPage() {
        const body = document.body;
        
        // Check for specific page templates
        if (body.classList.contains('home')) return 'home';
        if (body.classList.contains('single-video')) return 'video-single';
        if (body.classList.contains('page-template-performers')) return 'performers';
        if (body.classList.contains('page-template-login')) return 'login';
        if (body.classList.contains('page-template-register')) return 'register';
        if (body.classList.contains('page-template-likedvideos')) return 'liked-videos';
        if (body.classList.contains('page-template-shorties')) return 'short-videos';
        if (body.classList.contains('archive')) return 'archive';
        if (body.classList.contains('search')) return 'search';
        
        return 'default';
    }

    /**
     * Detect if current device is mobile
     * @returns {boolean} True if mobile device
     */
    isMobileDevice() {
        return window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Set up global error handling
     */
    setupErrorHandling() {
        window.addEventListener('error', (event) => {
            if (this.config.debug) {
                console.error('CustomTube Global Error:', {
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    error: event.error
                });
            }
            
            this.emit('error:global', {
                type: 'javascript',
                message: event.message,
                source: event.filename,
                line: event.lineno,
                column: event.colno
            });
        });

        window.addEventListener('unhandledrejection', (event) => {
            if (this.config.debug) {
                console.error('CustomTube Unhandled Promise Rejection:', event.reason);
            }
            
            this.emit('error:promise', {
                type: 'promise',
                reason: event.reason
            });
        });
    }

    /**
     * Set up performance monitoring for debug mode
     */
    setupPerformanceMonitoring() {
        // Monitor page load performance
        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                console.log('CustomTube Performance:', {
                    domContentLoaded: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
                    loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
                    totalLoadTime: perfData.loadEventEnd - perfData.fetchStart
                });
            }, 0);
        });

        // Monitor resource loading
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.name.includes('customtube') || entry.name.includes('.js') || entry.name.includes('.css')) {
                    console.log(`Resource loaded: ${entry.name} (${Math.round(entry.duration)}ms)`);
                }
            }
        });
        observer.observe({ entryTypes: ['resource'] });
    }

    /**
     * Set up global utility methods for backward compatibility
     */
    setupGlobalMethods() {
        // Global debounce and throttle for legacy code
        window.customtubeDebounce = debounce;
        window.customtubeThrottle = throttle;
        
        // Global AJAX access
        window.customtubeAjax = this.ajax;
        
        // Global event system access
        window.customtubeEvents = {
            emit: this.emit.bind(this),
            on: this.on.bind(this),
            off: this.off.bind(this)
        };
    }

    /**
     * Set up responsive breakpoint detection
     */
    setupResponsiveDetection() {
        const updateBreakpoint = () => {
            const width = window.innerWidth;
            const oldBreakpoint = this.state.currentBreakpoint;
            
            if (width < 576) {
                this.state.currentBreakpoint = 'xs';
            } else if (width < 768) {
                this.state.currentBreakpoint = 'sm';
            } else if (width < 992) {
                this.state.currentBreakpoint = 'md';
            } else if (width < 1200) {
                this.state.currentBreakpoint = 'lg';
            } else {
                this.state.currentBreakpoint = 'xl';
            }
            
            // Update mobile state
            this.state.isMobile = width <= 768;
            
            // Emit breakpoint change event if it changed
            if (oldBreakpoint !== this.state.currentBreakpoint) {
                this.emit('breakpoint:changed', {
                    from: oldBreakpoint,
                    to: this.state.currentBreakpoint,
                    width: width,
                    isMobile: this.state.isMobile
                });
            }
        };

        // Initial detection
        updateBreakpoint();

        // Listen for resize events
        window.addEventListener('resize', debounce(updateBreakpoint, 100));
    }

    /**
     * Utility method to wait for DOM elements
     * @param {string} selector - CSS selector
     * @param {number} timeout - Timeout in milliseconds
     * @returns {Promise<Element>} Promise resolving to element
     */
    waitForElement(selector, timeout = 10000) {
        return new Promise((resolve, reject) => {
            const element = document.querySelector(selector);
            if (element) {
                resolve(element);
                return;
            }

            const observer = new MutationObserver(() => {
                const element = document.querySelector(selector);
                if (element) {
                    observer.disconnect();
                    resolve(element);
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            setTimeout(() => {
                observer.disconnect();
                reject(new Error(`Element ${selector} not found within ${timeout}ms`));
            }, timeout);
        });
    }

    /**
     * Cleanup method for when the page is being unloaded
     */
    cleanup() {
        // Clean up all registered components
        for (const [name, component] of this.state.components) {
            if (component && typeof component.cleanup === 'function') {
                component.cleanup();
            }
        }

        // Clear state
        this.state.components.clear();
        this.state.isInitialized = false;

        this.emit('core:cleanup');
    }
}
