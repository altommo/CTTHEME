/**
 * Embed Overlay Handler
 * Intercepts clicks on embeds to prevent users from navigating away to external sites
 */
(function($) {
    'use strict';
    
    // Create the lightbox container if it doesn't exist
    function ensureLightboxExists() {
        if ($('#direct-player-lightbox').length === 0) {
            $('body').append(
                '<div id="direct-player-lightbox" class="direct-player-lightbox" style="display:none;">' +
                '<div class="direct-player-container">' +
                '<button class="direct-player-close">&times;</button>' +
                '<video id="direct-player-video" class="direct-player-video" controls autoplay></video>' +
                '</div>' +
                '</div>'
            );
            
            // Add close button handler
            $('.direct-player-close').on('click', function() {
                $('#direct-player-lightbox').fadeOut();
                $('#direct-player-video').get(0).pause();
                $('#direct-player-video').removeAttr('src');
            });
            
            // Close on ESC key or clicking background
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#direct-player-lightbox').is(':visible')) {
                    $('.direct-player-close').click();
                }
            });
            
            $('#direct-player-lightbox').on('click', function(e) {
                if (e.target === this) {
                    $('.direct-player-close').click();
                }
            });
        }
    }
    
    // Handle click on embed overlay
    function handleOverlayClick() {
        $('.embed-overlay').on('click', function(e) {
            e.preventDefault();
            
            const videoId = $(this).data('video-id');
            const container = $(this).closest('.embed-container');
            
            // Show loading indicator
            $(this).addClass('loading');
            
            // Ensure lightbox exists
            ensureLightboxExists();
            
            // Make AJAX request to get direct URL
            $.ajax({
                url: customtubeData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_extract_direct_url',
                    url: container.find('iframe').attr('src') || '',
                    platform: 'xhamster', // For now we only support XHamster
                    post_id: videoId,
                    nonce: customtubeData.nonce
                },
                success: function(response) {
                    if (response.success && response.data && response.data.direct_url) {
                        // Set the direct URL and thumbnail
                        const directUrl = response.data.direct_url;
                        const thumbnailUrl = response.data.thumbnail_url || '';
                        
                        // Update the video player
                        const video = $('#direct-player-video').get(0);
                        if (thumbnailUrl) {
                            $('#direct-player-video').attr('poster', thumbnailUrl);
                        }
                        $('#direct-player-video').attr('src', directUrl);
                        
                        // Show the lightbox
                        $('#direct-player-lightbox').fadeIn();
                        
                        // Try to autoplay
                        if (video) {
                            video.play().catch(function(error) {
                                console.warn('Autoplay prevented:', error);
                            });
                        }
                    } else {
                        console.error('Failed to get direct URL:', response);
                        // Fallback to playing the iframe if we failed to get the direct URL
                        alert('Sorry, we couldn\'t extract the direct video. Please try again later.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    alert('Sorry, there was an error processing your request. Please try again later.');
                },
                complete: function() {
                    // Remove loading indicator
                    $('.embed-overlay').removeClass('loading');
                }
            });
        });
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        ensureLightboxExists();
        handleOverlayClick();
    });
    
})(jQuery);