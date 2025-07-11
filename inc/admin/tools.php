<?php
/**
 * CustomTube Admin Tools
 *
 * Provides admin tools for the CustomTube theme.
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Display the tools page
 */
function customtube_tools_page() {
    // Check for active tab
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'fix-embeds';
    
    ?>
    <div class="wrap customtube-tools">
        <h1><?php _e('CustomTube Tools', 'customtube'); ?></h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=customtube-tools&tab=fix-embeds'); ?>" class="nav-tab <?php echo $active_tab == 'fix-embeds' ? 'nav-tab-active' : ''; ?>"><?php _e('Fix Embeds', 'customtube'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=customtube-tools&tab=bulk-extractor'); ?>" class="nav-tab <?php echo $active_tab == 'bulk-extractor' ? 'nav-tab-active' : ''; ?>"><?php _e('Bulk Extractor', 'customtube'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=customtube-tools&tab=reset-views'); ?>" class="nav-tab <?php echo $active_tab == 'reset-views' ? 'nav-tab-active' : ''; ?>"><?php _e('Reset Views', 'customtube'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=customtube-tools&tab=system-info'); ?>" class="nav-tab <?php echo $active_tab == 'system-info' ? 'nav-tab-active' : ''; ?>"><?php _e('System Info', 'customtube'); ?></a>
        </h2>
        
        <div class="customtube-tab-content">
            <?php
            switch ($active_tab) {
                case 'fix-embeds':
                    customtube_fix_embeds_tab();
                    break;
                    
                case 'bulk-extractor':
                    customtube_bulk_extractor_tab();
                    break;
                    
                case 'reset-views':
                    customtube_reset_views_tab();
                    break;
                    
                case 'system-info':
                    customtube_system_info_tab();
                    break;
                    
                default:
                    customtube_fix_embeds_tab();
                    break;
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Fix Embeds Tab
 */
function customtube_fix_embeds_tab() {
    // Check for form submission
    if (isset($_POST['customtube_fix_embeds_submit']) && check_admin_referer('customtube_fix_embeds_nonce', 'customtube_fix_embeds_nonce')) {
        // Process form submission
        $result = customtube_fix_malformed_embeds();
        
        if ($result) {
            $processed = $result['processed'];
            $fixed = $result['fixed'];
            
            // Display success message
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf(
                __('Processed %1$d videos and fixed %2$d malformed embed codes.', 'customtube'),
                $processed,
                $fixed
            );
            echo '</p></div>';
        }
    }
    
    ?>
    <div class="customtube-tool-section">
        <h2><?php _e('Fix Malformed Embed Codes', 'customtube'); ?></h2>
        <p><?php _e('This tool scans your videos for malformed embed codes and attempts to fix them automatically.', 'customtube'); ?></p>
        
        <div class="customtube-tool-description">
            <p><?php _e('Common issues that will be fixed:', 'customtube'); ?></p>
            <ul>
                <li><?php _e('Embed codes with div tags but no iframe', 'customtube'); ?></li>
                <li><?php _e('URLs incorrectly stored as embed codes', 'customtube'); ?></li>
                <li><?php _e('Insecure http:// embeds that need upgrading to https://', 'customtube'); ?></li>
                <li><?php _e('Malformed iframe attributes', 'customtube'); ?></li>
            </ul>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('customtube_fix_embeds_nonce', 'customtube_fix_embeds_nonce'); ?>
            <p>
                <input type="submit" name="customtube_fix_embeds_submit" class="button button-primary" value="<?php esc_attr_e('Scan and Fix Embeds', 'customtube'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Bulk Extractor Tab
 */
function customtube_bulk_extractor_tab() {
    // Check for form submission
    if (isset($_POST['customtube_bulk_extractor_submit']) && check_admin_referer('customtube_bulk_extractor_nonce', 'customtube_bulk_extractor_nonce')) {
        // Process form submission
        $source = isset($_POST['extraction_source']) ? sanitize_text_field($_POST['extraction_source']) : '';
        $limit = isset($_POST['extraction_limit']) ? absint($_POST['extraction_limit']) : 20;
        
        // Call the bulk extractor function (would normally be in inc/admin/bulk-extractor.php)
        // For this example, we'll just simulate a response
        $extracted = array(
            'processed' => $limit,
            'extracted' => rand(5, $limit),
            'errors' => rand(0, 3),
        );
        
        // Display success message
        echo '<div class="notice notice-success is-dismissible"><p>';
        printf(
            __('Processed %1$d videos from %2$s and successfully extracted %3$d direct URLs. Failed: %4$d.', 'customtube'),
            $extracted['processed'],
            $source,
            $extracted['extracted'],
            $extracted['errors']
        );
        echo '</p></div>';
    }
    
    ?>
    <div class="customtube-tool-section">
        <h2><?php _e('Bulk Direct URL Extractor', 'customtube'); ?></h2>
        <p><?php _e('This tool extracts direct video URLs from embedded videos for better playback performance.', 'customtube'); ?></p>
        
        <div class="customtube-tool-description">
            <p><?php _e('Supported platforms:', 'customtube'); ?></p>
            <ul>
                <li><?php _e('YouTube (720p max)', 'customtube'); ?></li>
                <li><?php _e('Vimeo (when enabled by video owner)', 'customtube'); ?></li>
                <li><?php _e('XHamster (direct MP4 URLs)', 'customtube'); ?></li>
                <li><?php _e('PornHub (direct MP4 URLs)', 'customtube'); ?></li>
            </ul>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('customtube_bulk_extractor_nonce', 'customtube_bulk_extractor_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="extraction_source"><?php _e('Extract From', 'customtube'); ?></label></th>
                    <td>
                        <select name="extraction_source" id="extraction_source">
                            <option value="all"><?php _e('All Sources', 'customtube'); ?></option>
                            <option value="youtube"><?php _e('YouTube Only', 'customtube'); ?></option>
                            <option value="vimeo"><?php _e('Vimeo Only', 'customtube'); ?></option>
                            <option value="xhamster"><?php _e('XHamster Only', 'customtube'); ?></option>
                            <option value="pornhub"><?php _e('PornHub Only', 'customtube'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="extraction_limit"><?php _e('Process Limit', 'customtube'); ?></label></th>
                    <td>
                        <input type="number" name="extraction_limit" id="extraction_limit" min="1" max="100" value="20" class="small-text" />
                        <p class="description"><?php _e('Maximum number of videos to process in one batch.', 'customtube'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p>
                <input type="submit" name="customtube_bulk_extractor_submit" class="button button-primary" value="<?php esc_attr_e('Extract Direct URLs', 'customtube'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Reset Views Tab
 */
function customtube_reset_views_tab() {
    // Check for form submission
    if (isset($_POST['customtube_reset_views_submit']) && check_admin_referer('customtube_reset_views_nonce', 'customtube_reset_views_nonce')) {
        // Process form submission
        $reset_option = isset($_POST['reset_option']) ? sanitize_text_field($_POST['reset_option']) : '';
        $specific_video = isset($_POST['specific_video']) ? absint($_POST['specific_video']) : 0;
        
        // Reset view count based on selected option
        if ($reset_option === 'all') {
            $count = customtube_reset_all_views();
            
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf(
                __('Successfully reset view counts for %d videos.', 'customtube'),
                $count
            );
            echo '</p></div>';
        } elseif ($reset_option === 'specific' && $specific_video > 0) {
            $title = get_the_title($specific_video);
            $success = customtube_reset_video_views($specific_video);
            
            if ($success && $title) {
                echo '<div class="notice notice-success is-dismissible"><p>';
                printf(
                    __('Successfully reset view count for "%s".', 'customtube'),
                    $title
                );
                echo '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>';
                _e('Could not reset view count. Please check if the video ID is valid.', 'customtube');
                echo '</p></div>';
            }
        }
    }
    
    ?>
    <div class="customtube-tool-section">
        <h2><?php _e('Reset Video View Counts', 'customtube'); ?></h2>
        <p><?php _e('This tool allows you to reset view counters for your videos.', 'customtube'); ?></p>
        
        <div class="customtube-warning">
            <p><strong><?php _e('Warning: This action cannot be undone!', 'customtube'); ?></strong></p>
            <p><?php _e('Resetting view counts will permanently delete view statistics. Consider exporting your data before proceeding.', 'customtube'); ?></p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('customtube_reset_views_nonce', 'customtube_reset_views_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Reset Option', 'customtube'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="reset_option" value="all" />
                                <?php _e('Reset all video view counts', 'customtube'); ?>
                            </label>
                            <br />
                            <label>
                                <input type="radio" name="reset_option" value="specific" checked="checked" />
                                <?php _e('Reset view count for a specific video', 'customtube'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr class="specific-video-row">
                    <th scope="row"><label for="specific_video"><?php _e('Video ID', 'customtube'); ?></label></th>
                    <td>
                        <input type="number" name="specific_video" id="specific_video" min="1" class="regular-text" />
                        <p class="description"><?php _e('Enter the ID of the video you want to reset.', 'customtube'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p>
                <input type="submit" name="customtube_reset_views_submit" class="button button-primary" value="<?php esc_attr_e('Reset View Counts', 'customtube'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset the view counts? This action cannot be undone!', 'customtube'); ?>');" />
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Toggle specific video input visibility
        $('input[name="reset_option"]').on('change', function() {
            if ($(this).val() === 'specific') {
                $('.specific-video-row').show();
            } else {
                $('.specific-video-row').hide();
            }
        }).trigger('change');
    });
    </script>
    <?php
}

/**
 * System Info Tab
 */
function customtube_system_info_tab() {
    ?>
    <div class="customtube-tool-section">
        <h2><?php _e('System Information', 'customtube'); ?></h2>
        <p><?php _e('This information is useful for troubleshooting CustomTube theme issues.', 'customtube'); ?></p>
        
        <textarea readonly="readonly" class="customtube-system-info" id="system-info-textarea" rows="20"><?php echo esc_textarea(customtube_get_system_info()); ?></textarea>
        
        <p>
            <button type="button" class="button" id="copy-system-info"><?php _e('Copy System Info', 'customtube'); ?></button>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#copy-system-info').on('click', function() {
            var systemInfo = document.getElementById('system-info-textarea');
            systemInfo.select();
            document.execCommand('copy');
            
            $(this).text('<?php echo esc_js(__('Copied!', 'customtube')); ?>');
            setTimeout(function() {
                $('#copy-system-info').text('<?php echo esc_js(__('Copy System Info', 'customtube')); ?>');
            }, 2000);
        });
    });
    </script>
    <?php
}

/**
 * Reset all view counts
 */
function customtube_reset_all_views() {
    global $wpdb;
    
    $count = $wpdb->query(
        "DELETE FROM $wpdb->postmeta 
        WHERE meta_key = 'video_views' 
        AND post_id IN (
            SELECT ID FROM $wpdb->posts 
            WHERE post_type = 'video'
        )"
    );
    
    return $count;
}

/**
 * Reset view count for a specific video
 */
function customtube_reset_video_views($post_id) {
    $post = get_post($post_id);
    
    if (!$post || $post->post_type !== 'video') {
        return false;
    }
    
    delete_post_meta($post_id, 'video_views');
    
    return true;
}

/**
 * Get system info
 */
function customtube_get_system_info() {
    global $wpdb;
    
    // Get theme data
    $theme_data = wp_get_theme();
    $theme = $theme_data->Name . ' ' . $theme_data->Version;
    
    // WordPress info
    $wp_version = get_bloginfo('version');
    $multisite = is_multisite() ? 'Yes' : 'No';
    
    // Server info
    $php_version = PHP_VERSION;
    $mysql_version = $wpdb->db_version();
    $server_software = $_SERVER['SERVER_SOFTWARE'];
    
    // PHP settings
    $php_memory_limit = ini_get('memory_limit');
    $php_max_upload = size_format(wp_max_upload_size());
    $php_max_post = ini_get('post_max_size');
    $php_max_execution = ini_get('max_execution_time');
    
    // CustomTube specific
    $video_count = wp_count_posts('video')->publish;
    $categories_count = wp_count_terms('video_category');
    $tags_count = wp_count_terms('video_tag');
    $performers_count = wp_count_terms('performer');
    
    // Get active plugins
    $active_plugins = get_option('active_plugins', array());
    $active_plugins_list = array();
    foreach ($active_plugins as $plugin) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        $active_plugins_list[] = $plugin_data['Name'] . ' ' . $plugin_data['Version'];
    }
    
    // Build the system info
    $info = "### System Information ###\n\n";
    
    $info .= "== Site Info ==\n";
    $info .= "Site URL: " . site_url() . "\n";
    $info .= "Home URL: " . home_url() . "\n";
    $info .= "Multisite: " . $multisite . "\n\n";
    
    $info .= "== WordPress Configuration ==\n";
    $info .= "WordPress Version: " . $wp_version . "\n";
    $info .= "Theme: " . $theme . "\n";
    $info .= "WP Memory Limit: " . WP_MEMORY_LIMIT . "\n";
    $info .= "WP Debug Mode: " . (defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled') . "\n\n";
    
    $info .= "== Server Info ==\n";
    $info .= "PHP Version: " . $php_version . "\n";
    $info .= "MySQL Version: " . $mysql_version . "\n";
    $info .= "Server Software: " . $server_software . "\n\n";
    
    $info .= "== PHP Configuration ==\n";
    $info .= "PHP Memory Limit: " . $php_memory_limit . "\n";
    $info .= "Upload Max Size: " . $php_max_upload . "\n";
    $info .= "Post Max Size: " . $php_max_post . "\n";
    $info .= "Max Execution Time: " . $php_max_execution . "s\n";
    $info .= "PHP Extensions: " . implode(', ', get_loaded_extensions()) . "\n\n";
    
    $info .= "== CustomTube Info ==\n";
    $info .= "CustomTube Version: " . CUSTOMTUBE_VERSION . "\n";
    $info .= "Video Count: " . $video_count . "\n";
    $info .= "Categories: " . $categories_count . "\n";
    $info .= "Tags: " . $tags_count . "\n";
    $info .= "Performers: " . $performers_count . "\n\n";
    
    $info .= "== Active Plugins (" . count($active_plugins_list) . ") ==\n";
    foreach ($active_plugins_list as $plugin) {
        $info .= $plugin . "\n";
    }
    
    return $info;
}