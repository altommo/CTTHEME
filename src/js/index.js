/**
 * Main Entry Point - CustomTube JavaScript Application
 * Imports and initializes all modules based on page context
 */

/**
 * TEST: Confirm minified JS is loaded
 * This will output a clear message in the browser console when the minified bundle is loaded.
 * The log runs only during development builds.
 */
if (process.env.NODE_ENV === 'development') {
    console.log('✅ CustomTube minified JS bundle file is being read.');
}
window.customtubeBundleLoaded = true;

// Core modules
import CustomTubeCore from './core/core.js';

// Component modules
import { Navigation } from './components/navigation.js';
import { ThemeSwitcher } from './components/theme-switcher.js';
import { VideoPlayer } from './components/video-player.js';
import { VideoGrid } from './components/video-grid.js';
import { EnhancedFeatures } from './components/enhanced-features.js';
import { AdsManager } from './components/ads.js';
import { Carousel } from './components/carousel.js'; // Import the new Carousel component

// Page-specific modules (will be created later)
// import { HomePage } from './pages/home.js';
// import { VideoSinglePage } from './pages/video-single.js';
// import { PerformersPage } from './pages/performers.js';
// import { AuthPages } = './pages/auth.js';

/**
 * Main CustomTube Application Class
 */
class CustomTube {
    constructor() {
        // Initialize core system
        this.core = new CustomTubeCore();
        
        // Component instances
        this.components = new Map();
        
        // Page-specific module instances
        this.pageModules = new Map();
        
        // Initialization state
        this.isInitialized = false;
    }

    /**
     * Initialize the application
     */
    async init() {
        if (this.isInitialized) {
            console.warn('CustomTube: Already initialized');
            return;
        }

        console.log('CustomTube: Starting initialization...');

        try {
            // Initialize core first
            await this.core.init();

            // Initialize global components
            await this.initializeGlobalComponents();

            // Initialize page-specific modules
            await this.initializePageSpecificModules();

            // Set up global event listeners
            this.setupGlobalEventListeners();

            // Mark as initialized
            this.isInitialized = true;

            // Emit initialization complete event
            this.core.emit('app:initialized', {
                components: Array.from(this.components.keys()),
                pageModules: Array.from(this.pageModules.keys()),
                currentPage: this.core.state.currentPage
            });

            console.log('CustomTube: Initialization complete');

        } catch (error) {
            console.error('CustomTube: Initialization failed', error);
            this.core.emit('app:initialization-failed', { error });
        }
    }

