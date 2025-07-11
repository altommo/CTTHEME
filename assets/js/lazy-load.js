/**
 * CustomTube Lazy Loading
 *
 * Handles lazy loading of images and videos
 */
(function($) {
    'use strict';

    /**
     * Initialize lazy loading for images and videos
     */
    function initializeLazyLoad() {
        // Use Intersection Observer if supported
        if ('IntersectionObserver' in window) {
            const lazyLoadObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const lazyElement = entry.target;
                        
                        if (lazyElement.tagName.toLowerCase() === 'img') {
                            // Handle images
                            if (lazyElement.dataset.src) {
                                lazyElement.src = lazyElement.dataset.src;
                                lazyElement.removeAttribute('data-src');
                            }
                            
                            if (lazyElement.dataset.srcset) {
                                lazyElement.srcset = lazyElement.dataset.srcset;
                                lazyElement.removeAttribute('data-srcset');
                            }
                            
                            lazyElement.classList.remove('lazy-load');
                            lazyElement.classList.add('loaded');
                            
                        } else if (lazyElement.tagName.toLowerCase() === 'video') {
                            // Handle videos
                            if (lazyElement.dataset.src) {
                                lazyElement.src = lazyElement.dataset.src;
                                lazyElement.removeAttribute('data-src');
                            }
                            
                            lazyElement.load();
                            lazyElement.classList.remove('lazy-load');
                            lazyElement.classList.add('loaded');
                        } else if (lazyElement.tagName.toLowerCase() === 'iframe') {
                            // Handle iframes
                            if (lazyElement.dataset.src) {
                                lazyElement.src = lazyElement.dataset.src;
                                lazyElement.removeAttribute('data-src');
                            }
                            
                            lazyElement.classList.remove('lazy-load');
                            lazyElement.classList.add('loaded');
                        }
                        
                        // Stop watching this element
                        observer.unobserve(lazyElement);
                    }
                });
            }, {
                rootMargin: '200px 0px', // Load images 200px before they enter viewport
                threshold: 0.01
            });
            
            // Observe all lazy-loadable elements
            document.querySelectorAll('img.lazy-load, video.lazy-load, iframe.lazy-load').forEach(function(element) {
                lazyLoadObserver.observe(element);
            });
            
        } else {
            // Fallback for browsers without Intersection Observer
            // Simple scroll-based lazy loading
            const lazyElements = document.querySelectorAll('img.lazy-load, video.lazy-load, iframe.lazy-load');
            
            function lazyLoad() {
                const scrollTop = window.pageYOffset;
                const viewportHeight = window.innerHeight;
                
                lazyElements.forEach(function(lazyElement) {
                    if (lazyElement.getBoundingClientRect().top - viewportHeight <= 200) {
                        if (lazyElement.tagName.toLowerCase() === 'img') {
                            if (lazyElement.dataset.src) {
                                lazyElement.src = lazyElement.dataset.src;
                                lazyElement.removeAttribute('data-src');
                            }
                            
                            if (lazyElement.dataset.srcset) {
                                lazyElement.srcset = lazyElement.dataset.srcset;
                                lazyElement.removeAttribute('data-srcset');
                            }
                            
                        } else if (lazyElement.tagName.toLowerCase() === 'video') {
                            if (lazyElement.dataset.src) {
                                lazyElement.src = lazyElement.dataset.src;
                                lazyElement.removeAttribute('data-src');
                            }
                            
                            lazyElement.load();
                        } else if (lazyElement.tagName.toLowerCase() === 'iframe') {
                            if (lazyElement.dataset.src) {
                                lazyElement.src = lazyElement.dataset.src;
                                lazyElement.removeAttribute('data-src');
                            }
                        }
                        
                        lazyElement.classList.remove('lazy-load');
                        lazyElement.classList.add('loaded');
                    }
                });
                
                // Clean up if all elements are loaded
                if (lazyElements.length === 0) {
                    document.removeEventListener('scroll', lazyLoad);
                    window.removeEventListener('resize', lazyLoad);
                    window.removeEventListener('orientationChange', lazyLoad);
                }
            }
            
            // Add scroll event listener
            document.addEventListener('scroll', lazyLoad, { passive: true });
            window.addEventListener('resize', lazyLoad, { passive: true });
            window.addEventListener('orientationChange', lazyLoad, { passive: true });
            
            // Initial load
            lazyLoad();
        }
    }
    
    /**
     * Initialize infinite scroll for video grid
     */
    function initInfiniteScroll() {
        const videoGrid = $('.video-grid');
        const loadMoreButton = $('.load-more-button');
        
        if (!videoGrid.length || !loadMoreButton.length) {
            return;
        }
        
        // Use intersection observer for auto-loading
        if ('IntersectionObserver' in window) {
            const loadMoreObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && !loadMoreButton.hasClass('loading')) {
                        loadMore();
                    }
                });
            }, {
                rootMargin: '0px 0px 200px 0px' // Load more when button is within 200px of viewport
            });
            
            loadMoreObserver.observe(loadMoreButton[0]);
        }
        
        // Also handle click events
        loadMoreButton.on('click', function(e) {
            e.preventDefault();
            loadMore();
        });
        
        function loadMore() {
            if (loadMoreButton.hasClass('loading')) {
                return;
            }
            
            loadMoreButton.addClass('loading').text('Loading...');
            
            const currentPage = parseInt(videoGrid.data('page') || 1);
            const nextPage = currentPage + 1;
            const maxPages = parseInt(videoGrid.data('max-pages') || 1);
            
            // Stop if we've reached the last page
            if (currentPage >= maxPages) {
                loadMoreButton.hide();
                return;
            }
            
            $.ajax({
                url: customtube_settings.ajax_url,
                type: 'POST',
                data: {
                    action: 'customtube_load_more',
                    page: nextPage,
                    tax_query: videoGrid.data('tax-query') || '',
                    orderby: videoGrid.data('orderby') || 'date',
                    order: videoGrid.data('order') || 'DESC',
                    nonce: customtube_settings.nonce
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        // Append new items
                        videoGrid.append(response.data.html);
                        
                        // Update page counter
                        videoGrid.data('page', nextPage);
                        
                        // Hide load more button if we're at the last page
                        if (nextPage >= maxPages) {
                            loadMoreButton.hide();
                        }
                        
                        // Trigger event for other scripts
                        $(document).trigger('ajax_content_loaded');
                        
                        // Reset button
                        loadMoreButton.removeClass('loading').text('Load More');
                    } else {
                        console.error('Error loading more videos:', response.data.message);
                        loadMoreButton.removeClass('loading').text('Load More');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    loadMoreButton.removeClass('loading').text('Load More');
                }
            });
        }
    }
    
    /**
     * Defer non-critical scripts
     */
    function deferScripts() {
        $('script[data-defer]').each(function() {
            const $script = $(this);
            const deferScript = document.createElement('script');
            
            // Copy all attributes
            $.each(this.attributes, function(index, attr) {
                if (attr.name !== 'data-defer') {
                    deferScript.setAttribute(attr.name, attr.value);
                }
            });
            
            // Set content if it's an inline script
            if ($script.html()) {
                deferScript.innerHTML = $script.html();
            }
            
            // Replace the original script
            $script.replaceWith(deferScript);
        });
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initializeLazyLoad();
        initInfiniteScroll();
        
        // Re-initialize when new content is loaded via AJAX
        $(document).on('ajax_content_loaded', function() {
            initializeLazyLoad();
        });
    });
    
    // Defer scripts after page load
    $(window).on('load', deferScripts);
    
})(jQuery);