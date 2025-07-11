/**
 * Video Grid Component
 * Handles video grid layouts, filtering, lazy loading, and pagination
 * Consolidates: video-grid.js, filter-fix.js, view-more.js, lazy-load.js
 */

import { debounce, throttle, addEvent, isInViewport } from '../core/utils.js';

export class VideoGrid {
    constructor(core) {
        this.core = core;
        this.elements = {};
        this.state = {
            currentPage: 1,
            isLoading: false,
            hasMore: true,
            activeFilters: {},
            lazyLoadObserver: null,
            masonryInstance: null
        };
        this.events = [];
    }

    /**
     * Initialize video grid component
     */
    init() {
        console.log('VideoGrid: Initializing...');
        
        // Cache DOM elements
        this.cacheElements();
        
        // Initialize grid layouts
        this.initializeLayouts();
        
        // Setup filtering
        this.setupFiltering();
        
        // Setup lazy loading
        this.setupLazyLoading();
        
        // Setup pagination/load more
        this.setupLoadMore();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Register with core
        this.core.registerComponent('videoGrid', this);
        
        console.log('VideoGrid: Initialization complete');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            grids: document.querySelectorAll('.video-grid, .video-masonry'),
            filterForm: document.querySelector('.filter-form'),
            filterInputs: document.querySelectorAll('.filter-input'),
            sortSelects: document.querySelectorAll('.sort-select'),
            categoryFilters: document.querySelectorAll('.category-filter a'),
            tagFilters: document.querySelectorAll('.tag-filter a'),
            performerFilters: document.querySelectorAll('.performer-filter a'),
            durationSliders: document.querySelectorAll('input[type="range"]'),
            loadMoreButtons: document.querySelectorAll('.load-more-button, .view-more-button'),
            lazyImages: document.querySelectorAll('img[data-src]'),
            videoItems: document.querySelectorAll('.video-grid-item, .video-card, .videoBox'),
            gridContainers: document.querySelectorAll('.video-grid-container')
        };
    }

    /**
     * Initialize grid layouts (masonry, flexbox, etc.)
     */
    initializeLayouts() {
        this.elements.grids.forEach(grid => {
            if (grid.classList.contains('video-masonry')) {
                this.initializeMasonry(grid);
            } else {
                this.initializeFlexGrid(grid);
            }
        });
    }

    /**
     * Initialize masonry layout
     * @param {Element} grid - Grid element
     */
    initializeMasonry(grid) {
        // Check if Masonry library is available
        if (typeof window.Masonry !== 'undefined') {
            const masonry = new window.Masonry(grid, {
                itemSelector: '.video-grid-item, .video-card',
                columnWidth: '.video-grid-item, .video-card',
                percentPosition: true,
                gutter: 10
            });
            
            this.state.masonryInstance = masonry;
            
            // Re-layout when images load
            this.setupMasonryImageLoad(grid, masonry);
            
            console.log('VideoGrid: Masonry layout initialized');
        } else {
            console.warn('VideoGrid: Masonry library not found, using fallback grid');
            this.initializeFlexGrid(grid);
        }
    }

    /**
     * Setup masonry image loading
     * @param {Element} grid - Grid element
     * @param {Object} masonry - Masonry instance
     */
    setupMasonryImageLoad(grid, masonry) {
        const images = grid.querySelectorAll('img');
        let loadedImages = 0;
        
        const checkAllImagesLoaded = () => {
            loadedImages++;
            if (loadedImages === images.length) {
                masonry.layout();
            }
        };
        
        images.forEach(img => {
            if (img.complete) {
                checkAllImagesLoaded();
            } else {
                this.events.push(
                    addEvent(img, 'load', checkAllImagesLoaded),
                    addEvent(img, 'error', checkAllImagesLoaded)
                );
            }
        });
    }

    /**
     * Initialize flex grid layout
     * @param {Element} grid - Grid element
     */
    initializeFlexGrid(grid) {
        // Ensure proper flex layout
        grid.style.display = 'flex';
        grid.style.flexWrap = 'wrap';
        grid.style.gap = '1rem';
        
        // Handle responsive columns
        this.setupResponsiveColumns(grid);
    }

    /**
     * Setup responsive columns for flex grid
     * @param {Element} grid - Grid element
     */
    setupResponsiveColumns(grid) {
        const updateColumns = () => {
            const items = grid.querySelectorAll('.video-grid-item, .video-card');
            const containerWidth = grid.offsetWidth;
            
            let columns;
            if (containerWidth < 768) {
                columns = 2; // Mobile
            } else if (containerWidth < 1024) {
                columns = 3; // Tablet
            } else if (containerWidth < 1440) {
                columns = 4; // Desktop
            } else {
                columns = 5; // Large desktop
            }
            
            const itemWidth = `calc(${100 / columns}% - 1rem)`;
            
            items.forEach(item => {
                item.style.width = itemWidth;
            });
        };
        
        // Initial update
        updateColumns();
        
        // Update on resize
        this.events.push(
            addEvent(window, 'resize', debounce(updateColumns, 250))
        );
    }

    /**
     * Setup filtering functionality
     */
    setupFiltering() {
        // Category filters
        this.elements.categoryFilters.forEach(filter => {
            this.events.push(
                addEvent(filter, 'click', (e) => {
                    e.preventDefault();
                    const category = filter.dataset.category || filter.textContent.trim();
                    this.applyFilter('category', category);
                })
            );
        });

        // Tag filters
        this.elements.tagFilters.forEach(filter => {
            this.events.push(
                addEvent(filter, 'click', (e) => {
                    e.preventDefault();
                    const tag = filter.dataset.tag || filter.textContent.trim();
                    this.applyFilter('tag', tag);
                })
            );
        });

        // Performer filters
        this.elements.performerFilters.forEach(filter => {
            this.events.push(
                addEvent(filter, 'click', (e) => {
                    e.preventDefault();
                    const performer = filter.dataset.performer || filter.textContent.trim();
                    this.applyFilter('performer', performer);
                })
            );
        });

        // Sort selects
        this.elements.sortSelects.forEach(select => {
            this.events.push(
                addEvent(select, 'change', (e) => {
                    const sortValue = e.target.value;
                    this.applyFilter('sort', sortValue);
                })
            );
        });

        // Duration sliders
        this.elements.durationSliders.forEach(slider => {
            this.events.push(
                addEvent(slider, 'input', debounce((e) => {
                    const duration = e.target.value;
                    this.applyFilter('duration', duration);
                }, 300))
            );
        });

        // Filter form submission
        if (this.elements.filterForm) {
            this.events.push(
                addEvent(this.elements.filterForm, 'submit', (e) => {
                    e.preventDefault();
                    this.applyFiltersFromForm();
                })
            );
        }
    }

    /**
     * Apply a single filter
     * @param {string} filterType - Type of filter
     * @param {string} filterValue - Filter value
     */
    applyFilter(filterType, filterValue) {
        // Update active filters
        this.state.activeFilters[filterType] = filterValue;
        
        // Add filtering classes for CSS styling
        this.addFilteringClasses();
        
        // Reset pagination
        this.state.currentPage = 1;
        this.state.hasMore = true;
        
        // Apply filters
        this.executeFiltering();
        
        // Emit filter event
        this.core.emit('video-grid:filter-applied', {
            filterType,
            filterValue,
            activeFilters: { ...this.state.activeFilters }
        });
    }

    /**
     * Apply filters from form
     */
    applyFiltersFromForm() {
        const formData = new FormData(this.elements.filterForm);
        const filters = {};
        
        for (const [key, value] of formData.entries()) {
            if (value) {
                filters[key] = value;
            }
        }
        
        this.state.activeFilters = filters;
        this.addFilteringClasses();
        this.executeFiltering();
        
        this.core.emit('video-grid:filters-applied', {
            activeFilters: { ...this.state.activeFilters }
        });
    }

    /**
     * Add CSS classes when filtering is active
     */
    addFilteringClasses() {
        // Add filtered class to grid containers
        this.elements.gridContainers.forEach(container => {
            container.classList.add('filtered-results');
        });
        
        this.elements.grids.forEach(grid => {
            grid.classList.add('filtered');
        });
        
        // Remove float-based classes that might interfere
        this.elements.videoItems.forEach(item => {
            item.style.float = 'none';
        });
    }

    /**
     * Execute filtering via AJAX
     */
    async executeFiltering() {
        if (this.state.isLoading) {
            return;
        }
        
        this.state.isLoading = true;
        this.showLoadingState();
        
        try {
            const response = await this.core.ajax.getFilteredVideos({
                ...this.state.activeFilters,
                page: this.state.currentPage
            });
            
            if (response.success) {
                this.updateGridContent(response.data.videos);
                this.state.hasMore = response.data.has_more;
                this.updateLoadMoreButton();
            } else {
                this.showError('Failed to load filtered videos');
            }
            
        } catch (error) {
            console.error('VideoGrid: Filtering failed:', error);
            this.showError('Error loading videos');
        } finally {
            this.state.isLoading = false;
            this.hideLoadingState();
        }
    }

    /**
     * Update grid content with new videos
     * @param {Array} videos - Array of video data
     */
    updateGridContent(videos) {
        this.elements.grids.forEach(grid => {
            // Clear existing content if it's the first page
            if (this.state.currentPage === 1) {
                grid.innerHTML = '';
            }
            
            // Add new videos
            videos.forEach(video => {
                const videoElement = this.createVideoElement(video);
                grid.appendChild(videoElement);
            });
            
            // Re-initialize lazy loading for new images
            this.setupLazyLoadingForElement(grid);
            
            // Re-layout masonry if needed
            if (this.state.masonryInstance) {
                this.state.masonryInstance.reloadItems();
                this.state.masonryInstance.layout();
            }
        });
        
        // Update video items cache
        this.elements.videoItems = document.querySelectorAll('.video-grid-item, .video-card, .videoBox');
        
        this.core.emit('video-grid:content-updated', { videos });
    }

    /**
     * Create video element from data
     * @param {Object} video - Video data
     * @returns {Element} Video element
     */
    createVideoElement(video) {
        const element = document.createElement('div');
        element.className = 'video-grid-item video-card';
        element.setAttribute('data-video-id', video.id); // Added data-video-id for tracking
        element.innerHTML = `
            <div class="video-thumbnail-container">
                <img data-src="${video.thumbnail}" alt="${video.title}" class="lazy-load">
                <div class="video-duration">${video.duration}</div>
                ${video.liked ? '<div class="video-liked-indicator">♥</div>' : ''}
            </div>
            <div class="video-info">
                <h3 class="video-title">
                    <a href="${video.url}">${video.title}</a>
                </h3>
                <div class="video-meta">
                    <span class="video-views">${video.views} views</span>
                    <span class="video-date">${video.date}</span>
                </div>
            </div>
        `;
        
        return element;
    }

    /**
     * Setup lazy loading functionality
     */
    setupLazyLoading() {
        // Use Intersection Observer if available
        if ('IntersectionObserver' in window) {
            this.setupIntersectionObserver();
        } else {
            // Fallback to scroll-based lazy loading
            this.setupScrollBasedLazyLoading();
        }
    }

    /**
     * Setup Intersection Observer for lazy loading
     */
    setupIntersectionObserver() {
        const options = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };
        
        this.state.lazyLoadObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    this.state.lazyLoadObserver.unobserve(entry.target);
                }
            });
        }, options);
        
        // Observe all lazy images
        this.elements.lazyImages.forEach(img => {
            this.state.lazyLoadObserver.observe(img);
        });
    }

    /**
     * Setup lazy loading for a specific element
     * @param {Element} container - Container element
     */
    setupLazyLoadingForElement(container) {
        const lazyImages = container.querySelectorAll('img[data-src]');
        
        if (this.state.lazyLoadObserver) {
            lazyImages.forEach(img => {
                this.state.lazyLoadObserver.observe(img);
            });
        } else {
            // Fallback: load images that are in viewport
            lazyImages.forEach(img => {
                if (isInViewport(img)) {
                    this.loadImage(img);
                }
            });
        }
    }

    /**
     * Load lazy image
     * @param {Element} img - Image element
     */
    loadImage(img) {
        if (img.dataset.src && !img.dataset.loaded) {
            const tempImg = new Image();
            
            tempImg.onload = () => {
                img.src = img.dataset.src;
                img.classList.add('loaded');
                img.dataset.loaded = 'true';
                
                // Remove data-src to prevent reprocessing
                delete img.dataset.src;
                
                this.core.emit('video-grid:image-loaded', { img });
            };
            
            tempImg.onerror = () => {
                img.classList.add('error');
                console.warn('VideoGrid: Failed to load image:', img.dataset.src);
            };
            
            tempImg.src = img.dataset.src;
        }
    }

    /**
     * Setup scroll-based lazy loading (fallback)
     */
    setupScrollBasedLazyLoading() {
        const checkLazyImages = throttle(() => {
            this.elements.lazyImages.forEach(img => {
                if (isInViewport(img)) {
                    this.loadImage(img);
                }
            });
        }, 100);
        
        this.events.push(
            addEvent(window, 'scroll', checkLazyImages, { passive: true }), // Mark as passive
            addEvent(window, 'resize', checkLazyImages, { passive: true })  // Mark as passive
        );
        
        // Initial check
        checkLazyImages();
    }

    /**
     * Setup load more / pagination functionality
     */
    setupLoadMore() {
        this.elements.loadMoreButtons.forEach(button => {
            this.events.push(
                addEvent(button, 'click', (e) => {
                    e.preventDefault();
                    this.loadMoreVideos();
                })
            );
        });
        
        // Setup infinite scroll if enabled
        if (this.shouldUseInfiniteScroll()) {
            this.setupInfiniteScroll();
        }
    }

    /**
     * Check if infinite scroll should be used
     * @returns {boolean} True if infinite scroll should be used
     */
    shouldUseInfiniteScroll() {
        return document.body.dataset.infiniteScroll === 'true' || 
               document.querySelector('.infinite-scroll-enabled');
    }

    /**
     * Setup infinite scroll
     */
    setupInfiniteScroll() {
        const checkScroll = throttle(() => {
            const scrollPosition = window.innerHeight + window.pageYOffset;
            const documentHeight = document.documentElement.offsetHeight;
            
            // Load more when user is 200px from bottom
            if (scrollPosition >= documentHeight - 200 && this.state.hasMore && !this.state.isLoading) {
                this.loadMoreVideos();
            }
        }, 250);
        
        this.events.push(
            addEvent(window, 'scroll', checkScroll, { passive: true }) // Mark as passive
        );
    }

    /**
     * Load more videos
     */
    async loadMoreVideos() {
        if (this.state.isLoading || !this.state.hasMore) {
            return;
        }
        
        this.state.isLoading = true;
        this.state.currentPage++;
        this.showLoadingState();
        
        try {
            const response = await this.core.ajax.loadMoreVideos(this.state.currentPage, this.state.activeFilters);
            
            if (response.success) {
                this.appendVideos(response.data.videos);
                this.state.hasMore = response.data.has_more;
                this.updateLoadMoreButton();
            } else {
                this.showError('Failed to load more videos');
                this.state.currentPage--; // Revert page increment
            }
            
        } catch (error) {
            console.error('VideoGrid: Load more failed:', error);
            this.showError('Error loading more videos');
            this.state.currentPage--; // Revert page increment
        } finally {
            this.state.isLoading = false;
            this.hideLoadingState();
        }
    }

    /**
     * Append new videos to existing grid
     * @param {Array} videos - Array of video data
     */
    appendVideos(videos) {
        this.elements.grids.forEach(grid => {
            videos.forEach(video => {
                const videoElement = this.createVideoElement(video);
                grid.appendChild(videoElement);
            });
            
            // Setup lazy loading for new elements
            this.setupLazyLoadingForElement(grid);
            
            // Re-layout masonry if needed
            if (this.state.masonryInstance) {
                this.state.masonryInstance.appended(grid.querySelectorAll('.video-grid-item:not(.masonry-processed)'));
                grid.querySelectorAll('.video-grid-item').forEach(item => {
                    item.classList.add('masonry-processed');
                });
            }
        });
        
        this.core.emit('video-grid:videos-appended', { videos, currentPage: this.state.currentPage });
    }

    /**
     * Update load more button state
     */
    updateLoadMoreButton() {
        this.elements.loadMoreButtons.forEach(button => {
            if (this.state.hasMore) {
                button.style.display = 'block';
                button.disabled = false;
                button.textContent = 'Load More Videos';
            } else {
                button.style.display = 'none';
            }
        });
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        this.elements.loadMoreButtons.forEach(button => {
            button.disabled = true;
            button.textContent = 'Loading...';
        });
        
        // Add loading class to grids
        this.elements.grids.forEach(grid => {
            grid.classList.add('loading');
        });
    }

    /**
     * Hide loading state
     */
    hideLoadingState() {
        this.elements.grids.forEach(grid => {
            grid.classList.remove('loading');
        });
    }

    /**
     * Show error message
     * @param {string} message - Error message
     */
    showError(message) {
        // Create or update error display
        let errorDiv = document.querySelector('.video-grid-error');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'video-grid-error';
            
            // Insert error div before first grid
            if (this.elements.grids.length > 0) {
                this.elements.grids[0].parentNode.insertBefore(errorDiv, this.elements.grids[0]);
            }
        }
        
        errorDiv.innerHTML = `
            <div class="error-message">
                <p>${message}</p>
                <button class="retry-button">Retry</button>
            </div>
        `;
        
        // Setup retry functionality
        const retryButton = errorDiv.querySelector('.retry-button');
        if (retryButton) {
            this.events.push(
                addEvent(retryButton, 'click', () => {
                    errorDiv.remove();
                    if (Object.keys(this.state.activeFilters).length > 0) {
                        this.executeFiltering();
                    } else {
                        this.loadMoreVideos();
                    }
                })
            );
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorDiv && errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for breakpoint changes
        this.core.on('breakpoint:changed', (event) => {
            this.handleBreakpointChange(event.detail);
        });
        
        // Listen for content updates
        this.core.on('content:loaded', () => {
            this.reinitialize();
        });
    }

    /**
     * Handle breakpoint changes
     * @param {Object} breakpointData - Breakpoint change data
     */
    handleBreakpointChange(breakpointData) {
        // Re-layout masonry on breakpoint change
        if (this.state.masonryInstance) {
            setTimeout(() => {
                this.state.masonryInstance.layout();
            }, 100);
        }
        
        // Update responsive columns for flex grids
        this.elements.grids.forEach(grid => {
            if (!grid.classList.contains('video-masonry')) {
                this.setupResponsiveColumns(grid);
            }
        });
    }

    /**
     * Clear all filters
     */
    clearFilters() {
        this.state.activeFilters = {};
        this.state.currentPage = 1;
        this.state.hasMore = true;
        
        // Remove filtering classes
        this.elements.gridContainers.forEach(container => {
            container.classList.remove('filtered-results');
        });
        
        this.elements.grids.forEach(grid => {
            grid.classList.remove('filtered');
        });
        
        // Reset form if it exists
        if (this.elements.filterForm) {
            this.elements.filterForm.reset();
        }
        
        // Reload original content
        this.executeFiltering();
        
        this.core.emit('video-grid:filters-cleared');
    }

    /**
     * Get current state
     * @returns {Object} Current component state
     */
    getState() {
        return {
            currentPage: this.state.currentPage,
            isLoading: this.state.isLoading,
            hasMore: this.state.hasMore,
            activeFilters: { ...this.state.activeFilters },
            gridCount: this.elements.grids.length,
            videoCount: this.elements.videoItems.length
        };
    }

    /**
     * Reinitialize component (useful for dynamic content)
     */
    reinitialize() {
        console.log('VideoGrid: Reinitializing...');
        
        // Re-cache elements
        this.cacheElements();
        
        // Re-setup lazy loading
        this.setupLazyLoading();
        
        // Re-layout grids
        this.initializeLayouts();
        
        console.log('VideoGrid: Reinitialization complete');
    }

    /**
     * Cleanup method
     */
    cleanup() {
        // Clean up event listeners
        this.events.forEach(cleanup => cleanup());
        this.events = [];
        
        // Clean up Intersection Observer
        if (this.state.lazyLoadObserver) {
            this.state.lazyLoadObserver.disconnect();
            this.state.lazyLoadObserver = null;
        }
        
        // Clean up Masonry
        if (this.state.masonryInstance) {
            this.state.masonryInstance.destroy();
            this.state.masonryInstance = null;
        }
        
        // Reset state
        this.state = {
            currentPage: 1,
            isLoading: false,
            hasMore: true,
            activeFilters: {},
            lazyLoadObserver: null,
            masonryInstance: null
        };
        
        console.log('VideoGrid: Cleaned up');
    }
}
