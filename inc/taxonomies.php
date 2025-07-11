<?php
/**
 * Custom Taxonomies for CustomTube Theme
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register custom taxonomies for video post type
 */
function customtube_register_taxonomies() {
    // Video Category Taxonomy
    $category_labels = array(
        'name'                       => _x('Video Categories', 'Taxonomy general name', 'customtube'),
        'singular_name'              => _x('Video Category', 'Taxonomy singular name', 'customtube'),
        'search_items'               => __('Search Video Categories', 'customtube'),
        'popular_items'              => __('Popular Video Categories', 'customtube'),
        'all_items'                  => __('All Video Categories', 'customtube'),
        'parent_item'                => __('Parent Video Category', 'customtube'),
        'parent_item_colon'          => __('Parent Video Category:', 'customtube'),
        'edit_item'                  => __('Edit Video Category', 'customtube'),
        'view_item'                  => __('View Video Category', 'customtube'),
        'update_item'                => __('Update Video Category', 'customtube'),
        'add_new_item'               => __('Add New Video Category', 'customtube'),
        'new_item_name'              => __('New Video Category Name', 'customtube'),
        'separate_items_with_commas' => __('Separate video categories with commas', 'customtube'),
        'add_or_remove_items'        => __('Add or remove video categories', 'customtube'),
        'choose_from_most_used'      => __('Choose from the most used video categories', 'customtube'),
        'not_found'                  => __('No video categories found.', 'customtube'),
        'no_terms'                   => __('No video categories', 'customtube'),
        'items_list_navigation'      => __('Video Categories list navigation', 'customtube'),
        'items_list'                 => __('Video Categories list', 'customtube'),
        'back_to_items'              => __('Back to video categories', 'customtube'),
    );

    $category_args = array(
        'labels'            => $category_labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'video-category'),
    );

    register_taxonomy('video_category', 'video', $category_args);

    // Video Tag Taxonomy
    $tag_labels = array(
        'name'                       => _x('Video Tags', 'Taxonomy general name', 'customtube'),
        'singular_name'              => _x('Video Tag', 'Taxonomy singular name', 'customtube'),
        'search_items'               => __('Search Video Tags', 'customtube'),
        'popular_items'              => __('Popular Video Tags', 'customtube'),
        'all_items'                  => __('All Video Tags', 'customtube'),
        'parent_item'                => __('Parent Video Tag', 'customtube'),
        'parent_item_colon'          => __('Parent Video Tag:', 'customtube'),
        'edit_item'                  => __('Edit Video Tag', 'customtube'),
        'view_item'                  => __('View Video Tag', 'customtube'),
        'update_item'                => __('Update Video Tag', 'customtube'),
        'add_new_item'               => __('Add New Video Tag', 'customtube'),
        'new_item_name'              => __('New Video Tag Name', 'customtube'),
        'separate_items_with_commas' => __('Separate video tags with commas', 'customtube'),
        'add_or_remove_items'        => __('Add or remove video tags', 'customtube'),
        'choose_from_most_used'      => __('Choose from the most used video tags', 'customtube'),
        'not_found'                  => __('No video tags found.', 'customtube'),
        'no_terms'                   => __('No video tags', 'customtube'),
        'items_list_navigation'      => __('Video Tags list navigation', 'customtube'),
        'items_list'                 => __('Video Tags list', 'customtube'),
        'back_to_items'              => __('Back to video tags', 'customtube'),
    );

    $tag_args = array(
        'labels'            => $tag_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'video-tag'),
    );

    register_taxonomy('video_tag', 'video', $tag_args);
    
    // Video Duration Taxonomy (for filtering)
    $duration_labels = array(
        'name'                       => _x('Video Durations', 'Taxonomy general name', 'customtube'),
        'singular_name'              => _x('Video Duration', 'Taxonomy singular name', 'customtube'),
        'search_items'               => __('Search Video Durations', 'customtube'),
        'popular_items'              => __('Popular Video Durations', 'customtube'),
        'all_items'                  => __('All Video Durations', 'customtube'),
        'edit_item'                  => __('Edit Video Duration', 'customtube'),
        'view_item'                  => __('View Video Duration', 'customtube'),
        'update_item'                => __('Update Video Duration', 'customtube'),
        'add_new_item'               => __('Add New Video Duration', 'customtube'),
        'new_item_name'              => __('New Video Duration Name', 'customtube'),
        'separate_items_with_commas' => __('Separate video durations with commas', 'customtube'),
        'add_or_remove_items'        => __('Add or remove video durations', 'customtube'),
        'choose_from_most_used'      => __('Choose from the most used video durations', 'customtube'),
        'not_found'                  => __('No video durations found.', 'customtube'),
        'no_terms'                   => __('No video durations', 'customtube'),
        'items_list_navigation'      => __('Video Durations list navigation', 'customtube'),
        'items_list'                 => __('Video Durations list', 'customtube'),
        'back_to_items'              => __('Back to video durations', 'customtube'),
    );

    $duration_args = array(
        'labels'            => $duration_labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => false,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'video-duration'),
    );

    register_taxonomy('video_duration', 'video', $duration_args);
    
    // Performer Taxonomy (with A-Z directory)
    $performer_labels = array(
        'name'                       => _x('Performers', 'Taxonomy general name', 'customtube'),
        'singular_name'              => _x('Performer', 'Taxonomy singular name', 'customtube'),
        'search_items'               => __('Search Performers', 'customtube'),
        'popular_items'              => __('Popular Performers', 'customtube'),
        'all_items'                  => __('All Performers', 'customtube'),
        'edit_item'                  => __('Edit Performer', 'customtube'),
        'view_item'                  => __('View Performer', 'customtube'),
        'update_item'                => __('Update Performer', 'customtube'),
        'add_new_item'               => __('Add New Performer', 'customtube'),
        'new_item_name'              => __('New Performer Name', 'customtube'),
        'separate_items_with_commas' => __('Separate performers with commas', 'customtube'),
        'add_or_remove_items'        => __('Add or remove performers', 'customtube'),
        'choose_from_most_used'      => __('Choose from the most used performers', 'customtube'),
        'not_found'                  => __('No performers found.', 'customtube'),
        'no_terms'                   => __('No performers', 'customtube'),
        'items_list_navigation'      => __('Performers list navigation', 'customtube'),
        'items_list'                 => __('Performers list', 'customtube'),
        'back_to_items'              => __('Back to performers', 'customtube'),
        'menu_name'                  => _x('Performers', 'Admin Menu text', 'customtube'),
    );

    $performer_args = array(
        'labels'            => $performer_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'show_in_rest'      => true,
        'rewrite'           => array(
            'slug' => 'performer',
            'with_front' => true
        ),
        'meta_box_cb'       => 'customtube_performer_meta_box',
        'capabilities'      => array(
            'manage_terms' => 'manage_categories',
            'edit_terms'   => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ),
    );

    register_taxonomy('performer', 'video', $performer_args);

    // Genre Taxonomy
    $genre_labels = array(
        'name'                       => _x('Genres', 'Taxonomy general name', 'customtube'),
        'singular_name'              => _x('Genre', 'Taxonomy singular name', 'customtube'),
        'search_items'               => __('Search Genres', 'customtube'),
        'popular_items'              => __('Popular Genres', 'customtube'),
        'all_items'                  => __('All Genres', 'customtube'),
        'parent_item'                => __('Parent Genre', 'customtube'),
        'parent_item_colon'          => __('Parent Genre:', 'customtube'),
        'edit_item'                  => __('Edit Genre', 'customtube'),
        'view_item'                  => __('View Genre', 'customtube'),
        'update_item'                => __('Update Genre', 'customtube'),
        'add_new_item'               => __('Add New Genre', 'customtube'),
        'new_item_name'              => __('New Genre Name', 'customtube'),
        'separate_items_with_commas' => __('Separate genres with commas', 'customtube'),
        'add_or_remove_items'        => __('Add or remove genres', 'customtube'),
        'choose_from_most_used'      => __('Choose from the most used genres', 'customtube'),
        'not_found'                  => __('No genres found.', 'customtube'),
        'no_terms'                   => __('No genres', 'customtube'),
        'items_list_navigation'      => __('Genres list navigation', 'customtube'),
        'items_list'                 => __('Genres list', 'customtube'),
        'back_to_items'              => __('Back to genres', 'customtube'),
        'menu_name'                  => __('Genres', 'customtube'),
    );

    $genre_args = array(
        'labels'            => $genre_labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'genre'),
    );

    register_taxonomy('genre', 'video', $genre_args);

    // Ad Zone Taxonomy
    $ad_zone_labels = array(
        'name'                       => _x('Ad Zones', 'Taxonomy general name', 'customtube'),
        'singular_name'              => _x('Ad Zone', 'Taxonomy singular name', 'customtube'),
        'search_items'               => __('Search Ad Zones', 'customtube'),
        'popular_items'              => __('Popular Ad Zones', 'customtube'),
        'all_items'                  => __('All Ad Zones', 'customtube'),
        'edit_item'                  => __('Edit Ad Zone', 'customtube'),
        'view_item'                  => __('View Ad Zone', 'customtube'),
        'update_item'                => __('Update Ad Zone', 'customtube'),
        'add_new_item'               => __('Add New Ad Zone', 'customtube'),
        'new_item_name'              => __('New Ad Zone Name', 'customtube'),
        'separate_items_with_commas' => __('Separate ad zones with commas', 'customtube'),
        'add_or_remove_items'        => __('Add or remove ad zones', 'customtube'),
        'choose_from_most_used'      => __('Choose from the most used ad zones', 'customtube'),
        'not_found'                  => __('No ad zones found.', 'customtube'),
        'no_terms'                   => __('No ad zones', 'customtube'),
        'items_list_navigation'      => __('Ad Zones list navigation', 'customtube'),
        'items_list'                 => __('Ad Zones list', 'customtube'),
        'back_to_items'              => __('Back to ad zones', 'customtube'),
        'menu_name'                  => __('Ad Zones', 'customtube'),
    );

    $ad_zone_args = array(
        'labels'            => $ad_zone_labels,
        'hierarchical'      => false,
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => false,
        'show_tagcloud'     => false,
        'show_in_rest'      => true,
        'rewrite'           => false,
    );

    register_taxonomy('ad_zone', 'tube_ad', $ad_zone_args);
}
add_action('init', 'customtube_register_taxonomies');

