<?php
/**
 * Template Functions for CustomTube Theme
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Helper functions used across CustomTube templates.
 * All customtube_get_* functions live here for consistency.
 */


/**
 * Get videos by performer
 * 
 * @param int|string $performer Performer ID or slug
 * @param int $count Number of videos to retrieve
 * @return WP_Query
 */
if (!function_exists('customtube_get_videos_by_performer')) {
function customtube_get_videos_by_performer($performer, $count = 8) {
    $field = is_numeric($performer) ? 'term_id' : 'slug';
    
    $args = array(
        'post_type'      => 'video',
        'posts_per_page' => $count,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'performer',
                'field'    => $field,
                'terms'    => $performer,
            ),
        ),
    );
    
    return new WP_Query($args);
}
}

/**
 * Display video search form
 * 
 * @return void
 */
if (!function_exists('customtube_search_form')) {
function customtube_search_form() {
    ?>
    <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
        <div class="search-input-container">
            <input type="search" class="search-field" placeholder="<?php echo esc_attr_x('Search videos...', 'placeholder', 'customtube'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
            <input type="hidden" name="post_type" value="video" />
        </div>
        <button type="submit" class="search-submit">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" fill="currentColor"/>
            </svg>
        </button>
    </form>
    <?php
}
}

/**
 * Display age verification modal
 * 
 * @return void
 * @deprecated This function is now replaced by customtube_enhanced_age_verification_modal
 */
if (!function_exists('customtube_age_verification_modal')) {
/**
 * Default age verification modal - DISABLED
 * 
 * This function has been disabled to prevent duplicate modals.
 * The enhanced version in inc/admin/age-verification.php is used instead.
 * 
 * @deprecated Use customtube_enhanced_age_verification_modal instead
 */
function customtube_age_verification_modal() {
    // Function disabled to prevent duplicate modals
    // The enhanced version in inc/admin/age-verification.php is used instead
    return;
    
    // Legacy code commented out to prevent execution
    /*
    ?>
    <div id="age-verification-overlay" class="age-verification-overlay">
        <div class="age-verification-modal">
            <div class="age-verification-logo">
                <img src="<?php echo esc_url(CUSTOMTUBE_URI . '/assets/images/logo.png'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
            </div>
            
            <h2><?php esc_html_e('Age Verification Required', 'customtube'); ?></h2>
            
            <p><?php esc_html_e('This website contains adult content and is only suitable for those who are 18 years or older. Please confirm your age to continue.', 'customtube'); ?></p>
            
            <div class="age-verification-buttons">
                <button id="age-confirm" class="age-confirm-button"><?php esc_html_e('I am 18 or older', 'customtube'); ?></button>
                <button id="age-cancel" class="age-cancel-button"><?php esc_html_e('I am under 18', 'customtube'); ?></button>
            </div>
            
            <div class="age-verification-links">
                <a href="<?php echo esc_url(get_privacy_policy_url()); ?>"><?php esc_html_e('Privacy Policy', 'customtube'); ?></a>
                <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>"><?php esc_html_e('Terms of Service', 'customtube'); ?></a>
            </div>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#age-confirm').on('click', function() {
                // Set a cookie to remember the age verification for 30 days
                document.cookie = 'age_verified=1; max-age=' + (60 * 60 * 24 * 30) + '; path=/';
                $('#age-verification-overlay').fadeOut();
            });
            
            $('#age-cancel').on('click', function() {
                window.location.href = 'https://www.google.com';
            });
        });
    </script>
    <?php
    */
}
}

/**
 * Display theme switcher
 * 
 * @return void
 */
if (!function_exists('customtube_theme_switcher')) {
function customtube_theme_switcher() {
    $current_mode = isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] : 'dark'; // Default to dark mode
    ?>
    <div class="theme-switcher">
        <button id="theme-toggle" class="theme-toggle-button" data-current-mode="<?php echo esc_attr($current_mode); ?>" style="background: none; border: none; color: inherit; cursor: pointer; padding: 8px; border-radius: 4px; transition: background-color 0.3s;">
            <svg class="light-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" style="<?php echo $current_mode === 'dark' ? 'display: none;' : 'display: block;'; ?>">
                <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zm11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06z" fill="currentColor"/>
            </svg>
            <svg class="dark-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" style="<?php echo $current_mode === 'dark' ? 'display: block;' : 'display: none;'; ?>">
                <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9c0-.46-.04-.92-.1-1.36-.98 1.37-2.58 2.26-4.4 2.26-2.98 0-5.4-2.42-5.4-5.4 0-1.81.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z" fill="currentColor"/>
            </svg>
        </button>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                var currentMode = this.getAttribute('data-current-mode');
                var newMode = currentMode === 'dark' ? 'light' : 'dark';
                
                // Update body class and data attribute
                var body = document.body;
                if (newMode === 'dark') {
                    body.classList.add('dark-mode');
                    body.setAttribute('data-theme', 'dark');
                } else {
                    body.classList.remove('dark-mode');
                    body.setAttribute('data-theme', 'light');
                }
                
                // Update button state and icons
                this.setAttribute('data-current-mode', newMode);
                var lightIcon = this.querySelector('.light-icon');
                var darkIcon = this.querySelector('.dark-icon');
                
                if (newMode === 'dark') {
                    lightIcon.style.display = 'none';
                    darkIcon.style.display = 'block';
                } else {
                    lightIcon.style.display = 'block';
                    darkIcon.style.display = 'none';
                }
                
                // Set cookie for 30 days
                var date = new Date();
                date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
                document.cookie = 'dark_mode=' + newMode + '; expires=' + date.toUTCString() + '; path=/';
            });
        }
    });
    </script>
    <?php
}
}

/**
 * Display video thumbnail with hover preview.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size (registered via add_image_size).
 */
if (!function_exists('customtube_video_thumbnail')) {
function customtube_video_thumbnail( $post_id = null, $size = 'video-thumb' ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    
    // Use the template part for consistent thumbnail display
    include(get_template_directory() . '/template-parts/thumbnail-preview.php');
}
}

/**
 * Display video card
 *
 * NOTE: This is a wrapper function that sets up the post context and
 * calls the thumbnail-preview.php template which includes the full card markup.
 *
 * @param int $post_id Post ID
 * @return void
 */
if (!function_exists('customtube_video_card')) {
function customtube_video_card($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Get post data
    $post = get_post($post_id);
    setup_postdata($post);

    // Include the template part which already has the article and content structure
    include(get_template_directory() . '/template-parts/thumbnail-preview.php');

    // Reset post data
    wp_reset_postdata();
}
}

/**
 * Detect video URL type
 * 
 * @param string $url Video URL or embed code
 * @return string The type of video (mp4, youtube, vimeo, xhamster, pornhub, xvideos, iframe, etc.)
 */
