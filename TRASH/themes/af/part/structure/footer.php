<?php global $af_templates; ?>
        <footer class="site-footer">
            <div class="row">
                <div class="small-12 columns footer-wrap">
                    <?php
                    $af_templates->af_nav_footer();
                    $af_templates->af_social();
                    $af_templates->af_copyright();
                    ?>
                </div>
            </div>
        </footer>
        <?php $af_templates->af_scripts_footer(); ?>
    </body>
</html>