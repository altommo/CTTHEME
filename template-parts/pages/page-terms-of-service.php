<?php
/**
 * Template Name: Terms of Service
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('terms-service-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-file-contract mr-3 text-primary"></i>
                            <?php esc_html_e('Terms of Service', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Please read these terms carefully before using our services.', 'customtube'); ?>
                        </p>
                        <div class="last-updated text-sm text-muted">
                            <i class="fas fa-calendar mr-2"></i>
                            <?php esc_html_e('Last updated:', 'customtube'); ?> 
                            <time datetime="<?php echo date('Y-m-d'); ?>"><?php echo date('F j, Y'); ?></time>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Plain Language Summary -->
            <div class="terms-summary bg-info-light rounded-lg p-6 mb-8">
                <div class="container">
                    <h2 class="summary-title text-xl font-semibold mb-4 text-info-dark">
                        <i class="fas fa-lightbulb mr-2"></i>
                        <?php esc_html_e('What You Need to Know', 'customtube'); ?>
                    </h2>
                    <div class="summary-content grid md:grid-cols-2 gap-4 text-sm">
                        <div class="summary-item">
                            <strong><?php esc_html_e('Age Requirement:', 'customtube'); ?></strong>
                            <?php esc_html_e('You must be 18+ to use this service', 'customtube'); ?>
                        </div>
                        <div class="summary-item">
                            <strong><?php esc_html_e('User Conduct:', 'customtube'); ?></strong>
                            <?php esc_html_e('No illegal or harmful content allowed', 'customtube'); ?>
                        </div>
                        <div class="summary-item">
                            <strong><?php esc_html_e('Privacy:', 'customtube'); ?></strong>
                            <?php esc_html_e('We protect your data per our Privacy Policy', 'customtube'); ?>
                        </div>
                        <div class="summary-item">
                            <strong><?php esc_html_e('Termination:', 'customtube'); ?></strong>
                            <?php esc_html_e('We may suspend accounts for violations', 'customtube'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms Content -->
            <div class="entry-content terms-content">
                <div class="container">
                    
                    <!-- Section Navigation -->
                    <nav class="terms-nav bg-surface rounded-lg p-4 mb-8">
                        <h3 class="nav-title text-lg font-semibold mb-4"><?php esc_html_e('Quick Navigation', 'customtube'); ?></h3>
                        <div class="nav-links grid md:grid-cols-3 gap-2">
                            <a href="#acceptance" class="nav-link"><?php esc_html_e('1. Acceptance of Terms', 'customtube'); ?></a>
                            <a href="#eligibility" class="nav-link"><?php esc_html_e('2. Eligibility', 'customtube'); ?></a>
                            <a href="#user-conduct" class="nav-link"><?php esc_html_e('3. User Conduct', 'customtube'); ?></a>
                            <a href="#content-policy" class="nav-link"><?php esc_html_e('4. Content Policy', 'customtube'); ?></a>
                            <a href="#intellectual-property" class="nav-link"><?php esc_html_e('5. Intellectual Property', 'customtube'); ?></a>
                            <a href="#privacy" class="nav-link"><?php esc_html_e('6. Privacy', 'customtube'); ?></a>
                            <a href="#disclaimers" class="nav-link"><?php esc_html_e('7. Disclaimers', 'customtube'); ?></a>
                            <a href="#limitation-liability" class="nav-link"><?php esc_html_e('8. Limitation of Liability', 'customtube'); ?></a>
                            <a href="#termination" class="nav-link"><?php esc_html_e('9. Termination', 'customtube'); ?></a>
                            <a href="#governing-law" class="nav-link"><?php esc_html_e('10. Governing Law', 'customtube'); ?></a>
                            <a href="#modifications" class="nav-link"><?php esc_html_e('11. Modifications', 'customtube'); ?></a>
                            <a href="#contact-terms" class="nav-link"><?php esc_html_e('12. Contact', 'customtube'); ?></a>
                        </div>
                    </nav>

                    <!-- 1. Acceptance of Terms -->
                    <section id="acceptance" class="terms-section mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <span class="section-number">1.</span>
                            <?php esc_html_e('Acceptance of Terms', 'customtube'); ?>
                        </h2>
                        <div class="section-content">
                            <p class="mb-4"><?php esc_html_e('By accessing or using our website and services, you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using or accessing this site.', 'customtube'); ?></p>
                            
                            <div class="highlight-box bg-warning-light rounded-lg p-4 mb-4">
                                <p class="text-warning-dark font-medium">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <?php esc_html_e('Important: By continuing to use our services, you acknowledge that you have read, understood, and agree to these terms.', 'customtube'); ?>
                                </p>
                            </div>
                            
                            <p><?php esc_html_e('These terms constitute a legally binding agreement between you and our service. Your use of the service is also governed by our Privacy Policy, which is incorporated by reference into these terms.', 'customtube'); ?></p>
                        </div>
                    </section>

                    <!-- Download PDF Link -->
                    <div class="pdf-download text-center mb-8">
                        <a href="#" class="btn btn-secondary px-6 py-3" onclick="window.print(); return false;">
                            <i class="fas fa-download mr-2"></i>
                            <?php esc_html_e('Download PDF Version', 'customtube'); ?>
                        </a>
                    </div>

                    <p class="text-center text-sm text-secondary mb-8">
                        <?php esc_html_e('For questions about these terms, please contact us at', 'customtube'); ?> 
                        <a href="mailto:legal@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-primary">
                            legal@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                        </a>
                    </p>

                </div>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<script>
// Smooth scrolling for navigation links
document.querySelectorAll('.nav-link').forEach(link => {
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

// Highlight current section in navigation
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('.terms-section');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let currentSection = '';
    sections.forEach(section => {
        const rect = section.getBoundingClientRect();
        if (rect.top <= 100 && rect.bottom >= 100) {
            currentSection = section.id;
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + currentSection) {
            link.classList.add('active');
        }
    });
});
</script>

<?php get_footer(); ?>
