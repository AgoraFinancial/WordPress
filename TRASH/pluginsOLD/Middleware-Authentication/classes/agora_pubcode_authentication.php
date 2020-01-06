<?php
/**
 * Description: This class contains methods to handle custom taxonomy for pub codes and it's wordpress admin interfaces Simply instantiating the class will enable everything you will need. The class acts as controller and model in a rough MVC structure
 */
class agora_pubcode_authentication extends agora_authentication{
	/**
	 * Description: List of Valid subscription statuses
	 * @var array
	 */
	public $valid_subscription_statuses = array('P','Q','R','X','W','G');
	public $valid_product_order_types = array('G','I','M','P');
	public $invalid_participant_statuses = array('B','C','N','R','S','U','X');

	/**
	 * Description: Expired or otherwise disallowed pubcodes
	 * @var  array list of expired or otherwise disallowed pubcodes
	 */
	public $expired_status = array('E');

	/**
	 * Description: The priority for this method of authentication
	 * @var int
	 */
	public $priority = 1;

	/**
	 * Description: List of post types that do not have pubcodes
	 * @var array
	 */
	public $standard_excludes = array('revision', 'attachment', 'safecss', 'nav_menu_item');

	/**
	 * Description: Constructor
	 * @param $config array of field information loaded from config file.
	 *
	 */
	public function __construct($config){

		parent::__construct($config);

		add_action( 'init', array($this, 'register_authcode_cpt'), 0 );
		add_action( 'add_meta_boxes', array( $this, 'add_authcode_metabox' ) );
		add_action( 'save_post', array( $this, 'save_authcode_metabox' ) );
		add_action( 'init', array($this, 'register_taxonomy'), 0 );
		add_action( 'save_post', array( $this, 'save_post_authcodes' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'register_pubcode_picker' ) );
		add_action( 'wp_ajax_authcode_create', array( $this, '_ajax_create_authcode' ));
		add_action( 'wp_ajax_authcode_update', array( $this, '_ajax_update_authcode' ));
		add_action( 'wp_ajax_authcode_delete', array( $this, '_ajax_delete_authcode' ));
		add_action( 'wp_ajax_rule_create', array( $this, '_ajax_create_rule'));
		add_action( 'wp_ajax_rule_delete', array($this, '_ajax_delete_rule'));
		add_action( 'wp_ajax_get_rule_form', array($this, '_ajax_get_rule_form'));
		add_filter( 'agora_mw_find_purchase', array($this, 'find_purchase'), 1, 3);
		add_filter( 'agora_mw_default_rule', array($this, 'default_rules'), 10, 1);
		add_filter( 'agora_get_adv_item_classname', array($this, 'get_classname'), 1, 2);
		add_shortcode('hidecontent', array($this, 'hide_content'));
		add_filter( 'agora_mw_dynamic_rule', array( $this, 'agora_mw_dynamic_rule' ), 10, 3 );

		$this->core->view->set_template_path($this->plugin_dir . '/views');
		$this->ajax_message = '';
	}

