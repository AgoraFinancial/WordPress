<?php
global $af_posts, $af_users;
$ticker = preg_replace("/\W/", '', get_query_var('ticker'));
$ticker = str_replace('_', ':', $ticker);
$logged_in = is_user_logged_in();
$user_id = $af_users->objAFUser['id'];
$user_token = $af_users->objAFUser['af_user_token'];
$user_meta = get_user_meta($user_id, 'af_user_ticker', false);
$has_ticker = is_array($user_meta) && in_array( strtoupper($ticker), $user_meta ) ? true : false ;
$valid_ticker = $af_users->getTickerData($ticker);
$iframe_src =  'https://s.tradingview.com/widgetembed/?symbol=' . $ticker . '&interval=D&hidetoptoolbar=1&hidesidetoolbar=0&symboledit=1&saveimage=1&toolbarbg=f1f3f6&details=1&studies=%5B%5D&hideideas=1&theme=White&style=1&timezone=EST&withdateranges=1&studies_overrides=%7B%7D&overrides=%7B%7D&enabled_features=%5B%5D&disabled_features=%5B%5D';
?>

<main class="content-wrapper ticker-display">
    <div class="row">
        <div class="small-12 medium-10 medium-centered columns">
            <article>
                <?php echo apply_filters('the_content', $post->post_content); ?>
            </article>
            <section class="ticker-section">
                <div class="row">
                    <div class="small-12 columns">

                        <?php if ($ticker) { ?>
                            <iframe id="tradingview_b489d" src="<?php echo esc_url( $iframe_src ); ?>" width="100%" height="450" frameborder="0" allowtransparency="true" scrolling="no" allowfullscreen=""></iframe>
                        <?php } else { ?>
                            <div class="callout alert">Invalid ticker symbol.</div>
                        <?php } ?>

                        <?php if ($logged_in) { ?>
                            <div class="ticker-cta">
                                <?php if (!$has_ticker && $valid_ticker) { ?>
                                    <a href="#" class="button ajax-button" id="add-to-watchlist" data-id="<?php echo $user_id; ?>" data-token="<?php echo $user_token; ?>" data-symbol="<?php echo $ticker; ?>">Add To My Watchlist</a>
                                <?php } ?>
                                <?php if (get_page_by_path('my-watchlist')) { ?>
                                    <a href="/my-watchlist/" class="button secondary">View My Watchlist</a>
                                <?php } ?> 
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php
    if ($ticker) {
        $related_posts = new WP_Query(
            array(
                'post_type' => 'post',
                's' => $ticker
            )
        );

        if ($related_posts->have_posts()) { ?>

            <div class="row">
                <div class="small-12 large-10 large-centered columns">
                    <section class="excerpt-list related-posts">
                        <h2 class="section-header h4">Related Posts</h2>
                        <?php
                        while ($related_posts->have_posts()) {
                            $related_posts->the_post();
                        ?>
                        <div class="row">
                            <div class="small-12 columns">
                                <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </section>
                </div>
            </div>

        <?php } wp_reset_query();
    } ?>

</main>