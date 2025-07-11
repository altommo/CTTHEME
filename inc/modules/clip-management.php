<?php
/**
 * Clip Management Module
 * Handles video clip creation, storage, and management
 *
 * @package CustomTube
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Check if clips functionality is properly configured
 * 
 * @return bool True if clips functionality is properly configured
 */
function customtube_clips_is_configured() {
    // Check FFmpeg availability
    if (!function_exists('customtube_ffmpeg_is_available') || !customtube_ffmpeg_is_available()) {
        error_log("FFmpeg is not available");
        return false;
    }
    
    // Get settings from options if constants aren't defined
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : get_option('customtube_ffmpeg_binary', '/usr/bin/ffmpeg');
    $ffprobe_path = defined('FFPROBE_BINARY') ? FFPROBE_BINARY : get_option('customtube_ffprobe_binary', '/usr/bin/ffprobe');
    $clip_output_dir = defined('CLIP_OUTPUT_DIR') ? CLIP_OUTPUT_DIR : get_option('customtube_clip_output_dir', WP_CONTENT_DIR . '/uploads/clips');
    $temp_dir = defined('TEMP_DIR') ? TEMP_DIR : get_option('customtube_temp_dir', sys_get_temp_dir());
    
    // Define constants if not already defined
    if (!defined('FFMPEG_BINARY')) define('FFMPEG_BINARY', $ffmpeg_path);
    if (!defined('FFPROBE_BINARY')) define('FFPROBE_BINARY', $ffprobe_path);
    if (!defined('CLIP_OUTPUT_DIR')) define('CLIP_OUTPUT_DIR', $clip_output_dir);
    if (!defined('TEMP_DIR')) define('TEMP_DIR', $temp_dir);
    
    // Check existence of binaries
    if (!file_exists($ffmpeg_path)) {
        error_log("FFmpeg binary not found at: $ffmpeg_path");
        return false;
    }
    
    if (!file_exists($ffprobe_path)) {
        error_log("FFprobe binary not found at: $ffprobe_path");
        return false;
    }
    
    // Create output directory if it doesn't exist
    if (!file_exists($clip_output_dir)) {
        if (!wp_mkdir_p($clip_output_dir)) {
            error_log("Failed to create clip output directory: $clip_output_dir");
            return false;
        }
    }
    
    // Check writable directories
    if (!is_writable($clip_output_dir)) {
        error_log("Clip output directory is not writable: $clip_output_dir");
        return false;
    }
    
    if (!is_writable($temp_dir)) {
        error_log("Temporary directory is not writable: $temp_dir");
        return false;
    }
    
    return true;
}

/**
 * Get the base clip directory for a post
 * 
 * @param int $post_id Post ID
 * @return string Full path to the clip directory
 */
function customtube_get_clip_directory($post_id) {
    $clip_output_dir = defined('CLIP_OUTPUT_DIR') ? CLIP_OUTPUT_DIR : trailingslashit(WP_CONTENT_DIR) . 'uploads/clips';
    $post_clip_dir = trailingslashit($clip_output_dir) . $post_id;
    
    // Create directory if it doesn't exist
    if (!file_exists($post_clip_dir)) {
        wp_mkdir_p($post_clip_dir);
    }
    
    return $post_clip_dir;
}

/**
 * Get the URL for a clip
 * 
 * @param int $post_id Post ID
 * @param string $clip_filename Clip filename
 * @return string URL to the clip
 */
function customtube_get_clip_url($post_id, $clip_filename) {
    $clip_output_dir = defined('CLIP_OUTPUT_DIR') ? CLIP_OUTPUT_DIR : trailingslashit(WP_CONTENT_DIR) . 'uploads/clips';
    $uploads_dir = wp_upload_dir();
    
    // Determine URL based on directories
    if (strpos($clip_output_dir, WP_CONTENT_DIR) !== false) {
        // Clip directory is inside WP content directory
        $relative_path = str_replace(WP_CONTENT_DIR, '', $clip_output_dir);
        $clip_base_url = content_url($relative_path);
    } else {
        // Clip directory is outside WP content directory, use uploads URL as fallback
        $clip_base_url = $uploads_dir['baseurl'] . '/clips';
    }
    
    return trailingslashit($clip_base_url) . $post_id . '/' . $clip_filename;
}

/**
 * Get video source path for a post
 * 
 * @param int $post_id Post ID
 * @return string|false Full path to video file or false if not found
 */
