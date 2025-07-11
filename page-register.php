<?php
/**
 * Template Name: Custom Registration Page
 * 
 * A sleek, custom registration page with branding.
 * 
 * @package CustomTube
 */

get_header();

// Check if user is logged in. If yes, redirect to home
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

// Check if registration is enabled
if (!get_option('users_can_register')) {
    wp_redirect(home_url('/login/?registration=disabled'));
    exit;
}

// Process registration form if submitted
$registration_errors = array();
if (isset($_POST['register_submit'])) {
    $user_login = sanitize_user($_POST['user_login']);
    $user_email = sanitize_email($_POST['user_email']);
    $user_pass = $_POST['user_pass'];
    $user_pass_confirm = $_POST['user_pass_confirm'];
    
    // Validation
    if (empty($user_login)) {
        $registration_errors[] = 'Please enter a username.';
    }
    
    if (empty($user_email) || !is_email($user_email)) {
        $registration_errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($user_pass)) {
        $registration_errors[] = 'Please enter a password.';
    }
    
    if ($user_pass !== $user_pass_confirm) {
        $registration_errors[] = 'Passwords do not match.';
    }
    
    if (username_exists($user_login)) {
        $registration_errors[] = 'Username already exists.';
    }
    
    if (email_exists($user_email)) {
        $registration_errors[] = 'Email address already exists.';
    }
    
    // Register user if no errors
    if (empty($registration_errors)) {
        $user_id = wp_create_user($user_login, $user_pass, $user_email);
        
        if (!is_wp_error($user_id)) {
            // Set user role
            $user = new WP_User($user_id);
            $user->set_role('subscriber');
            
            // Auto login after registration
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $user_login);
            
            // Redirect to home page
            wp_redirect(home_url());
            exit;
        } else {
            $registration_errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main register-page">
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
                    <h2 class="auth-page-title">Create Account</h2>
                </div>

                <div class="auth-content">
                    <?php
                    // Show error messages
                    if (!empty($registration_errors)) {
                        echo '<div class="auth-message auth-error"><ul>';
                        foreach ($registration_errors as $error) {
                            echo '<li>' . esc_html($error) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                    ?>

                    <form method="post" action="" class="registration-form">
                        <div class="form-group">
                            <label for="user_login">Username</label>
                            <input type="text" name="user_login" id="user_login" class="input" value="<?php echo isset($_POST['user_login']) ? esc_attr($_POST['user_login']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="user_email">Email Address</label>
                            <input type="email" name="user_email" id="user_email" class="input" value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="user_pass">Password</label>
                            <input type="password" name="user_pass" id="user_pass" class="input" required>
                        </div>

                        <div class="form-group">
                            <label for="user_pass_confirm">Confirm Password</label>
                            <input type="password" name="user_pass_confirm" id="user_pass_confirm" class="input" required>
                        </div>

                        <div class="form-group">
                            <input type="submit" name="register_submit" id="register-submit" class="button-primary" value="Create Account">
                        </div>
                    </form>

                    <div class="auth-links">
                        <span class="auth-note">Already have an account?</span>
                        <a href="<?php echo esc_url(home_url('/login/')); ?>" class="auth-link login-link">Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>