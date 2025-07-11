/**
 * Unified Preview System
 * 
 * A complete rewrite of the video hover preview system
 * - YouTube WebP animations
 * - Custom WebP animations for self-hosted videos
 * - Video element previews for other platforms
 * - Consistent behavior across all platforms
 * - Performance optimized with lazy loading
 * - Support for extracted preview images and videos from external platforms
 */

(function($) {
  'use strict';

  // Configuration
  const config = {
    previewDelay: 200,       // ms delay before showing preview (prevents flickering)
    touchDelay: 500,         // ms delay for touch interactions
    touchDuration: 2000,     // ms to show preview on touch before hiding
    youtubeWebpDuration: 3000, // duration requested for YouTube WebP animations
    previewWebpEnabled: true  // enable custom WebP animations for self-hosted videos
  };

  /**
   * Initialize the preview system
   */
  function initPreviewSystem() {
    console.log('🎬 Initializing unified preview system');
    
    // Set up lazy loading with IntersectionObserver
    if ('IntersectionObserver' in window) {
      setupLazyLoading();
    } else {
      // Fallback for browsers without IntersectionObserver
      setupAllPreviews();
    }
    
    // Process thumbnails already in viewport
    processVisibleThumbnails();
  }
  
  /**
   * Set up lazy loading with IntersectionObserver
   */
  function setupLazyLoading() {
    const options = {
      rootMargin: '200px', // Load before they come into view
      threshold: 0.01
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          setupPreview(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, options);
    
    // Start observing all containers
    document.querySelectorAll('.video-thumbnail-container').forEach(container => {
      observer.observe(container);
    });
  }
  
  /**
   * Process all thumbnails without lazy loading
   */
  function setupAllPreviews() {
    document.querySelectorAll('.video-thumbnail-container').forEach(container => {
      setupPreview(container);
    });
  }
  
  /**
   * Process thumbnails that are already visible in the viewport
   */
  function processVisibleThumbnails() {
    document.querySelectorAll('.video-thumbnail-container').forEach(container => {
      if (isInViewport(container)) {
        setupPreview(container);
      }
    });
  }
  
  /**
   * Set up preview for a specific container
   */
  function setupPreview(container) {
    // Skip if already initialized
    if (container.dataset.previewInitialized === 'true') {
      return;
    }
    
    // Mark as initialized
    container.dataset.previewInitialized = 'true';
    
    // Extract data attributes
    const videoType = container.dataset.videoType || '';
    const videoId = container.dataset.videoId || '';
    const previewSrc = container.dataset.previewSrc || '';
    const webpPreviewSrc = container.dataset.webpPreviewSrc || '';
    const clipPreviewSrc = container.dataset.clipPreviewSrc || '';
    
    // Always start with thumbnail visible
    const thumbnailImg = container.querySelector('.thumbnail-static');
    if (thumbnailImg) {
      thumbnailImg.style.opacity = '1';
    }
    
    // Different handling based on video type
    if (videoType === 'youtube' && videoId) {
      setupYouTubePreview(container, videoId);
    } else if (webpPreviewSrc && config.previewWebpEnabled) {
      // Use custom WebP animation if available
      setupWebpPreview(container, webpPreviewSrc);
    } else if (clipPreviewSrc) {
      // Use clip preview if available
      setupVideoPreview(container, clipPreviewSrc);
    } else if (previewSrc) {
      // Fallback to standard preview
      setupVideoPreview(container, previewSrc);
    }
  }
  
  /**
   * Set up YouTube preview with WebP animation
   */
  function setupYouTubePreview(container, videoId) {
    const thumbnailImg = container.querySelector('.thumbnail-static');
    
    // Create animated WebP image element if it doesn't exist
    let animatedImg = container.querySelector('.youtube-preview');
    if (!animatedImg) {
      animatedImg = document.createElement('img');
      animatedImg.className = 'youtube-preview';
      animatedImg.setAttribute('aria-hidden', 'true');
      animatedImg.style.position = 'absolute';
      animatedImg.style.top = '0';
      animatedImg.style.left = '0';
      animatedImg.style.width = '100%';
      animatedImg.style.height = '100%';
      animatedImg.style.opacity = '0';
      animatedImg.style.transition = 'opacity 0.3s ease';
      animatedImg.style.zIndex = '2';
      animatedImg.style.objectFit = 'cover';
      
      container.appendChild(animatedImg);
    }
    
    // Set up hover behavior
    let hoverTimer;
    let touchTimer;
    
    // Mouse enter - prepare to show preview
    container.addEventListener('mouseenter', () => {
      clearTimeout(hoverTimer);
      
      hoverTimer = setTimeout(() => {
        // Generate YouTube WebP URL
        const webpUrl = `https://i.ytimg.com/an_webp/${videoId}/mqdefault_6s.webp?du=${config.youtubeWebpDuration}`;
        
        // Set source and show
        animatedImg.src = webpUrl;
        animatedImg.style.opacity = '1';
        if (thumbnailImg) thumbnailImg.style.opacity = '0';
        container.classList.add('is-previewing');
      }, config.previewDelay);
    });
    
    // Mouse leave - hide preview
    container.addEventListener('mouseleave', () => {
      clearTimeout(hoverTimer);
      
      // Hide preview
      animatedImg.style.opacity = '0';
      if (thumbnailImg) thumbnailImg.style.opacity = '1';
      container.classList.remove('is-previewing');
      
      // Clean up source after transition
      setTimeout(() => {
        if (!container.matches(':hover')) {
          animatedImg.src = '';
        }
      }, 300);
    });
    
    // Touch behavior
    setupTouchInteraction(container, 
      // Show function
      () => {
        const webpUrl = `https://i.ytimg.com/an_webp/${videoId}/mqdefault_6s.webp?du=${config.youtubeWebpDuration}`;
        animatedImg.src = webpUrl;
        animatedImg.style.opacity = '1';
        if (thumbnailImg) thumbnailImg.style.opacity = '0';
        container.classList.add('is-previewing');
      },
      // Hide function
      () => {
        animatedImg.style.opacity = '0';
        if (thumbnailImg) thumbnailImg.style.opacity = '1';
        container.classList.remove('is-previewing');
        setTimeout(() => { animatedImg.src = ''; }, 300);
      }
    );
  }
  
  /**
   * Set up video preview using <video> element or WebP animation
   */
  function setupVideoPreview(container, previewSrc) {
    const thumbnailImg = container.querySelector('.thumbnail-static');
    
    // Check if we have a custom WebP preview available
    const hasWebpPreview = container.dataset.webpPreviewSrc && config.previewWebpEnabled;
    const webpPreviewSrc = container.dataset.webpPreviewSrc || '';
    
    // Handle WebP preview if available
    if (hasWebpPreview) {
      setupWebpPreview(container, webpPreviewSrc);
      return;
    }
    
    // Fallback to video element preview
    // Get or create video element
    let videoEl = container.querySelector('.thumbnail-preview');
    if (!videoEl) {
      videoEl = document.createElement('video');
      videoEl.className = 'thumbnail-preview';
      videoEl.setAttribute('muted', '');
      videoEl.setAttribute('loop', '');
      videoEl.setAttribute('playsinline', '');
      videoEl.setAttribute('aria-hidden', 'true');
      videoEl.style.position = 'absolute';
      videoEl.style.top = '0';
      videoEl.style.left = '0';
      videoEl.style.width = '100%';
      videoEl.style.height = '100%';
      videoEl.style.opacity = '0';
      videoEl.style.transition = 'opacity 0.3s ease';
      videoEl.style.zIndex = '2';
      videoEl.style.objectFit = 'cover';
      
      if (thumbnailImg && thumbnailImg.src) {
        videoEl.poster = thumbnailImg.src;
      }
      
      container.appendChild(videoEl);
    }
    
    // Set up hover behavior
    let hoverTimer;
    let isVideoLoaded = false;
    
    // Mouse enter - prepare to play video
    container.addEventListener('mouseenter', () => {
      clearTimeout(hoverTimer);
      
      hoverTimer = setTimeout(() => {
        // Lazy load video on first hover
        if (!isVideoLoaded) {
          videoEl.src = previewSrc;
          isVideoLoaded = true;
        }
        
        // Reset position and play
        videoEl.currentTime = 0;
        
        const playPromise = videoEl.play();
        if (playPromise !== undefined) {
          playPromise.then(() => {
            videoEl.style.opacity = '1';
            if (thumbnailImg) thumbnailImg.style.opacity = '0';
            container.classList.add('is-previewing');
          }).catch(error => {
            // Handle autoplay restrictions
            console.log('Video autoplay prevented:', error.message);
          });
        }
      }, config.previewDelay);
    });
    
    // Mouse leave - pause video
    container.addEventListener('mouseleave', () => {
      clearTimeout(hoverTimer);
      
      // Pause and hide video
      videoEl.pause();
      videoEl.style.opacity = '0';
      if (thumbnailImg) thumbnailImg.style.opacity = '1';
      container.classList.remove('is-previewing');
    });
    
    // Touch behavior
    setupTouchInteraction(container, 
      // Show function
      () => {
        if (!isVideoLoaded) {
          videoEl.src = previewSrc;
          isVideoLoaded = true;
        }
        
        videoEl.currentTime = 0;
        videoEl.play().then(() => {
          videoEl.style.opacity = '1';
          if (thumbnailImg) thumbnailImg.style.opacity = '0';
          container.classList.add('is-previewing');
        }).catch(() => {});
      },
      // Hide function
      () => {
        videoEl.pause();
        videoEl.style.opacity = '0';
        if (thumbnailImg) thumbnailImg.style.opacity = '1';
        container.classList.remove('is-previewing');
      }
    );
  }
  
  /**
   * Set up WebP animation preview for self-hosted videos
   */
  function setupWebpPreview(container, webpPreviewSrc) {
    const thumbnailImg = container.querySelector('.thumbnail-static');
    
    // Create WebP image element if it doesn't exist
    let animatedImg = container.querySelector('.webp-preview');
    if (!animatedImg) {
      animatedImg = document.createElement('img');
      animatedImg.className = 'webp-preview';
      animatedImg.setAttribute('aria-hidden', 'true');
      animatedImg.style.position = 'absolute';
      animatedImg.style.top = '0';
      animatedImg.style.left = '0';
      animatedImg.style.width = '100%';
      animatedImg.style.height = '100%';
      animatedImg.style.opacity = '0';
      animatedImg.style.transition = 'opacity 0.3s ease';
      animatedImg.style.zIndex = '2';
      animatedImg.style.objectFit = 'cover';
      
      container.appendChild(animatedImg);
    }
    
    // Set up hover behavior
    let hoverTimer;
    let touchTimer;
    
    // Mouse enter - prepare to show preview
    container.addEventListener('mouseenter', () => {
      clearTimeout(hoverTimer);
      
      hoverTimer = setTimeout(() => {
        // Set source and show
        animatedImg.src = webpPreviewSrc;
        animatedImg.style.opacity = '1';
        if (thumbnailImg) thumbnailImg.style.opacity = '0';
        container.classList.add('is-previewing');
      }, config.previewDelay);
    });
    
    // Mouse leave - hide preview
    container.addEventListener('mouseleave', () => {
      clearTimeout(hoverTimer);
      
      // Hide preview
      animatedImg.style.opacity = '0';
      if (thumbnailImg) thumbnailImg.style.opacity = '1';
      container.classList.remove('is-previewing');
      
      // Clean up source after transition
      setTimeout(() => {
        if (!container.matches(':hover')) {
          animatedImg.src = '';
        }
      }, 300);
    });
    
    // Touch behavior
    setupTouchInteraction(container, 
      // Show function
      () => {
        animatedImg.src = webpPreviewSrc;
        animatedImg.style.opacity = '1';
        if (thumbnailImg) thumbnailImg.style.opacity = '0';
        container.classList.add('is-previewing');
      },
      // Hide function
      () => {
        animatedImg.style.opacity = '0';
        if (thumbnailImg) thumbnailImg.style.opacity = '1';
        container.classList.remove('is-previewing');
        setTimeout(() => { animatedImg.src = ''; }, 300);
      }
    );
  }
  
  /**
   * Set up touch interaction for mobile devices
   */
  function setupTouchInteraction(container, showFn, hideFn) {
    let touchTimer;
    
    // Touch start - prepare for long press
    container.addEventListener('touchstart', e => {
      // Mark as pending
      container.dataset.touchPreview = 'pending';
      
      // Set timer for long press
      touchTimer = setTimeout(() => {
        container.dataset.touchPreview = 'active';
        showFn();
        
        // Prevent default only after we've decided this is a preview action
        e.preventDefault();
      }, config.touchDelay);
    });
    
    // Touch end - handle navigation or hiding
    container.addEventListener('touchend', e => {
      // For short taps, let navigation happen normally
      if (container.dataset.touchPreview === 'pending') {
        clearTimeout(touchTimer);
        container.dataset.touchPreview = 'cancelled';
        return;
      }
      
      // For preview actions, prevent navigation and hide after delay
      if (container.dataset.touchPreview === 'active') {
        e.preventDefault();
        
        // Hide after a set duration
        setTimeout(() => {
          hideFn();
          container.dataset.touchPreview = 'done';
        }, config.touchDuration);
      }
    });
    
    // Touch move - cancel preview on scroll
    container.addEventListener('touchmove', () => {
      clearTimeout(touchTimer);
      container.dataset.touchPreview = 'cancelled';
    });
  }
  
  /**
   * Check if element is in viewport
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
   * Limit trending videos to 8 items
   */
  function limitTrendingVideos() {
    console.log('Limiting trending videos to 8...');
    
    // Find trending section containers
    var containers = $('.trendingSection, .cat-box');
    
    // Process each container
    containers.each(function() {
      // Find video items in this container
      var $videoItems = $(this).find('.videoBox, .col-md-3, .col-md-4');
      
      // If we have more than 8 videos, hide the rest
      if ($videoItems.length > 8) {
        console.log('Found ' + $videoItems.length + ' trending videos, hiding extras');
        $videoItems.slice(8).hide();
      }
    });
  }

  // Initialize on document ready
  $(document).ready(function() {
    // Initialize preview system
    initPreviewSystem();
    
    // Limit trending videos to 8
    limitTrendingVideos();
  });
  
  // Re-initialize when new content is loaded via AJAX
  $(document).on('ajax_content_loaded', function() {
    initPreviewSystem();
    limitTrendingVideos();
  });
  
  // Make functions available globally
  window.initPreviewSystem = initPreviewSystem;
  window.limitTrendingVideos = limitTrendingVideos;
  
})(jQuery);