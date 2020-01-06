<?php
/**
 * Controls data/logic for users
 */

class AF_Users extends AF_Theme {
    public $objAFUser = array();

    public function __construct() {
        $this->af_set_user_data();
        $this->af_user_hooks();
    }

    // Get user data
    public function af_get_user_data($elem = '') {
        if ($elem) {
            if (isset($this->objAFUser[$elem])) {
                return $this->objAFUser[$elem];
            } else {
                $this->af_set_user_data();
                if (isset($this->objAFUser[$elem])) {
                    return $this->objAFUser[$elem];
                } else {
                    return;
                }
            }
        } else {
            if (isset($this->objAFUser)) {
                return $this->objAFUser;
            } else {
                $this->af_set_user_data();
                return;
            }
        }
    }

    // Reset user transient data after specified time
    public function af_reset_user_info( $seconds = 60 ) {
        $seconds = $seconds < 60 ? 60 : $seconds ;
        if (isset($this->objAFUser)) {
            $transient_id = 'af_user_' . $this->objAFUser['id'];
            $transient_data = apply_filters( 'af_user_data', $this->objAFUser, $this->objAFUser['id'] );
            set_transient( $transient_id, $transient_data, $seconds );
        }
        return;
    }

    // Set up user hooks
    public function af_user_hooks() {
        add_action('wp_ajax_user_data_ajax', array($this, 'processUserDataUpdates'));
        add_action('wp_ajax_nopriv_user_data_ajax', array($this, 'processUserDataUpdates'));
        add_filter('query_vars', array($this, 'af_ticker_query_var'));
        add_action('init', array($this, 'af_ticker_rewrite'));
    }

    // Setup user data
    public function af_set_user_data() {
        if (!is_user_logged_in()) return;

        $user_id = get_current_user_id();
        $transient_id = 'af_user_' . $user_id;
        $transient_data = get_transient( $transient_id );

        // Return if transient exists
        if ( $transient_data ) {
            $this->objAFUser = apply_filters( 'af_user_data', $transient_data, $user_id );
            setcookie( 'afadv', $transient_data['advantage_id'], time() + (86400 * 30), '/' );
            return;
        }

        $this->objAFUser = array();
        $ar_user = array();
        $current_user = wp_get_current_user();
        if (!isset($current_user->ID) || !$current_user->ID) {
            return array();
        }
        $current_user_meta = get_user_meta($current_user->ID);

        // ID fields
        $ar_user['id'] = $current_user->ID;
        $ar_user['username'] = $current_user->user_login;

        // Get advantage data from MW plugin aggregate data
        $mw_aggregate_data = isset($current_user_meta['agora_middleware_aggregate_data']) && $current_user_meta['agora_middleware_aggregate_data'] ? unserialize($current_user_meta['agora_middleware_aggregate_data'][0]) : '';
        $ar_user['advantage_id'] = isset($mw_aggregate_data->accounts[0]->customerNumber) && $mw_aggregate_data->accounts[0]->customerNumber ? $mw_aggregate_data->accounts[0]->customerNumber : '-1';
        $ar_user['advantage_email'] = isset($mw_aggregate_data->postalAddresses[0]->emailAddress->emailAddress) && $mw_aggregate_data->postalAddresses[0]->emailAddress->emailAddress ? $mw_aggregate_data->postalAddresses[0]->emailAddress->emailAddress : $current_user->user_email;

        // WP user email
        if (filter_var($current_user->data->user_email, FILTER_VALIDATE_EMAIL)) {
            $ar_user['email'] = $current_user->data->user_email;
        } elseif (filter_var($current_user->data->user_login, FILTER_VALIDATE_EMAIL)) {
            $ar_user['email'] = $current_user->data->user_login;
        } else {
            $ar_user['email'] = '';
        }

        // User token
        if (empty($current_user_meta['af_user_token'][0])) {
            update_user_meta($current_user->ID, 'af_user_token', $this->generateUserToken());
        }
        $ar_user['af_user_token'] = (isset($current_user_meta['af_user_token'][0]) && $current_user_meta['af_user_token'][0]) ? $current_user_meta['af_user_token'][0] : '';

        // Scalar fields
        $ar_user['customer_last_pub_purchased'] = (isset($current_user_meta['customer_last_pub_purchased'][0]) && $current_user_meta['customer_last_pub_purchased'][0]) ? $current_user_meta['customer_last_pub_purchased'][0] : '';
        $ar_user['first_name'] = (isset($current_user_meta['first_name'][0]) && $current_user_meta['first_name'][0]) ? $current_user_meta['first_name'][0] : '';
        $ar_user['last_name'] = (isset($current_user_meta['last_name'][0]) && $current_user_meta['last_name'][0]) ? $current_user_meta['last_name'][0] : '';
        $ar_user['nickname'] = (isset($current_user_meta['nickname'][0]) && $current_user_meta['nickname'][0]) ? $current_user_meta['nickname'][0] : '';
        if (isset($current_user->data->display_name) && $current_user->data->display_name) {
            $ar_user['display_name'] = $current_user->data->display_name;
        } elseif (isset($current_user->data->user_nicename) && $current_user->data->user_nicename) {
            $ar_user['display_name'] = $current_user->data->user_nicename;
        } elseif (isset($current_user_meta['nickname'][0]) && $current_user_meta['nickname'][0]) {
            $ar_user['display_name'] = $current_user_meta['nickname'][0];
        } else {
            $ar_user['display_name'] = ($ar_user['first_name'] && $ar_user['last_name']) ? $ar_user['first_name'].' '.$ar_user['last_name'] : $ar_user['first_name'].$ar_user['last_name'];
        }

        // json fields
        $ar_user['capabilities'] = (isset($current_user_meta['wp_capabilities'][0]) && $current_user_meta['wp_capabilities'][0]) ? $current_user_meta['wp_capabilities'][0] : '';
        $this->generateUserToken = $ar_user;

        $transient_data = apply_filters( 'af_user_data', $ar_user, $user_id );
        set_transient( $transient_id, $transient_data, 60 * 60 * 1 );
        setcookie( 'afadv', $transient_data['advantage_id'], time() + (86400 * 30), '/' );
        return;
    }

