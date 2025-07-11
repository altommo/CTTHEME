/**
 * Navigation Sidebar Positioning JavaScript
 * 
 * Handles dynamic positioning of the navigation sidebar below the site navigation
 */

(function() {
    'use strict';
    
    // Function to calculate and set sidebar position
    function positionSidebar() {
        const siteHeader = document.querySelector('#masthead, .site-header');
        const siteNavigation = document.querySelector('#site-navigation, .main-navigation');
        const navSidebar = document.querySelector('.nav-side');
        
        if (!navSidebar) return;
        
        let topOffset = 0;
        
        // Calculate header height
        if (siteHeader) {
            topOffset += 70; // Fixed header height
        }
        
        // Calculate navigation height
        if (siteNavigation) {
            topOffset += 70; // Fixed nav height
        }
        
        // Add admin bar height if present
        const adminBar = document.querySelector('#wpadminbar');
        if (adminBar) {
            topOffset += adminBar.offsetHeight;
        }
        
        // Apply the calculated position
        navSidebar.style.top = topOffset + 'px';
        navSidebar.style.height = `calc(100vh - ${topOffset}px)`;
        
        // Adjust main content area
        const siteContent = document.querySelector('.site-content, #content');
        if (siteContent) {
            const isDesktop = window.innerWidth > 992;
            const sidebarWidth = isDesktop ? 280 : 0;
            
            siteContent.style.marginTop = topOffset + 'px';
            if (isDesktop) {
                siteContent.style.marginLeft = sidebarWidth + 'px';
                siteContent.style.width = `calc(100% - ${sidebarWidth}px)`;
            } else {
                siteContent.style.marginLeft = '0px';
                siteContent.style.width = '100%';
            }
        }
        
        // Update overlay position for mobile
        const navOverlay = document.querySelector('.nav-overlay');
        if (navOverlay) {
            navOverlay.style.top = topOffset + 'px';
        }
    }
    
    // Function to handle responsive changes
    function handleResponsiveChanges() {
        const navSidebar = document.querySelector('.nav-side');
        const siteContent = document.querySelector('.site-content, #content');
        
        if (!navSidebar || !siteContent) return;
        
        const isDesktop = window.innerWidth > 992;
        const isMobile = window.innerWidth <= 767;
        
        if (isMobile) {
            // Mobile: Hide sidebar by default, show with transform
            navSidebar.classList.add('mobile-hidden');
        } else if (isDesktop) {
            // Desktop: Show sidebar normally
            navSidebar.classList.remove('mobile-hidden');
            navSidebar.style.transform = 'translateX(0)';
        }
        
        // Recalculate positions
        positionSidebar();
    }
    
    // Function to handle sticky header changes
    function handleStickyHeader() {
        const siteHeader = document.querySelector('#masthead, .site-header');
        
        if (!siteHeader) return;
        
        // Watch for sticky class changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    // Recalculate position when header becomes sticky/unsticky
                    setTimeout(positionSidebar, 100);
                }
            });
        });
        
        observer.observe(siteHeader, { attributes: true });
    }
    
    // Initialize positioning
    function init() {
        // Initial positioning
        positionSidebar();
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                handleResponsiveChanges();
                positionSidebar();
            }, 250);
        });
        
        // Handle scroll events that might affect sticky header
        let scrollTimer;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(positionSidebar, 100);
        });
        
        // Watch for header changes
        handleStickyHeader();
        
        // Watch for admin bar changes (WordPress admin bar can appear/disappear)
        const adminBar = document.querySelector('#wpadminbar');
        if (adminBar) {
            const adminBarObserver = new MutationObserver(positionSidebar);
            adminBarObserver.observe(adminBar, { attributes: true, childList: true, subtree: true });
        }
        
        // Initial responsive setup
        handleResponsiveChanges();
    }
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Also run after a short delay to ensure all elements are loaded
    setTimeout(init, 100);
    
})();
