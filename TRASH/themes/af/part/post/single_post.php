<?php
global $af_auth, $af_posts, $af_templates;

$post_data = $af_posts->af_single_post_data($post);
$is_lifetime = $af_auth->is_post_lifetime($post_data['article_id']);
$is_plus = $af_auth->is_post_plus($post_data['article_id']);
$has_access = $af_auth->has_pub_access($post_data['article_pubcodes'], $is_lifetime, $is_plus);

// Pass variables to parts
set_query_var('post_data', $post_data);
?>
<main class="content-wrapper paid-article">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="article-single <?php echo $post_data['article_classes']; ?>">
                <h1><?php the_title(); ?></h1>
                <?php
                $af_templates->af_post_meta();
                $af_posts->af_post_content();
                if ($has_access) {
                    $af_templates->af_stock_recommendations();
                }
                ?>
            </article>
        </div>
    </div>
    <?php
    if ($has_access && $af_posts->af_is_frontend_post()) {
        $af_templates->af_openx_bottom_frontend();
    }
    $af_templates->af_author_bio();
    $af_templates->af_related_posts();
    ?>
</main>