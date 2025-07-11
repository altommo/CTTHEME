/**
 * Filter Results Display Management
 * 
 * Ensures filter results replace the trending section when filters are applied
 */

jQuery(document).ready(function($) {
    // Handle filter button click
    $('.filter-button, .apply-filters, .filter-panel .category-filter a, .filter-panel .tag-filter a, .filter-panel .performer-filter a').on('click', function() {
        // Add a class to the body to indicate filtering is active
        $('body').addClass('filtering-active');
    });
    
    // Custom event handler for when filter results are loaded
    $(document).on('filter_results_loaded', function(e, resultHTML) {
        // Hide the trending section during filter results
        $('.trending-section').hide();
        
        // Check if filter results container already exists
        if ($('#filter-results-container').length === 0) {
            // Create a container for filter results
            var $resultsContainer = $('<div id="filter-results-container" class="filter-results-section"></div>');
            
            // Insert the container after the filter panel or before the trending section
            if ($('.filter-panel').length) {
                $('.filter-panel').after($resultsContainer);
            } else {
                $('.trending-section').before($resultsContainer);
            }
        }
        
        // Add content to the results container
        $('#filter-results-container').html(resultHTML);
        
        // Scroll to the results
        $('html, body').animate({
            scrollTop: $('#filter-results-container').offset().top - 100
        }, 500);
    });
    
    // Handle filter reset
    $('.reset-filters, .filter-actions .reset').on('click', function() {
        // Show the trending section again
        $('.trending-section').show();
        
        // Remove filter results container
        $('#filter-results-container').remove();
        
        // Remove filtering active class
        $('body').removeClass('filtering-active');
    });
    
    // Modify AJAX filter submission to trigger our custom event
    if (typeof(customtubeSettings) !== 'undefined') {
        var originalAjaxSuccess = null;
        
        // Store a reference to the original AJAX settings
        $(document).on('ajaxSend', function(event, xhr, settings) {
            if (settings.url === customtubeSettings.ajax_url && 
                settings.data && 
                settings.data.indexOf('customtube_filter_videos') !== -1) {
                
                // Store the original success callback
                originalAjaxSuccess = settings.success;
                
                // Modify the success callback
                settings.success = function(response) {
                    // Call the original success callback
                    if (originalAjaxSuccess) {
                        originalAjaxSuccess(response);
                    }
                    
                    // Trigger our custom event with the response HTML
                    if (response.success && response.data && response.data.html) {
                        $(document).trigger('filter_results_loaded', [response.data.html]);
                    }
                };
            }
        });
    }
});