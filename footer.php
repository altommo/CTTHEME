    <?php
    // The structure should be:
    // header.php opens: <div id="page"> <div id="content"> <div class="site-inner">
    // footer.php closes: </div><!-- .site-inner --> </div><!-- #content --> </div><!-- #page -->
    ?>
            </div><!-- .site-inner -->
        </div><!-- #content -->

    <?php do_action('customtube_before_footer'); ?>

    <!-- Footer Ad -->
    <div class="footer-ad-section">
        <div class="container">
            <div class="footer-ad">
                <a href="#"><img src="https://via.placeholder.com/970x250/333333/FFFFFF?text=Sponsored+Ad" alt="Sponsored Ad"></a>
            </div>
        </div>
    </div>

    <!-- Pre-Footer Text -->
    <div class="pre-footer">
        <div class="container">
            Lustful Clips brings you free adult videos, XXX movies, and hardcore porn in the best quality.
        </div>
    </div>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-widgets">
                <div class="widget">
                    <h3>Lustful Clips</h3>
                    <p>Adult video sharing platform</p>
                </div>
                <div class="widget">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="#">HD-Porn</a></li>
                        <li><a href="#">Babe</a></li>
                        <li><a href="#">Amateur</a></li>
                        <li><a href="#">Anal</a></li>
                    </ul>
                </div>
                <div class="widget">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Privacy Policy</a></li>
                        <li><a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>">Terms of Service</a></li>
                        <li><a href="<?php echo esc_url(home_url('/dmca/')); ?>">DMCA</a></li>
                        <li><a href="<?php echo esc_url(home_url('/2257-compliance/')); ?>">2257 Compliance</a></li>
                    </ul>
                </div>
                <div class="widget">
                    <h3>Discover</h3>
                    <ul>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Insights</a></li>
                        <li><a href="#">Wellness</a></li>
                        <li><a href="#">Mobile</a></li>
                    </ul>
                </div>
            </div>
            <div class="bottom-bar">
                &copy; <?php echo date('Y'); ?> Lustful Clips. All rights reserved.
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
