/**
 * CustomTube Navigation - Updated for Modular CSS Architecture
 *
 * Handles navigation menu, mobile menu, dropdowns, and other UI interactions
 */
(function($) {
    'use strict';

    /**
     * Initialize navigation functionality
     */
    function initializeNavigation() {
        // Updated selectors for modular CSS classes
        const menuToggle = $('#nav-toggle, .menu-toggle');
        const menuList = $('#menu-list');
        const navigation = $('#main-nav');
        const userMenuToggle = $('.user-menu-toggle');
        const userMenuDropdown = $('.user-menu-dropdown');
        const filterToggle = $('.filter-toggle-mobile button');
        const dropdownMenus = $('.has-dropdown');
        
        console.log('Initializing navigation with elements:', {
            menuToggle: menuToggle.length,
            menuList: menuList.length,
            dropdowns: dropdownMenus.length
        });
        
        // Mobile menu toggle
        menuToggle.on('click', function(e) {
            e.preventDefault();
            const isExpanded = $(this).attr('aria-expanded') === 'true';
            
            $(this).attr('aria-expanded', !isExpanded);
            
            if (isExpanded) {
                menuList.attr('hidden', true).removeClass('d-lg-flex').addClass('d-none');
            } else {
                menuList.removeAttr('hidden').removeClass('d-none').addClass('d-flex');
            }
            
            // Close user menu dropdown when opening mobile menu
            if (!isExpanded) {
                userMenuDropdown.addClass('d-none');
            }
            
            console.log('Mobile menu toggled:', !isExpanded);
        });
        
        // Dropdown menu functionality
        dropdownMenus.each(function() {
            const $dropdown = $(this);
            const $toggle = $dropdown.find('> .menu-link');
            const $menu = $dropdown.find('.dropdown');
            
            // Click handler for dropdown toggle
            $toggle.on('click', function(e) {
                e.preventDefault();
                
                // Close other dropdowns
                dropdownMenus.not($dropdown).find('.dropdown').addClass('d-none');
                dropdownMenus.not($dropdown).find('> .menu-link').attr('aria-expanded', 'false');
                
                // Toggle current dropdown
                const isOpen = !$menu.hasClass('d-none');
                
                if (isOpen) {
                    $menu.addClass('d-none');
                    $toggle.attr('aria-expanded', 'false');
                } else {
                    $menu.removeClass('d-none');
                    $toggle.attr('aria-expanded', 'true');
                }
                
                console.log('Dropdown toggled:', $dropdown.find('> .menu-link').text().trim(), !isOpen);
            });
            
            // Hover functionality for desktop
            if (window.innerWidth >= 1024) {
                $dropdown.on('mouseenter', function() {
                    $menu.removeClass('d-none');
                    $toggle.attr('aria-expanded', 'true');
                });
                
                $dropdown.on('mouseleave', function() {
                    $menu.addClass('d-none');
                    $toggle.attr('aria-expanded', 'false');
                });
            }
        });
        
        // User menu dropdown toggle
        userMenuToggle.on('click', function(e) {
            e.preventDefault();
            const isOpen = !userMenuDropdown.hasClass('d-none');
            
            if (isOpen) {
                userMenuDropdown.addClass('d-none');
            } else {
                userMenuDropdown.removeClass('d-none');
            }
        });
        
        // Close dropdowns when clicking elsewhere
        $(document).on('click', function(e) {
            const $target = $(e.target);
            
            // Close navigation dropdowns
            if (!$target.closest('.has-dropdown').length) {
                dropdownMenus.find('.dropdown').addClass('d-none');
                dropdownMenus.find('> .menu-link').attr('aria-expanded', 'false');
            }
            
            // Close user menu dropdown
            if (!$target.closest('.user-menu').length) {
                userMenuDropdown.addClass('d-none');
            }
        });
        
        // Filter toggle functionality
        filterToggle.on('click', function() {
            // This will be implemented when we create the filter functionality
            console.log('Filter toggle clicked');
        });
        
        // Handle ESC key to close menus
        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') {
                // Close all menus
                menuList.attr('hidden', true).removeClass('d-flex').addClass('d-none');
                menuToggle.attr('aria-expanded', 'false');
                userMenuDropdown.addClass('d-none');
                dropdownMenus.find('.dropdown').addClass('d-none');
                dropdownMenus.find('> .menu-link').attr('aria-expanded', 'false');
            }
        });
        
        // Responsive behavior
        $(window).on('resize', function() {
            const windowWidth = $(window).width();
            
            if (windowWidth >= 1024) {
                // Desktop: show menu, hide mobile toggle
                menuList.removeAttr('hidden').removeClass('d-none').addClass('d-lg-flex');
                menuToggle.attr('aria-expanded', 'false');
                
                // Enable hover for dropdowns on desktop
                dropdownMenus.off('mouseenter mouseleave').on('mouseenter', function() {
                    $(this).find('.dropdown').removeClass('d-none');
                }).on('mouseleave', function() {
                    $(this).find('.dropdown').addClass('d-none');
                });
            } else {
                // Mobile: hide menu by default, show mobile toggle
                if (menuToggle.attr('aria-expanded') !== 'true') {
                    menuList.attr('hidden', true).removeClass('d-flex d-lg-flex').addClass('d-none');
                }
                
                // Disable hover for dropdowns on mobile
                dropdownMenus.off('mouseenter mouseleave');
            }
        });
    }
    
    /**
     * Initialize theme switcher functionality
     */
    function initializeThemeSwitcher() {
        const themeSwitcher = $('.theme-switcher button, .theme-toggle');
        
        themeSwitcher.on('click', function() {
            const currentTheme = $('body').attr('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            $('body').attr('data-theme', newTheme);
            
            // Save preference
            document.cookie = `theme=${newTheme}; path=/; max-age=31536000`; // 1 year
            
            // Update button text/icon if needed
            const $button = $(this);
            const $icon = $button.find('i');
            const $text = $button.find('span');
            
            if (newTheme === 'dark') {
                $icon.removeClass('fa-sun').addClass('fa-moon');
                $text.text('Dark Mode');
            } else {
                $icon.removeClass('fa-moon').addClass('fa-sun');
                $text.text('Light Mode');
            }
        });
    }
    
    /**
     * Initialize video interactions
     */
    function initializeVideoInteractions() {
        // Handle share button
        $('.share-button').on('click', function() {
            const title = $(this).data('title');
            const url = $(this).data('url') || window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                }).catch((error) => console.log('Error sharing', error));
            } else {
                // Fallback for browsers that don't support Web Share API
                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = url;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                
                // Show copied notification
                const btn = $(this);
                const originalText = btn.find('span').text();
                btn.find('span').text('Copied!');
                
                setTimeout(function() {
                    btn.find('span').text(originalText);
                }, 2000);
            }
        });
        
        // Handle like button
        $('.like-button, .like-toggle').on('click', function() {
            const button = $(this);
            const postId = button.data('post-id');
            
            if (!postId) return;
            
            $.ajax({
                url: customtubeData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_toggle_like',
                    post_id: postId,
                    nonce: customtubeData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update like count
                        button.find('.like-count').text(response.data.likes_count);
                        
                        // Update button state
                        if (response.data.liked) {
                            button.addClass('liked bg-primary').removeClass('bg-secondary');
                            button.find('.like-text').text('Liked');
                        } else {
                            button.removeClass('liked bg-primary').addClass('bg-secondary');
                            button.find('.like-text').text('Like');
                        }
                    }
                },
                error: function() {
                    console.log('Error toggling like');
                }
            });
        });
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        console.log('CustomTube Navigation: Initializing...');
        
        initializeNavigation();
        initializeThemeSwitcher();
        initializeVideoInteractions();
        
        // Trigger resize to set initial responsive state
        $(window).trigger('resize');
        
        console.log('CustomTube Navigation: Initialized successfully');
    });
    
})(jQuery);

// Add CSS for dropdown animations
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .dropdown {
            transition: opacity 0.2s ease, transform 0.2s ease;
            transform-origin: top left;
        }
        
        .dropdown.d-none {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }
        
        .dropdown:not(.d-none) {
            opacity: 1;
            transform: translateY(0);
        }
        
        .menu-list {
            transition: all 0.3s ease;
        }
        
        @media (max-width: 1023px) {
            .menu-list {
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--color-surface);
                border: 1px solid var(--color-border);
                border-radius: var(--border-radius);
                margin-top: 0.5rem;
                padding: 1rem;
                box-shadow: var(--shadow-lg);
                z-index: 1000;
            }
            
            .dropdown {
                position: static !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin-top: 0.5rem !important;
                padding: 0.5rem !important;
                background: var(--color-background) !important;
            }
        }
    `;
    document.head.appendChild(style);
});
