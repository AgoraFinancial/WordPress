<?php
global $af_auth, $af_publications, $af_templates;

// Set default pub ID
$pub_id = $post->ID;

// If post_type is post, get authorized pub ID
if ($post->post_type == 'post') {
    $valid = $af_publications->af_get_valid_pub_ID();

    if (!empty($valid)) {
        $pub_id = $valid;
    } else { // If not valid, get fallback pub
        $pubs = $af_publications->af_get_pubs($post);
        if (is_array($pubs) && count($pubs)) {
            $pub_id = $af_publications->af_get_pub_ID($pubs[0]);
        }
    }
}

// Get pub data
$pub_data = $af_publications->af_get_pub_data($pub_id);

// Free articles
$free_article_array = apply_filters( 'filter_free_article_cats', array( 'free-articles' ) );

// Get post category
$post_category = get_the_category($post->ID);
?>
<div class="breadcrumbs-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li>
                        <a href="/">
                            <svg class="icon icon-home">
                                <use xlink:href="<?php echo PARENT_PATH_URI; ?>/img/symbol-defs.svg#icon-home"></use>
                            </svg>
                            Home
                        </a>
                    </li>
                    <?php // Loop multiple categories, exclude free-articles
                    foreach ($post_category as $cat) {
                        if ( in_array( $cat->slug , $free_article_array ) ) {
                            continue;
                        }
                    ?>
                    <li class="current">
                        <a href="<?php echo get_permalink($pub_data['pub_id']).'?type='.$cat->slug; ?>"><?php echo $cat->name; ?></a>
                    </li>
                    <?php
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>