if (!function_exists('customtube_get_video_type')) {
function customtube_get_video_type($url) {
    // Skip if URL is empty or not a string
    if (empty($url) || !is_string($url)) {
        error_log("Invalid URL passed to customtube_get_video_type");
        return 'invalid';
    }
    
    // Log for debugging (limit length to avoid huge logs)
    error_log("Detecting video type for URL: " . substr($url, 0, 150) . (strlen($url) > 150 ? '...' : ''));
    
    // Check if input contains HTML instead of a URL
    if (strpos($url, '<div') !== false && strpos($url, '<iframe') === false) {
        error_log("Detected HTML div without iframe: " . substr($url, 0, 50) . "...");
        return 'invalid_html';
    }
    
    // Check if it's a direct MP4 link
    if (preg_match('/\.(mp4|m4v|webm|ogg)(\?.*)?$/i', $url)) {
        return 'mp4';
    }
    
    // Check if it's an embed code (contains iframe tags)
    if (strpos($url, '<iframe') !== false) {
        // Extract the src from iframe if possible
        if (preg_match('/src=["\'](https?:\/\/[^\'"]+)["\']/i', $url, $matches)) {
            $iframe_src = $matches[1];
            // Ensure HTTPS
            $iframe_src = str_replace('http://', 'https://', $iframe_src);
            $url = $iframe_src; // Use the iframe src for further checks
            
            error_log("Extracted iframe src: $iframe_src");
        } else {
            error_log("Could not extract src from iframe: " . substr($url, 0, 100));
            return 'invalid_iframe';
        }
        
        // Check for various platforms
        if (preg_match('/(youtube\.com|youtu\.be)/i', $url)) {
            return 'youtube';
        }
        if (preg_match('/vimeo\.com/i', $url)) {
            return 'vimeo';
        }
        if (preg_match('/pornhub\.com/i', $url)) {
            return 'pornhub';
        }
        if (preg_match('/xvideos\.com/i', $url)) {
            return 'xvideos';
        }
        if (preg_match('/(xhamster\.com|xh\.video)/i', $url)) {
            return 'xhamster';
        }
        if (preg_match('/redtube\.com/i', $url)) {
            return 'redtube';
        }
        if (preg_match('/youporn\.com/i', $url)) {
            return 'youporn';
        }
        
        return 'iframe';
    }
    
    // Check for direct URLs to platforms
    
    // YouTube - check for various URL formats
    if (preg_match('/(youtube\.com|youtu\.be)/i', $url)) {
        error_log("YouTube URL detected: $url");
        return 'youtube';
    }
    
    // Vimeo
    if (preg_match('/vimeo\.com/i', $url)) {
        return 'vimeo';
    }
    
    // Adult sites (these are common adult tube sites)
    if (preg_match('/pornhub\.com/i', $url)) {
        return 'pornhub';
    }
    
    if (preg_match('/xvideos\.com/i', $url)) {
        return 'xvideos';
    }
    
    if (preg_match('/(xhamster\.(?:com|desi|uno|name)|xh\.video)/i', $url)) {
        error_log("XHamster URL detected: $url");
        return 'xhamster';
    }
    
    if (preg_match('/redtube\.com/i', $url)) {
        return 'redtube';
    }
    
    if (preg_match('/youporn\.com/i', $url)) {
        return 'youporn';
    }
    
    // Check if it looks like a URL
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return 'unknown_url';
    }
    
    // If we can't identify it at all
    return 'unknown';
}
}

/**
 * Get embed code for different video types
 * 
 * @param string $url Video URL
 * @param string $type Video type
 * @param string $poster_url Poster image URL
 * @return string HTML for embedding the video
 */
