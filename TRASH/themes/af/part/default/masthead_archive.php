<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <div class="masthead">
                <h1>
                    <?php
                    if (is_category()) {
                        single_cat_title();
                    } elseif (is_tag()) {
                        single_tag_title();
                    } elseif (is_day()) {
                        the_time('F jS, Y');
                    } elseif (is_month()) {
                        the_time('F Y');
                    } elseif (is_year()) {
                        the_time('Y');
                    } elseif (is_tax()) {
                        echo single_term_title();
                    } elseif (is_post_type_archive()) {
                        echo post_type_archive_title();
                    } elseif (is_home()) { // Assigned Posts Page
                        echo get_the_title(get_option('page_for_posts'));
                    } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { // Default
                        echo 'Archives';
                    }
                    ?>
                </h1>
            </div>
        </div>
    </div>
</header>