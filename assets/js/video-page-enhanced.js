/**
 * Enhanced Video Page Functionality
 * 
 * Adds interactive features to the video page layout:
 * - Expandable/collapsible description
 * - Related videos sidebar
 * - Responsive adjustments
 */
document.addEventListener('DOMContentLoaded', function() {
    // Setup expandable description
    setupExpandableDescription();
    
    // Initialize any video-specific features
    initializeVideoFeatures();
    
    // Track video view progress
    trackViewProgress();
});

/**
 * Setup expandable description functionality
 */
function setupExpandableDescription() {
    const descriptionEl = document.querySelector('.video-description');
    if (!descriptionEl) return;
    
    // Check if description needs expand functionality (is it truncated?)
    const isDescriptionTruncated = descriptionEl.scrollHeight > descriptionEl.clientHeight;
    
    if (isDescriptionTruncated) {
        // Create the container and expand button
        const expandableContainer = document.createElement('div');
        expandableContainer.className = 'expandable-container';
        
        // Create expand button
        const expandButton = document.createElement('div');
        expandButton.className = 'video-description-toggle';
        expandButton.textContent = 'Show more';
        expandButton.setAttribute('aria-expanded', 'false');
        expandButton.setAttribute('tabindex', '0');
        
        // Insert elements into the DOM
        descriptionEl.parentNode.insertBefore(expandableContainer, descriptionEl.nextSibling);
        expandableContainer.appendChild(expandButton);
        
        // Add event listener for expand/collapse
        expandButton.addEventListener('click', function() {
            const isExpanded = descriptionEl.classList.toggle('expanded');
            expandButton.textContent = isExpanded ? 'Show less' : 'Show more';
            expandButton.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        });
        
        // Also allow keyboard interaction
        expandButton.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                expandButton.click();
            }
        });
    }
}

/**
 * Initialize video-specific features
 */
function initializeVideoFeatures() {
    // Add active state to related videos
    const relatedVideos = document.querySelectorAll('.sidebar-video-item');
    relatedVideos.forEach(video => {
        video.addEventListener('mouseenter', function() {
            this.classList.add('active');
        });
        
        video.addEventListener('mouseleave', function() {
            this.classList.remove('active');
        });
    });
    
    // Set up social sharing functionality
    setupSocialSharing();
}

/**
 * Setup social sharing buttons
 */
function setupSocialSharing() {
    const shareButtons = document.querySelectorAll('.social-share-buttons a');
    
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // If it's not an email share, open in a popup window
            if (!this.classList.contains('email-share')) {
                e.preventDefault();
                const url = this.getAttribute('href');
                window.open(url, 'share-window', 'width=600,height=400');
            }
        });
    });
}

/**
 * Track video view progress
 */
function trackViewProgress() {
    const videoPlayer = document.querySelector('.video-player-wrapper video, .video-player-wrapper iframe');
    if (!videoPlayer) return;
    
    // For native video elements
    if (videoPlayer.tagName === 'VIDEO') {
        let progressTracked = {
            '25': false,
            '50': false,
            '75': false,
            '90': false
        };
        
        videoPlayer.addEventListener('timeupdate', function() {
            const progress = (videoPlayer.currentTime / videoPlayer.duration) * 100;
            
            // Track progress at specific points
            Object.keys(progressTracked).forEach(percent => {
                if (progress >= parseInt(percent) && !progressTracked[percent]) {
                    progressTracked[percent] = true;
                    
                    // You could send this data to an analytics endpoint
                    console.log(`Video watched ${percent}%`);
                }
            });
        });
    }
}

/**
 * Set up responsive layout adjustments
 */
function handleResponsiveLayout() {
    const videoSingle = document.querySelector('.video-single');
    if (!videoSingle) return;
    
    // Move related videos to sidebar on desktop, below content on mobile
    const mediaQuery = window.matchMedia('(min-width: 1200px)');
    
    function handleMediaChange(e) {
        if (e.matches) {
            // Desktop layout
            console.log('Desktop layout applied');
        } else {
            // Mobile layout
            console.log('Mobile layout applied');
        }
    }
    
    // Initial check
    handleMediaChange(mediaQuery);
    
    // Listen for changes
    mediaQuery.addEventListener('change', handleMediaChange);
}