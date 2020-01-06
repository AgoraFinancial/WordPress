<?php
/**
 * Description: A class with everything needed to interact with the middleware user
 * Class: agora_middleware_user constructor.
 * Version: 1.0
 * @author: Threefold Systems
 */
class agora_middleware_user{

	/**
	 * Description: Wordpress user object
	 * @var boolean|object
	 */
	public $wp_user = false;

	/**
	 * Description: Middleware data associated with a user
	 * @var boolean|object
	 */
	public $middleware_data = false;

	/**
	 * @var bool
	 */
	public $login_tokens = false;

	/**
	 * Description: Constructor
	 * @parm $core
	 * @parm $config
	 */
	public function __construct(agora_core_framework $core, $config){
		$this->config = $config;

		$this->core = $core;

        add_action('edit_user_profile', array($this, 'load_middleware_data'));
		add_filter('load_middleware_aggregate_data', array($this, 'load_aggregate_data'), 1, 3);

		// Only Allow One Session At A Time Per User
		add_action( 'init', array( $this, 'mw_destroy_all_other_user_sessions' ) );
	}

	/**
	 * Only Allow One Session At A Time Per User
	 * @method mw_destroy_all_other_user_sessions
	 */
	public function mw_destroy_all_other_user_sessions() {
		if ( is_user_logged_in() && ! empty( $this->config[ 'one_login_session_at_a_time_per_user' ] ) && $this->config[ 'one_login_session_at_a_time_per_user' ] == 1 ) {
			$token = wp_get_session_token();

			if ( $token ) {
				$manager = WP_Session_Tokens::get_instance( get_current_user_id() );
				$manager->destroy_others( $token );
			}
		}
	}

