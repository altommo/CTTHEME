<?php
/**
 * Smart Category Navigation Fix
 * 
 * This file provides a fix for the category navigation display issues
 */

// Add a hook to apply CSS fixes for smart category navigation
add_action('wp_head', 'customtube_fix_smart_category_navigation');

function customtube_fix_smart_category_navigation() {
    // Skip injecting CSS during REST API calls
    if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) : ?>
    <style>
    /* Fix for #navSide overlapping the top nav */
    #navSide {
        position: fixed;
        top: 60px;       /* adjust to your header height */
        left: 0;
        width: 240px;    /* or whatever width you need */
    }

    @media (max-width: 991px) {
        #navSide {
            position: relative;
            top: auto;
            width: 100%;
        }
    }
    </style>
    <?php endif;
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
