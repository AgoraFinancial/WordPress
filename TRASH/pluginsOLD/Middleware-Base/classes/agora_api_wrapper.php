<?php

/**
 * Class: agora_api_wrapper constructor.
 * @author: Threefold Systems
 */
class agora_api_wrapper{

	/**
	 * Description: The URL to use for the API calls
	 * @var string
	 */
	public $url;

	/**
	 * Description: The token used to authenticate with the rest service. Inserted into the header as a value for 'token:'
	 * @var string
	 */
	public $token;


	/**
	 * Description: Function to retrieve the *users* IP address
	 * @author: Threefold Systems
	 * @method get_user_ip
	 * @return string
	 */
	public function get_user_ip(){

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return filter_var($ip, FILTER_VALIDATE_IP);
	}

}