/**
 * Enhanced Features Component
 * Handles video hover previews, smooth scrolling, keyboard navigation, and other UX enhancements
 * Consolidates: enhanced-features.js, unified-preview.js
 */

import { debounce, addEvent, isInViewport } from '../core/utils.js';

export class EnhancedFeatures {
    constructor(core) {
        this.core = core;
        this.elements = {};
        this.state = {
            hoverDelay: 300,
            previewTimeout: null,
            activePreview: null,
            isPreviewPlaying: false,
            keyboardNavigationEnabled: true,
            watchedVideos: this.loadWatchedVideos() // Load watched videos from localStorage
        };
        this.events = [];
    }

    /**
     * Initialize enhanced features component
     */
    init() {
        console.log('EnhancedFeatures: Initializing...');
        
        // Cache DOM elements
        this.cacheElements();
        
        // Setup video hover previews
        this.setupHoverPreviews();
        
        // Setup smooth scrolling
        this.setupSmoothScrolling();
        
        // Setup keyboard navigation
        this.setupKeyboardNavigation();
        
        // Setup accessibility features
        this.setupAccessibilityFeatures();
        
        // Setup performance optimizations
        this.setupPerformanceOptimizations();

        // Track video clicks for personalization
        this.setupVideoClickTracking();
        
        // Register with core
        this.core.registerComponent('enhancedFeatures', this);
        
        console.log('EnhancedFeatures: Initialization complete');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            videoThumbnails: document.querySelectorAll('.video-thumbnail-container'),
            videoCards: document.querySelectorAll('.video-card, .videoBox'),
            smoothScrollTriggers: document.querySelectorAll('a[href^="#"]'),
            performerLetterLinks: document.querySelectorAll('a[href^="#letter-"]'),
            focusableElements: document.querySelectorAll('a, button, input, [tabindex]:not([tabindex="-1"])'),
            keyboardNavigableCards: document.querySelectorAll('.video-card[tabindex], .videoBox[tabindex]')
        };
    }

    /**
     * Setup video hover previews
     */
    setupHoverPreviews() {
        this.elements.videoThumbnails.forEach(container => {
            this.setupSingleHoverPreview(container);
        });
        
        console.log(`EnhancedFeatures: Set up hover previews for ${this.elements.videoThumbnails.length} containers`);
    }

    /**
     * Setup hover preview for a single container
     * @param {Element} container - Video thumbnail container
     */
    setupSingleHoverPreview(container) {
        const mouseEnterHandler = () => {
            // Clear any existing timeout
            if (this.state.previewTimeout) {
                clearTimeout(this.state.previewTimeout);
            }

            // Set timeout to delay preview start
            this.state.previewTimeout = setTimeout(() => {
                this.startPreview(container);
            }, this.state.hoverDelay);
        };

        const mouseLeaveHandler = () => {
            // Clear timeout if mouse leaves before delay
            if (this.state.previewTimeout) {
                clearTimeout(this.state.previewTimeout);
                this.state.previewTimeout = null;
            }

            // Stop any active preview
            this.stopPreview();
        };

        this.events.push(
            addEvent(container, 'mouseenter', mouseEnterHandler),
            addEvent(container, 'mouseleave', mouseLeaveHandler)
        );
    }

    /**
     * Start video preview
     * @param {Element} container - Video thumbnail container
     */
    startPreview(container) {
        // Stop any currently active preview
        this.stopPreview();
        
        const previewSrc = container.dataset.previewSrc;
        const webpPreviewSrc = container.dataset.webpPreviewSrc;
        
        if (previewSrc) {
            this.startVideoPreview(container, previewSrc);
        } else if (webpPreviewSrc) {
            this.startWebPPreview(container, webpPreviewSrc);
        }
    }

    /**
     * Start video preview (MP4/WebM)
     * @param {Element} container - Container element
     * @param {string} previewSrc - Video preview source
     */
    startVideoPreview(container, previewSrc) {
        // Create or get video element
        let video = container.querySelector('video.thumbnail-preview');
        
        if (!video) {
            video = document.createElement('video');
            video.className = 'thumbnail-preview';
            video.muted = true;
            video.loop = true;
            video.playsInline = true;
            video.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                z-index: 2;
                transition: opacity 0.3s ease;
            `;
            container.appendChild(video);
        }
        
        // Set source and load
        video.src = previewSrc;
        video.load();
        
        // Attempt to play
        const playPromise = video.play();
        
        if (playPromise !== undefined) {
            playPromise.then(() => {
                this.state.isPreviewPlaying = true;
                this.state.activePreview = video;
                container.classList.add('preview-active');
                
                this.core.emit('enhanced-features:preview-started', { container, type: 'video' });
            }).catch(error => {
                console.warn('EnhancedFeatures: Video preview playback prevented:', error);
                this.fallbackToStaticPreview(container);
            });
        }
    }

    /**
     * Start WebP animated preview
     * @param {Element} container - Container element
     * @param {string} webpPreviewSrc - WebP preview source
     */
    startWebPPreview(container, webpPreviewSrc) {
        const staticImg = container.querySelector('img.thumbnail-static, img:not(.thumbnail-webp)');
        
        // Fade out static image
        if (staticImg) {
            staticImg.style.opacity = '0';
        }
        
        // Create or get WebP image element
        let webpImg = container.querySelector('img.thumbnail-webp');
        
        if (!webpImg) {
            webpImg = document.createElement('img');
            webpImg.className = 'thumbnail-webp';
            webpImg.alt = 'Animated Preview';
            webpImg.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                z-index: 2;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            container.appendChild(webpImg);
        }
        
        // Load and show WebP
        webpImg.src = webpPreviewSrc;
        webpImg.style.opacity = '1';
        
        // Store reference
        this.state.activePreview = webpImg;
        container.classList.add('preview-active');
        
        this.core.emit('enhanced-features:preview-started', { container, type: 'webp' });
    }

    /**
     * Fallback to static preview when video fails
     * @param {Element} container - Container element
     */
    fallbackToStaticPreview(container) {
        // Add a subtle hover effect as fallback
        container.style.transform = 'scale(1.02)';
        container.style.transition = 'transform 0.3s ease';
        
        this.state.activePreview = container;
        container.classList.add('preview-active');
    }

    /**
     * Stop all active previews
     */
    stopPreview() {
        if (this.state.activePreview) {
            const container = this.state.activePreview.closest('.video-thumbnail-container') || 
                             this.state.activePreview;
            
            if (this.state.activePreview.tagName === 'VIDEO') {
                // Stop and reset video
                this.state.activePreview.pause();
                this.state.activePreview.currentTime = 0;
                this.state.isPreviewPlaying = false;
            } else if (this.state.activePreview.classList && this.state.activePreview.classList.contains('thumbnail-webp')) {
                // Fade out WebP and restore static image
                this.state.activePreview.style.opacity = '0';
                const staticImg = container.querySelector('img.thumbnail-static, img:not(.thumbnail-webp)');
                if (staticImg) {
                    staticImg.style.opacity = '1';
                }
            } else {
                // Reset transform for fallback preview
                container.style.transform = '';
            }
            
            container.classList.remove('preview-active');
            this.state.activePreview = null;
            
            this.core.emit('enhanced-features:preview-stopped', { container });
        }
    }

    /**
     * Setup smooth scrolling for anchor links
     */
    setupSmoothScrolling() {
        this.elements.smoothScrollTriggers.forEach(link => {
            this.events.push(
                addEvent(link, 'click', (e) => {
                    const href = link.getAttribute('href');
                    
                    // Only handle internal anchor links
                    if (href && href.startsWith('#')) {
                        const target = document.querySelector(href);
                        
                        if (target) {
                            e.preventDefault();
                            this.smoothScrollTo(target);
                        }
                    }
                }, { passive: true }) // Mark as passive
            );
        });

        // Special handling for performer directory letter links
        this.elements.performerLetterLinks.forEach(link => {
            this.events.push(
                addEvent(link, 'click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href');
                    const target = document.querySelector(targetId);
                    
                    if (target) {
                        this.smoothScrollTo(target, 50); // Extra offset for better visibility
                    }
                }, { passive: true }) // Mark as passive
            );
        });
    }

    /**
     * Smooth scroll to target element
     * @param {Element} target - Target element
     * @param {number} offset - Additional offset from top
     */
    smoothScrollTo(target, offset = 20) {
        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
        
        // Use native smooth scrolling if supported
        if ('scrollBehavior' in document.documentElement.style) {
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        } else {
            // Fallback smooth scrolling
            this.fallbackSmoothScroll(targetPosition);
        }
        
        this.core.emit('enhanced-features:smooth-scroll', { target, offset });
    }

    /**
     * Fallback smooth scrolling for older browsers
     * @param {number} targetPosition - Target scroll position
     */
    fallbackSmoothScroll(targetPosition) {
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        const duration = Math.min(Math.abs(distance) / 2, 500); // Max 500ms
        let start = null;

        const animation = (currentTime) => {
            if (start === null) start = currentTime;
            const timeElapsed = currentTime - start;
            const progress = Math.min(timeElapsed / duration, 1);
            
            // Easing function (ease-out)
            const ease = 1 - Math.pow(1 - progress, 3);
            
            window.scrollTo(0, startPosition + (distance * ease));
            
            if (timeElapsed < duration) {
                requestAnimationFrame(animation);
            }
        };
        
        requestAnimationFrame(animation);
    }

    /**
     * Setup keyboard navigation for video cards
     */
    setupKeyboardNavigation() {
        if (!this.state.keyboardNavigationEnabled) {
            return;
        }

        // Make video cards focusable if they aren't already
        this.elements.videoCards.forEach(card => {
            if (!card.hasAttribute('tabindex')) {
                card.setAttribute('tabindex', '0');
            }
        });

        // Add keyboard event listeners
        this.elements.keyboardNavigableCards.forEach(card => {
            this.events.push(
                addEvent(card, 'keydown', (e) => {
                    this.handleCardKeydown(e, card);
                })
            );
        });

        // Global keyboard shortcuts
        this.events.push(
            addEvent(document, 'keydown', (e) => {
                this.handleGlobalKeydown(e);
            })
        );
    }

    /**
     * Handle keydown events on video cards
     * @param {Event} e - Keydown event
     * @param {Element} card - Video card element
     */
    handleCardKeydown(e, card) {
        switch (e.key) {
            case 'Enter':
            case ' ':
                e.preventDefault();
                this.activateCard(card);
                break;
                
            case 'ArrowRight':
            case 'ArrowDown':
                e.preventDefault();
                this.focusNextCard(card);
                break;
                
            case 'ArrowLeft':
            case 'ArrowUp':
                e.preventDefault();
                this.focusPreviousCard(card);
                break;
                
            case 'Home':
                e.preventDefault();
                this.focusFirstCard();
                break;
                
            case 'End':
                e.preventDefault();
                this.focusLastCard();
                break;
        }
    }

    /**
     * Handle global keyboard shortcuts
     * @param {Event} e - Keydown event
     */
    handleGlobalKeydown(e) {
        // Alt + H: Focus on first video card
        if (e.altKey && e.key === 'h') {
            e.preventDefault();
            this.focusFirstCard();
        }
        
        // Escape: Clear focus from video cards
        if (e.key === 'Escape') {
            const focusedCard = document.activeElement;
            if (focusedCard && (focusedCard.classList.contains('video-card') || focusedCard.classList.contains('videoBox'))) {
                focusedCard.blur();
            }
        }
    }

    /**
     * Activate a video card (click the main link)
     * @param {Element} card - Video card element
     */
    activateCard(card) {
        const link = card.querySelector('a[href]:not([href^="#"])');
        if (link) {
            // Create and dispatch click event
            const clickEvent = new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            });
            link.dispatchEvent(clickEvent);
            
            this.core.emit('enhanced-features:card-activated', { card, link });
        }
    }

    /**
     * Focus next video card
     * @param {Element} currentCard - Currently focused card
     */
    focusNextCard(currentCard) {
        const cards = Array.from(this.elements.keyboardNavigableCards);
        const currentIndex = cards.indexOf(currentCard);
        const nextIndex = (currentIndex + 1) % cards.length;
        
        cards[nextIndex].focus();
        this.scrollCardIntoView(cards[nextIndex]);
    }

    /**
     * Focus previous video card
     * @param {Element} currentCard - Currently focused card
     */
    focusPreviousCard(currentCard) {
        const cards = Array.from(this.elements.keyboardNavigableCards);
        const currentIndex = cards.indexOf(currentCard);
        const previousIndex = currentIndex === 0 ? cards.length - 1 : currentIndex - 1;
        
        cards[previousIndex].focus();
        this.scrollCardIntoView(cards[previousIndex]);
    }

    /**
     * Focus first video card
     */
    focusFirstCard() {
        if (this.elements.keyboardNavigableCards.length > 0) {
            this.elements.keyboardNavigableCards[0].focus();
            this.scrollCardIntoView(this.elements.keyboardNavigableCards[0]);
        }
    }

    /**
     * Focus last video card
     */
    focusLastCard() {
        const cards = this.elements.keyboardNavigableCards;
        if (cards.length > 0) {
            cards[cards.length - 1].focus();
            this.scrollCardIntoView(cards[cards.length - 1]);
        }
    }

    /**
     * Scroll card into view if needed
     * @param {Element} card - Video card element
     */
    scrollCardIntoView(card) {
        if (!isInViewport(card)) {
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            });
        }
    }

    /**
     * Setup accessibility features
     */
    setupAccessibilityFeatures() {
        // Add ARIA labels to video cards
        this.elements.videoCards.forEach((card, index) => {
            if (!card.hasAttribute('aria-label')) {
                const title = card.querySelector('.video-title, h3, h2');
                const titleText = title ? title.textContent.trim() : `Video ${index + 1}`;
                card.setAttribute('aria-label', `Play video: ${titleText}`);
            }
            
            // Add role if not present
            if (!card.hasAttribute('role')) {
                card.setAttribute('role', 'button');
            }
        });

        // Improve focus indicators
        this.addFocusIndicators();
        
        // Setup reduced motion preferences
        this.setupReducedMotion();
    }

    /**
     * Add enhanced focus indicators
     */
    addFocusIndicators() {
        const style = document.createElement('style');
        style.textContent = `
            .video-card:focus,
            .videoBox:focus,
            .video-thumbnail-container:focus {
                outline: 2px solid #007cba;
                outline-offset: 2px;
                box-shadow: 0 0 0 4px rgba(0, 124, 186, 0.3);
            }
            
            .video-card:focus .video-thumbnail-container,
            .videoBox:focus .video-thumbnail-container {
                transform: scale(1.02);
                transition: transform 0.2s ease;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Setup reduced motion preferences
     */
    setupReducedMotion() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        
        const handleReducedMotion = (mediaQuery) => {
            if (mediaQuery.matches) {
                // Disable animations and previews for users who prefer reduced motion
                this.state.hoverDelay = 0;
                document.body.classList.add('reduced-motion');
                
                // Disable video previews
                this.elements.videoThumbnails.forEach(container => {
                    container.style.pointerEvents = 'auto';
                    container.dataset.previewDisabled = 'true';
                });
            } else {
                document.body.classList.remove('reduced-motion');
                
                // Re-enable video previews
                this.elements.videoThumbnails.forEach(container => {
                    delete container.dataset.previewDisabled;
                });
            }
        };
        
        // Initial check
        handleReducedMotion(prefersReducedMotion);
        
        // Listen for changes
        if (prefersReducedMotion.addEventListener) {
            prefersReducedMotion.addEventListener('change', handleSystemPreferenceChange);
        } else {
                // Fallback for older browsers
            prefersReducedMotion.addListener(handleSystemPreferenceChange);
        }
    }

    /**
     * Setup performance optimizations
     */
    setupPerformanceOptimizations() {
        // Throttled scroll handler for performance
        const throttledScrollHandler = debounce(() => {
            // Check if any video cards are in viewport and eligible for preview
            this.elements.videoThumbnails.forEach(container => {
                if (container.dataset.previewDisabled) return;
                
                if (!isInViewport(container)) {
                    // Stop preview if card scrolled out of view
                    if (this.state.activePreview && 
                        (this.state.activePreview === container || 
                         this.state.activePreview.closest('.video-thumbnail-container') === container)) {
                        this.stopPreview();
                    }
                }
            });
        }, 100);
        
        this.events.push(
            addEvent(window, 'scroll', throttledScrollHandler, { passive: true })
        );
        
        // Pause all video previews when page becomes hidden
        this.events.push(
            addEvent(document, 'visibilitychange', () => {
                if (document.hidden) {
                    this.stopPreview();
                }
            })
        );
    }

    /**
     * Track video clicks for personalization.
     * Stores clicked video IDs in localStorage and sends to backend.
     */
    setupVideoClickTracking() {
        this.elements.videoCards.forEach(card => {
            const link = card.querySelector('a[href]');
            if (link) {
                this.events.push(
                    addEvent(link, 'click', (e) => {
                        const videoId = card.dataset.videoId;
                        if (videoId) {
                            this.addWatchedVideo(videoId);
                            this.core.ajax.trackUserBehavior(videoId, 'click').catch(console.error);
                        }
                    }, { passive: true }) // Mark as passive
                );
            }
        });
    }

    /**
     * Load watched video IDs from localStorage.
     * @returns {Array} Array of watched video IDs.
     */
    loadWatchedVideos() {
        try {
            const watched = localStorage.getItem('watchedVideos');
            return watched ? JSON.parse(watched) : [];
        } catch (e) {
            console.error('Failed to load watched videos from localStorage:', e);
            return [];
        }
    }

    /**
     * Add a video ID to the watched list and save to localStorage.
     * @param {string} videoId - The ID of the video watched.
     */
    addWatchedVideo(videoId) {
        const id = parseInt(videoId);
        if (isNaN(id)) return;

        // Add to the beginning of the array
        this.state.watchedVideos = [id, ...this.state.watchedVideos.filter(v => v !== id)];
        
        // Keep the list to a reasonable size (e.g., last 50 videos)
        if (this.state.watchedVideos.length > 50) {
            this.state.watchedVideos = this.state.watchedVideos.slice(0, 50);
        }
        
        try {
            localStorage.setItem('watchedVideos', JSON.stringify(this.state.watchedVideos));
        } catch (e) {
            console.error('Failed to save watched videos to localStorage:', e);
        }
    }

    /**
     * Get the list of watched video IDs.
     * @returns {Array} Array of watched video IDs.
     */
    getWatchedVideos() {
        return this.state.watchedVideos;
    }

    /**
     * Update hover delay
     * @param {number} delay - New delay in milliseconds
     */
    setHoverDelay(delay) {
        this.state.hoverDelay = Math.max(0, delay);
        this.core.emit('enhanced-features:hover-delay-changed', { delay: this.state.hoverDelay });
    }

    /**
     * Enable/disable keyboard navigation
     * @param {boolean} enabled - Whether to enable keyboard navigation
     */
    setKeyboardNavigation(enabled) {
        this.state.keyboardNavigationEnabled = enabled;
        
        if (enabled) {
            this.setupKeyboardNavigation();
        } else {
            // Remove tabindex from cards
            this.elements.videoCards.forEach(card => {
                if (card.getAttribute('tabindex') === '0') {
                    card.removeAttribute('tabindex');
                }
            });
        }
        
        this.core.emit('enhanced-features:keyboard-navigation-changed', { enabled });
    }

    /**
     * Get current state
     * @returns {Object} Current component state
     */
    getState() {
        return {
            hoverDelay: this.state.hoverDelay,
            isPreviewPlaying: this.state.isPreviewPlaying,
            keyboardNavigationEnabled: this.state.keyboardNavigationEnabled,
            activePreview: !!this.state.activePreview,
            thumbnailCount: this.elements.videoThumbnails.length,
            cardCount: this.elements.videoCards.length,
            watchedVideosCount: this.state.watchedVideos.length
        };
    }

    /**
     * Reinitialize for dynamic content
     */
    reinitialize() {
        console.log('EnhancedFeatures: Reinitializing...');
        
        // Stop any active previews
        this.stopPreview();
        
        // Re-cache elements
        this.cacheElements();
        
        // Re-setup hover previews for new elements
        this.setupHoverPreviews();
        
        // Re-setup keyboard navigation
        if (this.state.keyboardNavigationEnabled) {
            this.setupKeyboardNavigation();
        }
        
        // Re-setup accessibility features
        this.setupAccessibilityFeatures();

        // Re-setup video click tracking for new elements
        this.setupVideoClickTracking();
        
        console.log('EnhancedFeatures: Reinitialization complete');
    }

    /**
     * Cleanup method
     */
    cleanup() {
        // Stop any active previews
        this.stopPreview();
        
        // Clear timeouts
        if (this.state.previewTimeout) {
            clearTimeout(this.state.previewTimeout);
            this.state.previewTimeout = null;
        }
        
        // Clean up event listeners
        this.events.forEach(cleanup => cleanup());
        this.events = [];
        
        // Reset state
        this.state = {
            hoverDelay: 300,
            previewTimeout: null,
            activePreview: null,
            isPreviewPlaying: false,
            keyboardNavigationEnabled: true,
            watchedVideos: []
        };
        
        console.log('EnhancedFeatures: Cleaned up');
    }
}
