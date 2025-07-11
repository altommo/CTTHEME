<?php
/**
 * CustomTube SEO Functions
 *
 * Implements structured data and SEO optimizations for video content
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate JSON-LD VideoObject schema for video pages
 * 
 * @param int $post_id The video post ID
 * @return string JSON-LD structured data
 */
function customtube_video_schema($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'video') {
        return '';
    }

    $video_url = get_post_meta($post_id, '_video_url', true);
    $duration = get_post_meta($post_id, '_video_duration', true);
    $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
    $upload_date = get_the_date('c', $post_id);
    $description = wp_strip_all_tags(get_the_excerpt($post_id));
    $view_count = (int)get_post_meta($post_id, '_view_count', true);
    $rating = get_post_meta($post_id, '_star_rating', true);
    
    // Format duration as ISO 8601
    if ($duration) {
        $duration_parts = explode(':', $duration);
        if (count($duration_parts) === 2) {
            // MM:SS format
            $duration = 'PT' . intval($duration_parts[0]) . 'M' . intval($duration_parts[1]) . 'S';
        } elseif (count($duration_parts) === 3) {
            // HH:MM:SS format
            $duration = 'PT' . intval($duration_parts[0]) . 'H' . intval($duration_parts[1]) . 'M' . intval($duration_parts[2]) . 'S';
        }
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'VideoObject',
        'name' => get_the_title($post_id),
        'description' => $description,
        'thumbnailUrl' => $thumbnail,
        'uploadDate' => $upload_date,
        'contentUrl' => $video_url,
        'embedUrl' => $video_url,
        'url' => get_permalink($post_id),
    );

    if ($duration) {
        $schema['duration'] = $duration;
    }

    if ($view_count) {
        $schema['interactionStatistic'] = array(
            '@type' => 'InteractionCounter',
            'interactionType' => 'https://schema.org/WatchAction',
            'userInteractionCount' => $view_count
        );
    }

    if ($rating) {
        $schema['aggregateRating'] = array(
            '@type' => 'AggregateRating',
            'ratingValue' => $rating,
            'bestRating' => '5',
            'worstRating' => '1',
            'ratingCount' => $view_count ? $view_count : 1
        );
    }

    // Get performers
    $performers = get_the_terms($post_id, 'performer');
    if ($performers && !is_wp_error($performers)) {
        $schema['actor'] = array();
        foreach ($performers as $performer) {
            $schema['actor'][] = array(
                '@type' => 'Person',
                'name' => $performer->name,
                'url' => get_term_link($performer)
            );
        }
    }

    // Get genres
    $genres = get_the_terms($post_id, 'genre');
    if ($genres && !is_wp_error($genres)) {
        $schema['genre'] = array();
        foreach ($genres as $genre) {
            $schema['genre'][] = $genre->name;
        }
    }

    return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Output video schema in the head section
 */
function customtube_output_video_schema() {
    if (is_singular('video')) {
        echo customtube_video_schema(get_the_ID());
    }
}
add_action('wp_head', 'customtube_output_video_schema');

/**
 * Generate XML sitemap for videos
 */