    /**
     * Initialize global components that run on every page
     */
    async initializeGlobalComponents() {
        console.log('CustomTube: Initializing global components...');

        // Navigation - always initialize
        const navElement = document.querySelector('.ph-nav');
        if (navElement) {
            console.log('CustomTube: Found .ph-nav element. Initializing Navigation component.'); // Added log
            const navigation = new Navigation(this.core);
            navigation.init();
            this.components.set('navigation', navigation);
        } else {
            console.log('CustomTube: .ph-nav element not found. Skipping Navigation component initialization.'); // Added log
        }

        // Theme Switcher - always initialize
        if (document.querySelector('.ph-nav__theme-toggle, #theme-toggle')) {
            console.log('CustomTube: Found theme toggle element. Initializing ThemeSwitcher component.'); // Added log
            const themeSwitcher = new ThemeSwitcher(this.core);
            themeSwitcher.init();
            this.components.set('themeSwitcher', themeSwitcher);
        } else {
            console.log('CustomTube: Theme toggle element not found. Skipping ThemeSwitcher component initialization.'); // Added log
        }

        // Video Player - initialize if video containers exist
        if (document.querySelector('.video-player-container')) {
            console.log('CustomTube: Found video player container. Initializing VideoPlayer component.'); // Added log
            const videoPlayer = new VideoPlayer(this.core);
            videoPlayer.init();
            this.components.set('videoPlayer', videoPlayer);
        } else {
            console.log('CustomTube: Video player container not found. Skipping VideoPlayer component initialization.'); // Added log
        }

        // Video Grid - initialize if video grids exist
        if (document.querySelector('.video-grid, .video-masonry, .video-grid-container')) {
            console.log('CustomTube: Found video grid container. Initializing VideoGrid component.'); // Added log
            const videoGrid = new VideoGrid(this.core);
            videoGrid.init();
            this.components.set('videoGrid', videoGrid);
        } else {
            console.log('CustomTube: Video grid container not found. Skipping VideoGrid component initialization.'); // Added log
        }

        // Enhanced Features - initialize if needed elements exist
        if (document.querySelector('.video-thumbnail-container, .video-card')) {
            console.log('CustomTube: Found video thumbnail/card. Initializing EnhancedFeatures component.'); // Added log
            const enhancedFeatures = new EnhancedFeatures(this.core);
            enhancedFeatures.init();
            this.components.set('enhancedFeatures', enhancedFeatures);
        } else {
            console.log('CustomTube: Video thumbnail/card not found. Skipping EnhancedFeatures component initialization.'); // Added log
        }

        // Ads Manager - initialize if ad containers exist
        if (document.querySelector('.ad-container, .banner-ad, .video-ad-container')) {
            console.log('CustomTube: Found ad container. Initializing AdsManager component.'); // Added log
            const adsManager = new AdsManager(this.core);
            adsManager.init();
            this.components.set('adsManager', adsManager);
        } else {
            console.log('CustomTube: Ad container not found. Skipping AdsManager component initialization.'); // Added log
        }

        // Carousel - initialize if carousel containers exist
        const carouselContainers = document.querySelectorAll('[data-carousel]');
        if (carouselContainers.length > 0) {
            console.log(`CustomTube: Found ${carouselContainers.length} carousel containers. Initializing Carousel components.`);
            carouselContainers.forEach((container, index) => {
                // Example: Retrieve options from data attributes or a global config
                const carouselId = container.id || `carousel-${index}`;
                const options = JSON.parse(container.dataset.carouselOptions || '{}');
                
                // For demonstration, let's assume some dummy slides
                // In a real scenario, these would come from a server-side render or AJAX
                if (!options.slides || options.slides.length === 0) {
                    // Use a local placeholder image
                    const defaultImageUrl = window.customtubeData.theme_uri + '/assets/images/default-video.jpg';
                    options.slides = [
                        { type: 'image', imageUrl: defaultImageUrl, title: 'Awesome Slide 1', description: 'This is a description for slide 1.', buttonText: 'Watch Now', buttonUrl: '#' },
                        { type: 'image', imageUrl: defaultImageUrl, title: 'Amazing Slide 2', description: 'This is a description for slide 2.', buttonText: 'Learn More', buttonUrl: '#' },
                        { type: 'video', videoUrl: 'https://www.w3schools.com/html/mov_bbb.mp4', title: 'Video Slide 3', description: 'A short video clip.', buttonText: 'Play Video', buttonUrl: '#' },
                        { type: 'image', imageUrl: defaultImageUrl, title: 'Fantastic Slide 4', description: 'Another great slide.', buttonText: 'Discover', buttonUrl: '#' }
                    ];
                }

                const carousel = new Carousel(container, options);
                this.components.set(`carousel-${carouselId}`, carousel);
            });
        } else {
            console.log('CustomTube: No carousel containers found. Skipping Carousel component initialization.');
        }

        console.log(`CustomTube: ${this.components.size} global components initialized`);
    }

    /**
     * Initialize page-specific modules based on current page context
     */
    async initializePageSpecificModules() {
        const currentPage = this.core.state.currentPage;
        console.log(`CustomTube: Initializing page-specific modules for: ${currentPage}`);

        switch (currentPage) {
            case 'home':
                await this.initializeHomePage();
                break;
                
            case 'video-single':
                await this.initializeVideoSinglePage();
                break;
                
            case 'performers':
                await this.initializePerformersPage();
                break;
                
            case 'login':
            case 'register':
                await this.initializeAuthPages();
                break;
                
            case 'liked-videos':
                await this.initializeLikedVideosPage();
                break;
                
            case 'short-videos':
                await this.initializeShortVideosPage();
                break;
                
            case 'trending': // Initialize trending page specific carousels
                await this.initializeTrendingPage();
                break;
                
            default:
                console.log('CustomTube: No specific page module for:', currentPage);
                break;
        }
    }

