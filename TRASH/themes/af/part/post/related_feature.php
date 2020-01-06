<?php
global $af_posts, $af_templates;

// Get a random related post from with last year
$cats = wp_get_post_categories($post->ID);
if ($cats) {
    $today = getdate();
    $args = array(
        'category__in'   => $cats,
        'post__not_in'   => array($post->ID),
        'posts_per_page' => 1,
        'orderby'        => 'rand',
        'date_query' => array(
            array(
                'after' => $today[ 'month' ] . ' 1st, ' . ($today[ 'year' ] - 1)
            )
        )
    );
    $related_posts = new WP_Query($args);
    if (is_array($related_posts->posts) && count($related_posts->posts)) {
        $related_post = $related_posts->posts[0];
    }
    if (!empty($related_post)) {
?>
<section class="related-article">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <h2 class="section-header h4">You May Also Be Interested In:</h2>
            <div class="row">
                <div class="small-12 medium-6 columns">
                    <?php $af_posts->af_get_post_excerpt($related_post, 'article_hero', ''); ?>
                </div>
                <div class="small-12 medium-6 columns">
                    <div class="openx-section">
                        <?php $af_templates->af_openx_related(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
    }
    wp_reset_query();
}