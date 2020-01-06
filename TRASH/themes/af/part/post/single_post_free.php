<?php
global $af_auth, $af_posts, $af_templates;

$template_directory_uri = PARENT_PATH_URI;
$post_data = $af_posts->af_single_post_data($post);

// Pass variables to parts
set_query_var('post_data', $post_data);
?>
<main class="content-wrapper free-article">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="article-single <?php echo $post_data['article_classes']; ?>">
                <?php
                if ($post_data['article_featured_image']) {
                    echo '<div class="post-featured-image">' . $post_data['article_featured_image'] . '</div>';
                }
                ?>
                <div class="row">
                    <div class="small-12 medium-2 columns aside">
                        <?php $af_templates->af_post_meta(); ?>
                        <div class="post-actions--free clearfix">
                            <span class="button button--circle button--print">
                                <svg class="icon icon-print">
                                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-print"></use>
                                </svg>
                            </span>
                            <span class="button button--circle button--resize">
                                <svg class="icon icon-resize">
                                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-resize"></use>
                                </svg>
                            </span>
                            <span class="button button--circle button--facebook" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url(get_permalink()); ?>', 'sharer', 'top=' + ((screen.height / 2) - 260) + ',left=' + ((screen.width / 2) - 175) + ',toolbar=0,status=0,width=520,height=350')" href="javascript:void(0);" data-profile-event-name="share">
                                <svg class="icon icon-facebook">
                                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-facebook"></use>
                                </svg>
                            </span>
                            <span class="button button--circle button--twitter" onclick="window.open('https://twitter.com/home?status=<?php echo esc_url(get_permalink()); ?>', 'sharer', 'top=' + ((screen.height / 2) - 260) + ',left=' + ((screen.width / 2) - 175) + ',toolbar=0,status=0,width=520,height=350');" href="javascript:void(0);" data-profile-event-name="share">
                                <svg class="icon icon-twitter">
                                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-twitter"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="small-12 medium-10 columns article">
                        <h1><?php the_title(); ?></h1>
                        <?php $af_posts->af_post_content(); ?>
                    </div>
                </div>
            </article>
        </div>
    </div>
    <?php
    $af_templates->af_related_feature();
    $af_templates->af_openx_bottom_free();
    $af_templates->af_author_bio();
    $af_templates->af_disqus_thread();
    $af_templates->af_related_posts();
    $af_templates->af_openx_exit_popup();
    ?>
</main>