<?php
/**
 * Created by PhpStorm.
 * User: ciaran
 * Date: 5/8/14
 * Time: 11:35 AM
 */

class agora_input_framework {


	protected $result = array();
	protected $input = null;
	protected $core;

	function __construct(agora_core_framework $core) {
		$this->core = $core;
	}


	public function get( $var = null){
		if(!$this->input) $this->input = $_GET;
		if($var){
			return (isset($this->input[$var])) ? $this->input[$var] : null;
		}else{
			return $this;
		}
	}

	public function post( $var = null){
		if(!$this->input) $this->input = $_POST;
		if($var){
			return (isset($this->input[$var])) ? $this->input[$var] : null;
		}else{
			return $this;
		}
	}

	public function all(){
		return $this->input;
	}

	public function validate($validator){

		if(!$this->input) return false;

		foreach($validator as $field => $rules){
			foreach(explode('|', $rules) as $rule){
				$this->$rule($field);
			}
		}
		if(count($this->result) > 0){
			return $this->result;
		}else{
			return true;
		}
	}

	private function failure_message($field, $rule){
		$message = new stdClass();
		$message->field = $field;
		$message->rule = $rule;
		$message->message = $this->core->get_language_variable('txt_'. $field .'_'. $rule .'_validation_failed');
		$this->result[] = $message;
	}

	private function required($field){
		if(isset($this->input[$field]) AND $this->input[$field] != ""){
			return true;
		}else{
			$this->failure_message($field, 'required');
			return false;
		}
	}

	private function email($field){
		if(filter_var($this->input[$field], FILTER_VALIDATE_EMAIL) == false OR $this->input[$field] == ''){
			$this->failure_message($field, 'email');
			return false;
		}else{
			return true;
		}
	}

	private function text($field){
		if(preg_match("/^[a-zA-Z,'.\-\s]*$/", stripslashes($this->input[$field])) == 1 OR $this->input[$field] == ''){
			return true;
		}else{
			$this->failure_message($field, 'text');
			return false;
		}
	}

	/**
	 * @param null $input
	 */
	public function setInput( $input ) {
		$this->input = $input;
	}


	private function confirm($field){
		if($this->input[$field] == '' AND  $this->input[$field . '_confirm'] == '') return true;

		if($this->input[$field] == $this->input[$field . '_confirm']){
			return true;
		}else{
			$this->failure_message($field, 'confirm');
			return false;
		}
	}

	private function numeric($field){
		if( isset( $this->input[$field] ) && ( is_numeric($this->input[$field]) OR $this->input[$field] == '' ) ){
			return true;
		}else{
			$this->failure_message($field, 'numeric');
			return false;
		}
	}
	/**
	 * Validates a VID value based on MC variables
	 *
	 * @param $field
	 *
	 * Due to changes on Message Central the variables for tokenized logins have changed
	 * Version 1.2.2.3 of the Auth plugin reflects these changes also.
	 *
	 * @return bool
	 */
	private function mc_login_token($field){

		$mid = isset( $this->input['o'] ) ? $this->input['o'] : null;
		$cid = isset( $this->input['u'] ) ? $this->input['u'] : null;
		$oid = isset( $this->input['a'] ) ? $this->input['a'] : null;
		$_field = isset( $this->input[$field] ) ? $this->input[$field] : null;

		if($this->vid_test($mid, $cid, $oid, $_field)){
			return true;
		}else{
			$this->failure_message($field, 'token');
			return false;
		}
	}

	/**
	 * Validates a VID value
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	private function login_token($field){
		$mid = isset( $this->input['mid'] ) ? $this->input['mid'] : null;
		$cid = isset( $this->input['cid'] ) ? $this->input['cid'] : null;
		$oid = isset( $this->input['oid'] ) ? $this->input['oid'] : null;
		$_field = isset( $this->input[$field] ) ? $this->input[$field] : null;
		if($this->vid_test($mid, $cid, $oid, $_field)){
			return true;
		}else{
			$this->failure_message($field, 'token');
			return false;
		}
	}

	/**
	 * Function to perform security check on "vid" URL variable sent from the affiliates' website.
	 * Note: vid key is always exactly 6 characters long
	 *
	 * @param $mailingID
	 * @param $contactID
	 * @param $orgID
	 * @param $vid_test
	 *
	 * @return bool
	 */
	private function vid_test($mailingID, $contactID, $orgID = null, $vid_test){

		$shsec = '5018bbccde7b4d3ecf0b800b39e7200f';

		$valueToHash = $mailingID.'|'.$contactID.'|'.$orgID.'|'.$shsec;

		$hash = $this->md5_base64($valueToHash);

		$hashArray = str_split($hash);

		$vid = $hashArray[1].$hashArray[8].$hashArray[4].$hashArray[15].$hashArray[2].$hashArray[11];

		$vid = str_replace("/", "-", $vid);

		$vid = str_replace("+", "_", $vid);

		if ( $vid != $vid_test ) {
			return false;
		}else{
			return true;
		}
	}

	/**
	 * Required to properly generate an MD5 hash matching the corresponding perl hash code.
	 * @param $data
	 * @return string
	 */
	function md5_base64 ( $data )
	{
		$hash = pack("H*", md5($data));
		$encode = base64_encode($hash);
		return $encode;
	}
}