    //require token, user id, method, and values
    //methods include...
    //Add Ticker
    //Remove Ticker
    //Get All User Tickers
    //Add Article
    //Remove Article
    //Get All User Articles

    public function processUserDataUpdates() {
        $uid = isset($_POST['uid']) ? $_POST['uid'] : 0;
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        $method = isset($_POST['method']) ? $_POST['method'] : '';
        $val = isset($_POST['val']) ? $_POST['val'] : '';

        //validate
        if (!($uid && $token && $method)) {
            return;
        }
        if ('getTickers' == $method || 'addTicker' == $method || 'removeTicker' == $method || 'getTickerContent' == $method) {
            $val = preg_replace('/\W/','',$val);
            if (!$val && ('getTickers' != $method && 'getTickerContent' != $method)) {
                return;
            }
        } elseif ('getArticles' == $method || 'addArticle' == $method || 'removeArticle' == $method) {
            $val = preg_replace('/\D/', '', $val);
            if (!$val && 'getArticles' != $method) {
                return;
            }
        }
        if ('getTickerContent' != $method && !$this->validateUserToken($uid, $token)) {
            return;
        }

        if ('addTicker' == $method) {
            $ar_chart = $this->getTickerData($val);
            if($ar_chart) {
                // Only add meta with legit tickers
                $this->addUserTicker($uid, $val);
                echo json_encode($ar_chart);
            } else {
                // Throw error
                echo json_encode(false);
            }
	    wp_die();
        } elseif ('removeTicker' == $method) {
            $this->removeUserTicker($uid,$val);
            $ar_tickers = $this->getUserTickers($uid);
            $ar_chart = $this->getTickerData($ar_tickers);
            echo json_encode($ar_chart);
	    wp_die();
        } elseif ('getTickers' == $method) {
            $ar_tickers = $this->getUserTickers($uid);
            $ar_chart = $this->getTickerData($ar_tickers);
            echo json_encode($ar_chart);
	    wp_die();
        } elseif ('getTickerContent' == $method) {
            $this->getTickerContent($token);
	    wp_die();
        } elseif ('addArticle' == $method) {
            $this->addUserArticle($uid, $val);
        } elseif ('removeArticle' == $method) {
            $this->removeUserArticle($uid, $val);
        } elseif ('getArticles' == $method) {
            $ar_posts = $this->getUserArticles($uid);
            echo json_encode($ar_posts);
	    wp_die();
        }
    }

    public function getUserTickers($uid) {
        global $wpdb;
        $ar_tokens = array();
        $query = "SELECT meta_value FROM wp_usermeta WHERE  user_id='$uid' AND meta_key='af_user_ticker'";
        $ar_results = $wpdb->get_results($query,'OBJECT');
        if (!$ar_results) {
            return $ar_tokens;
        }
        foreach ($ar_results as $obj_result) {
            if ($obj_result->meta_value) {
                $ar_tokens[] = $obj_result->meta_value;
            }
        }
        return $ar_tokens;
    }

    public function addUserTicker($uid, $symbol) {
        global $wpdb;
        $symbol = strtoupper($symbol);
        $query = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES ('$uid', 'af_user_ticker', '$symbol')";
        $wpdb->query($query);
    }

    public function removeUserTicker($uid, $symbol) {
        global $wpdb;
        $symbol = strtoupper($symbol);
        $query = "DELETE FROM wp_usermeta WHERE user_id='$uid' AND meta_key='af_user_ticker' AND meta_value='" . strtolower($symbol) . "'";
        $wpdb->query($query);
    }

    public function getUserArticles($uid) {
        global $wpdb;
        $ar_articles = array();
        //$query = "select p.ID,count(pp.post_id) as myCount from wp_posts p left join wp_popular_posts pp on p.ID=pp.post_id where pp.view_date > '2017-10-01' and p.post_status='publish' group by p.ID order by myCount desc limit 10";
        $query = "SELECT meta_value FROM wp_usermeta WHERE user_id='$uid' AND meta_key='af_user_article'";
        $ar_results = $wpdb->get_results($query, 'OBJECT');
        if (!$ar_results) {
            return $ar_articles;
        }
        foreach ($ar_results as $obj_result) {
            if ($obj_result->meta_value) {
                $ar_articles[] = $obj_result->meta_value;
            }
            //if ($obj_result->ID) $ar_articles[] = $obj_result->ID;
        }
        return $ar_articles;
    }

