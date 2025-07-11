/**
 * Navigation Component
 * Handles all navigation functionality including mobile menu, dropdowns, search, and user menu
 * Consolidates: navigation.js, ph-navigation.js, header-enhancements.js
 */

import { debounce, getCookie, setCookie, addEvent } from '../core/utils.js';

export class Navigation {
    constructor(core) {
        this.core = core;
        this.elements = {};
        this.state = {
            isMobileMenuOpen: false,
            activeDropdown: null,
            isUserMenuOpen: false
        };
        this.events = []; // This will now store objects { element, event, handler } for easier removal
    }

    /**
     * Initialize navigation component
     */
    init() {
        console.log('Navigation: Initializing...');
        
        // Cache DOM elements
        this.cacheElements();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Ensure mobile menu is collapsed on load
        this.ensureMobileMenuCollapsed();
        
        // Set up sticky header behavior
        this.setupStickyHeader();
        
        // Register with core
        this.core.registerComponent('navigation', this);
        
        console.log('Navigation: Initialization complete');
    }

    /**
     * Cache frequently used DOM elements
     */
    cacheElements() {
        this.elements = {
            // Updated selectors for the new header structure
            nav: document.querySelector('.site-header'), // Main header element
            burger: document.querySelector('.menu-toggle'), // Hamburger button
            mobileOverlay: document.querySelector('.mobile-menu-overlay'), // Corrected selector
            mobileMenu: document.querySelector('.mobile-menu-panel'), // Corrected selector
            // Removed old selectors like .ph-nav__burger, .ph-nav__links, .ph-nav__user-avatar, etc.
            // as they are no longer present or have been renamed.

            // Assuming these elements still exist with these classes if needed
            userAvatar: document.querySelector('.user-profile'), // User profile button
            userDropdown: document.querySelector('.user-menu-dropdown'), // Assuming a class for user dropdown
            searchField: document.querySelector('.header-search .search-field'), // Search field in header
            themeToggle: document.querySelector('.dark-mode-toggle'), // Dark mode toggle button
            headerAd: document.querySelector('.tube-ads-header') // Header ad element
        };

        // Cache dropdown elements for primary and secondary nav
        // Primary nav dropdowns are now handled by CSS hover
        this.elements.secondaryNavDropdowns = document.querySelectorAll('.secondary-nav .has-dropdown');
        this.elements.mobileDropdowns = document.querySelectorAll('.mobile-menu-list .has-dropdown');

        console.log('Navigation: Cached elements:', this.elements);
    }

    /**
     * Helper to add event listeners and store them for cleanup
     * @param {Element} element
     * @param {string} eventType
     * @param {Function} handler
     * @param {Object} options
     */
    _addEvent(element, eventType, handler, options = {}) {
        if (element) {
            element.addEventListener(eventType, handler, options);
            this.events.push({ element, eventType, handler });
            console.log(`Navigation: Added event listener for ${eventType} on`, element);
        } else {
            console.warn(`Navigation: Attempted to add event listener to null element for ${eventType}.`);
        }
    }

    /**
     * Set up all event listeners
     */
    setupEventListeners() {
        // Mobile menu toggle
        if (this.elements.burger) {
            this._addEvent(this.elements.burger, 'click', this.handleMobileMenuToggle.bind(this));
        }
        if (this.elements.mobileOverlay) {
            this._addEvent(this.elements.mobileOverlay, 'click', this.closeMobileMenu.bind(this));
        }

        // Secondary nav dropdowns (if any)
        this.elements.secondaryNavDropdowns.forEach(dropdown => {
            this.setupSecondaryNavDropdown(dropdown);
        });

        // Mobile dropdown toggles
        this.elements.mobileDropdowns.forEach(dropdown => {
            this.setupMobileDropdown(dropdown);
        });

        // User menu toggle (if element exists)
        if (this.elements.userProfile) { // Assuming user-profile is the toggle
            this._addEvent(this.elements.userProfile, 'click', this.handleUserMenuToggle.bind(this));
        }

        // Search functionality (if element exists)
        if (this.elements.searchField) {
            this._addEvent(this.elements.searchField, 'input', debounce(this.handleSearch.bind(this), 300));
        }

        // Global event listeners
        this._addEvent(document, 'keydown', this.handleKeydown.bind(this));
        this._addEvent(document, 'click', this.handleGlobalClick.bind(this));
        this._addEvent(window, 'resize', debounce(this.handleResize.bind(this), 100));

        console.log('Navigation: All event listeners set up.');
    }

