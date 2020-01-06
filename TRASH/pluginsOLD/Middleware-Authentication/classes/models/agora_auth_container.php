<?php

/**
 * Class: agora_auth_container constructor.
 * Description: A class to contain authentication information.
 *
 * Agora Authentication Methods are passed an object of this class
 * and they can identify if the user should be allowed to view the
 * content at $post_id
 *
 * The object is eventually returned to the Auth plugin which shows/hides
 * the content depending on the final state of the object.
 *
 * The final object is also passed to the login template to allow affiliates
 * to perform actions based on it.
 * Version: 1.0
 * @author: Threefold Systems
 */
class agora_auth_container {


	/**
	 * Description: The ID of the content object that's being inspected
	 * @var null
	 */
	public $post_id = null;


	/**
	 * Description: If any one auth method allows access the user will be let in.
	 * @var null
	 */
	private $explicity_allowed = null;


	/**
	 * Description: An array of the protection methods. e.g.:
	 *              array(  'type' => 'pubcode',
	 *                      'name' => 'PUB',
	 *                      'message' => 'You are not allowed to view this content'
	 *              )
	 * @var array
	 */
	private $protection = array();

	/**
	 * Class: agora_auth_container constructor.
	 * @author: Threefold Systems
	 * @parm $post_id The Id of the content object we're looking at
	 */
	function __construct($post_id){
		$this->post_id = $post_id;

		//by pass for admins
		if ( apply_filters( 'mw_current_user_can_access', false ) ) {
			$this->allow();
		}
	}

	/**
	 * Class: is_allowed constructor.
	 * @author: Threefold Systems
	 * @method is_allowed
	 * $return boolean
	 */
	public function is_allowed(){
		if($this->explicity_allowed) return true;
		if($this->explicity_allowed == null AND sizeof($this->protection) == 0) return true;
		return false;
	}

	/**
	 * Description: Set the explicitly_allowed flag to true and return the object instance
	 * @method allow
	 * @return $this
	 */
	public function allow(){
		$this->explicity_allowed = true;
		return $this;
	}


	/**
	 * Description: Add a method of protection to the object. Later if the allowed flag is false, and there are protection methods in place, the user will be denied access.
	 *
	 * @method protected_by
	 * @param $auth_type
	 * @param $name
	 * @param $message
	 * @return $this
	 */
	public function protected_by($auth_type, $name, $message){

		array_unshift($this->protection, array('type' => $auth_type, 'name' => $name, 'message' => $message));
		return $this;
	}

	/**
	 * Description: Determine if the content is protected by some method
	 * @method is_protected
	 * @return bool
	 */
	public function is_protected(){
		return (sizeof($this->protection) > 0)? true : false;
	}

	/**
	 * Description: Get the first protection method registered.
	 * @method first
	 * @param null $field [Optional] return just the supplied field from the first protection method
	 * @return mixed
	 */
	public function first($field = null){
		if($field) return $this->protection[0][$field];

		return $this->protection[0];
	}

	/**
	 * Description: Returns the entire protection methods array
	 * @method get_protection
	 * @return array|bool
	 */
	public function get_protection(){

		if(is_array($this->protection)){

			return $this->protection;

		}else{

			return false;
		}
	}

}