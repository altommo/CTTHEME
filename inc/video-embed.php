<?php
/**
 * Video Embedding Core Functionality
 *
 * Unified video embed handling for all platforms
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Detect the type of video from a URL
 * 
 * @param string $url The video URL to analyze
 * @return array Video information with type, id, and any other relevant data
 */
function customtube_detect_video($url) {
    $result = array(
        'type' => 'unknown',
        'id' => '',
        'url' => $url,
        'embed_url' => '',
        'thumbnail_url' => '',
        'direct_url' => '',
    );
    
    // Skip if URL is empty
    if (empty($url)) {
        return $result;
    }
    
    // Check if it's a direct video file
    if (preg_match('/\.(mp4|m4v|webm|ogg)(\?.*)?$/i', $url)) {
        $result['type'] = 'direct';
        $result['direct_url'] = $url;
        return $result;
    }
    
    // YouTube
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        $video_id = $matches[1];
        $result['type'] = 'youtube';
        $result['id'] = $video_id;
        $result['embed_url'] = "https://www.youtube.com/embed/{$video_id}?rel=0&showinfo=0&modestbranding=1";
        $result['thumbnail_url'] = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
        $result['preview_url'] = "https://i.ytimg.com/an_webp/{$video_id}/mqdefault_6s.webp?du=3000";
        return $result;
    }
    
    // Vimeo
    if (preg_match('/vimeo\.com\/(?:.*\/)?(?:videos\/)?([0-9]+)/', $url, $matches)) {
        $video_id = $matches[1];
        $result['type'] = 'vimeo';
        $result['id'] = $video_id;
        $result['embed_url'] = "https://player.vimeo.com/video/{$video_id}?api=1&title=0&byline=0&portrait=0&dnt=1";
        // Vimeo requires API call to get thumbnail, which we'll handle separately
        return $result;
    }
    
    // XHamster
    if (preg_match('/(xhamster\.(?:com|desi|uno|name)|xh\.video)/', $url)) {
        $result['type'] = 'xhamster';
        
        // Save the original URL
        $result['direct_url'] = $url;
        
        // Extract the full slug first if available
        $slug = '';
        if (preg_match('#xhamster\.(?:com|desi|uno|name)/videos/([^/&?\s]+)#i', $url, $slug_matches)) {
            $slug = $slug_matches[1];
            
            // Check if the slug contains the xh ID format (e.g. "i-came-to-donate-sperm-but-the-nurse-checked-my-dick-xhXMSSQ")
            if (preg_match('/^(.*-)?xh([A-Za-z0-9]+)$/', $slug, $id_matches)) {
                // Extract the ID (after "xh")
                $result['id'] = $id_matches[2];
                $result['embed_url'] = "https://xhamster.com/embed/xh{$result['id']}";
                return $result;
            }
        }
        
        // If we didn't find an ID from the slug, try other patterns
        // Extract ID using various patterns
        if (preg_match('#xhamster\.(?:com|desi|uno|name)/videos/([^/]+)-([0-9]+)#i', $url, $matches)) {
            $result['id'] = $matches[2];
        } else if (preg_match('#xh\.video/e/([^&\s/]+)#i', $url, $matches)) {
            $result['id'] = $matches[1];
        } else if (preg_match('#xhamster\.(?:com|desi|uno|name)/embed/([0-9]+)#i', $url, $matches)) {
            $result['id'] = $matches[1];
        } else if (preg_match('#xhamster\.(?:com|desi|uno|name)/embed/xh([0-9]+)#i', $url, $matches)) {
            $result['id'] = $matches[1];
            $result['embed_url'] = "https://xhamster.com/embed/xh{$result['id']}";
            return $result;
        } else if (preg_match('#xhamster\.(?:com|desi|uno|name)/embed/([^/&?\s]+)#i', $url, $matches)) {
            $result['id'] = $matches[1];
        } else if (preg_match('#xhamster\.(?:com|desi|uno|name)/(?:[^/]+/)*(?:[^-]+-)([0-9]+)(?:[^0-9]|$)#i', $url, $matches)) {
            $result['id'] = $matches[1];
        }
        
        // If we have a slug but no ID, use the slug as ID
        if (empty($result['id']) && !empty($slug)) {
            $result['id'] = $slug;
        }
        
        // Generate the embed URL
        if (!empty($result['id'])) {
            // If ID starts with "xh", format differently
            if (preg_match('/^xh([A-Za-z0-9]+)$/i', $result['id'], $xh_matches)) {
                $result['embed_url'] = "https://xhamster.com/embed/xh{$xh_matches[1]}";
            } else {
                $result['embed_url'] = "https://xhamster.com/embed/{$result['id']}";
            }
        } else if (!empty($slug)) {
            // Last resort - use the slug for the embed
            $result['embed_url'] = "https://xhamster.com/embed/{$slug}";
        }
        
        return $result;
    }
    
    // PornHub
    if (strpos($url, 'pornhub.com') !== false) {
        $result['type'] = 'pornhub';
        
        // Extract video ID from different PornHub URL formats
        if (preg_match('/viewkey=([^&]+)/', $url, $matches)) {
            $result['id'] = $matches[1];
        } else if (preg_match('#pornhub\.com/view_video\.php\?viewkey=([^&\s]+)#i', $url, $matches)) {
            $result['id'] = $matches[1];
        } else if (preg_match('#pornhub\.com/embed/([^/\s&?]+)#i', $url, $matches)) {
            $result['id'] = $matches[1];
        }
        
        if (!empty($result['id'])) {
            $result['embed_url'] = "https://www.pornhub.com/embed/{$result['id']}";
        }
        
        return $result;
    }
    
    // XVideos
    if (strpos($url, 'xvideos.com') !== false) {
        $result['type'] = 'xvideos';
        
        if (preg_match('#xvideos\.com/video([0-9]+)#i', $url, $matches)) {
            $result['id'] = $matches[1];
            $result['embed_url'] = "https://www.xvideos.com/embedframe/{$result['id']}";
        }
        
        return $result;
    }
    
    // Return original URL if type couldn't be determined
    return $result;
}

