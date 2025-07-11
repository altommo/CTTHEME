/**
 * Video Player Component
 * Handles all video player functionality for self-hosted and embedded videos
 * Consolidates: video-player.js, video-embed.js, play-button-fix.js
 */

import { formatTime, addEvent, debounce } from '../core/utils.js';

export class VideoPlayer {
    constructor(core) {
        this.core = core;
        this.players = new Map();
        this.events = [];
        this.viewCountTracked = new Set();
    }

    /**
     * Initialize video player component
     */
    init() {
        console.log('VideoPlayer: Initializing...');
        
        // Initialize all video containers
        this.initializeAllPlayers();
        
        // Set up global event listeners
        this.setupGlobalEventListeners();
        
        // Register with core
        this.core.registerComponent('videoPlayer', this);
        
        console.log('VideoPlayer: Initialization complete');
    }

    /**
     * Initialize all video players on the page
     */
    initializeAllPlayers() {
        const containers = document.querySelectorAll('.video-player-container');
        
        containers.forEach(container => {
            this.initializePlayer(container);
        });

        console.log(`VideoPlayer: Initialized ${containers.length} video players`);
    }

    /**
     * Initialize a single video player
     * @param {Element} container - Video player container
     */
    initializePlayer(container) {
        const videoId = container.dataset.videoId;
        const videoType = container.dataset.videoType || 'mp4';
        
        if (!videoId) {
            console.warn('VideoPlayer: No video ID found for container', container);
            return;
        }

        // Create player instance
        const player = {
            id: videoId,
            type: videoType,
            container: container,
            element: null,
            isInitialized: false,
            events: []
        };

        // Initialize based on video type
        if (videoType === 'mp4' || videoType === 'self-hosted') {
            this.initSelfHostedPlayer(player);
        } else {
            this.initEmbeddedPlayer(player);
        }

        // Track view count
        this.trackViewCount(player);

        // Store player instance
        this.players.set(videoId, player);
    }

    /**
     * Initialize self-hosted video player with custom controls
     * @param {Object} player - Player instance
     */
    initSelfHostedPlayer(player) {
        const video = player.container.querySelector('video.main-video-player');
        
        if (!video) {
            console.error('VideoPlayer: Video element not found in container');
            return;
        }

        player.element = video;
        
        // Ensure native controls are disabled
        video.removeAttribute('controls');
        video.controls = false;
        
        // Setup custom controls
        this.setupCustomControls(player);
        
        // Setup video event listeners
        this.setupVideoEventListeners(player);
        
        // Attempt autoplay
        this.attemptAutoplay(player);
        
        player.isInitialized = true;
        this.core.emit('video-player:initialized', { player, type: 'self-hosted' });
    }

    /**
     * Initialize embedded video player (YouTube, Vimeo, etc.)
     * @param {Object} player - Player instance
     */
    initEmbeddedPlayer(player) {
        const embedDiv = player.container.querySelector('.embed-responsive');
        
        if (!embedDiv) {
            console.error('VideoPlayer: Embed container not found');
            return;
        }

        // Create or update iframe
        this.createEmbedIframe(player, embedDiv);
        
        // Setup responsive behavior
        this.setupIframeResponsive(player);
        
        player.isInitialized = true;
        this.core.emit('video-player:initialized', { player, type: 'embedded' });
    }

