<?php
/**
 * Meta boxes for Ad post type
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add meta boxes for tube_ad post type
 */
function customtube_add_ad_meta_boxes() {
    add_meta_box(
        'ad-settings',
        __('Ad Settings', 'customtube'),
        'customtube_ad_settings_meta_box',
        'tube_ad',
        'normal',
        'high'
    );
    
    add_meta_box(
        'ad-targeting',
        __('Targeting Options', 'customtube'),
        'customtube_ad_targeting_meta_box',
        'tube_ad',
        'normal',
        'default'
    );
    
    add_meta_box(
        'ad-limits',
        __('Impression & Click Limits', 'customtube'),
        'customtube_ad_limits_meta_box',
        'tube_ad',
        'side',
        'default'
    );
    
    add_meta_box(
        'ad-stats',
        __('Ad Statistics', 'customtube'),
        'customtube_ad_stats_meta_box',
        'tube_ad',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'customtube_add_ad_meta_boxes');

/**
 * Ad Settings Meta Box
 */
function customtube_ad_settings_meta_box($post) {
    wp_nonce_field('customtube_ad_meta_box', 'customtube_ad_meta_box_nonce');
    
    $ad_type = get_post_meta($post->ID, '_ad_type', true) ?: 'code';
    $ad_status = get_post_meta($post->ID, '_ad_status', true) ?: 'active';
    $ad_code = get_post_meta($post->ID, '_ad_code', true) ?: '';
    $ad_image = get_post_meta($post->ID, '_ad_image', true) ?: '';
    $ad_url = get_post_meta($post->ID, '_ad_url', true) ?: '';
    $ad_width = get_post_meta($post->ID, '_ad_width', true) ?: 'auto';
    $ad_height = get_post_meta($post->ID, '_ad_height', true) ?: 'auto';
    $ad_class = get_post_meta($post->ID, '_ad_class', true) ?: '';
    ?>
    
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="ad_status"><?php _e('Ad Status', 'customtube'); ?></label>
            </th>
            <td>
                <select name="ad_status" id="ad_status" class="regular-text">
                    <option value="active" <?php selected($ad_status, 'active'); ?>><?php _e('Active', 'customtube'); ?></option>
                    <option value="inactive" <?php selected($ad_status, 'inactive'); ?>><?php _e('Inactive', 'customtube'); ?></option>
                    <option value="paused" <?php selected($ad_status, 'paused'); ?>><?php _e('Paused', 'customtube'); ?></option>
                </select>
                <p class="description"><?php _e('Control whether this ad is displayed on the site.', 'customtube'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="ad_type"><?php _e('Ad Type', 'customtube'); ?></label>
            </th>
            <td>
                <select name="ad_type" id="ad_type" class="regular-text">
                    <option value="code" <?php selected($ad_type, 'code'); ?>><?php _e('HTML/JavaScript Code', 'customtube'); ?></option>
                    <option value="image" <?php selected($ad_type, 'image'); ?>><?php _e('Image Banner', 'customtube'); ?></option>
                    <option value="vast" <?php selected($ad_type, 'vast'); ?>><?php _e('VAST Video Ad', 'customtube'); ?></option>
                </select>
                <p class="description"><?php _e('Select the type of ad content.', 'customtube'); ?></p>
            </td>
        </tr>
    </table>
    
    <!-- Code Ad Settings -->
    <div id="code-ad-settings" class="ad-type-settings" <?php echo $ad_type !== 'code' ? 'style="display:none;"' : ''; ?>>
        <h4><?php _e('HTML/JavaScript Code', 'customtube'); ?></h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="ad_code"><?php _e('Ad Code', 'customtube'); ?></label>
                </th>
                <td>
                    <textarea name="ad_code" id="ad_code" rows="8" cols="50" class="large-text code"><?php echo esc_textarea($ad_code); ?></textarea>
                    <p class="description"><?php _e('Paste your ad network code (HTML, JavaScript, etc.) here.', 'customtube'); ?></p>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Image Ad Settings -->
    <div id="image-ad-settings" class="ad-type-settings" <?php echo $ad_type !== 'image' ? 'style="display:none;"' : ''; ?>>
        <h4><?php _e('Image Banner Settings', 'customtube'); ?></h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="ad_image"><?php _e('Image URL', 'customtube'); ?></label>
                </th>
                <td>
                    <input type="url" name="ad_image" id="ad_image" value="<?php echo esc_url($ad_image); ?>" class="regular-text" />
                    <button type="button" class="button" id="upload-ad-image"><?php _e('Upload Image', 'customtube'); ?></button>
                    <p class="description"><?php _e('URL of the ad image.', 'customtube'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="ad_url"><?php _e('Click URL', 'customtube'); ?></label>
                </th>
                <td>
                    <input type="url" name="ad_url" id="ad_url" value="<?php echo esc_url($ad_url); ?>" class="regular-text" />
                    <p class="description"><?php _e('URL to redirect when the ad is clicked.', 'customtube'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="ad_width"><?php _e('Width', 'customtube'); ?></label>
                </th>
                <td>
                    <input type="text" name="ad_width" id="ad_width" value="<?php echo esc_attr($ad_width); ?>" class="small-text" />
                    <p class="description"><?php _e('Width in pixels or "auto". Examples: 728, 300, auto', 'customtube'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="ad_height"><?php _e('Height', 'customtube'); ?></label>
                </th>
                <td>
                    <input type="text" name="ad_height" id="ad_height" value="<?php echo esc_attr($ad_height); ?>" class="small-text" />
                    <p class="description"><?php _e('Height in pixels or "auto". Examples: 90, 250, auto', 'customtube'); ?></p>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Advanced Settings -->
    <h4><?php _e('Advanced Settings', 'customtube'); ?></h4>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="ad_class"><?php _e('CSS Class', 'customtube'); ?></label>
            </th>
            <td>
                <input type="text" name="ad_class" id="ad_class" value="<?php echo esc_attr($ad_class); ?>" class="regular-text" />
                <p class="description"><?php _e('Additional CSS classes for styling (optional).', 'customtube'); ?></p>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        // Show/hide ad type settings
        $('#ad_type').change(function() {
            $('.ad-type-settings').hide();
            $('#' + $(this).val() + '-ad-settings').show();
        });
        
        // Media uploader for ad images
        $('#upload-ad-image').click(function(e) {
            e.preventDefault();
            
            var mediaUploader = wp.media({
                title: '<?php _e('Choose Ad Image', 'customtube'); ?>',
                button: {
                    text: '<?php _e('Choose Image', 'customtube'); ?>'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#ad_image').val(attachment.url);
            });
            
            mediaUploader.open();
        });
    });
    </script>
    <?php
}

/**
 * Ad Targeting Meta Box
 */
function customtube_ad_targeting_meta_box($post) {
    $start_date = get_post_meta($post->ID, '_ad_start_date', true) ?: '';
    $end_date = get_post_meta($post->ID, '_ad_end_date', true) ?: '';
    $device_targeting = get_post_meta($post->ID, '_ad_device_targeting', true) ?: array();
    $geo_targeting = get_post_meta($post->ID, '_ad_geo_targeting', true) ?: '';
    $country_codes = get_post_meta($post->ID, '_ad_country_codes', true) ?: '';
    
    if (!is_array($device_targeting)) {
        $device_targeting = array();
    }
    ?>
    
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="ad_start_date"><?php _e('Start Date', 'customtube'); ?></label>
            </th>
            <td>
                <input type="date" name="ad_start_date" id="ad_start_date" value="<?php echo esc_attr($start_date); ?>" class="regular-text" />
                <p class="description"><?php _e('Leave empty for no start date restriction.', 'customtube'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="ad_end_date"><?php _e('End Date', 'customtube'); ?></label>
            </th>
            <td>
                <input type="date" name="ad_end_date" id="ad_end_date" value="<?php echo esc_attr($end_date); ?>" class="regular-text" />
                <p class="description"><?php _e('Leave empty for no end date restriction.', 'customtube'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <?php _e('Device Targeting', 'customtube'); ?>
            </th>
            <td>
                <fieldset>
                    <label>
                        <input type="checkbox" name="ad_device_targeting[]" value="desktop" <?php checked(in_array('desktop', $device_targeting)); ?> />
                        <?php _e('Desktop', 'customtube'); ?>
                    </label><br />
                    
                    <label>
                        <input type="checkbox" name="ad_device_targeting[]" value="tablet" <?php checked(in_array('tablet', $device_targeting)); ?> />
                        <?php _e('Tablet', 'customtube'); ?>
                    </label><br />
                    
                    <label>
                        <input type="checkbox" name="ad_device_targeting[]" value="mobile" <?php checked(in_array('mobile', $device_targeting)); ?> />
                        <?php _e('Mobile', 'customtube'); ?>
                    </label>
                </fieldset>
                <p class="description"><?php _e('Leave unchecked to show on all devices.', 'customtube'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="ad_geo_targeting"><?php _e('Geographic Targeting', 'customtube'); ?></label>
            </th>
            <td>
                <select name="ad_geo_targeting" id="ad_geo_targeting" class="regular-text">
                    <option value="" <?php selected($geo_targeting, ''); ?>><?php _e('No geographic targeting', 'customtube'); ?></option>
                    <option value="include" <?php selected($geo_targeting, 'include'); ?>><?php _e('Include only these countries', 'customtube'); ?></option>
                    <option value="exclude" <?php selected($geo_targeting, 'exclude'); ?>><?php _e('Exclude these countries', 'customtube'); ?></option>
                </select>
            </td>
        </tr>
        
        <tr id="country-codes-row" <?php echo empty($geo_targeting) ? 'style="display:none;"' : ''; ?>>
            <th scope="row">
                <label for="ad_country_codes"><?php _e('Country Codes', 'customtube'); ?></label>
            </th>
            <td>
                <textarea name="ad_country_codes" id="ad_country_codes" rows="3" cols="50" class="regular-text"><?php echo esc_textarea($country_codes); ?></textarea>
                <p class="description"><?php _e('Enter 2-letter country codes separated by commas (e.g., US, GB, CA, DE).', 'customtube'); ?></p>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        $('#ad_geo_targeting').change(function() {
            if ($(this).val() === '') {
                $('#country-codes-row').hide();
            } else {
                $('#country-codes-row').show();
            }
        });
    });
    </script>
    <?php
}

/**
 * Ad Limits Meta Box
 */
function customtube_ad_limits_meta_box($post) {
    $max_impressions = get_post_meta($post->ID, '_ad_max_impressions', true) ?: '0';
    $max_clicks = get_post_meta($post->ID, '_ad_max_clicks', true) ?: '0';
    ?>
    
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="ad_max_impressions"><?php _e('Max Impressions', 'customtube'); ?></label>
            </th>
            <td>
                <input type="number" name="ad_max_impressions" id="ad_max_impressions" value="<?php echo esc_attr($max_impressions); ?>" class="small-text" min="0" />
                <p class="description"><?php _e('0 = unlimited impressions', 'customtube'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="ad_max_clicks"><?php _e('Max Clicks', 'customtube'); ?></label>
            </th>
            <td>
                <input type="number" name="ad_max_clicks" id="ad_max_clicks" value="<?php echo esc_attr($max_clicks); ?>" class="small-text" min="0" />
                <p class="description"><?php _e('0 = unlimited clicks', 'customtube'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Ad Statistics Meta Box
 */
function customtube_ad_stats_meta_box($post) {
    $impressions = get_post_meta($post->ID, '_ad_impressions', true) ?: '0';
    $clicks = get_post_meta($post->ID, '_ad_clicks', true) ?: '0';
    $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
    
    $last_shown = get_post_meta($post->ID, '_ad_last_shown', true);
    $last_shown_formatted = $last_shown ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_shown) : __('Never', 'customtube');
    ?>
    
    <table class="form-table">
        <tr>
            <td>
                <strong><?php _e('Current Statistics:', 'customtube'); ?></strong><br />
                
                <div style="margin: 10px 0;">
                    <strong><?php _e('Impressions:', 'customtube'); ?></strong> <?php echo number_format($impressions); ?><br />
                    <strong><?php _e('Clicks:', 'customtube'); ?></strong> <?php echo number_format($clicks); ?><br />
                    <strong><?php _e('CTR:', 'customtube'); ?></strong> <?php echo $ctr; ?>%<br />
                    <strong><?php _e('Last Shown:', 'customtube'); ?></strong> <?php echo $last_shown_formatted; ?>
                </div>
                
                <button type="button" class="button" id="reset-ad-stats" data-ad-id="<?php echo $post->ID; ?>">
                    <?php _e('Reset Statistics', 'customtube'); ?>
                </button>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        $('#reset-ad-stats').click(function() {
            if (confirm('<?php _e('Are you sure you want to reset the statistics for this ad?', 'customtube'); ?>')) {
                var adId = $(this).data('ad-id');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'customtube_reset_ad_stats',
                        ad_id: adId,
                        nonce: '<?php echo wp_create_nonce('customtube_reset_ad_stats'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('<?php _e('Failed to reset statistics.', 'customtube'); ?>');
                        }
                    }
                });
            }
        });
    });
    </script>
    <?php
}

/**
 * Save meta box data
 */
function customtube_save_ad_meta_boxes($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['customtube_ad_meta_box_nonce'])) {
        return;
    }

    // Verify that the nonce is valid
    if (!wp_verify_nonce($_POST['customtube_ad_meta_box_nonce'], 'customtube_ad_meta_box')) {
        return;
    }

    // If this is an autosave, don't save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions
    if (isset($_POST['post_type']) && 'tube_ad' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Sanitize and save meta fields
    $meta_fields = array(
        '_ad_status' => 'sanitize_text_field',
        '_ad_type' => 'sanitize_text_field',
        '_ad_code' => 'customtube_sanitize_ad_code_bypass', // Even less restrictive for ads
        '_ad_image' => 'esc_url_raw',
        '_ad_url' => 'esc_url_raw',
        '_ad_width' => 'sanitize_text_field',
        '_ad_height' => 'sanitize_text_field',
        '_ad_class' => 'sanitize_text_field',
        '_ad_start_date' => 'sanitize_text_field',
        '_ad_end_date' => 'sanitize_text_field',
        '_ad_geo_targeting' => 'sanitize_text_field',
        '_ad_country_codes' => 'sanitize_textarea_field',
        '_ad_max_impressions' => 'absint',
        '_ad_max_clicks' => 'absint'
    );

    foreach ($meta_fields as $field => $sanitize_function) {
        $key = str_replace('_ad_', 'ad_', $field);
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            if (function_exists($sanitize_function)) {
                $value = call_user_func($sanitize_function, $value);
            }
            update_post_meta($post_id, $field, $value);
        }
    }

    // Handle device targeting array
    $device_targeting = isset($_POST['ad_device_targeting']) ? $_POST['ad_device_targeting'] : array();
    $device_targeting = array_map('sanitize_text_field', $device_targeting);
    update_post_meta($post_id, '_ad_device_targeting', $device_targeting);
}
add_action('save_post', 'customtube_save_ad_meta_boxes');

