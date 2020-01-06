<?php
global $af_posts, $af_templates;
$results = $wp_query->found_posts;
?>

<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="publication-archive">
                <section class="excerpt-list">
                    <?php
                    if (have_posts()) {
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <h2 class="section-header h4">Showing results for: <em>'<?php echo get_search_query(); ?>'</em>...</h2>
                            <?php
                            while (have_posts()) {
                                the_post();
                                $af_templates->af_faq_section();
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    } else {
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <h4 class="text-center">
                                No results found for <em>'<?php echo get_search_query(); ?>'</em>... Please try a different search.
                            </h4>
                            <?php $af_templates->af_search_bar_help(); ?>
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
                    $af_templates->af_faq_category_list();
                    ?>
                </section>
            </article>
        </div>
    </div>
</main>