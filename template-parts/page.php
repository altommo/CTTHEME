<?php
/**
 * Template for displaying all pages
 *
 * @package CustomTube
 */
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) : the_post();

            $template_name = get_page_template_slug();
            if ($template_name) {
                get_template_part('template-parts/pages/' . $template_name);
            } else {
                ?>
                <div class="page-content">
                    <h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </div>
                <?php
            }

        endwhile; // End of the loop.
        ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
?>
