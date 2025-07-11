<?php
/**
 * FFmpeg Integration Module
 * Provides core functionality for video processing with FFmpeg
 *
 * @package CustomTube
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Check if FFmpeg is installed and accessible
 * 
 * @return bool True if FFmpeg is available
 */
function customtube_ffmpeg_is_available() {
    // Get paths from options if not defined as constants
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : get_option('customtube_ffmpeg_binary', '/usr/bin/ffmpeg');
    $ffprobe_path = defined('FFPROBE_BINARY') ? FFPROBE_BINARY : get_option('customtube_ffprobe_binary', '/usr/bin/ffprobe');
    
    // Check if files exist first
    if (!file_exists($ffmpeg_path) || !file_exists($ffprobe_path)) {
        error_log("FFmpeg binaries not found at paths: ffmpeg=$ffmpeg_path, ffprobe=$ffprobe_path");
        return false;
    }
    
    // Try to execute FFmpeg and FFprobe with version flag
    $ffmpeg_check = shell_exec(escapeshellarg($ffmpeg_path) . " -version 2>&1");
    $ffprobe_check = shell_exec(escapeshellarg($ffprobe_path) . " -version 2>&1");
    
    $result = (!empty($ffmpeg_check) && !empty($ffprobe_check) && 
               strpos($ffmpeg_check, 'ffmpeg version') !== false && 
               strpos($ffprobe_check, 'ffprobe version') !== false);
    
    if (!$result) {
        error_log("FFmpeg binaries failed version check: ffmpeg=" . substr($ffmpeg_check, 0, 100) . 
                  ", ffprobe=" . substr($ffprobe_check, 0, 100));
    }
    
    return $result;
}

/**
 * Get video information using FFprobe
 * 
 * @param string $video_path Path to video file or URL
 * @return array|false Video metadata or false on failure
 */
