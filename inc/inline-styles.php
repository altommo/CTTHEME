<?php
/**
 * Add inline styles to force the two-tone logo effect
 *
 * This file is now deprecated. Its functionality has been moved to
 * assets/css/02-layout/header.css for better modularity and maintainability.
 *
 * @package CustomTube
 */

// This function is no longer needed as its styles are now in header.css
// Keeping the file for reference, but it should not be included in functions.php
function customtube_add_inline_logo_styles() {
    // No longer adding inline styles here.
    // The styling is now handled by assets/css/02-layout/header.css
}
// remove_action('wp_head', 'customtube_add_inline_logo_styles', 999); // Ensure it's not hooked
