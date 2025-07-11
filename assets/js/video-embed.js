/**
 * Video Embed and Auto-Detection JavaScript
 * 
 * Handles automatic detection of video metadata from URLs
 * and provides a smoother video adding experience
 */

(function($) {
    'use strict';
    
    // Main initialization function
    function initVideoEmbed() {
        const videoUrlField = $('#video_url');
        const autoDetectField = $('#auto_detect_url');
        const durationField = $('#video_duration');
        const thumbnailButton = $('#extract-video-thumbnail');
        const autoDetectButton = $('#auto-detect-duration');
        
        // Set up auto-detection on URL paste
        if (autoDetectField.length) {
            autoDetectField.on('input paste', debounce(function() {
                const url = $(this).val().trim();
                if (url && isValidUrl(url)) {
                    detectVideo(url);
                }
            }, 500));
        }
        
        // Set up auto-detection when video URL changes
        if (videoUrlField.length) {
            videoUrlField.on('input paste', debounce(function() {
                const url = $(this).val().trim();
                
                // Clear test video preview if URL is empty
                if (!url) {
                    $('.video-preview video').attr('src', '');
                    return;
                }
                
                // Update video preview for direct player
                if (isDirectVideoUrl(url)) {
                    $('.video-preview video').attr('src', url);
                }
            }, 500));
        }
        
        // Test video URL button
        $('#test-video-url').on('click', function(e) {
            e.preventDefault();
            
            const url = videoUrlField.val().trim();
            if (url) {
                $('.video-preview video').attr('src', url);
            }
            
            // If not already done, try to detect video metadata
            detectVideo(url);
        });
        
        // Auto-detect duration button
        if (autoDetectButton.length) {
            autoDetectButton.on('click', function(e) {
                e.preventDefault();
                
                const url = videoUrlField.val().trim();
                if (url) {
                    const spinner = $('#duration-spinner');
                    spinner.addClass('is-active');
                    
                    detectVideo(url, function(data) {
                        spinner.removeClass('is-active');
                        
                        if (data.duration) {
                            durationField.val(data.duration);
                        } else {
                            alert('Could not automatically detect duration. Please enter it manually.');
                        }
                    });
                } else {
                    alert('Please enter a video URL first.');
                }
            });
        }
        
        // Extract thumbnail button
        if (thumbnailButton.length) {
            thumbnailButton.on('click', function(e) {
                e.preventDefault();
                
                const url = videoUrlField.val().trim();
                if (url) {
                    const spinner = $('#thumbnail-spinner');
                    const result = $('#thumbnail-result');
                    
                    spinner.addClass('is-active');
                    result.html('');
                    
                    detectVideo(url, function(data) {
                        spinner.removeClass('is-active');
                        
                        if (data.thumbnail_url) {
                            result.html('<div><img src="' + data.thumbnail_url + '" style="max-width: 200px; height: auto;" /></div>' +
                                        '<p>Thumbnail detected! You can use "Set featured image" to add it to your post.</p>');
                        } else {
                            result.html('<p>Could not automatically extract thumbnail. Please upload one manually.</p>');
                        }
                    });
                } else {
                    alert('Please enter a video URL first.');
                }
            });
        }
    }
    
    /**
     * Detect video metadata from URL
     * 
     * @param {string} url The video URL
     * @param {function} callback Optional callback function
     */
    function detectVideo(url, callback) {
        if (!url || !isValidUrl(url)) {
            return;
        }
        
        const spinner = $('#auto-detect-spinner');
        const result = $('#auto-detect-result');
        
        spinner.addClass('is-active');
        result.html('');
        
        $.ajax({
            url: customtubeVideo.ajax_url,
            type: 'POST',
            data: {
                action: 'customtube_detect_video',
                url: url,
                nonce: customtubeVideo.nonce
            },
            success: function(response) {
                spinner.removeClass('is-active');
                
                if (response.success && response.data) {
                    const data = response.data;
                    
                    // Display detected info
                    result.html(
                        '<div class="notice notice-success inline">' +
                        '<p><strong>Video detected!</strong> Type: ' + data.type + 
                        (data.id ? ', ID: ' + data.id : '') + '</p>' +
                        '</div>'
                    );
                    
                    // Update fields with detected values
                    $('#video_url').val(data.url);
                    
                    // Set title if available and post is new
                    if (data.title && $('#title').val() === '') {
                        $('#title').val(data.title);
                    }
                    
                    // Set description if available and post is new
                    if (data.description && tinyMCE && tinyMCE.activeEditor && tinyMCE.activeEditor.getContent() === '') {
                        tinyMCE.activeEditor.setContent(data.description);
                    }
                    
                    // Set duration if available
                    if (data.duration && $('#video_duration').val() === '') {
                        $('#video_duration').val(data.duration);
                    }
                    
                    // Set preview URL for YouTube
                    if (data.type === 'youtube' && data.preview_url && $('#preview_url').val() === '') {
                        $('#preview_url').val(data.preview_url);
                    }
                    
                    // Show preview if available
                    if (data.type === 'direct') {
                        $('.video-preview video').attr('src', data.url);
                    }
                    
                    // Call the callback function if provided
                    if (typeof callback === 'function') {
                        callback(data);
                    }
                } else {
                    const errorMsg = response.data || 'Unknown error';
                    result.html(
                        '<div class="notice notice-error inline">' +
                        '<p><strong>Error:</strong> ' + errorMsg + '</p>' +
                        '</div>'
                    );
                    
                    if (typeof callback === 'function') {
                        callback({});
                    }
                }
            },
            error: function() {
                spinner.removeClass('is-active');
                result.html(
                    '<div class="notice notice-error inline">' +
                    '<p><strong>Error:</strong> Could not connect to server.</p>' +
                    '</div>'
                );
                
                if (typeof callback === 'function') {
                    callback({});
                }
            }
        });
    }
    
    /**
     * Check if a string is a valid URL
     */
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    /**
     * Check if URL is a direct video file
     */
    function isDirectVideoUrl(url) {
        return /\.(mp4|m4v|webm|ogg)(\?.*)?$/i.test(url);
    }
    
    /**
     * Simple debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // Initialize on document ready
    $(document).ready(initVideoEmbed);
    
})(jQuery);