    /**
     * Create embed iframe based on video type
     * @param {Object} player - Player instance
     * @param {Element} embedDiv - Embed container
     */
    createEmbedIframe(player, embedDiv) {
        const videoUrl = player.container.dataset.videoUrl || '';
        let iframeSrc = '';
        let videoId = player.id;

        // Extract video ID from URL if needed
        if (!videoId && videoUrl) {
            videoId = this.extractVideoId(videoUrl, player.type);
        }

        // Generate iframe source based on video type
        switch (player.type) {
            case 'youtube':
                iframeSrc = `https://www.youtube.com/embed/${videoId}?rel=0&showinfo=0&modestbranding=1`;
                break;
                
            case 'vimeo':
                iframeSrc = `https://player.vimeo.com/video/${videoId}?api=1&title=0&byline=0&portrait=0&dnt=1`;
                break;
                
            case 'pornhub':
                iframeSrc = `https://www.pornhub.com/embed/${videoId}`;
                break;
                
            case 'xvideos':
                iframeSrc = `https://www.xvideos.com/embedframe/${videoId}`;
                break;
                
            case 'xhamster':
                iframeSrc = this.createXHamsterEmbedUrl(videoId, videoUrl);
                break;
                
            case 'redtube':
                iframeSrc = `https://embed.redtube.com/?id=${videoId}`;
                break;
                
            case 'youporn':
                iframeSrc = `https://www.youporn.com/embed/${videoId}/`;
                break;
                
            default:
                console.error('VideoPlayer: Unsupported video type:', player.type);
                this.showVideoError(embedDiv, `Unsupported video type: ${player.type}`);
                return;
        }

        // Create iframe element
        const iframe = document.createElement('iframe');
        iframe.className = 'embed-iframe';
        iframe.src = iframeSrc;
        iframe.frameBorder = '0';
        iframe.allowFullscreen = true;
        iframe.loading = 'lazy';
        iframe.allow = 'autoplay; fullscreen';
        iframe.scrolling = 'no';
        iframe.sandbox = 'allow-same-origin allow-scripts allow-popups allow-forms';
        iframe.style.width = '100%';
        iframe.style.height = '100%';

        // Replace existing content
        embedDiv.innerHTML = '';
        embedDiv.appendChild(iframe);

        player.element = iframe;

        // Setup specific player APIs if needed
        if (player.type === 'youtube') {
            this.setupYouTubePlayer(player, iframe);
        } else if (player.type === 'vimeo') {
            this.setupVimeoPlayer(player, iframe);
        }
    }

    /**
     * Create XHamster embed URL with special handling
     * @param {string} videoId - Video ID
     * @param {string} videoUrl - Original video URL
     * @returns {string} Embed URL
     */
    createXHamsterEmbedUrl(videoId, videoUrl) {
        if (!videoId && videoUrl && videoUrl.includes('xhamster.com')) {
            // Extract slug from URL
            if (videoUrl.includes('/videos/')) {
                const slug = videoUrl.split('/videos/')[1].split('/')[0];
                if (slug) {
                    return `https://xhamster.com/embed/${slug}`;
                }
            }
            // Fallback: replace /videos/ with /embed/
            return videoUrl.replace('/videos/', '/embed/');
        }
        
        return `https://xhamster.com/embed/${videoId}`;
    }

