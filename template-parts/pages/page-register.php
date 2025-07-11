<?php
/**
 * Template Name: Custom Registration Page
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('register-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-user-plus mr-3 text-success"></i>
                            <?php esc_html_e('Create Account', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Join our community and start exploring amazing content!', 'customtube'); ?>
                        </p>
                    </div>
                </div>
            </header>

            <!-- Registration Content -->
            <div class="entry-content register-content">
                <div class="container">
                    <div class="register-wrapper max-w-lg mx-auto">
                        
                        <!-- Registration Form -->
                        <div class="register-form-container bg-surface rounded-lg shadow-lg p-8">
                            
                            <!-- Age Verification Notice -->
                            <div class="age-notice bg-warning-light rounded-lg p-4 mb-6">
                                <div class="d-flex align-center">
                                    <i class="fas fa-exclamation-triangle text-warning text-xl mr-3"></i>
                                    <div>
                                        <h3 class="font-semibold text-warning-dark mb-1">
                                            <?php esc_html_e('Age Verification Required', 'customtube'); ?>
                                        </h3>
                                        <p class="text-sm text-warning-dark">
                                            <?php esc_html_e('You must be 18 years or older to create an account.', 'customtube'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Registration Options -->
                            <div class="social-register mb-6">
                                <div class="social-buttons grid grid-cols-2 gap-3">
                                    <button class="social-btn bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg d-flex align-center justify-center transition-colors">
                                        <i class="fab fa-google mr-2"></i>
                                        <?php esc_html_e('Google', 'customtube'); ?>
                                    </button>
                                    <button class="social-btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg d-flex align-center justify-center transition-colors">
                                        <i class="fab fa-facebook mr-2"></i>
                                        <?php esc_html_e('Facebook', 'customtube'); ?>
                                    </button>
                                </div>
                                
                                <div class="divider d-flex align-center my-6">
                                    <div class="divider-line flex-1 h-px bg-border"></div>
                                    <span class="divider-text px-4 text-sm text-secondary"><?php esc_html_e('or register with email', 'customtube'); ?></span>
                                    <div class="divider-line flex-1 h-px bg-border"></div>
                                </div>
                            </div>

                            <!-- Registration Form -->
                            <form id="custom-register-form" class="register-form" method="post">
                                
                                <!-- Username -->
                                <div class="form-group mb-4">
                                    <label for="user_login" class="form-label text-sm font-medium mb-2">
                                        <?php esc_html_e('Username', 'customtube'); ?> <span class="text-error">*</span>
                                    </label>
                                    <div class="input-group position-relative">
                                        <i class="fas fa-user position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                        <input type="text" 
                                               id="user_login" 
                                               name="user_login" 
                                               class="form-input w-full pl-10 pr-4 py-3 border border-gray rounded-lg focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20" 
                                               placeholder="<?php esc_attr_e('Choose a username', 'customtube'); ?>" 
                                               required>
                                    </div>
                                    <div class="field-help text-xs text-secondary mt-1">
                                        <?php esc_html_e('3-20 characters, letters, numbers, and underscores only', 'customtube'); ?>
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="form-group mb-4">
                                    <label for="user_email" class="form-label text-sm font-medium mb-2">
                                        <?php esc_html_e('Email Address', 'customtube'); ?> <span class="text-error">*</span>
                                    </label>
                                    <div class="input-group position-relative">
                                        <i class="fas fa-envelope position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                        <input type="email" 
                                               id="user_email" 
                                               name="user_email" 
                                               class="form-input w-full pl-10 pr-4 py-3 border border-gray rounded-lg focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20" 
                                               placeholder="<?php esc_attr_e('Enter your email address', 'customtube'); ?>" 
                                               required>
                                    </div>
                                </div>
                                
                                <!-- Password -->
                                <div class="form-group mb-4">
                                    <label for="user_pass" class="form-label text-sm font-medium mb-2">
                                        <?php esc_html_e('Password', 'customtube'); ?> <span class="text-error">*</span>
                                    </label>
                                    <div class="input-group position-relative">
                                        <i class="fas fa-lock position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                        <input type="password" 
                                               id="user_pass" 
                                               name="user_pass" 
                                               class="form-input w-full pl-10 pr-12 py-3 border border-gray rounded-lg focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20" 
                                               placeholder="<?php esc_attr_e('Create a strong password', 'customtube'); ?>" 
                                               required>
                                        <button type="button" class="password-toggle position-absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary hover:text-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div class="strength-meter bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="strength-bar h-full transition-all duration-300 rounded-full" style="width: 0%;"></div>
                                        </div>
                                        <div class="strength-text text-xs mt-1"></div>
                                    </div>
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="form-group mb-4">
                                    <label for="user_pass_confirm" class="form-label text-sm font-medium mb-2">
                                        <?php esc_html_e('Confirm Password', 'customtube'); ?> <span class="text-error">*</span>
                                    </label>
                                    <div class="input-group position-relative">
                                        <i class="fas fa-lock position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                        <input type="password" 
                                               id="user_pass_confirm" 
                                               name="user_pass_confirm" 
                                               class="form-input w-full pl-10 pr-4 py-3 border border-gray rounded-lg focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20" 
                                               placeholder="<?php esc_attr_e('Confirm your password', 'customtube'); ?>" 
                                               required>
                                    </div>
                                </div>
                                
                                <!-- Date of Birth -->
                                <div class="form-group mb-4">
                                    <label for="user_birthdate" class="form-label text-sm font-medium mb-2">
                                        <?php esc_html_e('Date of Birth', 'customtube'); ?> <span class="text-error">*</span>
                                    </label>
                                    <div class="input-group position-relative">
                                        <i class="fas fa-calendar position-absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary"></i>
                                        <input type="date" 
                                               id="user_birthdate" 
                                               name="user_birthdate" 
                                               class="form-input w-full pl-10 pr-4 py-3 border border-gray rounded-lg focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20" 
                                               max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                               required>
                                    </div>
                                    <div class="field-help text-xs text-secondary mt-1">
                                        <?php esc_html_e('You must be 18 or older to register', 'customtube'); ?>
                                    </div>
                                </div>
                                
                                <!-- Terms and Conditions -->
                                <div class="form-group mb-6">
                                    <label class="terms-agreement d-flex align-start">
                                        <input type="checkbox" name="terms_agreement" class="form-checkbox mt-1 mr-3" required>
                                        <span class="text-sm text-secondary leading-relaxed">
                                            <?php esc_html_e('I agree to the', 'customtube'); ?> 
                                            <a href="<?php echo esc_url(home_url('/terms-of-service')); ?>" class="text-primary hover:text-accent" target="_blank">
                                                <?php esc_html_e('Terms of Service', 'customtube'); ?>
                                            </a> 
                                            <?php esc_html_e('and', 'customtube'); ?> 
                                            <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>" class="text-primary hover:text-accent" target="_blank">
                                                <?php esc_html_e('Privacy Policy', 'customtube'); ?>
                                            </a>
                                        </span>
                                    </label>
                                </div>
                                
                                <!-- Age Confirmation -->
                                <div class="form-group mb-6">
                                    <label class="age-confirmation d-flex align-start">
                                        <input type="checkbox" name="age_confirmation" class="form-checkbox mt-1 mr-3" required>
                                        <span class="text-sm text-secondary leading-relaxed">
                                            <?php esc_html_e('I confirm that I am 18 years of age or older and I am legally allowed to view adult content in my location.', 'customtube'); ?>
                                        </span>
                                    </label>
                                </div>
                                
                                <!-- Marketing Opt-in -->
                                <div class="form-group mb-6">
                                    <label class="marketing-optin d-flex align-start">
                                        <input type="checkbox" name="marketing_optin" class="form-checkbox mt-1 mr-3">
                                        <span class="text-sm text-secondary leading-relaxed">
                                            <?php esc_html_e('I would like to receive updates, promotions, and news via email (optional)', 'customtube'); ?>
                                        </span>
                                    </label>
                                </div>
                                
                                <?php wp_nonce_field('custom_register_nonce', 'register_nonce'); ?>
                                
                                <button type="submit" class="register-submit btn btn-success w-full py-3 text-lg font-semibold">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    <?php esc_html_e('Create Account', 'customtube'); ?>
                                </button>
                            </form>
                            
                            <!-- Registration Messages -->
                            <div id="register-messages" class="register-messages mt-4 d-none">
                                <!-- Messages will be displayed here -->
                            </div>
                            
                            <!-- Login Link -->
                            <div class="login-link text-center mt-6 pt-6 border-t border-gray">
                                <p class="text-secondary mb-3"><?php esc_html_e('Already have an account?', 'customtube'); ?></p>
                                <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn btn-outline-primary px-6 py-2">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    <?php esc_html_e('Sign In', 'customtube'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="additional-info text-center mt-6">
                            <div class="info-box bg-info-light rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-info-dark mb-2">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    <?php esc_html_e('Your Privacy Matters', 'customtube'); ?>
                                </h3>
                                <p class="text-sm text-info-dark">
                                    <?php esc_html_e('We take your privacy seriously. Your personal information is encrypted and secure, and we never share it with third parties without your consent.', 'customtube'); ?>
                                </p>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div><!-- .entry-content -->
            
        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<!-- Registration Page JavaScript -->
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
    
    // Password strength checker
    $('#user_pass').on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        updatePasswordStrength(strength);
    });
    
    // Real-time validation
    $('#user_login').on('blur', function() {
        const username = $(this).val().trim();
        if (username.length >= 3) {
            checkUsernameAvailability(username);
        }
    });
    
    $('#user_email').on('blur', function() {
        const email = $(this).val().trim();
        if (email && isValidEmail(email)) {
            checkEmailAvailability(email);
        }
    });
    
    // Form submission
    $('#custom-register-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('.register-submit');
        const $messages = $('#register-messages');
        
        // Clear previous messages
        $messages.removeClass('d-block').addClass('d-none').empty();
        
        // Validate form
        if (!validateRegistrationForm()) {
            return;
        }
        
        // Show loading state
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php esc_html_e('Creating Account...', 'customtube'); ?>');
        
        // Submit form via AJAX
        $.ajax({
            url: customtubeData.ajaxurl,
            type: 'POST',
            data: {
                action: 'custom_user_register',
                user_login: $('#user_login').val().trim(),
                user_email: $('#user_email').val().trim(),
                user_pass: $('#user_pass').val(),
                user_birthdate: $('#user_birthdate').val(),
                terms_agreement: $('input[name="terms_agreement"]').is(':checked') ? 1 : 0,
                age_confirmation: $('input[name="age_confirmation"]').is(':checked') ? 1 : 0,
                marketing_optin: $('input[name="marketing_optin"]').is(':checked') ? 1 : 0,
                nonce: $form.find('input[name="register_nonce"]').val()
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.data.message);
                    
                    // Redirect or show next steps
                    setTimeout(function() {
                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            window.location.href = '<?php echo esc_url(home_url('/login')); ?>';
                        }
                    }, 2000);
                } else {
                    showMessage('error', response.data.message || '<?php esc_html_e('Registration failed. Please try again.', 'customtube'); ?>');
                }
            },
            error: function() {
                showMessage('error', '<?php esc_html_e('An error occurred. Please try again.', 'customtube'); ?>');
            },
            complete: function() {
                // Reset button state
                $submitBtn.prop('disabled', false).html('<i class="fas fa-user-plus mr-2"></i><?php esc_html_e('Create Account', 'customtube'); ?>');
            }
        });
    });
    
    function validateRegistrationForm() {
        let isValid = true;
        
        // Username validation
        const username = $('#user_login').val().trim();
        if (!username || username.length < 3) {
            showMessage('error', '<?php esc_html_e('Username must be at least 3 characters long.', 'customtube'); ?>');
            isValid = false;
        }
        
        // Email validation
        const email = $('#user_email').val().trim();
        if (!email || !isValidEmail(email)) {
            showMessage('error', '<?php esc_html_e('Please enter a valid email address.', 'customtube'); ?>');
            isValid = false;
        }
        
        // Password validation
        const password = $('#user_pass').val();
        const confirmPassword = $('#user_pass_confirm').val();
        
        if (!password || password.length < 8) {
            showMessage('error', '<?php esc_html_e('Password must be at least 8 characters long.', 'customtube'); ?>');
            isValid = false;
        }
        
        if (password !== confirmPassword) {
            showMessage('error', '<?php esc_html_e('Passwords do not match.', 'customtube'); ?>');
            isValid = false;
        }
        
        // Age validation
        const birthdate = $('#user_birthdate').val();
        if (!birthdate || !isOver18(birthdate)) {
            showMessage('error', '<?php esc_html_e('You must be 18 years or older to register.', 'customtube'); ?>');
            isValid = false;
        }
        
        // Terms agreement
        if (!$('input[name="terms_agreement"]').is(':checked')) {
            showMessage('error', '<?php esc_html_e('You must agree to the Terms of Service and Privacy Policy.', 'customtube'); ?>');
            isValid = false;
        }
        
        // Age confirmation
        if (!$('input[name="age_confirmation"]').is(':checked')) {
            showMessage('error', '<?php esc_html_e('You must confirm that you are 18 years or older.', 'customtube'); ?>');
            isValid = false;
        }
        
        return isValid;
    }
    
    function checkPasswordStrength(password) {
        let score = 0;
        let feedback = [];
        
        if (password.length >= 8) score += 1;
        else feedback.push('<?php esc_html_e('At least 8 characters', 'customtube'); ?>');
        
        if (/[a-z]/.test(password)) score += 1;
        else feedback.push('<?php esc_html_e('Lowercase letter', 'customtube'); ?>');
        
        if (/[A-Z]/.test(password)) score += 1;
        else feedback.push('<?php esc_html_e('Uppercase letter', 'customtube'); ?>');
        
        if (/[0-9]/.test(password)) score += 1;
        else feedback.push('<?php esc_html_e('Number', 'customtube'); ?>');
        
        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        else feedback.push('<?php esc_html_e('Special character', 'customtube'); ?>');
        
        return { score, feedback };
    }
    
    function updatePasswordStrength(strength) {
        const $meter = $('.strength-bar');
        const $text = $('.strength-text');
        
        const colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#198754'];
        const labels = ['<?php esc_html_e('Very Weak', 'customtube'); ?>', '<?php esc_html_e('Weak', 'customtube'); ?>', '<?php esc_html_e('Fair', 'customtube'); ?>', '<?php esc_html_e('Good', 'customtube'); ?>', '<?php esc_html_e('Strong', 'customtube'); ?>'];
        
        const width = (strength.score / 5) * 100;
        const color = colors[strength.score - 1] || colors[0];
        const label = labels[strength.score - 1] || labels[0];
        
        $meter.css({
            'width': width + '%',
            'background-color': color
        });
        
        if (strength.score > 0) {
            $text.html(`<span style="color: ${color};">${label}</span>`);
            if (strength.feedback.length > 0) {
                $text.append(` - <?php esc_html_e('Missing:', 'customtube'); ?> ${strength.feedback.join(', ')}`);
            }
        } else {
            $text.text('');
        }
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function isOver18(birthdate) {
        const today = new Date();
        const birth = new Date(birthdate);
        const age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            return age - 1 >= 18;
        }
        return age >= 18;
    }
    
    function checkUsernameAvailability(username) {
        // AJAX call to check username availability
        // Implementation would go here
    }
    
    function checkEmailAvailability(email) {
        // AJAX call to check email availability
        // Implementation would go here
    }
    
    function showMessage(type, message) {
        const $messages = $('#register-messages');
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
    
})(jQuery);
</script>

<?php get_footer(); ?>
