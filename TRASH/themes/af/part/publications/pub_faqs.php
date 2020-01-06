<?php global $af_templates; ?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="publication-faqs">
                <div class="row">
                    <div class="small-12 columns">
                        <h2 class="section-header h4">Help - <?php the_title(); ?></h2>
                        <?php
                        // Create quick list of jump links for FAQs
                        $faq_links = '';

                        foreach ($grouped_faqs as $group) {
                            $group_title = $group['group_title'];
                            $group_faqs = $group['group_faqs'];
                            $group_id = sanitize_title($group_title);

                            if ($group_title && $group_faqs) {
                                $faq_links .= '<li><a href="#' . $group_id . '">' . $group_title . '</a></li>';
                            }
                        }

                        // Display list if exists
                        if ($faq_links != '') {
                        ?>
                        <p class="faq__nav--header">Click a link below to jump to that FAQ section:</p>
                        <ul class="faq__nav clearfix">
                            <?php echo $faq_links; ?>
                        </ul>
                        <?php
                        }

                        // Create faq blocks
                        $i == 0;

                        // Create faq blocks with anchor link
                        foreach ($grouped_faqs as $group) {
                            $group_title = $group['group_title'];
                            $group_faqs = $group['group_faqs'];
                            $group_id = sanitize_title($group_title);
                            $i++;

                            if ($group_title || $group_faqs) {
                                if ($i > 1) {
                        ?>
                        <hr class="custom-separator">
                        <?php
                                }
                                if ($group_title) {
                        ?>
                        <div class="faq__anchor" id="<?php echo $group_id; ?>"></div>
                        <h2 class="h3"><?php echo $group_title; ?></h2>
                        <?php
                                }
                                if ($group_faqs) {
                                    foreach ($group_faqs as $faq) {
                                        // Pass variable into part
                                        set_query_var('faq', $faq);
                                        $af_templates->af_faq_section();
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </article>
        </div>
    </div>
</main>