/**
 * Generate embed code for a video
 * 
 * @param array $video_data Video data from customtube_detect_video()
 * @return string HTML embed code
 */
function customtube_generate_embed($video_data) {
    $embed_code = '';
    $poster_attr = !empty($video_data['thumbnail_url']) ? ' poster="' . esc_url($video_data['thumbnail_url']) . '"' : '';
    
    switch ($video_data['type']) {
        case 'direct':
            // Direct video file
            $video_url = $video_data['url'];
            $embed_code = '<video class="main-video-player"' . $poster_attr . ' preload="metadata" controls playsinline>
                <source src="' . esc_url($video_url) . '" type="video/mp4">
                Your browser does not support the video tag.
            </video>';
            break;
            
        case 'youtube':
            // YouTube embed
            if (!empty($video_data['embed_url'])) {
                $embed_code = '<div class="embed-responsive">
                    <iframe class="embed-iframe" 
                        src="' . esc_url($video_data['embed_url']) . '" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                        loading="lazy">
                    </iframe>
                </div>';
            }
            break;
            
        case 'vimeo':
            // Vimeo embed
            if (!empty($video_data['embed_url'])) {
                $embed_code = '<div class="embed-responsive">
                    <iframe class="embed-iframe" 
                        src="' . esc_url($video_data['embed_url']) . '" 
                        frameborder="0" 
                        allow="autoplay; fullscreen; picture-in-picture" 
                        allowfullscreen
                        loading="lazy">
                    </iframe>
                </div>';
            }
            break;
            
        case 'xhamster':
        case 'pornhub':
        case 'xvideos':
            // Adult sites embeds
            if (!empty($video_data['embed_url'])) {
                $embed_code = '<div class="embed-responsive">
                    <iframe class="embed-iframe" 
                        src="' . esc_url($video_data['embed_url']) . '" 
                        frameborder="0" 
                        allow="autoplay; fullscreen" 
                        allowfullscreen
                        scrolling="no"
                        loading="lazy">
                    </iframe>
                </div>';
            }
            break;
            
        default:
            // Unknown type - try a generic embed if we have a URL
            if (!empty($video_data['url']) && filter_var($video_data['url'], FILTER_VALIDATE_URL)) {
                $embed_code = '<div class="embed-responsive">
                    <iframe class="embed-iframe" 
                        src="' . esc_url($video_data['url']) . '" 
                        frameborder="0" 
                        allowfullscreen
                        loading="lazy">
                    </iframe>
                </div>';
            } else {
                $embed_code = '<div class="video-placeholder">
                    <p>' . esc_html__('No video source provided.', 'customtube') . '</p>
                    <p class="video-placeholder-tip">' . esc_html__('Please edit this video and add a video URL in the Video Details box.', 'customtube') . '</p>
                </div>';
            }
            break;
    }
    
    return $embed_code;
}

