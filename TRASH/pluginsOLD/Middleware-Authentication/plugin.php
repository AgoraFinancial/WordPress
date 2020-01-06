<?php
/*
	Plugin Name: Agora Middleware 2.x Authentication Plugin
	Plugin URI: https://github.com/ThreefoldSystems/Middleware-Authentication
	Description: Authentication plugin for middleware 2.0.
	Author: Threefold Systems
	Version: 1.18
	Author URI: http://threefoldsystems.com
	Text Domain: agora-middleware
	Domain Path: /lang
 */
include_once('classes/models/agora_auth_container.php');
include_once('classes/agora_authentication.php');
include_once('classes/agora_pubcode_authentication.php');
include_once('classes/agora_middleware_user.php');
include_once('classes/agora_login_widget.php');
include_once('classes/agora_facebook_login.php');
include_once('classes/agora_file_access.php');
include_once('classes/models/agora_authcode.php');
include_once('classes/models/agora_auth_rule.php');
include_once('classes/models/agora_advantage_item.php');
include_once('classes/models/agora_publication.php');
include_once('classes/models/agora_product.php');
include_once('classes/models/agora_access_maintenance_billing.php');
include_once('classes/agora_login_security.php');
include_once('classes/agora_reset_password.php');

// Only include WP_CLI stuff if we're running WP_CLI
if ( defined('WP_CLI') && WP_CLI ) {
    include_once('wp-cli/convert-middleware-auth.php');
}

/**
 * Middleware Authentication Plugin
 *
 * A wordpress plugin used to password protect content and authenticate users
 *
 * @package agora_middleware_authentication
 *
 */
class agora_authentication_plugin{
    /**
     * An object for the base middleware plugin
     * @var object
     */
    protected $core;

    /**
     * @var Pubcode Authentication object
     */
    public $pubcodes;

    /**
     * @var
     */
    public $input;

    /**
     * @var object
     */
    public $plugin_activation_option_name_auth = 'mw_auth_activation';

    /**
     *  Constructor Method.
     */
    public function __construct(){
        // This plugin depends on the base plugin being installed. It can't do anything without it.
        if($this->_dependency_check()) {
            // Set some useful options and stuff
            $this->core = agora_core_framework::get_instance();
            $this->input = new agora_input_framework($this->core);
            $this->plugin_basename = plugin_basename(__FILE__);
            $this->plugin_dir = dirname(__FILE__);
            $this->plugin_admin_page = 'agora-middleware-authentication';
            $this->plugin_settings_page = 'agora-authentication-settings';
            $this->plugin_support_center_page = 'agora-support-center';
            $this->config_name = 'agora-middleware-auth-config';
            $this->social_config_name = 'agora-middleware-social-config';
            $this->file_protection = new agora_file_access( $this->core );

            // Load the base config from file
            $base_config = parse_ini_file(dirname(__FILE__) . '/default_config.ini');
            $social_config = parse_ini_file(dirname(__FILE__) . '/default_social_config.ini');

            // Default options are kept in the default_config.ini
            $this->config = $this->core->wp->get_option($this->config_name, $base_config);
            $this->social_config = $this->core->wp->get_option($this->social_config_name, $social_config);

            // Set up dependencies
            $this->core->user = new agora_middleware_user($this->core, $this->config );
            $this->core->fb = new agora_facebook_login($this->core, $this->social_config);
            $this->reset_password = new agora_reset_password($this->core, $this->config);
            $this->pubcodes = new agora_pubcode_authentication(include_once(dirname(__FILE__) . '/config/authcode_config.php'));
            $this->core->authentication = $this->pubcodes;
            $this->core->security = new agora_login_security($this->core->mw, $this->config['rate_limiting']);

            // Add the theme folder from this plugin to the view framework.
            // Ideally the templates should be copied to the sites actual theme folder
            $this->core->view->set_template_path(dirname(__FILE__) . '/theme');
            $this->core->view->set_template_path($this->plugin_dir . '/views');

            // Initialize auto update class
            if ( method_exists( 'agora_core_framework', 'auto_update' ) ) {
                $this->core->auto_update(
                    'https://github.com/ThreefoldSystems/Middleware-Authentication',
                    __FILE__,
                    'master'
                );
            }

            // Wordpress API Hooks
            $this->_wordpress_hooks();
        }
    }

    /**
     * Function for Health check
     */
    public function check_health()
    {
        if ( ! isset( $this->config[ 'pinghome1' ] ) || $this->config[ 'pinghome1' ] < strtotime( '-1 week' ) ) {
            $this->config[ 'pinghome1' ] = strtotime( "now" );

            update_option( $this->config_name, $this->config );

            // Keen.io reporting
            $plugin_details = get_plugin_data(__FILE__, false);

            $this->core->keen_reporting(
                'Plugin Weekly Ping',
                array(
                    'action' => 'Weekly Ping',
                    'plugin_name' => $plugin_details[ 'Name' ],
                    'plugin_version' => $plugin_details[ 'Version' ]
                )
            );
        }
    }

