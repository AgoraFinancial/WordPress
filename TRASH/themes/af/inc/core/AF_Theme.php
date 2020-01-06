<?php
/**
 * Extensible theme structure/functionality that is likely to change
 */

class AF_Theme extends AF_Base {
    public function __construct() {
        $this->af_enqueue_setup();
        $this->af_editor_style();
        $this->af_image_sizes();
        $this->af_customize_excerpt();
        $this->af_pre_get_posts();
    }

    // Enqeue styles/scripts
    public function af_enqueue_setup() {
        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'af_enqueue_styles'));

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'af_enqueue_scripts'));

        // Load js in wp_head
        add_action('wp_head', array($this, 'af_head_js'), 100);

        // Conditionally load js
        add_action('wp_footer', array($this, 'af_init_js'), 100);

        // Remove h1 from WYSIWYG editor.
        add_filter('tiny_mce_before_init', array($this, 'tiny_mce_formats'));
    }

    // Enqueue styles
    public function af_enqueue_styles() {
        $version = $this->af_get_static_asset_version();
        wp_enqueue_style('main-style', $this->af_get_child_theme_dir().'/css/app.css', '', $version);
    }

    // Enqueue scripts
    public function af_enqueue_scripts() {
        $parent_theme_dir = $this->af_get_parent_theme_dir();
        $child_theme_dir = $this->af_get_child_theme_dir();
        $version = $this->af_get_static_asset_version();
        $load_default_jquery = apply_filters( 'af_load_default_jquery', false );

        // Move JS to the footer.
        remove_action('wp_head', 'wp_print_scripts');
        remove_action('wp_head', 'wp_print_head_scripts', 9);
        remove_action('wp_head', 'wp_enqueue_scripts', 1);
        add_action('wp_footer', 'wp_print_scripts', 5);
        add_action('wp_footer', 'wp_enqueue_scripts', 5);
        add_action('wp_footer', 'wp_print_head_scripts', 5);

        // Optionally load jquery from foundation
        if ( !$load_default_jquery ) {
            wp_deregister_script('jquery');
            wp_enqueue_script('jquery', $parent_theme_dir.'/bower_components/jquery/dist/jquery.min.js', '', $version, true);
        }
        
        // Define our JS.
        wp_enqueue_script('what-input', $parent_theme_dir.'/bower_components/what-input/dist/what-input.min.js', '', $version, true);
        wp_enqueue_script('foundation', $parent_theme_dir.'/bower_components/foundation-sites/dist/js/foundation.min.js', '', $version, true);
        wp_enqueue_script('app', $child_theme_dir.'/js/app.min.js', array('jquery'), $version, true);
        wp_enqueue_script('comment');
        wp_enqueue_script('comment-reply');
    }

    // Load js in wp_head
    public function af_head_js() {
        echo '<script>var themeAjaxUrl = "/wp-admin/admin-ajax.php";</script>';
        return;
    }

    // Conditionally initilize js
    public function af_init_js() {
        global $current_user, $af_users;
        $is_user_logged_in = is_user_logged_in();

        // Open script
        echo '<script>';

        // Foundation scripts for free article exit pop
        if (is_page_template('post-free-article.php'))
            echo 'jQuery(document).foundation();';

        // On document ready
        echo '(function($){';
        echo '$(window).on("load", function() {';

        /***** Global *****/

        // Send email to lytics to match help match user
        if ($is_user_logged_in) {
            wp_get_current_user();
            if (user_can($current_user, "subscriber")) {
                $email = agora()->user->_get_email();
                if ($email) echo 'window.jstag.send({ email: "'.$email.'" });';
            }
        }

        // Navigations
        echo apply_filters('af_js_mainNav', 'mainNav();');
        if(!$is_user_logged_in) echo apply_filters('af_js_loginModal', 'loginModal();');

        // Search
        echo 'searchToggle();';

        // Email Oversight
        $this->af_init_email_oversight();

        // CTA/Button Tracking
        echo 'buttonAnalyticsTracking();';

        /***** Conditional *****/

        // OpenX
        if ($this->openxZones)
            $this->af_init_openx();

        // Free article / not logged in
        if (is_page_template('post-free-article.php')) {
            if (!$is_user_logged_in) {
                echo apply_filters('af_js_exitPop', 'exitPop();');
            }
            // Free article
            echo apply_filters('af_js_freeArticlePost', 'freeArticlePost();');
        }

        // Frequently asked questions
        if (is_tax('faq-category') || is_singular('faq') || is_singular('publications') && (isset($_GET['type']) && $_GET['type'] == 'help') || is_search() && (isset($_GET['post_type']) && $_GET['post_type'] == 'faq')) {
            echo apply_filters('af_js_faq', 'faq();');
            echo apply_filters('af_js_accordion', 'accordion();');
        }

        // Publication portfolios
        if (is_singular('publications') && (isset($_GET['type']) && $_GET['type'] == 'portfolio'))
            echo apply_filters('af_js_pubPortfolio', 'pubPortfolio();');

        // Co-reg/Free newsletter page
        if (is_page_template('page-co-reg.php'))
            echo apply_filters('af_js_coReg', 'coReg();');

        // Whats new
        if (is_page_template('page-whats-new.php'))
            echo apply_filters('af_js_whatsNew', 'whatsNew();');

        // Choose path
        if (is_page_template('page-choose-path.php'))
            echo apply_filters('af_js_choosePath', 'choosePath();');

        // My account
        if (is_page_template('page-my-account.php'))
            echo apply_filters('af_js_accordion', 'accordion();');

        // My profile
        if (is_page_template('page-my-profile.php'))
            echo apply_filters('af_js_profile', 'profile();');

        // My Watchlist
        if (is_page_template('page-my-watchlist.php')) {
            $user_id = $current_user->data->ID;
            $af_user_token = get_user_meta($user_id, 'af_user_token', true);
            $af_user_ticker = get_user_meta($user_id, 'af_user_ticker', false);
            $has_tickers = !empty($af_user_ticker) ? 1 : 0 ;
            echo 'var user_id = "' . $user_id . '";';
            echo 'var user_token = "' . $af_user_token . '";';
            echo 'var has_tickers = ' . $has_tickers . ';';
            echo 'loadWatchlistPage( user_id, user_token, has_tickers );';
        }

        // Posts
        if (is_singular('post') || is_page_template('page-my-saved-articles.php'))
            echo apply_filters('af_js_postActions', 'postActions();');

        // Posts archives
        if (isset($_GET['archive']) && is_numeric($_GET['archive']) && strlen($_GET['archive']) == 6)
            echo apply_filters('af_js_archiveNav', 'archiveNav();');

        // Placeholder for child theme
        $this->af_child_init_js();

        echo '});';
        echo '})(jQuery);';
        echo '</script>';
        return;
    }

    // Email Oversight init
    public function af_init_email_oversight() {
?>
        /*
        Weird tracking on live site causes form to be submitted 3x.
        Added flag for triple count.
        */
        var sendCount = 0;

        $('body').on('submit', '#LeadGen', function(e) {
            e.preventDefault();

            var form = this,
                email = $(form).find('input[name=email]').val(),
                ListID = $(form).find('input[name=ListID]').val() || '<?php if (defined('EO_DEFAULT_LIST_ID')) echo EO_DEFAULT_LIST_ID; ?>',
                PubCode = $(form).find("input[name=PubCode]").val() || '',
                ListName = $(form).find("input[name=ListName]").val() || '';

            <?php $this->af_email_oversight_msg(); ?>

            <?php // Reset count to zero on coreg template
            if(is_page_template('page-co-reg.php')) echo "sendCount = 0;"
            ?>

            if (sendCount % 3 == 0) {
                EmailValidationPost(form, email, ListID, ListName, PubCode, msgError);
                sendCount++;
            } else {
                sendCount++;
            }
        });
<?php
    }

    // Email Oversite messages
    public function af_email_oversight_msg() {
?>
        var msgError = 'There was a problem with your request, please <a href="/contact-us/">contact customer service</a> for further assistance.';
        // var msgSuccess = 'Success! You will be redirected shortly...';
        // var msgFormat = 'Your email is formatted incorrectly. Please verify your email address or call customer service for further assistance.';
        // var msgList = 'Thank you for your interest, but our records indicate that you are already a subscriber.';

<?php
    }

    // OpenX init
    public function af_init_openx() {
?>
        // OpenX
        var r_value = '';
        var l_value = '';
        var ca = document.cookie.split(";");
        for (var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == " ") {
                c = c.substring(1);
            }
            if (c.indexOf('r=') == 0) {
                r_value = c.substring(2, c.length);
            }
            if (c.indexOf('ly_segs=') == 0) {
                l_value = c.substring(8, c.length);
            }
        }
        r_value = (r_value === "" || typeof r_value == "undefined") ? null : encodeURIComponent(r_value);
        l_value = (l_value === "" || typeof l_value == "undefined") ? null : l_value;

        <?php
        // Load multiple zones
        $ar_zones = strpos($this->openxZones, ',') ? explode(',', $this->openxZones) : array($this->openxZones);

        // If single post or category page, assign catagories to openx call
        $ar_cats = (is_singular('post') || is_category()) ? get_the_category() : array();
        $cur_category = get_query_var('post-category');
        $has_page_cat = false;
        $out_cats = null;

        foreach ($ar_cats as $cat) {
            if (!isset($cat->category_nicename)) {
                continue;
            }
            $out_cats .= !empty($out_cats) ? ',' . $cat->category_nicename : $cat->category_nicename;
            if ($cur_category == $cat->category_nicename) {
                $has_page_cat = true;
            }
        }

        if ($cur_category && !$has_page_cat) {
            $out_cats .= $out_cats ? ',' . $cur_category : $cur_category;
        }

        foreach ($ar_zones as $zone) {
            list($zone_type, $wrapper) = explode(':', $zone, 2);
        ?>
                $.ajax({
                    url : themeAjaxUrl,
                    type : "post",
                    data : {
                        action : "openxZone",
                        params : {
                            zone : "<?php echo $zone_type; ?>",
                            r : r_value,
                            lysegs : l_value,
                            cat : "<?php echo $out_cats; ?>",
                        },
                    },
                    success : function( r ) {
                        if (r == '') {
                            $("#<?php echo $wrapper; ?>").addClass("empty");
                        } else {
                            $("#<?php echo $wrapper; ?>").append(r).addClass("active");
                            $("body").addClass("openx-active");
                        }
                        if (typeof callback == "function") {
                            callback();
                        }
                    },
                    error: function(errorThrown){
                        //alert(errorThrown);
                    }
                });
        <?php
            }
    }

    // Function placeholder for child theme to add own conditionals
    public function af_child_init_js() {}

    // Add custom editor style
    public function af_editor_style() {
       add_editor_style($this->af_get_child_theme_dir() . '/css/editor-style.css');
    }

    // Custom image sizes
    public function af_image_sizes() {
        add_image_size('article-thumb', 300, 300, true);
        add_image_size('article-hero', 630, 350, true);
        add_image_size('article-featured', 1000, 300, array('center', 'center'));
    }

    // Customized fallback excerpt options
    public function af_customize_excerpt() {
        // Modify excerpt length
        add_filter('excerpt_length', array($this, 'af_excerpt_length'));

        // Modify end of excerpt
        add_filter('excerpt_more', array($this, 'af_excerpt_more'));
    }

    // Excerpt length
    public function af_excerpt_length() {
        return 55;
    }

    // Excerpt suffix
    public function af_excerpt_more() {
        return '...';
    }

    // Add action to filter author page
    public function af_pre_get_posts() {
        add_action('pre_get_posts', array($this, 'af_filter_posts'));
    }

    // Remove specific categories from author pages
    public function af_filter_posts( $query ) {
        global $wp_query, $af_publications;

        if(!is_admin() && $query->is_main_query()) {

            // Filter search posts
            if($query->is_search && !isset($_GET['post_type'])) {

                $active_pubs = $af_publications->af_get_active_pub_ids();

                if( is_array($active_pubs) && !empty($active_pubs) ) {
                    $meta_query = array('relation'=>'OR');
                    foreach($active_pubs as $pub) {
                        $meta_query[] = array(
                                            'key' => 'publication',
                                            'value' => '"'.$pub.'"',
                                            'compare' => 'LIKE'
                                        );
                    }

                    // Add back in free posts
                    $meta_query[] = array(
                        'key' => 'publication',
                        'compare' => 'NOT EXISTS'
                    );
                    $meta_query[] = array(
                        'key' => 'publication',
                        'value' => false,
                        'compare' => 'BOOLEAN'
                    );

                }

                $query->set('cat', $this->af_get_excluded_search_cat_ids());
                $query->set('meta_query', $meta_query);
                $query->set('posts_per_page', 50);

            } elseif(is_author()) {

                // Category slugs to exclude
                $slugs = array('report', 'reports', 'book', 'books', 'help', 'getting-started', 'whats-new');
                $slugs = apply_filters('af_author_cat_filter', $slugs);
                $cat_ids = array();

                // Get IDs from slugs
                foreach ($slugs as $slug) {
                    $cat = get_category_by_slug($slug);
                    if ($cat == 0) {
                        continue;
                    }
                    $cat_ids[] .= $cat->term_id;
                }

                // Remove cats by ID
                $query->set('category__not_in', $cat_ids);
                remove_all_actions('__after_loop');

            } 
        }

    }

    // Rebuild the url for login referral
    public function af_get_url() {
        $server_name = isset($_SERVER["SERVER_NAME"]) && $_SERVER["SERVER_NAME"] ? $_SERVER["SERVER_NAME"] : '';
        $request_url = isset($_SERVER["REQUEST_URI"]) && $_SERVER["REQUEST_URI"] ? $_SERVER["REQUEST_URI"] : '';
        $url = '';

        if ($server_name) {
            $url  = isset($_SERVER["HTTPS"]) && @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$server_name :  'https://'.$server_name;
            $url .= $request_url;
        }
        return $url;
    }

    // Get array of all ACF fields for any post ID
    public function af_get_acf_fields($postID) {
        if (function_exists('get_fields')) {
            $fields = get_fields($postID);
        }
        return $fields;
    }

    // Set login redirect w/option to redirect if not set in query string
    public function af_get_login_redirect($slug = '') {
        $site_url = site_url();

        // Set redirect if part of query string
        if (isset($_GET['redirect_to'])){
            if (strpos($_GET['redirect_to'], $site_url) !== false){
                $redirect = $_GET['redirect_to'];
            } else {
                $redirect = site_url($_GET['redirect_to']);
            }
            return $redirect;
        }

        // If slug is set, direct to page
        if ($slug) {
            $path = get_page_by_path($slug);
            if ($path) {
                $redirect = get_permalink($path->ID);
                return $redirect;
            }
        }

        // Get referrer else home
        $origin = wp_get_referer();
        if ($origin) {
            $redirect = $origin;
            return $redirect;
        } else {
            $redirect = $site_url;
            return $redirect;
        }
        return;
    }

    // Forward visitor to login page with redirect to origin
    public function af_redirect_to_login($login = '/login/') {
        if (!is_user_logged_in()) {
            $return_url = add_query_arg(
                array (
                    'redirect_to' => $this->af_get_url()
                ), site_url($login)
            );
            wp_redirect($return_url);
            exit;
        }
        return;
    }

    // Get array of categories for free articles by slug
    public function af_get_free_article_cats() {
        $array = apply_filters( 'filter_free_article_cats', array( 'free-articles' ) );
        return $array;
    }

    // Get array of categories for to exclude from public archives
    public function af_get_excluded_cats() {
        $array = array('getting-started', 'report', 'book', 'issue', 'help');
        return apply_filters( 'af_get_excluded_cats', $array );
    }

    // Get array of categories for to exclude from paid archives
    public function af_get_excluded_paid_cats() {
        $array = array('getting-started', 'help');
        return apply_filters( 'af_get_excluded_paid_cats', $array );
    }

    // Get array of categories for to exclude from searches
    public function af_get_excluded_search_cats() {
        $array = array('getting-started', 'help');
        return apply_filters( 'af_get_excluded_search_cats', $array );
    }

    // Get array of negative (-) categories IDs for query args
    public function af_get_excluded_cat_ids( $slugs = array() ) {
        $array = array();
        $exclude_cats = empty($slugs) ? $this->af_get_excluded_cats() : $slugs ;
        foreach ($exclude_cats as $excluded_cat) {
            $cat_obj = get_category_by_slug($excluded_cat);
            if ($cat_obj == false) continue;
            $array[] = '-' . $cat_obj->term_id;
        }
        return $array;
    }

    // Get array of negative (-) categories IDs for paid archive
    public function af_get_excluded_paid_cat_ids() {
        return $this->af_get_excluded_cat_ids($this->af_get_excluded_paid_cats());
    }

    // Get array of negative (-) categories IDs for searches
    public function af_get_excluded_search_cat_ids() {
        return $this->af_get_excluded_cat_ids($this->af_get_excluded_search_cats());
    }

    public function af_get_static_asset_version() {
        if (defined('AF_STATIC_ASSET_VERSION')) {
            $version = AF_STATIC_ASSET_VERSION;
        } else {
            $version = false;
        }
        return $version;
    }


    public function tiny_mce_formats($settings) {
        // Remove H1 from WYSIWYG editors.
        $block_formats = [
            'Paragraph' => 'p',
            'Heading 2' => 'h2',
            'Heading 3' => 'h3',
            'Heading 4' => 'h4',
            'Heading 5' => 'h5',
            'Heading 6' => 'h6',
            'Preformatted' => 'pre'
        ];
        $block_formats_settings = '';
        foreach ($block_formats as $k => $v) {
            $block_formats_settings .= "$k=$v;";
        }
        $settings['block_formats'] = trim($block_formats_settings, '; ');
        return $settings;
    }
}
