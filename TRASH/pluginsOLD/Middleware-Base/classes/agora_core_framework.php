<?php
/**
 * Description: Agora Core Framework Class The core class for all agora middleware plugins. Acts as a rough framework for other agora and middleware related plugins to hook on to
 * Class agora_core_framework
 * @package    agora_core_framework
 * @license    Proprietary
 */
use KeenIO\Client\KeenIOClient;

class agora_core_framework {

	/**
	 * Description: The name for the config group in wordpress see http://codex.wordpress.org/Settings_API for more info about the Settings API
	 * @var string
	 */
	public $config_name;

	/**
	 * Description: An array with all the config options
	 * @var array
	 */
	public $config;

	/**
	 * Description: Language variables
	 * @var array
	 */
	private $vars = false;

	/**
	 * @var agora_rulepoint_client
	 */
	public $event;

	/**
	 * Description: Text domain for multi lang support, also used as basename for the plugin.
	 * @var string
	 */
	public $domain;

	/**
	 * Description: This class follows a singleton pattern.
	 * @return static
	 */
	public static function get_instance(){

		static $instance = null;

		if (null === $instance) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Class: agora_core_framework constructor.
	 * @author: Threefold Systems
	 */
	protected function __construct(){

		// Load class files
		include_once('agora_api_wrapper.php');
		include_once('agora_database_wrapper.php');
		include_once('agora_middleware_wrapper.php');
		include_once('agora_view_framework.php');
		include_once('agora_log_framework.php');
		include_once('agora_session_framework.php');
		include_once('agora_input_framework.php');
		include_once('agora_exception.php');
		include_once('agora_rulepoint_client.php');
        include_once('agora_mc_wrapper.php');

		$this->domain = 'agora_core_framework';

		$this->config_name = $this->domain .'_config';
		$this->event_config_name = $this->config_name .'_event';
        $this->mc_config_name = $this->config_name . '_mc';

		// Initialize the wp (database wrapper) object
		$this->wp = new agora_database_wrapper();

		// Initialize the session framework
		$this->session = new agora_session_framework();


		// Load the base config from file
		$base_config = parse_ini_file( dirname(__FILE__) . '/../default_config.ini');
		$eventing_base_config = parse_ini_file(dirname(__FILE__) . '/../eventing_default_config.ini');
        $mc_base_config = parse_ini_file(dirname(__FILE__) . '/../mc_default_config.ini');
		$this->eventing_admin_page = 'agora-middleware-eventing';

        // MC admin page
        $this->mc_admin_page = 'agora-mc';

        // Default options are kept in the defaultConfig.ini
		$this->config = $this->wp->get_option($this->config_name, $base_config);

		// Initialize the event tracking module

		$this->event = new agora_rulepoint_client($this->wp->get_option($this->event_config_name, $eventing_base_config), $this->config['production']);
		// Sets the name for the core plugin admin page
		$this->plugin_admin_page = 'agora-middleware-base';

		// Settings for the language variables admin
		$this->language_config = 'language_vars_config';
		$this->language_admin_page = 'agora-middleware-variables';

		// Add a log object to the base
		if($this->config['logging'] == 1 && !defined('AGORA_MW_LOG_ENABLED')){
			define('AGORA_MW_LOG_ENABLED', $this->config['logging']);
		}

		$this->log = new agora_log_framework();

		// Initialize the mw (middleware wrapper) object and give it a log object
		$this->mw = new agora_middleware_wrapper($this->config, $this);

        // Initialize the mc object
        $this->mc = new agora_mc_wrapper($this->wp->get_option($this->mc_config_name, $mc_base_config), $this);

		// Initialize the view object and give it a log object
		$this->view = new agora_view_framework();

		add_action( 'init', array( $this, 'text_domain' ) );
		add_action( 'init', array($this->session, 'session_start') );
		add_action( 'wp_logout', array($this->session, 'end_session'));
		add_action( 'wp_login', array($this->session, 'end_session'));
		add_filter( 'agora_middleware_admin_menu', array($this, 'add_tab_items'), 1, 1);
	}


	/**
	 * Class: add_tab_items constructor.
	 * @author: Threefold Systems
	 * @method add_tab_items
	 * @param $menu
	 * @return array
	 */
    public function add_tab_items($menu){
		$menu[] = array('title' => __('Base Settings'), 'page' => $this->plugin_admin_page);
		$menu[] = array('title' => __('Language Variables'), 'page' => $this->language_admin_page);
//		$menu[] = array('title' => __('Eventing'), 'page' => $this->eventing_admin_page);
        $menu[] = array('title' => __('Message Central'), 'page' => $this->mc_admin_page);
		return $menu;
	}


	/**
	 * Load the text domain
	 *
	 * @param void
	 * @return void
	 *
	 **/
	public function text_domain() {
		load_plugin_textdomain($this->domain, false, dirname(plugin_basename(__FILE__)) . '/lang/');
	}

	/**
	 * Function to display admin notices
	 * @param void
	 * @return void
	 *
	 **/
	public function admin_notices(){
		if ($notices= get_option('agora_mw_deferred_admin_notices')) {
    		foreach ($notices as $notice) {
      			$this->view->load('update_message', $notice);
    		}
			delete_option('agora_mw_deferred_admin_notices');
  		}

		if($notices= get_option('agora_mw_import_notices')) {
			foreach ($notices as $key=>$value) {
				if(strpos($key, 'error') === 0){
					$class = 'error';
				} else {
					$class = 'success';
				}
				printf( '<div class="notice notice-%1$s"><p>%2$s</p></div>', $class, $value);
			}
			delete_option('agora_mw_import_notices');
		}

	}

	/**
	 * Function to sanitize and validate options input
	 *	@param array $input 	The options array submitted by the form
	 * 	@return array
	 **/
	public function _sanitize_option_input($input){

		$output = array();

		foreach($input as $k => $v){
			if(isset($input[$k])){
				if(substr($input[$k],3,5) == '*****') {
					if($k == 'mc_token'){
						$output[$k] = $this->mc->token;
					} else {
						$output[$k] = $this->config[$k];
					}
				} else {
					$input[$k] = str_replace('http://', '', $input[$k]);
					$input[$k] = str_replace('https://', '', $input[$k]);
					$output[$k] = strip_tags(stripslashes($input[$k]));
				}
			}
		}

		return apply_filters( 'agora_middleware_sanitize_option_input', $output, $input );
	}


	/**
	 * Registers language variables with the system.
	 *
	 * This plugin and other related plugins can make use of the language variables to
	 * provide an interface Wordpress so Admins can customise the messages used throughout the system
	 *
	 *
	 * @param  array $new an associative array of language variables and their corresponding text
	 * @return mixed returns false on error
	 */
	public function register_language_variables($new){

		if(is_array($new)){
			$current = $this->wp->get_option($this->language_config);

			$updated = wp_parse_args($current, $new);

			return $this->wp->update_option($this->language_config, $updated);

		}else{
			return false;
		}
	}


	/**
	 * Loads & returns the requested language variable from wordpress.
	 *
	 * @param   $var_name the specific language variable requested
	 * @param   $content An key => value array of fields that will be find/replaced into the content
	 * @return string/array A string where a single variable has been requested, an array where no specific variable has been requested
	 */
	public function get_language_variable( $var_name = null, $content = null){
		if ( ! $this->vars ) {
			$this->vars = $this->wp->get_option($this->language_config);
		}

		if( $var_name ){
			$snippet = apply_filters('before_' . $var_name, isset( $this->vars[$var_name] ) ? $this->vars[$var_name] : null );
			if( $content ){
				foreach($content as $key => $value){
					$snippet = str_replace('{{'.$key.'}}', $value, $snippet);
				}
			}

			$language_variable = apply_filters( $var_name, $snippet );

			return $language_variable;
		}else{
			return $this->vars;
		}
	}

	/**
	 *
	 * Helper function to create pages needed by the plugin on install
	 *
	 * @param  string  $slug         A page slug
	 * @param  string  $page_title   [description]
	 * @param  string  $page_content [description]
	 * @param  integer $post_parent  [description]
	 * @return void
	 */
	public function _create_page( $slug, $page_title = '', $page_content = '', $post_parent = 0 ) {

		global $wpdb;
		$page_data = array(
	        'post_status' 		=> 'publish',
	        'post_type' 		=> 'page',
	        'post_author' 		=> 1,
	        'post_name' 		=> $slug,
	        'post_title' 		=> $page_title,
	        'post_content' 		=> $page_content,
	        'post_parent' 		=> $post_parent,
	        'comment_status' 	=> 'closed'
	    );

		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );

		if ( $page_found ) {
			$page_data['ID'] = $page_found;
		}

	    $page_id = wp_insert_post( $page_data );
	    return $page_id;
	}


