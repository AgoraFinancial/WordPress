<?php
/**
 * Theme shortcodes
 */
class AF_Shortcodes extends AF_Theme {
    public function __construct() {
        $this->af_add_shortcodes();
        $this->af_add_custom_filters();
    }

    // Add shortcodes
    public function af_add_shortcodes() {
        // Add shortcodes
        add_shortcode('first_name', array($this, 'af_shortcode_first_name'));
        add_shortcode('subscriptions_table', array($this, 'af_shortcode_subscriptions_table'));
        add_shortcode('af_portfolio', array($this, 'af_shortcode_portfolio'));
        add_shortcode('af_update_renewal_flag', array($this, 'af_shortcode_update_renewal_flag'));

        // Add rewrites for af_update_renewal_flag shortcode
        add_filter('query_vars', array($this, 'af_renewal_query_var'));
        add_action('init', array($this, 'af_renewal_rewrite'));
    }

    // Generate user first name
    public function af_shortcode_first_name($atts = array(), $content = '') {
        global $af_users;
        $mw_current_user = $af_users->af_get_user_data();
        $mw_first_name = isset($mw_current_user['first_name']) && $mw_current_user['first_name'] != '' ? $mw_current_user['first_name'] : '' ;
        $wp_current_user = wp_get_current_user();
        $wp_first_name = isset($wp_current_user->first_name) && $wp_current_user->first_name != '' ? $wp_current_user->first_name : 'Reader' ;
        $first_name = $mw_first_name && $mw_first_name != '' ? $mw_first_name : $wp_first_name ;
        return ucwords($first_name);
    }

    // Generate table of user subscriptions
    public function af_shortcode_subscriptions_table($atts = array(), $content = '') {
        global $af_users;
        $user_subscriptions = $af_users->af_subscription_data();
        $html = '';

        if (empty($user_subscriptions)) {
            $html = '<p class="callout warning">You currently have no subscriptions.</p>';
            return $html;
        }

        $html .= '
        <table class="table-subscriptions table-responsive shortcode-table">
            <thead>
                <tr>
                    <th>Subscriptions</th>
                    <th>Start Date</th>
                    <th>Expiration Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>';

        foreach ($user_subscriptions as $sub) {
            $html .= '
                <tr>
                    <td data-label="Subscription" class="subscription-name">' . $sub['sub_name'] . '</td>
                    <td data-label="Start Date" class="subscription-start">' . $sub['sub_start'] . '</td>
                    <td data-label="Expiration Date" class="subscription-end">' . $sub['sub_end'] . '</td>
                    <td class="subscription-renew">' . $sub['sub_renew'] . '</td>
                </tr>
            ';
        }

        $html .= '
            </tbody>
        </table>
        ';
        return $html;
    }

    // Generate portfolio based on pubcode
    public function af_shortcode_portfolio($atts = array(), $content = '') {
        global $af_auth, $af_templates, $af_publications;

        // Shortcode attributes
        $settings = shortcode_atts(array(
            'pubcode' => '',
            'lifetime' => '',
            'plus' => '',
            'columns' => '',
        ), $atts);

        // Pubcode attribute
        $pubcode = $settings['pubcode'];
        if ($pubcode == '') {
            return '<p class="callout alert">No pubcode provided.</p>';
        }

        // Lifetime attribute
        $is_lifetime = $settings['lifetime'] ? true : false;

        // Plus attribute
        $is_plus = $settings['plus'] ? true : false;

        // Custom portfolio code
        $is_custom = false;

        // Columns attribute
        $columns = $settings['columns'];

        if ($columns) {
            $columns = explode(',', $columns);
            foreach ($columns as $column) {
                $column_arr = explode('|', $column);
                $columns_tmp[$column_arr[0]] = $column_arr[1];
            }
            $columns = $columns_tmp;
        } else {
            // Default columns
            $columns = array(
                'symbol' => 'Symbol',
                'name' => 'Name',
                'comments' => 'Comments',
                'entryDate' => 'Entry Date',
                'closeDate' => 'Close Date',
                'entryPrice' => 'Entry Price',
                'currentPrice' => 'Current Price',
                'percentGain' => 'Percent Gain'
            );
        }

        // Get id from pubcode
        $pub_id = $af_publications->af_get_pub_ID($pubcode);

        $pubcode_check = '';

        // If invalid, check if pubcode is plus_portfolio_pubcode
        if ($pub_id == '') {
            $pub_id = $af_publications->af_get_pub_ID_by_meta('plus_portfolio_code', $pubcode);
            if ($pub_id != '') {
                $pubcode_check = get_field('pubcode', $pub_id);
            } else {
                $is_custom = true;
            }
        } else {
            $pubcode_check = $pubcode;
        }

        // Check access and build paygate if needed
        if (!$af_auth->has_pub_access($pubcode_check, $is_lifetime, $is_plus) && !$is_custom) {
            $html = '';
            $pub_title = get_the_title($pub_id);
            $paygate_content_var = get_field('paygate_content', $pub_id);
            $paygate_content = $paygate_content_var ? $paygate_content_var : '<p>You cannot access this information.</p>';
            $plus_name = get_field('plus_name', $pub_id);
            $plus_paygate_content_var = get_field('plus_paygate_content', $pub_id);
            $plus_paygate_content = $plus_paygate_content_var ? $plus_paygate_content_var : '<p>You cannot access this information.</p>';
            $title = $is_plus ? $pub_title . ' - ' . $plus_name : $pub_title;
            $paygate = $is_plus ? $plus_paygate_content : $paygate_content;

            $html .= '<div class="callout warning paygate-wrapper">';
            $html .= '<h4>' . $title . '</h4>';
            $html .= $paygate;
            $html .= '</div>';
            return $html;
        }

        // If all clear, build portfolio
        $portfolioResponse = $af_publications->af_get_pub_portfolio($columns, $pubcode);

        add_action( 'wp_footer', function(){ 
            echo '<script>(function($){pubPortfolio();})(jQuery);</script>';
        }, 9999 );

        set_query_var('portfolio_columns', $columns);
        set_query_var('portfolio_response', $portfolioResponse);
        ob_start();
        $af_templates->af_pub_portfolio_table();
        return ob_get_clean();
    }

