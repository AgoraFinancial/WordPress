<?php


/**
* Agora Session Framework
*
* Provides functions for working with session data
*/	
class agora_session_framework
{
	public $basename = 'agora_session_var';

	public $to_set;

	public $to_clear;

	public function session_start(){
		if(!session_id())
        	session_start();
	}

	/**
	 * Sets or retreives a flash message for passing messages between pageloads
	 *
	 * 	
	 * @param  string $key   A name for the message you want to store
	 * @param  mixed $value  (Optional) A Value to store in the session
	 * @param  string $class (Optional) A class to store, can be something like 'error', 'message', 'notice', 'warning' etc. Defaults to 'message'
	 * @return mixed        Returns true if successfully stored data, returns object with message, and class properties
	 */
	public function flash_message($key, $value = null, $class = 'message'){
		
		if( $value ){

			$message = array(
				'key' => $key,
				'value' => array(
					'message' => $value,
					'class' => $class
				)
			);

			$this->set_flash_message($message);

			return true;

		}elseif( isset( $_SESSION[$this->basename][$key] ) ){
			
			$message = new stdClass();
			$message->message = $_SESSION[ $this->basename][$key]['message'];
			$message->class = $_SESSION[ $this->basename ][$key]['class'];

			$this->to_clear[] = $key;

			// Clearing the flash messages needs to be delayed as filters can cause the messages to be called multiple times in one pageload.
			add_action('wp_footer', array(&$this, 'clear_flash_messages'));

			return $message;
		}
		return false;
	}


	/**
	 * Kill the session
	 */
	public function end_session(){
		session_destroy();
	}


	/**
	 * Sets the session data
	 * @param $message
	 */
	public function set_flash_message($message){

			$_SESSION[$this->basename][$message['key']] = $message['value'];
	}

	/**
	 * Delete a specific flash message
	 *
	 * @param $message_key
	 * 
	 */
	public function delete_flash_message($message_key){
			unset($_SESSION[$this->basename][$message_key]);
	}


	/**
	 *  Clears the flash messages from the session
	 *  Hooked to the wp_footer so that the messages can be retrieved multiple times
	 *  in one page load before being destroyed.
	 */
	public function clear_flash_messages(){
		foreach($this->to_clear as $key){
			unset($_SESSION[$this->basename][$key]);
		}
	}
}