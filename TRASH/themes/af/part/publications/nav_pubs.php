<?php
global $af_templates, $af_publications;

// Check if publications plugin is active
if (in_array('af-publications/af-publications.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Get active pub ids
    $pub_ids = $af_publications->af_get_active_pub_ids();
    $class = !empty($pub_ids) ? 'menu-item-has-children' : '' ;
?>

<li class="publications-menu <?php echo $class; ?>"><a href="<?php echo get_post_type_archive_link('publications'); ?>"><?php echo apply_filters( 'af_pub_nav_label', 'Publications' ); ?></a>
    <?php if (!empty($pub_ids)) { ?>
    <ul class="sub-menu">
        <?php foreach ($pub_ids as $id) { ?>
        <li><a href="<?php echo get_permalink($id); ?>"><?php echo get_the_title($id); ?></a></li>
        <?php } ?>
    </ul>
    <?php } ?>
</li>

<?php } ?>