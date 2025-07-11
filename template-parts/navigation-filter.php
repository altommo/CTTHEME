<?php
/**
 * Template part for displaying the navigation and filtering system
 *
 * @package CustomTube
 */

// Get taxonomy data
$genres = get_terms(array(
    'taxonomy' => 'genre',
    'hide_empty' => true,
));

// Try to get tags from multiple taxonomies
$all_tags = array();

// Try post_tag taxonomy
$post_tags = get_terms(array(
    'taxonomy' => 'post_tag',
    'hide_empty' => true,
    'number' => 20,
    'orderby' => 'count',
    'order' => 'DESC',
));

if (!is_wp_error($post_tags) && !empty($post_tags)) {
    $all_tags = array_merge($all_tags, $post_tags);
}

// Try video_tag taxonomy
$video_tags = get_terms(array(
    'taxonomy' => 'video_tag',
    'hide_empty' => true,
    'number' => 20,
    'orderby' => 'count',
    'order' => 'DESC',
));

if (!is_wp_error($video_tags) && !empty($video_tags)) {
    $all_tags = array_merge($all_tags, $video_tags);
}

// Try tag taxonomy (fallback)
$generic_tags = get_terms(array(
    'taxonomy' => 'tag',
    'hide_empty' => true,
    'number' => 20,
    'orderby' => 'count',
    'order' => 'DESC',
));

if (!is_wp_error($generic_tags) && !empty($generic_tags)) {
    $all_tags = array_merge($all_tags, $generic_tags);
}

// Use all collected tags
$tags = $all_tags;

// Debug output to check what tags we found
error_log('CustomTube found ' . count($tags) . ' tags in total');

$performers = get_terms(array(
    'taxonomy' => 'performer',
    'hide_empty' => true,
    'number' => 20, // Limit to top 20 performers
    'orderby' => 'count',
    'order' => 'DESC',
));

$durations = get_terms(array(
    'taxonomy' => 'video_duration',
    'hide_empty' => true,
));