if (!function_exists('customtube_get_embed_code')) {
function customtube_get_embed_code($url, $type, $poster_url = '') {
    $embed_code = '';
    
    // For debugging
    error_log("Creating embed code for URL: $url, type: $type");
    
    switch ($type) {
        case 'mp4':
            // Make sure url is not empty before creating video element
            if (!empty($url)) {
                $embed_code = '<video class="main-video-player" poster="' . esc_url($poster_url) . '" preload="metadata" playsinline>
                    <source src="' . esc_url($url) . '" type="video/mp4">
                    Your browser does not support the video tag.
                </video>';
            } else {
                $embed_code = '<div class="video-placeholder">
                    <p>' . esc_html__('No video source provided.', 'customtube') . '</p>
                    <p class="video-placeholder-tip">' . esc_html__('Please edit this video and add a direct video URL.', 'customtube') . '</p>
                </div>';
            }
            break;
            
        case 'youtube':
            // Extract YouTube video ID
            $video_id = '';
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
                $video_id = $matches[1];
            }
            
            if ($video_id) {
                // Updated YouTube embed code with balanced security approach
                $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-iframe" 
                        src="https://www.youtube.com/embed/' . esc_attr($video_id) . '?rel=0&showinfo=0&modestbranding=1" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>
                </div>';
            }
            break;
            
        case 'vimeo':
            // Extract Vimeo video ID
            $video_id = '';
            if (preg_match('/vimeo\.com\/(?:.*\/)?(?:videos\/)?([0-9]+)/', $url, $matches)) {
                $video_id = $matches[1];
            }
            
            if ($video_id) {
                $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-iframe" 
                        src="https://player.vimeo.com/video/' . esc_attr($video_id) . '?api=1&title=0&byline=0&portrait=0&dnt=1" 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        loading="lazy"
                        sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                        allow="autoplay; fullscreen; picture-in-picture" 
                        allowfullscreen></iframe>
                </div>';
            }
            break;
            
        case 'pornhub':
            // If it's already an iframe code, enhance it for security but maintain compatibility
            if (strpos($url, '<iframe') !== false) {
                // Extract the source URL
                if (preg_match('/src=[\'"](https?:\/\/[^\'"]*)[\'"]/i', $url, $src_matches)) {
                    $src_url = $src_matches[1];
                    // Ensure HTTPS
                    $src_url = str_replace('http://', 'https://', $src_url);
                    
                    // Check if it's the standard PornHub embed format and has the video ID
                    if (strpos($src_url, 'pornhub.com/embed/') !== false) {
                        // It's a PornHub embed - use official format with best practices
                        $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-iframe" 
                                src="' . esc_url($src_url) . '" 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                loading="lazy"
                                sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                                scrolling="no" 
                                allowfullscreen></iframe>
                        </div>';
                    } else {
                        // Not standard format - use with caution
                        $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-iframe" 
                                src="' . esc_url($src_url) . '" 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                loading="lazy"
                                sandbox="allow-same-origin allow-scripts allow-popups"
                                scrolling="no" 
                                allowfullscreen></iframe>
                        </div>';
                    }
                } else {
                    // Fallback to original but wrapped in container
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">' . $url . '</div>';
                }
            } else {
                // Extract video ID from different PornHub URL formats
                $video_id = '';
                
                // Try to extract from viewkey parameter (most common format)
                if (preg_match('/viewkey=([^&]+)/', $url, $matches)) {
                    $video_id = $matches[1];
                }
                // Try to extract from URL path format
                else if (preg_match('#pornhub\.com/view_video\.php\?viewkey=([^&\s]+)#i', $url, $matches)) {
                    $video_id = $matches[1];
                }
                // Try to extract from direct embed URL
                else if (preg_match('#pornhub\.com/embed/([^/\s&?]+)#i', $url, $matches)) {
                    $video_id = $matches[1];
                }
                
                if (!empty($video_id)) {
                    // Use official PornHub embed format
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="https://www.pornhub.com/embed/' . esc_attr($video_id) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    // If we can't extract the ID, create a fallback with the original URL
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                }
            }
            break;
            
        case 'xvideos':
            // XVideos site-specific handling
            if (strpos($url, '<iframe') !== false) {
                // Extract the source URL
                if (preg_match('/src=[\'"](https?:\/\/[^\'"]*)[\'"]/i', $url, $src_matches)) {
                    $src_url = $src_matches[1];
                    // Ensure HTTPS
                    $src_url = str_replace('http://', 'https://', $src_url);
                    
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($src_url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">' . $url . '</div>';
                }
            } else {
                // Extract video ID from XVideos URL
                $video_id = '';
                if (preg_match('#xvideos\.com/video([0-9]+)#i', $url, $matches)) {
                    $video_id = $matches[1];
                }
                
                if (!empty($video_id)) {
                    // Use official XVideos embed format
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="https://www.xvideos.com/embedframe/' . esc_attr($video_id) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    // If we can't extract the ID, create a fallback with the original URL
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                }
            }
            break;
            
        case 'xhamster':
            // XHamster site-specific handling
            if (strpos($url, '<iframe') !== false) {
                // Extract the source URL
                if (preg_match('/src=[\'"](https?:\/\/[^\'"]*)[\'"]/i', $url, $src_matches)) {
                    $src_url = $src_matches[1];
                    // Ensure HTTPS
                    $src_url = str_replace('http://', 'https://', $src_url);
                    
                    // Create embed with less restrictive security settings
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($src_url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            allow="autoplay; fullscreen"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">' . $url . '</div>';
                }
            } else {
                // Try to extract video ID from XHamster URL - support multiple formats
                $embed_id = '';
                
                // Debug the URL
                error_log("Processing XHamster URL: $url");
                
                // Handle various URL formats to extract the ID
                if (preg_match('#xhamster\.(?:com|desi|uno|name)/videos/([^/]+)-([0-9]+)#i', $url, $matches)) {
                    $embed_id = $matches[2];
                    error_log("XHamster ID from videos URL: $embed_id");
                } else if (preg_match('#xh\.video/e/([^&\s/]+)#i', $url, $matches)) {
                    $embed_id = $matches[1];
                    error_log("XHamster ID from xh.video URL: $embed_id");
                } else if (preg_match('#xhamster\.(?:com|desi|uno|name)/embed/([0-9]+)#i', $url, $matches)) {
                    $embed_id = $matches[1];
                    error_log("XHamster ID from embed URL: $embed_id");
                } else if (preg_match('#xhamster\.(?:com|desi|uno|name)/embed/xh([0-9]+)/#i', $url, $matches)) {
                    $embed_id = $matches[1];
                    error_log("XHamster ID from xhID URL: $embed_id");
                } else if (preg_match('#xhamster\.(?:com|desi|uno|name)/(?:[^/]+/)*(?:[^-]+-)([0-9]+)(?:[^0-9]|$)#i', $url, $matches)) {
                    $embed_id = $matches[1];
                    error_log("XHamster ID from generic pattern: $embed_id");
                }
                
                if (!empty($embed_id)) {
                    // Log for debugging
                    error_log("Using XHamster embed ID: $embed_id");
                    
                    // Use direct embed from XHamster with proper format
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="https://xhamster.com/embed/' . esc_attr($embed_id) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            allow="autoplay; fullscreen"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    // If we couldn't extract the ID, create a direct iframe
                    error_log("No XHamster ID found, using direct URL: $url");
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            allow="autoplay; fullscreen"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                }
            }
            break;
            
        case 'redtube':
            // RedTube site-specific handling
            if (strpos($url, '<iframe') !== false || strpos($url, '<object') !== false) {
                // Extract the source URL
                if (preg_match('/src=[\'"](https?:\/\/[^\'"]*)[\'"]/i', $url, $src_matches)) {
                    $src_url = $src_matches[1];
                    // Ensure HTTPS
                    $src_url = str_replace('http://', 'https://', $src_url);
                    
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($src_url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    // It's likely an object/embed tag or something else - wrap it
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">' . $url . '</div>';
                }
            } else {
                // Extract RedTube video ID
                $video_id = '';
                if (preg_match('#redtube\.com/([0-9]+)#i', $url, $matches)) {
                    $video_id = $matches[1];
                }
                
                if (!empty($video_id)) {
                    // Use HTML5 RedTube embed format
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="https://embed.redtube.com/?id=' . esc_attr($video_id) . '&bgcolor=000000" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    // Fallback for direct URL
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                }
            }
            break;
            
        case 'youporn':
            // YouPorn site-specific handling
            if (strpos($url, '<iframe') !== false) {
                // Extract the source URL
                if (preg_match('/src=[\'"](https?:\/\/[^\'"]*)[\'"]/i', $url, $src_matches)) {
                    $src_url = $src_matches[1];
                    // Ensure HTTPS
                    $src_url = str_replace('http://', 'https://', $src_url);
                    
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($src_url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">' . $url . '</div>';
                }
            } else {
                // Extract YouPorn video ID
                $video_id = '';
                if (preg_match('#youporn\.com/watch/([0-9]+)#i', $url, $matches)) {
                    $video_id = $matches[1];
                }
                
                if (!empty($video_id)) {
                    // Use YouPorn embed format
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="https://www.youporn.com/embed/' . esc_attr($video_id) . '/" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    // Fallback for direct URL
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                }
            }
            break;
            
        case 'invalid':
        case 'invalid_html':
        case 'invalid_iframe':
            // Handle invalid or malformed embed codes
            error_log("Invalid embed type detected for: " . substr($url, 0, 100));
            $embed_code = '<div class="video-placeholder">
                <p>' . esc_html__('Invalid embed code detected.', 'customtube') . '</p>
                <p class="video-placeholder-tip">' . esc_html__('Please edit this video and add a valid video URL or embed code.', 'customtube') . '</p>
            </div>';
            break;
            
        case 'iframe':
        case 'unknown_url':
        case 'unknown':
            // General iframe handling for unknown sources
            if (strpos($url, '<iframe') !== false) {
                // Extract the source URL
                if (preg_match('/src=[\'"](https?:\/\/[^\'"]*)[\'"]/i', $url, $src_matches)) {
                    $src_url = $src_matches[1];
                    // Ensure HTTPS
                    $src_url = str_replace('http://', 'https://', $src_url);
                    
                    // Try to detect the source from the URL to apply platform-specific handling
                    if (strpos($src_url, 'youtube.com') !== false || strpos($src_url, 'youtube-nocookie.com') !== false || strpos($src_url, 'youtu.be') !== false) {
                        return customtube_get_embed_code($src_url, 'youtube', $poster_url);
                    } 
                    else if (strpos($src_url, 'vimeo.com') !== false) {
                        return customtube_get_embed_code($src_url, 'vimeo', $poster_url);
                    }
                    else if (strpos($src_url, 'pornhub.com') !== false) {
                        return customtube_get_embed_code($src_url, 'pornhub', $poster_url);
                    }
                    else if (strpos($src_url, 'xvideos.com') !== false) {
                        return customtube_get_embed_code($src_url, 'xvideos', $poster_url);
                    }
                    else if (strpos($src_url, 'xhamster.com') !== false || strpos($src_url, 'xh.video') !== false) {
                        return customtube_get_embed_code($src_url, 'xhamster', $poster_url);
                    }
                    else if (strpos($src_url, 'redtube.com') !== false) {
                        return customtube_get_embed_code($src_url, 'redtube', $poster_url);
                    }
                    else if (strpos($src_url, 'youporn.com') !== false) {
                        return customtube_get_embed_code($src_url, 'youporn', $poster_url);
                    }
                    
                    // Generic secure embedding for unknown sources with valid URL
                    $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-iframe" 
                            src="' . esc_url($src_url) . '" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-popups"
                            scrolling="no" 
                            allowfullscreen></iframe>
                    </div>';
                } else {
                    // If we can't extract a valid src, don't use the iframe directly
                    // Instead, show a placeholder with error message
                    error_log("Cannot extract valid src from iframe: " . substr($url, 0, 100));
                    $embed_code = '<div class="video-placeholder">
                        <p>' . esc_html__('Invalid iframe code detected.', 'customtube') . '</p>
                        <p class="video-placeholder-tip">' . esc_html__('Please edit this video and add a valid embed code with proper src attribute.', 'customtube') . '</p>
                    </div>';
                }
            } else {
                // Try to detect a valid URL first
                $is_valid_url = filter_var($url, FILTER_VALIDATE_URL) !== false;
                
                if (!$is_valid_url) {
                    // Not a valid URL or iframe, show placeholder
                    error_log("Not a valid URL or iframe: " . substr($url, 0, 100));
                    $embed_code = '<div class="video-placeholder">
                        <p>' . esc_html__('Invalid video source detected.', 'customtube') . '</p>
                        <p class="video-placeholder-tip">' . esc_html__('Please edit this video and add a valid video URL or embed code.', 'customtube') . '</p>
                    </div>';
                    break;
                }
                
                // Try to detect the source from the URL to apply platform-specific handling
                if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                    return customtube_get_embed_code($url, 'youtube', $poster_url);
                } 
                else if (strpos($url, 'vimeo.com') !== false) {
                    return customtube_get_embed_code($url, 'vimeo', $poster_url);
                }
                else if (strpos($url, 'pornhub.com') !== false) {
                    return customtube_get_embed_code($url, 'pornhub', $poster_url);
                }
                else if (strpos($url, 'xvideos.com') !== false) {
                    return customtube_get_embed_code($url, 'xvideos', $poster_url);
                }
                else if (strpos($url, 'xhamster.com') !== false || strpos($url, 'xh.video') !== false) {
                    return customtube_get_embed_code($url, 'xhamster', $poster_url);
                }
                else if (strpos($url, 'redtube.com') !== false) {
                    return customtube_get_embed_code($url, 'redtube', $poster_url);
                }
                else if (strpos($url, 'youporn.com') !== false) {
                    return customtube_get_embed_code($url, 'youporn', $poster_url);
                }
                
                // For direct URLs to unknown sites, create a secure iframe
                $embed_code = '<div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-iframe" 
                        src="' . esc_url($url) . '" 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        loading="lazy"
                        sandbox="allow-same-origin allow-scripts allow-popups"
                        scrolling="no" 
                        allowfullscreen></iframe>
                </div>';
            }
            break;
    }
    
    return $embed_code;
}
}

/**
 * Display video player
 * 
 * @param int $post_id Post ID
 * @return void
 */
if (!function_exists('customtube_video_player')) {
function customtube_video_player($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Debug mode for admins
    $debug = isset($_GET['debug']) && current_user_can('manage_options');
    
    // Use the new modular video display function if available
    if (function_exists('customtube_display_video')) {
        if ($debug) {
            echo '<div style="background: #f5f5f5; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;">';
            echo '<strong>Debug Info:</strong><br>';
            echo 'Using modern video display function<br>';
            echo 'Post ID: ' . esc_html($post_id) . '<br>';
            echo '</div>';
        }
        
        // Call the improved modular function
        customtube_display_video($post_id);
        return;
    }
    
    // Legacy implementation - this will run only if the modern function isn't available
    
    // Retrieve metadata
    $video_url = get_post_meta($post_id, 'video_url', true);
    $embed_code = get_post_meta($post_id, 'embed_code', true);
    $quality_options = get_post_meta($post_id, 'quality_options', true);
    $poster_url = get_the_post_thumbnail_url($post_id, 'video-large');
    $has_quality_options = false;
    $video_source_type = get_post_meta($post_id, 'video_source_type', true) ?: 'direct';
    
    // Simplified video source detection
    // First check if we have a URL (preferred)
    if (!empty($video_url)) {
        $video_source = $video_url;
        $video_type = customtube_get_video_type($video_url);
        
        // Log for debugging
        error_log("Using video URL field: type={$video_type}, source={$video_source}");
    } 
    // If no URL, fall back to embed code if available
    else if (!empty($embed_code)) {
        $video_source = $embed_code;
        $video_type = customtube_get_video_type($embed_code);
        
        // Log for debugging
        error_log("Using embed code field: type={$video_type}, source={$video_source}");
    }
    // No source available
    else {
        $video_source = '';
        $video_type = 'unknown';
        
        // Log for debugging
        error_log("No video source found for post ID: {$post_id}");
    }
    
    // Determine if it's a self-hosted video file
    $is_self_hosted = ($video_type === 'mp4');
    
    // Check if there are quality options (only for self-hosted videos)
    if ($is_self_hosted && is_array($quality_options)) {
        foreach ($quality_options as $quality => $url) {
            if (!empty($url)) {
                $has_quality_options = true;
                break;
            }
        }
    }
    ?>    
    <div class="video-player-container" data-video-id="<?php echo $post_id; ?>" data-video-type="<?php echo esc_attr($video_type); ?>">
        <?php 
        if ($debug) {
            echo '<div style="background: #f5f5f5; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;">';
            echo '<strong>Debug Info (Legacy Mode):</strong><br>';
            echo 'Video Source Type: ' . esc_html($video_source_type) . '<br>';
            echo 'Video URL: ' . (empty($video_url) ? 'Not set' : esc_html($video_url)) . '<br>';
            echo 'Embed Code: ' . (empty($embed_code) ? 'Not set' : '[Set]') . '<br>';
            echo 'Video Type: ' . esc_html($video_type) . '<br>';
            echo '</div>';
        }
        
        // Display the appropriate video embed based on type
        if ($debug) {
            echo '<div style="background: #f9f9f9; padding: 5px; margin-bottom: 5px; border: 1px solid #ddd; font-size: 11px;">';
            echo 'Final decision: source_type=' . esc_html($video_source_type);
            echo ', video_type=' . esc_html($video_type);
            echo ', video_source=' . esc_html(substr($video_source, 0, 100)) . (strlen($video_source) > 100 ? '...' : '');
            echo '</div>';
        }
        
        // Enhanced debug output
        error_log("PLAYER DEBUG: source_type=$video_source_type, video_type=$video_type, has_source=" . (!empty($video_source) ? 'yes' : 'no'));
        
        // Check if we have a valid source
        if (!empty($video_source)) {
            $resulting_embed = customtube_get_embed_code($video_source, $video_type, $poster_url);
            
            // Debug output
            error_log("EMBED RESULT LENGTH: " . strlen($resulting_embed));
            if (strlen($resulting_embed) < 100) {
                error_log("EMBED RESULT CONTENT: $resulting_embed");
            }
            
            // If debug is enabled, show a debug box with the embed code
            if ($debug) {
                echo '<div style="background: #eaf7ea; padding: 5px; margin-bottom: 5px; border: 1px solid #ddd; font-size: 11px;">';
                echo 'Generated embed code: <pre style="white-wrap; word-break: break-all;">' . esc_html($resulting_embed) . '</pre>';
                echo '</div>';
            }
            
            // Output the embed code
            echo $resulting_embed;
        } else {
            echo '<div class="video-placeholder">';
            echo '<p>' . esc_html__('No video source provided.', 'customtube') . '</p>';
            echo '<p class="video-placeholder-tip">' . esc_html__('Please edit this video and add a video URL in the Video Details box.', 'customtube');
            echo '</div>';
        }
        
        // Only show custom controls for self-hosted videos
        if ($is_self_hosted) {
            $video_url = get_post_meta($post_id, 'video_url', true);
            if (!empty($video_url)) {
        ?>
        <div class="video-controls custom-controls">
            <div class="video-progress">
                <div class="progress-bar"></div>
            </div>
            
            <div class="control-buttons">
                <button class="play-pause" aria-label="Play/Pause">
                    <svg class="play-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M8 5v14l11-7z" fill="currentColor"/>
                    </svg>
                    <svg class="pause-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor"/>
                    </svg>
                </button>
                
                <div class="volume-control">
                    <button class="volume-button" aria-label="Mute/Unmute">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                            <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z" fill="currentColor"/>
                        </svg>
                    </button>
                    <input type="range" class="volume-slider" min="0" max="1" step="0.1" value="1">
                </div>
                
                <div class="time-display">
                    <span class="current-time">0:00</span> / <span class="duration">0:00</span>
                </div>
                
                <?php if ($has_quality_options): ?>
                <div class="quality-selector">
                    <button class="quality-button" aria-label="Video Quality">HD</button>
                    <div class="quality-options">
                        <?php foreach ($quality_options as $quality => $url): ?>
                            <?php if (!empty($url)): ?>
                                <button data-quality="<?php echo esc_attr($quality); ?>" data-url="<?php echo esc_url($url); ?>">
                                    <?php echo esc_html($quality); ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <button class="fullscreen-button" aria-label="Fullscreen">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </div>
        <?php } // end if !empty video_url
        } // end if is_self_hosted
        ?>
    </div>
    <?php
}
}

/**
 * Display related videos grid
 * 
 * @param int $post_id Post ID
 * @param int $count Number of videos to display
 * @return void
 */
if (!function_exists('customtube_related_videos')) {
function customtube_related_videos($post_id = null, $count = 12) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $related_videos = customtube_get_related_videos($post_id, $count);
    
    if ($related_videos->have_posts()) {
        ?>
        <div class="related-videos">
            <h3 class="related-videos-title"><?php esc_html_e('Related Videos', 'customtube'); ?></h3>
            
            <div class="video-grid">
                <?php while ($related_videos->have_posts()) : $related_videos->the_post(); ?>
                    <?php customtube_video_card(get_the_ID()); ?>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    }
}
}

/**
 * Get related videos
 * 
 * @param int $post_id Post ID
 * @param int $count Number of videos to retrieve
 * @return WP_Query
 */
if (!function_exists('customtube_get_related_videos')) {
function customtube_get_related_videos($post_id, $count = 12) {
    $post_terms = wp_get_post_terms($post_id, 'genre', array('fields' => 'ids'));
    $post_tags = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'ids'));
    $post_performers = wp_get_post_terms($post_id, 'performer', array('fields' => 'ids'));
    
    $args = array(
        'post_type'      => 'video',
        'posts_per_page' => $count,
        'post__not_in'   => array($post_id),
        'tax_query'      => array(
            'relation'   => 'OR',
        ),
    );

    // Add taxonomies to the tax query if they have terms
    if (!empty($post_terms)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genre',
            'field'    => 'term_id',
            'terms'    => $post_terms,
        );
    }
    
    if (!empty($post_tags)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'post_tag',
            'field'    => 'term_id',
            'terms'    => $post_tags,
        );
    }
    
    if (!empty($post_performers)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'performer',
            'field'    => 'term_id',
            'terms'    => $post_performers,
        );
    }
    
    return new WP_Query($args);
}
}

