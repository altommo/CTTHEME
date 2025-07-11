<?php
/**
 * Template Name: Custom Login Page
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('login-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-sign-in-alt mr-3 text-primary"></i>
                            <?php esc_html_e('Sign In', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Welcome back! Please sign in to your account.', 'customtube'); ?>
                        </p>
                    </div>
                </div>
            </header>

            <!-- Login Content -->
            <div class="entry-content login-content">
                <div class="container">
                    <div class="login-wrapper max-w-md mx-auto">
                        
                        <!-- Login Form -->
                        <div class="login-form-container bg-surface rounded-lg shadow-lg p-8">
                            
                            <!-- Social Login Options -->
                            <div class="social-login mb-6">
                                <div class="social-buttons grid grid-cols-2 gap-3">
                                    <button class="social-btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg d-flex align-center justify-center transition-colors">
                                        <i class="fab fa-google mr-2"></i>
                                        <?php esc_html_e('Google', 'customtube'); ?>
                                    </button>
                                    <button class="social-btn bg-blue-800 hover:bg-blue-900 text-white px-4 py-3 rounded-lg d-flex align-center justify-center transition-colors">
                                        <i class="fab fa-facebook mr-2"></i>
                                        <?php esc_html_e('Facebook', 'customtube'); ?>
                                    </button>
                                </div>
                                
                                <div class="divider d-flex align-center my-6">
                                    <div class="divider-line flex-1 h-px bg-border"></div>
                                    <span class="divider-text px-4 text-sm text-secondary"><?php esc_html_e('or', 'customtube'); ?></span>
                                    <div class="divider-line flex-1 h-px bg-border"></div>
                                </div>
                            </div>

                            <!-- Login Form -->
                            <form id="custom-login-form" class="login-form" method="post">
                                <div class="form-group mb-4">
                                    <label for="user_login" class="form-label text-sm font-medium mb-2">
                                        <?php esc_html_e('Username or Email', 'customtube'); ?>
                                    </label>
                                    <div class="input-group position-relative">
                                        <i class="fas fa-user position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                        <input type="text" 
                                               id="user_login" 
                                               name="log" 
                                               class="form-input w-full pl-10 pr-4 py-3 border border-gray rounded-lg focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20" 
                                               placeholder="<?php esc_attr_e('Enter your username or email', 'customtube'); ?>" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label for="user_pass" class="form-label text-sm font-medium mb-2">
                                        <?php esc_html_e('Password', 'customtube'); ?>
                                    </label>
                                    <div class="input-group position-relative">
                                        <i class="fas fa-lock position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                        <input type="password" 
                                               id="user_pass" 
                                               name="pwd" 
                                               class="form-input w-full pl-10 pr-12 py-3 border border-gray rounded-lg focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20" 
                                               placeholder="<?php esc_attr_e('Enter your password', 'customtube'); ?>" 
                                               required>
                                        <button type="button" class="password-toggle position-absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary hover:text-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-options d-flex justify-between align-center mb-6">
                                    <label class="remember-me d-flex align-center">
                                        <input type="checkbox" name="rememberme" class="form-checkbox mr-2">
                                        <span class="text-sm text-secondary"><?php esc_html_e('Remember me', 'customtube'); ?></span>
                                    </label>
                                    
                                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="forgot-password text-sm text-primary hover:text-accent">
                                        <?php esc_html_e('Forgot password?', 'customtube'); ?>
                                    </a>
                                </div>
                                
                                <?php wp_nonce_field('custom_login_nonce', 'login_nonce'); ?>
                                
                                <button type="submit" class="login-submit btn btn-primary w-full py-3 text-lg font-semibold">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    <?php esc_html_e('Sign In', 'customtube'); ?>
                                </button>
                            </form>
                            
                            <!-- Login Messages -->
                            <div id="login-messages" class="login-messages mt-4 d-none">
                                <!-- Messages will be displayed here -->
                            </div>
                            
                            <!-- Registration Link -->
                            <div class="register-link text-center mt-6 pt-6 border-t border-gray">
                                <p class="text-secondary mb-3"><?php esc_html_e('Don\'t have an account?', 'customtube'); ?></p>
                                <a href="<?php echo esc_url(home_url('/register')); ?>" class="btn btn-outline-primary px-6 py-2">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    <?php esc_html_e('Create Account', 'customtube'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Additional Links -->
                        <div class="additional-links text-center mt-6">
                            <div class="links-container d-flex flex-wrap justify-center gap-4 text-sm">
                                <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>" class="text-secondary hover:text-primary">
                                    <?php esc_html_e('Privacy Policy', 'customtube'); ?>
                                </a>
                                <a href="<?php echo esc_url(home_url('/terms-of-service')); ?>" class="text-secondary hover:text-primary">
                                    <?php esc_html_e('Terms of Service', 'customtube'); ?>
                                </a>
                                <a href="<?php echo esc_url(home_url('/help')); ?>" class="text-secondary hover:text-primary">
                                    <?php esc_html_e('Help & Support', 'customtube'); ?>
                                </a>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div><!-- .entry-content -->
            
        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<!-- Login Page JavaScript -->
<script>
(function($) {
    'use strict';
    
    // Password toggle functionality
    $('.password-toggle').on('click', function() {
        const $passwordField = $(this).siblings('input[type="password"], input[type="text"]');
        const $icon = $(this).find('i');
        
        if ($passwordField.attr('type') === 'password') {
            $passwordField.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $passwordField.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Form validation
    $('#custom-login-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('.login-submit');
        const $messages = $('#login-messages');
        
        // Clear previous messages
        $messages.removeClass('d-block').addClass('d-none').empty();
        
        // Validate form
        let isValid = true;
        const username = $('#user_login').val().trim();
        const password = $('#user_pass').val().trim();
        
        if (!username) {
            showMessage('error', '<?php esc_html_e('Please enter your username or email.', 'customtube'); ?>');
            isValid = false;
        }
        
        if (!password) {
            showMessage('error', '<?php esc_html_e('Please enter your password.', 'customtube'); ?>');
            isValid = false;
        }
        
        if (!isValid) return;
        
        // Show loading state
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php esc_html_e('Signing In...', 'customtube'); ?>');
        
        // Submit form via AJAX
        $.ajax({
            url: customtubeData.ajaxurl,
            type: 'POST',
            data: {
                action: 'custom_user_login',
                log: username,
                pwd: password,
                rememberme: $form.find('input[name="rememberme"]').is(':checked') ? 1 : 0,
                nonce: $form.find('input[name="login_nonce"]').val()
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.data.message);
                    
                    // Redirect after successful login
                    setTimeout(function() {
                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            window.location.reload();
                        }
                    }, 1500);
                } else {
                    showMessage('error', response.data.message || '<?php esc_html_e('Login failed. Please try again.', 'customtube'); ?>');
                }
            },
            error: function() {
                showMessage('error', '<?php esc_html_e('An error occurred. Please try again.', 'customtube'); ?>');
            },
            complete: function() {
                // Reset button state
                $submitBtn.prop('disabled', false).html('<i class="fas fa-sign-in-alt mr-2"></i><?php esc_html_e('Sign In', 'customtube'); ?>');
            }
        });
    });
    
    // Social login handlers
    $('.social-btn').on('click', function() {
        const provider = $(this).find('i').hasClass('fa-google') ? 'google' : 'facebook';
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php esc_html_e('Connecting...', 'customtube'); ?>');
        
        // Implement social login logic here
        // This would typically redirect to OAuth provider
        showMessage('info', '<?php esc_html_e('Social login is not configured yet.', 'customtube'); ?>');
        
        // Reset button
        setTimeout(() => {
            $(this).prop('disabled', false);
            if (provider === 'google') {
                $(this).html('<i class="fab fa-google mr-2"></i><?php esc_html_e('Google', 'customtube'); ?>');
            } else {
                $(this).html('<i class="fab fa-facebook mr-2"></i><?php esc_html_e('Facebook', 'customtube'); ?>');
            }
        }, 2000);
    });
    
    function showMessage(type, message) {
        const $messages = $('#login-messages');
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-error' : 
                          type === 'info' ? 'alert-info' : 'alert-warning';
        
        const iconClass = type === 'success' ? 'fa-check-circle' : 
                         type === 'error' ? 'fa-exclamation-triangle' : 
                         type === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle';
        
        $messages.html(`
            <div class="alert ${alertClass} rounded-lg p-4">
                <i class="fas ${iconClass} mr-2"></i>
                ${message}
            </div>
        `).removeClass('d-none').addClass('d-block');
    }
    
    // Auto-focus first input
    $(document).ready(function() {
        if (!$('#user_login').val()) {
            $('#user_login').focus();
        }
    });
    
})(jQuery);
</script>

<!-- Custom Login Styles -->
<style>
.login-page {
    min-height: calc(100vh - 200px);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.form-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-checkbox {
    width: 1rem;
    height: 1rem;
    accent-color: var(--color-primary);
}

.alert {
    border: 1px solid transparent;
}

.alert-success {
    background-color: var(--color-success-light, #f0f9f0);
    color: var(--color-success-dark, #2d5a2d);
    border-color: var(--color-success, #28a745);
}

.alert-error {
    background-color: var(--color-error-light, #fef2f2);
    color: var(--color-error-dark, #7f1d1d);
    border-color: var(--color-error, #dc3545);
}

.alert-info {
    background-color: var(--color-info-light, #f0f9ff);
    color: var(--color-info-dark, #1e40af);
    border-color: var(--color-info, #17a2b8);
}

.alert-warning {
    background-color: var(--color-warning-light, #fffbeb);
    color: var(--color-warning-dark, #92400e);
    border-color: var(--color-warning, #ffc107);
}

.social-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 640px) {
    .login-form-container {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .social-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<?php get_footer(); ?>