/**
 * AJAX handler to reset ad statistics
 */
function customtube_reset_ad_stats_ajax() {
    check_ajax_referer('customtube_reset_ad_stats', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }
    
    $ad_id = intval($_POST['ad_id']);
    
    if ($ad_id && get_post_type($ad_id) === 'tube_ad') {
        update_post_meta($ad_id, '_ad_impressions', 0);
        update_post_meta($ad_id, '_ad_clicks', 0);
        delete_post_meta($ad_id, '_ad_last_shown');
        
        wp_send_json_success();
    } else {
        wp_send_json_error('Invalid ad ID');
    }
}
add_action('wp_ajax_customtube_reset_ad_stats', 'customtube_reset_ad_stats_ajax');

/**
 * Custom sanitization for ad code that preserves JavaScript
 */
function customtube_sanitize_ad_code($code) {
    // For ad code, we need to be less restrictive than wp_kses_post
    // but still provide some basic security
    
    // Remove any PHP code for security
    $code = preg_replace('/<\?php.*?\?>/is', '', $code);
    $code = preg_replace('/<\?.*?\?>/is', '', $code);
    
    // Allow common ad network tags and attributes
    $allowed_html = array(
        'script' => array(
            'type' => array(),
            'src' => array(),
            'async' => array(),
            'defer' => array(),
            'charset' => array(),
            'data-cfasync' => array(), // CloudFlare async attribute
            'data-*' => array()
        ),
        'iframe' => array(
            'src' => array(),
            'width' => array(),
            'height' => array(),
            'frameborder' => array(),
            'scrolling' => array(),
            'allowfullscreen' => array(),
            'style' => array(),
            'class' => array(),
            'id' => array(),
            'data-*' => array()
        ),
        'div' => array(
            'class' => array(),
            'id' => array(),
            'style' => array(),
            'data-*' => array()
        ),
        'a' => array(
            'href' => array(),
            'target' => array(),
            'rel' => array(),
            'class' => array(),
            'id' => array(),
            'data-*' => array()
        ),
        'img' => array(
            'src' => array(),
            'alt' => array(),
            'width' => array(),
            'height' => array(),
            'class' => array(),
            'id' => array(),
            'style' => array(),
            'data-*' => array()
        ),
        'ins' => array(
            'id' => array(),
            'class' => array(),
            'data-width' => array(),
            'data-height' => array(),
            'data-*' => array()
        ),
        'noscript' => array(),
        'span' => array(
            'class' => array(),
            'id' => array(),
            'style' => array(),
            'data-*' => array()
        )
    );
    
    // For trusted users (admins), allow more permissive content
    if (current_user_can('unfiltered_html')) {
        // Just remove PHP but allow everything else
        return $code;
    }
    
    // For other users, use wp_kses with our custom allowed HTML
    return wp_kses($code, $allowed_html);
}

