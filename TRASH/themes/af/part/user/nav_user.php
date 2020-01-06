<?php global $af_templates;

// Filters
$my_account_slug = apply_filters( 'af_account_nav_slug', 'my-account' );
$my_account_name = apply_filters( 'af_account_nav_label', 'My Account' );

?>

<li class="user-welcome">
    <span>Hi, <?php echo do_shortcode('[first_name]'); ?></span>
    <a href="<?php echo wp_logout_url(); ?>">(Logout)</a>
</li>
<?php
if (get_page_by_path($my_account_slug)) {
?>
<li class="menu-item-has-children">
    <a href="/<?php echo $my_account_slug; ?>/"><?php echo $my_account_name; ?></a>
    <ul class="sub-menu">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'af-account-menu',
            'container' => false,
            'items_wrap' =>  '%3$s'
        ));
        ?>
    </ul>
</li>

<?php
}
$af_templates->af_nav_subs();