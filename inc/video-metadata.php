<?php
/**
 * Video Metadata Detection System
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Helper function to set a featured image from a URL
 * 
 * @param int $post_id Post ID
 * @param string $image_url Image URL
 * @return bool Success status
 */
if (!function_exists('customtube_set_featured_image_from_url')) {
    function customtube_set_featured_image_from_url($post_id, $image_url) {
        // Log critical information
        error_log("FEATURED IMAGE: Setting for post ID $post_id with URL: $image_url");
        
        // Validate inputs
        if (empty($post_id) || empty($image_url)) {
            error_log("FEATURED IMAGE ERROR: Empty post ID or image URL");
            return false;
        }
        
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            error_log("FEATURED IMAGE ERROR: Invalid image URL: $image_url");
            return false;
        }
        
        // Check if the post exists
        $post = get_post($post_id);
        if (!$post) {
            error_log("FEATURED IMAGE ERROR: Post ID $post_id does not exist");
            return false;
        }
        
        // Need these files for media_sideload_image() and wp_get_attachment_image()
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Check if image already exists in media library by URL
        global $wpdb;
        $image_url_md5 = md5($image_url);
        $existing_attachment = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_source_url_md5' AND meta_value = %s",
                $image_url_md5
            )
        );
        
        if ($existing_attachment) {
            error_log("FEATURED IMAGE: Found existing attachment ID $existing_attachment for URL: $image_url");
            $attachment_id = $existing_attachment;
        } else {
            // First check if the URL is accessible
            $response = wp_remote_head($image_url, array('timeout' => 10));
            if (is_wp_error($response)) {
                error_log("FEATURED IMAGE ERROR: Image URL is not accessible: " . $response->get_error_message());
                return false;
            }
            
            // Check the HTTP status code
            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code !== 200) {
                error_log("FEATURED IMAGE ERROR: Image URL returned HTTP status $status_code: $image_url");
                return false;
            }
            
            // Check for CORS restrictions
            $headers = wp_remote_retrieve_headers($response);
            if (isset($headers['access-control-allow-origin']) && 
                $headers['access-control-allow-origin'] !== '*' && 
                strpos($headers['access-control-allow-origin'], $_SERVER['HTTP_HOST']) === false) {
                error_log("FEATURED IMAGE WARNING: Image URL may have CORS restrictions. Proceeding anyway...");
            }
            
            // If it's a YouTube thumbnail URL, modify it for better compatibility
            if (strpos($image_url, 'img.youtube.com') !== false) {
                if (strpos($image_url, 'maxresdefault.jpg') !== false) {
                    // Also try the high-quality thumbnail as fallback
                    $alt_image_url = str_replace('maxresdefault.jpg', 'hqdefault.jpg', $image_url);
                    error_log("FEATURED IMAGE: Also trying alt YouTube URL: $alt_image_url");
                }
            }
            
            // Try to download the image
            error_log("FEATURED IMAGE: Attempting to download from URL: $image_url");
            $attachment_id = media_sideload_image($image_url, $post_id, '', 'id');
            
            // If there was an error and we have an alternate URL, try that
            if (is_wp_error($attachment_id) && isset($alt_image_url)) {
                error_log("FEATURED IMAGE: Primary URL failed, trying alternate URL: $alt_image_url");
                $attachment_id = media_sideload_image($alt_image_url, $post_id, '', 'id');
            }
            
            // If there was an error, try direct file download
            if (is_wp_error($attachment_id)) {
                error_log("FEATURED IMAGE ERROR: media_sideload_image failed: " . $attachment_id->get_error_message());
                
                // Try a more direct approach
                $tmp_file = download_url($image_url);
                if (is_wp_error($tmp_file)) {
                    error_log("FEATURED IMAGE ERROR: direct download_url failed: " . $tmp_file->get_error_message());
                    return false;
                }
                
                // Determine file extension from URL
                $url_path = parse_url($image_url, PHP_URL_PATH);
                $file_name = basename($url_path);
                $file_array = array(
                    'name' => $file_name,
                    'tmp_name' => $tmp_file,
                    'error' => 0,
                    'size' => filesize($tmp_file),
                );
                
                // Use WordPress's built-in file handling function to move the temp file
                $attachment_id = media_handle_sideload($file_array, $post_id);
                
                if (is_wp_error($attachment_id)) {
                    // Clean up the temp file
                    @unlink($tmp_file);
                    error_log("FEATURED IMAGE ERROR: media_handle_sideload failed: " . $attachment_id->get_error_message());
                    return false;
                }
            }
            
            // Store the original URL hash to avoid duplicate downloads
            if (!is_wp_error($attachment_id)) {
                update_post_meta($attachment_id, '_source_url', $image_url);
                update_post_meta($attachment_id, '_source_url_md5', $image_url_md5);
            }
        }
        
        // Final error check before setting featured image
        if (is_wp_error($attachment_id)) {
            error_log("FEATURED IMAGE ERROR: Final attachment ID check failed: " . $attachment_id->get_error_message());
            return false;
        }
        
        // Remove any existing featured image
        $existing_thumbnail_id = get_post_thumbnail_id($post_id);
        if ($existing_thumbnail_id) {
            error_log("FEATURED IMAGE: Removing existing thumbnail (ID: $existing_thumbnail_id)");
            delete_post_thumbnail($post_id);
        }
        
        // Set the featured image using attachment ID
        error_log("FEATURED IMAGE: Setting attachment ID $attachment_id as featured image for post ID $post_id");
        $result = set_post_thumbnail($post_id, $attachment_id);
        
        if ($result) {
            error_log("FEATURED IMAGE: Successfully set featured image!");
            
            // Extra validation - did it REALLY work?
            $new_thumbnail_id = get_post_thumbnail_id($post_id);
            if ($new_thumbnail_id == $attachment_id) {
                error_log("FEATURED IMAGE: Verified thumbnail ID matches: $new_thumbnail_id");
            } else {
                error_log("FEATURED IMAGE WARNING: Thumbnail ID mismatch - expected $attachment_id, got $new_thumbnail_id");
            }
            
            return true;
        } else {
            error_log("FEATURED IMAGE ERROR: set_post_thumbnail failed");
            return false;
        }
    }
}