function customtube_get_video_source_path($post_id) {
    // Only works for self-hosted videos
    $video_source_type = get_post_meta($post_id, 'video_source_type', true);
    if ($video_source_type !== 'direct') {
        error_log("Post ID $post_id: Not a direct video source type");
        return false;
    }
    
    $video_url = get_post_meta($post_id, 'video_url', true);
    if (empty($video_url)) {
        error_log("Post ID $post_id: No video URL found");
        return false;
    }
    
    // Check if this is actually an embedded video incorrectly stored as direct
    // Popular platforms that we know can't be directly processed by FFmpeg
    $excluded_domains = array(
        'youtube.com', 'youtu.be',        // YouTube
        'vimeo.com',                      // Vimeo
        'pornhub.com',                    // Pornhub
        'xhamster.com', 'xh.video',       // XHamster
        'xvideos.com',                    // XVideos
        'redtube.com',                    // RedTube
        'youporn.com'                     // YouPorn
    );
    
    foreach ($excluded_domains as $domain) {
        if (strpos($video_url, $domain) !== false) {
            error_log("Post ID $post_id: URL contains excluded domain $domain - cannot process with FFmpeg");
            return false;
        }
    }
    
    error_log("Post ID $post_id: Checking video URL: $video_url");
    
    // Convert URL to local path if possible
    if (strpos($video_url, site_url()) === 0) {
        // This is a local URL, convert to path
        $uploads_dir = wp_upload_dir();
        $site_url = site_url();
        $uploads_url = $uploads_dir['baseurl'];
        
        error_log("Local URL detected: site_url=$site_url, uploads_url=$uploads_url");
        
        if (strpos($video_url, $uploads_url) === 0) {
            // It's in the uploads directory
            $relative_path = substr($video_url, strlen($uploads_url));
            $file_path = $uploads_dir['basedir'] . $relative_path;
            
            error_log("Converted to file path: $file_path");
            
            if (file_exists($file_path)) {
                return $file_path;
            } else {
                error_log("File not found at path: $file_path");
            }
        }
    }
    
    // For common remote testing URLs that are publicly accessible, use them directly
    $allowed_remote_domains = array(
        'commondatastorage.googleapis.com',
        'sample-videos.com',
        'download.blender.org',
        'test-videos.co.uk'
    );
    
    foreach ($allowed_remote_domains as $domain) {
        if (strpos($video_url, $domain) !== false) {
            error_log("Allowed remote URL from domain: $domain");
            return $video_url; // Return the URL directly for FFmpeg to process
        }
    }
    
    // Check if URL is a direct file with correct extension
    $video_extensions = array('mp4', 'webm', 'mov', 'avi', 'wmv', 'flv', 'mkv');
    $url_extension = strtolower(pathinfo($video_url, PATHINFO_EXTENSION));
    
    if (in_array($url_extension, $video_extensions)) {
        // For external direct video URLs, we can also try to process them directly with FFmpeg
        // But only return for URLs with proper video extensions
        error_log("External direct video detected with extension $url_extension");
        return $video_url;
    }
    
    error_log("Could not convert to local path and not an allowed remote URL: $video_url");
    return false;
}

/**
 * Generate clip file path
 * 
 * @param int $post_id Post ID
 * @param string $clip_type Type of clip (e.g., preview, highlight, intro)
 * @param string $extension File extension without dot
 * @return string Full path to clip file
 */
function customtube_get_clip_path($post_id, $clip_type = 'preview', $extension = 'mp4') {
    $clip_dir = customtube_get_clip_directory($post_id);
    return trailingslashit($clip_dir) . "{$clip_type}.{$extension}";
}

/**
 * Create video clips for a post
 * 
 * @param int $post_id Post ID
 * @param array $options Optional. Clip creation options.
 * @return array Clip information (success, clips)
 */
