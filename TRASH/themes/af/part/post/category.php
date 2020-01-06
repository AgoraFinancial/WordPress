<?php
global $af_theme, $af_posts, $af_templates, $af_users;

// Set up carry-over index for paging
$paged_var = $wp_query->query_vars['paged'];
$i = (isset($paged_var) && $paged_var > 0) ? $wp_query->query_vars['posts_per_page'] * ($paged_var - 1): 0;

// If archive has posts
if (have_posts()) {

    $queried_object = get_queried_object();
    $template_directory_uri = PARENT_PATH_URI;

    // Get date of latest post to set up archive link
    $date = $posts[0]->post_date;
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $date)->format('mY');

    // Get url for archive
    if (isset($type)) {
        $query_args['type'] = $type;
    }
    $query_args['archive'] = $date;
    $archive_url = esc_url(add_query_arg($query_args, get_term_link($queried_object->cat_ID)));

    while (have_posts()) {
        the_post();

        $category_description = category_description();
        $is_paged = is_paged();

        // Start excerpt list html hero or at top of paged
        if ($i == 4 || $is_paged && $i % 10 == 0) {
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="post-archive">
                <?php
                // Display category description only on paged
                if ($is_paged) {
                    if (is_category() && $category_description != '') {
                        echo $category_description;
                ?>
                <p class="archive-link">
                    <a href="<?php echo $archive_url; ?>">
                        <svg class="icon icon-calendar">
                            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-calendar"></use>
                        </svg>
                        View Archives
                    </a>
                </p>
                <hr class="custom-separator">
                <?php
                    }
                }
                ?>
                <section class="excerpt-list">
                    <?php
                    // OpenX - Only for home page before list
                    if ($i == 4) {
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
        // Start hero articles on first page
        if ($i < 4) {
            // Open hero section html and display first article w/OpenX sidebar
            if ($i == 0) {
?>
                <section class="content-wrapper hero-section">
                    <?php
                    // Display category description on first page
                    if (is_category() && $category_description != '' && $i == 0) {
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <?php echo $category_description; ?>
                            <p class="archive-link">
                                <a href="<?php echo $archive_url; ?>">
                                    <svg class="icon icon-calendar">
                                        <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-calendar"></use>
                                    </svg>
                                    View Archives
                                </a>
                            </p>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                    <div class="row">
                        <div class="small-12 medium-7 large-8 columns">
                            <?php $af_posts->af_get_post_excerpt($post, 'article_hero', ''); ?>
                        </div>
                        <div class="small-12 medium-5 large-4 columns">
                            <?php
                            $af_templates->af_social();
                            $af_templates->af_openx_home_sidebar();
                            ?>
                        </div>
                    </div>
                    <div class="row sub-articles">
<?php
            } else { // Display remaining hero sub-articles
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
            }
        } else { // End hero articles and start list articles
?>
                <div class="row">
                    <div class="small-12 columns">
                        <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                    </div>
                </div>
<?php
        } // End list articles
?>


                <?php $i++; ?>


                <?php // OpenX
                if ($i % 5 == 0 && $i != 5) { ?>

                    <div class="row">
                        <div class="small-12 columns">
                            <article class="article-card openx-card">

                                <?php // Set variables to pass into part
                                    set_query_var('i', $i);
                                    $af_templates->af_openx_archive();
                                ?>

                            </article>
                        </div>
                    </div>

                <?php } // End OpenX ?>

            <?php } // end loop ?>

            <?php // Display pagination
                if ($wp_query->max_num_pages > 1) { ?>

                <div class="row">
                    <div class="small-12 centered columns">
                        <div class="more-articles">
                            <?php echo wp_pagenavi(); ?>
                            <p class="archive-link archive-link--footer">
                                <a href="<?php echo $archive_url; ?>">
                                    <svg class="icon icon-calendar">
                                        <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-calendar"></use>
                                    </svg>
                                    View Archives
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php } // End excerpt list html ?>
                </section>
            </article>
        </div>
    </div>
</main>
<?php } else { ?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            Currently there are no posts.
        </div>
    </div>
</main>
<?php } ?>
