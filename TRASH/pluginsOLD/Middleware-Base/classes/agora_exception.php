<?php

class agora_exception extends Exception{

	protected $my_message;

	function __construct($message, $code = 0, Exception $previous = null) {

		$this->my_message = $message;

		if(is_array($message)){
			parent::__construct($message[0]->message, $code, $previous);
		}else{
			parent::__construct($message, $code, $previous);
		}
	}

	function get_message(){
		return (is_array($this->my_message)) ? $this->my_message : array($this->my_message);
	}

	function message_class(){
		return 'error';
	}
}