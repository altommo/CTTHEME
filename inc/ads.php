<?php
/**
 * ENHANCED Ad System with Performance Optimizations & Bug Fixes
 * Addresses cache invalidation scope, transient bloat, security, and other gotchas
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// TEMPORARY DEBUG - Test if we can define a simple function
function customtube_ads_test_function() {
    return 'OPTIMIZED ADS SYSTEM LOADED';
}

// Log that we're starting to load
error_log('CustomTube: Starting to load optimized ads.php');

/**
 * Get cached ads for a specific zone with intelligent caching
 * 
 * @param string $zone Ad zone slug
 * @param int $limit Number of ads to retrieve
 * @param bool $force_refresh Force cache refresh
 * @return array Array of valid ad objects with all meta data pre-loaded
 */
function customtube_get_ads_by_zone_cached($zone, $limit = 1, $force_refresh = false) {
    $start_time = microtime(true); // Start performance timer

    // Create unique cache key including user context for targeting
    $user_context = customtube_get_user_context_hash();
    $cache_key = "customtube_ads_zone_{$zone}_{$limit}_{$user_context}";
    
    // Try to get from cache first
    if (!$force_refresh) {
        $cached_ads = get_transient($cache_key);
        if ($cached_ads !== false) {
            // Log cache hit if debug mode enabled
            customtube_log_debug("Cache HIT for zone: {$zone}, key: {$cache_key}");
            customtube_log_debug("Ad fetch time for zone {$zone}: " . (microtime(true) - $start_time) . "s (Cache HIT)");
            return $cached_ads;
        }
    }
    
    // Cache miss - fetch and process ads
    customtube_log_debug("Cache MISS for zone: {$zone}, fetching fresh ads");
    $valid_ads = customtube_fetch_and_process_ads($zone, $limit);
    
    // Basic error recovery: if no ads are found, log it.
    if (empty($valid_ads)) {
        customtube_log_debug("No valid ads found for zone: {$zone} after fetching.");
    }

    // Cache for 15 minutes (can be adjusted based on ad rotation needs)
    $cache_duration = apply_filters('customtube_ad_cache_duration', 15 * MINUTE_IN_SECONDS, $zone);
    
    // Validate cache duration to prevent security issues
    $cache_duration = absint($cache_duration);
    if ($cache_duration < 60) $cache_duration = 60; // Minimum 1 minute
    if ($cache_duration > DAY_IN_SECONDS) $cache_duration = DAY_IN_SECONDS; // Maximum 1 day
    
    // Use non-autoload transient to prevent wp_options bloat
    customtube_set_transient_no_autoload($cache_key, $valid_ads, $cache_duration);
    
    customtube_log_debug("Ad fetch time for zone {$zone}: " . (microtime(true) - $start_time) . "s (Cache MISS)");
    return $valid_ads;
}

/**
 * Set transient without autoloading to prevent wp_options bloat
 * 
 * @param string $transient Transient name
 * @param mixed $value Value to store
 * @param int $expiration Expiration time in seconds
 */
function customtube_set_transient_no_autoload($transient, $value, $expiration) {
    global $wpdb;
    
    $transient_timeout = '_transient_timeout_' . $transient;
    $transient_option = '_transient_' . $transient;
    
    $expiration_time = time() + $expiration;
    
    // Delete existing entries first
    $wpdb->delete($wpdb->options, array('option_name' => $transient_timeout));
    $wpdb->delete($wpdb->options, array('option_name' => $transient_option));
    
    // Insert new entries with autoload = 'no'
    $wpdb->insert(
        $wpdb->options,
        array(
            'option_name' => $transient_timeout,
            'option_value' => $expiration_time,
            'autoload' => 'no'
        )
    );
    
    $wpdb->insert(
        $wpdb->options,
        array(
            'option_name' => $transient_option,
            'option_value' => maybe_serialize($value),
            'autoload' => 'no'
        )
    );
}

/**
 * Fetch and process ads with optimized batch queries
 * 
 * @param string $zone Ad zone slug  
 * @param int $limit Number of ads to retrieve
 * @return array Processed ads with all metadata
 */
