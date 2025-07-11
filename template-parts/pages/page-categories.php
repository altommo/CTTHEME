<?php
/**
 * Template Name: Categories Page
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('categories-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-th-large mr-3 text-secondary"></i>
                            <?php esc_html_e('Browse Categories', 'customtube'); ?>
                        </h1>
                        <nav class="breadcrumb-nav text-sm text-secondary mb-4">
                            <a href="<?php echo esc_url(home_url()); ?>" class="text-primary"><?php esc_html_e('Home', 'customtube'); ?></a>
                            <span class="breadcrumb-separator mx-2">›</span>
                            <span><?php esc_html_e('Categories', 'customtube'); ?></span>
                        </nav>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Explore videos by category and discover new content', 'customtube'); ?>
                        </p>
                    </div>
                </div>
            </header>

            <!-- Category Hero Banner -->
            <?php
            $featured_category = get_terms(array(
                'taxonomy' => 'video_category',
                'meta_key' => 'featured_category',
                'meta_value' => '1',
                'number' => 1
            ));
            
            if (!empty($featured_category)) :
                $category = $featured_category[0];
                $category_image = get_term_meta($category->term_id, 'category_image', true);
            ?>
            <div class="category-hero mb-8" style="<?php echo $category_image ? 'background-image: url(' . esc_url($category_image) . ');' : ''; ?>">
                <div class="hero-overlay bg-gradient-to-r from-primary to-secondary opacity-90"></div>
                <div class="hero-content container position-relative py-12 text-center text-white">
                    <h2 class="hero-title text-3xl font-bold mb-4">
                        <?php printf(esc_html__('Featured: %s', 'customtube'), esc_html($category->name)); ?>
                    </h2>
                    <p class="hero-description text-lg mb-6 opacity-90">
                        <?php echo esc_html($category->description ?: __('Discover the hottest videos in this category', 'customtube')); ?>
                    </p>
                    <div class="hero-stats d-flex justify-center gap-8 mb-6">
                        <div class="stat-item">
                            <div class="stat-number text-2xl font-bold"><?php echo number_format($category->count); ?></div>
                            <div class="stat-label text-sm opacity-80"><?php esc_html_e('Videos', 'customtube'); ?></div>
                        </div>
                    </div>
                    <a href="<?php echo esc_url(get_term_link($category)); ?>" class="btn btn-white px-6 py-3">
                        <i class="fas fa-play mr-2"></i>
                        <?php esc_html_e('Explore Category', 'customtube'); ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filters and Sorting -->
            <div class="categories-controls mb-6">
                <div class="container">
                    <div class="controls-wrapper bg-surface rounded-lg p-4">
                        <div class="d-flex justify-between align-center flex-wrap gap-4">
                            <!-- Filters Sidebar Toggle -->
                            <div class="filters-section d-flex align-center gap-4">
                                <button id="filters-toggle" class="btn btn-secondary d-md-none">
                                    <i class="fas fa-filter mr-2"></i>
                                    <?php esc_html_e('Filters', 'customtube'); ?>
                                </button>
                                
                                <!-- Quick Filters -->
                                <div class="quick-filters d-none d-md-flex gap-2">
                                    <button class="filter-chip active" data-filter="all">
                                        <?php esc_html_e('All Categories', 'customtube'); ?>
                                    </button>
                                    <button class="filter-chip" data-filter="popular">
                                        <?php esc_html_e('Most Popular', 'customtube'); ?>
                                    </button>
                                    <button class="filter-chip" data-filter="recent">
                                        <?php esc_html_e('Recently Updated', 'customtube'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Sorting -->
                            <div class="sorting-section d-flex align-center gap-3">
                                <label for="category-sort" class="text-sm font-medium"><?php esc_html_e('Sort by:', 'customtube'); ?></label>
                                <select id="category-sort" class="form-select bg-white border border-gray rounded px-3 py-2">
                                    <option value="name"><?php esc_html_e('Name (A-Z)', 'customtube'); ?></option>
                                    <option value="count" selected><?php esc_html_e('Video Count', 'customtube'); ?></option>
                                    <option value="recent"><?php esc_html_e('Recently Added', 'customtube'); ?></option>
                                    <option value="popular"><?php esc_html_e('Most Viewed', 'customtube'); ?></option>
                                </select>
                                
                                <!-- View Toggle -->
                                <div class="view-toggle d-flex">
                                    <button class="view-btn btn btn-secondary p-2 active" data-view="grid">
                                        <i class="fas fa-th"></i>
                                    </button>
                                    <button class="view-btn btn btn-secondary p-2" data-view="list">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Content -->
            <div class="categories-content">
                <div class="container">
                    <div class="categories-layout d-flex gap-6">
                        
                        <!-- Filters Sidebar -->
                        <aside class="filters-sidebar w-64 d-none d-md-block" id="filters-sidebar">
                            <div class="filters-container bg-surface rounded-lg p-4 sticky top-4">
                                <h3 class="filters-title text-lg font-semibold mb-4">
                                    <i class="fas fa-sliders-h mr-2"></i>
                                    <?php esc_html_e('Filters', 'customtube'); ?>
                                </h3>
                                
                                <!-- Video Count Filter -->
                                <div class="filter-group mb-4">
                                    <h4 class="filter-label font-medium mb-2"><?php esc_html_e('Video Count', 'customtube'); ?></h4>
                                    <div class="filter-options">
                                        <label class="filter-option d-flex align-center">
                                            <input type="checkbox" class="filter-checkbox mr-2" data-filter="count" value="1-10">
                                            <span class="text-sm">1-10 <?php esc_html_e('videos', 'customtube'); ?></span>
                                        </label>
                                        <label class="filter-option d-flex align-center">
                                            <input type="checkbox" class="filter-checkbox mr-2" data-filter="count" value="11-50">
                                            <span class="text-sm">11-50 <?php esc_html_e('videos', 'customtube'); ?></span>
                                        </label>
                                        <label class="filter-option d-flex align-center">
                                            <input type="checkbox" class="filter-checkbox mr-2" data-filter="count" value="51-100">
                                            <span class="text-sm">51-100 <?php esc_html_e('videos', 'customtube'); ?></span>
                                        </label>
                                        <label class="filter-option d-flex align-center">
                                            <input type="checkbox" class="filter-checkbox mr-2" data-filter="count" value="100+">
                                            <span class="text-sm">100+ <?php esc_html_e('videos', 'customtube'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Popular Categories -->
                                <div class="filter-group mb-4">
                                    <h4 class="filter-label font-medium mb-2"><?php esc_html_e('Popular Tags', 'customtube'); ?></h4>
                                    <div class="tag-cloud">
                                        <?php
                                        $popular_tags = get_terms(array(
                                            'taxonomy' => 'video_tag',
                                            'orderby' => 'count',
                                            'order' => 'DESC',
                                            'number' => 10
                                        ));
                                        
                                        if ($popular_tags && !is_wp_error($popular_tags)) :
                                            foreach ($popular_tags as $tag) : ?>
                                                <button class="tag-filter btn btn-sm btn-outline-secondary mb-1 mr-1" data-tag="<?php echo esc_attr($tag->slug); ?>">
                                                    <?php echo esc_html($tag->name); ?>
                                                    <span class="tag-count text-xs opacity-70">(<?php echo $tag->count; ?>)</span>
                                                </button>
                                            <?php endforeach;
                                        endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Clear Filters -->
                                <button id="clear-filters" class="btn btn-outline-primary w-full">
                                    <i class="fas fa-times mr-2"></i>
                                    <?php esc_html_e('Clear Filters', 'customtube'); ?>
                                </button>
                            </div>
                        </aside>

                        <!-- Categories Grid -->
                        <main class="categories-main flex-1">
                            <div id="categories-grid" class="categories-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <?php
                                $categories = get_terms(array(
                                    'taxonomy' => 'video_category',
                                    'hide_empty' => true,
                                    'orderby' => 'count',
                                    'order' => 'DESC'
                                ));
                                
                                if ($categories && !is_wp_error($categories)) :
                                    foreach ($categories as $category) :
                                        $category_image = get_term_meta($category->term_id, 'category_image', true);
                                        $category_color = get_term_meta($category->term_id, 'category_color', true) ?: '#6366f1';
                                        ?>
                                        <div class="category-card bg-surface rounded-lg overflow-hidden hover:shadow-lg transition-all group">
                                            <div class="category-thumbnail position-relative">
                                                <?php if ($category_image) : ?>
                                                    <img src="<?php echo esc_url($category_image); ?>" alt="<?php echo esc_attr($category->name); ?>" class="w-full h-32 object-cover">
                                                <?php else : ?>
                                                    <div class="category-placeholder w-full h-32 d-flex align-center justify-center" style="background: linear-gradient(135deg, <?php echo esc_attr($category_color); ?>, <?php echo esc_attr($category_color); ?>88);">
                                                        <i class="fas fa-folder text-3xl text-white opacity-80"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="category-overlay position-absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity d-flex align-center justify-center">
                                                    <div class="overlay-content text-center text-white">
                                                        <i class="fas fa-play text-2xl mb-2"></i>
                                                        <div class="text-sm"><?php esc_html_e('Browse Videos', 'customtube'); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="category-info p-4">
                                                <h3 class="category-title text-lg font-semibold mb-2">
                                                    <a href="<?php echo esc_url(get_term_link($category)); ?>" class="text-primary hover:text-accent no-underline">
                                                        <?php echo esc_html($category->name); ?>
                                                    </a>
                                                </h3>
                                                
                                                <?php if ($category->description) : ?>
                                                    <p class="category-description text-sm text-secondary mb-3 line-clamp-2">
                                                        <?php echo esc_html($category->description); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="category-meta d-flex justify-between align-center text-sm text-secondary">
                                                    <span class="video-count">
                                                        <i class="fas fa-video mr-1"></i>
                                                        <?php printf(_n('%s video', '%s videos', $category->count, 'customtube'), number_format($category->count)); ?>
                                                    </span>
                                                    
                                                    <div class="category-actions">
                                                        <a href="<?php echo esc_url(get_term_link($category)); ?>" class="btn btn-sm btn-primary">
                                                            <?php esc_html_e('Browse', 'customtube'); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                else : ?>
                                    <div class="empty-state col-span-full text-center py-12">
                                        <i class="fas fa-folder-open text-6xl text-gray opacity-50 mb-4"></i>
                                        <h3 class="text-xl font-semibold mb-3"><?php esc_html_e('No Categories Found', 'customtube'); ?></h3>
                                        <p class="text-secondary"><?php esc_html_e('Categories will appear here once videos are added.', 'customtube'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="categories-pagination mt-8">
                                <?php
                                // Add pagination if needed for large numbers of categories
                                $total_categories = wp_count_terms('video_category');
                                if ($total_categories > 20) :
                                ?>
                                <nav class="pagination-nav text-center">
                                    <div class="pagination-controls d-flex justify-center gap-2">
                                        <button class="btn btn-secondary" disabled>
                                            <i class="fas fa-chevron-left mr-1"></i>
                                            <?php esc_html_e('Previous', 'customtube'); ?>
                                        </button>
                                        <span class="pagination-info d-flex align-center px-4 text-sm text-secondary">
                                            <?php esc_html_e('Page 1 of 1', 'customtube'); ?>
                                        </span>
                                        <button class="btn btn-secondary" disabled>
                                            <?php esc_html_e('Next', 'customtube'); ?>
                                            <i class="fas fa-chevron-right ml-1"></i>
                                        </button>
                                    </div>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </main>
                        
                    </div>
                </div>
            </div>

        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<!-- Categories Page JavaScript -->
<script>
(function($) {
    'use strict';
    
    let currentView = 'grid';
    let currentSort = 'count';
    let activeFilters = {};
    
    // Toggle mobile filters
    $('#filters-toggle').on('click', function() {
        $('#filters-sidebar').toggleClass('d-block d-none');
    });
    
    // Quick filter chips
    $('.filter-chip').on('click', function() {
        $('.filter-chip').removeClass('active');
        $(this).addClass('active');
        
        const filter = $(this).data('filter');
        applyQuickFilter(filter);
    });
    
    // Sort change
    $('#category-sort').on('change', function() {
        currentSort = $(this).val();
        applySorting();
    });
    
    // View toggle
    $('.view-btn').on('click', function() {
        const view = $(this).data('view');
        currentView = view;
        
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        
        updateGridView(view);
    });
    
    // Filter checkboxes
    $('.filter-checkbox').on('change', function() {
        const filterType = $(this).data('filter');
        const filterValue = $(this).val();
        
        if (!activeFilters[filterType]) {
            activeFilters[filterType] = [];
        }
        
        if ($(this).is(':checked')) {
            activeFilters[filterType].push(filterValue);
        } else {
            activeFilters[filterType] = activeFilters[filterType].filter(v => v !== filterValue);
        }
        
        applyFilters();
    });
    
    // Tag filters
    $('.tag-filter').on('click', function() {
        $(this).toggleClass('active');
        applyTagFilters();
    });
    
    // Clear filters
    $('#clear-filters').on('click', function() {
        $('.filter-checkbox').prop('checked', false);
        $('.tag-filter').removeClass('active');
        $('.filter-chip').removeClass('active').first().addClass('active');
        activeFilters = {};
        
        $('.category-card').show();
    });
    
    function applyQuickFilter(filter) {
        const $cards = $('.category-card');
        
        switch (filter) {
            case 'all':
                $cards.show();
                break;
            case 'popular':
                $cards.hide();
                $cards.filter(function() {
                    const count = parseInt($(this).find('.video-count').text().match(/\d+/)[0]);
                    return count >= 20;
                }).show();
                break;
            case 'recent':
                // This would require additional data from the server
                $cards.show();
                break;
        }
    }
    
    function applySorting() {
        const $container = $('#categories-grid');
        const $cards = $('.category-card').detach();
        
        $cards.sort(function(a, b) {
            switch (currentSort) {
                case 'name':
                    const nameA = $(a).find('.category-title a').text().toLowerCase();
                    const nameB = $(b).find('.category-title a').text().toLowerCase();
                    return nameA.localeCompare(nameB);
                    
                case 'count':
                    const countA = parseInt($(a).find('.video-count').text().match(/\d+/)[0]);
                    const countB = parseInt($(b).find('.video-count').text().match(/\d+/)[0]);
                    return countB - countA;
                    
                case 'recent':
                case 'popular':
                default:
                    return 0;
            }
        });
        
        $container.append($cards);
    }
    
    function updateGridView(view) {
        const $grid = $('#categories-grid');
        
        if (view === 'list') {
            $grid.removeClass('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4')
                 .addClass('categories-list');
            $('.category-card').addClass('list-view');
        } else {
            $grid.removeClass('categories-list')
                 .addClass('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4');
            $('.category-card').removeClass('list-view');
        }
    }
    
    function applyFilters() {
        const $cards = $('.category-card');
        
        $cards.each(function() {
            const $card = $(this);
            let shouldShow = true;
            
            // Check count filters
            if (activeFilters.count && activeFilters.count.length > 0) {
                const count = parseInt($card.find('.video-count').text().match(/\d+/)[0]);
                const matchesCount = activeFilters.count.some(range => {
                    switch (range) {
                        case '1-10': return count >= 1 && count <= 10;
                        case '11-50': return count >= 11 && count <= 50;
                        case '51-100': return count >= 51 && count <= 100;
                        case '100+': return count > 100;
                        default: return false;
                    }
                });
                shouldShow = shouldShow && matchesCount;
            }
            
            $card.toggle(shouldShow);
        });
    }
    
    function applyTagFilters() {
        // This would require additional implementation with AJAX
        // to filter categories by associated tags
    }
    
})(jQuery);
</script>

<!-- Add custom CSS for list view -->
<style>
.categories-list .category-card.list-view {
    display: flex;
    margin-bottom: 1rem;
}

.categories-list .category-card.list-view .category-thumbnail {
    width: 200px;
    flex-shrink: 0;
}

.categories-list .category-card.list-view .category-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.filter-chip {
    padding: 0.5rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    background: white;
    color: var(--color-text);
    cursor: pointer;
    transition: var(--transition-colors);
    font-size: 0.875rem;
}

.filter-chip.active,
.filter-chip:hover {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.tag-filter.active {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}
</style>

<?php get_footer(); ?>
