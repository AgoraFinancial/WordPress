<?php
global $af_auth, $af_templates;

// Get front page content
$front_page = get_post(get_option('page_on_front'));
$content = $front_page->post_content;

// Get all pubs and loop through promotional content
$publications = new WP_Query(array(
    'post_type'      => 'publications',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'meta_key'       => 'hide_from_public',
    'meta_value'     => 0
));
if (!empty($content) || $publications->have_posts()) {
?>
<main class="content-wrapper pubinfo-section">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <?php
            if (!empty($content)) {
            ?>
            <div class="row">
                <div class="small-12 columns">
                    <article>
                        <?php echo apply_filters('the_content', $content); ?>
                    </article>
                </div>
            </div>
            <?php
            }
            if ($publications->have_posts()) {
            ?>
            <div class="row">
                <div class="small-12 columns">
                    <section class="pub-excerpt-list">
                        <div class="row small-up-1 medium-up-2">
                            <?php foreach ($publications->posts as $pub) { ?>
                            <div class="column column-block small-centered">
                                <?php
                                // Set variables to pass into part
                                set_query_var('pub_id', $pub->ID);
                                $af_templates->af_pub_excerpt();
                                ?>
                            </div>
                            <?php } ?>
                        </div>
                    </section>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
</main>
<?php
}