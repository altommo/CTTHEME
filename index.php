<?php
/**
 * Main template file
 *
 * This file serves as the blog posts index page by default in WordPress.
 * Since a static homepage (`page-home.php`) is now being used, this file
 * will redirect to the homepage if accessed directly as the main query.
 *
 * @package CustomTube
 */

// If this is the main query and not a specific archive or search,
// and if a static front page is set, redirect to the front page.
if ( is_main_query() && ! is_home() && ! is_archive() && ! is_search() && get_option( 'show_on_front' ) === 'page' ) {
    wp_redirect( home_url(), 301 );
    exit();
}

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        // Display a generic archive title if this page is somehow still accessed as an archive
        if ( is_archive() ) : ?>
            <header class="page-header">
                <h1 class="page-title">
                    <?php the_archive_title(); ?>
                </h1>
                <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
            </header><!-- .page-header -->
        <?php endif; ?>

        <?php
        // Loop through posts if any are found (e.g., if this is a blog posts page)
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
                get_template_part( 'template-parts/content', get_post_type() );
            endwhile;

            the_posts_navigation();

        else :
            get_template_part( 'template-parts/content', 'none' );
        endif;
        ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>

<?php
get_footer();
