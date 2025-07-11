<?php
/**
 * AJAX Handlers Module
 * Handles all AJAX requests for auto-detection features
 *
 * @package CustomTube
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AJAX handler for auto-detecting URL type and populating fields
 */
function customtube_ajax_auto_detect_url() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_auto_detect_url')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'customtube')));
    }
    
    // Check if we have the required data
    if (!isset($_POST['url'])) {
        wp_send_json_error(array('message' => __('No URL provided.', 'customtube')));
    }
    
    $url = $_POST['url'];
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $auto_process = isset($_POST['auto_process']) ? (bool)$_POST['auto_process'] : false;
    
    // Clean the URL to remove any extraneous whitespace or special characters
    $url = trim($url);
    
    // Log the received URL for debugging
    error_log("Received URL for auto-detection: $url");
    
    // Detect URL type
    $url_data = customtube_detect_url_type($url);
    
    // Log the detected URL data
    error_log("Detected URL type: platform=" . $url_data['platform'] . 
              ", source_type=" . $url_data['source_type'] . 
              ", video_id=" . (isset($url_data['video_id']) ? $url_data['video_id'] : 'none'));
    
    // Initialize metadata array with fields we want to auto-populate
    $metadata = array(
        'title' => '',
        'description' => '',
        'duration' => '',
        'duration_seconds' => 0,
        'width' => 0,
        'height' => 0,
        'filesize' => 0,
        'thumbnail_url' => '',
    );
    
    // Get metadata based on URL type and platform
    $metadata = customtube_get_metadata_by_platform($url, $url_data, $metadata, $post_id);
    
    // Generate embed code if needed
    $url_data['embed_code'] = customtube_generate_embed_code($url_data);
    
    // Merge URL data with any metadata we found
    $response = array_merge($url_data, $metadata);
    
    // If URL data contains a direct_url, make sure it's in the response
    if (!empty($url_data['direct_url'])) {
        $response['direct_url'] = $url_data['direct_url'];
    }
    
    // Update the post title immediately if we have a valid title and post ID
    if (!empty($metadata['title']) && !empty($post_id) && $post_id > 0 && current_user_can('edit_post', $post_id)) {
        error_log("Attempting to update post title for post ID $post_id to: {$metadata['title']}");
        
        // Update the post title
        $post_data = array(
            'ID' => $post_id,
            'post_title' => $metadata['title']
        );
        
        $update_result = wp_update_post($post_data);
        if ($update_result) {
            error_log("Successfully updated post title to: {$metadata['title']}");
            
            // Also store as meta for backup
            update_post_meta($post_id, '_video_title', $metadata['title']);
        } else {
            error_log("Failed to update post title for post ID: $post_id");
        }
    }
    
    wp_send_json_success($response);
}
add_action('wp_ajax_customtube_auto_detect_url', 'customtube_ajax_auto_detect_url');

/**
 * AJAX handler for setting a thumbnail from a URL
 */