    /**
     * Extract video ID from URL based on video type
     * @param {string} url - Video URL
     * @param {string} type - Video type
     * @returns {string} Extracted video ID
     */
    extractVideoId(url, type) {
        const patterns = {
            youtube: [
                /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/,
                /youtube\.com\/embed\/([^&\n?#]+)/
            ],
            vimeo: [
                /vimeo\.com\/(\d+)/,
                /player\.vimeo\.com\/video\/(\d+)/
            ],
            xhamster: [
                /xhamster\.com\/videos\/([^\/&?"']+)/,
                /xhamster\.com\/embed\/([^\/&?"']+)/
            ]
        };

        const typePatterns = patterns[type] || [];
        
        for (const pattern of typePatterns) {
            const match = url.match(pattern);
            if (match && match[1]) {
                return match[1];
            }
        }

        return '';
    }

    /**
     * Setup custom controls for self-hosted video
     * @param {Object} player - Player instance
     */
    setupCustomControls(player) {
        const container = player.container;
        const video = player.element;
        const controlsContainer = container.querySelector('.custom-controls');
        
        if (!controlsContainer) {
            console.warn('VideoPlayer: Custom controls container not found');
            return;
        }

        // Cache control elements
        const controls = {
            playPause: controlsContainer.querySelector('.play-pause'),
            volumeButton: controlsContainer.querySelector('.volume-button'),
            volumeSlider: controlsContainer.querySelector('.volume-slider'),
            progressBar: controlsContainer.querySelector('.progress-bar'),
            progressContainer: controlsContainer.querySelector('.video-progress'),
            currentTime: controlsContainer.querySelector('.current-time'),
            duration: controlsContainer.querySelector('.duration'),
            fullscreen: controlsContainer.querySelector('.fullscreen-button'),
            quality: controlsContainer.querySelector('.quality-selector')
        };

        // Store controls reference
        player.controls = controls;

        // Setup control event listeners
        this.setupControlEventListeners(player);

        // Setup auto-hide behavior
        this.setupControlsAutoHide(player);
    }

    /**
     * Setup control event listeners
     * @param {Object} player - Player instance
     */
    setupControlEventListeners(player) {
        const video = player.element;
        const controls = player.controls;
        const container = player.container;

        // Play/Pause button
        if (controls.playPause) {
            player.events.push(
                addEvent(controls.playPause, 'click', () => {
                    if (video.paused || video.ended) {
                        this.playVideo(player);
                    } else {
                        this.pauseVideo(player);
                    }
                })
            );
        }

        // Volume control
        if (controls.volumeButton) {
            player.events.push(
                addEvent(controls.volumeButton, 'click', () => {
                    this.toggleMute(player);
                })
            );
        }

        if (controls.volumeSlider) {
            player.events.push(
                addEvent(controls.volumeSlider, 'input', (e) => {
                    const volume = parseFloat(e.target.value);
                    video.volume = volume;
                    video.muted = (volume === 0);
                    this.updateVolumeState(player);
                })
            );
        }

        // Progress bar seeking
        if (controls.progressContainer) {
            player.events.push(
                addEvent(controls.progressContainer, 'click', (e) => {
                    if (!isNaN(video.duration)) {
                        const rect = controls.progressContainer.getBoundingClientRect();
                        const clickPosition = e.clientX - rect.left;
                        const progressWidth = rect.width;
                        const seekTime = (clickPosition / progressWidth) * video.duration;
                        video.currentTime = seekTime;
                    }
                })
            );
        }

        // Fullscreen button
        if (controls.fullscreen) {
            player.events.push(
                addEvent(controls.fullscreen, 'click', () => {
                    this.toggleFullscreen(player);
                })
            );
        }

        // Quality selector
        if (controls.quality) {
            this.setupQualitySelector(player);
        }

        // Video click to play/pause
        player.events.push(
            addEvent(video, 'click', () => {
                if (video.paused || video.ended) {
                    this.playVideo(player);
                } else {
                    this.pauseVideo(player);
                }
            })
        );

        // Keyboard shortcuts
        player.events.push(
            addEvent(document, 'keydown', (e) => {
                if (container.classList.contains('is-active')) {
                    this.handleKeyboardShortcuts(player, e);
                }
            })
        );
    }

    /**
     * Setup video event listeners
     * @param {Object} player - Player instance
     */
    setupVideoEventListeners(player) {
        const video = player.element;
        const container = player.container;

        // Play/pause state changes
        player.events.push(
            addEvent(video, 'play', () => {
                container.classList.add('is-playing');
                this.core.emit('video-player:play', { player });
            }),
            
            addEvent(video, 'pause', () => {
                container.classList.remove('is-playing');
                this.core.emit('video-player:pause', { player });
            }),

            addEvent(video, 'ended', () => {
                container.classList.remove('is-playing');
                this.core.emit('video-player:ended', { player });
            }),

            addEvent(video, 'timeupdate', () => {
                this.updateProgress(player);
            }),

            addEvent(video, 'loadedmetadata', () => {
                this.updateDuration(player);
            }),

            addEvent(video, 'volumechange', () => {
                this.updateVolumeState(player);
            })
        );

        // Container focus for keyboard shortcuts
        player.events.push(
            addEvent(container, 'mouseenter', () => {
                document.querySelectorAll('.video-player-container').forEach(c => {
                    c.classList.remove('is-active');
                });
                container.classList.add('is-active');
            })
        );
    }

    /**
     * Attempt to autoplay video
     * @param {Object} player - Player instance
     */
    attemptAutoplay(player) {
        const video = player.element;
        
        // Try muted autoplay first
        video.muted = true;
        
        const playPromise = video.play();
        
        if (playPromise !== undefined) {
            playPromise.then(() => {
                console.log('VideoPlayer: Autoplay started successfully');
                
                // Show unmute option on user interaction
                player.container.addEventListener('click', () => {
                    video.muted = false;
                }, { once: true });
                
            }).catch(error => {
                console.warn('VideoPlayer: Autoplay prevented:', error);
                this.showPlayButton(player);
            });
        }
    }

    /**
     * Show play button overlay when autoplay is prevented
     * @param {Object} player - Player instance
     */
    showPlayButton(player) {
        const container = player.container;
        
        // Create play button overlay
        const overlay = document.createElement('div');
        overlay.className = 'play-button-overlay';
        overlay.innerHTML = `
            <div class="play-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="white">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </div>
        `;
        
        container.appendChild(overlay);
        
        // Handle click
        overlay.addEventListener('click', () => {
            this.playVideo(player).then(() => {
                player.element.muted = false;
                overlay.remove();
            }).catch(e => {
                console.error('VideoPlayer: Cannot play video after click:', e);
            });
        });
    }

    /**
     * Play video
     * @param {Object} player - Player instance
     * @returns {Promise} Play promise
     */
    async playVideo(player) {
        try {
            await player.element.play();
            return true;
        } catch (error) {
            console.error('VideoPlayer: Play failed:', error);
            return false;
        }
    }

    /**
     * Pause video
     * @param {Object} player - Player instance
     */
    pauseVideo(player) {
        player.element.pause();
    }

    /**
     * Toggle mute
     * @param {Object} player - Player instance
     */
    toggleMute(player) {
        const video = player.element;
        
        if (video.muted) {
            video.muted = false;
            if (player.controls.volumeSlider) {
                player.controls.volumeSlider.value = video.volume;
            }
        } else {
            video.muted = true;
            if (player.controls.volumeSlider) {
                player.controls.volumeSlider.value = 0;
            }
        }
        
        this.updateVolumeState(player);
    }

    /**
     * Toggle fullscreen
     * @param {Object} player - Player instance
     */
    toggleFullscreen(player) {
        const video = player.element;
        
        if (video.requestFullscreen) {
            video.requestFullscreen();
        } else if (video.webkitRequestFullscreen) {
            video.webkitRequestFullscreen();
        } else if (video.msRequestFullscreen) {
            video.msRequestFullscreen();
        }
    }

    /**
     * Update progress bar and time display
     * @param {Object} player - Player instance
     */
    updateProgress(player) {
        const video = player.element;
        const controls = player.controls;
        
        if (!controls || isNaN(video.duration)) return;
        
        const currentTime = video.currentTime;
        const duration = video.duration;
        const progressPercent = (currentTime / duration) * 100;
        
        // Update progress bar
        if (controls.progressBar) {
            controls.progressBar.style.width = progressPercent + '%';
        }
        
        // Update time display
        if (controls.currentTime) {
            controls.currentTime.textContent = formatTime(currentTime);
        }
    }

    /**
     * Update duration display
     * @param {Object} player - Player instance
     */
    updateDuration(player) {
        const video = player.element;
        const controls = player.controls;
        
        if (controls && controls.duration && !isNaN(video.duration)) {
            controls.duration.textContent = formatTime(video.duration);
        }
    }

    /**
     * Update volume button state
     * @param {Object} player - Player instance
     */
    updateVolumeState(player) {
        const video = player.element;
        const controls = player.controls;
        
        if (controls && controls.volumeButton) {
            if (video.muted || video.volume === 0) {
                controls.volumeButton.classList.add('is-muted');
            } else {
                controls.volumeButton.classList.remove('is-muted');
            }
        }
    }

    /**
     * Handle keyboard shortcuts
     * @param {Object} player - Player instance
     * @param {Event} e - Keyboard event
     */
    handleKeyboardShortcuts(player, e) {
        const video = player.element;
        
        switch (e.code) {
            case 'Space':
                if (video.paused || video.ended) {
                    this.playVideo(player);
                } else {
                    this.pauseVideo(player);
                }
                e.preventDefault();
                break;
                
            case 'ArrowLeft':
                video.currentTime = Math.max(0, video.currentTime - 5);
                e.preventDefault();
                break;
                
            case 'ArrowRight':
                video.currentTime = Math.min(video.duration, video.currentTime + 5);
                e.preventDefault();
                break;
                
            case 'ArrowUp':
                video.volume = Math.min(1, video.volume + 0.1);
                if (player.controls.volumeSlider) {
                    player.controls.volumeSlider.value = video.volume;
                }
                this.updateVolumeState(player);
                e.preventDefault();
                break;
                
            case 'ArrowDown':
                video.volume = Math.max(0, video.volume - 0.1);
                if (player.controls.volumeSlider) {
                    player.controls.volumeSlider.value = video.volume;
                }
                this.updateVolumeState(player);
                e.preventDefault();
                break;
                
            case 'KeyM':
                this.toggleMute(player);
                e.preventDefault();
                break;
                
            case 'KeyF':
                this.toggleFullscreen(player);
                e.preventDefault();
                break;
        }
    }

    /**
     * Setup quality selector
     * @param {Object} player - Player instance
     */
    setupQualitySelector(player) {
        const controls = player.controls;
        const qualityButton = controls.quality.querySelector('.quality-button');
        const qualityOptions = controls.quality.querySelector('.quality-options');
        
        if (!qualityButton || !qualityOptions) return;
        
        // Toggle quality options
        player.events.push(
            addEvent(qualityButton, 'click', () => {
                qualityOptions.classList.toggle('is-visible');
            })
        );
        
        // Handle quality selection
        const qualityButtons = qualityOptions.querySelectorAll('button');
        qualityButtons.forEach(button => {
            player.events.push(
                addEvent(button, 'click', () => {
                    const quality = button.dataset.quality;
                    const videoUrl = button.dataset.url;
                    this.changeQuality(player, quality, videoUrl);
                    qualityOptions.classList.remove('is-visible');
                })
            );
        });
        
        // Hide quality options when clicking outside
        player.events.push(
            addEvent(document, 'click', (e) => {
                if (!controls.quality.contains(e.target)) {
                    qualityOptions.classList.remove('is-visible');
                }
            })
        );
    }

    /**
     * Change video quality
     * @param {Object} player - Player instance
     * @param {string} quality - Quality label
     * @param {string} videoUrl - New video URL
     */
    changeQuality(player, quality, videoUrl) {
        const video = player.element;
        const currentTime = video.currentTime;
        const isPaused = video.paused;
        
        // Update source and restore state
        video.src = videoUrl;
        video.currentTime = currentTime;
        
        if (!isPaused) {
            this.playVideo(player);
        }
        
        // Update quality button text
        const qualityButton = player.controls.quality.querySelector('.quality-button');
        if (qualityButton) {
            qualityButton.textContent = quality;
        }
        
        this.core.emit('video-player:quality-changed', { player, quality });
    }

    /**
     * Setup controls auto-hide behavior
     * @param {Object} player - Player instance
     */
    setupControlsAutoHide(player) {
        const container = player.container;
        const controlsContainer = container.querySelector('.custom-controls');
        
        if (!controlsContainer) return;
        
        let hideTimeout;
        
        const showControls = () => {
            controlsContainer.classList.add('is-visible');
            clearTimeout(hideTimeout);
            
            hideTimeout = setTimeout(() => {
                if (!player.element.paused) {
                    controlsContainer.classList.remove('is-visible');
                }
            }, 3000);
        };
        
        const hideControls = () => {
            controlsContainer.classList.remove('is-visible');
            clearTimeout(hideTimeout);
        };
        
        // Show controls on mouse movement
        player.events.push(
            addEvent(container, 'mousemove', showControls),
            addEvent(container, 'mouseenter', showControls),
            addEvent(container, 'mouseleave', hideControls)
        );
        
        // Always show controls when paused
        player.events.push(
            addEvent(player.element, 'pause', () => {
                controlsContainer.classList.add('is-visible');
                clearTimeout(hideTimeout);
            })
        );
    }

    /**
     * Setup YouTube player with API
     * @param {Object} player - Player instance
     * @param {Element} iframe - YouTube iframe
     */
    setupYouTubePlayer(player, iframe) {
        // Ensure iframe has proper attributes for YouTube API
        const src = iframe.src;
        if (src.indexOf('enablejsapi=1') === -1) {
            const separator = src.indexOf('?') !== -1 ? '&' : '?';
            iframe.src = src + separator + 'enablejsapi=1';
        }
        
        // Add API-ready class
        iframe.classList.add('youtube-api-ready');
        
        // YouTube API setup would go here if needed
        // For now, basic iframe functionality is sufficient
    }

    /**
     * Setup Vimeo player
     * @param {Object} player - Player instance 
     * @param {Element} iframe - Vimeo iframe
     */
    setupVimeoPlayer(player, iframe) {
        // Vimeo-specific setup if needed
        iframe.classList.add('vimeo-api-ready');
    }

    /**
     * Setup responsive behavior for iframe embeds
     * @param {Object} player - Player instance
     */
    setupIframeResponsive(player) {
        const iframe = player.element;
        const container = player.container;
        
        // Ensure proper responsive styling
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = '0';
        
        // Handle container focus
        player.events.push(
            addEvent(container, 'mouseenter', () => {
                document.querySelectorAll('.video-player-container').forEach(c => {
                    c.classList.remove('is-active');
                });
                container.classList.add('is-active');
            })
        );
    }

    /**
     * Show video error message
     * @param {Element} container - Container element
     * @param {string} message - Error message
     */
    showVideoError(container, message) {
        container.innerHTML = `
            <div class="video-error">
                <p><strong>Video Error:</strong> ${message}</p>
                <p>Please check the video URL and try again.</p>
            </div>
        `;
    }

    /**
     * Track video view count
     * @param {Object} player - Player instance
     */
    trackViewCount(player) {
        const videoId = player.id;
        
        // Only track once per page load
        if (this.viewCountTracked.has(videoId)) {
            return;
        }
        
        this.viewCountTracked.add(videoId);
        
        // Track view after a short delay
        setTimeout(() => {
            this.core.ajax.updateViewCount(videoId).catch(error => {
                console.warn('VideoPlayer: Failed to update view count:', error);
            });
        }, 1000);
    }

    /**
     * Setup global event listeners
     */
    setupGlobalEventListeners() {
        // Handle window load to catch any missed videos
        this.events.push(
            addEvent(window, 'load', () => {
                setTimeout(() => {
                    this.initializeAllPlayers();
                }, 500);
            })
        );

        // Handle dynamic content loading
        this.core.on('content:loaded', () => {
            this.initializeAllPlayers();
        });
    }

    /**
     * Get player by ID
     * @param {string} videoId - Video ID
     * @returns {Object|null} Player instance or null
     */
    getPlayer(videoId) {
        return this.players.get(videoId) || null;
    }

    /**
     * Get all players
     * @returns {Map} All player instances
     */
    getAllPlayers() {
        return this.players;
    }

    /**
     * Remove player
     * @param {string} videoId - Video ID
     */
    removePlayer(videoId) {
        const player = this.players.get(videoId);
        
        if (player) {
            // Clean up event listeners
            player.events.forEach(cleanup => cleanup());
            
            // Remove from tracking
            this.viewCountTracked.delete(videoId);
            
            // Remove from players map
            this.players.delete(videoId);
            
            this.core.emit('video-player:removed', { videoId });
        }
    }

    /**
     * Cleanup method
     */
    cleanup() {
        // Clean up all players
        this.players.forEach((player, videoId) => {
            this.removePlayer(videoId);
        });
        
        // Clean up global event listeners
        this.events.forEach(cleanup => cleanup());
        this.events = [];
        
        // Clear tracking sets
        this.viewCountTracked.clear();
        
        console.log('VideoPlayer: Cleaned up');
    }
}
