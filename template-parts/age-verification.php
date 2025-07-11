<?php
/**
 * Modern Age Verification Template
 * 
 * @package CustomTube
 */

// Get settings from admin
$text = customtube_get_option('age_verification_text', 'This website contains adult content and is only suitable for those who are 18 years or older. Please confirm your age to continue.');
$redirect_url = customtube_get_option('age_verification_redirect', 'https://www.google.com');
$cookie_expiry = customtube_get_option('age_verification_cookie_expiry', 30);

// Convert days to seconds for the cookie
$cookie_expiry_seconds = 60 * 60 * 24 * intval($cookie_expiry);
?>

<div id="age-verification-overlay" class="age-verification-overlay">
    <div class="age-verification-modal">
        <div class="age-verification-logo">
            <?php 
            // Use the site logo if available
            if (function_exists('customtube_display_logo')) {
                customtube_display_logo();
            } elseif (has_custom_logo()) {
                the_custom_logo();
            } else {
                echo '<h2 class="age-verification-site-title">' . get_bloginfo('name') . '</h2>';
            }
            ?>
        </div>
        
        <h2><?php esc_html_e('Age Verification Required', 'customtube'); ?></h2>
        
        <div class="age-verification-text">
            <?php echo wp_kses_post($text); ?>
        </div>
        
        <div class="age-verification-buttons">
            <button id="age-confirm" class="age-confirm-button"><?php esc_html_e('I am 18 or older', 'customtube'); ?></button>
            <button id="age-cancel" class="age-cancel-button"><?php esc_html_e('I am under 18', 'customtube'); ?></button>
        </div>
        
        <div class="age-verification-links">
            <?php if (get_privacy_policy_url()): ?>
                <a href="<?php echo esc_url(get_privacy_policy_url()); ?>"><?php esc_html_e('Privacy Policy', 'customtube'); ?></a>
            <?php endif; ?>
            
            <?php if (get_page_by_path('terms-of-service')): ?>
                <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>"><?php esc_html_e('Terms of Service', 'customtube'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Modern Age Verification - Immediate execution
(function() {
    'use strict';
    
    function initAgeVerification() {
        console.log('🔧 Initializing modern age verification...');
        
        var confirmBtn = document.getElementById('age-confirm');
        var cancelBtn = document.getElementById('age-cancel');
        var overlay = document.getElementById('age-verification-overlay');
        
        if (!confirmBtn || !cancelBtn) {
            console.log('❌ Age verification buttons not found');
            return;
        }
        
        console.log('✅ Age verification buttons found');
        
        // Clear any existing event listeners
        confirmBtn.onclick = null;
        cancelBtn.onclick = null;
        
        // Confirm button (I am 18 or older)
        confirmBtn.onclick = function(e) {
            e.preventDefault();
            console.log('✅ Age confirmed - setting cookie');
            
            // Set cookie with the configured expiry time
            document.cookie = 'age_verified=1; max-age=<?php echo esc_js($cookie_expiry_seconds); ?>; path=/';
            
            // Hide overlay with fade effect
            if (overlay) {
                overlay.style.transition = 'opacity 0.3s ease-out';
                overlay.style.opacity = '0';
                setTimeout(function() {
                    overlay.style.display = 'none';
                }, 300);
            }
            
            console.log('🎉 Age verification completed');
            return false;
        };
        
        // Cancel button (I am under 18)
        cancelBtn.onclick = function(e) {
            e.preventDefault();
            console.log('❌ Age verification cancelled');
            
            <?php if (!empty($redirect_url)): ?>
            window.location.href = '<?php echo esc_js($redirect_url); ?>';
            <?php else: ?>
            alert('<?php esc_html_e('You must be at least 18 years old to view this website.', 'customtube'); ?>');
            <?php endif; ?>
            
            return false;
        };
        
        console.log('🎉 Modern age verification initialized successfully');
    }
    
    // Initialize immediately if DOM is ready, otherwise wait
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAgeVerification);
    } else {
        // DOM is already ready, init immediately
        initAgeVerification();
    }
    
    // Also init when page is fully loaded as backup
    window.addEventListener('load', initAgeVerification);
    
})();
</script>
