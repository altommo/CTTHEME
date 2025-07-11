/**
 * Duplicate Content Fixer Script
 * 
 * This script detects and fixes duplicate video-card-content elements
 * that might be present in the DOM.
 */
(function($) {
    'use strict';
    
    // Function to fix duplicate content in video cards
    function fixDuplicateContent() {
        $('.video-card').each(function() {
            var $card = $(this);
            var $contentDivs = $card.find('.video-card-content');
            
            // If there's more than one content div, keep only the first one
            if ($contentDivs.length > 1) {
                console.log('Found duplicate .video-card-content, removing extras');
                $contentDivs.not(':first').remove();
            }
            
            // Fix duplicate titles (sometimes happening independently)
            var $titles = $card.find('.video-card-title');
            if ($titles.length > 1) {
                console.log('Found duplicate .video-card-title, removing extras');
                $titles.not(':first').remove();
            }
            
            // Fix duplicate meta sections
            var $meta = $card.find('.video-card-meta');
            if ($meta.length > 1) {
                console.log('Found duplicate .video-card-meta, removing extras');
                $meta.not(':first').remove();
            }
        });
    }
    
    // Run on document ready
    $(document).ready(function() {
        fixDuplicateContent();
        
        // Run again after a slight delay to catch any content loaded after page load
        setTimeout(fixDuplicateContent, 500);
    });
    
    // Listen for any dynamically loaded content
    $(document).on('grid_items_added ajax_content_loaded post-load', function() {
        setTimeout(fixDuplicateContent, 100);
    });
    
    // Create a MutationObserver to catch dynamically added content
    if (window.MutationObserver) {
        var observer = new MutationObserver(function(mutations) {
            var shouldFix = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length) {
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        var node = mutation.addedNodes[i];
                        
                        // If a video card was added or something that might contain a video card
                        if (node.nodeType === 1 && (
                            $(node).hasClass('video-card') || 
                            $(node).find('.video-card').length > 0 ||
                            $(node).hasClass('video-card-content') ||
                            $(node).find('.video-card-content').length > 0)
                        ) {
                            shouldFix = true;
                            break;
                        }
                    }
                }
            });
            
            if (shouldFix) {
                setTimeout(fixDuplicateContent, 100);
            }
        });
        
        // Start observing the document body
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
})(jQuery);