/**
 * Create default duration terms
 */
function customtube_create_default_durations() {
    $durations = array(
        'short'   => __('Short (0-5 min)', 'customtube'),
        'medium'  => __('Medium (5-20 min)', 'customtube'),
        'long'    => __('Long (20+ min)', 'customtube'),
    );

    foreach ($durations as $slug => $name) {
        if (!term_exists($slug, 'video_duration')) {
            wp_insert_term($name, 'video_duration', array('slug' => $slug));
        }
    }
}
add_action('init', 'customtube_create_default_durations');

/**
 * Create default genre terms
 */
function customtube_create_default_genres() {
    if (!taxonomy_exists('genre')) {
        return;
    }

    $genres = array(
        'amateur'       => __('Amateur', 'customtube'),
        'anal'          => __('Anal', 'customtube'),
        'asian'         => __('Asian', 'customtube'),
        'bbw'           => __('BBW', 'customtube'),
        'big-boobs'     => __('Big Boobs', 'customtube'),
        'big-dick'      => __('Big Dick', 'customtube'),
        'blonde'        => __('Blonde', 'customtube'),
        'blowjob'       => __('Blowjob', 'customtube'),
        'brunette'      => __('Brunette', 'customtube'),
        'compilation'   => __('Compilation', 'customtube'),
        'creampie'      => __('Creampie', 'customtube'),
        'cumshot'       => __('Cumshot', 'customtube'),
        'ebony'         => __('Ebony', 'customtube'),
        'fetish'        => __('Fetish', 'customtube'),
        'gangbang'      => __('Gangbang', 'customtube'),
        'hardcore'      => __('Hardcore', 'customtube'),
        'hd'            => __('HD', 'customtube'),
        'interracial'   => __('Interracial', 'customtube'),
        'latina'        => __('Latina', 'customtube'),
        'lesbian'       => __('Lesbian', 'customtube'),
        'mature'        => __('Mature', 'customtube'),
        'milf'          => __('MILF', 'customtube'),
        'pov'           => __('POV', 'customtube'),
        'public'        => __('Public', 'customtube'),
        'redhead'       => __('Redhead', 'customtube'),
        'solo'          => __('Solo', 'customtube'),
        'squirt'        => __('Squirt', 'customtube'),
        'teen'          => __('Teen', 'customtube'),
        'threesome'     => __('Threesome', 'customtube'),
    );

    foreach ($genres as $slug => $name) {
        if (!term_exists($slug, 'genre')) {
            wp_insert_term($name, 'genre', array('slug' => $slug));
        }
    }
}
add_action('init', 'customtube_create_default_genres');