/**
 * Display video categories
 * 
 * @param int $post_id Post ID
 * @return void
 */
if (!function_exists('customtube_video_categories')) {
function customtube_video_categories($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $categories = get_the_terms($post_id, 'genre');
    
    if ($categories && !is_wp_error($categories)) {
        ?>
        <div class="video-categories">
            <?php foreach ($categories as $category) : ?>
                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="video-category">
                    <?php echo esc_html($category->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
}

/**
 * Display video performers
 * 
 * @param int $post_id Post ID
 * @return void
 */
if (!function_exists('customtube_video_performers')) {
function customtube_video_performers($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $performers = get_the_terms($post_id, 'performer');
    
    if ($performers && !is_wp_error($performers)) {
        ?>
        <div class="video-performers">
            <span class="performers-label"><?php esc_html_e('Performers:', 'customtube'); ?></span>
            <?php foreach ($performers as $performer) : ?>
                <a href="<?php echo esc_url(get_term_link($performer)); ?>" class="video-performer">
                    <?php echo esc_html($performer->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
}

/**
 * Display video tags
 * 
 * @param int $post_id Post ID
 * @return void
 */
if (!function_exists('customtube_video_tags')) {
function customtube_video_tags($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $tags = get_the_terms($post_id, 'post_tag');
    
    if ($tags && !is_wp_error($tags)) {
        ?>
        <div class="video-tags">
            <span class="tags-label"><?php esc_html_e('Tags:', 'customtube'); ?></span>
            <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="video-tag">
                    <?php echo esc_html($tag->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
}

/**
 * Display video grid
 * 
 * @param WP_Query $query The query
 * @param string $title Grid title
 * @return void
 */
if (!function_exists('customtube_video_grid')) {
function customtube_video_grid($query = null, $title = '') {
    if (null === $query) {
        global $wp_query;
        $query = $wp_query;
    }
    
    // Always show section heading even if no videos
    ?>
    <div class="video-section">
        <?php if ($title) : ?>
            <h2 class="section-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        
        <?php if ($query->have_posts()) : ?>
            <div class="video-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php customtube_video_card(get_the_ID()); ?>
                <?php endwhile; ?>
            </div>
            
            <?php customtube_pagination($query); ?>
            
            <?php wp_reset_postdata(); ?>
        <?php else: ?>
            <div class="no-videos">
                <p><?php 
                if ($title == __('Short Videos', 'customtube')) {
                    esc_html_e('No short videos found. Try adding videos less than 5 minutes long.', 'customtube');
                } else {
                    esc_html_e('No videos found.', 'customtube'); 
                }
                ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
}

/**
 * Display pagination
 * 
 * @param WP_Query $query The query
 * @return void
 */
if (!function_exists('customtube_pagination')) {
function customtube_pagination($query = null) {
    if (null === $query) {
        global $wp_query;
        $query = $wp_query;
    }
    
    $big = 999999999;
    $pages = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $query->max_num_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type'      => 'array',
    ));
    
    if (is_array($pages)) {
        ?>
        <nav class="pagination-container">
            <div class="pagination">
                <?php foreach ($pages as $page) : ?>
                    <div class="page-item"><?php echo $page; ?></div>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php
    }
}
}

/**
 * Display video information section
 * 
 * @param int $post_id Post ID
 * @return void
 */
if (!function_exists('customtube_video_info')) {
function customtube_video_info($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $views = get_post_meta($post_id, 'video_views', true);
    $views = $views ? intval($views) : 0;
    
    $likes = get_post_meta($post_id, 'video_likes', true);
    $likes_count = count($likes ? $likes : array());
    
    $author_id = get_post_field('post_author', $post_id);
    $author_name = get_the_author_meta('display_name', $author_id);
    ?>
    <div class="video-info">
        <h1 class="video-title"><?php echo get_the_title($post_id); ?></h1>
        
        <div class="video-meta">
            <div class="video-stats">
                <span class="video-views">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                    </svg>
                    <?php echo number_format($views); ?> <?php esc_html_e('views', 'customtube'); ?>
                </span>
                
                <span class="video-date">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z" fill="currentColor"/>
                    </svg>
                    <?php echo get_the_date('', $post_id); ?>
                </span>
            </div>
            
            <div class="video-actions">
                <?php if (is_user_logged_in()): ?>
                    <button class="like-button <?php echo customtube_is_video_liked($post_id) ? 'liked' : ''; ?>" data-post-id="<?php echo $post_id; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                        </svg>
                        <span class="like-count"><?php echo number_format($likes_count); ?></span>
                        <span class="like-text">
                            <?php 
                            if (customtube_is_video_liked($post_id)) {
                                esc_html_e('Liked', 'customtube');
                            } else {
                                esc_html_e('Like', 'customtube');
                            }
                            ?>
                        </span>
                    </button>
                <?php else: ?>
                    <div class="like-count-display">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                        </svg>
                        <span class="like-count"><?php echo number_format($likes_count); ?></span>
                    </div>
                <?php endif; ?>
                
                <button class="share-button" data-title="<?php echo esc_attr(get_the_title($post_id)); ?>" data-url="<?php echo esc_url(get_permalink($post_id)); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z" fill="currentColor"/>
                    </svg>
                    <span><?php esc_html_e('Share', 'customtube'); ?></span>
                </button>
            </div>
        </div>
        
        <div class="video-author">
            <div class="author-avatar">
                <?php echo get_avatar($author_id, 48); ?>
            </div>
            <div class="author-info">
                <h3 class="author-name"><?php echo esc_html($author_name); ?></h3>
            </div>
        </div>
        
        <div class="video-description">
            <?php the_content(); ?>
        </div>
        
        <?php customtube_video_categories($post_id); ?>
        <?php customtube_video_performers($post_id); ?>
        <?php customtube_video_tags($post_id); ?>
    </div>
    <?php
}
}

/**
 * Function to get formatted view count
 */
if (!function_exists('customtube_get_view_count')) {
function customtube_get_view_count($post_id) {
    $view_count = get_post_meta($post_id, 'video_views', true);
    $view_count = $view_count ? intval($view_count) : 0;
    
    if ($view_count >= 1000000) {
        return round($view_count / 1000000, 1) . 'M';
    } elseif ($view_count >= 1000) {
        return round($view_count / 1000, 1) . 'K';
    } else {
        return $view_count;
    }
}
}

/**
 * Function to check if video is liked by user
 */
if (!function_exists('customtube_is_video_liked')) {
function customtube_is_video_liked($post_id) {
    if (!is_user_logged_in()) {
        return false;
    }
    
    $user_id = get_current_user_id();
    $likes = get_post_meta($post_id, 'video_likes', true);
    $likes = $likes ? $likes : array();
    
    return in_array($user_id, $likes);
}
}

/**
 * Display performers directory
 * 
 * @param array $args Additional arguments
 */
if (!function_exists('customtube_performer_directory')) {
function customtube_performer_directory($args = array()) {
    $defaults = array(
        'hide_empty' => false,
        'number' => 0,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $args = wp_parse_args($args, $defaults);
    
    // Get all performers
    $performers = get_terms(array(
        'taxonomy' => 'performer',
        'hide_empty' => $args['hide_empty'],
        'number' => $args['number'],
        'orderby' => $args['orderby'],
        'order' => $args['order'],
    ));
    
    if (empty($performers) || is_wp_error($performers)) {
        echo '<p>' . esc_html__('No performers found.', 'customtube') . '</p>';
        return;
    }
    
    // Group performers by first letter
    $performer_groups = array();
    foreach ($performers as $performer) {
        $first_letter = strtoupper(substr($performer->name, 0, 1));
        if (!isset($performer_groups[$first_letter])) {
            $performer_groups[$first_letter] = array();
        }
        $performer_groups[$first_letter][] = $performer;
    }
    ksort($performer_groups);
    
    // Get all letters for navigation
    $all_letters = range('A', 'Z');
    $active_letters = array_keys($performer_groups);
    
    ?>
    <div class="performer-directory-container">
        <!-- A-Z Navigation -->
        <div class="performer-az-navigation">
            <?php foreach ($all_letters as $letter) : ?>
                <a href="#performer-letter-<?php echo esc_attr($letter); ?>" 
                   class="<?php echo in_array($letter, $active_letters) ? 'has-performers' : 'no-performers'; ?>">
                   <?php echo esc_html($letter); ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Performer sections by letter -->
        <?php foreach ($all_letters as $letter) : ?>
            <div id="performer-letter-<?php echo esc_attr($letter); ?>" class="performer-letter-section">
                <h2 class="performer-letter-heading"><?php echo esc_html($letter); ?></h2>
                
                <?php if (isset($performer_groups[$letter])) : ?>
                    <div class="performer-letter-content">
                        <?php foreach ($performer_groups[$letter] as $performer) : ?>
                            <div class="performer-item">
                                <a href="<?php echo esc_url(get_term_link($performer)); ?>">
                                    <?php echo esc_html($performer->name); ?>
                                    <span class="count"><?php echo esc_html($performer->count); ?></span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="performer-letter-empty">
                        <?php esc_html_e('No performers starting with this letter.', 'customtube'); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // A-Z Navigation
        $('.performer-az-navigation a').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            if ($(target).length) {
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 100
                }, 500);
            }
        });
        
        // Make navigation sticky
        var nav = $('.performer-az-navigation');
        var navTop = nav.offset().top;
        
        $(window).scroll(function() {
            var scrollTop = $(window).scrollTop();
            
            if (scrollTop > navTop) {
                nav.addClass('sticky');
            } else {
                nav.removeClass('sticky');
            }
        });
    });
    </script>
    <?php
}
}

/**
 * Display schema.org VideoObject structured data
 * 
 * @see inc/seo.php for implementation
 */
// Function moved to inc/seo.php to avoid duplication

/**
 * Modern CSS Grid layout for video grids
 * Replaces previous masonry layout with more efficient CSS Grid
 */
if (!function_exists('customtube_modern_grid_layout')) {
function customtube_modern_grid_layout() {
    // No JavaScript needed - using pure CSS Grid
    // The styles are defined in video-grid.css and trending-section-refactored.css
}
add_action('wp_footer', 'customtube_modern_grid_layout');
}

/**
 * Add infinite scroll for video grid
 * 
 * This is a basic implementation. For a more robust solution,
 * consider using a plugin like Jetpack's Infinite Scroll module.
 */
if (!function_exists('customtube_infinite_scroll')) {
function customtube_infinite_scroll() {
    // Only add on archive pages
    if (!is_archive() && !is_home() && !is_search()) {
        return;
    }
    
    global $wp_query;
    
    // Don't proceed if there are no more pages
    if ($wp_query->max_num_pages <= 1) {
        return;
    }
    
    $next_link = get_next_posts_link();
    if (!$next_link) {
        return;
    }
    
    ?>
    <div class="infinite-scroll-container">
        <div class="infinite-scroll-status">
            <div class="loading-spinner"></div>
            <p><?php esc_html_e('Loading more videos...', 'customtube'); ?></p>
        </div>
        <div class="infinite-scroll-last">
            <p><?php esc_html_e('You\'ve reached the end!', 'customtube'); ?></p>
        </div>
        <div class="infinite-scroll-error">
            <p><?php esc_html_e('Error loading videos', 'customtube'); ?></p>
        </div>
        <div class="pagination-wrap"><?php echo $next_link; ?></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var container = $('.video-grid');
        var status = $('.infinite-scroll-status');
        var loading = false;
        
        function loadMorePosts() {
            if (loading) return;
            
            var nextPage = $('.pagination-wrap a').attr('href');
            if (!nextPage) return;
            
            loading = true;
            status.find('.infinite-scroll-request').show();
            
            $.ajax({
                url: nextPage,
                type: 'GET',
                success: function(data) {
                    var $data = $(data);
                    var $newPosts = $data.find('.video-grid .video-card');
                    var $nextLink = $data.find('.pagination-wrap a');
                    
                    if ($newPosts.length) {
                        $newPosts.hide();
                        container.append($newPosts);
                        $newPosts.fadeIn(500);
                        
                        // Reinitialize hover previews and scripts
                        if (typeof initHoverPreviews === 'function') {
                            initHoverPreviews();
                        }
                        
                        // No masonry update needed - CSS Grid handles new items automatically
                        // Just trigger a redraw for any dynamic content that might need it
                        $(document).trigger('grid_items_added', [$newPosts]);
                    }
                    
                    if ($nextLink.length) {
                        $('.pagination-wrap a').attr('href', $nextLink.attr('href'));
                    } else {
                        $('.pagination-wrap').empty();
                        status.find('.infinite-scroll-last').show();
                    }
                    
                    loading = false;
                    status.find('.infinite-scroll-request').hide();
                },
                error: function() {
                    status.find('.infinite-scroll-request').hide();
                    status.find('.infinite-scroll-error').show();
                    loading = false;
                }
            });
        }
        
        // Load more posts when user scrolls near bottom
        $(window).scroll(function() {
            var scrollHeight = $(document).height();
            var scrollPosition = $(window).height() + $(window).scrollTop();
            
            if ((scrollHeight - scrollPosition) / scrollHeight <= 0.1) {
                loadMorePosts();
            }
        });
        
        // Hide status elements initially
        status.find('.infinite-scroll-request, .infinite-scroll-last, .infinite-scroll-error').hide();
        
        // Hide default pagination
        $('.pagination-container').hide();
    });
    </script>
    <?php
    // Add styles for infinite scroll
    echo '<style type="text/css">
        .infinite-scroll-container {
            margin-top: 2rem;
        }
        
        .infinite-scroll-status {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .infinite-scroll-request,
        .infinite-scroll-last,
        .infinite-scroll-error {
            padding: 1rem;
            background: var(--color-surface);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
        }
        
        .dark-mode .infinite-scroll-request,
        .dark-mode .infinite-scroll-last,
        .dark-mode .infinite-scroll-error {
            background: var(--color-dark-surface);
        }
        
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: var(--color-primary);
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 0.5rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .pagination-wrap {
            display: none;
        }
    </style>';
}
add_action('wp_footer', 'customtube_infinite_scroll');
}

/**
 * Helper function to get video data in a format suitable for the carousel.
 *
 * @param WP_Post $post The video post object.
 * @return array Formatted video data.
 */
function customtube_get_carousel_video_data($post) {
    $post_id = $post->ID;
    $video_url = get_post_meta($post_id, 'video_url', true);
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'video-large');
    $video_type = get_post_meta($post_id, 'video_type', true);

    // Determine carousel slide type (video or image)
    $carousel_video_type = 'image'; // Default to image
    if ($video_type === 'mp4' || $video_type === 'self-hosted') {
        $carousel_video_type = 'video';
    }

    return array(
        'id'          => $post_id,
        'type'        => $carousel_video_type,
        'imageUrl'    => $thumbnail_url ?: CUSTOMTUBE_URI . '/assets/images/default-video.jpg',
        'videoUrl'    => ($carousel_video_type === 'video') ? $video_url : '',
        'title'       => get_the_title($post_id),
        'description' => wp_trim_words(get_the_excerpt($post_id), 20),
        'buttonText'  => 'Watch Now',
        'buttonUrl'   => get_permalink($post_id),
    );
}

/**
 * Theme option helpers
 */
if (!function_exists('customtube_get_options')) {
function customtube_get_options() {
    $defaults = array(
        'age_verification_enabled' => true,
        'age_verification_text' => 'This website contains adult content and is only suitable for those who are 18 years or older. Please confirm your age to continue.',
        'age_verification_redirect' => 'https://www.google.com',
        'age_verification_cookie_expiry' => 30,
    );

    $options = get_option('customtube_options', array());
    return wp_parse_args($options, $defaults);
}
}

if (!function_exists('customtube_get_option')) {
function customtube_get_option($key, $default = null) {
    $options = customtube_get_options();

    if (isset($options[$key])) {
        return $options[$key];
    }

    return $default;
}
}

/**
 * AJAX handler for retrieving liked videos
 */
if (!function_exists('customtube_get_liked_videos')) {
function customtube_get_liked_videos() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'recent';
    $per_page = 12;

    $user_id = get_current_user_id();
    $liked_videos = get_user_meta($user_id, 'liked_videos', true);
    if (!is_array($liked_videos)) {
        $liked_videos = array();
    }

    if (empty($liked_videos)) {
        wp_send_json_success(array(
            'videos' => array(),
            'total' => 0,
            'has_more' => false
        ));
    }

    $args = array(
        'post_type' => 'video',
        'post_status' => 'publish',
        'post__in' => $liked_videos,
        'posts_per_page' => $per_page,
        'paged' => $page,
        'meta_query' => array(
            array(
                'key' => 'video_file',
                'compare' => 'EXISTS'
            )
        )
    );

    switch ($sort) {
        case 'oldest':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'duration':
            $args['meta_key'] = 'video_duration';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }

    $query = new WP_Query($args);
    $videos = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $videos[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'url' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url($post_id, 'video-thumbnail') ?: CUSTOMTUBE_URI . '/assets/images/default-video.jpg',
                'duration' => get_post_meta($post_id, 'video_duration', true) ?: '0:00',
                'views' => number_format(intval(get_post_meta($post_id, 'video_views', true))),
                'date' => get_the_date('M j, Y'),
                'liked' => true
            );
        }
    }

    wp_reset_postdata();

    wp_send_json_success(array(
        'videos' => $videos,
        'total' => count($liked_videos),
        'has_more' => $query->max_num_pages > $page
    ));
}
}
add_action('wp_ajax_get_liked_videos', 'customtube_get_liked_videos');

