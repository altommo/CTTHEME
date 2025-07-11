<?php
/**
 * Template Name: Home Page
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('home-page'); ?>>
            
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <h1 class="hero-title"><?php esc_html_e('Welcome to LustfulClips', 'customtube'); ?></h1>
                    <p class="hero-subtitle"><?php esc_html_e('Your ultimate destination for high-quality adult videos and clips.', 'customtube'); ?></p>
                    
                    <div class="hero-search">
                        <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                            <input type="search" class="search-field" placeholder="<?php esc_attr_e('Search for videos...', 'customtube'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
                            <input type="hidden" name="post_type" value="video" />
                            <button type="submit" class="search-submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo number_format(wp_count_posts('video')->publish); ?></span>
                            <span class="hero-stat-label"><?php esc_html_e('Videos', 'customtube'); ?></span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo number_format(wp_count_terms('performer')); ?></span>
                            <span class="hero-stat-label"><?php esc_html_e('Performers', 'customtube'); ?></span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo number_format(wp_count_terms('genre')); ?></span>
                            <span class="hero-stat-label"><?php esc_html_e('Categories', 'customtube'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Hero Carousel (using the new Carousel component) -->
            <section class="hero-carousel-section">
                <div class="container">
                    <div id="hero-carousel" data-carousel="hero-main" data-carousel-options='{"autoplay": true, "loop": true, "displayMode": "full-width-hero"}'>
                        <!-- Slides will be dynamically loaded by the Carousel JS component -->
                        <div class="loading-container d-flex justify-center align-center py-12">
                            <div class="loading-spinner mr-4"></div>
                            <span class="text-secondary"><?php esc_html_e('Loading hero content...', 'customtube'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Categories -->
            <section class="featured-categories">
                <div class="container">
                    <div class="featured-categories-header">
                        <h2 class="section-title"><?php esc_html_e('Explore Top Categories', 'customtube'); ?></h2>
                        <p class="section-subtitle"><?php esc_html_e('Dive into our most popular video categories.', 'customtube'); ?></p>
                    </div>
                    
                    <div class="categories-grid">
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => 'genre',
                            'hide_empty' => true,
                            'number' => 6,
                            'orderby' => 'count',
                            'order' => 'DESC'
                        ));
                        
                        if ($categories && !is_wp_error($categories)) :
                            foreach ($categories as $category) :
                                $category_image = get_term_meta($category->term_id, 'category_image', true);
                                $category_icon = get_term_meta($category->term_id, 'category_icon', true) ?: '<i class="fas fa-folder"></i>';
                                ?>
                                <div class="category-card">
                                    <div class="category-icon" style="<?php echo $category_image ? 'background-image: url(' . esc_url($category_image) . '); background-size: cover;' : ''; ?>">
                                        <?php echo $category_icon; ?>
                                    </div>
                                    <h3 class="category-title"><?php echo esc_html($category->name); ?></h3>
                                    <p class="category-count"><?php printf(_n('%s video', '%s videos', $category->count, 'customtube'), number_format($category->count)); ?></p>
                                    <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-link">
                                        <?php esc_html_e('View Category', 'customtube'); ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endforeach;
                        else : ?>
                            <p class="col-span-full text-center"><?php esc_html_e('No categories found.', 'customtube'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- "Because you watched..." Carousel -->
            <section class="carousel-section">
                <div class="container">
                    <h2 class="section-title"><?php esc_html_e('Because you watched...', 'customtube'); ?></h2>
                    <div id="because-you-watched-carousel" data-carousel="because-you-watched" data-carousel-options='{"slidesToShow": 4, "loop": false, "displayMode": "multi-row-strip"}'>
                        <div class="loading-container d-flex justify-center align-center py-12">
                            <div class="loading-spinner mr-4"></div>
                            <span class="text-secondary"><?php esc_html_e('Loading recommendations...', 'customtube'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- "You might also like..." Carousel -->
            <section class="carousel-section">
                <div class="container">
                    <h2 class="section-title"><?php esc_html_e('You might also like...', 'customtube'); ?></h2>
                    <div id="you-might-also-like-carousel" data-carousel="you-might-also-like" data-carousel-options='{"slidesToShow": 4, "loop": false, "displayMode": "multi-row-strip"}'>
                        <div class="loading-container d-flex justify-center align-center py-12">
                            <div class="loading-spinner mr-4"></div>
                            <span class="text-secondary"><?php esc_html_e('Loading more suggestions...', 'customtube'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Trending Videos Section -->
            <section class="trending-section">
                <div class="container">
                    <div class="trending-header">
                        <h2 class="section-title"><?php esc_html_e('Trending Videos', 'customtube'); ?></h2>
                        <div class="trending-nav">
                            <button class="trending-tab is-active" data-period="today">
                                <?php esc_html_e('Today', 'customtube'); ?>
                            </button>
                            <button class="trending-tab" data-period="week">
                                <?php esc_html_e('This Week', 'customtube'); ?>
                            </button>
                            <button class="trending-tab" data-period="month">
                                <?php esc_html_e('This Month', 'customtube'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="trending-content">
                        <div class="trending-panel is-active" id="trending-today">
                            <div class="video-grid">
                                <?php
                                $trending_today = customtube_get_trending_videos(8);
                                if ($trending_today->have_posts()) {
                                    while ($trending_today->have_posts()) : $trending_today->the_post();
                                        customtube_video_card(get_the_ID());
                                    endwhile;
                                } else {
                                    echo '<p class="col-span-full text-center">' . esc_html__('No trending videos today.', 'customtube') . '</p>';
                                }
                                wp_reset_postdata();
                                ?>
                            </div>
                        </div>
                        <!-- Other trending panels (week, month) would be loaded via AJAX -->
                    </div>
                </div>
            </section>

            <!-- Newsletter Signup -->
            <section class="newsletter-section">
                <div class="newsletter-content">
                    <h2 class="newsletter-title"><?php esc_html_e('Stay Updated', 'customtube'); ?></h2>
                    <p class="newsletter-subtitle"><?php esc_html_e('Subscribe to our newsletter for the latest videos, updates, and exclusive content.', 'customtube'); ?></p>
                    
                    <form class="newsletter-form">
                        <input type="email" class="newsletter-input" placeholder="<?php esc_attr_e('Enter your email address', 'customtube'); ?>" required>
                        <button type="submit" class="newsletter-submit">
                            <?php esc_html_e('Subscribe', 'customtube'); ?>
                        </button>
                    </form>
                </div>
            </section>

        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