    // Auto renewal removal flag
    public function af_shortcode_update_renewal_flag( $atts, $content = '' ) {
        global $wp, $af_auth, $af_publications;

        // User must be logged in
        $current_url = home_url( $wp->request );
        $is_user_logged_in = is_user_logged_in();        
        if ( !$is_user_logged_in)
            return $this->af_renewal_error_msg( 'You must be logged in to update your billing preferences. <a href="/login/?redirect_to=' . esc_url( $current_url ) . '">Click here to login.</a>' );

        // URL must contain a have pubcode
        $pubcode = get_query_var( 'pub', false );
        if ( !$pubcode ) {
            return $this->af_renewal_error_msg( 'No pubcode provided.' );
        }

        // Pubcode must be valid
        $pubcode = $pubcode ? strtoupper( $pubcode ) : '' ;
        $active_pubcodes = $af_publications->af_get_active_pubcodes();
        $has_valid_pubcode = in_array( $pubcode, $active_pubcodes ) ? true : false ;
        if ( !$has_valid_pubcode )
            return $this->af_renewal_error_msg( 'Pubcode is invalid.' );

        // Must have access to this pubcode
        if ( !$af_auth->has_pub_access( $pubcode ) )
            return $this->af_renewal_error_msg( 'You do not have access to this publication.' );

        // Get pub data
        $pub_id = $af_publications->af_get_pub_ID( $pubcode );
        $pub_data = $af_publications->af_get_pub_data( $pub_id );
        $pub_title = $pub_data['pub_title'];

        // Get user data
        $current_user = wp_get_current_user();
        $email = $current_user->data->user_email;

        // Default renewal flag
        $renewal_flag = 'D';

        // Build content
        $content = '
        <h3><em>' . $pub_title . '</em> &mdash; Remove From Auto Renew</h3>
        <p>If you have received your product and wish to opt out of monthly or yearly billing to your membership to <em>' . $pub_title . '</em> please enter your email address below. By entering your email address your billing preferences you are requesting that your account be updated so that you will not be automatically charged to renew your subscription.</p>
        <p>Please allow up to 24 hours for us to process your request to update your billing preferences.</p>
        ';
        $content = apply_filters( 'af_renewal_preform_content', $content, $pub_data );

        // Build form
        $form_label = apply_filters( 'af_renewal_form_label', 'Enter the email address associated with your account below:' );
        $form_button = apply_filters( 'af_renewal_form_button', 'Yes, I am requesting to be removed from Auto Renewal' );
        $form = '
        <div class="callout auto-renewal-removal-wrapper">
            <form action="' . $current_url . '" method="post" class="auto-renewal-removal-form">
                <label for="email">' . $form_label . '</label>
                <input id="email" name="email" required type="text" value="' . $email . '" placeholder="Email Address">
                <input class="button" value="' . $form_button .'" type="submit">
            </form>
        </div>
        ';

        // Must have email
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !$email )
            return $content . $this->af_renewal_error_msg( 'No email address provided.' ) . $form;

