<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <input type="text" class="search-input" value="" name="s" placeholder="Search our site...">
    <button class="search-button">
        <svg class="icon icon-search">
            <use xlink:href="<?php echo PARENT_PATH_URI; ?>/img/symbol-defs.svg#icon-search"></use>
        </svg>
    </button>
</form>