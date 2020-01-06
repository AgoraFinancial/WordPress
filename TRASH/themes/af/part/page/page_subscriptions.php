<?php
global $af_auth, $af_theme, $af_publications, $af_templates;

$publications = new WP_Query(
    array(
        'post_type'         => 'publications',
        'posts_per_page'    => -1,
        'orderby'           => 'menu_order',
        'order'             => 'ASC',
        'meta_key'          => 'hide_from_public',
        'meta_value'        => 0
    )
);

// Set up subs/non_subs arrays
$subs = array();
$non_subs = array();

foreach ($publications->posts as $pub) {
    $pubcode = get_field('pubcode', $pub->ID);

    // Assign as subscribed
    if ($af_auth->has_pub_access($pubcode)) {
        $subs[] = $pub;
    } else { // Assign as non-subscribed
        $non_subs[] = $pub;
    }
}

// Get acf fields data
$fields = $af_theme->af_get_acf_fields($post->ID);
$additional_subscriptions = $fields['additional_subscriptions'];
?>
<main class="content-wrapper pubinfo-section">
    <div class="row">
        <div class="small-12 columns">
            <div class="row">
                <div class="small-12 columns">
                    <section class="pub-subscription-list">
                        <?php
                        // Show my subscriptions
                        if (!empty($subs)) {
                            ?>
                            <h2 class="h4 section-header"><?php echo apply_filters('af_subscriptions_heading', 'My Subscriptions'); ?></h2>
                            <div class="row align-center small-up-1 medium-up-2 large-up-3">
                            <?php
                            // Get all active pubs
                            foreach ($subs as $pub) {
                                ?>
                                <div class="column column-block">
                                    <?php
                                    // Set variables to pass into part
                                    set_query_var('pub_id', $pub->ID);
                                    $af_templates->af_pub_excerpt_subscription();
                                    ?>
                                </div>
                                <?php
                            }
                            // Get manually added subscriptions
                            if (null !== $additional_subscriptions && false !== $additional_subscriptions) {
                                foreach ($additional_subscriptions as $sub) {
                                    $name = $sub['name'];
                                    $pubcode = $sub['pubcode'];
                                    $logo = $sub['logo'];
                                    $description = $sub['description'];
                                    $url = $sub['url'];

                                    if (!$af_auth->has_pub_access($pubcode)) {
                                        continue;
                                    }
                                    ?>
                                    <div class="column column-block">
                                        <div class="pub-card">
                                            <?php
                                            if ($logo) {
                                                ?>
                                                <h3>
                                                    <?php
                                                    $logo_html = '';
                                                    if ($url) {
                                                        $logo_html .= '<a href="' . $url . '" target="_blank" class="pub-logo-link">';
                                                    }
                                                    if ($logo) {
                                                        $logo_html .= wp_get_attachment_image($logo['id'], 'medium_large');
                                                    }
                                                    if ($url) {
                                                        $logo_html .= '</a>';
                                                    }
                                                    echo $logo_html;
                                                    ?>
                                                </h3>
                                                <?php
                                            }
                                            if ($description) {
                                                echo '<p>' . $description . '</p>';
                                            }
                                            if ($logo) {
                                                ?>
                                                <div class="pub-actions">
                                                    <a class="button button--view" href="<?php echo $url; ?>"
                                                       target="_blank">Go To <?php echo $name; ?></a>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                </div>
                                <?php
                            }
                        }
                        // Show remaining pubs
                        if (!empty($non_subs)) {
                        ?>
                        <h2 class="h4 section-header"><?php echo apply_filters('af_subscriptions_non_sub', 'Subscriptions You May Be Interested In'); ?></h2>
                        <div class="row align-center small-up-1 medium-up-2 large-up-3">
                            <?php foreach ($non_subs as $pub) { ?>
                            <div class="column column-block">
                                <?php // Set variables to pass into part
                                set_query_var('pub_id', $pub->ID);
                                $af_templates->af_pub_excerpt_subscription(); ?>
                            </div>
                            <?php } ?>
                        </div>
                        <?php
                        }
                        ?>
                    </section>
                </div>
            </div>
        </div>
    </div>
</main>