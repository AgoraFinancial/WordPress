<?php

/**
 * Description: See the Wordpress Widget API documentation for more info: http://codex.wordpress.org/Widgets_API
 */
class agora_login_widget extends WP_Widget{

	/**
	 * Description: Holder for the agora core framework object
	 * @var object
	 */
	protected $core;

	/**
	 * Description: Constructor
	 */
	function __construct(){
		parent::__construct('agora_login_widget', __('Middleware Login Widget'), array('description' => __('Use this widget to place a login box in your sidebars')));
		$this->core = agora_core_framework::get_instance();
	} 


	/**
	 * Description: The admin form for the widget.
	 * @method form
	 * @param  array $instance The array holding the current widget instance data
	 * @return void
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'logged_out' ] ) ) {
			$logged_out = $instance[ 'logged_out' ];
		}else {
			$logged_out = __( 'Widget Title for Logged out users' );
		}

		if ( isset( $instance[ 'logged_in' ] ) ) {
			$logged_in = $instance[ 'logged_in' ];
		}else {
			$logged_in = __( 'Widget Title for Logged in users' );
		}

		$field['logged_out']['id'] = $this->get_field_id('logged_out');
		$field['logged_out']['title'] = $this->get_field_name('logged_out');
		$field['logged_out']['value'] = $logged_out;
		$field['logged_out']['label'] = __( 'Widget title for logged out users:' );

		$field['logged_in']['id'] = $this->get_field_id('logged_in');
		$field['logged_in']['title'] = $this->get_field_name('logged_in');
		$field['logged_in']['value'] = $logged_in;
		$field['logged_in']['label'] = __( 'Widget title for logged in users:' );

		$this->core->view->load('widget_form', $field);
	}

	/**
	 * Description: Updater function for the admin form
	 * @method update
	 * @param  array $new_instance 
	 * @param  array $old_instance 
	 * @return array An array of the new data
	 */
	public function update($new_instance, $old_instance){
		$instance = array();
		$instance['logged_out'] = ( ! empty( $new_instance['logged_out'] ) ) ? strip_tags( $new_instance['logged_out'] ) : '';
		$instance['logged_in'] = ( ! empty( $new_instance['logged_in'] ) ) ? strip_tags( $new_instance['logged_in'] ) : '';

		return $instance;
	}

	/**
	 * Description: Actual widget function to display the login widget
	 * @method widget
	 * @param  array $args     Widget arguments
	 * @param  array $instance settings for this particular instance of the widget
	 * @return void
	 */
	public function widget($args, $instance){
        add_action('login_form_bottom', array($this, 'mw_added_login_field'));

		$content['title'] = $title = apply_filters( 'widget_title', $instance['logged_out'] );

		if( is_user_logged_in() ){
			$content['user_name'] = $this->core->user->get_name();
			$content['title'] = $title = apply_filters( 'widget_title', $instance['logged_in'] );
		}

		$content = array_merge($content, $args, $instance);
        $flash_message = $this->core->session->flash_message('login');
        $is_widget = $this->core->session->flash_message('is_widget');

        if(  $flash_message AND $is_widget){
            $content['message']        = $flash_message->message;
            $content['message_class']  = $flash_message->class;
        }

        $content['logout_link'] = $this->core->get_language_variable('txt_logout');
		$content['forgot_username_link'] = $this->core->get_language_variable('txt_forgot_username_link');
		$content['forgot_password_link'] = $this->core->get_language_variable('txt_forgot_password_link');
		$content['forgot_password_link_short'] = $this->core->get_language_variable('txt_forgot_password_link_short');
		$content['welcome'] = $this->core->get_language_variable('txt_welcome');

        $content['form_parameters'] = array(
			'label_username' => $this->core->get_language_variable('txt_login_username_label'),
			'label_log_in' => $this->core->get_language_variable('txt_login_button'),
			'label_password' => $this->core->get_language_variable('txt_login_password_label'),
			'label_remember' => $this->core->get_language_variable('txt_login_remember'),
            'value_username' => '',
			'username_placeholder' => $this->core->get_language_variable('txt_login_username_placeholder'),
			'password_placeholder' => $this->core->get_language_variable('txt_login_password_placeholder'),
			'redirect' => site_url() . $_SERVER['REQUEST_URI'],
        );

		$this->core->view->load('mw-login-widget', $content);

        remove_action('login_form_bottom', array($this, 'mw_added_login_field'));

	}

    public function mw_added_login_field(){

        return '<input value="true" class="input" name="is_widget" hidden>';

    }
}