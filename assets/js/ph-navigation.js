/**
 * PH Navigation JavaScript
 * CustomTube Theme - Navigation Functionality
 * 
 * Handles mobile menu, theme switching, and user dropdown interactions
 */

(function() {
    'use strict';
    
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeNavigation();
    });
    
    function initializeNavigation() {
        // Initialize all navigation components
        initializeMobileMenu();
        initializeThemeToggle();
        initializeUserDropdown();
        initializeSearchForm();
        initializeCategoryDropdown();
        initializeScrollBehavior();
    }
    
    /**
     * Mobile Menu Functionality
     */
    function initializeMobileMenu() {
        const burger = document.querySelector('.ph-nav__burger');
        const overlay = document.querySelector('.ph-nav-mobile-overlay');
        const mobileMenu = document.querySelector('.ph-nav-mobile-menu');
        
        if (!burger) return;
        
        // Create mobile menu if it doesn't exist
        if (!mobileMenu) {
            createMobileMenu();
        }
        
        // Toggle mobile menu
        burger.addEventListener('click', function() {
            toggleMobileMenu();
        });
        
        // Close menu when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                closeMobileMenu();
            });
        }
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });
        
        // Close menu when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeMobileMenu();
            }
        });
    }
    
    function createMobileMenu() {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'ph-nav-mobile-overlay';
        document.body.appendChild(overlay);
        
        // Create mobile menu
        const mobileMenu = document.createElement('div');
        mobileMenu.className = 'ph-nav-mobile-menu';
        
        // Clone navigation links
        const navLinks = document.querySelector('.ph-nav__links');
        if (navLinks) {
            const mobileNavList = navLinks.cloneNode(true);
            mobileNavList.className = 'ph-nav-mobile-list';
            mobileMenu.appendChild(mobileNavList);
        }
        
        document.body.appendChild(mobileMenu);
    }
    
    function toggleMobileMenu() {
        const overlay = document.querySelector('.ph-nav-mobile-overlay');
        const mobileMenu = document.querySelector('.ph-nav-mobile-menu');
        const burger = document.querySelector('.ph-nav__burger');
        
        if (overlay && mobileMenu) {
            const isActive = overlay.classList.contains('is-active');
            
            if (isActive) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        }
    }
    
    function openMobileMenu() {
        const overlay = document.querySelector('.ph-nav-mobile-overlay');
        const mobileMenu = document.querySelector('.ph-nav-mobile-menu');
        const burger = document.querySelector('.ph-nav__burger');
        
        if (overlay && mobileMenu) {
            overlay.classList.add('is-active');
            mobileMenu.classList.add('is-active');
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            
            // Update aria attributes
            if (burger) {
                burger.setAttribute('aria-expanded', 'true');
            }
        }
    }
    
    function closeMobileMenu() {
        const overlay = document.querySelector('.ph-nav-mobile-overlay');
        const mobileMenu = document.querySelector('.ph-nav-mobile-menu');
        const burger = document.querySelector('.ph-nav__burger');
        
        if (overlay && mobileMenu) {
            overlay.classList.remove('is-active');
            mobileMenu.classList.remove('is-active');
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Update aria attributes
            if (burger) {
                burger.setAttribute('aria-expanded', 'false');
            }
        }
    }
    
    /**
     * Theme Toggle Functionality
     */
    function initializeThemeToggle() {
        const themeToggle = document.querySelector('.ph-nav__theme-toggle');
        
        if (!themeToggle) return;
        
        // Get current theme from localStorage or default to light
        const currentTheme = localStorage.getItem('theme') || 
                           (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply initial theme
        setTheme(currentTheme);
        
        // Theme toggle click handler
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('theme')) {
                setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
    
    function setTheme(theme) {
        // Update document attribute
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update body class for backward compatibility
        document.body.classList.toggle('dark-mode', theme === 'dark');
        
        // Save to localStorage
        localStorage.setItem('theme', theme);
        
        // Update theme toggle button aria-label
        const themeToggle = document.querySelector('.ph-nav__theme-toggle');
        if (themeToggle) {
            themeToggle.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} mode`);
        }
        
        // Dispatch custom event for theme change
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: theme }
        }));
    }
    
    /**
     * User Dropdown Functionality
     */
    function initializeUserDropdown() {
        const userAvatar = document.querySelector('.ph-nav__user-avatar');
        const userDropdown = document.querySelector('.ph-nav__user-dropdown');
        
        if (!userAvatar || !userDropdown) return;
        
        // Toggle dropdown on click
        userAvatar.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleUserDropdown();
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userAvatar.contains(e.target)) {
                closeUserDropdown();
            }
        });
        
        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUserDropdown();
            }
        });
    }
    
    function toggleUserDropdown() {
        const userDropdown = document.querySelector('.ph-nav__user-dropdown');
        if (userDropdown) {
            const isActive = userDropdown.classList.contains('is-active');
            if (isActive) {
                closeUserDropdown();
            } else {
                openUserDropdown();
            }
        }
    }
    
    function openUserDropdown() {
        const userDropdown = document.querySelector('.ph-nav__user-dropdown');
        if (userDropdown) {
            userDropdown.classList.add('is-active');
        }
    }
    
    function closeUserDropdown() {
        const userDropdown = document.querySelector('.ph-nav__user-dropdown');
        if (userDropdown) {
            userDropdown.classList.remove('is-active');
        }
    }
    
    /**
     * Search Form Enhancement
     */
    function initializeSearchForm() {
        const searchForm = document.querySelector('.ph-nav__search');
        const searchInput = searchForm ? searchForm.querySelector('input') : null;
        
        if (!searchInput) return;
        
        // Enhanced search functionality
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(this.value);
            }
        });
        
        // Search suggestions (placeholder for future implementation)
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 2) {
                // Placeholder for search suggestions
                console.log('Search query:', query);
            }
        });
    }
    
    function performSearch(query) {
        if (!query.trim()) return;
        
        // Redirect to search results page
        const searchUrl = new URL(window.location.origin);
        searchUrl.searchParams.set('s', query.trim());
        window.location.href = searchUrl.toString();
    }
    
    /**
     * Category Dropdown Functionality
     */
    function initializeCategoryDropdown() {
        const categoryItems = document.querySelectorAll('.ph-nav__links .has-dropdown');
        
        categoryItems.forEach(function(item) {
            const link = item.querySelector('a');
            
            if (!link) return;
            
            // Create dropdown menu if it doesn't exist
            let dropdown = item.querySelector('.ph-nav-dropdown');
            if (!dropdown) {
                dropdown = createCategoryDropdown();
                item.appendChild(dropdown);
            }
            
            // Show dropdown on hover (desktop)
            item.addEventListener('mouseenter', function() {
                if (window.innerWidth >= 1024) {
                    showDropdown(item);
                }
            });
            
            item.addEventListener('mouseleave', function() {
                if (window.innerWidth >= 1024) {
                    hideDropdown(item);
                }
            });
            
            // Toggle dropdown on click (mobile)
            link.addEventListener('click', function(e) {
                if (window.innerWidth < 1024) {
                    e.preventDefault();
                    toggleDropdown(item);
                }
            });
        });
    }
    
    function createCategoryDropdown() {
        const dropdown = document.createElement('div');
        dropdown.className = 'ph-nav-dropdown';
        
        // Sample categories (replace with actual data)
        const categories = [
            { name: 'Popular', url: '/categories/popular' },
            { name: 'New', url: '/categories/new' },
            { name: 'Trending', url: '/categories/trending' },
            { name: 'HD', url: '/categories/hd' },
            { name: 'VR', url: '/categories/vr' }
        ];
        
        const list = document.createElement('ul');
        categories.forEach(function(category) {
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = category.url;
            link.textContent = category.name;
            item.appendChild(link);
            list.appendChild(item);
        });
        
        dropdown.appendChild(list);
        return dropdown;
    }
    
    function showDropdown(item) {
        item.classList.add('has-dropdown--open');
    }
    
    function hideDropdown(item) {
        item.classList.remove('has-dropdown--open');
    }
    
    function toggleDropdown(item) {
        item.classList.toggle('has-dropdown--open');
    }
    
    /**
     * Navigation Scroll Behavior
     */
    function initializeScrollBehavior() {
        let lastScrollTop = 0;
        const nav = document.querySelector('.ph-nav');
        
        if (!nav) return;
        
        window.addEventListener('scroll', debounce(function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add shadow when scrolled
            if (scrollTop > 10) {
                nav.classList.add('is-scrolled');
            } else {
                nav.classList.remove('is-scrolled');
            }
            
            // Hide/show nav on scroll (optional - can be disabled)
            if (Math.abs(lastScrollTop - scrollTop) <= 5) return;
            
            if (scrollTop > lastScrollTop && scrollTop > nav.offsetHeight) {
                // Scrolling down - hide nav
                nav.classList.add('is-hidden');
            } else {
                // Scrolling up - show nav
                nav.classList.remove('is-hidden');
            }
            
            lastScrollTop = scrollTop;
        }, 10));
    }
    
    /**
     * Utility Functions
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
    
    /**
     * Notifications functionality
     */
    function initializeNotifications() {
        const notificationButton = document.querySelector('.ph-nav__notifications');
        
        if (!notificationButton) return;
        
        notificationButton.addEventListener('click', function() {
            // Placeholder for notifications functionality
            console.log('Notifications clicked');
            // You can implement a dropdown or redirect to notifications page
        });
    }
    
    // Initialize notifications
    document.addEventListener('DOMContentLoaded', function() {
        initializeNotifications();
    });
    
})();