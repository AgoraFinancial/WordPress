<?php
/**
 * Controls modifications/extensions of plugins
 */

class AF_Plugins extends AF_Theme {
    public function __construct() {
        $this->af_plugin_hooks();
        $this->af_sso_mc_params();
    }

    function __return_false() {
        return false;
    }

    // Set up plugin hooks
    public function af_plugin_hooks() {

        // Re-enable default custom fields for ACF 5.6+
        add_filter('acf/settings/remove_wp_meta_box', array($this, '__return_false'));
        add_filter('load_middleware_aggregate_data', array($this, 'load_aggregate_data'), 10, 3);

        // Move Yoast to bottom of edit page
        add_filter('wpseo_metabox_prio', function() {
            return 'low';
        });

        // Move GF scripts to footer
        add_filter( 'gform_init_scripts_footer', '__return_true' );

        // If only free articles, skip redirect
        if ( !defined( 'FREE_ARTICLES_ONLY' ) ) define( 'FREE_ARTICLES_ONLY', false );
        if ( !FREE_ARTICLES_ONLY ) {
            // Set login redirect to referrer for modal login
            add_filter('login_redirect', array($this, 'af_set_login_redirect'), 10, 3 );
            add_action('init', array($this, 'af_redirect_login_page'));
        }

        // Set login redirect to referrer for modal login
        add_action('wp_login_failed', array($this, 'af_login_fail_clear_cache'), 9, 1 );
        add_action('wp_login', array($this, 'af_login_success_clear_cache'), 11, 2 );

        // Remove MW filter and add custom one to accomodate modal login
        $login_page = isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/login/' ? true : false ;
        global $agora_mw_auth_plugin;
        if(!$login_page) {
            remove_filter( 'the_content', array($agora_mw_auth_plugin, 'content_filter'), 999);            
        }
        add_filter( 'af_modal_login', array($agora_mw_auth_plugin, 'content_filter'));

        // Modify request body for sparkpost email send 
        add_filter( 'wpsp_request_body', array( $this, 'af_filter_wpsp_request_body' ) );

    }

    // Maintain referrer link for modal redirect
    public function af_set_login_redirect( $redirect_to, $request, $user ) {
        $referrer = $this->af_strip_trailing_slash(wp_get_referer());
        // Follow redirect_to query string, if provided
        if(strpos($referrer, 'redirect_to=')) {
            $arr = explode('redirect_to=', $referrer);
            $redirect_to = $arr[1];
        } else {
            $home_page = $this->af_strip_trailing_slash(home_url());
            $login_page = $home_page . '/login';
            $choose_path = get_page_by_path('choose-path') ? $home_page . '/choose-path/' : $home_page;
            $choose_path = apply_filters('af_login_landing_page', $choose_path);
            // Redirect home page and login page to choose-path
            if($referrer == $home_page || $referrer == $login_page) {
                $redirect_to = $choose_path;
            } else {
                $redirect_to = $referrer;
            }
        }
        return $redirect_to;
    }

    // Helper - remove trailing slash from URL
    public function af_strip_trailing_slash( $url ) {
        $url = preg_replace( '{/$}', '', $url );
        return $url;
    }

    // Alter incoming MC unsub param links to match MW authentication plugin to allow sso
    public function af_sso_mc_params() {
        // Get current URL
        $url = $this->af_get_url();
        $slug = trim(parse_url($url, PHP_URL_PATH), '/');

        // Assign customer ID if has cookie for afadv
        $customer_id = isset($_COOKIE['afadv']) ? $_COOKIE['afadv'] : -1;

        // If user doesn't have a cookie and URL matches parameters, and is not login, redirect to login
        if ($slug != 'login' && isset($_GET['mid'], $_GET['oid'], $_GET['cid']) && $customer_id == -1 && !is_user_logged_in()) {
            $this->af_redirect_to_login();
        }

        // This code reassigns variables for MW tokenized login
        if (isset($_GET['mid']))
            $_GET['o'] = $_GET['mid'];
        if (isset($_GET['oid']))
            $_GET['a'] = $_GET['oid'];
        if (isset($_GET['cid']))
            $_GET['u'] = $customer_id;

        // If query includes MC query params
        if (isset($_GET['mid']) && isset($_GET['oid']) && isset($_GET['cid']) && isset($_GET['r'])) {

            // Reassign to MW plugin tokenized params
            $u = $_GET['u'];
            $a = $_GET['a'];
            $o = $_GET['o'];

            // Prep array to very MC cid against profiles
            $data = array(
                'token' => $_GET['vid'],
                'customer_id' => '$customer_id',
                'mid' => $_GET['mid'],
                'cid' => $_GET['cid'],
                'oid' => $_GET['oid'],
                'stack' => $_GET['r'],
            );

            $profiles = new \Agora_Profiles_Client();
            $request = $profiles->profiles_request($data, 'sso/validate-token', false);
            $request = json_decode($request, true);

            // If profiles comes back with a customer ID, match to cookie and if all good grant access
            if ((isset($request['valid']) && (boolean) $request['valid'] === false) && (isset($request['customer_id']) && $request['customer_id'] == $customer_id)) {

                // Remove r param to circumvent new MW flag from MW Auth Plugin
                if (isset($_GET['r'])) {
                    unset($_GET['r']);
                }

                // Create custom vid for MW Auth Plugin
                $tmp_vid = agora()->security->generate_vid($u, $a, $o);
                if ($_GET['vid']) {
                    $_GET['vid'] = $tmp_vid;
                }
            }
        }
        return;
    }

