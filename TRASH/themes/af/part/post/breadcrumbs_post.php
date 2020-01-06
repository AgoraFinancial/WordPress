<?php $post_category = get_the_category($post->ID); ?>
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
                        if ($cat->slug == 'free-articles') {
                            continue;
                        }
                    ?>
                    <li class="current">
                        <a href="<?php echo get_term_link($cat->term_id); ?>"><?php echo $cat->name; ?></a>
                    </li>
                    <?php
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>