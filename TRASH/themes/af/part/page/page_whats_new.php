<?php
global $af_auth, $af_theme, $af_posts, $af_publications, $af_templates;

/*
 * This page gets all of the pubs the subscriber has access to and displays them in a filterable list
 * by first getting all mw accessible pubs then comparing that list to the pubs offered by this wp site.
 * Currently it ignores free articles by adding negative category ids and adding them to the category
 * array using this method: $af_theme->af_get_free_article_cats();
 *
 * 12/14/18 Update: - To reduce query load, grabbing all posts from last 30 days and filtering the display
 * by what the user has access to
 */

// Create array of valid user pub id
$valid_pub_ids = $af_publications->af_get_user_pub_ids();

// Get array of all pub ids
// $pubs = $af_publications->af_get_user_pubcodes();

// Get array of all valid cat ids
$cats = $af_posts->af_get_active_cats();

// Get query vars
$pub = isset($_GET['pub']) ? $_GET['pub'] : '';
$cat_name = isset($_GET['cat']) ? $_GET['cat'] : '';
$cat = $cat_name ? get_category_by_slug($cat_name)->term_id : $cats;

// Exclude free articles
if (!$cat_name && is_array($cat)) {
    $free_cats = $af_theme->af_get_free_article_cats();
    foreach ($free_cats as $free_cat) {
        $cat[] = '-'.get_category_by_slug($free_cat)->term_id;
    }
}

// Get meta query value
if ($pub) {
    $pub_id = $af_publications->af_get_pub_ID($pub);
    $meta_query = array(array(
        'key'       => 'publication',
        'value'     => '"' . $pub_id . '"',
        'compare'   => 'LIKE'
    ));
} else {
    $meta_query = '';
}

// Query arguments
$posts = new WP_Query(array(
    'cat'                 => $cat,
    'meta_query'          => $meta_query,
    'ignore_sticky_posts' => 1,
    'date_query' => array(
        array(
            'after' => '-30 days',
            'column' => 'post_date',
        ),
    ),
    'posts_per_page' => 50

));
?>
<main class="content-wrapper whats-new-page">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="whats-new-archive">
                <?php
                if ($post->post_content != '' && $paged == 1) {
                    echo apply_filters('the_content', $post->post_content);
                }
                ?>
                <p>Check out recent posts you may missed. <strong>Please select a subscription or category below to filter your content.</strong></p>
                <form class="whats-new-form">
                    <?php // Create list of publications ?>
                    <select class="whats-new-selector">
                        <?php
                        // Get default option
                        $default_link = get_permalink();
                        if ($cat) {
                            $default_link = add_query_arg('cat', $cat_name, $default_link);
                        }
                        ?>
                        <option value="<?php echo $default_link; ?>">View All Subscriptions</option>
                        <?php
                        // Set all publication options
                        foreach ($valid_pub_ids as $pub_id) {
                            $pubcode = strtolower(get_field('pubcode', $pub_id));

                            // Add applicable query args
                            $pub_link = get_permalink();
                            $pub_link = add_query_arg('pub', $pubcode, $pub_link);
                            if ($cat_name) {
                                $pub_link = add_query_arg('cat', $cat_name, $pub_link);
                            }
                        ?>
                        <option value="<?php echo esc_url($pub_link); ?>"<?php if ($pub == $pubcode) echo ' selected'; ?>>
                            <?php echo get_the_title($pub_id); ?>
                        </option>
                        <?php
                        }
                        ?>
                    </select>
                    <?php // Get list of categories ?>
                    <select class="whats-new-selector">
                        <?php
                        // Get default option
                        $default_link = get_permalink();
                        if ($pub) {
                            $default_link = add_query_arg('pub', $pub, $default_link);
                        }
                        ?>
                        <option value="<?php echo $default_link; ?>">View All Categories</option>
                        <?php
                        // Set all category options
                        foreach ($cats as $cat_id) {
                            $category = get_category($cat_id);
                            $cat_slug = $category->slug;

                            // Add applicable query args
                            $cat_link = get_permalink();
                            if ($pub) {
                                $cat_link = add_query_arg('pub', $pub, $cat_link);
                            }
                            $cat_link = add_query_arg('cat', $cat_slug, $cat_link);
                        ?>
                        <option value="<?php echo esc_url($cat_link); ?>"<?php if ($cat_name == $cat_slug) echo ' selected'; ?>>
                            <?php echo get_cat_name($cat_id); ?>
                        </option>
                        <?php
                        }
                        ?>
                    </select>
                </form>
                <hr class="custom-separator">
                <section class="excerpt-list">
                    <?php
                    $i = (isset($posts->query_vars['page']) && $posts->query_vars['page'] > 0) ? $posts->query_vars['posts_per_page'] * ($posts->query_vars['page'] - 1) : 0;
                    if ($posts->have_posts()) {
                        while ($posts->have_posts()) {
                            $posts->the_post();

                            // Get associated pubs and compare to user pubs - continue if no match
                            $pub_id = get_post_meta($post->ID, 'publication', true);
                            $result = is_array($valid_pub_ids) && is_array($pub_id) ? array_intersect($pub_id, $valid_pub_ids) : '' ;
                            if(empty($result)) continue; ?>

                    <div class="row">
                        <div class="small-12 columns">
                            <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                        </div>
                    </div>
                    <?php
                            // OpenX
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
                    } else {
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            Currently there are no posts within the last 30 days that match your criteria.
                        </div>
                    </div>
                    <?php
                    }
                    wp_reset_postdata(); ?>
                </section>
            </article>
        </div>
    </div>
</main>