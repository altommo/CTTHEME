<?php
/**
 * CustomTube Theme Functions
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add plugin dependency notice
add_action('admin_notices', function() {
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    if (!is_plugin_active('customtube-video-importer/customtube-video-importer.php')) {
        echo '<div class="notice notice-warning">';
        echo '<p><strong>CustomTube Notice:</strong> For video import functionality, please install and activate the <a href="https://example.com/customtube-video-importer.zip">CustomTube Video Importer</a> plugin.</p>';
        echo '</div>';
    }
});

// Define theme constants
define('CUSTOMTUBE_VERSION', '1.0.1'); // Updated version for new CSS architecture
define('CUSTOMTUBE_DIR', get_template_directory());
define('CUSTOMTUBE_URI', get_template_directory_uri());

// Include required files - Modular architecture
require_once CUSTOMTUBE_DIR . '/inc/post-types.php';
require_once CUSTOMTUBE_DIR . '/inc/taxonomies.php';
require_once CUSTOMTUBE_DIR . '/inc/branding.php';
require_once CUSTOMTUBE_DIR . '/inc/permalinks.php';
require_once CUSTOMTUBE_DIR . '/inc/ads.php';
require_once CUSTOMTUBE_DIR . '/inc/ad-meta-boxes.php';
require_once CUSTOMTUBE_DIR . '/inc/seo.php';

// Core theme modules
require_once CUSTOMTUBE_DIR . '/inc/ajax-filter-handler.php';
require_once CUSTOMTUBE_DIR . '/inc/ajax-load-more.php';
require_once CUSTOMTUBE_DIR . '/inc/modules/recommendation-engine.php'; // New: Include recommendation engine

// Admin-only includes
if (is_admin()) {
    require_once CUSTOMTUBE_DIR . '/inc/admin/settings.php';
    require_once CUSTOMTUBE_DIR . '/inc/admin/dashboard.php';
    require_once CUSTOMTUBE_DIR . '/inc/admin/tools.php';
}

// Include template functions
require_once CUSTOMTUBE_DIR . '/inc/template-functions.php';

// Theme Setup
function customtube_theme_setup() {
    // Enable translations
    load_theme_textdomain( 'customtube', CUSTOMTUBE_DIR . '/languages' );
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'customtube'),
        'footer'  => esc_html__('Footer Menu', 'customtube'),
    ));

    add_image_size('video-thumbnail', 320, 180, true);
    add_image_size('video-medium', 640, 360, true);
    add_image_size('video-large', 1280, 720, true);
}
add_action('after_setup_theme', 'customtube_theme_setup');

/**
 * Enqueue modular CSS & JS for CustomTube theme.
 */
