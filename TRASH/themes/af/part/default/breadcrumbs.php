<div class="breadcrumbs-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <nav aria-label="You are here:" role="navigation">
              <ul class="breadcrumbs">
                <li>
                    <a href="<?php echo esc_url(home_url()); ?>">
                        <svg class="icon icon-home">
                            <use xlink:href="<?php echo PARENT_PATH_URI; ?>/img/symbol-defs.svg#icon-home"></use>
                        </svg>
                        Home
                    </a>
                </li>
                <li class="current"><?php echo get_the_title($post->ID); ?></li>
              </ul>
            </nav>
        </div>
    </div>
</div>