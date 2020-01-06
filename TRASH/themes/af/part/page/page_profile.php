<?php
global $af_auth, $af_users;

$data = $af_users->getUserFinancialProfile();
$content = isset( $data['content'] ) ? $data['content'] : '' ;
$results = isset( $data['results'] ) ? $data['results'] : '' ;
$complete = isset( $data['completion'] ) ? $data['completion'] : '' ;
$cta = isset( $data['cta'] ) ? $data['cta'] : '' ;
$lytics_data = isset( $data['lytics_data'] ) ? $data['lytics_data'] : '' ;
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 medium-10 medium-centered columns">
            <?php
            if ($post->post_content != '') {
            ?>
            <div class="row">
                <div class="small-12 columns">
                    <article class="profile-content">
                        <?php echo apply_filters('the_content', $post->post_content); ?>
                    </article>
                </div>
            </div>
            <?php
            }
            if ($results && count($results)) {
            ?>
            <div class="row">
                <div class="small-12 columns">
                    <article class="profile-results">
                        <?php echo $cta; ?>
                        <section class="results-section">
                            <?php foreach ($results as $pubchart) { ?>
                            <div class="row collapse results-row <?php if ($pubchart['active_pub']) echo 'active';?>">
                                <div class="small-12 medium-4 columns">
                                    <div class="results-pub">
                                        <h3 class="results-title h5">
                                            <a href="<?php echo $pubchart['pub_url']; ?>"
                                               class="title-view-pub"
                                               data-pubcode="<?php echo $pubchart['pub_code']; ?>">
                                                <?php echo $pubchart['pub_name']; ?>
                                            </a>
                                        </h3>
                                        <?php if ($pubchart['active_pub']) { ?>
                                        <p class="recent-pub-purchase">Your Most Recent Purchase<p>
                                        <?php } ?>
                                        <p class="pub-actions">
                                            <a class="button secondary tiny button-view-pub" href="<?php echo $pubchart['pub_url']; ?>" data-pubcode="<?php echo $pubchart['pub_code']; ?>">
                                               View
                                            </a>
                                            <?php if (!$af_auth->has_pub_access($pubchart['pub_code']) && isset($pubchart['subscribe_url'])) { ?>
                                            <a class="button tiny button-subscribe" href="<?php echo $pubchart['subscribe_url']; ?>" target="_blank" data-pubcode="<?php echo $pubchart['pub_code']; ?>" data-event-category="Subscribe Button">
                                               Subscribe
                                            </a>
                                            <?php } ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="small-12 medium-8 columns">
                                    <div class="results-progress">
                                        <div class="results-progress-wrapper" >
                                            <div class="results-progress-completion" data-width="<?php echo $pubchart['completion_percent'];?>" style="opacity: <?php echo $pubchart['transparency_level'];?>">
                                                <?php if ($pubchart['score'] == $pubchart['max_score']) { ?>
                                                <span class="label bestmatch alert">Best Match</span>
                                                <?php } ?>
                                           </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </section>
                        <hr class="custom-separator">
                    </article>
                </div>
            </div>

            <?php
            }
            if ($content) {
            ?>
            <div class="row">
                <div class="small-12 columns">
                    <article class="profile-survey">
                        <?php echo $content; ?>
                    </article>
                </div>
            </div>
            <?php
            }
            ?>

        </div>
    </div>
</main>

<script>
    <?php // Report completed percentage is greater than 0
    if ( $complete > 0 ) { ?>
    var chkSendLyticsProfile = setInterval(function(){
        if (typeof afga != 'undefined' && typeof afga.send == 'function'){
            clearInterval(chkSendLyticsProfile);
            setTimeout(function(){
                afga.send('event', {
                    'eventCategory' : 'Financial Profile',
                    'eventAction' : 'Results',
                    'eventLabel' : '<?php echo $complete;?>%'
                });
            }, 2000);
        }
    }, 300);
    <?php } ?>

    <?php // Send valid lytics data
    if ( $lytics_data ) { ?>
        window.jstag.send( <?php echo $lytics_data;?> );
    <?php } ?>
</script>