    /**
     * Allow the use of one-time use login tokens.
     * Hooks to the init action in wordpress and picks up on URL variables to process the users login.
     *
     * Due to changes on Message Central the variables for tokenized logins have changed
     * a = oid = Org ID
     * o = mid = Mailing ID
     * u = cid = Contact ID
     *
     */
    public function tokenized_login(){
        if(!$this->input->get('u')) return;

        remove_filter('authenticate', array($this, 'catch_empty_login'));
        global $wp;

        if(empty($_SESSION['sso_request_url'])){
            if(empty($_SERVER['REDIRECT_URL'])) {
                // Nginx fix
                // Get the redirect URL manually, strip out SSO variables from it
                $full_url = site_url() . $_SERVER['REQUEST_URI'];

                $current_url = remove_query_arg(array('u', 'vid', 'o', 'a'), $full_url);
            } else{
                $_SESSION['sso_request_url'] = $_SERVER['REDIRECT_URL'];
                $current_url = $_SERVER['REDIRECT_URL'];
            }
        }else{
            $current_url = $_SESSION['sso_request_url'];
        }

        // We remove the 's' variable because Wordpress thinks it's a search but MC passes it along.
        $current_url = remove_query_arg(array('s'), $current_url);

        $validator = $this->input->get()->validate(array('u' => 'required|numeric', 'vid' => 'required|mc_login_token', 'a' => 'required', 'o' => 'required|numeric'));

        if($validator === true){
            // If link is generated from Message Central then lookup the customer number
            if ($this->input->get('r') && strpos($this->input->get('r'), 'MC') !== false) {
                $account = $this->core->mw->get_customer_number_by_contact_id_org_id_stack_name($this->input->get('u'), $this->input->get('a'), $this->input->get('r'));
                $customer_number = $account->customerNumber;
            // If link is generated from the single signon url then the customer number is assigned to the u variable
            } else {
                $customer_number = $this->input->get('u');
            }

            $users_found = $this->core->user->get_login_by_id($customer_number);

            foreach($users_found as $u){
                $user = get_user_by('login', $u->username);
                if($user){
                    $user_attempt = $u;
                    $this->core->user->wp_user = $user;
                    break;
                }
            }
            
            $creds = array();
            // If the user does not exist in the wordpress database then create an account 
            if(!isset($user_attempt)){
                $accounts = $this->core->mw->get_account_by_id($customer_number);
                if(!is_wp_error($accounts)){
                    foreach ($accounts as $a) {
                        // Get details if account is active and authType is email
                        if($a->authStatus === 'A' && $a->id->authType === 'E') {
                            $creds = array(
                                'user_login'    => $a->id->userName,
                                'user_password' => $a->password,
                                'remember'      => true,
                            );
                            break;
                        }
                    }
                }

            // If the token has already been used then send headers
            } elseif( $this->core->user->is_token_used($this->input->get('vid'), $this->input->get('o')) == true ) {
                add_action('send_headers', array($this, 'failed_token_login_header'));

            // If the user exists in the wordpress database then use these details
            } else {
                $creds = array(
                    'user_login'    => $user_attempt->username,
                    'user_password' => $user_attempt->password,
                    'remember'      => true,
                );
            }

            // Attempt to sign the user in
            $user = wp_signon( $creds );
            if ( is_wp_error($user) )
                $this->core->log->error($user->get_error_message());

            $this->core->user->wp_user = $user;

            // Write this token to the users account so we can't use it again.
            $this->core->user->set_used_token($this->input->get('vid'), $this->input->get('o'));

            // Bounce the user back to the originally requested page
            wp_redirect($current_url, 302);
            exit;
        }
    }
    /**
     * Support for Single Sign on.
     * Allows another site to link to this site and pass URL vars that we can validate and process the login for the user
     * Differs from the Tokenized login feature in that it uses Customer Number rather than Contact ID
     *
     * @deprecated deprecated since version 1.2.5.2
     */
    public function single_sign_on(){

        if(!$this->input->get('csid')) return;

        remove_filter('authenticate', array($this, 'catch_empty_login'));
        global $wp;

        if(empty($_SESSION['sso_request_url'])){
            $_SESSION['sso_request_url'] = $_SERVER['REDIRECT_URL'];
            $current_url = $_SERVER['REDIRECT_URL'];
        }else{
            $current_url = $_SESSION['sso_request_url'];
        }
        $validator = $this->input->get()->validate(array('csid' => 'required|numeric', 'vid' => 'required|login_token', 'oid' => 'required', 'mid' => 'required|numeric'));

        if($validator === true){
            $users_found = $this->core->user->get_login_by_id($this->input->get('csid'));
            foreach($users_found as $u){
                $user = get_user_by('login', $u->username);
                if($user){
                    $user_attempt = $u;
                    $this->core->user->wp_user = $user;
                    break;
                }
            }

            // Check to see if the current token was used before.
            $token_is_used = $this->core->user->is_token_used($this->input->get('vid'), $this->input->get('o'));
            if($token_is_used == false){
                // Build up the login credentials
                $creds = array(
                    'user_login'    => $user_attempt->username,
                    'user_password' => $user_attempt->password,
                    'remember'      => true,
                );
                // Attempt to sign the user in
                $user = wp_signon( $creds );
                if ( is_wp_error($user) )
                    $this->core->log->error($user->get_error_message());

                // Write this token to the users account so we can't use it again.
                $this->core->user->set_used_token($this->input->get('vid'), $this->input->get('o'));

                // Bounce the user back to the originally requested page
                wp_redirect($current_url, 302);
                exit;
            }else{
                // Add some response headers so we can see that the the token login failed.
                add_action('send_headers', array($this, 'failed_token_login_header'));
            }
        }
    }

    /**
     * Add a response header when a tokenized login failed.
     */
    function failed_token_login_header(){
        header( 'Middleware-Plugin: Login Failed, Token already used' );
    }

    /**
     * Function to handle login box shortcode
     *
     * @param   array $atts Attribute array from shortcode
     * @param   string  $content    Content string from the shortcode
     * @return void
     */
    function login_page_shortcode($atts, $content = ''){
        $this->core->view->set_template_path( dirname(__FILE__) . '/theme' );

        $get_username = ( isset( $_GET[ 'username' ] ) ? $this->reset_password->decrypt_password_reset_email_address( sanitize_text_field( $_GET[ 'username' ] ) ) : '' );
        $post_multi_data = ( isset( $_POST[ 'multi-data' ] ) ? sanitize_text_field( $_POST[ 'multi-data' ] ) : '' );
        $post_multi_username = ( isset( $_POST[ 'multi-username' ] ) ? sanitize_text_field( $_POST[ 'multi-username' ] ) : '' );

        $content = array(
            'title' => $this->core->get_language_variable('txt_login_title'),
            'forgot_username_link' => $this->core->get_language_variable('txt_forgot_username_link'),
            'forgot_password_link' => $this->core->get_language_variable('txt_forgot_password_link'),
            'forgot_password_link_short' => $this->core->get_language_variable('txt_forgot_password_link_short'),
            'forgot_link' => $this->core->get_language_variable('txt_forgot_link'),
            'message' => '',
            'subtitle' => $this->core->get_language_variable('txt_default_login_message'),
            'form_parameters' => array(
                'redirect' => home_url(),
                'label_username' => $this->core->get_language_variable('txt_login_username_label'),
                'label_log_in' => $this->core->get_language_variable('txt_login_button'),
                'label_password' => $this->core->get_language_variable('txt_login_password_label'),
                'label_remember' => $this->core->get_language_variable('txt_login_remember'),
                'value_username' => $get_username,
                'username_placeholder' => $this->core->get_language_variable('txt_login_username_placeholder'),
                'password_placeholder' => $this->core->get_language_variable('txt_login_password_placeholder')
            )
        );

        if ( $this->core->user->is_middleware_user() AND is_user_logged_in() ) {
            $content = array(
                'user_name'     => $this->core->user->get_name(),
                'message'       => $this->core->get_language_variable('txt_already_logged_in'),
                'welcome'       => $this->core->get_language_variable('txt_welcome'),
            );

            return $this->core->view->load('mw-user-logged-in', $content, true);
        } else {
            if ( ! empty( $post_multi_data ) && !empty( $post_multi_username ) ) {
                $content['message_class'] = 'error';
                $content['message'] = $this->core->get_language_variable('txt_fb_assign_error');
                $content['subtitle'] = $this->core->get_language_variable('txt_default_login_message');
            } else {
                $content['message_class'] = '';
                $content['message'] = '';
                $content['subtitle'] = $this->core->get_language_variable('txt_default_login_message');
            }

            return $this->core->view->load('mw-login-block', $content, true);
        }
    }

