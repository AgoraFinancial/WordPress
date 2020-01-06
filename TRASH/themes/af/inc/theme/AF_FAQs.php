<?php
/**
 * Controls data/logic for frequently asked questions
 */

class AF_FAQs extends AF_Theme {
    public function __construct() {
         $this->af_faq_hooks();
    }

    // Set up faq hooks
    public function af_faq_hooks() {
        add_action('pre_get_posts', array($this, 'af_faq_show_all_results'));
    }

    // Show all faqs on taxonomy archive or search page
    public function af_faq_show_all_results($query) {
        if ($query->is_tax('faq-category') || $query->is_search() && isset($_GET['post_type']) && $_GET['post_type'] == 'faq') {
            $query->set('post_type', 'faq');
            $query->set('posts_per_page', 50);
        } 
        return;
    }

    // Get all the data you need for a single faq
    public function af_single_faq_data($post) {
        global $af_posts, $af_publications;

        // Get all ACF fields
        $fields = $af_posts->af_get_acf_fields($post->ID);

        // Get post title
        $faq_title = get_the_title($post->ID);

        // Get associated pubcodes
        $faq_pubcodes = $af_publications->af_get_pubs($post);

        // Get assigned publications
        $faq_publications = $fields['publication'];

        // Assemble array with all final data
        $faq_data = array(
            'faq_id' => $post->ID,
            'faq_title' => $faq_title,
            'faq_pubcodes' => $faq_pubcodes,
            'faq_publications' => $faq_publications,
        );
        return $faq_data;
    }
}