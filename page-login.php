<?php
/**
 * Template Name: Custom Login Page
 * 
 * A sleek, custom login page with branding.
 * 
 * @package CustomTube
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main login-page">
        <div class="auth-container">
            <div class="auth-box">
                <div class="auth-header">
                    <div class="site-branding-large">
                        <?php 
                        // Get the site logo if available, otherwise use site title
                        $custom_logo_id = get_theme_mod('custom_logo');
                        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
                        
                        if ($logo) {
                            echo '<img src="' . esc_url($logo[0]) . '" alt="' . get_bloginfo('name') . '" class="auth-logo">';
                        } else {
                            echo '<h1 class="auth-title">' . get_bloginfo('name') . '</h1>';
                        }
                        ?>
                    </div>
                    <h2 class="auth-page-title">Sign In</h2>
                </div>

                <div class="auth-content">
                    <?php
                    // Show any error messages
                    if (isset($_GET['login']) && $_GET['login'] == 'failed') {
                        echo '<div class="auth-message auth-error">Invalid username or password. Please try again.</div>';
                    } elseif (isset($_GET['login']) && $_GET['login'] == 'empty') {
                        echo '<div class="auth-message auth-error">Please enter both username and password.</div>';
                    } elseif (isset($_GET['registered']) && $_GET['registered'] == 'success') {
                        echo '<div class="auth-message auth-success">Registration successful! You can now log in.</div>';
                    }
                    ?>

                    <form method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" class="login-form">
                        <div class="form-group">
                            <label for="user_login">Username or Email</label>
                            <input type="text" name="log" id="user_login" class="input" autocomplete="username" required>
                        </div>

                        <div class="form-group">
                            <label for="user_pass">Password</label>
                            <input type="password" name="pwd" id="user_pass" class="input" autocomplete="current-password" required>
                        </div>

                        <div class="form-group form-checkbox">
                            <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                            <label for="rememberme">Remember Me</label>
                        </div>

                        <div class="form-group">
                            <input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Sign In">
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url()); ?>">
                        </div>
                    </form>

                    <div class="auth-links">
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="auth-link">Forgot your password?</a>
                        <?php
                        // Link to registration page if registration is enabled
                        if (get_option('users_can_register')) {
                            echo '<a href="' . esc_url(home_url('/register/')) . '" class="auth-link register-link">Create an account</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>