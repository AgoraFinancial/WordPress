<?php
global $af_theme, $af_posts, $af_publications, $af_templates;

// Clear category if whats-new
if ($type == 'whats-new') {
    $type = '';
    $cat_name = 'Articles from <em>'.get_the_title($post->ID).'</em>';
} else { // Check if category matches type and assign label
    $category_data = $af_publications->af_get_pub_cat_data($post->ID);
    if (is_array($category_data) && count($category_data)) {
        foreach ($category_data as $category) {

            // Skip non-matches
            if ($type != ($category['category']->slug || $category['category_slug'])) {
                continue;
            }

            // Assign label
            $cat_name = $category['category_label'] != '' ? $category['category_label'] : $category['category']->name;

            // Stop loop on slug match
            if ($type == $category['category']->slug) {
                break;
            }

            // Stop loop on custom slug match and reset type for query
            if ($type == $category['category_slug']) {
                $type = $category['category']->slug;
                break;
            }
        }
    } else {
        $cat_name = '';
    }
}

// Setup static page pagination
$paged = get_query_var('page');
$paged = ($paged) ? $paged : 1;

// Get term id
$type_id = get_category_by_slug($type);
$type_id = isset($type_id->term_id) ? $type_id->term_id : '';

// Get exluded cats
$exclude = $af_theme->af_get_excluded_paid_cat_ids();

// Query arguments
$args = array(
    'category_name'       => $type,
    'paged'               => $paged,
    'ignore_sticky_posts' => 1,
    'cat'                 => $exclude,
    'meta_query'          => array(
        array (
            'key'         => 'publication',
            'value'       => '"'.$post->ID.'"',
            'compare'     => 'LIKE'
        )
    )
);

$posts = new WP_Query($args);

// Get archive url
if ($posts->have_posts()) {
    // Get date of latest post to set up archive link
    $date = $posts->posts[0]->post_date;
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $date)->format('mY');

    // Get url for archive
    $archive_url = add_query_arg( array(
        'type' => $type,
        'archive' => $date
    ), get_permalink());
}
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="publication-archive">
                <?php
                echo apply_filters('the_content', category_description($type_id));
                if ($type && $archive_url) {
                ?>
                <p class="archive-link">
                    <a href="<?php echo esc_url( $archive_url ); ?>">
                        <svg class="icon icon-calendar">
                            <use xlink:href="<?php echo PARENT_PATH_URI; ?>/img/symbol-defs.svg#icon-calendar"></use>
                        </svg>
                        View Archives
                    </a>
                </p>
                <?php
                }
                ?>
                <h2 class="section-header h4">Latest <?php echo !empty($cat_name) ? $cat_name : ''; ?></h2>
                <section class="excerpt-list">
                    <?php
                    if ($posts->have_posts()) {
                        while ($posts->have_posts()) {
                            $posts->the_post();
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                    ?>
                    <p>There are currently no posts in this archive.</p>
                    <?php
                    }
                    wp_reset_postdata();

                    if ($posts->max_num_pages > 1 && $type) {
                    ?>
                    <div class="row">
                        <div class="small-12 centered columns">
                            <div class="more-articles">
                                <?php echo wp_pagenavi(array('echo' => false, 'query' => $posts)); ?>
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