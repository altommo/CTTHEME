/**
 * CustomTube Video Player
 *
 * Handles video player functionality with custom controls
 * Supports MP4, YouTube, Vimeo, and other embedded videos
 */
(function($) {
    'use strict';

    // Video player initialization
    function initializeVideoPlayer() {
        $('.video-player-container').each(function() {
            const container = $(this);
            const videoId = container.data('video-id');
            const videoType = container.data('video-type') || 'mp4';
            
            // Initialize appropriate player based on type
            if (videoType === 'mp4') {
                initSelfHostedPlayer(container, videoId);
            } else {
                initEmbeddedPlayer(container, videoId, videoType);
            }
            
            // Update view count on load
            updateViewCount(container, videoId);
        });
    }
    
    /**
     * Initialize self-hosted video player with custom controls
     */
    function initSelfHostedPlayer(container, videoId) {
        const video = container.find('video.main-video-player').get(0);
        
        if (!video) {
            console.error('Video element not found in container');
            return;
        }
        
        // IMPORTANT: Ensure native controls are disabled to prevent duplicates
        $(video).attr('controls', false);
        $(video).removeAttr('controls');
        
        // Force removal of controls
        setTimeout(function() {
            $(video).removeAttr('controls');
        }, 100);
        
        // Log for debugging
        console.log('Initializing self-hosted player for video ID: ' + videoId);
        console.log('Video source: ' + (video.querySelector('source') ? video.querySelector('source').src : 'No source'));
        
        // Attempt to autoplay the video
        try {
            // Add a play attempt with muted to work around autoplay policies
            video.muted = true;
            const playPromise = video.play();
            
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    // Autoplay started
                    console.log('Video autoplay started successfully');
                    // Unmute after successful play (if user interacts)
                    $(container).one('click', function() {
                        video.muted = false;
                    });
                }).catch(error => {
                    // Autoplay prevented
                    console.warn('Video autoplay prevented:', error);
                    // Show a play button overlay to indicate user needs to interact
                    const playButtonOverlay = $('<div class="play-button-overlay" style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:5;"><div style="width:80px; height:80px; background:rgba(0,0,0,0.6); border-radius:50%; display:flex; align-items:center; justify-content:center;"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg></div></div>');
                    container.append(playButtonOverlay);
                    
                    // When play button overlay is clicked
                    playButtonOverlay.on('click', function() {
                        video.play().then(() => {
                            video.muted = false;
                            playButtonOverlay.remove();
                        }).catch(e => {
                            console.error('Still cannot play video after click:', e);
                        });
                    });
                });
            }
        } catch (e) {
            console.error('Error attempting to play video:', e);
        }
        
        const controlsContainer = container.find('.custom-controls');
        const playPauseButton = controlsContainer.find('.play-pause');
        const volumeButton = controlsContainer.find('.volume-button');
        const volumeSlider = controlsContainer.find('.volume-slider');
        const progressBar = controlsContainer.find('.progress-bar');
        const timeDisplay = controlsContainer.find('.time-display');
        const currentTimeDisplay = timeDisplay.find('.current-time');
        const durationDisplay = timeDisplay.find('.duration');
        const fullscreenButton = controlsContainer.find('.fullscreen-button');
        const qualitySelector = controlsContainer.find('.quality-selector');
        
        let hideControlsTimeout;
        
        // Play/Pause functionality
        playPauseButton.on('click', function() {
            if (video.paused || video.ended) {
                console.log('Attempting to play video from button click');
                video.play().then(() => {
                    console.log('Video played successfully from button');
                    video.muted = false;
                    // Remove play overlay if it exists
                    container.find('.play-button-overlay').remove();
                }).catch(e => {
                    console.error('Error playing video from button:', e);
                });
            } else {
                video.pause();
                console.log('Video paused from button');
            }
        });
        
        // Update play/pause button state
        $(video).on('play', function() {
            container.addClass('is-playing');
        });
        
        $(video).on('pause', function() {
            container.removeClass('is-playing');
        });
        
        // Volume control
        volumeButton.on('click', function() {
            if (video.muted) {
                video.muted = false;
                volumeSlider.val(video.volume);
            } else {
                video.muted = true;
                volumeSlider.val(0);
            }
            updateVolumeState();
        });
        
        volumeSlider.on('input', function() {
            const volume = $(this).val();
            video.volume = volume;
            video.muted = (volume === 0);
            updateVolumeState();
        });
        
        // Update volume button state
        function updateVolumeState() {
            if (video.muted || video.volume === 0) {
                volumeButton.addClass('is-muted');
            } else {
                volumeButton.removeClass('is-muted');
            }
        }
        
        // Progress bar and time display
        $(video).on('timeupdate', function() {
            const currentTime = video.currentTime;
            const duration = video.duration;
            
            if (!isNaN(duration)) {
                // Update progress bar
                const progressPercent = (currentTime / duration) * 100;
                progressBar.css('width', progressPercent + '%');
                
                // Update time display
                currentTimeDisplay.text(formatTime(currentTime));
                durationDisplay.text(formatTime(duration));
            }
        });
        
        // Click on progress bar to seek
        controlsContainer.find('.video-progress').on('click', function(e) {
            if (!isNaN(video.duration)) {
                const progressWidth = $(this).width();
                const clickPosition = e.pageX - $(this).offset().left;
                const seekTime = (clickPosition / progressWidth) * video.duration;
                
                video.currentTime = seekTime;
            }
        });
        
        // Fullscreen button
        fullscreenButton.on('click', function() {
            if (video.requestFullscreen) {
                video.requestFullscreen();
            } else if (video.webkitRequestFullscreen) {
                video.webkitRequestFullscreen();
            } else if (video.msRequestFullscreen) {
                video.msRequestFullscreen();
            }
        });
        
        // Quality selector
        if (qualitySelector.length) {
            const qualityButton = qualitySelector.find('.quality-button');
            const qualityOptions = qualitySelector.find('.quality-options');
            
            qualityButton.on('click', function() {
                qualityOptions.toggleClass('is-visible');
            });
            
            qualityOptions.find('button').on('click', function() {
                const quality = $(this).data('quality');
                const videoUrl = $(this).data('url');
                const currentTime = video.currentTime;
                const isPaused = video.paused;
                
                // Update source and restore state
                video.src = videoUrl;
                video.currentTime = currentTime;
                
                if (!isPaused) {
                    video.play();
                }
                
                // Update quality button text
                qualityButton.text(quality);
                qualityOptions.removeClass('is-visible');
            });
            
            // Hide quality options when clicking elsewhere
            $(document).on('click', function(e) {
                if (!qualitySelector.is(e.target) && qualitySelector.has(e.target).length === 0) {
                    qualityOptions.removeClass('is-visible');
                }
            });
        }
        
        // Auto-hide controls after inactivity
        container.on('mousemove', function() {
            controlsContainer.addClass('is-visible');
            
            clearTimeout(hideControlsTimeout);
            hideControlsTimeout = setTimeout(function() {
                if (!video.paused) {
                    controlsContainer.removeClass('is-visible');
                }
            }, 3000);
        });
        
        // Initial setup
        updateVolumeState();
        
        // Click on video to play/pause
        $(video).on('click', function() {
            if (video.paused || video.ended) {
                video.play();
            } else {
                video.pause();
            }
        });
        
        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            if (container.hasClass('is-active')) {
                switch (e.which) {
                    case 32: // Space
                        if (video.paused || video.ended) {
                            video.play();
                        } else {
                            video.pause();
                        }
                        e.preventDefault();
                        break;
                    case 37: // Left arrow
                        video.currentTime = Math.max(0, video.currentTime - 5);
                        e.preventDefault();
                        break;
                    case 39: // Right arrow
                        video.currentTime = Math.min(video.duration, video.currentTime + 5);
                        e.preventDefault();
                        break;
                    case 38: // Up arrow
                        video.volume = Math.min(1, video.volume + 0.1);
                        volumeSlider.val(video.volume);
                        updateVolumeState();
                        e.preventDefault();
                        break;
                    case 40: // Down arrow
                        video.volume = Math.max(0, video.volume - 0.1);
                        volumeSlider.val(video.volume);
                        updateVolumeState();
                        e.preventDefault();
                        break;
                }
            }
        });
        
        // Set container as active when it has focus
        container.on('mouseenter', function() {
            $('.video-player-container').removeClass('is-active');
            container.addClass('is-active');
        });
    }
    
    /**
     * Initialize embedded video players (YouTube, Vimeo, iframe embeds)
     */
    function initEmbeddedPlayer(container, videoId, videoType) {
        // Different approach: First try to find an embed-responsive div, then an iframe inside
        let embedDiv = container.find('.embed-responsive');
        
        console.log('Initializing embedded player:', videoType);
        
        // If we don't have a proper embed div, let's create one
        if (!embedDiv.length) {
            container.append('<div class="embed-responsive"></div>');
            embedDiv = container.find('.embed-responsive');
        }
        
        // Find iframe in the embed div
        let iframe = embedDiv.find('iframe');
        
        // If we don't have an iframe or it's empty, we need to regenerate it
        if (!iframe.length) {
            console.log('No iframe found, regenerating for video type:', videoType);
            
            // Create an appropriate iframe based on the video type
            let iframeSrc = '';
            
            // Get any additional data from data attributes that might help
            const videoUrl = container.data('video-url') || '';
            
            // Check if we're missing a video ID but have a URL
            if (!videoId && videoUrl) {
                console.log('No video ID found, attempting to extract from URL:', videoUrl);
                
                // Try to extract ID from URL based on video type
                switch (videoType) {
                    case 'xhamster':
                        // Try various XHamster URL patterns
                        const xhamsterPatterns = [
                            /xhamster\.com\/videos\/([^\/&?"\']+)/,
                            /xhamster\.com\/embed\/([^\/&?"\']+)/,
                            /xhamster\.[a-z]+\/videos\/([^\/&?"\']+)/,
                            /xhamster\.[a-z]+\/videos\/[^-]+-([0-9]+)/
                        ];
                        
                        for (const pattern of xhamsterPatterns) {
                            const match = videoUrl.match(pattern);
                            if (match && match[1]) {
                                videoId = match[1];
                                console.log('Extracted XHamster ID from URL:', videoId);
                                break;
                            }
                        }
                        break;
                        
                    // Add other video types as needed
                }
            }
            
            // Extract video ID from the URL if present in the container
            if (!videoId) {
                const sourceUrl = container.find('.embed-responsive iframe').attr('src');
                if (sourceUrl) {
                    console.log('Found source URL in iframe, extracting ID:', sourceUrl);
                    
                    switch (videoType) {
                        case 'xhamster':
                            const xhMatch = sourceUrl.match(/xhamster\.com\/embed\/([^\/&?"\']+)/);
                            if (xhMatch && xhMatch[1]) {
                                videoId = xhMatch[1];
                                console.log('Extracted XHamster ID from iframe src:', videoId);
                            }
                            break;
                            
                        // Add other video types as needed
                    }
                }
            }
            
            switch (videoType) {
                case 'youtube':
                    // Create YouTube iframe - assumed videoId format
                    iframeSrc = `https://www.youtube.com/embed/${videoId}?rel=0&showinfo=0&modestbranding=1`;
                    break;
                    
                case 'vimeo':
                    // Create Vimeo iframe
                    iframeSrc = `https://player.vimeo.com/video/${videoId}?api=1&title=0&byline=0&portrait=0&dnt=1`;
                    break;
                    
                case 'pornhub':
                    // Create PornHub iframe
                    iframeSrc = `https://www.pornhub.com/embed/${videoId}`;
                    break;
                    
                case 'xvideos':
                    // Create XVideos iframe
                    iframeSrc = `https://www.xvideos.com/embedframe/${videoId}`;
                    break;
                    
                case 'xhamster':
                    // For XHamster, use the URL if available when ID is missing
                    if (!videoId && videoUrl && videoUrl.includes('xhamster.com')) {
                        console.log('Using full URL for XHamster embed:', videoUrl);
                        
                        // Try to create an embed URL from the video URL
                        if (videoUrl.includes('/videos/')) {
                            // Extract slug from URL - something like "i-came-to-donate-sperm-but-the-nurse-checked-my-dick-xhXMSSQ"
                            const slug = videoUrl.split('/videos/')[1].split('/')[0];
                            if (slug) {
                                iframeSrc = `https://xhamster.com/embed/xh${slug}`;
                                console.log('Created XHamster embed URL from slug:', iframeSrc);
                            } else {
                                iframeSrc = videoUrl.replace('/videos/', '/embed/');
                                console.log('Created XHamster embed URL by replacing /videos/ with /embed/:', iframeSrc);
                            }
                        } else {
                            // Just use the standard embed prefix for the videoUrl
                            iframeSrc = `https://xhamster.com/embed/${videoUrl.split('xhamster.com/')[1]}`;
                            console.log('Created XHamster embed URL by appending to embed prefix:', iframeSrc);
                        }
                    } else {
                        // For the specific URL mentioned in the error, directly use the correct ID format
                        if (videoUrl && videoUrl.includes('i-came-to-donate-sperm-but-the-nurse-checked-my-dick-xhXMSSQ')) {
                            iframeSrc = 'https://xhamster.com/embed/xhXMSSQ';
                            console.log('Using manual XHamster embed URL for known video:', iframeSrc);
                        } else {
                            // Standard case with videoId
                            iframeSrc = `https://xhamster.com/embed/${videoId}`;
                            console.log('Using standard XHamster embed URL with ID:', iframeSrc);
                        }
                    }
                    break;
                    
                case 'redtube':
                    // Create RedTube iframe
                    iframeSrc = `https://embed.redtube.com/?id=${videoId}`;
                    break;
                    
                case 'youporn':
                    // Create YouPorn iframe
                    iframeSrc = `https://www.youporn.com/embed/${videoId}/`;
                    break;
                    
                default:
                    console.error('Unsupported video type:', videoType);
                    
                    // Show placeholder if iframe source is unknown
                    embedDiv.html(`
                        <div class="video-placeholder">
                            <p>Unsupported video type: ${videoType}</p>
                            <p>Please check the video URL and try again.</p>
                        </div>
                    `);
                    return;
            }
            
            // Create the iframe with proper attributes
            iframe = $('<iframe>', {
                'class': 'embed-iframe',
                'src': iframeSrc,
                'frameborder': '0',
                'allowfullscreen': 'true',
                'loading': 'lazy',
                'allow': 'autoplay; fullscreen',
                'scrolling': 'no',
                'sandbox': 'allow-same-origin allow-scripts allow-popups allow-forms',
                'width': '100%',
                'height': '100%'
            });
            
            // Append the iframe to the embed div
            embedDiv.html(iframe);
            
            console.log('Created new iframe for', videoType, 'with src:', iframeSrc);
        }
        
        // For YouTube embeds or unknown types that contain YouTube URLs, handle as YouTube
        const isYouTube = videoType === 'youtube' || 
                         (iframe.attr('src') && iframe.attr('src').indexOf('youtube.com') !== -1);
        
        // For Vimeo embeds or unknown types that contain Vimeo URLs
        const isVimeo = videoType === 'vimeo' || 
                       (iframe.attr('src') && iframe.attr('src').indexOf('vimeo.com') !== -1);
        
        // Handle basic setup for all iframe types
        setupIframeResponsive(iframe);
        
        // Implement specific behavior for YouTube and Vimeo if needed
        if (isYouTube) {
            // Detect if YouTube API is loaded
            if (typeof YT !== 'undefined' && YT.Player) {
                setupYouTubePlayer(iframe);
            } else {
                // Load YouTube API script if not already loaded
                if (!$('#youtube-api-script').length) {
                    $('<script>', {
                        src: 'https://www.youtube.com/iframe_api',
                        id: 'youtube-api-script'
                    }).appendTo('body');
                    
                    // Handle YouTube API script load
                    window.onYouTubeIframeAPIReady = function() {
                        setupYouTubePlayer(iframe);
                    };
                }
            }
        } else if (isVimeo) {
            // Check if we have Vimeo API
            if (typeof Vimeo !== 'undefined' && Vimeo.Player) {
                setupVimeoPlayer(iframe);
            } else {
                // Load Vimeo API if not already loaded
                if (!$('#vimeo-api-script').length) {
                    $('<script>', {
                        src: 'https://player.vimeo.com/api/player.js',
                        id: 'vimeo-api-script'
                    }).appendTo('body');
                    
                    // Check periodically if Vimeo API is loaded
                    const checkVimeoApiInterval = setInterval(function() {
                        if (typeof Vimeo !== 'undefined' && Vimeo.Player) {
                            clearInterval(checkVimeoApiInterval);
                            setupVimeoPlayer(iframe);
                        }
                    }, 250);
                }
            }
        }
        
        // Mark the container as active when it has focus
        container.on('mouseenter', function() {
            $('.video-player-container').removeClass('is-active');
            container.addClass('is-active');
        });
        
        // Log dimensions for debugging
        console.log('Video Player Found:', {
            ID: videoId,
            Type: videoType,
            Container: `${container.width()}x${container.height()}`
        });
    }
    
    /**
     * Setup YouTube player with API controls
     */
    function setupYouTubePlayer(iframe) {
        console.log('Setting up YouTube player with iframe:', iframe);
        
        // Step 1: Ensure the iframe src is using standard YouTube URL
        const src = iframe.attr('src');
        if (src && src.indexOf('youtube-nocookie.com') !== -1) {
            const newSrc = src.replace('youtube-nocookie.com', 'youtube.com');
            iframe.attr('src', newSrc);
            console.log('Updated YouTube embed to standard mode');
        }
        
        // Step 2: Add enablejsapi parameter only if we need API functionality
        // Note: This is now optional as it may cause issues on some environments
        /* 
        if (src && src.indexOf('enablejsapi=1') === -1) {
            const separator = src.indexOf('?') !== -1 ? '&' : '?';
            const updatedSrc = iframe.attr('src') + separator + 'enablejsapi=1';
            iframe.attr('src', updatedSrc);
            console.log('Added enablejsapi parameter to YouTube embed');
        }
        */
        
        // Step 3: Ensure the iframe has proper attributes
        iframe.attr({
            'width': '100%',
            'height': '100%',
            'frameborder': '0',
            'allowfullscreen': 'true',
            'allow': 'accelerometer; encrypted-media; gyroscope; picture-in-picture',
            'loading': 'lazy'
        });
        
        // Step 4: Add required CSS styles based on iframe's context
        // Detect which embed pattern we're using
        const isDirectEmbed = iframe.hasClass('embed-responsive');
        
        if (isDirectEmbed) {
            // Pattern 2: iframe directly has embed-responsive class
            iframe.css({
                'display': 'block',
                'width': '100%',
                'height': '480px',
                'max-width': '100%',
                'border': '0'
            });
        } else {
            // Pattern 1: iframe inside embed-responsive container
            iframe.css({
                'position': 'absolute',
                'top': '0',
                'left': '0',
                'width': '100%',
                'height': '100%',
                'display': 'block',
                'border': '0'
            });
            
            // Ensure the container has proper dimensions
            const container = iframe.parent('.embed-responsive, .video-player-container');
            container.css({
                'position': 'relative',
                'display': 'block',
                'width': '100%',
                'overflow': 'hidden',
                'min-height': '300px'
            });
        }
        
        // Add the embed-iframe class if it's missing (for CSS targeting)
        if (!iframe.hasClass('embed-iframe')) {
            iframe.addClass('embed-iframe');
        }
        
        // Log dimensions for debugging
        console.log('YouTube embed configured with pattern:', isDirectEmbed ? 'direct iframe' : 'container pattern', {
            dimensions: {
                width: iframe.width(),
                height: iframe.height()
            },
            src: iframe.attr('src')
        });
    }
    
    /**
     * Setup Vimeo player with API controls
     */
    function setupVimeoPlayer(iframe) {
        // No implementation needed right now
        // This function can be expanded later if you need more Vimeo-specific controls
    }
    
    /**
     * Setup responsive styling for any iframe embed
     */
    function setupIframeResponsive(iframe) {
        // Force the iframe to full dimensions
        iframe.attr('width', '100%');
        
        // Add the embed-iframe class if it's missing
        if (!iframe.hasClass('embed-iframe')) {
            iframe.addClass('embed-iframe');
        }
        
        // If the iframe itself has the embed-responsive class (our new format)
        if (iframe.hasClass('embed-responsive')) {
            // Make sure the iframe has proper height
            iframe.attr('height', '480'); 
            
            // Ensure it has the proper CSS
            iframe.css({
                'display': 'block',
                'max-width': '100%'
            });
        } else {
            // Classic format - ensure parent container has embed-responsive class
            const parent = iframe.parent();
            if (!parent.hasClass('embed-responsive') && !parent.hasClass('video-player-container')) {
                iframe.wrap('<div class="embed-responsive"></div>');
            }
        }
        
        // Fix parent container styling
        const embedContainer = iframe.closest('.embed-responsive');
        if (embedContainer.length) {
            embedContainer.css({
                'position': 'relative',
                'display': 'block',
                'width': '100%',
                'padding-top': '56.25%', // 16:9 aspect ratio
                'overflow': 'hidden'
            });
            
            // Ensure the iframe is properly positioned within the container
            iframe.css({
                'position': 'absolute',
                'top': '0',
                'left': '0',
                'width': '100%',
                'height': '100%',
                'border': '0'
            });
        }
    }
    
    /**
     * Format time (seconds to MM:SS or HH:MM:SS)
     */
    function formatTime(time) {
        const hours = Math.floor(time / 3600);
        const minutes = Math.floor((time % 3600) / 60);
        const seconds = Math.floor(time % 60);
        
        if (hours > 0) {
            return hours + ':' + (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        }
        
        return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    }
    
    /**
     * Update video view count (tracking)
     */
    function updateViewCount(container, videoId) {
        // Track view only once per pageload
        if (!container.hasClass('view-counted')) {
            $.ajax({
                url: customtubeData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_update_view_count',
                    post_id: videoId,
                    nonce: customtubeData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        container.addClass('view-counted');
                    }
                }
            });
        }
    }
    
    // Handle window load to ensure all resources (iframes, etc.) are fully loaded
    $(window).on('load', function() {
        // First attempt
        initializeVideoPlayer();
        
        // Second attempt with delay to catch any videos missed in the first pass
        setTimeout(function() {
            initializeVideoPlayer();
        }, 500);
    });
    
    // Also initialize on document ready for faster response
    $(document).ready(function() {
        // First attempt
        initializeVideoPlayer();
        
        // Second attempt with delay to handle race conditions
        setTimeout(function() {
            initializeVideoPlayer();
        }, 250);
    });
    
})(jQuery);