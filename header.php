<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <div id="page" class="site">
        <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'customtube' ); ?></a>

        <!-- Header Stack -->
        <div class="header-stack">
            <!-- Utility Bar -->
            <div class="utility-bar">
                <a href="https://myyoungtits.com" class="utility-link">MyYoungTits</a>
                <a href="#" class="utility-link">Spicevids</a>
                <a href="#" class="utility-link">Sexual Wellness</a>
                <a href="#" class="utility-link">Affiliate Link</a>
            </div>

            <?php
            // Include the enhanced navigation template part which contains the main header structure
            get_template_part('template-parts/enhanced-navigation');
            ?>

            <!-- Secondary Navigation -->
            <nav class="secondary-nav">
                <ul class="secondary-nav-list">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                    <li class="has-dropdown">
                        <a href="<?php echo esc_url(home_url('/porn-videos')); ?>">Porn Videos ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Latest Porn</a></li>
                            <li><a href="#">Popular Porn</a></li>
                            <li><a href="#">HD Porn</a></li>
                            <li><a href="#">VR Porn</a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="<?php echo esc_url(home_url('/categories')); ?>">Categories ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Amateur</a></li>
                            <li><a href="#">Anal</a></li>
                            <li><a href="#">Asian</a></li>
                            <li><a href="#">Big Tits</a></li>
                            <li><a href="#">Blonde</a></li>
                            <li><a href="#">Brunette</a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="<?php echo esc_url(home_url('/live-cams')); ?>" class="nav-ad">Live Cams ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Live Girls</a></li>
                            <li><a href="#">Gay Cams</a></li>
                            <li><a href="#">Trans Cams</a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo esc_url(home_url('/fuck-now')); ?>" class="nav-ad">Fuck Now</a></li>
                    <li class="has-dropdown">
                        <a href="<?php echo esc_url(home_url('/community')); ?>">Community ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Forum</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Support</a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="<?php echo esc_url(home_url('/photos-gifs')); ?>">Photos & GIFs ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Latest Photos</a></li>
                            <li><a href="#">Popular GIFs</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div><!-- .header-stack -->

        <?php
        // Age verification modal - only display if not already verified
        if (!isset($_COOKIE['age_verified'])) {
            get_template_part('template-parts/age-verification');
        }
        ?>

        <div id="content" class="site-content">
            <?php
            // Action hook for ads or content that should appear directly below the fixed header/nav
            // but still be part of the scrollable content.
            do_action('customtube_before_site_inner');
            ?>
            <div class="site-inner">
                <?php
                // Include the category-tags-nav template part (sidebar)
                get_template_part('template-parts/category-tags-nav');
                ?>