/**
 * Bypass sanitization for ad codes - minimal filtering
 */
function customtube_sanitize_ad_code_bypass($code) {
    // Only remove PHP code for security, allow everything else
    $code = preg_replace('/<\?php.*?\?>/is', '', $code);
    $code = preg_replace('/<\?.*?\?>/is', '', $code);
    
    // For admins, don't sanitize at all (except PHP removal)
    if (current_user_can('unfiltered_html')) {
        return $code;
    }
    
    // For non-admins, still allow script tags but with basic filtering
    // This is more permissive than wp_kses_post
    $allowed_html = wp_kses_allowed_html('post');
    
    // Add script tag with all possible attributes
    $allowed_html['script'] = array(
        'type' => true,
        'src' => true,
        'async' => true,
        'defer' => true,
        'charset' => true,
        'data-cfasync' => true,
        'data-turbolinks-track' => true,
        'crossorigin' => true,
        'integrity' => true,
        'nonce' => true
    );
    
    // Add ins tag for ad networks
    $allowed_html['ins'] = array(
        'id' => true,
        'class' => true,
        'data-width' => true,
        'data-height' => true,
        'data-zoneid' => true,
        'data-ad-slot' => true
    );
    
    return wp_kses($code, $allowed_html);
}

/**
 * Add custom columns to ads list table
 */