function customtube_enqueue_assets() {
    // CSS
    wp_enqueue_style(
        'customtube-main',
        get_template_directory_uri() . '/dist/css/main.css',
        array(),
        filemtime( get_template_directory() . '/dist/css/main.css' )
    );

    wp_enqueue_style(
        'customtube-style',
        get_stylesheet_uri(),
        array( 'customtube-main' ),
        CUSTOMTUBE_VERSION
    );

    // JavaScript
    wp_enqueue_script(
        'customtube-main',
        get_template_directory_uri() . '/dist/js/customtube.min.js',
        array( 'jquery' ),
        filemtime( get_template_directory() . '/dist/js/customtube.min.js' ),
        true
    );

    wp_localize_script(
        'customtube-main',
        'customtubeData',
        array(
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'customtube-ajax-nonce' ),
            'theme_version' => CUSTOMTUBE_VERSION,
        )
    );
    
    // Suppress third-party ad console logs in production
    if (!WP_DEBUG) {
        wp_add_inline_script(
            'customtube-main',
            '
            // Override console.log for ad networks to reduce noise
            const originalLog = console.log;
            console.log = function(...args) {
                const message = args.join(" ");
                // Block known ad network console spam
                if (message.includes("magsrv.com") || 
                    message.includes("Request #") ||
                    message.includes("Placement #") ||
                    message.includes("Zones Batch Size")) {
                    return; // Suppress these logs
                }
                originalLog.apply(console, args);
            };
            ',
            'before'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'customtube_enqueue_assets', 20 );

// Register widget areas
function customtube_widgets_init() {
    register_sidebar(array(
        'name'          => esc_html__('Sidebar', 'customtube'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'customtube'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Footer Widgets', 'customtube'),
        'id'            => 'footer-widgets',
        'description'   => esc_html__('Add footer widgets here.', 'customtube'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'customtube_widgets_init');

// AJAX function to update view count
function customtube_update_view_count() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ($post_id > 0) {
        $view_count = get_post_meta($post_id, 'video_views', true);
        $view_count = $view_count ? intval($view_count) + 1 : 1;
        update_post_meta($post_id, 'video_views', $view_count);
        wp_send_json_success(array('view_count' => $view_count));
    } else {
        wp_send_json_error('Invalid post ID');
    }
}
add_action('wp_ajax_customtube_update_view_count', 'customtube_update_view_count');
add_action('wp_ajax_nopriv_customtube_update_view_count', 'customtube_update_view_count');

/**
 * Admin-specific scripts and styles enqueuing
 * Load admin styles and the new modular admin JavaScript
 */
function customtube_admin_enqueue_scripts($hook) {
    // Load admin styles on relevant pages
    $admin_pages = array(
        'post.php',
        'post-new.php',
        'edit.php',
        'toplevel_page_customtube-settings',
        'customtube_page_customtube-tools'
    );
    
    // Load CustomTube admin styles and scripts on specific pages
    if (in_array($hook, $admin_pages) || strpos($hook, 'customtube') !== false) {
        // Admin CSS
        wp_enqueue_style(
            'customtube-admin', 
            CUSTOMTUBE_URI . '/assets/css/admin/admin.css', 
            array(), 
            CUSTOMTUBE_VERSION
        );
        
        // Admin JavaScript (if file exists)
        $admin_js_path = CUSTOMTUBE_DIR . '/dist/js/admin.min.js';
        if (file_exists($admin_js_path)) {
            wp_enqueue_script(
                'customtube-admin', 
                CUSTOMTUBE_URI . '/dist/js/admin.min.js', 
                array('jquery'), 
                filemtime($admin_js_path),
                true
            );
            
            // Admin JavaScript configuration
            wp_localize_script('customtube-admin', 'customtubeAdminData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('customtube-ajax-nonce'),
                'debug'   => defined('WP_DEBUG') && WP_DEBUG ? true : false,
                'admin_url' => admin_url(),
                'theme_uri' => CUSTOMTUBE_URI
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'customtube_admin_enqueue_scripts');

// Body classes
function customtube_body_classes($classes) {
    $dark_mode = isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] : 'dark'; // Default to dark mode
    if ($dark_mode === 'dark') {
        $classes[] = 'dark-mode';
    }
    
    // Add CSS architecture class for styling hooks
    $classes[] = 'customtube-modular-css';
    
    // Add PH navigation class for proper body padding
    $classes[] = 'ph-nav-enabled';
    
    return $classes;
}
add_filter('body_class', 'customtube_body_classes');

/**
 * Remove the “failsafe” CSS once and for all
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_style( 'mobile-menu-failsafe' );
    wp_deregister_style( 'mobile-menu-failsafe' );
}, 20 );

// Age verification functionality is now handled directly in template-parts/age-verification.php
// require_once CUSTOMTUBE_DIR . '/inc/admin/age-verification.php'; // DISABLED - moved to .disabled

// Custom rewrite rules
function customtube_add_custom_rewrite_rules() {
    add_rewrite_rule('video/([0-9]+)/([^/]+)/?$', 'index.php?post_type=video&p=$matches[1]', 'top');
    add_rewrite_rule('video-sitemap\.xml$', 'index.php?customtube_video_sitemap=1', 'top');
    add_rewrite_rule('performers/?$', 'index.php?pagename=performers', 'top');
}
add_action('init', 'customtube_add_custom_rewrite_rules');

function customtube_add_query_vars($vars) {
    $vars[] = 'customtube_video_sitemap';
    return $vars;
}
add_filter('query_vars', 'customtube_add_query_vars');

function customtube_rewrite_flush() {
    customtube_add_custom_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'customtube_rewrite_flush');

// Import functionality stubs that forward to plugin functions
function customtube_fix_malformed_embeds() {
    if (function_exists('customtube_importer_fix_malformed_embeds')) {
        return customtube_importer_fix_malformed_embeds();
    }
    wp_send_json_error(array('message' => 'This feature requires the CustomTube Video Importer plugin.'));
}

function customtube_fix_embeds_ajax() {
    if (function_exists('customtube_importer_fix_embeds_ajax')) {
        customtube_importer_fix_embeds_ajax();
        return;
    }
    wp_send_json_error(array('message' => 'This feature requires the CustomTube Video Importer plugin.'));
}
add_action('wp_ajax_customtube_fix_embeds', 'customtube_fix_embeds_ajax');

// ==========================================================================
// AJAX HANDLERS FOR NEW PAGE FUNCTIONALITY
// ==========================================================================


// AJAX handler for getting trending videos
function customtube_ajax_get_trending_videos() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'today';
    $per_page = 20;
    
    // Calculate date range based on period
    $date_query = array();
    switch ($period) {
        case 'today':
            $date_query = array(
                'after' => '1 day ago'
            );
            break;
        case 'week':
            $date_query = array(
                'after' => '1 week ago'
            );
            break;
        case 'month':
            $date_query = array(
                'after' => '1 month ago'
            );
            break;
        case 'all':
        default:
            // No date restriction
            break;
    }
    
    // Set up query arguments
    $args = array(
        'post_type' => 'video',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'meta_query' => array(
            array(
                'key' => 'video_file',
                'compare' => 'EXISTS'
            )
        ),
        'orderby' => array(
            'meta_value_num' => 'DESC',
            'date' => 'DESC'
        ),
        'meta_key' => 'video_views'
    );
    
    if (!empty($date_query)) {
        $args['date_query'] = array($date_query);
    }
    
    $query = new WP_Query($args);
    $videos = array();
    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    $user_likes = $user_id ? get_user_meta($user_id, 'liked_videos', true) : array();
    if (!is_array($user_likes)) {
        $user_likes = array();
    }
    
    if ($query->have_posts()) {
        $rank = ($page - 1) * $per_page;
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $views = intval(get_post_meta($post_id, 'video_views', true));
            $likes = intval(get_post_meta($post_id, 'video_likes', true));
            
            // Calculate trending score (simplified)
            $trend_score = $views + ($likes * 10);
            
            // Calculate growth percentage (mock data for demo)
            $growth = rand(5, 150);
            
            $videos[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'url' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url($post_id, 'video-thumbnail') ?: CUSTOMTUBE_URI . '/assets/images/default-video.jpg',
                'duration' => get_post_meta($post_id, 'video_duration', true) ?: '0:00',
                'views' => number_format($views),
                'likes' => $likes,
                'growth' => $growth,
                'trend_score' => number_format($trend_score),
                'trending_time' => human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago',
                'liked' => in_array($post_id, $user_likes)
            );
            $rank++;
        }
    }
    
    wp_reset_postdata();
    
    // Generate mock stats
    $stats = array(
        'total_views' => number_format(rand(1000000, 5000000)),
        'trending_count' => count($videos),
        'hot_categories' => rand(5, 15),
        'growth_rate' => rand(10, 45)
    );
    
    wp_send_json_success(array(
        'videos' => $videos,
        'total' => $query->found_posts,
        'has_more' => $query->max_num_pages > $page,
        'stats' => $stats
    ));
}
add_action('wp_ajax_get_trending_videos', 'customtube_ajax_get_trending_videos');
add_action('wp_ajax_nopriv_get_trending_videos', 'customtube_ajax_get_trending_videos');

// AJAX handler for getting short videos
function customtube_ajax_get_short_videos() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'newest';
    $duration = isset($_POST['duration']) ? sanitize_text_field($_POST['duration']) : 'all';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $per_page = 20;
    
    // Set up query arguments
    $args = array(
        'post_type' => 'video',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'meta_query' => array(
            array(
                'key' => 'video_file',
                'compare' => 'EXISTS'
            )
        )
    );
    
    // Handle duration filtering
    if ($duration !== 'all') {
        $duration_seconds = intval($duration);
        $args['meta_query'][] = array(
            'key' => 'video_duration_seconds',
            'value' => $duration_seconds,
            'compare' => '<=',
            'type' => 'NUMERIC'
        );
    } else {
        // For "all short videos" we'll limit to under 5 minutes (300 seconds)
        $args['meta_query'][] = array(
            'key' => 'video_duration_seconds',
            'value' => 300,
            'compare' => '<=',
            'type' => 'NUMERIC'
        );
    }
    
    // Handle category filtering
    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'video_category',
                'field' => 'slug',
                'terms' => $category
            )
        );
    }
    
    // Handle sorting
    switch ($sort) {
        case 'popular':
            $args['meta_key'] = 'video_likes';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'views':
            $args['meta_key'] = 'video_views';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'shortest':
            $args['meta_key'] = 'video_duration_seconds';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'random':
            $args['orderby'] = 'rand';
            break;
        default: // newest
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }
    
    $query = new WP_Query($args);
    $videos = array();
    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    $user_likes = $user_id ? get_user_meta($user_id, 'liked_videos', true) : array();
    if (!is_array($user_likes)) {
        $user_likes = array();
    }
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            // Get video category
            $categories = get_the_terms($post_id, 'video_category');
            $category_name = '';
            if ($categories && !is_wp_error($categories)) {
                $category_name = $categories[0]->name;
            }
            
            $videos[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'url' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url($post_id, 'video-thumbnail') ?: CUSTOMTUBE_URI . '/assets/images/default-video.jpg',
                'duration' => get_post_meta($post_id, 'video_duration', true) ?: '0:00',
                'views' => number_format(intval(get_post_meta($post_id, 'video_views', true))),
                'likes' => intval(get_post_meta($post_id, 'video_likes', true)),
                'date' => get_the_date('M j, Y'),
                'category' => $category_name,
                'liked' => in_array($post_id, $user_likes)
            );
        }
    }
    
    wp_reset_postdata();
    
    wp_send_json_success(array(
        'videos' => $videos,
        'total' => $query->found_posts,
        'has_more' => $query->max_num_pages > $page
    ));
}
add_action('wp_ajax_get_short_videos', 'customtube_ajax_get_short_videos');
add_action('wp_ajax_nopriv_get_short_videos', 'customtube_ajax_get_short_videos');

// AJAX handler for toggling video likes
function customtube_toggle_like() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = get_current_user_id();
    
    if (!$post_id || !get_post($post_id)) {
        wp_send_json_error('Invalid post ID');
    }
    
    // Get current user likes
    $user_likes = get_user_meta($user_id, 'liked_videos', true);
    if (!is_array($user_likes)) {
        $user_likes = array();
    }
    
    // Get current video likes count
    $video_likes = intval(get_post_meta($post_id, 'video_likes', true));
    
    $is_liked = in_array($post_id, $user_likes);
    
    if ($is_liked) {
        // Unlike the video
        $user_likes = array_diff($user_likes, array($post_id));
        $video_likes = max(0, $video_likes - 1);
        $liked = false;
    } else {
        // Like the video
        $user_likes[] = $post_id;
        $video_likes++;
        $liked = true;
    }
    
    // Update user likes
    update_user_meta($user_id, 'liked_videos', array_values($user_likes));
    
    // Update video likes count
    update_post_meta($post_id, 'video_likes', $video_likes);
    
    wp_send_json_success(array(
        'liked' => $liked,
        'likes_count' => $video_likes
    ));
}
add_action('wp_ajax_customtube_toggle_like', 'customtube_toggle_like');

// Helper function to convert duration string to seconds
function customtube_duration_to_seconds($duration) {
    if (empty($duration) || $duration === '0:00') {
        return 0;
    }
    
    $parts = explode(':', $duration);
    $seconds = 0;
    
    if (count($parts) == 2) {
        // MM:SS format
        $seconds = (intval($parts[0]) * 60) + intval($parts[1]);
    } elseif (count($parts) == 3) {
        // HH:MM:SS format
        $seconds = (intval($parts[0]) * 3600) + (intval($parts[1]) * 60) + intval($parts[2]);
    }
    
    return $seconds;
}

// Hook to save duration in seconds when video is saved
function customtube_save_video_duration_seconds($post_id) {
    if (get_post_type($post_id) !== 'video') {
        return;
    }
    
    $duration = get_post_meta($post_id, 'video_duration', true);
    if (!empty($duration)) {
        $duration_seconds = customtube_duration_to_seconds($duration);
        update_post_meta($post_id, 'video_duration_seconds', $duration_seconds);
    }
}
add_action('save_post', 'customtube_save_video_duration_seconds');

// AJAX handler for custom user login
function customtube_custom_user_login() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'custom_login_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    $username = sanitize_text_field($_POST['log']);
    $password = $_POST['pwd'];
    $remember = isset($_POST['rememberme']) && $_POST['rememberme'] == '1';
    
    if (empty($username) || empty($password)) {
        wp_send_json_error(array('message' => 'Please enter both username and password'));
    }
    
    $user = wp_authenticate($username, $password);
    
    if (is_wp_error($user)) {
        wp_send_json_error(array('message' => $user->get_error_message()));
    }
    
    wp_clear_auth_cookie();
    wp_set_auth_cookie($user->ID, $remember, is_ssl());
    wp_set_current_user($user->ID);
    
    wp_send_json_success(array(
        'message' => 'Login successful! Welcome back.',
        'redirect_url' => home_url()
    ));
}
add_action('wp_ajax_custom_user_login', 'customtube_custom_user_login');
add_action('wp_ajax_nopriv_custom_user_login', 'customtube_custom_user_login');

// AJAX handler for custom user registration
function customtube_custom_user_register() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'custom_register_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    $username = sanitize_text_field($_POST['user_login']);
    $email = sanitize_email($_POST['user_email']);
    $password = $_POST['user_pass'];
    $birthdate = sanitize_text_field($_POST['user_birthdate']);
    $terms_agreement = isset($_POST['terms_agreement']) && $_POST['terms_agreement'] == '1';
    $age_confirmation = isset($_POST['age_confirmation']) && $_POST['age_confirmation'] == '1';
    $marketing_optin = isset($_POST['marketing_optin']) && $_POST['marketing_optin'] == '1';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error(array('message' => 'Please fill in all required fields'));
    }
    
    if (!$terms_agreement) {
        wp_send_json_error(array('message' => 'You must agree to the Terms of Service'));
    }
    
    if (!$age_confirmation) {
        wp_send_json_error(array('message' => 'You must confirm you are 18 years or older'));
    }
    
    // Check age
    if (!empty($birthdate)) {
        $birth_timestamp = strtotime($birthdate);
        $age = floor((time() - $birth_timestamp) / 31556926); // seconds in a year
        if ($age < 18) {
            wp_send_json_error(array('message' => 'You must be 18 years or older to register'));
        }
    }
    
    // Check if username or email already exists
    if (username_exists($username)) {
        wp_send_json_error(array('message' => 'Username already exists'));
    }
    
    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Email address already registered'));
    }
    
    // Create user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error(array('message' => $user_id->get_error_message()));
    }
    
    // Save additional user meta
    if (!empty($birthdate)) {
        update_user_meta($user_id, 'birthdate', $birthdate);
    }
    
    update_user_meta($user_id, 'marketing_optin', $marketing_optin ? '1' : '0');
    update_user_meta($user_id, 'registration_date', current_time('mysql'));
    
    wp_send_json_success(array(
        'message' => 'Registration successful! Please sign in to continue.',
        'redirect_url' => home_url('/login')
    ));
}
add_action('wp_ajax_custom_user_register', 'customtube_custom_user_register');
add_action('wp_ajax_nopriv_custom_user_register', 'customtube_custom_user_register');

/**
 * AJAX handler for tracking user behavior (clicks, views, etc.)
 */
function customtube_track_user_behavior() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Track User Behavior AJAX Error: Invalid nonce. Nonce received: ' . ($_POST['nonce'] ?? 'N/A'));
        }
        wp_send_json_error('Invalid nonce');
    }

    $video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;
    $event_type = isset($_POST['event_type']) ? sanitize_text_field($_POST['event_type']) : '';
    $user_id = get_current_user_id(); // 0 if not logged in

    // Validate inputs
    if ($video_id <= 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Track User Behavior AJAX Error: Invalid video ID: ' . $video_id);
        }
        wp_send_json_error('Invalid video ID.');
    }
    if (empty($event_type)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Track User Behavior AJAX Error: Empty event type.');
        }
        wp_send_json_error('Empty event type.');
    }
    // Optionally, validate event_type against a whitelist
    $allowed_event_types = ['click', 'view', 'watch_complete', 'like', 'share'];
    if (!in_array($event_type, $allowed_event_types)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Track User Behavior AJAX Error: Disallowed event type: ' . $event_type);
        }
        wp_send_json_error('Disallowed event type.');
    }

    // Log the behavior (for demonstration purposes)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("User ID: {$user_id}, Video ID: {$video_id}, Event Type: {$event_type}");
    }

    // In a real application, you would store this data in a custom table
    // or send it to a dedicated analytics/recommendation service.
    // For now, we'll just store a simple history in user meta.
    if ($user_id > 0) {
        $watched_history = get_user_meta($user_id, 'customtube_watched_history', true);
        if (!is_array($watched_history)) {
            $watched_history = array();
        }

        // Add the video to the history, ensuring no duplicates and keeping it recent
        $watched_history = array_filter($watched_history, function($item) use ($video_id) {
            return ($item['video_id'] ?? 0) !== $video_id; // Use null coalescing for safety
        });
        array_unshift($watched_history, ['video_id' => $video_id, 'timestamp' => time(), 'event' => $event_type]);

        // Keep history to a reasonable size (e.g., last 50 videos)
        $watched_history = array_slice($watched_history, 0, 50);
        update_user_meta($user_id, 'customtube_watched_history', $watched_history);
    }

    wp_send_json_success('Behavior tracked.');
}
add_action('wp_ajax_customtube_track_user_behavior', 'customtube_track_user_behavior');
add_action('wp_ajax_nopriv_customtube_track_user_behavior', 'customtube_track_user_behavior');



