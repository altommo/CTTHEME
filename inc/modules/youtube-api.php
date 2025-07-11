<?php
/**
 * YouTube API Module
 * Handles YouTube-specific API interactions and metadata extraction
 *
 * @package CustomTube
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Extract metadata from YouTube videos using different methods
 * 
 * @param string $url YouTube URL
 * @param string $video_id YouTube video ID (optional)
 * @param bool $get_title Whether to specifically focus on getting the title
 * @return array Metadata array
 */
if (!function_exists('customtube_extract_youtube_metadata')) {
    function customtube_extract_youtube_metadata($url, $video_id = '', $get_title = false) {
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
            'tags' => array(),          // Initialize tags array
            'categories' => array(),    // Initialize categories array
            'performers' => array(),    // Initialize performers array
        );
        
        // Try to get API key from settings
        $api_key = get_option('customtube_youtube_api_key', '');
        
        // If we have an API key, use the YouTube API
        if (!empty($api_key)) {
            $metadata = customtube_extract_youtube_data_via_api($video_id, $api_key, $metadata);
        }
        
        // If no title or duration via API, try oEmbed
        if (empty($metadata['title']) || empty($metadata['duration'])) {
            $metadata = customtube_extract_youtube_data_via_oembed($video_id, $metadata);
        }
        
        // Try page scraping regardless - we want to get categories and tags even if we already have title
        $metadata = customtube_extract_youtube_data_via_scraping($video_id, $metadata, $get_title);
        
        // Always set the thumbnail using the video ID (doesn't require API)
        $metadata = customtube_set_youtube_thumbnails($video_id, $metadata);
        
        // Ensure we have a valid duration format if we have duration_seconds
        if (!empty($metadata['duration_seconds']) && empty($metadata['duration'])) {
            $metadata['duration'] = customtube_format_duration($metadata['duration_seconds']);
        }
        
        // Only set default duration as absolute last resort
        if (empty($metadata['duration']) && empty($metadata['duration_seconds'])) {
            $metadata['duration_seconds'] = 180; // 3 minutes default duration
            $metadata['duration'] = '03:00';
            error_log("WARNING: Using default 3-min duration for YouTube video ID: $video_id");
        }
        
        // Process tags and categories for WordPress taxonomy
        if (!empty($metadata['tags']) && is_array($metadata['tags'])) {
            // Filter out very short tags (likely not useful)
            $metadata['tags'] = array_filter($metadata['tags'], function($tag) {
                return strlen($tag) > 2;
            });
            
            // Limit to 10 tags maximum to avoid tag spam
            if (count($metadata['tags']) > 10) {
                $metadata['tags'] = array_slice($metadata['tags'], 0, 10);
            }
            
            error_log("Final YouTube tags: " . implode(', ', $metadata['tags']));
        }
        
        // Process categories - make sure they're properly formatted
        if (!empty($metadata['categories']) && is_array($metadata['categories'])) {
            // Filter out empty categories
            $metadata['categories'] = array_filter($metadata['categories']);
            error_log("Final YouTube categories: " . implode(', ', $metadata['categories']));
        }
        
        // Process performers - typically channel name for YouTube
        if (!empty($metadata['performers']) && is_array($metadata['performers'])) {
            // Filter out empty performers
            $metadata['performers'] = array_filter($metadata['performers']);
            error_log("Final YouTube performers: " . implode(', ', $metadata['performers']));
        }
        
        return $metadata;
    }
}

/**
 * Extract YouTube metadata via official API
 *
 * @param string $video_id YouTube video ID
 * @param string $api_key YouTube API key
 * @param array $metadata Existing metadata to append to
 * @return array Updated metadata
 */