    // Redirect login.php to login for MW plugin
    public function af_redirect_login_page() {    

        // Ignore all but wp-login.php
        if ($GLOBALS['pagenow'] !== 'wp-login.php') return;

        // Check if login page exists
        $login_slug = 'login';
        $login_page = get_page_by_path($login_slug);

        if (null !== $login_page) {
            
            // Set vars
            $referrer = wp_get_referer();
            $referrer_slug = trim(parse_url($referrer, PHP_URL_PATH), '/');
            $login_url = get_permalink($login_page->ID);

            // Allow login from modal
            $has_log = isset($_POST['log']) ? true : false;
            $has_pwd = isset($_POST['pwd']) ? true : false;
            $ignore_redirect = $has_log && $has_pwd ? true : false ;
            $ignore_redirect = apply_filters( 'af_interrupt_login_redirect', $ignore_redirect );
            if ($ignore_redirect) return;

            // Allow for logout account confirmation
            if (isset($_POST['wp-submit']) || (isset($_GET['action']) && $_GET['action'] == 'logout') || (isset($_GET['checkemail']) && $_GET['checkemail'] == 'confirm') || (isset($_GET['checkemail']) && $_GET['checkemail'] == 'registered')) {
                return;
            }

            // Redirect from wp-login.php unless coming from /login/
            if ($login_slug != $referrer_slug) {                
                wp_redirect($login_url);
                exit;
            } else {
                return;
            }
            
        } else {
            return;
        }
    }

    // Append list subscription data to the MW aggregate_data user meta
    public function load_aggregate_data($aggregate_data, $username, $password) {
        // if(!$this->customer_number){
        //     $customer = agora()->mw->get_customer_by_login($username, $password);
        //     $this->customer_number = $customer->customerNumber;
        // }
        // $listSubscriptions  = agora()->mw->get_customer_list_signups_by_id($this->customer_number);

        $emailAddress = $aggregate_data->postalAddresses[0]->emailAddress->emailAddress;
        $listSubscriptions  = agora()->mw->get_customer_list_signups_by_email($emailAddress);

        if(is_wp_error($listSubscriptions)){
            return $aggregate_data;
        }elseif(is_wp_error($aggregate_data)){
            $result = new stdClass();
            $result->listSubscriptions = $listSubscriptions;
            return $result;
        }else{
            $aggregate_data->listSubscriptions = $listSubscriptions;
            return $aggregate_data;
        }
    }

    // Clear W3TC cache on login fail/success
    public function af_login_fail_clear_cache( $array ) {
        if (function_exists('w3tc_flush_posts')) {
            w3tc_flush_posts();
        }
    }

    // Clear W3TC cache on login fail
    public function af_login_success_clear_cache( $user_login, $user ) {
        if (function_exists('w3tc_flush_posts')) {
            w3tc_flush_posts();
        }
    }

    /**
     * Append a bounce subdomainreturn path for SparkPost plugin
     * Based on sending domain from admin settings
     * Example:
     * Sending address: noreply@userhelp.website.com
     * Return address: noreply@bounce.userhelp.website.com    
     * @param  array $array
     * @return array
     */
    public function af_filter_wpsp_request_body( $array ) {

        // Get sending address
        $sending_address = isset( $array['content']['from']['email'] ) ? $array['content']['from']['email'] : '' ;

        if ( $sending_address ) {

            // Separate email and domain
            $data = explode( '@', $sending_address );
            
            // Buiild and apply filters
            $email_name = apply_filters( 'af_wpsp_email_name', $data[0] );
            $sending_domain = apply_filters( 'af_wpsp_sending_domain', $data[1] );
            $bounce_subdomain = apply_filters ( 'af_wpsp_bounce_subdomain', 'bounce' );
            $bounce_address = apply_filters( 'af_wpsp_bounce_address', $email_name . '@' . $bounce_subdomain . '.' . $sending_domain );

            $array['return_path'] = $bounce_address;
        }

        return $array;
    }

}