/**
 * Asset version helpers
 */
if (!function_exists('customtube_get_css_version')) {
function customtube_get_css_version() {
    $compiled_css_file = CUSTOMTUBE_DIR . '/dist/css/main.css';

    if (file_exists($compiled_css_file)) {
        return filemtime($compiled_css_file);
    }

    return CUSTOMTUBE_VERSION;
}
}

if (!function_exists('customtube_get_js_version')) {
function customtube_get_js_version() {
    $compiled_js_file = CUSTOMTUBE_DIR . '/dist/js/customtube.min.js';

    if (file_exists($compiled_js_file)) {
        return filemtime($compiled_js_file);
    }

    return CUSTOMTUBE_VERSION;
}
}

/**
 * Page template resolver
 */
if (!function_exists('customtube_get_page_template')) {
function customtube_get_page_template($page_template) {
    $template_slug = get_page_template_slug();
    if (empty($template_slug)) {
        return $page_template;
    }

    $custom_template_path = CUSTOMTUBE_DIR . '/' . $template_slug;

    if (file_exists($custom_template_path)) {
        return $custom_template_path;
    }

    return $page_template;
}
}
add_filter('page_template', 'customtube_get_page_template');

/**
 * Query helper: trending videos
 */
if (!function_exists('customtube_get_trending_videos')) {
function customtube_get_trending_videos($count = 8) {
    $cache_key = 'customtube_trending_videos_' . $count;
    $query = get_transient($cache_key);

    if (false === $query) {
        $args = array(
            'post_type'      => 'video',
            'posts_per_page' => $count,
            'meta_key'       => 'video_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        );
        $query = new WP_Query($args);
        set_transient($cache_key, $query, HOUR_IN_SECONDS);
    }
    return $query;
}
}

/**
 * Query helper: recent videos
 */
if (!function_exists('customtube_get_recent_videos')) {
function customtube_get_recent_videos($count = 8) {
    $cache_key = 'customtube_recent_videos_' . $count;
    $query = get_transient($cache_key);

    if (false === $query) {
        $args = array(
            'post_type'      => 'video',
            'posts_per_page' => $count,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        );
        $query = new WP_Query($args);
        set_transient($cache_key, $query, DAY_IN_SECONDS);
    }
    return $query;
}
}

/**
 * Query helper: videos filtered by duration
 */
if (!function_exists('customtube_get_videos_by_duration')) {
function customtube_get_videos_by_duration($duration = 'medium', $count = 8) {
    $cache_key = 'customtube_videos_duration_' . $duration . '_' . $count;
    $query = get_transient($cache_key);

    if (false === $query) {
        $meta_query = array();

        switch ($duration) {
            case 'short':
                $max_seconds = 300;
                $meta_query = array(
                    'key'     => 'duration_seconds',
                    'value'   => $max_seconds,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                );
                break;

            case 'medium':
                $min_seconds = 300;
                $max_seconds = 1200;
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'duration_seconds',
                        'value'   => $min_seconds,
                        'compare' => '>',
                        'type'    => 'NUMERIC',
                    ),
                    array(
                        'key'     => 'duration_seconds',
                        'value'   => $max_seconds,
                        'compare' => '<=',
                        'type'    => 'NUMERIC',
                    ),
                );
                break;

            case 'long':
                $min_seconds = 1200;
                $meta_query = array(
                    'key'     => 'duration_seconds',
                    'value'   => $min_seconds,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                );
                break;
        }

        $args = array(
            'post_type'      => 'video',
            'posts_per_page' => $count,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        );

        if (!empty($meta_query)) {
            $args['meta_query'] = array($meta_query);
        }
        $query = new WP_Query($args);
        set_transient($cache_key, $query, DAY_IN_SECONDS);
    }
    return $query;
}
}