    /**
     * Initialize home page specific functionality
     */
    async initializeHomePage() {
        console.log('CustomTube: Home page module ready for implementation');
        
        // Basic home page functionality
        this.setupHomepageFeatures();
        
        // Fetch and update personalized carousels
        await this.fetchAndDisplayPersonalizedCarousels();

        // Listen for video clicks to update recommendations
        this.core.on('enhanced-features:card-activated', debounce(() => {
            this.updatePersonalizedCarousels();
        }, 1000)); // Debounce to avoid too many updates
    }

    /**
     * Fetch and display personalized carousels on page load.
     */
    async fetchAndDisplayPersonalizedCarousels() {
        const enhancedFeatures = this.getComponent('enhancedFeatures');
        const watchedVideoIds = enhancedFeatures ? enhancedFeatures.getWatchedVideos() : [];

        // Fetch Hero Carousel recommendations
        const heroCarouselElement = document.querySelector('#hero-carousel');
        if (heroCarouselElement) {
            try {
                const heroRecommendations = await this.core.ajax.getPersonalizedRecommendations(watchedVideoIds, 5, 'hero');
                const heroCarousel = this.components.get('carousel-hero-main');
                if (heroCarousel) {
                    heroCarousel.updateSlides(heroRecommendations);
                }
            } catch (error) {
                console.error('Failed to fetch hero recommendations:', error);
            }
        }

        // Fetch "Because you watched..." recommendations
        const becauseYouWatchedCarouselElement = document.querySelector('#because-you-watched-carousel');
        if (becauseYouWatchedCarouselElement) {
            try {
                const becauseYouWatchedRecommendations = await this.core.ajax.getPersonalizedRecommendations(watchedVideoIds, 6, 'because_you_watched');
                const becauseYouWatchedCarousel = this.components.get('carousel-because-you-watched');
                if (becauseYouWatchedCarousel) {
                    becauseYouWatchedCarousel.updateSlides(becauseYouWatchedRecommendations);
                }
            } catch (error) {
                console.error('Failed to fetch "Because you watched" recommendations:', error);
            }
        }

        // Fetch "You might also like..." recommendations
        const youMightAlsoLikeCarouselElement = document.querySelector('#you-might-also-like-carousel');
        if (youMightAlsoLikeCarouselElement) {
            try {
                const youMightAlsoLikeRecommendations = await this.core.ajax.getPersonalizedRecommendations(watchedVideoIds, 6, 'you_might_also_like');
                const youMightAlsoLikeCarousel = this.components.get('carousel-you-might-also-like');
                if (youMightAlsoLikeCarousel) {
                    youMightAlsoLikeCarousel.updateSlides(youMightAlsoLikeRecommendations);
                }
            } catch (error) {
                console.error('Failed to fetch "You might also like" recommendations:', error);
            }
        }
    }

    /**
     * Update personalized carousels after user interaction.
     */
    async updatePersonalizedCarousels() {
        const enhancedFeatures = this.getComponent('enhancedFeatures');
        const watchedVideoIds = enhancedFeatures ? enhancedFeatures.getWatchedVideos() : [];

        // Only update "Because you watched..." and "You might also like..."
        const becauseYouWatchedCarouselElement = document.querySelector('#because-you-watched-carousel');
        if (becauseYouWatchedCarouselElement) {
            try {
                const becauseYouWatchedRecommendations = await this.core.ajax.getPersonalizedRecommendations(watchedVideoIds, 6, 'because_you_watched');
                const becauseYouWatchedCarousel = this.components.get('carousel-because-you-watched');
                if (becauseYouWatchedCarousel) {
                    becauseYouWatchedCarousel.updateSlides(becauseYouWatchedRecommendations);
                }
            } catch (error) {
                console.error('Failed to update "Because you watched" recommendations:', error);
            }
        }

        const youMightAlsoLikeCarouselElement = document.querySelector('#you-might-also-like-carousel');
        if (youMightAlsoLikeCarouselElement) {
            try {
                const youMightAlsoLikeRecommendations = await this.core.ajax.getPersonalizedRecommendations(watchedVideoIds, 6, 'you_might_also_like');
                const youMightAlsoLikeCarousel = this.components.get('carousel-you-might-also-like');
                if (youMightAlsoLikeCarousel) {
                    youMightAlsoLikeCarousel.updateSlides(youMightAlsoLikeRecommendations);
                }
            } catch (error) {
                console.error('Failed to update "You might also like" recommendations:', error);
            }
        }
    }

