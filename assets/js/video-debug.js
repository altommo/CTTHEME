/**
 * CustomTube Video Debug Helper
 *
 * Adds debugging capabilities for troubleshooting video playback issues
 */
(function($) {
    'use strict';

    // Check for debug parameter in URL
    function hasDebugParameter() {
        return window.location.search.indexOf('debug') !== -1;
    }

    // Initialize video debugging features
    function initializeVideoDebug() {
        if (!hasDebugParameter()) {
            return; // Only run in debug mode
        }
        
        // Add debug container if it doesn't exist
        if ($('#video-debug-panel').length === 0) {
            $('<div id="video-debug-panel"></div>').appendTo('body');
            
            // Style the debug panel
            $('#video-debug-panel').css({
                'position': 'fixed',
                'bottom': '0',
                'left': '0',
                'right': '0',
                'background': 'rgba(0, 0, 0, 0.8)',
                'color': '#fff',
                'font-family': 'monospace',
                'font-size': '12px',
                'padding': '10px',
                'max-height': '200px',
                'overflow-y': 'auto',
                'z-index': '999999'
            });
        }
        
        // Debug video elements
        $('.video-player-container').each(function() {
            const container = $(this);
            const videoId = container.data('video-id');
            const videoType = container.data('video-type') || 'unknown';
            
            logDebugInfo('Video Player Found', {
                'ID': videoId,
                'Type': videoType,
                'Container': container.width() + 'x' + container.height()
            });
            
            // Debug self-hosted videos
            const video = container.find('video.main-video-player');
            if (video.length) {
                // Log video details
                logDebugInfo('Self-hosted Video', {
                    'Source': video.find('source').attr('src') || 'No source',
                    'Poster': video.attr('poster') || 'No poster',
                    'Controls': video.attr('controls') ? 'Yes' : 'No',
                    'Dimensions': video.width() + 'x' + video.height()
                });
                
                // Add event listeners for troubleshooting
                video.on('error', function() {
                    const errorCode = this.error ? this.error.code : 'unknown';
                    let errorMessage = 'Unknown error';
                    
                    // Translate error codes
                    switch(errorCode) {
                        case 1: 
                            errorMessage = 'MEDIA_ERR_ABORTED: The fetching process was aborted';
                            break;
                        case 2: 
                            errorMessage = 'MEDIA_ERR_NETWORK: A network error occurred';
                            break;
                        case 3: 
                            errorMessage = 'MEDIA_ERR_DECODE: The media could not be decoded';
                            break;
                        case 4: 
                            errorMessage = 'MEDIA_ERR_SRC_NOT_SUPPORTED: The media format is not supported';
                            break;
                    }
                    
                    logDebugInfo('Video Error', {
                        'Code': errorCode,
                        'Message': errorMessage,
                        'Source': video.find('source').attr('src') || 'No source'
                    }, 'error');
                });
                
                video.on('loadedmetadata', function() {
                    logDebugInfo('Video Loaded', {
                        'Duration': this.duration.toFixed(2) + 's',
                        'Dimensions': this.videoWidth + 'x' + this.videoHeight,
                        'Ready State': this.readyState
                    }, 'success');
                });
            }
            
            // Debug embedded videos
            const iframe = container.find('iframe.embed-iframe');
            if (iframe.length) {
                logDebugInfo('Embedded Video', {
                    'Source': iframe.attr('src') || 'No source',
                    'Container': iframe.parent().width() + 'x' + iframe.parent().height(),
                    'Iframe': iframe.width() + 'x' + iframe.height()
                });
            }
        });
    }
    
    // Log debug information to the debug panel
    function logDebugInfo(title, data, type = 'info') {
        const debugPanel = $('#video-debug-panel');
        
        if (!debugPanel.length) {
            return;
        }
        
        // Create log entry
        let logEntry = '<div class="debug-entry debug-' + type + '" style="margin-bottom: 10px;">';
        logEntry += '<strong>' + title + ':</strong> ';
        
        if (typeof data === 'object') {
            logEntry += '<ul style="margin: 5px 0; padding-left: 20px;">';
            for (let key in data) {
                logEntry += '<li><strong>' + key + ':</strong> ' + data[key] + '</li>';
            }
            logEntry += '</ul>';
        } else {
            logEntry += data;
        }
        
        logEntry += '</div>';
        
        // Add to debug panel
        debugPanel.append(logEntry);
    }
    
    // Add a test button in debug mode
    function addTestButton() {
        if (!hasDebugParameter()) {
            return;
        }
        
        $('.video-player-container').each(function() {
            const container = $(this);
            
            // Add test buttons panel
            const buttonsPanel = $('<div class="debug-buttons" style="position: absolute; top: 10px; right: 10px; z-index: 9999;"></div>');
            container.append(buttonsPanel);
            
            // Add test source button for direct videos
            const testSourceButton = $('<button style="background: #ff5722; color: white; border: none; padding: 5px 10px; margin-right: 5px; border-radius: 3px; cursor: pointer;">Test Source</button>');
            buttonsPanel.append(testSourceButton);
            
            testSourceButton.on('click', function() {
                const video = container.find('video.main-video-player');
                if (video.length) {
                    // Log current source info
                    const src = video.find('source').attr('src');
                    logDebugInfo('Testing Source', { 'URL': src }, 'info');
                    
                    // Try to force video reload
                    video[0].load();
                }
            });
            
            // Add reload button
            const reloadButton = $('<button style="background: #2196F3; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Reload</button>');
            buttonsPanel.append(reloadButton);
            
            reloadButton.on('click', function() {
                window.location.reload();
            });
        });
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        initializeVideoDebug();
        addTestButton();
    });

})(jQuery);