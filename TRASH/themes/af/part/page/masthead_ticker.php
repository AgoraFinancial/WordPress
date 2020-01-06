<?php
global $af_templates;
$ticker = preg_replace("/\W/", '', get_query_var('ticker'));
$ticker = str_replace('_', ':', $ticker);
?>
<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 large-8 large-centered columns">
            <div class="masthead">
                <h1><?php echo $ticker ? "Ticker: $ticker" : 'Ticker'; ?></h1>
            </div>
        </div>
    </div>
</header>