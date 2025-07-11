<?php
/**
 * CustomTube Admin Settings Framework
 *
 * Handles the main settings framework for the CustomTube theme admin dashboard.
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register the CustomTube admin menu
 */
function customtube_admin_menu() {
    // Main menu item
    add_menu_page(
        __('CustomTube Dashboard', 'customtube'),
        __('CustomTube', 'customtube'),
        'manage_options',
        'customtube',
        'customtube_dashboard_page',
        'dashicons-video-alt2',
        30
    );
    
    // Dashboard submenu
    add_submenu_page(
        'customtube',
        __('Dashboard', 'customtube'),
        __('Dashboard', 'customtube'),
        'manage_options',
        'customtube',
        'customtube_dashboard_page'
    );
    
    // Settings page
    add_submenu_page(
        'customtube',
        __('Settings', 'customtube'),
        __('Settings', 'customtube'),
        'manage_options',
        'customtube-settings',
        'customtube_settings_page'
    );
    
    // Tools page (combines existing tools)
    add_submenu_page(
        'customtube',
        __('Tools', 'customtube'),
        __('Tools', 'customtube'),
        'manage_options',
        'customtube-tools',
        'customtube_tools_page'
    );
    
    // Import/Export page
    add_submenu_page(
        'customtube',
        __('Import/Export', 'customtube'),
        __('Import/Export', 'customtube'),
        'manage_options',
        'customtube-import-export',
        'customtube_import_export_page'
    );
}
add_action('admin_menu', 'customtube_admin_menu');

// Note: customtube_admin_enqueue_scripts() function is already defined in functions.php
// to avoid conflicts, we'll use the one from functions.php

/**
 * Register settings
 */
function customtube_register_settings() {
    // Register setting
    register_setting('customtube_options', 'customtube_options', 'customtube_validate_options');
    
    // General Settings
    add_settings_section(
        'customtube_general',
        __('General Settings', 'customtube'),
        'customtube_general_section_callback',
        'customtube-settings'
    );
    
    // Age Verification Settings
    add_settings_section(
        'customtube_age_verification',
        __('Age Verification', 'customtube'),
        'customtube_age_verification_section_callback',
        'customtube-settings'
    );
    
    // Video Settings
    add_settings_section(
        'customtube_video',
        __('Video Settings', 'customtube'),
        'customtube_video_section_callback',
        'customtube-settings'
    );
    
    // SEO Settings
    add_settings_section(
        'customtube_seo',
        __('SEO Settings', 'customtube'),
        'customtube_seo_section_callback',
        'customtube-settings'
    );
    
    // Performance Settings
    add_settings_section(
        'customtube_performance',
        __('Performance', 'customtube'),
        'customtube_performance_section_callback',
        'customtube-settings'
    );
    
    // Add fields to General section
    add_settings_field(
        'site_logo',
        __('Site Logo', 'customtube'),
        'customtube_site_logo_callback',
        'customtube-settings',
        'customtube_general'
    );
    
    add_settings_field(
        'mobile_logo',
        __('Mobile Logo', 'customtube'),
        'customtube_mobile_logo_callback',
        'customtube-settings',
        'customtube_general'
    );
    
    add_settings_field(
        'default_color_scheme',
        __('Default Color Scheme', 'customtube'),
        'customtube_default_color_scheme_callback',
        'customtube-settings',
        'customtube_general'
    );
    
    // Add fields to Age Verification section
    add_settings_field(
        'age_verification_enabled',
        __('Enable Age Verification', 'customtube'),
        'customtube_age_verification_enabled_callback',
        'customtube-settings',
        'customtube_age_verification'
    );
    
    add_settings_field(
        'age_verification_text',
        __('Age Verification Text', 'customtube'),
        'customtube_age_verification_text_callback',
        'customtube-settings',
        'customtube_age_verification'
    );
    
    add_settings_field(
        'age_verification_redirect',
        __('Underage Redirect URL', 'customtube'),
        'customtube_age_verification_redirect_callback',
        'customtube-settings',
        'customtube_age_verification'
    );
    
    add_settings_field(
        'age_verification_cookie_expiry',
        __('Cookie Expiration (days)', 'customtube'),
        'customtube_age_verification_cookie_expiry_callback',
        'customtube-settings',
        'customtube_age_verification'
    );
    
    // Add fields to Video section
    add_settings_field(
        'default_player',
        __('Default Video Player', 'customtube'),
        'customtube_default_player_callback',
        'customtube-settings',
        'customtube_video'
    );
    
    add_settings_field(
        'autoplay_enabled',
        __('Enable Autoplay', 'customtube'),
        'customtube_autoplay_enabled_callback',
        'customtube-settings',
        'customtube_video'
    );
    
    add_settings_field(
        'hover_preview_enabled',
        __('Enable Hover Previews', 'customtube'),
        'customtube_hover_preview_enabled_callback',
        'customtube-settings',
        'customtube_video'
    );
    
    // Add fields to SEO section
    add_settings_field(
        'video_schema_enabled',
        __('Enable VideoObject Schema', 'customtube'),
        'customtube_video_schema_enabled_callback',
        'customtube-settings',
        'customtube_seo'
    );
    
    add_settings_field(
        'video_sitemap_enabled',
        __('Enable Video Sitemap', 'customtube'),
        'customtube_video_sitemap_enabled_callback',
        'customtube-settings',
        'customtube_seo'
    );
    
    // Add fields to Performance section
    add_settings_field(
        'lazy_loading_enabled',
        __('Enable Lazy Loading', 'customtube'),
        'customtube_lazy_loading_enabled_callback',
        'customtube-settings',
        'customtube_performance'
    );
    
    add_settings_field(
        'image_optimization',
        __('Image Optimization Level', 'customtube'),
        'customtube_image_optimization_callback',
        'customtube-settings',
        'customtube_performance'
    );
}
add_action('admin_init', 'customtube_register_settings');

