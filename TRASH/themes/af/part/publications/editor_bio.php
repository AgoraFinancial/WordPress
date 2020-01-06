<?php
if (!empty($pub_data['editor_image']) && !empty($pub_data['editor_name']) && !empty($pub_data['editor_bio']) && !empty($pub_data['editor_url'])) {
    $template_directory_uri = PARENT_PATH_URI;
?>
<hr class="custom-separator">
<section class="callout author-bio">
    <div class="author-bio-image">
        <?php echo $pub_data['editor_image']; ?>
    </div>
    <div class="author-bio-byline">
        <h4><strong>Meet the Editor:</strong> <?php echo $pub_data['editor_name']; ?></h4>
        <?php if (!empty($pub_data['editor_facebook']) || !empty($pub_data['editor_twitter'])) { ?>
        <p class="author-bio-social">
            <?php
            if (!empty($pub_data['editor_facebook'])) {
            ?>
            <a href="<?php echo $pub_data['editor_facebook']; ?>" target="_blank">
                <svg class="icon icon-facebook">
                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-facebook"></use>
                </svg>
            </a>
            <?php
            }
            if (!empty($pub_data['editor_twitter'])) {
            ?>
            <a href="<?php echo $pub_data['editor_twitter']; ?>" target="_blank">
                <svg class="icon icon-twitter">
                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-twitter"></use>
                </svg>
            </a>
            <?php
            }
            ?>
        </p>
        <?php } ?>
    </div>
    <div class="author-bio-text">
        <?php echo wpautop(wp_trim_words($pub_data['editor_bio'], 55, '...')); ?>
        <a class="button" href="<?php echo $pub_data['editor_url']; ?>">View More By <?php echo $pub_data['editor_name']; ?></a>
    </div>
</section>
<?php
}