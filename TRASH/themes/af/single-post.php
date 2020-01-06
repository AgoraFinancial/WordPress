<?php
global $af_templates, $af_theme;

// Redirect to PDF view
if(isset($_GET['pdf']) && 1 == $_GET['pdf']) {
    $af_templates->af_single_post_pdf();
} else {
    $af_templates->af_head();
    $af_templates->af_header();
    $af_templates->af_masthead_publications();
    $af_templates->af_breadcrumbs_publications();
    $af_templates->af_single_post();
    $af_templates->af_footer();
}