    /**
     * Function to handle customer full name shortcode
     */
    function customer_fullname_shortcode() {
        return $this->core->user->get_name();
    }

    /**
     * Function to handle customer first name shortcode
     */
    function customer_firstname_shortcode() {
        return $this->core->user->get_first_name();
    }

    /**
     * Function to handle customer email shortcode
     */
    function customer_email_shortcode() {
        return $this->core->user->_get_email();
    }

    /**
     * Function to handle customer number shortcode
     */
    function customer_number_shortcode() {
        return $this->core->user->get_customer_number();
    }


    /**
     * The main controller function for protected content
     *
     * Hooks into the 'the_content' hook and runs all the authentication functions to determine if the user is allowed to view content or not.
     * This function invokes the agora_middleware_check_permission filter which other plugins can hook into to allow or deny access
     *
     *
     * @param  object $content The post Content
     * @return object          The post content, modified.
     */
    public function content_filter( $content ) {
        if ( apply_filters( 'mw_current_user_can_access', false ) ) {
            return $content;
        }

        $data = array();
        $data['form_parameters'] = array(
            'label_username' => $this->core->get_language_variable('txt_login_username_label'),
            'label_log_in' => $this->core->get_language_variable('txt_login_button'),
            'label_password' => $this->core->get_language_variable('txt_login_password_label'),
            'label_remember' => $this->core->get_language_variable('txt_login_remember'),
            'value_username' => '',
            'redirect' => site_url() . $_SERVER['REQUEST_URI'],
            'username_placeholder' => $this->core->get_language_variable('txt_login_username_placeholder'),
            'password_placeholder' => $this->core->get_language_variable('txt_login_password_placeholder')
        );

        $flash_message = $this->core->session->flash_message('login');
        $is_widget = $this->core->session->flash_message('is_widget');

        if ( $flash_message  AND !$is_widget ){
            $data['title'] = $this->core->get_language_variable('txt_login_title');
            $data['forgot_username_link'] = $this->core->get_language_variable('txt_forgot_username_link');
            $data['forgot_password_link'] = $this->core->get_language_variable('txt_forgot_password_link');
            $data['forgot_password_link_short'] = $this->core->get_language_variable('txt_forgot_password_link_short');
            $data['message']        = $flash_message->message;
            $data['message_class']  = $flash_message->class;
            $data['subtitle'] = $this->core->get_language_variable('txt_default_login_message');

            $result = $this->get_teaser($content);
            $result .= $this->core->view->load('mw-login-block', $data, true);
            return $result;

        } else {
            /**
             * This plugin and others can hook into the agora_middleware_check_permission filter to allow or deny access.
             */
            global $post;

            $auth_container = new agora_auth_container($post->ID);

            $auth_container = apply_filters( 'agora_middleware_check_permission', $auth_container);

            if( $auth_container->is_allowed() !== true AND $auth_container->is_protected() === true ){
                $data['title'] = $this->core->get_language_variable('txt_login_title');
                $data['forgot_username_link'] = $this->core->get_language_variable('txt_forgot_username_link');
                $data['forgot_password_link'] = $this->core->get_language_variable('txt_forgot_password_link');
                $data['forgot_password_link_short'] = $this->core->get_language_variable('txt_forgot_password_link_short');

                $data['auth_bucket']    = $auth_container;

                $data['message_class'] = '';
                $data['message'] = '';
                $data['subtitle'] = $this->core->get_language_variable('txt_default_login_message');

                $result = $this->get_teaser($content);
                $result .= $this->core->view->load('mw-login-block', $data, true);
                return $result;
            }else{
                return $content;
            }
        }

        return $content;
    }

    /**
     * @param $content
     *
     * @return string
     */
    private function get_teaser($content){
        switch ($this->config['teaser']){
            case 'excerpt':
                global $post;
                return $post->post_excerpt;
                break;
            case 'more_tag':
                $more_at = strpos($content, '<span id="more-');
                if($more_at !== false){
                    return substr($content, 0, $more_at);
                }
                break;
            case 'none':
                return '';
        }
    }


    /**
     * tfs_change_pwd
     *
     * Detect if password used starts with a specific temporary prefix and set transient
     * On next load if there's a signed in user and transient redirect to temporary password reset
     *
     */
    public function tfs_change_pwd()
    {
        if( !session_id() ) {
            session_start();
        }

        $post_pwd = ( isset( $_POST[ 'pwd' ] ) ? $_POST[ 'pwd' ] : '' );

        //Check if the password needs to be changed
        if ( $post_pwd && strtolower( substr( trim( $post_pwd ), 0, 6 ) ) == "nd312_" ) {
            $_SESSION["tfs_change_pwd"] = 1;
        } else {
            if (isset( $_SESSION["tfs_change_pwd"])) {
                if (is_user_logged_in() && !is_admin()) {
                    $user = wp_get_current_user();
                    $login = $this->core->user->get_login_by_email( $user->data->user_email );

                    $transName = $this->core->tfs_hash( 'PR_' . $user->data->user_email );
                    set_transient($transName, $login, 1 * HOUR_IN_SECONDS);

                    $redirect = home_url(). '/login/temporary-password?mode=temp&teue=' . $this->reset_password->encrypt_password_reset_email_address( $user->data->user_email ) . '&t=' . $transName;

                    if(defined("REDIRECT_PASSWORD")){
                        $redirect = $redirect . '&rd=' . REDIRECT_PASSWORD;
                    }


                    unset( $_SESSION["tfs_change_pwd"]);
                    session_write_close();
                    wp_redirect($redirect);
                    exit();
                } else {
                    //if there's no logged in user then delete the transient
                    unset( $_SESSION["tfs_change_pwd"]);
                }
            }
        }
    }