// Get active filters from URL
$active_genre = isset($_GET['genre']) ? absint($_GET['genre']) : 0;
$active_tag = isset($_GET['tag']) ? absint($_GET['tag']) : 0;
$active_performer = isset($_GET['performer']) ? absint($_GET['performer']) : 0;
$active_duration = isset($_GET['duration']) ? intval($_GET['duration']) : 0;
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Get section expansion state from localStorage via inline script
?>
<!-- Navigation sidebar -->
<aside class="nav-side" id="navSide">
    <!-- Genres section -->
    <div class="nav-section" data-section-id="genres">
        <div class="nav-section-header" tabindex="0" role="button" aria-expanded="false">
            <i class="fas fa-film nav-section-icon"></i>
            <span class="nav-section-title">Genres</span>
            <i class="fas fa-chevron-down nav-section-chevron"></i>
        </div>
        <div class="nav-category-items">
            <?php if ($genres) : ?>
                <?php foreach ($genres as $genre) : ?>
                    <a 
                        href="<?php echo esc_url(add_query_arg('genre', $genre->term_id, home_url())); ?>" 
                        class="nav-category-item <?php echo ($active_genre === $genre->term_id) ? 'active' : ''; ?>"
                        data-category-type="genre"
                        data-category-id="<?php echo esc_attr($genre->term_id); ?>"
                    >
                        <?php echo esc_html($genre->name); ?>
                        <span class="nav-category-count"><?php echo esc_html($genre->count); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="nav-category-item">No genres found</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tags section -->
    <div class="nav-section" data-section-id="tags">
        <div class="nav-section-header" tabindex="0" role="button" aria-expanded="false">
            <i class="fas fa-tags nav-section-icon"></i>
            <span class="nav-section-title">Tags</span>
            <i class="fas fa-chevron-down nav-section-chevron"></i>
        </div>
        <div class="nav-category-items">
            <?php
            // Create a fallback set of tags if none were found
            if (empty($tags)) {
                // Manually create some demo tags
                $demo_tags = array(
                    (object) array('term_id' => 1, 'name' => 'Blonde', 'count' => 24),
                    (object) array('term_id' => 2, 'name' => 'Brunette', 'count' => 18),
                    (object) array('term_id' => 3, 'name' => 'Asian', 'count' => 12),
                    (object) array('term_id' => 4, 'name' => 'Tattoo', 'count' => 10),
                    (object) array('term_id' => 5, 'name' => 'Big Tits', 'count' => 15),
                    (object) array('term_id' => 6, 'name' => 'Petite', 'count' => 9),
                    (object) array('term_id' => 7, 'name' => 'MILF', 'count' => 22),
                    (object) array('term_id' => 8, 'name' => 'Teen', 'count' => 28),
                );
                $display_tags = $demo_tags;
            } else {
                $display_tags = $tags;
            }
            ?>

            <?php if (!empty($display_tags)) : ?>
                <?php foreach ($display_tags as $tag) : ?>
                    <a
                        href="<?php echo esc_url(add_query_arg('tag', $tag->term_id, home_url())); ?>"
                        class="nav-category-item <?php echo ($active_tag === $tag->term_id) ? 'active' : ''; ?>"
                        data-category-type="tag"
                        data-category-id="<?php echo esc_attr($tag->term_id); ?>"
                    >
                        <?php echo esc_html($tag->name); ?>
                        <span class="nav-category-count"><?php echo esc_html($tag->count); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="nav-category-item">No tags found</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Performers section -->
    <div class="nav-section" data-section-id="performers">
        <div class="nav-section-header" tabindex="0" role="button" aria-expanded="false">
            <i class="fas fa-user-alt nav-section-icon"></i>
            <span class="nav-section-title">Performers</span>
            <i class="fas fa-chevron-down nav-section-chevron"></i>
        </div>
        <div class="nav-category-items">
            <?php if ($performers) : ?>
                <?php foreach ($performers as $performer) : ?>
                    <a 
                        href="<?php echo esc_url(add_query_arg('performer', $performer->term_id, home_url())); ?>" 
                        class="nav-category-item <?php echo ($active_performer === $performer->term_id) ? 'active' : ''; ?>"
                        data-category-type="performer"
                        data-category-id="<?php echo esc_attr($performer->term_id); ?>"
                    >
                        <?php echo esc_html($performer->name); ?>
                        <span class="nav-category-count"><?php echo esc_html($performer->count); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="nav-category-item">No performers found</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Duration section -->
    <div class="nav-section" data-section-id="duration">
        <div class="nav-section-header" tabindex="0" role="button" aria-expanded="false">
            <i class="fas fa-clock nav-section-icon"></i>
            <span class="nav-section-title">Duration</span>
            <i class="fas fa-chevron-down nav-section-chevron"></i>
        </div>
        <div class="nav-category-items">
            <div class="duration-filter">
                <div class="duration-value">
                    <?php
                    if ($active_duration == 0) {
                        echo 'Any length';
                    } elseif ($active_duration <= 5) {
                        echo 'Under 5m';
                    } elseif ($active_duration <= 10) {
                        echo 'Under 10m';
                    } elseif ($active_duration <= 20) {
                        echo 'Under 20m';
                    } else {
                        echo 'Over 20m';
                    }
                    ?>
                </div>
                <div class="duration-slider-container">
                    <input 
                        type="range" 
                        min="0" 
                        max="30" 
                        step="5" 
                        value="<?php echo esc_attr($active_duration); ?>" 
                        class="duration-slider"
                        aria-label="Duration filter"
                    >
                    <div class="duration-labels">
                        <span class="duration-label">Any</span>
                        <span class="duration-label">5m</span>
                        <span class="duration-label">10m</span>
                        <span class="duration-label">20m</span>
                        <span class="duration-label">30m+</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile overlay -->
<div class="nav-overlay"></div>

<!-- Initialize section expansion state from localStorage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        const expandedSections = JSON.parse(localStorage.getItem('expandedSections') || '{}');
        Object.keys(expandedSections).forEach(sectionId => {
            const section = document.querySelector(`.nav-section[data-section-id="${sectionId}"]`);
            if (section && expandedSections[sectionId]) {
                section.classList.add('expanded');
                const header = section.querySelector('.nav-section-header');
                if (header) {
                    header.setAttribute('aria-expanded', 'true');
                }
            }
        });
        
        // Always expand sections with active items
        const activeItems = document.querySelectorAll('.nav-category-item.active');
        activeItems.forEach(item => {
            const section = item.closest('.nav-section');
            if (section && !section.classList.contains('expanded')) {
                section.classList.add('expanded');
                const header = section.querySelector('.nav-section-header');
                if (header) {
                    header.setAttribute('aria-expanded', 'true');
                }
            }
        });
    } catch (e) {
        console.error('Error restoring section state:', e);
    }
});
</script>