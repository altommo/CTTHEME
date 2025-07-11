<?php
/**
 * Pagination template part
 * Based on TubeAcePlay's pagination
 *
 * @package CustomTube
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Clear any floats before pagination
echo '<div class="clearfix"></div>';

echo '<div class="customtube-pagination">';

// Check for WP-PageNavi plugin
if (function_exists('wp_pagenavi')) {
    wp_pagenavi();
} 
// Check for WP-Paginate plugin
elseif (function_exists('wp_paginate')) {
    wp_paginate();
} 
// Default WordPress pagination
else {
    the_posts_pagination(array(
        'mid_size' => 2,
        'prev_text' => __('« Previous', 'customtube'),
        'next_text' => __('Next »', 'customtube'),
        'screen_reader_text' => __('Posts navigation', 'customtube'),
    ));
}

echo '</div>';