function customtube_fetch_and_process_ads($zone, $limit) {
    // Get current user targeting context
    $user_context = customtube_get_user_targeting_context();
    
    // Step 1: Build simplified WP_Query - REMOVED COMPLEX META QUERIES
    $args = array(
        'post_type'      => 'tube_ad',
        'posts_per_page' => $limit * 2, // Get a few extra to account for any filtering
        'post_status'    => 'publish',
        'orderby'        => 'rand',
        'tax_query'      => array(
            array(
                'taxonomy' => 'ad_zone',
                'field'    => 'slug',
                'terms'    => $zone,
            ),
        ),
        // SIMPLIFIED: Only check if ad status is active (if it exists)
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_ad_status',
                'value'   => 'active',
                'compare' => '='
            ),
            array(
                'key'     => '_ad_status',
                'compare' => 'NOT EXISTS'
            )
        )
    );
    
    // Log query for debugging if enabled
    customtube_log_debug("Ad query args for zone {$zone}:", $args);
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        return array();
    }
    
    // Step 2: Batch fetch ALL metadata for all ads in one go
    $ad_ids = wp_list_pluck($query->posts, 'ID');
    $all_meta = customtube_batch_get_post_meta($ad_ids);
    
    // Step 3: Process each ad with pre-loaded metadata
    $valid_ads = array();
    $processed_count = 0;
    
    foreach ($query->posts as $post) {
        if ($processed_count >= $limit) {
            break;
        }
        
        $ad_meta = isset($all_meta[$post->ID]) ? $all_meta[$post->ID] : array();
        
        // Final validation with pre-loaded meta data
        if (customtube_validate_ad_targeting($ad_meta, $user_context)) {
            // Add meta data to post object to avoid future queries
            $post->ad_meta = $ad_meta;
            $valid_ads[] = $post;
            $processed_count++;
        }
    }
    
    return $valid_ads;
}

/**
 * Fixed batch fetch all post meta with proper prepared statements
 * 
 * @param array $ad_ids Array of ad IDs
 * @return array Multi-dimensional array [ad_id][meta_key] = meta_value
 */
function customtube_batch_get_post_meta($ad_ids) {
    if (empty($ad_ids)) {
        return array();
    }
    
    global $wpdb;
    
    // Ensure all IDs are integers
    $ad_ids = array_map('absint', $ad_ids);
    $ad_ids = array_filter($ad_ids); // Remove any zeros
    
    if (empty($ad_ids)) {
        return array();
    }
    
    // Create proper placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($ad_ids), '%d'));
    
    // Proper prepared statement with separate parameters
    $query = $wpdb->prepare("
        SELECT post_id, meta_key, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE post_id IN ($placeholders)
        AND meta_key LIKE %s
    ", array_merge($ad_ids, ['_ad_%']));
    
    $results = $wpdb->get_results($query);
    
    // Organize results by post ID
    $organized_meta = array();
    foreach ($results as $row) {
        if (!isset($organized_meta[$row->post_id])) {
            $organized_meta[$row->post_id] = array();
        }
        
        // Unserialize if needed
        $value = maybe_unserialize($row->meta_value);
        $organized_meta[$row->post_id][$row->meta_key] = $value;
    }
    
    return $organized_meta;
}

/**
 * Enhanced user context hash with reduced collision probability
 * 
 * @return string Hash representing user's targeting context
 */
function customtube_get_user_context_hash() {
    $context = customtube_get_user_targeting_context();
    
    // Create more specific hash to reduce context variations
    $hash_data = array(
        'device' => $context['device'],
        'country' => $context['country'],
        'date' => $context['date'],
        // Round to nearest hour to reduce cache fragmentation
        'hour_block' => floor($context['timestamp'] / 3600)
    );
    
    return substr(md5(serialize($hash_data)), 0, 12); // Shorter hash
}

/**
 * Get user targeting context for ad filtering
 * 
 * @return array User context for targeting
 */
function customtube_get_user_targeting_context() {
    static $context = null;
    
    if ($context !== null) {
        return $context;
    }
    
    $context = array(
        'device' => customtube_detect_device_type(),
        'country' => customtube_get_user_country(),
        'date' => current_time('Y-m-d'),
        'timestamp' => current_time('timestamp')
    );
    
    return $context;
}

/**
 * Detect device type more accurately
 * 
 * @return string Device type: desktop|tablet|mobile
 */
function customtube_detect_device_type() {
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return 'desktop';
    }
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Tablet detection
    if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $user_agent)) {
        return 'tablet';
    }
    
    // Mobile detection  
    if (preg_match('/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/i', $user_agent)) {
        return 'mobile';
    }
    
    return 'desktop';
}