function customtube_ajax_auto_set_thumbnail() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_auto_set_thumbnail')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'customtube')));
    }
    
    // Check if we have the required data
    if (!isset($_POST['post_id']) || !isset($_POST['thumbnail_url'])) {
        wp_send_json_error(array('message' => __('Missing required data.', 'customtube')));
    }
    
    $post_id = intval($_POST['post_id']);
    $thumbnail_url = esc_url_raw($_POST['thumbnail_url']);
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    $auto_populate_categories = isset($_POST['auto_populate_categories']) ? (bool)$_POST['auto_populate_categories'] : false;
    $auto_populate_tags = isset($_POST['auto_populate_tags']) ? (bool)$_POST['auto_populate_tags'] : false;
    
    // Verify user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(array('message' => __('You do not have permission to edit this post.', 'customtube')));
    }
    
    // Try to set the thumbnail
    $attachment_id = customtube_set_featured_image_from_url($post_id, $thumbnail_url);
    
    if ($attachment_id) {
        // Also populate categories and tags if requested
        $site_data = null;
        
        if (($auto_populate_categories || $auto_populate_tags) && !empty($url)) {
            // Get site data from URL
            $site_data = customtube_extract_opengraph_data($url);
        }
        
        // Auto-populate categories/genres if requested
        if ($auto_populate_categories && $site_data && !empty($site_data['categories'])) {
            customtube_populate_categories($post_id, $site_data);
        }
        
        // Auto-populate tags if requested
        if ($auto_populate_tags && $site_data && !empty($site_data['tags'])) {
            customtube_populate_tags($post_id, $site_data);
        }
        
        // Prepare the response data
        $response_data = array(
            'message' => __('Thumbnail set successfully!', 'customtube'),
            'thumbnail_url' => $thumbnail_url,
            'attachment_id' => $attachment_id
        );
        
        // Add category and tag info to the response
        if ($auto_populate_categories) {
            $categories = wp_get_post_categories($post_id, array('fields' => 'names'));
            $genres = array();
            if (taxonomy_exists('genre')) {
                $genres = wp_get_post_terms($post_id, 'genre', array('fields' => 'names'));
            }
            
            $response_data['categories'] = $categories;
            $response_data['genres'] = $genres;
        }
        
        if ($auto_populate_tags) {
            $tags = wp_get_post_tags($post_id, array('fields' => 'names'));
            $response_data['tags'] = $tags;
        }
        
        wp_send_json_success($response_data);
    } else {
        error_log("Failed to set featured image from URL: $thumbnail_url");
        wp_send_json_error(array(
            'message' => __('Failed to set thumbnail.', 'customtube'),
            'thumbnail_url' => $thumbnail_url
        ));
    }
}
add_action('wp_ajax_customtube_auto_set_thumbnail', 'customtube_ajax_auto_set_thumbnail');

/**
 * AJAX handler for saving video metadata directly
 */
