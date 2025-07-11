/**
 * Carousel Component
 * A flexible, data-driven carousel component.
 */

import { addEvent, debounce, throttle } from '../core/utils.js';

export class Carousel {
    constructor(container, options = {}) {
        if (!container) {
            console.error('Carousel: Container element is required.');
            return;
        }

        this.container = container;
        this.options = {
            slides: [], // Array of slide data objects
            autoplay: false,
            autoplayInterval: 5000,
            loop: true,
            initialSlide: 0,
            slidesToShow: 1, // Number of slides visible at once
            scrollPerPage: false, // Scroll one page at a time (slidesToShow)
            keyboard: true,
            touch: true,
            lazyLoad: true,
            displayMode: 'default', // 'full-width-hero', 'multi-row-strip', 'micro-carousel'
            ...options
        };

        this.elements = {};
        this.state = {
            currentSlide: this.options.initialSlide,
            totalSlides: this.options.slides.length,
            isPaused: !this.options.autoplay,
            isDragging: false,
            startX: 0,
            scrollLeft: 0,
            autoplayTimer: null,
            intersectionObserver: null
        };

        this.events = []; // To store event listener cleanup functions

        this.init();
    }

    /**
     * Initialize the carousel.
     */
    init() {
        if (this.options.slides.length === 0) {
            console.warn('Carousel: No slides provided. Initializing empty carousel.');
            this.container.innerHTML = '<p class="carousel-empty-state">No slides available.</p>';
            return;
        }

        this.render();
        this.cacheElements();
        this.applyDisplayMode();
        this.setupEventListeners();
        this.setupLazyLoading();

        if (this.options.autoplay && !this.state.isPaused) {
            this.play();
        }

        this.goTo(this.state.currentSlide, false); // Render initial slide without transition
        console.log('Carousel: Initialized successfully.', this.options);
    }

    /**
     * Render the carousel structure and slides.
     */
    render() {
        this.container.classList.add('carousel-component');
        this.container.setAttribute('role', 'region');
        this.container.setAttribute('aria-label', 'Image Carousel');

        // Clear existing content before rendering new slides
        this.container.innerHTML = '';

        const trackContainer = document.createElement('div');
        trackContainer.className = 'carousel-track-container';

        const track = document.createElement('div');
        track.className = 'carousel-track';
        track.setAttribute('role', 'listbox');

        this.options.slides.forEach((slide, index) => {
            const slideElement = document.createElement('div');
            slideElement.className = 'carousel-slide';
            slideElement.setAttribute('role', 'option');
            slideElement.setAttribute('aria-label', `Slide ${index + 1} of ${this.options.slides.length}`);
            slideElement.setAttribute('data-slide-index', index);
            slideElement.innerHTML = this.renderSlideContent(slide);
            track.appendChild(slideElement);
        });

        trackContainer.appendChild(track);
        this.container.appendChild(trackContainer);

        const prevButton = document.createElement('button');
        prevButton.className = 'carousel-button carousel-button-prev';
        prevButton.setAttribute('aria-label', 'Previous slide');
        prevButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>';
        this.container.appendChild(prevButton);

        const nextButton = document.createElement('button');
        nextButton.className = 'carousel-button carousel-button-next';
        nextButton.setAttribute('aria-label', 'Next slide');
        nextButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
        this.container.appendChild(nextButton);

        const pagination = document.createElement('div');
        pagination.className = 'carousel-pagination';
        this.container.appendChild(pagination);
    }

    /**
     * Render individual slide content based on slide data.
     * @param {Object} slide - Slide data.
     * @returns {string} HTML string for slide content.
     */
    renderSlideContent(slide) {
        // Example: Render an image or video based on slide type
        if (slide.type === 'video' && slide.videoUrl) {
            return `
                <video class="carousel-slide-media" data-src="${slide.videoUrl}" muted playsinline preload="none" loop></video>
                <div class="carousel-slide-content">
                    ${slide.title ? `<h3 class="carousel-slide-title">${slide.title}</h3>` : ''}
                    ${slide.description ? `<p class="carousel-slide-description">${slide.description}</p>` : ''}
                    ${slide.buttonText && slide.buttonUrl ? `<a href="${slide.buttonUrl}" class="carousel-slide-button">${slide.buttonText}</a>` : ''}
                </div>
            `;
        } else if (slide.imageUrl) {
            return `
                <img class="carousel-slide-media" data-src="${slide.imageUrl}" alt="${slide.title || 'Carousel slide'}">
                <div class="carousel-slide-content">
                    ${slide.title ? `<h3 class="carousel-slide-title">${slide.title}</h3>` : ''}
                    ${slide.description ? `<p class="carousel-slide-description">${slide.description}</p>` : ''}
                    ${slide.buttonText && slide.buttonUrl ? `<a href="${slide.buttonUrl}" class="carousel-slide-button">${slide.buttonText}</a>` : ''}
                </div>
            `;
        } else {
            return `
                <div class="carousel-slide-content">
                    ${slide.title ? `<h3 class="carousel-slide-title">${slide.title}</h3>` : ''}
                    ${slide.description ? `<p class="carousel-slide-description">${slide.description}</p>` : ''}
                    ${slide.buttonText && slide.buttonUrl ? `<a href="${slide.buttonUrl}" class="carousel-slide-button">${slide.buttonText}</a>` : ''}
                </div>
            `;
        }
    }