/**
 * Create default ad zone terms
 */
function customtube_create_default_ad_zones() {
    if (!taxonomy_exists('ad_zone')) {
        return;
    }

    $ad_zones = array(
        'header'        => __('Header', 'customtube'),
        'below-header'  => __('Below Header', 'customtube'),
        'in-content'    => __('In Content', 'customtube'),
        'sidebar'       => __('Sidebar', 'customtube'),
        'footer'        => __('Footer', 'customtube'),
    );

    foreach ($ad_zones as $slug => $name) {
        if (!term_exists($slug, 'ad_zone')) {
            wp_insert_term($name, 'ad_zone', array('slug' => $slug));
        }
    }
}
add_action('init', 'customtube_create_default_ad_zones');

/**
 * Auto-assign duration term based on video duration
 */
function customtube_auto_assign_duration_term($post_id) {
    // Only run on video post type
    if (get_post_type($post_id) !== 'video') {
        return;
    }
    
    // Get video duration
    $duration_string = get_post_meta($post_id, 'video_duration', true);
    if (!$duration_string) {
        return;
    }
    
    // Convert duration string to seconds
    $duration_parts = explode(':', $duration_string);
    $duration_seconds = 0;
    
    if (count($duration_parts) === 3) {
        // HH:MM:SS format
        $duration_seconds = ($duration_parts[0] * 3600) + ($duration_parts[1] * 60) + $duration_parts[2];
    } elseif (count($duration_parts) === 2) {
        // MM:SS format
        $duration_seconds = ($duration_parts[0] * 60) + $duration_parts[1];
    } else {
        // Just seconds
        $duration_seconds = intval($duration_parts[0]);
    }
    
    // Assign the appropriate term
    $term_slug = '';
    if ($duration_seconds <= 300) { // 0-5 minutes
        $term_slug = 'short';
    } elseif ($duration_seconds <= 1200) { // 5-20 minutes
        $term_slug = 'medium';
    } else { // 20+ minutes
        $term_slug = 'long';
    }
    
    // Get the term ID
    $term = get_term_by('slug', $term_slug, 'video_duration');
    if ($term) {
        wp_set_object_terms($post_id, $term->term_id, 'video_duration');
    }
}
add_action('save_post', 'customtube_auto_assign_duration_term');

