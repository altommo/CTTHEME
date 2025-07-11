<?php
/**
 * Template Name: Liked Videos
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('liked-videos-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-heart mr-3 text-error"></i>
                            <?php esc_html_e('Liked Videos', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Your favorite videos, saved for easy access', 'customtube'); ?>
                        </p>
                    </div>
                </div>
            </header>

            <?php if (!is_user_logged_in()) : ?>
                <!-- Not Logged In State -->
                <div class="not-logged-in">
                    <div class="container">
                        <div class="login-prompt text-center py-12">
                            <div class="prompt-icon mb-6">
                                <i class="fas fa-heart text-6xl text-gray opacity-50"></i>
                            </div>
                            <h2 class="prompt-title text-2xl font-bold mb-4">
                                <?php esc_html_e('Sign In to View Your Liked Videos', 'customtube'); ?>
                            </h2>
                            <p class="prompt-text text-lg text-secondary mb-6">
                                <?php esc_html_e('Create an account or sign in to start liking videos and building your personal collection.', 'customtube'); ?>
                            </p>
                            <div class="prompt-actions d-flex justify-center gap-4">
                                <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn btn-primary px-6 py-3">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    <?php esc_html_e('Sign In', 'customtube'); ?>
                                </a>
                                <a href="<?php echo esc_url(home_url('/register')); ?>" class="btn btn-outline-primary px-6 py-3">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    <?php esc_html_e('Create Account', 'customtube'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <!-- Logged In Content -->
                <div class="liked-videos-content">
                    <div class="container">
                        
                        <!-- User Stats -->
                        <div class="user-stats mb-6">
                            <div class="stats-container bg-surface rounded-lg p-4">
                                <div class="grid md:grid-cols-4 gap-4 text-center">
                                    <div class="stat-item">
                                        <div class="stat-number text-2xl font-bold text-error" id="liked-count">0</div>
                                        <div class="stat-label text-sm text-secondary"><?php esc_html_e('Liked Videos', 'customtube'); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-2xl font-bold text-primary" id="total-duration">0:00</div>
                                        <div class="stat-label text-sm text-secondary"><?php esc_html_e('Total Duration', 'customtube'); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-2xl font-bold text-success" id="categories-count">0</div>
                                        <div class="stat-label text-sm text-secondary"><?php esc_html_e('Categories', 'customtube'); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-2xl font-bold text-info" id="recent-likes">0</div>
                                        <div class="stat-label text-sm text-secondary"><?php esc_html_e('This Week', 'customtube'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controls -->
                        <div class="liked-videos-controls mb-6">
                            <div class="controls-wrapper bg-surface rounded-lg p-4">
                                <div class="d-flex justify-between align-center flex-wrap gap-4">
                                    <!-- Search -->
                                    <div class="search-section">
                                        <div class="search-input position-relative">
                                            <i class="fas fa-search position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                            <input type="text" 
                                                   id="liked-search" 
                                                   class="form-input pl-10 pr-4 py-2 border border-gray rounded-lg" 
                                                   placeholder="<?php esc_attr_e('Search your liked videos...', 'customtube'); ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Sort and View Options -->
                                    <div class="controls-section d-flex align-center gap-3">
                                        <select id="liked-sort" class="form-select bg-white border border-gray rounded px-3 py-2">
                                            <option value="recent"><?php esc_html_e('Recently Liked', 'customtube'); ?></option>
                                            <option value="oldest"><?php esc_html_e('Oldest First', 'customtube'); ?></option>
                                            <option value="title"><?php esc_html_e('Title (A-Z)', 'customtube'); ?></option>
                                            <option value="duration"><?php esc_html_e('Duration', 'customtube'); ?></option>
                                        </select>
                                        
                                        <div class="view-toggle d-flex">
                                            <button class="view-btn btn btn-secondary p-2 active" data-view="grid">
                                                <i class="fas fa-th"></i>
                                            </button>
                                            <button class="view-btn btn btn-secondary p-2" data-view="list">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        </div>
                                        
                                        <button id="clear-all-likes" class="btn btn-outline-error px-4 py-2">
                                            <i class="fas fa-trash mr-2"></i>
                                            <?php esc_html_e('Clear All', 'customtube'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Videos Grid -->
                        <div id="liked-videos-grid" class="liked-videos-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <!-- Videos will be loaded here via AJAX -->
                            <div class="loading-container d-flex justify-center align-center py-12 col-span-full">
                                <div class="loading-spinner mr-4"></div>
                                <span class="text-secondary"><?php esc_html_e('Loading your liked videos...', 'customtube'); ?></span>
                            </div>
                        </div>

                        <!-- Load More Button -->
                        <div class="load-more-container text-center mt-8">
                            <button id="load-more-liked" class="btn btn-primary px-6 py-3 d-none">
                                <i class="fas fa-plus mr-2"></i>
                                <?php esc_html_e('Load More Videos', 'customtube'); ?>
                            </button>
                        </div>

                        <!-- Empty State -->
                        <div id="liked-videos-empty" class="empty-state text-center py-12 d-none">
                            <div class="empty-icon mb-4">
                                <i class="fas fa-heart text-6xl text-gray opacity-50"></i>
                            </div>
                            <h3 class="empty-title text-xl font-semibold mb-3 text-primary">
                                <?php esc_html_e('No Liked Videos Yet', 'customtube'); ?>
                            </h3>
                            <p class="empty-text text-secondary mb-6">
                                <?php esc_html_e('Start exploring and like videos to build your personal collection!', 'customtube'); ?>
                            </p>
                            <a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary px-6 py-3">
                                <i class="fas fa-search mr-2"></i>
                                <?php esc_html_e('Discover Videos', 'customtube'); ?>
                            </a>
                        </div>

                        <!-- Bulk Actions -->
                        <div id="bulk-actions" class="bulk-actions bg-primary text-white rounded-lg p-4 mt-6 d-none">
                            <div class="d-flex justify-between align-center">
                                <div class="selected-info">
                                    <span id="selected-count">0</span> <?php esc_html_e('videos selected', 'customtube'); ?>
                                </div>
                                <div class="bulk-buttons d-flex gap-3">
                                    <button id="bulk-unlike" class="btn btn-outline-white btn-sm">
                                        <i class="fas fa-heart-broken mr-2"></i>
                                        <?php esc_html_e('Unlike Selected', 'customtube'); ?>
                                    </button>
                                    <button id="bulk-playlist" class="btn btn-outline-white btn-sm">
                                        <i class="fas fa-plus mr-2"></i>
                                        <?php esc_html_e('Add to Playlist', 'customtube'); ?>
                                    </button>
                                    <button id="bulk-share" class="btn btn-outline-white btn-sm">
                                        <i class="fas fa-share mr-2"></i>
                                        <?php esc_html_e('Share Selected', 'customtube'); ?>
                                    </button>
                                    <button id="clear-selection" class="btn btn-outline-white btn-sm">
                                        <i class="fas fa-times mr-2"></i>
                                        <?php esc_html_e('Clear Selection', 'customtube'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<!-- Liked Videos JavaScript -->
<script>
(function($) {
    'use strict';
    
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let currentSort = 'recent';
    let currentView = 'grid';
    let searchQuery = '';
    let selectedVideos = new Set();
    
    // Only run if user is logged in
    <?php if (is_user_logged_in()) : ?>
    
    function loadLikedVideos(page = 1, sort = 'recent', search = '', append = false) {
        if (isLoading) return;
        isLoading = true;
        
        const $grid = $('#liked-videos-grid');
        const $loadMore = $('#load-more-liked');
        const $empty = $('#liked-videos-empty');
        
        // Show loading state
        if (!append) {
            $grid.html('<div class="loading-container d-flex justify-center align-center py-12 col-span-full"><div class="loading-spinner mr-4"></div><span class="text-secondary"><?php esc_html_e('Loading...', 'customtube'); ?></span></div>');
        }
        
        $.ajax({
            url: customtubeData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_liked_videos',
                page: page,
                sort: sort,
                search: search,
                nonce: customtubeData.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update stats
                    updateLikedStats(data);
                    
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
                            const videoCard = createLikedVideoCard(video);
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
                $grid.html('<div class="error-message text-center py-12 col-span-full"><i class="fas fa-exclamation-triangle text-error text-2xl mb-3"></i><p class="text-secondary"><?php esc_html_e('Error loading liked videos. Please try again.', 'customtube'); ?></p></div>');
            }
        });
    }
    
    function createLikedVideoCard(video) {
        const isSelected = selectedVideos.has(video.id);
        
        return `
            <div class="liked-video-card bg-surface rounded-lg overflow-hidden hover:shadow-lg transition-all group position-relative ${isSelected ? 'selected' : ''}" data-video-id="${video.id}">
                <div class="video-selection position-absolute top-2 left-2 z-10">
                    <input type="checkbox" class="video-checkbox form-checkbox" ${isSelected ? 'checked' : ''}>
                </div>
                <div class="video-thumbnail-container position-relative">
                    <a href="${video.url}" class="video-thumbnail-link">
                        <img src="${video.thumbnail}" alt="${video.title}" class="video-thumbnail w-full aspect-video object-cover">
                        <div class="video-duration position-absolute bottom-2 right-2 bg-dark text-white px-2 py-1 rounded text-xs font-bold">
                            ${video.duration}
                        </div>
                        <div class="liked-badge position-absolute top-2 right-2 bg-error text-white px-2 py-1 rounded text-xs font-bold">
                            <i class="fas fa-heart"></i>
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
                    <div class="video-actions d-flex justify-between align-center">
                        <button class="unlike-btn btn btn-error text-xs px-2 py-1" data-video-id="${video.id}">
                            <i class="fas fa-heart-broken mr-1"></i>
                            <?php esc_html_e('Unlike', 'customtube'); ?>
                        </button>
                        <div class="video-actions-right d-flex gap-1">
                            <button class="share-btn btn btn-secondary text-xs px-2 py-1" data-url="${video.url}" data-title="${video.title}">
                                <i class="fas fa-share"></i>
                            </button>
                            <button class="playlist-btn btn btn-secondary text-xs px-2 py-1" data-video-id="${video.id}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function updateLikedStats(data) {
        $('#liked-count').text(data.total || '0');
        $('#total-duration').text(data.total_duration || '0:00');
        $('#categories-count').text(data.categories_count || '0');
        $('#recent-likes').text(data.recent_count || '0');
    }
    
    // Initialize when page loads
    $(document).ready(function() {
        loadLikedVideos();
        
        // Search functionality
        let searchTimeout;
        $('#liked-search').on('input', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                searchQuery = query;
                currentPage = 1;
                loadLikedVideos(1, currentSort, searchQuery, false);
            }, 500);
        });
        
        // Sort change
        $('#liked-sort').on('change', function() {
            currentSort = $(this).val();
            currentPage = 1;
            loadLikedVideos(1, currentSort, searchQuery, false);
        });
        
        // View toggle
        $('.view-btn').on('click', function() {
            const view = $(this).data('view');
            currentView = view;
            
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            
            updateGridView(view);
        });
        
        // Load more
        $('#load-more-liked').on('click', function() {
            if (hasMore && !isLoading) {
                currentPage++;
                loadLikedVideos(currentPage, currentSort, searchQuery, true);
            }
        });
        
        // Video selection
        $(document).on('change', '.video-checkbox', function() {
            const videoId = parseInt($(this).closest('.liked-video-card').data('video-id'));
            const $card = $(this).closest('.liked-video-card');
            
            if ($(this).is(':checked')) {
                selectedVideos.add(videoId);
                $card.addClass('selected');
            } else {
                selectedVideos.delete(videoId);
                $card.removeClass('selected');
            }
            
            updateBulkActions();
        });
        
        // Unlike individual video
        $(document).on('click', '.unlike-btn', function() {
            const videoId = $(this).data('video-id');
            const $card = $(this).closest('.liked-video-card');
            
            $.ajax({
                url: customtubeData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'customtube_toggle_like',
                    post_id: videoId,
                    nonce: customtubeData.nonce
                },
                success: function(response) {
                    if (response.success && !response.data.liked) {
                        $card.fadeOut(300, function() {
                            $(this).remove();
                            // Update stats
                            const currentCount = parseInt($('#liked-count').text()) - 1;
                            $('#liked-count').text(Math.max(0, currentCount));
                        });
                    }
                }
            });
        });
        
        // Clear all likes
        $('#clear-all-likes').on('click', function() {
            if (confirm('<?php esc_html_e('Are you sure you want to unlike all videos? This action cannot be undone.', 'customtube'); ?>')) {
                // Implementation for clearing all likes
                location.reload();
            }
        });
        
        // Bulk actions
        $('#bulk-unlike').on('click', function() {
            if (selectedVideos.size > 0 && confirm(`<?php esc_html_e('Unlike', 'customtube'); ?> ${selectedVideos.size} <?php esc_html_e('selected videos?', 'customtube'); ?>`)) {
                // Bulk unlike implementation
                Array.from(selectedVideos).forEach(videoId => {
                    $(`.liked-video-card[data-video-id="${videoId}"]`).fadeOut();
                });
                selectedVideos.clear();
                updateBulkActions();
            }
        });
        
        $('#clear-selection').on('click', function() {
            selectedVideos.clear();
            $('.video-checkbox').prop('checked', false);
            $('.liked-video-card').removeClass('selected');
            updateBulkActions();
        });
    });
    
    function updateGridView(view) {
        const $grid = $('#liked-videos-grid');
        
        if (view === 'list') {
            $grid.removeClass('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5')
                 .addClass('liked-videos-list');
            $('.liked-video-card').addClass('list-view');
        } else {
            $grid.removeClass('liked-videos-list')
                 .addClass('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5');
            $('.liked-video-card').removeClass('list-view');
        }
    }
    
    function updateBulkActions() {
        const count = selectedVideos.size;
        const $bulkActions = $('#bulk-actions');
        
        if (count > 0) {
            $bulkActions.removeClass('d-none');
            $('#selected-count').text(count);
        } else {
            $bulkActions.addClass('d-none');
        }
    }
    
    <?php endif; ?>
    
})(jQuery);
</script>

<!-- Custom Styles -->
<style>
.liked-video-card.selected {
    border: 2px solid var(--color-primary);
    transform: scale(0.98);
}

.liked-videos-list .liked-video-card.list-view {
    display: flex;
    margin-bottom: 1rem;
}

.liked-videos-list .liked-video-card.list-view .video-thumbnail-container {
    width: 200px;
    flex-shrink: 0;
}

.liked-videos-list .liked-video-card.list-view .video-info {
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

.loading-spinner {
    width: 1rem;
    height: 1rem;
    border: 2px solid var(--color-border);
    border-top: 2px solid var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<?php get_footer(); ?>
