<?php
/**
 * Enhanced Navigation Menu Template
 *
 * Provides an improved navigation menu with better structure and appropriate menu items
 * Fixed duplicate mobile menu issue
 *
 * @package CustomTube
 */

// Get the search query if it exists
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Get popular categories for dropdown
$categories = get_terms(array(
    'taxonomy' => 'genre',
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 6, // Limit for quick display
    'hide_empty' => true
));

// Get popular performers for dropdown
$performers = get_terms(array(
    'taxonomy' => 'performer',
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 6, // Limit for quick display
    'hide_empty' => true
));
?>

<header class="site-header">
    <div class="site-header-inner">
        <!-- Mobile menu toggle -->
        <button class="menu-toggle" 
                aria-controls="primary-menu-mobile" 
                aria-expanded="false" 
                aria-label="<?php esc_attr_e('Toggle navigation menu', 'customtube'); ?>"
                data-mobile-menu-closed="true"
                type="button">
            ☰
        </button>

        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
            <span class="logo-text">Lustful</span><span class="logo-highlight">Clips</span>
        </a>

        <!-- Search Form -->
        <div class="header-search">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" class="search-field" placeholder="<?php esc_attr_e('Search videos...', 'customtube'); ?>" value="<?php echo esc_attr($search_query); ?>" name="s" />
                <input type="hidden" name="post_type" value="video" />
                <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Search', 'customtube'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>
        
        <!-- Header Actions -->
        <div class="header-actions">
            <a href="#" class="ad-button">AI JERK OFF</a>
            <button class="icon-button" aria-label="Upload">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.2 15.8v3.4c0 1.7-1.4 3.1-3.1 3.1H5.9c-1.7 0-3.1-1.4-3.1-3.1v-3.4"></path>
                    <path d="M12 2.8v13.4"></path>
                    <path d="M17.2 8L12 2.8 6.8 8"></path>
                </svg>
            </button>
            <button class="icon-button" aria-label="Media">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </button>
            <button class="user-profile" aria-label="Profile">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </button>
            <button class="dark-mode-toggle" aria-label="Toggle dark mode">🌙</button>
        </div>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" data-mobile-menu-overlay></div>

<!-- Mobile Menu Panel -->
<nav class="mobile-menu-panel" 
     aria-label="<?php esc_attr_e('Mobile navigation', 'customtube'); ?>"
     data-mobile-menu-panel>
    <div class="mobile-menu-content">
        <!-- Mobile Search Form -->
        <div class="mobile-menu-search">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" class="search-field" placeholder="<?php esc_attr_e('Search videos...', 'customtube'); ?>" value="<?php echo esc_attr($search_query); ?>" name="s" />
                <input type="hidden" name="post_type" value="video" />
                <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Search', 'customtube'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Mobile Primary Menu -->
        <ul id="primary-menu-mobile" class="mobile-menu-list">
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
            <li><a href="<?php echo esc_url(home_url('/trending')); ?>">Trending</a></li>
            <li class="has-dropdown">
                <a href="#" data-mobile-dropdown-toggle>Categories</a>
                <ul class="mobile-submenu">
                    <?php foreach ($categories as $category): ?>
                        <li><a href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?php echo esc_url(get_post_type_archive_link('video')); ?>">All Categories</a></li>
                </ul>
            </li>
            <li class="has-dropdown">
                <a href="#" data-mobile-dropdown-toggle>Performers</a>
                <ul class="mobile-submenu">
                    <?php foreach ($performers as $performer): ?>
                        <li><a href="<?php echo esc_url(get_term_link($performer)); ?>"><?php echo esc_html($performer->name); ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?php echo esc_url(home_url('/performers')); ?>">All Performers</a></li>
                </ul>
            </li>
            <?php if (is_user_logged_in()): ?>
                <li><a href="<?php echo esc_url(home_url('/liked-videos')); ?>">Liked Videos</a></li>
            <?php endif; ?>
        </ul>

        <!-- Mobile Theme Toggle and Auth Section -->
        <div class="mobile-menu-actions">
            <button class="dark-mode-toggle mobile-theme-btn" aria-label="Toggle dark mode">
                🌙 <span class="theme-toggle-text">Dark Mode</span>
            </button>
            <?php if (is_user_logged_in()): ?>
                <div class="mobile-user-section">
                    <div class="mobile-user-info">
                        <img src="<?php echo esc_url(get_avatar_url(get_current_user_id())); ?>" alt="User Avatar" class="mobile-user-avatar">
                        <span class="mobile-user-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                    </div>
                    <ul class="mobile-user-menu">
                        <li><a href="<?php echo esc_url(get_edit_profile_url()); ?>">My Profile</a></li>
                        <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="mobile-auth-buttons">
                    <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn btn-outline-primary">Login</a>
                    <a href="<?php echo esc_url(home_url('/register')); ?>" class="btn btn-primary">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
