/**
 * Filter Fixes for CustomTube
 * 
 * Ensures that the layout remains consistent when using filters
 */

jQuery(document).ready(function($) {
    // Function to add filtered class to elements when duration slider changes
    function applyFilteredClass() {
        // Add filtered class to the grid container
        $('.video-grid-container').addClass('filtered-results');
        $('.video-grid').addClass('filtered');
        
        // Remove any float-based classes that might interfere with grid layout
        $('.videoBox, .col-items-md').css({
            'float': 'none',
            'width': '100%'
        });
    }
    
    // Listen for changes to duration slider
    $('input[type="range"]').on('input change', function() {
        applyFilteredClass();
    });
    
    // When filter button is clicked
    $('.filter-button, .apply-filters').on('click', function() {
        applyFilteredClass();
    });
    
    // When sorting or category links are clicked
    $('.sort-option a, .category-filter a, .tag-filter a, .performer-filter a').on('click', function() {
        // Add a class to help selector match after AJAX load
        $('body').addClass('filtering-active');
    });
    
    // Apply on page load if filters are active
    if (window.location.search.indexOf('duration=') > -1 || 
        window.location.search.indexOf('orderby=') > -1) {
        applyFilteredClass();
    }
});