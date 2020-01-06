<?php
global $af_pages, $af_users;
?>

<main class="content-wrapper">
    <div class="row">
        <div class="small-12 medium-large-10 medium-large-centered columns">
            <article>
                <?php echo apply_filters('the_content', $post->post_content); ?>             
            </article>
            <section class="watchlist-section">
                <div class="row">
                    <div class="small-12 columns">
                        <div class="processing-overlay" id="ajax-loading">
                            <div class="processing-spinner"></div>                    
                        </div>
                        <table class="table-responsive table-centered table-sortable ticker-table ticker-display" id="watchlist-table"></table>
                        <div id="watchlist-instructions">
                            <h2 class="h3">How To Use This Page</h2>
                            <h3 class="h4">Keep track of the companies and securities that are most important to you. See how below:</h3>
                            <p><strong>Adding Tickers Using This Page:</strong> At the top of this page, enter a valid ticker symbol (<em>for example Apple Computers ticker symbol is AAPL</em>) in the field above and click the <strong>Add To My Watchlist</strong> button. This will add the ticker to your watchlist. You can add as many ticker symbols as you like.</p>
                            <p><strong>Adding Tickers From Individual Ticker Pages:</strong> Click the <strong>Add To My Watchlist</strong> button at the top of the individual ticker pages to add them to this watchlist.</p>
                            <p>The watchlist will contain information on each tickers daily performance along with a full featured chart and links to any articles that our team has ever written about that ticker.</p>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>
</main>