/**
 * Get user country code with caching
 * 
 * @return string 2-letter country code
 */
function customtube_get_user_country() {
    static $country = null;
    
    if ($country !== null) {
        return $country;
    }
    
    // Try Cloudflare first (most reliable)
    if (isset($_SERVER['HTTP_CF_IPCOUNTRY']) && $_SERVER['HTTP_CF_IPCOUNTRY'] !== 'XX') {
        $country = sanitize_text_field($_SERVER['HTTP_CF_IPCOUNTRY']);
        return $country;
    }
    
    // Get user IP
    $ip = customtube_get_user_ip();
    if (!$ip || $ip === '127.0.0.1') {
        $country = 'US'; // Default for localhost
        return $country;
    }
    
    // Try cached lookup
    $cache_key = 'customtube_user_country_' . md5($ip);
    $country = get_transient($cache_key);
    
    if ($country === false) {
        // Simple rate limiting example
        $rate_limit_key = 'geoip_calls_' . gmdate('YmdH'); // Per hour
        $current_calls = get_transient($rate_limit_key) ?: 0;
        if ($current_calls >= 100) { // 100 calls per hour limit
            customtube_log_debug("GeoIP API rate limit hit. Falling back to 'US'.");
            return 'US'; // Fallback
        }
        set_transient($rate_limit_key, $current_calls + 1, HOUR_IN_SECONDS);

        // Implement actual geo-IP lookup
        $country = customtube_geoip_lookup($ip);
        
        // Cache for 6 hours (IPs don't change location frequently)
        set_transient($cache_key, $country, 6 * HOUR_IN_SECONDS);
    }
    
    return $country;
}

/**
 * Get user's real IP address
 * 
 * @return string IP address
 */
function customtube_get_user_ip() {
    // Check various headers for real IP (common in proxy/CDN setups)
    $ip_headers = array(
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_CLIENT_IP',            // Proxy
        'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
        'HTTP_X_FORWARDED',          // Proxy
        'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
        'HTTP_FORWARDED_FOR',        // Proxy
        'HTTP_FORWARDED',            // Proxy
        'REMOTE_ADDR'                // Standard
    );
    
    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = sanitize_text_field($_SERVER[$header]);
            
            // Handle comma-separated IPs (X-Forwarded-For can have multiple)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            
            // Validate IP format
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Simple geo-IP lookup using free service
 * 
 * @param string $ip IP address
 * @return string Country code
 */
function customtube_geoip_lookup($ip) {
    // Use a free geo-IP service (consider ip-api.com, ipinfo.io, etc.)
    $api_url = "http://ip-api.com/json/{$ip}?fields=countryCode";
    
    $response = wp_remote_get($api_url, array(
        'timeout' => 3,
        'user-agent' => 'CustomTube-AdSystem/1.0'
    ));
    
    if (is_wp_error($response)) {
        return 'US'; // Default fallback
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['countryCode']) && strlen($data['countryCode']) === 2) {
        return strtoupper($data['countryCode']);
    }
    
    return 'US'; // Default fallback
}

/**
 * Simplified date range meta query (flattened structure)
 * 
 * @return array Flattened meta query elements for date filtering
 */
