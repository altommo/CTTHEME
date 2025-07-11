/**
 * Trending Videos Section - Lightweight JavaScript
 * 
 * Modern, performance-optimized JavaScript without masonry dependencies
 * Focused only on enhancing the pure CSS Grid layout
 */
(function($) {
  'use strict';

  // Initialize when DOM is fully loaded
  $(document).ready(function() {
    initTrendingSection();
  });

  /**
   * Initialize trending section enhancements
   */
  function initTrendingSection() {
    // Initialize hover effects
    // Removed JS-based hover effects to rely on CSS
    
    // Initialize lazy loading for images
    initLazyLoading();
    
    // Initialize video previews if available
    initVideoPreview();
  }

  /**
   * Implement efficient lazy loading using Intersection Observer API
   */
  function initLazyLoading() {
    // Check if browser supports Intersection Observer
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver(function(entries, observer) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            const img = entry.target;
            const src = img.getAttribute('data-src');

            if (src) {
              // Create a temporary image to preload
              const tempImg = new Image();

              // Set up the onload callback
              tempImg.onload = function() {
                // Once preloaded, update the real image
                img.src = src;
                img.removeAttribute('data-src');
                img.classList.add('loaded');

                // Apply crossfade effect
                $(img).css('opacity', 0).animate({opacity: 1}, 300);
              };

              // Handle loading errors
              tempImg.onerror = function() {
                console.warn('Failed to load image:', src);
                img.parentNode.replaceWith(createPlaceholder(img.alt));
              };

              // Start loading
              tempImg.src = src;
            }

            observer.unobserve(img);
          }
        });
      }, {
        rootMargin: '0px 0px 200px 0px',
        threshold: 0.01
      });

      // Apply observer to all images with data-src
      document.querySelectorAll('.trending-videos-thumbnail img[data-src]').forEach(function(img) {
        // If the image is already in the viewport, load it immediately
        if (isInViewport(img)) {
          const src = img.getAttribute('data-src');
          if (src) {
            img.src = src;
            img.removeAttribute('data-src');
            img.classList.add('loaded');
          }
        } else {
          imageObserver.observe(img);
        }
      });
    } else {
      // Fallback for browsers without Intersection Observer
      $('.trending-videos-thumbnail img[data-src]').each(function() {
        $(this).attr('src', $(this).attr('data-src'));
        $(this).removeAttr('data-src');
        $(this).addClass('loaded');
      });
    }
  }

  /**
   * Helper function to create a placeholder when image fails to load
   */
  function createPlaceholder(altText) {
    const placeholder = document.createElement('div');
    placeholder.className = 'thumbnail-placeholder';
    placeholder.setAttribute('aria-label', altText || 'Image not available');
    return placeholder;
  }

  /**
   * Helper function to check if element is in viewport
   */
  function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  /**
   * Initialize video preview on hover
   */
  function initVideoPreview() {
    $('.trending-videos-thumbnail[data-preview-src]').each(function() {
      const $container = $(this);
      const previewSrc = $container.attr('data-preview-src');
      
      if (previewSrc) {
        const $image = $container.find('img');
        const $video = $('<video class="trending-videos-preview" muted loop playsinline></video>');
        
        // Set video style to match image positioning
        $video.css({
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          opacity: 0,
          transition: 'opacity 0.3s ease',
          objectFit: 'cover',
          zIndex: 2
        });
        
        // Add source to video
        $video.append(`<source src="${previewSrc}" type="video/mp4">`);
        
        // Add video to container after image
        $container.append($video);
        
        // Handle hover events
        $container.hover(
          function() {
            const video = $video[0];
            video.play();
            $video.css('opacity', 1);
            $image.css('opacity', 0);
          },
          function() {
            const video = $video[0];
            video.pause();
            $video.css('opacity', 0);
            $image.css('opacity', 1);
          }
        );
      }
    });
  }

  /**
   * Handle responsive behavior
   * CSS Grid handles most of the responsive needs, this just provides enhancements
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

  // Ensure videos stop playing when scrolled out of view for performance
  let observer;
  if ('IntersectionObserver' in window) {
    observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (!entry.isIntersecting) {
          const videos = entry.target.querySelectorAll('video');
          videos.forEach(function(video) {
            if (!video.paused) {
              video.pause();
            }
          });
        }
      });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.trending-videos-item').forEach(function(item) {
      observer.observe(item);
    });
  }

})(jQuery);
