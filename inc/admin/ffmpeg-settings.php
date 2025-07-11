<?php
/**
 * FFmpeg Settings and Clip Management Admin Page
 *
 * @package CustomTube
 * @subpackage Admin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register FFmpeg settings and clips management page
 */
function customtube_register_ffmpeg_settings_page() {
    add_submenu_page(
        'edit.php?post_type=video',
        __('FFmpeg & Clips Settings', 'customtube'),
        __('FFmpeg & Clips', 'customtube'),
        'manage_options',
        'customtube-ffmpeg-settings',
        'customtube_render_ffmpeg_settings_page'
    );
    
    // Register settings
    register_setting('customtube_ffmpeg_settings', 'customtube_ffmpeg_binary');
    register_setting('customtube_ffmpeg_settings', 'customtube_ffprobe_binary');
    register_setting('customtube_ffmpeg_settings', 'customtube_clip_output_dir');
    register_setting('customtube_ffmpeg_settings', 'customtube_temp_dir');
    register_setting('customtube_ffmpeg_settings', 'customtube_auto_generate_clips');
    register_setting('customtube_ffmpeg_settings', 'customtube_clip_preview_size');
    register_setting('customtube_ffmpeg_settings', 'customtube_clip_highlight_size');
    register_setting('customtube_ffmpeg_settings', 'customtube_clip_intro_size');
    register_setting('customtube_ffmpeg_settings', 'customtube_clip_gif_size');
    register_setting('customtube_ffmpeg_settings', 'customtube_clip_webp_size');
}
add_action('admin_menu', 'customtube_register_ffmpeg_settings_page');

/**
 * Render FFmpeg settings page
 */
