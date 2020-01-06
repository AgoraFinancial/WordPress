<?php global $af_theme, $af_posts, $af_publications, $af_templates; ?>

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
                            <h2 class="h4 text-center">
                                <?php
                                if ($results == 1) {
                                    echo 'There is ' . $results . ' result ';
                                } else {
                                    if ($results != 1) {
                                        echo 'There are ' . $results . ' results ';
                                    }
                                }
                                ?>
                                found for <em>'<?php echo get_search_query(); ?>'</em>...
                            </h2>
                        </div>
                    </div>
                    <?php
                        while (have_posts()) {
                            the_post();
                            $permalink = get_permalink();
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <article class="search-result-card">
                                <a href="<?php echo $permalink; ?>">
                                    <h1 class="h4"><?php the_title(); ?></h1>
                                </a>
                                <p>
                                    <span class="search-result-url"><?php echo $permalink; ?></span>
                                    <?php echo get_the_excerpt(); ?>
                                    <a href="<?php echo $permalink; ?>">View Result</a>
                                </p>
                            </article>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <h4 class="text-center">No results found for <em>'<?php echo get_search_query(); ?>'</em>... Please try a different search.</h4>
                            <?php $af_templates->af_search_bar(); ?>
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
                    ?>
                </section>
            </article>
        </div>
    </div>
</main>