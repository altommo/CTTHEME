<?php
/**
 * Single post template for CustomTube
 */
get_header(); ?>
<main class="single-post">
  <?php
    if ( have_posts() ) :
      while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
          <h1><?php the_title(); ?></h1>
          <?php if ( has_post_thumbnail() ) : ?>
            <div class="featured-image"><?php the_post_thumbnail('large'); ?></div>
          <?php endif; ?>
          <div class="entry-content">
            <?php the_content(); ?>
          </div>
        </article>
      <?php endwhile;
    endif;
  ?>
</main>
<?php get_footer(); ?>
