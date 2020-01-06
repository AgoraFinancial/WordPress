<?php global $af_auth, $af_setup, $af_templates; ?>
<body <?php body_class($af_setup->af_custom_body_class()); ?>>
    <?php $af_templates->af_scripts_body(); ?>
    <div class="search-bar">
        <div class="row">
            <div class="small-12 medium-8 medium-centered columns">
                <?php $af_templates->af_search_bar(); ?>
            </div>
        </div>
    </div>
    <?php if(!is_user_logged_in()) $af_templates->af_modal_login(); ?>
    <header class="site-header">
        <?php
        if (defined('HAS_AFR') && HAS_AFR === true && is_user_logged_in() && $af_auth->is_afr_member())
            $af_templates->af_toolbar();
        ?>
        <div class="row">
            <div class="small-12 columns header-wrap">
                <!--Logo-->
                <div class="site-logo">
                    <a href="<?php echo home_url('/'); ?>">
                        <?php $af_templates->af_logo(); ?>
                    </a>
                 </div>
                <!--END Logo-->
                <div class="nav-toggle float-right">
                    <span class="button secondary">
                        <svg class="icon icon-bars">
                            <use xlink:href="<?php echo PARENT_PATH_URI; ?>/img/symbol-defs.svg#icon-bars"></use>
                        </svg>
                    </span>
                </div>
                <!--Main Menu-->
                <nav class="main-nav">
                    <ul class="menu float-right">
                        <?php $af_templates->af_nav_main(); ?>
                    </ul>
                </nav>
                <!--END Main Menu-->
            </div>
        </div>
    </header>