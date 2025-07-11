<?php
/**
 * YouTube Metadata Extraction Functions
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Extract detailed metadata from YouTube videos
 * 
 * @param string $url YouTube URL
 * @param string $video_id YouTube video ID
 * @return array Metadata array
 */
function customtube_extract_youtube_detailed_metadata($url, $video_id = '') {
    // Extract video ID if not provided
    if (empty($video_id)) {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            $video_id = $matches[1];
        } else {
            return array();
        }
    }
    
    // Set up default metadata
    $metadata = array(
        'title' => '',
        'description' => '',
        'duration' => '',
        'duration_seconds' => 0,
        'width' => 1280,  // Default HD width
        'height' => 720,  // Default HD height
        'thumbnail_url' => '',
        'source' => 'youtube',
        'source_url' => "https://www.youtube.com/watch?v={$video_id}",
    );
    
    // Try to get API key from settings
    $api_key = get_option('customtube_youtube_api_key', '');
    
    // If we have an API key, use the YouTube API
    if (!empty($api_key)) {
        $api_url = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=snippet,contentDetails,statistics";
        $response = wp_remote_get($api_url);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!empty($data['items'][0])) {
                $item = $data['items'][0];
                
                // Extract title
                if (!empty($item['snippet']['title'])) {
                    $metadata['title'] = $item['snippet']['title'];
                }
                
                // Extract description
                if (!empty($item['snippet']['description'])) {
                    $metadata['description'] = $item['snippet']['description'];
                }
                
                // Parse duration (in ISO 8601 format)
                if (!empty($item['contentDetails']['duration'])) {
                    $duration_str = $item['contentDetails']['duration'];
                    
                    try {
                        $interval = new DateInterval($duration_str);
                        $seconds = $interval->h * 3600 + $interval->i * 60 + $interval->s;
                        
                        $metadata['duration_seconds'] = $seconds;
                        $metadata['duration'] = customtube_format_duration($seconds);
                    } catch (Exception $e) {
                        // Handle duration parsing error
                        error_log('Error parsing YouTube duration: ' . $e->getMessage());
                    }
                }
                
                // Extract thumbnail URLs
                if (!empty($item['snippet']['thumbnails'])) {
                    $thumbnails = $item['snippet']['thumbnails'];
                    
                    if (!empty($thumbnails['maxres'])) {
                        $metadata['thumbnail_url'] = $thumbnails['maxres']['url'];
                    } elseif (!empty($thumbnails['high'])) {
                        $metadata['thumbnail_url'] = $thumbnails['high']['url'];
                    } elseif (!empty($thumbnails['medium'])) {
                        $metadata['thumbnail_url'] = $thumbnails['medium']['url'];
                    } elseif (!empty($thumbnails['default'])) {
                        $metadata['thumbnail_url'] = $thumbnails['default']['url'];
                    }
                }
                
                // Extract view count
                if (!empty($item['statistics']['viewCount'])) {
                    $metadata['view_count'] = intval($item['statistics']['viewCount']);
                }
                
                // Extract like count
                if (!empty($item['statistics']['likeCount'])) {
                    $metadata['like_count'] = intval($item['statistics']['likeCount']);
                }
                
                // Extract upload date
                if (!empty($item['snippet']['publishedAt'])) {
                    $metadata['upload_date'] = $item['snippet']['publishedAt'];
                }
                
                // Extract tags as categories
                if (!empty($item['snippet']['tags']) && is_array($item['snippet']['tags'])) {
                    $metadata['tags'] = $item['snippet']['tags'];
                }
                
                // Extract channel information
                if (!empty($item['snippet']['channelTitle'])) {
                    $metadata['channel_title'] = $item['snippet']['channelTitle'];
                }
                if (!empty($item['snippet']['channelId'])) {
                    $metadata['channel_id'] = $item['snippet']['channelId'];
                }
            }
        }
    } else {
        // No API key, use alternative methods
        
        // Try to extract info by requesting the YouTube page
        $response = wp_remote_get("https://www.youtube.com/watch?v={$video_id}");
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $html = wp_remote_retrieve_body($response);
            
            // Extract title
            if (preg_match('/<meta property="og:title" content="(.*?)">/', $html, $matches)) {
                $metadata['title'] = html_entity_decode($matches[1], ENT_QUOTES);
            } elseif (preg_match('/<title>(.*?)<\/title>/', $html, $matches)) {
                // Fallback to title tag (removes " - YouTube" suffix)
                $title = html_entity_decode($matches[1], ENT_QUOTES);
                $metadata['title'] = preg_replace('/ - YouTube$/', '', $title);
            }
            
            // Extract description
            if (preg_match('/<meta property="og:description" content="(.*?)">/', $html, $matches)) {
                $metadata['description'] = html_entity_decode($matches[1], ENT_QUOTES);
            }
            
            // Try to extract length from schema.org data
            if (preg_match('/"length":\s*"(\d+:\d+)"/', $html, $matches)) {
                $metadata['duration'] = $matches[1];
                
                // Convert MM:SS to seconds
                $parts = explode(':', $matches[1]);
                $seconds = 0;
                if (count($parts) === 2) { // MM:SS
                    $seconds = (intval($parts[0]) * 60) + intval($parts[1]);
                } elseif (count($parts) === 3) { // HH:MM:SS
                    $seconds = (intval($parts[0]) * 3600) + (intval($parts[1]) * 60) + intval($parts[2]);
                }
                
                $metadata['duration_seconds'] = $seconds;
            }
        }
        
        // Always set the thumbnail using the video ID (doesn't require API)
        // First try maxresdefault (HD)
        $max_thumb_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
        $max_thumb_response = wp_remote_head($max_thumb_url);
        
        if (!is_wp_error($max_thumb_response) && wp_remote_retrieve_response_code($max_thumb_response) === 200) {
            $metadata['thumbnail_url'] = $max_thumb_url;
        } else {
            // Fallback to hqdefault if maxresdefault doesn't exist
            $metadata['thumbnail_url'] = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
        }
    }
    
    // Ensure we have a valid duration format if we have duration_seconds
    if (!empty($metadata['duration_seconds']) && empty($metadata['duration'])) {
        $metadata['duration'] = customtube_format_duration($metadata['duration_seconds']);
    }
    
    // Only set default duration as absolute last resort
    // This should only happen if we have no duration data AND couldn't fetch it from the YouTube API
    if (empty($metadata['duration']) && empty($metadata['duration_seconds'])) {
        // Check if we have a video ID and can fetch a more accurate duration
        if (!empty($video_id)) {
            // Try one more time with a different YouTube page scraping approach
            $watch_url = "https://www.youtube.com/watch?v={$video_id}";
            $response = wp_remote_get($watch_url);
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $html = wp_remote_retrieve_body($response);
                
                // Look for duration in various formats (with much broader patterns to catch all possible locations)
                error_log("Searching for duration in YouTube page HTML for video ID: $video_id");
                
                // Save the first 2000 characters of HTML to the log for debugging
                error_log("First 2000 chars of YouTube page: " . substr($html, 0, 2000));
                
                // Try multiple patterns for extracting duration
                $duration_patterns = [
                    ['pattern' => '/"lengthSeconds":\s*"?(\d+)"?/', 'name' => 'lengthSeconds'],
                    ['pattern' => '/"length_seconds":\s*"?(\d+)"?/', 'name' => 'length_seconds'],
                    ['pattern' => '/"approxDurationMs":\s*"?(\d+)"?/', 'name' => 'approxDurationMs', 'divider' => 1000],
                    ['pattern' => '/"duration":\s*"?(\d+)"?/', 'name' => 'duration'],
                    ['pattern' => '/"videoDuration":\s*"?(\d+)"?/', 'name' => 'videoDuration'],
                    ['pattern' => '/"videoDuration":\s*"PT(\d+)M(\d+)S"/', 'name' => 'ISO duration'],
                    ['pattern' => '/"duration":\s*"PT(\d+)M(\d+)S"/', 'name' => 'ISO duration'],
                    ['pattern' => '/ytInitialPlayerResponse.*?"lengthSeconds":"(\d+)"/', 'name' => 'ytInitialPlayerResponse'],
                    ['pattern' => '/videoDetails.*?"lengthSeconds":"(\d+)"/', 'name' => 'videoDetails']
                ];
                
                foreach ($duration_patterns as $pattern_data) {
                    if (isset($pattern_data['pattern'])) {
                        $pattern = $pattern_data['pattern'];
                        $name = $pattern_data['name'];
                        
                        if (preg_match($pattern, $html, $matches)) {
                            if ($name === 'ISO duration' && isset($matches[1]) && isset($matches[2])) {
                                // This is for ISO 8601 duration format like PT3M45S (3 min 45 sec)
                                $seconds = (intval($matches[1]) * 60) + intval($matches[2]);
                                $metadata['duration_seconds'] = $seconds;
                                $metadata['duration'] = customtube_format_duration($seconds);
                                error_log("Extracted YouTube duration from $name: {$seconds}s for video ID {$video_id}");
                                break;
                            } elseif (isset($matches[1])) {
                                $seconds = intval($matches[1]);
                                if (isset($pattern_data['divider'])) {
                                    $seconds = round($seconds / $pattern_data['divider']);
                                }
                                $metadata['duration_seconds'] = $seconds;
                                $metadata['duration'] = customtube_format_duration($seconds);
                                error_log("Extracted YouTube duration from $name: {$seconds}s for video ID {$video_id}");
                                break;
                            }
                        }
                    }
                }
                
                // If we still don't have duration, try to find it in the watch page directly
                if (empty($metadata['duration_seconds'])) {
                    // Look for the duration in page metadata
                    if (preg_match('/<meta itemprop="duration" content="PT(\d+)M(\d+)S">/', $html, $meta_matches)) {
                        $minutes = intval($meta_matches[1]);
                        $seconds = intval($meta_matches[2]);
                        $total_seconds = ($minutes * 60) + $seconds;
                        $metadata['duration_seconds'] = $total_seconds;
                        $metadata['duration'] = customtube_format_duration($total_seconds);
                        error_log("Extracted YouTube duration from meta tag: {$total_seconds}s for video ID {$video_id}");
                    }
                    // Try another approach looking for the timestamp in the UI
                    elseif (preg_match('/playabilityStatus.*?"text":\s*"(\d+):(\d+)"/', $html, $ui_matches)) {
                        $minutes = intval($ui_matches[1]);
                        $seconds = intval($ui_matches[2]);
                        $total_seconds = ($minutes * 60) + $seconds;
                        $metadata['duration_seconds'] = $total_seconds;
                        $metadata['duration'] = customtube_format_duration($total_seconds);
                        error_log("Extracted YouTube duration from UI timestamp: {$total_seconds}s for video ID {$video_id}");
                    }
                }
            }
        }
        
        // If still no duration after all attempts, use a placeholder but log it
        if (empty($metadata['duration']) && empty($metadata['duration_seconds'])) {
            error_log("WARNING: Could not determine duration for YouTube video - using default 5 minutes");
            $metadata['duration_seconds'] = 300;
            $metadata['duration'] = '05:00';
        }
    }
    
    return $metadata;
}

// Format duration helper function if not already defined
if (!function_exists('customtube_format_duration')) {
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
}