    /**
     * Initialize single video page specific functionality
     */
    async initializeVideoSinglePage() {
        console.log('CustomTube: Video single page module ready for implementation');
        
        // Basic video page functionality
        this.setupVideoPageFeatures();
        
        // TODO: Implement full VideoSinglePage module when created
        /*
        const videoSinglePage = new VideoSinglePage(this.core);
        videoSinglePage.init();
        this.pageModules.set('videoSinglePage', videoSinglePage);
        */
    }

    /**
     * Initialize performers page specific functionality
     */
    async initializePerformersPage() {
        console.log('CustomTube: Performers page module ready for implementation');
        
        // Basic performers page functionality
        this.setupPerformersPageFeatures();
        
        // TODO: Implement full PerformersPage module when created
        /*
        const performersPage = new PerformersPage(this.core);
        performersPage.init();
        this.pageModules.set('performersPage', performersPage);
        */
    }

    /**
     * Initialize authentication pages (login/register)
     */
    async initializeAuthPages() {
        console.log('CustomTube: Auth pages module ready for implementation');
        
        // Basic auth functionality
        this.setupAuthPageFeatures();
        
        // TODO: Implement full AuthPages module when created
        /*
        const authPages = new AuthPages(this.core);
        authPages.init();
        this.pageModules.set('authPages', authPages);
        */
    }

    /**
     * Initialize liked videos page
     */
    async initializeLikedVideosPage() {
        console.log('CustomTube: Liked videos page functionality');
        
        // Set up liked videos specific features
        this.setupLikedVideosFeatures();
    }

    /**
     * Initialize short videos page
     */
    async initializeShortVideosPage() {
        console.log('CustomTube: Short videos page functionality');
        
        // Set up short videos specific features
        this.setupShortVideosFeatures();
    }

    /**
     * Initialize trending page specific functionality
     */
    async initializeTrendingPage() {
        console.log('CustomTube: Trending page module ready for implementation');
        
        // Example: Initialize a carousel on the trending page
        const heroCarouselElement = document.querySelector('#trending-carousel');
        if (heroCarouselElement) {
            const defaultImageUrl = window.customtubeData.theme_uri + '/assets/images/default-video.jpg';
            const trendingSlides = [
                { type: 'image', imageUrl: defaultImageUrl, title: 'Top Trending Video 1', description: 'This is the hottest video right now!', buttonText: 'Watch Now', buttonUrl: '#' },
                { type: 'image', imageUrl: defaultImageUrl, title: 'Viral Sensation 2', description: 'Don\'t miss this one!', buttonText: 'Check it out', buttonUrl: '#' },
                { type: 'video', videoUrl: 'https://www.w3schools.com/html/mov_bbb.mp4', title: 'Trending Video 3', description: 'A must-watch clip.', buttonText: 'Play', buttonUrl: '#' }
            ];
            const trendingCarousel = new Carousel(heroCarouselElement, { slides: trendingSlides, autoplay: true, loop: true, displayMode: 'full-width-hero' });
            this.components.set('trendingHeroCarousel', trendingCarousel);
        }
    }