/**
 * Save video meta data during post save
 * 
 * @param int $post_id The post ID
 * @param WP_Post $post The post object
 */
function customtube_save_video_meta($post_id, $post) {
    // Skip if this is an autosave or not a video post
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== 'video') return;
    
    // Get the video URL
    $video_url = isset($_POST['video_url']) ? sanitize_text_field($_POST['video_url']) : '';
    
    if (!empty($video_url)) {
        // Update the video URL
        update_post_meta($post_id, 'video_url', $video_url);
        
        // Detect video type and metadata
        $video_data = customtube_detect_video($video_url);
        
        // Save video type and ID
        update_post_meta($post_id, 'video_type', $video_data['type']);
        if (!empty($video_data['id'])) {
            update_post_meta($post_id, 'video_id', $video_data['id']);
        }
        
        // Generate embed code
        $embed_code = customtube_generate_embed($video_data);
        update_post_meta($post_id, 'embed_code', $embed_code);
        
        // Save thumbnail URL if available
        if (!empty($video_data['thumbnail_url'])) {
            update_post_meta($post_id, 'thumbnail_url', $video_data['thumbnail_url']);
        }
        
        // Save preview URL if available
        if (!empty($video_data['preview_url'])) {
            update_post_meta($post_id, 'preview_url', $video_data['preview_url']);
        }
    }
    
    // Save other meta fields
    if (isset($_POST['preview_url'])) {
        update_post_meta($post_id, 'preview_url', esc_url_raw($_POST['preview_url']));
    }
    
    if (isset($_POST['video_duration'])) {
        update_post_meta($post_id, 'video_duration', sanitize_text_field($_POST['video_duration']));
    }
    
    if (isset($_POST['quality_options']) && is_array($_POST['quality_options'])) {
        $quality_options = array();
        foreach ($_POST['quality_options'] as $quality => $url) {
            $quality_options[$quality] = esc_url_raw($url);
        }
        update_post_meta($post_id, 'quality_options', $quality_options);
    }
}
add_action('save_post', 'customtube_save_video_meta', 10, 2);

/**
 * AJAX handler for auto-detecting video information
 */
function customtube_ajax_detect_video() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_video_details')) {
        wp_send_json_error('Invalid security token.');
    }
    
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    
    if (empty($url)) {
        wp_send_json_error('No URL provided.');
    }
    
    // Detect video type and metadata
    $video_data = customtube_detect_video($url);
    
    // For YouTube, get additional metadata
    if ($video_data['type'] === 'youtube' && !empty($video_data['id'])) {
        // Use YouTube API if available
        $api_key = get_option('customtube_youtube_api_key');
        
        if (!empty($api_key)) {
            $api_url = "https://www.googleapis.com/youtube/v3/videos?id={$video_data['id']}&key={$api_key}&part=snippet,contentDetails,statistics";
            
            $response = wp_remote_get($api_url);
            
            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                
                if (!empty($data['items'][0])) {
                    $item = $data['items'][0];
                    
                    // Get title
                    if (!empty($item['snippet']['title'])) {
                        $video_data['title'] = $item['snippet']['title'];
                    }
                    
                    // Get description
                    if (!empty($item['snippet']['description'])) {
                        $video_data['description'] = $item['snippet']['description'];
                    }
                    
                    // Get duration
                    if (!empty($item['contentDetails']['duration'])) {
                        $duration = $item['contentDetails']['duration'];
                        $video_data['duration'] = customtube_format_iso8601_duration($duration);
                    }
                    
                    // Get view count
                    if (!empty($item['statistics']['viewCount'])) {
                        $video_data['views'] = intval($item['statistics']['viewCount']);
                    }
                    
                    // Get thumbnail
                    if (!empty($item['snippet']['thumbnails']['maxres']['url'])) {
                        $video_data['thumbnail_url'] = $item['snippet']['thumbnails']['maxres']['url'];
                    } elseif (!empty($item['snippet']['thumbnails']['high']['url'])) {
                        $video_data['thumbnail_url'] = $item['snippet']['thumbnails']['high']['url'];
                    }
                }
            }
        }
    }
    
    // Return the video data
    wp_send_json_success($video_data);
}
add_action('wp_ajax_customtube_detect_video', 'customtube_ajax_detect_video');

