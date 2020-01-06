<?php


/**
 * Class: agora_auth_rule constructor.
 * @author: Threefold Systems
 */
class agora_auth_rule{

	/**
	 * Description: The name of the field this rule evaluates
	 * @var string
	 */
	public $field;

	/**
	 * Description: The parent type of this field. e.g. Subscriptions, Products, AccessMaintenanceBilling
	 * @var string
	 */
	public $field_group;

	/**
	 * Description: The operator used to evaluate this rule
	 * @var string
	 */
	public $operator;

	/**
	 * Description: The value to compare with the field
	 * @var string
	 */
	public $value;

	/**
	 * Description: Fields that will be picked up when an array/object is passed to the constructor.
	 * @var array
	 */
	public $public_fields = array('field', 'field_group', 'operator', 'value');


	/**
	 * Description: The various operators and their readable equivalents
	 * @var array
	 */
	static $operators = array(
		'equals'  => 'Equals',
		'notEquals' => 'Not Equals',
		'greaterThan'  => 'Greater Than',
		'greaterThanEqual' => 'Greater Than or Equal',
		'lessThan'  => 'Less Than',
		'lessThanEqual' => 'Less Than or Equal',
		'contains' => 'Contains',
		'containedIn' => 'Contained In',
		'notContainedIn' => 'Not contained In',
		'doesNotContain' => 'Does Not Contain',
		'startsWith'  => 'Starts With',
		'endsWith'  => 'Ends With'
	);

	/**
	 * Description: Optional parameter can be used to initialise a rule based on an object or associative array.
	 * @param null $rule
	 */
	public function __construct($rule = null){
		if ($rule) {
			foreach ($rule as $field => $value) {
				$this->$field = $value;
			}
		}
	}

	/**
	 * Class: get_value constructor.
	 * @author: Threefold Systems
	 * @method get_value
	 * @return $this->value
	 */
	public function get_value(){
		if(strpos($this->field, 'Date') AND !is_int($this->value)){
			return strtotime($this->value);
		}else{
			return $this->value;
		}
	}

	/**
	 * @param $input
	 * @method create
	 * @return agora_auth_rule
	 */
	public static function create($input){
		$rule = new self;
		foreach ($rule->public_fields as $field_name){
			$rule->$field_name  = $input[$field_name];
		}
		return $rule;
	}

	/**
	 * Description: The 'field_path' is used to locate the field based on the structure passed to it.
	 * @param $field_path
	 * @method get_field_path
	 * @return array
	 */
	public function get_field_path($field_path){
		return explode('.', $field_path[$this->field_group][$this->field]['path']);
	}

	/**
	 * Description: Returns the readable version of the current rules operator
	 * @method readable_operator
	 * @return mixed
	 */
	public function readable_operator()
	{
		return self::$operators[$this->operator];
	}


	/**
	 * Description: Returns the operators array. Used for populating drop-down menus
	 * @method get_operators
	 * @return array
	 */
	public function get_operators(){
		return self::$operators;
	}

	/**
	 * Description: Evaluates the current rule, given the matching product, and field structure
	 * @method evaluate
	 * @param $product
	 * @param $field_structure
	 * @return bool
	 */
	public function evaluate($product, $field_structure){

		$value = $this->_resolve_path($product, $this->get_field_path($field_structure));

		switch ($this->operator){
			case 'equals':
				return $this->get_value() == $value;
			break;
			case 'notEquals':
				return $this->get_value() != $value;
			break;
			case 'greaterThan':
				return $value > $this->get_value();
			break;
			case 'greaterThanEqual':
				return $value >= $this->get_value();
			break;
			case 'lessThanEqual':
				return $value <= $this->get_value();
			break;
			case 'lessThan':
				return $value < $this->get_value();
			break;
			case 'contains':
				return (strpos($value, $this->get_value())) ? true : false;
			break;
			case 'containedIn':
                return (array_search($value, explode(',', str_replace(", ", ",", $this->get_value()))) !== false) ? true : false;
			break;
			case 'notContainedIn':
				return (array_search($value, explode(',', str_replace(", ", ",", $this->get_value()))) !== false) ? false : true;
				break;
			case 'doesNotContain':
				return (!strpos($value, $this->get_value())) ? true : false;
			break;
			case 'startsWith':
				return $this->get_value() === '' || strpos($value, $this->get_value()) === 0;
			break;
			case 'endsWith':
				return $this->get_value() === '' || substr($value, -strlen($this->get_value())) === $this->get_value();
			break;
		}
	}

	/**
	 * Description: Examines the product/object and returns the value at the end of the given field path.
	 * @method _resolve_path
	 * @param $data_object
	 * @param $field_path
	 * @return mixed
	 */
	private function _resolve_path($data_object, $field_path){

		if($x = array_shift($field_path)){
			$data_object = $this->_resolve_path($data_object->{$x}, $field_path);
		}
		if(strpos($this->field, 'Date') AND !is_int($data_object)){
			return strtotime($data_object);
		}else{
			return $data_object;
		}
	}
}