function customtube_get_simplified_date_meta_query() {
    $current_date = current_time('Y-m-d');
    
    return array(
        // Start date check (ad should be active)
        array(
            'relation' => 'OR',
            array(
                'key'     => '_ad_start_date',
                'value'   => $current_date,
                'compare' => '<=',
                'type'    => 'DATE'
            ),
            array(
                'key'     => '_ad_start_date',
                'compare' => 'NOT EXISTS'
            )
        ),
        // End date check (ad should not be expired)
        array(
            'relation' => 'OR',
            array(
                'key'     => '_ad_end_date',
                'value'   => $current_date,
                'compare' => '>=',
                'type'    => 'DATE'
            ),
            array(
                'key'     => '_ad_end_date',
                'compare' => 'NOT EXISTS'
            )
        )
    );
}

/**
 * Build device targeting meta query
 * 
 * @param string $device_type Current user's device type
 * @return array Meta query for device targeting
 */
function customtube_get_device_meta_query($device_type) {
    return array(
        'relation' => 'OR',
        // No device targeting set (targets all)
        array(
            'key'     => '_ad_device_targeting',
            'compare' => 'NOT EXISTS'
        ),
        // Empty device targeting (targets all)
        array(
            'key'     => '_ad_device_targeting',
            'value'   => '',
            'compare' => '='
        ),
        // Specific device targeting that includes current device
        array(
            'key'     => '_ad_device_targeting',
            'value'   => $device_type,
            'compare' => 'LIKE'
        )
    );
}

/**
 * Build limits meta query for impression/click caps
 * 
 * @return array Meta query for limits
 */
function customtube_get_limits_meta_query() {
    return array(
        'relation' => 'AND',
        // Either no impression limit OR current impressions below limit
        array(
            'relation' => 'OR',
            array(
                'key'     => '_ad_max_impressions',
                'value'   => 0,
                'compare' => '='
            ),
            array(
                'key'     => '_ad_max_impressions',
                'compare' => 'NOT EXISTS'
            ),
            // This is a simplified check - in practice you'd need a more complex query
            // or handle this in PHP after fetching
        ),
        // Either no click limit OR current clicks below limit  
        array(
            'relation' => 'OR',
            array(
                'key'     => '_ad_max_clicks',
                'value'   => 0,
                'compare' => '='
            ),
            array(
                'key'     => '_ad_max_clicks',
                'compare' => 'NOT EXISTS'
            )
        )
    );
}

/**
 * Validate ad targeting with pre-loaded metadata - SIMPLIFIED VERSION
 * 
 * @param array $ad_meta Pre-loaded ad metadata
 * @param array $user_context User targeting context
 * @return bool Whether ad should be displayed
 */
function customtube_validate_ad_targeting($ad_meta, $user_context) {
    // SIMPLIFIED VALIDATION - Only check essential fields
    
    // Status check (default to active if not set)
    $ad_status = $ad_meta['_ad_status'][0] ?? 'active';
    if ($ad_status !== 'active') {
        return false;
    }
    
    // REMOVED: Complex impression/click limit checks
    // REMOVED: Complex date range validation  
    // REMOVED: Complex device targeting validation
    // REMOVED: Complex geo targeting validation
    
    // Simple date check - only if dates are actually set
    $start_date = $ad_meta['_ad_start_date'][0] ?? '';
    $end_date = $ad_meta['_ad_end_date'][0] ?? '';
    $current_date = $user_context['date'];
    
    // Only check dates if they're actually set
    if (!empty($start_date) && $current_date < $start_date) {
        return false;
    }
    
    if (!empty($end_date) && $current_date > $end_date) {
        return false;
    }
    
    // If we get here, the ad passes validation
    return true;
}

/**
 * EMERGENCY: Replace complex ad function with ultra-simple version
 */
