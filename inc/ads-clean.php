<?php
/**
 * Clean Ad System - Production Version
 * Simplified ad display without debug functions
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Ultra-simple ad display function - Production version
 */
function customtube_display_ads_in_zone_optimized($zone, $limit = 1) {
    // Simple query to get ads for the zone
    $args = array(
        'post_type' => 'tube_ad',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'ad_zone',
                'field' => 'slug',
                'terms' => $zone
            )
        ),
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => '_ad_status',
                'value' => 'active',
                'compare' => '='
            ),
            array(
                'key' => '_ad_status',
                'compare' => 'NOT EXISTS'
            )
        )
    );
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        echo '<div class="tube-ads-' . esc_attr($zone) . ' tube-ads-container empty-zone"><!-- No ads for zone: ' . esc_attr($zone) . ' --></div>';
        wp_reset_postdata();
        return;
    }
    
    echo '<div class="tube-ads-' . esc_attr($zone) . ' tube-ads-container">';
    
    while ($query->have_posts()) {
        $query->the_post();
        $ad_id = get_the_ID();
        $ad_type = get_post_meta($ad_id, '_ad_type', true) ?: 'code';
        $ad_code = get_post_meta($ad_id, '_ad_code', true) ?: '';
        $ad_title = get_the_title();
        
        echo '<div class="tube-ad tube-ad-' . esc_attr($ad_type) . '" data-ad-id="' . esc_attr($ad_id) . '">';
        
        if ($ad_type === 'code' && $ad_code) {
            echo $ad_code;
        } else {
            echo '<p>Ad: ' . esc_html($ad_title) . ' (Type: ' . esc_html($ad_type) . ')</p>';
        }
        
        echo '</div>';
    }
    
    echo '</div>';
    wp_reset_postdata();
}

/**
 * Theme compatibility function
 */
if (!function_exists('customtube_display_ad')) {
    function customtube_display_ad($zone, $limit = 1) {
        customtube_display_ads_in_zone_optimized($zone, $limit);
    }
}

/**
 * Hook Integration - Connect ad system to theme
 */
function customtube_integrate_ad_hooks() {
    // Hook into theme-specific actions
    add_action('customtube_before_site_inner', function() {
        customtube_display_ads_in_zone_optimized('below-header');
    }, 5);
    
    add_action('customtube_after_header', function() {
        customtube_display_ads_in_zone_optimized('header');
    }, 10);
    
    add_action('customtube_sidebar', function() {
        customtube_display_ads_in_zone_optimized('sidebar', 3); // Show up to 3 sidebar ads
    }, 10);
    
    add_action('customtube_in_content', function() {
        customtube_display_ads_in_zone_optimized('in-content');
    }, 10);
    
    add_action('customtube_footer', function() {
        customtube_display_ads_in_zone_optimized('footer');
    }, 10);
}
add_action('init', 'customtube_integrate_ad_hooks');

?>