/**
 * Get default options
 * 
 * This function is used internally by the admin settings framework
 * and provides all the default settings.
 */
function customtube_admin_default_options() {
    $defaults = array(
        // General
        'site_logo' => '',
        'mobile_logo' => '',
        'default_color_scheme' => 'dark',
        
        // Age Verification
        'age_verification_enabled' => true,
        'age_verification_text' => 'This website contains adult content. You must be at least 18 years old to enter.',
        'age_verification_redirect' => '',
        'age_verification_cookie_expiry' => 30,
        
        // Video
        'default_player' => 'video-js',
        'autoplay_enabled' => false,
        'hover_preview_enabled' => true,
        
        // SEO
        'video_schema_enabled' => true,
        'video_sitemap_enabled' => true,
        
        // Performance
        'lazy_loading_enabled' => true,
        'image_optimization' => 'medium',
    );
    
    return apply_filters('customtube_default_options', $defaults);
}

// Use the global functions if they are defined in functions.php,
// otherwise define them here for backward compatibility
if (!function_exists('customtube_get_options')) {
    /**
     * Get the current options, with defaults as fallback
     */
    function customtube_get_options() {
        $options = get_option('customtube_options', array());
        return wp_parse_args($options, customtube_admin_default_options());
    }
}

if (!function_exists('customtube_get_option')) {
    /**
     * Get a specific option
     */
    function customtube_get_option($key, $default = null) {
        $options = customtube_get_options();
        
        if (isset($options[$key])) {
            return $options[$key];
        }
        
        if ($default !== null) {
            return $default;
        }
        
        $defaults = customtube_admin_default_options();
        return isset($defaults[$key]) ? $defaults[$key] : null;
    }
}

/**
 * Validate options
 */
function customtube_validate_options($input) {
    $input = $input ? $input : array();
    $output = array();
    
    // General
    $output['site_logo'] = isset($input['site_logo']) ? esc_url_raw($input['site_logo']) : '';
    $output['mobile_logo'] = isset($input['mobile_logo']) ? esc_url_raw($input['mobile_logo']) : '';
    $output['default_color_scheme'] = isset($input['default_color_scheme']) ? sanitize_text_field($input['default_color_scheme']) : 'dark';
    
    // Age Verification
    $output['age_verification_enabled'] = isset($input['age_verification_enabled']) ? (bool) $input['age_verification_enabled'] : false;
    $output['age_verification_text'] = isset($input['age_verification_text']) ? wp_kses_post($input['age_verification_text']) : '';
    $output['age_verification_redirect'] = isset($input['age_verification_redirect']) ? esc_url_raw($input['age_verification_redirect']) : '';
    $output['age_verification_cookie_expiry'] = isset($input['age_verification_cookie_expiry']) ? intval($input['age_verification_cookie_expiry']) : 30;
    
    // Video
    $output['default_player'] = isset($input['default_player']) ? sanitize_text_field($input['default_player']) : 'video-js';
    $output['autoplay_enabled'] = isset($input['autoplay_enabled']) ? (bool) $input['autoplay_enabled'] : false;
    $output['hover_preview_enabled'] = isset($input['hover_preview_enabled']) ? (bool) $input['hover_preview_enabled'] : true;
    
    // SEO
    $output['video_schema_enabled'] = isset($input['video_schema_enabled']) ? (bool) $input['video_schema_enabled'] : true;
    $output['video_sitemap_enabled'] = isset($input['video_sitemap_enabled']) ? (bool) $input['video_sitemap_enabled'] : true;
    
    // Performance
    $output['lazy_loading_enabled'] = isset($input['lazy_loading_enabled']) ? (bool) $input['lazy_loading_enabled'] : true;
    $output['image_optimization'] = isset($input['image_optimization']) ? sanitize_text_field($input['image_optimization']) : 'medium';
    
    return apply_filters('customtube_validate_options', $output, $input);
}