    /**
     * Cache DOM elements after rendering.
     */
    cacheElements() {
        this.elements = {
            trackContainer: this.container.querySelector('.carousel-track-container'),
            track: this.container.querySelector('.carousel-track'),
            slides: Array.from(this.container.querySelectorAll('.carousel-slide')),
            prevButton: this.container.querySelector('.carousel-button-prev'),
            nextButton: this.container.querySelector('.carousel-button-next'),
            pagination: this.container.querySelector('.carousel-pagination')
        };
        this.createPaginationDots();
    }

    /**
     * Apply the specified display mode CSS class.
     */
    applyDisplayMode() {
        this.container.classList.add(`carousel-mode-${this.options.displayMode}`);
        if (this.options.slidesToShow > 1) {
            this.container.style.setProperty('--carousel-slides-to-show', this.options.slidesToShow);
        }
    }

    /**
     * Create pagination dots.
     */
    createPaginationDots() {
        if (!this.elements.pagination) return;

        this.elements.pagination.innerHTML = '';
        for (let i = 0; i < this.state.totalSlides; i++) {
            const dot = document.createElement('button');
            dot.classList.add('carousel-pagination-dot');
            dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
            dot.setAttribute('data-slide-index', i);
            this.elements.pagination.appendChild(dot);

            this.events.push(addEvent(dot, 'click', () => this.goTo(i)));
        }
    }

    /**
     * Set up all event listeners.
     */
    setupEventListeners() {
        // Clear existing event listeners before adding new ones
        this.events.forEach(removeListener => removeListener());
        this.events = [];

        if (this.elements.prevButton) {
            this.events.push(addEvent(this.elements.prevButton, 'click', () => this.prev()));
        }
        if (this.elements.nextButton) {
            this.events.push(addEvent(this.elements.nextButton, 'click', () => this.next()));
        }

        if (this.options.keyboard) {
            this.events.push(addEvent(document, 'keydown', this.handleKeyboard.bind(this)));
        }

        if (this.options.touch && this.elements.trackContainer) {
            this.events.push(addEvent(this.elements.trackContainer, 'mousedown', this.handleDragStart.bind(this), { passive: true }));
            this.events.push(addEvent(this.elements.trackContainer, 'mouseleave', this.handleDragEnd.bind(this), { passive: true }));
            this.events.push(addEvent(this.elements.trackContainer, 'mouseup', this.handleDragEnd.bind(this), { passive: true }));
            this.events.push(addEvent(this.elements.trackContainer, 'mousemove', this.handleDragMove.bind(this), { passive: true }));

            this.events.push(addEvent(this.elements.trackContainer, 'touchstart', this.handleDragStart.bind(this), { passive: true }));
            this.events.push(addEvent(this.elements.trackContainer, 'touchend', this.handleDragEnd.bind(this), { passive: true }));
            this.events.push(addEvent(this.elements.trackContainer, 'touchmove', this.handleDragMove.bind(this), { passive: true }));
        }

        // Pause autoplay on hover
        if (this.options.autoplay) {
            this.events.push(addEvent(this.container, 'mouseenter', () => this.pause()));
            this.events.push(addEvent(this.container, 'mouseleave', () => this.play()));
        }

        // Recalculate slide positions on resize
        this.events.push(addEvent(window, 'resize', debounce(() => this.goTo(this.state.currentSlide, false), 250)));
    }

    /**
     * Handle keyboard navigation.
     * @param {KeyboardEvent} event - The keyboard event.
     */
    handleKeyboard(event) {
        if (this.container.contains(document.activeElement) || this.container === document.activeElement) {
            if (event.key === 'ArrowLeft') {
                this.prev();
            } else if (event.key === 'ArrowRight') {
                this.next();
            }
        }
    }

