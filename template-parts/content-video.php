<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

  <?php
  // Thumbnail removed; our grid/preview handles all poster images.
  // <div class="video-thumbnail">
  //   <a href="<?php the_permalink(); ?>">
  //     <?php // if ( has_post_thumbnail() ) the_post_thumbnail('medium'); ?>
  //   </a>
  // </div>
  ?>

  <div class="excerpt"><?php the_excerpt(); ?></div>
</article>