if (!function_exists('customtube_extract_youtube_data_via_api')) {
    function customtube_extract_youtube_data_via_api($video_id, $api_key, $metadata) {
        $api_url = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=snippet,contentDetails,statistics";
        $response = wp_remote_get($api_url);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!empty($data['items'][0])) {
                $item = $data['items'][0];
                
                // Extract title
                if (!empty($item['snippet']['title'])) {
                    $metadata['title'] = $item['snippet']['title'];
                    error_log("API: Got YouTube title: {$metadata['title']}");
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
                        error_log("API: Got YouTube duration: {$metadata['duration']} ({$seconds}s)");
                    } catch (Exception $e) {
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
        
        return $metadata;
    }
}

/**
 * Extract YouTube metadata via oEmbed
 *
 * @param string $video_id YouTube video ID
 * @param array $metadata Existing metadata to append to
 * @return array Updated metadata
 */
if (!function_exists('customtube_extract_youtube_data_via_oembed')) {
    function customtube_extract_youtube_data_via_oembed($video_id, $metadata) {
        // Try oEmbed API
        $oembed_url = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$video_id}&format=json";
        $response = wp_remote_get($oembed_url);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!empty($data['title'])) {
                $metadata['title'] = $data['title'];
                error_log("oEmbed: Got YouTube title: {$metadata['title']}");
            }
            
            if (!empty($data['author_name'])) {
                $metadata['channel_title'] = $data['author_name'];
            }
            
            if (!empty($data['thumbnail_url'])) {
                // The oEmbed thumbnail is usually small, only use if we don't have one
                if (empty($metadata['thumbnail_url'])) {
                    $metadata['thumbnail_url'] = $data['thumbnail_url'];
                }
            }
        }
        
        return $metadata;
    }
}

/**
 * Extract YouTube metadata via page scraping
 *
 * @param string $video_id YouTube video ID
 * @param array $metadata Existing metadata to append to
 * @param bool $get_title Whether to focus on getting title
 * @return array Updated metadata
 */
