<?php
/**
 * Class: agora_advantage_item constructor.
 * Description: Base class for mapping advantage subscription items to a php object.
 * @author: Threefold Systems
 */
class agora_advantage_item {

	/**
	 * Class: agora_advantage_item constructor.
	 * @author: Threefold Systems
	 */
	function __construct($item, agora_authcode $authcode = null) {

		foreach($item as $k => $v){
			$this->$k = $v;
		}
		$this->authcode = $authcode;
	}
}