function customtube_ajax_save_video_metadata() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_save_metadata')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'customtube')));
    }
    
    // Check if we have the required data
    if (!isset($_POST['post_id']) || !isset($_POST['metadata'])) {
        wp_send_json_error(array('message' => __('Missing required data.', 'customtube')));
    }
    
    $post_id = intval($_POST['post_id']);
    $metadata = $_POST['metadata'];
    $debug_info = isset($_POST['debug_info']) ? json_decode(stripslashes($_POST['debug_info']), true) : array();
    $force_title_update = isset($_POST['force_title_update']) && $_POST['force_title_update'];
    
    // Add extra debugging for title updates
    if (isset($metadata['post_title'])) {
        error_log("TITLE DEBUG: Attempting to save title '{$metadata['post_title']}' for post ID: $post_id");
        
        if ($force_title_update) {
            error_log("FORCE TITLE UPDATE: Force flag set for Gutenberg editor title update");
        }
        
        if (isset($_POST['debug_info'])) {
            error_log("TITLE DEBUG INFO: " . $_POST['debug_info']);
        }
    }
    
    // Log detailed debugging info
    error_log('Attempting to save metadata for post ID ' . $post_id);
    
    // Get the post to check its status
    $post = get_post($post_id);
    $post_info = array(
        'post_exists' => ($post !== null),
        'post_status' => $post ? $post->post_status : 'none',
        'post_type' => $post ? $post->post_type : 'none'
    );
    
    // Verify user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(array(
            'message' => __('You do not have permission to edit this post.', 'customtube'),
            'post_info' => $post_info
        ));
    }
    
    // If post doesn't exist yet, create a draft first
    if (!$post) {
        error_log('Post does not exist, creating a draft post first');
        
        // Create a draft post first if we have title data
        $title = isset($metadata['post_title']) ? sanitize_text_field($metadata['post_title']) : 'New Video';
        
        $post_data = array(
            'post_title' => $title,
            'post_status' => 'draft',
            'post_type' => 'video'
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            error_log('Error creating draft post: ' . $post_id->get_error_message());
            wp_send_json_error(array(
                'message' => __('Failed to create post.', 'customtube'),
                'error' => $post_id->get_error_message()
            ));
            return;
        }
        
        error_log('Successfully created draft post with ID: ' . $post_id);
    }
    
    // Special handling for post title
    if (isset($metadata['post_title']) && !empty($metadata['post_title'])) {
        $title_value = sanitize_text_field($metadata['post_title']);
        error_log("TITLE UPDATE: Updating post title to '{$title_value}' for post ID: $post_id");
        
        // Get the current post to check its status first
        $current_post = get_post($post_id);
        if ($current_post) {
            error_log("Current post title: '{$current_post->post_title}', status: '{$current_post->post_status}'");
        } else {
            error_log("WARNING: Could not retrieve post with ID: $post_id");
        }
        
        // Update the actual post title - force it
        $post_data = array(
            'ID' => $post_id,
            'post_title' => $title_value
        );
        
        $title_result = wp_update_post($post_data);
        
        if ($title_result) {
            error_log("TITLE UPDATE: Successfully updated post title");
            
            // Double check it was actually updated
            $updated_post = get_post($post_id);
            if ($updated_post) {
                error_log("VERIFICATION: Post title after update: '{$updated_post->post_title}'");
                
                // If title still doesn't match, try one more time with a slight delay
                if ($updated_post->post_title !== $title_value) {
                    error_log("TITLE MISMATCH: Title not updated correctly, trying again");
                    
                    // Clear any potential caches
                    clean_post_cache($post_id);
                    
                    // Try update once more with higher priority
                    add_action('shutdown', function() use ($post_id, $title_value) {
                        global $wpdb;
                        
                        // Direct database update as a last resort for block editor
                        $wpdb->update(
                            $wpdb->posts,
                            array('post_title' => $title_value),
                            array('ID' => $post_id)
                        );
                        
                        wp_update_post(array(
                            'ID' => $post_id,
                            'post_title' => $title_value
                        ));
                        
                        clean_post_cache($post_id);
                        error_log("TITLE RETRY: Second attempt to update title for post ID: $post_id");
                    }, 1);
                }
            }
        } else {
            error_log("TITLE UPDATE ERROR: Failed to update post title");
            
            // Force update with database direct access for Block Editor
            if ($force_title_update) {
                global $wpdb;
                
                $result = $wpdb->update(
                    $wpdb->posts,
                    array('post_title' => $title_value),
                    array('ID' => $post_id)
                );
                
                if ($result) {
                    error_log("TITLE UPDATE: Successfully updated post title using direct database access");
                    clean_post_cache($post_id);
                } else {
                    error_log("TITLE UPDATE ERROR: Failed to update post title even with direct database access");
                }
            }
        }
        
        // Also store it as meta for safekeeping
        update_post_meta($post_id, '_video_title', $title_value);
        update_post_meta($post_id, '_youtube_title', $title_value); // Additional backup
        
        // For Block Editor integration - add a special meta that can be used by scripts
        if ($force_title_update || (isset($debug_info['editor_type']) && $debug_info['editor_type'] === 'block')) {
            update_post_meta($post_id, '_gutenberg_title', $title_value);
            error_log("BLOCK EDITOR: Added special _gutenberg_title meta for Block Editor integration");
            
            // Extra step: Force update directly in the database for Block Editor
            global $wpdb;
            $result = $wpdb->update(
                $wpdb->posts,
                array('post_title' => $title_value),
                array('ID' => $post_id)
            );
            
            error_log("BLOCK EDITOR: Direct database update for title - Result: " . 
                      ($result !== false ? "Success ($result)" : "Failed"));
            
            // Force clear any internal caches
            clean_post_cache($post_id);
            
            // Also use another WP API for saving - sometimes one works when the other doesn't
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title_value
            ));
        }
        
        // Remove from metadata array to prevent double processing
        unset($metadata['post_title']);
    }
    
    // Update each regular metadata field
    $updated_fields = array();
    $update_errors = array();
    
    foreach ($metadata as $meta_key => $meta_value) {
        if (!empty($meta_value)) {
            $result = update_post_meta($post_id, $meta_key, $meta_value);
            if ($result) {
                $updated_fields[] = $meta_key;
            } else {
                $update_errors[] = $meta_key;
                error_log("Failed to update meta field: $meta_key for post ID: $post_id");
            }
        }
    }
    
    // Verify the metadata was actually saved
    $verification = array();
    foreach ($updated_fields as $field) {
        $saved_value = get_post_meta($post_id, $field, true);
        $verification[$field] = array(
            'expected' => $metadata[$field],
            'actual' => $saved_value,
            'matches' => ($saved_value == $metadata[$field])
        );
    }
    
    // Force purge any caches that might be preventing changes from being seen
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Return success with detailed information
    wp_send_json_success(array(
        'message' => __('Metadata saved successfully!', 'customtube'),
        'post_id' => $post_id,
        'updated_fields' => $updated_fields,
        'update_errors' => $update_errors,
        'verification' => $verification,
        'post_info' => $post_info
    ));
}
add_action('wp_ajax_customtube_save_video_metadata', 'customtube_ajax_save_video_metadata');