function customtube_add_ad_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        if ($key === 'title') {
            $new_columns['ad_type'] = __('Type', 'customtube');
            $new_columns['ad_status'] = __('Status', 'customtube');
        }
        
        if ($key === 'taxonomy-ad_zone') {
            $new_columns['ad_stats'] = __('Stats', 'customtube');
        }
    }
    
    return $new_columns;
}
add_filter('manage_tube_ad_posts_columns', 'customtube_add_ad_columns');

/**
 * Display custom column content
 */
function customtube_display_ad_columns($column, $post_id) {
    switch ($column) {
        case 'ad_type':
            $ad_type = get_post_meta($post_id, '_ad_type', true) ?: 'code';
            $types = array(
                'code' => __('HTML/JS', 'customtube'),
                'image' => __('Image', 'customtube'),
                'vast' => __('VAST', 'customtube')
            );
            echo isset($types[$ad_type]) ? $types[$ad_type] : ucfirst($ad_type);
            break;
            
        case 'ad_status':
            $status = get_post_meta($post_id, '_ad_status', true) ?: 'active';
            $status_colors = array(
                'active' => '#46b450',
                'inactive' => '#dc3232',
                'paused' => '#ffb900'
            );
            $color = isset($status_colors[$status]) ? $status_colors[$status] : '#666';
            echo '<span style="color: ' . $color . '; font-weight: bold;">' . ucfirst($status) . '</span>';
            break;
            
        case 'ad_stats':
            $impressions = get_post_meta($post_id, '_ad_impressions', true) ?: '0';
            $clicks = get_post_meta($post_id, '_ad_clicks', true) ?: '0';
            $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
            
            echo '<strong>' . __('Impressions:', 'customtube') . '</strong> ' . number_format($impressions) . '<br />';
            echo '<strong>' . __('Clicks:', 'customtube') . '</strong> ' . number_format($clicks) . '<br />';
            echo '<strong>' . __('CTR:', 'customtube') . '</strong> ' . $ctr . '%';
            break;
    }
}
add_action('manage_tube_ad_posts_custom_column', 'customtube_display_ad_columns', 10, 2);

/**
 * Make custom columns sortable
 */
function customtube_sortable_ad_columns($columns) {
    $columns['ad_type'] = 'ad_type';
    $columns['ad_status'] = 'ad_status';
    return $columns;
}
add_filter('manage_edit-tube_ad_sortable_columns', 'customtube_sortable_ad_columns');

/**
 * Handle sorting for custom columns
 */
function customtube_ad_custom_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('ad_type' === $orderby) {
        $query->set('meta_key', '_ad_type');
        $query->set('orderby', 'meta_value');
    } elseif ('ad_status' === $orderby) {
        $query->set('meta_key', '_ad_status');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'customtube_ad_custom_orderby');

?>