/**
 * Fetch metadata for a given video URL or embed code
 *
 * @param string $video_source The URL or embed code
 * @param string $source_type Either 'direct' or 'embed'
 * @return array|WP_Error Metadata array or WP_Error on failure
 */
function customtube_fetch_video_metadata($video_source, $source_type = 'direct') {
    // Default metadata structure
    $metadata = array(
        'duration'         => '',
        'duration_seconds' => 0,
        'width'            => 0,
        'height'           => 0,
        'filesize'         => 0,
        'source'           => '',
        'source_url'       => '',
        'title'            => '',
        'thumbnail_url'    => '',
        'description'      => '',
    );
    
    if (empty($video_source)) {
        return new WP_Error('empty_source', __('No video source provided', 'customtube'));
    }
    
    // Determine the type of source
    if ($source_type === 'direct') {
        // For direct MP4/WebM URLs
        $metadata = customtube_get_direct_video_metadata($video_source, $metadata);
    } else {
        // For embed codes or external video URLs
        $video_type = customtube_get_video_type($video_source);
        
        switch ($video_type) {
            case 'youtube':
                $metadata = customtube_get_youtube_metadata($video_source, $metadata);
                break;
                
            case 'vimeo':
                $metadata = customtube_get_vimeo_metadata($video_source, $metadata);
                break;
                
            case 'pornhub':
                $metadata = customtube_get_pornhub_metadata($video_source, $metadata);
                break;
                
            case 'xvideos':
                $metadata = customtube_get_xvideos_metadata($video_source, $metadata);
                break;
                
            case 'xhamster':
                $metadata = customtube_get_xhamster_metadata($video_source, $metadata);
                break;
                
            case 'iframe':
            case 'unknown':
                // For generic iframe embeds, we can't reliably get metadata
                $metadata['source'] = 'iframe';
                if (preg_match('/src=["\'](https?:\/\/[^"\']+)["\']/', $video_source, $matches)) {
                    $metadata['source_url'] = $matches[1];
                    $domain = parse_url($metadata['source_url'], PHP_URL_HOST);
                    $metadata['source'] = str_replace('www.', '', $domain);
                }
                break;
        }
    }
    
    // Try to extract a thumbnail URL if none was found
    if (empty($metadata['thumbnail_url']) && function_exists('customtube_extract_video_thumbnail')) {
        $thumbnail_url = customtube_extract_video_thumbnail($video_source, $source_type);
        if ($thumbnail_url) {
            $metadata['thumbnail_url'] = $thumbnail_url;
        }
    }
    
    // Set last fetched timestamp
    $metadata['metadata_last_fetched'] = time();
    
    return $metadata;
}