/**
 * AJAX handler for direct YouTube page scraping (server-side)
 */
function customtube_ajax_direct_scrape_youtube() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customtube_direct_scrape')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'customtube')));
    }
    
    // Check if we have the required data
    if (!isset($_POST['video_id']) || !isset($_POST['url'])) {
        wp_send_json_error(array('message' => __('Missing required data.', 'customtube')));
    }
    
    $video_id = sanitize_text_field($_POST['video_id']);
    $url = sanitize_text_field($_POST['url']);
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $get_title = isset($_POST['get_title']) ? (bool)$_POST['get_title'] : false;
    $auto_populate_taxonomies = isset($_POST['auto_populate_taxonomies']) ? (bool)$_POST['auto_populate_taxonomies'] : true;
    
    // Log for debugging
    error_log("Direct scraping YouTube page for video ID: $video_id, URL: $url, get_title: " . ($get_title ? 'true' : 'false'));
    
    // Use the YouTube module to extract metadata
    $metadata = customtube_extract_youtube_metadata($url, $video_id, $get_title);
    
    // If we have a post ID and title, update the post
    if ($post_id > 0 && !empty($metadata['title']) && current_user_can('edit_post', $post_id)) {
        // Update the post title
        $post_data = array(
            'ID' => $post_id,
            'post_title' => $metadata['title']
        );
        
        $result = wp_update_post($post_data);
        if ($result) {
            error_log("Successfully updated post title to: {$metadata['title']}");
            
            // Also update it as post meta for safekeeping
            update_post_meta($post_id, '_video_title', $metadata['title']);
        } else {
            error_log("Failed to update post title");
        }
        
        // If auto-populate taxonomies is enabled, set the categories, tags, and performers
        if ($auto_populate_taxonomies) {
            // Auto-populate categories if available
            if (!empty($metadata['categories']) && is_array($metadata['categories'])) {
                $category_terms = array();
                
                // Limit to 5 categories max
                $categories = array_slice($metadata['categories'], 0, 5);
                
                foreach ($categories as $category) {
                    $category = trim($category);
                    if (!empty($category)) {
                        // Check if category exists
                        $term = get_term_by('name', $category, 'category');
                        
                        if ($term) {
                            $category_terms[] = $term->term_id;
                        } else {
                            // Create the category
                            $new_term = wp_insert_term($category, 'category');
                            if (!is_wp_error($new_term)) {
                                $category_terms[] = $new_term['term_id'];
                            }
                        }
                    }
                }
                
                // Set the categories
                if (!empty($category_terms)) {
                    wp_set_post_categories($post_id, $category_terms, false);
                    error_log("Set categories for post ID $post_id: " . implode(', ', $metadata['categories']));
                }
                
                // Also set genres if they exist
                if (taxonomy_exists('genre')) {
                    $genre_terms = array();
                    
                    foreach ($categories as $genre) {
                        $genre = trim($genre);
                        if (!empty($genre)) {
                            $term = get_term_by('name', $genre, 'genre');
                            
                            if ($term) {
                                $genre_terms[] = $term->term_id;
                            } else {
                                $new_term = wp_insert_term($genre, 'genre');
                                if (!is_wp_error($new_term)) {
                                    $genre_terms[] = $new_term['term_id'];
                                }
                            }
                        }
                    }
                    
                    if (!empty($genre_terms)) {
                        wp_set_object_terms($post_id, $genre_terms, 'genre', false);
                        error_log("Set genres for post ID $post_id: " . implode(', ', $metadata['categories']));
                    }
                }
            }
            
            // Auto-populate tags if available
            if (!empty($metadata['tags']) && is_array($metadata['tags'])) {
                // Limit to 10 tags max to avoid tag spam
                $tags_to_set = array_slice($metadata['tags'], 0, 10);
                
                if (!empty($tags_to_set)) {
                    wp_set_post_tags($post_id, $tags_to_set, true);
                    error_log("Set tags for post ID $post_id: " . implode(', ', $tags_to_set));
                }
            }
            
            // Auto-populate performers if available (uses 'performer' custom taxonomy)
            if (!empty($metadata['performers']) && is_array($metadata['performers']) && taxonomy_exists('performer')) {
                $performer_terms = array();
                
                foreach ($metadata['performers'] as $performer) {
                    $performer = trim($performer);
                    if (!empty($performer)) {
                        $term = get_term_by('name', $performer, 'performer');
                        
                        if ($term) {
                            $performer_terms[] = $term->term_id;
                        } else {
                            $new_term = wp_insert_term($performer, 'performer');
                            if (!is_wp_error($new_term)) {
                                $performer_terms[] = $new_term['term_id'];
                            }
                        }
                    }
                }
                
                if (!empty($performer_terms)) {
                    wp_set_object_terms($post_id, $performer_terms, 'performer', false);
                    error_log("Set performers for post ID $post_id: " . implode(', ', $metadata['performers']));
                }
            }
        }
    }
    
    // Return the metadata
    wp_send_json_success($metadata);
}
add_action('wp_ajax_customtube_direct_scrape_youtube', 'customtube_ajax_direct_scrape_youtube');