/**
 * Convert ISO 8601 duration format to human-readable format
 * 
 * @param string $duration ISO 8601 duration string (e.g., PT1H2M3S)
 * @return string Formatted duration (e.g., 1:02:03)
 */
function customtube_format_iso8601_duration($duration) {
    $matches = array();
    preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);
    
    $hours = isset($matches[1]) ? intval($matches[1]) : 0;
    $minutes = isset($matches[2]) ? intval($matches[2]) : 0;
    $seconds = isset($matches[3]) ? intval($matches[3]) : 0;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}

/**
 * Add video auto-detection JavaScript
 */
function customtube_enqueue_video_js() {
    global $post_type;
    
    // Only on video post edit screen
    if ($post_type === 'video' && (is_admin() && (get_current_screen()->base === 'post'))) {
        wp_enqueue_script(
            'customtube-video-embed',
            CUSTOMTUBE_URI . '/assets/js/video-embed.js',
            array('jquery'),
            CUSTOMTUBE_VERSION,
            true
        );
        
        wp_localize_script('customtube-video-embed', 'customtubeVideo', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('customtube_video_details'),
            'errorMsg' => __('Error detecting video: ', 'customtube'),
        ));
        
        // Add styles for the video embed UI
        wp_enqueue_style(
            'customtube-video-embed-css',
            CUSTOMTUBE_URI . '/assets/css/video-embed.css',
            array(),
            CUSTOMTUBE_VERSION
        );
    }
}
add_action('admin_enqueue_scripts', 'customtube_enqueue_video_js');

/**
 * Display a video on the frontend
 * 
 * @param int $post_id Post ID
 */
function customtube_display_video($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Get video URL and other meta
    $video_url = get_post_meta($post_id, 'video_url', true);
    $video_type = get_post_meta($post_id, 'video_type', true);
    $embed_code = get_post_meta($post_id, 'embed_code', true);
    
    // If we have a URL but no embed code or type, generate them
    if (!empty($video_url) && (empty($embed_code) || empty($video_type))) {
        $video_data = customtube_detect_video($video_url);
        $video_type = $video_data['type'];
        $embed_code = customtube_generate_embed($video_data);
        
        // Update the meta for future use
        update_post_meta($post_id, 'video_type', $video_type);
        update_post_meta($post_id, 'embed_code', $embed_code);
    }
    
    // Display the video
    echo '<div class="video-player-container" data-video-type="' . esc_attr($video_type) . '" data-video-id="' . esc_attr(get_post_meta($post_id, 'video_id', true)) . '" data-video-url="' . esc_attr($video_url) . '">';
    
    if (!empty($embed_code)) {
        echo $embed_code;
    } else if (!empty($video_url)) {
        // Fallback direct player
        $poster_url = get_the_post_thumbnail_url($post_id, 'video-large');
        echo '<video class="main-video-player" controls playsinline preload="metadata"' . 
             (!empty($poster_url) ? ' poster="' . esc_url($poster_url) . '"' : '') . '>
            <source src="' . esc_url($video_url) . '" type="video/mp4">
            Your browser does not support the video tag.
        </video>';
    } else {
        // No video found
        echo '<div class="video-placeholder">
            <p>' . esc_html__('No video source provided.', 'customtube') . '</p>
            <p class="video-placeholder-tip">' . esc_html__('Please edit this video and add a video URL in the Video Details box.', 'customtube') . '</p>
        </div>';
    }
    
    echo '</div>';
}