function customtube_create_video_clips($post_id, $options = array()) {
    if (!customtube_clips_is_configured()) {
        error_log("Clips functionality is not properly configured");
        return array(
            'success' => false,
            'message' => 'Clips functionality is not properly configured.',
            'clips' => array()
        );
    }
    
    // Get video source path
    $video_path = customtube_get_video_source_path($post_id);
    if (!$video_path) {
        error_log("No valid video source found for post ID: $post_id");
        return array(
            'success' => false, 
            'message' => 'No valid video source found. Only self-hosted videos are supported.',
            'clips' => array()
        );
    }
    
    // Parse options
    $defaults = array(
        'preview' => true,      // Generate preview clip
        'highlight' => true,    // Generate highlight clip
        'intro' => true,        // Generate intro clip
        'gif' => true,          // Generate animated GIF
        'webp' => true,         // Generate WebP animation
        'width' => 640,         // Default width
        'height' => 360,        // Default height
        'preview_duration' => 15, // Preview duration in seconds
        'highlight_duration' => 30, // Highlight duration in seconds
        'intro_duration' => 10, // Intro duration in seconds
        'gif_duration' => 3,    // GIF duration in seconds
        'webp_duration' => 5,   // WebP duration in seconds
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // Get video info
    if (!function_exists('customtube_get_video_info')) {
        include_once(get_template_directory() . '/inc/modules/ffmpeg-integration.php');
    }
    
    $video_info = customtube_get_video_info($video_path);
    if (!$video_info) {
        error_log("Failed to retrieve video info for post ID: $post_id");
        return array(
            'success' => false,
            'message' => 'Failed to analyze video file.',
            'clips' => array()
        );
    }
    
    // Initialize clips array to store results
    $clips = array();
    $success = false;
    
    // Generate preview clip
    if ($options['preview']) {
        $preview_path = customtube_get_clip_path($post_id, 'preview', 'mp4');
        $preview_options = array(
            'start' => min(5, $video_info['duration'] * 0.1),
            'duration' => min($options['preview_duration'], $video_info['duration'] * 0.5),
            'width' => $options['width'],
            'height' => $options['height'],
            'audio' => true,
            'bitrate' => '1M',
        );
        
        if (customtube_generate_preview_clip($video_path, $preview_path, $preview_options)) {
            $clips['preview'] = array(
                'path' => $preview_path,
                'url' => customtube_get_clip_url($post_id, basename($preview_path)),
                'type' => 'video/mp4',
                'duration' => $preview_options['duration'],
            );
            $success = true;
            
            // Save preview clip meta
            update_post_meta($post_id, 'clip_preview', basename($preview_path));
            update_post_meta($post_id, 'clip_preview_url', $clips['preview']['url']);
        }
    }
    
    // Generate highlight clip
    if ($options['highlight']) {
        $highlight_path = customtube_get_clip_path($post_id, 'highlight', 'mp4');
        $highlight_options = array(
            'duration' => min($options['highlight_duration'], $video_info['duration'] * 0.5),
            'width' => $options['width'],
            'height' => $options['height'],
            'scene_threshold' => 0.4,
        );
        
        if (customtube_create_highlight_clip($video_path, $highlight_path, $highlight_options)) {
            $clips['highlight'] = array(
                'path' => $highlight_path,
                'url' => customtube_get_clip_url($post_id, basename($highlight_path)),
                'type' => 'video/mp4',
                'duration' => $highlight_options['duration'],
            );
            $success = true;
            
            // Save highlight clip meta
            update_post_meta($post_id, 'clip_highlight', basename($highlight_path));
            update_post_meta($post_id, 'clip_highlight_url', $clips['highlight']['url']);
        }
    }
    
    // Generate intro clip (from beginning of video)
    if ($options['intro']) {
        $intro_path = customtube_get_clip_path($post_id, 'intro', 'mp4');
        $intro_options = array(
            'start' => 0,
            'duration' => min($options['intro_duration'], $video_info['duration'] * 0.3),
            'width' => $options['width'],
            'height' => $options['height'],
            'audio' => true,
            'bitrate' => '1M',
        );
        
        if (customtube_extract_clip($video_path, $intro_path, $intro_options)) {
            $clips['intro'] = array(
                'path' => $intro_path,
                'url' => customtube_get_clip_url($post_id, basename($intro_path)),
                'type' => 'video/mp4',
                'duration' => $intro_options['duration'],
            );
            $success = true;
            
            // Save intro clip meta
            update_post_meta($post_id, 'clip_intro', basename($intro_path));
            update_post_meta($post_id, 'clip_intro_url', $clips['intro']['url']);
        }
    }
    
    // Generate animated GIF
    if ($options['gif']) {
        $gif_path = customtube_get_clip_path($post_id, 'preview', 'gif');
        $gif_options = array(
            'start' => min(10, $video_info['duration'] * 0.2),
            'duration' => min($options['gif_duration'], 3),
            'width' => 320,
            'height' => 180,
            'fps' => 10,
            'optimize' => true,
        );
        
        if (customtube_generate_animated_gif($video_path, $gif_path, $gif_options)) {
            $clips['gif'] = array(
                'path' => $gif_path,
                'url' => customtube_get_clip_url($post_id, basename($gif_path)),
                'type' => 'image/gif',
                'duration' => $gif_options['duration'],
            );
            $success = true;
            
            // Save GIF meta
            update_post_meta($post_id, 'clip_gif', basename($gif_path));
            update_post_meta($post_id, 'clip_gif_url', $clips['gif']['url']);
        }
    }
    
    // Generate WebP animation
    if ($options['webp']) {
        $webp_path = customtube_get_clip_path($post_id, 'preview', 'webp');
        $webp_options = array(
            'start' => min(10, $video_info['duration'] * 0.2),
            'duration' => min($options['webp_duration'], 5),
            'width' => 320,
            'height' => 180,
            'fps' => 12,
            'quality' => 80,
        );
        
        if (customtube_generate_webp_animation($video_path, $webp_path, $webp_options)) {
            $clips['webp'] = array(
                'path' => $webp_path,
                'url' => customtube_get_clip_url($post_id, basename($webp_path)),
                'type' => 'image/webp',
                'duration' => $webp_options['duration'],
            );
            $success = true;
            
            // Save WebP meta
            update_post_meta($post_id, 'clip_webp', basename($webp_path));
            update_post_meta($post_id, 'clip_webp_url', $clips['webp']['url']);
        }
    }
    
    // Save clip generation time
    if ($success) {
        update_post_meta($post_id, 'clips_generated', time());
        update_post_meta($post_id, 'clips_available', array_keys($clips));
    }
    
    return array(
        'success' => $success,
        'message' => $success ? 'Clips generated successfully.' : 'Failed to generate clips.',
        'clips' => $clips
    );
}

/**
 * Delete all clips for a post
 * 
 * @param int $post_id Post ID
 * @return bool True on success, false on failure
 */
function customtube_delete_video_clips($post_id) {
    $clip_dir = customtube_get_clip_directory($post_id);
    
    if (!file_exists($clip_dir)) {
        return true; // No clips to delete
    }
    
    // List of clip types to check and delete
    $clip_types = array('preview', 'highlight', 'intro');
    $extensions = array('mp4', 'gif', 'webp');
    
    // Delete each clip file
    foreach ($clip_types as $type) {
        foreach ($extensions as $ext) {
            $clip_path = customtube_get_clip_path($post_id, $type, $ext);
            if (file_exists($clip_path)) {
                @unlink($clip_path);
            }
        }
    }
    
    // Remove directory if empty
    $is_empty = count(glob("$clip_dir/*")) === 0;
    if ($is_empty) {
        @rmdir($clip_dir);
    }
    
    // Delete post meta
    delete_post_meta($post_id, 'clips_generated');
    delete_post_meta($post_id, 'clips_available');
    delete_post_meta($post_id, 'clip_preview');
    delete_post_meta($post_id, 'clip_preview_url');
    delete_post_meta($post_id, 'clip_highlight');
    delete_post_meta($post_id, 'clip_highlight_url');
    delete_post_meta($post_id, 'clip_intro');
    delete_post_meta($post_id, 'clip_intro_url');
    delete_post_meta($post_id, 'clip_gif');
    delete_post_meta($post_id, 'clip_gif_url');
    delete_post_meta($post_id, 'clip_webp');
    delete_post_meta($post_id, 'clip_webp_url');
    
    return true;
}

/**
 * Check if clips are available for a post
 * 
 * @param int $post_id Post ID
 * @return array|false Array of available clips or false if none
 */
function customtube_get_available_clips($post_id) {
    $available_clips = get_post_meta($post_id, 'clips_available', true);
    
    if (empty($available_clips)) {
        return false;
    }
    
    // Verify clips actually exist
    $clips = array();
    
    foreach ($available_clips as $clip_type) {
        $clip_file = '';
        $clip_url = '';
        
        switch ($clip_type) {
            case 'preview':
                $clip_file = get_post_meta($post_id, 'clip_preview', true);
                $clip_url = get_post_meta($post_id, 'clip_preview_url', true);
                break;
            case 'highlight':
                $clip_file = get_post_meta($post_id, 'clip_highlight', true);
                $clip_url = get_post_meta($post_id, 'clip_highlight_url', true);
                break;
            case 'intro':
                $clip_file = get_post_meta($post_id, 'clip_intro', true);
                $clip_url = get_post_meta($post_id, 'clip_intro_url', true);
                break;
            case 'gif':
                $clip_file = get_post_meta($post_id, 'clip_gif', true);
                $clip_url = get_post_meta($post_id, 'clip_gif_url', true);
                break;
            case 'webp':
                $clip_file = get_post_meta($post_id, 'clip_webp', true);
                $clip_url = get_post_meta($post_id, 'clip_webp_url', true);
                break;
        }
        
        if (!empty($clip_file) && !empty($clip_url)) {
            $clip_path = customtube_get_clip_directory($post_id) . '/' . $clip_file;
            
            if (file_exists($clip_path)) {
                $extension = pathinfo($clip_file, PATHINFO_EXTENSION);
                $mime_type = '';
                
                switch ($extension) {
                    case 'mp4':
                        $mime_type = 'video/mp4';
                        break;
                    case 'gif':
                        $mime_type = 'image/gif';
                        break;
                    case 'webp':
                        $mime_type = 'image/webp';
                        break;
                }
                
                $clips[$clip_type] = array(
                    'file' => $clip_file,
                    'url' => $clip_url,
                    'path' => $clip_path,
                    'type' => $mime_type,
                    'filesize' => filesize($clip_path),
                );
            }
        }
    }
    
    return !empty($clips) ? $clips : false;
}

/**
 * Get HTML for clip preview player
 * 
 * @param int $post_id Post ID
 * @param string $clip_type Type of clip (preview, highlight, intro)
 * @param array $attributes Optional. Additional attributes for player.
 * @return string HTML for clip player
 */
function customtube_get_clip_player_html($post_id, $clip_type = 'preview', $attributes = array()) {
    $clips = customtube_get_available_clips($post_id);
    
    if (!$clips || !isset($clips[$clip_type])) {
        return '';
    }
    
    $clip = $clips[$clip_type];
    
    // Default attributes
    $defaults = array(
        'width' => '100%',
        'height' => 'auto',
        'controls' => true,
        'autoplay' => false,
        'loop' => true,
        'muted' => true,
        'playsinline' => true,
        'class' => 'customtube-clip-player',
    );
    
    $attributes = wp_parse_args($attributes, $defaults);
    
    // Create HTML attributes
    $html_attributes = '';
    foreach ($attributes as $attr => $value) {
        if (is_bool($value)) {
            if ($value) {
                $html_attributes .= ' ' . $attr;
            }
        } else {
            $html_attributes .= ' ' . $attr . '="' . esc_attr($value) . '"';
        }
    }
    
    // Create player based on clip type
    if (in_array(pathinfo($clip['file'], PATHINFO_EXTENSION), array('mp4'))) {
        // Video player
        $html = '<video' . $html_attributes . '>';
        $html .= '<source src="' . esc_url($clip['url']) . '" type="' . esc_attr($clip['type']) . '">';
        $html .= 'Your browser does not support HTML5 video.';
        $html .= '</video>';
    } elseif (in_array(pathinfo($clip['file'], PATHINFO_EXTENSION), array('gif', 'webp'))) {
        // Image
        $html = '<img src="' . esc_url($clip['url']) . '" alt="Video clip" width="' . esc_attr($attributes['width']) . '" class="' . esc_attr($attributes['class']) . '">';
    } else {
        $html = '';
    }
    
    return $html;
}

/**
 * Auto-generate clips for a video
 * 
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 */
function customtube_auto_generate_clips($post_id, $post) {
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
    
    // Skip if already has clips
    $has_clips = get_post_meta($post_id, 'clips_generated', true);
    if ($has_clips) {
        return;
    }
    
    // Only for self-hosted videos
    $video_source_type = get_post_meta($post_id, 'video_source_type', true);
    if ($video_source_type !== 'direct') {
        return;
    }
    
    // Check if video URL is available
    $video_url = get_post_meta($post_id, 'video_url', true);
    if (empty($video_url)) {
        return;
    }
    
    // Make sure we have FFmpeg integration loaded
    if (!function_exists('customtube_ffmpeg_is_available')) {
        include_once(get_template_directory() . '/inc/modules/ffmpeg-integration.php');
    }
    
    // Check if FFmpeg is available
    if (!customtube_ffmpeg_is_available()) {
        return;
    }
    
    // Generate clips in the background
    wp_schedule_single_event(time() + 10, 'customtube_generate_clips_event', array($post_id));
}
// add_action('save_post', 'customtube_auto_generate_clips', 30, 2);

/**
 * Handle scheduled clip generation
 * 
 * @param int $post_id Post ID
 */
function customtube_handle_clips_generation($post_id) {
    // Make sure we have the necessary functions
    if (!function_exists('customtube_create_video_clips')) {
        include_once(get_template_directory() . '/inc/modules/clip-management.php');
    }
    
    if (!function_exists('customtube_ffmpeg_is_available')) {
        include_once(get_template_directory() . '/inc/modules/ffmpeg-integration.php');
    }
    
    // Generate clips
    $result = customtube_create_video_clips($post_id);
    
    // Log the result
    if ($result['success']) {
        error_log("Successfully generated clips for post ID $post_id: " . count($result['clips']) . " clips created.");
    } else {
        error_log("Failed to generate clips for post ID $post_id: " . $result['message']);
    }
}
add_action('customtube_generate_clips_event', 'customtube_handle_clips_generation');

/**
 * Add "Generate Clips" button to video meta box
 */
function customtube_add_generate_clips_button($post) {
    if ($post->post_type !== 'video') {
        return;
    }
    
    // Check source type
    $video_source_type = get_post_meta($post->ID, 'video_source_type', true);
    if ($video_source_type !== 'direct') {
        return;
    }
    
    // Check if FFmpeg is available
    if (!function_exists('customtube_ffmpeg_is_available')) {
        include_once(get_template_directory() . '/inc/modules/ffmpeg-integration.php');
    }
    
    $ffmpeg_available = customtube_ffmpeg_is_available();
    
    // Check if clips already exist
    $clips_generated = get_post_meta($post->ID, 'clips_generated', true);
    $clips = customtube_get_available_clips($post->ID);
    
    ?>
    <div class="video-clips-section" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;">
        <h4><?php _e('Video Clips', 'customtube'); ?></h4>
        
        <?php if (!$ffmpeg_available): ?>
            <p style="color: #d63638;"><?php _e('FFmpeg is not available. Clips functionality requires FFmpeg to be installed on the server.', 'customtube'); ?></p>
        <?php else: ?>
            <?php if ($clips && !empty($clips)): ?>
                <div class="clips-list">
                    <p><?php 
                        printf(
                            __('Clips generated on %s.', 'customtube'), 
                            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $clips_generated)
                        ); 
                    ?></p>
                    
                    <table class="widefat" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th><?php _e('Type', 'customtube'); ?></th>
                                <th><?php _e('Format', 'customtube'); ?></th>
                                <th><?php _e('Size', 'customtube'); ?></th>
                                <th><?php _e('Actions', 'customtube'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clips as $clip_type => $clip): ?>
                                <tr>
                                    <td><?php echo ucfirst($clip_type); ?></td>
                                    <td><?php echo strtoupper(pathinfo($clip['file'], PATHINFO_EXTENSION)); ?></td>
                                    <td><?php echo size_format($clip['filesize']); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url($clip['url']); ?>" target="_blank" class="button button-small"><?php _e('View', 'customtube'); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="clips-actions" style="margin-top: 10px;">
                    <button type="button" id="regenerate-clips-button" class="button">
                        <?php _e('Regenerate Clips', 'customtube'); ?>
                    </button>
                    <button type="button" id="delete-clips-button" class="button">
                        <?php _e('Delete Clips', 'customtube'); ?>
                    </button>
                </div>
            <?php else: ?>
                <p><?php _e('No clips have been generated for this video.', 'customtube'); ?></p>
                <button type="button" id="generate-clips-button" class="button">
                    <?php _e('Generate Clips', 'customtube'); ?>
                </button>
            <?php endif; ?>
            
            <div id="clips-result" style="margin-top: 10px;"></div>
            <span id="clips-spinner" class="spinner"></span>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Generate clips
        $('#generate-clips-button, #regenerate-clips-button').on('click', function() {
            var button = $(this);
            var isRegenerate = button.attr('id') === 'regenerate-clips-button';
            var spinner = $('#clips-spinner');
            var result = $('#clips-result');
            
            button.prop('disabled', true);
            spinner.css('visibility', 'visible');
            result.empty();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_ajax_generate_clips',
                    post_id: <?php echo $post->ID; ?>,
                    regenerate: isRegenerate ? 1 : 0,
                    nonce: '<?php echo wp_create_nonce("customtube_generate_clips_{$post->ID}"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        result.html('<p style="color: green;">' + response.data.message + '</p>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        result.html('<p style="color: red;">' + response.data + '</p>');
                    }
                },
                error: function() {
                    result.html('<p style="color: red;"><?php _e('Error generating clips. Please try again.', 'customtube'); ?></p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.css('visibility', 'hidden');
                }
            });
        });
        
        // Delete clips
        $('#delete-clips-button').on('click', function() {
            if (!confirm('<?php _e('Are you sure you want to delete all clips for this video?', 'customtube'); ?>')) {
                return;
            }
            
            var button = $(this);
            var spinner = $('#clips-spinner');
            var result = $('#clips-result');
            
            button.prop('disabled', true);
            spinner.css('visibility', 'visible');
            result.empty();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_ajax_delete_clips',
                    post_id: <?php echo $post->ID; ?>,
                    nonce: '<?php echo wp_create_nonce("customtube_delete_clips_{$post->ID}"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        result.html('<p style="color: green;">' + response.data.message + '</p>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        result.html('<p style="color: red;">' + response.data + '</p>');
                    }
                },
                error: function() {
                    result.html('<p style="color: red;"><?php _e('Error deleting clips. Please try again.', 'customtube'); ?></p>');
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
add_action('customtube_after_video_settings', 'customtube_add_generate_clips_button');

/**
 * AJAX handler for generating clips
 */
function customtube_ajax_generate_clips() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], "customtube_generate_clips_{$_POST['post_id']}")) {
        wp_send_json_error(__('Security check failed.', 'customtube'));
    }
    
    // Check if we have the post ID
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(__('No post ID provided.', 'customtube'));
    }
    
    $post_id = intval($_POST['post_id']);
    $regenerate = isset($_POST['regenerate']) && $_POST['regenerate'] == 1;
    
    // Verify user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(__('You do not have permission to edit this post.', 'customtube'));
    }
    
    // Get post
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'video') {
        wp_send_json_error(__('Invalid post type.', 'customtube'));
    }
    
    // Check if clips exist and regeneration is required
    $has_clips = get_post_meta($post_id, 'clips_generated', true);
    if ($has_clips && !$regenerate) {
        wp_send_json_error(__('Clips already exist for this video. Use regenerate option to create new clips.', 'customtube'));
    }
    
    // If regenerating, delete existing clips
    if ($regenerate) {
        customtube_delete_video_clips($post_id);
    }
    
    // Make sure we have necessary functions
    if (!function_exists('customtube_create_video_clips')) {
        include_once(get_template_directory() . '/inc/modules/clip-management.php');
    }
    
    if (!function_exists('customtube_ffmpeg_is_available')) {
        include_once(get_template_directory() . '/inc/modules/ffmpeg-integration.php');
    }
    
    // Generate clips
    $result = customtube_create_video_clips($post_id);
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => __('Clips generated successfully.', 'customtube'),
            'clips' => $result['clips'],
        ));
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_customtube_ajax_generate_clips', 'customtube_ajax_generate_clips');

/**
 * AJAX handler for deleting clips
 */
function customtube_ajax_delete_clips() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], "customtube_delete_clips_{$_POST['post_id']}")) {
        wp_send_json_error(__('Security check failed.', 'customtube'));
    }
    
    // Check if we have the post ID
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(__('No post ID provided.', 'customtube'));
    }
    
    $post_id = intval($_POST['post_id']);
    
    // Verify user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(__('You do not have permission to edit this post.', 'customtube'));
    }
    
    // Get post
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'video') {
        wp_send_json_error(__('Invalid post type.', 'customtube'));
    }
    
    // Make sure we have necessary functions
    if (!function_exists('customtube_delete_video_clips')) {
        include_once(get_template_directory() . '/inc/modules/clip-management.php');
    }
    
    // Delete clips
    $result = customtube_delete_video_clips($post_id);
    
    if ($result) {
        wp_send_json_success(array(
            'message' => __('Clips deleted successfully.', 'customtube'),
        ));
    } else {
        wp_send_json_error(__('Failed to delete clips.', 'customtube'));
    }
}
add_action('wp_ajax_customtube_ajax_delete_clips', 'customtube_ajax_delete_clips');