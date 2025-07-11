/**
 * Filter Navigation Fix JS
 * 
 * Ensures proper separation and functionality between the top navigation and side filter
 */

jQuery(document).ready(function($) {
    // Add mobile filter toggle button
    if ($('.filter-toggle-mobile').length === 0) {
        $('body').append('<div class="filter-toggle-mobile"><i class="fas fa-filter"></i></div>');
    }
    
    // Handle filter toggle button click
    $('.filter-toggle-mobile').on('click', function() {
        $('.nav-side').toggleClass('active');
        $('.nav-overlay').toggleClass('active');
    });
    
    // Close filter panel when clicking overlay
    $('.nav-overlay').on('click', function() {
        $('.nav-side').removeClass('active');
        $(this).removeClass('active');
    });
    
    // Ensure proper filter panel height on window resize
    $(window).on('resize', function() {
        adjustSidebarHeight();
    });
    
    // Adjust sidebar height initially
    adjustSidebarHeight();
    
    // Function to adjust sidebar height based on header height
    function adjustSidebarHeight() {
        var headerHeight = $('.site-header.enhanced').outerHeight() || 70;
        $('.nav-side').css({
            'top': headerHeight + 'px',
            'height': 'calc(100vh - ' + headerHeight + 'px)'
        });
        
        $('.site-content').css({
            'margin-top': headerHeight + 'px',
            'min-height': 'calc(100vh - ' + headerHeight + 'px)'
        });
    }
    
    // Add touch swipe support for mobile
    var touchStartX = 0;
    var touchEndX = 0;
    
    // Detect touch start position
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    // Detect touch end position and determine swipe direction
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);
    
    // Handle swipe direction
    function handleSwipe() {
        // Left to right swipe (open filter)
        if (touchEndX - touchStartX > 100 && touchStartX < 50) {
            $('.nav-side').addClass('active');
            $('.nav-overlay').addClass('active');
        }
        
        // Right to left swipe (close filter)
        if (touchStartX - touchEndX > 100 && $('.nav-side').hasClass('active')) {
            $('.nav-side').removeClass('active');
            $('.nav-overlay').removeClass('active');
        }
    }
});