        // If successful post request
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $email ) {
            
            // Email format is invalid
            if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                return $content . $this->af_renewal_error_msg( 'Email address format is invalid.' ) . $form . $this->af_generate_profiles_send( $pubcode, 'Email malformed' );
            }

            // Get user subscription data from MW
            $subref = '';
            $ar_pubs = $af_auth->get_mw_data( 'get_sub_emailaddress', array('email' => $email ), 0 );
            
            // Must have subscription data
            if ( !isset( $ar_pubs['subscriptions'] ) )
                return $content . $this->af_renewal_error_msg( 'We could not retrieve your subscription data.' ) . $form . $this->af_generate_profiles_send( $pubcode, 'No Subscription Data' );

            // Loop through subscriptions
            foreach ($ar_pubs['subscriptions'] as $ar_pub) {
                
                // Skip temp subscriptions
                if ( ( isset($ar_pub['temp'] ) && $ar_pub['temp'] ) || preg_match( "/\D/", $ar_pub['id']['customerNumber'] ) ) {
                    continue;
                }

                // Find subscription that matches pubcode
                if ( $pubcode == $ar_pub['id']['item']['itemNumber'] ) {
                    $subref = $ar_pub['id']['subRef'];
                }
            }

            // Skip subscription if not available
            if (!$subref) {
                return $content . $this->af_renewal_error_msg( 'We did not find this subscription on your account.' ) . $form . $this->af_generate_profiles_send( $pubcode, 'Not Subscribed' );
            }

            // Update MW
            $retVal = $af_auth->get_mw_data( 'post_sub_renewalflag_update', array( 'subref'=> "$subref", 'renewalFlag' => "$renewal_flag" ), 0);
            
            // Success
            return $content . $this->af_renewal_success_msg( 'You have successfully requested removal from automatic billing.' ) . $this->af_generate_profiles_send( $pubcode, 'Successfully Removed' );

        } else {
            return $content . $form;
        }
    }

    /**
     * Format error message
     * @param  string $message
     * @return string
     */
    public function af_renewal_error_msg( $message = 'There was a problem with your request.' ) {
        return '<p class="callout warning">' . $message . '</p>';
    }

    /**
     * Format success message
     * @param  string $message
     * @return string
     */
    public function af_renewal_success_msg( $message = 'Your request has been successfully sent.' ) {
        return '<p class="callout success">' . $message . '</p>';
    }

    /**
     * Add custom query var for tickers
     * @param  array $vars
     * @return array
     */
    public function af_renewal_query_var($vars) {
        $vars[] .= 'pub';
        return $vars;
    }
    /**
     * Add url rewrite for ticker pagse
     * @return null
     */
    public function af_renewal_rewrite() {
        $pagename = apply_filters( 'af_renewal_rewrite_pagename', 'billing-preferences' );
        add_rewrite_rule('^autorenew/([a-zA-Z0-9_]+)/?', 'index.php?pagename=' . $pagename . '&pub=$matches[1]', 'top');
    }

    // Generate an afga.send for auto renewal cancellation
    public function af_generate_profiles_send($pubcode, $event_label) {
        if ($event_label == '') {
            return;
        }

        $html = "
        <script>
        document.addEventListener('afga_ready', pageGA, false);
        function pageGA() {
            afga.send('event', {
                'eventCategory': 'Billing Preference',
                'eventAction': 'Auto Renew Cancel',
                'eventLabel': '$event_label - $pubcode'
            });
        }
        </script>
        ";
        return $html;
    }

    // Add filters
    public function af_add_custom_filters() {
        add_filter('the_content', array($this, 'af_custom_embed_filter'));
    }

    // Filter custom embeds from ACF repeater
    public function af_custom_embed_filter($content) {
        if (function_exists('get_field')) {
            $post_id = get_the_ID();
            $embed_codes = get_field('embed_codes', $post_id);
            if (is_array($embed_codes) && count($embed_codes)) {
                foreach ($embed_codes as $embed_code) {
                    $short_code_label = $embed_code['shortcode_label'];
                    $embed_content = $embed_code['embed_code'];
                    if ($short_code_label && $embed_content) {
                        $content = str_replace('[' . $short_code_label . ']', $embed_content, $content);
                    }
                }
            }
        }
        return $content;
    }
}
