<?php
global $af_theme, $af_posts, $af_templates;
$category_description = category_description();
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="general-archive">
                <?php
                if (is_category() && $category_description != '') {
                    echo $category_description . '<hr class="custom-separator">';
                }
                ?>
                <section class="excerpt-list">
                    <?php
                    $paged = $wp_query->query_vars['paged'];
                    $i = (!empty($paged) && $paged > 0) ? $wp_query->query_vars['posts_per_page'] * ($paged - 1) : 0;

                    while (have_posts()) {
                        the_post();
                    ?>
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
                            <?php
                            // Set variables to pass into part
                            set_query_var('i', $i);
                            $af_templates->af_openx_archive();
                            ?>
                        </div>
                    </div>
                    <?php
                        } else {
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            Currently there are no posts.
                        </div>
                    </div>
                    <?php
                        }
                        if ($wp_query->max_num_pages > 1) {
                    ?>
                    <div class="row">
                        <div class="small-12 centered columns">
                            <div class="more-articles">
                                <?php echo wp_pagenavi(); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    }
                    ?>
                </section>
            </article>
        </div>
    </div>
</main>