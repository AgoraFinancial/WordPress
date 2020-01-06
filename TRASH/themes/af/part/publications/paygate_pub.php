<?php
global $af_auth, $af_theme, $af_publications;

$pub_data = $af_publications->af_get_pub_data($post->ID);
$is_user_logged_in = is_user_logged_in();

// Setup action buttons
$login = '';
if (!$is_user_logged_in) {
    $login = '<a href="/login?redirect_to=' . esc_url( get_permalink() ) . '" class="button secondary">Login</a>';
}

$subscribe = $pub_data['subscribe_url'];
if ($subscribe) {
    $subscribe = '<a href="'.$subscribe.'" class="button" data-event-category="Get Access Button">Get Access</a>';
}

$renewal = '';
if ($is_user_logged_in) {
    $renewal = $pub_data['renewal_url'];
    if ($renewal) {
        $renewal = '<a href="'.$renewal.'" class="button" data-event-category="Renew Subscription Button">Renew Subscription</a>';
    }
}

$lifetime = $pub_data['lifetime_url'];
if ($lifetime) {
    $lifetime = '<a href="'.$lifetime.'" class="button alert" data-event-category="Get Lifetime Access Button">Get Lifetime Access</a>';
}

$redirect_url = $af_theme->af_get_url();
?>
<div class="callout warning paygate-wrapper">
    <h3>You Must Be A Subscriber To Unlock This Content</h3>
    <?php echo $pub_data['paygate_content']; ?>
    <hr>
    <?php if (!$is_user_logged_in) { // If not logged in ?>
    <p>If you are already a subscriber, click the login button below to get access.</p>
    <p class="paygate-actions"><?php echo $login . $subscribe . $lifetime; ?></p>
    <?php } elseif ($is_user_logged_in && !$af_auth->has_pub_access($pub_data['pubcode'])) { // If logged in but no access ?>
    <p>Your current account does not have access to this publication.<p>
    <p class="paygate-actions"><?php echo $subscribe . $renewal . $lifetime; ?></p>
    <?php } else { // If all else fails ?>
    <p>Please contact customer support for additional information.</p>
    <?php } ?>
</div>