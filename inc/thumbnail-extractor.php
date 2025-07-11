<?php
/**
 * Thumbnail extraction functions for CustomTube Theme
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main function to extract a thumbnail URL from a video source
 * 
 * @param string $video_source URL or embed code
 * @param string $source_type 'direct' or 'embed'
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_video_thumbnail($video_source, $source_type = 'direct') {
    if (empty($video_source)) {
        return false;
    }
    
    // If it's direct, check if it's actually a platform URL
    if ($source_type === 'direct') {
        $video_type = customtube_get_video_type($video_source);
        if ($video_type !== 'mp4' && $video_type !== 'unknown') {
            $source_type = 'embed';
        }
    }
    
    // For embed codes, determine the platform
    if ($source_type === 'embed') {
        $video_type = customtube_get_video_type($video_source);
        
        switch ($video_type) {
            case 'youtube':
                return customtube_extract_youtube_thumbnail($video_source);
                
            case 'vimeo':
                return customtube_extract_vimeo_thumbnail($video_source);
                
            case 'pornhub':
                return customtube_extract_pornhub_thumbnail($video_source);
                
            case 'xvideos':
                return customtube_extract_xvideos_thumbnail($video_source);
                
            case 'xhamster':
                return customtube_extract_xhamster_thumbnail($video_source);
                
            case 'youporn':
                return customtube_extract_youporn_thumbnail($video_source);
                
            case 'redtube':
                return customtube_extract_redtube_thumbnail($video_source);
                
            case 'iframe':
                // Try to extract Open Graph tags from the iframe source
                return customtube_extract_opengraph_thumbnail($video_source);
        }
    }
    
    return false;
}

/**
 * Extract thumbnail from YouTube URL or embed code
 * 
 * @param string $url YouTube URL or embed code
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_youtube_thumbnail($url) {
    // Extract YouTube video ID
    $video_id = '';
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    if (empty($video_id)) {
        return false;
    }
    
    // API-based method (if API key is available)
    $api_key = get_option('customtube_youtube_api_key', '');
    if (!empty($api_key)) {
        $response = wp_remote_get("https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=snippet");
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!empty($data['items'][0]['snippet']['thumbnails'])) {
                $thumbnails = $data['items'][0]['snippet']['thumbnails'];
                
                // Try to get the highest quality thumbnail
                if (!empty($thumbnails['maxres'])) {
                    return $thumbnails['maxres']['url'];
                } elseif (!empty($thumbnails['high'])) {
                    return $thumbnails['high']['url'];
                } elseif (!empty($thumbnails['medium'])) {
                    return $thumbnails['medium']['url'];
                } elseif (!empty($thumbnails['default'])) {
                    return $thumbnails['default']['url'];
                }
            }
        }
    }
    
    // Fallback method (no API key required)
    // Try maxresdefault first (HD)
    $maxres_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
    $response = wp_remote_head($maxres_url);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        return $maxres_url;
    }
    
    // Fall back to hqdefault if maxresdefault doesn't exist
    return "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
}

/**
 * Extract thumbnail from Vimeo URL or embed code
 * 
 * @param string $url Vimeo URL or embed code
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_vimeo_thumbnail($url) {
    // Extract Vimeo video ID
    $video_id = '';
    if (preg_match('/vimeo\.com\/(?:.*\/)*([0-9]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    if (empty($video_id)) {
        return false;
    }
    
    // Use Vimeo's oEmbed API (no key required)
    $response = wp_remote_get("https://vimeo.com/api/v2/video/{$video_id}.json");
    
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($data[0]['thumbnail_large'])) {
            return $data[0]['thumbnail_large'];
        } elseif (!empty($data[0]['thumbnail_medium'])) {
            return $data[0]['thumbnail_medium'];
        } elseif (!empty($data[0]['thumbnail_small'])) {
            return $data[0]['thumbnail_small'];
        }
    }
    
    return false;
}

/**
 * Extract thumbnail from PornHub URL or embed code using Open Graph tags
 * 
 * @param string $url PornHub URL or embed code
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_pornhub_thumbnail($url) {
    // Extract PornHub video key
    $video_key = '';
    if (preg_match('/viewkey=([^&"\']+)/', $url, $matches)) {
        $video_key = $matches[1];
    } elseif (preg_match('/pornhub\.com\/embed\/([a-zA-Z0-9]+)/', $url, $matches)) {
        $video_key = $matches[1];
    }
    
    if (empty($video_key)) {
        return false;
    }
    
    // Construct the full page URL
    $page_url = "https://www.pornhub.com/view_video.php?viewkey={$video_key}";
    
    // Try to fetch the Open Graph image tag
    return customtube_extract_opengraph_thumbnail($page_url);
}

/**
 * Extract thumbnail from XVideos URL or embed code
 * 
 * @param string $url XVideos URL or embed code
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_xvideos_thumbnail($url) {
    // Extract XVideos video ID
    $video_id = '';
    if (preg_match('/xvideos\.com\/video([0-9]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    if (empty($video_id)) {
        return false;
    }
    
    // Try to fetch the Open Graph image tag
    return customtube_extract_opengraph_thumbnail($url);
}

/**
 * Extract thumbnail from XHamster URL or embed code
 * 
 * @param string $url XHamster URL or embed code
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_xhamster_thumbnail($url) {
    // Try to fetch the Open Graph image tag
    return customtube_extract_opengraph_thumbnail($url);
}

/**
 * Extract thumbnail from YouPorn URL or embed code
 * 
 * @param string $url YouPorn URL or embed code
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_youporn_thumbnail($url) {
    // Try to fetch the Open Graph image tag
    return customtube_extract_opengraph_thumbnail($url);
}

/**
 * Extract thumbnail from RedTube URL or embed code
 * 
 * @param string $url RedTube URL or embed code
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_redtube_thumbnail($url) {
    // Try to fetch the Open Graph image tag
    return customtube_extract_opengraph_thumbnail($url);
}

/**
 * Extract Open Graph thumbnail image from a URL
 * This uses WP_HTTP to fetch the page and extract the og:image meta tag
 * 
 * @param string $url Page URL
 * @return string|false Thumbnail URL or false on failure
 */
