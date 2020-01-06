<?php
global $af_auth, $af_publications;

$pub_data = $af_publications->af_get_pub_data($pub_id);
$has_pub_access = $af_auth->has_pub_access($pub_data['pubcode']);
$permalink = get_permalink($pub_data['pub_id']);
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
    <?php if (trim($pub_data['editor_name'])) { ?>
    <div class="publication-editor">
        <div class="editor-image">
            <a href="<?php echo $pub_data['editor_url']; ?>">
                <?php echo $pub_data['editor_image']; ?>
            </a>
        </div>
        <div class="editor-name">
            <span>
                <strong>Editor:</strong>
                <a href="<?php echo $pub_data['editor_url']; ?>"><?php echo $pub_data['editor_name']; ?></a>
            </span>
        </div>
    </div>
    <?php } ?>
    <div class="pub-actions">
        <?php
        if ($has_pub_access) {
        ?>
        <a href="<?php echo $permalink; ?>" class="button button--view">View This Publication</a>
        <?php
        } else {
        ?>
        <a href="<?php echo $permalink; ?>" class="button button--learn">Learn More</a>
        <?php
            if ($pub_data['subscribe_url'] && !$pub_data['remove_all_access']) {
        ?>
        <a href="<?php echo $pub_data['subscribe_url']; ?>" class="button secondary button--subscribe" data-event-category="Subscribe Button">Subscribe</a>
        <?php
            }
        }
        ?>
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