/**
 * Get metadata for direct video files (MP4, WebM)
 * 
 * @param string $url Video URL
 * @param array $metadata Initial metadata array
 * @return array Updated metadata
 */
function customtube_get_direct_video_metadata($url, $metadata) {
    // Only proceed if we have the getID3 library (WordPress core includes it)
    if (!function_exists('wp_get_attachment_metadata') || !function_exists('wp_read_video_metadata')) {
        return $metadata;
    }
    
    // Try to get video metadata using WordPress functions
    $video_data = wp_read_video_metadata($url);
    
    if (!empty($video_data)) {
        // Set duration
        if (!empty($video_data['length'])) {
            $duration_seconds = (int)$video_data['length'];
            $metadata['duration_seconds'] = $duration_seconds;
            $metadata['duration'] = customtube_format_duration($duration_seconds);
        }
        
        // Set dimensions
        if (!empty($video_data['width']) && !empty($video_data['height'])) {
            $metadata['width'] = (int)$video_data['width'];
            $metadata['height'] = (int)$video_data['height'];
        }
        
        // Set filesize if we can determine it
        if (!empty($video_data['filesize'])) {
            $metadata['filesize'] = (int)$video_data['filesize'];
        } else {
            // Try to get filesize using headers
            $metadata['filesize'] = customtube_get_remote_filesize($url);
        }
        
        // Set source info
        $metadata['source'] = 'direct';
        $metadata['source_url'] = $url;
    } else {
        // Fallback for remote videos we can't directly analyze
        // Try to get filesize
        $metadata['filesize'] = customtube_get_remote_filesize($url);
        
        // Set source info
        $metadata['source'] = 'direct';
        $metadata['source_url'] = $url;
    }
    
    return $metadata;
}

/**
 * Get metadata for YouTube videos
 * 
 * @param string $url YouTube URL or embed code
 * @param array $metadata Initial metadata array
 * @return array Updated metadata
 */
