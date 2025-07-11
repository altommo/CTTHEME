<?php
/**
 * Custom Post Types for CustomTube Theme
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Video Custom Post Type
 */
function customtube_register_post_types() {
    // Video Post Type
    $labels = array(
        'name'                  => _x('Videos', 'Post type general name', 'customtube'),
        'singular_name'         => _x('Video', 'Post type singular name', 'customtube'),
        'menu_name'             => _x('Videos', 'Admin Menu text', 'customtube'),
        'name_admin_bar'        => _x('Video', 'Add New on Toolbar', 'customtube'),
        'add_new'               => __('Add New', 'customtube'),
        'add_new_item'          => __('Add New Video', 'customtube'),
        'new_item'              => __('New Video', 'customtube'),
        'edit_item'             => __('Edit Video', 'customtube'),
        'view_item'             => __('View Video', 'customtube'),
        'all_items'             => __('All Videos', 'customtube'),
        'search_items'          => __('Search Videos', 'customtube'),
        'parent_item_colon'     => __('Parent Videos:', 'customtube'),
        'not_found'             => __('No videos found.', 'customtube'),
        'not_found_in_trash'    => __('No videos found in Trash.', 'customtube'),
        'featured_image'        => _x('Video Thumbnail', 'Overrides the "Featured Image" phrase', 'customtube'),
        'set_featured_image'    => _x('Set video thumbnail', 'Overrides the "Set featured image" phrase', 'customtube'),
        'remove_featured_image' => _x('Remove video thumbnail', 'Overrides the "Remove featured image" phrase', 'customtube'),
        'use_featured_image'    => _x('Use as video thumbnail', 'Overrides the "Use as featured image" phrase', 'customtube'),
        'archives'              => _x('Video archives', 'The post type archive label used in nav menus', 'customtube'),
        'insert_into_item'      => _x('Insert into video', 'Overrides the "Insert into post" phrase', 'customtube'),
        'uploaded_to_this_item' => _x('Uploaded to this video', 'Overrides the "Uploaded to this post" phrase', 'customtube'),
        'filter_items_list'     => _x('Filter videos list', 'Screen reader text for the filter links', 'customtube'),
        'items_list_navigation' => _x('Videos list navigation', 'Screen reader text for the pagination', 'customtube'),
        'items_list'            => _x('Videos list', 'Screen reader text for the items list', 'customtube'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array(
            'slug'       => 'video',  // Changed from 'videos' to 'video' to match our rewrite rule
            'with_front' => false,
        ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-video-alt3',
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'       => true,
    );

    register_post_type('video', $args);

    // Ad Post Type
    $ad_labels = array(
        'name'                  => _x('Ads', 'Post type general name', 'customtube'),
        'singular_name'         => _x('Ad', 'Post type singular name', 'customtube'),
        'menu_name'             => _x('Ads', 'Admin Menu text', 'customtube'),
        'name_admin_bar'        => _x('Ad', 'Add New on Toolbar', 'customtube'),
        'add_new'               => __('Add New', 'customtube'),
        'add_new_item'          => __('Add New Ad', 'customtube'),
        'new_item'              => __('New Ad', 'customtube'),
        'edit_item'             => __('Edit Ad', 'customtube'),
        'view_item'             => __('View Ad', 'customtube'),
        'all_items'             => __('All Ads', 'customtube'),
        'search_items'          => __('Search Ads', 'customtube'),
        'parent_item_colon'     => __('Parent Ads:', 'customtube'),
        'not_found'             => __('No ads found.', 'customtube'),
        'not_found_in_trash'    => __('No ads found in Trash.', 'customtube'),
    );

    $ad_args = array(
        'labels'             => $ad_labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 60,
        'menu_icon'          => 'dashicons-megaphone',
        'supports'           => array('title'),
    );

    register_post_type('tube_ad', $ad_args);
}
add_action('init', 'customtube_register_post_types');

/**
 * Register Video Meta Fields
 */
function customtube_register_video_meta() {
    // Video URL
    register_post_meta('video', 'video_url', array(
        'show_in_rest'      => true,
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    // Embed code for third-party videos
    register_post_meta('video', 'embed_code', array(
        'show_in_rest'      => true,
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'wp_kses_post', // Allow necessary HTML for embeds
    ));
    
    // Video source type (direct/embed)
    register_post_meta('video', 'video_source_type', array(
        'show_in_rest'      => true,
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'direct',
    ));
    
    // Video Duration
    register_post_meta('video', 'video_duration', array(
        'show_in_rest'      => true,
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    // Duration in seconds (for sorting and filtering)
    register_post_meta('video', 'duration_seconds', array(
        'show_in_rest'      => true,
        'type'              => 'integer',
        'single'            => true,
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ));
    
    // Video Width
    register_post_meta('video', 'video_width', array(
        'show_in_rest'      => true,
        'type'              => 'integer',
        'single'            => true,
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ));
    
    // Video Height
    register_post_meta('video', 'video_height', array(
        'show_in_rest'      => true,
        'type'              => 'integer',
        'single'            => true,
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ));
    
    // Video Filesize (in bytes)
    register_post_meta('video', 'video_filesize', array(
        'show_in_rest'      => true,
        'type'              => 'integer',
        'single'            => true,
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ));
    
    // Video Source
    register_post_meta('video', 'video_source', array(
        'show_in_rest'      => true,
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    // Video Source URL
    register_post_meta('video', 'video_source_url', array(
        'show_in_rest'      => true,
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    // Video Views
    register_post_meta('video', 'video_views', array(
        'show_in_rest'      => true,
        'type'              => 'integer',
        'single'            => true,
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ));
    
    // Video Likes (array of user IDs)
    register_post_meta('video', 'video_likes', array(
        'show_in_rest'      => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'integer'
                )
            )
        ),
        'type'              => 'array',
        'single'            => true,
        'default'           => array(),
    ));
    
    // Video Preview URL
    register_post_meta('video', 'preview_url', array(
        'show_in_rest'      => true,
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    // Video Quality Options
    register_post_meta('video', 'quality_options', array(
        'show_in_rest'      => array(
            'schema' => array(
                'type'       => 'object',
                'properties' => array(
                    '360p'  => array('type' => 'string'),
                    '720p'  => array('type' => 'string'),
                    '1080p' => array('type' => 'string')
                )
            )
        ),
        'type'              => 'object',
        'single'            => true,
        'default'           => array(
            '360p'  => '',
            '720p'  => '',
            '1080p' => '',
        ),
    ));
    
    // Last fetched metadata timestamp
    register_post_meta('video', 'metadata_last_fetched', array(
        'show_in_rest'      => true,
        'type'              => 'integer',
        'single'            => true,
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ));
}
add_action('init', 'customtube_register_video_meta');

/**
 * Change "Enter title here" placeholder for video post type
 */
function customtube_change_title_placeholder($title, $post) {
    if ('video' === $post->post_type) {
        return __('Enter video title here', 'customtube');
    }
    return $title;
}
add_filter('enter_title_here', 'customtube_change_title_placeholder', 10, 2);

/**
 * Add custom columns to video post type admin
 */
function customtube_add_video_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key == 'title') {
            $new_columns[$key] = $value;
            $new_columns['thumbnail'] = __('Thumbnail', 'customtube');
        } elseif ($key == 'date') {
            $new_columns['duration'] = __('Duration', 'customtube');
            $new_columns['views'] = __('Views', 'customtube');
            $new_columns[$key] = $value;
        } else {
            $new_columns[$key] = $value;
        }
    }
    
    return $new_columns;
}
add_filter('manage_video_posts_columns', 'customtube_add_video_columns');

/**
 * Display content for custom columns in video post type admin
 */
function customtube_display_video_columns($column, $post_id) {
    switch ($column) {
        case 'thumbnail':
            if (has_post_thumbnail($post_id)) {
                echo '<img src="' . esc_url(get_the_post_thumbnail_url($post_id, 'thumbnail')) . '" width="80" height="45" />';
            } else {
                echo '<div style="width:80px;height:45px;background:#f0f0f0;"></div>';
            }
            break;
        
        case 'duration':
            $duration = get_post_meta($post_id, 'video_duration', true);
            echo $duration ? esc_html($duration) : '—';
            break;
        
        case 'views':
            $views = get_post_meta($post_id, 'video_views', true);
            echo $views ? esc_html(number_format($views)) : '0';
            break;
    }
}
add_action('manage_video_posts_custom_column', 'customtube_display_video_columns', 10, 2);

/**
 * Make custom columns sortable
 */
function customtube_sortable_video_columns($columns) {
    $columns['views'] = 'views';
    $columns['duration'] = 'duration';
    return $columns;
}
add_filter('manage_edit-video_sortable_columns', 'customtube_sortable_video_columns');

/**
 * Handle sorting of custom columns
 */
function customtube_video_custom_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('views' === $orderby) {
        $query->set('meta_key', 'video_views');
        $query->set('orderby', 'meta_value_num');
    }
    
    if ('duration' === $orderby) {
        $query->set('meta_key', 'video_duration');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'customtube_video_custom_orderby');
