<?php
/**
*	A class to wrap middleware calls into nice php methods and return shiny objects
*
*	@package agora_core_framework
*	@subpackage agora_middleware_wrapper
*	@author Ciaran McGrath
*
**/

class agora_middleware_wrapper extends agora_api_wrapper{

	/**
	 * Object container for the logging system.
	 * @var object
	 */
	private $log;

	/**
	 * @var
	 */
	private $core;
    /**
     * @var
     */
	private $affiliate_code;

	/**
	 *  PHP Constructor
	 *
	 * @param array $config The configuration, loaded by the base class and passed to the MW wrapper on instantiation
	 *
	 */
	public function __construct($config, agora_core_framework $core){

		if($config['production'] == 1){
			$this->url = trim($config['prod_url']);
			$this->token = trim($config['prod_token']);
		}else{
			$this->url = trim($config['uat_url']);
			$this->token = trim($config['uat_token']);
		}

		if ( ! empty( $config['caching'] ) ) {
			$this->caching = trim($config['caching']);
		} else {
			$this->caching = 0;
		}

		$this->log = new agora_log_framework();

		$this->core = $core;

		$this->http_args = array('headers' => array('token' => $this->token), 'sslverify' => false);
		$this->affiliate_code	= ( ! empty( $config[ 'affiliate_code' ] ) ? trim($config['affiliate_code']) : '' );
		$this->url = 'https://' . $this->url . '/middleware/';
	}

	/**
	 * findCustomerNumberByContactIdOrgId
	 * @deprecated
	 * Get the customer number from their Message Central Contact ID and Org ID
	 * @param $contact_id
	 * @param $org_id
	 *
	 * @return mixed
	 */
	function get_customer_number_by_contact_id_org_id($contact_id, $org_id){
		$url = $this->url . 'customer/contactid/' . $contact_id . '/orgid/' . $org_id;
		return $this->_get($url);
	}

    /**
     * 1.11 findCustomerNumberByContactIdOrgIdStackName
     * Get the customer number from their Message Cntral Contact ID, Org ID and Stack Name
     * @param $contact_id
     * @param $org_id
     * @param $stack_name
     *
     * @return mixed
     */
    function get_customer_number_by_contact_id_org_id_stack_name($contact_id, $org_id, $stack_name){
        $url = $this->url . 'customer/contactid/' . $contact_id . '/orgid/' . $org_id . '/stackname/' . $stack_name;
        return $this->_get($url);
    }

	/**
	 * 1.12 findEmailAddressbyContactIdOrgIdStackName
	 * Get the customer email address from their Message Cntral Contact ID, Org ID and Stack Name
	 * @param $contact_id
	 * @param $org_id
	 * @param $stack_name
	 *
	 * @return mixed
	 */
	function get_customer_email_by_contact_id_org_id_stack_name($contact_id, $org_id, $stack_name){
		$url = $this->url . 'lookup/emailaddress/contactid/' . $contact_id . '/orgid/' . $org_id . '/stack/' . $stack_name;

		return $this->_get($url);
	}

	/**
	 * 2.3	findAccountByEmailAddress
	 * Get Customer account by email address
	 * @param  string $email email address
	 * @return object
	 */
	function get_account_by_email($email){
		$url = $this->url . 'account/emailaddress?email=' . urlencode($email);
        $this->http_args['timeout'] = '10';
		return $this->_get($url);
	}

	/**
	*  5.3	findLoginAggregateData
	*  Get Customer Aggregate Data for a given Username and Password
	*
	*	@param string $username An advantage username
	*   @param string $password An advantage password to accompany the Username
	*	@return object
	**/
	function get_aggregate_data_by_login($username, $password){

		$username = base64_encode(stripslashes($username));
		$password = base64_encode($password);

        //add the current URL to http_args
        $current_url = apply_filters('agora_get_login_url', get_site_url());

        $this->http_args['headers']['source_url'] = $current_url;
		$this->http_args['headers']['website'] = $_SERVER['SERVER_NAME'];

		$url = $this->url . 'data/username/' . $username . '/password/' . $password;

		$result = $this->_get($url);
		// Since MW doesn't return a 404 for resource not found we need to fake it
		if(
			( isset( $result->accounts ) && $result->accounts )
			OR ( isset( $result->emailAddresses ) && $result->emailAddresses )
			OR isset( $result->subscriptionsAndOrders ) && $result->subscriptionsAndOrders
		){
			return $result;
		}else{
			return new WP_Error('404', 'Not found', $result);
		}
	}