function customtube_get_youtube_metadata($url, $metadata) {
    // Extract YouTube video ID
    $video_id = '';
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    if (empty($video_id)) {
        // Try harder to extract a YouTube video ID - sometimes the regex misses some formats
        if (strpos($url, 'youtube.com') !== false && preg_match('/[?&]v=([^&]{11})/', $url, $matches)) {
            $video_id = $matches[1];
        } elseif (strpos($url, 'youtu.be') !== false && preg_match('/youtu\.be\/([^?&\/\s]{11})/', $url, $matches)) {
            $video_id = $matches[1];
        }
    }
    
    if (empty($video_id)) {
        // Log the failure for debugging
        error_log("Failed to extract YouTube video ID from: $url");
        return $metadata;
    }
    
    // Log success
    error_log("Successfully extracted YouTube video ID: $video_id from URL: $url");
    
    // Use our enhanced YouTube metadata extraction function if available
    if (function_exists('customtube_extract_youtube_detailed_metadata')) {
        $youtube_metadata = customtube_extract_youtube_detailed_metadata($url, $video_id);
        
        // If we got metadata, merge it with the existing metadata
        if (!empty($youtube_metadata)) {
            foreach ($youtube_metadata as $key => $value) {
                if (!empty($value)) {
                    $metadata[$key] = $value;
                }
            }
            
            // Return the enhanced metadata
            return $metadata;
        }
    }
    
    // If the above function doesn't exist or returns empty data, fall back to the original method
    
    // Try to fetch metadata from YouTube API
    $api_key = get_option('customtube_youtube_api_key', '');
    
    if (!empty($api_key)) {
        $response = wp_remote_get("https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=snippet,contentDetails,statistics");
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!empty($data['items'][0])) {
                $item = $data['items'][0];
                
                // Parse duration (in ISO 8601 format)
                if (!empty($item['contentDetails']['duration'])) {
                    $duration_str = $item['contentDetails']['duration'];
                    try {
                        $interval = new DateInterval($duration_str);
                        $seconds = $interval->h * 3600 + $interval->i * 60 + $interval->s;
                        
                        $metadata['duration_seconds'] = $seconds;
                        $metadata['duration'] = customtube_format_duration($seconds);
                    } catch (Exception $e) {
                        // If duration parsing fails, set a reasonable default
                        $metadata['duration_seconds'] = 300;  // 5 minutes
                        $metadata['duration'] = '05:00';
                    }
                }
                
                // Set dimensions (YouTube doesn't provide exact dimensions in API)
                $metadata['width'] = 1280;  // Assume HD
                $metadata['height'] = 720;
                
                // Set title if available
                if (!empty($item['snippet']['title'])) {
                    $metadata['title'] = $item['snippet']['title'];
                }
                
                // Set description if available
                if (!empty($item['snippet']['description'])) {
                    $metadata['description'] = $item['snippet']['description'];
                }
                
                // Set thumbnail if available
                if (!empty($item['snippet']['thumbnails'])) {
                    if (!empty($item['snippet']['thumbnails']['maxres'])) {
                        $metadata['thumbnail_url'] = $item['snippet']['thumbnails']['maxres']['url'];
                    } elseif (!empty($item['snippet']['thumbnails']['high'])) {
                        $metadata['thumbnail_url'] = $item['snippet']['thumbnails']['high']['url'];
                    } elseif (!empty($item['snippet']['thumbnails']['medium'])) {
                        $metadata['thumbnail_url'] = $item['snippet']['thumbnails']['medium']['url'];
                    } elseif (!empty($item['snippet']['thumbnails']['default'])) {
                        $metadata['thumbnail_url'] = $item['snippet']['thumbnails']['default']['url'];
                    }
                }
                
                // Set source info
                $metadata['source'] = 'youtube';
                $metadata['source_url'] = "https://www.youtube.com/watch?v={$video_id}";
            }
        }
    } else {
        // Without API key, we can still get some metadata
        $metadata['source'] = 'youtube';
        $metadata['source_url'] = "https://www.youtube.com/watch?v={$video_id}";
        
        // Try to scrape metadata from the page
        $response = wp_remote_get("https://www.youtube.com/watch?v={$video_id}");
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $html = wp_remote_retrieve_body($response);
            
            // Try to extract title
            if (preg_match('/<meta property="og:title" content="(.*?)">/', $html, $title_matches)) {
                $metadata['title'] = html_entity_decode($title_matches[1], ENT_QUOTES);
            } elseif (preg_match('/<title>(.*?)<\/title>/', $html, $title_matches)) {
                // Remove " - YouTube" from the title
                $title = html_entity_decode($title_matches[1], ENT_QUOTES);
                $metadata['title'] = preg_replace('/ - YouTube$/', '', $title);
            }
            
            // Try to extract description
            if (preg_match('/<meta property="og:description" content="(.*?)">/', $html, $desc_matches)) {
                $metadata['description'] = html_entity_decode($desc_matches[1], ENT_QUOTES);
            }
            
            // Try to extract duration
            if (preg_match('/"length":\s*"(\d+:\d+)"/', $html, $duration_matches)) {
                $duration_str = $duration_matches[1];
                $parts = explode(':', $duration_str);
                if (count($parts) === 2) {
                    $seconds = (intval($parts[0]) * 60) + intval($parts[1]);
                    $metadata['duration_seconds'] = $seconds;
                    $metadata['duration'] = $duration_str;
                }
            }
        }
        
        // If we still don't have a duration, set a default
        if (empty($metadata['duration'])) {
            $metadata['duration_seconds'] = 300;  // Default to 5 minutes
            $metadata['duration'] = '05:00';
        }
        
        // Always try to get thumbnail using the YouTube thumbnail API
        // Try maxresdefault.jpg first (HD quality)
        $max_thumb_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
        $max_thumb_response = wp_remote_head($max_thumb_url);
        
        if (!is_wp_error($max_thumb_response) && wp_remote_retrieve_response_code($max_thumb_response) === 200) {
            $metadata['thumbnail_url'] = $max_thumb_url;
        } else {
            // Fallback to hqdefault.jpg if maxresdefault.jpg is not available
            $metadata['thumbnail_url'] = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
        }
    }
    
    return $metadata;
}