function customtube_render_ffmpeg_settings_page() {
    // Check if FFmpeg is installed and get paths from functions
    if (!function_exists('customtube_ffmpeg_is_available')) {
        include_once(get_template_directory() . '/inc/modules/ffmpeg-integration.php');
    }
    
    $ffmpeg_available = function_exists('customtube_ffmpeg_is_available') ? customtube_ffmpeg_is_available() : false;
    
    // Default paths from constants if defined
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    $ffprobe_path = defined('FFPROBE_BINARY') ? FFPROBE_BINARY : '/usr/bin/ffprobe';
    $clip_output_dir = defined('CLIP_OUTPUT_DIR') ? CLIP_OUTPUT_DIR : WP_CONTENT_DIR . '/uploads/clips';
    $temp_dir = defined('TEMP_DIR') ? TEMP_DIR : sys_get_temp_dir();
    
    // Get settings from database
    $ffmpeg_path_setting = get_option('customtube_ffmpeg_binary', $ffmpeg_path);
    $ffprobe_path_setting = get_option('customtube_ffprobe_binary', $ffprobe_path);
    $clip_output_dir_setting = get_option('customtube_clip_output_dir', $clip_output_dir);
    $temp_dir_setting = get_option('customtube_temp_dir', $temp_dir);
    $auto_generate_clips = get_option('customtube_auto_generate_clips', '0');
    
    // Clip size settings
    $preview_size = get_option('customtube_clip_preview_size', '640x360');
    $highlight_size = get_option('customtube_clip_highlight_size', '854x480');
    $intro_size = get_option('customtube_clip_intro_size', '640x360');
    $gif_size = get_option('customtube_clip_gif_size', '320x180');
    $webp_size = get_option('customtube_clip_webp_size', '320x180');
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('FFmpeg & Clips Settings', 'customtube'); ?></h1>
        
        <div class="notice notice-info">
            <p><?php echo esc_html__('These settings control how video clips and thumbnails are generated using FFmpeg. They affect preview clips, highlight reels, and animated thumbnails.', 'customtube'); ?></p>
        </div>
        
        <?php if ($ffmpeg_available): ?>
            <div class="notice notice-success">
                <p><strong><?php echo esc_html__('FFmpeg Available:', 'customtube'); ?></strong> <?php echo esc_html__('FFmpeg is installed and accessible on this server.', 'customtube'); ?></p>
            </div>
        <?php else: ?>
            <div class="notice notice-error">
                <p><strong><?php echo esc_html__('FFmpeg Not Available:', 'customtube'); ?></strong> <?php echo esc_html__('FFmpeg is not installed or not accessible at the configured path. Clip generation functionality will not work until this is fixed.', 'customtube'); ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="options.php">
            <?php settings_fields('customtube_ffmpeg_settings'); ?>
            
            <h2><?php echo esc_html__('FFmpeg Configuration', 'customtube'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="customtube_ffmpeg_binary"><?php echo esc_html__('FFmpeg Binary Path', 'customtube'); ?></label></th>
                    <td>
                        <input type="text" id="customtube_ffmpeg_binary" name="customtube_ffmpeg_binary" value="<?php echo esc_attr($ffmpeg_path_setting); ?>" class="regular-text">
                        <p class="description"><?php echo esc_html__('The path to the FFmpeg binary (e.g., /usr/bin/ffmpeg).', 'customtube'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_ffprobe_binary"><?php echo esc_html__('FFprobe Binary Path', 'customtube'); ?></label></th>
                    <td>
                        <input type="text" id="customtube_ffprobe_binary" name="customtube_ffprobe_binary" value="<?php echo esc_attr($ffprobe_path_setting); ?>" class="regular-text">
                        <p class="description"><?php echo esc_html__('The path to the FFprobe binary (e.g., /usr/bin/ffprobe).', 'customtube'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_clip_output_dir"><?php echo esc_html__('Clip Output Directory', 'customtube'); ?></label></th>
                    <td>
                        <input type="text" id="customtube_clip_output_dir" name="customtube_clip_output_dir" value="<?php echo esc_attr($clip_output_dir_setting); ?>" class="regular-text">
                        <p class="description"><?php echo esc_html__('The directory where generated clips will be stored.', 'customtube'); ?></p>
                        <?php
                        // Check if directory exists and is writable
                        if (!file_exists($clip_output_dir_setting)) {
                            echo '<p class="description" style="color: red;">' . esc_html__('Directory does not exist. It will be created when needed, but ensure the server has permission to create it.', 'customtube') . '</p>';
                        } elseif (!is_writable($clip_output_dir_setting)) {
                            echo '<p class="description" style="color: red;">' . esc_html__('Directory exists but is not writable. Please check permissions.', 'customtube') . '</p>';
                        } else {
                            echo '<p class="description" style="color: green;">' . esc_html__('Directory exists and is writable.', 'customtube') . '</p>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_temp_dir"><?php echo esc_html__('Temporary Directory', 'customtube'); ?></label></th>
                    <td>
                        <input type="text" id="customtube_temp_dir" name="customtube_temp_dir" value="<?php echo esc_attr($temp_dir_setting); ?>" class="regular-text">
                        <p class="description"><?php echo esc_html__('The directory where temporary files will be stored during processing.', 'customtube'); ?></p>
                        <?php
                        // Check if directory exists and is writable
                        if (!file_exists($temp_dir_setting)) {
                            echo '<p class="description" style="color: red;">' . esc_html__('Directory does not exist. Please specify a valid directory.', 'customtube') . '</p>';
                        } elseif (!is_writable($temp_dir_setting)) {
                            echo '<p class="description" style="color: red;">' . esc_html__('Directory exists but is not writable. Please check permissions.', 'customtube') . '</p>';
                        } else {
                            echo '<p class="description" style="color: green;">' . esc_html__('Directory exists and is writable.', 'customtube') . '</p>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            
            <h2><?php echo esc_html__('Clip Generation Settings', 'customtube'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="customtube_auto_generate_clips"><?php echo esc_html__('Auto-generate Clips', 'customtube'); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="customtube_auto_generate_clips" name="customtube_auto_generate_clips" value="1" <?php checked('1', $auto_generate_clips); ?>>
                            <?php echo esc_html__('Automatically generate clips when a new video is uploaded', 'customtube'); ?>
                        </label>
                        <p class="description"><?php echo esc_html__('When enabled, clips will be generated in the background when a new video is saved.', 'customtube'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_clip_preview_size"><?php echo esc_html__('Preview Clip Size', 'customtube'); ?></label></th>
                    <td>
                        <select id="customtube_clip_preview_size" name="customtube_clip_preview_size">
                            <option value="320x180" <?php selected('320x180', $preview_size); ?>><?php echo esc_html__('Small (320x180)', 'customtube'); ?></option>
                            <option value="640x360" <?php selected('640x360', $preview_size); ?>><?php echo esc_html__('Medium (640x360)', 'customtube'); ?></option>
                            <option value="854x480" <?php selected('854x480', $preview_size); ?>><?php echo esc_html__('Large (854x480)', 'customtube'); ?></option>
                            <option value="1280x720" <?php selected('1280x720', $preview_size); ?>><?php echo esc_html__('HD (1280x720)', 'customtube'); ?></option>
                        </select>
                        <p class="description"><?php echo esc_html__('The size of generated preview clips.', 'customtube'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_clip_highlight_size"><?php echo esc_html__('Highlight Clip Size', 'customtube'); ?></label></th>
                    <td>
                        <select id="customtube_clip_highlight_size" name="customtube_clip_highlight_size">
                            <option value="640x360" <?php selected('640x360', $highlight_size); ?>><?php echo esc_html__('Medium (640x360)', 'customtube'); ?></option>
                            <option value="854x480" <?php selected('854x480', $highlight_size); ?>><?php echo esc_html__('Large (854x480)', 'customtube'); ?></option>
                            <option value="1280x720" <?php selected('1280x720', $highlight_size); ?>><?php echo esc_html__('HD (1280x720)', 'customtube'); ?></option>
                        </select>
                        <p class="description"><?php echo esc_html__('The size of generated highlight clips.', 'customtube'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_clip_intro_size"><?php echo esc_html__('Intro Clip Size', 'customtube'); ?></label></th>
                    <td>
                        <select id="customtube_clip_intro_size" name="customtube_clip_intro_size">
                            <option value="320x180" <?php selected('320x180', $intro_size); ?>><?php echo esc_html__('Small (320x180)', 'customtube'); ?></option>
                            <option value="640x360" <?php selected('640x360', $intro_size); ?>><?php echo esc_html__('Medium (640x360)', 'customtube'); ?></option>
                            <option value="854x480" <?php selected('854x480', $intro_size); ?>><?php echo esc_html__('Large (854x480)', 'customtube'); ?></option>
                        </select>
                        <p class="description"><?php echo esc_html__('The size of generated intro clips.', 'customtube'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_clip_gif_size"><?php echo esc_html__('GIF Animation Size', 'customtube'); ?></label></th>
                    <td>
                        <select id="customtube_clip_gif_size" name="customtube_clip_gif_size">
                            <option value="160x90" <?php selected('160x90', $gif_size); ?>><?php echo esc_html__('Tiny (160x90)', 'customtube'); ?></option>
                            <option value="240x135" <?php selected('240x135', $gif_size); ?>><?php esc_html__('Small (240x135)', 'customtube'); ?></option>
                            <option value="320x180" <?php selected('320x180', $gif_size); ?>><?php esc_html__('Medium (320x180)', 'customtube'); ?></option>
                        </select>
                        <p class="description"><?php echo esc_html__('The size of generated GIF animations. Keep small for better performance.', 'customtube'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="customtube_clip_webp_size"><?php echo esc_html__('WebP Animation Size', 'customtube'); ?></label></th>
                    <td>
                        <select id="customtube_clip_webp_size" name="customtube_clip_webp_size">
                            <option value="240x135" <?php selected('240x135', $webp_size); ?>><?php echo esc_html__('Small (240x135)', 'customtube'); ?></option>
                            <option value="320x180" <?php selected('320x180', $webp_size); ?>><?php echo esc_html__('Medium (320x180)', 'customtube'); ?></option>
                            <option value="480x270" <?php selected('480x270', $webp_size); ?>><?php echo esc_html__('Large (480x270)', 'customtube'); ?></option>
                        </select>
                        <p class="description"><?php echo esc_html__('The size of generated WebP animations. WebP is more efficient than GIF.', 'customtube'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php echo esc_html__('Generate Clips in Bulk', 'customtube'); ?></h2>
            <p><?php echo esc_html__('You can generate clips for all self-hosted videos that don\'t already have clips.', 'customtube'); ?></p>
            
            <div id="bulk-clips-controls">
                <button id="start-bulk-generation" class="button button-primary"><?php echo esc_html__('Start Bulk Generation', 'customtube'); ?></button>
                <p class="description"><?php echo esc_html__('This process will run in the background. You can close this page after starting.', 'customtube'); ?></p>
                <div id="bulk-clips-results" style="margin-top: 10px;"></div>
                <div class="progress-bar-container" style="margin-top: 10px; display: none;">
                    <div id="bulk-clips-progress"></div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#start-bulk-generation').on('click', function() {
                    if (!confirm('<?php echo esc_js(__('This will generate clips for all videos without clips. It may take a significant amount of server resources. Continue?', 'customtube')); ?>')) {
                        return;
                    }
                    
                    var button = $(this);
                    button.prop('disabled', true);
                    
                    $('#bulk-clips-results').html('<p><?php echo esc_js(__('Scanning for videos without clips...', 'customtube')); ?></p>');
                    
                    // First, get a count of videos without clips
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'customtube_count_videos_without_clips',
                            nonce: '<?php echo wp_create_nonce("customtube_bulk_clips"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                var totalVideos = response.data.count;
                                if (totalVideos === 0) {
                                    $('#bulk-clips-results').html('<p><?php echo esc_js(__('No videos found that need clips.', 'customtube')); ?></p>');
                                    button.prop('disabled', false);
                                    return;
                                }
                                
                                $('#bulk-clips-results').html('<p>' + 
                                    '<?php echo esc_js(__('Found', 'customtube')); ?> ' + 
                                    totalVideos + 
                                    ' <?php echo esc_js(__('videos without clips. Starting processing...', 'customtube')); ?></p>'
                                );
                                
                                // Show progress bar
                                $('.progress-bar-container').show();
                                
                                // Start processing in batches
                                processBatch(0, totalVideos, 5); // Process 5 at a time
                            } else {
                                $('#bulk-clips-results').html('<p style="color: red;">' + response.data + '</p>');
                                button.prop('disabled', false);
                            }
                        },
                        error: function() {
                            $('#bulk-clips-results').html('<p style="color: red;"><?php echo esc_js(__('Error counting videos. Please try again.', 'customtube')); ?></p>');
                            button.prop('disabled', false);
                        }
                    });
                    
                    // Function to process videos in batches
                    function processBatch(processed, total, batchSize) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'customtube_process_bulk_clips',
                                offset: processed,
                                limit: batchSize,
                                nonce: '<?php echo wp_create_nonce("customtube_bulk_clips"); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    var newProcessed = processed + response.data.processed;
                                    var progress = Math.round((newProcessed / total) * 100);
                                    
                                    // Update progress
                                    $('#bulk-clips-progress').css('width', progress + '%');
                                    $('#bulk-clips-results').html(
                                        '<p><?php echo esc_js(__('Processing videos...', 'customtube')); ?> ' + 
                                        newProcessed + ' <?php echo esc_js(__('of', 'customtube')); ?> ' + 
                                        total + ' (' + progress + '%)</p>'
                                    );
                                    
                                    // Check if we need to continue
                                    if (newProcessed < total && response.data.processed > 0) {
                                        processBatch(newProcessed, total, batchSize);
                                    } else {
                                        $('#bulk-clips-results').html(
                                            '<p style="color: green;"><?php echo esc_js(__('Completed processing', 'customtube')); ?> ' + 
                                            newProcessed + ' <?php echo esc_js(__('videos.', 'customtube')); ?></p>'
                                        );
                                        button.prop('disabled', false);
                                    }
                                } else {
                                    $('#bulk-clips-results').html('<p style="color: red;">' + response.data + '</p>');
                                    button.prop('disabled', false);
                                }
                            },
                            error: function() {
                                $('#bulk-clips-results').html(
                                    '<p style="color: red;"><?php echo esc_js(__('Error processing batch. Processed', 'customtube')); ?> ' + 
                                    processed + ' <?php echo esc_js(__('of', 'customtube')); ?> ' + 
                                    total + ' <?php echo esc_js(__('videos.', 'customtube')); ?></p>'
                                );
                                button.prop('disabled', false);
                            }
                        });
                    }
                });
            });
            </script>
        </div>
    </div>
    <?php
}

