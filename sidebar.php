<?php
/**
 * The sidebar template file
 *
 * @package CustomTube
 */

if (!is_active_sidebar('sidebar-1') && !has_action('customtube_sidebar')) {
    return;
}
?>

<aside id="secondary" class="widget-area sidebar">
    <div class="sidebar-inner">
        <?php
        // Display sidebar ads first
        if (has_action('customtube_sidebar')) {
            do_action('customtube_sidebar');
        }
        
        // Add related videos on single video pages
        if (is_singular('video')) {
            // Get current video ID
            $current_video_id = get_the_ID();
            
            // Get related videos
            $related_args = array(
                'post_type' => 'video',
                'post__not_in' => array($current_video_id),
                'posts_per_page' => 5,
                'ignore_sticky_posts' => 1,
                'orderby' => 'rand',
                'meta_query' => array(
                    array(
                        'key' => 'video_file',
                        'compare' => 'EXISTS'
                    )
                )
            );
            
            // Try to get videos from same categories
            $categories = get_the_category($current_video_id);
            if (!empty($categories)) {
                $category_ids = array();
                foreach ($categories as $category) {
                    $category_ids[] = $category->term_id;
                }
                $related_args['category__in'] = $category_ids;
            }
            
            $related_query = new WP_Query($related_args);
            
            if ($related_query->have_posts()) {
                echo '<div class="sidebar-section related-videos">';
                echo '<h3 class="sidebar-section-title">Related Videos</h3>';
                
                while ($related_query->have_posts()) {
                    $related_query->the_post();
                    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                    $duration = get_post_meta(get_the_ID(), 'video_duration', true);
                    $views = get_post_meta(get_the_ID(), 'video_views', true);
                    
                    echo '<div class="sidebar-video-item">';
                    echo '<div class="sidebar-video-thumbnail">';
                    echo '<a href="' . get_permalink() . '">';
                    echo '<img src="' . esc_url($thumb ? $thumb : CUSTOMTUBE_URI . '/assets/images/default-video.jpg') . '" alt="' . get_the_title() . '">';
                    
                    if (!empty($duration)) {
                        echo '<div class="video-duration">' . esc_html($duration) . '</div>';
                    }
                    
                    echo '</a>';
                    echo '</div>';
                    echo '<div class="sidebar-video-info">';
                    echo '<h4 class="sidebar-video-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4>';
                    
                    if ($views) {
                        echo '<div class="sidebar-video-meta">';
                        echo '<span class="views">' . number_format(intval($views)) . ' views</span>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
                wp_reset_postdata();
            }
        }
        
        // Then display any widgets
        if (is_active_sidebar('sidebar-1')) {
            dynamic_sidebar('sidebar-1');
        }
        ?>
    </div>
</aside><!-- #secondary -->