    /**
     * Handle drag start for touch/swipe.
     * @param {MouseEvent|TouchEvent} event - The event.
     */
    handleDragStart(event) {
        this.state.isDragging = true;
        this.elements.trackContainer.classList.add('dragging');
        this.state.startX = (event.pageX || event.touches[0].pageX) - this.elements.trackContainer.offsetLeft;
        this.state.scrollLeft = this.elements.trackContainer.scrollLeft;
        cancelAnimationFrame(this.state.autoplayTimer); // Pause autoplay during drag
    }

    /**
     * Handle drag move for touch/swipe.
     * @param {MouseEvent|TouchEvent} event - The event.
     */
    handleDragMove(event) {
        if (!this.state.isDragging) return;
        // event.preventDefault(); // Removed to allow passive listener
        const x = (event.pageX || event.touches[0].pageX) - this.elements.trackContainer.offsetLeft;
        const walk = (x - this.state.startX) * 1.5; // Adjust scroll speed
        this.elements.trackContainer.scrollLeft = this.state.scrollLeft - walk;
    }

    /**
     * Handle drag end for touch/swipe.
     */
    handleDragEnd() {
        this.state.isDragging = false;
        this.elements.trackContainer.classList.remove('dragging');
        if (this.options.autoplay && this.state.isPaused) {
            this.play(); // Resume autoplay after drag ends
        }
        // Snap to nearest slide if not scrolling per page
        if (!this.options.scrollPerPage) {
            this.snapToNearestSlide();
        }
    }

    /**
     * Snap to the nearest slide after dragging.
     */
    snapToNearestSlide() {
        const scrollLeft = this.elements.trackContainer.scrollLeft;
        const slideWidth = this.elements.slides[0].offsetWidth;
        const newSlideIndex = Math.round(scrollLeft / slideWidth);
        this.goTo(newSlideIndex);
    }

    /**
     * Go to a specific slide.
     * @param {number} index - The index of the slide to go to.
     * @param {boolean} smooth - Whether to use smooth scrolling (default: true).
     */
    goTo(index, smooth = true) {
        let targetIndex = index;
        if (this.options.loop) {
            targetIndex = (index + this.state.totalSlides) % this.state.totalSlides;
        } else {
            targetIndex = Math.max(0, Math.min(index, this.state.totalSlides - this.options.slidesToShow));
        }

        this.state.currentSlide = targetIndex;

        const slideWidth = this.elements.slides[0].offsetWidth;
        const scrollAmount = targetIndex * slideWidth;

        this.elements.trackContainer.scrollTo({
            left: scrollAmount,
            behavior: smooth ? 'smooth' : 'auto'
        });

        this.updateActiveState();
        this.loadVisibleSlides();
        this.emit('slideChange', { currentSlide: this.state.currentSlide });
    }

    /**
     * Go to the next slide.
     */
    next() {
        const nextIndex = this.state.currentSlide + (this.options.scrollPerPage ? this.options.slidesToShow : 1);
        this.goTo(nextIndex);
    }

    /**
     * Go to the previous slide.
     */
    prev() {
        const prevIndex = this.state.currentSlide - (this.options.scrollPerPage ? this.options.slidesToShow : 1);
        this.goTo(prevIndex);
    }

    /**
     * Pause autoplay.
     */
    pause() {
        if (this.state.autoplayTimer) {
            clearInterval(this.state.autoplayTimer);
            this.state.autoplayTimer = null;
            this.state.isPaused = true;
            this.emit('pause');
        }
    }

    /**
     * Start/resume autoplay.
     */
    play() {
        if (!this.options.autoplay || !this.state.isPaused) return;

        this.state.autoplayTimer = setInterval(() => {
            this.next();
        }, this.options.autoplayInterval);
        this.state.isPaused = false;
        this.emit('play');
    }

    /**
     * Update active state for slides and pagination dots.
     */
    updateActiveState() {
        this.elements.slides.forEach((slide, index) => {
            if (index >= this.state.currentSlide && index < this.state.currentSlide + this.options.slidesToShow) {
                slide.classList.add('is-active');
                slide.setAttribute('aria-hidden', 'false');
            } else {
                slide.classList.remove('is-active');
                slide.setAttribute('aria-hidden', 'true');
            }
        });

        Array.from(this.elements.pagination.children).forEach((dot, index) => {
            if (index === this.state.currentSlide) {
                dot.classList.add('is-active');
            } else {
                dot.classList.remove('is-active');
            }
        });

        // Update button disabled states for non-looping carousels
        if (!this.options.loop) {
            if (this.elements.prevButton) {
                this.elements.prevButton.disabled = this.state.currentSlide === 0;
            }
            if (this.elements.nextButton) {
                this.elements.nextButton.disabled = this.state.currentSlide >= this.state.totalSlides - this.options.slidesToShow;
            }
        }
    }

