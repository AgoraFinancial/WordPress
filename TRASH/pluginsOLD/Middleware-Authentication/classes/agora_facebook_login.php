<?php

/**
 * Class agora_facebook_login
 * Description: Allow users to login using their facebook accounts
 * Author: Threefold Systems
 * Version: 1.0
 */
class agora_facebook_login
{
	/**
	 * Class: agora_facebook_login constructor.
	 * @author: Threefold Systems
	 */

    public function __construct(agora_core_framework $core, $social_config)
    {
        $this->core = $core;
        $this->config = $social_config;
        $this->client_id = $this->config['fb_app_id'];
        $this->api_version = $this->config['fb_api_version'];
        $this->client_secret = $this->config['fb_app_secret'];
    }

	/**
	 * Class: fb_login_integration constructor.
	 * Description: Main function that handles the user login Process with FB
	 * Version: 1.0
	 * @author: Threefold Systems
	 * @method fb_login_integration
	 */
    function fb_login_integration()
    {
        //Call facebook to get the users Token
        $get_code = ( isset( $_GET[ 'code' ] ) ? sanitize_text_field( $_GET[ 'code' ] ) : '' );
        $get_redirect_to = ( isset( $_GET[ 'redirect_to' ] ) ? sanitize_text_field( $_GET[ 'redirect_to' ] ) : '' );

        if ($get_code && $get_redirect_to) {
            //Check that the FB APP details are configured in the backend
            if (empty($this->client_id) || empty($this->client_secret)) {
                $this->fb_login_error("Error - Webmaster, please setup your APP secret & ID");
            }

            $fb_redir_url = wp_login_url( $get_redirect_to );
            $fb_auth = wp_remote_get('https://graph.facebook.com/v' . $this->api_version . '/oauth/access_token?client_id=' .
                $this->client_id . '&redirect_uri=' . urlencode($fb_redir_url) . '&client_secret=' . $this->client_secret . '&code=' . $get_code);
            if(is_wp_error($fb_auth)){
                $this->fb_login_error("Error - Could not connect to Facebook");
                return;
            }
            $fb_auth = wp_remote_retrieve_body($fb_auth);
            $facebook_response = json_decode($fb_auth);
            if (isset($facebook_response->error)) {
                $this->fb_login_error("Error - " . $facebook_response->error->message);
                return;
            }
            //Call facebook with the token to retrieve the user email address
            if (isset($facebook_response->access_token)) {
                $endpoint = wp_remote_get('https://graph.facebook.com/v' . $this->api_version . '/me?fields=name,email&access_token=' .
                    $facebook_response->access_token);
                if(is_wp_error($endpoint)){
                    $this->fb_login_error("Error - Could not connect to Facebook");
                    return;
                }
                $endpoint = wp_remote_retrieve_body($endpoint);
                $endpoint_response = json_decode($endpoint);
            } else {
                $this->fb_login_error($this->core->get_language_variable('txt_fb_token_error'));
                return;
            }

            if (isset($endpoint_response->error)) {
                $this->fb_login_error("Error: - " . $endpoint_response->error);
                return;
            }

            //Check with middleware if the email exist
            if (isset($endpoint_response->email)) {
                $email_address = addslashes($endpoint_response->email);
                $user = $this->core->mw->get_account_by_email($email_address);

                //No Middleware account found
                if (isset($user->errors)) {
                    $this->fb_login_error($this->core->get_language_variable('txt_fb_no_user_mw'));
                    return;
                }

                $valid = array();
                if (sizeof($user) > 1) {
                    foreach ($user as $account) {
                        $subs = $this->core->mw->get_active_subscriptions_by_id($account->customerNumber);
                        if (!is_wp_error($subs)) {
                            $valid[] = $account;
                        }
                    }
                    $valid = apply_filters( 'validate_user_accounts', $valid );
                    if (sizeof($valid) == 1) {
                        $user = $valid[0];
                    } else {
                        $email_hash = $this->core->tfs_hash( 'multiple_' . $email_address );
                        set_transient( $email_hash, $user, 15 * 60);
                        wp_redirect($this->fb_login_getredirect() . '?multiple-users=' . $email_hash . '&email=' . $email_address);
                        exit();
                    }
                } else{
                    $user = $user[0];
                }

            } else {
                $this->fb_login_error($this->core->get_language_variable('txt_fb_no_email'));
                return;
            }
            //Assign POST vars with the correct data
            if (!$user->errors && $user->id->userName && $user->password) {
                $_POST['log'] = $user->id->userName;
                $_POST['pwd'] = $user->password;

                //Indicate that is a FB user
                $_POST['fb_origin'] = 1;
            } else {
                $this->fb_login_error($user->id->userName . $user->password . '  ' . $user->errors);
            }
        }
    }

    /**
     * Description: Detects and parses the redirect_to URL from the facebook callback
     * @method fb_login_getredirect
     * @return mixed
     */
    function fb_login_getredirect()
    {
        parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $fb_redir);
	    return apply_filters('fb_login_redirect_url', $fb_redir['redirect_to'] );
    }

    /**
     * Description: Sets redirect_to URL for facebook call
     * @method fb_login_set_redirect
     * @return mixed
     */
    function fb_login_set_redirect( $redirect_url )
    {
        if ( !empty( $redirect_url ) ) {
            return wp_login_url( $redirect_url );
        }
        return wp_login_url();
    }

    /**
     * Description: Redirect to login error page and displays the message
     * @method fb_login_error
     * @param $message
     */
    function fb_login_error($message)
    {
        $this->core->session->flash_message('login', __($message), 'error');
        wp_redirect($this->fb_login_getredirect());
        exit();
    }

    /**
     * Description: Adds a WordPress shortcode for the facebook login button
     * @method fb_login_shortcode
     * @return null|string
     */
    function fb_login_shortcode()
    {
        // Generate url for redirection
        global $post;

        if ( is_user_logged_in() ) {
            return null;
        }

        $current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . $_SERVER["HTTP_HOST"];

        $content = array(
            'message' => $this->core->get_language_variable( 'txt_fb_login_using_fb' ),
            'fb_redir_url' => get_site_url() . '/wp-login.php?redirect_to=' . $current_url . '?fb_redirect=true',
            'client_id' =>   $this->client_id ,
            'api_version' => $this->api_version,
            'fb_icon' => plugins_url( '../img/facebook.png', __FILE__ )
        );

        $this->core->view->load( 'fb_login_shortcode', $content);
    }

    /**
     * Description: Save user_meta to identify it as a FB account
     * @method fb_usermeta
     */
    function fb_usermeta()
    {
        $post_fb_origin = ( isset( $_POST[ 'fb_origin' ] ) ? sanitize_text_field( $_POST[ 'fb_origin' ] ) : '' );

        if (!empty($post_fb_origin)){
            $user_id = $this->core->user->get_user_id();
            update_user_meta($user_id, 'agora_fb_login', 1);
        }
    }
}