/**
 * AJAX handler to count videos without clips
 */
function customtube_ajax_count_videos_without_clips() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_bulk_clips')) {
        wp_send_json_error(__('Security check failed.', 'customtube'));
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You do not have permission to do this.', 'customtube'));
    }
    
    // Count videos without clips
    $args = array(
        'post_type' => 'video',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'video_source_type',
                'value' => 'direct',
                'compare' => '='
            ),
            array(
                'relation' => 'OR',
                array(
                    'key' => 'clips_generated',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'clips_generated',
                    'value' => '',
                    'compare' => '='
                )
            )
        ),
        'fields' => 'ids'
    );
    
    $videos = get_posts($args);
    
    wp_send_json_success(array(
        'count' => count($videos)
    ));
}
add_action('wp_ajax_customtube_count_videos_without_clips', 'customtube_ajax_count_videos_without_clips');

/**
 * AJAX handler to process videos in bulk
 */
function customtube_ajax_process_bulk_clips() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_bulk_clips')) {
        wp_send_json_error(__('Security check failed.', 'customtube'));
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You do not have permission to do this.', 'customtube'));
    }
    
    // Get parameters
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
    
    // Get videos without clips
    $args = array(
        'post_type' => 'video',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'offset' => $offset,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'video_source_type',
                'value' => 'direct',
                'compare' => '='
            ),
            array(
                'relation' => 'OR',
                array(
                    'key' => 'clips_generated',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'clips_generated',
                    'value' => '',
                    'compare' => '='
                )
            )
        ),
        'fields' => 'ids'
    );
    
    $videos = get_posts($args);
    
    // Make sure we have necessary functions
    if (!function_exists('customtube_create_video_clips')) {
        include_once(get_template_directory() . '/inc/modules/clip-management.php');
    }
    
    if (!function_exists('customtube_ffmpeg_is_available')) {
        include_once(get_template_directory() . '/inc/modules/ffmpeg-integration.php');
    }
    
    // Schedule clip generation for each video
    $processed = 0;
    foreach ($videos as $post_id) {
        // Schedule the clip generation as a non-blocking background task
        wp_schedule_single_event(time() + ($processed * 30), 'customtube_generate_clips_event', array($post_id));
        $processed++;
    }
    
    wp_send_json_success(array(
        'processed' => $processed
    ));
}
add_action('wp_ajax_customtube_process_bulk_clips', 'customtube_ajax_process_bulk_clips');

