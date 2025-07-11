<?php
/**
 * Template Name: Short Videos
 * 
 * Page template for displaying short videos
 *
 * @package CustomTube
 */

get_header(); ?>

<div class="short-videos-page container py-4">
    <!-- Page Header -->
    <div class="page-header text-center mb-8">
        <h1 class="page-title text-3xl font-bold mb-4 text-primary">
            <i class="fas fa-video mr-3 text-info"></i>
            <?php esc_html_e('Short Videos', 'customtube'); ?>
        </h1>
        <p class="page-description text-lg text-secondary mb-6">
            <?php esc_html_e('Quick clips and highlights - perfect for a quick watch', 'customtube'); ?>
        </p>
    </div>

    <!-- Filter Controls -->
    <div class="short-videos-controls d-flex justify-between align-center mb-6 flex-wrap gap-4">
        <div class="duration-filters d-flex gap-2 flex-wrap">
            <button class="duration-filter btn btn-secondary text-sm px-4 py-2 active" data-duration="all">
                <?php esc_html_e('All Short Videos', 'customtube'); ?>
            </button>
            <button class="duration-filter btn btn-secondary text-sm px-4 py-2" data-duration="30">
                <?php esc_html_e('Under 30s', 'customtube'); ?>
            </button>
            <button class="duration-filter btn btn-secondary text-sm px-4 py-2" data-duration="60">
                <?php esc_html_e('Under 1 min', 'customtube'); ?>
            </button>
            <button class="duration-filter btn btn-secondary text-sm px-4 py-2" data-duration="180">
                <?php esc_html_e('Under 3 min', 'customtube'); ?>
            </button>
        </div>
        
        <div class="short-controls d-flex gap-3 align-center">
            <select id="short-sort" class="form-select bg-surface border border-gray rounded px-3 py-2">
                <option value="newest"><?php esc_html_e('Newest First', 'customtube'); ?></option>
                <option value="popular"><?php esc_html_e('Most Popular', 'customtube'); ?></option>
                <option value="views"><?php esc_html_e('Most Viewed', 'customtube'); ?></option>
                <option value="shortest"><?php esc_html_e('Shortest First', 'customtube'); ?></option>
                <option value="random"><?php esc_html_e('Random', 'customtube'); ?></option>
            </select>
            
            <div class="view-toggle d-flex">
                <button class="view-btn btn btn-secondary p-2 active" data-view="grid">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn btn btn-secondary p-2" data-view="masonry">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="short-categories mb-6">
        <div class="category-tabs d-flex gap-2 flex-wrap justify-center">
            <button class="category-tab btn btn-secondary text-sm px-4 py-2 active" data-category="">
                <?php esc_html_e('All Categories', 'customtube'); ?>
            </button>
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'video_category',
                'hide_empty' => true,
                'number' => 8
            ));
            
            if ($categories && !is_wp_error($categories)) :
                foreach ($categories as $category) : ?>
                    <button class="category-tab btn btn-secondary text-sm px-4 py-2" data-category="<?php echo esc_attr($category->slug); ?>">
                        <?php echo esc_html($category->name); ?>
                    </button>
                <?php endforeach;
            endif; ?>
        </div>
    </div>

    <!-- Videos Count -->
    <div class="videos-count mb-4">
        <span class="text-sm text-secondary">
            <i class="fas fa-video mr-2"></i>
            <span id="short-videos-count"><?php esc_html_e('Loading...', 'customtube'); ?></span>
        </span>
    </div>

    <!-- Videos Grid -->
    <div id="short-videos-grid" class="short-videos-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <!-- Videos will be loaded here via AJAX -->
        <div class="loading-container d-flex justify-center align-center py-12 col-span-full">
            <div class="loading-spinner mr-4"></div>
            <span class="text-secondary"><?php esc_html_e('Loading short videos...', 'customtube'); ?></span>
        </div>
    </div>

    <!-- Load More Button -->
    <div class="load-more-container text-center mt-8">
        <button id="load-more-shorts" class="btn btn-primary px-6 py-3 d-none">
            <i class="fas fa-plus mr-2"></i>
            <?php esc_html_e('Load More Videos', 'customtube'); ?>
        </button>
    </div>

    <!-- Empty State -->
    <div id="short-videos-empty" class="empty-state text-center py-12 d-none">
        <div class="empty-icon mb-4">
            <i class="fas fa-video text-6xl text-gray opacity-50"></i>
        </div>
        <h3 class="empty-title text-xl font-semibold mb-3 text-primary">
            <?php esc_html_e('No Short Videos Found', 'customtube'); ?>
        </h3>
        <p class="empty-text text-secondary mb-6">
            <?php esc_html_e('Try adjusting your filters or check back later for new content.', 'customtube'); ?>
        </p>
        <button class="btn btn-secondary px-6 py-3" onclick="resetFilters()">
            <i class="fas fa-refresh mr-2"></i>
            <?php esc_html_e('Reset Filters', 'customtube'); ?>
        </button>
    </div>