    /**
     * process_login
     *
     * @param $username
     * @param $password
     */
    public function process_login( $username, $password ) {
        if ( isset( $this->clean_username ) ) {
            $username = $this->clean_username;
        }

        $this->core->security->handle_login();

        $post_is_widget = ( isset( $_POST[ 'is_widget' ] ) ? sanitize_text_field( $_POST[ 'is_widget' ] ) : '' );
        $post_redirect_to = ( isset( $_POST[ 'redirect_to' ] ) ? sanitize_text_field( $_POST[ 'redirect_to' ] ) : '' );

        if($post_is_widget){
            $this->core->session->flash_message('is_widget', 'true');
        }
        // Ignore login attempts for the 'admin' user. WP will take care of this itself.
        if($username AND $password AND $username !== 'admin'){
            // First grab the user by login so we can see what they are
            $user = get_user_by('login', preg_replace("/[^\w@.-]/", '', $username));
            // Check to see if the current login attempt is for an admin user
            // We must also allow for MW users who have never logged in before.
            if(!$user OR !$user->has_cap('administrator')) {
                $this->core->user->initialize_user($username, $password);
                if($this->core->user->is_middleware_user()){
                    $request_url = addslashes( $post_redirect_to );
                    $login_event_params = array(
                        'cvi' => $this->core->user->get_cvi_number(),
                        'customer_number' => $this->core->user->get_customer_number(),
                        'authgroup' => $this->core->user->get_authgroup(),
                        'url' => $request_url
                    );
                    apply_filters('agora_middleware_login_event', $login_event_params);
                }
            }
        }
    }


