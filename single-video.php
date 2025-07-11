<?php
/**
 * Enhanced Single Video Template
 *
 * Improved layout with two-column design, better spacing,
 * and enhanced visual appearance.
 *
 * @package CustomTube
 */

get_header();
?>

<div id="primary" class="content-area">
    <?php include(get_template_directory() . '/template-parts/category-tags-nav.php'); ?>

    <main id="main" class="site-main">

        <?php while (have_posts()) : the_post();
            // Get video metadata
            $post_id = get_the_ID();
            $video_url = get_post_meta($post_id, 'video_url', true);
            $video_type = get_post_meta($post_id, 'video_type', true);
            $video_id = get_post_meta($post_id, 'video_id', true);
            $duration = get_post_meta($post_id, 'video_duration', true);
            $duration_seconds = get_post_meta($post_id, 'duration_seconds', true);
            $view_count = function_exists('pvc_post_views') ? pvc_get_post_views($post_id) : 0;

            // Get taxonomy terms
            $categories = get_the_category();
            $tags = get_the_tags();
            $performers = get_the_terms($post_id, 'performer');

            // Get related videos for sidebar
            $related_args = array(
                'post_type' => 'video',
                'post__not_in' => array($post_id),
                'posts_per_page' => 4, // Small number for sidebar
                'ignore_sticky_posts' => 1,
                'orderby' => 'rand',
            );

            // Add category or tag filtering to related videos
            $category_ids = array();
            foreach ($categories as $category) {
                $category_ids[] = $category->term_id;
            }

            $tag_ids = array();
            $tags = wp_get_post_tags($post_id);
            foreach ($tags as $tag) {
                $tag_ids[] = $tag->term_id;
            }

            if (!empty($category_ids)) {
                $related_args['category__in'] = $category_ids;
            }

            if (!empty($tag_ids)) {
                $related_args['tag__in'] = $tag_ids;
            }

            // Run the sidebar related query
            $sidebar_related = new WP_Query($related_args);

            // Prepare related videos for bottom grid (more videos)
            // Create a new query that gets more videos, not just those in same categories
            $grid_related_args = array(
                'post_type' => 'video',
                'post__not_in' => array($post_id),
                'posts_per_page' => 12,
                'ignore_sticky_posts' => 1,
                'orderby' => 'rand',
            );

            // Don't use category or tag filtering for "More Videos" to ensure we get enough content
            // Only if we have very few results will we add the category filtering
            $grid_related = new WP_Query($grid_related_args);

            // If we didn't get enough results, try with category filtering
            if ($grid_related->post_count < 6) {
                $grid_related_args['category__in'] = $category_ids;
                $grid_related = new WP_Query($grid_related_args);
            }
        ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class('video-single'); ?>>
                <!-- Video Title & Meta Section -->
                <header class="video-header">
                    <h1 class="video-title"><?php the_title(); ?></h1>

                    <div class="video-meta">
                        <!-- Date Info -->
                        <div class="video-meta-primary">
                            <span class="video-date">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <?php echo get_the_date(); ?>
                            </span>
                        </div>

                        <!-- Views & Duration -->
                        <div class="video-meta-secondary">
                            <?php if (!empty($duration)): ?>
                                <span class="video-duration">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?php echo esc_html($duration); ?>
                                </span>
                            <?php endif; ?>

                            <?php if (function_exists('pvc_post_views')): ?>
                                <span class="video-views">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <?php echo number_format($view_count); ?> views
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>

                <!-- Main Video Player Section -->
                <div class="video-player-container">
                    <?php do_action('customtube_before_video_player'); ?>

                    <?php customtube_video_player(); ?>

                    <?php do_action('customtube_after_video_player'); ?>
                </div>

                <!-- Taxonomies Display below video -->
                <div class="video-taxonomies-wrapper">
                    <?php if (!empty($performers)): ?>
                        <div class="video-performers">
                            <h4>Performers</h4>
                            <div class="performer-list">
                                <?php
                                foreach ($performers as $performer) {
                                    echo '<a href="' . esc_url(get_term_link($performer)) . '" class="performer-tag">' .
                                         esc_html($performer->name) . '</a>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($categories)): ?>
                        <div class="video-categories">
                            <h4>Categories</h4>
                            <div class="category-list">
                                <?php
                                foreach ($categories as $category) {
                                    echo '<a href="' . esc_url(get_category_link($category)) . '" class="category-tag">' .
                                         esc_html($category->name) . '</a>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tags)): ?>
                        <div class="video-tags">
                            <h4>Tags</h4>
                            <div class="tag-list">
                                <?php
                                foreach ($tags as $tag) {
                                    echo '<a href="' . esc_url(get_tag_link($tag)) . '" class="tag-item">' .
                                         esc_html($tag->name) . '</a>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Main Content Column -->
                <div class="video-content-wrapper">
                    <!-- Video Information -->
                    <div class="video-info">
                        <!-- Description with expand/collapse -->
                        <div class="video-description">
                            <?php the_content(); ?>
                        </div>

                        <!-- Rating & Sharing (if available) -->
                        <div class="video-engagement">
                            <?php if (function_exists('the_ratings')): ?>
                                <div class="video-ratings">
                                    <?php the_ratings(); ?>
                                </div>
                            <?php endif; ?>

                            <div class="video-share">
                                <h4>Share This Video</h4>
                                <div class="social-share-buttons">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" class="facebook-share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" class="twitter-share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                                    </a>
                                    <a href="mailto:?subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>" class="email-share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="video-comments-section">
                        <?php
                        // If comments are open or we have at least one comment, load up the comment template.
                        if (comments_open() || get_comments_number()) :
                            comments_template();
                        endif;
                        ?>
                    </div>
                </div>



                <!-- Bottom Related Videos Section (Grid) -->
                <div class="related-videos-section">
                    <h3 class="related-videos-title">More Videos You May Like</h3>

                    <?php
                    // Display grid related videos
                    if ($grid_related->have_posts()) {
                        // Create a reference to the global $post object
                        $original_post = $GLOBALS['post'];

                        // Pass the related videos to the grid
                        $videos = $grid_related;
                        $disable_pagination = true; // Don't show pagination for related videos

                        // Include the specialized grid template for more videos
                        include(get_template_directory() . '/template-parts/more-videos-grid.php');

                        // Reset post data back to the original
                        $GLOBALS['post'] = $original_post;
                        wp_reset_postdata();
                    } else {
                        echo '<p>No more related videos found.</p>';
                    }
                    ?>
                </div>
            </article><!-- #post-<?php the_ID(); ?> -->

        <?php endwhile; ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>

<?php
get_footer();
