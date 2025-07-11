/**
 * Header Enhancement Script
 * 
 * Enhances the mobile menu, burger button, and navigation
 */

jQuery(document).ready(function($) {
    // Enhance the burger button with proper markup
    var $menuToggle = $('.menu-toggle');
    
    // Skip if already enhanced
    if ($menuToggle.find('.burger-icon').length === 0) {
        $menuToggle.html('<span class="burger-icon"><span></span><span></span><span></span><span></span></span>');
    }
    
    // Add menu overlay
    if ($('.menu-overlay').length === 0) {
        $('body').append('<div class="menu-overlay"></div>');
    }
    
    // Toggle mobile menu
    $menuToggle.on('click', function() {
        $(this).toggleClass('active');
        $('.main-navigation').toggleClass('toggled');
        $('.menu-overlay').toggleClass('active');
        
        // Prevent scrolling when menu is open
        $('body').toggleClass('menu-open');
        
        if ($('body').hasClass('menu-open')) {
            $('body').css('overflow', 'hidden');
        } else {
            $('body').css('overflow', '');
        }
    });
    
    // Close menu when clicking on overlay
    $('.menu-overlay').on('click', function() {
        $menuToggle.removeClass('active');
        $('.main-navigation').removeClass('toggled');
        $('.menu-overlay').removeClass('active');
        $('body').removeClass('menu-open').css('overflow', '');
    });
    
    // Add appropriate classes to menu items
    $('.main-navigation li').each(function() {
        var $item = $(this);
        var itemText = $item.find('> a').text().toLowerCase();
        
        // Add featured class to special menu items
        if (itemText.includes('new') || itemText.includes('premium') || itemText.includes('featured')) {
            $item.addClass('featured-menu-item');
        }
        
        // Mark menu items with children
        if ($item.find('ul').length > 0) {
            $item.addClass('menu-item-has-children');
        }
    });
    
    // Mobile sub-menu toggle
    $('.main-navigation li.menu-item-has-children > a').on('click', function(e) {
        if (window.innerWidth <= 991) {
            e.preventDefault();
            $(this).parent().toggleClass('focus');
        }
    });
    
    // Close menu on window resize
    $(window).on('resize', function() {
        if (window.innerWidth > 991) {
            $menuToggle.removeClass('active');
            $('.main-navigation').removeClass('toggled');
            $('.menu-overlay').removeClass('active');
            $('body').removeClass('menu-open').css('overflow', '');
        }
    });
});