// Fix REST API issues for Site Kit
add_filter('rest_authentication_errors', function($result) {
    if (!empty($result)) {
        return $result;
    }
    if (!is_user_logged_in()) {
        return new WP_Error('rest_not_logged_in', 'You must be logged in to access the REST API.', array('status' => 401));
    }
    return $result;
});

// Ensure Site Kit compatibility
add_action('init', function() {
    // Allow Site Kit to function properly
    if (class_exists('Google\Site_Kit\Plugin')) {
        remove_action('rest_api_init', 'wp_oembed_register_route');
        add_action('rest_api_init', 'wp_oembed_register_route', 5);
    }
});

// Register custom pages
function customtube_register_pages() {
    $pages = array(
        'privacy-policy' => 'Privacy Policy',
        'terms-of-service' => 'Terms of Service',
        'dmca' => 'DMCA',
        '2257-compliance' => '2257 Compliance'
    );

    foreach ($pages as $slug => $title) {
        $page = get_page_by_path($slug);

        if (!$page) {
            $page_id = wp_insert_post(array(
                'post_title' => $title,
                'post_name' => $slug,
                'post_status' => 'publish',
                'post_content' => "[page_{$slug}]"
            ));

            if (!$page_id || is_wp_error($page_id)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Failed to create page: {$title}");
                }
            }
        }
    }
}
add_action('init', 'customtube_register_pages');

