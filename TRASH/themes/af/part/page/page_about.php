<?php
global $af_auth, $af_templates;

// Get all pubs and loop through promotional content
$publications = new WP_Query(
    array(
        'post_type'      => 'publications',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'meta_key'       => 'hide_from_public',
        'meta_value'     => 0
    )
);

if ($publications->have_posts()) {
?>
<section class="content-wrapper pubinfo-section">
    <div class="row">
        <div class="small-12 columns">
            <div class="row">
                <div class="small-12 large-12 large-centered columns">
                    <section class="pub-excerpt-list">
                        <h2 class="centered h3">Choose a publication below to learn more:</h2>
                        <hr class="custom-separator">
                        <div class="row small-up-1 medium-up-2 large-up-3">
                            <?php foreach ($publications->posts as $pub) { ?>
                            <div class="column column-block">
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
        </div>
    </div>
</section>
<?php
}