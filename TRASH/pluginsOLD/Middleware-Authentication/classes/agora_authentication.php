<?php
/**
 * Class: agora_authentication constructor.
 * Description: A base class for authentication methods
 * @author: Threefold Systems
 */
abstract class agora_authentication{

	/**
	 * @var int
	 */
	public $priority = 1;

	/**
	 * @var array of what fields an auth code can use
	 */
	public $field_structure;

	/**
	 * @var array of available auth_types
	 */
	public $auth_types;

	/**
	 * @var array of where auth types are stored in relation to middleware data.
	 */
	public $auth_type_location;

	/**
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Constructor
	 * @param $config null
	 */
	public function __construct($config = null){

		$this->field_structure  = $config['field_structure'];
		$this->auth_types = $config['auth_types'];
		$this->auth_type_locations = $config['auth_type_locations'];

		$this->plugin_dir   = dirname(__FILE__) . '/..';
		$this->core         = agora_core_framework::get_instance();

		add_filter( 'agora_middleware_check_permission' , array($this, 'check_permission'), $this->priority, 2);
		add_action( 'agora_authentication_disable_cache', array($this, 'disable_cache') );
		add_filter( 'agora_get_login_url', array($this, 'get_login_url'), 0, 1);
	}


	/**
	 * Description: Method to tell wordpress to disable caching
	 * @method disable_cache
	 */
	public function disable_cache(){

		if($this->is_protected_content()){
			defined('DONOTCACHEPAGE') or define('DONOTCACHEPAGE', true);
		}
	}

	/**
	 * Description: Function to get a list of authcodes
	 * @method get_all_authocdes
	 * @param  string $args
	 * @return array - An Array of objects representing the pubcodes taxonomy
	 */

	public function get_all_authcodes($args = '') {
		$defaults = array('type' => 'pubcode', 'hide_empty' => false);
		$args = wp_parse_args($args, $defaults);
		$pubcodes = get_terms('pubcode', $args);
		foreach($pubcodes as &$p) {
			$p = new agora_authcode($p);
		}
		return $pubcodes;
	}

	/**
	 * Description: Save pubcode relationships to a post
	 * @method save_post_authcodes
	 * @param $post_ID
	 * @param $post
	 * @return array|bool|WP_Error
	 */
	public function save_post_authcodes($post_ID, $post) {
		$post_post_type = ( ! empty( $_POST[ 'post_type' ] ) ? sanitize_text_field( $_POST[ 'post_type' ] ) : '' );

		$post_post_pubcode = ( ! empty( $_POST[ 'post_pubcode' ] ) ? $_POST[ 'post_pubcode' ] : '' );

		if($post->post_type == 'revision' || empty($post_post_type))
			return;

		$post_pubcodes = isset( $post_post_pubcode ) ? $post_post_pubcode : false;

		if ( is_array( $post_pubcodes ) ) {
			$post_pubcodes = array_map( 'sanitize_text_field', wp_unslash( $post_pubcodes ) );

			$post_pubcodes = array_map( 'intval', $post_pubcodes );
			$post_pubcodes = array_unique( $post_pubcodes );

			return wp_set_object_terms($post_ID, $post_pubcodes, 'pubcode');
		} else {
			wp_delete_object_term_relationships($post_ID, 'pubcode' );

			return true;
		}

		return false;
	}

	/**
	 * Description: Method to determine if the current content object is password protected
	 * @method is_protected_content
	 * @return bool
	 */
	public function is_protected_content(){
		global $post;
		if(isset($post)){
			$result = $this->get_post_authcodes($post->ID);
			if(is_array($result)) return true;
		}
		return false;
	}



	/**
	 * Description: Retrieves the pubcodes assigned to a given post, or the current global post.
	 * @param null $post_id
 	 * @method get_post_authcodes
	 * @return bool
	 */
	public function get_post_authcodes($post_id=null) {

		if(!$post_id){
			global $post;

			if(isset($post)) {
				$post_id = $post->ID;
			}else{
				return;
			}
		}

		foreach(wp_get_object_terms( $post_id, 'pubcode' ) as $code) {
			$post_pubcodes[$code->term_id] = new agora_authcode($code);
		}

		if( isset($post_pubcodes) AND is_array($post_pubcodes) )
			return $post_pubcodes;

		// return false;
	}

	//hide content shortcode
	public function get_authcodes_by_name( $codes ) {
		$result = array();

		if ( is_array( $codes ) ) {
			foreach ( $codes as $code ){
				$result[] = new agora_authcode( get_term_by( 'name', trim( $code ), 'pubcode' ) );
			}
		}

		return $result;
	}

	/**
	 * Description: Hooks into agora_get_login_url to retrieve the URL the user is attempting to log into
	 * @method: get_login_url
	 * @return string
	 */
	public function get_login_url($url){
		$post_redirect_to = ( isset( $_POST[ 'redirect_to' ] ) ? sanitize_text_field( $_POST[ 'redirect_to' ] ) : '' );

		if ( ! empty( $post_redirect_to ) ) {
			return $post_redirect_to;
		}else{
			return $url;
		}
	}
}