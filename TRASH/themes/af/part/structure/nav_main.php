<?php
global $af_templates;

// Logged in users
if (is_user_logged_in()) {
    $af_templates->af_nav_user();
    wp_nav_menu(array(
        'theme_location' => 'af-loggedin-menu',
        'container' => false,
        'items_wrap' =>  '%3$s'
    ));
} else {
    $af_templates->af_nav_pubs();
    wp_nav_menu(array(
        'theme_location' => 'af-main-menu',
        'container' => false,
        'items_wrap' =>  '%3$s'
    ));
}
?>
<li class="search-toggle">
    <svg class="icon icon-search">
        <use xlink:href="<?php echo PARENT_PATH_URI; ?>/img/symbol-defs.svg#icon-search"></use>
    </svg>
    <?php $af_templates->af_search_bar(); ?>
</li>