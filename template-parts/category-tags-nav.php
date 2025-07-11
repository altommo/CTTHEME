<?php
/**
 * Template part for displaying smart category navigation with trending tags
 *
 * @package CustomTube
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get popular tags based on post count and recency
 * 
 * @param int $count Number of tags to retrieve
 * @return array Array of tag objects
 */
function customtube_get_popular_tags($count = 12) {
    // Get tags with most videos
    $tags = get_terms(array(
        'taxonomy'   => 'post_tag',
        'hide_empty' => true,
        'number'     => $count * 2, // Get more than needed to filter
        'orderby'    => 'count',
        'order'      => 'DESC',
    ));
    
    // If no tags, return empty array
    if (empty($tags) || is_wp_error($tags)) {
        return array();
    }
    
    // Filter tags based on recent usage (posts in the last 30 days)
    $filtered_tags = array();
    foreach ($tags as $tag) {
        // Get recent posts with this tag
        $recent_posts = get_posts(array(
            'post_type'      => 'video',
            'posts_per_page' => 1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'post_tag',
                    'field'    => 'term_id',
                    'terms'    => $tag->term_id,
                ),
            ),
            'date_query'     => array(
                array(
                    'after' => '30 days ago',
                ),
            ),
        ));
        
        // If there are recent posts with this tag, add it to filtered tags
        if (!empty($recent_posts)) {
            $filtered_tags[] = $tag;
        }
        
        // If we have enough tags, stop
        if (count($filtered_tags) >= $count) {
            break;
        }
    }
    
    // If we don't have enough filtered tags, add more from the original list
    if (count($filtered_tags) < $count && count($tags) > count($filtered_tags)) {
        $needed = $count - count($filtered_tags);
        $existing_ids = array_map(function($tag) { return $tag->term_id; }, $filtered_tags);
        
        foreach ($tags as $tag) {
            if (!in_array($tag->term_id, $existing_ids)) {
                $filtered_tags[] = $tag;
                $needed--;
            }
            
            if ($needed <= 0) {
                break;
            }
        }
    }
    
    return $filtered_tags;
}

// Get popular tags and categories
$popular_tags = customtube_get_popular_tags(12);
$popular_categories = customtube_get_popular_categories(8);

// Only show if we have tags or categories
if (!empty($popular_tags) || !empty($popular_categories)) :
?>
<div class="smart-category-navigation">
    <div class="smart-navigation-container">
        <?php if (!empty($popular_categories)) : ?>
            <div class="popular-categories">
                <ul class="category-list">
                    <?php foreach ($popular_categories as $category) : ?>
                        <li class="category-item">
                            <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-link">
                                <span class="category-icon">
                                    <!-- SVG icon based on category name (first letter) -->
                                    <svg viewBox="0 0 24 24" width="16" height="16">
                                        <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.2"/>
                                        <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="currentColor">
                                            <?php echo esc_html(substr($category->name, 0, 1)); ?>
                                        </text>
                                    </svg>
                                </span>
                                <?php echo esc_html($category->name); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="category-item more-categories">
                        <a href="<?php echo esc_url(get_post_type_archive_link('video')); ?>" class="category-link">
                            <span class="category-icon">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.2"/>
                                    <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="currentColor">
                                        +
                                    </text>
                                </svg>
                            </span>
                            <?php esc_html_e('More', 'customtube'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($popular_tags)) : ?>
            <div class="trending-tags">
                <div class="tags-header">
                    <span class="trending-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                            <path d="M17.66 8L12 2.35 6.34 8C4.78 9.56 4 11.64 4 13.64s.78 4.11 2.34 5.67 3.61 2.35 5.66 2.35 4.1-.78 5.66-2.35S20 15.64 20 13.64 19.22 9.56 17.66 8zM6 14c.01-2 .62-3.27 1.76-4.4L12 5.27l4.24 4.38C17.38 10.77 17.99 12 18 14H6z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="trending-label"><?php esc_html_e('Trending:', 'customtube'); ?></span>
                </div>
                <ul class="tags-list">
                    <?php foreach ($popular_tags as $tag) : ?>
                        <li class="tag-item">
                            <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="tag-link">
                                <?php echo esc_html($tag->name); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>