/**
 * Get metadata for Vimeo videos
 * 
 * @param string $url Vimeo URL or embed code
 * @param array $metadata Initial metadata array
 * @return array Updated metadata
 */
function customtube_get_vimeo_metadata($url, $metadata) {
    // Extract Vimeo video ID
    $video_id = '';
    if (preg_match('/vimeo\.com\/(?:.*\/)*([0-9]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    if (empty($video_id)) {
        return $metadata;
    }
    
    // Try to fetch metadata from Vimeo's oEmbed API (no key required)
    $response = wp_remote_get("https://vimeo.com/api/oembed.json?url=https://vimeo.com/{$video_id}");
    
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($data)) {
            // Set duration
            if (isset($data['duration'])) {
                $seconds = (int)$data['duration'];
                $metadata['duration_seconds'] = $seconds;
                $metadata['duration'] = customtube_format_duration($seconds);
            }
            
            // Set dimensions
            if (isset($data['width']) && isset($data['height'])) {
                $metadata['width'] = (int)$data['width'];
                $metadata['height'] = (int)$data['height'];
            }
            
            // Set source info
            $metadata['source'] = 'vimeo';
            $metadata['source_url'] = "https://vimeo.com/{$video_id}";
        }
    } else {
        // If oEmbed fails, still set basic info
        $metadata['source'] = 'vimeo';
        $metadata['source_url'] = "https://vimeo.com/{$video_id}";
    }
    
    return $metadata;
}

/**
 * Get metadata for PornHub videos
 * 
 * @param string $url PornHub URL or embed code
 * @param array $metadata Initial metadata array
 * @return array Updated metadata
 */
function customtube_get_pornhub_metadata($url, $metadata) {
    // Extract PornHub video key
    $video_key = '';
    if (preg_match('/viewkey=([^&"\']+)/', $url, $matches)) {
        $video_key = $matches[1];
    } elseif (preg_match('/pornhub\.com\/embed\/([a-zA-Z0-9]+)/', $url, $matches)) {
        $video_key = $matches[1];
    }
    
    if (empty($video_key)) {
        return $metadata;
    }
    
    // Set source info
    $metadata['source'] = 'pornhub';
    $metadata['source_url'] = "https://www.pornhub.com/view_video.php?viewkey={$video_key}";
    
    // For adult sites, we might not be able to directly fetch metadata via an API
    // We'd need to scrape the page, but that's complex and might violate ToS
    // This function stub can be expanded if PornHub provides an official API
    
    return $metadata;
}

/**
 * Get metadata for XVideos
 * 
 * @param string $url XVideos URL or embed code
 * @param array $metadata Initial metadata array
 * @return array Updated metadata
 */
function customtube_get_xvideos_metadata($url, $metadata) {
    // Set source info
    $metadata['source'] = 'xvideos';
    $metadata['source_url'] = $url;
    
    // Similar to PornHub, getting detailed metadata from XVideos 
    // would require page scraping, which might violate ToS
    
    return $metadata;
}

/**
 * Get metadata for XHamster
 * 
 * @param string $url XHamster URL or embed code
 * @param array $metadata Initial metadata array
 * @return array Updated metadata
 */