    /**
     * Function to catch empty username or password fields
     * @param  object $user WP User object
     * @return void
     */
    function catch_empty_login( $user ){
        // check what page the login attempt is coming from
        $referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;
        $error = false;

        $post_pwd = ( isset( $_POST[ 'pwd' ] ) ? $_POST[ 'pwd' ] : '' );
        $post_log = ( isset( $_POST[ 'log' ] ) ? sanitize_text_field( $_POST[ 'log' ] ) : '' );

        if ( ! isset( $post_pwd, $post_log ) || $post_log == '' || $post_pwd == '' ) {
            $error = true;
        }

        // check that were not on the default login page
        if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && $error ) {

            $this->core->session->flash_message('login', $this->core->get_language_variable('txt_failed_login'), 'error');
            wp_redirect( $referrer );
            exit;
        }
    }

    /**
     *	Activation Method
     *
     *	@param void
     *	@return void
     **/
    public function activation(){
        // Pick out the default language vars from the ini file and set them up
        $default_variables = parse_ini_file(dirname(__FILE__) . '/default_variables.ini');
        $this->core->register_language_variables( $default_variables );

        // Update activation option in the database
        update_option( $this->plugin_activation_option_name_auth, 'activated' );

        // Create the two placeholder pages we're going to need
        $login_page_id = $this->core->_create_page( 'login','Login', '[agora_middleware_login]');
        $forgot_password_page_id = $this->core->_create_page('forgot-password', 'Forgot Password', '[agora_middleware_forgot_password]', $login_page_id );
        $temporary_password_page_id = $this->core->_create_page('temporary-password', 'Temporary Password', '[agora_middleware_forgot_password]', $login_page_id );

        $notices= $this->core->wp->get_option('agora_mw_deferred_admin_notices', array());
        $notices[]= __('Middleware Authentication Plugin Activated');
        $this->core->wp->update_option('agora_mw_deferred_admin_notices', $notices);

        // Keen.io reporting
        $plugin_details = get_plugin_data(__FILE__, false);

        $this->core->keen_reporting(
            'Plugin Activations',
            array(
                'action' => 'Plugin Activated',
                'plugin_name' => $plugin_details[ 'Name' ],
                'plugin_version' => $plugin_details[ 'Version' ]
            )
        );
    }

    /**
     *	Deactivation Method
     *
     *	@param void
     *	@return void
     **/
    public function deactivation() {
        delete_option( $this->plugin_activation_option_name_auth );

        // Keen.io reporting
        $plugin_details = get_plugin_data(__FILE__, false);

        $this->core->keen_reporting(
            'Plugin De-Activations',
            array(
                'action' => 'Plugin De-Activated',
                'plugin_name' => $plugin_details[ 'Name' ],
                'plugin_version' => $plugin_details[ 'Version' ]
            )
        );
    }

    /**
     *	Modifying htaccess, add/remove 'MiddleWare Authentication Rewrite' rules.
     *
     *	@param void
     *	@return void
     **/
    public function modify_htaccess_write(){
        // Get path to main .htaccess for WordPress

        // When updating WP, get_home_path() function gets unloaded, crashing the site.
        if(function_exists('get_home_path') && function_exists('insert_with_markers')) {
            $htaccess_file = get_home_path().".htaccess";

            $htaccess_content = array();

            // If auth_rewrite_htaccess is set, write rules
            if(isset($this->config['auth_rewrite_htaccess']) && $this->config['auth_rewrite_htaccess']) {
                $htaccess_content[] = "
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteRule ^(.*/)?files/(.*).pdf index.php?file_path=%{REQUEST_URI} [L]
	RewriteRule ^(.*/)?uploads/(.*).pdf index.php?file_path=%{REQUEST_URI} [L]
</IfModule>
				";
            }
            else
            {
                $htaccess_content[] = "";
            }

            insert_with_markers($htaccess_file, "MiddleWare Authentication Rewrite", $htaccess_content);
        }
    }


    /**
     *	Helper method to figure out if the required plugin(s) are active
     *
     *	@param void
     *	@return void
     **/
    function _dependency_check(){
        if(!class_exists('agora_core_framework')){

            // For some reason this isn't automatically included at runtime of this particular function.
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            add_action( 'all_admin_notices', array($this, 'dependent_plugin_admin_error'));
            return false;
        }else{
            return true;
        }
    }

    public function dependent_plugin_admin_error() {
        echo '<div id="message" class="error"><p>'. __( 'The Authentication Plugin requires the Base plugin to work. Authentication will not function without it' ) .'</p></div>';
    }


    /**
     * Function to add menu item(s) to the Admin menu
     *
     * @param void
     * @return void
     */
    public function initialize_menu(){

        add_submenu_page(
            $this->core->plugin_admin_page,
            __('Agora Middleware 2.x Authentication'),
            __('Authentication Codes'),
            'manage_options',
            $this->plugin_admin_page,
            array($this, 'admin_page'));

        add_submenu_page(
            $this->core->plugin_admin_page,
            __('Agora Middleware 2.x Authentication'),
            __('Settings'),
            'manage_options',
            $this->plugin_settings_page,
            array($this, 'settings_page'));

        add_submenu_page(
            $this->core->plugin_admin_page,
            __('Support Center'),
            __('Support Center'),
            'manage_options',
            $this->plugin_support_center_page,
            array($this, 'support_center_page'));
    }

    /**
     * Function for Settings Link in plugins admin
     * @param array $links An array of plugin-related links generated by wordpress
     * @return array
     */
    public function get_settings_link($links) {
        $admin_link = '<a href="admin.php?page='. $this->plugin_admin_page .'">Authentication Codes</a>';
        $settings_link = '<a href="admin.php?page='. $this->plugin_settings_page .'">Settings</a>';
        $links[] = $admin_link;
        $links[] = $settings_link;
        return $links;
    }

    /**
     *	Admin menu handler
     *
     *	@param void
     *	@return void
     **/
    function admin_page(){
        $content = array(
            'menuItems'         => apply_filters('agora_middleware_admin_menu', array() ),
            'plugin_admin_page' => $this->plugin_admin_page,
        );

        $this->core->view->load('admin_header', $content);
        do_action('auth_plugin_admin_page');
        $this->core->view->load('admin_footer');
    }

    /**
     *	Support center menu handler
     *
     *	@param void
     *	@return void
     **/
    function support_center_page(){
        $content = array(
            'menuItems'         => apply_filters('agora_middleware_admin_menu', array() )
        );

        $this->core->view->load('admin_header', $content);
        do_action('auth_plugin_support_center_page');
        $this->core->view->load('admin_footer');
    }

    /**
     *	Settings menu handler
     *
     *	@param void
     *	@return void
     **/
    function settings_page(){
        $content = array(
            'menuItems'         => apply_filters('agora_middleware_admin_menu', array() ),
            'plugin_settings_page' => $this->plugin_settings_page,
        );

        $this->core->view->load('admin_header', $content);
        do_action('auth_plugin_settings_page');
        $this->core->view->load('admin_footer');
    }

    /**
     *  Show contextual help information when on the Authentication admin tab.
     */
    public function admin_help_tab(){
        $screen = get_current_screen();

        $tabs = array(
            array(
                'title'    => 'Authcodes',
                'id'       => 'authcode_help_tab',
                'content'  => $this->core->view->load('auth_help_tab_content', null, true)
            ),
            array(
                'title'    => 'Rules',
                'id'       => 'authcode_rules_tab',
                'content'  => $this->core->view->load('rules_help_tab_content', null, true)
            )
        );
        foreach($tabs as $tab){
            $screen->add_help_tab($tab);
        }
    }

    /**
     * Display the authcode admin panel.
     */
    public function authcode_admin(){
        $content = array(
            'all_pubcodes'  => $this->core->authentication->get_all_authcodes(),
            //'fields'        => $this->rule_fields,
            'auth_types'    => $this->core->authentication->get_auth_types()
        );
        $this->core->view->load('authcodes_admin', $content);
    }

    /**
     * Display the support center admin panel.
     */
    public function support_center() {
        $this->core->view->load( 'support_center' );
    }

    /**
     * Function to display admin notices.
     * @param void
     * @return void
     *
     **/
    function _error_notice(){
        echo '<div class="updated"><p>'.
            __('There was an error activating the Authentication plugin. Check for dependent plugins and try again')
            .'</p></div>';
    }

    /**
     * Authentication plugin activation issue.
     */
    public function authentication_plugin_activation_issue()
    {
        echo '<div id="message" class="error"><p>'. __( 'An error occurred while activating Authentication Plugin, please reactivate it manually.' ) .'</p></div>';
    }

    /**
     *	Function to enqueue scripts and styles for admin page
     *
     *	@param string $hook
     *	@return void
     **/
    function _admin_enqueue($hook){
        // Don't enqueue stuff if we're not on the right page.
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style( 'wp-jquery-ui-accordion', plugin_dir_url( __FILE__ ) . '/css/jquery-ui-css.css');
        wp_enqueue_style( $this->plugin_basename . 'styles_remodal', plugin_dir_url( __FILE__ ) . '/css/remodal.css' );

        if ($hook == 'post.php' || $hook == 'post-new.php') {
            wp_enqueue_style($this->plugin_basename . 'styles', plugin_dir_url(__FILE__) . '/css/pubcode-picker.css');
        }
        wp_enqueue_script( $this->plugin_basename . '_post_remodal', plugin_dir_url( __FILE__ ) . '/js/remodal.js', array('jquery'));
        wp_enqueue_script( $this->plugin_basename . '_post', plugin_dir_url( __FILE__ ) . '/js/agora_post_edit.js', array('jquery'));

        wp_enqueue_script( $this->plugin_basename . '_fast-select-standalone', plugin_dir_url( __FILE__ ) . '/js/fastselect.standalone.min.js', array('jquery'));
        wp_enqueue_script( $this->plugin_basename . '_fast-select', plugin_dir_url( __FILE__ ) . '/js/fastselect.min.js', array('jquery'));

        if(isset($this->config['mw_authcode_show']) && $this->config['mw_authcode_show'] === "0") {
            wp_enqueue_style( $this->plugin_basename . '_hide', plugin_dir_url( __FILE__ ) . '/css/hide-item.css' );
        }

        if(!strpos($hook, $this->plugin_admin_page) && !strpos($hook, $this->plugin_settings_page) && !strpos($hook, $this->plugin_support_center_page) )
            return;
        wp_enqueue_script( $this->plugin_basename . '_admin', plugin_dir_url( __FILE__ ) . '/js/agora_authentication_admin.js', array('jquery'));
        wp_enqueue_style( $this->plugin_basename . 'styles', plugin_dir_url( __FILE__ ) . '/css/authentication_admin.css');
        wp_localize_script( $this->plugin_basename .'_admin', 'agora_middleware_authentication', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'rule_fields' => $this->pubcodes->field_structure ) );
    }

    /**
     * Middleware enqueue scripts for front end.
     */
    function mw_enqueue_scripts_front() {
        // Will write customer information into header for use with google analytics/other js.
        // Check if user is middleware user, add his details into js file
        if($this->core->user->is_middleware_user() && !empty($this->config['show_user_js'])) {
            $mw_data = array(
                'agora_customer_firstname' => $this->customer_firstname_shortcode(),
                'agora_customer_fullname' => $this->customer_fullname_shortcode(),
                'agora_customer_email' => $this->customer_email_shortcode(),
                'agora_customer_number' => $this->customer_number_shortcode()
            );

            // Register the script
            // This will just be an empty script that we append variables to
            wp_register_script($this->plugin_basename . '_agora_cd', plugin_dir_url( __FILE__ ) . '/js/agora_customer_details.js');
            wp_localize_script($this->plugin_basename . '_agora_cd', 'mw_customer_data', $mw_data);
            wp_enqueue_script($this->plugin_basename . '_agora_cd');
        }

        wp_register_script('localized_frontend_data', plugin_dir_url( __FILE__ ) . 'js/frontend-localized.js' );
        wp_localize_script('localized_frontend_data', 'localized_frontend_data', $this->localize_frontend_data());
        wp_enqueue_script('localized_frontend_data', array( 'jquery' ));

        wp_enqueue_script('mw_auth_validate', plugin_dir_url( __FILE__ ) . 'js/jquery.validate.min.js', array( 'jquery' ));
        wp_enqueue_script('mw_auth_frontend', plugin_dir_url( __FILE__ ) . 'js/frontend.js', array( 'jquery', 'mw_auth_validate' ));

        wp_enqueue_style( 'mw_auth_frontend', plugin_dir_url( __FILE__ ) . 'css/frontend.css');
    }

    /**
     * Function to redirect the user after they log out. Overrides the default WP behaviour of redirecting the user back to wp-login.php
     * @param  string $url Optional url string to redirect to
     * @return void
     */
    public function redirect_after_logout($url = null){
        if(!$url)
            $url = home_url();

        wp_redirect( $url );

        exit();
    }

    /**
     *
     */
    public function general_authentication_settings(){
        $content = array(
            'config' => $this->config,
            'config_name' => $this->config_name
        );
        $this->core->view->load('general_options', $content);
    }

    /**
     *
     */
    public function fb_login_settings(){
        $content = array(
            'social_config' => $this->social_config,
            'social_config_name' => $this->social_config_name
        );
        $this->core->view->load('fb_login', $content);
    }

    /**
     * Function to check if the current post has pub codes and if so, disable caching
     * @return void
     */
    public function disable_cache(){
        do_action('agora_authentication_disable_cache');
    }

    /**
     * Handle a failed login
     * @param  object $user
     */
    function login_failed( $user ) {
        // check what page the login attempt is coming from
        $referrer = $_SERVER['HTTP_REFERER'];
        $this->core->security->failed_login();
        // check that were not on the default login page
        if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && $user!=null ) {
            // make sure we don't already have a failed login attempt
            $m = $this->core->session->flash_message('login');
            if($this->core->session->flash_message('login') == false){
                $failed_login_message = $this->core->get_language_variable('txt_invalid_username_or_password');

                // Check if user has reset their password within the last 15 minutes and show different message
                $trans_name_email = $this->core->tfs_hash( 'tfs_prs_email_' . strtolower( $user ) );
                $trans_name_username = $this->core->tfs_hash( 'tfs_prs_username_' . strtolower( $user ) );

                if ( ! empty( get_transient( $trans_name_email ) ) || ! empty( get_transient( $trans_name_username ) ) ) {
                    $failed_login_message = $this->core->get_language_variable('txt_invalid_username_or_password_after_pr');
                }

                $this->core->session->flash_message('login', $failed_login_message, 'error');
            }

            if ($url = parse_url($referrer)) {
                $redirectto = sprintf ('%s://%s%s', $url['scheme'], $url['host'], $url['path']);
            }

            if(isset($this->config['cache_buster']) && $this->config['cache_buster']) {
                $cache_bust = $this->core->tfs_hash(time());
                wp_redirect($redirectto . '?login=failed&cache_bust='.$cache_bust);
            }
            else
            {
                wp_redirect($redirectto);
            }
            exit;
        }
    }

    /**
     * Admin Init stuff.
     * @return void
     */
    public function admin_initialize() {
        /**
         * Redirect non admin users away from the dashboard
         */
        if ( ! apply_filters( 'mw_current_user_can_access', false ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            wp_redirect(home_url());
            exit;
        }

        register_setting($this->config_name . '_group', $this->config_name);
        register_setting($this->social_config_name.'_group', $this->social_config_name);
    }

    /**
     * Add a tab for this plugin to the admin menu
     * @param $menu
     * @return array
     */
    public function add_tab_item($menu){
        $menu[] = array('title' => __('Authentication Codes'), 'page' => $this->plugin_admin_page);
        $menu[] = array('title' => __('Settings'), 'page' => $this->plugin_settings_page);
        $menu[] = array('title' => __('Support Center'), 'page' => $this->plugin_support_center_page);
        return $menu;
    }

    /**
     * Add meta tags
     */
    public function add_meta_tags()
    {
        // Noindex meta tag for protected content
        if ( $this->pubcodes->is_protected_content() ) {
            $this->core->view->load('post_head_content');
        }

        // Pubcodes meta tag for protected content
        if ( $this->config[ 'header_meta_tag_pubcodes' ] === "1" ) {
            global $post;

            if ( isset( $post ) ) {
                $post_pubcodes = $this->pubcodes->get_post_authcodes( $post->ID );

                if ( is_array( $post_pubcodes ) ) {
                    $list_post_pubcodes = '';

                    foreach ( $post_pubcodes as $post_pubcode ) {
                        if ( $post_pubcode->name ) {
                            $list_post_pubcodes .= $post_pubcode->name . ', ';
                        }
                    }

                    if ( $list_post_pubcodes ) {
                        // Remove last comma from the list
                        $list_post_pubcodes = rtrim( $list_post_pubcodes, ', ' );

                        $this->core->view->load( 'post-head-pubcodes', $list_post_pubcodes );
                    }
                }
            }
        }
    }

    /**
     * If enabled, allow multiple users to share the same username
     * @param $user_email
     * @return mixed
     */


    public function skip_email_exist($user_email){
        define( 'WP_IMPORTING', 'SKIP_EMAIL_EXIST' );
        return $user_email;
    }


    /**
     * Remove '+' symbols from email after the plugin is finished but before WP tries to authenticate
     *
     * @param $username
     * @param $raw_username
     * @param $strict
     * @return mixed
     */
    function sanitize_user_email($username, $raw_username, $strict) {
        $new_username = preg_replace('/[\+]/', '', $raw_username);
        return $new_username;
    }

    /**
     * This is super ugly but for some reason advantage allows apostrophes in the username...
     *
     */
    function clean_user_credentials(){
        $post_log = ( isset( $_POST[ 'log' ] ) ? sanitize_text_field( $_POST[ 'log' ] ) : '' );
        $request_log = ( isset( $_REQUEST[ 'log' ] ) ? sanitize_text_field( $_REQUEST[ 'log' ] ) : '' );

        if(! empty($post_log) AND strpos($post_log, "'")) {
            //we still need the apostrophe if there is one to authenticate with mw so keep it
            $this->clean_username = stripslashes($post_log);
            //strip out the garbage so WP can authenticate
            $_POST['log'] = preg_replace("/[^A-Za-z0-9 ]/", '', $post_log);
            $_REQUEST['log'] = preg_replace("/[^A-Za-z0-9 ]/", '', $request_log);
        }
    }

    /**
     * add_middleware_shortcode_picker
     *
     * Add "MW Shortcodes" button/modal
     */
    function add_middleware_shortcode_picker() {
        return $this->core->view->load('shortcode-picker', true);
    }

    /**
     * auto_login
     *
     * Auto login based on username in a transient - Used with the reset password functionality
     */
    public function auto_login(){

        if( !session_id() ) {
            session_start();
        }

        if(isset($_SESSION["auto_login"])) {
            $account = $_SESSION["auto_login"];
            //Set post vars to trick catch_empty_login()
            $_POST['log'] = $account->id->userName;
            $_POST['pwd'] = $account->password;
            //build crediential array for wp_signon()
            $creds = array();
            $creds['user_login'] = $account->id->userName;
            $creds['user_password'] = $account->password;
            $creds['remember'] = true;
            $user = wp_signon($creds, false);
            unset($_SESSION["auto_login"]);
            if (is_wp_error($user)){
               $this->auto_login_feedback('fail');
               return;
            }
            $this->auto_login_feedback('success');
            wp_set_current_user( $user->ID, $user->user_login );
        } elseif ( isset($_SESSION['auto_login_fail'])) {
            unset($_SESSION["auto_login_fail"]);
            $this->auto_login_feedback('fail');
        }
    }

    /**
     * auto_login_feedback
     *
     * @param null $status
     */
    public function auto_login_feedback ($status = null ) {
        switch ($status) {
            case 'success':
                $message = $this->core->get_language_variable('txt_auto_login_success');
                break;
            case 'fail':
                $message = $this->core->get_language_variable('txt_auto_login_fail');
                break;
        }

        if( !empty( $message ) && isset( $_SESSION['secure_login_reset_link'] ) ) {
            echo '<div class="mw_auto_login_feedback"><p>'. $message .'. <a href="' . $_SESSION['secure_login_reset_link'] . '">Click here</a> to reset your password</p></div>';
            unset($_SESSION['secure_login_reset_link']);
        } else {
            echo '<div class="mw_auto_login_feedback">'.$message.'</div>';
        }

    }

    /**
     * prevent_auth_expiry
     *
     * extend authentication expiry to keep user sessions open
     *
     * @param $expire
     * @return int
     */
    public function prevent_auth_expiry($expire ) {
        return 20 * YEAR_IN_SECONDS; // 20 years in seconds
    }

    /**
     * failed_login_email
     *
     */
    public function failed_login_email() {
        $request_log = sanitize_text_field( $_REQUEST[ 'log' ] );

        if(empty($request_log)) {
            return;
        }
        if ($user = get_user_by( 'login', $request_log ) ){
            $user_email = $user->data->user_email;

            if($mw_user = $this->core->mw->get_account_by_email($user_email)){
                $trans_name = $this->core->tfs_hash( 'login_attempt_' . $user_email );

                $number_of_attempts = 0;

                if(get_transient($trans_name) !== false) {
                    $number_of_attempts = get_transient($trans_name);
                    if($number_of_attempts >= $this->config['failed_login_number'] - 1) {
                        delete_transient($trans_name);
                        $this->trigger_failed_attempts_email($user_email);
                        return;
                    }
                }
                $number_of_attempts ++;
                set_transient($trans_name, $number_of_attempts, 0.5 * HOUR_IN_SECONDS);
            }
        }
    }

    /**
     * trigger_failed_attempts_email
     *
     * @param $user_email
     */
    private function trigger_failed_attempts_email($user_email) {
        $email_address = addslashes($user_email);
        $login = $this->core->user->get_login_by_email( $email_address );
        $login = apply_filters('agora_mw_lost_password', $login);

        // create unique transient
        $transName = $this->core->tfs_hash( 'PR_' . $user_email );

        set_transient($transName, $login, 0);

        $post_user_email = ( isset( $_POST[ 'log' ] ) ? sanitize_text_field( $_POST[ 'log' ] ) : '' );

        $snippet = home_url(). '/login/forgot-password?teue=' . $this->reset_password->encrypt_password_reset_email_address( $post_user_email ) . '&t=' . $transName;

        $this->reset_password->send_email( $snippet, $email_address, 'triggered' );
    }

    /**
     *   Localize frontend data
     */
    private function localize_frontend_data() {
        return array(
            'mw_ajax_url' => admin_url( 'admin-ajax.php' )
        );
    }

    /**
     * Check of current user should be given access
     */
    public function mw_current_user_can_access( $allow = false ) {
        // If 'false' is passed in, check access manually
        if ( $allow === false ) {
            if ( current_user_can( 'edit_posts' ) ) {
                return true;
            } else {
                return false;
            }
        }

        // Something else other than 'false' is passed in
        // Filter has been applied and access has been checked manually - return the paramater back
        return $allow;
    }

    /**
     *  Keep all the wordpress hooks in here.
     */
    private function _wordpress_hooks(){
        register_activation_hook( __FILE__, array($this, 'activation' ) );
        register_deactivation_hook( __FILE__, array($this, 'deactivation' ) );

        /*
        * If this plugin is active, but there has been no option saved in the database but the option has been saved for
         * base plugin - deactivate auth plugin. Bug with activating both plugins at the same time.
        */
        if ( ! get_option( $this->plugin_activation_option_name_auth ) && get_option( 'mw_base_activation' ) ) {
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            add_action( 'all_admin_notices', array($this, 'authentication_plugin_activation_issue'));

            deactivate_plugins( plugin_basename( __FILE__ ) );

            return false;
        }

        /**
         * Check of current user should be given access
         */
        add_filter( 'mw_current_user_can_access', array( $this, 'mw_current_user_can_access' ) );

        /**
         * If duplicate users are enabled, fire this filter
         */
        if($this->config['dup_user']){
            add_filter('pre_user_email', array($this, 'skip_email_exist') );
        }

        /**
         * If Write authentication rules into .htaccess, fire this filter
         */
        add_filter('generate_rewrite_rules', array($this, 'modify_htaccess_write') );

        /**
         * Add tags to header
         */
        add_action('wp_head', array($this,'add_meta_tags'));

        /**
         * Handle the authentication hooks & events
         */
        add_action( 'wp_authenticate', array($this, 'process_login'), 1, 2);
        add_action( 'authenticate', array($this, 'catch_empty_login') );

        /**
         * Add facebook integration
         */
        if ( $this->social_config['fb_login_enable'] && $this->social_config['fb_app_id'] && $this->social_config['fb_api_version'] && $this->social_config['fb_app_secret'] ) {
            add_action('init', array($this->core->fb, 'fb_login_integration'));
            add_action('wp_login', array($this->core->fb, 'fb_usermeta'),100);
            add_filter( 'fb_login_redirect', array( $this->core->fb, 'fb_login_set_redirect' ) );

        }

        /**
         * Set up the user object on WP init
         */
        add_action( 'init', array($this->core->user, 'load_user') );

        /**
         * Disable Caching for protected posts
         */
        add_action('init', array($this, 'disable_cache'));

        /**
         * Some setup stuff for the plugin
         */
        add_action( 'admin_menu', array( $this, 'initialize_menu') );
        add_action( 'admin_enqueue_scripts', array($this, '_admin_enqueue') );
        add_action( 'admin_init', array( $this, 'admin_initialize') );
        add_action( 'auth_plugin_admin_page', array( $this, 'authcode_admin'));
        add_action( 'auth_plugin_support_center_page', array( $this, 'support_center'));
        add_action( 'auth_plugin_settings_page', array( $this, 'fb_login_settings'));
        add_action( 'auth_plugin_settings_page', array( $this, 'general_authentication_settings'));
        /**
         * Hiding the WP login page from the users
         */
        add_action( 'wp_logout', array($this, 'redirect_after_logout') );
        add_action( 'wp_login_failed', array($this, 'login_failed') );
        add_filter( 'plugin_action_links_' . $this->plugin_basename, array( $this, 'get_settings_link' ) );
        add_filter( 'load-middleware-2_page_' . $this->plugin_admin_page, array($this, 'admin_help_tab'));
        add_filter( 'load-middleware-2_page_' . $this->plugin_settings_page, array($this, 'admin_help_tab'));
        /**
         * Filter the content. This is where the password protection kicks in
         */
        add_filter( 'the_content', array($this, 'content_filter'), 999 );

        /**
         * Add Shortcodes for customer name, customer email, login, and forgot password pages
         */
        add_shortcode( 'agora_customer_number', array( $this, 'customer_number_shortcode') );
        add_shortcode( 'agora_customer_email', array( $this, 'customer_email_shortcode') );
        add_shortcode( 'agora_customer_fullname', array( $this, 'customer_fullname_shortcode') );
        add_shortcode( 'agora_customer_firstname', array( $this, 'customer_firstname_shortcode') );
        add_shortcode( 'agora_middleware_login', array( $this, 'login_page_shortcode') );

        /*
         * Add facebook Shortcode button
         * */
        if($this->social_config['fb_login_enable']) {
            // Localize and enqueue facebook login js
            wp_register_script(
                $this->plugin_basename . '_facebook_localized',
                plugin_dir_url( __FILE__ ) . 'js/mw-facebook-localized.js'
            );
            wp_localize_script(
                $this->plugin_basename . '_facebook_localized',
                'mw_social_login_data',
                $this->localize_data()
            );
            wp_enqueue_script( $this->plugin_basename . '_facebook_localized');

            wp_enqueue_script(
                $this->plugin_basename . '_facebook',
                plugin_dir_url( __FILE__ ) . 'js/mw-facebook.js',
                array('jquery'),
                '',
                true
            );
            add_shortcode('fb_login_shortcode', array($this->core->fb, 'fb_login_shortcode'));
        }

        add_shortcode( 'multiple_users_shortcode', array( $this->reset_password, 'multiple_users_shortcode' ) );

        // Add "MW Shortcodes" button/modal
        add_action('media_buttons', array( $this, 'add_middleware_shortcode_picker'));

        // Enqueue scripts for front end of the site.
        add_action('wp_enqueue_scripts', array( $this, 'mw_enqueue_scripts_front'));

        /**
         * Add support for widget-based login box
         */
        add_action( 'widgets_init', function(){ register_widget( 'agora_login_widget' );});

        add_filter( 'agora_middleware_admin_menu', array($this, 'add_tab_item'), 5, 1);

        add_action( 'wp_login', array($this->core->security, 'successful_login'));

        /**
         * Tokenized logins
         */
        add_action( 'init', array($this, 'tokenized_login'));

        /**
         * Single sign on, legacy
         */
        add_action( 'init', array($this, 'single_sign_on'));

        /**
         * Check health
         */
        add_action('admin_init', array($this, 'check_health'));

        add_action('init', array($this, 'clean_user_credentials'));
        /**
         * Deal with usernames that have apostrophes in them
         */
        add_filter( 'sanitize_user', array($this, 'sanitize_user_email'), 10, 3);

        /**
         * Stop sending password change emails
         */
        add_filter( 'send_password_change_email', '__return_false');
        /**
         * Stop sending email change emails
         */
        add_filter( 'send_email_change_email', '__return_false');
        /**
         * Run function to force users with temporary passwords to reset
         */
        add_action( 'wp_login', array($this, 'tfs_change_pwd'));
        add_action( 'template_redirect', array($this, 'tfs_change_pwd'));
        /**
         * auto login users who have just reset their password
         */
        add_action( 'init', array($this->reset_password, 'multiple_users' ));
        add_action( 'init', array($this->reset_password, 'magic_link' ));
        add_action( 'init', array($this, 'auto_login'));

        if($this->config['no_expire'] === "1") {
            /**
             * prevent session from expiring
             */
            add_filter( 'auth_cookie_expiration', array( $this, 'prevent_auth_expiry' ) );
        }

        if($this->config['failed_login_email'] === '1') {
            /**
             * Record Failed Login Attempts
             */
            add_action('wp_login_failed', array($this, 'failed_login_email'), 1);
        }
        //Look for cookie that it is created before FB Login kicks in & after login redirect you to old URI
        add_action( 'init', array( $this, 'fb_redirect' ), 0 );
    }

    /**
     * Description: Look for session varible that it is created before FB Login kicks in & after login redirect you to old URI
     */
    public function fb_redirect()
    {
        if ( $_COOKIE['fb_login_redirect_uri'] && $_GET['fb_redirect'] == 'true' ) {
            wp_redirect($_COOKIE['fb_login_redirect_uri']);
        }
    }

    /**
     *   Localize data
     */
    private function localize_data()
    {
        if ( !empty( $this->social_config['fb_app_id'] ) && !empty( $this->social_config['fb_api_version'] ) ) {
            return array(
                'fb_login_src' => 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v' .
                    $this->social_config['fb_api_version']  . '&appId=' . $this->social_config['fb_app_id']
                );
        }
    }
}


if(class_exists('agora_core_framework')){
    $agora_mw_auth_plugin = new agora_authentication_plugin;
}else{
    add_action( 'all_admin_notices', array('agora_authentication_plugin', 'dependent_plugin_admin_error'));
}