    public function addUserArticle($uid, $pid) {
        global $wpdb;
        $query = "SELECT umeta_id FROM wp_usermeta WHERE user_id='$uid' AND meta_key='af_user_article' AND meta_value='$pid'";
        $ar_results = $wpdb->get_results($query,'OBJECT');
        if (!$ar_results) {
            $query = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES ('$uid', 'af_user_article', '$pid')";
            $wpdb->query($query);
        }
    }

    public function removeUserArticle($uid, $pid) {
        global $wpdb;
        $query = "DELETE FROM wp_usermeta WHERE user_id='$uid' AND meta_key='af_user_article' AND meta_value='$pid'";
        $wpdb->query($query);
    }

    public function generateUserToken() {
        $token = '';
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";

        $char_array = str_split($chars);
        for ($i = 0; $i < 16; $i++){
            $rand_item = array_rand($char_array);
            $token .= "".$char_array[$rand_item];
        }
        return $token;
    }

    public function validateUserToken($uid, $token) {
        $uid = preg_replace("/\D/", "", $uid);
        $token = preg_replace("/\W/", "", $token);
        if (!($uid && $token))
            return false;
        if (strlen($token) != 16)
            return false;

        global $wpdb;
        $query = "SELECT meta_value FROM wp_usermeta WHERE user_id='$uid' AND meta_key='af_user_token'";
        $ar_results = $wpdb->get_results($query, 'OBJECT');
        if (!$ar_results) {
            return false;
        }
        $tokens_match = 0;
        foreach ($ar_results as $obj_result){
            if ($obj_result->meta_value == $token) {
                $tokens_match = 1;
            }
        }
        if (!$tokens_match) {
            return false;
        }
        return true;
    }

/**
     * @param  string $ticker
     * @return null (data printed to stdout)
     */
    public function getTickerContent($ticker='')
    {
	global $af_posts;
	$content = '';
	if(!$ticker) return '';
	$related_posts = new WP_Query(
		array(
			'post_type' => 'post',
			's' => $ticker,
            'posts_per_page' => 5
		)
	);
	if($related_posts->have_posts()){
		echo '<section class="excerpt-list related-posts watchlist-related-posts">';
		while($related_posts->have_posts()){
			$related_posts->the_post();
			echo '<div class="row"><div class="small-12 columns">';
			$af_posts->af_get_post_excerpt($post, 'article_thumb', '');
			echo '</div></div>';
		}
		echo '</section>';
	}
	wp_reset_query();

        return '';
    }

/**
     * @param  string $ticker
     * @return array
     */
    public function getTickerData($symbol='')
    {
	if(!$symbol){
            return array();
        } elseif (is_scalar($symbol)) {
                $symbols = $symbol;
        } elseif (is_array($symbol)) {

            $symbols = '';
            foreach ($symbol as $cur_symbol){
                if ($symbols) $symbols .= ",";
                $symbols .= $cur_symbol;
            }
        }
        if(!$symbols) return array();
	//changed to paid endpoint with token
        //$endpoint = 'https://api.iextrading.com/1.0/stock/market/batch/?symbols='.$symbols.'&types=quote&filter=symbol,companyName,latestPrice,change,changePercent';
        $endpoint = 'https://cloud.iexapis.com/v1/stock/market/batch/?token=pk_d9220ef82e3042b685777b863dba5186&symbols='.$symbols.'&types=quote&filter=symbol,companyName,latestPrice,change,changePercent';

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $content  = curl_exec($ch);
        if(curl_errno($ch)){
                return array();
        }else{
                $arTickerCharts = json_decode($content,true);
                $arCharts = array();
                foreach($arTickerCharts as $keySymbol => $arQuote){
                        if(!$arQuote['quote']['symbol']) continue;
                        $arCharts[] = array(
                                'Symbol' => $arQuote['quote']['symbol'],
                                'Name' => $arQuote['quote']['companyName'],
                                'LastTradePriceOnly' => $arQuote['quote']['latestPrice'],
                                'Change' => $arQuote['quote']['change'],
                                'ChangeinPercent' => $arQuote['quote']['changePercent']
                        );
                }
        }
        curl_close($ch);

        return $arCharts;
    }