function customtube_get_video_info($video_path) {
    // Check if it's a local file or URL
    $is_url = filter_var($video_path, FILTER_VALIDATE_URL) !== false;
    
    if (!$is_url && !file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Get FFprobe path from settings if needed
    $ffprobe_path = defined('FFPROBE_BINARY') ? FFPROBE_BINARY : get_option('customtube_ffprobe_binary', '/usr/bin/ffprobe');
    
    // Build command to get JSON output with video metadata
    // Add timeout for URLs to prevent hanging
    $timeout_opt = $is_url ? '-timeout 30000000 ' : ''; // 30 second timeout for URLs
    
    $cmd = sprintf(
        '%s %s-v quiet -print_format json -show_format -show_streams %s',
        escapeshellarg($ffprobe_path),
        $timeout_opt,
        escapeshellarg($video_path)
    );
    
    error_log("Executing FFprobe command: $cmd");
    
    // Execute command
    $output = shell_exec($cmd);
    
    if (empty($output)) {
        error_log("Failed to get video info for: $video_path");
        return false;
    }
    
    // Parse JSON output
    $data = json_decode($output, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Failed to parse FFprobe JSON output: " . json_last_error_msg());
        return false;
    }
    
    // Extract key information
    $info = array(
        'duration' => 0,
        'width' => 0,
        'height' => 0,
        'format' => '',
        'bitrate' => 0,
        'size' => 0,
        'streams' => array(),
    );
    
    // Get format information
    if (isset($data['format'])) {
        $info['format'] = isset($data['format']['format_name']) ? $data['format']['format_name'] : '';
        $info['duration'] = isset($data['format']['duration']) ? floatval($data['format']['duration']) : 0;
        $info['size'] = isset($data['format']['size']) ? intval($data['format']['size']) : 0;
        $info['bitrate'] = isset($data['format']['bit_rate']) ? intval($data['format']['bit_rate']) : 0;
    }
    
    // Get video stream information
    if (isset($data['streams']) && is_array($data['streams'])) {
        foreach ($data['streams'] as $stream) {
            $info['streams'][] = $stream;
            
            // Extract video dimensions from the first video stream
            if (isset($stream['codec_type']) && $stream['codec_type'] === 'video' && $info['width'] === 0) {
                $info['width'] = isset($stream['width']) ? intval($stream['width']) : 0;
                $info['height'] = isset($stream['height']) ? intval($stream['height']) : 0;
            }
        }
    }
    
    return $info;
}

/**
 * Generate a thumbnail from a video at a specific timestamp
 * 
 * @param string $video_path Path to video file
 * @param string $output_path Path where thumbnail should be saved
 * @param int|string $timestamp Time position (seconds or HH:MM:SS)
 * @param array $options Additional options (width, height, quality)
 * @return bool True on success, false on failure
 */
function customtube_generate_thumbnail($video_path, $output_path, $timestamp = 3, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Ensure output directory exists
    $output_dir = dirname($output_path);
    if (!file_exists($output_dir)) {
        if (!wp_mkdir_p($output_dir)) {
            error_log("Failed to create output directory: $output_dir");
            return false;
        }
    }
    
    // Parse options
    $defaults = array(
        'width' => 640,
        'height' => 360,
        'quality' => 2, // Lower is better quality (1-31)
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // Format timestamp if it's numeric
    if (is_numeric($timestamp)) {
        $hours = floor($timestamp / 3600);
        $mins = floor(($timestamp % 3600) / 60);
        $secs = $timestamp % 60;
        $timestamp = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }
    
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    
    // Build command
    $cmd = sprintf(
        '%s -y -ss %s -i %s -vframes 1 -q:v %d -vf "scale=%d:%d" %s 2>&1',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($timestamp),
        escapeshellarg($video_path),
        intval($options['quality']),
        intval($options['width']),
        intval($options['height']),
        escapeshellarg($output_path)
    );
    
    // Execute command
    $output = shell_exec($cmd);
    
    // Check if thumbnail was created
    if (!file_exists($output_path)) {
        error_log("Failed to generate thumbnail. Command: $cmd, Output: $output");
        return false;
    }
    
    return true;
}

/**
 * Generate multiple thumbnails at different timestamps
 * 
 * @param string $video_path Path to video file
 * @param string $output_dir Directory where thumbnails should be saved
 * @param int $count Number of thumbnails to generate
 * @param array $options Additional options (width, height, quality)
 * @return array|false Array of thumbnail paths or false on failure
 */
function customtube_generate_multiple_thumbnails($video_path, $output_dir, $count = 5, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Get video info for duration
    $video_info = customtube_get_video_info($video_path);
    if (!$video_info) {
        error_log("Couldn't get video info for thumbnails");
        return false;
    }
    
    $duration = $video_info['duration'];
    if ($duration <= 0) {
        error_log("Invalid video duration: $duration");
        return false;
    }
    
    // Calculate timestamp intervals
    $interval = $duration / ($count + 1);
    $thumbnails = array();
    
    // Generate thumbnails at intervals
    for ($i = 1; $i <= $count; $i++) {
        $timestamp = $interval * $i;
        $output_path = trailingslashit($output_dir) . "thumbnail_{$i}.jpg";
        
        if (customtube_generate_thumbnail($video_path, $output_path, $timestamp, $options)) {
            $thumbnails[] = $output_path;
        }
    }
    
    return !empty($thumbnails) ? $thumbnails : false;
}

/**
 * Generate a preview clip from a video
 * 
 * @param string $video_path Path to video file
 * @param string $output_path Path where clip should be saved
 * @param array $options Additional options (start, duration, width, height, etc.)
 * @return bool True on success, false on failure
 */
function customtube_generate_preview_clip($video_path, $output_path, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Ensure output directory exists
    $output_dir = dirname($output_path);
    if (!file_exists($output_dir)) {
        if (!wp_mkdir_p($output_dir)) {
            error_log("Failed to create output directory: $output_dir");
            return false;
        }
    }
    
    // Get video info
    $video_info = customtube_get_video_info($video_path);
    if (!$video_info) {
        error_log("Couldn't get video info for preview clip");
        return false;
    }
    
    // Parse options
    $defaults = array(
        'start' => 5,             // Start at 5 seconds in
        'duration' => 15,         // 15 second clip
        'width' => 640,           // Output width
        'height' => 360,          // Output height
        'audio' => true,          // Include audio
        'bitrate' => '1M',        // Video bitrate
        'framerate' => 30,        // Frame rate
        'format' => 'mp4',        // Output format
        'codec' => 'libx264',     // Video codec
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // If no start time provided and duration is known, pick a random spot
    if ($options['start'] === 5 && isset($video_info['duration']) && $video_info['duration'] > 30) {
        $max_start = $video_info['duration'] - $options['duration'] - 5;
        if ($max_start > 5) {
            $options['start'] = rand(5, $max_start);
        }
    }
    
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    
    // Build audio options
    $audio_opts = $options['audio'] ? '-c:a aac -b:a 128k' : '-an';
    
    // Build command
    $cmd = sprintf(
        '%s -y -ss %s -i %s -t %s -vf "scale=%d:%d" -c:v %s -b:v %s -r %d %s %s 2>&1',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($options['start']),
        escapeshellarg($video_path),
        escapeshellarg($options['duration']),
        intval($options['width']),
        intval($options['height']),
        escapeshellarg($options['codec']),
        escapeshellarg($options['bitrate']),
        intval($options['framerate']),
        $audio_opts,
        escapeshellarg($output_path)
    );
    
    // Execute command
    $output = shell_exec($cmd);
    
    // Check if clip was created
    if (!file_exists($output_path)) {
        error_log("Failed to generate preview clip. Command: $cmd, Output: $output");
        return false;
    }
    
    return true;
}

/**
 * Generate an animated GIF preview from a video
 * 
 * @param string $video_path Path to video file
 * @param string $output_path Path where GIF should be saved
 * @param array $options Additional options (start, duration, width, height, etc.)
 * @return bool True on success, false on failure
 */
function customtube_generate_animated_gif($video_path, $output_path, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Ensure output directory exists
    $output_dir = dirname($output_path);
    if (!file_exists($output_dir)) {
        if (!wp_mkdir_p($output_dir)) {
            error_log("Failed to create output directory: $output_dir");
            return false;
        }
    }
    
    // Get video info
    $video_info = customtube_get_video_info($video_path);
    if (!$video_info) {
        error_log("Couldn't get video info for animated GIF");
        return false;
    }
    
    // Parse options
    $defaults = array(
        'start' => 5,             // Start at 5 seconds in
        'duration' => 3,          // 3 second GIF
        'width' => 320,           // Output width
        'height' => 180,          // Output height
        'fps' => 10,              // Frames per second (lower for smaller file size)
        'optimize' => true,       // Apply optimization for file size
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // If no start time provided and duration is known, pick a random spot
    if ($options['start'] === 5 && isset($video_info['duration']) && $video_info['duration'] > 10) {
        $max_start = $video_info['duration'] - $options['duration'] - 3;
        if ($max_start > 5) {
            $options['start'] = rand(5, $max_start);
        }
    }
    
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    
    // Base command
    $base_cmd = sprintf(
        '%s -y -ss %s -i %s -t %s -vf "scale=%d:%d:flags=lanczos,fps=%d"',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($options['start']),
        escapeshellarg($video_path),
        escapeshellarg($options['duration']),
        intval($options['width']),
        intval($options['height']),
        intval($options['fps'])
    );
    
    // Generate command based on optimization preferences
    if ($options['optimize']) {
        // Using palette generation method for better quality and smaller size
        $palette_path = trailingslashit(defined('TEMP_DIR') ? TEMP_DIR : sys_get_temp_dir()) . 'palette.png';
        
        // Generate palette
        $palette_cmd = $base_cmd . sprintf(
            ' -vf "fps=%d,scale=%d:%d:flags=lanczos,palettegen" %s',
            intval($options['fps']),
            intval($options['width']),
            intval($options['height']),
            escapeshellarg($palette_path)
        );
        
        // Execute palette command
        $palette_output = shell_exec($palette_cmd);
        
        if (!file_exists($palette_path)) {
            error_log("Failed to generate palette for GIF. Output: $palette_output");
            return false;
        }
        
        // Generate GIF using palette
        $gif_cmd = sprintf(
            '%s -y -ss %s -i %s -i %s -t %s -filter_complex "fps=%d,scale=%d:%d:flags=lanczos[x];[x][1:v]paletteuse" %s 2>&1',
            escapeshellarg($ffmpeg_path),
            escapeshellarg($options['start']),
            escapeshellarg($video_path),
            escapeshellarg($palette_path),
            escapeshellarg($options['duration']),
            intval($options['fps']),
            intval($options['width']),
            intval($options['height']),
            escapeshellarg($output_path)
        );
        
        // Execute GIF command
        $output = shell_exec($gif_cmd);
        
        // Clean up palette
        @unlink($palette_path);
    } else {
        // Simple GIF generation (larger file size, lower quality)
        $cmd = $base_cmd . ' ' . escapeshellarg($output_path) . ' 2>&1';
        $output = shell_exec($cmd);
    }
    
    // Check if GIF was created
    if (!file_exists($output_path)) {
        error_log("Failed to generate animated GIF. Output: $output");
        return false;
    }
    
    return true;
}

/**
 * Generate a WebP animation from a video
 * (WebP animations are more efficient than GIFs)
 * 
 * @param string $video_path Path to video file
 * @param string $output_path Path where WebP should be saved
 * @param array $options Additional options (start, duration, width, height, etc.)
 * @return bool True on success, false on failure
 */
function customtube_generate_webp_animation($video_path, $output_path, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Ensure output directory exists
    $output_dir = dirname($output_path);
    if (!file_exists($output_dir)) {
        if (!wp_mkdir_p($output_dir)) {
            error_log("Failed to create output directory: $output_dir");
            return false;
        }
    }
    
    // Check if FFmpeg has WebP support
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    $webp_check = shell_exec("$ffmpeg_path -v quiet -codecs | grep -i webp");
    
    if (empty($webp_check)) {
        error_log("FFmpeg installation doesn't support WebP encoding");
        return false;
    }
    
    // Get video info
    $video_info = customtube_get_video_info($video_path);
    if (!$video_info) {
        error_log("Couldn't get video info for WebP animation");
        return false;
    }
    
    // Parse options
    $defaults = array(
        'start' => 5,             // Start at 5 seconds in
        'duration' => 3,          // 3 second animation
        'width' => 320,           // Output width
        'height' => 180,          // Output height
        'fps' => 12,              // Frames per second
        'quality' => 80,          // Quality (0-100)
        'lossless' => false,      // Use lossless compression
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // If no start time provided and duration is known, pick a random spot
    if ($options['start'] === 5 && isset($video_info['duration']) && $video_info['duration'] > 10) {
        $max_start = $video_info['duration'] - $options['duration'] - 3;
        if ($max_start > 5) {
            $options['start'] = rand(5, $max_start);
        }
    }
    
    // Build WebP options
    $webp_opts = $options['lossless'] ? '-lossless 1' : '-quality ' . intval($options['quality']);
    
    // Build command
    $cmd = sprintf(
        '%s -y -ss %s -i %s -t %s -vf "scale=%d:%d:flags=lanczos,fps=%d" -loop 0 %s %s 2>&1',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($options['start']),
        escapeshellarg($video_path),
        escapeshellarg($options['duration']),
        intval($options['width']),
        intval($options['height']),
        intval($options['fps']),
        $webp_opts,
        escapeshellarg($output_path)
    );
    
    // Execute command
    $output = shell_exec($cmd);
    
    // Check if WebP was created
    if (!file_exists($output_path)) {
        error_log("Failed to generate WebP animation. Command: $cmd, Output: $output");
        return false;
    }
    
    return true;
}

/**
 * Generate video with a watermark
 * 
 * @param string $video_path Path to video file
 * @param string $watermark_path Path to watermark image (PNG with transparency recommended)
 * @param string $output_path Path where output video should be saved
 * @param array $options Additional options (position, size, opacity, etc.)
 * @return bool True on success, false on failure
 */
function customtube_add_watermark_to_video($video_path, $watermark_path, $output_path, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    if (!file_exists($watermark_path)) {
        error_log("Watermark file not found: $watermark_path");
        return false;
    }
    
    // Ensure output directory exists
    $output_dir = dirname($output_path);
    if (!file_exists($output_dir)) {
        if (!wp_mkdir_p($output_dir)) {
            error_log("Failed to create output directory: $output_dir");
            return false;
        }
    }
    
    // Parse options
    $defaults = array(
        'position' => 'bottom-right', // Options: top-left, top-right, bottom-left, bottom-right, center
        'margin' => 20,               // Margin from edge (in pixels)
        'scale' => 0.2,               // Scale of watermark relative to video width (0.0-1.0)
        'opacity' => 0.7,             // Opacity of watermark (0.0-1.0)
        'codec' => 'libx264',         // Video codec
        'bitrate' => '2M',            // Video bitrate
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // Calculate the position
    $position_map = array(
        'top-left' => 'x=' . $options['margin'] . ':y=' . $options['margin'],
        'top-right' => 'x=main_w-overlay_w-' . $options['margin'] . ':y=' . $options['margin'],
        'bottom-left' => 'x=' . $options['margin'] . ':y=main_h-overlay_h-' . $options['margin'],
        'bottom-right' => 'x=main_w-overlay_w-' . $options['margin'] . ':y=main_h-overlay_h-' . $options['margin'],
        'center' => 'x=(main_w-overlay_w)/2:y=(main_h-overlay_h)/2',
    );
    
    $position = isset($position_map[$options['position']]) ? $position_map[$options['position']] : $position_map['bottom-right'];
    
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    
    // Build overlay complex filter
    $overlay_filter = sprintf(
        'overlay="%s:format=auto,format=yuv420p"',
        $position
    );
    
    if ($options['scale'] != 1.0) {
        $overlay_filter = sprintf(
            '[1:v]scale=iw*%f:-1[overlay];[0:v][overlay]%s',
            floatval($options['scale']),
            $overlay_filter
        );
    }
    
    if ($options['opacity'] != 1.0) {
        $overlay_filter = sprintf(
            '[1:v]format=rgba,colorchannelmixer=aa=%f[overlay];[0:v][overlay]%s',
            floatval($options['opacity']),
            $overlay_filter
        );
    }
    
    // Build command
    $cmd = sprintf(
        '%s -y -i %s -i %s -filter_complex "%s" -c:v %s -b:v %s -c:a copy %s 2>&1',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($video_path),
        escapeshellarg($watermark_path),
        $overlay_filter,
        escapeshellarg($options['codec']),
        escapeshellarg($options['bitrate']),
        escapeshellarg($output_path)
    );
    
    // Execute command
    $output = shell_exec($cmd);
    
    // Check if watermarked video was created
    if (!file_exists($output_path)) {
        error_log("Failed to add watermark to video. Command: $cmd, Output: $output");
        return false;
    }
    
    return true;
}

/**
 * Extract a clip from a video at a specific time range
 * 
 * @param string $video_path Path to video file
 * @param string $output_path Path where clip should be saved
 * @param array $options Additional options (start, duration, width, height, etc.)
 * @return bool True on success, false on failure
 */
function customtube_extract_clip($video_path, $output_path, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Ensure output directory exists
    $output_dir = dirname($output_path);
    if (!file_exists($output_dir)) {
        if (!wp_mkdir_p($output_dir)) {
            error_log("Failed to create output directory: $output_dir");
            return false;
        }
    }
    
    // Parse options
    $defaults = array(
        'start' => 0,             // Start time in seconds
        'duration' => 30,         // Duration in seconds
        'width' => 0,             // Output width (0 = keep original)
        'height' => 0,            // Output height (0 = keep original)
        'audio' => true,          // Include audio
        'bitrate' => '',          // Video bitrate (empty = keep original)
        'codec' => 'copy',        // Video codec (copy = no re-encoding)
    );
    
    $options = wp_parse_args($options, $defaults);
    
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    
    // Build scale filter if dimensions are specified
    $scale_filter = '';
    if ($options['width'] > 0 && $options['height'] > 0) {
        $scale_filter = sprintf(' -vf "scale=%d:%d"', $options['width'], $options['height']);
        $options['codec'] = 'libx264'; // Force re-encoding when scaling
    }
    
    // Build audio options
    $audio_opts = $options['audio'] ? '-c:a copy' : '-an';
    
    // Build bitrate option
    $bitrate_opt = !empty($options['bitrate']) ? '-b:v ' . escapeshellarg($options['bitrate']) : '';
    
    // Adjust codec for copy vs re-encode
    $video_codec = $options['codec'] === 'copy' ? '-c:v copy' : '-c:v ' . escapeshellarg($options['codec']);
    
    // Build command
    $cmd = sprintf(
        '%s -y -ss %s -i %s -t %s%s %s %s %s %s 2>&1',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($options['start']),
        escapeshellarg($video_path),
        escapeshellarg($options['duration']),
        $scale_filter,
        $video_codec,
        $audio_opts,
        $bitrate_opt,
        escapeshellarg($output_path)
    );
    
    // Execute command
    $output = shell_exec($cmd);
    
    // Check if clip was created
    if (!file_exists($output_path)) {
        error_log("Failed to extract clip. Command: $cmd, Output: $output");
        return false;
    }
    
    return true;
}

/**
 * Create a highlight clip with automatic scene detection
 * 
 * @param string $video_path Path to video file
 * @param string $output_path Path where highlight clip should be saved
 * @param array $options Additional options (duration, threshold, etc.)
 * @return bool True on success, false on failure
 */
function customtube_create_highlight_clip($video_path, $output_path, $options = array()) {
    if (!file_exists($video_path)) {
        error_log("Video file not found: $video_path");
        return false;
    }
    
    // Get video info
    $video_info = customtube_get_video_info($video_path);
    if (!$video_info) {
        error_log("Couldn't get video info for highlight clip");
        return false;
    }
    
    // Parse options
    $defaults = array(
        'duration' => 30,         // Duration of highlight clip in seconds
        'scene_threshold' => 0.4, // Scene change detection threshold (0.0-1.0)
        'min_scene_length' => 3,  // Minimum scene length in seconds
        'max_scenes' => 5,        // Maximum number of scenes to include
        'width' => 854,           // Output width
        'height' => 480,          // Output height
        'bitrate' => '2M',        // Video bitrate
        'codec' => 'libx264',     // Video codec
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // Ensure video is long enough for highlight detection
    if ($video_info['duration'] < 60) {
        // If video is short, just make a clip of the first part
        return customtube_extract_clip($video_path, $output_path, array(
            'start' => 0,
            'duration' => min($options['duration'], $video_info['duration']),
            'width' => $options['width'],
            'height' => $options['height'],
            'bitrate' => $options['bitrate'],
            'codec' => $options['codec'],
        ));
    }
    
    $ffmpeg_path = defined('FFMPEG_BINARY') ? FFMPEG_BINARY : '/usr/bin/ffmpeg';
    $temp_dir = defined('TEMP_DIR') ? TEMP_DIR : sys_get_temp_dir();
    $temp_dir = trailingslashit($temp_dir);
    
    // Create unique temp directory for this operation
    $temp_subdir = $temp_dir . 'highlight_' . md5($video_path . time());
    if (!wp_mkdir_p($temp_subdir)) {
        error_log("Failed to create temp directory: $temp_subdir");
        return false;
    }
    
    // Step 1: Detect scenes
    $scene_list_file = $temp_subdir . '/scenes.txt';
    $scene_detect_cmd = sprintf(
        '%s -y -i %s -filter:v "select=\'gt(scene,%f)\',showinfo" -f null - 2>&1 | grep "pts_time" > %s',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($video_path),
        floatval($options['scene_threshold']),
        escapeshellarg($scene_list_file)
    );
    
    // Execute scene detection
    shell_exec($scene_detect_cmd);
    
    if (!file_exists($scene_list_file) || filesize($scene_list_file) === 0) {
        error_log("Scene detection failed or found no scenes");
        // Fallback: just extract a clip from a good part of the video
        $start_time = min($video_info['duration'] * 0.3, $video_info['duration'] - $options['duration']);
        $result = customtube_extract_clip($video_path, $output_path, array(
            'start' => $start_time,
            'duration' => $options['duration'],
            'width' => $options['width'],
            'height' => $options['height'],
            'bitrate' => $options['bitrate'],
            'codec' => $options['codec'],
        ));
        
        // Clean up
        customtube_recursive_rmdir($temp_subdir);
        return $result;
    }
    
    // Step 2: Parse scene list
    $scene_data = file_get_contents($scene_list_file);
    $scenes = array();
    
    preg_match_all('/pts_time:([0-9.]+)/', $scene_data, $matches);
    if (isset($matches[1]) && count($matches[1]) > 0) {
        $scene_times = array_map('floatval', $matches[1]);
        
        // Add video start as first scene
        array_unshift($scene_times, 0);
        
        // Calculate scene lengths
        $valid_scenes = array();
        for ($i = 0; $i < count($scene_times) - 1; $i++) {
            $start = $scene_times[$i];
            $end = $scene_times[$i + 1];
            $length = $end - $start;
            
            if ($length >= $options['min_scene_length']) {
                $valid_scenes[] = array(
                    'start' => $start,
                    'length' => $length
                );
            }
        }
        
        // Add the last segment if it's long enough
        $last_start = end($scene_times);
        $last_length = $video_info['duration'] - $last_start;
        if ($last_length >= $options['min_scene_length']) {
            $valid_scenes[] = array(
                'start' => $last_start,
                'length' => $last_length
            );
        }
        
        // Sort scenes by length (descending) to get the longest/most interesting ones
        usort($valid_scenes, function($a, $b) {
            return $b['length'] - $a['length'];
        });
        
        // Take the top scenes up to max_scenes
        $scenes = array_slice($valid_scenes, 0, $options['max_scenes']);
        
        // Sort scenes by start time for chronological order
        usort($scenes, function($a, $b) {
            return $a['start'] - $b['start'];
        });
    }
    
    // If no valid scenes were found, use fallback
    if (empty($scenes)) {
        error_log("No valid scenes found");
        // Fallback: extract a clip from 1/3 into the video
        $start_time = min($video_info['duration'] * 0.3, $video_info['duration'] - $options['duration']);
        $result = customtube_extract_clip($video_path, $output_path, array(
            'start' => $start_time,
            'duration' => $options['duration'],
            'width' => $options['width'],
            'height' => $options['height'],
            'bitrate' => $options['bitrate'],
            'codec' => $options['codec'],
        ));
        
        // Clean up
        customtube_recursive_rmdir($temp_subdir);
        return $result;
    }
    
    // Step 3: Extract clips from each scene
    $clip_files = array();
    $total_duration = 0;
    $target_duration = $options['duration'];
    
    foreach ($scenes as $i => $scene) {
        // Calculate how much of this scene to use
        $scene_duration = min($scene['length'], $target_duration - $total_duration);
        if ($scene_duration <= 0) {
            break; // We have enough footage
        }
        
        $clip_file = $temp_subdir . "/clip_{$i}.mp4";
        
        $clip_cmd = sprintf(
            '%s -y -ss %s -i %s -t %s -vf "scale=%d:%d" -c:v %s -b:v %s -c:a aac -b:a 128k %s 2>&1',
            escapeshellarg($ffmpeg_path),
            escapeshellarg($scene['start']),
            escapeshellarg($video_path),
            escapeshellarg($scene_duration),
            intval($options['width']),
            intval($options['height']),
            escapeshellarg($options['codec']),
            escapeshellarg($options['bitrate']),
            escapeshellarg($clip_file)
        );
        
        // Execute clip extraction
        shell_exec($clip_cmd);
        
        if (file_exists($clip_file)) {
            $clip_files[] = $clip_file;
            $total_duration += $scene_duration;
        }
        
        if ($total_duration >= $target_duration) {
            break; // We have enough footage
        }
    }
    
    // If no clips were extracted, use fallback
    if (empty($clip_files)) {
        error_log("Failed to extract any clips for highlight reel");
        // Fallback: extract a clip from a good part of the video
        $start_time = min($video_info['duration'] * 0.3, $video_info['duration'] - $options['duration']);
        $result = customtube_extract_clip($video_path, $output_path, array(
            'start' => $start_time,
            'duration' => $options['duration'],
            'width' => $options['width'],
            'height' => $options['height'],
            'bitrate' => $options['bitrate'],
            'codec' => $options['codec'],
        ));
        
        // Clean up
        customtube_recursive_rmdir($temp_subdir);
        return $result;
    }
    
    // Step 4: Concatenate clips
    $concat_file = $temp_subdir . '/concat.txt';
    $concat_content = '';
    
    foreach ($clip_files as $clip_file) {
        $concat_content .= "file '" . str_replace("'", "\\'", $clip_file) . "'\n";
    }
    
    file_put_contents($concat_file, $concat_content);
    
    $concat_cmd = sprintf(
        '%s -y -f concat -safe 0 -i %s -c copy %s 2>&1',
        escapeshellarg($ffmpeg_path),
        escapeshellarg($concat_file),
        escapeshellarg($output_path)
    );
    
    // Execute concatenation
    shell_exec($concat_cmd);
    
    // Check if final highlight clip was created
    $success = file_exists($output_path);
    
    // Clean up
    customtube_recursive_rmdir($temp_subdir);
    
    return $success;
}

/**
 * Helper function to recursively remove a directory
 * 
 * @param string $dir Directory path
 * @return bool True on success, false on failure
 */
function customtube_recursive_rmdir($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? customtube_recursive_rmdir($path) : unlink($path);
    }
    
    return rmdir($dir);
}