/**
 * Add menu link to FFmpeg settings in WordPress admin bar
 */
function customtube_admin_bar_ffmpeg_link($admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $admin_bar->add_node(array(
        'id'    => 'customtube-ffmpeg-settings',
        'parent' => 'site-name',
        'title' => __('FFmpeg & Clips Settings', 'customtube'),
        'href'  => admin_url('edit.php?post_type=video&page=customtube-ffmpeg-settings'),
    ));
}
add_action('admin_bar_menu', 'customtube_admin_bar_ffmpeg_link', 100);

/**
 * Add status of clips to videos list
 */
function customtube_add_clips_column($columns) {
    $columns['clips'] = __('Clips', 'customtube');
    return $columns;
}
add_filter('manage_video_posts_columns', 'customtube_add_clips_column');

/**
 * Display clips status in admin columns
 */
function customtube_display_clips_column($column, $post_id) {
    if ($column !== 'clips') {
        return;
    }
    
    $video_source_type = get_post_meta($post_id, 'video_source_type', true);
    if ($video_source_type !== 'direct') {
        echo '<span class="dashicons dashicons-no" style="color: #ccc;" title="' . 
             esc_attr__('External video, clips not available', 'customtube') . '"></span>';
        return;
    }
    
    $clips_generated = get_post_meta($post_id, 'clips_generated', true);
    if (!$clips_generated) {
        echo '<a href="#" class="generate-clips-button button button-small" data-post-id="' . esc_attr($post_id) . '">' . 
             __('Generate', 'customtube') . '</a>';
        echo '<span class="spinner"></span>';
    } else {
        $clips_available = get_post_meta($post_id, 'clips_available', true);
        if (!empty($clips_available)) {
            echo '<span class="dashicons dashicons-yes" style="color: #46b450;" title="' . 
                 esc_attr__('Clips available', 'customtube') . '"></span> ';
            echo '<span class="clip-count">(' . count($clips_available) . ')</span>';
        } else {
            echo '<span class="dashicons dashicons-warning" style="color: #ffb900;" title="' . 
                 esc_attr__('Generation attempted but no clips available', 'customtube') . '"></span>';
        }
    }
}
add_action('manage_video_posts_custom_column', 'customtube_display_clips_column', 10, 2);

