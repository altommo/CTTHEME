<?php
/**
 * Thumbnail Handler Module
 * Handles thumbnail extraction and setting featured images
 *
 * @package CustomTube
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Extract video thumbnail URL from video URL
 * Only define if not already defined in thumbnail-extractor.php
 * 
 * @param string $url Video URL
 * @param string $source_type 'direct' or 'embed'
 * @return string|bool Thumbnail URL or false on failure
 */
if (!function_exists('customtube_extract_video_thumbnail')) {
function customtube_extract_video_thumbnail($url, $source_type = 'embed') {
    // For direct videos (e.g., MP4), we need video processing libraries
    if ($source_type === 'direct') {
        // Check if FFmpeg is available for thumbnail generation
        if (function_exists('customtube_ffmpeg_is_available') && customtube_ffmpeg_is_available()) {
            // For direct videos, return false to let FFmpeg handle it
            return false;
        }
        
        // If FFmpeg is not available, we'll try to extract a thumbnail from the URL
        // later using other methods
    }
    
    // First check if we have preview_extractor module available
    if (function_exists('customtube_get_preview_data')) {
        // Use preview_extractor for any URL, it's more robust
        $post_id = 0; // We don't have a post ID yet
        
        // Create temporary post meta to store the URL
        add_filter('get_post_metadata', function($value, $object_id, $meta_key, $single) use ($url, $source_type) {
            if ($object_id === 0) {
                if ($meta_key === 'video_source_type') {
                    return $source_type;
                } elseif ($meta_key === 'video_url' && $source_type === 'direct') {
                    return $url;
                } elseif ($meta_key === 'embed_code' && $source_type === 'embed') {
                    return $url;
                }
            }
            return $value;
        }, 10, 4);
        
        // Extract preview data
        $preview_data = customtube_get_preview_data(0);
        
        // Remove the filter
        remove_all_filters('get_post_metadata');
        
        if ($preview_data && !empty($preview_data['images'])) {
            return $preview_data['images'][0];
        }
    }
    
    // Fallback to traditional methods
    
    // For embedded videos, we need to determine the platform
    $url_data = customtube_detect_url_type($url);
    
    if ($url_data['platform'] === 'youtube' && !empty($url_data['video_id'])) {
        return "https://img.youtube.com/vi/{$url_data['video_id']}/maxresdefault.jpg";
    } elseif ($url_data['platform'] === 'vimeo' && !empty($url_data['video_id'])) {
        // For Vimeo, we need to make an API request
        $vimeo_data = customtube_get_vimeo_metadata($url, array());
        if (!empty($vimeo_data['thumbnail_url'])) {
            return $vimeo_data['thumbnail_url'];
        }
    } elseif (in_array($url_data['platform'], array('pornhub', 'xvideos', 'xhamster', 'redtube', 'youporn'))) {
        // For adult sites, try to extract Open Graph image
        $og_data = customtube_extract_opengraph_data($url);
        if (!empty($og_data['image'])) {
            return $og_data['image'];
        }
    }
    
    // If we couldn't determine a thumbnail, try Open Graph as a last resort
    $og_data = customtube_extract_opengraph_data($url);
    if (!empty($og_data['image'])) {
        return $og_data['image'];
    }
    
    return false;
}
}

/**
 * Set featured image from a URL
 * Only define if not already defined in video-metadata.php
 * 
 * @param int $post_id Post ID
 * @param string $thumbnail_url Thumbnail URL
 * @return int|bool Attachment ID or false on failure
 */
if (!function_exists('customtube_set_featured_image_from_url')) {
function customtube_set_featured_image_from_url($post_id, $thumbnail_url) {
    // Bail if no post ID or URL
    if (empty($post_id) || empty($thumbnail_url)) {
        error_log("Missing post ID or thumbnail URL");
        return false;
    }
    
    // Check if already has a featured image
    if (get_post_thumbnail_id($post_id)) {
        error_log("Post ID $post_id already has a featured image");
        return get_post_thumbnail_id($post_id);
    }
    
    error_log("Setting featured image for post ID $post_id from URL: $thumbnail_url");
    
    // First, check if this URL has already been imported
    $existing_attachment = customtube_get_attachment_by_url($thumbnail_url);
    if ($existing_attachment) {
        error_log("Found existing attachment (ID: {$existing_attachment}) for URL: $thumbnail_url");
        $attachment_id = $existing_attachment;
    } else {
        // Download the image
        $attachment_id = customtube_download_image_from_url($thumbnail_url, $post_id);
        if (!$attachment_id) {
            error_log("Failed to download image from URL: $thumbnail_url");
            return false;
        }
    }
    
    // Set as featured image
    $result = set_post_thumbnail($post_id, $attachment_id);
    if ($result) {
        error_log("Successfully set attachment ID $attachment_id as featured image for post ID $post_id");
        return $attachment_id;
    } else {
        error_log("Failed to set attachment ID $attachment_id as featured image for post ID $post_id");
        return false;
    }
}
}

/**
 * Check if an attachment already exists for a given URL
 * 
 * @param string $url Image URL
 * @return int|bool Attachment ID or false if not found
 */
if (!function_exists('customtube_get_attachment_by_url')) {
function customtube_get_attachment_by_url($url) {
    $url = esc_url_raw($url);
    
    // Try to find by source URL meta
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_source_url',
                'value' => $url,
                'compare' => '='
            )
        )
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        return $query->posts[0]->ID;
    }
    
    // Try to find by matching URL in guid
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_attached_file',
                'value' => basename($url),
                'compare' => 'LIKE'
            )
        )
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        return $query->posts[0]->ID;
    }
    
    return false;
}
}

/**
 * Download an image from a URL and add it to the media library
 * 
 * @param string $url Image URL
 * @param int $post_id Post ID
 * @return int|bool Attachment ID or false on failure
 */
if (!function_exists('customtube_download_image_from_url')) {
function customtube_download_image_from_url($url, $post_id) {
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Generate a unique filename
    $filename = basename(parse_url($url, PHP_URL_PATH));
    
    // Download the file
    $tmp = download_url($url);
    
    if (is_wp_error($tmp)) {
        error_log("Error downloading image: " . $tmp->get_error_message());
        return false;
    }
    
    // Prepare file data for sideloading
    $file_array = array(
        'name' => $filename,
        'tmp_name' => $tmp
    );
    
    // Upload the image
    $attachment_id = media_handle_sideload($file_array, $post_id);
    
    // Clean up the temporary file
    @unlink($tmp);
    
    if (is_wp_error($attachment_id)) {
        error_log("Error creating attachment: " . $attachment_id->get_error_message());
        return false;
    }
    
    // Store the source URL as meta data
    update_post_meta($attachment_id, '_source_url', esc_url_raw($url));
    
    return $attachment_id;
}
}