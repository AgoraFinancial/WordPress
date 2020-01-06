<?php
global $af_templates;

// Check if search results are by post type
$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';

// Use FAQ results
if (isset($post_type) && $post_type == 'faq') {
    $af_templates->af_search_results_help();
} else { // Default
    $af_templates->af_search_results_default();
}