/**
 * Register custom page templates from subdirectories.
 *
 * This function is crucial for WordPress to recognize page templates
 * located within subdirectories like `template-parts/pages/`.
 *
 * @param array $templates Existing page templates.
 * @return array Modified array of page templates.
 */
function customtube_register_page_templates($templates) {
    $theme_dir = trailingslashit(get_template_directory());
    $template_path = 'template-parts/pages/'; // Path to your custom templates

    // Get all PHP files in the specified directory
    $files = glob($theme_dir . $template_path . '*.php');

    if ($files) {
        foreach ($files as $file) {
            $filename = basename($file);
            // Extract template name from the file header
            $file_headers = get_file_data($file, array('Template Name' => 'Template Name'));
            if (!empty($file_headers['Template Name'])) {
                $templates[$template_path . $filename] = $file_headers['Template Name'];
            }
        }
    }

    return $templates;
}
add_filter('theme_page_templates', 'customtube_register_page_templates');

// Ensure the theme loads the correct page templates

/**
 * Get trending videos
 *
 * @param int $count Number of videos to retrieve
 * @return WP_Query
 */
function customtube_get_trending_videos( $count = 10 ) {
    $args = array(
        'post_type'      => 'video',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'meta_query'     => array(
            array(
                'key'     => 'video_file',
                'compare' => 'EXISTS',
            ),
        ),
        'orderby'        => array(
            'meta_value_num' => 'DESC',
            'date'           => 'DESC',
        ),
        'meta_key'       => 'video_views',
    );

    return new WP_Query( $args );
}