    /**
     * Setup basic homepage features
     */
    setupHomepageFeatures() {
        // Trending section functionality
        const trendingSection = document.querySelector('.trending-section');
        if (trendingSection) {
            this.setupTrendingSection(trendingSection);
        }
        
        // Category navigation
        const categoryNav = document.querySelector('.category-navigation');
        if (categoryNav) {
            this.setupCategoryNavigation(categoryNav);
        }

        // Example: Initialize a carousel on the homepage hero section
        // This will be overridden by personalized recommendations if available
        const heroCarouselElement = document.querySelector('#hero-carousel');
        if (heroCarouselElement && !this.components.has('carousel-hero-main')) {
            const defaultImageUrl = window.customtubeData.theme_uri + '/assets/images/default-video.jpg';
            const heroSlides = [
                { type: 'image', imageUrl: defaultImageUrl, title: 'Welcome to CustomTube', description: 'Discover amazing videos!', buttonText: 'Explore', buttonUrl: '#' },
                { type: 'image', imageUrl: defaultImageUrl, title: 'New Content Daily', description: 'Never miss a moment.', buttonText: 'Browse', buttonUrl: '#' }
            ];
            const heroCarousel = new Carousel(heroCarouselElement, { slides: heroSlides, autoplay: true, loop: true, displayMode: 'full-width-hero' });
            this.components.set('carousel-hero-main', heroCarousel);
        }

        // Initialize "Because you watched..." carousel
        const becauseYouWatchedCarouselElement = document.querySelector('#because-you-watched-carousel');
        if (becauseYouWatchedCarouselElement && !this.components.has('carousel-because-you-watched')) {
            const becauseYouWatchedCarousel = new Carousel(becauseYouWatchedCarouselElement, { slidesToShow: 4, loop: false, displayMode: 'multi-row-strip' });
            this.components.set('carousel-because-you-watched', becauseYouWatchedCarousel);
        }

        // Initialize "You might also like..." carousel
        const youMightAlsoLikeCarouselElement = document.querySelector('#you-might-also-like-carousel');
        if (youMightAlsoLikeCarouselElement && !this.components.has('carousel-you-might-also-like')) {
            const youMightAlsoLikeCarousel = new Carousel(youMightAlsoLikeCarouselElement, { slidesToShow: 4, loop: false, displayMode: 'multi-row-strip' });
            this.components.set('carousel-you-might-also-like', youMightAlsoLikeCarousel);
        }
    }

    /**
     * Setup basic video page features
     */
    setupVideoPageFeatures() {
        // Video recommendations
        const recommendationsSection = document.querySelector('.video-recommendations');
        if (recommendationsSection) {
            this.setupVideoRecommendations(recommendationsSection);
        }
        
        // Like button functionality
        const likeButton = document.querySelector('.video-like-button');
        if (likeButton) {
            this.setupLikeButton(likeButton);
        }
    }

    /**
     * Setup basic performers page features
     */
    setupPerformersPageFeatures() {
        // Alphabet navigation for performers
        const alphabetNav = document.querySelector('.performers-alphabet-nav');
        if (alphabetNav) {
            this.setupAlphabetNavigation(alphabetNav);
        }
    }

    /**
     * Setup basic auth page features
     */
    setupAuthPageFeatures() {
        // Form validation
        const loginForm = document.querySelector('.login-form');
        const registerForm = document.querySelector('.register-form');
        
        if (loginForm) {
            this.setupLoginForm(loginForm);
        }
        
        if (registerForm) {
            this.setupRegisterForm(registerForm);
        }
    }

    /**
     * Setup liked videos features
     */
    setupLikedVideosFeatures() {
        // Load liked videos via AJAX
        if (document.querySelector('.liked-videos-container')) {
            this.loadLikedVideos();
        }
    }

    /**
     * Setup short videos features
     */
    setupShortVideosFeatures() {
        // Load short videos with filtering
        if (document.querySelector('.short-videos-container')) {
            this.loadShortVideos();
        }
    }

    /**
     * Setup trending section
     * @param {Element} section - Trending section element
     */
    setupTrendingSection(section) {
        const loadTrendingButton = section.querySelector('.load-trending');
        if (loadTrendingButton) {
            loadTrendingButton.addEventListener('click', async () => {
                try {
                    const response = await this.core.ajax.getTrendingVideos();
                    if (response.success) {
                        this.updateTrendingContent(section, response.data.videos);
                    }
                } catch (error) {
                    console.error('Failed to load trending videos:', error);
                }
            });
        }
    }

