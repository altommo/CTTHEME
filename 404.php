<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package CustomTube
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <section class="error-404 not-found text-center py-12">
            <div class="container">
                <h1 class="page-title mb-4"><?php esc_html_e( 'Page not found', 'customtube' ); ?></h1>
                <p class="mb-6"><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'customtube' ); ?></p>
                <?php if ( function_exists( 'customtube_search_form' ) ) {
                    customtube_search_form();
                } else {
                    get_search_form();
                } ?>
                <div class="mt-6">
                    <a class="button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <?php esc_html_e( 'Back to Home', 'customtube' ); ?>
                    </a>
                </div>
            </div>
        </section>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
