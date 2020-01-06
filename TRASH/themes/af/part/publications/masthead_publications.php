<?php
global $af_templates, $af_auth, $af_theme, $af_publications, $af_users;

// Set default pub ID
$pub_id = $post->ID;
$valid_post_type = in_array($post->post_type, apply_filters('af_additional_masthead_pub_post_type', array('post')));
// If post_type is post, get authorized pub ID
if ($valid_post_type) {
    $valid = $af_publications->af_get_valid_pub_ID();

    if (!empty($valid)) {
        $pub_id = $valid;
    } else { // If not valid, get fallback pub
        $pubs = $af_publications->af_get_pubs($post);
        if (is_array($pubs) && count($pubs)) {
            $pub_id = $af_publications->af_get_pub_ID($pubs[0]);
        }
    }
}

$pub_data = $af_publications->af_get_pub_data($pub_id);
$redirect_url = $af_theme->af_get_url();
$pub_folded = $pub_data['pub_folded'];
$has_pub_access = $af_auth->has_pub_access($pub_data['pubcode']);
$renew_eligible = $af_users->af_check_renewal_eligibility($pub_data['pubcode']);
$is_user_logged_in = is_user_logged_in();
?>
<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <div class="masthead">
                <?php // Get pub header image, else pub title
                if ($pub_data['header_image']) {
                    echo $pub_data['header_image'];
                } else {
                    echo '<h1>'.$pub_data['pub_title'].'</h1>';
                } ?>
                <div class="pub-cta">
                    <?php // If user doesn't have access and subscribe link available
                    if ($pub_data['subscribe_url'] && !$has_pub_access && !$pub_data['remove_all_access'] && !$pub_folded) { ?>
                        <a href="<?php echo $pub_data['subscribe_url']; ?>" class="button button--subscribe" data-event-category="Subscribe Today Button">Subscribe Now</a>
                    <?php } ?>

                    <?php // If user is not logged in
                    if (!$is_user_logged_in) { ?>
                        <a href="/login/?redirect_to=<?php echo esc_url( $pub_data['pub_url'] ); ?>" class="button button--login" data-event-category="Login Button">Login</a>
                    <?php } ?>

                    <?php // If use is eligible for renewal
                    if ($is_user_logged_in && !$has_pub_access && $renew_eligible && $pub_data['renewal_url'] && !$pub_data['remove_all_access'] && !$pub_folded) { ?>
                        <a href="<?php echo $pub_data['renewal_url']; ?>" class="button button--renew" data-event-category="Renew Subscription Button">Renew Subscription</a>
                    <?php } ?>

                    <?php // If has pub access, but not lifetime
                    if ($has_pub_access && !$af_auth->has_pub_access($pub_data['pubcode'], true) && $pub_data['lifetime_url'] && !$pub_data['remove_all_access']) { ?>
                        <a href="<?php echo $pub_data['lifetime_url']; ?>" class="button alert button--lifetime" data-event-category="Get Lifetime Access Button">Get Lifetime Access</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php // If user has access, display category nav
    if ($has_pub_access) {
        set_query_var('pubID', $pub_id);
        $af_templates->af_nav_pub_tabs();
    }
    ?>
</header>