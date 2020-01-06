<?php
// Template Name: Watchlist
global $af_theme, $af_templates;

$af_theme->af_redirect_to_login();
$af_templates->af_head();
$af_templates->af_header();
$af_templates->af_masthead_watchlist();
$af_templates->af_page_watchlist();
$af_templates->af_footer();