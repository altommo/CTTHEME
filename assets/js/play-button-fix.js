/**
 * Play Button Fix Script
 * 
 * This script fixes issues with duplicate play buttons by ensuring only one play button
 * is shown on video thumbnails. It works by:
 * 
 * 1. Removing any duplicate play buttons when the DOM loads
 * 2. Monitoring for new elements added to the page (for AJAX-loaded content)
 * 3. Making sure only the correct play button structure is used
 */
(function($) {
    'use strict';
    
    // Function to fix play buttons on all video thumbnails
    function fixPlayButtons() {
        console.log('Running play button fix...');
        
        // Process each video thumbnail container
        $('.video-thumbnail-container').each(function() {
            var $container = $(this);
            
            // Remove all play buttons except the first .overlay-play-icon
            var $playButtons = $container.find('[class*="play"]');
            
            // Keep track of the first overlay-play-icon
            var $primaryButton = $container.find('.overlay-play-icon').first();
            
            // If we don't have a primary button but have other play buttons, 
            // convert the first one to our standard format
            if ($primaryButton.length === 0 && $playButtons.length > 0) {
                $playButtons.first()
                    .removeClass()
                    .addClass('overlay-play-icon')
                    .attr('aria-hidden', 'true')
                    .empty();
                
                $primaryButton = $playButtons.first();
            }
            
            // Remove any additional play buttons
            $playButtons.not($primaryButton).remove();
            
            // Do NOT clean the primary button - keep the triangle
            // $primaryButton.empty();
            
            // Make sure the play button is positioned correctly in the DOM
            // It should be directly inside the container, before the thumbnail link
            if ($primaryButton.parent().is('.video-thumbnail-container') === false) {
                $container.prepend($primaryButton);
            }
            
            // Ensure the thumbnail link is present and properly structured
            var $thumbnailLink = $container.find('.thumbnail-link');
            if ($thumbnailLink.length === 0) {
                // Find any image that might be a thumbnail
                var $img = $container.find('img').first();
                if ($img.length > 0) {
                    // Get the post URL from any existing link or fallback to the current page
                    var href = $container.find('a').first().attr('href') || window.location.href;
                    
                    // Create proper thumbnail link
                    $img.wrap('<a href="' + href + '" class="thumbnail-link"></a>');
                }
            }
        });
    }
    
    // Run on document ready
    $(document).ready(function() {
        fixPlayButtons();
        
        // Run again after a slight delay to catch any lazy-loaded elements
        setTimeout(fixPlayButtons, 500);
    });
    
    // Listen for AJAX content loads
    $(document).on('grid_items_added ajax_content_loaded post-load', function() {
        // Allow a small delay for the DOM to update
        setTimeout(fixPlayButtons, 100);
    });
    
    // Create a MutationObserver to catch dynamically added content
    if (window.MutationObserver) {
        var observer = new MutationObserver(function(mutations) {
            // Check if any mutations added video thumbnails
            var needsFix = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length) {
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        var node = mutation.addedNodes[i];
                        
                        // If a video thumbnail container was added or something that might contain one
                        if (node.nodeType === 1 && (
                            $(node).hasClass('video-thumbnail-container') || 
                            $(node).find('.video-thumbnail-container').length > 0)
                        ) {
                            needsFix = true;
                            break;
                        }
                    }
                }
            });
            
            if (needsFix) {
                // Run the fix with a small delay to ensure the DOM is fully updated
                setTimeout(fixPlayButtons, 100);
            }
        });
        
        // Start observing the document body
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
})(jQuery);