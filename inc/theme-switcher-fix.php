<?php
/**
 * Theme Switcher Fix
 * 
 * This is a quick fix for the theme switcher functionality
 * to ensure it properly sets and maintains dark/light mode.
 */

// Add a hook to ensure the dark/light mode is set properly
add_action('wp_head', 'customtube_fix_theme_mode');

function customtube_fix_theme_mode() {
    // Default to dark mode
    $current_mode = isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] : 'dark';
    
    // Make sure body has the right data attribute for JS
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var bodyElement = document.querySelector("body");
            var themeToggle = document.getElementById("theme-toggle");
            
            if (bodyElement) {
                if ("' . $current_mode . '" === "dark") {
                    bodyElement.classList.add("dark-mode");
                    bodyElement.setAttribute("data-theme", "dark");
                } else {
                    bodyElement.classList.remove("dark-mode");
                    bodyElement.setAttribute("data-theme", "light");
                }
            }
            
            if (themeToggle) {
                themeToggle.setAttribute("data-current-mode", "' . $current_mode . '");
            }
        });
    </script>';
}

// Add a filter to override the default theme mode cookie
add_filter('customtube_theme_mode', 'customtube_override_theme_mode');

function customtube_override_theme_mode($mode) {
    // Default to dark mode instead of light
    return isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] : 'dark';
}

// Add JavaScript to fix the theme toggle button functionality
add_action('wp_footer', 'customtube_fix_theme_switcher_js');

function customtube_fix_theme_switcher_js() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                var currentMode = this.getAttribute('data-current-mode');
                var newMode = currentMode === 'dark' ? 'light' : 'dark';
                
                // Update body class and data attribute
                var body = document.body;
                if (newMode === 'dark') {
                    body.classList.add('dark-mode');
                    body.setAttribute('data-theme', 'dark');
                } else {
                    body.classList.remove('dark-mode');
                    body.setAttribute('data-theme', 'light');
                }
                
                // Update button state
                this.setAttribute('data-current-mode', newMode);
                
                // Set cookie for 30 days
                var date = new Date();
                date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
                document.cookie = 'dark_mode=' + newMode + '; expires=' + date.toUTCString() + '; path=/';
            });
        }
    });
    </script>
    <?php
}