	/**
	*   3.2 findSubscriptionsByCustomerNumber
	*	Get Customer Subscriptions for a given customer ID, both active AND inactive
	*
	*	@param string $customer_id
	*	@return array
	**/
	function get_subscriptions_by_id($customer_id){
		$url = $this->url . 'sub/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	*   3.1	findActiveSubscriptionsByCustomerNumber
	*	Get *ACTIVE* Customer Subscriptions for a given customer ID
	*
	*	@param string $customer_id
	*	@return array
	**/
	function get_active_subscriptions_by_id($customer_id){
		$url = $this->url . 'sub/active/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	*   1.5	findEmailAddressesByCustomerNumber
	*	Get customer email address by customer ID
	*
	*	@param string $customer_id
	*	@return array An array of email addresses for the given customer ID
	**/
	function get_customer_email_by_id($customer_id){

		$url = $this->url . 'customer/emailaddress/customernumber/' . $customer_id;
		return $this->_get($url);
	}

    /**
     *   8.1 findEmailFulfillmentHistoryByCustomerNumber
	 * 	 Get email fulfillment history for a given customer ID
     *
     *   @param string $customer_id
     *   @return array
     */
    function get_email_fulfillment_history_by_id($customer_id){

        $url = $this->url . 'emailfulfillment/history/customernumber/' . $customer_id;
        return $this->_get($url);
    }

	/**
	*   1.2	findPostalAddressesByCustomerNumber
	*	Get Customer Address by customer ID
	*
	*	@param string $customer_id
	*	@return array Associative array of returned data
	**/
	function get_customer_address_by_id($customer_id){

		$url = $this->url . 'postaladdress/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	 * 2.2	findAccountByCustomerNumber
	 * Find an account using the customer number.
	 *
	 * Service returns the account information for the customer number supplied in the request URL.
	 * The response is restricted to the portalCode/authGroup tied to the token.
	 *
	 * @param $customer_id
	 *
	 * @return array
	 */
	function get_account_by_id($customer_id){
		$url = $this->url . 'account/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	*   2.2	findAccountByCustomerNumber
	*	Get Customer By account Number
	*
	*	@param string $customer_id Customer Advantage User ID
	*	@return array Associative array of returned data
	 *
	**/
	function get_customer_by_id($customer_id){

		$url = $this->url . 'account/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	*   1.1	findCustomerIdentifier
	*	Get Customer ID From username and password
	*
	*	@param string $username
	*	@param string $password
	*	@return array Associative array of returned data
	**/
	function get_customer_by_login($username, $password){

		$username = base64_encode($username);
		$password = base64_encode($password);

		$url = $this->url .'customer/username/'. $username .'/password/' . $password;
		$result =  $this->_get($url);

		$result->core64['username'] = $username;
		$result->core64['password'] = $password;
		return $result;
	}

	/**
	 * 1.3	findPostalAddressesByEmailAddress
	 * Get the customer’s demographic information using their email address—this call is like the previous call, but uses emailAddress instead of customerNumber
	 * The postal address data block contains demographic information such as name, postalAddress, emailAddress, etc.
	 * The  data returned in this call can be used to pre-populate personalized fields on a website
	 *
	 * @param $email
	 *
	 * @return array
	 */
	public function get_postal_addresses_by_email($email){
		$url = $this->url . 'postaladdress/emailaddress/' . urlencode($email);
		return $this->_get($url);
	}

    /**
     * Get Subscription, Postal Address By Sub Ref
     * Get the subscription and attached postal address for a customer using the subref
     *
     * @param $ref
     *
     * @return array
     */
    public function get_postal_address_by_subref ( $subref )
    {
        $url = $this->url . 'sub/postaladdress/subref/' . $subref;
        return $this->_get($url);
    }

    /**
	 * 1.14	findLowestCustomerNumberByEmailAddress
	 * Find the lowest customer number using the customer’s e-mail address
	 *
	 * Service returns the lowest customer number for e-mail address supplied in the request URL.
	 * Result set is not restricted to any portalCode/authGroup.
	 *
	 * @param $email
	 *
	 * @return array
	 */
	function get_lowest_customer_number_by_email($email){
		$url = $this->url . 'customer/findlowestactivecustomernumber/emailaddress/' . urlencode($email);
		return $this->_get($url);
	}

	/**
	*	Get Customer subscriptions by login
	*
	*	@param string $username
	*	@param string $password
	*	@return object PHP object of user data
	**/
	function get_subscriptions_by_login($username, $password){
		$customer = $this->get_customer_by_login($username, $password);
		if($customer){
			return $this->get_subscriptions_by_id($customer->customerNumber);
		}
	}

	/**
	*	Get future subscriptions by subref
	*
	*	@param string $subref
    *
	*	@return object PHP object of user data
	**/
	function get_future_subscriptions_by_subref($subref){
        $url = $this->url . 'sub/future/subref/' . $subref;
        return $this->_get($url);
	}

	/**
	*	Method to test middleware connectivity.
	*
	*	@param void
	*	@return string of HTML that's returned from default middlware call, else a JSON object of error information
	**/
	function ping_test(){
		// Just grab the base url and return the result. Todo: set a some sort of timeout to catch
		$url = $this->url;

		$result = wp_remote_get($url);

		if($result['response']['code'] == 200){
			return wp_remote_retrieve_body($result);
		}else{
			return "Response Code: " . $result['response']['code'];
		}
	}

	/**
	 * 1.16	createCustomer
	 * Create a new customer
	 *
	 * Service returns the customer number for newly-created customer.
	 * Result set is not restricted to any portalCode/authGroup. The body of the response will return the following outputs
	 *
	 * @param $email
	 *
	 * @return array|mixed|WP_Error
	 */
	public function put_create_customer_by_email($email){
		$url = $this->url . 'customer/create';
		$payload = array('emailAddress' => $email);

		return $this->_post($url, $payload);
	}

	/**
	 * 7.11	unsubCustomerSignup
	 *
	 * Unsubscribe a customer signup.
	 *
	 * @param      $list_code  string
	 *                         Code that identifies the list from which the customer will unsubscribe
	 * @param      $email_address string
	 *                          The customer’s e-mail address
	 * @param null $reference string
	 *                          Reference number to track this unsub. This can be any random, alphanumeric ID
	 *                          If no reference number is supplied, MW2 generates it by applying the hash
	 *                          algorithm on the other required fields supplied.
	 *
	 * @return array|mixed|WP_Error
	 */
	public function put_unsub_customer_signup($list_code, $email_address, $reference = null){
		$url = $this->url . 'list/customersignup/unsub';
		$payload = array(
			'listCode' => $list_code,
			'emailAddress' => $email_address
		);
		if($reference) $payload['referenceNumber'] = $reference;
		return $this->_post($url, $payload);
	}

	/**
	 * 7.1 findCustomerListSignupsByCustomerNumber
	 * Find the customer’s list signups using the customer number.
	 *
	 * @param $customer_id
	 *
	 * @return array
	 */
	public function get_customer_list_signups_by_id($customer_id){
		$url = $this->url . 'adv/list/signup/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	 * 7.3 findCustomerListSignupsByEmailAddress
	 * Find the customer's list signups using their email address.
	 * 
	 * @param $email
	 * 
	 * @return array
	 */
	public function get_customer_list_signups_by_email($email){
		$url = $this->url . 'adv/list/signup/emailaddress/' . $email;
		return $this->_get($url);
	}

	/**
	 * 12.1 findAffiliateFactsByCustomerNumber
	 * Retrieve affiliate facts based off of a customer number.
	 * @param $customer_id
	 *
	 * @return array
	 */
	public function get_affiliate_facts_by_id($customer_id){
		$url = $this->url . 'target/affiliate/fact/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	 * 12.2	findListFactsByCustomerNumber
	 * Retrieve list facts based off of a customer number
	 *
	 * @param $customer_id
	 *
	 * @return array
	 */
	public function get_list_facts_by_id($customer_id){
		$url = $this->url . 'target/list/fact/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	 * 12.3 findAffiliateTagsByCustomerNumber
	 * Retrieve affiliate tagging information based off of a customer number
	 *
	 * @param $customer_id
	 *
	 * @return array
	 */
	public function get_affiliate_tags_by_id($customer_id){
		$url = $this->url . 'target/affiliate/tag/customernumber/' . $customer_id;
		return $this->_get($url);
	}

	/**
	 * 12.4 findAffiliateTagsByEmailAddressOwningOrg
	 * Retrieve affiliate tagging information based off of an email address and owning org
	 * @param $email
	 * @param $owning_org
	 *
	 * @return array
	 */
	public function get_affiliate_tags_by_email_owning_org($email, $owning_org){
		$url = $this->url . 'target/affiliate/tag/emailaddress/' . $email . '/owningorg/' . $owning_org;
		return $this->_get($url);
	}

    /**
     * 1.14	findLowestCustomerNumberByEmailAddress
     * Method to find the lowest customer number using the customer’s e-mail address
     * @param $purchaseOrderNumber
     *
     * @return array
     */
    public function findSubscriptionsByPurchaseOrderNumber($purchaseOrderNumber){
        $url = $this->url . 'sub/purchaseordernumber/' . $purchaseOrderNumber;
        return $this->_get($url);
    }

    /**
     * find_credit_cards_by_customer_number_affiliate_code
     * Find credit cards saved to a customer’s account using their customer number.
     *
     * https://wiki.pubsvs.com/display/MWSUPPORT/Get+Credit+Cards+By+Customer+Number
     *
     * @param $customer_id
     * @param $affiliate_code
     *
     * @return array
     *
     * NOTE: Response is NOT restricted to the authGroup but instead restricted by affiliateCode
     */
    public function find_credit_cards_by_customer_number_affiliate_code($customer_id){
        $url = $this->url . 'creditcard/customernumber/' . $customer_id .'/affiliatecode/' .$this->affiliate_code;
        return $this->_get($url);
    }

    /**
     * 3.11	findSubscriptionPostalAddressByPurchaseOrderNumber
     * Get the records combining subscription and postal address using a purchase order number.
     * @param $purchaseOrderNumber
     *
     * @return array
     */
    public function findSubscriptionsAndPostalAddressesByPurchaseOrderNumber($purchaseOrderNumber){
        $url = $this->url . 'sub/postaladdress/purchaseordernumber/' . $purchaseOrderNumber;
        return $this->_get($url);
    }

	/**
	 * 12.5 createAffiliateTags
	 * Create an Affiliate Tag
	 *
	 * @param      $customer_id
	 * @param      $email
	 * @param      $tag_name
	 * @param      $tag_value
	 * @param null $owning_org
	 *
	 * @return array|mixed|WP_Error
	 */
	public function put_create_affiliate_tags($customer_id, $email, $tag_name, $tag_value, $owning_org = null){
		$url = $this->url . 'target/affiliate/tag/create';

		$payload = array(
			'customerNumber'    => $customer_id,
			'emailAddress'      => $email,
			'tagName'           => $tag_name,
			'tagValue'          => $tag_value,
		);
		if(!empty($owning_org)) $payload['ownOrg'] = $owning_org;

		return $this->_post($url, $payload);
	}

	/**
	 * 7.8	addCustomerSignup
	 * Add a customer signup to a list.
	 *
	 * @param      $email
	 * @param      $list_code
	 * @param      $source_code
	 * @param null $attributes
	 *
	 * @return array|mixed|WP_Error
	 */
	public function put_customer_signup_by_email($email, $list_code, $source_code, $attributes = null){

		$url = $this->url . 'list/customersignup/add';

		$payload = array(
			'emailAddress'  => $email,
			'listCode'      => $list_code,
			'sourceId'      => $source_code
		);

		if($attributes !== null)
			$payload = array_merge($payload, $attributes);

		return $this->_post($url, $payload);

	}

	/**
	 * 1.7	updateEmailAddress
	 * Update the email address associated to the customer’s account
	 * @param      $customerNumber
	 * @param      $emailAddress
	 *
	 * @return array|mixed|WP_Error
	 */
	public function put_update_email_address($customerNumber, $emailAddress){
		$url = $this->url . 'customer/update/emailaddress';

		$payload = array(
			'emailAddress' => $emailAddress,
			'customerNumber' => $customerNumber
		);

		return $this->_post($url, $payload);
	}

	/**
	 * 1.4	updatePostalAddress
	 * Update the postal address associated to the customer’s account
	 * @param $payload
	 *
	 * @return mixed
	 */
	public function put_update_postal_address($payload){
		$url = $this->url . 'customer/update/postaladdress';
		$defaults = array('addressCode' => 'ADDR-01');
		$payload = wp_parse_args($payload, $defaults);
		return $this->_post($url, $payload);
	}

	/**
	 * 2.6	addAccount
	 * Add a customer account.
	 *
	 * @param $customer_id
	 * @param $username
	 * @param $password
	 *
	 * @return array|mixed|WP_Error
	 */
	public function put_add_account_by_id_username_pass($customer_id, $username, $password){
		$url = $this->url . 'account/authentication/create';
		$payload = array('customerNumber' => $customer_id, 'username' => $username, 'password' => $password);

		return $this->_post($url, $payload);
	}

	/**
	 * account/password/reset
	 * Reset Account Password - password hashing mode
	 * @param $username string Username/email address
	 * @param $newPassword string New password
	 *
	 * @return mixed
	 */
	public function put_password_reset( $username, $newPassword ) {
		$url = $this->url . 'account/password/reset';
		$payload = array( 'username' => strtoupper( $username ), 'newPassword' => $newPassword );
		return $this->_post( $url, $payload );
	}

	/**
	 * 2.8	updatePassword
	 * Update the password associated to the customer’s account
	 * @param $payload
	 *
	 * @return mixed
	 */
	public function put_update_password($customer_id, $username, $password, $newPassword){
		$url = $this->url . 'account/update/password';
		$payload = array('customerNumber' => $customer_id, 'username' => $username, 'existingPassword' => $password, 'newPassword' => $newPassword);
		return $this->_post($url, $payload);
	}

	/**
	 * Helper Method for POST requests
	 *
	 * @param $url
	 * @param $payload
	 *
	 * @return array|mixed|WP_Error
	 */
	private function _post($url, $payload){
		$this->log->info('Middleware POST request to: ' . $url);

		$tfs_payload = array(
			't' => 'event', //Hit type
			'ec' => 'MW POST Call', //Event Category
			'ea' => $url, //Event Action
		);

		$url = esc_url_raw($url);
		$request_data = $this->http_args;
		$request_data['body'] = json_encode($payload);
		$request_data['headers']['Content-Type'] = 'application/json';
		$request_data['headers']['IP Address'] = $this->get_user_ip();

		$this->log->info('Request data', $request_data);

		$result = wp_remote_post($url, $request_data);

		if(is_wp_error($result)){
			$tfs_payload['el'] = $result;
//			$this->core->tfs_monitor($tfs_payload);
			$this->log->error('WP Error', $result);
			return $result;

		}elseif($result['response']['code'] == 200){
			$response_content = json_decode(wp_remote_retrieve_body($result));
			$tfs_payload['el'] = $result['response']['message'];
//			$this->core->tfs_monitor($tfs_payload);
			$this->log->info('Response ' . $result['response']['code'], $response_content);
			return ($response_content == null OR $response_content == '') ? true : $response_content;

		}else{
			$tfs_payload['el'] = $result;
//			$this->core->tfs_monitor($tfs_payload);
			$this->log->error('Result: ', $result);

			// Handle the error data
			$error_log = new WP_Error('post_request_failed', __('Middleware POST request failed'));
			$error_log->add_data( $result, 'post_request_failed' );

			return $error_log;
		}
	}


	/**
	*	A helper method to reduce repetition
	*
	*	@param string $url
     *  @param boolean $cache - enabled by default for all end points
	*	@return array Associative array of returned data. Returns WP_Error object on error
	**/
	private function _get($url, $cache = true){
		$this->log->info('Middleware GET Request to: ' . $url);


		$tfs_payload = array(
			't' => 'event', //Hit type
			'ec' => 'MW GET Call', //Event Category
			'ea' => $url, //Event Action
		);


		$url = esc_url_raw($url);

        if($this->caching != 0 && $cache == true){

            //generate URL of MD5
            $transName = $this->core->tfs_hash( $url );
            $trans = false;

            if (false !== ($trans = get_transient($transName))) {
                $this->log->info('Returning: Cached Version');
                $result = $trans;
            }else{
                $this->log->info('Middlware call been made');
                $result = wp_remote_get($url, $this->http_args);
            }
        }else{
            $this->log->info('Middlware call been made');
            $result = wp_remote_get($url, $this->http_args);
        }


		if(is_wp_error($result)){
			$tfs_payload['el'] = $result;
//			 $this->core->tfs_monitor($tfs_payload);
			/**
			 * WP error can happen if we can't connect or get internal server errors etc.
			 */
			$this->log->error('WP Error', $result);
			return $result;

		}elseif( $result['response']['code'] == 422 OR strlen(wp_remote_retrieve_body($result)) < 10){
			$tfs_payload['el'] = $result['response']['message'];
//			 $this->core->tfs_monitor($tfs_payload);
			/**
			 * Some middleware calls return 422 HTTP code if nothing is found, others return a 200 code but empty content.
			 */
			$this->log->error('Result: ', $result);

			// Handle the error data
			$error_log = new WP_Error('get_request_failed', __('Middleware GET request failed'));
			$error_log->add_data( $result, 'get_request_failed' );

			return $error_log;

		}elseif($result['response']['code'] == 200){

            if($this->caching != 0 && $cache == true) {
                //do not reset a saved transient or it will never expire
                if (false === $trans) {
                    set_transient($transName, $result, 2 * HOUR_IN_SECONDS);
                }
            }
			$tfs_payload['el'] = $result['response']['message'];
//			 $this->core->tfs_monitor($tfs_payload);
			/**
			 * This is a successful call and will return a php object
			 */
			$response_content = json_decode(wp_remote_retrieve_body($result));
			$this->log->info('Response', $response_content);
			return $response_content;
		}else{
			$tfs_payload['el'] = $result;
//			$this->core->tfs_monitor($tfs_payload);
			/**
			 * Some other thing happened, log an error
			 */
			$this->log->error($url, $result);
		}
	}

	/**
	*	A method to give a list of middleware calls by method
	*
	*	@param string $type A string describing the type of calls we want e.g. get, put, update, delete
	*	@param string $input A string describing what inputs to use. e.g. login, customer_ID
	*	@return array Associative array of methods that match the requested parameters
	**/
	public function list_methods($type, $input){
		$methods = get_class_methods($this);

		// Check and clean up the $type parameter
		if(in_array($type, array('get', 'put', 'update', 'delete') ) ){
			$type .= '_';
		}else{
			trigger_error('Invalid Value: '. $type .' for Parameter $type');
			return;
		}

		// Check and cleanup the $input parameter
		if($input == 'customer_ID'){
			$input = '_by_id';
		}elseif($input == 'login'){
			$input = '_by_login';
		}elseif($input == 'email'){
			$input = '_by_email';
		}else{
			trigger_error('Invalid Value: '. $input .' for Parameter $input');
			return;
		}

		// Cycle through all the methods, and find those that match the type and input
		foreach($methods as $m){
			if(strpos($m, $type) !== false){
				if(strpos($m, $input)){
					$result[] = $m;
				}
			}
		}
		return $result;
	}

	/**
	 * 2.7	updateUsername
	 * Update the status of a customer signup.
	 *
	 * @param $newUsername
	 * @param $existingUsername
	 *
	 * @return string|WP_Error
	 */
	public function updateUsername($existingUsername, $newUsername){
		$url = $this->url . 'account/update/username/';
		$payload = array(
			'existingUsername'  => $existingUsername,
			'newUsername'      => $newUsername,
		);
		return $this->_post($url, $payload);
	}

	/**
	 * 3.1	updateSubscriptionEmailAddress
	 * Update the e-mail address associated to a single subscription.
	 *
	 * @param subRef
	 * @param newEmailAddress
	 *
	 * @return string|WP_Error
	 */
	public function updateSubscriptionEmailAddress($subRef, $newEmailAddress){
		$url = $this->url . 'sub/update/emailaddress/';
		$payload = array(
			'subRef'  => $subRef,
			'newEmailAddress'      => $newEmailAddress,
			'emailSequenceNumber' => 1
		);
		return $this->_post($url, $payload);
	}

	/**
	 * 3.5	findSubscriptionEmailAddressBySubRef
	 * Find the e-mail address associated to a single subscription.
	 * @param $subRef
	 *
	 * @return array
	 */
	public function findSubscriptionEmailAddressBySubRef($subRef){
		$url = $this->url . 'sub/emailaddress/subref/' . $subRef;
		return $this->_get($url);
	}

	/**
	 * 3.6	findSubscriptionByEmailAddress
	 * Find all subscriptions tied to a single email address.
	 * Because an email address can be tied to multiple customer numbers, this call may return
	 * subscriptions tied to several customer numbers.
	 *
	 * @param emailAddress
	 *
	 * @return array
	 */
	public function findSubscriptionByEmailAddress($emailAddress){
		$url = $this->url . 'sub/emailaddress/' . $emailAddress;
		return $this->_get($url);
	}

    /**
     * 3.14	update_subscription_auto_renew_flag
     * Update the autoRenewalFlag for a subscription.
     *
     * @param subRef
     * @param string renewalFlag
     *
     * @return string|WP_Error
     */
    public function update_subscription_auto_renew_flag($subRef, $renewalFlag = "A"){
        $url = $this->url . 'sub/renewalflag/update';
        $payload = array(
            'subRef'  => $subRef,
            'renewalFlag'      => $renewalFlag
        );

        return $this->_post($url, $payload);
    }

    /**
     * 4.3 findOrderDetailByOrderNumber
     * Get more detailed information on the product order.
     *
     * @param $order_number
     *
     * @return mixed
     */
    public function get_order_detail_by_order_number( $order_number ) {
        $url = $this->url . 'order/detail/ordernumber/' . $order_number;

        return $this->_get( $url );
    }

    /**
     * 4.4 findOrderDetailByCustomerNumberOwningOrg
     * Get detailed information on all of a customer's orders.
     *
     * @param $customer_number
     * @param $owning_org
     *
     * @return mixed
     */
    public function find_order_detail_by_customer_number_owning_org( $customer_number, $owning_org ) {
        $url = $this->url . 'order/detail/customernumber/' . $customer_number . '/owningorg/' . $owning_org;

        return $this->_get( $url );
    }

	/**
	 * 7.14	updateCustomerSignup
	 * Update the status of a customer signup.
	 *
	 * @param $email
	 * @param $list_code
	 * @param $attributes (optional)
	 *
	 * @return array|mixed|WP_Error
	 */
	public function updateCustomerSignup($email, $list_code, $attributes = null){
		$url = $this->url . 'list/customersignup/update';
		$payload = array(
			'emailAddress'  => $email,
			'listCode'      => $list_code,
		);
		if($attributes !== null)
			$payload = array_merge($payload, $attributes);

		return $this->_post($url, $payload);
	}

	/**
	 * 10.3	findItemsAndChoicesByPromoCode
	 * Get the items and choices associated with a promotion code.
	 *
	 * @param $promo_code
	 * @param $currencies
	 *
	 * @return array|mixed|WP_Error
	 */
	public function find_items_and_choices_by_promo_code($promo_code, $currencies){
		$url = $this->url . 'promo/findwithitemsandchoices';
		$payload = array(
			'promoCode'  => $promo_code,
			'currencies' => $currencies,
		);
		return $this->_post($url, $payload);
	}

    /**
     * cancelSubscription
     * Create a workflow event in advantage to cancel a subscription
     *
     * @param $pub_code
     * @param $cust_no
     * @param $sub_ref
     *
     * @return array
     */
    public function cancelSubscription($pub_code, $cust_no, $sub_ref){
        $url = $this->url . 'workflow/event/create';
        $payload = array(
            "eventTypeId" => "API-TRIGGER-CIR-GET",
            "contextKeyList" => array (
                "PUB-CDE" => $pub_code,
                "CTM-NBR" => $cust_no,
                "SUB-REF" => $sub_ref
            )
        );
        return $this->_post($url, $payload);
    }
}
