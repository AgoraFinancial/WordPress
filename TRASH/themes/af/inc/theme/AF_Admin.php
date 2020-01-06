<?php
class AF_Admin {

    
    /**
     * WP transients sorted by post type
     * @var array
     */
    public $transients = array(
        'publications' => array (
            'af_transient_active_pub_ids',
            'af_transient_active_pubcodes',
            'afr_toolbar'
        )
    );

    public function __construct() {
        $this->hooks();
    }

    public function hooks() {
        add_filter('parse_query', array($this, 'af_admin_posts_filter'));
        add_action('restrict_manage_posts', array($this, 'af_admin_posts_filter_restrict_manage_posts'));
        add_action('manage_posts_custom_column', array($this, 'af_custom_columns'));
        add_action('manage_faq_posts_custom_column', array($this, 'af_custom_columns'));
        add_action('manage_publications_posts_custom_column', array($this, 'af_publications_custom_columns'));
        add_filter('manage_edit-post_columns', array($this, 'af_post_columns'));
        add_filter('manage_edit-faq_columns', array($this, 'af_faq_columns'));
        add_filter('manage_edit-publications_columns', array($this, 'af_publications_columns'));
        add_action('save_post', array($this, 'af_update_post_template'));
        add_action('save_post', array($this, 'af_clear_transients'));
        add_filter('acf/validate_value/name=section_id', array($this, 'af_valid_acf_id_fields'), 10, 4);
        add_filter('acf/validate_value/name=answer_id', array($this, 'af_valid_acf_id_fields'), 10, 4);
        add_filter('acf/validate_value/name=category_slug', array($this, 'af_valid_acf_slug_fields'), 10, 4);
        // add_filter('acf/validate_value/name=column_id', array($this, 'af_valid_acf_portfolio_fields'), 10, 4);
        add_action('load-post.php', array($this, 'af_load_user_dropdown_filter'));
        add_action('load-post-new.php', array($this, 'af_load_user_dropdown_filter'));
	}