function customtube_display_ads_in_zone_optimized($zone, $limit = 1) {
    // ULTRA-SIMPLE: Just get ads and display them, no complex filtering
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
        )
    );
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        // Still output the container even if no ads, so JavaScript can find it
        echo '<div class="tube-ads-' . esc_attr($zone) . ' tube-ads-container empty-zone"><!-- No ads available for zone: ' . esc_attr($zone) . ' --></div>';
        wp_reset_postdata();
        return;
    }
    
    echo '<div class="tube-ads-' . esc_attr($zone) . ' tube-ads-container ultra-simple">';
    
    while ($query->have_posts()) {
        $query->the_post();
        $ad_id = get_the_ID();
        $ad_type = get_post_meta($ad_id, '_ad_type', true) ?: 'code';
        $ad_code = get_post_meta($ad_id, '_ad_code', true) ?: '';
        $ad_title = get_the_title();
        
        echo '<div class="tube-ad tube-ad-' . esc_attr($ad_type) . '" data-ad-id="' . esc_attr($ad_id) . '">';
        echo '<!-- Ad: ' . esc_html($ad_title) . ' -->';
        
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
 * Optimized ad display function using pre-loaded metadata
 * 
 * @param WP_Post $ad Ad post object with pre-loaded ad_meta
 * @param bool $echo Whether to echo output
 * @return string Ad HTML
 */
function customtube_display_ad_optimized($ad, $echo = true) {
    $ad_meta = $ad->ad_meta ?? array();
    
    $ad_type = $ad_meta['_ad_type'][0] ?? 'image';
    $ad_class = $ad_meta['_ad_class'][0] ?? '';
    $class = 'tube-ad tube-ad-' . $ad_type . ($ad_class ? ' ' . $ad_class : '');
    
    $html = '<div class="' . esc_attr($class) . '" data-ad-id="' . esc_attr($ad->ID) . '">';
    
    switch ($ad_type) {
        case 'image':
            $ad_image = $ad_meta['_ad_image'][0] ?? '';
            $ad_url = $ad_meta['_ad_url'][0] ?? '';
            $ad_width = $ad_meta['_ad_width'][0] ?? 'auto';
            $ad_height = $ad_meta['_ad_height'][0] ?? 'auto';
            
            $style = '';
            if ($ad_width) {
                $style .= 'width:' . esc_attr($ad_width) . ';';
            }
            if ($ad_height) {
                $style .= 'height:' . esc_attr($ad_height) . ';';
            }
            
            $html .= '<a href="' . esc_url($ad_url) . '" target="_blank" rel="noopener" class="tube-ad-link" data-ad-click="true">';
            $html .= '<img src="' . esc_url($ad_image) . '" alt="' . esc_attr($ad->post_title) . '" style="' . $style . '" class="tube-ad-image" loading="lazy" />';
            $html .= '</a>';
            break;
            
        case 'code':
            $ad_code = $ad_meta['_ad_code'][0] ?? '';
            $html .= $ad_code;
            break;
            
        case 'vast':
            $vast_url = $ad_meta['_vast_url'][0] ?? '';
            $skip_after = $ad_meta['_skip_after'][0] ?? 0;
            
            $html .= '<div class="tube-vast-container" data-vast-url="' . esc_url($vast_url) . '" data-skip-after="' . esc_attr($skip_after) . '">';
            $html .= '<div class="tube-vast-video-container">';
            $html .= '<video class="tube-vast-video" playsinline></video>';
            $html .= '<div class="tube-vast-controls">';
            $html .= '<div class="tube-vast-progress"><div class="tube-vast-progress-bar"></div></div>';
            $html .= '<button class="tube-vast-play-pause" aria-label="Play/Pause"></button>';
            $html .= '<div class="tube-vast-time">0:00 / 0:00</div>';
            $html .= '<button class="tube-vast-mute" aria-label="Mute/Unmute"></button>';
            $html .= '<div class="tube-vast-volume"><div class="tube-vast-volume-bar"></div></div>';
            $html .= '</div>';
            $html .= '<div class="tube-vast-skip" style="display:none;">Skip Ad in <span class="tube-vast-skip-countdown"></span></div>';
            $html .= '</div>';
            $html .= '</div>';
            break;
    }
    
    $html .= '</div>';
    
    if ($echo) {
        echo $html;
    }
    
    return $html;
}

/**
 * Cache invalidation when ads are saved/updated
 */
function customtube_clear_ad_cache_on_save($post_id) {
    if (get_post_type($post_id) !== 'tube_ad') {
        return;
    }
    
    // Get zones this ad belongs to
    $ad_zones = customtube_get_ad_zones($post_id);
    
    if (empty($ad_zones)) {
        // If no specific zones, clear all (safety fallback)
        customtube_clear_all_ad_zones_cache();
        return;
    }
    
    // Only clear caches for zones this ad actually belongs to
    foreach ($ad_zones as $zone) {
        customtube_clear_zone_cache($zone);
        customtube_log_debug("Cleared cache for zone: {$zone} due to ad {$post_id} update");
    }
    
    // Update the stored zones list for this ad
    customtube_update_ad_zones_cache($post_id, $ad_zones);
}
add_action('save_post', 'customtube_clear_ad_cache_on_save');

/**
 * Get zones that an ad belongs to
 * 
 * @param int $post_id Ad post ID
 * @return array Array of zone slugs
 */
function customtube_get_ad_zones($post_id) {
    $zones = wp_get_object_terms($post_id, 'ad_zone', array('fields' => 'slugs'));
    
    if (is_wp_error($zones)) {
        return array();
    }
    
    return (array) $zones;
}

/**
 * Store which zones an ad belongs to for efficient cache invalidation
 * 
 * @param int $post_id Ad post ID
 * @param array $zones Array of zone slugs
 */
function customtube_update_ad_zones_cache($post_id, $zones) {
    update_post_meta($post_id, '_cached_ad_zones', $zones);
}

/**
 * Clear all zone caches (fallback method)
 */
function customtube_clear_all_ad_zones_cache() {
    $zones = array('header', 'below-header', 'in-content', 'sidebar', 'footer', 'pre-roll', 'mid-roll', 'post-roll');
    
    foreach ($zones as $zone) {
        customtube_clear_zone_cache($zone);
    }
}

/**
 * Enhanced cache clearing with better SQL
 * 
 * @param string $zone Zone to clear cache for
 */
function customtube_clear_zone_cache($zone) {
    global $wpdb;
    
    $zone_escaped = $wpdb->esc_like("_transient_customtube_ads_zone_{$zone}_");
    
    // Delete transients and their timeouts in one operation
    $wpdb->query($wpdb->prepare("
        DELETE t1, t2 FROM {$wpdb->options} t1
        LEFT JOIN {$wpdb->options} t2 ON t2.option_name = REPLACE(t1.option_name, '_timeout', '')
        WHERE t1.option_name LIKE %s
        OR t1.option_name LIKE %s
    ", 
        $zone_escaped . '%',
        '_transient_timeout' . $zone_escaped . '%'
    ));
}

/**
 * Enhanced AJAX handler with proper security
 */
add_action('wp_ajax_customtube_clear_ad_cache', function() {
    // Create proper nonce for this action
    $nonce_action = 'customtube_clear_ad_cache';
    
    if (!wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', $nonce_action) || !current_user_can('manage_options')) {
        wp_die('Unauthorized', 'Error', array('response' => 403));
    }
    
    // Clear all ad caches
    customtube_clear_all_ad_zones_cache();
    
    // Add success notice
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p>Ad cache cleared successfully!</p></div>';
    });
    
    wp_safe_redirect(wp_get_referer() ?: admin_url());
    exit;
});

