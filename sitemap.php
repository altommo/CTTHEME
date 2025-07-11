<?php
/**
 * Template Name: Sitemap
 *
 * @package CustomTube
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header><!-- .entry-header -->

            <div class="entry-content">
                <?php the_content(); ?>

                <div class="sitemap-container">
                    <section class="sitemap-section">
                        <h2><?php _e('Videos', 'customtube'); ?></h2>
                        <ul class="sitemap-list">
                            <?php
                            $videos = get_posts(array(
                                'post_type'      => 'video',
                                'posts_per_page' => -1,
                                'post_status'    => 'publish',
                                'orderby'        => 'date',
                                'order'          => 'DESC',
                            ));

                            foreach ($videos as $video) :
                                $date = get_the_date('F j, Y', $video->ID);
                                $views = get_post_meta($video->ID, 'video_views', true);
                                $views = $views ? number_format(intval($views)) : '0';
                            ?>
                                <li class="sitemap-item">
                                    <a href="<?php echo get_permalink($video->ID); ?>"><?php echo get_the_title($video->ID); ?></a>
                                    <span class="sitemap-meta">
                                        <span class="sitemap-date"><?php echo $date; ?></span>
                                        <span class="sitemap-views"><?php echo $views; ?> <?php _e('views', 'customtube'); ?></span>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="sitemap-section">
                        <h2><?php _e('Genres', 'customtube'); ?></h2>
                        <ul class="sitemap-list">
                            <?php
                            $genres = get_terms(array(
                                'taxonomy'   => 'genre',
                                'hide_empty' => true,
                                'orderby'    => 'name',
                                'order'      => 'ASC',
                            ));

                            foreach ($genres as $genre) :
                                $count = $genre->count;
                            ?>
                                <li class="sitemap-item">
                                    <a href="<?php echo get_term_link($genre); ?>"><?php echo $genre->name; ?></a>
                                    <span class="sitemap-meta">
                                        <span class="sitemap-count"><?php echo $count; ?> <?php echo _n('video', 'videos', $count, 'customtube'); ?></span>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="sitemap-section">
                        <h2><?php _e('Performers', 'customtube'); ?></h2>
                        <ul class="sitemap-list">
                            <?php
                            $performers = get_terms(array(
                                'taxonomy'   => 'performer',
                                'hide_empty' => true,
                                'orderby'    => 'name',
                                'order'      => 'ASC',
                            ));

                            foreach ($performers as $performer) :
                                $count = $performer->count;
                            ?>
                                <li class="sitemap-item">
                                    <a href="<?php echo get_term_link($performer); ?>"><?php echo $performer->name; ?></a>
                                    <span class="sitemap-meta">
                                        <span class="sitemap-count"><?php echo $count; ?> <?php echo _n('video', 'videos', $count, 'customtube'); ?></span>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="sitemap-section">
                        <h2><?php _e('Tags', 'customtube'); ?></h2>
                        <ul class="sitemap-list sitemap-tags">
                            <?php
                            $tags = get_terms(array(
                                'taxonomy'   => 'post_tag',
                                'hide_empty' => true,
                                'orderby'    => 'count',
                                'order'      => 'DESC',
                            ));

                            foreach ($tags as $tag) :
                                $count = $tag->count;
                            ?>
                                <li class="sitemap-tag">
                                    <a href="<?php echo get_term_link($tag); ?>"><?php echo $tag->name; ?></a>
                                    <span class="sitemap-count"><?php echo $count; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="sitemap-section">
                        <h2><?php _e('Pages', 'customtube'); ?></h2>
                        <ul class="sitemap-list">
                            <?php
                            $pages = get_pages(array(
                                'sort_column' => 'menu_order',
                                'sort_order'  => 'ASC',
                            ));

                            foreach ($pages as $page) :
                                $date = get_the_date('F j, Y', $page->ID);
                            ?>
                                <li class="sitemap-item">
                                    <a href="<?php echo get_permalink($page->ID); ?>"><?php echo get_the_title($page->ID); ?></a>
                                    <span class="sitemap-meta">
                                        <span class="sitemap-date"><?php echo $date; ?></span>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="sitemap-section">
                        <h2><?php _e('XML Sitemaps', 'customtube'); ?></h2>
                        <ul class="sitemap-list">
                            <li class="sitemap-item">
                                <a href="<?php echo home_url('video-sitemap.xml'); ?>"><?php _e('Video Sitemap (XML)', 'customtube'); ?></a>
                                <span class="sitemap-meta">
                                    <span class="sitemap-info"><?php _e('For search engines', 'customtube'); ?></span>
                                </span>
                            </li>
                            <?php if (function_exists('get_sitemap_url')) : ?>
                                <li class="sitemap-item">
                                    <a href="<?php echo get_sitemap_url(); ?>"><?php _e('WordPress Sitemap (XML)', 'customtube'); ?></a>
                                    <span class="sitemap-meta">
                                        <span class="sitemap-info"><?php _e('Core WordPress sitemap', 'customtube'); ?></span>
                                    </span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </section>
                </div>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->

    </main><!-- #main -->
</div><!-- #primary -->

<style>
    .sitemap-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .sitemap-section {
        margin-bottom: 30px;
        background-color: var(--color-surface, #ffffff);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .dark-mode .sitemap-section {
        background-color: var(--color-dark-surface, #1e1e1e);
    }
    
    .sitemap-section h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--color-border, #eeeeee);
        font-size: 1.5rem;
    }
    
    .dark-mode .sitemap-section h2 {
        border-bottom-color: var(--color-dark-border, #333333);
    }
    
    .sitemap-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sitemap-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--color-border-light, #f5f5f5);
    }
    
    .dark-mode .sitemap-item {
        border-bottom-color: var(--color-dark-border-light, #252525);
    }
    
    .sitemap-item:last-child {
        border-bottom: none;
    }
    
    .sitemap-item a {
        color: var(--color-text, #333333);
        text-decoration: none;
        font-weight: 500;
        max-width: 70%;
    }
    
    .dark-mode .sitemap-item a {
        color: var(--color-dark-text, #ffffff);
    }
    
    .sitemap-item a:hover {
        color: var(--color-primary, #ff4057);
    }
    
    .sitemap-meta {
        display: flex;
        gap: 15px;
        color: var(--color-text-muted, #777777);
        font-size: 0.9rem;
    }
    
    .dark-mode .sitemap-meta {
        color: var(--color-dark-text-muted, #999999);
    }
    
    .sitemap-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .sitemap-tag {
        display: inline-block;
        padding: 6px 12px;
        background-color: var(--color-background-light, #f5f5f5);
        border-radius: 20px;
    }
    
    .dark-mode .sitemap-tag {
        background-color: var(--color-background-dark, #252525);
    }
    
    .sitemap-tag a {
        text-decoration: none;
        color: var(--color-text, #333333);
    }
    
    .dark-mode .sitemap-tag a {
        color: var(--color-dark-text, #ffffff);
    }
    
    .sitemap-count {
        font-size: 0.8rem;
        color: var(--color-text-muted, #777777);
        margin-left: 5px;
    }
    
    .dark-mode .sitemap-count {
        color: var(--color-dark-text-muted, #999999);
    }
    
    @media (max-width: 768px) {
        .sitemap-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .sitemap-meta {
            margin-top: 8px;
        }
        
        .sitemap-item a {
            max-width: 100%;
        }
    }
</style>

<?php
get_sidebar();
get_footer();