	/**
	 * keen_reporting
	 *
	 * @param array $args
	 *
	 * returns: WP-Error or array
	 */
	public function keen_reporting( $stream, $event_info )
	{
		// Ensure stream and plugin info have been passed in
		if ( $stream && $event_info && is_array( $event_info ) ) {
			// Project ID / Write key
			$client = KeenIOClient::factory([
				'projectId' => '5a14526cc9e77c000109974b',
				'writeKey' => 'F23E4DBFA5CAF100D90956C3E04F209A43DDD314E6CE45EC4358E5E4CB63E0DBB4AACBAA848B72EE7B638DBB0D542A95AE8B5E5C68CCC6CEAB7AF51E767126862BB8430D952649CA6D583EBF46524A78E8549717AA607113BC6440B9DEDFECE0'
			]);

			$website_info = array(
				'site_name'  => get_bloginfo('name'),
				'source_url' => get_site_url(),
				'admin_email' => get_bloginfo('admin_email'),
			);

			$sendData['user_to_perform_action'] = 'not available';
			$sendData['user_email'] = 'not available';

			if ( function_exists( 'wp_get_current_user' ) ) {
				$current_user = wp_get_current_user();

				if ( $current_user != false ) {
					$sendData['user_to_perform_action'] = $current_user->data->user_login;
					$sendData['user_email'] = $current_user->data->user_email;
				}
			}

			$event = array(
				'event' => $event_info,
				'website' => $website_info
			);

			$client->addEvent( $stream, $event );
		}
	}


