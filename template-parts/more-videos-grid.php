<?php
/**
 * Template part: More Videos Grid
 *
 * A specialized grid for displaying "More Videos" on the single video page
 * with better handling for thumbnails and empty states.
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit;
}

// Arguments: $videos (WP_Query or array of post objects)
if (empty($videos) || !$videos->have_posts()) {
    echo '<p class="no-videos">No more videos found.</p>';
    return;
}

// Set a fallback image for videos without thumbnails
$fallback_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIwIiBoZWlnaHQ9IjE4MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzIwIiBoZWlnaHQ9IjE4MCIgZmlsbD0iIzJhMmEyYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiBmaWxsPSIjNjY2IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSIgZm9udC1mYW1pbHk9IkFyaWFsLHNhbnMtc2VyaWYiPk5vIFRodW1ibmFpbDwvdGV4dD48L3N2Zz4=';
?>

<div class="more-videos-grid">
    <?php while ($videos->have_posts()): $videos->the_post(); 
        $post_id = get_the_ID();
        
        // Get video metadata
        $duration = get_post_meta($post_id, 'video_duration', true);
        $duration_seconds = get_post_meta($post_id, 'duration_seconds', true);
        $view_count = function_exists('pvc_post_views') ? pvc_get_post_views($post_id) : 0;
        
        // Get thumbnail
        $thumbnail = get_the_post_thumbnail_url($post_id, 'video-medium');
        if (empty($thumbnail)) {
            $thumbnail = get_post_meta($post_id, 'preview_image_url', true);
        }
        if (empty($thumbnail)) {
            $thumbnail = $fallback_image;
        }
    ?>
    <div class="more-video-item">
        <div class="more-video-thumbnail">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                
                <?php if (!empty($duration)): ?>
                    <div class="video-duration" data-seconds="<?php echo esc_attr($duration_seconds); ?>">
                        <?php echo esc_html($duration); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Play icon overlay - matches homepage style -->
                <div class="overlay-play-icon" aria-hidden="true">
                    <!-- The icon is created using CSS :before pseudo-element -->
                </div>
            </a>
        </div>
        
        <div class="more-video-info">
            <h3 class="more-video-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            
            <div class="more-video-meta">
                <?php if (function_exists('pvc_post_views')): ?>
                    <span class="more-video-views"><?php echo number_format($view_count); ?> views</span>
                <?php endif; ?>
                
                <span class="more-video-date"><?php echo get_the_date(); ?></span>
            </div>
        </div>
    </div>
    <?php endwhile; wp_reset_postdata(); ?>
</div>

<?php
// Add pagination if not specified otherwise
if (!isset($disable_pagination) || !$disable_pagination) {
    get_template_part('template-parts/pagination');
}
?>