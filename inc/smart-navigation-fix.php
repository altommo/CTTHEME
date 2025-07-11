<?php
/**
 * Smart Category Navigation Fix
 * 
 * This file provides a fix for the category navigation display issues
 */

// Add a hook to apply CSS fixes for smart category navigation
add_action('wp_head', 'customtube_fix_smart_category_navigation');

function customtube_fix_smart_category_navigation() {
    ?>
    <style>
    /* Fix for Smart Category Navigation */
    .smart-category-navigation {
        overflow: visible;
        width: 100%;
        margin-bottom: 20px;
    }
    
    .smart-navigation-container {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding: 8px 16px;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
        background-color: var(--color-surface, #2d2d2d);
        border-radius: var(--border-radius, 8px);
    }
    
    .dark-mode .smart-navigation-container {
        background-color: var(--color-dark-surface, #1e1e1e);
    }
    
    .popular-categories {
        width: 100%;
    }
    
    .category-list {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .category-item {
        flex: 0 0 auto;
        margin: 0;
    }
    
    .category-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background-color: var(--color-background, #393939);
        border-radius: var(--border-radius-sm, 4px);
        color: var(--color-text, #e0e0e0);
        text-decoration: none;
        font-size: 14px;
        transition: background-color 0.2s, color 0.2s;
    }
    
    .dark-mode .category-link {
        background-color: var(--color-dark-background, #121212);
        color: var(--color-light-text, #f0f0f0);
    }
    
    .category-link:hover {
        background-color: var(--color-primary, #f5912c);
        color: #fff;
    }
    
    .category-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .category-icon svg {
        width: 20px;
        height: 20px;
    }
    
    /* Ensure SVG text is visible */
    .category-icon svg text {
        fill: currentColor;
        font-weight: bold;
    }
    
    /* Add a subtle shadow to the navigation container for depth */
    .smart-navigation-container {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Ensure the more button stands out */
    .more-categories .category-link {
        background-color: var(--color-primary, #f5912c);
        color: #fff;
    }
    
    /* Fix any scrollbar issues */
    .smart-navigation-container::-webkit-scrollbar {
        height: 4px;
    }
    
    .smart-navigation-container::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .smart-navigation-container::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }
    </style>
    <?php
}

// Add JavaScript to help with the navigation
add_action('wp_footer', 'customtube_fix_navigation_scripts');

function customtube_fix_navigation_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fix for category navigation scroll
        const navContainer = document.querySelector('.smart-navigation-container');
        if (navContainer) {
            // Check if we have overflow and add fade indicators if needed
            const hasOverflow = navContainer.scrollWidth > navContainer.clientWidth;
            if (hasOverflow) {
                navContainer.parentNode.classList.add('has-overflow');
                
                // Create fade indicators
                const fadeRight = document.createElement('div');
                fadeRight.className = 'fade-indicator fade-right';
                navContainer.parentNode.appendChild(fadeRight);
                
                // Update fade indicators on scroll
                navContainer.addEventListener('scroll', updateFadeIndicators);
                
                // Initial update
                updateFadeIndicators();
            }
        }
        
        function updateFadeIndicators() {
            const fadeRight = document.querySelector('.fade-right');
            if (!fadeRight) return;
            
            // Hide right fade when scrolled to the end
            if (navContainer.scrollLeft + navContainer.clientWidth >= navContainer.scrollWidth - 10) {
                fadeRight.style.opacity = '0';
            } else {
                fadeRight.style.opacity = '1';
            }
        }
        
        // Fix theme toggle button if it exists - removed as this is now in theme-switcher-fix.php
    });
    </script>
    <style>
    /* Fade indicators */
    .smart-category-navigation.has-overflow {
        position: relative;
    }
    
    .fade-indicator {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 40px;
        pointer-events: none;
        z-index: 1;
        transition: opacity 0.2s;
    }
    
    .fade-right {
        right: 0;
        background: linear-gradient(to right, rgba(0,0,0,0), var(--color-background, #222));
    }
    
    .dark-mode .fade-right {
        background: linear-gradient(to right, rgba(0,0,0,0), var(--color-dark-background, #000));
    }
    </style>
    <?php
}
