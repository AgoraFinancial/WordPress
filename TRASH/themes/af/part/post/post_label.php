<?php
// Get needed article data
$category = $post_data['article_category_name'];
$category_url = $post_data['article_category_url'];
$publications = $post_data['article_publications'];
$is_single_publication = is_singular('publications');
$label = '';

// Get article labels for specific pages
if (is_front_page() || $category && is_author() || is_page_template('page-whats-new.php')) {
    if ($publications) {
        foreach ($publications as $pub) {
            $label .= '<a href="' . get_permalink($pub->ID) . '" class="label label-link primary">' . $pub->post_title . '</a>';
        }
    } else {
        $label .= '<a href="' . $category_url . '" class="label label-link secondary">' . $category . '</a>';
    }
}
// For pub view, link to pub-specific category
if ($category && $is_single_publication && !isset($_GET['type']) || $category && $is_single_publication && $_GET['type'] == 'whats-new') {
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . strtok($_SERVER['REQUEST_URI'],'?');
    $category_url = $actual_link . '?type=' . $post_data['article_category'];
    $label = '<a href="'.$category_url.'" class="label label-link secondary">'.$category.'</a>';
}
// Display label
if ($label) {
    echo '<p class="article-category">' . $label . '</p>';
}