    /**
     * Set up Intersection Observer for lazy loading.
     */
    setupLazyLoading() {
        if (this.state.intersectionObserver) {
            this.state.intersectionObserver.disconnect();
        }

        if (!this.options.lazyLoad || !('IntersectionObserver' in window)) {
            this.loadAllSlides(); // Load all if lazy loading is off or not supported
            return;
        }

        this.state.intersectionObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadSlideMedia(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: this.elements.trackContainer, // Observe relative to the scrollable track
            rootMargin: '0px',
            threshold: 0.1 // Trigger when 10% of the slide is visible
        });

        this.elements.slides.forEach(slide => {
            this.state.intersectionObserver.observe(slide);
        });
    }

    /**
     * Load media for a specific slide.
     * @param {Element} slideElement - The slide DOM element.
     */
    loadSlideMedia(slideElement) {
        const mediaElement = slideElement.querySelector('.carousel-slide-media');
        if (mediaElement && mediaElement.dataset.src) {
            mediaElement.src = mediaElement.dataset.src;
            mediaElement.removeAttribute('data-src');
            mediaElement.classList.add('is-loaded');

            if (mediaElement.tagName === 'VIDEO') {
                mediaElement.load(); // Load video data
                if (slideElement.classList.contains('is-active')) {
                    mediaElement.play().catch(e => console.warn('Video autoplay prevented:', e));
                }
            }
            this.emit('slideMediaLoaded', { slideElement });
        }
    }

    /**
     * Load media for all slides (fallback if lazy loading is disabled).
     */
    loadAllSlides() {
        this.elements.slides.forEach(slide => this.loadSlideMedia(slide));
    }

    /**
     * Load media for currently visible slides.
     */
    loadVisibleSlides() {
        this.elements.slides.forEach((slide, index) => {
            if (index >= this.state.currentSlide && index < this.state.currentSlide + this.options.slidesToShow) {
                this.loadSlideMedia(slide);
            }
        });
    }

    /**
     * Update the carousel with new slides.
     * This is the exposed API method for dynamic reloading.
     * @param {Array} newSlides - An array of new slide data objects.
     * @param {number} initialSlide - The index of the slide to start on after update.
     */
    updateSlides(newSlides, initialSlide = 0) {
        this.pause(); // Stop autoplay during update
        this.options.slides = newSlides;
        this.state.totalSlides = newSlides.length;
        this.state.currentSlide = initialSlide;

        this.render(); // Re-render the carousel with new slides
        this.cacheElements(); // Re-cache elements after re-render
        this.setupEventListeners(); // Re-setup event listeners for new elements
        this.setupLazyLoading(); // Re-setup lazy loading for new slides

        this.goTo(this.state.currentSlide, false); // Go to the initial slide without animation
        if (this.options.autoplay) {
            this.play(); // Resume autoplay
        }
        console.log('Carousel: Slides updated dynamically.', newSlides);
        this.emit('slides-updated', { carouselId: this.container.id, newSlides });
    }

    /**
     * Emit a custom event.
     * @param {string} name - Event name.
     * @param {Object} detail - Event detail data.
     */
    emit(name, detail = {}) {
        this.container.dispatchEvent(new CustomEvent(`carousel:${name}`, { detail }));
    }

    /**
     * Add an event listener to the carousel.
     * @param {string} name - Event name.
     * @param {Function} callback - Callback function.
     */
    on(name, callback) {
        this.container.addEventListener(`carousel:${name}`, callback);
    }

    /**
     * Remove an event listener from the carousel.
     * @param {string} name - Event name.
     * @param {Function} callback - Callback function.
     */
    off(name, callback) {
        this.container.removeEventListener(`carousel:${name}`, callback);
    }

    /**
     * Clean up event listeners and observers.
     */
    cleanup() {
        this.pause(); // Stop autoplay

        this.events.forEach(removeListener => removeListener());
        this.events = [];

        if (this.state.intersectionObserver) {
            this.state.intersectionObserver.disconnect();
            this.state.intersectionObserver = null;
        }

        this.container.innerHTML = ''; // Clear carousel content
        this.container.classList.remove('carousel-component', `carousel-mode-${this.options.displayMode}`);
        this.container.removeAttribute('role');
        this.container.removeAttribute('aria-label');

        console.log('Carousel: Cleaned up.');
    }
}
