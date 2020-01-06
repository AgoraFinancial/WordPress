<?php
class AF_Auth {
    public function __construct()  {
        if (is_user_logged_in()) {
            $this->get_current_user_active_pubs();
        }
        $this->af_auth_hooks();
    }

    // Set up auth hooks
    public function af_auth_hooks() {
        add_action('rest_publications_query', array($this, 'af_api_filter_hidden_pubs'), 10, 2);
        add_action('rest_authentication_errors', array($this, 'af_api_filter_by_ip'));
    }

    // Add hide_from_public meta to API call
    public function af_api_filter_hidden_pubs($args, $request) {
        $args['meta_key'] = 'hide_from_public';
        $args['meta_value'] = 1;
        $args['meta_compare'] = '!=';
        return $args;
    }

    // Restrict API access by IP
    public function af_api_filter_by_ip($errors) {
        $allowed_ips = apply_filters( 'af_api_allowed_ips', array('34.202.15.35', '199.114.7.237') );
        $request_server = $_SERVER['REMOTE_ADDR'];
        if(!in_array($request_server, $allowed_ips))
            return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
        return $errors; 
    }

    public function is_post_lifetime($post_id) {
        $has_life = get_field('is_lifetime_content', $post_id);
        if (isset($has_life[0]) && $has_life[0] == 'Yes') {
            return true;
        } else {
            return false;
        }
    }

    public function is_post_plus($post_id) {
        $has_plus = get_field('is_plus_content', $post_id);
        if (isset($has_plus[0]) && $has_plus[0] == 'Yes') {
            return true;
        } else {
            return false;
        }
    }

    public function has_pub_access($pub = 'NHS', $life = false, $plus = false) {
        global $af_publications;

        if (empty($pub)) {
            return false;
        }

        // Is the user logged
        if (!is_user_logged_in()) {
            return false;
        }

        // If the user has the power let them through
        if (current_user_can('editor') || current_user_can('administrator'))
            return true;

        // Check if all access is blocked
        if ($this->all_access_blocked($pub) === false)
            return false;

        // Check if AFR member
        if ($this->is_afr_member() == true)
            return true;

        // Check against lifetime or plus
        if (!$life && !$plus) {
          $active_pubs = $this->get_current_user_active_pubs();
        } elseif ($life) {
          $active_pubs = $this->get_current_user_active_pubs($life);
        } elseif ($plus) {
            $active_pubs = $this->get_current_user_active_pubs('', $plus);
        }
        if (in_array($pub, $active_pubs)) {
            return true;
        }

        // If pubs area array and matches at active
        if (is_array($pub) && !empty(array_intersect($pub, $active_pubs))) {
            return true;
        }

        // Check if pub is temp
        if (!empty(array_intersect($af_publications->af_get_temp_access_pubs($pub), $active_pubs))) {
            return true;
        }

        // Check if pubcode is an listcode
        if($this->validate_as_listcode($pub)) {
            return true;
        }

        return apply_filters( 'af_has_pub_access', false, $pub, $life, $plus );
    }

