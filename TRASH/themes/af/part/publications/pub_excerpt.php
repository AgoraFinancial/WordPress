<?php
global $af_auth, $af_theme, $af_publications;

$pub_data = $af_publications->af_get_pub_data($pub_id);
$redirect_url = $af_theme->af_get_url();
$permalink = get_permalink($pub_data['pub_id']);
$is_user_logged_in = is_user_logged_in();
$has_pub_access = $af_auth->has_pub_access($pub_data['pubcode']);
?>
<div class="pub-card">
    <h3 class="h4">
        <a href="<?php echo $permalink; ?>" class="pub-logo-link">
            <?php if (!empty($pub_data['pub_logo'])) {
                echo $pub_data['pub_logo'];
            } else {
                echo '<span>' . $pub_data['pub_title'] . '</span>';
            } ?>
        </a>
    </h3>
    <p><?php echo $pub_data['pub_excerpt']; ?></p>
    <div class="pub-actions">
        <?php if (!$is_user_logged_in || !$has_pub_access) { ?>
        <a href="<?php echo $permalink; ?>" class="button button--learn">Learn More</a>
        <?php } if ($is_user_logged_in && $has_pub_access) { ?>
        <a href="<?php echo $permalink; ?>" class="button button--view">View Publication</a>
        <?php } if ($is_user_logged_in && !$has_pub_access && $pub_data['subscribe_url'] && !$pub_data['remove_all_access']) { ?>
        <a href="<?php echo $pub_data['subscribe_url']; ?>" class="button secondary button--subscribe" data-event-category="Subscribe Button">Subscribe</a>
        <?php } if (!$is_user_logged_in) { ?>
        <a href="/login?redirect_to=<?php echo esc_url( $redirect_url ); ?>" class="button secondary button--login">Login</a>
        <?php } ?>
    </div>
    <?php
    // Check if user has pub access, but not lifetime and if lifetime url is available
    if ($has_pub_access && !$af_auth->has_pub_access($pub_data['pubcode'], true) && $pub_data['lifetime_url'] && !$pub_data['remove_all_access']) {
    ?>
    <div class="pub-actions">
        <a href="<?php echo $pub_data['lifetime_url']; ?>" class="button alert button--lifetime" data-event-category="Get Lifetime Access Button">Get Lifetime Access</a>
    </div>
    <?php
    }
    ?>
</div>
