<?php
global $af_auth, $af_theme, $af_posts, $af_faqs, $af_templates;

$fields = $af_theme->af_get_acf_fields($post->ID);
$sidebar_content = $fields['sidebar_content'];
?>
<main class="content-wrapper layout-sidebar-right">
    <div class="row">
        <div class="small-12 medium-8 columns">
            <article>
                <?php
                echo apply_filters('the_content', $post->post_content);

                // Get most popular FAQs
                $ar_popular_posts = $af_posts->af_get_popular_posts();
                $html = '';
                if (is_array($ar_popular_posts) && count($ar_popular_posts)) {
                    foreach ($ar_popular_posts as $post_id) {
                        $post_obj = get_post($post_id);
                        $faq_data = $af_faqs->af_single_faq_data($post_obj);
                        $pubcodes = $faq_data['faq_pubcodes'];
                        $has_access = false;

                        // If no publication assigned, set flag
                        if (empty($pubcodes)) {
                            $has_access = true;
                            // If pub assigned, check if has access
                        } elseif ($af_auth->has_pub_access($pubcodes)) {
                            $has_access = true;
                        }
                        if ($has_access) {
                            $html .= '<li><a href="' . get_post_permalink($post_id) . '">' . get_the_title($post_id) . '</a></li>';
                        }
                    }
                    if ($html != '') {
                ?>
                <h2>Popular Questions</h2>
                <ul class="no-bullet faq-popular-list">
                    <?php echo $html; ?>
                </ul>
                <?php
                    }
                }
                // Get FAQ categories
                $af_templates->af_faq_category_list();
                ?>
            </article>
        </div>
        <div class="small-12 medium-4 columns">
            <aside>
                <?php echo $sidebar_content; ?>
            </aside>
        </div>
    </div>
</main>