/**
 * Custom meta box for the Performer taxonomy with A-Z directory
 */
function customtube_performer_meta_box($post, $box) {
    $taxonomy = 'performer';
    $tax = get_taxonomy($taxonomy);
    $selected = wp_get_object_terms($post->ID, $taxonomy, array('fields' => 'ids'));
    $performers = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    
    // Group performers by first letter
    $performer_groups = array();
    foreach ($performers as $performer) {
        $first_letter = strtoupper(substr($performer->name, 0, 1));
        if (!isset($performer_groups[$first_letter])) {
            $performer_groups[$first_letter] = array();
        }
        $performer_groups[$first_letter][] = $performer;
    }
    ksort($performer_groups);
    
    // Get all letters for navigation
    $all_letters = range('A', 'Z');
    $active_letters = array_keys($performer_groups);
    
    // Output the meta box
    ?>
    <div id="taxonomy-<?php echo esc_attr($taxonomy); ?>" class="categorydiv performer-metabox">
        <input type="hidden" name="tax_input[<?php echo esc_attr($taxonomy); ?>][]" value="0" />

        <!-- A-Z Navigation -->
        <div class="performer-az-navigation">
            <?php foreach ($all_letters as $letter) : ?>
                <a href="#performer-letter-<?php echo esc_attr($letter); ?>" 
                   class="<?php echo in_array($letter, $active_letters) ? 'has-performers' : 'no-performers'; ?>">
                   <?php echo esc_html($letter); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Form -->
        <div class="performer-search">
            <input type="text" id="performer-search-input" placeholder="<?php esc_attr_e('Search performers...', 'customtube'); ?>" />
            <div id="performer-search-results" class="performer-search-results"></div>
        </div>

        <div class="tabs-panel performers-tabs-panel">
            <ul class="categorychecklist form-no-clear">
                <?php foreach ($performer_groups as $letter => $letter_performers) : ?>
                    <h4 id="performer-letter-<?php echo esc_attr($letter); ?>" class="performer-letter-heading"><?php echo esc_html($letter); ?></h4>
                    <?php foreach ($letter_performers as $performer) : ?>
                        <li id="performer-<?php echo esc_attr($performer->term_id); ?>">
                            <label class="selectit">
                                <input type="checkbox" name="tax_input[<?php echo esc_attr($taxonomy); ?>][]" 
                                       id="in-<?php echo esc_attr($taxonomy); ?>-<?php echo esc_attr($performer->term_id); ?>" 
                                       value="<?php echo esc_attr($performer->term_id); ?>" 
                                       <?php checked(in_array($performer->term_id, $selected)); ?> />
                                <?php echo esc_html($performer->name); ?>
                                <span class="count">(<?php echo esc_html($performer->count); ?>)</span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Add New Performer -->
        <div class="performer-add-new">
            <label for="new-performer-name"><?php esc_html_e('Add New Performer:', 'customtube'); ?></label>
            <input type="text" id="new-performer-name" placeholder="<?php esc_attr_e('Enter name', 'customtube'); ?>" />
            <button type="button" id="add-new-performer" class="button button-secondary" data-nonce="<?php echo esc_attr(wp_create_nonce('add-performer-nonce')); ?>">
                <?php esc_html_e('Add', 'customtube'); ?>
            </button>
            <span class="spinner"></span>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // A-Z Navigation
        $('.performer-az-navigation a').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            if ($(target).length) {
                $('.performers-tabs-panel').animate({
                    scrollTop: $(target).position().top
                }, 500);
                $(target).addClass('highlighted');
                setTimeout(function() {
                    $(target).removeClass('highlighted');
                }, 2000);
            }
        });

        // Search functionality
        $('#performer-search-input').on('input', function() {
            var searchText = $(this).val().toLowerCase();
            if (searchText.length < 2) {
                $('#performer-search-results').empty();
                return;
            }
            
            var results = [];
            $('.performers-tabs-panel li').each(function() {
                var performerName = $(this).find('label').text().toLowerCase();
                if (performerName.includes(searchText)) {
                    results.push({
                        id: $(this).attr('id'),
                        name: performerName
                    });
                }
            });
            
            var resultsHtml = '';
            if (results.length) {
                resultsHtml = '<ul>';
                results.forEach(function(result) {
                    resultsHtml += '<li data-id="' + result.id + '">' + result.name + '</li>';
                });
                resultsHtml += '</ul>';
            } else {
                resultsHtml = '<p><?php esc_html_e('No performers found.', 'customtube'); ?></p>';
            }
            
            $('#performer-search-results').html(resultsHtml);
        });
        
        // Click on search result
        $(document).on('click', '#performer-search-results li', function() {
            var performerId = $(this).data('id');
            var $target = $('#' + performerId);
            if ($target.length) {
                $('.performers-tabs-panel').animate({
                    scrollTop: $target.position().top
                }, 500);
                $target.addClass('highlighted');
                setTimeout(function() {
                    $target.removeClass('highlighted');
                }, 2000);
                
                // Check the checkbox
                $target.find('input[type="checkbox"]').prop('checked', true);
                
                // Clear search
                $('#performer-search-input').val('');
                $('#performer-search-results').empty();
            }
        });

        // Add new performer via AJAX
        $('#add-new-performer').on('click', function() {
            var performerName = $('#new-performer-name').val().trim();
            if (!performerName) {
                alert('<?php esc_html_e('Please enter a performer name.', 'customtube'); ?>');
                return;
            }
            
            var $button = $(this);
            var $spinner = $button.next('.spinner');
            
            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_add_performer',
                    performer_name: performerName,
                    post_id: <?php echo intval($post->ID); ?>,
                    nonce: $button.data('nonce')
                },
                success: function(response) {
                    if (response.success) {
                        // Reload the meta box
                        $('#taxonomy-<?php echo esc_attr($taxonomy); ?>').html(response.data.metabox);
                        // Show success message
                        alert(response.data.message);
                    } else {
                        alert(response.data.message || '<?php esc_html_e('Error adding performer.', 'customtube'); ?>');
                    }
                },
                error: function() {
                    alert('<?php esc_html_e('An error occurred. Please try again.', 'customtube'); ?>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                    $('#new-performer-name').val('');
                }
            });
        });
    });
    </script>
    <style>
    .performer-metabox {
        max-height: 300px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .performer-az-navigation {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 10px;
        padding: 10px;
        background: #f0f0f0;
        border-radius: 3px;
    }
    .performer-az-navigation a {
        display: inline-block;
        width: 26px;
        height: 26px;
        line-height: 26px;
        text-align: center;
        text-decoration: none;
        background: #fff;
        border-radius: 3px;
    }
    .performer-az-navigation a.has-performers {
        font-weight: bold;
        background: #2271b1;
        color: #fff;
    }
    .performer-az-navigation a.no-performers {
        color: #999;
        cursor: default;
    }
    .performers-tabs-panel {
        overflow-y: auto;
        max-height: 200px;
        padding: 10px;
        border: 1px solid #ddd;
        background: #fff;
    }
    .performer-letter-heading {
        position: sticky;
        top: 0;
        background: #f0f0f0;
        margin: 0 -10px 10px;
        padding: 5px 10px;
        border-bottom: 1px solid #ddd;
    }
    .performer-letter-heading.highlighted {
        background: #ffff9c;
    }
    .performer-search {
        margin-bottom: 10px;
        position: relative;
    }
    .performer-search-results {
        position: absolute;
        z-index: 100;
        background: #fff;
        border: 1px solid #ddd;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .performer-search-results ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .performer-search-results li {
        padding: 5px 10px;
        cursor: pointer;
    }
    .performer-search-results li:hover {
        background: #f0f0f0;
    }
    .performer-add-new {
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .performer-add-new input {
        flex: 1;
    }
    .performer-add-new .spinner {
        float: none;
        margin: 0;
    }
    </style>
    <?php
}

/**
 * AJAX handler for adding new performers
 */
function customtube_add_performer_ajax() {
    check_ajax_referer('add-performer-nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('You do not have permission to do this.', 'customtube')));
    }
    
    $performer_name = isset($_POST['performer_name']) ? sanitize_text_field($_POST['performer_name']) : '';
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (empty($performer_name)) {
        wp_send_json_error(array('message' => __('Performer name cannot be empty.', 'customtube')));
    }
    
    // Check if term already exists
    $term = get_term_by('name', $performer_name, 'performer');
    
    if ($term) {
        // Term exists, assign it to the post
        wp_set_object_terms($post_id, $term->term_id, 'performer', true);
        $message = sprintf(__('Performer "%s" assigned successfully.', 'customtube'), $performer_name);
    } else {
        // Create new term
        $result = wp_insert_term($performer_name, 'performer');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        // Assign term to post
        wp_set_object_terms($post_id, $result['term_id'], 'performer', true);
        $message = sprintf(__('Performer "%s" created and assigned successfully.', 'customtube'), $performer_name);
    }
    
    // Get updated metabox HTML
    ob_start();
    customtube_performer_meta_box(get_post($post_id), array('args' => array('taxonomy' => 'performer')));
    $metabox_html = ob_get_clean();
    
    wp_send_json_success(array(
        'message' => $message,
        'metabox' => $metabox_html
    ));
}
add_action('wp_ajax_customtube_add_performer', 'customtube_add_performer_ajax');

/**
 * Register scripts and styles for performer taxonomy
 */
function customtube_enqueue_performer_admin_scripts() {
    $screen = get_current_screen();
    
    if ($screen && $screen->post_type === 'video') {
        wp_enqueue_style(
            'customtube-performer-admin',
            CUSTOMTUBE_URI . '/assets/css/admin-performers.css',
            array(),
            CUSTOMTUBE_VERSION
        );
    }
}
add_action('admin_enqueue_scripts', 'customtube_enqueue_performer_admin_scripts');