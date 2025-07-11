/**
 * View More Button Functionality
 * 
 * Adds "View More" buttons to homepage sections that load additional videos via AJAX
 */

jQuery(document).ready(function($) {
    // Store the current page for each section
    var sectionPages = {};
    
    // Initialize view more buttons
    function initViewMoreButtons() {
        // Find all sections with video grids that should have view more buttons
        $('.video-section').each(function(index) {
            var $section = $(this);
            var sectionId = $section.attr('id');
            
            // If section doesn't have an ID, generate one
            if (!sectionId) {
                sectionId = 'video-section-' + index;
                $section.attr('id', sectionId);
            }
            
            // Skip sections that already have a view more button
            if ($section.find('.view-more-button').length) {
                return;
            }
            
            // Get the section title to use in AJAX requests
            var sectionTitle = $section.find('.section-title').text().trim();
            var sectionType = getSectionType(sectionTitle);
            
            // Initialize the page counter for this section
            sectionPages[sectionId] = 1;
            
            // Add view more button if the section has items
            if ($section.find('.video-card').length > 0) {
                var $viewMoreButton = $('<div class="view-more-container"><button class="view-more-button" data-section="' + sectionId + '" data-type="' + sectionType + '">View More</button></div>');
                $section.append($viewMoreButton);
            }
        });
        
        // Attach click event to view more buttons
        $('.view-more-button').off('click').on('click', loadMoreVideos);
    }
    
    // Determine the section type from its title
    function getSectionType(title) {
        title = title.toLowerCase();
        
        if (title.includes('trend') || title.includes('popular')) {
            return 'trending';
        } else if (title.includes('recent') || title.includes('latest') || title.includes('new')) {
            return 'recent';
        } else if (title.includes('short')) {
            return 'short';
        } else if (title.includes('long')) {
            return 'long';
        } else {
            // Default to recent videos
            return 'recent';
        }
    }
    
    // Load more videos when button is clicked
    function loadMoreVideos() {
        var $button = $(this);
        var sectionId = $button.data('section');
        var sectionType = $button.data('type');
        var $section = $('#' + sectionId);
        var $grid = $section.find('.video-grid');
        
        // Show loading state
        $button.prop('disabled', true).text('Loading...');
        
        // Increment the page counter
        sectionPages[sectionId]++;
        
        // Make AJAX request to load more videos
        $.ajax({
            url: customtubeData.ajaxurl,
            type: 'POST',
            data: {
                action: 'customtube_load_more_videos',
                nonce: customtubeData.nonce,
                page: sectionPages[sectionId],
                type: sectionType,
                count: 8 // Number of videos to load
            },
            success: function(response) {
                if (response.success) {
                    // Append new videos to the grid
                    var $newVideos = $(response.data.html).find('.video-card');
                    $grid.append($newVideos);
                    
                    // Initialize hover previews for new videos
                    if (typeof initHoverPreviews === 'function') {
                        initHoverPreviews();
                    }
                    
                    // Hide the button if no more videos
                    if (!response.data.has_more) {
                        $button.parent().fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        // Reset button state
                        $button.prop('disabled', false).text('View More');
                    }
                } else {
                    // Handle error
                    $button.parent().html('<p class="error-message">Error loading videos</p>');
                    console.error('Error loading videos:', response.data);
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                $button.parent().html('<p class="error-message">Error loading videos</p>');
                console.error('AJAX error:', status, error);
            }
        });
    }
    
    // Initialize on page load
    initViewMoreButtons();
    
    // Re-initialize when content is updated via AJAX
    $(document).on('content_updated', initViewMoreButtons);
});