	/**
	 * sendToReportingService
	 *
	 * @param array $args
	 *
	 * returns: WP-Error or array
	 *
	 * Left it as legacy mode for plugins such as mobile app plugin.
     */
    public function sendToReportingService( $args = array() )
	{
		return true;
	}


	/**
	 * tfs_hash
	 *
	 * @param string $string_to_hash
	 *
	 * returns: WP-Error or array
	 *
	 * TFS hash function. Replacement for MD5
	 */
	public function tfs_hash( $string_to_hash )
	{
		return hash( 'sha256', $string_to_hash );
	}


	/**
	 * Auto update
	 *
	 * @param $repository_url string Repository URL
	 * @param $plugin_file string Plugin file
	 * @param $branch string Branch name
	 *
	 * @return void
	 */
	public function auto_update( $repository_url, $plugin_file, $branch ) {
		// Auto updater
		if ( $repository_url && $plugin_file && $branch ) {
			$className = PucFactory::getLatestClassVersion('PucGitHubChecker');

			$myUpdateChecker = new $className(
				$repository_url,
				$plugin_file,
				$branch
			);

            $myUpdateChecker->setAccessToken('53a317cf3d9b8b132b921e98f6cfdded05c29374');
		}
	}

	/**
	 * safe_token
	 *
	 * @param $token
	 * @return string
	 */
	public function safe_token($token){
		if($token == '') {
			return $token;
		}
		$length_to_obscure = strlen($token) - 7; //7 = first 3 + last 4
		return substr($token, 0, 3) . str_repeat('*', $length_to_obscure) . substr($token, -4);
	}

	/**
	 * tfs_monitor
	 *
	 * Disable by adding:
	 * add_filter( 'tfs_killswitch', function( $payload ) { return $payload = false; } );
	 * to functions.php
	 *
	 * @param $payload
     */
	public function tfs_monitor( $payload, $event_name = 'Monitor' )
	{
		// Check for killswitch
        $tfs_killswitch = apply_filters( 'tfs_killswitch', $payload );

        // Check if data has been overriden by extension data
        if ( $tfs_killswitch != $payload ) {
			return false;
		}

		$this->keen_reporting( $event_name, $payload );
	}

	/**
	* Private clone method to prevent cloning of the instance of the
	* *Singleton* instance.
	*
	* @return void
	*/
	private function __clone(){

	}
}
