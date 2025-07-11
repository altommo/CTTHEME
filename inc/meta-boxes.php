<?php
/**
 * Meta Boxes for Video Post Type
 *
 * @package CustomTube
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Only declare these if they don't already exist
if ( ! function_exists( 'customtube_add_meta_boxes' ) ) {
    /**
     * Register custom meta boxes for the video post type.
     */
    function customtube_add_meta_boxes() {
        add_meta_box(
            'video_details',
            __( 'Video Details', 'customtube' ),
            'customtube_video_details_callback',
            'video',
            'normal',
            'high'
        );
    }
    add_action( 'add_meta_boxes', 'customtube_add_meta_boxes' );
}

if ( ! function_exists( 'customtube_video_details_callback' ) ) {
    /**
     * Video details meta box callback.
     *
     * @param WP_Post $post The post object.
     */
    function customtube_video_details_callback( $post ) {
        wp_nonce_field( 'customtube_video_details', 'customtube_video_details_nonce' );

        // Retrieve existing meta values.
        $video_url      = get_post_meta( $post->ID, 'video_url', true );
        $embed_code     = get_post_meta( $post->ID, 'embed_code', true );
        $preview_url    = get_post_meta( $post->ID, 'preview_url', true );
        $duration       = get_post_meta( $post->ID, 'video_duration', true );
        $quality_options = get_post_meta( $post->ID, 'quality_options', true );
        $video_source_type = get_post_meta( $post->ID, 'video_source_type', true ) ?: 'direct';

        if ( ! is_array( $quality_options ) ) {
            $quality_options = array(
                '360p'  => '',
                '720p'  => '',
                '1080p' => '',
            );
        }
        
        // Check if we're using the Block Editor
        $using_block_editor = function_exists('use_block_editor_for_post_type') && 
                               use_block_editor_for_post_type('video');
        
        // Only output auto-detect UI in the meta box for Block Editor
        if ($using_block_editor) {
            ?>
            <div class="auto-detect-url-container gutenberg-in-metabox">
                <h3 style="margin-top: 0; color: #2271b1;"><?php _e('Paste Video URL', 'customtube'); ?></h3>
                <p class="description"><?php _e('Simply paste any video URL (YouTube, Vimeo, PornHub, XVideos, direct MP4, etc.) and fields will be automatically populated.', 'customtube'); ?></p>
                <div class="auto-detect-url-input-container" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                    <input type="url" id="auto_detect_url" class="widefat" 
                        placeholder="<?php _e('Paste or type video URL here - auto-detection happens immediately', 'customtube'); ?>"
                        style="flex: 1; padding: 12px; font-size: 14px; border: 1px solid #8c8f94;">
                    <span id="auto-detect-spinner" class="spinner"></span>
                </div>
                <div id="auto-detect-result"></div>
            </div>
            <hr style="margin: 20px 0;">
            <?php
        }
        ?>
        <div class="customtube-meta-box">
            <!-- Simplified Video Entry - Single URL field for all types -->
            <p>
                <input type="hidden" name="video_source_type" value="direct" />
                
                <!-- Video URL -->
                <p>
                    <label for="video_url"><strong><?php esc_html_e( 'Video URL', 'customtube' ); ?></strong></label>
                    <input type="url" id="video_url" name="video_url"
                        value="<?php echo esc_url( $video_url ); ?>" class="widefat" required />
                    <span class="description">
                        <?php esc_html_e( 'Paste any video URL here - YouTube, Vimeo, PornHub, or direct MP4 video links all work.', 'customtube' ); ?>
                    </span>
                    <button type="button" id="test-video-url" class="button button-secondary" style="margin-top: 5px;">
                        <?php esc_html_e( 'Test Video URL', 'customtube' ); ?>
                    </button>
                </p>
                
                <!-- Hidden Embed Code (still kept for backwards compatibility) -->
                <input type="hidden" id="embed_code" name="embed_code" value="<?php echo esc_attr($embed_code); ?>" />
                
                <div class="supported-examples">
                    <h4><?php esc_html_e( 'Supported URL Types', 'customtube' ); ?></h4>
                    <ul>
                        <li><strong>YouTube:</strong> https://www.youtube.com/watch?v=abcd1234</li>
                        <li><strong>Vimeo:</strong> https://vimeo.com/123456789</li>
                        <li><strong>Pornhub:</strong> https://www.pornhub.com/view_video.php?viewkey=abc123</li>
                        <li><strong>XVideos:</strong> https://www.xvideos.com/video12345/title</li>
                        <li><strong>XHamster:</strong> https://xhamster.com/videos/title-12345</li>
                        <li><strong>Direct video:</strong> https://example.com/videos/video.mp4</li>
                    </ul>
                </div>

                <!-- Quality Options (only for MP4 videos) -->
                <div class="quality-options-container">
                    <h4><?php esc_html_e( 'Quality Options', 'customtube' ); ?></h4>
                    <p class="description">
                        <?php esc_html_e( 'Optional alternate quality URLs (only for MP4 videos)', 'customtube' ); ?>
                    </p>
                    <?php foreach ( $quality_options as $label => $url ) : ?>
                        <div class="quality-option">
                            <label for="quality_<?php echo esc_attr( $label ); ?>">
                                <strong><?php echo esc_html( strtoupper( $label ) . ' URL' ); ?></strong>
                            </label>
                            <input type="url"
                                id="quality_<?php echo esc_attr( $label ); ?>"
                                name="quality_options[<?php echo esc_attr( $label ); ?>]"
                                value="<?php echo esc_url( $url ); ?>"
                                class="widefat" />
                        </div>
                    <?php endforeach; ?>
                </div>
            </p>

            <!-- Preview URL (common for both types) -->
            <p>
                <label for="preview_url"><strong><?php esc_html_e( 'Preview Video URL (hover)', 'customtube' ); ?></strong></label>
                <input type="url" id="preview_url" name="preview_url"
                    value="<?php echo esc_url( $preview_url ); ?>" class="widefat" />
                <span class="description">
                    <?php esc_html_e( 'Enter a short preview video URL for thumbnail hover (optional)', 'customtube' ); ?>
                </span>
            </p>

            <!-- Duration (common for both types) -->
            <p>
                <label for="video_duration"><strong><?php esc_html_e( 'Video Duration', 'customtube' ); ?></strong></label>
                <input type="text" id="video_duration" name="video_duration"
                    value="<?php echo esc_attr( $duration ); ?>"
                    placeholder="MM:SS or HH:MM:SS"
                    pattern="^([0-9]+:)?[0-5]?[0-9]:[0-5][0-9]$"
                    class="regular-text" />
                <span class="description">
                    <?php esc_html_e( 'Enter duration in MM:SS or HH:MM:SS format or click "Auto-detect" to fetch it automatically', 'customtube' ); ?>
                </span>
                <button type="button" id="auto-detect-duration" class="button button-secondary">
                    <?php esc_html_e( 'Auto-detect', 'customtube' ); ?>
                </button>
                <span id="duration-spinner" class="spinner"></span>
            </p>
            
            <!-- Additional Video Metadata (hidden fields) -->
            <div class="video-metadata-fields" style="display: none;">
                <input type="hidden" id="duration_seconds" name="duration_seconds" value="<?php echo esc_attr( get_post_meta( $post->ID, 'duration_seconds', true ) ); ?>" />
                <input type="hidden" id="video_width" name="video_width" value="<?php echo esc_attr( get_post_meta( $post->ID, 'video_width', true ) ); ?>" />
                <input type="hidden" id="video_height" name="video_height" value="<?php echo esc_attr( get_post_meta( $post->ID, 'video_height', true ) ); ?>" />
                <input type="hidden" id="video_filesize" name="video_filesize" value="<?php echo esc_attr( get_post_meta( $post->ID, 'video_filesize', true ) ); ?>" />
                <input type="hidden" id="video_source" name="video_source" value="<?php echo esc_attr( get_post_meta( $post->ID, 'video_source', true ) ); ?>" />
                <input type="hidden" id="video_source_url" name="video_source_url" value="<?php echo esc_attr( get_post_meta( $post->ID, 'video_source_url', true ) ); ?>" />
                <input type="hidden" id="metadata_last_fetched" name="metadata_last_fetched" value="<?php echo esc_attr( get_post_meta( $post->ID, 'metadata_last_fetched', true ) ); ?>" />
            </div>
            </div>

            <!-- Live Preview -->
            <div class="video-preview">
                <h4><?php esc_html_e( 'Video Preview', 'customtube' ); ?></h4>
                <?php if ( $video_url ) : ?>
                    <video src="<?php echo esc_url( $video_url ); ?>"
                           width="480" height="270"
                           controls preload="metadata"></video>
                <?php else : ?>
                    <p class="description">
                        <?php esc_html_e( 'No video URL provided.', 'customtube' ); ?>
                    </p>
                <?php endif; ?>
                
                <!-- Thumbnail extraction button -->
                <div class="thumbnail-extraction" style="margin-top: 15px;">
                    <button type="button" id="extract-video-thumbnail" class="button button-secondary">
                        <?php esc_html_e('Extract Thumbnail', 'customtube'); ?>
                    </button>
                    <span id="thumbnail-spinner" class="spinner"></span>
                    <div id="thumbnail-result" style="margin-top: 5px;"></div>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    $('#extract-video-thumbnail').on('click', function() {
                        var button = $(this);
                        var spinner = $('#thumbnail-spinner');
                        var result = $('#thumbnail-result');
                        var sourceType = $('input[name="video_source_type"]:checked').val();
                        var videoSource = sourceType === 'direct' ? $('#video_url').val() : $('#embed_code').val();
                        
                        if (!videoSource) {
                            result.html('<p class="error" style="color: red;"><?php esc_html_e('Please enter a video URL or embed code first.', 'customtube'); ?></p>');
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
                                    // Show success message
                                    result.html('<p class="success" style="color: green;"><?php esc_html_e('Thumbnail successfully extracted and set!', 'customtube'); ?></p>');
                                    
                                    // Refresh featured image display if possible
                                    if (wp.media && wp.media.featuredImage && wp.media.featuredImage.frame) {
                                        wp.media.featuredImage.frame.content.mode('browse');
                                        wp.media.featuredImage.frame.content.get('browse').collection.props.set({ignore: (+ new Date())});
                                        wp.media.featuredImage.frame.content.get('browse').collection.props.get('ignore');
                                    }
                                } else {
                                    result.html('<p class="error" style="color: red;">' + (response.data || '<?php esc_html_e('Error extracting thumbnail.', 'customtube'); ?>') + '</p>');
                                }
                            },
                            error: function() {
                                result.html('<p class="error" style="color: red;"><?php esc_html_e('Error communicating with the server.', 'customtube'); ?></p>');
                            },
                            complete: function() {
                                button.prop('disabled', false);
                                spinner.css('visibility', 'hidden');
                            }
                        });
                    });
                });
                </script>
            </div>
        </div>
        <style>
        /* Inline styles only for the admin meta box */
        .customtube-meta-box { margin: 20px 0; }
        .customtube-meta-box p { margin-bottom: 15px; }
        .quality-options-container {
            background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;
        }
        .quality-option { margin-bottom: 10px; }
        .video-preview { margin-top: 20px; }
        </style>
        <script>
        jQuery( document ).ready( function( $ ) {
            // Auto-detect metadata when video URL changes
            $('#video_url').on('change', function() {
                updateVideoPreview();
                autoDetectMetadata('direct');
            });
            
            // Auto-detect on paste for faster response
            $('#video_url').on('paste', function() {
                setTimeout(function() {
                    autoDetectMetadata('direct');
                }, 100); // Short delay to ensure the paste completes
            });
            
            // Function to update video preview
            function updateVideoPreview() {
                var url = $('#video_url').val();
                var preview = $('.video-preview');
                
                preview.html('<h4><?php esc_html_e( "Video Preview", "customtube" ); ?></h4>');
                
                if (url) {
                    // Check if it's a direct media file (mp4, webm, etc)
                    if (url.match(/\.(mp4|webm|ogg|mov)(\?.*)?$/i)) {
                        // It's a direct video URL
                        preview.append(
                            '<video src="' + url +
                            '" width="480" height="270" controls preload="metadata"></video>'
                        );
                    } else {
                        // It's a platform URL (YouTube, Vimeo, etc.)
                        preview.append(
                            '<p class="description"><?php esc_html_e( "Platform URL detected. The video will be embedded from the source site.", "customtube" ); ?></p>' +
                            '<p><strong><?php esc_html_e( "URL:", "customtube" ); ?></strong> ' + url + '</p>'
                        );
                    }
                } else {
                    preview.append(
                        '<p class="description"><?php esc_html_e( "No video URL provided.", "customtube" ); ?></p>'
                    );
                }
            }
            
            // Test video URL button
            $('#test-video-url').on('click', function() {
                var videoUrl = $('#video_url').val();
                
                if (!videoUrl) {
                    alert('<?php esc_html_e("Please enter a video URL to test", "customtube"); ?>');
                    return;
                }
                
                // Create a temporary video element to test
                var videoTest = $('<video style="display:none"></video>').appendTo('body');
                var sourceTest = $('<source>', {
                    src: videoUrl,
                    type: 'video/mp4'
                }).appendTo(videoTest);
                
                // Show loading status
                var button = $(this);
                var originalText = button.text();
                button.text('<?php esc_html_e("Testing...", "customtube"); ?>').prop('disabled', true);
                
                // Set a timeout in case the video never loads
                var testTimeout = setTimeout(function() {
                    videoTest.remove();
                    button.text(originalText).prop('disabled', false);
                    alert('<?php esc_html_e("The video URL could not be loaded. Please check the URL and try again.", "customtube"); ?>');
                }, 10000); // 10 second timeout
                
                // Listen for events
                videoTest.on('loadedmetadata', function() {
                    clearTimeout(testTimeout);
                    var duration = videoTest[0].duration;
                    var formattedDuration = formatDuration(duration);
                    $('#video_duration').val(formattedDuration);
                    $('#duration_seconds').val(Math.round(duration));
                    videoTest.remove();
                    button.text(originalText).prop('disabled', false);
                    alert('<?php esc_html_e("Video URL is valid! Duration detected: ", "customtube"); ?>' + formattedDuration);
                }).on('error', function() {
                    clearTimeout(testTimeout);
                    videoTest.remove();
                    button.text(originalText).prop('disabled', false);
                    alert('<?php esc_html_e("Error loading video. Please check the URL and try again.", "customtube"); ?>');
                });
                
                // Try to load the video
                videoTest[0].load();
            });
            
            // Format duration in seconds to HH:MM:SS
            function formatDuration(seconds) {
                var hours = Math.floor(seconds / 3600);
                var minutes = Math.floor((seconds % 3600) / 60);
                var secs = Math.floor(seconds % 60);
                
                if (hours > 0) {
                    return (hours < 10 ? '0' : '') + hours + ':' + 
                           (minutes < 10 ? '0' : '') + minutes + ':' + 
                           (secs < 10 ? '0' : '') + secs;
                } else {
                    return (minutes < 10 ? '0' : '') + minutes + ':' + 
                           (secs < 10 ? '0' : '') + secs;
                }
            }
            
            // Function to auto-detect metadata whenever URL changes
            function autoDetectMetadata(sourceType) {
                var videoSource = $('#video_url').val();
                var spinner = $('#duration-spinner');
                
                if (!videoSource) {
                    return; // No source provided yet
                }
                
                // Show spinner
                spinner.css('visibility', 'visible');
                
                // Check if it looks like a platform URL or direct video
                var isDirectVideo = videoSource.match(/\.(mp4|webm|ogg|mov)(\?.*)?$/i);
                
                // Always use 'direct' source type since we're now using just one field
                // but keep both fields populated for backwards compatibility
                
                // Make AJAX request to get metadata
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'customtube_fetch_video_metadata',
                        post_id: <?php echo $post->ID; ?>,
                        source_type: 'direct',
                        video_source: videoSource,
                        nonce: '<?php echo wp_create_nonce('customtube_fetch_metadata'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update form fields with new metadata
                            if (response.data.duration) {
                                $('#video_duration').val(response.data.duration);
                            }
                            
                            // Update embed code field with the generated embed code (for backwards compatibility)
                            if (response.data.embed_code) {
                                $('#embed_code').val(response.data.embed_code);
                            }
                            
                            // Update thumbnail if available and no thumbnail has been set
                            if (response.data.thumbnail_url && !wp.media.featuredImage.get()) {
                                // If we have a thumbnail URL, we would set it, but WP doesn't have a direct API
                                // This would need to be implemented server-side
                                console.log('Thumbnail URL available:', response.data.thumbnail_url);
                            }
                            
                            // Update title if empty and available
                            if (response.data.title && !$('#title').val()) {
                                $('#title').val(response.data.title);
                                
                                // If we're in the Block Editor, also update the title field there
                                if (window.wp && wp.data && wp.data.select('core/editor')) {
                                    wp.data.dispatch('core/editor').editPost({ title: response.data.title });
                                }
                            }
                            
                            // Update hidden metadata fields
                            $('#duration_seconds').val(response.data.duration_seconds || 0);
                            $('#video_width').val(response.data.width || 0);
                            $('#video_height').val(response.data.height || 0);
                            $('#video_filesize').val(response.data.filesize || 0);
                            $('#video_source').val(response.data.source || '');
                            $('#video_source_url').val(response.data.source_url || '');
                            $('#metadata_last_fetched').val(response.data.metadata_last_fetched || 0);
                            
                            // Update preview
                            updateVideoPreview();
                            
                            // No alert needed for automatic detection
                        } else {
                            console.error('Error fetching metadata:', response.data?.message || 'Unknown error');
                        }
                    },
                    error: function() {
                        console.error('AJAX error fetching metadata');
                    },
                    complete: function() {
                        // Hide spinner
                        spinner.css('visibility', 'hidden');
                    }
                });
            }
            
            // Manual auto-detect duration button
            $('#auto-detect-duration').on('click', function() {
                var button = $(this);
                var spinner = $('#duration-spinner');
                var videoSource = $('#video_url').val();
                
                if (!videoSource) {
                    alert('<?php esc_html_e("Please enter a video URL first.", "customtube"); ?>');
                    return;
                }
                
                // Disable button and show spinner
                button.prop('disabled', true);
                spinner.css('visibility', 'visible');
                
                // Make AJAX request to get metadata
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'customtube_fetch_video_metadata',
                        post_id: <?php echo $post->ID; ?>,
                        source_type: 'direct',
                        video_source: videoSource,
                        nonce: '<?php echo wp_create_nonce('customtube_fetch_metadata'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update form fields with new metadata
                            if (response.data.duration) {
                                $('#video_duration').val(response.data.duration);
                            }
                            
                            // Update embed code field with the generated embed code (for backwards compatibility)
                            if (response.data.embed_code) {
                                $('#embed_code').val(response.data.embed_code);
                            }
                            
                            // Update hidden metadata fields
                            $('#duration_seconds').val(response.data.duration_seconds || 0);
                            $('#video_width').val(response.data.width || 0);
                            $('#video_height').val(response.data.height || 0);
                            $('#video_filesize').val(response.data.filesize || 0);
                            $('#video_source').val(response.data.source || '');
                            $('#video_source_url').val(response.data.source_url || '');
                            $('#metadata_last_fetched').val(response.data.metadata_last_fetched || 0);
                            
                            // Show success message
                            alert('<?php esc_html_e("Metadata successfully detected!", "customtube"); ?>');
                        } else {
                            // Show error message
                            alert(response.data?.message || '<?php esc_html_e("Error fetching metadata.", "customtube"); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php esc_html_e("Error fetching metadata. Please try again.", "customtube"); ?>');
                    },
                    complete: function() {
                        // Re-enable button and hide spinner
                        button.prop('disabled', false);
                        spinner.css('visibility', 'hidden');
                    }
                });
            });
            
            // Initialize preview
            updateVideoPreview();
        });
        </script>
        <style>
        /* Inline styles only for the admin meta box */
        .customtube-meta-box { margin: 20px 0; }
        .customtube-meta-box p { margin-bottom: 15px; }
        .quality-options-container {
            background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;
        }
        .quality-option { margin-bottom: 10px; }
        .video-preview { margin-top: 20px; }
        .embed-examples {
            background: #f0f0f0; border: 1px solid #ddd; padding: 10px; margin-top: 10px;
        }
        .embed-examples h4 { margin-top: 0; }
        .embed-examples p { margin: 5px 0; }
        #duration-spinner {
            float: none;
            margin-left: 5px;
            vertical-align: middle;
            visibility: hidden;
        }
        #auto-detect-duration {
            margin-left: 10px;
            vertical-align: middle;
        }
        </style>
        <?php
    }
}

