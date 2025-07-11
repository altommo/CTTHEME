/**
 * Ads Manager Component
 * Handles ad integration, synchronization, and management
 * Consolidates: ads.js, header-ad-sync.js
 */

import { debounce, addEvent } from '../core/utils.js';

export class AdsManager {
    constructor(core) {
        this.core = core;
        this.elements = {};
        this.state = {
            adsLoaded: false,
            headerAdSynced: false,
            vastClient: null,
            adEvents: new Map(),
            activeVideoAds: new Map()
        };
        this.events = [];
    }

    /**
     * Initialize ads manager
     */
    init() {
        console.log('AdsManager: Initializing...');
        
        // Cache DOM elements
        this.cacheElements();
        
        // Setup banner ads
        this.setupBannerAds();
        
        // Setup video ads
        this.setupVideoAds();
        
        // Setup header ad synchronization
        this.setupHeaderAdSync();
        
        // Setup ad event tracking
        this.setupAdEventTracking();
        
        // Load external ad libraries if needed
        this.loadAdLibraries();
        
        // Register with core
        this.core.registerComponent('adsManager', this);
        
        console.log('AdsManager: Initialization complete');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            adContainers: document.querySelectorAll('.ad-container'),
            bannerAds: document.querySelectorAll('.banner-ad, .ad-banner'),
            headerAds: document.querySelectorAll('.header-ad, .ph-nav-ad'),
            sidebarAds: document.querySelectorAll('.sidebar-ad'),
            footerAds: document.querySelectorAll('.footer-ad'),
            videoAdContainers: document.querySelectorAll('.video-ad-container'),
            prerollContainers: document.querySelectorAll('.preroll-ad'),
            midrollContainers: document.querySelectorAll('.midroll-ad'),
            postrollContainers: document.querySelectorAll('.postroll-ad'),
            stickyAds: document.querySelectorAll('.sticky-ad'),
            mobileAds: document.querySelectorAll('.mobile-ad')
        };
    }

    /**
     * Setup banner ads
     */
    setupBannerAds() {
        this.elements.adContainers.forEach(container => {
            this.initializeAdContainer(container);
        });

        // Setup responsive ad behavior
        this.setupResponsiveAds();
        
        console.log(`AdsManager: Initialized ${this.elements.adContainers.length} ad containers`);
    }

    /**
     * Initialize a single ad container
     * @param {Element} container - Ad container element
     */
    initializeAdContainer(container) {
        const adCode = container.dataset.adCode;
        const adType = container.dataset.adType || 'banner';
        const adSize = container.dataset.adSize || '728x90';
        const adNetwork = container.dataset.adNetwork || 'generic';
        
        if (adCode) {
            this.loadAdContent(container, adCode, adType, adNetwork);
        } else {
            this.loadPlaceholderAd(container, adSize);
        }
        
        // Setup ad visibility tracking
        this.setupAdVisibilityTracking(container);
    }

    /**
     * Load ad content into container
     * @param {Element} container - Ad container
     * @param {string} adCode - Ad code/script
     * @param {string} adType - Type of ad
     * @param {string} adNetwork - Ad network
     */
    loadAdContent(container, adCode, adType, adNetwork) {
        try {
            switch (adNetwork.toLowerCase()) {
                case 'adsense':
                    this.loadAdSenseAd(container, adCode);
                    break;
                case 'dfp':
                case 'gam':
                    this.loadGoogleAdManagerAd(container, adCode);
                    break;
                case 'prebid':
                    this.loadPrebidAd(container, adCode);
                    break;
                default:
                    this.loadGenericAd(container, adCode);
                    break;
            }
            
            this.core.emit('ads:ad-loaded', { container, adType, adNetwork });
        } catch (error) {
            console.error('AdsManager: Failed to load ad:', error);
            this.loadPlaceholderAd(container);
        }
    }

    /**
     * Load Google AdSense ad
     * @param {Element} container - Ad container
     * @param {string} adCode - AdSense ad code
     */
    loadAdSenseAd(container, adCode) {
        // Create AdSense container
        const adDiv = document.createElement('div');
        adDiv.innerHTML = adCode;
        container.appendChild(adDiv);
        
        // Load AdSense script if not already loaded
        if (!window.adsbygoogle) {
            this.loadScript('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', () => {
                (adsbygoogle = window.adsbygoogle || []).push({});
            });
        } else {
            (adsbygoogle = window.adsbygoogle || []).push({});
        }
    }

    /**
     * Load Google Ad Manager ad
     * @param {Element} container - Ad container
     * @param {string} adCode - GAM ad unit code
     */
    loadGoogleAdManagerAd(container, adCode) {
        const adId = `ad-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
        container.id = adId;
        
        // Load GPT if not already loaded
        if (!window.googletag) {
            this.loadScript('https://securepubads.g.doubleclick.net/tag/js/gpt.js', () => {
                window.googletag = window.googletag || { cmd: [] };
                this.defineGAMSlot(adId, adCode, container);
            });
        } else {
            this.defineGAMSlot(adId, adCode, container);
        }
    }

    /**
     * Define Google Ad Manager slot
     * @param {string} adId - Ad container ID
     * @param {string} adCode - Ad unit code
     * @param {Element} container - Ad container
     */
    defineGAMSlot(adId, adCode, container) {
        googletag.cmd.push(() => {
            const adSize = container.dataset.adSize ? container.dataset.adSize.split('x').map(Number) : [728, 90];
            const slot = googletag.defineSlot(adCode, adSize, adId).addService(googletag.pubads());
            
            googletag.pubads().enableSingleRequest();
            googletag.enableServices();
            googletag.display(adId);
            
            // Store slot reference
            container.dataset.adSlot = slot.getSlotElementId();
        });
    }

    /**
     * Load Prebid ad
     * @param {Element} container - Ad container
     * @param {string} adCode - Prebid ad unit code
     */
    loadPrebidAd(container, adCode) {
        // Prebid implementation would go here
        // For now, fall back to generic ad loading
        this.loadGenericAd(container, adCode);
    }

    /**
     * Load generic ad
     * @param {Element} container - Ad container
     * @param {string} adCode - Generic ad code
     */
    loadGenericAd(container, adCode) {
        // Handle raw HTML/JavaScript ad code
        const adDiv = document.createElement('div');
        adDiv.innerHTML = adCode;
        container.appendChild(adDiv);
        
        // Execute any script tags in the ad code
        const scripts = adDiv.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            document.head.appendChild(newScript);
        });
    }

    /**
     * Load placeholder ad
     * @param {Element} container - Ad container
     * @param {string} adSize - Ad size (e.g., "728x90")
     */
    loadPlaceholderAd(container, adSize = '728x90') {
        const [width, height] = adSize.split('x').map(Number);
        
        const placeholder = document.createElement('div');
        placeholder.className = 'ad-placeholder';
        placeholder.style.cssText = `
            width: ${width}px;
            height: ${height}px;
            background: #f0f0f0;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-family: Arial, sans-serif;
            font-size: 14px;
        `;
        placeholder.textContent = `Ad Space ${adSize}`;
        
        container.appendChild(placeholder);
    }

    /**
     * Setup video ads
     */
    setupVideoAds() {
        // Listen for video player initialization
        this.core.on('video-player:initialized', (event) => {
            const { player } = event.detail;
            this.setupVideoPlayerAds(player);
        });

        // Setup existing video players
        const videoPlayer = this.core.getComponent('videoPlayer');
        if (videoPlayer) {
            videoPlayer.getAllPlayers().forEach(player => {
                this.setupVideoPlayerAds(player);
            });
        }
    }

    /**
     * Setup ads for a video player
     * @param {Object} player - Video player instance
     */
    setupVideoPlayerAds(player) {
        if (player.type !== 'mp4' && player.type !== 'self-hosted') {
            return; // Only setup ads for self-hosted videos
        }

        const videoElement = player.element;
        const container = player.container;
        
        // Setup preroll ad
        this.setupPrerollAd(player);
        
        // Setup midroll ads
        this.setupMidrollAds(player);
        
        // Setup postroll ad
        this.setupPostrollAd(player);
        
        // Store player reference
        this.state.activeVideoAds.set(player.id, {
            player,
            prerollPlayed: false,
            midrollsPlayed: [],
            postrollPlayed: false
        });
    }

    /**
     * Setup preroll ad for video player
     * @param {Object} player - Video player instance
     */
    setupPrerollAd(player) {
        const adContainer = player.container.querySelector('.preroll-ad');
        if (!adContainer) return;

        const videoElement = player.element;
        
        // Listen for play attempts
        player.events.push(
            addEvent(videoElement, 'play', (e) => {
                const adState = this.state.activeVideoAds.get(player.id);
                
                if (adState && !adState.prerollPlayed) {
                    e.preventDefault();
                    videoElement.pause();
                    this.playPrerollAd(player, adContainer);
                }
            })
        );
    }

    /**
     * Play preroll ad
     * @param {Object} player - Video player instance
     * @param {Element} adContainer - Ad container element
     */
    async playPrerollAd(player, adContainer) {
        const adState = this.state.activeVideoAds.get(player.id);
        if (!adState) return;

        try {
            // Show ad container
            adContainer.style.display = 'block';
            adContainer.style.position = 'absolute';
            adContainer.style.top = '0';
            adContainer.style.left = '0';
            adContainer.style.width = '100%';
            adContainer.style.height = '100%';
            adContainer.style.zIndex = '10';
            
            // Load VAST ad if configured
            const vastUrl = adContainer.dataset.vastUrl;
            if (vastUrl && this.state.vastClient) {
                await this.playVastAd(vastUrl, adContainer);
            } else {
                // Show static ad for configured duration
                const adDuration = parseInt(adContainer.dataset.duration) || 5000;
                await this.showStaticAd(adContainer, adDuration);
            }
            
            // Mark preroll as played
            adState.prerollPlayed = true;
            
            // Hide ad container
            adContainer.style.display = 'none';
            
            // Start main video
            player.element.play();
            
            this.core.emit('ads:preroll-completed', { player });
            
        } catch (error) {
            console.error('AdsManager: Preroll ad failed:', error);
            // Continue with main video on error
            adContainer.style.display = 'none';
            player.element.play();
        }
    }

    /**
     * Setup midroll ads for video player
     * @param {Object} player - Video player instance
     */
    setupMidrollAds(player) {
        const videoElement = player.element;
        const midrollTimes = this.getMidrollTimes(player);
        
        if (midrollTimes.length === 0) return;
        
        // Listen for time updates
        player.events.push(
            addEvent(videoElement, 'timeupdate', () => {
                this.checkMidrollTriggers(player, midrollTimes);
            })
        );
    }

    /**
     * Get midroll ad times for a video
     * @param {Object} player - Video player instance
     * @returns {Array} Array of midroll times in seconds
     */
    getMidrollTimes(player) {
        const container = player.container;
        const midrollData = container.dataset.midrollTimes;
        
        if (midrollData) {
            return midrollData.split(',').map(time => parseInt(time.trim()));
        }
        
        // Default: show midroll at 50% of video duration
        return ['50%'];
    }

    /**
     * Check if any midroll ads should be triggered
     * @param {Object} player - Video player instance
     * @param {Array} midrollTimes - Array of midroll trigger times
     */
    checkMidrollTriggers(player, midrollTimes) {
        const videoElement = player.element;
        const currentTime = videoElement.currentTime;
        const duration = videoElement.duration;
        const adState = this.state.activeVideoAds.get(player.id);
        
        if (!adState || !duration) return;
        
        midrollTimes.forEach((time, index) => {
            if (adState.midrollsPlayed.includes(index)) return;
            
            let triggerTime;
            if (typeof time === 'string' && time.includes('%')) {
                const percentage = parseInt(time) / 100;
                triggerTime = duration * percentage;
            } else {
                triggerTime = parseInt(time);
            }
            
            if (currentTime >= triggerTime) {
                this.playMidrollAd(player, index);
                adState.midrollsPlayed.push(index);
            }
        });
    }

    /**
     * Play midroll ad
     * @param {Object} player - Video player instance
     * @param {number} adIndex - Midroll ad index
     */
    async playMidrollAd(player, adIndex) {
        const adContainer = player.container.querySelector('.midroll-ad');
        if (!adContainer) return;

        try {
            // Pause main video
            player.element.pause();
            
            // Show midroll ad
            await this.playPrerollAd(player, adContainer); // Reuse preroll logic
            
            this.core.emit('ads:midroll-completed', { player, adIndex });
            
        } catch (error) {
            console.error('AdsManager: Midroll ad failed:', error);
        }
    }

    /**
     * Setup postroll ad for video player
     * @param {Object} player - Video player instance
     */
    setupPostrollAd(player) {
        const adContainer = player.container.querySelector('.postroll-ad');
        if (!adContainer) return;

        const videoElement = player.element;
        
        // Listen for video end
        player.events.push(
            addEvent(videoElement, 'ended', () => {
                this.playPostrollAd(player, adContainer);
            })
        );
    }

    /**
     * Play postroll ad
     * @param {Object} player - Video player instance
     * @param {Element} adContainer - Ad container element
     */
    async playPostrollAd(player, adContainer) {
        const adState = this.state.activeVideoAds.get(player.id);
        if (!adState || adState.postrollPlayed) return;

        try {
            // Show postroll ad
            await this.playPrerollAd(player, adContainer); // Reuse preroll logic
            
            // Mark postroll as played
            adState.postrollPlayed = true;
            
            this.core.emit('ads:postroll-completed', { player });
            
        } catch (error) {
            console.error('AdsManager: Postroll ad failed:', error);
        }
    }

    /**
     * Show static ad for specified duration
     * @param {Element} adContainer - Ad container
     * @param {number} duration - Duration in milliseconds
     * @returns {Promise} Promise that resolves when ad completes
     */
    showStaticAd(adContainer, duration) {
        return new Promise((resolve) => {
            // Add skip button after 5 seconds
            const skipDelay = Math.min(5000, duration - 1000);
            
            let skipButton;
            const skipTimer = setTimeout(() => {
                skipButton = document.createElement('button');
                skipButton.textContent = 'Skip Ad';
                skipButton.className = 'ad-skip-button';
                skipButton.style.cssText = `
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    padding: 5px 10px;
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    border: none;
                    border-radius: 3px;
                    cursor: pointer;
                    z-index: 11;
                `;
                
                adContainer.appendChild(skipButton);
                
                skipButton.addEventListener('click', () => {
                    clearTimeout(adTimer);
                    resolve();
                });
            }, skipDelay);
            
            // Auto-complete after duration
            const adTimer = setTimeout(() => {
                clearTimeout(skipTimer);
                if (skipButton) {
                    skipButton.remove();
                }
                resolve();
            }, duration);
        });
    }

    /**
     * Play VAST ad
     * @param {string} vastUrl - VAST URL
     * @param {Element} adContainer - Ad container
     * @returns {Promise} Promise that resolves when ad completes
     */
    async playVastAd(vastUrl, adContainer) {
        if (!this.state.vastClient) {
            throw new Error('VAST client not available');
        }
        
        try {
            const vastResponse = await this.state.vastClient.get(vastUrl);
            // VAST implementation would go here
            // For now, fall back to static ad
            return this.showStaticAd(adContainer, 15000);
        } catch (error) {
            console.error('AdsManager: VAST ad failed:', error);
            return this.showStaticAd(adContainer, 5000);
        }
    }

    /**
     * Setup header ad synchronization
     */
    setupHeaderAdSync() {
        const headerAds = this.elements.headerAds;
        if (headerAds.length === 0) return;

        // Sync header ad dimensions with navigation
        const navigation = this.core.getComponent('navigation');
        if (navigation) {
            this.syncHeaderAdDimensions();
            
            // Re-sync on window resize
            this.events.push(
                addEvent(window, 'resize', debounce(() => {
                    this.syncHeaderAdDimensions();
                }, 250))
            );
        }
        
        this.state.headerAdSynced = true;
        console.log('AdsManager: Header ad synchronization set up');
    }

    /**
     * Synchronize header ad dimensions
     */
    syncHeaderAdDimensions() {
        const navElement = document.querySelector('.ph-nav');
        if (!navElement) return;

        const navHeight = navElement.offsetHeight;
        const navWidth = navElement.offsetWidth;
        
        this.elements.headerAds.forEach(ad => {
            // Adjust ad container to fit within nav dimensions
            const maxWidth = Math.min(navWidth - 40, 728); // Leave some padding
            const maxHeight = Math.min(navHeight - 20, 90);
            
            ad.style.maxWidth = maxWidth + 'px';
            ad.style.maxHeight = maxHeight + 'px';
            ad.style.overflow = 'hidden';
        });
        
        this.core.emit('ads:header-sync-updated', { navHeight, navWidth });
    }

    /**
     * Setup responsive ad behavior
     */
    setupResponsiveAds() {
        const updateAdSizes = () => {
            const viewportWidth = window.innerWidth;
            
            this.elements.adContainers.forEach(container => {
                const adType = container.dataset.adType;
                const isMobileAd = container.classList.contains('mobile-ad');
                const isDesktopAd = container.classList.contains('desktop-ad');
                
                // Hide/show ads based on viewport
                if (viewportWidth <= 768) {
                    // Mobile
                    if (isDesktopAd) {
                        container.style.display = 'none';
                    } else if (isMobileAd) {
                        container.style.display = 'block';
                    }
                } else {
                    // Desktop
                    if (isMobileAd) {
                        container.style.display = 'none';
                    } else if (isDesktopAd) {
                        container.style.display = 'block';
                    }
                }
                
                // Adjust ad sizes for responsive design
                this.adjustAdSize(container, viewportWidth);
            });
        };
        
        // Initial update
        updateAdSizes();
        
        // Update on resize
        this.events.push(
            addEvent(window, 'resize', debounce(updateAdSizes, 250))
        );
    }

    /**
     * Adjust ad size based on viewport
     * @param {Element} container - Ad container
     * @param {number} viewportWidth - Viewport width
     */
    adjustAdSize(container, viewportWidth) {
        const originalSize = container.dataset.adSize || '728x90';
        let newSize = originalSize;
        
        // Common responsive ad size mappings
        if (viewportWidth <= 320) {
            // Small mobile
            if (originalSize === '728x90') newSize = '320x50';
            if (originalSize === '300x250') newSize = '300x250';
        } else if (viewportWidth <= 768) {
            // Mobile/tablet
            if (originalSize === '728x90') newSize = '320x50';
            if (originalSize === '970x250') newSize = '728x90';
        }
        
        if (newSize !== originalSize) {
            const [width, height] = newSize.split('x').map(Number);
            container.style.width = width + 'px';
            container.style.height = height + 'px';
            
            // Update data attribute
            container.dataset.currentSize = newSize;
        }
    }

    /**
     * Setup ad visibility tracking
     * @param {Element} container - Ad container
     */
    setupAdVisibilityTracking(container) {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.trackAdVisibility(entry.target);
                    }
                });
            }, {
                threshold: 0.5 // Ad must be 50% visible
            });
            
            observer.observe(container);
        }
    }

    /**
     * Track ad visibility
     * @param {Element} adContainer - Ad container that became visible
     */
    trackAdVisibility(adContainer) {
        const adId = adContainer.id || adContainer.dataset.adId;
        if (!adId || this.state.adEvents.has(adId)) return;
        
        // Mark as tracked
        this.state.adEvents.set(adId, {
            viewTime: Date.now(),
            adType: adContainer.dataset.adType,
            adNetwork: adContainer.dataset.adNetwork
        });
        
        this.core.emit('ads:ad-viewed', {
            adId,
            adType: adContainer.dataset.adType,
            adNetwork: adContainer.dataset.adNetwork
        });
    }

    /**
     * Setup ad event tracking
     */
    setupAdEventTracking() {
        // Track ad clicks
        this.elements.adContainers.forEach(container => {
            this.events.push(
                addEvent(container, 'click', (e) => {
                    // Check if click is on an actual ad, not just the container
                    if (e.target !== container) {
                        this.trackAdClick(container);
                    }
                })
            );
        });
        
        // Track page visibility changes (for viewability)
        this.events.push(
            addEvent(document, 'visibilitychange', () => {
                if (document.hidden) {
                    this.pauseVideoAds();
                } else {
                    this.resumeVideoAds();
                }
            })
        );
    }

    /**
     * Track ad click
     * @param {Element} adContainer - Clicked ad container
     */
    trackAdClick(adContainer) {
        const adId = adContainer.id || adContainer.dataset.adId;
        
        this.core.emit('ads:ad-clicked', {
            adId,
            adType: adContainer.dataset.adType,
            adNetwork: adContainer.dataset.adNetwork,
            timestamp: Date.now()
        });
    }

    /**
     * Pause all active video ads
     */
    pauseVideoAds() {
        this.state.activeVideoAds.forEach((adState) => {
            const videoAds = adState.player.container.querySelectorAll('video[src*="ad"]');
            videoAds.forEach(ad => ad.pause());
        });
    }

    /**
     * Resume all active video ads
     */
    resumeVideoAds() {
        this.state.activeVideoAds.forEach((adState) => {
            const videoAds = adState.player.container.querySelectorAll('video[src*="ad"]');
            videoAds.forEach(ad => {
                if (!ad.ended) {
                    ad.play().catch(() => {}); // Ignore play failures
                }
            });
        });
    }

    /**
     * Load external script
     * @param {string} src - Script source URL
     * @param {Function} callback - Callback when loaded
     */
    loadScript(src, callback) {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        
        if (callback) {
            script.onload = callback;
            script.onerror = () => {
                console.error('AdsManager: Failed to load script:', src);
            };
        }
        
        document.head.appendChild(script);
    }

    /**
     * Load external ad libraries
     */
    loadAdLibraries() {
        // Load VAST client if needed
        if (document.querySelector('[data-vast-url]')) {
            this.loadScript('/assets/js/lib/vast-client.min.js', () => {
                if (window.VASTClient) {
                    this.state.vastClient = new window.VASTClient();
                }
            });
        }
    }

    /**
     * Refresh all ads
     */
    refreshAds() {
        // Refresh GPT ads
        if (window.googletag) {
            googletag.cmd.push(() => {
                googletag.pubads().refresh();
            });
        }
        
        // Refresh other ad networks as needed
        this.core.emit('ads:refresh-requested');
    }

    /**
     * Get ad performance data
     * @returns {Object} Ad performance metrics
     */
    getAdMetrics() {
        return {
            totalAds: this.elements.adContainers.length,
            viewedAds: this.state.adEvents.size,
            activeVideoAds: this.state.activeVideoAds.size,
            headerAdSynced: this.state.headerAdSynced,
            adsLoaded: this.state.adsLoaded
        };
    }

    /**
     * Cleanup method
     */
    cleanup() {
        // Clean up event listeners
        this.events.forEach(cleanup => cleanup());
        this.events = [];
        
        // Clear ad state
        this.state.adEvents.clear();
        this.state.activeVideoAds.clear();
        
        // Reset state
        this.state = {
            adsLoaded: false,
            headerAdSynced: false,
            vastClient: null,
            adEvents: new Map(),
            activeVideoAds: new Map()
        };
        
        console.log('AdsManager: Cleaned up');
    }
}
