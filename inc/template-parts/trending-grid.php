<?php
/**
 * Template part for displaying trending videos grid
 *
 * Uses the same structure and styling as the regular video grid
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Display trending videos section using the standard video grid layout
 */
function customtube_trending_grid() {
    // Get trending videos
    $trending_videos = customtube_get_trending_videos(12);

    // Use the standard video grid function to avoid duplicate markup
    customtube_video_grid($trending_videos, esc_html__('Trending Videos', 'customtube'));
}