    // Depricated
    public function getTickerData_Yahoo($symbol) {
        if (!$symbol) {
            return array();
        } elseif (is_scalar($symbol)) {
            $ticker_query = 'SELECT Symbol, Name, LastTradePriceOnly, Change, ChangeinPercent FROM yahoo.finance.quotes WHERE symbol = "' . $symbol . '"';
        } elseif (is_array($symbol)) {
            $symbols = '';
            foreach ($symbol as $cur_symbol){
                if ($symbols) $symbols .= ",";
                $symbols .= '"' . $cur_symbol . '"';
            }
            $ticker_query = 'SELECT Symbol, Name, LastTradePriceOnly, Change, ChangeinPercent FROM yahoo.finance.quotes WHERE symbol IN (' . $symbols . ')';
        } else {
            return array();
        }

        $endpoint = 'https://query.yahooapis.com/v1/public/yql';
        $params = array(
            'q'        => $ticker_query,
            'format'   => 'json',
            'env'      => 'store://datatables.org/alltableswithkeys',
            'callback' => '',
        );

        $p = '';
        foreach ($params as $key => $value) {
            $p .= $key . '=' . urlencode($value) . '&';
        }
        $p = trim($p, '&');
        $url = $endpoint . '?' . $p;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            return array();
        } else {
            $ar_ticker_charts = json_decode($content, true);
            if (isset($ar_ticker_charts['query']['results']['quote'][0])) {
                $ar_charts = $ar_ticker_charts['query']['results']['quote'];
            } elseif (isset($ar_ticker_charts['query']['results']['quote'])) {
                $ar_charts = array();
                $ar_charts[] = $ar_ticker_charts['query']['results']['quote'];
            } else {
                $ar_charts = array();
            }
        }
        curl_close($ch);
        return $ar_charts;
    }

    // Get subscription data from middleware
    public function getCustomerSubscriptions() {
        global $af_auth;
        $ar_active_subs = array('R', 'P', 'U');
        $ar_response = $af_auth->get_mw_data('get_sub_customernumber', array('customernumber' => $this->objAFUser['advantage_id']));
        $ar_subs = array();

        if (is_array($ar_response) && count($ar_response)) {
            foreach ($ar_response as $ar_subscription) {
                if (isset($ar_subscription['temp']) && $ar_subscription['temp'] || !isset($ar_subscription['id']['item']['itemNumber']) || !$ar_subscription['id']['item']['itemNumber'] || !isset($ar_subscription['circStatus']) || !in_array($ar_subscription['circStatus'], $ar_active_subs)) {
                    continue;
                }
                $ar_subs[$ar_subscription['id']['item']['itemNumber']] = array(
                    'pubcode' => $ar_subscription['id']['item']['itemNumber'],
                    'circStatus' => $ar_subscription['circStatus'],
                    'issuesRemaining' => $ar_subscription['issuesRemaining'],
                    'renewMethod' => $ar_subscription['renewMethod'],
                    'subRef' => $ar_subscription['id']['subRef'],
                    'description' => $ar_subscription['id']['item']['itemDescription'],
                    'startDate' => $ar_subscription['startDate'],
                    'expirationDate' => $ar_subscription['expirationDate'],
                    'lastIssue' => $ar_subscription['lastIssue']
                );
            }
        }
        return $ar_subs;
    }

    // Convert middleware subscription data to friendly array
    public function af_subscription_data() {
        global $af_publications;

        // Retrieve middleware data
        $subscriptions = $this->getCustomerSubscriptions();
        $data = array();

        // Create data array
        foreach ($subscriptions as $sub) {

            // Get pub IDs
            $pub_id = $af_publications->af_get_pub_ID($sub['pubcode']);

            // Only get pubs for this website
            if (!$pub_id) {
                continue;
            }

            $data[] = array(
                'sub_pub_id' => $pub_id,
                'sub_pubcode' => $sub['pubcode'],
                'sub_name' => get_the_title($pub_id),
                'sub_start' => $this->af_sub_date_format($sub['startDate'], 'M Y'),
                'sub_end' => $this->af_sub_expiration_date($sub['expirationDate'], $sub['renewMethod']),
                'sub_renew' => $this->af_sub_renewal_method($sub['renewMethod'], $pub_id)
            );
        }
        return $data;
    }

    // Format middleware date
    public function af_sub_date_format($sub_date, $format) {
        // Match incoming format
        $date = DateTime::createFromFormat('Y-m-d h:i:s', $sub_date);

        // Return empty if doesnt match
        if (!$date) {
            return '';
        }

        // Reformat
        $new_date = $date->format($format);
        return $new_date;
    }

    // Get expiration
    public function af_sub_expiration_date($exp_date, $renew_method) {
        // If lifetime
        if ($renew_method == 'A')
            return '<span class="sub-noexp">No Expiration</span>';

        // Else, return format
        return $this->af_sub_date_format($exp_date, 'M Y');
    }

    // Get renewal method
    public function af_sub_renewal_method($renew_method, $pub_id) {
        // If lifetime
        if ($renew_method == 'A') {
            return '<span class="sub-lifetime">Lifetime</span>';
        }

        // Auto Renew
        if ($renew_method == 'C') {
            return '<span class="sub-auto">Enrolled In Auto Renew</span>';
        } else {
            // Return renewal button
            return $this->af_get_renewal_button($pub_id);
        }
    }

    // Create renewal button
    public function af_get_renewal_button($pub_id) {
        $renewal_url = get_field('renewal_url', $pub_id);
        if (!$renewal_url) {
            return;
        }
        return '<a href="' . $renewal_url . '" target="_blank" class="button renew-button" data-event-category="Renew Button">Renew</a>';
    }

    public function getCustomerAddress() {
        global $af_auth;
        $ar_responses = $af_auth->get_mw_data('get_postaladdress_customernumber',array('customernumber' => $this->objAFUser['advantage_id']));
        $ar_address = array();

        if (null !== $ar_responses) {
            foreach ($ar_responses as $ar_response) {
                if (is_array($ar_response) && count($ar_response) && isset($ar_response['id']['customerNumber'])) {
                    $ar_address[$ar_response['id']['customerNumber']] = array(
                        'firstName' => $ar_response['firstName'],
                        'lastName' => $ar_response['lastName'],
                        'emailAddress' => $ar_response['emailAddress']['emailAddress'],
                        'phoneNumber' => $ar_response['phoneNumber'],
                        'street' => $ar_response['street'],
                        'street2' => $ar_response['street2'],
                        'city' => $ar_response['city'],
                        'state' => $ar_response['state'],
                        'postalCode' => $ar_response['postalCode'],
                        'addressCode' => $ar_response['id']['addressCode'],
                    );
                }
            }
        }
        return $ar_address;
    }

    public function frmUpdateCustomerAddress($ar_data) {
        global $af_auth;

        if (!is_array($ar_data)) {
            return 'No address data provided.';
        }
        if (!isset($ar_data['customerNumber']) || !isset($ar_data['addressCode'])) {
            return 'Required information is missing.';
        }

        // Create empty array of available fields to update
        $ar_fields = array(
            'customerNumber' => '',
            'addressCode' => '',
            'firstName' => '',
            'lastName' => '',
            'emailAddress' => '',
            'phoneNumber' => '',
            'street' => '',
            'street2' => '',
            'city' => '',
            'state' => '',
            'postalCode' => ''
        );

        foreach ($ar_fields as $key => $val) {
            if ($ar_data[$key] == '') {
                unset($ar_fields[$key]);
                continue;
            }
            if (isset($ar_data[$key])) {
                $ar_fields[$key] = $ar_data[$key];
            }
        }

        $ar_responses = $af_auth->get_mw_data('post_customer_update_postaladdress', $ar_fields);
        return $ar_responses;
    }



    public function frmUpdateCustomerPassword($ar_data) {
        global $af_auth;

        if (!is_array($ar_data)) {
            return 'No password data provided.';
        }
        if (!$ar_data['customerNumber'] || !$ar_data['username'] || !$ar_data['existingPassword'] || !$ar_data['newPassword'] || !$ar_data['newPassword2']) {
            return 'Required information is missing.';
        }
        if ($ar_data['newPassword'] != $ar_data['newPassword2']) {
            return 'New password does not match confirm new password.';
        }

        $ar_fields = array(
            'customerNumber' => '',
            'username' => '',
            'existingPassword' => '',
            'newPassword' => ''
        );

        foreach ($ar_fields as $key => $val){
            if ($ar_data[$key] == '') {
                unset($ar_fields[$key]);
                continue;
            }
            if (isset($ar_data[$key])) $ar_fields[$key] = $ar_data[$key];
        }
        $ar_responses = $af_auth->get_mw_data('post_account_update_password', $ar_fields, 1);
        return $ar_responses;
    }

    // Build the questions array to match legacy from acf fields
    public function af_build_profile_questions_array($acf_array) {

        // Get admin set ACF field options
        $sections = $acf_array;
        $acf_questions = array();

        foreach ($sections as $section) {

            // Set section questions
            $questions = $section['section_questions'];
            $arr_questions = array();

            foreach ($questions as $question) {
                $arr_answers = array();

                // Set question/display text
                $arr_answers['display'] = $question['answer'];

                // Get associated pubs as ids, retrieve assocated pubcode and create pipe separated string
                $pubs = $question['associated_pubs'];
                $pubcodes = array();
                foreach ($pubs as $pub) {
                    $pubcodes[] = get_field('pubcode', $pub);
                }
                $arr_answers['results'] = implode('|', $pubcodes);

                // Set answer_id as key for each answers array
                $arr_questions[$question['answer_id']] = $arr_answers;
            }

            // Set section field type and display/title
            $arr_sections = array();
            $arr_sections['fldType'] = $section['section_type'];
            $arr_sections['display'] = $section['section_title'];

            // Append questions if applicable
            if (!empty($arr_questions)) {
                $arr_sections['answers'] = $arr_questions;
            }

            // Set section_id as key for each section array
            $acf_questions[$section['section_id']] = $arr_sections;
        }
        return $acf_questions;
    }

    /*
     * The is a legacy feature from AF that may get retired.
     * This code is largely unchanged, but is modified to fit new theme,
     * limit to site-specific pub codes and utilize new methods
     */
    public function getUserFinancialProfile() {
        global $wpdb, $post, $af_auth, $af_publications;

        // ACF Fields to replace static inputs
        $fields = get_fields($post->ID);
        $financial_profile = $fields['financial_profile'];
        $presurvey_content = $fields['presurvey_content'];
        $postsurvey_content = $fields['postsurvey_content'];
        $postsurvey_content_results = $fields['postsurvey_content_results'];

        // Get processed array from ACF fields to match legacy array
        $ar_questions = $this->af_build_profile_questions_array($financial_profile);

        // Return if no questions supplied
        if (empty($ar_questions)) {
            return;
        }

        // Check if last pub purchased is set in url query string and update user meta
        if (isset($_GET['pb']) && preg_match("/^[A-Z]{3}$/", strtoupper($_GET['pb']))) {
            $uid = $this->objAFUser['id'];
            $pb = strtoupper($_GET['pb']);
            if ($uid) {
                $query = "SELECT umeta_id FROM wp_usermeta WHERE user_id='$uid' AND meta_key='customer_last_pub_purchased'";
                $ar_results = $wpdb->get_results($query,'OBJECT');
                if (!$ar_results) {
                    $query = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES ('$uid', 'customer_last_pub_purchased', '$pb')";
                    $wpdb->query($query);
                } else {
                    $metaid = $ar_results[0]->umeta_id;
                    $query = "UPDATE wp_usermeta SET meta_value='$pb' WHERE umeta_id='$metaid'";
                    $wpdb->query($query);
                }
            }
        }
        $default_content = $presurvey_content;
        $content = '';
        $ar_user_answers = array();
        $query = '';
        $last_pub_purchased = '';
        $total_question_count = 0;

        foreach ($ar_questions as $send_key => $ar_answers){
            if ('header' == $ar_answers['fldType']) {
                continue;
            }
            if ($query) {
                $query .= ",";
            }
            $query .= "'" . $send_key . "'";
            $ar_user_answers[$send_key] = '';
            $total_question_count++;
        }

        // Get current data from user meta
        $current_user_meta = get_user_meta($this->objAFUser['id']);

        foreach ($current_user_meta as $ar_meta => $value){
            if (isset($ar_user_answers[$ar_meta])) {
                $ar_user_answers[$ar_meta] = $value[0];
            }
        }

        if (isset($current_user_meta['customer_last_pub_purchased'][0])) {
            $last_pub_purchased = $current_user_meta['customer_last_pub_purchased'][0];
        } elseif (isset($_GET['pb']) && preg_match("/^[A-Z]{3}$/", strtoupper($_GET['pb']))) {
            $last_pub_purchased = $_GET['pb'];
        } else {
            $last_pub_purchased = '';
        }

        // Check if they are posting answers and update user meta
        if ($_POST) {
            $uid = $this->objAFUser['id'];
            foreach ($_POST as $key => $val) {
                if (isset($ar_questions[$key])) {
                    if ('header' == $ar_questions[$key]['fldType']) {
                        continue;
                    }
                    $qry_val = "";
                    if (('checkboxes' == $ar_questions[$key]['fldType']) || ('multiselect' == $ar_questions[$key]['fldType'])) {
                        if (count($_POST[$key])) {
                            $qry_val = implode('|', $_POST[$key]);
                        } elseif ($_POST[$key]) {
                            $qry_val = $_POST[$key];
                        }
                    } else {
                        $qry_val = $_POST[$key];
                    }
                    $query = "SELECT umeta_id FROM wp_usermeta WHERE user_id='$uid' AND meta_key='$key'";
                    $ar_results = $wpdb->get_results($query, 'OBJECT');
                    if (!$ar_results) {
                        $query = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES ('$uid', '$key', '$qry_val')";
                        if ($wpdb->query($query)) {
                            $ar_user_answers[$key] = $qry_val;
                        }
                    } else {
                        $metaid = $ar_results[0]->umeta_id;
                        $query = "UPDATE wp_usermeta SET meta_value='$qry_val' WHERE umeta_id='$metaid'";
                        if ($wpdb->query($query)) {
                            $ar_user_answers[$key] = $qry_val;
                        }
                    }
                }
            }
        }

        //return to calling page
        $profile_completion_data = (count($ar_user_answers) / $total_question_count) * 100;

        $inc = 0;

        foreach ($ar_questions as $send_key => $ar_answers) {
            if ('header' == $ar_answers['fldType']) {
                // Offset closing divs
                if ($inc != 0) {
                    $content .= '</section>';
                }

                // Open question sections
                $content .= '
                <section class="questions-section questions-section-' . sanitize_title($ar_answers['display']) . '">
                    <h3 class="questions-header">' . $ar_answers['display'] . '</h3>
                ';
                continue;
            }
            $inc++;
            $content .= '<div class="question"><p class="question-title">' . $ar_answers['display'] . '</p>';

            if ('multiselect' == $ar_answers['fldType']) {
                $content .= "<select class='profile-input' name='" . $send_key . "[]' id='" . $send_key . "[]' multiple><option value=''>Choose One</option>";
                $ar_check = $ar_user_answers[$send_key] ? explode('|', $ar_user_answers[$send_key]) : array();
            } elseif ('select' == $ar_answers['fldType']) {
                $content .= "<select class='profile-input' name='$send_key' id='$send_key'><option value=''>Select One</option>";
            } elseif ('checkboxes' == $ar_answers['fldType']) {
                $ar_check = $ar_user_answers[$send_key] ? explode('|', $ar_user_answers[$send_key]) : array();
                if ($_POST && !isset($_POST[$send_key])) {
                    $ar_check = array(); // Post with no checkboxes in checkbox array
                }
            }
            foreach ($ar_answers['answers'] as $key => $ar_opt) {
                if ('multiselect' == $ar_answers['fldType']) {
                    if (count($ar_check)) {
                        $selected = in_array($key, $ar_check) ? ' selected' : '';
                    } else {
                        $selected = '';
                    }
                    $content .= "<div class='answer-wrapper'><option value='$key'$selected>" . $ar_opt['display'] . "</option></div>";
                } elseif ('checkboxes' == $ar_answers['fldType']) {
                    $is_checked = count($ar_check) && in_array($key, $ar_check) ? ' checked' : '';
                    $content .= "<div class='answer-wrapper'><label for='" . $send_key . $key . "'><input class='profile-input' name='" . $send_key . "[]' id='" . $send_key . $key . "' type='checkbox' value='$key'$is_checked><p class='answer'>" . $ar_opt['display'] . "</p></label></div>";
                } elseif ('checkbox' == $ar_answers['fldType']) {
                    $is_checked = ($key == $ar_user_answers[$send_key]) ? ' checked' : '';
                    if ($_POST && !isset($_POST[$send_key])) {
                        $is_checked = ''; // Post with checkbox unchecked
                    }
                    $content .= "<div class='answer-wrapper'><input class='profile-input' name='" . $send_key . "' id='" . $send_key . "' type='checkbox' value='$key'$is_checked><p class='answer'>" . $ar_opt['display'] . "</p></div>";
                } elseif ('radio' == $ar_answers['fldType']) {
                    $is_checked = ($key == $ar_user_answers[$send_key]) ? ' checked' : '';
                    $content .= '<div class="answer-wrapper"><label for="' . $send_key . '_' . $key . '"><input class="profile-input" type="radio" name="' . $send_key . '" id="' . $send_key . '_' . $key . '" value="' . $key . '" ' . $is_checked . '><p class="answer">' . $ar_opt['display'] . '</p></label></div>';
                } elseif ('select' == $ar_answers['fldType']) {
                    $selected = ($key == $ar_user_answers[$send_key]) ? ' selected' : '';
                    $content .= "<div class='answer-wrapper'><option value='$key'$selected>" . $ar_opt['display'] . "</option></div>";
                }
            }
            if ('multiselect' == $ar_answers['fldType'] || 'select' == $ar_answers['fldType']) {
                $content .= "</select>";
            }
            $content .= '</div>';
        }
        $content .= '</section>';

        if ($content){
            $is_done = '';
            $is_empty = $default_content;
            $ar_suggested_pubs = array();
            $send_lytics_json_data = array();
            $max_score = 0;

            foreach ($ar_user_answers as $key => $val){
                if (!$val) {
                    $is_done = false;
                }
                if ($val) {
                    $send_lytics_json_data[$key] = $val;
                    $is_empty = '';
                    if (strpos($val, '|')) {
                        $ar_opt = explode('|', $val);
                    } else {
                        $ar_opt = array($val);
                    }
                    foreach ($ar_opt as $cur_val) {
                        if (isset($ar_questions[$key]['answers'][$cur_val]['results'])) {
                            if (strpos($ar_questions[$key]['answers'][$cur_val]['results'], '|')) {
                                $ar_tmp = explode('|', $ar_questions[$key]['answers'][$cur_val]['results']);
                            } else {
                                $ar_tmp = array($ar_questions[$key]['answers'][$cur_val]['results']);
                            }
                            foreach ($ar_tmp as $tmp) {
                                if (!$tmp) {
                                    continue;
                                }
                                if (!isset($ar_suggested_pubs[$tmp])) {
                                    $ar_suggested_pubs[$tmp] = 0;
                                }
                                $ar_suggested_pubs[$tmp]++;
                                if ($ar_suggested_pubs[$tmp] > $max_score) {
                                    $max_score = $ar_suggested_pubs[$tmp];
                                }
                            }
                        }
                    }
                }
            }
            $buffer = round($max_score / 3);
            $max_score = $max_score + $buffer;

            if (count($ar_suggested_pubs)) {
                $is_done = $postsurvey_content;
                $ar_pubs = $af_publications->af_get_active_pub_ids();
                $ar_pubcodes = $af_publications->af_get_active_pubcodes();

                // Get user meta that contains which pubs from this website user has access to
                $ar_subscriptions = $this->objAFUser['subscribed_pubs'];

                if ($this->objAFUser['email']) {
                    $send_lytics_json_data['email'] = $this->objAFUser['email'];
                }

                // Return to calling page
                if (count($send_lytics_json_data)) {
                    $send_lytics_json_data = json_encode($send_lytics_json_data);
                }
                
                if (isset($ar_subscriptions) && is_array($ar_subscriptions)) {
                    foreach ($ar_pubs as $pidx => $pub) {
                        foreach ($ar_subscriptions as $sidx => $sub) {
                            if ($sub['pub_code'] == $pub['pubcode']) {
                                $pubs[$pidx]['hasPub'] = 1;
                            }
                        }
                    }
                }

                // Get all non-suggested pubs and append with score of 0
                foreach ($ar_suggested_pubs as $key => $value) {
                    $suggested_pubcodes[] = $key;
                }
                $not_suggested = array_diff($ar_pubcodes, $suggested_pubcodes);
                foreach ($not_suggested as $pub) {
                    $ar_suggested_pubs[$pub] .= 0;
                }

                // append buffer if recommended, or already owned
                foreach ($ar_suggested_pubs as $pubcode => $value) {
                    if ($af_auth->has_pub_access($pubcode) || $value > 0) {
                        $ar_suggested_pubs[$pubcode] = $value + $buffer;
                        // if is last purchased pub, buffer rating further
                        if ($pubcode == $last_pub_purchased && $value != $max_score) {
                            $ar_suggested_pubs[$pubcode] = $max_score - 1;
                        }
                    } else {
                        continue;
                    }
                }
                arsort($ar_suggested_pubs);

                // Build data array for results chart
                foreach ($ar_suggested_pubs as $key => $val) {
                    // if (!($key && $val)) continue;
                    foreach ($ar_pubs as $pub) {
                        $pubcode = get_field('pubcode', $pub);

                        if ($key == $pubcode) {

                            $has_access = $af_auth->has_pub_access($pubcode) ? 1 : 0;

                            // for our chart
                            $is_active_pub = ($key == $last_pub_purchased && $has_access) ? 1 : 0;
                            $transparency_level = round(($val / $max_score),2);
                            $bgcolor = ($pub['hasPub']) ? "rgba(38, 127, 57, " . round(($val / $max_score), 2) . ")" : "rgba(162, 148, 41, " . round(($val / $max_score), 2) . ")";
                            $chart[] = array(
                                'score' => $val,
                                'max_score' => $max_score + 0,
                                'transparency_level' => $transparency_level,
                                'completion_percent' => ($transparency_level * 100),
                                'pub_code' => "$key",
                                'pub_name' => get_the_title($pub),
                                'pub_url' => get_permalink($pub),
                                'subscribe_url' => get_field('subscribe_url', $pub),
                                'active_pub' => $is_active_pub,
                                'has_pub' => $has_access,
                                'backgroundColor' => "$bgcolor"
                            );
                        }
                    }
                }
            }

            if (!empty($ar_suggested_pubs))
                //return to calling page
                $pubrecommendations = 'true';

            $content = "$is_empty$is_done<form name='frmSurvey' id='frmSurvey' action='" . site_url('/my-profile/') . "' method='post'>" . $content . "<input class='btn_profile button' type='submit' name='submit' value='Save My Profile'></form>";
        }

        // Return to calling page
        if (!$content) {
            $content = $default_content;
        }

        $data = array(
            'content' => $content,
            'results' => $chart,
            'completion' => $profile_completion_data,
            'cta' => $postsurvey_content_results,
            'lytics_data' => $send_lytics_json_data
        );
        return $data;
    }

    // Sanitize form input data
    public function af_sanitize_input($data) {
        $data = htmlspecialchars(stripslashes(trim($data)));
        return $data;
    }

    // Display input error
    public function af_display_input_error($msg = '') {
        if ($msg) {
            echo '<p class="input-error-msg">' . $msg . '</p>';
        }
        return;
    }

    // Display callout message
    public function af_callout_msg($msg = '', $type = '') {
        $html = '';
        if ($msg) {
            $html .= '<div class="callout ' . $type . ' small">' . $msg . '</div>';
        }
        return $html;
    }

    /**
     * Check user account data to see if eligible for renewal
     * Ineligible if don't have pub, lifetime, or already on autorenew
     * @param  string $pubcode
     * @return boolean
     */
    public function af_check_renewal_eligibility( $pubcode ) {
        $renew_eligible = false;
        if(!is_user_logged_in()) return $renew_eligible;
        $user = wp_get_current_user();
        $meta = get_usermeta( $user->ID, 'agora_middleware_aggregate_data', true );
        if($meta) {
            $subscriptions = $meta->subscriptionsAndOrders->subscriptions;
            if(isset($subscriptions)) {
                foreach($subscriptions as $row) {
                    if($row->id->item->itemNumber == $pubcode) {
                        $renew_eligible = $row->renewMethod == 'A' || $row->renewMethod == 'C' ? false : true ;
                        break;
                    } else {
                        continue;
                    }
                }
            }
        }
        return $renew_eligible;
    }

    /**
     * Add custom query var for tickers
     * @param  array $vars
     * @return array
     */
    public function af_ticker_query_var($vars) {
        $vars[] .= 'ticker';
        return $vars;
    }
    /**
     * Add url rewrite for ticker pagse
     * @return null
     */
    public function af_ticker_rewrite() {
        add_rewrite_rule('^ticker/([a-zA-Z_]+)/?', 'index.php?pagename=ticker&ticker=$matches[1]', 'top');
    }

}
