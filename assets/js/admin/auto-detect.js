/**
 * CustomTube Auto-Detect URL Module
 * Handles automatic URL detection and field population for video posts
 */
(function($) {
    'use strict';
    
    // Main auto-detect object
    var CustomTubeAutoDetect = {
        // Initialize the module
        init: function() {
            console.log('Initializing CustomTube Auto-Detect');
            this.bindEvents();
            this.setupCache();
        },
        
        // Setup local storage cache
        setupCache: function() {
            // Initialize localStorage for saving form data as backup
            if (window.localStorage && window.location.href.includes('post-new.php')) {
                try {
                    var savedFormData = localStorage.getItem('customtube_pending_video');
                    if (savedFormData) {
                        console.log('Found saved form data in localStorage');
                        var formData = JSON.parse(savedFormData);
                        
                        // Check if the form is empty (indicating a fresh page load)
                        var formIsEmpty = !this.getPostTitle() && !$('#video_url').val() && !$('#embed_code').val();
                        
                        if (formIsEmpty) {
                            console.log('Form is empty, restoring saved data:', formData);
                            
                            // Restore form fields
                            if (formData.title) this.setPostTitle(formData.title);
                            if (formData.video_duration) $('#video_duration').val(formData.video_duration);
                            if (formData.duration_seconds) $('#duration_seconds').val(formData.duration_seconds);
                            if (formData.video_width) $('#video_width').val(formData.video_width);
                            if (formData.video_height) $('#video_height').val(formData.video_height);
                            if (formData.video_source) $('#video_source').val(formData.video_source);
                            
                            // Set the correct source type
                            if (formData.video_url && !formData.embed_code) {
                                $('input[name="video_source_type"][value="direct"]').prop('checked', true);
                                $('#video_url').val(formData.video_url);
                                $('#direct-video-options').show();
                                $('#embed-video-options').hide();
                            } else if (formData.embed_code) {
                                $('input[name="video_source_type"][value="embed"]').prop('checked', true);
                                $('#embed_code').val(formData.embed_code);
                                $('#direct-video-options').hide();
                                $('#embed-video-options').show();
                            }
                            
                            // Set content if we have tinyMCE
                            if (formData.content && window.tinyMCE && tinyMCE.activeEditor) {
                                tinyMCE.activeEditor.setContent(formData.content);
                            }
                            
                            // Add a notice about restored data
                            $('#titlediv').before('<div class="notice notice-info" style="padding: 10px; margin: 20px 0;"><p><strong>Your previous video data has been restored!</strong> Click the "Publish" button to save this video with all its metadata.</p></div>');
                            
                            // Clear the storage since we've restored it
                            localStorage.removeItem('customtube_pending_video');
                        }
                    }
                } catch (e) {
                    console.error('Error restoring form data from localStorage', e);
                }
            }
        },
        
        // Get the post title - works with both Classic and Block Editor
        getPostTitle: function() {
            // If the classic editor is present 
            if ($('#title').length) {
                return $('#title').val();
            }
            
            // For Block Editor (Gutenberg)
            var titleElement = $('.editor-post-title__input, .wp-block-post-title');
            if (titleElement.length) {
                return titleElement.text();
            }
            
            return '';
        },
        
        // Set the post title - works with both Classic and Block Editor
        setPostTitle: function(title) {
            console.log('Setting post title to: ' + title);
            
            // Track if any title element was found
            let titleElementFound = false;
            
            // If the classic editor is present
            if ($('#title').length) {
                $('#title').val(title);
                $('#title-prompt-text').addClass('screen-reader-text');
                console.log('Title set in Classic Editor');
                titleElementFound = true;
            }
            
            // For Block Editor (Gutenberg) - try multiple selectors
            const possibleSelectors = [
                '.editor-post-title__input', 
                '.wp-block-post-title', 
                '.editor-post-title .editor-post-title__input',
                '[data-type="core/post-title"] .block-editor-rich-text__editable',
                '.edit-post-visual-editor__post-title-wrapper .editor-post-title__input'
            ];
            
            // Try each possible selector
            possibleSelectors.forEach(selector => {
                const elements = $(selector);
                if (elements.length) {
                    elements.each(function() {
                        // Update value and text content to cover all bases
                        $(this).val(title);
                        $(this).text(title);
                        
                        // Trigger events to make sure Gutenberg notices the change
                        const events = ['input', 'change', 'keyup', 'blur'];
                        events.forEach(eventType => {
                            const event = new Event(eventType, {bubbles: true});
                            this.dispatchEvent(event);
                        });
                    });
                    
                    console.log('Title set in Block Editor using selector: ' + selector);
                    titleElementFound = true;
                }
            });
            
            // Always update via WP Data API (most reliable method)
            if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                try {
                    // Check for different store versions (WordPress 5.0+ vs 5.8+)
                    if (wp.data.dispatch('core/editor')) {
                        wp.data.dispatch('core/editor').editPost({ title: title });
                        console.log('Updated title via wp.data.dispatch(core/editor)');
                        titleElementFound = true;
                    } else if (wp.data.dispatch('core')) {
                        wp.data.dispatch('core').editPost({ title: title });
                        console.log('Updated title via wp.data.dispatch(core)');
                        titleElementFound = true;
                    }
                } catch (e) {
                    console.error('Error updating title via wp.data:', e);
                }
            }
            
            // If no title element was found, create a fallback solution
            if (!titleElementFound) {
                console.warn('Could not find title element to set title directly: ' + title);
                
                // Last resort: Try to find any elements with "title" in their name or class
                const potentialTitleElements = $('input[name*="title"], textarea[name*="title"], div[class*="title"], h1, h2, .editor-post-title');
                
                if (potentialTitleElements.length) {
                    potentialTitleElements.each(function() {
                        // Skip elements that are clearly not the title
                        if ($(this).hasClass('screen-reader-text') || 
                            $(this).attr('id') === 'title-prompt-text' ||
                            $(this).css('display') === 'none') {
                            return;
                        }
                        
                        // Try to update
                        $(this).val(title);
                        $(this).text(title);
                        console.log('Attempted to update potential title element:', this);
                    });
                }
            }
            
            // Always do the AJAX update as a failsafe
            this.saveTitleViaAjax(title);
        },
        
        // New method to save title via AJAX
        saveTitleViaAjax: function(title) {
            var postId = $('#post_ID').val();
            
            console.log('Saving title via AJAX for post ID:', postId);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_save_video_metadata',
                    post_id: postId,
                    metadata: {
                        'post_title': title
                    },
                    nonce: customtube_auto_detect.nonce_metadata,
                    force_title_update: true,
                    debug_info: JSON.stringify({
                        'source': 'saveTitleViaAjax',
                        'editor_type': 'block',
                        'title_length': title.length,
                        'timestamp': new Date().toISOString()
                    })
                },
                success: function(response) {
                    console.log('Title saved directly via AJAX:', response);
                    
                    // Show feedback that the title was saved
                    $('#auto-detect-result').append('<p style="color: #00a32a;">Title saved to database. You may need to refresh to see it in the editor.</p>');
                },
                error: function(xhr, status, error) {
                    console.error('Failed to save title via AJAX:', error);
                }
            });
        },
        
        // Bind events
        bindEvents: function() {
            var self = this;
            
            // First, remove any existing event handlers to avoid duplicates
            $(document).off('paste', '#auto_detect_url');
            $(document).off('input', '#auto_detect_url');
            
            // Use event delegation to handle events on dynamically added elements
            // This is more reliable for elements that might be added/removed from the DOM
            
            // Auto-detect on paste in the URL field
            $(document).on('paste', '#auto_detect_url', function(e) {
                console.log('Paste event triggered on auto_detect_url');
                // Get pasted content
                var pastedUrl;
                if (e.originalEvent.clipboardData) {
                    pastedUrl = e.originalEvent.clipboardData.getData('text');
                } else if (window.clipboardData) {
                    pastedUrl = window.clipboardData.getData('Text');
                }
                
                if (pastedUrl) {
                    console.log('Processing pasted URL:', pastedUrl);
                    $('#auto-detect-result').html('<p>Processing URL...</p>');
                    setTimeout(function() {
                        self.detectUrl(pastedUrl);
                    }, 100); // Short timeout to ensure field is updated
                }
            });
            
            // Also trigger on input change for manual typing or for browsers that don't support paste event
            $(document).on('input', '#auto_detect_url', function() {
                var url = $(this).val();
                console.log('Input event triggered on auto_detect_url. Value:', url);
                
                if (url && url.length > 10 && (url.indexOf('http') === 0 || url.indexOf('www.') === 0)) {
                    // Show processing message
                    $('#auto-detect-result').html('<p>Processing URL...</p>');
                    
                    // Debounce to avoid multiple triggers
                    clearTimeout($(this).data('timer'));
                    $(this).data('timer', setTimeout(function() {
                        self.detectUrl(url);
                    }, 500));
                }
            });
            
            // Also add a direct submit handler for browsers that might miss the other events
            $(document).on('keydown', '#auto_detect_url', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var url = $(this).val();
                    if (url && url.length > 10) {
                        console.log('Enter key pressed, processing URL:', url);
                        $('#auto-detect-result').html('<p>Processing URL...</p>');
                        self.detectUrl(url);
                    }
                }
            });
            
            // Debug info
            console.log('CustomTube Auto-Detect events bound');
            console.log('Auto-detect URL input found:', $('#auto_detect_url').length > 0);
            console.log('Auto-detect URL container found:', $('.auto-detect-url-container').length > 0);
        },
        
        // Extract YouTube video ID from a URL
        getYoutubeVideoId: function(url) {
            var videoId = '';
            
            // Try various regex patterns for different YouTube URL formats
            var patterns = [
                /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/,
                /[?&]v=([^&]{11})/,
                /youtu\.be\/([^?&\/\s]{11})/
            ];
            
            for (var i = 0; i < patterns.length; i++) {
                var match = url.match(patterns[i]);
                if (match && match[1]) {
                    videoId = match[1];
                    break;
                }
            }
            
            return videoId;
        },
        
        // Detect URL and populate fields
        detectUrl: function(url) {
            if (!url) return;
            
            var self = this;
            
            $('#auto-detect-spinner').css('visibility', 'visible');
            $('#auto-detect-result').empty();
            
            // Pre-extract YouTube video ID if applicable
            var isYoutubeUrl = url.indexOf('youtube.com') !== -1 || url.indexOf('youtu.be') !== -1;
            var youtubeVideoId = isYoutubeUrl ? this.getYoutubeVideoId(url) : '';
            
            console.log('Auto-detecting URL: ' + url + (isYoutubeUrl ? ' (YouTube ID: ' + youtubeVideoId + ')' : ''));
            
            // Make the AJAX request to detect the URL
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_auto_detect_url',
                    url: url,
                    post_id: $('#post_ID').val(),
                    nonce: customtube_auto_detect.nonce,
                    youtube_video_id: youtubeVideoId,
                    auto_process: true
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        console.log('URL detection successful. Response data:', data);
                        
                        // Process the successful response
                        self.processSuccessfulResponse(data, url, isYoutubeUrl, youtubeVideoId);
                    } else {
                        // Show error message
                        $('#auto-detect-result').html('<p style="color: #d63638;">' + 
                            (response.data.message || customtube_auto_detect.labels.error) + '</p>');
                        console.error('URL detection failed:', response);
                    }
                    
                    $('#auto-detect-spinner').css('visibility', 'hidden');
                },
                error: function(xhr, status, error) {
                    // Show server error message
                    $('#auto-detect-result').html('<p style="color: #d63638;">' + 
                        customtube_auto_detect.labels.server_error + '</p>');
                    $('#auto-detect-spinner').css('visibility', 'hidden');
                    console.error('AJAX error:', status, error);
                }
            });
        },
        
        // Process successful URL detection response
        processSuccessfulResponse: function(data, url, isYoutubeUrl, youtubeVideoId) {
            var self = this;
            
            // If this is a YouTube video and we don't have a title, try to scrape it directly
            if (isYoutubeUrl && youtubeVideoId && (!data.title || data.title === 'Video from Youtube' || 
                data.title === 'YouTube Video: ' + youtubeVideoId)) {
                
                this.getYouTubeMetadata(data, youtubeVideoId, url);
            }
            
            // Select the right source type radio
            $('input[name="video_source_type"][value="' + data.source_type + '"]').prop('checked', true).trigger('change');
            
            // Populate the appropriate field based on source type
            this.populateSourceFields(data, url);
            
            // Populate metadata fields
            this.populateMetadataFields(data);
            
            // Extract and set thumbnail
            if (data.thumbnail_url) {
                this.setThumbnail(data, url);
            }
            
            // For YouTube videos, get more accurate data
            if (data.platform === 'youtube' && data.video_id) {
                this.processYouTubeVideo(data, url);
            }
            
            // Auto-populate post content/description if available
            if (data.description && window.tinyMCE && tinyMCE.activeEditor && tinyMCE.activeEditor.getContent() === '') {
                tinyMCE.activeEditor.setContent(data.description);
            }
            
            // Show success message with details of what was populated
            this.showSuccessMessage(data);
            
            // Save form data to localStorage as backup
            this.saveFormDataToLocalStorage(data);
        },
        
        // Set video source fields (direct URL or embed code)
        populateSourceFields: function(data, url) {
            // Ensure URL uses HTTPS
            var secureUrl = url.replace(/http:/g, 'https:');
            
            if (data.source_type === 'direct' && data.is_direct_video) {
                // For direct videos, populate the direct URL field
                $('#video_url').val(secureUrl);
                
                // Show direct options, hide embed options
                $('#direct-video-options').show();
                $('#embed-video-options').hide();
            } else if (data.platform === 'youtube' || data.platform === 'vimeo') {
                // For YouTube/Vimeo videos, allow them to be used in both direct URL and embed modes
                
                // First set the URL in the video_url field for direct usage
                if (data.direct_url) {
                    // Use the clean direct URL format if available
                    $('#video_url').val(data.direct_url);
                } else if (data.platform === 'youtube' && data.video_id) {
                    $('#video_url').val('https://www.youtube.com/watch?v=' + data.video_id);
                } else if (data.embed_url) {
                    $('#video_url').val(secureUrl);
                } else {
                    $('#video_url').val(secureUrl);
                }
                
                // Also create the embed code for embed usage
                if (data.embed_code) {
                    // Ensure embed code uses HTTPS, not HTTP
                    var secureEmbedCode = data.embed_code.replace(/http:/g, 'https:');
                    
                    // Fix YouTube embeds to use the proper iframe format
                    if (data.platform === 'youtube' && data.video_id && 
                        (secureEmbedCode.indexOf('<div') >= 0 || secureEmbedCode.indexOf('embed-responsive') >= 0)) {
                        secureEmbedCode = '<iframe class="embed-responsive" width="100%" height="480" ' + 
                                         'src="https://www.youtube.com/embed/' + data.video_id + 
                                         '?enablejsapi=1&rel=0&showinfo=0" frameborder="0" ' +
                                         'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" ' +
                                         'allowfullscreen></iframe>';
                        console.log('Fixed YouTube embed format to proper iframe');
                    }
                    
                    $('#embed_code').val(secureEmbedCode);
                } else if (data.embed_url) {
                    // Generate basic iframe if we have an embed URL but no embed code
                    var iframeEmbed = '<iframe class="embed-responsive" width="100%" height="480" ' + 
                                     'src="' + data.embed_url + '" frameborder="0" ' +
                                     'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" ' +
                                     'allowfullscreen></iframe>';
                    $('#embed_code').val(iframeEmbed);
                } else {
                    $('#embed_code').val(secureUrl);
                }
                
                // Since we support both modes for YouTube/Vimeo, select direct URL by default
                $('input[name="video_source_type"][value="direct"]').prop('checked', true);
                
                // Show direct options, hide embed options
                $('#direct-video-options').show();
                $('#embed-video-options').hide();
                
                console.log('Set up video URL and embed code for platform: ' + data.platform);
            } else {
                // For other platforms, populate the embed code field
                if (data.embed_code) {
                    // Ensure embed code uses HTTPS, not HTTP
                    var secureEmbedCode = data.embed_code.replace(/http:/g, 'https:');
                    $('#embed_code').val(secureEmbedCode);
                } else {
                    $('#embed_code').val(secureUrl);
                }
                
                // Show embed options, hide direct options
                $('#direct-video-options').hide();
                $('#embed-video-options').show();
            }
        },
        
        // Populate metadata fields
        populateMetadataFields: function(data) {
            // Duration
            if (data.duration) {
                $('#video_duration').val(data.duration);
            }
            if (data.duration_seconds) {
                $('#duration_seconds').val(data.duration_seconds);
            }
            
            // Dimensions
            if (data.width) {
                $('#video_width').val(data.width);
            }
            if (data.height) {
                $('#video_height').val(data.height);
            }
            
            // File size
            if (data.filesize) {
                $('#video_filesize').val(data.filesize);
            }
            
            // Source/platform
            if (data.platform) {
                $('#video_source').val(data.platform);
            }
            
            // Source URL
            if (data.embed_url) {
                $('#video_source_url').val(data.embed_url);
            } else {
                $('#video_source_url').val(data.url || url);
            }
            
            // Title - auto-populate post title
            if (data.title) {
                this.setPostTitle(data.title);
                console.log('Set title to: ' + data.title);
                
                // Add visual indication that title was updated
                $('#auto-detect-result').append(
                    '<p style="color: #00a32a;">Title set to: "' + data.title + '"</p>'
                );
                
                // Add specially for Block Editor
                if ($('.wp-block').length) {
                    $('#auto-detect-result').append(
                        '<p style="color: #00a32a;">' +
                        'Note: If the title doesn\'t appear in the editor, it has still been saved to the database. ' +
                        'You can continue adding other metadata.</p>'
                    );
                }
            } else {
                // If no title is detected, create a default title based on the platform
                var defaultTitle = 'Video from ' + (data.platform.charAt(0).toUpperCase() + data.platform.slice(1));
                this.setPostTitle(defaultTitle);
                console.log('Set default title: ' + defaultTitle);
                
                // Add visual indication of default title
                $('#auto-detect-result').append(
                    '<p style="color: #FFA500;">Default title set: "' + defaultTitle + '"</p>'
                );
            }
        },
        
        // Set thumbnail
        setThumbnail: function(data, url) {
            var self = this; // Store reference to CustomTubeAutoDetect
            console.log('Setting thumbnail from URL: ' + data.thumbnail_url);
            
            // Auto-set the thumbnail without requiring a click
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_auto_set_thumbnail',
                    post_id: $('#post_ID').val(),
                    thumbnail_url: data.thumbnail_url,
                    nonce: customtube_auto_detect.nonce_thumbnail,
                    auto_populate_categories: true,
                    auto_populate_tags: true,
                    url: url
                },
                success: function(thumbnailResponse) {
                    if (thumbnailResponse.success) {
                        $('#extract-thumbnail-result').html('<p style="color: #00a32a;">' + 
                            customtube_auto_detect.labels.thumbnail_set + '</p>');
                        
                        // Call our helper function to update the UI
                        if (typeof self.updateFeaturedImage === 'function') {
                            console.log('Updating featured image UI');
                            self.updateFeaturedImage(
                                thumbnailResponse.data.thumbnail_url, 
                                thumbnailResponse.data.attachment_id
                            );
                        } else {
                            console.warn('updateFeaturedImage function not available');
                        }
                        
                        // Show information about auto-populated categories and tags
                        var populated = [];
                        
                        if (thumbnailResponse.data.categories && thumbnailResponse.data.categories.length) {
                            populated.push('categories');
                        }
                        
                        if (thumbnailResponse.data.genres && thumbnailResponse.data.genres.length) {
                            populated.push('genres');
                        }
                        
                        if (thumbnailResponse.data.tags && thumbnailResponse.data.tags.length) {
                            populated.push('tags');
                        }
                        
                        if (populated.length) {
                            $('#extract-thumbnail-result').append('<p style="color: #00a32a;">' +
                                customtube_auto_detect.labels.auto_populated + ' ' + populated.join(', ') + '</p>');
                        }
                    } else {
                        $('#extract-thumbnail-result').html('<p style="color: #ffa500;">' + 
                            customtube_auto_detect.labels.thumbnail_error + '</p>');
                        
                        // If there's a manual button, show a message to use it
                        if ($('#extract-thumbnail-button').length) {
                            $('#extract-thumbnail-result').append('<p>' + 
                                customtube_auto_detect.labels.thumbnail_manual + '</p>');
                        }
                    }
                },
                error: function() {
                    $('#extract-thumbnail-result').html('<p style="color: #ffa500;">' + 
                        customtube_auto_detect.labels.thumbnail_server_error + '</p>');
                }
            });
        },
        
        // Process YouTube video for additional metadata
        processYouTubeVideo: function(data, url) {
            var self = this;
            console.log('Getting accurate YouTube metadata for video ID: ' + data.video_id);
            
            // Use the direct scrape AJAX handler
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_direct_scrape_youtube',
                    video_id: data.video_id,
                    url: 'https://www.youtube.com/watch?v=' + data.video_id,
                    post_id: $('#post_ID').val(),
                    get_title: true,
                    auto_populate_taxonomies: true,
                    nonce: customtube_auto_detect.nonce_scrape
                },
                success: function(metadataResponse) {
                    console.log('YouTube metadata response:', metadataResponse);
                    
                    // Update title if available and not generic
                    if (metadataResponse.success && metadataResponse.data && metadataResponse.data.title) {
                        var youtubeTitle = metadataResponse.data.title;
                        console.log('Got YouTube title: ' + youtubeTitle);
                        
                        // Check if the title is a generic one (contains "YouTube Video:" or just the video ID)
                        if (youtubeTitle.indexOf('YouTube Video:') === 0 || 
                            youtubeTitle === 'Video from Youtube' || 
                            youtubeTitle === data.video_id) {
                            console.log('Received generic YouTube title, keeping original: ' + data.title);
                            $('#auto-detect-result').append(
                                '<p style="color: #ffa500;">Received generic YouTube metadata, keeping original title "' + 
                                data.title + '"</p>'
                            );
                        } else {
                            // Only update if we have a meaningful title
                            console.log('Setting meaningful YouTube title: ' + youtubeTitle);
                            
                            // Update title using our helper function that works with both editors
                            self.setPostTitle(youtubeTitle);
                            
                            // Update the UI to show the title update
                            $('#auto-detect-result').append(
                                '<p style="color: #00a32a;">Updated title to: "' + youtubeTitle + '"</p>'
                            );
                            
                            // Save title to database
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'customtube_save_video_metadata',
                                    post_id: $('#post_ID').val(),
                                    metadata: {
                                        'post_title': youtubeTitle
                                    },
                                    nonce: customtube_auto_detect.nonce_metadata,
                                    debug_info: JSON.stringify({
                                        'source': 'processYouTubeVideo',
                                        'editor_type': $('.wp-block').length ? 'block' : 'classic'
                                    })
                                },
                                success: function(saveResponse) {
                                    console.log('Title saved to database:', saveResponse);
                                }
                            });
                        }
                    }
                    
                    // Update duration if available
                    if (metadataResponse.success && metadataResponse.data) {
                        if (metadataResponse.data.duration && 
                            metadataResponse.data.duration_seconds) {
                            
                            // Update the duration fields
                            $('#video_duration').val(metadataResponse.data.duration);
                            $('#duration_seconds').val(metadataResponse.data.duration_seconds);
                            
                            console.log('Updated duration: ' + metadataResponse.data.duration + 
                                ' (' + metadataResponse.data.duration_seconds + 's)');
                            
                            // Save duration to database
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'customtube_save_video_metadata',
                                    post_id: $('#post_ID').val(),
                                    metadata: {
                                        'video_duration': metadataResponse.data.duration,
                                        'duration_seconds': metadataResponse.data.duration_seconds
                                    },
                                    nonce: customtube_auto_detect.nonce_metadata
                                }
                            });
                        }
                        
                        // Show information about any populated taxonomies
                        var taxonomyInfo = [];
                        
                        // Check for categories
                        if (metadataResponse.data.categories && metadataResponse.data.categories.length) {
                            taxonomyInfo.push('categories (' + metadataResponse.data.categories.join(', ') + ')');
                        }
                        
                        // Check for tags
                        if (metadataResponse.data.tags && metadataResponse.data.tags.length) {
                            // Limit the display to first 5 tags to avoid overwhelming the UI
                            var displayTags = metadataResponse.data.tags.slice(0, 5);
                            var tagCount = metadataResponse.data.tags.length;
                            taxonomyInfo.push('tags (' + displayTags.join(', ') + 
                                (tagCount > 5 ? ' and ' + (tagCount - 5) + ' more' : '') + ')');
                        }
                        
                        // Check for performers (channel)
                        if (metadataResponse.data.performers && metadataResponse.data.performers.length) {
                            taxonomyInfo.push('performers (' + metadataResponse.data.performers.join(', ') + ')');
                        }
                        
                        // Display taxonomy information if available
                        if (taxonomyInfo.length > 0) {
                            $('#auto-detect-result').append(
                                '<p style="color: #00a32a;">Auto-populated: ' + taxonomyInfo.join(', ') + '</p>'
                            );
                        }
                    }
                }
            });
        },
        
        // Get YouTube metadata using direct scraping
        getYouTubeMetadata: function(data, youtubeVideoId, url) {
            var self = this;
            console.log('Getting YouTube title via direct scraping');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_direct_scrape_youtube',
                    video_id: youtubeVideoId,
                    url: url,
                    post_id: $('#post_ID').val(),
                    get_title: true,
                    auto_populate_taxonomies: true,
                    nonce: customtube_auto_detect.nonce_scrape
                },
                success: function(response) {
                    if (response.success && response.data && response.data.title) {
                        var scrapedTitle = response.data.title;
                        
                        // Check if it's a generic title like "YouTube Video: ID"
                        if (scrapedTitle.indexOf('YouTube Video:') === 0 || 
                            scrapedTitle === 'Video from Youtube' || 
                            scrapedTitle === youtubeVideoId) {
                            console.log('Received generic YouTube title, keeping original: ' + data.title);
                            $('#auto-detect-result').append(
                                '<p style="color: #ffa500;">Received generic YouTube metadata, keeping original title "' + 
                                data.title + '"</p>'
                            );
                        } else {
                            console.log('Setting meaningful YouTube title from scraping: ' + scrapedTitle);
                            data.title = scrapedTitle;
                            self.setPostTitle(data.title);
                            
                            // Update the UI to reflect the change
                            $('#auto-detect-result').append(
                                '<p style="color: #00a32a;">Updated title to: "' + scrapedTitle + '"</p>'
                            );
                            
                            // Save title to database
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'customtube_save_video_metadata',
                                    post_id: $('#post_ID').val(),
                                    metadata: {
                                        'post_title': data.title
                                    },
                                    nonce: customtube_auto_detect.nonce_metadata,
                                    debug_info: JSON.stringify({
                                        'source': 'getYouTubeMetadata',
                                        'editor_type': $('.wp-block').length ? 'block' : 'classic'
                                    })
                                }
                            });
                        }
                        
                        // Show information about any populated taxonomies
                        var taxonomyInfo = [];
                        
                        // Check for categories
                        if (response.data.categories && response.data.categories.length) {
                            taxonomyInfo.push('categories (' + response.data.categories.join(', ') + ')');
                        }
                        
                        // Check for tags
                        if (response.data.tags && response.data.tags.length) {
                            // Limit the display to first 5 tags to avoid overwhelming the UI
                            var displayTags = response.data.tags.slice(0, 5);
                            var tagCount = response.data.tags.length;
                            taxonomyInfo.push('tags (' + displayTags.join(', ') + 
                                (tagCount > 5 ? ' and ' + (tagCount - 5) + ' more' : '') + ')');
                        }
                        
                        // Check for performers (channel)
                        if (response.data.performers && response.data.performers.length) {
                            taxonomyInfo.push('performers (' + response.data.performers.join(', ') + ')');
                        }
                        
                        // Display taxonomy information if available
                        if (taxonomyInfo.length > 0) {
                            $('#auto-detect-result').append(
                                '<p style="color: #00a32a;">Auto-populated: ' + taxonomyInfo.join(', ') + '</p>'
                            );
                        }
                    }
                }
            });
        },
        
        // Show success message with populated fields
        showSuccessMessage: function(data) {
            var populatedFields = [];
            if (data.title) populatedFields.push('title');
            if (data.description) populatedFields.push('description');
            if (data.duration) populatedFields.push('duration');
            if (data.thumbnail_url) populatedFields.push('thumbnail');
            
            var successMsg = customtube_auto_detect.labels.success;
            if (populatedFields.length > 0) {
                successMsg += ' ' + customtube_auto_detect.labels.auto_populated + ' ' + populatedFields.join(', ');
            }
            
            $('#auto-detect-result').html('<p style="color: #00a32a;">' + successMsg + '</p>');
        },
        
        // Save form data to localStorage as backup
        saveFormDataToLocalStorage: function(data) {
            try {
                var formData = {
                    'video_duration': $('#video_duration').val(),
                    'duration_seconds': $('#duration_seconds').val(),
                    'video_width': $('#video_width').val(),
                    'video_height': $('#video_height').val(),
                    'video_source': $('#video_source').val(),
                    'video_url': $('#video_url').val(),
                    'embed_code': $('#embed_code').val(),
                    'title': this.getPostTitle(),
                    'content': window.tinyMCE && tinyMCE.activeEditor ? tinyMCE.activeEditor.getContent() : '',
                    'thumbnail_url': data.thumbnail_url
                };
                
                localStorage.setItem('customtube_pending_video', JSON.stringify(formData));
                console.log('Saved form data to localStorage as backup');
            } catch (e) {
                console.error('Failed to save data to localStorage', e);
            }
        },
        
        // Update featured image in both Classic and Block Editor
        updateFeaturedImage: function(thumbnailUrl, attachmentId) {
            console.log('Updating featured image UI with:', thumbnailUrl, attachmentId);
            
            // For Classic Editor
            if ($('#postimagediv').length) {
                $('#set-post-thumbnail').html('<img src="' + thumbnailUrl + '" alt="Featured Image" />').attr('data-thumbnail-id', attachmentId);
                $('#remove-post-thumbnail').show();
                $('#postimagediv').removeClass('postbox-hidden');
                $('#postimagediv .inside').addClass('has-post-thumbnail');
                $('#postimagediv .hndle span').text('Featured Image: Set');
                console.log('Updated Classic Editor featured image');
            }
            
            // For Block Editor (Gutenberg)
            if ($('.editor-post-featured-image').length) {
                // Find or create preview element
                let previewEl = $('.editor-post-featured-image__preview');
                
                if (previewEl.length === 0) {
                    $('.editor-post-featured-image').append('<div class="editor-post-featured-image__preview"></div>');
                    previewEl = $('.editor-post-featured-image__preview');
                }
                
                // Set the image
                previewEl.html('<img src="' + thumbnailUrl + '" alt="Featured Image" />');
                
                // Add necessary classes
                $('.editor-post-featured-image').removeClass('is-loading').addClass('has-image');
                
                // Update via WP Data API
                if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                    try {
                        wp.data.dispatch('core/editor').editPost({ featured_media: attachmentId });
                        console.log('Updated featured image in Block Editor data store');
                    } catch (e) {
                        console.error('Error updating Block Editor featured image:', e);
                    }
                }
                
                console.log('Updated Block Editor featured image UI');
            }
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        CustomTubeAutoDetect.init();
        
        // Log the auto-detect URL input element
        console.log('Auto-detect URL input found:', $('#auto_detect_url').length > 0);
        console.log('Auto-detect URL container found:', $('.auto-detect-url-container').length > 0);
        
        // Special handling for block editor
        if ($('.wp-block').length || $('.block-editor').length) {
            console.log('Block Editor (Gutenberg) detected');
            
            // For Gutenberg, we need to handle UI initialization differently
            // The main auto-detect UI will be injected by our JavaScript code
            
            // Set up observers to handle delayed initialization
            // This helps if UI is loaded after our script runs
            var attemptCount = 0;
            var maxAttempts = 5;
            
            // Check and rebind at regular intervals
            var checkInterval = setInterval(function() {
                attemptCount++;
                
                if ($('#auto_detect_url').length) {
                    console.log('Auto-detect URL input found, rebinding events');
                    CustomTubeAutoDetect.bindEvents();
                    clearInterval(checkInterval);
                } else if (attemptCount >= maxAttempts) {
                    console.warn('Auto-detect URL input not found after ' + maxAttempts + ' attempts');
                    clearInterval(checkInterval);
                } else {
                    console.log('Waiting for auto-detect URL input to be ready (attempt ' + attemptCount + ')');
                }
            }, 1000);
            
            // Also listen for DOM changes to catch when our UI might get added
            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes && mutation.addedNodes.length) {
                            // If our box was added, rebind events
                            if ($('#auto_detect_url').length && !$('#auto_detect_url').data('events-bound')) {
                                console.log('Detected auto-detect URL input added to DOM, binding events');
                                CustomTubeAutoDetect.bindEvents();
                                $('#auto_detect_url').data('events-bound', true);
                            }
                        }
                    });
                });
                
                // Start observing the document body for DOM changes
                observer.observe(document.body, { childList: true, subtree: true });
            }
        }
    });
    
})(jQuery);