function customtube_get_xhamster_metadata($url, $metadata) {
    // Extract XHamster video ID
    $video_id = '';
    
    if (preg_match('/xhamster\.com\/videos\/([^\/&?"\']+)/', $url, $matches) || 
        preg_match('/xhamster\.com\/embed\/([^\/&?"\']+)/', $url, $matches) ||
        preg_match('/xhamster\.[a-z]+\/videos\/([^\/&?"\']+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    // Set source info
    $metadata['source'] = 'xhamster';
    $metadata['source_url'] = $url;
    
    // If we have a video ID, try to get thumbnail and metadata
    if (!empty($video_id)) {
        // Normalize URL for proper fetching
        $direct_url = "https://xhamster.com/videos/{$video_id}";
        $metadata['source_url'] = $direct_url;
        
        // Try to fetch OG data using our existing function
        if (function_exists('customtube_extract_opengraph_data')) {
            error_log("Attempting to extract OpenGraph data for XHamster video: {$video_id}");
            $og_data = customtube_extract_opengraph_data($direct_url);
            
            if ($og_data) {
                if (!empty($og_data['title'])) {
                    $metadata['title'] = $og_data['title'];
                }
                if (!empty($og_data['description'])) {
                    $metadata['description'] = $og_data['description'];
                }
                if (!empty($og_data['image'])) {
                    $metadata['thumbnail_url'] = $og_data['image'];
                }
                if (!empty($og_data['tags'])) {
                    $metadata['tags'] = $og_data['tags'];
                }
                if (!empty($og_data['categories'])) {
                    $metadata['categories'] = $og_data['categories'];
                }
                
                error_log("Successfully extracted metadata for XHamster video: {$video_id}");
            } else {
                error_log("Failed to extract OpenGraph data for XHamster video: {$video_id}");
            }
        }
    }
    
    return $metadata;
}

/**
 * Try to get the filesize of a remote file
 * 
 * @param string $url The remote file URL
 * @return int Filesize in bytes, 0 if unknown
 */
function customtube_get_remote_filesize($url) {
    $filesize = 0;
    
    $response = wp_remote_head($url);
    
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $headers = wp_remote_retrieve_headers($response);
        
        if (isset($headers['content-length'])) {
            $filesize = (int)$headers['content-length'];
        }
    }
    
    return $filesize;
}

/**
 * Format duration in seconds to HH:MM:SS format
 *
 * @param int $seconds Duration in seconds
 * @return string Formatted duration
 */
function customtube_format_duration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

/**
 * Format duration in seconds to a display-friendly format with unit indicator
 *
 * @param int $seconds Duration in seconds
 * @param bool $compact Whether to use compact format (5m vs 5 mins)
 * @return string Formatted duration with unit
 */
function customtube_format_duration_display($seconds) {
    // Handle invalid input
    if (empty($seconds) || !is_numeric($seconds)) {
        return '?m'; // Unknown duration indicator
    }

    // Ensure we have a positive integer
    $seconds = max(0, intval($seconds));

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        if ($minutes > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } else {
            return sprintf('%dh', $hours);
        }
    } else if ($minutes > 0) {
        return sprintf('%dm', $minutes);
    } else {
        // For durations less than 1 minute:
        // If we already have a numeric value as input (like "10"),
        // display it as minutes (e.g., "10m") rather than converting to seconds
        if (is_numeric($seconds) && $seconds > 0 && $seconds <= 59) {
            // Check if this might be a minute value that was passed directly
            return sprintf('%dm', $seconds);
        } else {
            // Default case - minutes are always minimum unit
            return '1m';
        }
    }
}

/**
 * Get formatted duration for display from post ID
 *
 * @param int $post_id Post ID
 * @return string Formatted duration with unit
 */
function customtube_get_formatted_duration($post_id) {
    // First try to get raw seconds
    $duration_seconds = get_post_meta($post_id, 'duration_seconds', true);

    if (!empty($duration_seconds)) {
        return customtube_format_duration_display($duration_seconds);
    }

    // Fall back to parsing the formatted duration
    $duration = get_post_meta($post_id, 'video_duration', true);

    if (!empty($duration)) {
        $seconds = customtube_duration_to_seconds($duration);
        return customtube_format_duration_display($seconds);
    }

    return '';
}

// Note: customtube_duration_to_seconds() function is now defined in functions.php
// to avoid duplicate function declaration errors

/**
 * Auto-detect video metadata on save
 */