function customtube_video_sitemap() {
    global $wpdb;

    if (!isset($_GET['customtube_video_sitemap'])) {
        return;
    }

    // Set proper content type
    header('Content-Type: application/xml; charset=UTF-8');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

    $videos = get_posts(array(
        'post_type' => 'video',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    foreach ($videos as $video) {
        $video_url = get_post_meta($video->ID, '_video_url', true);
        $thumbnail = get_the_post_thumbnail_url($video->ID, 'large');
        $duration = get_post_meta($video->ID, '_video_duration', true);
        $duration_seconds = 0;
        
        // Convert duration to seconds
        if ($duration) {
            $duration_parts = explode(':', $duration);
            if (count($duration_parts) === 2) {
                // MM:SS format
                $duration_seconds = intval($duration_parts[0]) * 60 + intval($duration_parts[1]);
            } elseif (count($duration_parts) === 3) {
                // HH:MM:SS format
                $duration_seconds = intval($duration_parts[0]) * 3600 + intval($duration_parts[1]) * 60 + intval($duration_parts[2]);
            }
        }

        $genres = get_the_terms($video->ID, 'genre');
        $genre_names = array();
        if ($genres && !is_wp_error($genres)) {
            foreach ($genres as $genre) {
                $genre_names[] = $genre->name;
            }
        }

        echo '<url>' . "\n";
        echo '  <loc>' . esc_url(get_permalink($video->ID)) . '</loc>' . "\n";
        echo '  <video:video>' . "\n";
        echo '    <video:thumbnail_loc>' . esc_url($thumbnail) . '</video:thumbnail_loc>' . "\n";
        echo '    <video:title>' . esc_html(get_the_title($video->ID)) . '</video:title>' . "\n";
        echo '    <video:description>' . esc_html(wp_strip_all_tags(get_the_excerpt($video->ID))) . '</video:description>' . "\n";
        echo '    <video:content_loc>' . esc_url($video_url) . '</video:content_loc>' . "\n";
        
        if ($duration_seconds) {
            echo '    <video:duration>' . esc_html($duration_seconds) . '</video:duration>' . "\n";
        }
        
        if (!empty($genre_names)) {
            foreach ($genre_names as $genre) {
                echo '    <video:category>' . esc_html($genre) . '</video:category>' . "\n";
            }
        }
        
        echo '    <video:publication_date>' . esc_html(get_the_date('c', $video->ID)) . '</video:publication_date>' . "\n";
        echo '  </video:video>' . "\n";
        echo '</url>' . "\n";
    }

    echo '</urlset>';
    exit;
}
add_action('init', 'customtube_video_sitemap');

/**
 * Register sitemap with WordPress
 */
function customtube_add_video_sitemap($wp_sitemaps_registry) {
    // Add rewrite rule for custom video sitemap
    add_rewrite_rule(
        'video-sitemap\.xml$',
        'index.php?customtube_video_sitemap=1',
        'top'
    );
    
    // Register the query var
    add_filter('query_vars', function($vars) {
        $vars[] = 'customtube_video_sitemap';
        return $vars;
    });
}
add_action('init', 'customtube_add_video_sitemap');

/**
 * Modify permalink structure for videos
 */
function customtube_video_permalink_structure($post_link, $post) {
    if ($post->post_type === 'video') {
        $post_link = home_url('/video/' . $post->ID . '/' . $post->post_name . '/');
    }
    return $post_link;
}
add_filter('post_type_link', 'customtube_video_permalink_structure', 10, 2);

/**
 * Add custom rewrite rules for videos
 */
function customtube_video_rewrite_rules() {
    add_rewrite_rule(
        'video/([0-9]+)/([^/]+)/?$',
        'index.php?post_type=video&p=$matches[1]',
        'top'
    );
}
add_action('init', 'customtube_video_rewrite_rules');

/**
 * Optimize meta tags for videos
 */
function customtube_video_meta_tags() {
    if (!is_singular('video')) {
        return;
    }
    
    $post_id = get_the_ID();
    $title = get_the_title($post_id);
    $description = wp_strip_all_tags(get_the_excerpt($post_id));
    $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
    
    // Open Graph tags
    echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
    echo '<meta property="og:type" content="video.other" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink($post_id)) . '" />' . "\n";
    
    if ($thumbnail) {
        echo '<meta property="og:image" content="' . esc_url($thumbnail) . '" />' . "\n";
    }
    
    $video_url = get_post_meta($post_id, '_video_url', true);
    if ($video_url) {
        echo '<meta property="og:video" content="' . esc_url($video_url) . '" />' . "\n";
        echo '<meta property="og:video:url" content="' . esc_url($video_url) . '" />' . "\n";
        echo '<meta property="og:video:secure_url" content="' . esc_url($video_url) . '" />' . "\n";
        echo '<meta property="og:video:type" content="video/mp4" />' . "\n";
        
        if ($thumbnail) {
            echo '<meta property="og:video:image" content="' . esc_url($thumbnail) . '" />' . "\n";
        }
    }
    
    // Twitter card
    echo '<meta name="twitter:card" content="player" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
    
    if ($thumbnail) {
        echo '<meta name="twitter:image" content="' . esc_url($thumbnail) . '" />' . "\n";
    }
    
    if ($video_url) {
        echo '<meta name="twitter:player" content="' . esc_url($video_url) . '" />' . "\n";
    }
}
add_action('wp_head', 'customtube_video_meta_tags', 1);

/**
 * Implement lazy loading for thumbnails
 */
function customtube_lazyload_images($content) {
    // Don't modify admin pages or feeds
    if (is_admin() || is_feed()) {
        return $content;
    }
    
    // Don't modify REST API requests
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return $content;
    }
    
    // Replace image src with data-src for lazy loading
    $content = preg_replace_callback('/<img([^>]+)src=[\'"]([^\'"]+)[\'"]([^>]*)>/', function($matches) {
        // Skip if already has data-src or lazyload class
        if (strpos($matches[0], 'data-src') !== false || strpos($matches[0], 'skip-lazy') !== false) {
            return $matches[0];
        }
        
        // Add lazyload class
        $class_attr = '';
        if (strpos($matches[1] . $matches[3], 'class=') === false) {
            $class_attr = ' class="lazyload"';
        } else {
            $matches[0] = preg_replace('/class=[\'"](.*?)[\'"]/i', 'class="$1 lazyload"', $matches[0]);
        }
        
        // Add placeholder and data attributes
        return '<img' . $matches[1] . 'src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="' . $matches[2] . '"' . $matches[3] . $class_attr . '>';
    }, $content);
    
    return $content;
}
add_filter('the_content', 'customtube_lazyload_images', 99);
add_filter('post_thumbnail_html', 'customtube_lazyload_images', 99);

/**
 * Set long cache headers for video assets
 */
function customtube_set_cache_headers() {
    if (is_singular('video')) {
        // One week for video pages
        header('Cache-Control: public, max-age=604800');
    } else if (is_tax('performer') || is_tax('genre') || is_tax('tag')) {
        // One day for taxonomy pages
        header('Cache-Control: public, max-age=86400');
    }
}
add_action('template_redirect', 'customtube_set_cache_headers');

/**
 * Add schema breadcrumbs
 */
function customtube_breadcrumb_schema() {
    if (!is_singular('video') && !is_tax('performer') && !is_tax('genre') && !is_tax('tag')) {
        return;
    }
    
    $breadcrumbs = array();
    $position = 1;
    
    // Add home
    $breadcrumbs[] = array(
        '@type' => 'ListItem',
        'position' => $position,
        'name' => 'Home',
        'item' => home_url()
    );
    $position++;
    
    if (is_singular('video')) {
        // Add video category/genre if applicable
        $genres = get_the_terms(get_the_ID(), 'genre');
        if ($genres && !is_wp_error($genres)) {
            $primary_genre = reset($genres);
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $primary_genre->name,
                'item' => get_term_link($primary_genre)
            );
            $position++;
        }
        
        // Add current video
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_title(),
            'item' => get_permalink()
        );
    } elseif (is_tax()) {
        $term = get_queried_object();
        
        // Check if term has a parent
        if ($term->parent) {
            $parent = get_term($term->parent, $term->taxonomy);
            if ($parent && !is_wp_error($parent)) {
                $breadcrumbs[] = array(
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $parent->name,
                    'item' => get_term_link($parent)
                );
                $position++;
            }
        } else {
            // Add taxonomy archive
            $tax_obj = get_taxonomy($term->taxonomy);
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $tax_obj->labels->name,
                'item' => get_post_type_archive_link('video')
            );
            $position++;
        }
        
        // Add current term
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $term->name,
            'item' => get_term_link($term)
        );
    }
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbs
    );
    
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}
add_action('wp_head', 'customtube_breadcrumb_schema');