<?php
/**
 * CustomTube Admin Dashboard
 *
 * Provides a main dashboard interface for the CustomTube theme.
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Display the dashboard page
 */
function customtube_dashboard_page() {
    // Get video stats
    $video_count = wp_count_posts('video')->publish;
    $total_views = customtube_get_total_views();
    $latest_videos = customtube_get_latest_videos(5);
    $popular_videos = customtube_get_popular_videos(5);
    
    // Check for new video imports available
    $imports_available = customtube_check_import_sources();
    
    // Check for system issues
    $system_issues = customtube_check_system_issues();
    
    ?>
    <div class="wrap customtube-dashboard">
        <h1><?php _e('CustomTube Dashboard', 'customtube'); ?></h1>
        
        <div class="customtube-welcome-panel">
            <div class="welcome-panel-content">
                <h2><?php _e('Welcome to CustomTube', 'customtube'); ?></h2>
                <p class="about-description"><?php _e('Your ultimate WordPress theme for organizing and presenting adult video content.', 'customtube'); ?></p>
                
                <div class="welcome-panel-column-container">
                    <div class="welcome-panel-column">
                        <h3><?php _e('Quick Actions', 'customtube'); ?></h3>
                        <ul>
                            <li><a href="<?php echo admin_url('post-new.php?post_type=video'); ?>" class="button button-primary"><?php _e('Add New Video', 'customtube'); ?></a></li>
                            <li><a href="<?php echo admin_url('admin.php?page=customtube-settings'); ?>"><?php _e('Configure Settings', 'customtube'); ?></a></li>
                            <li><a href="<?php echo admin_url('admin.php?page=customtube-tools'); ?>"><?php _e('Access Tools', 'customtube'); ?></a></li>
                        </ul>
                    </div>
                    <div class="welcome-panel-column">
                        <h3><?php _e('Next Steps', 'customtube'); ?></h3>
                        <ul>
                            <?php if ($video_count < 5) : ?>
                            <li><?php _e('Start by adding some videos to your site', 'customtube'); ?></li>
                            <?php endif; ?>
                            <li><?php _e('Customize your site appearance', 'customtube'); ?></li>
                            <li><?php _e('Configure age verification settings', 'customtube'); ?></li>
                            <li><?php _e('Set up your video import feeds', 'customtube'); ?></li>
                        </ul>
                    </div>
                    <div class="welcome-panel-column welcome-panel-last">
                        <h3><?php _e('More Actions', 'customtube'); ?></h3>
                        <ul>
                            <li><a href="<?php echo admin_url('edit-tags.php?taxonomy=video_category&post_type=video'); ?>"><?php _e('Manage Video Categories', 'customtube'); ?></a></li>
                            <li><a href="<?php echo admin_url('edit-tags.php?taxonomy=video_tag&post_type=video'); ?>"><?php _e('Manage Video Tags', 'customtube'); ?></a></li>
                            <li><a href="<?php echo admin_url('edit-tags.php?taxonomy=performer&post_type=video'); ?>"><?php _e('Manage Performers', 'customtube'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <div class="postbox-container" style="width:49%;">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        
                        <!-- Video Statistics -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle ui-sortable-handle"><?php _e('Video Statistics', 'customtube'); ?></h2>
                            </div>
                            <div class="inside">
                                <div class="main">
                                    <ul class="customtube-stats">
                                        <li>
                                            <div class="customtube-stat-box">
                                                <h3><?php _e('Total Videos', 'customtube'); ?></h3>
                                                <span class="customtube-stat-count"><?php echo esc_html($video_count); ?></span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="customtube-stat-box">
                                                <h3><?php _e('Total Views', 'customtube'); ?></h3>
                                                <span class="customtube-stat-count"><?php echo esc_html(number_format_i18n($total_views)); ?></span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="customtube-stat-box">
                                                <h3><?php _e('Categories', 'customtube'); ?></h3>
                                                <span class="customtube-stat-count"><?php echo esc_html(wp_count_terms('video_category')); ?></span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="customtube-stat-box">
                                                <h3><?php _e('Performers', 'customtube'); ?></h3>
                                                <span class="customtube-stat-count"><?php echo esc_html(wp_count_terms('performer')); ?></span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Latest Videos -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle ui-sortable-handle"><?php _e('Latest Videos', 'customtube'); ?></h2>
                            </div>
                            <div class="inside">
                                <div class="main">
                                    <?php if ($latest_videos && !empty($latest_videos)) : ?>
                                        <table class="wp-list-table widefat fixed striped customtube-videos-table">
                                            <thead>
                                                <tr>
                                                    <th><?php _e('Thumbnail', 'customtube'); ?></th>
                                                    <th><?php _e('Title', 'customtube'); ?></th>
                                                    <th><?php _e('Date', 'customtube'); ?></th>
                                                    <th><?php _e('Views', 'customtube'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($latest_videos as $video) : 
                                                    $views = get_post_meta($video->ID, 'video_views', true);
                                                    $views = $views ? $views : 0;
                                                    $thumbnail = get_the_post_thumbnail_url($video->ID, 'thumbnail');
                                                ?>
                                                <tr>
                                                    <td class="video-thumbnail">
                                                        <?php if ($thumbnail) : ?>
                                                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($video->post_title); ?>" width="80" height="45" />
                                                        <?php else : ?>
                                                            <div class="no-thumbnail"></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo get_edit_post_link($video->ID); ?>"><?php echo esc_html($video->post_title); ?></a>
                                                    </td>
                                                    <td><?php echo get_the_date('', $video->ID); ?></td>
                                                    <td><?php echo number_format_i18n($views); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <p class="customtube-view-all">
                                            <a href="<?php echo admin_url('edit.php?post_type=video'); ?>"><?php _e('View All Videos', 'customtube'); ?> →</a>
                                        </p>
                                    <?php else : ?>
                                        <p><?php _e('No videos found. Start adding videos to your site.', 'customtube'); ?></p>
                                        <a href="<?php echo admin_url('post-new.php?post_type=video'); ?>" class="button button-primary"><?php _e('Add New Video', 'customtube'); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="postbox-container" style="width:49%;">
                    <div id="side-sortables" class="meta-box-sortables ui-sortable">
                        
                        <!-- Popular Videos -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle ui-sortable-handle"><?php _e('Popular Videos', 'customtube'); ?></h2>
                            </div>
                            <div class="inside">
                                <div class="main">
                                    <?php if ($popular_videos && !empty($popular_videos)) : ?>
                                        <table class="wp-list-table widefat fixed striped customtube-videos-table">
                                            <thead>
                                                <tr>
                                                    <th><?php _e('Thumbnail', 'customtube'); ?></th>
                                                    <th><?php _e('Title', 'customtube'); ?></th>
                                                    <th><?php _e('Views', 'customtube'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($popular_videos as $video) : 
                                                    $views = get_post_meta($video->ID, 'video_views', true);
                                                    $views = $views ? $views : 0;
                                                    $thumbnail = get_the_post_thumbnail_url($video->ID, 'thumbnail');
                                                ?>
                                                <tr>
                                                    <td class="video-thumbnail">
                                                        <?php if ($thumbnail) : ?>
                                                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($video->post_title); ?>" width="80" height="45" />
                                                        <?php else : ?>
                                                            <div class="no-thumbnail"></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo get_edit_post_link($video->ID); ?>"><?php echo esc_html($video->post_title); ?></a>
                                                    </td>
                                                    <td><?php echo number_format_i18n($views); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else : ?>
                                        <p><?php _e('No data available yet. Views will be tracked as visitors watch your videos.', 'customtube'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Status -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle ui-sortable-handle"><?php _e('System Status', 'customtube'); ?></h2>
                            </div>
                            <div class="inside">
                                <div class="main">
                                    <?php if ($system_issues && !empty($system_issues)) : ?>
                                        <div class="customtube-system-issues">
                                            <p><?php _e('The following issues need your attention:', 'customtube'); ?></p>
                                            <ul>
                                                <?php foreach ($system_issues as $issue) : ?>
                                                    <li class="customtube-system-issue-<?php echo esc_attr($issue['severity']); ?>">
                                                        <span class="dashicons <?php echo esc_attr($issue['icon']); ?>"></span>
                                                        <?php echo esc_html($issue['message']); ?>
                                                        <?php if (!empty($issue['action_url'])) : ?>
                                                            <a href="<?php echo esc_url($issue['action_url']); ?>"><?php echo esc_html($issue['action_text']); ?></a>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php else : ?>
                                        <div class="customtube-system-ok">
                                            <p><span class="dashicons dashicons-yes-alt"></span> <?php _e('All systems are running smoothly!', 'customtube'); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="customtube-system-info">
                                        <table class="widefat" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td><?php _e('CustomTube Version', 'customtube'); ?></td>
                                                    <td><?php echo CUSTOMTUBE_VERSION; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php _e('WordPress Version', 'customtube'); ?></td>
                                                    <td><?php echo get_bloginfo('version'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php _e('PHP Version', 'customtube'); ?></td>
                                                    <td><?php echo PHP_VERSION; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php _e('Memory Limit', 'customtube'); ?></td>
                                                    <td><?php echo WP_MEMORY_LIMIT; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php _e('Max Upload Size', 'customtube'); ?></td>
                                                    <td><?php echo size_format(wp_max_upload_size()); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get total views for all videos
 */
function customtube_get_total_views() {
    global $wpdb;
    
    $total_views = $wpdb->get_var(
        "SELECT SUM(meta_value) 
        FROM $wpdb->postmeta 
        WHERE meta_key = 'video_views' 
        AND post_id IN (
            SELECT ID FROM $wpdb->posts 
            WHERE post_type = 'video' 
            AND post_status = 'publish'
        )"
    );
    
    return $total_views ? absint($total_views) : 0;
}

/**
 * Get latest videos
 */
function customtube_get_latest_videos($limit = 5) {
    $args = array(
        'post_type'      => 'video',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    
    $query = new WP_Query($args);
    
    return $query->posts;
}

/**
 * Get popular videos
 */
function customtube_get_popular_videos($limit = 5) {
    $args = array(
        'post_type'      => 'video',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_key'       => 'video_views',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    );
    
    $query = new WP_Query($args);
    
    return $query->posts;
}

/**
 * Check if there are new videos available for import
 */
function customtube_check_import_sources() {
    // This would normally check external APIs for new videos
    // For now, we'll just return a placeholder
    return array(
        'youtube' => 12,
        'xhamster' => 15,
        'pornhub' => 8,
    );
}

/**
 * Check for system issues
 */
function customtube_check_system_issues() {
    $issues = array();
    
    // Check age verification
    if (!customtube_get_option('age_verification_enabled')) {
        $issues[] = array(
            'severity' => 'warning',
            'icon' => 'dashicons-warning',
            'message' => __('Age verification is disabled which may expose adult content to minors.', 'customtube'),
            'action_url' => admin_url('admin.php?page=customtube-settings#age_verification_enabled'),
            'action_text' => __('Enable Now', 'customtube'),
        );
    }
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $issues[] = array(
            'severity' => 'warning',
            'icon' => 'dashicons-warning',
            'message' => __('Your PHP version is outdated. We recommend using PHP 7.4 or later.', 'customtube'),
            'action_url' => 'https://wordpress.org/support/update-php/',
            'action_text' => __('Learn More', 'customtube'),
        );
    }
    
    // Check video count
    $video_count = wp_count_posts('video')->publish;
    if ($video_count < 1) {
        $issues[] = array(
            'severity' => 'info',
            'icon' => 'dashicons-info',
            'message' => __('Your site has no videos yet. Start adding some content!', 'customtube'),
            'action_url' => admin_url('post-new.php?post_type=video'),
            'action_text' => __('Add Video', 'customtube'),
        );
    }
    
    return $issues;
}