    /**
     * Set up secondary navigation dropdown behavior
     * @param {Element} dropdown - Dropdown element (li.has-dropdown)
     */
    setupSecondaryNavDropdown(dropdown) {
        const link = dropdown.querySelector('a');
        const panel = dropdown.querySelector('.dropdown-menu'); // Assuming this class for submenu

        if (!link || !panel) {
            console.warn('Navigation: Could not find link or panel for secondary nav dropdown:', dropdown);
            return;
        }

        // Click behaviour for touch devices and accessibility
        const handleClick = (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (dropdown.classList.contains('is-active')) {
                this.closeSecondaryNavDropdown(dropdown, link, panel);
            } else {
                // Close other dropdowns first
                this.elements.secondaryNavDropdowns.forEach(other => {
                    if (other !== dropdown && other.classList.contains('is-active')) {
                        this.closeSecondaryNavDropdown(other, other.querySelector('a'), other.querySelector('.dropdown-menu'));
                    }
                });
                this.openSecondaryNavDropdown(dropdown, link, panel);
            }
        };

        this._addEvent(link, 'click', handleClick);
        console.log('Navigation: Setup secondary nav dropdown for', dropdown);
    }

    /**
     * Open secondary navigation dropdown
     */
    openSecondaryNavDropdown(dropdown, link, panel) {
        dropdown.classList.add('is-active');
        panel.style.display = 'flex'; // Assuming flex for horizontal layout
        link.setAttribute('aria-expanded', 'true');
        this.state.activeDropdown = dropdown;
        this.core.emit('navigation:secondary-dropdown-opened', dropdown);
    }

    /**
     * Close secondary navigation dropdown
     */
    closeSecondaryNavDropdown(dropdown, link, panel) {
        dropdown.classList.remove('is-active');
        panel.style.display = 'none';
        link.setAttribute('aria-expanded', 'false');
        if (this.state.activeDropdown === dropdown) {
            this.state.activeDropdown = null;
        }
        this.core.emit('navigation:secondary-dropdown-closed', dropdown);
    }

    /**
     * Set up mobile dropdown behavior
     * @param {Element} dropdown - Mobile dropdown element
     */
    setupMobileDropdown(dropdown) {
        // Support both <a> and <span> as menu-link
        const toggle = dropdown.querySelector('[data-mobile-dropdown-toggle]') || dropdown.querySelector('a');
        const submenu = dropdown.querySelector('.mobile-submenu');

        if (!toggle || !submenu) {
            console.warn('Navigation: Could not find toggle or submenu for mobile dropdown:', dropdown);
            return;
        }

        const handleToggle = (e) => {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = dropdown.classList.contains('is-open');

            // Close other mobile dropdowns
            this.elements.mobileDropdowns.forEach(other => {
                if (other !== dropdown) {
                    other.classList.remove('is-open');
                    const otherSubmenu = other.querySelector('.mobile-submenu');
                    if (otherSubmenu) {
                        otherSubmenu.style.display = 'none';
                    }
                }
            });

            // Toggle current dropdown
            if (isOpen) {
                dropdown.classList.remove('is-open');
                submenu.style.display = 'none';
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                dropdown.classList.add('is-open');
                submenu.style.display = 'block';
                toggle.setAttribute('aria-expanded', 'true');
            }
        };

        this._addEvent(toggle, 'click', handleToggle);
        console.log('Navigation: Setup mobile dropdown for', dropdown);
    }

