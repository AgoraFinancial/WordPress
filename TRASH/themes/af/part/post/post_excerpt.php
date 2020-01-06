<?php
global $af_auth, $af_posts, $af_templates, $af_publications;

$post_data = $af_posts->af_single_post_data($single_post);
$has_access = $af_auth->has_pub_access($post_data['article_pubcodes']) ? true : false ;

// Pass variables into part
set_query_var('post_data', $post_data);

// Get post object
$post_object = get_post($post_data['article_id']);

// Get needed article data
$stock_recommendations = $post_data['article_stock_recommendations'];
?>
<article class="article-card <?php echo $post_data['article_classes']; ?>">
    <?php
    // Only show featured image if book or report
    $free_article_array = apply_filters( 'filter_free_article_cats', array( 'free-articles' ) );
    $is_category = is_category();
    $has_post_thumbnail = has_post_thumbnail($post_data['article_id']);
    $has_category_report = has_category('report');
    $has_category_books = has_category('books');
    $has_category_free_articles = has_category($free_article_array);
    if (is_front_page()                             // homepage
    || is_home()                                    // blog/index
    || $is_category && $has_category_free_articles  // free article archive
    || $has_category_report && $has_post_thumbnail  // report category
    || $has_category_books && $has_post_thumbnail   // book category
    || $has_category_free_articles && !is_author()  // free-articles category
    || is_category($free_article_array)                 // free-articles category archive
    || $is_category && defined('FREE_ARTICLES_ONLY') && FREE_ARTICLES_ONLY === true // globally set free articles
    ) {
    ?>
        <div class="article-image-wrapper">
            <?php if ($imagesize != '') { ?>
                <a class="article-image-link" href="<?php echo $post_data['article_url']; ?>">
                    <?php
                    // If book or report, show featured image default medium
                    if ($has_post_thumbnail && $has_category_report || $has_category_books) {
                        echo get_the_post_thumbnail($post_data['article_id'], 'medium', array( 'class' => ''));
                    } else {
                        echo $post_data[$imagesize];
                    }
                    ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="article-excerpt-wrapper">
        <?php $af_templates->af_post_label(); ?>
        <h2 class="excerpt-title h3">
            <a href="<?php echo $post_data['article_url']; ?>">
                <?php echo $post_data['article_title']; ?>
            </a>
        </h2>
        <?php $af_templates->af_post_meta(); ?>
        <p class="article-excerpt"><?php echo $post_data['article_excerpt']; ?></p>
        <?php do_action( 'af_after_article_excerpt', $post_data ); ?>
        <?php
        if ($has_access && $stock_recommendations) {
        ?>
        <p class="excerpt-recommendations">
            <strong>Recommendations: </strong>
            <?php
            foreach ($stock_recommendations as $stock) {
                if (($stock['action'] === 'Buy') || ($stock['action'] === 'buy')) {
                    $label = 'success';
                    $action = 'Buy';
                }
                if (($stock['action'] === 'Sell') || ($stock['action'] === 'sell')) {
                    $label = 'alert';
                    $action = 'Sell';
                }
                if (($stock['action'] === 'Hold') || ($stock['action'] === 'hold')) {
                    $label = 'warning';
                    $action = 'Hold';
                }
                if (($stock['action'] === 'Buy To Close') || ($stock['action'] === 'btoclose')) {
                    $label = 'alert';
                    $action = 'Buy To Close';
                }
                if (($stock['action'] === 'Buy To Open') || ($stock['action'] === 'btoopen')) {
                    $label = 'success';
                    $action = 'Buy To Open';
                }
                if (($stock['action'] === 'Sell To Open') || ($stock['action'] === 'stoopen')) {
                    $label = 'success';
                    $action = 'Sell To Open';
                }
                if (($stock['action'] === 'Sell To Close') || ($stock['action'] === 'stoclose')) {
                    $label = 'alert';
                    $action = 'Sell To Close';
                }
                ?>
                <span class="label <?php echo $label; ?>"><span><?php echo $action; ?></span> <?php echo $stock['ticker_symbol']; ?></span>
            <?php } ?>
        </p>
        <?php
        }
        if ($readmore != '') {
        ?>
        <a href="<?php echo $post_data['article_url']; ?>" class="button">Read This</a>
        <?php
        }
        ?>
    </div>
</article>