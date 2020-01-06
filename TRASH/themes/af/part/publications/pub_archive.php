<?php
global $af_posts, $af_publications, $af_templates;

// Get category has label and assign
$category_data = $af_publications->af_get_pub_cat_data($post->ID);
foreach ($category_data as $category) {
    if ($category['category']->slug != $type) {
        continue;
    } else {
        $cat_name = $category['category_label'] != '' ? $category['category_label'] : $category['category']->name;
        $term_taxonomy_id = $category['category']->term_taxonomy_id;
    }
}

// Get current query string dates
$current_archive = $month . '-' . $year;
$current_archive = DateTime::createFromFormat('m-Y', $current_archive)->format('F Y');

// Return url for archive
$return_url = add_query_arg(array('type' => $type), get_permalink());

// Get selected month/year current link for links
$selected_year = $year;
$selected_date = $month . $year;

// Post excerpt arguments by date
$args = array(
    'category_name' => $type,
    'posts_per_page' => -1,
    'ignore_sticky_posts' => 1,
    'meta_query' => array(
        array(
            'key'     => 'publication',
            'value'   => '"'.$post->ID.'"',
            'compare' => 'LIKE'
        )
    ),
    'date_query' => array(
        array(
            'year'  => $year,
            'month' => $month,
        ),
    ),
);

$posts = new WP_Query($args);

// Date query arguments
$args = array(
    'category_name' => $type,
    'posts_per_page' => 3,
    'ignore_sticky_posts' => 1,
    'meta_query' => array(
        array(
            'key'     => 'publication',
            'value'   => '"'.$post->ID.'"',
            'compare' => 'LIKE'
        )
    )
);

$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts LEFT JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id) INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id ) WHERE 1=1 AND wp_term_relationships.term_taxonomy_id IN ($term_taxonomy_id) AND wp_postmeta.meta_key = 'publication' AND wp_postmeta.meta_value LIKE '%$post->ID%' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC";
$key = md5( $query );
$key = "wp_get_archives:$key:$last_changed";
$dates = $wpdb->get_results( $query );

?>
<main class="content-wrapper layout-sidebar-right">
    <div class="row">
        <div class="small-12 medium-9 columns">
            <article class="publication-archive">
                <h2 class="section-header h4"><?php echo $cat_name; ?> Archive: <?php echo $current_archive; ?></h2>
                <section class="excerpt-list">
                    <?php
                    if ($posts->have_posts()) {
                        while ($posts->have_posts()) {
                            $posts->the_post();
                    ?>
                    <div class="row">
                        <div class="small-12 columns">
                            <?php $af_posts->af_get_post_excerpt($post, 'article_thumb', ''); ?>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                    ?>
                    <p>There are currently no posts in this archive.</p>
                    <?php
                    }
                    wp_reset_postdata();
                    ?>
                </section>
            </article>
        </div>
        <div class="small-12 medium-3 columns float-right">
            <aside>
                <p class="archive-return">
                    <a href="<?php echo $return_url; ?>">
                        <svg class="icon icon-clock-o">
                            <use xlink:href="<?php echo PARENT_PATH_URI; ?>/img/symbol-defs.svg#icon-clock-o"></use>
                        </svg>
                        View Latest <?php echo $cat_name; ?>
                    </a>
                </p>
               
                <?php if($dates) { ?>

                    <nav class="side-nav archive-nav">
                        <?php
                        // Generate list of archive links
                        $current_date = $new_date = '';
                        $i = 0;

                        // Loop through posts and add dates
                        foreach ($dates as $row) {  
                            $current_year = $new_year;
                            $current_month = $new_month;
                            $current_date = $new_date;
                            $new_year = $row->year;
                            $new_month = sprintf("%02d", $row->month);
                            $new_date = $new_month . $new_year;
                            $date_object = DateTime::createFromFormat('!m', $new_month);
                            $month_name = $date_object->format('F');

                            // Continue if date not unique
                            if ($current_date == $new_date) {
                                continue;

                            // Create new link for every new month    
                            } else {
                                $date_url = add_query_arg(array('archive' => $new_date), $return_url);
                                $class_month = $selected_date == $new_date ? 'current-month' : '' ;
                                $class_year = $selected_year == $new_year ? 'current-year' : '' ;
                                $active_year = $selected_year == $new_year ? 'active' : '' ;

                                if ($current_year != $new_year) {
                                    // Only  close UL if it's not the first item.
                                    if ($i > 0) {
                                        echo '</ul>';
                                    }
                                    echo '<h4 class="archive-year-header '.$active_year.'">'.$new_year.'</h4><ul class="vertical menu text-center archive-month-list '.$class_year.'">';
                                    $i++;
                                }
                            
                                ?>

                            <li class="<?php echo $class_month; ?>">
                                <a href="<?php echo esc_url($date_url); ?>"><?php echo $month_name; ?></a>
                            </li>

                            <?php }
                        } ?>

                        </ul>
                    </nav>
                
                <?php } ?>

            </aside>
        </div>
    </div>
</main>