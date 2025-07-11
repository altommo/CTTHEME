<?php
/**
 * Template Name: Trending Page
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('trending-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-fire mr-3 text-error"></i>
                            <?php esc_html_e('Trending Videos', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Discover what\'s hot and trending right now', 'customtube'); ?>
                        </p>
                    </div>
                </div>
            </header>

            <!-- Hero Carousel for Top Trending -->
            <div class="trending-hero mb-8">
                <div class="container">
                    <div class="hero-carousel bg-surface rounded-lg overflow-hidden" data-carousel="trending-hero-carousel" data-carousel-options='{"autoplay": true, "loop": true, "displayMode": "full-width-hero"}'>
                        <div class="carousel-header bg-primary text-white p-4">
                            <h2 class="text-xl font-bold mb-0">
                                <i class="fas fa-trophy mr-2"></i>
                                <?php esc_html_e('Top 5 Trending Now', 'customtube'); ?>
                            </h2>
                        </div>
                        
                        <!-- Slides will be rendered by JavaScript Carousel component -->
                        <div class="loading-container d-flex justify-center align-center py-12">
                            <div class="loading-spinner mr-4"></div>
                            <span class="text-secondary"><?php esc_html_e('Loading trending videos...', 'customtube'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Filter Tabs -->
            <div class="trending-filters mb-6">
                <div class="container">
                    <div class="filter-tabs d-flex justify-center gap-2 flex-wrap">
                        <button class="time-filter btn btn-secondary px-4 py-2 active" data-period="today">
                            <i class="fas fa-clock mr-2"></i>
                            <?php esc_html_e('Today', 'customtube'); ?>
                        </button>
                        <button class="time-filter btn btn-secondary px-4 py-2" data-period="week">
                            <i class="fas fa-calendar-week mr-2"></i>
                            <?php esc_html_e('This Week', 'customtube'); ?>
                        </button>
                        <button class="time-filter btn btn-secondary px-4 py-2" data-period="month">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <?php esc_html_e('This Month', 'customtube'); ?>
                        </button>
                        <button class="time-filter btn btn-secondary px-4 py-2" data-period="all">
                            <i class="fas fa-infinity mr-2"></i>
                            <?php esc_html_e('All Time', 'customtube'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trending Content -->
            <div class="trending-content">
                <div class="container">
                    
                    <!-- Trending Stats -->
                    <div class="trending-stats mb-6">
                        <div class="stats-container bg-surface rounded-lg p-4">
                            <div class="grid md:grid-cols-4 gap-4 text-center">
                                <div class="stat-item">
                                    <div class="stat-number text-2xl font-bold text-primary" id="total-views">0</div>
                                    <div class="stat-label text-sm text-secondary"><?php esc_html_e('Total Views', 'customtube'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number text-2xl font-bold text-success" id="trending-count">0</div>
                                    <div class="stat-label text-sm text-secondary"><?php esc_html_e('Trending Videos', 'customtube'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number text-2xl font-bold text-info" id="hot-categories">0</div>
                                    <div class="stat-label text-sm text-secondary"><?php esc_html_e('Hot Categories', 'customtube'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number text-2xl font-bold text-warning" id="growth-rate">+0%</div>
                                    <div class="stat-label text-sm text-secondary"><?php esc_html_e('Growth Rate', 'customtube'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trending Videos Grid -->
                    <div id="trending-videos-grid" class="trending-videos-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <!-- Videos will be loaded here via AJAX -->
                        <div class="loading-container d-flex justify-center align-center py-12 col-span-full">
                            <div class="loading-spinner mr-4"></div>
                            <span class="text-secondary"><?php esc_html_e('Loading trending videos...', 'customtube'); ?></span>
                        </div>
                    </div>

                    <!-- Load More Button -->
                    <div class="load-more-container text-center mt-8">
                        <button id="load-more-trending" class="btn btn-primary px-6 py-3 d-none">
                            <i class="fas fa-plus mr-2"></i>
                            <?php esc_html_e('Load More Trending', 'customtube'); ?>
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div id="trending-empty" class="empty-state text-center py-12 d-none">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-fire text-6xl text-gray opacity-50"></i>
                        </div>
                        <h3 class="empty-title text-xl font-semibold mb-3 text-primary">
                            <?php esc_html_e('No Trending Videos Found', 'customtube'); ?>
                        </h3>
                        <p class="empty-text text-secondary mb-6">
                            <?php esc_html_e('Check back later to see what\'s trending!', 'customtube'); ?>
                        </p>
                    </div>

                </div>
            </div>

        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<!-- Trending Page JavaScript -->
<script>
(function($) {
    'use strict';
    
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let currentPeriod = 'today';
    
    function loadTrendingVideos(period = 'today', page = 1, append = false) {
        if (isLoading) return;
        isLoading = true;
        
        const $grid = $('#trending-videos-grid');
        const $loadMore = $('#load-more-trending');
        const $empty = $('#trending-empty');
        
        // Show loading state
        if (!append) {
            $grid.html('<div class="loading-container d-flex justify-center align-center py-12 col-span-full"><div class="loading-spinner mr-4"></div><span class="text-secondary"><?php esc_html_e('Loading...', 'customtube'); ?></span></div>');
        }
        
        $.ajax({
            url: customtubeData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_trending_videos',
                period: period,
                page: page,
                nonce: customtubeData.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update stats
                    updateTrendingStats(data.stats);
                    
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
                        data.videos.forEach(function(video, index) {
                            const videoCard = createTrendingVideoCard(video, index + ((page - 1) * 20));
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
                    
                    // Update hero carousel if first page
                    if (page === 1 && data.videos.length > 0) {
                        // Pass slides data to the Carousel component
                        const heroCarouselElement = document.querySelector('[data-carousel="trending-hero-carousel"]');
                        if (heroCarouselElement && window.CustomTube && window.CustomTube.getComponent(`carousel-${heroCarouselElement.id}`)) {
                            const carouselInstance = window.CustomTube.getComponent(`carousel-${heroCarouselElement.id}`);
                            carouselInstance.options.slides = data.videos.slice(0, 5).map(video => ({
                                type: 'image', // Assuming images for hero carousel
                                imageUrl: video.thumbnail,
                                title: video.title,
                                description: video.description || '', // Add description if available
                                buttonText: 'Watch Now',
                                buttonUrl: video.url
                            }));
                            carouselInstance.render(); // Re-render carousel with new slides
                            carouselInstance.goTo(0, false); // Go to first slide
                            carouselInstance.play(); // Start autoplay
                        }
                    }
                }
                
                isLoading = false;
            },
            error: function() {
                isLoading = false;
                $grid.html('<div class="error-message text-center py-12 col-span-full"><i class="fas fa-exclamation-triangle text-error text-2xl mb-3"></i><p class="text-secondary"><?php esc_html_e('Error loading trending videos. Please try again.', 'customtube'); ?></p></div>');
            }
        });
    }
    
    function createTrendingVideoCard(video, rank) {
        const trendingBadge = getTrendingBadge(rank);
        
        return `
            <div class="trending-video-card bg-surface rounded-lg overflow-hidden hover:shadow-lg transition-all group position-relative">
                ${trendingBadge}
                <div class="video-thumbnail-container position-relative">
                    <a href="${video.url}" class="video-thumbnail-link">
                        <img src="${video.thumbnail}" alt="${video.title}" class="video-thumbnail w-full aspect-video object-cover">
                        <div class="video-duration position-absolute bottom-2 right-2 bg-dark text-white px-2 py-1 rounded text-xs font-bold">
                            ${video.duration}
                        </div>
                        <div class="trending-metrics position-absolute top-2 right-2 bg-error text-white px-2 py-1 rounded text-xs font-bold">
                            <i class="fas fa-fire mr-1"></i>
                            ${video.trend_score}
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
                        <span class="video-growth text-success">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +${video.growth}%
                        </span>
                    </div>
                    <div class="trending-info text-xs text-warning mb-2">
                        <i class="fas fa-clock mr-1"></i>
                        ${video.trending_time}
                    </div>
                    <div class="video-actions d-flex justify-between align-center">
                        <button class="like-btn btn btn-secondary text-xs px-2 py-1 ${video.liked ? 'liked bg-primary' : ''}" data-video-id="${video.id}">
                            <i class="fas fa-heart mr-1"></i>
                            <span class="like-count">${video.likes}</span>
                        </button>
                        <div class="video-actions-right d-flex gap-1">
                            <button class="share-btn btn btn-secondary text-xs px-2 py-1" data-url="${video.url}" data-title="${video.title}">
                                <i class="fas fa-share"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function getTrendingBadge(rank) {
        let badgeClass = 'bg-secondary';
        let icon = 'fire';
        
        if (rank === 0) {
            badgeClass = 'bg-warning';
            icon = 'crown';
        } else if (rank <= 2) {
            badgeClass = 'bg-error';
            icon = 'fire';
        } else if (rank <= 9) {
            badgeClass = 'bg-primary';
            icon = 'arrow-up';
        }
        
        return `
            <div class="trending-rank position-absolute top-2 left-2 ${badgeClass} text-white px-2 py-1 rounded text-xs font-bold z-10">
                <i class="fas fa-${icon} mr-1"></i>
                #${rank + 1}
            </div>
        `;
    }
    
    function updateTrendingStats(stats) {
        $('#total-views').text(stats.total_views || '0');
        $('#trending-count').text(stats.trending_count || '0');
        $('#hot-categories').text(stats.hot_categories || '0');
        $('#growth-rate').text('+' + (stats.growth_rate || '0') + '%');
    }
    
    // Initialize when page loads
    $(document).ready(function() {
        loadTrendingVideos();
        
        // Time filter handlers
        $('.time-filter').on('click', function() {
            const period = $(this).data('period');
            currentPeriod = period;
            currentPage = 1;
            
            $('.time-filter').removeClass('active');
            $(this).addClass('active');
            
            loadTrendingVideos(currentPeriod, 1, false);
        });
        
        // Load more handler
        $('#load-more-trending').on('click', function() {
            if (hasMore && !isLoading) {
                currentPage++;
                loadTrendingVideos(currentPeriod, currentPage, true);
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
    });
    
})(jQuery);
</script>

<?php get_footer(); ?>
