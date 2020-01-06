<?php
global $af_templates;

$is_archive = isset($_GET['archive']) && is_numeric($_GET['archive']) && strlen($_GET['archive']) == 6 ? $_GET['archive'] : false;

$af_templates->af_head();
$af_templates->af_header();
$af_templates->af_masthead_blog();

if ($is_archive) {
    // Get month/year and pass to part
    $month = (int)substr($is_archive, 0, 2);
    $year = (int)substr($is_archive, 2);
    set_query_var('year', $year);
    set_query_var('month', $month);
    $af_templates->af_blog_archive();
} else {
    $af_templates->af_blog();
}

$af_templates->af_footer();