function customtube_extract_opengraph_thumbnail($url) {
    // If it's an iframe embed code, try to extract the src URL
    if (strpos($url, '<iframe') !== false) {
        if (preg_match('/src=["\'](https?:\/\/[^"\']+)["\']/', $url, $matches)) {
            $url = $matches[1];
        }
    }
    
    // Verify it's a valid URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Fetch the page content
    $response = wp_remote_get($url, array(
        'timeout' => 10,
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ),
    ));
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    
    // Extract og:image meta tag
    if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\'](.*?)["\']/', $body, $matches)) {
        return $matches[1];
    }
    
    // Alternative: Try to find Twitter image tag
    if (preg_match('/<meta[^>]*name=["\']twitter:image["\'][^>]*content=["\'](.*?)["\']/', $body, $matches)) {
        return $matches[1];
    }
    
    return false;
}

/**
 * Auto-extract thumbnail when a video is created or updated
 * and set it as the featured image if none exists
 */
function customtube_auto_extract_thumbnail($post_id, $post) {
    // Only run for video post type
    if ($post->post_type !== 'video') {
        return;
    }
    
    // Don't run during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Don't run for revisions
    if (wp_is_post_revision($post_id)) {
        return;
    }
    
    // Skip if post already has a featured image
    if (has_post_thumbnail($post_id)) {
        return;
    }
    
    // Get video source type
    $source_type = get_post_meta($post_id, 'video_source_type', true);
    $source_type = empty($source_type) ? 'direct' : $source_type;
    
    // Get video source based on type
    $video_source = '';
    if ($source_type === 'direct') {
        $video_source = get_post_meta($post_id, 'video_url', true);
    } else {
        $video_source = get_post_meta($post_id, 'embed_code', true);
    }
    
    // Skip if no video source
    if (empty($video_source)) {
        return;
    }
    
    // Try to extract a thumbnail
    $thumbnail_url = customtube_extract_video_thumbnail($video_source, $source_type);
    
    // If we got a thumbnail URL, set it as the featured image
    if ($thumbnail_url) {
        customtube_set_featured_image_from_url($post_id, $thumbnail_url);
    }
}
add_action('save_post', 'customtube_auto_extract_thumbnail', 20, 2);

/**
 * Add action link to extract video thumbnail manually
 */
function customtube_add_extract_thumbnail_action($actions, $post) {
    // Only for video post type
    if ($post->post_type === 'video') {
        $url = wp_nonce_url(
            admin_url("admin-ajax.php?action=customtube_extract_thumbnail&post_id={$post->ID}"),
            "extract_thumbnail_{$post->ID}"
        );
        $actions['extract_thumbnail'] = '<a href="' . esc_url($url) . '">' . __('Extract Thumbnail', 'customtube') . '</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'customtube_add_extract_thumbnail_action', 10, 2);

/**
 * AJAX handler for manually extracting thumbnails
 */
function customtube_ajax_extract_thumbnail() {
    // Check if we have the post ID
    if (!isset($_REQUEST['post_id'])) {
        wp_die(__('No post ID provided.', 'customtube'));
    }
    
    // Verify nonce
    $post_id = intval($_REQUEST['post_id']);
    check_admin_referer("extract_thumbnail_{$post_id}");
    
    // Verify user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_die(__('You do not have permission to edit this post.', 'customtube'));
    }
    
    // Get post
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'video') {
        wp_die(__('Invalid post type.', 'customtube'));
    }
    
    // Get video source type
    $source_type = get_post_meta($post_id, 'video_source_type', true);
    $source_type = empty($source_type) ? 'direct' : $source_type;
    
    // Get video source based on type
    $video_source = '';
    if ($source_type === 'direct') {
        $video_source = get_post_meta($post_id, 'video_url', true);
    } else {
        $video_source = get_post_meta($post_id, 'embed_code', true);
    }
    
    // Check if we have a video source
    if (empty($video_source)) {
        wp_die(__('No video source found for this post.', 'customtube'));
    }
    
    // Try to extract a thumbnail
    $thumbnail_url = customtube_extract_video_thumbnail($video_source, $source_type);
    
    // If we got a thumbnail URL, set it as the featured image
    if ($thumbnail_url) {
        $result = customtube_set_featured_image_from_url($post_id, $thumbnail_url);
        
        if ($result) {
            // Redirect back with success message
            wp_redirect(add_query_arg('thumbnail_extracted', '1', admin_url("post.php?post={$post_id}&action=edit")));
            exit;
        } else {
            wp_die(__('Failed to download and set the thumbnail image.', 'customtube'));
        }
    } else {
        wp_die(__('Could not extract a thumbnail from the video source.', 'customtube'));
    }
}
add_action('wp_ajax_customtube_extract_thumbnail', 'customtube_ajax_extract_thumbnail');