/**
 * Query helper: videos by category
 */
if (!function_exists('customtube_get_videos_by_category')) {
function customtube_get_videos_by_category($category, $count = 8) {
    $cache_key = 'customtube_videos_category_' . (is_numeric($category) ? $category : sanitize_title($category)) . '_' . $count;
    $query = get_transient($cache_key);

    if (false === $query) {
        $field = is_numeric($category) ? 'term_id' : 'slug';

        $args = array(
            'post_type'      => 'video',
            'posts_per_page' => $count,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'genre',
                    'field'    => $field,
                    'terms'    => $category,
                ),
            ),
        );
        $query = new WP_Query($args);
        set_transient($cache_key, $query, DAY_IN_SECONDS);
    }
    return $query;
}
}

/**
 * Query helper: most popular categories
 */
if (!function_exists('customtube_get_popular_categories')) {
function customtube_get_popular_categories($count = 8) {
    $cache_key = 'customtube_popular_categories_' . $count;
    $categories = get_transient($cache_key);

    if (false === $categories) {
        $categories = get_terms(array(
            'taxonomy'   => 'genre',
            'hide_empty' => true,
            'number'     => $count,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ));

        if (empty($categories) || is_wp_error($categories)) {
            $categories = get_terms(array(
                'taxonomy'   => 'category',
                'hide_empty' => true,
                'number'     => $count,
                'orderby'    => 'count',
                'order'      => 'DESC',
            ));
        }

        if (empty($categories) || is_wp_error($categories)) {
            $categories = array();
        }
        set_transient($cache_key, $categories, DAY_IN_SECONDS);
    }
    return $categories;
}
}
