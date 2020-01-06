<?php
global $af_posts;

// Re-request post data to ensure not overwritten by featured post
$post_data = $af_posts->af_single_post_data($post);
if (is_array($post_data) && count($post_data) && !empty($post_data['article_author_image']) && !empty($post_data['article_author']) && !empty($post_data['article_author_bio'])) {
    $template_directory_uri = PARENT_PATH_URI;
?>
<div class="row">
    <div class="small-12 large-10 large-centered columns">
        <section class="callout author-bio">
            <div class="author-bio-image">
                <?php echo $post_data['article_author_image']; ?>
            </div>
            <div class="author-bio-byline">
                <p><?php echo $post_data['article_author']; ?></p>
                <?php if (!empty($post_data['article_facebook']) || !empty($post_data['article_twitter'])) { ?>
                <p class="author-bio-social">
                    <?php
                    if (!empty($post_data['article_facebook'])) {
                    ?>
                    <a href="<?php echo $post_data['article_facebook']; ?>" target="_blank">
                        <svg class="icon icon-facebook">
                            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-facebook"></use>
                        </svg>
                    </a>
                    <?php
                    }
                    if (!empty($post_data['article_twitter'])) {
                    ?>
                    <a href="<?php echo $post_data['article_twitter']; ?>" target="_blank">
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
                <?php echo $post_data['article_author_bio']; ?>
                <a class="button" href="<?php echo $post_data['article_author_url']; ?>">View More By <?php echo $post_data['article_author']; ?></a>
            </div>
        </section>
    </div>
</div>
<?php
}