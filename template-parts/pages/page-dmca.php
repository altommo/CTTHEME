<?php
/**
 * Template Name: DMCA Policy
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('dmca-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-copyright mr-3 text-warning"></i>
                            <?php esc_html_e('DMCA Policy', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Digital Millennium Copyright Act Compliance and Takedown Procedures', 'customtube'); ?>
                        </p>
                        <div class="last-updated text-sm text-muted">
                            <i class="fas fa-calendar mr-2"></i>
                            <?php esc_html_e('Last updated:', 'customtube'); ?> 
                            <time datetime="<?php echo date('Y-m-d'); ?>"><?php echo date('F j, Y'); ?></time>
                        </div>
                    </div>
                </div>
            </header>

            <!-- DMCA Agent Information -->
            <div class="dmca-agent bg-info-light rounded-lg p-6 mb-8">
                <div class="container">
                    <h2 class="agent-title text-xl font-semibold mb-4 text-info-dark">
                        <i class="fas fa-user-tie mr-2"></i>
                        <?php esc_html_e('Designated DMCA Agent', 'customtube'); ?>
                    </h2>
                    <div class="agent-info grid md:grid-cols-2 gap-6">
                        <div class="agent-contact bg-white rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-3"><?php esc_html_e('Contact Information', 'customtube'); ?></h3>
                            <div class="contact-details space-y-2">
                                <div class="contact-item">
                                    <strong><?php esc_html_e('Name:', 'customtube'); ?></strong> 
                                    <?php echo esc_html(get_bloginfo('name')); ?> DMCA Agent
                                </div>
                                <div class="contact-item">
                                    <strong><?php esc_html_e('Email:', 'customtube'); ?></strong> 
                                    <a href="mailto:dmca@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-primary">
                                        dmca@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                    </a>
                                </div>
                                <div class="contact-item">
                                    <strong><?php esc_html_e('Website:', 'customtube'); ?></strong> 
                                    <a href="<?php echo esc_url(home_url()); ?>" class="text-primary">
                                        <?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="response-time bg-white rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-3"><?php esc_html_e('Response Timeframes', 'customtube'); ?></h3>
                            <div class="time-info space-y-2 text-sm">
                                <div class="time-item">
                                    <strong><?php esc_html_e('Acknowledgment:', 'customtube'); ?></strong> 
                                    <?php esc_html_e('Within 24 hours', 'customtube'); ?>
                                </div>
                                <div class="time-item">
                                    <strong><?php esc_html_e('Investigation:', 'customtube'); ?></strong> 
                                    <?php esc_html_e('1-3 business days', 'customtube'); ?>
                                </div>
                                <div class="time-item">
                                    <strong><?php esc_html_e('Action Taken:', 'customtube'); ?></strong> 
                                    <?php esc_html_e('Within 7 business days', 'customtube'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="entry-content dmca-content">
                <div class="container">
                    
                    <!-- DMCA Notice Template -->
                    <section id="dmca-template" class="dmca-template mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-file-alt mr-3"></i>
                            <?php esc_html_e('DMCA Notice Template', 'customtube'); ?>
                        </h2>
                        
                        <div class="template-container bg-surface rounded-lg p-6">
                            <div class="template-header mb-4">
                                <p class="text-sm text-secondary mb-2"><?php esc_html_e('Copy and complete the template below:', 'customtube'); ?></p>
                                <button class="btn btn-secondary btn-sm copy-template" onclick="copyTemplate()">
                                    <i class="fas fa-copy mr-2"></i>
                                    <?php esc_html_e('Copy Template', 'customtube'); ?>
                                </button>
                            </div>
                            
                            <div class="template-content bg-white rounded border p-4 font-mono text-sm" id="template-text">
                                <p><strong>DMCA Takedown Notice</strong></p>
                                <br>
                                <p><strong>To:</strong> <?php echo esc_html(get_bloginfo('name')); ?> DMCA Agent</p>
                                <p><strong>Email:</strong> dmca@<?php echo esc_html($_SERVER['HTTP_HOST']); ?></p>
                                <br>
                                <p><strong>1. Identification of the copyrighted work:</strong></p>
                                <p>[Describe the copyrighted work that you claim has been infringed]</p>
                                <br>
                                <p><strong>2. Identification of the infringing material:</strong></p>
                                <p>[Provide the specific URL(s) where the infringing material is located]</p>
                                <br>
                                <p><strong>3. Contact information:</strong></p>
                                <p>Name: [Your full name]</p>
                                <p>Address: [Your mailing address]</p>
                                <p>Phone: [Your phone number]</p>
                                <p>Email: [Your email address]</p>
                                <br>
                                <p><strong>4. Good faith statement:</strong></p>
                                <p>I have a good faith belief that the use of the material described above is not authorized by the copyright owner, its agent, or the law.</p>
                                <br>
                                <p><strong>5. Accuracy statement:</strong></p>
                                <p>I swear, under penalty of perjury, that the information in this notification is accurate and that I am the copyright owner or am authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</p>
                                <br>
                                <p><strong>Signature:</strong> [Your signature]</p>
                                <p><strong>Date:</strong> [Current date]</p>
                            </div>
                        </div>
                    </section>

                    <!-- False Claims Warning -->
                    <section class="false-claims mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-balance-scale mr-3"></i>
                            <?php esc_html_e('False Claims & Penalties', 'customtube'); ?>
                        </h2>
                        
                        <div class="false-claims-content">
                            <div class="warning-box bg-error-light rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-semibold mb-3 text-error-dark">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <?php esc_html_e('Important Legal Warning', 'customtube'); ?>
                                </h3>
                                <p class="text-error-dark"><?php esc_html_e('Filing false DMCA claims is perjury and may result in substantial legal penalties including damages and attorney fees.', 'customtube'); ?></p>
                            </div>
                            
                            <p class="mb-4"><?php esc_html_e('Before filing a DMCA notice, please ensure that you have a good faith belief that the use is not authorized. Consider whether the use might qualify as fair use or fall under other copyright exemptions.', 'customtube'); ?></p>
                            
                            <h3 class="text-xl font-semibold mb-3"><?php esc_html_e('Potential Consequences of False Claims:', 'customtube'); ?></h3>
                            <ul class="consequences-list mb-6">
                                <li><?php esc_html_e('Legal liability for damages caused by false takedown', 'customtube'); ?></li>
                                <li><?php esc_html_e('Payment of defendant\'s attorney fees', 'customtube'); ?></li>
                                <li><?php esc_html_e('Potential criminal perjury charges', 'customtube'); ?></li>
                                <li><?php esc_html_e('Permanent record of false claim filing', 'customtube'); ?></li>
                            </ul>
                        </div>
                    </section>

                    <!-- Contact Information -->
                    <section class="dmca-contact mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-envelope mr-3"></i>
                            <?php esc_html_e('Contact & Support', 'customtube'); ?>
                        </h2>
                        
                        <div class="contact-methods grid md:grid-cols-2 gap-6">
                            <div class="contact-method bg-surface rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-3 text-primary">
                                    <i class="fas fa-file-signature mr-2"></i>
                                    <?php esc_html_e('DMCA Notices', 'customtube'); ?>
                                </h3>
                                <p class="text-secondary mb-3"><?php esc_html_e('For copyright takedown requests:', 'customtube'); ?></p>
                                <a href="mailto:dmca@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-primary font-medium text-lg">
                                    dmca@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                </a>
                            </div>
                            
                            <div class="contact-method bg-surface rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-3 text-secondary">
                                    <i class="fas fa-question-circle mr-2"></i>
                                    <?php esc_html_e('General Questions', 'customtube'); ?>
                                </h3>
                                <p class="text-secondary-dark mb-3"><?php esc_html_e('For questions about this policy:', 'customtube'); ?></p>
                                <a href="mailto:legal@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-secondary font-medium text-lg">
                                    legal@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="additional-info mt-6 bg-info-light rounded-lg p-4">
                            <h4 class="font-semibold mb-2 text-info-dark"><?php esc_html_e('Need Help?', 'customtube'); ?></h4>
                            <p class="text-info-dark text-sm"><?php esc_html_e('If you\'re unsure whether your claim qualifies for DMCA protection, consider consulting with a qualified attorney before filing a notice.', 'customtube'); ?></p>
                        </div>
                    </section>

                </div>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<script>
function copyTemplate() {
    const templateText = document.getElementById('template-text');
    const range = document.createRange();
    range.selectNode(templateText);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
    
    try {
        document.execCommand('copy');
        const button = document.querySelector('.copy-template');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-2"></i><?php esc_html_e('Copied!', 'customtube'); ?>';
        button.classList.add('btn-success');
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
        }, 2000);
    } catch (err) {
        console.error('Failed to copy template:', err);
    }
    
    window.getSelection().removeAllRanges();
}
</script>

<?php get_footer(); ?>