    /**
     * Setup category navigation
     * @param {Element} nav - Category navigation element
     */
    setupCategoryNavigation(nav) {
        const categoryLinks = nav.querySelectorAll('a[data-category]');
        categoryLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const category = link.dataset.category || link.textContent.trim();
                this.filterByCategory(category);
            });
        });
    }

    /**
     * Setup video recommendations
     * @param {Element} section - Recommendations section
     */
    setupVideoRecommendations(section) {
        const videoId = section.dataset.videoId;
        if (videoId) {
            this.loadVideoRecommendations(videoId, section);
        }
    }

    /**
     * Setup like button
     * @param {Element} button - Like button element
     */
    setupLikeButton(button) {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const videoId = button.dataset.videoId;
            
            if (videoId) {
                try {
                    const response = await this.core.ajax.toggleLike(videoId);
                    if (response.success) {
                        this.updateLikeButton(button, response.data);
                    }
                } catch (error) {
                    console.error('Failed to toggle like:', error);
                }
            }
        });
    }

    /**
     * Setup alphabet navigation
     * @param {Element} nav - Alphabet navigation element
     */
    setupAlphabetNavigation(nav) {
        const letters = nav.querySelectorAll('a[href^="#letter-"]');
        letters.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href');
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    /**
     * Setup login form
     * @param {Element} form - Login form element
     */
    setupLoginForm(form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const username = formData.get('log');
            const password = formData.get('pwd');
            const remember = formData.get('rememberme') === '1';
            
            try {
                const response = await this.core.ajax.userLogin(username, password, remember);
                if (response.success) {
                    window.location.href = response.data.redirect_url || '/';
                } else {
                    this.showFormError(form, response.data.message);
                }
            } catch (error) {
                this.showFormError(form, 'Login failed. Please try again.');
            }
        });
    }

    /**
     * Setup register form
     * @param {Element} form - Register form element
     */
    setupRegisterForm(form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const userData = Object.fromEntries(formData.entries());
            
            try {
                const response = await this.core.ajax.userRegister(userData);
                if (response.success) {
                    this.showFormSuccess(form, response.data.message);
                    setTimeout(() => {
                        window.location.href = response.data.redirect_url || '/login';
                    }, 2000);
                } else {
                    this.showFormError(form, response.data.message);
                }
            } catch (error) {
                this.showFormError(form, 'Registration failed. Please try again.');
            }
        });
    }

    /**
     * Load liked videos
     */
    async loadLikedVideos() {
        try {
            const response = await this.core.ajax.getLikedVideos();
            if (response.success) {
                this.updateLikedVideosContent(response.data.videos);
            }
        } catch (error) {
            console.error('Failed to load liked videos:', error);
        }
    }

    /**
     * Load short videos
     */
    async loadShortVideos() {
        try {
            const response = await this.core.ajax.getShortVideos();
            if (response.success) {
                this.updateShortVideosContent(response.data.videos);
            }
        } catch (error) {
            console.error('Failed to load short videos:', error);
        }
    }

    /**
     * Set up global event listeners for the application
     */
    setupGlobalEventListeners() {
        // Listen for core events
        this.core.on('error:global', (event) => {
            console.error('Global error caught:', event.detail);
        });

        this.core.on('breakpoint:changed', (event) => {
            console.log('Breakpoint changed:', event.detail);
            // Notify all components about breakpoint change
            this.components.forEach(component => {
                if (component && typeof component.handleBreakpointChange === 'function') {
                    component.handleBreakpointChange(event.detail);
                }
            });
        });

        // Page visibility change
        document.addEventListener('visibilitychange', () => {
            const isVisible = !document.hidden;
            this.core.emit('page:visibility-changed', { isVisible });
        });

        // Page unload cleanup
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });

        // Handle dynamic content loading
        this.core.on('content:loaded', () => {
            this.reinitializeComponents();
        });
    }

    /**
     * Reinitialize components for dynamic content
     */
    reinitializeComponents() {
        this.components.forEach(component => {
            if (component && typeof component.reinitialize === 'function') {
                component.reinitialize();
            }
        });
    }

    /**
     * Helper methods for UI updates
     */
    updateTrendingContent(section, videos) {
        const container = section.querySelector('.trending-videos');
        if (container && videos) {
            // Implementation would update the trending videos display
            this.core.emit('content:updated', { section: 'trending', videos });
        }
    }

    filterByCategory(category) {
        const videoGrid = this.getComponent('videoGrid');
        if (videoGrid) {
            videoGrid.applyFilter('category', category);
        }
    }

    async loadVideoRecommendations(videoId, section) {
        try {
            const response = await this.core.ajax.getRecommendations(videoId);
            if (response.success) {
                // Update recommendations display
                this.core.emit('content:updated', { section: 'recommendations', videos: response.data });
            }
        } catch (error) {
                console.error('Failed to load recommendations:', error);
            }
        }

    updateLikeButton(button, data) {
        button.classList.toggle('liked', data.liked);
        const countElement = button.querySelector('.like-count');
        if (countElement) {
            countElement.textContent = data.likes_count;
        }
    }

    updateLikedVideosContent(videos) {
        const container = document.querySelector('.liked-videos-container');
        if (container) {
            // Implementation would update liked videos display
            this.core.emit('content:updated', { section: 'liked-videos', videos });
        }
    }

    updateShortVideosContent(videos) {
        const container = document.querySelector('.short-videos-container');
        if (container) {
            // Implementation would update short videos display
            this.core.emit('content:updated', { section: 'short-videos', videos });
        }
    }

    showFormError(form, message) {
        let errorDiv = form.querySelector('.form-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            form.insertBefore(errorDiv, form.firstChild);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    showFormSuccess(form, message) {
        let successDiv = form.querySelector('.form-success');
        if (!successDiv) {
            successDiv = document.createElement('div');
            successDiv.className = 'form-success';
            form.insertBefore(successDiv, form.firstChild);
        }
        successDiv.textContent = message;
        successDiv.style.display = 'block';
    }

    /**
     * Get component by name
     * @param {string} name - Component name
     * @returns {Object|null} Component instance or null
     */
    getComponent(name) {
        return this.components.get(name) || null;
    }

    /**
     * Get page module by name
     * @param {string} name - Page module name
     * @returns {Object|null} Page module instance or null
     */
    getPageModule(name) {
        return this.pageModules.get(name) || null;
    }

    /**
     * Get application state
     * @returns {Object} Current application state
     */
    getState() {
        return {
            isInitialized: this.isInitialized,
            currentPage: this.core.state.currentPage,
            components: Array.from(this.components.keys()),
            pageModules: Array.from(this.pageModules.keys()),
            breakpoint: this.core.state.currentBreakpoint,
            isMobile: this.core.state.isMobile
        };
    }

    /**
     * Reinitialize the application (useful for SPA-like behavior)
     */
    async reinitialize() {
        console.log('CustomTube: Reinitializing...');
        
        // Cleanup current state
        this.cleanup();
        
        // Reset initialization flag
        this.isInitialized = false;
        
        // Reinitialize
        await this.init();
    }

    /**
     * Cleanup method
     */
    cleanup() {
        console.log('CustomTube: Starting cleanup...');

        // Cleanup all components
        this.components.forEach((component, name) => {
            if (component && typeof component.cleanup === 'function') {
                try {
                    component.cleanup();
                } catch (error) {
                    console.error(`Error cleaning up component ${name}:`, error);
                }
            }
        });

        // Cleanup all page modules
        this.pageModules.forEach((module, name) => {
            if (module && typeof module.cleanup === 'function') {
                try {
                    module.cleanup();
                } catch (error) {
                    console.error(`Error cleaning up page module ${name}:`, error);
                }
            }
        });

        // Cleanup core
        if (this.core && typeof this.core.cleanup === 'function') {
            this.core.cleanup();
        }

        // Clear collections
        this.components.clear();
        this.pageModules.clear();

        // Reset state
        this.isInitialized = false;

        console.log('CustomTube: Cleanup complete');
    }
}

/**
 * Initialize CustomTube when DOM is ready
 */
function initializeCustomTube() {
    console.log('CustomTube: DOMContentLoaded event fired. Attempting to initialize CustomTube.'); // Added log
    // Create global instance
    window.CustomTube = new CustomTube();
    
    // Initialize the application
    window.CustomTube.init().catch(error => {
        console.error('Failed to initialize CustomTube:', error);
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCustomTube);
} else {
    // DOM is already ready
    initializeCustomTube();
}

// Export for module systems
export default CustomTube;