if (!function_exists('customtube_extract_youtube_data_via_scraping')) {
    function customtube_extract_youtube_data_via_scraping($video_id, $metadata, $get_title = false) {
        // Try to extract info by requesting the YouTube page
        $response = wp_remote_get("https://www.youtube.com/watch?v={$video_id}", array(
            'timeout' => 15,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ));
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $html = wp_remote_retrieve_body($response);
            
            // Extract title if needed
            if (empty($metadata['title']) || $get_title) {
                // Title extraction patterns
                $title_patterns = array(
                    '/<title>(.*?) - YouTube<\/title>/' => 'title tag',
                    '/<meta property="og:title" content="(.*?)"/i' => 'og:title',
                    '/<meta name="title" content="(.*?)"/i' => 'meta title',
                    '/videoDetails.*?"title":"([^"]+)"/s' => 'videoDetails title',
                    '/ytInitialPlayerResponse.*?"title":"([^"]+)"/s' => 'ytInitialPlayerResponse title',
                    '/"videoDetails".*?"title":"([^"]+)"/s' => 'videoDetails title full',
                    '/"title":"([^"]{5,100})"/s' => 'JSON title field', 
                    '/"title":\s*"(.*?)(?<!\\\\)",/s' => 'JSON title alt',
                    '/\\"title\\":\\"([^\\"]+)\\"/s' => 'escaped JSON title',
                    '/"watchHeadline":"([^"]+)"/s' => 'watchHeadline',
                    '/<h1[^>]*>([^<]+)<\/h1>/s' => 'h1 tag',
                    // Extra patterns for different YouTube formats
                    '/name="twitter:title" content="([^"]+)"/' => 'twitter title',
                    '/property="twitter:title" content="([^"]+)"/' => 'twitter property title',
                    '/<span[^>]*id="eow-title"[^>]*>([^<]+)<\/span>/' => 'eow-title span',
                    '/<meta[^>]*itemprop="name"[^>]*content="([^"]+)"/' => 'itemprop name',
                    '/title":"([^"]{5,200})","description":"/' => 'structured title field' 
                );
                
                foreach ($title_patterns as $pattern => $name) {
                    if (preg_match($pattern, $html, $matches)) {
                        $title = trim(html_entity_decode($matches[1], ENT_QUOTES));
                        $metadata['title'] = $title;
                        error_log("Scraping: Got YouTube title via $name: $title");
                        break;
                    }
                }
                
                // Try to extract structured data from the JSON blocks in the page
                if (empty($metadata['title']) || $get_title) {
                    // Attempt to extract ytInitialData JSON block
                    if (preg_match('/var ytInitialData = ({.+?});/s', $html, $json_matches) || 
                        preg_match('/window\["ytInitialData"\] = ({.+?});/s', $html, $json_matches)) {
                        
                        $json_data = json_decode($json_matches[1], true);
                        if (!empty($json_data)) {
                            // Extract title from various JSON paths
                            if (empty($metadata['title']) && isset($json_data['contents']['twoColumnWatchNextResults']['results']['results']['contents'][0]['videoPrimaryInfoRenderer']['title']['runs'][0]['text'])) {
                                $metadata['title'] = $json_data['contents']['twoColumnWatchNextResults']['results']['results']['contents'][0]['videoPrimaryInfoRenderer']['title']['runs'][0]['text'];
                                error_log("Scraping: Got YouTube title via JSON path: {$metadata['title']}");
                            }
                        }
                    }
                    
                    // Try other JSON blocks as well
                    if (empty($metadata['title']) && preg_match('/var ytInitialPlayerResponse = ({.+?});/s', $html, $player_json_matches)) {
                        $player_json = json_decode($player_json_matches[1], true);
                        if (!empty($player_json) && isset($player_json['videoDetails']['title'])) {
                            $metadata['title'] = $player_json['videoDetails']['title'];
                            error_log("Scraping: Got YouTube title via player JSON: {$metadata['title']}");
                        }
                    }
                }
            }
            
            // Extract description if needed
            if (empty($metadata['description'])) {
                if (preg_match('/<meta property="og:description" content="(.*?)">/', $html, $matches)) {
                    $metadata['description'] = html_entity_decode($matches[1], ENT_QUOTES);
                }
                // Try JSON structured data for description too
                elseif (preg_match('/var ytInitialPlayerResponse = ({.+?});/s', $html, $player_json_matches)) {
                    $player_json = json_decode($player_json_matches[1], true);
                    if (!empty($player_json) && isset($player_json['videoDetails']['shortDescription'])) {
                        $metadata['description'] = $player_json['videoDetails']['shortDescription'];
                    }
                }
            }
            
            // Extract categories and keywords/tags
            if (empty($metadata['tags']) || empty($metadata['categories'])) {
                // Try to extract keywords from meta tags
                if (preg_match('/<meta name="keywords" content="([^"]+)"/', $html, $keyword_matches)) {
                    $keywords = explode(',', $keyword_matches[1]);
                    $metadata['tags'] = array_map('trim', $keywords);
                    error_log("Scraping: Got YouTube keywords via meta tag: " . implode(', ', $metadata['tags']));
                }
                
                // Try to extract from JSON data
                if (preg_match('/var ytInitialPlayerResponse = ({.+?});/s', $html, $player_json_matches)) {
                    $player_json = json_decode($player_json_matches[1], true);
                    
                    // Extract keywords
                    if (!empty($player_json) && isset($player_json['videoDetails']['keywords']) && is_array($player_json['videoDetails']['keywords'])) {
                        $metadata['tags'] = $player_json['videoDetails']['keywords'];
                        error_log("Scraping: Got YouTube tags via player JSON: " . implode(', ', $metadata['tags']));
                    }
                    
                    // Extract category
                    if (!empty($player_json) && isset($player_json['microformat']['playerMicroformatRenderer']['category'])) {
                        $metadata['categories'] = array($player_json['microformat']['playerMicroformatRenderer']['category']);
                        error_log("Scraping: Got YouTube category: " . $metadata['categories'][0]);
                    }
                }
                
                // Try to extract from ytInitialData JSON block
                if (preg_match('/var ytInitialData = ({.+?});/s', $html, $json_matches) || 
                    preg_match('/window\["ytInitialData"\] = ({.+?});/s', $html, $json_matches)) {
                    
                    $json_data = json_decode($json_matches[1], true);
                    if (!empty($json_data)) {
                        // Look for videoSecondaryInfoRenderer which often contains category info
                        if (empty($metadata['categories']) && isset($json_data['contents']['twoColumnWatchNextResults']['results']['results']['contents'])) {
                            foreach ($json_data['contents']['twoColumnWatchNextResults']['results']['results']['contents'] as $content) {
                                if (isset($content['videoSecondaryInfoRenderer']['metadataRowContainer']['metadataRowContainerRenderer']['rows'])) {
                                    $rows = $content['videoSecondaryInfoRenderer']['metadataRowContainer']['metadataRowContainerRenderer']['rows'];
                                    foreach ($rows as $row) {
                                        if (isset($row['metadataRowRenderer']['title']['simpleText']) && 
                                            $row['metadataRowRenderer']['title']['simpleText'] == 'Category') {
                                            if (isset($row['metadataRowRenderer']['contents'][0]['runs'][0]['text'])) {
                                                $metadata['categories'] = array($row['metadataRowRenderer']['contents'][0]['runs'][0]['text']);
                                                error_log("Scraping: Got YouTube category from metadataRow: " . $metadata['categories'][0]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Extract channel (creator) info
            if (empty($metadata['channel_title']) || empty($metadata['channel_id'])) {
                // Try meta tags first
                if (preg_match('/<link itemprop="name" content="([^"]+)"/', $html, $channel_matches)) {
                    $metadata['channel_title'] = $channel_matches[1];
                    error_log("Scraping: Got YouTube channel name via meta tag: " . $metadata['channel_title']);
                }
                
                if (preg_match('/<meta itemprop="channelId" content="([^"]+)"/', $html, $channel_id_matches)) {
                    $metadata['channel_id'] = $channel_id_matches[1];
                }
                
                // Try JSON data for channel info
                if (preg_match('/var ytInitialPlayerResponse = ({.+?});/s', $html, $player_json_matches)) {
                    $player_json = json_decode($player_json_matches[1], true);
                    
                    if (!empty($player_json)) {
                        // Channel title
                        if (empty($metadata['channel_title']) && isset($player_json['videoDetails']['author'])) {
                            $metadata['channel_title'] = $player_json['videoDetails']['author'];
                            error_log("Scraping: Got YouTube channel name via player JSON: " . $metadata['channel_title']);
                        }
                        
                        // Channel ID
                        if (empty($metadata['channel_id']) && isset($player_json['videoDetails']['channelId'])) {
                            $metadata['channel_id'] = $player_json['videoDetails']['channelId'];
                        }
                    }
                }
                
                // Treat channel as a performer for adult sites integration
                if (!empty($metadata['channel_title']) && empty($metadata['performers'])) {
                    $metadata['performers'] = array($metadata['channel_title']);
                    error_log("Setting channel as performer: " . $metadata['channel_title']);
                }
            }
            
            // Extract duration if needed
            if (empty($metadata['duration']) || empty($metadata['duration_seconds'])) {
                // Duration patterns
                $duration_patterns = array(
                    '/"lengthSeconds":\s*"?(\d+)"?/' => 'lengthSeconds',
                    '/"length_seconds":\s*"?(\d+)"?/' => 'length_seconds',
                    '/"approxDurationMs":\s*"?(\d+)"?/' => 'approxDurationMs',
                    '/"duration":\s*"?(\d+)"?/' => 'duration',
                    '/"videoDuration":\s*"?(\d+)"?/' => 'videoDuration',
                    '/"videoDuration":\s*"PT(\d+)M(\d+)S"/' => 'ISO duration',
                    '/"duration":\s*"PT(\d+)M(\d+)S"/' => 'ISO duration',
                    '/ytInitialPlayerResponse.*?"lengthSeconds":"(\d+)"/' => 'ytInitialPlayerResponse',
                    '/videoDetails.*?"lengthSeconds":"(\d+)"/' => 'videoDetails'
                );
                
                foreach ($duration_patterns as $pattern => $name) {
                    if (preg_match($pattern, $html, $matches)) {
                        if ($name === 'ISO duration' && isset($matches[1]) && isset($matches[2])) {
                            // This is for ISO 8601 duration format like PT3M45S (3 min 45 sec)
                            $seconds = (intval($matches[1]) * 60) + intval($matches[2]);
                            $metadata['duration_seconds'] = $seconds;
                            $metadata['duration'] = customtube_format_duration($seconds);
                            error_log("Scraping: Got YouTube duration from $name: {$seconds}s");
                            break;
                        } elseif (isset($matches[1])) {
                            $seconds = intval($matches[1]);
                            if ($name === 'approxDurationMs') {
                                $seconds = round($seconds / 1000);
                            }
                            $metadata['duration_seconds'] = $seconds;
                            $metadata['duration'] = customtube_format_duration($seconds);
                            error_log("Scraping: Got YouTube duration from $name: {$seconds}s");
                            break;
                        }
                    }
                }
                
                // Try metadata formats as well
                if (empty($metadata['duration_seconds'])) {
                    if (preg_match('/<meta itemprop="duration" content="PT(\d+)M(\d+)S">/', $html, $matches)) {
                        $minutes = intval($matches[1]);
                        $seconds = intval($matches[2]);
                        $total_seconds = ($minutes * 60) + $seconds;
                        $metadata['duration_seconds'] = $total_seconds;
                        $metadata['duration'] = customtube_format_duration($total_seconds);
                        error_log("Scraping: Got YouTube duration from meta tag: {$total_seconds}s");
                    }
                    // Try another approach looking for the timestamp in the UI
                    elseif (preg_match('/playabilityStatus.*?"text":\s*"(\d+):(\d+)"/', $html, $matches)) {
                        $minutes = intval($matches[1]);
                        $seconds = intval($matches[2]);
                        $total_seconds = ($minutes * 60) + $seconds;
                        $metadata['duration_seconds'] = $total_seconds;
                        $metadata['duration'] = customtube_format_duration($total_seconds);
                        error_log("Scraping: Got YouTube duration from UI timestamp: {$total_seconds}s");
                    }
                }
            }
        }
        
        // If we still have no title, use video ID as last resort
        if (empty($metadata['title'])) {
            $metadata['title'] = "YouTube Video: {$video_id}";
            error_log("Using fallback title for YouTube video: {$metadata['title']}");
        }
        
        return $metadata;
    }
}

/**
 * Set YouTube thumbnails in metadata
 *
 * @param string $video_id YouTube video ID
 * @param array $metadata Existing metadata to append to
 * @return array Updated metadata
 */
if (!function_exists('customtube_set_youtube_thumbnails')) {
    function customtube_set_youtube_thumbnails($video_id, $metadata) {
        // If no thumbnail URL is already set, try different quality thumbnails
        if (empty($metadata['thumbnail_url'])) {
            // First try maxresdefault (HD)
            $max_thumb_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
            $max_thumb_response = wp_remote_head($max_thumb_url);
            
            if (!is_wp_error($max_thumb_response) && wp_remote_retrieve_response_code($max_thumb_response) === 200) {
                $metadata['thumbnail_url'] = $max_thumb_url;
                error_log("Using YouTube maxresdefault thumbnail");
            } else {
                // Fallback to hqdefault if maxresdefault doesn't exist
                $metadata['thumbnail_url'] = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
                error_log("Using YouTube hqdefault thumbnail (fallback)");
            }
        }
        
        return $metadata;
    }
}

/**
 * Ensure format_duration function exists
 * 
 * This function is already defined in video-metadata.php, so we check first
 * to avoid redeclaration errors
 */
if (!function_exists('customtube_format_duration')) {
    /**
     * Format duration helper function
     *
     * @param int $seconds Duration in seconds
     * @return string Formatted duration (MM:SS or HH:MM:SS)
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
}