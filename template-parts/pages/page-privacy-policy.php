<?php
/**
 * Template Name: Privacy Policy
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('privacy-policy-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-shield-alt mr-3 text-info"></i>
                            <?php esc_html_e('Privacy Policy', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Your privacy is important to us. This policy explains how we collect, use, and protect your information.', 'customtube'); ?>
                        </p>
                        <div class="last-updated text-sm text-muted">
                            <i class="fas fa-calendar mr-2"></i>
                            <?php esc_html_e('Last updated:', 'customtube'); ?> 
                            <time datetime="<?php echo date('Y-m-d'); ?>"><?php echo date('F j, Y'); ?></time>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Table of Contents -->
            <div class="privacy-toc bg-surface rounded-lg p-6 mb-8">
                <div class="container">
                    <h2 class="toc-title text-xl font-semibold mb-4">
                        <i class="fas fa-list mr-2"></i>
                        <?php esc_html_e('Table of Contents', 'customtube'); ?>
                    </h2>
                    <nav class="toc-nav">
                        <ol class="toc-list grid md:grid-cols-2 gap-2">
                            <li><a href="#information-collection" class="toc-link"><?php esc_html_e('Information We Collect', 'customtube'); ?></a></li>
                            <li><a href="#information-use" class="toc-link"><?php esc_html_e('How We Use Information', 'customtube'); ?></a></li>
                            <li><a href="#information-sharing" class="toc-link"><?php esc_html_e('Information Sharing', 'customtube'); ?></a></li>
                            <li><a href="#data-security" class="toc-link"><?php esc_html_e('Data Security', 'customtube'); ?></a></li>
                            <li><a href="#cookies" class="toc-link"><?php esc_html_e('Cookies & Tracking', 'customtube'); ?></a></li>
                            <li><a href="#user-rights" class="toc-link"><?php esc_html_e('Your Rights', 'customtube'); ?></a></li>
                            <li><a href="#age-restrictions" class="toc-link"><?php esc_html_e('Age Restrictions', 'customtube'); ?></a></li>
                            <li><a href="#policy-changes" class="toc-link"><?php esc_html_e('Policy Changes', 'customtube'); ?></a></li>
                            <li><a href="#contact" class="toc-link"><?php esc_html_e('Contact Information', 'customtube'); ?></a></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Policy Content -->
            <div class="entry-content privacy-content">
                <div class="container">
                    
                    <!-- Information Collection -->
                    <section id="information-collection" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-database mr-3"></i>
                            <?php esc_html_e('Information We Collect', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-info-light rounded-lg p-4 mb-6">
                            <p class="text-info-dark font-medium">
                                <?php esc_html_e('We collect information to provide better services to all our users.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="subsection mb-6">
                            <h3 class="subsection-title text-xl font-semibold mb-3"><?php esc_html_e('Personal Information', 'customtube'); ?></h3>
                            <p class="mb-4"><?php esc_html_e('We may collect the following personal information when you register or use our services:', 'customtube'); ?></p>
                            <ul class="info-list">
                                <li><?php esc_html_e('Email address and username', 'customtube'); ?></li>
                                <li><?php esc_html_e('Profile information you provide', 'customtube'); ?></li>
                                <li><?php esc_html_e('Communication preferences', 'customtube'); ?></li>
                                <li><?php esc_html_e('Content preferences and viewing history', 'customtube'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="subsection mb-6">
                            <h3 class="subsection-title text-xl font-semibold mb-3"><?php esc_html_e('Technical Information', 'customtube'); ?></h3>
                            <p class="mb-4"><?php esc_html_e('We automatically collect certain technical information:', 'customtube'); ?></p>
                            <ul class="info-list">
                                <li><?php esc_html_e('IP address and browser information', 'customtube'); ?></li>
                                <li><?php esc_html_e('Device type and operating system', 'customtube'); ?></li>
                                <li><?php esc_html_e('Website usage statistics and analytics', 'customtube'); ?></li>
                                <li><?php esc_html_e('Referral sources and search terms', 'customtube'); ?></li>
                            </ul>
                        </div>
                    </section>

                    <!-- Information Use -->
                    <section id="information-use" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-cogs mr-3"></i>
                            <?php esc_html_e('How We Use Information', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-success-light rounded-lg p-4 mb-6">
                            <p class="text-success-dark font-medium">
                                <?php esc_html_e('We use your information to improve our services and provide you with a personalized experience.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="use-cases grid md:grid-cols-2 gap-6">
                            <div class="use-case bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Service Provision', 'customtube'); ?></h4>
                                <ul class="text-sm text-secondary">
                                    <li><?php esc_html_e('Account management and authentication', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Content recommendations', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Customer support', 'customtube'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="use-case bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Communication', 'customtube'); ?></h4>
                                <ul class="text-sm text-secondary">
                                    <li><?php esc_html_e('Service updates and notifications', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Marketing communications (with consent)', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Legal and policy updates', 'customtube'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="use-case bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Analytics', 'customtube'); ?></h4>
                                <ul class="text-sm text-secondary">
                                    <li><?php esc_html_e('Usage pattern analysis', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Performance optimization', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Content trend analysis', 'customtube'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="use-case bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Security', 'customtube'); ?></h4>
                                <ul class="text-sm text-secondary">
                                    <li><?php esc_html_e('Fraud prevention', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Security monitoring', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Compliance enforcement', 'customtube'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Information Sharing -->
                    <section id="information-sharing" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-share-alt mr-3"></i>
                            <?php esc_html_e('Information Sharing', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-warning-light rounded-lg p-4 mb-6">
                            <p class="text-warning-dark font-medium">
                                <?php esc_html_e('We do not sell your personal information. We may share information only in specific circumstances.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="sharing-info">
                            <p class="mb-4"><?php esc_html_e('We may share your information in the following situations:', 'customtube'); ?></p>
                            <ul class="info-list mb-6">
                                <li><?php esc_html_e('With your explicit consent', 'customtube'); ?></li>
                                <li><?php esc_html_e('With service providers who assist our operations', 'customtube'); ?></li>
                                <li><?php esc_html_e('When required by law or legal processes', 'customtube'); ?></li>
                                <li><?php esc_html_e('To protect our rights and prevent fraud', 'customtube'); ?></li>
                                <li><?php esc_html_e('In connection with business transactions', 'customtube'); ?></li>
                            </ul>
                        </div>
                    </section>

                    <!-- Data Security -->
                    <section id="data-security" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-lock mr-3"></i>
                            <?php esc_html_e('Data Security', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-success-light rounded-lg p-4 mb-6">
                            <p class="text-success-dark font-medium">
                                <?php esc_html_e('We implement appropriate security measures to protect your personal information.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="security-measures grid md:grid-cols-3 gap-4 mb-6">
                            <div class="security-item text-center p-4 bg-surface rounded-lg">
                                <i class="fas fa-shield-alt text-2xl text-primary mb-2"></i>
                                <h4 class="font-semibold mb-2"><?php esc_html_e('Encryption', 'customtube'); ?></h4>
                                <p class="text-sm text-secondary"><?php esc_html_e('Data encrypted in transit and at rest', 'customtube'); ?></p>
                            </div>
                            
                            <div class="security-item text-center p-4 bg-surface rounded-lg">
                                <i class="fas fa-user-lock text-2xl text-primary mb-2"></i>
                                <h4 class="font-semibold mb-2"><?php esc_html_e('Access Control', 'customtube'); ?></h4>
                                <p class="text-sm text-secondary"><?php esc_html_e('Limited access on need-to-know basis', 'customtube'); ?></p>
                            </div>
                            
                            <div class="security-item text-center p-4 bg-surface rounded-lg">
                                <i class="fas fa-eye text-2xl text-primary mb-2"></i>
                                <h4 class="font-semibold mb-2"><?php esc_html_e('Monitoring', 'customtube'); ?></h4>
                                <p class="text-sm text-secondary"><?php esc_html_e('Continuous security monitoring', 'customtube'); ?></p>
                            </div>
                        </div>
                    </section>

                    <!-- Cookies -->
                    <section id="cookies" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-cookie-bite mr-3"></i>
                            <?php esc_html_e('Cookies & Tracking', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-info-light rounded-lg p-4 mb-6">
                            <p class="text-info-dark font-medium">
                                <?php esc_html_e('We use cookies and similar technologies to enhance your browsing experience.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="cookie-types">
                            <div class="cookie-type mb-4">
                                <h4 class="font-semibold mb-2"><?php esc_html_e('Essential Cookies', 'customtube'); ?></h4>
                                <p class="text-secondary"><?php esc_html_e('Required for basic site functionality and security.', 'customtube'); ?></p>
                            </div>
                            
                            <div class="cookie-type mb-4">
                                <h4 class="font-semibold mb-2"><?php esc_html_e('Analytics Cookies', 'customtube'); ?></h4>
                                <p class="text-secondary"><?php esc_html_e('Help us understand how visitors interact with our website.', 'customtube'); ?></p>
                            </div>
                            
                            <div class="cookie-type mb-4">
                                <h4 class="font-semibold mb-2"><?php esc_html_e('Preference Cookies', 'customtube'); ?></h4>
                                <p class="text-secondary"><?php esc_html_e('Remember your settings and personalization choices.', 'customtube'); ?></p>
                            </div>
                        </div>
                    </section>

                    <!-- User Rights -->
                    <section id="user-rights" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-user-shield mr-3"></i>
                            <?php esc_html_e('Your Rights', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-success-light rounded-lg p-4 mb-6">
                            <p class="text-success-dark font-medium">
                                <?php esc_html_e('You have several rights regarding your personal information.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="user-rights grid md:grid-cols-2 gap-4">
                            <div class="right-item bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Access', 'customtube'); ?></h4>
                                <p class="text-sm text-secondary"><?php esc_html_e('Request a copy of your personal data', 'customtube'); ?></p>
                            </div>
                            
                            <div class="right-item bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Correction', 'customtube'); ?></h4>
                                <p class="text-sm text-secondary"><?php esc_html_e('Update or correct inaccurate information', 'customtube'); ?></p>
                            </div>
                            
                            <div class="right-item bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Deletion', 'customtube'); ?></h4>
                                <p class="text-sm text-secondary"><?php esc_html_e('Request deletion of your personal data', 'customtube'); ?></p>
                            </div>
                            
                            <div class="right-item bg-surface rounded-lg p-4">
                                <h4 class="font-semibold mb-2 text-primary"><?php esc_html_e('Portability', 'customtube'); ?></h4>
                                <p class="text-sm text-secondary"><?php esc_html_e('Export your data in a portable format', 'customtube'); ?></p>
                            </div>
                        </div>
                        
                        <div class="rights-contact mt-6 bg-primary-light rounded-lg p-4">
                            <p class="text-primary-dark">
                                <strong><?php esc_html_e('To exercise your rights:', 'customtube'); ?></strong> 
                                <?php esc_html_e('Contact us using the information provided in the Contact section below.', 'customtube'); ?>
                            </p>
                        </div>
                    </section>

                    <!-- Age Restrictions -->
                    <section id="age-restrictions" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-id-card mr-3"></i>
                            <?php esc_html_e('Age Restrictions', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-error-light rounded-lg p-4 mb-6">
                            <p class="text-error-dark font-medium">
                                <?php esc_html_e('Our services are restricted to users who are 18 years of age or older.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="age-content">
                            <p class="mb-4"><?php esc_html_e('By using our website, you confirm that you are at least 18 years old. We do not knowingly collect personal information from minors under 18 years of age.', 'customtube'); ?></p>
                            <p class="mb-4"><?php esc_html_e('If you believe we have inadvertently collected information from someone under 18, please contact us immediately so we can delete such information.', 'customtube'); ?></p>
                        </div>
                    </section>

                    <!-- Policy Changes -->
                    <section id="policy-changes" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-edit mr-3"></i>
                            <?php esc_html_e('Policy Changes', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-warning-light rounded-lg p-4 mb-6">
                            <p class="text-warning-dark font-medium">
                                <?php esc_html_e('We may update this privacy policy from time to time.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="policy-changes-content">
                            <p class="mb-4"><?php esc_html_e('We will notify you of any material changes to this privacy policy by:', 'customtube'); ?></p>
                            <ul class="info-list mb-4">
                                <li><?php esc_html_e('Posting the updated policy on this page', 'customtube'); ?></li>
                                <li><?php esc_html_e('Updating the "Last Updated" date', 'customtube'); ?></li>
                                <li><?php esc_html_e('Sending email notifications for significant changes', 'customtube'); ?></li>
                            </ul>
                            <p><?php esc_html_e('Your continued use of our services after any changes constitutes acceptance of the updated policy.', 'customtube'); ?></p>
                        </div>
                    </section>

                    <!-- Contact -->
                    <section id="contact" class="privacy-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-envelope mr-3"></i>
                            <?php esc_html_e('Contact Information', 'customtube'); ?>
                        </h2>
                        <div class="section-summary bg-info-light rounded-lg p-4 mb-6">
                            <p class="text-info-dark font-medium">
                                <?php esc_html_e('Have questions about this privacy policy? We\'re here to help.', 'customtube'); ?>
                            </p>
                        </div>
                        
                        <div class="contact-info grid md:grid-cols-2 gap-6">
                            <div class="contact-method bg-surface rounded-lg p-6">
                                <h4 class="font-semibold mb-3 text-primary">
                                    <i class="fas fa-envelope mr-2"></i>
                                    <?php esc_html_e('Email', 'customtube'); ?>
                                </h4>
                                <p class="text-secondary mb-2"><?php esc_html_e('For privacy-related inquiries:', 'customtube'); ?></p>
                                <a href="mailto:privacy@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-primary font-medium">
                                    privacy@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                </a>
                            </div>
                            
                            <div class="contact-method bg-surface rounded-lg p-6">
                                <h4 class="font-semibold mb-3 text-primary">
                                    <i class="fas fa-clock mr-2"></i>
                                    <?php esc_html_e('Response Time', 'customtube'); ?>
                                </h4>
                                <p class="text-secondary"><?php esc_html_e('We typically respond to privacy inquiries within 48 hours during business days.', 'customtube'); ?></p>
                            </div>
                        </div>
                        
                        <div class="data-controller mt-6 bg-surface rounded-lg p-6">
                            <h4 class="font-semibold mb-3"><?php esc_html_e('Data Controller', 'customtube'); ?></h4>
                            <p class="text-secondary mb-2"><?php echo esc_html(get_bloginfo('name')); ?></p>
                            <p class="text-secondary"><?php esc_html_e('Website:', 'customtube'); ?> <a href="<?php echo esc_url(home_url()); ?>" class="text-primary"><?php echo esc_html($_SERVER['HTTP_HOST']); ?></a></p>
                        </div>
                    </section>

                </div>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<script>
// Smooth scrolling for table of contents links
document.querySelectorAll('.toc-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Highlight current section in table of contents
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('.privacy-section');
    const tocLinks = document.querySelectorAll('.toc-link');
    
    let currentSection = '';
    sections.forEach(section => {
        const rect = section.getBoundingClientRect();
        if (rect.top <= 100 && rect.bottom >= 100) {
            currentSection = section.id;
        }
    });
    
    tocLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + currentSection) {
            link.classList.add('active');
        }
    });
});
</script>

<?php get_footer(); ?>