/**
 * Add JavaScript for quick clip generation in posts list
 */
function customtube_admin_clips_js() {
    $screen = get_current_screen();
    if ($screen->base !== 'edit' || $screen->post_type !== 'video') {
        return;
    }
    
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.generate-clips-button').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var postId = button.data('post-id');
            var spinner = button.siblings('.spinner');
            
            button.hide();
            spinner.css('visibility', 'visible');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_ajax_generate_clips',
                    post_id: postId,
                    regenerate: 0,
                    nonce: '<?php echo wp_create_nonce("customtube_generate_clips_list"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var clipCount = Object.keys(response.data.clips).length;
                        spinner.css('visibility', 'hidden');
                        button.after('<span class="dashicons dashicons-yes" style="color: #46b450;"></span> <span class="clip-count">(' + clipCount + ')</span>');
                    } else {
                        spinner.css('visibility', 'hidden');
                        button.show();
                        alert(response.data);
                    }
                },
                error: function() {
                    spinner.css('visibility', 'hidden');
                    button.show();
                    alert('<?php echo esc_js(__('Error generating clips. Please try again.', 'customtube')); ?>');
                }
            });
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'customtube_admin_clips_js');

/**
 * AJAX handler for quick clip generation from posts list
 */
function customtube_ajax_generate_clips_list() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_generate_clips_list')) {
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
        wp_send_json_error(__('You do not have permission to do this.', 'customtube'));
    }
    
    // Get post
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'video') {
        wp_send_json_error(__('Invalid post type.', 'customtube'));
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
add_action('wp_ajax_customtube_ajax_generate_clips_list', 'customtube_ajax_generate_clips_list');

/**
 * Define constants from settings if not already defined
 */
function customtube_define_ffmpeg_constants() {
    if (!defined('FFMPEG_BINARY')) {
        $ffmpeg_path = get_option('customtube_ffmpeg_binary', '/usr/bin/ffmpeg');
        define('FFMPEG_BINARY', $ffmpeg_path);
    }
    
    if (!defined('FFPROBE_BINARY')) {
        $ffprobe_path = get_option('customtube_ffprobe_binary', '/usr/bin/ffprobe');
        define('FFPROBE_BINARY', $ffprobe_path);
    }
    
    if (!defined('CLIP_OUTPUT_DIR')) {
        $clip_output_dir = get_option('customtube_clip_output_dir', WP_CONTENT_DIR . '/uploads/clips');
        define('CLIP_OUTPUT_DIR', $clip_output_dir);
    }
    
    if (!defined('TEMP_DIR')) {
        $temp_dir = get_option('customtube_temp_dir', sys_get_temp_dir());
        define('TEMP_DIR', $temp_dir);
    }
}
add_action('init', 'customtube_define_ffmpeg_constants');