/**
 * Get metadata based on the platform
 *
 * @param string $url Original URL
 * @param array $url_data URL detection data
 * @param array $metadata Initial metadata
 * @param int $post_id Post ID
 * @return array Updated metadata
 */
function customtube_get_metadata_by_platform($url, $url_data, $metadata, $post_id) {
    // For direct videos (MP4, etc.)
    if ($url_data['source_type'] === 'direct') {
        // Direct videos require different handling
        // Currently minimal metadata is available without server-side processing
        $metadata['title'] = basename($url);
        return $metadata;
    }
    
    // For embedded videos, use platform-specific extractors
    switch ($url_data['platform']) {
        case 'youtube':
            if (!empty($url_data['video_id'])) {
                // Use YouTube module to extract metadata
                $youtube_metadata = customtube_extract_youtube_metadata($url, $url_data['video_id']);
                return array_merge($metadata, $youtube_metadata);
            }
            break;
            
        case 'vimeo':
            if (!empty($url_data['video_id'])) {
                // Use Vimeo API to extract metadata if available
                if (function_exists('customtube_get_vimeo_metadata')) {
                    $vimeo_metadata = customtube_get_vimeo_metadata($url, array());
                    return array_merge($metadata, $vimeo_metadata);
                }
            }
            break;
            
        case 'xhamster':
            // Use XHamster metadata function if available
            if (function_exists('customtube_get_xhamster_metadata')) {
                error_log("Using XHamster metadata function for URL: $url");
                $xhamster_metadata = customtube_get_xhamster_metadata($url, array());
                return array_merge($metadata, $xhamster_metadata);
            }
            break;
            
        default:
            // For other platforms, try OpenGraph data
            $og_data = customtube_extract_opengraph_data($url);
            if ($og_data) {
                if (!empty($og_data['title'])) {
                    $metadata['title'] = $og_data['title'];
                }
                if (!empty($og_data['description'])) {
                    $metadata['description'] = $og_data['description'];
                }
                if (!empty($og_data['image'])) {
                    $metadata['thumbnail_url'] = $og_data['image'];
                }
                if (!empty($og_data['tags'])) {
                    $metadata['tags'] = $og_data['tags'];
                }
                if (!empty($og_data['categories'])) {
                    $metadata['categories'] = $og_data['categories'];
                }
            }
            break;
    }
    
    return $metadata;
}

