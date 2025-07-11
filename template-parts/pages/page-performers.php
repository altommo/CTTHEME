<?php
/**
 * Template Name: Performers Directory
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
                <?php
                // Display page content if any
                the_content();
                
                // Get all performers
                $performers = get_terms(array(
                    'taxonomy' => 'performer',
                    'hide_empty' => true,
                ));
                
                if (!empty($performers) && !is_wp_error($performers)) {
                    // Sort performers alphabetically
                    usort($performers, function($a, $b) {
                        return strcasecmp($a->name, $b->name);
                    });
                    
                    // Create index of first letters
                    $letters = array();
                    foreach ($performers as $performer) {
                        $first_letter = strtoupper(substr($performer->name, 0, 1));
                        if (!in_array($first_letter, $letters)) {
                            $letters[] = $first_letter;
                        }
                    }
                    
                    // Output the alphabetical index
                    echo '<div class="performer-index">';
                    echo '<ul class="performer-letters">';
                    foreach ($letters as $letter) {
                        echo '<li><a href="#letter-' . esc_attr($letter) . '">' . esc_html($letter) . '</a></li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                    
                    // Output performers organized by first letter
                    echo '<div class="performers-directory">';
                    
                    $current_letter = '';
                    foreach ($performers as $performer) {
                        $first_letter = strtoupper(substr($performer->name, 0, 1));
                        
                        if ($first_letter !== $current_letter) {
                            // Close the previous section if needed
                            if ($current_letter !== '') {
                                echo '</ul>';
                                echo '</div>';
                            }
                            
                            // Start a new section
                            $current_letter = $first_letter;
                            echo '<div class="performer-section" id="letter-' . esc_attr($current_letter) . '">';
                            echo '<h2 class="performer-letter">' . esc_html($current_letter) . '</h2>';
                            echo '<ul class="performer-list">';
                        }
                        
                        // Get performer thumbnail/image if available
                        $performer_image = '';
                        $image_id = get_term_meta($performer->term_id, 'performer_image_id', true);
                        if ($image_id) {
                            $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                            if ($image_url) {
                                $performer_image = '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($performer->name) . '" class="performer-thumbnail">';
                            }
                        }
                        
                        // Get video count for this performer
                        $video_count = $performer->count;
                        
                        // Output the performer
                        echo '<li class="performer-item">';
                        echo '<a href="' . esc_url(get_term_link($performer)) . '" class="performer-link">';
                        
                        if ($performer_image) {
                            echo $performer_image;
                        }
                        
                        echo '<span class="performer-name">' . esc_html($performer->name) . '</span>';
                        echo '<span class="performer-count">' . sprintf(_n('%s video', '%s videos', $video_count, 'customtube'), number_format($video_count)) . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    
                    // Close the last section
                    if ($current_letter !== '') {
                        echo '</ul>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                } else {
                    echo '<p>' . esc_html__('No performers found.', 'customtube') . '</p>';
                }
                ?>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();