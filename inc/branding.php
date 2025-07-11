<?php
/**
 * Site Branding Functionality
 *
 * Handles site logo, favicon, and other branding elements
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Set up custom logo and site identity features
 */
function customtube_branding_setup() {
    // Add custom logo support with specific dimensions
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 220,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => array('site-title', 'site-description'),
    ));
    
    // Add site icon (favicon) support
    add_theme_support('site-icon', 512);
    
    // Add custom header support for banner images
    add_theme_support('custom-header', array(
        'default-image'      => '',
        'width'              => 1920,
        'height'             => 200,
        'flex-height'        => true,
        'flex-width'         => true,
        'uploads'            => true,
        'random-default'     => false,
        'header-text'        => false,
        'default-text-color' => '',
    ));
}
add_action('after_setup_theme', 'customtube_branding_setup');

/**
 * Display site logo or text based on availability
 */
function customtube_display_logo() {
    if (has_custom_logo()) {
        $logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image_src($logo_id, 'full');
        
        if ($logo) {
            $logo_url = $logo[0];
            $logo_width = $logo[1];
            $logo_height = $logo[2];
            $alt_text = get_post_meta($logo_id, '_wp_attachment_image_alt', true) ?: get_bloginfo('name');
            
            // Output logo with proper size and alt text
            echo '<a href="' . esc_url(home_url('/')) . '" rel="home" class="site-logo-link">';
            echo '<img src="' . esc_url($logo_url) . '" width="' . esc_attr($logo_width) . '" height="' . esc_attr($logo_height) . '" alt="' . esc_attr($alt_text) . '" class="custom-logo">';
            echo '</a>';
        } else {
            the_custom_logo(); // Fallback to standard function
        }
    } else {
        // Display site title if no logo
        // Get the site name
        $site_name = get_bloginfo('name');
        // Check if site name contains "Clips" to create two-tone effect
        if (strpos($site_name, 'Clips') !== false) {
            $name_parts = explode('Clips', $site_name);
            $first_part = $name_parts[0];
            echo '<h1 class="site-title">';
            echo '<a href="' . esc_url(home_url('/')) . '" rel="home" class="site-logo-link">';
            echo '<span class="site-title-main">' . esc_html($first_part) . '</span>';
            echo '<span class="site-title-accent">Clips</span>';
            echo '</a>';
            echo '</h1>';
        } else {
            echo '<h1 class="site-title"><a href="' . esc_url(home_url('/')) . '" rel="home" class="site-logo-link">' . get_bloginfo('name') . '</a></h1>';
        }
        
        // Optionally show site description
        $description = get_bloginfo('description');
        if ($description) {
            echo '<p class="site-description">' . esc_html($description) . '</p>';
        }
    }
}

/**
 * Add site branding options to Customizer
 */
function customtube_branding_customizer($wp_customize) {
    // Add a section for additional branding options
    $wp_customize->add_section('customtube_branding', array(
        'title'       => __('Site Branding', 'customtube'),
        'description' => __('Customize site logo and branding options', 'customtube'),
        'priority'    => 20,
    ));
    
    // Add mobile logo option
    $wp_customize->add_setting('mobile_logo', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ));
    
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'mobile_logo', array(
        'label'       => __('Mobile Logo', 'customtube'),
        'description' => __('Upload a smaller logo optimized for mobile devices', 'customtube'),
        'section'     => 'customtube_branding',
        'settings'    => 'mobile_logo',
        'mime_type'   => 'image',
    )));
    
    // Add option to show/hide site title alongside logo
    $wp_customize->add_setting('show_site_title', array(
        'default'           => false,
        'sanitize_callback' => 'customtube_sanitize_checkbox',
    ));
    
    $wp_customize->add_control('show_site_title', array(
        'label'       => __('Show Site Title with Logo', 'customtube'),
        'description' => __('Display the site title alongside the logo', 'customtube'),
        'section'     => 'customtube_branding',
        'settings'    => 'show_site_title',
        'type'        => 'checkbox',
    ));
}
add_action('customize_register', 'customtube_branding_customizer');

/**
 * Sanitize checkbox values
 */
function customtube_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

/**
 * Display mobile logo if available
 */
function customtube_display_mobile_logo() {
    $mobile_logo_id = get_theme_mod('mobile_logo');
    
    if ($mobile_logo_id) {
        $mobile_logo = wp_get_attachment_image_src($mobile_logo_id, 'full');
        
        if ($mobile_logo) {
            $logo_url = $mobile_logo[0];
            $logo_width = $mobile_logo[1];
            $logo_height = $mobile_logo[2];
            $alt_text = get_post_meta($mobile_logo_id, '_wp_attachment_image_alt', true) ?: get_bloginfo('name');
            
            echo '<a href="' . esc_url(home_url('/')) . '" rel="home" class="mobile-logo-link">';
            echo '<img src="' . esc_url($logo_url) . '" width="' . esc_attr($logo_width) . '" height="' . esc_attr($logo_height) . '" alt="' . esc_attr($alt_text) . '" class="mobile-logo">';
            echo '</a>';
            
            return true;
        }
    }
    
    return false;
}
