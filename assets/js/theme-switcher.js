/**
 * Lustful Clips - Theme Switcher
 * 
 * Handles switching between dark and light mode
 * Dark mode is the default for Lustful Clips design
 */

(function($) {
  'use strict';
  
  // Initialize dark/light mode
  function initThemeSwitcher() {
    // Check cookie for existing preference or use system preference
    var currentMode = getCurrentTheme();
    
    // Initialize HTML attribute on page load to avoid null
    document.documentElement.setAttribute('data-current-mode', currentMode);
    
    // Apply initial mode
    setTheme(currentMode);
    
    // Handle toggle button click - Updated selector to match new class
    $('.ph-nav__theme-toggle, #theme-toggle').on('click', function() {
      // Get the current mode from the HTML element
      var currentMode = $('html').attr('data-current-mode') || 'dark';
      // Toggle to the new mode
      var newMode = currentMode === 'dark' ? 'light' : 'dark';
      
      console.log('Theme toggle clicked: Current mode:', currentMode, '→ New mode:', newMode);
      
      // Apply the new theme
      setTheme(newMode);
    });
    
    // Listen for system preference changes
    if (window.matchMedia) {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        // Only apply if user hasn't set a preference (no cookie)
        if (!getCookie('dark_mode')) {
          setTheme(e.matches ? 'dark' : 'light');
        }
      });
    }
  }
  
  // Function to set theme and cookie
  function setTheme(theme) {
    const html = document.documentElement;
    
    if (theme === 'light') {
      $('body').removeClass('dark-mode').attr('data-theme', 'light');
      html.setAttribute('data-current-mode', 'light');
      // Update button data attribute for backwards compatibility
      $('.ph-nav__theme-toggle, #theme-toggle').attr('data-current-mode', 'light');
    } else {
      $('body').addClass('dark-mode').attr('data-theme', 'dark');
      html.setAttribute('data-current-mode', 'dark');
      // Update button data attribute for backwards compatibility
      $('.ph-nav__theme-toggle, #theme-toggle').attr('data-current-mode', 'dark');
    }
    
    // Set cookie with 30 days expiration
    setCookie('dark_mode', theme, 30);
    
    console.log('Theme set to:', theme, '- HTML data-current-mode:', html.getAttribute('data-current-mode'));
  }
  
  // Function to get current theme from cookie or system preferences
  function getCurrentTheme() {
    // Try to get theme from cookie
    var cookieTheme = getCookie('dark_mode');
    if (cookieTheme) {
      return cookieTheme;
    }
    
    // If no cookie, check system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark';
    }
    
    // Default to dark mode (Lustful Clips is dark mode first)
    return 'dark';
  }
  
  // Helper: Get cookie value
  function getCookie(name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
  }
  
  // Helper: Set cookie
  function setCookie(name, value, days) {
    var expires = '';
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + value + expires + '; path=/';
  }
  
  // Initialize on document ready
  $(document).ready(function() {
    initThemeSwitcher();
  });

})(jQuery);