function customtube_auto_detect_video_metadata($post_id, $post) {
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
    
    // Get existing duration to check if we need to update
    $existing_duration = get_post_meta($post_id, 'video_duration', true);
    
    // If we already have a duration and it wasn't manually set, skip metadata fetch
    if (!empty($existing_duration) && !isset($_POST['video_duration'])) {
        return;
    }
    
    // Fetch metadata
    $metadata = customtube_fetch_video_metadata($video_source, $source_type);
    
    // Only update if we got valid metadata
    if (!is_wp_error($metadata)) {
        // Only update duration if it's not already set manually
        if (empty($existing_duration) || isset($_POST['video_duration'])) {
            update_post_meta($post_id, 'video_duration', $metadata['duration']);
            update_post_meta($post_id, 'duration_seconds', $metadata['duration_seconds']);
        }
        
        // Update other metadata
        update_post_meta($post_id, 'video_width', $metadata['width']);
        update_post_meta($post_id, 'video_height', $metadata['height']);
        update_post_meta($post_id, 'video_filesize', $metadata['filesize']);
        update_post_meta($post_id, 'video_source', $metadata['source']);
        update_post_meta($post_id, 'video_source_url', $metadata['source_url']);
        update_post_meta($post_id, 'metadata_last_fetched', $metadata['metadata_last_fetched']);
        
        // Auto-set title if it's empty and we have a title
        if (empty(get_the_title($post_id)) && !empty($metadata['title'])) {
            $post_update = array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($metadata['title'])
            );
            wp_update_post($post_update);
        }
        
        // Auto-set the description if it's empty and we have one
        if (empty(get_the_content(null, false, $post_id)) && !empty($metadata['description'])) {
            $post_update = array(
                'ID' => $post_id,
                'post_content' => wp_kses_post($metadata['description'])
            );
            wp_update_post($post_update);
        }
        
        // Auto-set the featured image if not already set and we have a thumbnail URL
        if (!has_post_thumbnail($post_id) && !empty($metadata['thumbnail_url'])) {
            customtube_set_featured_image_from_url($post_id, $metadata['thumbnail_url']);
        }
    }
}
add_action('save_post', 'customtube_auto_detect_video_metadata', 10, 2);

/**
 * Add "Fetch Metadata" button to video edit screen
 */
function customtube_add_fetch_metadata_button() {
    global $post;
    
    // Only show on video post type edit screen
    if (!$post || get_post_type($post) !== 'video') {
        return;
    }
    
    ?>
    <div class="misc-pub-section">
        <button type="button" id="fetch-video-metadata" class="button">
            <?php esc_html_e('Fetch Video Metadata', 'customtube'); ?>
        </button>
        <span class="spinner" id="metadata-spinner"></span>
        <div id="metadata-result"></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#fetch-video-metadata').on('click', function() {
            var button = $(this);
            var spinner = $('#metadata-spinner');
            var result = $('#metadata-result');
            var sourceType = $('input[name="video_source_type"]:checked').val();
            var videoSource = sourceType === 'direct' ? $('#video_url').val() : $('#embed_code').val();
            
            if (!videoSource) {
                result.html('<p class="error"><?php esc_html_e('Please enter a video URL or embed code first.', 'customtube'); ?></p>');
                return;
            }
            
            button.prop('disabled', true);
            spinner.addClass('is-active');
            result.empty();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_fetch_video_metadata',
                    post_id: <?php echo $post->ID; ?>,
                    source_type: sourceType,
                    video_source: videoSource,
                    nonce: '<?php echo wp_create_nonce('customtube_fetch_metadata'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Update form fields with new metadata
                        if (response.data.duration) {
                            $('#video_duration').val(response.data.duration);
                        }
                        result.html('<p class="success"><?php esc_html_e('Metadata fetched successfully!', 'customtube'); ?></p>');
                    } else {
                        result.html('<p class="error">' + response.data.message + '</p>');
                    }
                },
                error: function() {
                    result.html('<p class="error"><?php esc_html_e('Error fetching metadata. Please try again.', 'customtube'); ?></p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
    });
    </script>
    
    <style>
    #metadata-spinner {
        float: none;
        margin-top: 0;
    }
    #metadata-result {
        margin-top: 5px;
    }
    #metadata-result .success {
        color: green;
    }
    #metadata-result .error {
        color: red;
    }
    </style>
    <?php
}
add_action('post_submitbox_misc_actions', 'customtube_add_fetch_metadata_button');

