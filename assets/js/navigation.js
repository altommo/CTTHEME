/**
 * CustomTube Navigation - Unified Frontend JavaScript
 *
 * Handles all navigation-related functionality, including:
 * - Desktop primary menu with dropdowns (hover)
 * - Mobile hamburger menu with overlay and panel (click)
 * - Mobile menu nested dropdowns (accordion)
 * - Theme switching (dark/light mode)
 * - User menu dropdown
 * - Search bar interaction
 * - Accessibility (ARIA attributes, keyboard navigation)
 */

(function($) {
    'use strict';

    // DOM Elements (cached for performance)
    const $phNav = $('.ph-nav');
    const $phBurger = $('.ph-nav__burger');
    const $phNavLinks = $('.ph-nav__links'); // Desktop primary menu
    const $phNavDropdowns = $phNavLinks.find('.has-dropdown'); // Desktop dropdown parents
    const $phNavSearch = $('.ph-nav__search');
    const $phThemeToggle = $('.ph-nav__theme-toggle');
    const $phUserAvatar = $('.ph-nav__user-avatar');
    const $phUserDropdown = $('.ph-nav__user-menu-dropdown'); // Corrected selector
    const $phMobileOverlay = $('.ph-nav-mobile-overlay');
    const $phMobileMenu = $('.ph-nav-mobile-menu');
    const $phMobileDropdowns = $phMobileMenu.find('.has-dropdown'); // Mobile dropdown parents

    /**
     * Ensures mobile menu is collapsed by default on page load
     */
    function ensureMobileMenuCollapsed() {
        // Force mobile menu to collapsed state
        $phMobileOverlay.removeClass('force-show');
        $phMobileMenu.removeClass('force-show');
        $phBurger.attr('aria-expanded', 'false').attr('data-mobile-menu-closed', 'true');
        $('body').removeClass('mobile-menu-open').css('overflow', '');
        
        // Hide all mobile dropdowns
        $('.ph-nav-mobile-submenu').hide();
        $('.has-dropdown').removeClass('is-open');
        
        console.log('Mobile menu forced to collapsed state');
    }

    /**
     * Initializes all navigation components and event listeners.
     */
    function initializeNavigation() {
        console.log('CustomTube Navigation: Initializing unified system...');
        
        // FIRST: Ensure mobile menu is collapsed
        ensureMobileMenuCollapsed();

        // 1. Mobile Menu Toggle
        $phBurger.off('click.mobileMenu').on('click.mobileMenu', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Hamburger clicked!');
            console.log('Current menu state:', $phMobileMenu.hasClass('force-show'));
            
            const isOpen = $phMobileMenu.hasClass('force-show');
            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
        $phMobileOverlay.off('click.mobileMenu').on('click.mobileMenu', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileMenu();
        });
        $(document).off('keydown.mobileMenu').on('keydown.mobileMenu', function(e) {
            if (e.key === 'Escape' && $phMobileMenu.hasClass('force-show')) {
                closeMobileMenu();
            }
        });
        $(window).off('resize.mobileMenu').on('resize.mobileMenu', handleWindowResize); // Use unified resize handler

        // 2. Desktop Dropdown Menus (Hover & ARIA)
        $phNavDropdowns.each(function() {
            const $dropdownParent = $(this);
            const $dropdownLink = $dropdownParent.find('> .menu-link');
            const $dropdownPanel = $dropdownParent.find('> .ph-nav-dropdown');

            // Set initial ARIA attributes
            $dropdownLink.attr('aria-haspopup', 'true');
            $dropdownLink.attr('aria-expanded', 'false');

            // Desktop hover behavior (only for desktop widths)
            if (window.innerWidth >= 1025) {
                $dropdownParent.on('mouseenter', function() {
                    openDropdown($dropdownParent, $dropdownLink, $dropdownPanel);
                }).on('mouseleave', function() {
                    closeDropdown($dropdownParent, $dropdownLink, $dropdownPanel);
                });
            }

            // Click behavior for desktop dropdowns (for touch devices or accessibility)
            $dropdownLink.on('click', function(e) {
                // Only toggle if it's a dropdown link and not a direct navigation
                if ($dropdownPanel.length) {
                    e.preventDefault();
                    if ($dropdownParent.hasClass('is-active')) {
                        closeDropdown($dropdownParent, $dropdownLink, $dropdownPanel);
                    } else {
                        // Close other open dropdowns before opening this one
                        $phNavDropdowns.not($dropdownParent).each(function() {
                            closeDropdown($(this), $(this).find('> .menu-link'), $(this).find('> .ph-nav-dropdown'));
                        });
                        openDropdown($dropdownParent, $dropdownLink, $dropdownPanel);
                    }
                }
            });
        });

        // 3. Mobile Menu Nested Dropdowns (Accordion)
        $('.ph-nav-mobile-list [data-mobile-dropdown-toggle]').off('click.mobileDropdown').on('click.mobileDropdown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $link = $(this);
            const $parent = $link.closest('.has-dropdown');
            const $submenu = $parent.find('.ph-nav-mobile-submenu');
            const isOpen = $parent.hasClass('is-open');
            
            console.log('Mobile dropdown clicked:', $parent, 'isOpen:', isOpen);
            
            // Close other dropdowns
            $('.ph-nav-mobile-list .has-dropdown.is-open').not($parent).removeClass('is-open')
                .find('.ph-nav-mobile-submenu').slideUp(200);
            
            // Toggle current dropdown
            if (isOpen) {
                $parent.removeClass('is-open');
                $submenu.slideUp(200);
                $link.attr('aria-expanded', 'false');
            } else {
                $parent.addClass('is-open');
                $submenu.slideDown(200);
                $link.attr('aria-expanded', 'true');
            }
        });

        // 4. Theme Switching
        $phThemeToggle.on('click', toggleTheme);
        // Apply initial theme based on cookie or system preference
        applyInitialTheme();

        // 5. User Menu Dropdown
        $phUserAvatar.on('click', toggleUserDropdown);
        // Close user dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$phUserAvatar.is(e.target) && $phUserAvatar.has(e.target).length === 0 &&
                !$phUserDropdown.is(e.target) && $phUserDropdown.has(e.target).length === 0) {
                closeUserDropdown();
            }
        });

        // 6. Search Bar (AJAX suggestions - placeholder)
        $phNavSearch.find('.search-field').on('input', debounce(function() {
            const query = $(this).val().trim();
            if (query.length > 2) {
                // console.log('Search suggestions for:', query);
                // Implement AJAX search suggestions here
            }
        }, 300));

        // 7. Sticky Header Scroll Behavior
        let lastScrollTop = 0;
        $(window).on('scroll', debounce(function() {
            const scrollTop = $(window).scrollTop();

            // Add shadow when scrolled
            if (scrollTop > 10) {
                $phNav.addClass('is-scrolled');
            } else {
                $phNav.removeClass('is-scrolled');
            }

            // Hide/show nav on scroll (optional, based on spec)
            if (Math.abs(lastScrollTop - scrollTop) <= 5) {
                lastScrollTop = scrollTop;
                return;
            }

            if (scrollTop > lastScrollTop && scrollTop > $phNav.outerHeight()) {
                // Scrolling down - hide nav
                $phNav.addClass('is-hidden');
            } else {
                // Scrolling up - show nav
                $phNav.removeClass('is-hidden');
            }
            lastScrollTop = scrollTop;
        }, 10));

        console.log('CustomTube Navigation: Initialization complete.');
    }

    /**
     * Open mobile menu - ENSURES PROPER COLLAPSED BEHAVIOR
     */
    function openMobileMenu() {
        console.log('Opening mobile menu...');
        $phMobileOverlay.addClass('force-show');
        $phMobileMenu.addClass('force-show');
        $('body').addClass('mobile-menu-open').css('overflow', 'hidden'); // Prevent scroll
        $phBurger.attr('aria-expanded', 'true').attr('data-mobile-menu-closed', 'false');
    }

    /**
     * Close mobile menu - ENSURES PROPER COLLAPSED BEHAVIOR
     */
    function closeMobileMenu() {
        console.log('Closing mobile menu...');
        $phMobileOverlay.removeClass('force-show');
        $phMobileMenu.removeClass('force-show');
        $('body').removeClass('mobile-menu-open').css('overflow', ''); // Restore scroll
        $phBurger.attr('aria-expanded', 'false').attr('data-mobile-menu-closed', 'true');
        // Close any open mobile dropdowns
        $phMobileDropdowns.removeClass('is-open').find('.ph-nav-mobile-submenu').slideUp(200);
        $phMobileDropdowns.find('> .menu-link').attr('aria-expanded', 'false');
    }

    /**
     * Handles escape key press to close menus.
     */
    function handleEscapeKey(e) {
        if (e.key === 'Escape') {
            closeMobileMenu(); // Close mobile menu if open
            closeUserDropdown();
            $phNavDropdowns.each(function() {
                closeDropdown($(this), $(this).find('> .menu-link'), $(this).find('> .ph-nav-dropdown'));
            });
        }
    }

    /**
     * Handles window resize events for responsive menu behavior.
     */
    function handleWindowResize() {
        // Removed the conditional closeMobileMenu() for widths >= 1025px
        // The CSS now handles hiding the mobile menu on desktop.
    }

    /**
     * Opens a desktop dropdown panel.
     */
    function openDropdown($parent, $link, $panel) {
        $parent.addClass('is-active');
        $link.attr('aria-expanded', 'true');
        $panel.addClass('is-active'); // Use is-active for CSS transitions
    }

    /**
     * Closes a desktop dropdown panel.
     */
    function closeDropdown($parent, $link, $panel) {
        $parent.removeClass('is-active');
        $link.attr('aria-expanded', 'false');
        $panel.removeClass('is-active'); // Use is-active for CSS transitions
    }

    /**
     * Toggles the theme (dark/light mode).
     */
    function toggleTheme() {
        const currentTheme = $('body').attr('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    }

    /**
     * Applies the specified theme to the body and saves preference.
     */
    function setTheme(theme) {
        // Set theme on body (backwards compatibility)
        $('body').attr('data-theme', theme);
        $('body').toggleClass('dark-mode', theme === 'dark'); // For backward compatibility
        
        // CRITICAL: Set data-current-mode on HTML element for CSS selectors
        document.documentElement.setAttribute('data-current-mode', theme);
        
        // Update button state
        $phThemeToggle.attr('data-current-mode', theme);

        // Update icons
        $phThemeToggle.find('.theme-icon--sun').toggle(theme === 'light');
        $phThemeToggle.find('.theme-icon--moon').toggle(theme === 'dark');

        // Save preference to cookie for 30 days
        const date = new Date();
        date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
        document.cookie = `dark_mode=${theme}; expires=${date.toUTCString()}; path=/`;

        console.log('Theme set to:', theme, '- HTML data-current-mode:', document.documentElement.getAttribute('data-current-mode'));
    }

    /**
     * Applies the initial theme on page load.
     */
    function applyInitialTheme() {
        const cookieTheme = getCookie('dark_mode');
        let initialTheme = 'dark'; // Default to dark mode as per spec

        if (cookieTheme) {
            initialTheme = cookieTheme;
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            initialTheme = 'light'; // Respect system light mode if no cookie
        }
        
        // Initialize HTML attribute on page load
        document.documentElement.setAttribute('data-current-mode', initialTheme);
        
        setTheme(initialTheme);
    }

    /**
     * Toggles the user menu dropdown.
     */
    function toggleUserDropdown() {
        const isOpen = $phUserAvatar.hasClass('is-active');
        if (isOpen) {
            closeUserDropdown();
        } else {
            openUserDropdown();
        }
    }

    /**
     * Opens the user menu dropdown.
     */
    function openUserDropdown() {
        $phUserAvatar.addClass('is-active');
        $phUserAvatar.attr('aria-expanded', 'true');
        $phUserDropdown.addClass('is-active');
    }

    /**
     * Closes the user menu dropdown.
     */
    function closeUserDropdown() {
        $phUserAvatar.removeClass('is-active');
        $phUserAvatar.attr('aria-expanded', 'false');
        $phUserDropdown.removeClass('is-active');
    }

    /**
     * Helper: Gets a cookie value by name.
     */
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    /**
     * Helper: Debounces a function call.
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initialize navigation when the document is ready
    $(document).ready(initializeNavigation);

})(jQuery);
