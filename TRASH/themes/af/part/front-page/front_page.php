<?php
global $af_theme, $af_posts, $af_publications, $af_templates;

$i = 0;
$meta_query = '';

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
                    'key' => 'publication',
                    'value' => '"'.$pub.'"',
                    'compare' => 'LIKE'
                );
            }
        }

        // Add back in free posts
        $meta_query[] = array(
            'key' => 'publication',
            'compare' => 'NOT EXISTS'
        );
        $meta_query[] = array(
            'key' => 'publication',
            'value' => false,
            'compare' => 'BOOLEAN'
        );
    }
}

// Get exluded cats
$exclude = $af_theme->af_get_excluded_cat_ids();
$home_posts = new WP_Query(array(
    'posts_per_page'    => 9,
    'cat'               => $exclude,
    'meta_query'        => $meta_query,
));

if ($home_posts->have_posts()) {
    while ($home_posts->have_posts()) {
        $home_posts->the_post();
        if ($i < 4) {
            // Open hero section html and display first article w/OpenX sidebar
            if ($i == 0) {
?>
<section class="content-wrapper hero-section">
    <div class="row">
        <div class="small-12 medium-7 large-8 columns">
            <?php $af_posts->af_get_post_excerpt($post, 'article_hero', ''); ?>
        </div>
        <div class="small-12 medium-5 large-4 columns">
            <?php $af_templates->af_social(); ?>
            <?php $af_templates->af_openx_home_sidebar(); ?>
        </div>
    </div>
    <div class="row sub-articles">
<?php
            } else {
            // Display remaining hero sub-articles
?>
        <div class="small-12 medium-6 medium-centered large-4 columns">
            <?php $af_posts->af_get_post_excerpt($post, 'article_hero', ''); ?>
        </div>
<?php
            }
            // Close sub-articles and hero section
            if ($i == 3) {
?>
    </div>
</section>
<?php
                $af_templates->af_openx_home_middle();
                $af_templates->af_home_pub_content();
            }
        } else {

            // Open excerpt list html
            if ($i == 4) {
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <article class="post-archive">
                <section class="excerpt-list">
                    <h2 class="section-header h4">Recently Added Articles</h2>
<?php
            }
?>
                    <div class="row">
                        <div class="small-12 columns">
                            <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                        </div>
                    </div>
<?php
        } // End list articles
        $i++;

        // OpenX
        if ($i % 5 == 0 && $i != 5) {
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
        } // End OpenX
    } // end loop
    // End excerpt list html
?>
                </section>
            </article>
        </div>
    </div>
</main>
<?php
} else {
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            Currently there are no posts.
        </div>
    </div>
</main>
<?php
}