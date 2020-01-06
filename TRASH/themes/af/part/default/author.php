<?php
global $af_theme, $af_posts, $af_publications, $af_templates;

$meta_query = '';

// Get author bio
$author = get_user_by('slug', get_query_var('author_name'));
$author_meta = get_user_meta($author->ID);
$description = wpautop($author_meta['description'][0]);

// If free articles only, don't worry about pub visibility
if (defined('FREE_ARTICLES_ONLY') && FREE_ARTICLES_ONLY !== true) {

    // Check if site has any hidden publications
    $has_hidden_pubs = $af_publications->af_has_hidden_pubs();

    // If has hidden, alter meta query
    if ($has_hidden_pubs) {

        // Get active pub ids.
        $visible_pubs = $af_publications->af_get_active_pub_ids();

        // Set up comparision for 'OR'
        $meta_query = array('relation'=>'OR');

        // Loop through and add visible pubs to meta query
        if (!empty($visible_pubs)) {
            foreach ($visible_pubs as $pub) {
                $meta_query[] = array(
                    'key'     => 'publication',
                    'value'   => '"' . $pub . '"',
                    'compare' => 'LIKE'
                );
            }
        }

        // Add back in free posts
        $meta_query[] = array(
            'key'     => 'publication',
            'compare' => 'NOT EXISTS'
        );
        $meta_query[] = array(
            'key'     => 'publication',
            'value'   => false,
            'compare' => 'BOOLEAN'
        );
    }
}

// Manually get author query with removed meta
$paged = get_query_var('paged');
$paged = ($paged) ? $paged : 1;

$args = array(
    'author'     => get_query_var('author'),
    'cat'        => $af_theme->af_get_excluded_cat_ids(),
    'paged'      => $paged,
    'meta_query' => $meta_query,
);

$author_query = new WP_Query($args);
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="author-archive">
                <?php
                echo $description;
                if ($author_query->have_posts()) {
                ?>
                <hr class="custom-separator">
                <?php
                }
                ?>
            </article>
        </div>
    </div>
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="author-archive">
                <section class="excerpt-list">
                    <?php
                    $paged_var = $author_query->query_vars['paged'];
                    $i = (isset($paged_var) && $paged_var > 0) ? $author_query->query_vars['posts_per_page'] * ($paged_var -1) : 0;

                    while ($author_query->have_posts()) {
                        $author_query->the_post();
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                        </div>
                    </div>
                    <?php // OpenX
                        $i++;
                        if ($i % 5 == 0) {
                    ?>
                    <div class="row incontent-az">
                        <div class="small-12 columns">
                            <article class="article-card openx-card">
                                <?php
                                // Set variables to pass into part
                                set_query_var('i', $i);
                                $af_templates->af_openx_archive();
                                ?>
                            </article>
                        </div>
                    </div>
                    <?php
                        }
                    }
                    if ($author_query->max_num_pages > 1) {
                    ?>
                    <div class="row">
                        <div class="small-12 centered columns">
                            <div class="more-articles">
                                <?php echo wp_pagenavi(array('query' => $author_query)); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                </section>
            </article>
        </div>
    </div>
</main>