/**
 * Populate categories and genres from site data
 *
 * @param int $post_id Post ID
 * @param array $site_data Site data with categories
 */
function customtube_populate_categories($post_id, $site_data) {
    // Don't populate if no post ID or no categories
    if (empty($post_id) || empty($site_data['categories'])) {
        return;
    }
    
    // Check for categories and genres taxonomies
    if (taxonomy_exists('category')) {
        $category_terms = array();
        
        // Limit to first 5 categories
        $categories = array_slice($site_data['categories'], 0, 5);
        
        foreach ($categories as $category) {
            // Clean the category name
            $category = trim($category);
            
            if (!empty($category)) {
                // Check if the category exists
                $term = get_term_by('name', $category, 'category');
                
                if ($term) {
                    $category_terms[] = $term->term_id;
                } else {
                    // Create the category
                    $new_term = wp_insert_term($category, 'category');
                    if (!is_wp_error($new_term)) {
                        $category_terms[] = $new_term['term_id'];
                    }
                }
            }
        }
        
        // Set the category terms
        if (!empty($category_terms)) {
            wp_set_post_categories($post_id, $category_terms, false);
        }
    }
    
    // Also try to set genres if that taxonomy exists
    if (taxonomy_exists('genre')) {
        $genre_terms = array();
        
        // Use the same categories for genres
        $genres = array_slice($site_data['categories'], 0, 5);
        
        foreach ($genres as $genre) {
            // Clean the genre name
            $genre = trim($genre);
            
            if (!empty($genre)) {
                // Check if the genre exists
                $term = get_term_by('name', $genre, 'genre');
                
                if ($term) {
                    $genre_terms[] = $term->term_id;
                } else {
                    // Create the term
                    $new_term = wp_insert_term($genre, 'genre');
                    if (!is_wp_error($new_term)) {
                        $genre_terms[] = $new_term['term_id'];
                    }
                }
            }
        }
        
        // Set the genre terms
        if (!empty($genre_terms)) {
            wp_set_object_terms($post_id, $genre_terms, 'genre', false);
        } else {
            // If we didn't find any genre, assign to a default "Uncategorized" genre
            $uncategorized = get_term_by('name', 'Uncategorized', 'genre');
            if (!$uncategorized) {
                $uncategorized_id = wp_insert_term('Uncategorized', 'genre');
                if (!is_wp_error($uncategorized_id)) {
                    wp_set_object_terms($post_id, $uncategorized_id['term_id'], 'genre', false);
                }
            } else {
                wp_set_object_terms($post_id, $uncategorized->term_id, 'genre', false);
            }
        }
    }
}

/**
 * Populate tags from site data
 *
 * @param int $post_id Post ID
 * @param array $site_data Site data with tags
 */
function customtube_populate_tags($post_id, $site_data) {
    // Don't populate if no post ID or no tags
    if (empty($post_id) || empty($site_data['tags'])) {
        return;
    }
    
    // Limit to first 10 tags to avoid overpopulating
    $tags_to_set = array_slice($site_data['tags'], 0, 10);
    
    if (!empty($tags_to_set)) {
        // Log the tags we're setting
        error_log("Setting " . count($tags_to_set) . " tags for post ID $post_id: " . implode(', ', $tags_to_set));
        wp_set_post_tags($post_id, $tags_to_set, true);
    }
}