<?php
/**
 * Admin UI Module for CustomTube
 * Handles admin UI integration for auto-detection
 *
 * @package CustomTube
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add the auto-detect URL UI to the post editor
 */
function customtube_add_auto_detect_url_ui() {
    // Debug information
    error_log("customtube_add_auto_detect_url_ui called");
    
    // Get current screen
    $screen = get_current_screen();
    
    // Log screen info
    error_log("Current screen: " . ($screen ? $screen->id . " / " . $screen->post_type : "No screen"));
    
    // Only add on video post type edit screen
    if (!$screen || $screen->post_type !== 'video') {
        error_log("Not a video post type edit screen - skipping auto-detect UI");
        return;
    }
    
    // Don't add UI for Block Editor - we handle that separately with JavaScript 
    if (function_exists('use_block_editor_for_post_type') && use_block_editor_for_post_type($screen->post_type)) {
        error_log("Block editor detected - skipping classic UI output");
        return;
    }
    
    error_log("Adding auto-detect UI to classic editor for video post type");
    
    // Enqueue the JavaScript for auto-detection
    wp_enqueue_script(
        'customtube-auto-detect', 
        get_template_directory_uri() . '/assets/js/admin/auto-detect.js', 
        array('jquery'), 
        filemtime(get_template_directory() . '/assets/js/admin/auto-detect.js'), 
        true
    );
    
    // Localize the script with data
    wp_localize_script('customtube-auto-detect', 'customtube_auto_detect', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('customtube_auto_detect_url'),
        'nonce_thumbnail' => wp_create_nonce('customtube_auto_set_thumbnail'),
        'nonce_metadata' => wp_create_nonce('customtube_save_metadata'),
        'nonce_scrape' => wp_create_nonce('customtube_direct_scrape'),
        'labels' => array(
            'paste_url' => __('Paste Video URL', 'customtube'),
            'description' => __('Simply paste any video URL (YouTube, Vimeo, PornHub, XVideos, direct MP4, etc.) and fields will be automatically populated.', 'customtube'),
            'placeholder' => __('Paste or type video URL here - auto-detection happens immediately', 'customtube'),
            'success' => __('Video configured successfully!', 'customtube'),
            'error' => __('Error detecting URL.', 'customtube'),
            'server_error' => __('Error communicating with the server.', 'customtube'),
            'auto_populated' => __('Auto-populated:', 'customtube'),
            'thumbnail_set' => __('Thumbnail automatically set!', 'customtube'),
            'thumbnail_error' => __('Could not set thumbnail automatically.', 'customtube'),
            'thumbnail_manual' => __('Click "Extract Thumbnail from Video" to try manually.', 'customtube'),
            'thumbnail_server_error' => __('Error setting thumbnail automatically.', 'customtube'),
            'data_loaded' => __('Video data loaded! Click "Publish" when ready.', 'customtube'),
            'data_loaded_notice' => __('Video data loaded. Click "Publish" to save.', 'customtube')
        )
    ));
    
    // Output the auto-detect UI HTML - only for the Classic Editor
    ?>
    <div class="auto-detect-url-container classic-editor-only">
        <h3><?php _e('Paste Video URL', 'customtube'); ?></h3>
        <p class="description"><?php _e('Simply paste any video URL (YouTube, Vimeo, PornHub, XVideos, direct MP4, etc.) and fields will be automatically populated.', 'customtube'); ?></p>
        <div class="auto-detect-url-input-container">
            <input type="url" id="auto_detect_url" class="widefat" 
                placeholder="<?php _e('Paste or type video URL here - auto-detection happens immediately', 'customtube'); ?>">
            <span id="auto-detect-spinner" class="spinner"></span>
        </div>
        <div id="auto-detect-result"></div>
    </div>
    <style>
    .auto-detect-url-container {
        margin-bottom: 20px;
        background: #f0f6fc;
        padding: 20px;
        border: 1px solid #2271b1;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .auto-detect-url-container h3 {
        margin-top: 0;
        color: #2271b1;
    }
    .auto-detect-url-input-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    #auto_detect_url {
        flex: 1;
        padding: 12px;
        font-size: 14px;
        border: 1px solid #8c8f94;
    }
    #auto-detect-spinner {
        float: none;
        margin: 0;
    }
    .auto-detect-notice {
        padding: 8px;
        margin: 10px 0;
    }
    
    /* Hide the Classic Editor UI if Block Editor is active */
    body.block-editor-page .classic-editor-only {
        display: none;
    }
    </style>
    <?php
}

/**
 * Initialize the admin UI integration
 */