if ( ! function_exists( 'customtube_save_video_details' ) ) {
    /**
     * Save video details meta box data.
     *
     * @param int $post_id The post ID.
     */
    function customtube_save_video_details( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['customtube_video_details_nonce'] ) ||
             ! wp_verify_nonce( $_POST['customtube_video_details_nonce'], 'customtube_video_details' ) ) {
            return;
        }
        // Abort on autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Permission check
        if ( isset( $_POST['post_type'] ) && 'video' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }
        // Save each field if set
        if ( isset( $_POST['video_source_type'] ) ) {
            update_post_meta( $post_id, 'video_source_type', sanitize_text_field( $_POST['video_source_type'] ) );
        }
        
        if ( isset( $_POST['video_url'] ) ) {
            update_post_meta( $post_id, 'video_url', esc_url_raw( $_POST['video_url'] ) );
        }
        
        if ( isset( $_POST['embed_code'] ) ) {
            // Don't use esc_html here as it would break iframe codes
            // We'll do more targeted sanitization
            $embed_code = $_POST['embed_code'];
            
            // If it looks like a URL, sanitize it as a URL
            if (filter_var($embed_code, FILTER_VALIDATE_URL)) {
                $embed_code = esc_url_raw($embed_code);
            } else {
                // Allow specific HTML tags needed for embeds
                $allowed_html = array(
                    'iframe' => array(
                        'src' => array(),
                        'width' => array(),
                        'height' => array(),
                        'frameborder' => array(),
                        'allowfullscreen' => array(),
                        'allow' => array(),
                        'class' => array(),
                        'style' => array(),
                    ),
                    'script' => array(
                        'src' => array(),
                        'type' => array(),
                        'async' => array(),
                        'defer' => array(),
                    ),
                    'div' => array(
                        'class' => array(),
                        'id' => array(),
                        'style' => array(),
                        'data-id' => array(),
                        'data-player' => array(),
                    ),
                );
                $embed_code = wp_kses($embed_code, $allowed_html);
            }
            
            update_post_meta( $post_id, 'embed_code', $embed_code );
        }
        
        if ( isset( $_POST['preview_url'] ) ) {
            update_post_meta( $post_id, 'preview_url', esc_url_raw( $_POST['preview_url'] ) );
        }
        
        if ( isset( $_POST['video_duration'] ) ) {
            update_post_meta( $post_id, 'video_duration', sanitize_text_field( $_POST['video_duration'] ) );
            
            // If duration changed, update duration_seconds
            if (isset($_POST['duration_seconds'])) {
                update_post_meta( $post_id, 'duration_seconds', absint($_POST['duration_seconds']) );
            } else {
                // Calculate duration in seconds from the formatted string
                $duration_seconds = customtube_duration_to_seconds($_POST['video_duration']);
                update_post_meta( $post_id, 'duration_seconds', $duration_seconds );
            }
        }
        
        // Save additional metadata fields
        if ( isset( $_POST['video_width'] ) ) {
            update_post_meta( $post_id, 'video_width', absint( $_POST['video_width'] ) );
        }
        
        if ( isset( $_POST['video_height'] ) ) {
            update_post_meta( $post_id, 'video_height', absint( $_POST['video_height'] ) );
        }
        
        if ( isset( $_POST['video_filesize'] ) ) {
            update_post_meta( $post_id, 'video_filesize', absint( $_POST['video_filesize'] ) );
        }
        
        if ( isset( $_POST['video_source'] ) ) {
            update_post_meta( $post_id, 'video_source', sanitize_text_field( $_POST['video_source'] ) );
        }
        
        if ( isset( $_POST['video_source_url'] ) ) {
            update_post_meta( $post_id, 'video_source_url', esc_url_raw( $_POST['video_source_url'] ) );
        }
        
        if ( isset( $_POST['metadata_last_fetched'] ) ) {
            update_post_meta( $post_id, 'metadata_last_fetched', absint( $_POST['metadata_last_fetched'] ) );
        }
        
        if ( isset( $_POST['quality_options'] ) && is_array( $_POST['quality_options'] ) ) {
            $qualities = array(
                '360p'  => isset( $_POST['quality_options']['360p'] )  ? esc_url_raw( $_POST['quality_options']['360p'] )  : '',
                '720p'  => isset( $_POST['quality_options']['720p'] )  ? esc_url_raw( $_POST['quality_options']['720p'] )  : '',
                '1080p' => isset( $_POST['quality_options']['1080p'] ) ? esc_url_raw( $_POST['quality_options']['1080p'] ) : '',
            );
            update_post_meta( $post_id, 'quality_options', $qualities );
        }
    }
    add_action( 'save_post', 'customtube_save_video_details' );
}
