<?php global $af_templates; ?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="faq-category-archive">
                <?php
                if (have_posts()) {
                ?>
                <h2 class="section-header h4">Help Category: <?php echo single_term_title(); ?></h2>
                <?php
                    while (have_posts()) {
                        the_post();
                        $af_templates->af_faq_section();
                    }
                }
                if ($posts->max_num_pages > 1) {
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
            </article>
        </div>
    </div>
</main>