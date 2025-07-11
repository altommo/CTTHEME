<?php
/**
 * Template part for displaying video thumbnails with preview functionality
 *
 * @package CustomTube
 */

// Get video data
$post_id = get_the_ID();
$video_source_type = get_post_meta($post_id, 'video_source_type', true);
$video_url = get_post_meta($post_id, 'video_url', true);
$platform = get_post_meta($post_id, 'video_type', true);

// If the platform is empty but we have a URL, detect it
if (empty($platform) && !empty($video_url) && function_exists('customtube_detect_platform_from_url')) {
    $platform = customtube_detect_platform_from_url($video_url);
}

// Get preview data
$preview_video_url = get_post_meta($post_id, 'preview_video_url', true);
$preview_image_url = get_post_meta($post_id, 'preview_image_url', true);

// If we don't have preview data, try to extract it
if (empty($preview_video_url) && empty($preview_image_url) && function_exists('customtube_extract_preview_urls') && !empty($video_url)) {
    $preview_data = customtube_extract_preview_urls($video_url, $platform);
    if (!empty($preview_data['videos'][0])) {
        $preview_video_url = $preview_data['videos'][0];
        update_post_meta($post_id, 'preview_video_url', $preview_video_url);
    }
    if (!empty($preview_data['images'][0])) {
        $preview_image_url = $preview_data['images'][0];
        update_post_meta($post_id, 'preview_image_url', $preview_image_url);
    }
}

// Video duration - use new display format with units and store duration in seconds as data attribute
$duration_seconds = get_post_meta($post_id, 'duration_seconds', true);
$duration_seconds = !empty($duration_seconds) ? intval($duration_seconds) : 0;

if (function_exists('customtube_get_formatted_duration')) {
    $video_duration = customtube_get_formatted_duration($post_id);
} else {
    $video_duration = get_post_meta($post_id, 'video_duration', true);
}

// Get post thumbnail
$has_thumbnail = has_post_thumbnail();
$thumbnail_url = $has_thumbnail ? get_the_post_thumbnail_url($post_id, isset($size) ? $size : 'video-medium') : '';

// If we have a preview image but no thumbnail, use the preview image as the thumbnail
if (empty($thumbnail_url) && !empty($preview_image_url)) {
    $thumbnail_url = $preview_image_url;
    $has_thumbnail = true;
}

// If we still don't have a thumbnail, try to extract preview data again
if (empty($thumbnail_url) && function_exists('customtube_extract_preview_urls') && !empty($video_url)) {
    $preview_data = customtube_extract_preview_urls($video_url, $platform);
    if (!empty($preview_data['images'][0])) {
        $thumbnail_url = $preview_data['images'][0];
        update_post_meta($post_id, 'preview_image_url', $thumbnail_url);
        $has_thumbnail = true;
    }
}

// Prepare data attributes for the thumbnail container
$data_attrs = array(
    'data-video-id' => $post_id,
    'data-video-type' => $platform ?: 'unknown',
);

// Add preview-specific data attributes if available
if (!empty($preview_video_url)) {
    $data_attrs['data-preview-src'] = esc_attr($preview_video_url);
}

if (!empty($preview_image_url)) {
    $data_attrs['data-webp-preview-src'] = esc_attr($preview_image_url);
}

// Build the HTML attributes string
$data_attrs_html = '';
foreach ($data_attrs as $key => $value) {
    $data_attrs_html .= ' ' . $key . '="' . $value . '"';
}

// Container classes
$container_class = 'video-thumbnail-container';
if ($platform) {
    $container_class .= ' platform-' . $platform;
}

// Additional metadata to display
$view_count = function_exists('pvc_post_views') ? pvc_get_post_views($post_id) : 0;
$tags = get_the_tags($post_id);
$categories = get_the_category($post_id);
$performers = get_the_terms($post_id, 'performer');

// Prepare date and views for consistent output
$date = get_the_date();
$date_iso = get_the_date('c');
$views = number_format($view_count);
$tag_names = !empty($tags) ? wp_list_pluck($tags, 'name') : [];
?>

<article class="video-card" tabindex="0" data-video-id="<?php echo esc_attr($post_id); ?>">
    <div class="video-thumbnail-container<?php echo ' ' . esc_attr($platform ? 'platform-' . $platform : ''); ?>"<?php echo $data_attrs_html; ?>>
        <?php if (!empty($video_duration)): ?>
            <div class="video-duration" data-seconds="<?php echo esc_attr($duration_seconds); ?>"><?php echo esc_html($video_duration); ?></div>
        <?php endif; ?>

        <?php if ($has_thumbnail): ?>
            <!-- Single Play Icon - Include the designated template -->
            <?php include(get_template_directory() . '/template-parts/play-icon.php'); ?>

            <!-- Clickable Thumbnail Link - Always on top via z-index -->
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="thumbnail-link">
                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="thumbnail-static">
            </a>
        <?php endif; ?>
    </div>

    <div class="video-card-content">
        <!-- Video Title -->
        <h3 class="video-title">
            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
                <?php the_title(); ?>
            </a>
        </h3>

        <!-- Video Meta Information - Always present for consistent height -->
        <div class="video-meta">
            <?php if ($date): ?>
                <time datetime="<?php echo esc_attr($date_iso); ?>" class="video-date">
                    <?php echo esc_html($date); ?>
                </time>
            <?php endif; ?>

            <?php if (function_exists('pvc_post_views')): ?>
                <span class="video-views">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <?php echo $views; ?>
                </span>
            <?php endif; ?>

            <?php if (function_exists('customtube_is_video_liked_by_user')): ?>
                <div class="video-like-button">
                    <button type="button" data-post-id="<?php echo get_the_ID(); ?>" class="<?php echo customtube_is_video_liked_by_user(get_the_ID()) ? 'liked' : ''; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?php echo customtube_is_video_liked_by_user(get_the_ID()) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <?php 
                        $likes_count = get_post_meta(get_the_ID(), 'video_likes', true);
                        $likes_count = is_array($likes_count) ? count($likes_count) : 0;
                        echo $likes_count;
                        ?>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (!empty($tag_names)): ?>
                <span class="video-tags">
                    <?php echo esc_html(implode(', ', $tag_names)); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($performers) || !empty($categories)): // Only show this div if there's content for it ?>
            <div class="video-taxonomies">
                <?php
                $tax_links = array();
                $total_tags = 0;
                $max_tags = 3; // Maximum tags to display

                // Add performers (limit to 1 if we have other taxonomies)
                if (!empty($performers)) {
                    $performer = reset($performers); // Get first performer
                    $tax_links[] = '<a href="' . esc_url(get_term_link($performer)) . '" class="video-taxonomy-tag performer-tag">' . esc_html($performer->name) . '</a>';
                    $total_tags++;
                }

                // Add categories (limit to 1)
                if (!empty($categories) && $total_tags < $max_tags) {
                    $category = reset($categories); // Get first category
                    $tax_links[] = '<a href="' . esc_url(get_category_link($category)) . '" class="video-taxonomy-tag category-tag">' . esc_html($category->name) . '</a>';
                    $total_tags++;
                }

                echo implode(' ', $tax_links);
                ?>
            </div>
        <?php endif; ?>
    </div>
</article>
