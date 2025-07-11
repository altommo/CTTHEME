<?php
/**
 * Template part: Video Grid
 *
 * Displays a responsive grid of video thumbnails with hover-preview
 * 
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit;
}

// Arguments: $videos (WP_Query or array of post objects)
if (empty($videos)) {
    echo '<p class="no-videos">' . esc_html__('No videos found.', 'customtube') . '</p>';
    return;
}

// Force CSS grid layout which is more reliable for responsive layouts
?>

<div class="video-grid-container">
    <div class="video-grid">
        <?php foreach ($videos as $post): setup_postdata($post); ?>
            <div class="video-grid-item">
                <?php include(get_template_directory() . '/template-parts/thumbnail-preview.php'); ?>
            </div>
        <?php endforeach; wp_reset_postdata(); ?>
    </div>
    
    <?php
    // Add pagination if not specified otherwise
    if (!isset($disable_pagination) || !$disable_pagination) {
        get_template_part('template-parts/pagination');
    }
    ?>
</div>