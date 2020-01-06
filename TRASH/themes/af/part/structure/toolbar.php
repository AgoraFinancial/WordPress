<?php
global $af_templates;

// Array of imprints with title and url
$imprints = array(
    'Seven Figure Publishing' => 'https://sevenfigurepublishing.com/',
    'Saint Paul Research' => 'https://stpaulresearch.com/',
    'Paradigm Press' => 'https://paradigm.press/',
);

// Quickly set AF base URL (for dev)
$af_url = 'https://agorafinancial.com';

// Arbitrary number to ensure we get all publications (up to 100)
$per_page = 30;

// Target an empty URL to get the appended signon query
$target_url = '';
$signon_url =  class_exists('agora_authentication_plugin') ? agora()->security->get_single_sign_on_url($target_url, agora()->user->get_customer_number(), 'AFR') : '' ;

// For dev, if URL contains &ref=1 it will delete the transient
$transient_id = 'afr_toolbar';

// Get account URL if page exists
$account = get_page_by_path('my-account');
if ($account) {
    $account_url = get_permalink($account->ID);
} else {
    $account_url = '#';
}

$toolbar = '';
$imprint_data = array();
$template_directory_uri = PARENT_PATH_URI;

// If imprint data is set as transient, retrieve
$imprint_data_var = get_transient($transient_id);
if ($imprint_data_var) {
    $imprint_data = $imprint_data_var;
    // Else, we build it and set it to transient for 2 hours
} else {
    // Use the WP API to get publication data for each imprint.
    foreach ($imprints as $k => $v) {
        $response = wp_remote_get($v.'wp-json/wp/v2/publications/?per_page='.$per_page.'&order=asc&orderby=menu_order');

        if ( is_array( $response ) ) {
            $publications = json_decode($response['body']);
            $pub_data = array();

            // Create list of pubs for each imprint.
            foreach ($publications as $pub) {

                // Build supporting array of each individuation pub.
                $pub_data[] = array(
                    'pub_name' => $pub->title->rendered,
                    'pub_url' => $pub->link,
                );

            }
        }

        // Build final array of imprint with pub data.
        $imprint_data[] = array(
            'site_name' => $k,
            'site_url' => $v,
            'pubs' => $pub_data,
        );
    }

    // Save imprint data to transient to reduce API calls
    set_transient($transient_id, $imprint_data, 60 * 60 * 24);
}
?>
<div class="toolbar">
    <div class="row">
        <div class="small-12 columns">
            <a href="<?php echo $af_url; ?>/pub/afr-pt/<?php echo $signon_url; ?>" class="afr-link" target="_blank">
                <svg class="icon icon-chevron-left">
                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-chevron-left"></use>
                </svg>
                <span>Platinum Reserve Member Home</span>
            </a>
            <a href="#" class="imprint-link">
                <svg class="icon icon-bars">
                    <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-bars"></use>
                </svg>
                <span>Resources</span>
            </a>
        </div>
    </div>
</div>
<div class="toolbar-links">
    <div class="row">
        <div class="small-12 columns toolbar-columns">
            <?php
            foreach ($imprint_data as $imprint) {
                $site_name = $imprint['site_name'];
                $site_url = $imprint['site_url'].$signon_url;
                $pubs = $imprint['pubs'];
            ?>
            <section class="toolbar-group">
                <div class="links-wrap">
                    <!-- <h3 class="h6"><a href="<?php echo $site_url; ?>" target="_blank"><?php echo $site_name; ?></a></h3> -->
                    <ul class="toolbar-menu">
                        <?php
                        foreach ($pubs as $pub) {
                            $pub_name = $pub['pub_name'];
                            $pub_url = $pub['pub_url'].$signon_url;
                        ?>
                        <li><a href="<?php echo $pub_url; ?>" target="_blank"><?php echo $pub_name; ?></a></li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </section>
            <?php
            }
            ?>
            <section class="toolbar-group">
                <div class="links-wrap">
                <!--<h3 class="h6"><a href="<?php echo $account_url; ?>">My Account</a></h3>-->
                <ul class="toolbar-menu">
                    <?php if ($account) { ?>
                    <li><a href="<?php echo $account_url; ?>">Change My Password</a></li>
                    <li><a href="<?php echo $account_url; ?>">Update My Account</a></li>
                    <?php } ?>
                    <li><a href="<?php echo wp_logout_url(); ?>">Logout</a></li>
                </ul>
                </div>
            </section>
        </div>
    </div>
</div>