/**
 * Show admin notice after extracting thumbnail
 */
function customtube_thumbnail_extracted_notice() {
    if (isset($_GET['thumbnail_extracted']) && $_GET['thumbnail_extracted'] === '1') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Thumbnail successfully extracted and set as featured image!', 'customtube'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'customtube_thumbnail_extracted_notice');

/**
 * Add "Extract Thumbnail" button to video meta box
 */
function customtube_add_extract_thumbnail_button($post) {
    if ($post->post_type !== 'video') {
        return;
    }
    
    ?>
    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
        <button type="button" id="extract-thumbnail-button" class="button">
            <?php _e('Extract Thumbnail from Video', 'customtube'); ?>
        </button>
        <span id="extract-thumbnail-spinner" class="spinner"></span>
        <div id="extract-thumbnail-result"></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#extract-thumbnail-button').on('click', function() {
            var button = $(this);
            var spinner = $('#extract-thumbnail-spinner');
            var result = $('#extract-thumbnail-result');
            var sourceType = $('input[name="video_source_type"]:checked').val();
            var videoSource = sourceType === 'direct' ? $('#video_url').val() : $('#embed_code').val();
            
            if (!videoSource) {
                result.html('<p style="color: red;"><?php _e('Please enter a video URL or embed code first.', 'customtube'); ?></p>');
                return;
            }
            
            button.prop('disabled', true);
            spinner.css('visibility', 'visible');
            result.empty();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_ajax_extract_thumbnail',
                    post_id: <?php echo $post->ID; ?>,
                    source_type: sourceType,
                    video_source: videoSource,
                    nonce: '<?php echo wp_create_nonce("customtube_extract_thumbnail_{$post->ID}"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        result.html('<p style="color: green;"><?php _e('Thumbnail successfully extracted!', 'customtube'); ?></p>');
                        
                        // Refresh the featured image meta box
                        if (response.thumbnail_url) {
                            // If there's a featured image container, update it
                            if ($('#set-post-thumbnail').length) {
                                $('#set-post-thumbnail').html('<img src="' + response.thumbnail_url + '" />');
                                $('.inside', '#postimagediv').addClass('has-thumbnail');
                            }
                        }
                    } else {
                        result.html('<p style="color: red;">' + response.data + '</p>');
                    }
                },
                error: function() {
                    result.html('<p style="color: red;"><?php _e('Error extracting thumbnail. Please try again.', 'customtube'); ?></p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.css('visibility', 'hidden');
                }
            });
        });
    });
    </script>
    <?php
}
add_action('post_thumbnail_html', 'customtube_add_extract_thumbnail_button');

/**
 * AJAX handler for extracting thumbnail inside the post editor
 */
function customtube_ajax_extract_thumbnail_in_editor() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], "customtube_extract_thumbnail_{$_POST['post_id']}")) {
        wp_send_json_error(__('Security check failed.', 'customtube'));
    }
    
    // Check if we have the required data
    if (!isset($_POST['post_id']) || !isset($_POST['video_source']) || !isset($_POST['source_type'])) {
        wp_send_json_error(__('Missing required data.', 'customtube'));
    }
    
    $post_id = intval($_POST['post_id']);
    $video_source = $_POST['video_source'];
    $source_type = sanitize_text_field($_POST['source_type']);
    
    // Verify user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(__('You do not have permission to edit this post.', 'customtube'));
    }
    
    // Try to extract a thumbnail
    $thumbnail_url = customtube_extract_video_thumbnail($video_source, $source_type);
    
    // If we got a thumbnail URL, set it as the featured image
    if ($thumbnail_url) {
        $result = customtube_set_featured_image_from_url($post_id, $thumbnail_url);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Thumbnail successfully extracted and set as featured image!', 'customtube'),
                'thumbnail_url' => $thumbnail_url
            ));
        } else {
            wp_send_json_error(__('Failed to download and set the thumbnail image.', 'customtube'));
        }
    } else {
        wp_send_json_error(__('Could not extract a thumbnail from the video source.', 'customtube'));
    }
}
add_action('wp_ajax_customtube_ajax_extract_thumbnail', 'customtube_ajax_extract_thumbnail_in_editor');