<?php
global $af_templates;
$title = '';
if ( is_tax() ) {
    $title = single_term_title( '', false );
} elseif ( is_search() ) {
    $title = apply_filters( 'af_help_search_results_title', 'Help Search Results' );
} else {
    $title = get_the_title();
}

?>
<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 large-8 large-centered columns">
            <div class="masthead">
                <h1><?php echo $title; ?></h1>
                <?php $af_templates->af_search_bar_help(); ?>
            </div>
        </div>
    </div>
</header>