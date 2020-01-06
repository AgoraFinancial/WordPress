<?php global $af_templates; ?>
<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 medium-8 large-6 medium-centered columns">
            <div class="masthead">
                <h1><?php the_title(); ?></h1>
                <?php $af_templates->af_search_bar_watchlist(); ?>
            </div>
        </div>
    </div>
</header>