    /**
     * Handle mobile menu toggle
     * @param {Event} e - Click event
     */
    handleMobileMenuToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Navigation: Mobile menu toggle clicked.');

        if (this.state.isMobileMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }

    /**
     * Open mobile menu
     */
    openMobileMenu() {
        if (!this.elements.mobileMenu || !this.elements.mobileOverlay) {
            console.warn('Navigation: Cannot open mobile menu, elements not found.');
            return;
        }

        this.state.isMobileMenuOpen = true;

        // Show overlay and panel
        if (this.elements.mobileOverlay) {
            this.elements.mobileOverlay.classList.add('is-active');
        }
        if (this.elements.mobileMenu) {
            this.elements.mobileMenu.classList.add('is-active');
        }
        document.body.classList.add('mobile-menu-open');
        document.body.style.overflow = 'hidden';

        if (this.elements.burger) {
            this.elements.burger.setAttribute('aria-expanded', 'true');
            this.elements.burger.setAttribute('data-mobile-menu-closed', 'false');
        }

        this.core.emit('navigation:mobile-menu-opened');
        console.log('Navigation: Mobile menu opened.');
    }

    /**
     * Close mobile menu
     */
    closeMobileMenu() {
        if (!this.elements.mobileMenu || !this.elements.mobileOverlay) {
            console.warn('Navigation: Cannot close mobile menu, elements not found.');
            return;
        }

        this.state.isMobileMenuOpen = false;

        // Hide overlay and panel
        if (this.elements.mobileOverlay) {
            this.elements.mobileOverlay.classList.remove('is-active');
        }
        if (this.elements.mobileMenu) {
            this.elements.mobileMenu.classList.remove('is-active');
        }
        document.body.classList.remove('mobile-menu-open');
        document.body.style.overflow = '';

        if (this.elements.burger) {
            this.elements.burger.setAttribute('aria-expanded', 'false');
            this.elements.burger.setAttribute('data-mobile-menu-closed', 'true');
        }

        // Close any open mobile dropdowns
        this.elements.mobileDropdowns.forEach(dropdown => {
            dropdown.classList.remove('is-open');
            const submenu = dropdown.querySelector('.mobile-submenu');
            if (submenu) {
                submenu.style.display = 'none';
            }
        });

        this.core.emit('navigation:mobile-menu-closed');
        console.log('Navigation: Mobile menu closed.');
    }

    /**
     * Ensure mobile menu is collapsed on page load
     */
    ensureMobileMenuCollapsed() {
        this.closeMobileMenu();
    }

    /**
     * Handle user menu toggle
     */
    handleUserMenuToggle() {
        console.log('Navigation: User menu toggle clicked.');
        if (this.state.isUserMenuOpen) {
            this.closeUserMenu();
        } else {
            this.openUserMenu();
        }
    }

    /**
     * Open user menu
     */
    openUserMenu() {
        if (!this.elements.userAvatar || !this.elements.userDropdown) {
            console.warn('Navigation: Cannot open user menu, elements not found.');
            return;
        }

        this.state.isUserMenuOpen = true;
        this.elements.userAvatar.classList.add('is-active');
        this.elements.userDropdown.classList.add('is-active');
        this.elements.userAvatar.setAttribute('aria-expanded', 'true');

        this.core.emit('navigation:user-menu-opened');
        console.log('Navigation: User menu opened.');
    }

    /**
     * Close user menu
     */
    closeUserMenu() {
        if (!this.elements.userAvatar || !this.elements.userDropdown) {
            console.warn('Navigation: Cannot close user menu, elements not found.');
            return;
        }

        this.state.isUserMenuOpen = false;
        this.elements.userAvatar.classList.remove('is-active');
        this.elements.userDropdown.classList.remove('is-active');
        this.elements.userAvatar.setAttribute('aria-expanded', 'false');

        this.core.emit('navigation:user-menu-closed');
        console.log('Navigation: User menu closed.');
    }

