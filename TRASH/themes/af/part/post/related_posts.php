<?php
global $af_posts;

// Get 6 recent posts related by category
$cats = wp_get_post_categories($post->ID);

// If related articles are paid, limit query to same publications
$pub_query = '';

if ($post_data['article_publications']) {
    $pubs_array = $post_data['article_publications'];
    $pub_query = array(
        array (
            'key'     => 'publication',
            'value'   => '"' . $pubs_array[0]->ID . '"',
            'compare' => 'LIKE'
        )
    );
}

if ($cats) {
    $args = array(
        'category__in'   => $cats,
        'post__not_in'   => array($post->ID),
        'posts_per_page' => 6,
        'orderby'        => 'rand',
        'meta_query'     => $pub_query
    );

    $related_posts = new WP_Query($args);

    if ($related_posts->have_posts()) {
?>
<div class="row">
    <div class="small-12 large-10 large-centered columns">
        <section class="excerpt-list related-posts">
        <h2 class="section-header h4">Related Posts</h2>
            <?php
            while ($related_posts->have_posts()) {
                $related_posts->the_post();
            ?>
            <div class="row">
                <div class="small-12 columns">
                    <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                </div>
            </div>
            <?php
            }
            ?>
        </section>
    </div>
</div>
<?php
    }
    wp_reset_query();
}