    // Check if any pubcode is completely blocked from access
    public function all_access_blocked($pubcode) {

        // If AFR, always return true
        if ($pubcode == 'AFR')
            return true;

        // If single value, make into array
        if (!is_array($pubcode)) {
            $pub_array = array();
            $pub_array[] = $pubcode;
        } else {
            $pub_array = $pubcode;
        }

        // Set count for multiple pubs
        $i = 0;

        // Check each pubcode in array to see if blocked
        foreach ($pub_array as $pubcode) {
            $pub = get_posts(array('post_type' => 'publications', 'meta_key' => 'pubcode', 'meta_value' => $pubcode));
            foreach ($pub as $post) {
                setup_postdata($post);
                $access = get_field('remove_all_access', $post->ID);
                if (!isset($access) || $access != true) {
                    $i++;
                }
            }
            wp_reset_postdata();
        }

        // If at least one checked pub comes back true, show, else false
        if ($i > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_current_user_active_pubs($life = false, $plus = false) {
        // Default is to not let them in
        $active_pubs = array();

        $middleware_data = $this->get_current_user_mw_data();

        // No pubs to work with?
        if (!$middleware_data)
            return $active_pubs;

        // The active circ statuses to check against
        $circstatus_active = array('R', 'P', 'U');

        /**
         * Go through each of the users subs and see if they have an active sub
         * based on past $pub and confirm the circ status is correct then
         * optionally return an array of lifetime pubs or simple active pubs.
         */
        foreach ($middleware_data->subscriptionsAndOrders->subscriptions as $subscription) {
            if (in_array($subscription->circStatus, $circstatus_active)) {
                if (!$life && !$plus) {
                    array_push($active_pubs, $subscription->id->item->itemNumber);
                } elseif ($life) {
                    if ($subscription->subType == 'LIFE') {
                        array_push($active_pubs, $subscription->id->item->itemNumber);
                    }
                } elseif ($plus) {
                    if ($subscription->subType == 'PLUS' || $subscription->subType == 'LIFE') {
                        array_push($active_pubs, $subscription->id->item->itemNumber);
                    }
                }
            }
        }
        return $active_pubs;
    }

    // Get a list of active listcodoes from usermeta
    public function validate_as_listcode($pubcode) {

        // Setup vars
        $user_listcodes = array();
        $user = wp_get_current_user();
        $listSubscriptions = $user->data->middleware_data->listSubscriptions;

        // Check if subscriptions exisit
        if (empty($listSubscriptions)) return false;

        // Build array of liscodes to check
        foreach($listSubscriptions as $listcode) {
            $user_listcodes[] = $listcode->listCode;
        }

        // If testing against an array,
        if (is_array($pubcode)) {

            // Check for any matching pubs in array of listcodes
            $result = array_intersect($pubcode, $user_listcodes);
            if(!empty($result)) {
                return true;
            } 

        } else {

            // Check if single pub is in array of listcodes
            if(in_array($pubcode, $user_listcodes)) {
                return true;
            }
        }

        return false;
    }

    public function get_current_user_mw_data()
    {

        if (!is_user_logged_in())
            return false;

        //get the current user
        $current_user = wp_get_current_user();

        //get the user middleware data
        $middleware_data = get_user_meta($current_user->data->ID, 'agora_middleware_aggregate_data');

        if (!isset($middleware_data[0])) {
            return false;
        } else {
            $customer_number = (isset($middleware_data[0]->accounts[0]->customerNumber) && $middleware_data[0]->accounts[0]->customerNumber) ? $middleware_data[0]->accounts[0]->customerNumber : "-1";
            // if ($customer_number && "-1" != $customer_number){
            //     setcookie("afadv","$customer_number");
            // }
            return $middleware_data[0];
        }
    }

    public function is_afr_member() {
        $pubs = $this->get_current_user_active_pubs();
        foreach ($pubs as $pub) {
            if ($pub == 'AFR') return true;
        }
        return false;
    }

    public function is_temp_user() {
        $middleware_data =  $this->get_current_user_mw_data();
        if ($middleware_data->accounts[0]->role == 'TEMP') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to retrieve middleware data
     * @return array on success
     * if showErrors is set to a false value (default) an error will return an empty array
     * if showErrors is set to a true value an error will return a string
     */
    function get_mw_data($method, $params = array(), $show_errors = 0) {
        if (!$method)
            return;

        // Check config if mw should be in UAT mode
        $uat_mode = defined( 'AGORA_MW_UAT_MODE' ) && AGORA_MW_UAT_MODE === true ? true : false ;
        $access_token = '';
        $base_url = '';
        
        // Set tokens from config
        if ( $uat_mode ) {
            $access_token = defined( 'AGORA_MW_TOKEN_UAT' ) && AGORA_MW_TOKEN_UAT ? AGORA_MW_TOKEN_UAT : '' ;
            $base_url = defined( 'AGORA_MW_URL_UAT' ) && AGORA_MW_URL_UAT ? AGORA_MW_URL_UAT : '' ;
        } else {
            $access_token = defined( 'AGORA_MW_TOKEN' ) && AGORA_MW_TOKEN ? AGORA_MW_TOKEN : '' ;
            $base_url = defined( 'AGORA_MW_URL' ) && AGORA_MW_URL ? AGORA_MW_URL : '' ;
        }

        if (!$access_token || !$base_url)
            return;

        $exclude_list = array('post_customer_update_postaladdress', 'post_account_update_password');
        if (!in_array($method, $exclude_list)) {
            $params = array_change_key_case($params, CASE_LOWER);
        }

        $is_post = false;

        //standard fields validation
        if (isset($params['email'])){
            $params['email'] = filter_var($params['email'], FILTER_VALIDATE_EMAIL) ? strtoupper($params['email']) : '';
        } elseif (isset($params['emailAddress'])) {
            $params['emailAddress'] = filter_var($params['emailAddress'], FILTER_VALIDATE_EMAIL) ? strtoupper($params['emailAddress']) : '';
        }
        if (isset($params['customernumber'])) {
            if (!preg_match("/^\d{12}$/", $params['customernumber'])) {
                $params['customernumber'] = '';
            }
        } elseif (isset($params['customerNumber'])) {
            if (!preg_match("/^\d{12}$/", $params['customerNumber'])) {
                $params['customerNumber'] = '';
            }
        }
        if (isset($params['pubcode'])) {
            if (!preg_match("/^\w{3}$/", $params['pubcode'])) {
                $params['pubcode'] = '';
            }
        }
        if (isset($params['renewalflag'])) {
            if (!preg_match("/^\w{1}$/", $params['renewalflag'])) {
                $params['renewalflag'] = '';
            }
            $params['renewalflag'] = (preg_match("/^\w$/", $params['renewalflag'])) ? strtoupper($params['renewalflag']) : '';
        }
        switch ($method) {
            case "get_customer_findlowestcustomernumber_emailaddress":
                if (!$params['email'])
                    return '';
                $url = $base_url . 'customer/findlowestcustomernumber/emailaddress/' . $params['email'];
                break;
            case "get_sub_emailaddress":
                if (!$params['email'])
                    return '';
                $url = $base_url . 'sub/emailaddress/' . $params['email'];
                break;
            case "get_sub_customernumber":
                if (!$params['customernumber'])
                    return '';
                $url = $base_url . 'sub/customernumber/' . $params['customernumber'];
                break;
            case "get_postaladdress_emailaddress":
                if (!$params['email'])
                    return '';
                $url = $base_url . 'postaladdress/emailaddress/' . $params['email'];
                break;
            case "get_postaladdress_customernumber":
                if (!$params['customernumber'])
                    return '';
                $url = $base_url . 'postaladdress/customernumber/' . $params['customernumber'];
                break;
            case "post_customer_update_postaladdress":
                if (!$params['emailAddress'])
                    return 'No email address';
                if (!$params['customerNumber'])
                   return 'No customer number';
                if (!$params['addressCode'])
                    return 'No address code';
                $url = $base_url . 'customer/update/postaladdress';
                $post_fields = $params;
                $is_post = true;
                break;
            case "post_account_update_password":
                if (!$params['username'])
                    return 'No username';
                if (!$params['customerNumber'])
                    return 'No customerNumber';
                if (!$params['existingPassword'])
                    return 'No exisiting password';
                if (!$params['newPassword'])
                    return 'No new password';
                $url = $base_url . 'account/update/password';
                $post_fields = $params;
                $is_post = true;
                break;
            case "post_sub_renewalflag_update":
                if (!$params['subref'])
                    return '';
                if (!$params['renewalflag'])
                    return '';
                $url = $base_url . 'sub/renewalflag/update/';
                $post_fields = array('subRef' => $params['subref'], 'renewalFlag' => $params['renewalflag']);
                $is_post = true;
                break;
            case "get_pubs":
                if (!$params['customernumber'])
                    return '';
                $url = $base_url . 'adv/list/signup/customernumber/' . $params['customernumber'];
                break;
            case "get_adv_data":
                if (!$params['customernumber'])
                    return '';
                $url = $base_url . 'target/affiliate/fact/customernumber/' . $params['customernumber'];
                break;
            case "custom_rdp_survey":
                if (!$params['pubcode']) $params['pubcode'] = 'RDP';//required field
                $params['eventtypeid'] = 'API-TRIGGER-CIR-MODEXT';//required field
               //very customized combined call to remove a user from A/R and extend their subscription by 3 months
               //we require a customer number, pub code, sub ref to complete this request
               //      the customer number can be looked up with email if we have to
               //      the subref can be looked up with customer number
                if (isset($params['email']) && $params['email'] && (!isset($params['customernumber']) || !$params['customernumber'])){
                    $response = $this->get_mw_data('get_customer_findlowestcustomernumber_emailaddress', array('email' => $params['email'])); 
                    if (isset($response[0]['id']['customerNumber'])) {
                        $params['customernumber'] = $response[0]['id']['customerNumber'];
                    } elseif (isset($response['customerNumber'])) {
                        $params['customernumber'] = $response['customerNumber'];
                    } else {
                        $params['customernumber'] = '';
                    }
                }
                if (!$params['customernumber']) return '';//required field
                if (!isset($params['subref']) || !$params['subref']){
                    $response = $this->get_mw_data('get_sub_customernumber', array('customernumber' => $params['customernumber']));
                   foreach($response as $arResponse){
                       if(isset($arResponse['id']['item']['itemNumber']) && 'RDP' == $arResponse['id']['item']['itemNumber']){
                               if(!$arResponse['temp'] && in_array($arResponse['circStatus'],array('R','P'))) $params['subref'] = $arResponse['id']['subRef'];
                       }
                   }
                }
                if (!$params['subref']) return '';//required field
               //first cancel auto renew, then extend the subscription
               //this is a request, no response given on success so we have to assume it worked
                $response = $this->get_mw_data('post_sub_renewalflag_update', array("subref" => $params['subref'],"renewalflag" => "D"));
               //now extend the subscription with a workflow event
                $url = $base_url.'workflow/event/create';
                $post_fields = array(
                   "eventTypeId" => "API-TRIGGER-CIR-MODEXT",
                   "contextKeyList" => array(
                       "PUB-CDE"=>$params['pubcode'],
                       "CTM-NBR"=>$params['customernumber'],
                       "SUB-REF"=>$params['subref']
                   )
                );
                $is_post = true;
                break;
            case "target_affiliate_tag_update":
                if (isset($params['email']) && $params['email'] && (!isset($params['customernumber']) || !$params['customernumber'])){
                    $response = get_mw_data('get_customer_findlowestcustomernumber_emailaddress', array('email' => $params['email']));
                    if (isset($response[0]['id']['customerNumber'])) {
                        $params['customernumber'] = $response[0]['id']['customerNumber'];
                    } elseif (isset($response['customerNumber'])) {
                        $params['customernumber'] = $response['customerNumber'];
                    } else {
                        $params['customernumber'] = '';
                    }
                }
                if (!$params['customernumber'] || !$params['email'] || !$params['newtagname'] || !$params['newtagvalue'])
                    return '';
                if (!$params['tagname'])
                    $params['tagname'] = $params['newtagname'];
                if (!$params['tagvalue'])
                    $params['tagvalue'] = $params['newtagvalue'];
                $url = $base_url.'target/affiliate/tag/update/';
                $post_fields = array(
                    'owningOrg' => '400',
                    'newTagName' => $params['newtagname'],
                    'newTagValue' => $params['newtagvalue'],
                    'customerNumber' => $params['customernumber'],
                    'emailAddress' => $params['email'],
                    'tagName' => $params['tagname'],
                    'tagValue' => $params['tagvalue']
                );
                $is_post = true;
                break;
            default:
                return '';
        }

        $ch = curl_init($url);
        if ($is_post) {
            $data_string = json_encode($post_fields);
            $curl_options = array(
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data_string,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'token: ' . $access_token)
            );
        } else {
            $curl_options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_HEADER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_HTTPHEADER => array('token: ' . $access_token)
            );
        }
        curl_setopt_array($ch, $curl_options);

        $content = curl_exec($ch);
        if (curl_errno($ch)){
            curl_close($ch);
            return $show_errors ? curl_error($ch) : '';
        }
        curl_close($ch);
        $ar_response = json_decode($content, true);
        if ($show_errors && !count($ar_response)) {
            return '';
        }
        return $ar_response;
    }
}
