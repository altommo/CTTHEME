/**
 * Trending Videos Section Functionality
 * Self-contained scripts for the trending videos grid
 */
(function($) {
    'use strict';

    // Initialize once DOM is ready
    $(document).ready(function() {
        initTrendingSection();
    });

    /**
     * Initialize the trending videos section
     */
    function initTrendingSection() {
        // Add hover effects to trending cards
        // Removed JS-based hover effects to rely on CSS
        
        // Initialize lazy loading for trending section images
        initTrendingLazyLoad();
        
        // Initialize hover preview videos if available
        initTrendingHoverPreviews();
    }

    /**
     * Initialize lazy loading for trending section images
     */
    function initTrendingLazyLoad() {
        // Use Intersection Observer if supported
        if ('IntersectionObserver' in window) {
            const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        
                        // Load the actual image
                        if (lazyImage.dataset.src) {
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.removeAttribute('data-src');
                            lazyImage.classList.add('loaded');
                        }
                        
                        // Stop observing the image
                        observer.unobserve(lazyImage);
                    }
                });
            }, {
                rootMargin: '200px 0px' // Start loading 200px before image enters viewport
            });
            
            // Observe all lazy images in trending section
            document.querySelectorAll('.trending-videos-thumbnail img[data-src]').forEach(function(img) {
                lazyImageObserver.observe(img);
            });
        } else {
            // Fallback for browsers that don't support Intersection Observer
            function lazyLoad() {
                let lazyImages = document.querySelectorAll('.trending-videos-thumbnail img[data-src]');
                
                lazyImages.forEach(function(img) {
                    if (img.getBoundingClientRect().top <= window.innerHeight && img.getBoundingClientRect().bottom >= 0) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        img.classList.add('loaded');
                    }
                });
                
                // Clean up event listener if all images are loaded
                if (document.querySelectorAll('.trending-videos-thumbnail img[data-src]').length === 0) {
                    document.removeEventListener('scroll', lazyLoad);
                    window.removeEventListener('resize', lazyLoad);
                    window.removeEventListener('orientationChange', lazyLoad);
                }
            }
            
            // Add event listeners for fallback lazy loading
            document.addEventListener('scroll', lazyLoad);
            window.addEventListener('resize', lazyLoad);
            window.addEventListener('orientationChange', lazyLoad);
            
            // Initial load
            lazyLoad();
        }
    }

    /**
     * Initialize video hover previews for trending section
     */
    function initTrendingHoverPreviews() {
        $('.trending-videos-thumbnail').each(function() {
            const $container = $(this);
            const $staticImage = $container.find('img');
            const previewSrc = $container.data('preview-src');
            
            // Only proceed if we have a preview source
            if (!previewSrc) return;
            
            // Create preview video element if not exists
            if ($container.find('video.preview-video').length === 0) {
                const $previewVideo = $('<video class="preview-video" muted loop playsinline></video>');
                $previewVideo.css({
                    'position': 'absolute',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%',
                    'object-fit': 'cover',
                    'opacity': '0',
                    'transition': 'opacity 0.3s ease',
                    'z-index': '2'
                });
                
                // Add source to video
                $previewVideo.append('<source src="' + previewSrc + '" type="video/mp4">');
                
                // Add video to container
                $container.append($previewVideo);
                
                // Handle hover events
                $container.hover(
                    function() {
                        const video = $previewVideo.get(0);
                        if (video) {
                            video.currentTime = 0;
                            video.play();
                            $previewVideo.css('opacity', '1');
                            $staticImage.css('opacity', '0');
                        }
                    },
                    function() {
                        const video = $previewVideo.get(0);
                        if (video) {
                            video.pause();
                            $previewVideo.css('opacity', '0');
                            $staticImage.css('opacity', '1');
                        }
                    }
                );
            }
        });
    }

    /**
     * Handle window resize to maintain grid proportions
     */
    $(window).on('resize', function() {
        // Adjust grid columns based on screen size
        const $grid = $('.trending-videos-grid');
        const width = window.innerWidth;
        
        if (width < 480) {
            $grid.css('grid-template-columns', '1fr');
        } else if (width < 768) {
            $grid.css('grid-template-columns', 'repeat(auto-fill, minmax(250px, 1fr))');
        } else {
            $grid.css('grid-template-columns', 'repeat(auto-fill, minmax(280px, 1fr))');
        }
    });

})(jQuery);
