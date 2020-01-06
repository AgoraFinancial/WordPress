<?php
/**
 * Class: agora_product constructor.
 * Description: Accepts an stdClass object of product data and transforms to a more usable structure.
 * @author: Threefold Systems
 */
class agora_product extends agora_advantage_item{

	/**
	 * @var $allow_access, $description, $item_number, $item_type, $authcode
	 */
	public $allow_access, $description, $item_number, $item_type, $authcode;

	/**
	 * Class: agora_product constructor.
	 * @author: Threefold Systems
	 * @param $product
	 * @param $authcode
	 */
	public function __construct($product, agora_authcode $authcode = null){
		$this->allow_access     = isset($product->allowAccess) ? $product->allowAccess : 'Y';
		$this->description      = isset($product->item->itemDescription) ? $product->item->itemDescription : $authcode->description;
		$this->item_number      = isset($product->item->itemNumber) ? strtoupper($product->item->itemNumber): $authcode->advantage_code;
		$this->item_type        = isset($product->item->itemType) ? $product->item->itemType : 'PRO';
		$this->authcode         = $authcode;
	}
}