	/**
	 * Class: load_middleware_data constructor.
	 * @author: Threefold Systems
	 * @method load_middleware_data
	 * @param $user
	 */
    public function load_middleware_data($user){

        $this->load_user($user->ID);
        $this->core->view->load('middleware_meta_user', $this->middleware_data);
        wp_enqueue_script( 'jquery-ui-accordion');
    }
	/**
	 * Description: Function to get the middleware data for user based on username and password Then create a wordpress account for the user, if one already exists the WP account will be updated with current data
	 * @method initialize_user
	 * @param  string $username 	Username
	 * @param  string $password 	Password
	 * @param  string $first_name
	 * @param  string $last_name
	 * @param  string $email_address
	 * @return boolean|WP_Error 	Returns True if a corresponding middleware user was found and initialized, WP_Error if not.
	 */
	public function initialize_user($username, $password, $first_name = null, $last_name = null, $email_address = null){

		// Load the user data from Middleware
		//$this->middleware_data = $this->core->mw->get_aggregate_data_by_login($username, $password);
		$this->middleware_data = apply_filters('load_middleware_aggregate_data', $this->middleware_data, $username, $password);

		if ( $this->is_middleware_user() AND $username AND $password){
			// Only allow valid users to log in
			if ( $this->valid_user_login() === false ) {
				// check what page the login attempt is coming from
				$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;
				$redirect_invalid_login = home_url();

				// check that were not on the default login page
				if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) {
					$redirect_invalid_login = $referrer;
					$this->core->session->flash_message( 'login', $this->core->get_language_variable( 'txt_no_valid_subscriptions' ), 'error' );
				}

				wp_redirect( $redirect_invalid_login );
				exit;
			}

			// Try to create a wordpress user account.
			$user_id = $this->_create_wp_user($username, $password, $first_name, $last_name, $email_address);

			// If an error is returned the user must already have an account
            if(isset($user_id->errors['existing_user_email'])){

                $this->core->session->flash_message('login', $this->core->get_language_variable('txt_email_already_in_use'), 'error');
            }else if( is_wp_error( $user_id ) OR $user_id == null){

				$this->wp_user = get_user_by('login', $username);
				// Update the existing record
				$user_id = $this->_update_wp_user($this->wp_user->ID);
			}

			// If we don't get an error, we must have gotten the user ID so go grab it.
			if( !is_wp_error($user_id) ){
				$this->wp_user = get_user_by('id', $user_id);
				return true;
			}
		}
        else if(is_wp_error($this->middleware_data) AND $user_to_check = get_user_by( 'login', $username))
        {
            // some error from middle ware
            $passMatch = wp_check_password( $password, $user_to_check->data->user_pass, $user_to_check->ID);

            if ( $user_to_check && $passMatch)
            {
                return false;
            }
            else {
                $user_to_check = get_user_by( 'login', $username);
                $user_meta = get_user_meta($user_to_check->ID,'description');

                /* Checking for Middle or middle so we search for iddle */

                if(!empty( $user_meta) &&  strpos($user_meta[0],"iddle"))
                {
                    $user_data[ 'ID'] =  $user_to_check->ID;
                    $user_data[ 'user_pass'] = 'qwedaxfchaoxlhfcakjdcoiaklbfncprettyslim';
                    $result = wp_update_user($user_data);
                    $this->core->log->error('MisMatched Password user pass changed', $result);
                }

            }
        }
		return $this->middleware_data;
	}

	/**
	 *Only allow valid users to log in
 	*/
	public function valid_user_login() {
		if ( ! empty( $this->config[ 'valid_user_login' ] ) && $this->config[ 'valid_user_login' ] == 1 ) {
			$valid_user = false;

			// If user has subscriptions
			if ( $this->middleware_data->subscriptionsAndOrders->subscriptions && is_array( $this->middleware_data->subscriptionsAndOrders->subscriptions ) ) {

				/* 'invalid' subscription:
                 * startDate is in the future
                 * expirationDate is in the past
                 * circStatus is not R, P or Q
                 */
				// For each subscriptions
				foreach ( $this->middleware_data->subscriptionsAndOrders->subscriptions as $subscription ) {
					// Dates
					$subscription_start_date = new DateTime( $subscription->startDate );
					$subscription_expiration_date = new DateTime( $subscription->expirationDate );

					$current_date = new DateTime();


					// If startDate is in the future, the subscriptions is not valid yet
					if ( $subscription_start_date > $current_date ) {
						continue;
					}


					// If expirationDate is in the future, the subscriptions is not valid anymore
					if ( $subscription_expiration_date < $current_date ) {
						continue;
					}


					// If circStatus of the subscription is not (R)Active, (P)Perpetual, (Q)Controlled, continue on with the rest of the subs
					if ( $subscription->circStatus != 'R' && $subscription->circStatus != 'P' && $subscription->circStatus != 'Q' ) {
						continue;
					}


					// User is valid for et least one subscriptions, let him in.
					$valid_user = true;
					break;
				}
			}

			// If user is not valid, don't let him in
			if ( $valid_user === false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Description: Hooks to the load_middleware_aggregate_data filter. Doesn't *modify* the $aggregate_data variable because it should only be run first, and should never receive any data.
	 * @method load_aggregate_date
	 * @param $aggregate_data
	 * @param $username
	 * @param $password
	 * @return mixed
	 */
	public function load_aggregate_data($aggregate_data, $username, $password){
		return $this->core->mw->get_aggregate_data_by_login($username, $password);
	}


    /**
     * Description: Parse the users subscriptions return a more usable object structure.
     * @method get_subscriptions
     * @return mixed
     */
    public function get_subscriptions(){
	    return $this->core->authentication->get_user_subscriptions('subscriptions');
    }

    /**
     * Description: Parse the users product purchases and return a more usable object structure.
     * @method get_products
     * @return array
     */
    public function get_products(){
	     return $this->core->authentication->get_user_subscriptions('productOrders');
    }

	/**
	 * Description: Parse the users product purchases and return a usable object
	 * @method get_access_maintenance_billing
	 * @return array
	 */
	public function get_access_maintenance_billing(){
		return $this->core->authentication->get_user_subscriptions('accessMaintenanceOrders');
	}



	/**
	 * Description: Load user data, loads WP and middleware data into the user object If given a user_id parameter will load that user, else it will load the current user
	 * @method load_user
	 * @param  int $user_id Wordpress database user ID
	 * @return object
	 */
	public function load_user($user_id = null){

		if($user_id){
			$this->wp_user = get_user_by('id', $user_id);

		}else{
			$user = wp_get_current_user();

			if($user->ID != 0)
				$this->wp_user = $user;
		}

		if( !is_wp_error($this->wp_user) && $this->wp_user != false ){
			$this->middleware_data = get_user_meta($this->wp_user->ID, 'agora_middleware_aggregate_data', true);
            $this->wp_user->middleware_data = $this->middleware_data;
		}

		return $this->wp_user;

	}

	/**
	 * Description: Sends an email address to the MW wrapper, interprets the result and passes back an array of objects containing usernames & passwords
	 * @method get_login_by_email
	 * @param  string $email_address The email address to search for
	 * @return array                An array of stdClass objects
	 */
	public function get_login_by_email($email_address){
		$account = $this->core->mw->get_account_by_email($email_address);

		$result = array();
		if(is_wp_error($account))
			return $account;

		if ( is_array( $account ) ) {
			foreach($account as $a){
				$login = new stdClass;
				$login->password = $a->password;
				$login->username = $a->id->userName;
				$result[] = $login;
			}
		}

		return $result;
	}

	/**
	 * Description: Get login(s) associated with the given customer ID
	 * @method get_login_by_id
	 * @param $customer_id
	 * @return array
	 */
	public function get_login_by_id($customer_id){
		$account = $this->core->mw->get_account_by_id($customer_id);
		$result = array();
		if(is_wp_error($account))
			return $account;

		foreach($account as $a){
			$login = new stdClass;
			$login->password = $a->password;
			$login->username = $a->id->userName;
			$result[] = $login;
		}
		return $result;
	}

	/**
	 * Description: Find a wordpress login for the given customer number. If none found, return the first middleware login
	 * @method get_wp_login_by_cid
	 * @param $cid
	 * @param $org_id
	 * @return array
	 */
	public function get_wp_login_by_cid($cid, $org_id){

		$cn = $this->core->mw->get_customer_number_by_contact_id_org_id($cid, $org_id);

		$logins = $this->core->user->get_login_by_id($cn->customerNumber);
		if(is_wp_error($logins)) return;
		$user_attempt = $logins[0];

		foreach($logins as $l){
			if($user = get_user_by('login', $l->username)){
				$user_attempt = $l;
				break;
			}
		}
		return $user_attempt;
	}

	/**
	 * Description: Takes the middleware_data and updates the users WP account information
	 * @method _update_wp_user
	 * @param  int $user_id 		Wordpress User ID
	 * @return int|object          	Returns the user ID on success, or WP_Error object on fail
	 */
	public function _update_wp_user($user_id){

		if(!function_exists('wp_update_user'))
			require_once(ABSPATH.'wp-includes/user.php');


        $user_to_check = get_user_by( 'login', $this->get_username());

		if( $user_to_check && $this->middleware_data AND !is_wp_error($this->middleware_data) ){

            $user_data = array(
                'ID' => $user_id,
                'user_login' => preg_replace("/[^\w@.-]/", '', $this->get_username()),
                'description' => __('Middleware User'),
                'user_email' => $this->_get_email(),
                'first_name' => $this->get_name( 'firstName' ),
                'last_name' => $this->get_name( 'lastName' ),
                'user_nicename' => sanitize_title($this->get_name()),
            );

            /** Only Change the password is the password is different to the one from Advantage */

            $passMatch = wp_check_password( $this->get_password(), $user_to_check->data->user_pass, $user_to_check->ID);

            if ( $user_to_check && ! $passMatch)
            {
                $user_data[ 'user_pass'] = $this->get_password();
            }


			$result = wp_update_user($user_data);

			if(is_wp_error($result)){
				$this->core->log->error('Error Updating user', $result);
			}else{

				$this->save_middleware_data($user_id);
			}


		}


        return $result;
	}

	/**
	 * Description: Query middleware for user and create a record from it. Also adds the middleware aggregate data to the users account in the form of user_meta If $middleware_data has not been set it will do nothing.
 	 * @method _create_wp_user
	 * @param $username
	 * @param $password
	 * @param $first_name
	 * @param $last_name
	 * @param $email_address
	 * @return int|object 	Returns user_id integer if valid user was created, else returns a WP_Error object
	 *
	 */
	public function _create_wp_user($username = null, $password = null, $first_name = null, $last_name = null, $email_address = null){

		if($this->middleware_data AND !is_wp_error( $this->middleware_data ) ){
			if(!function_exists('wp_update_user'))
				require_once(ABSPATH.'/wp-includes/user.php');

			$nicename = ($first_name != null OR $last_name != null) ? $first_name . ' ' . $last_name : $this->get_name();

            //remove apostrophes before saving because it's 2015
			$user_data = array(
				'user_login' => ($username != null) ? preg_replace("/[^\w@.-]/", '', $username) : preg_replace("/[^\w@.-]/", '', $this->get_username()),
				'user_pass' => ($password != null) ? $password : $this->get_password(),
				'description' => __('Middleware User'),
				'user_email' => ($email_address != null) ? $email_address : $this->_get_email(),
				'first_name' => ($first_name != null) ? $first_name : $this->get_name( 'firstName' ),
				'last_name' => ($last_name != null) ? $last_name : $this->get_name( 'lastName' ),
				'user_nicename' => sanitize_title($nicename),
				'role' => 'subscriber'
			);

			$user_id = wp_insert_user($user_data);

			if( !is_wp_error( $user_id ) ) {

				$this->save_middleware_data($user_id);

			}else{
				$this->core->log->error('Unable to create User', $user_id);
			}

			return $user_id;
		}
	}

	/**
	 * Description: Function to save middleware_data to the given user_id Basically just wraps the update_user_meta function but since we might change the keys going forward it makes sense to wrap it.
	 * @method save_middleware_data
	 * @param  int $user_id Wordpress User ID
	 * @return mixed
	 *
	 */
	function save_middleware_data($user_id){
		if( !is_wp_error($this->middleware_data) ){
			$mw_data = apply_filters('agora_before_save_aggregate_data', $this->middleware_data);
			return update_user_meta($user_id, 'agora_middleware_aggregate_data', $mw_data)
				or $this->core->log->error('Unable to Store User Meta for ' . $user_id);
		}
	}


	/**
	 * Description: Method to add a token to the list of used tokens for the current user
	 * @method set_used_token
     * @param $mailingid
     */
	function set_used_token($token, $mailingid = 0 ){
		if ( ! $this->login_tokens ) {
			$this->get_login_tokens();
		}

		$this->login_tokens = is_array( $this->login_tokens ) ? $this->login_tokens : array();

		if ( isset( $this->login_tokens[ $token ] ) && ! is_array( $this->login_tokens[ $token ] ) ) {
           unset( $this->login_tokens[ $token ] );
		}

		$this->login_tokens[ $token ][ $mailingid ] = time();

		$this->set_login_tokens();
	}

	/**
	 * Description: Update the used login tokens for the current user
	 * @method set_login_tokens
	 * @return bool|int
	 */
	public function set_login_tokens(){
		if(!$this->login_tokens){
			$this->get_login_tokens();
		}
		return update_user_meta($this->get_user_id(), 'agora_login_tokens', $this->login_tokens );
	}

	/**
	 * Description: Retrieve the array of used login tokens
	 * @method get_login_tokens
	 * @return mixed
	 */
	public function get_login_tokens(){
		return $this->login_tokens = get_user_meta($this->get_user_id(), 'agora_login_tokens', true);
	}

	/**
	 * Description: Determine if the given token has been used before
	 * @method is_token_used
	 * @param $token
     * @param $mailingid = 0
	 * @return bool
	 */
	public function is_token_used($token, $mailingid = 0){
		if(!$this->login_tokens){
			$this->get_login_tokens();
		}
        return (isset($this->login_tokens[$token]) && isset($this->login_tokens[$token][$mailingid])) ? true : false;

	}

	/**
	 * Description: Function to get the current password
	 * @method get_password
	 * @return string String containing the password stored in the middleware data
	 */
	public function get_password(){
		$post_pwd = ( isset( $_POST[ 'pwd' ] ) ? $_POST[ 'pwd' ] : '' );

		if($post_pwd){
			return $post_pwd;
		}elseif($this->middleware_data){
			return $this->middleware_data->accounts[0]->password;
		}
		return false;
	}

	/**
	 * Description: Function to get the current username
	 * @method get_username
	 * @return string String containing the username stored in the middleware data
	 */
	public function get_username(){
		$post_log = ( isset( $_POST[ 'log' ] ) ? sanitize_text_field( $_POST[ 'log' ] ) : '' );

		if($post_log){
			return $post_log;
		}elseif($this->middleware_data){
			return $this->middleware_data->accounts[0]->id->userName;
		}
		return false;
	}

	/**
	 * Description: Function to return the users name from their Middleware data Pulled from the Address data where Primary address is identified by addressFlag = 0
	 * @method get_name
	 * @param  	string $field         Optional: firstName, or lastName
	 * @return 	string         A string, or object matching the field, or entire address object
	 */
	public function get_name($field = null){

		$address = $this->get_address();

		if( $field && isset( $address->$field ) ){
			return ucfirst( strtolower( $address->$field ) );
		} elseif ( isset( $address->firstName, $address->lastName ) ) {
			return ucfirst( strtolower( $address->firstName ) ) . ' ' . ucfirst( strtolower( $address->lastName ) );
		}
	}

	/**
	 * Description: Get the users first name from middleware data
	 * @method get_first_name
	 * @return string
	 */
	public function get_first_name(){
		return $this->get_name('firstName');
	}

	/**
	 * Description: Get the users last name from middleware data
	 * @method get_last_name
	 * @return string
	 */
	public function get_last_name(){
		return $this->get_name('lastName');
	}

	/**
	 * Description: Retrieves the Users current address records Current Address is denoted by addressFlag == 0
	 * @method get_address
	 * @param  boolean $get_all Optional: Returns all address records when set to true, defaults to just 'current' record.
	 * @return mixed
	 */
	public function get_address($get_all = false){

		if($get_all == true AND isset($this->middleware_data->postalAddresses)) return $this->middleware_data->postalAddresses;
		if(!isset($this->middleware_data->postalAddresses)) return false;
		foreach( $this->middleware_data->postalAddresses as $adr ){
			if( $adr->id->addressFlag == '0' ){
				return $adr;
			}
		}
		return false;
	}

	/**
	 * Description: Get the customer zip code from their *current* address
	 * @method get_zip_code
	 * @return string
	 */
	public function get_zip_code(){
		$address = $this->get_address();
		if($address){
			return $address->postalCode;
		}else{
			return false;
		}
	}

	/**
	 * Description: Get the customers country code from their *current* address
	 * @method get_country
	 * @return string
	 */
	public function get_country(){
		$address = $this->get_address();

		if($address){
			return $address->countryCode;
		}else{
			return false;
		}
	}

	/**
	 * Description: Function to get the users primary email address
	 * @method _get_email
	 * @return string string containing an email address. Or FALSE if none found
	 */
	public function _get_email(){

		if( is_array( $this->middleware_data->emailAddresses ) ){

			// Get the last position of the array
			return end($this->middleware_data->emailAddresses)->emailAddress;

		}else{
			return false;
		}
	}

	/**
	 * Description: Function to determine if current user is a middleware user.
	 * @method is_middleware_user
	 * @return boolean
	 */
	public function is_middleware_user(){
		if (is_wp_error($this->middleware_data)){
			return false;
		}

		if ( isset( $this->middleware_data->accounts ) && sizeof( $this->middleware_data->accounts ) > 0){
			return true;
		}
		return false;
	}

    /**
     * Description: Function to determine if current user is on a trial AMB.
     * @method get_trail_amb_by_days
     * @param days - duration of a trial in days
     * @return array
     */
    public function get_trial_amb_by_days( $days ) {
        $trial_results = array();
        $trial_results[ 'status' ] = 200;
        $trial_results[ 'trials' ] = array();
        $trial_results[ 'trial_details' ] = array();
        if ( is_wp_error( $this->middleware_data ) ) {
            $trial_results[ 'status' ] = 500;
            return $trial_results;
        }
        $ambs = $this->get_access_maintenance_billing();
		if ( ! empty( $ambs ) ) {
			foreach ( $ambs as $amb ) {
				$interval = date_diff( date_create($amb->startDate ), date_create( $amb->expirationDate ) );
				$interval = $interval->days;
				if ( $amb->temp == true OR $interval <= $days ) {
					$trial_results["trials"][] = $amb->id->item->itemNumber;
					$trial_details = array(
						'item_number' => $amb->id->item->itemNumber,
						'expiration' => $amb->expirationDate
					);
					array_push( $trial_results[ 'trial_details' ], $trial_details );
				}
			}
		}
        return $trial_results;
    }

	/**
	 * Description: Gets the users customer number
	 * @method get_customer_number
	 * @return mixed
	 */
	public function get_customer_number(){
		return (isset($this->middleware_data->accounts[0]->customerNumber)) ? $this->middleware_data->accounts[0]->customerNumber : false;
	}

	/**
	 * Description: Gets the users CVI number
	 * @method get_cvi_number
	 * @return mixed
	 */
	public function get_cvi_number(){
		return (isset($this->middleware_data->accounts[0]->cviNbr)) ? $this->middleware_data->accounts[0]->cviNbr : false;
	}

	/**
	 * Description: Gets the users Auth Group
	 * @method get_authgroup
	 * @return mixed
	 */
	public function get_authgroup(){
		return (isset($this->middleware_data->accounts[0]->id->portalCode->authGroup)) ? $this->middleware_data->accounts[0]->id->portalCode->authGroup : false;
	}

	/**
	 * @method get_user_id
	 * @return bool
	 */
	public function get_user_id(){
		return (isset($this->wp_user->ID)) ? $this->wp_user->ID : false;
	}

	/**
	 * Description: Handles sending password retrieval email to user. Lifted straight from the WP source code as of version 4.0.1
	 * @method retrive_password
	 * @uses $wpdb WordPress Database object
	 * @return bool|WP_Error True: when finish. WP_Error on error
	 */
	public static function retrieve_password($email_address) {
		global $wpdb, $wp_hasher;

		$errors = new WP_Error();

		if ( strpos( $email_address, '@' ) ) {
			$user_data = get_user_by( 'email', trim( $email_address ) );
			if ( empty( $user_data ) )
				$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
		} else {
			$login = trim($email_address);
			$user_data = get_user_by('login', $login);
		}

		do_action( 'lostpassword_post' );

		if ( $errors->get_error_code() )
			return $errors;

		if ( !$user_data ) {
			$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
			return $errors;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retreive_password', $user_login );
		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow )
			return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
		else if ( is_wp_error($allow) )
			return $allow;

		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );

		do_action( 'retrieve_password_key', $user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

		$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

		if ( is_multisite() )
			$blogname = $GLOBALS['current_site']->site_name;
		else
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$title = sprintf( __('[%s] Password Reset'), $blogname );

		$title = apply_filters( 'retrieve_password_title', $title );

		$message = apply_filters( 'retrieve_password_message', $message, $key );

		if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) )
			wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.') );

		return true;
	}
}