/**
 * Section callbacks
 */
function customtube_general_section_callback() {
    echo '<p>' . __('Configure general settings for your CustomTube site.', 'customtube') . '</p>';
}

function customtube_age_verification_section_callback() {
    echo '<p>' . __('Configure age verification settings for your adult content.', 'customtube') . '</p>';
}

function customtube_video_section_callback() {
    echo '<p>' . __('Configure video player and display settings.', 'customtube') . '</p>';
}

function customtube_seo_section_callback() {
    echo '<p>' . __('Configure SEO settings for better video discoverability.', 'customtube') . '</p>';
}

function customtube_performance_section_callback() {
    echo '<p>' . __('Configure performance settings for optimal site speed.', 'customtube') . '</p>';
}

/**
 * Field callbacks
 */

// General settings fields
function customtube_site_logo_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $value = isset($options['site_logo']) ? $options['site_logo'] : '';
    
    echo '<input type="text" id="site_logo" name="customtube_options[site_logo]" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<button type="button" class="button customtube-media-upload" data-target="site_logo">' . __('Select Image', 'customtube') . '</button>';
    
    if (!empty($value)) {
        echo '<div class="customtube-logo-preview"><img src="' . esc_url($value) . '" /></div>';
    }
    
    echo '<p class="description">' . __('Upload or select your site logo.', 'customtube') . '</p>';
}

function customtube_mobile_logo_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $value = isset($options['mobile_logo']) ? $options['mobile_logo'] : '';
    
    echo '<input type="text" id="mobile_logo" name="customtube_options[mobile_logo]" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<button type="button" class="button customtube-media-upload" data-target="mobile_logo">' . __('Select Image', 'customtube') . '</button>';
    
    if (!empty($value)) {
        echo '<div class="customtube-logo-preview"><img src="' . esc_url($value) . '" /></div>';
    }
    
    echo '<p class="description">' . __('Upload or select your mobile logo (optional).', 'customtube') . '</p>';
}

function customtube_default_color_scheme_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $value = isset($options['default_color_scheme']) ? $options['default_color_scheme'] : 'dark';
    
    echo '<select id="default_color_scheme" name="customtube_options[default_color_scheme]">';
    echo '<option value="dark" ' . selected($value, 'dark', false) . '>' . __('Dark Mode', 'customtube') . '</option>';
    echo '<option value="light" ' . selected($value, 'light', false) . '>' . __('Light Mode', 'customtube') . '</option>';
    echo '</select>';
    
    echo '<p class="description">' . __('Select the default color scheme for your site.', 'customtube') . '</p>';
}

// Age verification settings fields
function customtube_age_verification_enabled_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $checked = isset($options['age_verification_enabled']) && $options['age_verification_enabled'] ? 'checked="checked"' : '';
    
    echo '<label for="age_verification_enabled">';
    echo '<input type="checkbox" id="age_verification_enabled" name="customtube_options[age_verification_enabled]" value="1" ' . $checked . ' />';
    echo __('Enable age verification prompt for visitors', 'customtube');
    echo '</label>';
}

function customtube_age_verification_text_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $default_text = 'This website contains adult content and is only suitable for those who are 18 years or older. Please confirm your age to continue.';
    $value = isset($options['age_verification_text']) ? $options['age_verification_text'] : $default_text;
    
    echo '<textarea id="age_verification_text" name="customtube_options[age_verification_text]" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Customize the text displayed on the age verification prompt.', 'customtube') . '</p>';
}

function customtube_age_verification_redirect_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $value = isset($options['age_verification_redirect']) ? $options['age_verification_redirect'] : '';
    
    echo '<input type="url" id="age_verification_redirect" name="customtube_options[age_verification_redirect]" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">' . __('Optional URL to redirect underage visitors (leave empty to show message instead).', 'customtube') . '</p>';
}

function customtube_age_verification_cookie_expiry_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $value = isset($options['age_verification_cookie_expiry']) ? $options['age_verification_cookie_expiry'] : 30;
    
    echo '<input type="number" id="age_verification_cookie_expiry" name="customtube_options[age_verification_cookie_expiry]" value="' . esc_attr($value) . '" class="small-text" min="1" max="365" />';
    echo '<p class="description">' . __('Number of days the age verification cookie will be stored.', 'customtube') . '</p>';
}