/**
 * AJAX handler for fetching video metadata
 */
function customtube_ajax_fetch_video_metadata() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_fetch_metadata')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'customtube')));
    }
    
    // Check if we have the required data
    if (!isset($_POST['post_id']) || !isset($_POST['video_source']) || !isset($_POST['source_type'])) {
        wp_send_json_error(array('message' => __('Missing required data.', 'customtube')));
    }
    
    $post_id = intval($_POST['post_id']);
    $video_source = $_POST['video_source'];
    $source_type = sanitize_text_field($_POST['source_type']);
    
    // Verify user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(array('message' => __('You do not have permission to edit this post.', 'customtube')));
    }
    
    // Fetch metadata
    $metadata = customtube_fetch_video_metadata($video_source, $source_type);
    
    if (is_wp_error($metadata)) {
        wp_send_json_error(array('message' => $metadata->get_error_message()));
    }
    
    // Update post meta
    update_post_meta($post_id, 'video_duration', $metadata['duration']);
    update_post_meta($post_id, 'duration_seconds', $metadata['duration_seconds']);
    update_post_meta($post_id, 'video_width', $metadata['width']);
    update_post_meta($post_id, 'video_height', $metadata['height']);
    update_post_meta($post_id, 'video_filesize', $metadata['filesize']);
    update_post_meta($post_id, 'video_source', $metadata['source']);
    update_post_meta($post_id, 'video_source_url', $metadata['source_url']);
    update_post_meta($post_id, 'metadata_last_fetched', $metadata['metadata_last_fetched']);
    
    // Auto-set title if it's empty and we have a title
    if (empty(get_the_title($post_id)) && !empty($metadata['title'])) {
        $post_update = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field($metadata['title'])
        );
        wp_update_post($post_update);
        
        // Add post title to the response data
        $metadata['post_title_updated'] = true;
    }
    
    // Auto-set the description if it's empty and we have one
    if (empty(get_the_content(null, false, $post_id)) && !empty($metadata['description'])) {
        $post_update = array(
            'ID' => $post_id,
            'post_content' => wp_kses_post($metadata['description'])
        );
        wp_update_post($post_update);
        
        // Add description update info to the response data
        $metadata['post_content_updated'] = true;
    }
    
    // Auto-set the featured image if not already set and we have a thumbnail URL
    if (!has_post_thumbnail($post_id) && !empty($metadata['thumbnail_url'])) {
        $thumbnail_set = customtube_set_featured_image_from_url($post_id, $metadata['thumbnail_url']);
        
        // Add thumbnail update info to the response data
        $metadata['thumbnail_set'] = $thumbnail_set;
    }
    
    // Return success with metadata
    wp_send_json_success($metadata);
}
add_action('wp_ajax_customtube_fetch_video_metadata', 'customtube_ajax_fetch_video_metadata');

/**
 * Add settings for video metadata APIs (YouTube, etc.)
 */
function customtube_add_metadata_settings() {
    add_settings_section(
        'customtube_metadata_settings',
        __('Video Metadata Settings', 'customtube'),
        'customtube_metadata_settings_callback',
        'media'
    );
    
    add_settings_field(
        'customtube_youtube_api_key',
        __('YouTube API Key', 'customtube'),
        'customtube_youtube_api_key_callback',
        'media',
        'customtube_metadata_settings'
    );
    
    register_setting('media', 'customtube_youtube_api_key', 'sanitize_text_field');
}
add_action('admin_init', 'customtube_add_metadata_settings');

/**
 * Settings section callback
 */
function customtube_metadata_settings_callback() {
    echo '<p>' . __('API keys for fetching video metadata from various services.', 'customtube') . '</p>';
}

/**
 * YouTube API key field callback
 */
function customtube_youtube_api_key_callback() {
    $api_key = get_option('customtube_youtube_api_key', '');
    echo '<input type="text" name="customtube_youtube_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
    echo '<p class="description">' . __('Enter your YouTube Data API key for fetching video metadata.', 'customtube') . ' <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">' . __('Get API Key', 'customtube') . '</a></p>';
}