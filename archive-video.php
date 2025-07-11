<?php
/**
 * Archive template for videos
 *
 * @package CustomTube
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <header class="page-header">
            <?php
            if (is_tax('video_category')) {
                $term = get_queried_object();
                ?>
                <h1 class="page-title"><?php echo esc_html($term->name); ?> <?php esc_html_e('Videos', 'customtube'); ?></h1>
                <?php if (!empty($term->description)) : ?>
                    <div class="archive-description"><?php echo wp_kses_post($term->description); ?></div>
                <?php endif; ?>
                <?php
            } elseif (is_tax('video_tag')) {
                $term = get_queried_object();
                ?>
                <h1 class="page-title"><?php esc_html_e('Videos Tagged:', 'customtube'); ?> <?php echo esc_html($term->name); ?></h1>
                <?php if (!empty($term->description)) : ?>
                    <div class="archive-description"><?php echo wp_kses_post($term->description); ?></div>
                <?php endif; ?>
                <?php
            } elseif (is_tax('video_duration')) {
                $term = get_queried_object();
                ?>
                <h1 class="page-title"><?php echo esc_html($term->name); ?> <?php esc_html_e('Videos', 'customtube'); ?></h1>
                <?php if (!empty($term->description)) : ?>
                    <div class="archive-description"><?php echo wp_kses_post($term->description); ?></div>
                <?php endif; ?>
                <?php
            } elseif (is_post_type_archive('video')) {
                ?>
                <h1 class="page-title"><?php esc_html_e('All Videos', 'customtube'); ?></h1>
                <?php
            } elseif (is_search()) {
                ?>
                <h1 class="page-title">
                    <?php
                    /* translators: %s: search query */
                    printf(esc_html__('Search Results for: %s', 'customtube'), '<span>' . get_search_query() . '</span>');
                    ?>
                </h1>
                <?php
            }
            ?>
            
            <div class="archive-filters">
                <div class="filter-toggle">
                    <button class="filter-button">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                            <path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z" fill="currentColor"/>
                        </svg>
                        <?php esc_html_e('Filter & Sort', 'customtube'); ?>
                    </button>
                </div>
                
                <div class="filter-panel">
                    <form method="get" action="<?php echo esc_url(get_post_type_archive_link('video')); ?>" class="filter-form">
                        <?php if (get_search_query()) : ?>
                            <input type="hidden" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
                        <?php endif; ?>
                        
                        <div class="filter-group">
                            <label for="duration-filter"><?php esc_html_e('Duration', 'customtube'); ?></label>
                            <select name="duration" id="duration-filter">
                                <option value=""><?php esc_html_e('Any Duration', 'customtube'); ?></option>
                                <option value="short" <?php selected(isset($_GET['duration']) && $_GET['duration'] === 'short'); ?>><?php esc_html_e('Short (0-5 min)', 'customtube'); ?></option>
                                <option value="medium" <?php selected(isset($_GET['duration']) && $_GET['duration'] === 'medium'); ?>><?php esc_html_e('Medium (5-20 min)', 'customtube'); ?></option>
                                <option value="long" <?php selected(isset($_GET['duration']) && $_GET['duration'] === 'long'); ?>><?php esc_html_e('Long (20+ min)', 'customtube'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="orderby-filter"><?php esc_html_e('Sort By', 'customtube'); ?></label>
                            <select name="orderby" id="orderby-filter">
                                <option value="date" <?php selected(!isset($_GET['orderby']) || $_GET['orderby'] === 'date'); ?>><?php esc_html_e('Newest', 'customtube'); ?></option>
                                <option value="views" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] === 'views'); ?>><?php esc_html_e('Most Viewed', 'customtube'); ?></option>
                                <option value="likes" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] === 'likes'); ?>><?php esc_html_e('Most Liked', 'customtube'); ?></option>
                                <option value="title" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] === 'title'); ?>><?php esc_html_e('Title', 'customtube'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="apply-filters"><?php esc_html_e('Apply Filters', 'customtube'); ?></button>
                            <a href="<?php echo esc_url(remove_query_arg(array('duration', 'orderby'))); ?>" class="reset-filters"><?php esc_html_e('Reset', 'customtube'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </header><!-- .page-header -->
        
        <?php
        // Apply filters to the main query if needed
        if (!is_admin() && (isset($_GET['duration']) || isset($_GET['orderby']))) {
            add_action('pre_get_posts', 'customtube_filter_archive_query');
        }
        ?>
        
        <?php
        // Add the filtered class for consistent styling when filters are applied
        if (isset($_GET['duration']) || isset($_GET['orderby'])) {
            echo '<div class="filtered-results">';
            customtube_video_grid(null, ''); // Use the main query
            echo '</div>';
        } else {
            customtube_video_grid(null, ''); // Use the main query
        }
        ?>
        
    </main><!-- #main -->
</div><!-- #primary -->

<?php
/**
 * Filter the archive query based on user selections
 *
 * @param WP_Query $query The query object
 */
function customtube_filter_archive_query($query) {
    if (!$query->is_main_query() || !is_post_type_archive('video')) {
        return;
    }
    
    // Filter by duration
    if (isset($_GET['duration']) && !empty($_GET['duration'])) {
        $tax_query = array(
            array(
                'taxonomy' => 'video_duration',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['duration']),
            ),
        );
        
        $existing_tax_query = $query->get('tax_query');
        if (!empty($existing_tax_query)) {
            $tax_query = array_merge($existing_tax_query, $tax_query);
            $tax_query['relation'] = 'AND';
        }
        
        $query->set('tax_query', $tax_query);
    }
    
    // Order by selected option
    if (isset($_GET['orderby']) && !empty($_GET['orderby'])) {
        switch ($_GET['orderby']) {
            case 'views':
                $query->set('meta_key', 'video_views');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
                
            case 'likes':
                $query->set('meta_key', 'video_likes');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
                
            case 'title':
                $query->set('orderby', 'title');
                $query->set('order', 'ASC');
                break;
                
            case 'date':
            default:
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
        }
    }
}

get_footer();