</div>

<!-- Short Videos JavaScript -->
<script>
(function($) {
    'use strict';
    
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let currentSort = 'newest';
    let currentView = 'grid';
    let currentDuration = 'all';
    let currentCategory = '';
    
    function loadShortVideos(page = 1, sort = 'newest', duration = 'all', category = '', append = false) {
        if (isLoading) return;
        isLoading = true;
        
        const $grid = $('#short-videos-grid');
        const $loadMore = $('#load-more-shorts');
        const $empty = $('#short-videos-empty');
        
        // Show loading state
        if (!append) {
            $grid.html('<div class="loading-container d-flex justify-center align-center py-12 col-span-full"><div class="loading-spinner mr-4"></div><span class="text-secondary"><?php esc_html_e('Loading...', 'customtube'); ?></span></div>');
        }
        
        $.ajax({
            url: customtubeData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_short_videos',
                page: page,
                sort: sort,
                duration: duration,
                category: category,
                nonce: customtubeData.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update count
                    $('#short-videos-count').text(data.total + ' ' + (data.total === 1 ? '<?php esc_html_e('video', 'customtube'); ?>' : '<?php esc_html_e('videos', 'customtube'); ?>'));
                    
                    if (data.videos.length === 0 && page === 1) {
                        // Show empty state
                        $grid.addClass('d-none');
                        $empty.removeClass('d-none');
                        $loadMore.addClass('d-none');
                    } else {
                        // Show videos
                        $grid.removeClass('d-none');
                        $empty.addClass('d-none');
                        
                        if (!append) {
                            $grid.empty();
                        } else {
                            $grid.find('.loading-container').remove();
                        }
                        
                        // Add video cards
                        data.videos.forEach(function(video) {
                            const videoCard = createShortVideoCard(video);
                            $grid.append(videoCard);
                        });
                        
                        // Update load more button
                        if (data.has_more) {
                            $loadMore.removeClass('d-none');
                        } else {
                            $loadMore.addClass('d-none');
                        }
                        
                        hasMore = data.has_more;
                    }
                }
                
                isLoading = false;
            },
            error: function() {
                isLoading = false;
                $grid.html('<div class="error-message text-center py-12 col-span-full"><i class="fas fa-exclamation-triangle text-error text-2xl mb-3"></i><p class="text-secondary"><?php esc_html_e('Error loading videos. Please try again.', 'customtube'); ?></p></div>');
            }
        });
    }
    
    function createShortVideoCard(video) {
        const isMasonry = currentView === 'masonry';
        const cardClass = isMasonry ? 'short-video-card masonry-item' : 'short-video-card';
        
        return `
            <div class="${cardClass} bg-surface rounded-lg overflow-hidden hover:shadow-lg transition-all group">
                <div class="video-thumbnail-container position-relative">
                    <a href="${video.url}" class="video-thumbnail-link">
                        <img src="${video.thumbnail}" alt="${video.title}" class="video-thumbnail w-full ${isMasonry ? 'h-auto' : 'aspect-video'} object-cover">
                        <div class="video-duration position-absolute bottom-2 right-2 bg-dark text-white px-2 py-1 rounded text-xs font-bold">
                            ${video.duration}
                        </div>
                        <div class="short-badge position-absolute top-2 left-2 bg-info text-white px-2 py-1 rounded text-xs font-bold">
                            <i class="fas fa-clock mr-1"></i>
                            SHORT
                        </div>
                        <div class="play-overlay position-absolute inset-0 d-flex align-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black bg-opacity-50">
                            <div class="play-button bg-primary rounded-full w-12 h-12 d-flex align-center justify-center transform scale-90 group-hover:scale-100 transition-transform">
                                <i class="fas fa-play text-white"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="video-info p-3">
                    <h3 class="video-title font-semibold mb-2 text-sm line-clamp-2 leading-tight">
                        <a href="${video.url}" class="text-primary hover:text-accent no-underline">${video.title}</a>
                    </h3>
                    <div class="video-meta d-flex justify-between align-center text-xs text-secondary mb-2">
                        <span class="video-views">
                            <i class="fas fa-eye mr-1"></i>
                            ${video.views}
                        </span>
                        <span class="video-date">${video.date}</span>
                    </div>
                    ${video.category ? `<div class="video-category mb-2"><span class="category-tag bg-secondary text-xs px-2 py-1 rounded">${video.category}</span></div>` : ''}
                    <div class="video-actions d-flex justify-between align-center">
                        <button class="like-btn btn btn-secondary text-xs px-2 py-1 ${video.liked ? 'liked bg-primary' : ''}" data-video-id="${video.id}">
                            <i class="fas fa-heart mr-1"></i>
                            <span class="like-count">${video.likes}</span>
                        </button>
                        <div class="video-actions-right d-flex gap-1">
                            <button class="share-btn btn btn-secondary text-xs px-2 py-1" data-url="${video.url}" data-title="${video.title}">
                                <i class="fas fa-share"></i>
                            </button>
                            <button class="add-to-playlist-btn btn btn-secondary text-xs px-2 py-1" data-video-id="${video.id}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function resetFilters() {
        currentSort = 'newest';
        currentDuration = 'all';
        currentCategory = '';
        currentPage = 1;
        
        // Reset UI
        $('.duration-filter').removeClass('active').first().addClass('active');
        $('.category-tab').removeClass('active').first().addClass('active');
        $('#short-sort').val('newest');
        
        // Reload videos
        loadShortVideos();
    }
    
    // Initialize when page loads
    $(document).ready(function() {
        loadShortVideos();
        
        // Duration filter handlers
        $('.duration-filter').on('click', function() {
            const duration = $(this).data('duration');
            currentDuration = duration;
            currentPage = 1;
            
            $('.duration-filter').removeClass('active');
            $(this).addClass('active');
            
            loadShortVideos(1, currentSort, currentDuration, currentCategory, false);
        });
        
        // Category tab handlers
        $('.category-tab').on('click', function() {
            const category = $(this).data('category');
            currentCategory = category;
            currentPage = 1;
            
            $('.category-tab').removeClass('active');
            $(this).addClass('active');
            
            loadShortVideos(1, currentSort, currentDuration, currentCategory, false);
        });
        
        // Sort change handler
        $('#short-sort').on('change', function() {
            currentSort = $(this).val();
            currentPage = 1;
            loadShortVideos(1, currentSort, currentDuration, currentCategory, false);
        });
        
        // View toggle handler
        $('.view-btn').on('click', function() {
            const view = $(this).data('view');
            currentView = view;
            
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            
            const $grid = $('#short-videos-grid');
            if (view === 'masonry') {
                $grid.removeClass('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5').addClass('masonry-grid');
            } else {
                $grid.removeClass('masonry-grid').addClass('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5');
            }
            
            // Reload to apply new view
            loadShortVideos(1, currentSort, currentDuration, currentCategory, false);
        });
        
        // Load more handler
        $('#load-more-shorts').on('click', function() {
            if (hasMore && !isLoading) {
                currentPage++;
                loadShortVideos(currentPage, currentSort, currentDuration, currentCategory, true);
            }
        });
        
        // Like button handler
        $(document).on('click', '.like-btn', function() {
            const $btn = $(this);
            const videoId = $btn.data('video-id');
            
            $.ajax({
                url: customtubeData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_toggle_like',
                    post_id: videoId,
                    nonce: customtubeData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.find('.like-count').text(response.data.likes_count);
                        
                        if (response.data.liked) {
                            $btn.addClass('liked bg-primary').removeClass('bg-secondary');
                        } else {
                            $btn.removeClass('liked bg-primary').addClass('bg-secondary');
                        }
                    }
                }
            });
        });
        
        // Share button handler
        $(document).on('click', '.share-btn', function() {
            const url = $(this).data('url');
            const title = $(this).data('title');
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                }).catch((error) => console.log('Error sharing', error));
            } else {
                // Fallback: copy to clipboard
                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = url;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                
                // Show notification
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.html('<i class="fas fa-check"></i>');
                
                setTimeout(function() {
                    $btn.html(originalHtml);
                }, 1500);
            }
        });
        
        // Add to playlist handler (placeholder)
        $(document).on('click', '.add-to-playlist-btn', function() {
            // This would open a playlist modal
            console.log('Add to playlist:', $(this).data('video-id'));
        });
    });
    
    // Make resetFilters globally available
    window.resetFilters = resetFilters;
    
})(jQuery);
</script>

<!-- Add masonry CSS for grid layout -->
<style>
.masonry-grid {
    column-count: 5;
    column-gap: 1rem;
}

.masonry-item {
    break-inside: avoid;
    margin-bottom: 1rem;
}

@media (max-width: 1200px) {
    .masonry-grid {
        column-count: 4;
    }
}

@media (max-width: 1024px) {
    .masonry-grid {
        column-count: 3;
    }
}

@media (max-width: 768px) {
    .masonry-grid {
        column-count: 2;
    }
}

@media (max-width: 480px) {
    .masonry-grid {
        column-count: 1;
    }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php get_footer(); ?>
