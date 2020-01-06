<?php
/**
 * Instantiates all theme classes
 */

$af_base = new AF_Base;

// Instantiate only if parent theme
if (PARENT_PATH === CHILD_PATH) {
    $af_theme = new AF_Theme;
}

$af_admin = new AF_Admin;
$af_auth = new AF_Auth;
$af_setup = new AF_Setup;
$af_plugins = new AF_Plugins;
$af_templates = new AF_Templates;
$af_posts = new AF_Posts;
$af_publications = new AF_Publications;
$af_pages = new AF_Pages;
$af_faqs = new AF_FAQs;
$af_products = new AF_Products;
$af_shortcodes = new AF_Shortcodes;
$af_users = new AF_Users;