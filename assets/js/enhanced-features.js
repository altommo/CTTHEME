/**
 * Enhanced features for CustomTube theme
 * Based on TubeAcePlay functionality with improvements
 */

(function($) {
    'use strict';

    // Video Thumbnail Preview on Hover
    var VideoPreview = {
        hoverDelay: 300,
        timeoutId: null,
        activePreview: null,
        isPreviewPlaying: false,

        init: function() {
            // Initialize hover previews
            this.setupHoverEvents();
            
            // Initialize masonry layout if it exists
            this.initializeMasonry();
            
            // Setup smooth scroll to anchors
            this.setupSmoothScroll();
        },

        setupHoverEvents: function() {
            var self = this;

            // On hover event for video thumbnails
            $('.video-thumbnail-container').hover(
                function() {
                    var $container = $(this);
                    
                    // Clear any previous timeout
                    if (self.timeoutId) {
                        clearTimeout(self.timeoutId);
                    }

                    // Set timeout to delay preview start
                    self.timeoutId = setTimeout(function() {
                        self.startPreview($container);
                    }, self.hoverDelay);
                },
                function() {
                    // Mouse out - clear timeout if it exists
                    if (self.timeoutId) {
                        clearTimeout(self.timeoutId);
                        self.timeoutId = null;
                    }

                    // Stop active preview
                    self.stopPreview();
                }
            );
        },

        startPreview: function($container) {
            var self = this;
            
            // Stop any active preview
            self.stopPreview();
            
            // Check if this container has a video preview source
            var previewSrc = $container.data('preview-src');
            var webpPreviewSrc = $container.data('webp-preview-src');
            
            if (previewSrc) {
                // Create video element if it doesn't exist
                var $video = $container.find('video');
                if ($video.length === 0) {
                    $video = $('<video class="thumbnail-preview" muted loop playsinline></video>');
                    $container.append($video);
                }
                
                // Set the source and load the video
                $video.attr('src', previewSrc);
                $video.get(0).load();
                
                // Play the video
                var playPromise = $video.get(0).play();
                
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        self.isPreviewPlaying = true;
                        self.activePreview = $video;
                        
                        // Add active class to container
                        $container.addClass('preview-active');
                    }).catch(function(error) {
                        console.log('Video preview playback prevented: ', error);
                    });
                }
            } else if (webpPreviewSrc) {
                // If we have a WebP preview (animated image), show it
                var $staticImg = $container.find('img.thumbnail-static');
                $staticImg.css('opacity', '0');
                
                // Create WebP image element
                var $webpImg = $container.find('img.thumbnail-webp');
                if ($webpImg.length === 0) {
                    $webpImg = $('<img class="thumbnail-webp" alt="Preview">');
                    $container.append($webpImg);
                }
                
                $webpImg.attr('src', webpPreviewSrc).css('opacity', '1');
                
                // Store reference
                self.activePreview = $webpImg;
                $container.addClass('preview-active');
            }
        },

        stopPreview: function() {
            var self = this;
            
            if (self.activePreview) {
                if (self.activePreview.is('video')) {
                    // Pause and reset video
                    self.activePreview.get(0).pause();
                    self.activePreview.get(0).currentTime = 0;
                    self.isPreviewPlaying = false;
                } else if (self.activePreview.is('img.thumbnail-webp')) {
                    // Fade out WebP image and restore static image
                    self.activePreview.css('opacity', '0');
                    self.activePreview.closest('.video-thumbnail-container').find('img.thumbnail-static').css('opacity', '1');
                }
                
                self.activePreview.closest('.video-thumbnail-container').removeClass('preview-active');
                self.activePreview = null;
            }
        },

        initializeMasonry: function() {
            if (typeof $.fn.masonry !== 'undefined' && $('.video-masonry').length) {
                $('.video-masonry').masonry({
                    itemSelector: '.video-grid-item',
                    columnWidth: '.video-grid-item',
                    percentPosition: true
                });
                
                // Re-layout masonry when images load
                $('.video-masonry').imagesLoaded().progress(function() {
                    $('.video-masonry').masonry('layout');
                });
            }
        },

        setupSmoothScroll: function() {
            // Smooth scroll for performer directory anchor links
            $('a[href^="#letter-"]').on('click', function(e) {
                e.preventDefault();
                
                var target = $(this.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 50
                    }, 500);
                }
            });
        }
    };

    // On document ready
    $(document).ready(function() {
        VideoPreview.init();

        // Keyboard accessibility for video cards
        document.querySelectorAll('.video-card[tabindex]').forEach(el =>
            el.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault(); // Prevent default scroll behavior for spacebar
                    el.click();
                }
            })
        );
    });

})(jQuery);
