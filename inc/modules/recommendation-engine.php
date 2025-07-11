<?php
/**
 * Recommendation Engine Module
 * Simulates an AI-powered recommendation API for personalized content.
 *
 * @package CustomTube
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Fetches personalized video recommendations for a user.
 * This is a simulated API. In a real scenario, this would query a machine learning model.
 *
 * @param int $user_id The ID of the current user.
 * @param array $watched_video_ids An array of video IDs the user has recently watched.
 * @param int $limit The maximum number of recommendations to return.
 * @param string $type The type of recommendation ('hero', 'because_you_watched', 'you_might_also_like').
 * @return array An array of recommended video data.
 */
function customtube_get_personalized_recommendations($user_id, $watched_video_ids = array(), $limit = 5, $type = 'hero') {
    $recommended_videos = array();
    $exclude_ids = array();

    // Add watched videos to exclusion list to avoid recommending them again immediately
    if (!empty($watched_video_ids)) {
        $exclude_ids = array_merge($exclude_ids, $watched_video_ids);
    }

    // Logic for different recommendation types
    switch ($type) {
        case 'hero':
            // For hero, prioritize trending or highly-rated videos, mixed with some random
            $args = array(
                'post_type'      => 'video',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'rand', // Random for now, could be 'meta_value_num' for views/likes
                'meta_key'       => 'video_views', // Example: order by views
                'order'          => 'DESC',
                'post__not_in'   => $exclude_ids,
            );
            break;

        case 'because_you_watched':
            // Simulate "Because you watched..." by finding videos similar to watched ones.
            // For this demo, we'll just pick random videos, but in a real system:
            // 1. Get categories/tags/performers of watched videos.
            // 2. Query for videos with similar categories/tags/performers.
            if (!empty($watched_video_ids)) {
                // Get terms from a random watched video to simulate similarity
                $random_watched_id = $watched_video_ids[array_rand($watched_video_ids)];
                $terms = wp_get_post_terms($random_watched_id, array('genre', 'post_tag', 'performer'), array('fields' => 'ids'));
                
                $args = array(
                    'post_type'      => 'video',
                    'post_status'    => 'publish',
                    'posts_per_page' => $limit,
                    'orderby'        => 'rand',
                    'post__not_in'   => array_merge($exclude_ids, array($random_watched_id)),
                );

                if (!empty($terms)) {
                    $args['tax_query'] = array(
                        'relation' => 'OR',
                        array(
                            'taxonomy' => 'genre',
                            'field'    => 'term_id',
                            'terms'    => $terms,
                        ),
                        array(
                            'taxonomy' => 'post_tag',
                            'field'    => 'term_id',
                            'terms'    => $terms,
                        ),
                        array(
                            'taxonomy' => 'performer',
                            'field'    => 'term_id',
                            'terms'    => $terms,
                        ),
                    );
                }
            } else {
                // Fallback if no watched videos
                $args = array(
                    'post_type'      => 'video',
                    'post_status'    => 'publish',
                    'posts_per_page' => $limit,
                    'orderby'        => 'rand',
                    'post__not_in'   => $exclude_ids,
                );
            }
            break;

        case 'you_might_also_like':
            // Simulate "You might also like..." with a broader set of popular or random videos.
            $args = array(
                'post_type'      => 'video',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'rand',
                'post__not_in'   => $exclude_ids,
            );
            break;

        default:
            // Default to random videos
            $args = array(
                'post_type'      => 'video',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'rand',
                'post__not_in'   => $exclude_ids,
            );
            break;
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $video_url = get_post_meta($post_id, 'video_url', true);
            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'video-large');
            $video_type = get_post_meta($post_id, 'video_type', true);

            // Ensure we have a valid video type for the carousel
            $carousel_video_type = 'image'; // Default to image
            if ($video_type === 'mp4' || $video_type === 'self-hosted') {
                $carousel_video_type = 'video';
            }

            $recommended_videos[] = array(
                'id'          => $post_id,
                'type'        => $carousel_video_type,
                'imageUrl'    => $thumbnail_url ?: CUSTOMTUBE_URI . '/assets/images/default-video.jpg',
                'videoUrl'    => ($carousel_video_type === 'video') ? $video_url : '',
                'title'       => get_the_title(),
                'description' => wp_trim_words(get_the_excerpt(), 20),
                'buttonText'  => 'Watch Now',
                'buttonUrl'   => get_permalink(),
            );
        }
        wp_reset_postdata();
    }

    return $recommended_videos;
}

/**
 * AJAX handler for fetching personalized recommendations.
 */
function customtube_ajax_get_personalized_recommendations() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube-ajax-nonce')) {
        error_log('Personalized Recommendations AJAX Error: Invalid nonce. Nonce received: ' . ($_POST['nonce'] ?? 'N/A'));
        wp_send_json_error('Invalid nonce');
    }

    $user_id = get_current_user_id(); // 0 if not logged in
    
    // Sanitize and validate watched_video_ids
    $watched_video_ids = array();
    if (isset($_POST['watched_video_ids']) && is_array($_POST['watched_video_ids'])) {
        $watched_video_ids = array_map('intval', $_POST['watched_video_ids']);
        $watched_video_ids = array_filter($watched_video_ids); // Remove any non-integer or zero values
    } else {
        error_log('Personalized Recommendations AJAX Warning: watched_video_ids not provided or not an array.');
    }

    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'hero';

    // Validate limit and type
    if ($limit <= 0 || $limit > 20) $limit = 5; // Cap limit to prevent abuse
    if (!in_array($type, ['hero', 'because_you_watched', 'you_might_also_like'])) $type = 'hero';

    $recommendations = customtube_get_personalized_recommendations($user_id, $watched_video_ids, $limit, $type);

    wp_send_json_success($recommendations);
}
add_action('wp_ajax_customtube_ajax_get_personalized_recommendations', 'customtube_ajax_get_personalized_recommendations');
add_action('wp_ajax_nopriv_customtube_ajax_get_personalized_recommendations', 'customtube_ajax_get_personalized_recommendations');
