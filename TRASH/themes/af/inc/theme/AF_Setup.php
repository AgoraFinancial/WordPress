<?php
/**
 * Core theme setup options that are unlikely to change
 */

class AF_Setup extends AF_Base {
    public function __construct() {
        parent::__construct();
        $this->af_setup_hooks();
    }

    // Set up basic wordpress modifications
    public function af_setup_hooks() {
        // Register nav menus
        add_action('init', array($this, 'af_register_nav_menus'));

        // Remove admin bar
        add_action('after_setup_theme', array($this, 'af_remove_admin_bar'));

        // Remove emoji support
        add_action('init', array($this, 'af_disable_emojis'));

        // Remove WP meta-name="generator"
        remove_action('wp_head', 'wp_generator');

        // Remove unused rels links
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

        // Feature image support
        add_theme_support('post-thumbnails');

        // Change default email send name
        add_filter('wp_mail_from_name', array($this, 'af_change_sender_name'));
    }

    // Navigation menus
    public function af_register_nav_menus() {
        register_nav_menus([
            'af-main-menu' => 'Main Menu',
            'af-loggedin-menu' => 'Logged-In Menu',
            'af-account-menu' => 'Account Menu',
            'af-footer-menu' => 'Footer Menu'
        ]);
    }

    // Remove admin bar for non admins or editors
    public function af_remove_admin_bar() {
        if (!current_user_can('delete_others_pages') && !is_admin()) {
            show_admin_bar(false);
        }
    }

    // Remove emoji support
    public function af_disable_emojis() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        add_filter('tiny_mce_plugins', array($this, 'af_disable_emojis_tinymce'));
    }

    // Remove emoji support
    public function af_disable_emojis_tinymce($plugins) {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        } else {
            return array();
        }
    }

    // Add custom body class post_type + post_name
    public function af_custom_body_class() {
        global $post, $af_auth;
        $classes = array();
        if (defined('HAS_AFR') && HAS_AFR === true && is_user_logged_in() && $af_auth->is_afr_member()) {
            $classes[] .= 'afr-member';
        }
        if (is_page() || is_singular()) {
            $classes[] .= $post->post_type . '-' . $post->post_name;
        }
        return apply_filters( 'af_body_classes', $classes );
    }

    // Replace sender name with site title
    public function af_change_sender_name($original_email_from) {
        return get_bloginfo();
    }
}