/**
 * Enhanced admin cache management with proper nonce
 */
function customtube_add_cache_admin_tools() {
    add_action('admin_bar_menu', function($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $nonce_url = wp_nonce_url(
            admin_url('admin-ajax.php?action=customtube_clear_ad_cache'), 
            'customtube_clear_ad_cache'
        );
        
        $wp_admin_bar->add_node(array(
            'id'    => 'customtube-clear-ad-cache',
            'title' => 'Clear Ad Cache',
            'href'  => $nonce_url
        ));
    }, 100);
}
add_action('wp_loaded', 'customtube_add_cache_admin_tools');

/**
 * Debug logging function
 * 
 * @param string $message Debug message
 * @param mixed $data Additional data to log
 */
function customtube_log_debug($message, $data = null) {
    // Only log if debug mode is enabled
    if (!defined('CUSTOMTUBE_DEBUG_ADS') || !CUSTOMTUBE_DEBUG_ADS) {
        return;
    }
    
    $log_message = '[CustomTube Ads] ' . $message;
    
    if ($data !== null) {
        $log_message .= ' Data: ' . print_r($data, true);
    }
    
    error_log($log_message);
}

/**
 * Add debug mode constant check
 */
if (!defined('CUSTOMTUBE_DEBUG_ADS')) {
    define('CUSTOMTUBE_DEBUG_ADS', WP_DEBUG && (WP_DEBUG_LOG || current_user_can('manage_options')));
}

