<?php
global $af_users, $af_pages, $af_posts;

$template_directory_uri = PARENT_PATH_URI;
$saved_posts = $af_pages->af_get_user_articles();
$uid = $af_users->af_get_user_data('id');
$token = $af_users->af_get_user_data('af_user_token');
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 medium-10 medium-centered columns">
            <article class="saved-archive">
                <?php echo apply_filters('the_content', $post->post_content); ?>
                <section class="excerpt-list">
                    <?php
                    if (count($saved_posts)) {
                        foreach ($saved_posts as $post_id) {
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <?php $af_posts->af_get_post_excerpt(get_post($post_id), 'article_thumb', ''); ?>
                            <span class="button alert button--remove" data-uid="<?php echo $uid; ?>" data-aid="<?php echo $post_id; ?>" data-token="<?php echo $token; ?>">
                                <svg class="icon icon-close">
                                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-close"></use>
                                </svg>
                                <span>Remove From Saved Articles</span>
                            </span>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                    ?>
                    <p>You currently have <strong>no saved articles</strong>.</p>
                    <p>To save an article to read later, click the "Save For Later" button located at the top of an article, as seen below:</p>
                    <p>
                        <em>Look for this button at the top of an article:</em>&nbsp;&nbsp;
                        <span class="button">
                            <svg class="icon icon-bookmark">
                                <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-bookmark"></use>
                            </svg>
                            <span>Save For Later</span>
                        </span>
                    </p>
                    <?php
                    }
                    ?>
                </section>
            </article>
        </div>
    </div>
</main>