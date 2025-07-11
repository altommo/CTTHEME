/**
 * Smart Category Navigation JavaScript
 * 
 * Enhances the category navigation with horizontal scrolling and overflow detection
 */

(function() {
    'use strict';

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        initCategoryNavigation();
    });

    /**
     * Initialize category navigation functionality
     */
    function initCategoryNavigation() {
        const categoryList = document.querySelector('.category-list');
        const tagsList = document.querySelector('.tags-list');
        
        if (!categoryList && !tagsList) return;

        // Check for overflow on both lists
        checkForOverflow(categoryList, '.popular-categories');
        checkForOverflow(tagsList, '.trending-tags');

        // Add scroll buttons if needed
        if (categoryList) {
            addScrollButtons(categoryList, '.popular-categories');
        }
        
        if (tagsList) {
            addScrollButtons(tagsList, '.trending-tags');
        }

        // Update overflow detection on window resize
        window.addEventListener('resize', function() {
            checkForOverflow(categoryList, '.popular-categories');
            checkForOverflow(tagsList, '.trending-tags');
        });
    }

    /**
     * Check if element has horizontal overflow
     * @param {HTMLElement} element - The element to check for overflow
     * @param {string} containerSelector - The parent container selector for adding class
     */
    function checkForOverflow(element, containerSelector) {
        if (!element) return;
        
        const container = document.querySelector(containerSelector);
        if (!container) return;
        
        const hasOverflow = element.scrollWidth > element.clientWidth;
        
        if (hasOverflow) {
            container.classList.add('has-overflow');
        } else {
            container.classList.remove('has-overflow');
        }
    }

    /**
     * Add scroll buttons to horizontally scrollable elements
     * @param {HTMLElement} scrollElement - The scrollable element
     * @param {string} containerSelector - The parent container selector
     */
    function addScrollButtons(scrollElement, containerSelector) {
        if (!scrollElement) return;
        
        const container = document.querySelector(containerSelector);
        if (!container) return;
        
        // Add scrollable container wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'scrollable-container';
        
        // Clone the scroll element
        const clone = scrollElement.cloneNode(true);
        
        // Create scroll buttons
        const leftButton = document.createElement('button');
        leftButton.className = 'scroll-button scroll-left';
        leftButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>`;
        leftButton.setAttribute('aria-label', 'Scroll left');
        
        const rightButton = document.createElement('button');
        rightButton.className = 'scroll-button scroll-right';
        rightButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
        rightButton.setAttribute('aria-label', 'Scroll right');
        
        // Add event listeners to buttons
        leftButton.addEventListener('click', function() {
            smoothScroll(clone, -200); // Scroll left by 200px
        });
        
        rightButton.addEventListener('click', function() {
            smoothScroll(clone, 200); // Scroll right by 200px
        });
        
        // Replace original with wrapped version
        scrollElement.parentNode.insertBefore(wrapper, scrollElement);
        wrapper.appendChild(clone);
        wrapper.appendChild(leftButton);
        wrapper.appendChild(rightButton);
        scrollElement.remove();
        
        // Update the overflow detection
        checkForOverflow(clone, containerSelector);
    }

    /**
     * Smooth scroll an element horizontally
     * @param {HTMLElement} element - The element to scroll
     * @param {number} distance - The distance to scroll (positive for right, negative for left)
     */
    function smoothScroll(element, distance) {
        if (!element) return;
        
        const currentScroll = element.scrollLeft;
        element.scrollTo({
            left: currentScroll + distance,
            behavior: 'smooth'
        });
    }

    /**
     * Track user click events on categories and tags for personalization
     * This is a placeholder for future AI-driven personalization features
     */
    function trackCategoryAndTagClicks() {
        const categoryLinks = document.querySelectorAll('.category-link');
        const tagLinks = document.querySelectorAll('.tag-link');
        
        // Track category clicks
        categoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Get category name from link text
                const categoryName = this.textContent.trim();
                
                // Log click for analytics (placeholder for future implementation)
                console.log('Category clicked:', categoryName);
                
                // Store in localStorage for personalization
                storeUserPreference('category', categoryName);
            });
        });
        
        // Track tag clicks
        tagLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Get tag name from link text
                const tagName = this.textContent.trim();
                
                // Log click for analytics (placeholder for future implementation)
                console.log('Tag clicked:', tagName);
                
                // Store in localStorage for personalization
                storeUserPreference('tag', tagName);
            });
        });
    }

    /**
     * Store user preference in localStorage for personalization
     * This is a placeholder for future AI-driven personalization features
     * 
     * @param {string} type - The type of preference (category or tag)
     * @param {string} value - The value of the preference
     */
    function storeUserPreference(type, value) {
        // Get existing preferences or initialize empty object
        let preferences = JSON.parse(localStorage.getItem('userPreferences') || '{}');
        
        // Initialize type array if not exists
        if (!preferences[type]) {
            preferences[type] = [];
        }
        
        // Add to beginning of array (most recent first)
        preferences[type].unshift(value);
        
        // Keep only last 10 items
        preferences[type] = preferences[type].slice(0, 10);
        
        // Save back to localStorage
        localStorage.setItem('userPreferences', JSON.stringify(preferences));
    }

    // Initialize click tracking when ready
    if (document.readyState === 'complete') {
        trackCategoryAndTagClicks();
    } else {
        window.addEventListener('load', trackCategoryAndTagClicks);
    }
})();