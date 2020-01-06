<?php
global $post, $af_auth, $af_theme, $af_publications, $af_users;

$is_user_logged_in = is_user_logged_in();
$is_lifetime = $af_auth->is_post_lifetime($post->ID);
$is_plus = $af_auth->is_post_plus($post->ID);
$pubs = $af_publications->af_get_pubs($post);
$pub_count = count($pubs);
$renew_eligible = $af_users->af_check_renewal_eligibility($pub_data['pubcode']);
$is_user_logged_in = is_user_logged_in();

// Setup buttons
$login = '';
$redirect_url = $af_theme->af_get_url();

if (!$is_user_logged_in) {
    $login = '<a href="/login?redirect_to=' . esc_url( $redirect_url ) . '" class="button secondary">Login</a>';
}
?>
<div class="callout warning paygate-wrapper">
    <?php if ($is_lifetime) { // Headline ?>
    <h3>This Content Is For Lifetime Members Only.</h3>
    <?php } elseif ($is_plus) { ?>
    <h3>This Content Is For Plus Members Only.</h3>
    <?php } else { ?>
    <h3>You Must Be A Subscriber To View This Content.</h3>
    <?php } ?>
    <p>
        <?php if (!$is_user_logged_in) { // If not logged in ?>
        If you are already a subscriber, click the login button below to get access.
        <?php } ?>
        Not yet a subscriber? Checkout our publication<?php if ($pub_count > 1) echo '(s)'; ?> below and get access today!
    </p>
    <hr>
    <?php
    // Get access for each pub
    foreach ($pubs as $pub) {
        $pub_id = $af_publications->af_get_pub_ID($pub);
        $pub_data = $af_publications->af_get_pub_data($pub_id);
        $is_plus = $pub_data['plus_check'];

        $subscribe = !$is_plus && !$af_auth->has_pub_access($pub) && !$pub_data['remove_all_access'] && !$is_lifetime ? $pub_data['subscribe_url'] : '';
        $renewal = $is_user_logged_in && $renew_eligible && !$pub_data['remove_all_access'] ? $pub_data['renewal_url'] : '';
        if($is_user_logged_in) {
            $lifetime = $pub_data['lifetime_url'] && !$af_auth->has_pub_access($pub_data['pubcode'], true) && $af_auth->has_pub_access($pub) && !$pub_data['remove_all_access'] && $is_lifetime ? $pub_data['lifetime_url'] : '';
        } else {
            $lifetime = $pub_data['lifetime_url'] && $is_lifetime ? $pub_data['lifetime_url'] : '';
        }

        $pub_title = $pub_data['pub_title'];
        $paygate_content = $pub_data['paygate_content'];

        $plus_name = $pub_data['plus_name'];
        $plus_paygate_content = $pub_data['plus_paygate_content'];

        $paygate = $is_plus ? $plus_paygate_content : $paygate_content;

        // Setup pub actions
        if ($subscribe) $subscribe = '<a href="' . $subscribe . '" class="button" data-event-category="Get Access Button">Get Access</a>';
        if ($renewal) $renewal = '<a href="' . $renewal . '" class="button" data-event-category="Renew Subscription Button">Renew Subscription</a>';
        if ($lifetime) $lifetime = '<a href="' . $lifetime . '" class="button alert" data-event-category="Get Lifetime Access Button">Get Lifetime Access</a>';
        ?>
        <h4>
            <?php
            echo $pub_title;
            if ($is_plus) echo '- '.$plus_name;
            ?>
        </h4>
        <?php echo $paygate; ?>
        <p class="paygate-actions">
            <?php echo $login . $subscribe . $renewal . $lifetime; ?>
        </p>
    <?php } ?>
</div>