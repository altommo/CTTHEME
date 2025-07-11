<?php
/**
 * Ajax handler for video filtering
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register AJAX actions
 */
function customtube_register_filter_actions() {
    add_action('wp_ajax_customtube_filter_videos', 'customtube_filter_videos_handler');
    add_action('wp_ajax_nopriv_customtube_filter_videos', 'customtube_filter_videos_handler');
}
add_action('init', 'customtube_register_filter_actions');

/**
 * AJAX handler for filtering videos
 */
function customtube_filter_videos_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        wp_send_json_error('Invalid security token');
    }
    
    // Get filter parameters
    $genre_id = isset($_POST['genre']) ? absint($_POST['genre']) : 0;
    $tag_id = isset($_POST['tag']) ? absint($_POST['tag']) : 0;
    $performer_id = isset($_POST['performer']) ? absint($_POST['performer']) : 0;
    $duration = isset($_POST['duration']) ? absint($_POST['duration']) : 0; // In seconds
    $search_query = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';
    
    // Set up basic query args
    $args = array(
        'post_type' => 'video',
        'post_status' => 'publish',
        'posts_per_page' => 24,
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    );
    
    // Add taxonomy filters
    $tax_query = array();
    
    if ($genre_id > 0) {
        $tax_query[] = array(
            'taxonomy' => 'genre',
            'field' => 'term_id',
            'terms' => $genre_id,
        );
    }
    
    if ($tag_id > 0) {
        $tax_query[] = array(
            'taxonomy' => 'post_tag',
            'field' => 'term_id',
            'terms' => $tag_id,
        );
    }
    
    if ($performer_id > 0) {
        $tax_query[] = array(
            'taxonomy' => 'performer',
            'field' => 'term_id',
            'terms' => $performer_id,
        );
    }
    
    if (!empty($tax_query)) {
        // If we have multiple tax queries, set the relationship
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        
        $args['tax_query'] = $tax_query;
    }
    
    // Add duration filter if specified
    if ($duration > 0) {
        // We'll need a meta query for duration
        $args['meta_query'] = array(
            array(
                'key' => 'duration_seconds',
                'value' => $duration,
                'compare' => '<=',
                'type' => 'NUMERIC',
            ),
        );
    }
    
    // Add search query if provided
    if (!empty($search_query)) {
        $args['s'] = $search_query;
    }
    
    // Run the query
    $query = new WP_Query($args);
    
    // Start output buffer to capture the loop
    ob_start();
    
    if ($query->have_posts()) {
        // Show a heading for filtered results
        echo '<div class="filtered-results-heading">';
        echo '<h2 class="section-title">Filter Results</h2>';
        echo '</div>';

        echo '<div class="video-grid-container filtered-results">';
        echo '<div class="video-grid filtered">';
        
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/thumbnail-preview');
        }
        
        echo '</div>'; // .video-grid
        
        // Pagination
        echo '<div class="pagination-container">';
        echo paginate_links(array(
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $query->max_num_pages,
            'prev_text' => '<i class="fas fa-chevron-left"></i>',
            'next_text' => '<i class="fas fa-chevron-right"></i>',
        ));
        echo '</div>'; // .pagination-container
        
        echo '</div>'; // .video-grid-container
    } else {
        // No results found
        echo '<div class="no-results">';
        echo '<h2>No videos found</h2>';
        echo '<p>Try adjusting your filters or search query.</p>';
        echo '</div>';
    }
    
    // Restore original post data
    wp_reset_postdata();
    
    // Get the HTML from the buffer
    $html = ob_get_clean();
    
    // Get term counts for active filters
    $counts = customtube_get_filter_counts();
    
    // Send success response with HTML and counts
    wp_send_json_success(array(
        'html' => $html,
        'counts' => $counts,
    ));
}

/**
 * Get term counts for filters
 * 
 * @return array Array of term counts grouped by taxonomy
 */
function customtube_get_filter_counts() {
    $counts = array(
        'genre' => array(),
        'tag' => array(),
        'performer' => array(),
    );
    
    // Get genre counts
    $genres = get_terms(array(
        'taxonomy' => 'genre',
        'hide_empty' => true,
    ));
    
    if (!is_wp_error($genres)) {
        foreach ($genres as $genre) {
            $counts['genre'][$genre->term_id] = $genre->count;
        }
    }
    
    // Get tag counts
    $tags = get_terms(array(
        'taxonomy' => 'post_tag',
        'hide_empty' => true,
        'number' => 20,
        'orderby' => 'count',
        'order' => 'DESC',
    ));
    
    if (!is_wp_error($tags)) {
        foreach ($tags as $tag) {
            $counts['tag'][$tag->term_id] = $tag->count;
        }
    }
    
    // Get performer counts
    $performers = get_terms(array(
        'taxonomy' => 'performer',
        'hide_empty' => true,
        'number' => 20,
        'orderby' => 'count',
        'order' => 'DESC',
    ));
    
    if (!is_wp_error($performers)) {
        foreach ($performers as $performer) {
            $counts['performer'][$performer->term_id] = $performer->count;
        }
    }
    
    return $counts;
}

// Remove the action that loads the problematic scripts - prevent conflicts
remove_action('wp_enqueue_scripts', 'customtube_enqueue_filter_scripts');

/**
 * Add necessary scripts and styles - FIXED VERSION
 */
function customtube_enqueue_filter_scripts_fixed() {
    // NO CSS files - using modular CSS architecture only
    // The navigation styles are included in the main modular CSS system
    
    // Only enqueue the JavaScript file with correct handle
    wp_enqueue_script(
        'customtube-navigation-filter',
        get_template_directory_uri() . '/assets/js/navigation-filter.js',
        array('jquery'),
        CUSTOMTUBE_VERSION, // Use theme version instead of filemtime for cache busting
        true
    );
    
    // Enqueue Font Awesome
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css',
        array(),
        '5.15.3'
    );
    
    // Localize the script with our data
    wp_localize_script('customtube-navigation-filter', 'customtube_settings', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('customtube-ajax-nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'customtube_enqueue_filter_scripts_fixed');