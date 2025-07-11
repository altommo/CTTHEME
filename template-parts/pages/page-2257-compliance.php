<?php
/**
 * Template Name: 2257 Compliance
 *
 * @package CustomTube
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('compliance-2257-page'); ?>>
            
            <!-- Page Header -->
            <header class="entry-header page-header-section">
                <div class="container">
                    <div class="header-content text-center">
                        <h1 class="entry-title page-title">
                            <i class="fas fa-shield-check mr-3 text-success"></i>
                            <?php esc_html_e('18 U.S.C. § 2257 Compliance', 'customtube'); ?>
                        </h1>
                        <p class="page-subtitle text-lg text-secondary mb-4">
                            <?php esc_html_e('Record-Keeping Requirements Compliance Statement', 'customtube'); ?>
                        </p>
                        <div class="compliance-status bg-success-light rounded-lg p-4 mt-4">
                            <p class="text-success-dark font-medium">
                                <i class="fas fa-check-circle mr-2"></i>
                                <?php esc_html_e('This website is fully compliant with 18 U.S.C. § 2257 and 28 C.F.R. 75 record-keeping requirements.', 'customtube'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Compliance Statement -->
            <div class="compliance-statement bg-info-light rounded-lg p-6 mb-8">
                <div class="container">
                    <h2 class="statement-title text-xl font-semibold mb-4 text-info-dark">
                        <i class="fas fa-file-contract mr-2"></i>
                        <?php esc_html_e('Compliance Statement', 'customtube'); ?>
                    </h2>
                    <div class="statement-content text-info-dark">
                        <p class="mb-4 font-medium"><?php esc_html_e('All models, actors, actresses and other persons that appear in any visual depiction of actual sexually explicit conduct appearing or otherwise contained in this website were over the age of eighteen years at the time of the creation of such depictions.', 'customtube'); ?></p>
                        <p class="mb-4"><?php esc_html_e('All other visual depictions displayed on this website are exempt from the provision of 18 U.S.C. § 2257 and 28 C.F.R. 75 because they do not portray conduct as specifically defined in 18 U.S.C § 2256 (2)(A) through (D), but are merely indicative of the types of lewd visual depictions of persons of the age of majority found on this website.', 'customtube'); ?></p>
                        <p><?php esc_html_e('Moreover, with respect to all visual depictions displayed on this website, whether of actual sexually explicit conduct, simulated sexual content or otherwise, all persons in said visual depictions were at least 18 years of age when said visual depictions were created.', 'customtube'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="entry-content compliance-content">
                <div class="container">
                    
                    <!-- Custodian Information -->
                    <section class="custodian-info mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-user-shield mr-3"></i>
                            <?php esc_html_e('Custodian of Records', 'customtube'); ?>
                        </h2>
                        
                        <div class="custodian-details bg-surface rounded-lg p-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="custodian-contact">
                                    <h3 class="text-lg font-semibold mb-3"><?php esc_html_e('Primary Custodian', 'customtube'); ?></h3>
                                    <div class="contact-info space-y-2">
                                        <div class="info-item">
                                            <strong><?php esc_html_e('Name:', 'customtube'); ?></strong> 
                                            <?php echo esc_html(get_bloginfo('name')); ?> Records Custodian
                                        </div>
                                        <div class="info-item">
                                            <strong><?php esc_html_e('Title:', 'customtube'); ?></strong> 
                                            <?php esc_html_e('Custodian of Records', 'customtube'); ?>
                                        </div>
                                        <div class="info-item">
                                            <strong><?php esc_html_e('Organization:', 'customtube'); ?></strong> 
                                            <?php echo esc_html(get_bloginfo('name')); ?>
                                        </div>
                                        <div class="info-item">
                                            <strong><?php esc_html_e('Email:', 'customtube'); ?></strong> 
                                            <a href="mailto:records@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-primary">
                                                records@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="record-location">
                                    <h3 class="text-lg font-semibold mb-3"><?php esc_html_e('Records Location', 'customtube'); ?></h3>
                                    <div class="location-info">
                                        <p class="mb-3"><?php esc_html_e('All records required by 18 U.S.C. § 2257 and 28 C.F.R. 75 are kept by the Custodian of Records at the following location:', 'customtube'); ?></p>
                                        <div class="address bg-white rounded border p-3">
                                            <p class="font-mono text-sm">
                                                <?php echo esc_html(get_bloginfo('name')); ?><br>
                                                <?php esc_html_e('Attn: Records Custodian', 'customtube'); ?><br>
                                                <?php esc_html_e('[Physical Address]', 'customtube'); ?><br>
                                                <?php esc_html_e('[City, State, ZIP Code]', 'customtube'); ?><br>
                                                <?php esc_html_e('[Country]', 'customtube'); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Download Release Form -->
                    <section class="release-form mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-file-download mr-3"></i>
                            <?php esc_html_e('Model Release Form', 'customtube'); ?>
                        </h2>
                        
                        <div class="form-download bg-secondary-light rounded-lg p-6">
                            <p class="mb-4"><?php esc_html_e('Content producers must use our official model release form to ensure compliance with record-keeping requirements.', 'customtube'); ?></p>
                            
                            <div class="download-buttons d-flex gap-4 flex-wrap">
                                <a href="#" class="btn btn-primary px-6 py-3" onclick="window.open('#', '_blank'); return false;">
                                    <i class="fas fa-file-pdf mr-2"></i>
                                    <?php esc_html_e('Download PDF Form', 'customtube'); ?>
                                </a>
                                
                                <a href="#" class="btn btn-secondary px-6 py-3" onclick="window.open('#', '_blank'); return false;">
                                    <i class="fas fa-file-word mr-2"></i>
                                    <?php esc_html_e('Download Word Form', 'customtube'); ?>
                                </a>
                            </div>
                            
                            <div class="form-requirements mt-4 bg-white rounded p-4">
                                <h4 class="font-semibold mb-2"><?php esc_html_e('Required Documentation:', 'customtube'); ?></h4>
                                <ul class="text-sm text-secondary">
                                    <li><?php esc_html_e('Completed and signed model release form', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Copy of government-issued photo ID', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Proof of age verification', 'customtube'); ?></li>
                                    <li><?php esc_html_e('Contact information for all participants', 'customtube'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Contact Information -->
                    <section class="compliance-contact mb-10">
                        <h2 class="section-title text-2xl font-bold mb-4 text-primary">
                            <i class="fas fa-envelope mr-3"></i>
                            <?php esc_html_e('Contact Information', 'customtube'); ?>
                        </h2>
                        
                        <div class="contact-methods grid md:grid-cols-2 gap-6">
                            <div class="contact-method bg-surface rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-3 text-primary">
                                    <i class="fas fa-folder mr-2"></i>
                                    <?php esc_html_e('Records Inquiries', 'customtube'); ?>
                                </h3>
                                <p class="text-secondary mb-3"><?php esc_html_e('For record-keeping and compliance questions:', 'customtube'); ?></p>
                                <a href="mailto:records@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-primary font-medium text-lg">
                                    records@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                </a>
                            </div>
                            
                            <div class="contact-method bg-surface rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-3 text-secondary">
                                    <i class="fas fa-balance-scale mr-2"></i>
                                    <?php esc_html_e('Legal Department', 'customtube'); ?>
                                </h3>
                                <p class="text-secondary-dark mb-3"><?php esc_html_e('For legal and compliance matters:', 'customtube'); ?></p>
                                <a href="mailto:legal@<?php echo esc_attr($_SERVER['HTTP_HOST']); ?>" class="text-secondary font-medium text-lg">
                                    legal@<?php echo esc_html($_SERVER['HTTP_HOST']); ?>
                                </a>
                            </div>
                        </div>
                    </section>

                </div>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