    /**
     * First create the dropdown
     * make sure to change POST_TYPE to the name of your custom post type
     */
    public function af_admin_posts_filter_restrict_manage_posts() {
        global $wpdb;
        $type = 'post';
        $post_type = $_GET['post_type'];

        if (isset($post_type)) {
            $type = $post_type;
        }

        // Only add filter to post type you want
        if ('post' === $type || 'faq' === $type) {
            // Change this to the list of values you want to show in 'label' => 'value' format

            $pubs = $wpdb->get_results("
                SELECT ID, post_title
                FROM $wpdb->posts
                WHERE post_type = 'publications'
            ");

            $values = array();
            foreach ($pubs as $pub) {
                $values[$pub->post_title] = $pub->ID;
            }
            ?>
            <select name="pub">
                <option value="">Filter By Publication</option>
                <?php
                $current_v = isset($_GET['pub']) ? $_GET['pub'] : '';
                foreach ($values as $label => $value) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v ? ' selected="selected"' : '',
                        $label
                    );
                }
                ?>
            </select>
            <?php
        } else {
            return;
        }
    }

    /**
     * If submitted filter by post meta
     *
     * Make sure to change META_KEY to the actual meta key
     * and POST_TYPE to the name of your custom post type
     */
    public function af_admin_posts_filter($query) {
        global $pagenow;
        $type = 'post';

        $type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post' ;
        $cat = isset($_GET['cat']) ? $_GET['cat'] : '' ;

        if (('post' == $type || 'faq' == $type) && is_admin() && $pagenow == 'edit.php' && isset($_GET['pub']) && $_GET['pub'] != '') {

            // Get publications now as array
            $publications = array (
                'key'     => 'publication',
                'value'   => '"' . $_GET['pub'] . '"',
                'compare' => 'LIKE'
            );
            $meta_query_array[] = $publications;
            $query->query_vars['meta_query'] = $meta_query_array;
            
        }

        // There is an issue with legacy data on some sites, so if cat ID is set, we also add cat name to filter results
        if($cat) {
            $query->query_vars['category_name'] = get_category($cat)->slug;
        }

        return;
    }

    // Add publications to post columns
    public function af_post_columns($columns) {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'title'        => 'Title',
            'author'       => 'Author',
            'publications' => 'Publications',
            'categories'   => 'Categories',
            'tags'         => 'Tags',
            'date'         => 'Date',
        );

        // Remove publications for all free articles
        if (FREE_ARTICLES_ONLY === true) {
            unset($columns['publications']);
        }
        return $columns;
    }

    // Add publications to faq columns
    public function af_faq_columns($columns) {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'title'        => 'Title',
            'publications' => 'Publications',
            'taxonomy-faq-category' => 'Categories',
            'date'         => 'Date',
        );

        // Remove publications for all free articles
        if (FREE_ARTICLES_ONLY === true) {
            unset($columns['publications']);
        }
        return $columns;
    }

    // Add pubcodes to publications columns
    public function af_publications_columns($columns) {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'title'        => 'Title',
            'pubcode'      => 'Pubcode',
            'date'         => 'Date',
        );

        // Remove publications for all free articles
        if (FREE_ARTICLES_ONLY === true) {
            unset($columns['publications']);
        }
        return $columns;
    }

    /// Populate publications column
    public function af_custom_columns($column) {
        global $post;
        if ('publications' == $column) {
            $publications = get_post_meta( $post->ID, $key = 'publication', true );
            $pub_array = array();
            if(is_array($publications)) {
                foreach ($publications as $pub) {
                    $pub_array[] .= get_the_title($pub);
                }
            }
            echo implode(', ', $pub_array);
        }
    }

    // Populate pubcode column
    public function af_publications_custom_columns($column) {
        global $post;
        if ('pubcode' == $column) {
            if (function_exists('get_field')) {
                $pubcode = get_field('pubcode', $post->ID);
                echo $pubcode;
            }
        }
    }

    // Update post template based on category/publication settings
    public function af_update_post_template($post_id) {
        global $af_theme;

        // Get post type, if not post, return
        $post_type = get_post_type($post_id);
        if ('post' != $post_type || wp_is_post_revision($post_id)) {
            return;
        }

        // Get free article categories
        $free_article_cats = $af_theme->af_get_free_article_cats();

        // Set flags
        $is_free_article = false;
        $has_publication = false;

        // If free articles only, set all categories to free
        if (FREE_ARTICLES_ONLY === true) {
            $is_free_article = true;
        } else {
            // Get post categories, if has a free category, update flag
            $categories = get_the_category($post_id);
            foreach ($categories as $category) {
                if (!in_array($category->slug, $free_article_cats)) {
                    continue;
                } else {
                    $is_free_article = true;

                    // Append free-articles category if not assigned
                    $default_free_category = apply_filters( 'filter_default_free_article_cat', 'free-articles' );
                    $cat_obj = get_category_by_slug( $default_free_category );
                    $cat_id = $cat_obj->term_id;
                    $cats = array($cat_id);
                    wp_set_post_categories($post_id, $cats, true);
                }
            }
        }

        // Check for publications, if set, update flag
        $publications = get_field('publication', $post_id);

        if ($publications) {
            $has_publication = true;
        }

        // Update post template to free articles if set as category
        if ($is_free_article && !$has_publication) {
            update_post_meta($post_id, '_wp_page_template', 'post-free-article.php');
        } else {
            update_post_meta($post_id, '_wp_page_template', 'default');
        }
        return;
    }

    // ACF field validation of ID fields
    public function af_valid_acf_id_fields($valid, $value, $field, $input) {
        if (!is_admin()) {
            return;
        }
        if (!$value) {
            return $valid;
        }
        if (!preg_match('/^[a-z_]+$/i', $value)) {
            $valid = 'Lowercase letters only, no whitespaces (underscores only).';
        }
        return $valid;
    }

    // ACF field validation of slug fields
    public function af_valid_acf_slug_fields($valid, $value, $field, $input) {
        if (!is_admin()) {
            return;
        }
        if (!$value) {
            return $valid;
        }
        if (!preg_match('/^[a-z\-]+$/i', $value)) {
            $valid = 'Lowercase letters only, no whitespaces (dashes only).';
        }
        return $valid;
    }

    // ACF field validation of portfolio column fields
    public function af_valid_acf_portfolio_fields($valid, $value, $field, $input) {
        if (!is_admin()) {
            return;
        }
        if (!$value) {
            return $valid;
        }
        if (!preg_match('/^[a-zA-Z]+$/i', $value)) {
            $valid = 'Letters only, no whitespaces.';
        }
        return $valid;
    }

    // Load admin post check if admin and post screen
    public function af_load_user_dropdown_filter() {
        // if (!is_admin()) return;
        $screen = get_current_screen();
        if (empty($screen->post_type) || 'post' !== $screen->post_type) {
            return;
        }
        add_filter('wp_dropdown_users_args', array($this, 'af_dropdown_users_args'), 10, 2);
    }

    // Filter author dropdown to only show author role
    public function af_dropdown_users_args($args, $r) {
        global $wp_roles, $post;
        $user = wp_get_current_user();

        // Check that this is the correct drop-down
        if ('post_author_override' === $r['name'] && 'post' === $post->post_type) {
            $args['who'] = '';
            $args['role__in'] = 'author';
        }
        return $args;
    }

    // Clear defined list of transients
    public function af_clear_transients( $post_id ) {

        // Get post type
        $post_type = get_post_type($post_id);

        // Publications
        if ('publications' == $post_type) {

            // Delete pub_data transient
            delete_transient('af_transient_pub_'.$post_id);

            foreach($this->transients['publications'] as $transient_id) {
                delete_transient($transient_id);
            }
        }
        
        
    }
}