<?php
/**
 * Class: agora_authcode constructor.
 * Description: Authcodes are configured as a custom taxonomy. Through the use of the wp_options table we tack on extra meta data for each authcode. Such as Type, additional rules, etc.
 * @author: Threefold Systems
 */
class agora_authcode{

	/**
	 * @var
	 */
	public $name;
	/**
	 * @var
	 */
	public $term_id;
	/**
	 * @var
	 */
	public $description;

	/**
	 * @var
	 */
	public $type = 'subscriptions';

	/**
	 *
	 * @var
	 */
	public $advantage_code;

	/**
	 * @var
	 */
	public $location;

	/**
	 * @var array of rules belonging to this authcode
	 */
	public $rules = array();


	/**
	 *
	 * @param $authcode Accepts an stdClass or an Integer
	 *                  If stdClass it assumes Wordpress taxonomy properties and adds those properties to the object
	 *                  If integer, it fetches the term from the database and initialises itself with the term data.
	 */
	function __construct($authcode = null){

		$this->core = agora_core_framework::get_instance();
		if($authcode == null){
			return;
		}elseif(is_array($authcode) OR is_object($authcode)){
				foreach($authcode as $key => $value){
					$this->$key = $value;
				}
			$this->get_type();
			$this->get_advantage_code();
			$this->get_rules();
		}else{
				$x = get_term_by('id', $authcode, 'pubcode');
				$this->__construct($x);
		}
	}

	/**
	 * @method get_rules
	 * @return array of agora_auth_rule
	 */
 	public function get_rules(){
  		if(empty($this->rules)){
  			if($rules = $this->core->wp->get_term_meta($this->name)){
                $this->rules = $rules;
  			}
		} else {
             //loop here
             foreach($this->rules as $rule){
				 if ( $rule->value && $rule->value == '{{now}}' ) {
					 $rule->value = date('Y-m-d H:i:s');
					 $rule->shortcode =  "{{now}}";
				 }
             }
         }
 
  		return $this->rules;
  	}

	/**
	 * @method get_type
	 * @return mixed
	 */
	public function get_type(){
		$type =  $this->core->wp->get_term_meta($this->name . '_type');
		return $this->type = ($type) ? $type : 'subscriptions';
	}

	/**
	 * @method get_advemtage_code
	 * @return mixed
	 */
	public function get_advantage_code(){
		$advantage_code = $this->core->wp->get_term_meta($this->name . '_advantage_code');
		return $this->advantage_code = ($advantage_code) ? $advantage_code : '';
	}

	/**
	 * Description: Delete this authentication code
	 * @method delete
	 * @return bool|WP_Error
	 */
	public function delete(){
		$this->core->wp->delete_term_meta($this->name . '_type');
		$this->core->wp->delete_term_meta($this->name . '_advantage_code');
		$this->core->wp->delete_term_meta($this->name);

		return wp_delete_term( (int) $this->term_id, 'pubcode');
	}

	/**
	 * Description: Save this authenctation code along with any rules it might have
	 * @method save
	 * @return $this->update/insert
	 */
	public function save(){
		if($this->term_id AND $this->_validate()){
			if($this->rules)
				$this->save_rules();
			$this->update();
		}elseif($this->_validate()){
			$this->insert();
		}
	}

	/**
	 * Description: Validate this authentication code (used prior to saving)
	 * @return bool
	 */
	private function _validate(){
		if($this->name AND $this->description){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Description: Save the rules belonging to this authentication code
	 * @method save_rules
	 * @return mixed
	 */
	public function save_rules(){
		return $this->core->wp->set_term_meta($this->name, $this->rules);
	}

	/**
	 * Description: Update this authentication code
	 * @method update
	 * @return array|WP_Error
	 */
	public function update(){
		$this->set_type();
		$this->set_advantage_code();
		return wp_update_term($this->term_id, 'pubcode', array('description' => $this->description));
	}

	/**
	 * Description: Set the type field for this authcode. Type is stored as custom meta since WP lacks this feature.
	 * @method set_type
	 * @param null $value
	 */
	public function set_type($value = null){
		if($value) $this->type = $value;
		$this->core->wp->set_term_meta($this->name . '_type', $this->type);
	}

	/**
	 * @method set_advantage_code
	 * @param mixed $value
	 */
	public function set_advantage_code($value = null){
		if($value) $this->advantage_code = $value;
		$this->core->wp->set_term_meta($this->name . '_advantage_code', $this->advantage_code);
	}

	/**
	 * Description: Create this authcode, and store it's type meta
	 * @return array|WP_Error
	 */
	public function insert(){
		$this->set_type();
		$this->set_advantage_code();
		return wp_insert_term($this->name, 'pubcode', array('description' => $this->description));
	}

	/**
	 * Description: Update the rule at $rule_id with the given $rule
	 * @param $rule_id
	 * @param $rule
	 * @return $this
	 */
	public function update_rule($rule_id, $rule){
		if(isset($this->rules[$rule_id])){
			$this->rules[$rule_id] = $rule;
			$this->save_rules();
		}
		return $this;
	}

	/**
	 * @param agora_auth_rule $rule
	 * @return mixed
	 */
	public function add_rule(agora_auth_rule $rule){
		$this->rules[] = $rule;
		return $this;
	}

	/**
	 * Description: Deletes all rules associated with this authcode
	 * @method delete_rules
	 */
	public function delete_rules(){
		$this->core->wp->delete_term_meta($this->name);
		$this->rules = array();
	}

	/**
	 * Description: Delete the individual rule at the given $rule_id
	 * @param $rule_id
	 * @return $this
	 */
	public function delete_rule($rule_id){
		if(isset($this->rules[$rule_id])){
			unset($this->rules[$rule_id]);
			$this->rules = array_values($this->rules);
		}
		return $this;
	}

	/**
	 * Description: Does this authcode have any rules
	 * @return bool
	 */
	public function has_rules(){
		if(sizeof($this->rules) > 0){
			return true;
		}else{
			return false;
		}
	}
}