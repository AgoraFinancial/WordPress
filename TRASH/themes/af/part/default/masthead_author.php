<?php
global $af_posts;

// Get author social media
$author = get_user_by('slug', get_query_var('author_name'));
$author_meta = get_user_meta($author->ID);
$article_author_image = $af_posts->af_get_author_image($wp_query->queried_object->data->display_name, $author->ID);
$twitter = !empty($author_meta['twitter'][0]) ? '//twitter.com/'.$author_meta['twitter'][0] : false;
$facebook = !empty($author_meta['facebook'][0]) ? $author_meta['facebook'][0] : false;
?>
<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <div class="masthead">
                <h1>
                    <?php
                    if ($article_author_image) {
                        echo $article_author_image;
                    }
                    echo $wp_query->queried_object->data->display_name;
                    ?>
                </h1>
                <?php
                if ($facebook || $twitter) {
                    $template_directory_uri = PARENT_PATH_URI;
                ?>
                <p class="author-page-social">
                    <span>Follow Me:</span>
                    <?php
                    if ($facebook) {
                    ?>
                    <a href="<?php echo $facebook; ?>" target="_blank">
                        <svg class="icon icon-facebook">
                            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-facebook"></use>
                        </svg>
                    </a>
                    <?php
                    }
                    if ($twitter) {
                    ?>
                    <a href="<?php echo $twitter; ?>" target="_blank">
                        <svg class="icon icon-twitter">
                            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-twitter"></use>
                        </svg>
                    </a>
                    <?php
                    }
                    ?>
                </p>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</header>