function customtube_init_admin_ui() {
    error_log("Initializing admin UI integration");
    
    // Add the auto-detect UI to the post editor - CRITICAL for the URL box to appear!
    add_action('edit_form_after_title', 'customtube_add_auto_detect_url_ui', 10);
    error_log("Added hook: edit_form_after_title -> customtube_add_auto_detect_url_ui");
    
    // Also add it to the add_meta_boxes hook as a backup
    add_action('add_meta_boxes_video', 'customtube_add_auto_detect_meta_box');
    error_log("Added hook: add_meta_boxes_video -> customtube_add_auto_detect_meta_box");
    
    // Add support for Gutenberg block editor
    add_action('enqueue_block_editor_assets', 'customtube_enqueue_gutenberg_assets');
    error_log("Added hook: enqueue_block_editor_assets -> customtube_enqueue_gutenberg_assets");
    
    // Enqueue the scripts and styles
    add_action('admin_enqueue_scripts', 'customtube_enqueue_auto_detect_scripts');
    error_log("Added hook: admin_enqueue_scripts -> customtube_enqueue_auto_detect_scripts");
}

/**
 * Add a meta box for auto-detect URL (as a backup approach)
 */
function customtube_add_auto_detect_meta_box() {
    error_log("Adding auto-detect URL meta box");
    
    // Skip meta box for Block Editor, since we're adding the UI via JavaScript
    $screen = get_current_screen();
    if (function_exists('use_block_editor_for_post_type') && 
        $screen && $screen->post_type === 'video' && 
        use_block_editor_for_post_type($screen->post_type)) {
        error_log("Block editor detected - skipping auto-detect meta box");
        return;
    }
    
    add_meta_box(
        'customtube-auto-detect-url',
        __('Auto-Detect Video URL', 'customtube'),
        'customtube_add_auto_detect_meta_box_content',
        'video',
        'normal',
        'high'
    );
}

/**
 * Content callback for auto-detect URL meta box
 */
function customtube_add_auto_detect_meta_box_content() {
    // This function is specifically for the meta box version of the auto-detect UI
    ?>
    <div class="auto-detect-url-container-meta-box">
        <p class="description"><?php _e('Simply paste any video URL (YouTube, Vimeo, PornHub, XVideos, direct MP4, etc.) and fields will be automatically populated.', 'customtube'); ?></p>
        <div class="auto-detect-url-input-container">
            <input type="url" id="auto_detect_url_meta_box" class="widefat" 
                placeholder="<?php _e('Paste or type video URL here - auto-detection happens immediately', 'customtube'); ?>">
            <span id="auto-detect-spinner-meta-box" class="spinner"></span>
        </div>
        <div id="auto-detect-result-meta-box"></div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        // Redirect the meta box input to the main auto-detect input
        $('#auto_detect_url_meta_box').on('input paste', function(e) {
            var value = $(this).val();
            if (value) {
                // Forward the value to the main input
                var mainInput = $('#auto_detect_url');
                if (mainInput.length) {
                    mainInput.val(value).trigger('input');
                    // Clear this input after forwarding
                    $(this).val('');
                }
            }
        });
    });
    </script>
    <?php
}

/**
 * Enqueue scripts and styles for the auto-detect functionality
 */
function customtube_enqueue_auto_detect_scripts($hook) {
    error_log("customtube_enqueue_auto_detect_scripts called with hook: $hook");
    
    $screen = get_current_screen();
    error_log("Screen: " . ($screen ? $screen->id . " / " . $screen->post_type : "No screen"));
    
    if (!$screen || $screen->post_type !== 'video') {
        error_log("Not on video post screen - skipping script enqueuing");
        return;
    }
    
    // Enqueue any scripts or styles needed for auto-detection
    wp_enqueue_script('jquery');
    error_log("Enqueued jQuery");
    
    // Only on post edit screens
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        error_log("On post edit screen - loading auto-detect CSS");
        
        // CSS for the auto-detect UI
        $css_file = get_template_directory_uri() . '/assets/css/admin/auto-detect.css';
        $css_path = get_template_directory() . '/assets/css/admin/auto-detect.css';
        
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'customtube-auto-detect-css', 
                $css_file,
                array(),
                filemtime($css_path)
            );
            error_log("Successfully enqueued auto-detect CSS: $css_file");
        } else {
            error_log("WARNING: Auto-detect CSS file not found: $css_path");
        }
    }
}

/**
 * Enqueue assets for the Gutenberg block editor
 */
