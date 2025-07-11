<?php
/**
 * AJAX Handler for Loading More Videos
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register AJAX action for loading more videos
 */
function customtube_register_load_more_action() {
    add_action('wp_ajax_customtube_load_more_videos', 'customtube_load_more_videos_handler');
    add_action('wp_ajax_nopriv_customtube_load_more_videos', 'customtube_load_more_videos_handler');
}
add_action('init', 'customtube_register_load_more_action');

/**
 * AJAX handler for loading more videos
 */
function customtube_load_more_videos_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        wp_send_json_error('Invalid security token');
    }
    
    // Get request parameters
    $page = isset($_POST['page']) ? absint($_POST['page']) : 2;
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'recent';
    $count = isset($_POST['count']) ? absint($_POST['count']) : 8;
    
    // Build query args based on type
    $args = array(
        'post_type' => 'video',
        'post_status' => 'publish',
        'posts_per_page' => $count,
        'paged' => $page,
        'ignore_sticky_posts' => 1,
    );
    
    // Modify args based on type
    switch ($type) {
        case 'trending':
            $args['meta_key'] = 'video_views';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
            
        case 'recent':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
            
        case 'short':
            $args['meta_query'] = array(
                array(
                    'key' => 'duration_seconds',
                    'value' => 300, // 5 minutes in seconds
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                )
            );
            break;
            
        case 'long':
            $args['meta_query'] = array(
                array(
                    'key' => 'duration_seconds',
                    'value' => 1200, // 20 minutes in seconds
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                )
            );
            break;
            
        default:
            // Default to recent
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }
    
    // Run the query
    $query = new WP_Query($args);
    
    // Start output buffering
    ob_start();
    
    if ($query->have_posts()) {
        echo '<div class="video-grid">';
        
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/thumbnail-preview');
        }
        
        echo '</div>';
    } else {
        echo '<p class="no-videos">No more videos found.</p>';
    }
    
    // Store the output
    $html = ob_get_clean();
    
    // Check if there are more posts
    $max_pages = $query->max_num_pages;
    $has_more = ($page < $max_pages);
    
    // Return the response
    wp_send_json_success(array(
        'html' => $html,
        'has_more' => $has_more,
        'page' => $page,
        'max_pages' => $max_pages
    ));
}