<?php
/**
 * CustomTube Settings Page
 *
 * Renders the settings page interface for the CustomTube theme.
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Display the settings page
 */
function customtube_settings_page() {
    // Get the active tab
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    
    ?>
    <div class="wrap customtube-settings">
        <h1><?php _e('CustomTube Settings', 'customtube'); ?></h1>
        
        <?php settings_errors(); ?>
        
        <h2 class="nav-tab-wrapper customtube-settings-tabs">
            <a href="#general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'customtube'); ?></a>
            <a href="#age-verification" class="nav-tab <?php echo $active_tab == 'age-verification' ? 'nav-tab-active' : ''; ?>"><?php _e('Age Verification', 'customtube'); ?></a>
            <a href="#video" class="nav-tab <?php echo $active_tab == 'video' ? 'nav-tab-active' : ''; ?>"><?php _e('Video', 'customtube'); ?></a>
            <a href="#seo" class="nav-tab <?php echo $active_tab == 'seo' ? 'nav-tab-active' : ''; ?>"><?php _e('SEO', 'customtube'); ?></a>
            <a href="#performance" class="nav-tab <?php echo $active_tab == 'performance' ? 'nav-tab-active' : ''; ?>"><?php _e('Performance', 'customtube'); ?></a>
        </h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('customtube_options'); ?>
            
            <div id="general" class="customtube-tab-content">
                <table class="form-table">
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('General Settings', 'customtube'); ?></h3>
                        </th>
                    </tr>
                    <?php do_settings_fields('customtube-settings', 'customtube_general'); ?>
                </table>
            </div>
            
            <div id="age-verification" class="customtube-tab-content">
                <table class="form-table">
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('Age Verification Settings', 'customtube'); ?></h3>
                        </th>
                    </tr>
                    <?php do_settings_fields('customtube-settings', 'customtube_age_verification'); ?>
                </table>
            </div>
            
            <div id="video" class="customtube-tab-content">
                <table class="form-table">
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('Video Settings', 'customtube'); ?></h3>
                        </th>
                    </tr>
                    <?php do_settings_fields('customtube-settings', 'customtube_video'); ?>
                </table>
            </div>
            
            <div id="seo" class="customtube-tab-content">
                <table class="form-table">
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('SEO Settings', 'customtube'); ?></h3>
                        </th>
                    </tr>
                    <?php do_settings_fields('customtube-settings', 'customtube_seo'); ?>
                </table>
            </div>
            
            <div id="performance" class="customtube-tab-content">
                <table class="form-table">
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('Performance Settings', 'customtube'); ?></h3>
                        </th>
                    </tr>
                    <?php do_settings_fields('customtube-settings', 'customtube_performance'); ?>
                </table>
            </div>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle tab navigation
        $('.customtube-settings-tabs a').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            $('.customtube-settings-tabs a').removeClass('nav-tab-active');
            
            // Add active class to clicked tab
            $(this).addClass('nav-tab-active');
            
            // Hide all tab content
            $('.customtube-tab-content').hide();
            
            // Show the content for the active tab
            $($(this).attr('href')).show();
        });
        
        // Activate the first tab by default
        $('.customtube-settings-tabs a:first').trigger('click');
        
        // If URL has a hash, activate that tab
        if (window.location.hash) {
            var tabSelector = '.customtube-settings-tabs a[href="' + window.location.hash + '"]';
            if ($(tabSelector).length) {
                $(tabSelector).trigger('click');
            }
        }
    });
    </script>
    <?php
}