function customtube_enqueue_gutenberg_assets() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'video') {
        return;
    }
    
    error_log("Enqueueing Gutenberg assets for video post type");
    
    // Enqueue the JavaScript for auto-detection
    wp_enqueue_script(
        'customtube-auto-detect', 
        get_template_directory_uri() . '/assets/js/admin/auto-detect.js', 
        array('jquery', 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'), 
        filemtime(get_template_directory() . '/assets/js/admin/auto-detect.js'), 
        true
    );
    
    // Localize the script with data
    wp_localize_script('customtube-auto-detect', 'customtube_auto_detect', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('customtube_auto_detect_url'),
        'nonce_thumbnail' => wp_create_nonce('customtube_auto_set_thumbnail'),
        'nonce_metadata' => wp_create_nonce('customtube_save_metadata'),
        'nonce_scrape' => wp_create_nonce('customtube_direct_scrape'),
        'is_gutenberg' => true,
        'labels' => array(
            'paste_url' => __('Paste Video URL', 'customtube'),
            'description' => __('Simply paste any video URL (YouTube, Vimeo, PornHub, XVideos, direct MP4, etc.) and fields will be automatically populated.', 'customtube'),
            'placeholder' => __('Paste or type video URL here - auto-detection happens immediately', 'customtube'),
            'success' => __('Video configured successfully!', 'customtube'),
            'error' => __('Error detecting URL.', 'customtube'),
            'server_error' => __('Error communicating with the server.', 'customtube'),
            'auto_populated' => __('Auto-populated:', 'customtube'),
            'thumbnail_set' => __('Thumbnail automatically set!', 'customtube'),
            'thumbnail_error' => __('Could not set thumbnail automatically.', 'customtube'),
            'thumbnail_manual' => __('Click "Extract Thumbnail from Video" to try manually.', 'customtube'),
            'thumbnail_server_error' => __('Error setting thumbnail automatically.', 'customtube'),
            'data_loaded' => __('Video data loaded! Click "Publish" when ready.', 'customtube'),
            'data_loaded_notice' => __('Video data loaded. Click "Publish" to save.', 'customtube')
        )
    ));
    
    // CSS for the auto-detect UI
    $css_file = get_template_directory_uri() . '/assets/css/admin/auto-detect.css';
    $css_path = get_template_directory() . '/assets/css/admin/auto-detect.css';
    
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'customtube-auto-detect-css',
            $css_file,
            array(),
            filemtime($css_path)
        );
        error_log("Successfully enqueued auto-detect CSS for Gutenberg: $css_file");
    }
    
    // Register a special hook to add our UI to the Block Editor
    add_action('admin_footer', 'customtube_add_gutenberg_auto_detect_ui');
}

/**
 * Add auto-detect UI to the Block Editor via JavaScript
 */
function customtube_add_gutenberg_auto_detect_ui() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'video') {
        return;
    }
    
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Check if we're in the block editor
        if (!$('.wp-block').length && !$('.block-editor').length) {
            console.log('Not in Block Editor - skipping additional UI integration');
            return;
        }
        
        console.log('Block Editor detected - initializing auto-detect UI');
        
        // Function to ensure events are bound
        function ensureAutoDetectIsWorking() {
            // Check if the auto-detect input exists in the meta box
            if ($('.gutenberg-in-metabox #auto_detect_url').length) {
                console.log('Auto-detect URL input found in Video Details meta box');
                
                // Make sure events are bound
                if (typeof CustomTubeAutoDetect !== 'undefined') {
                    console.log('Binding events to the auto-detect input in meta box');
                    CustomTubeAutoDetect.bindEvents();
                    
                    // Show initialization message
                    $('#auto-detect-result').html(
                        '<p style="color: #2271b1;">Auto-detect box initialized. Paste a video URL above to automatically extract metadata.</p>'
                    );
                }
                return true;
            }
            return false;
        }
        
        // Try multiple times with increasing delays
        // This helps with different WordPress versions and page load timing
        setTimeout(ensureAutoDetectIsWorking, 500);
        setTimeout(ensureAutoDetectIsWorking, 1000);
        setTimeout(ensureAutoDetectIsWorking, 2000);
        
        // Also try when Gutenberg signals it's ready
        if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
            var hasRun = false;
            wp.data.subscribe(function() {
                // Only run once
                if (hasRun) return;
                
                // Check if editor is initialized
                var isInitialized = false;
                
                try {
                    // Different ways to check, depending on WP version
                    if (wp.data.select('core/editor') && wp.data.select('core/editor').isCleanNewPost) {
                        isInitialized = true;
                    } else if (wp.data.select('core/edit-post') && wp.data.select('core/edit-post').isFeatureActive) {
                        isInitialized = true;
                    }
                } catch(e) {
                    // Ignore errors with data API
                }
                
                if (isInitialized) {
                    hasRun = true;
                    setTimeout(ensureAutoDetectIsWorking, 300);
                }
            });
        }
        
        // Special styling for the meta box in Block Editor
        var style = document.createElement('style');
        style.textContent = `
          .gutenberg-in-metabox {
            background: #f0f6fc;
            padding: 15px;
            margin: -16px -16px 20px -16px;
            border-bottom: 2px solid #2271b1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
          }
          .gutenberg-in-metabox h3 {
            margin-top: 0 !important;
          }
          #auto-detect-result {
            margin-top: 10px;
          }
        `;
        document.head.appendChild(style);
    });
    </script>
    <?php
}

// Initialize the admin UI - THIS IS THE KEY LINE FOR IT TO WORK!
add_action('admin_init', 'customtube_init_admin_ui');
error_log("Registered admin_init hook for customtube_init_admin_ui");