    /**
     * Handle search input
     * @param {Event} e - Input event
     */
    handleSearch(e) {
        const query = e.target.value.trim();
        console.log('Navigation: Search input changed:', query);
        
        if (query.length > 2) {
            this.core.emit('navigation:search', { query });
            // Implement search suggestions here if needed
        }
    }

    /**
     * Handle global keydown events
     * @param {Event} e - Keydown event
     */
    handleKeydown(e) {
        if (e.key === 'Escape') {
            console.log('Navigation: Escape key pressed.');
            this.closeMobileMenu();
            this.closeUserMenu();
            // Close all secondary nav dropdowns
            this.elements.secondaryNavDropdowns.forEach(dropdown => {
                if (dropdown.classList.contains('is-active')) {
                    this.closeSecondaryNavDropdown(dropdown, dropdown.querySelector('a'), dropdown.querySelector('.dropdown-menu'));
                }
            });
        }
    }

    /**
     * Handle global clicks for closing menus
     * @param {Event} e - Click event
     */
    handleGlobalClick(e) {
        // Close user menu if clicking outside
        if (this.state.isUserMenuOpen && 
            !this.elements.userAvatar?.contains(e.target) && 
            !this.elements.userDropdown?.contains(e.target)) {
            this.closeUserMenu();
        }

        // Close secondary nav dropdowns if clicking outside
        this.elements.secondaryNavDropdowns.forEach(dropdown => {
            if (dropdown.classList.contains('is-active') && !dropdown.contains(e.target)) {
                this.closeSecondaryNavDropdown(dropdown, dropdown.querySelector('a'), dropdown.querySelector('.dropdown-menu'));
            }
        });
    }

    /**
     * Handle window resize
     */
    handleResize() {
        console.log('Navigation: Window resized.');
        // Close mobile menu on desktop sizes
        if (window.innerWidth >= 1024 && this.state.isMobileMenuOpen) {
            this.closeMobileMenu();
        }
    }

    /**
     * Set up sticky header behavior
     */
    setupStickyHeader() {
        const nav = this.elements.nav; // This is now .site-header
        const headerAd = this.elements.headerAd;
        if (!nav) return;

        let lastScrollTop = 0;
        let ticking = false;

        const updateNav = () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add/remove is-scrolled class based on scroll position
            if (scrollTop > 10) {
                nav.classList.add('is-scrolled');
            } else {
                nav.classList.remove('is-scrolled');
            }
            
            // Handle hide/show on scroll (for main header and header ad)
            if (Math.abs(lastScrollTop - scrollTop) <= 5) {
                lastScrollTop = scrollTop;
                ticking = false;
                return;
            }
            
            // Assuming the entire header-stack will be hidden/shown
            const headerStack = document.querySelector('.header-stack');
            if (!headerStack) return;

            const headerStackHeight = headerStack.offsetHeight;

            if (scrollTop > lastScrollTop && scrollTop > headerStackHeight) {
                headerStack.classList.add('is-hidden');
                // If headerAd is part of the stack, its transform will be handled by the stack's transform
            } else if (scrollTop < lastScrollTop) {
                headerStack.classList.remove('is-hidden');
            }
            
            lastScrollTop = scrollTop;
            ticking = false;
        };

        const onScroll = () => {
            if (!ticking) {
                requestAnimationFrame(updateNav);
                ticking = true;
            }
        };

        this._addEvent(window, 'scroll', onScroll, { passive: true });
        console.log('Navigation: Sticky header setup complete.');
    }

    /**
     * Cleanup method
     */
    cleanup() {
        // Remove all event listeners
        this.events.forEach(({ element, eventType, handler }) => {
            if (element) {
                element.removeEventListener(eventType, handler);
                console.log(`Navigation: Removed event listener for ${eventType} on`, element);
            }
        });
        this.events = [];

        // Reset state
        this.state = {
            isMobileMenuOpen: false,
            activeDropdown: null,
            isUserMenuOpen: false
        };

        console.log('Navigation: Cleaned up');
    }
}
