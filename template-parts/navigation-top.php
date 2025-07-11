<?php
/**
 * Template part for displaying the top navigation bar
 *
 * @package CustomTube
 */

$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
?>

<nav class="nav-top">
    <!-- Mobile menu toggle (only visible on mobile) -->
    <button class="menu-toggle" aria-expanded="false" aria-label="Toggle navigation menu">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Logo -->
    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">
        <span class="site-logo-text">Lustful</span>
        <span class="site-logo-accent">Clips</span>
    </a>
    
    <!-- Search bar -->
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input 
            type="text" 
            class="search-input" 
            placeholder="Search videos..."
            aria-label="Search videos"
            value="<?php echo esc_attr($search_query); ?>"
        >
    </div>
    
    <!-- User actions -->
    <div class="user-actions">
        <!-- Theme toggle -->
        <button class="theme-toggle clickable" aria-label="Toggle light/dark mode">
            <i class="fas fa-moon"></i>
        </button>
        
        <?php if (is_user_logged_in()) : ?>
            <!-- User profile/account link -->
            <a href="<?php echo esc_url(get_edit_profile_url()); ?>" class="user-profile clickable">
                <?php 
                $current_user = wp_get_current_user();
                echo get_avatar($current_user->ID, 32);
                ?>
            </a>
        <?php else : ?>
            <!-- Login/Register buttons -->
            <a href="<?php echo esc_url(wp_login_url()); ?>" class="login-button clickable">
                Login
            </a>
            <a href="<?php echo esc_url(wp_registration_url()); ?>" class="register-button clickable">
                Register
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- Script to restore theme preference -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const themePreference = localStorage.getItem('theme');
    const themeToggle = document.querySelector('.theme-toggle');
    const themeIcon = themeToggle ? themeToggle.querySelector('i') : null;
    
    if (themePreference === 'dark') {
        document.body.classList.add('dark-mode');
        if (themeIcon) {
            themeIcon.className = 'fas fa-sun';
        }
    } else if (themePreference === 'light') {
        document.body.classList.remove('dark-mode');
        if (themeIcon) {
            themeIcon.className = 'fas fa-moon';
        }
    } else {
        // Check preferred color scheme if no preference saved
        const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDarkMode) {
            document.body.classList.add('dark-mode');
            if (themeIcon) {
                themeIcon.className = 'fas fa-sun';
            }
        }
    }
});
</script>