	/**
	 * Description: Generates value from a dynamic authcode rule.
	 * @method agora_mw_dynamic_rule
	 *
	 * @param $value
	 * @param $authcode
	 *
	 * @retun $value
	 */
	public function agora_mw_dynamic_rule( $value, $authcode, $products ) {
		// Ensure it's a product
		if ( $authcode->type == 'productOrders' ) {
			// remove } and {
			$value = str_replace( array( "{", "}" ), "", $value);

			if ( $products && is_array( $products ) ) {
				foreach ( $products as $product ) {
					if ( $product->item->itemNumber == $authcode->advantage_code ) {
						if ( isset( $product->{$value} ) ) {
							return $product->{$value};
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Description: Take in the parameters of the Shortcode [hidecontent/] and hide the content based on what the current user is subscribed to.
     * @method hide_content
     * @parm $atts
     * @parm $content
     * @retun $content|shortcode
	 */
	public function hide_content($atts, $content){

		$authcodes = $this->get_authcodes_by_name(explode(',', $atts['pubcodes']));
		$auth_container = new agora_auth_container(1);
		$auth_container = apply_filters( 'agora_middleware_check_permission', $auth_container, $authcodes);

		$warning = $this->core->get_language_variable('txt_hide_content_shortcode');

		if($auth_container->is_allowed()){
			return $content;
		}else{
			return $this->core->view->load('mw-hide-content-shortcode',
				array( 'authcodes'=>$authcodes, 'warning' => $warning),
				true);
		}
	}

	/**
	 * Description: Method to call the agora_mw_auth_field_structure filter and return the field structure defined by this and other plugins.
     * @method get_field_structure
	 * @return mixed|void
	 */
	public function get_field_structure(){
		return apply_filters('agora_mw_auth_field_structure', $this->field_structure, 1);
	}

	/**
	 * Description: Method to call the agora_mw_auth_types filter and return all auth types registered by this and other plugins.
     * @method get_auth_types
	 * @return mixed|void
	 */
	public function get_auth_types(){
		return apply_filters('agora_mw_auth_types', $this->auth_types, 1);
	}

	/**
	 * Description: Inspect the current post for authentication codes. And execute the attached rules If an authcode doesn't have any rules we assign a default based on valid circStatus
	 * @method check_permission
	 * @param  agora_auth_container $auth_container
	 * @param  $authcodes
	 * @return agora_auth_container
	 */

	public function check_permission(agora_auth_container $auth_container, $authcodes = null){
		if (!$authcodes){
			$authcodes = $this->get_post_authcodes($auth_container->post_id);
		}

		if(!empty($authcodes) AND is_array($authcodes)){
			// The user must be logged in
			if(!is_user_logged_in()) return $auth_container->protected_by('authcode', 'Authcode', $this->core->get_language_variable('txt_default_login_message'));

			foreach($authcodes as $code){
				if(!$code->has_rules()){
					$code = apply_filters('agora_mw_default_rule', $code);
				}
				$auth_container = $this->check_rules($code, $auth_container);
			}
		}
		return $auth_container;
	}


	/**
	 * Description: Function to add default rules to Authcodes that have no user-defined rules
	 * Todo: This is a bit of a hack and shouldn't really belong here. Would probably be better as part of the authcode object
     * @method default_rules
	 * @param agora_authcode $authcode
	 * @return agora_authcode
	 */
	public function default_rules(agora_authcode $authcode){
		if($authcode->type == 'subscriptions'){
			$authcode->add_rule(new agora_auth_rule(array(
				'field'         => 'circStatus',
				'field_group'   => 'subscriptions',
				'operator'      => 'containedIn',
				'value'         => implode(', ', $this->valid_subscription_statuses)
			)));
		}elseif($authcode->type == 'accessMaintenanceOrders'){
			$authcode->add_rule(new agora_auth_rule(array(
				'field'         => 'expirationDate',
				'field_group'   => 'accessMaintenanceOrders',
				'operator'      => 'greaterThanEqual',
				'value'         => time()
			)));

			$authcode->add_rule(new agora_auth_rule(array(
				'field'         => 'participantStatus',
				'field_group'   => 'accessMaintenanceOrders',
				'operator'      => 'notContainedIn',
				'value'         => implode(', ', $this->invalid_participant_statuses)
			)));
		}elseif($authcode->type == 'productOrders'){
			$authcode->add_rule(new agora_auth_rule(array(
				'field'         => 'orderStatus',
				'field_group'   => 'productOrders',
				'operator'      => 'notEquals',
				'value'         => "C"
			)));

			$authcode->add_rule(new agora_auth_rule(array(
				'field'         => 'allowAccess',
				'field_group'   => 'productOrders',
				'operator'      => 'equals',
				'value'         => '1'
			)));

			$authcode->add_rule(new agora_auth_rule(array(
				'field'         => 'orderType',
				'field_group'   => 'productOrders',
				'operator'      => 'containedIn',
				'value'         => implode(', ', $this->valid_product_order_types)
			)));

			$authcode->add_rule(new agora_auth_rule(array(
				'field'         => 'quantityOrdered',
				'field_group'   => 'productOrders',
				'operator'      => 'greaterThan',
				'value'         => '{{quantityReturned}}'
			)));
		}

		return $authcode;
	}

	/**
	 * Description: Cycle through each rule on the $authcode and evaluate based on users middleware data. Assumes that default rules have been added.
	 * @method check_rules
	 * @param agora_authcode       $authcode
	 * @param agora_auth_container $auth_container
	 * @return $this
	 */
	public function check_rules(agora_authcode $authcode, agora_auth_container $auth_container){

		$subscription_objects = apply_filters('agora_mw_find_purchase', array(), $this->core->user->middleware_data, $authcode);

		foreach($subscription_objects as $s){
			$i = sizeof($authcode->get_rules());
			if($this->core->user->is_middleware_user() == false OR !$s){
				return $auth_container->protected_by($authcode->type, $authcode->name, $this->core->get_language_variable('txt_access_denied'));
			}

			foreach($authcode->get_rules() as $rule){
				if ( substr( $rule->value, 0, 2 ) === '{{') {
					$rule->value = apply_filters(
						'agora_mw_dynamic_rule',
						$rule->value, $authcode,
						$this->core->user->middleware_data->subscriptionsAndOrders->productOrders
					);
				}

				if($rule->evaluate($s, $this->get_field_structure())){
					$i--;
				}
			}
			if($i == 0) return $auth_container->allow();
		}

		// If none of the rules have passed we will get to this point. Return a access denied.
		return $auth_container->protected_by($authcode->type, $authcode->name, $this->core->get_language_variable('txt_access_denied'));

	}


	/**
	 * Description: Compares the users account with all the auth codes configured on the site. All matching subscriptions will be returned
	 * @method get_user_subscriptions
	 * @param null $type Optional type e.g. 'subscriptions', 'productOrders', 'listSubscriptions' etc.
	 * @return array
	 */
	public function get_user_subscriptions($type = null){

		$result = array();

		// Get all authcodes configured onn the site
		foreach($this->get_all_authcodes() as $authcode){
			// $type allows us to filter for specific types of Auth type object
			if(isset($type) AND $authcode->type != $type) continue;

			// See if the current user can edit posts (i.e. are they an admin, or editor user)
			// If so, we want the site to think they have purchased everything.
			if ( apply_filters( 'mw_current_user_can_access', false ) ) {
				$classname = apply_filters('agora_get_adv_item_classname', '', $authcode);
				$result[] = new $classname(new stdClass, $authcode);
				continue;
			}

			if(!$authcode->has_rules()){
				$authcode = apply_filters('agora_mw_default_rule', $authcode);
			}
			$subscription_objects  = apply_filters('agora_mw_find_purchase', array(), $this->core->user->middleware_data, $authcode);


			foreach($subscription_objects as $subscription_object){
				$i = sizeof($authcode->get_rules());
				foreach($authcode->get_rules() as $rule){

					if ( substr( $rule->value, 0, 2 ) === '{{') {
						$rule->value = apply_filters(
							'agora_mw_dynamic_rule',
							$rule->value, $authcode,
							$this->core->user->middleware_data->subscriptionsAndOrders->productOrders
						);
					}

					if(!$this->core->user->is_middleware_user() OR !$subscription_object){
						continue;
					}elseif($rule->evaluate($subscription_object, $this->get_field_structure())){
						$i--;
					}
				}

				if($i == 0){
					// The filter will figure out what type of object we need to create. By examining the type property on the $authcode
					// We use a filter to allow other plugins (like list-auth plugin) to extend the available object types.
					$classname = apply_filters('agora_get_adv_item_classname', '', $authcode);
					unset($authcode->core);
					$result[] = new $classname($subscription_object, $authcode);
				}
			}
		}
		return $result;
	}

	/**
	 * Description: Hooks into agora_get_adv_item_classname filter in order to determine what model class to use
     * @method get_classname
	 * @param                $classname
	 * @param agora_authcode $authcode
	 * @return string
	 */
	public function get_classname($classname, agora_authcode $authcode){
		switch ($authcode->type){
			case 'subscriptions':
				return 'agora_publication';
				break;
			case 'productOrders':
				return 'agora_product';
				break;
			case 'accessMaintenanceOrders':
				return 'agora_access_maintenance_billing';
				break;
		}
		return $classname;
	}

	/**
	 * Description: Hooks into agora_mw_find_purchase and parses the users middleware data for an item matching the provided $auth_code The filter requires 3 parameters but this particular method does not use the $middleware_data parameter
	 * @method find_purchase
     * @param                $item
	 * @param null           $middleware_data
	 * @param agora_authcode $auth_code
	 * @return bool
	 */
	public function find_purchase($items, $middleware_data = null, agora_authcode $auth_code){

		if(!$this->core->user->middleware_data OR !isset($this->core->user->middleware_data->subscriptionsAndOrders->{$auth_code->type})) return $items;

		try{
			foreach($this->core->user->middleware_data->subscriptionsAndOrders->{$auth_code->type} as $key => $item){

				switch($auth_code->type){
					case 'subscriptions':
						if(strtoupper($item->id->item->itemNumber) == strtoupper($auth_code->advantage_code)) {
							$items[] = $item;
						}
						break;
					case 'productOrders':
						if(strtoupper($item->item->itemNumber) == strtoupper($auth_code->advantage_code)) {
							$items[] = $item;
						}
						break;
					case 'accessMaintenanceOrders':
						if(strtoupper($item->id->item->itemNumber) == strtoupper($auth_code->advantage_code)) {
							$items[] = $item;
						}
						break;
				}
			}
			return $items;
		}catch (Exception $e){
			return false;
		}
		return false;
	}


	/**
	 * Class: _ajax_get_rule_form constructor.
	 * Description: Fetch create/edit form
	 * @author: Threefold Systems
	 * @method _ajax_get_rule_form
	 */
	public function _ajax_get_rule_form(){
		$post_authcode_id = ( isset( $_POST[ 'authcode_id' ] ) ? sanitize_text_field( $_POST[ 'authcode_id' ] ) : '' );

		$data = array(
			'fields'    => $this->get_field_structure(),
			'authcode'      => new agora_authcode($post_authcode_id)
		);
		$this->core->view->load('rule_form', $data);
		die();
	}

	/**
	 * Description: Handles the update authcode AJAX request
     * @method _ajax_update_authcode
	 */
	public function _ajax_update_authcode(){
		$this->_ajax_security_check('authcode');
		$this->core->log->notice('Update Authcode');
		$this->ajax_message = '';

		$post_name = ( isset( $_POST[ 'name' ] ) ? sanitize_text_field( $_POST[ 'name' ] ) : '' );
		$post_description = ( isset( $_POST[ 'description' ] ) ? sanitize_text_field( $_POST[ 'description' ] ) : '' );
		$post_action = ( isset( $_POST[ 'action' ] ) ? sanitize_text_field( $_POST[ 'action' ] ) : '' );
		$post_id = ( isset( $_POST[ 'id' ] ) ? sanitize_text_field( $_POST[ 'id' ] ) : '' );
		$post_advantage_code = ( isset( $_POST[ 'advantage_code' ] ) ? sanitize_text_field( $_POST[ 'advantage_code' ] ) : '' );
		$post_type = ( isset( $_POST[ 'type' ] ) ? sanitize_text_field( $_POST[ 'type' ] ) : '' );

		if($post_name AND $post_description AND $post_action == 'authcode_update'){
			$authcode = new agora_authcode($post_id);
			$authcode->description = $post_description;
			$authcode->name = $post_name;
			$authcode->advantage_code = $post_advantage_code;
			$authcode->type = $post_type;
			$authcode->save();
			$this->core->log->info('Authcode '. $post_name . ' Updated');
		}else{
			$this->core->log->warn(__('Required Fields Missing updating authcode in ajax handler'));
		}
		$this->authcode_rows($authcode);
	}

	/**
	 * Description: Handles the AJAX request to delete an authcode
     * @method _ajax_delete_authcode
	 */
	public function _ajax_delete_authcode(){
		$this->_ajax_security_check('authcode');

		$post_action = ( isset( $_POST[ 'action' ] ) ? sanitize_text_field( $_POST[ 'action' ] ) : '' );
		$post_id = ( isset( $_POST[ 'id' ] ) ? sanitize_text_field( $_POST[ 'id' ] ) : '' );

		if($post_id AND $post_action == 'authcode_delete'){
			$this->core->log->info('Deleting authcode with ID of ' . $post_id);
			$authcode = new agora_authcode($post_id);
			$authcode->delete();
		}else{
			$this->core->log->warn(__('Required Fields Missing deleting authcode in ajax handler'));
		}
		$this->authcode_rows();
	}


	/**
	 * Description: Handles the AJAX request to create a new authcode
     * @method _ajax_create_authcode
	 */
	public function _ajax_create_authcode(){
		$this->_ajax_security_check('authcode');
		$this->core->log->info('Adding a new Authcode');

		$post_name = ( isset( $_POST[ 'name' ] ) ? sanitize_text_field( $_POST[ 'name' ] ) : '' );
		$post_description = ( isset( $_POST[ 'description' ] ) ? sanitize_text_field( $_POST[ 'description' ] ) : '' );
		$post_action = ( isset( $_POST[ 'action' ] ) ? sanitize_text_field( $_POST[ 'action' ] ) : '' );
		$post_advantage_code = ( isset( $_POST[ 'advantage_code' ] ) ? sanitize_text_field( $_POST[ 'advantage_code' ] ) : '' );
		$post_type = ( isset( $_POST[ 'type' ] ) ? sanitize_text_field( $_POST[ 'type' ] ) : '' );

		if($post_name AND $post_description AND $post_action == 'authcode_create'){
			$authcode = new agora_authcode();
			$authcode->name = $post_name;
			$authcode->description = $post_description;
			$authcode->advantage_code = $post_advantage_code;
			$authcode->type = $post_type;
			$authcode->save();
			$this->core->log->info('Authcode '. $post_name . ' Added');
			$this->ajax_message = 'Pass';
		}else{
			$this->core->log->warn(__('Required Fields Missing when creating pubcode in ajax handler'));
			$this->ajax_message = 'Fail';
		}
		$this->authcode_rows();
	}


	/**
	 * Description: AJAX Response for loading authcode rows
     * @method authcode_rows
	 */
	public function authcode_rows($current_authcode = null){
		$all_pubcodes = $this->get_all_authcodes();
		$this->core->view->load('authcode_rows', array(
			'all_pubcodes'      => $all_pubcodes,
			'current_authcode'  => $current_authcode,
			'auth_types'        => $this->get_auth_types()
		));
		if($this->ajax_message != '') {
			echo $this->ajax_message;
			$this->ajax_message = '';
		}
		die();
	}

	/**
	 * Description: AJAX handler for deleting rules
	 * @method _ajax_delete_rule
	 */
	public function _ajax_delete_rule(){
		$this->_ajax_security_check('rule');
		$this->core->log->notice('Deleting a rule');

		$post_parent = ( isset( $_POST[ 'parent' ] ) ? sanitize_text_field( $_POST[ 'parent' ] ) : '' );
		$post_id = ( isset( $_POST[ 'id' ] ) ? sanitize_text_field( $_POST[ 'id' ] ) : '' );

		$authcode = new agora_authcode($post_parent);
		$authcode->delete_rule($post_id)->save_rules();
		$this->authcode_rows($authcode);
	}


	/**
	 * AJAX handler for creating pubcodes
	 */
	public function _ajax_create_rule(){
		$this->_ajax_security_check('rule');
		$this->core->log->notice('Creating a new Rule');

		$post_authcode_id = ( isset( $_POST[ 'authcode_id' ] ) ? sanitize_text_field( $_POST[ 'authcode_id' ] ) : '' );

		$authcode = new agora_authcode($post_authcode_id);
		$rule = agora_auth_rule::create($_POST, $this->get_field_structure());
		$authcode->add_rule($rule)->save_rules();
		$this->authcode_rows($authcode);
	}


	/**
	 * Checks the user permissions to perform an action and validates a nonce.
	 * @param $object_type
	 */
	public function _ajax_security_check($object_type){
		$fn = $object_type . '_rows';
		if(!current_user_can('manage_options')) {
			$this->core->log->warn('Attempted ajax action prevented due to insufficient permissions');
			$this->{$fn}();
		}
		if(!check_ajax_referer( 'agora_authentication_nonce', 'security', false )){
			$this->core->log->warn('Attempted ajax action prevented due to failed nonce check');
			$this->{$fn}();
		}
	}
	/**
	 *	Show Pubcode Picker
	 *
	 *   Method to show a pubcode chooser meta box when editing content items
	 *
	 *	@param void
	 *	@return void
	 **/
	function show_pubcode_picker(){

		$content['post_pubcodes']      = $this->get_post_authcodes();
		$content['all_pubcodes']       = $this->get_all_authcodes();

		$this->core->view->set_template_path($this->plugin_dir . '/views');
		$this->core->view->load('pubcode_picker', $content );

	}

	/**
	 * Register Pubcode Picker
	 *
	 * Method to enable the pubcode picker on all post types that we'll need.
	 * Basically just using the add_meta_box WP function to call a custom meta box on the edit screen
	 * The $obj parameter is used to pass the 'controller' or plugin calling the method
	 *
	 * @return void
	 */
	public function register_pubcode_picker() {

		// Get the post types so we can add snippets to all needed
		$post_types = get_post_types();

		// Exclude some of the standard wordpress post types because we don't need them
		$standard_excludes = apply_filters('pubsvs_auth_pubcode_exclude_post_types', $this->standard_excludes);

		$post_types = array_diff($post_types, $standard_excludes);

		if (is_array($post_types) && !empty($post_types)) {
			foreach ($post_types as $type) {
				add_meta_box('agora-pubcode-picker',
					__('Publication Code', ''),
					array($this, 'show_pubcode_picker'),
					$type,
					'advanced',
					'high'
				);
			}
		}
		return;
	}

	/**
	 * Register Taxonomy method.
	 *
	 * Function to register the pubcode taxonomy with wordpress.
	 *
	 */
	public function register_taxonomy( ){
		$standard_excludes = apply_filters('pubsvs_auth_pubcode_exclude_post_types', $this->standard_excludes);
		$post_types = get_post_types();
		$post_types = array_diff($post_types, $standard_excludes);

		$register_taxonomy_args = array(
			'hierarchical' => true,
			'labels' => array(
				'name' => _x( 'Pubcodes', 'taxonomy general name' ),
				'singular_name' => _x( 'Pubcode', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Pubcodes' ),
				'popular_items' => __( 'Popular Pubcodes' ),
				'all_items' => __( 'All Pubcodes' ),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __( 'Edit Pubcode' ),
				'update_item' => __( 'Update Pubcode' ),
				'add_new_item' => __( 'Add New Pubcode' ),
				'new_item_name' => __( 'New Pubcode Name' ),
				'separate_items_with_commas' => __( 'Separate pubcodes with commas' ),
				'add_or_remove_items' => __( 'Add or remove pubcodes' ),
				'choose_from_most_used' => __( 'Choose from the most used pubcodes' )
			),
			'query_var' => true,
			'show_tagcloud' => false,
			'show_in_quick_edit' => true,
			'show_ui' => false
		);
		register_taxonomy( 'pubcode', $post_types ,$register_taxonomy_args );
	}

	/**
	 * Register 'tfs_authcode' custom post type
	 */
	public function register_authcode_cpt() {
		// Register nutrition and healing cpt
		$labels_authcode_cpt = array(
			'name'               =>  'MW Authcodes',
			'singular_name'      =>  'MW Authcode',
			'add_new'            =>  'Add New',
			'add_new_item'       =>  'Add New MW Authcode',
			'edit_item'          =>  'Edit MW Authcode',
			'new_item'           =>  'New MW Authcode',
			'all_items'          =>  'All MW Authcodes',
			'view_item'          =>  'View MW Authcode',
			'search_items'       =>  'Search MW Authcodes',
			'not_found'          =>  'No MW Authcodes found',
			'not_found_in_trash' =>  'No MW Authcodes found in the Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'MW Authcodes'
		);

		$args_authcode_cpt = array(
			'labels'        => $labels_authcode_cpt,
			'menu_icon'     => 'dashicons-lock',
			'description'   => 'Holds MW Authcodes',
			'public'        => true,
			'menu_position' => 98,
			'supports'      => array(),
			'has_archive'   => false,
		);

		register_post_type( 'tfs_authcode', $args_authcode_cpt );
	}

	/**
	 * Add 'Authcode' meta box
	 **/
	function add_authcode_metabox() {
		// Add metabox
		add_meta_box(
			'authcode_metabox',
			'Authcode',
			array(
				$this,
				'authcode_metabox_callback'
			),
			'tfs_authcode',
			'normal',
			'high');
	}

	/**
	 * 'Authcode' meta box callback
	 **/
	function authcode_metabox_callback() {
		global $post;

		wp_nonce_field( 'authcode_settings_nonce', 'authcode_settings_nonce' );

		// Values of custom meta boxes
		$authcode_value = get_post_meta( $post->ID, 'authcode_value', true );

		?>
		<label for="authcode_value"><b>Authcode Value:</b> </label><br />
		<input type="text" name="authcode_value" id="authcode_value" value="<?php if ( isset ( $authcode_value ) ) {  echo $authcode_value; } ?>" />
		<?php
	}

	/**
	 * 'Authcode' meta box save
	 **/
	function save_authcode_metabox( $post_id ) {
		// Bail if we're doing an auto save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		$post_authcode_settings_nonce = ( isset( $_POST[ 'authcode_settings_nonce' ] ) ? sanitize_text_field( $_POST[ 'authcode_settings_nonce' ] ) : '' );
		$post_authcode_value = ( isset( $_POST[ 'authcode_value' ] ) ? sanitize_text_field( $_POST[ 'authcode_value' ] ) : '' );

		// If our nonce isn't there, or we can't verify it, bail
		if ( empty( $post_authcode_settings_nonce ) || ! wp_verify_nonce( $post_authcode_settings_nonce, 'authcode_settings_nonce' ) ) return;

		// if our current user can't edit this post, bail
		if ( ! apply_filters( 'mw_current_user_can_access', false ) ) {
			return;
		}

		// Save input content
		$authcode_value = sanitize_text_field( $post_authcode_value );

		if ( isset( $authcode_value ) ) {
			update_post_meta( $post_id, 'authcode_value', $authcode_value);
		}
	}

	/**
	 * Get list of customer's valid authcodes
	 *
	 * @param $userID int User ID
	 *
	 * @return boolean/array Either list of authcodes (array) or false if no authcodes
	 */
	public function get_customer_authcodes( $userID ) {
		// Check if userID has been passed
		if ( $userID ) {
			// Check if user exists
			if ( get_user_by( 'ID', $userID ) ) {
				// Get a list of user's valid pubcodes
				$query_authcodes_args = array(
					'post_type' => 'tfs_authcode',
					'post_status' => 'publish',
					'posts_per_page' => -1
				);

				$query_authcodes = new WP_Query( $query_authcodes_args );

				// If there are posts
				if ( $query_authcodes->have_posts() ) {
					$authcodes = array();

					// For each post
					while ( $query_authcodes->have_posts() ) {
						$query_authcodes->the_post();

						// Check if user has access to the post
						$auth_container = new agora_auth_container( $query_authcodes->post->ID );
						$auth_container = apply_filters('agora_middleware_check_permission', $auth_container);

						// If user has access to the post
						if ( $auth_container->is_allowed() OR current_user_can( 'manage_options' ) ) {
							// Get authcode of the post
							$authcode_value = get_post_meta( $query_authcodes->post->ID, 'authcode_value', true );

							if ( ! empty( $authcode_value ) ) {
								if ( ! in_array( $authcode_value, $authcodes ) ) {
									array_push( $authcodes, $authcode_value );
								}
							}
						}
					}

					wp_reset_postdata();

					// Check if user has any authcodes (access to any authcode posts)
					if ( ! empty( $authcodes ) ) {
						return $authcodes;
					}
				}
			}
		}

		return false;
	}
}