// Rest of the original functions remain the same but with debug logging added...
// (customtube_get_user_targeting_context, customtube_detect_device_type, etc.)

/**
 * Memory usage optimization - Clean up expired transients periodically
 */
function customtube_cleanup_expired_ad_transients() {
    global $wpdb;
    
    // Clean up expired ad-related transients once daily
    $last_cleanup = get_option('customtube_last_transient_cleanup', 0);
    if (time() - $last_cleanup < DAY_IN_SECONDS) {
        return;
    }
    
    // Delete expired transients
    $wpdb->query("
        DELETE t1, t2 FROM {$wpdb->options} t1
        LEFT JOIN {$wpdb->options} t2 ON t2.option_name = REPLACE(t1.option_name, '_timeout', '')
        WHERE t1.option_name LIKE '_transient_timeout_customtube_ads_%'
        AND t1.option_value < UNIX_TIMESTAMP()
    ");
    
    update_option('customtube_last_transient_cleanup', time());
}
add_action('wp_loaded', 'customtube_cleanup_expired_ad_transients');

/**
 * TEMPORARY: Create test ads if none exist - FORCE CREATION
 */
add_action('init', function() {
    // Remove the admin check temporarily to force creation
    // if (!current_user_can('manage_options')) {
    //     return;
    // }
    
    // Only run once - RESET FOR TESTING
    // if (get_option('customtube_test_ads_created') === 'yes') {
    //     return;
    // }
    
    // Force reset for testing
    delete_option('customtube_test_ads_created');
    
    error_log('CustomTube: Checking for existing ads...');
    
    // Check if we have any ads
    $existing_ads = get_posts(array(
        'post_type' => 'tube_ad',
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    error_log('CustomTube: Found ' . count($existing_ads) . ' existing ads');
    
    if (empty($existing_ads)) {
        error_log('CustomTube: Creating test ads...');
        
        // Create test ads
        $test_ads = array(
            array(
                'title' => 'Test Header Ad',
                'zone' => 'header',
                'type' => 'image',
                'image' => 'https://via.placeholder.com/728x90/ff0000/ffffff?text=Header+Ad',
                'url' => 'https://example.com'
            ),
            array(
                'title' => 'Test Footer Ad',
                'zone' => 'footer', 
                'type' => 'image',
                'image' => 'https://via.placeholder.com/728x90/0000ff/ffffff?text=Footer+Ad',
                'url' => 'https://example.com'
            ),
            array(
                'title' => 'Test Sidebar Ad',
                'zone' => 'sidebar',
                'type' => 'image', 
                'image' => 'https://via.placeholder.com/300x250/00ff00/ffffff?text=Sidebar+Ad',
                'url' => 'https://example.com'
            )
        );
        
        foreach ($test_ads as $ad_data) {
            error_log('CustomTube: Creating ad: ' . $ad_data['title']);
            
            // Create the post
            $post_id = wp_insert_post(array(
                'post_title' => $ad_data['title'],
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'tube_ad'
            ));
            
            if ($post_id && !is_wp_error($post_id)) {
                error_log('CustomTube: Created post ID: ' . $post_id);
                
                // Add meta data
                update_post_meta($post_id, '_ad_status', 'active');
                update_post_meta($post_id, '_ad_type', $ad_data['type']);
                update_post_meta($post_id, '_ad_image', $ad_data['image']);
                update_post_meta($post_id, '_ad_url', $ad_data['url']);
                update_post_meta($post_id, '_ad_width', 'auto');
                update_post_meta($post_id, '_ad_height', 'auto');
                
                // Assign to zone (if taxonomy exists)
                if (taxonomy_exists('ad_zone')) {
                    error_log('CustomTube: ad_zone taxonomy exists');
                    
                    // Create the term if it doesn't exist
                    $term = term_exists($ad_data['zone'], 'ad_zone');
                    if (!$term) {
                        $term = wp_insert_term($ad_data['zone'], 'ad_zone');
                        error_log('CustomTube: Created term: ' . $ad_data['zone']);
                    }
                    
                    if (!is_wp_error($term)) {
                        $result = wp_set_object_terms($post_id, $ad_data['zone'], 'ad_zone');
                        error_log('CustomTube: Assigned term result: ' . print_r($result, true));
                    }
                } else {
                    error_log('CustomTube: ad_zone taxonomy does NOT exist');
                }
                
                error_log("CustomTube: Successfully created test ad: {$ad_data['title']} (ID: $post_id)");
            } else {
                error_log('CustomTube: Failed to create post: ' . print_r($post_id, true));
            }
        }
        
        // Mark as created
        update_option('customtube_test_ads_created', 'yes');
        
        // Clear ad cache
        if (function_exists('customtube_clear_all_ad_zones_cache')) {
            customtube_clear_all_ad_zones_cache();
        }
        
        error_log("CustomTube: Test ads creation process completed");
    }
});

/**
 * Hook Integration - Connect optimized ad system to theme
 * RESTORED with single container per zone
 */
function customtube_integrate_ad_hooks() {
    // Create the main compatibility function
    if (!function_exists('customtube_display_ad')) {
        /**
         * Theme compatibility function - bridges old and new ad systems
         * 
         * @param string $zone Ad zone slug
         * @param int $limit Number of ads to display
         */
        function customtube_display_ad($zone, $limit = 1) {
            // Use our optimized system
            customtube_display_ads_in_zone_optimized($zone, $limit);
        }
    }
    
    // Hook into theme-specific actions (these are called by your theme)
    add_action('customtube_before_site_inner', function() {
        customtube_display_ads_in_zone_optimized('below-header');
    }, 5);
    
    add_action('customtube_after_header', function() {
        customtube_display_ads_in_zone_optimized('header');
    }, 10);
    
    add_action('customtube_sidebar', function() {
        customtube_display_ads_in_zone_optimized('sidebar', 3);
    }, 10);
    
    add_action('customtube_in_content', function() {
        customtube_display_ads_in_zone_optimized('in-content');
    }, 10);
    
    add_action('customtube_footer', function() {
        customtube_display_ads_in_zone_optimized('footer');
    }, 10);
}
add_action('init', 'customtube_integrate_ad_hooks');

/**
 * FORCE DISPLAY ALL AD ZONES - For Testing/Debug - DISABLED
 */
function customtube_force_display_all_ads() {
    // DISABLED to prevent duplicate ads
    return;
}
add_action('init', 'customtube_force_display_all_ads');

/**
 * SIMPLE AD DISPLAY - Bypass all complex filtering for testing
 */
function customtube_simple_ad_display($zone, $limit = 1) {
    // Simple query without complex meta queries
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
        )
    );
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        echo '<div class="tube-ads-' . esc_attr($zone) . ' tube-ads-container simple-empty"><!-- Simple: No ads found for zone: ' . esc_attr($zone) . ' --></div>';
        return;
    }
    
    echo '<div class="tube-ads-' . esc_attr($zone) . ' tube-ads-container simple-display">';
    
    while ($query->have_posts()) {
        $query->the_post();
        $ad_id = get_the_ID();
        $ad_type = get_post_meta($ad_id, '_ad_type', true) ?: 'code';
        $ad_code = get_post_meta($ad_id, '_ad_code', true) ?: '';
        $ad_title = get_the_title();
        
        echo '<div class="tube-ad tube-ad-' . esc_attr($ad_type) . '" data-ad-id="' . esc_attr($ad_id) . '">';
        echo '<!-- Ad: ' . esc_html($ad_title) . ' -->';
        
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
 * Test simple ad display - DISABLED
 */
function customtube_test_simple_ads() {
    // DISABLED to prevent duplicate ads
    return;
}
add_action('init', 'customtube_test_simple_ads');

?>
