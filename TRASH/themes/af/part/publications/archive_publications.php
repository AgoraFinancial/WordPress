<?php global $af_templates; ?>
<main class="content-wrapper pubinfo-section">
    <div class="row">
        <div class="small-12 columns">
            <div class="row">
                <div class="small-12 columns">
                    <?php if (have_posts()) { ?>
                    <section class="pub-excerpt-list">
                        <div class="row small-up-1 medium-up-2 large-up-3">
                            <?php
                            while (have_posts()) {
                                the_post();

                                // Hide from public
                                if (get_field('hide_from_public')) {
                                    continue;
                                }
                            ?>
                            <div class="column column-block">
                                <?php
                                // Set variables to pass into part
                                set_query_var('pub_id', $post->ID);
                                $af_templates->af_pub_excerpt_subscription();
                                ?>
                            </div>
                            <?php
                            }
                            ?>
                        </div>
                    </section>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</main>