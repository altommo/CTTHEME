<?php
/**
 * Permalink Structure and Settings for CustomTube Theme
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Filter the permalink structure for video post type
 * 
 * This ensures all video permalinks use the format /video/ID/post-name/
 */
function customtube_video_post_type_link($post_link, $post) {
    if ($post->post_type === 'video') {
        // Get the preferred slug from options (default to 'video')
        $video_slug = get_option('customtube_video_slug', 'video');
        
        // Create a consistent format: /video/ID/slug/
        $post_link = home_url("/{$video_slug}/{$post->ID}/{$post->post_name}/");
    }
    return $post_link;
}
add_filter('post_type_link', 'customtube_video_post_type_link', 10, 2);

/**
 * Get video permalink in consistent format
 * 
 * @param int|WP_Post $post Video post or post ID
 * @return string Permalink URL
 */
function customtube_get_video_permalink($post) {
    if (is_numeric($post)) {
        $post = get_post($post);
    }
    
    if (!$post || $post->post_type !== 'video') {
        return '';
    }
    
    // Get the preferred slug from options (default to 'video')
    $video_slug = get_option('customtube_video_slug', 'video');
    
    // Create a consistent format: /video/ID/slug/
    return home_url("/{$video_slug}/{$post->ID}/{$post->post_name}/");
}

/**
 * Add additional rewrite endpoints for video functionality
 */
function customtube_add_video_endpoints() {
    // Add endpoint for watching videos at a specific time
    add_rewrite_endpoint('t', EP_PERMALINK);
    
    // Add endpoint for specific video quality
    add_rewrite_endpoint('quality', EP_PERMALINK);
    
    // Add endpoint for video download
    add_rewrite_endpoint('download', EP_PERMALINK);
}
add_action('init', 'customtube_add_video_endpoints');

/**
 * Fix post thumbnails for videos
 */
function customtube_fix_post_thumbnails($html, $post_id, $post_thumbnail_id, $size, $attr) {
    // Only modify video post types
    if (get_post_type($post_id) !== 'video') {
        return $html;
    }
    
    // Add data attributes to thumbnails for videos
    $html = str_replace('<img ', '<img data-video-id="' . $post_id . '" ', $html);
    
    return $html;
}
add_filter('post_thumbnail_html', 'customtube_fix_post_thumbnails', 10, 5);

/**
 * Fix canonical URLs for video pages
 */
function customtube_video_canonical_url($canonical_url, $post) {
    if (is_singular('video') && $post->post_type === 'video') {
        // Ensure canonical URL follows our preferred format
        $canonical_url = site_url('/video/' . $post->ID . '/' . $post->post_name . '/');
    }
    return $canonical_url;
}
add_filter('get_canonical_url', 'customtube_video_canonical_url', 10, 2);

/**
 * Add settings fields for permalink structure
 */
function customtube_permalink_settings() {
    add_settings_section(
        'customtube_permalink_settings',
        __('CustomTube Permalinks', 'customtube'),
        'customtube_permalink_settings_callback',
        'permalink'
    );
    
    add_settings_field(
        'customtube_video_slug',
        __('Video base', 'customtube'),
        'customtube_video_slug_callback',
        'permalink',
        'customtube_permalink_settings'
    );
}
add_action('admin_init', 'customtube_permalink_settings');

/**
 * Settings section callback
 */
function customtube_permalink_settings_callback() {
    echo '<p>' . __('These settings control the permalinks used for videos in the CustomTube theme.', 'customtube') . '</p>';
}

/**
 * Video slug field callback
 */
function customtube_video_slug_callback() {
    $video_slug = get_option('customtube_video_slug', 'video');
    ?>
    <input type="text" value="<?php echo esc_attr($video_slug); ?>" name="customtube_video_slug" id="customtube_video_slug" class="regular-text code">
    <p class="description"><?php _e('Default: "video"', 'customtube'); ?></p>
    <?php
}

/**
 * Save permalink settings
 */
function customtube_save_permalink_settings() {
    if (isset($_POST['customtube_video_slug'])) {
        update_option('customtube_video_slug', sanitize_title($_POST['customtube_video_slug']));
    }
}
add_action('admin_init', 'customtube_save_permalink_settings');