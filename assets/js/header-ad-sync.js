/**
 * Header Ad Synchronization Script
 * Syncs header ad visibility with navigation bar transforms
 */

(function($) {
    'use strict';

    let headerAdSync = null;

    function initHeaderAdSync() {
        console.log('🎯 Header Ad Synchronization: Initializing...');
        
        // Clean up previous instance
        if (headerAdSync && headerAdSync.cleanup) {
            headerAdSync.cleanup();
        }
        
        const headerAd = document.querySelector('.tube-ads-header');
        const navigation = document.querySelector('.ph-nav, header#masthead');
        
        if (!headerAd || !navigation) {
            console.warn('⚠️ Header ad or navigation elements not found');
            return;
        }
        
        // Get dimensions - recalculate for responsive behavior
        const navRect = navigation.getBoundingClientRect();
        const adRect = headerAd.getBoundingClientRect();
        const navHeight = navRect.height; // Dynamic height (50px mobile, 70px desktop)
        const adHeight = adRect.height;
        
        console.log(`📏 Nav height: ${navHeight}px, Ad height: ${adHeight}px (Width: ${window.innerWidth}px)`);
        
        let lastNavTransform = '';
        
        function smartAdTransform() {
            const navStyles = getComputedStyle(navigation);
            const currentNavTransform = navStyles.transform;
            
            // Recalculate nav height in case window was resized
            const currentNavHeight = navigation.getBoundingClientRect().height;
            const currentAdHeight = headerAd.getBoundingClientRect().height;
            
            if (currentNavTransform !== lastNavTransform) {
                console.log('🔍 Nav transform:', currentNavTransform, `(Nav: ${currentNavHeight}px)`);
                
                if (currentNavTransform.includes('matrix') && currentNavTransform !== 'matrix(1, 0, 0, 1, 0, 0)') {
                    // Navigation is hiding - hide ad completely
                    const totalHideDistance = currentNavHeight + currentAdHeight;
                    headerAd.style.transform = `translateY(-${totalHideDistance}px)`;
                    console.log(`📉 Hiding ad completely (up ${totalHideDistance}px)`);
                    
                } else if (currentNavTransform === 'matrix(1, 0, 0, 1, 0, 0)' || currentNavTransform === 'none') {
                    // Navigation is visible - show ad
                    headerAd.style.transform = 'translateY(0px)';
                    console.log('📈 Showing ad (position: normal)');
                    
                } else {
                    // For any other transform, try to parse and adjust
                    const match = currentNavTransform.match(/matrix\(1, 0, 0, 1, 0, (-?\d+(?:\.\d+)?)\)/);
                    if (match) {
                        const navOffset = parseFloat(match[1]);
                        const adOffset = navOffset - currentAdHeight;
                        headerAd.style.transform = `translateY(${adOffset}px)`;
                        console.log(`🔄 Custom offset: nav ${navOffset}px, ad ${adOffset}px`);
                    }
                }
                
                lastNavTransform = currentNavTransform;
            }
        }
        
        // Watch for changes
        const observer = new MutationObserver(() => {
            smartAdTransform();
        });
        
        observer.observe(navigation, { 
            attributes: true, 
            attributeFilter: ['style', 'class'],
            subtree: false
        });
        
        // Check periodically
        const intervalCheck = setInterval(smartAdTransform, 100);
        
        // Initial sync
        smartAdTransform();
        
        console.log('✅ Header Ad Synchronization: Applied');
        console.log('📐 Ad will hide completely when nav hides');
        console.log('👁️ Ad will show normally when nav shows');
        
        // Store cleanup function
        headerAdSync = {
            cleanup: () => {
                observer.disconnect();
                clearInterval(intervalCheck);
                headerAd.style.transform = '';
                headerAd.style.transition = '';
                console.log('🧹 Header Ad Synchronization: Cleaned up');
            }
        };
    }

    // Initialize when document is ready
    $(document).ready(function() {
        // Small delay to ensure navigation is initialized first
        setTimeout(initHeaderAdSync, 100);
    });

})(jQuery);