// Video settings fields
function customtube_default_player_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $value = isset($options['default_player']) ? $options['default_player'] : 'video-js';
    
    echo '<select id="default_player" name="customtube_options[default_player]">';
    echo '<option value="video-js" ' . selected($value, 'video-js', false) . '>' . __('Video.js Player', 'customtube') . '</option>';
    echo '<option value="plyr" ' . selected($value, 'plyr', false) . '>' . __('Plyr Player', 'customtube') . '</option>';
    echo '<option value="html5" ' . selected($value, 'html5', false) . '>' . __('Default HTML5 Player', 'customtube') . '</option>';
    echo '</select>';
    
    echo '<p class="description">' . __('Select the default video player for direct video files.', 'customtube') . '</p>';
}

function customtube_autoplay_enabled_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $checked = isset($options['autoplay_enabled']) && $options['autoplay_enabled'] ? 'checked="checked"' : '';
    
    echo '<label for="autoplay_enabled">';
    echo '<input type="checkbox" id="autoplay_enabled" name="customtube_options[autoplay_enabled]" value="1" ' . $checked . ' />';
    echo __('Enable autoplay for videos (when supported by the browser)', 'customtube');
    echo '</label>';
}

function customtube_hover_preview_enabled_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $checked = isset($options['hover_preview_enabled']) && $options['hover_preview_enabled'] ? 'checked="checked"' : '';
    
    echo '<label for="hover_preview_enabled">';
    echo '<input type="checkbox" id="hover_preview_enabled" name="customtube_options[hover_preview_enabled]" value="1" ' . $checked . ' />';
    echo __('Enable hover preview for video thumbnails', 'customtube');
    echo '</label>';
}

// SEO settings fields
function customtube_video_schema_enabled_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $checked = isset($options['video_schema_enabled']) && $options['video_schema_enabled'] ? 'checked="checked"' : '';
    
    echo '<label for="video_schema_enabled">';
    echo '<input type="checkbox" id="video_schema_enabled" name="customtube_options[video_schema_enabled]" value="1" ' . $checked . ' />';
    echo __('Add VideoObject schema markup to video pages for better search engine visibility', 'customtube');
    echo '</label>';
}

function customtube_video_sitemap_enabled_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $checked = isset($options['video_sitemap_enabled']) && $options['video_sitemap_enabled'] ? 'checked="checked"' : '';
    
    echo '<label for="video_sitemap_enabled">';
    echo '<input type="checkbox" id="video_sitemap_enabled" name="customtube_options[video_sitemap_enabled]" value="1" ' . $checked . ' />';
    echo __('Generate a separate video sitemap for search engines', 'customtube');
    echo '</label>';
}

// Performance settings fields
function customtube_lazy_loading_enabled_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $checked = isset($options['lazy_loading_enabled']) && $options['lazy_loading_enabled'] ? 'checked="checked"' : '';
    
    echo '<label for="lazy_loading_enabled">';
    echo '<input type="checkbox" id="lazy_loading_enabled" name="customtube_options[lazy_loading_enabled]" value="1" ' . $checked . ' />';
    echo __('Enable lazy loading for images and video thumbnails', 'customtube');
    echo '</label>';
}

function customtube_image_optimization_callback() {
    // Get options using the correct function
    $options = function_exists('customtube_get_options') ? customtube_get_options() : get_option('customtube_options', array());
    $value = isset($options['image_optimization']) ? $options['image_optimization'] : 'medium';
    
    echo '<select id="image_optimization" name="customtube_options[image_optimization]">';
    echo '<option value="none" ' . selected($value, 'none', false) . '>' . __('None', 'customtube') . '</option>';
    echo '<option value="low" ' . selected($value, 'low', false) . '>' . __('Low', 'customtube') . '</option>';
    echo '<option value="medium" ' . selected($value, 'medium', false) . '>' . __('Medium', 'customtube') . '</option>';
    echo '<option value="high" ' . selected($value, 'high', false) . '>' . __('High', 'customtube') . '</option>';
    echo '</select>';
    
    echo '<p class="description">' . __('Set the level of image optimization (affects image quality and file size).', 'customtube') . '</p>';
}

/**
 * Include other admin page files
 */
require_once CUSTOMTUBE_DIR . '/inc/admin/dashboard.php';
require_once CUSTOMTUBE_DIR . '/inc/admin/tools.php';

// Import/Export is now part of the CustomTube Video Importer plugin
// Provide a stub function if it doesn't exist
if (!function_exists('customtube_import_export_page')) {
    function customtube_import_export_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('Import/Export', 'customtube') . '</h1>';
        echo '<div class="notice notice-warning"><p>' . 
             __('The Import/Export functionality requires the CustomTube Video Importer plugin. Please install and activate it to use this feature.', 'customtube') . 
             '</p></div>';
        echo '</div>';
    }
}

// Only include on admin pages
if (is_admin()) {
    // Add media uploader scripts
    function customtube_admin_scripts() {
        wp_enqueue_media();
    }
    add_action('admin_enqueue_scripts', 'customtube_admin_scripts');
}