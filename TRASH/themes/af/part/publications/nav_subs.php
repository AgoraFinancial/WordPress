<?php
global $af_auth, $af_publications;

// Filters
$my_subscriptions_slug = apply_filters( 'af_sub_nav_slug', 'my-subscriptions' );
$my_subscriptions_name = apply_filters( 'af_sub_nav_label', 'My Subscriptions' );
$view_all_nav_text = apply_filters( 'af_all_sub_nav_label', 'View All Subscriptions' );
$show_view_all_subscriptions = apply_filters( 'af_all_sub_nav_show', true );

// Check if publications plugin is active
if (in_array('af-publications/af-publications.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Get active pub ids
    $pub_ids = $af_publications->af_get_active_pub_ids();
    $sub_page = get_page_by_path($my_subscriptions_slug);
    $class = !empty($pub_ids) ? 'menu-item-has-children' : '' ;
    $show_view_all = $sub_page && $show_view_all_subscriptions;
    // Get parent link
    if ($show_view_all) {
        $sub_page_link = get_permalink($parent->ID);
    } else {
        $sub_page_link = '#';
    }
?>
<li class="subscriptions-menu <?php echo $class; ?>">

    <a href="<?php echo $sub_page_link; ?>"><?php echo $my_subscriptions_name; ?></a>

    <ul class="sub-menu">
        <?php
        if ($show_view_all) {
            echo '<li><a href="'.$sub_page_link.'">' . $view_all_nav_text . '</a></li>';
        }
        // Get list of accessible pubs
        if (!empty($pub_ids)) {
            foreach ($pub_ids as $id) {
                $pubcode = get_post_meta($id, 'pubcode', true);
                $permalink = get_permalink($id);
                $title = get_the_title($id);
                if ($af_auth->has_pub_access($pubcode)) {
                    echo '<li><a href="' . $permalink . '">' . $title . '</a></li>';
                }
            }
        }
        ?>
    </ul>
</li>
<?php } ?>