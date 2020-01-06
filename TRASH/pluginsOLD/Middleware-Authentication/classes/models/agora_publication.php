<?php
/**
 * Class: agora_publication constructor.
 * Description:  Accepts an stdClass object of publication subscription data and transforms to a more usable structure.
 * @author: Threefold Systems
 */
class agora_publication extends agora_advantage_item{

	public $status, $description, $pubcode, $member_cat, $member_org, $start_date, $expiration_date, $final_expiration_date, $subref, $authcode, $temp;

	/**
	 * Class: constructor.
	 * @param $mw_object stdClass A subscription item from the 'subscriptions' array in middleware_data
	 * @param $authcode agora_authcode An authcode object to give more detail about the subscription object.
	 */
	public function __construct($mw_object, agora_authcode $authcode = null){
		$this->status                = isset($mw_object->circStatus) ? $mw_object->circStatus : 'R';
		$this->description           = isset($mw_object->id->item->itemDescription) ? $mw_object->id->item->itemDescription : $authcode->name;
		$this->pubcode               = isset($mw_object->id->item->itemNumber) ? strtoupper($mw_object->id->item->itemNumber) : $authcode->advantage_code;
		$this->member_cat            = isset($mw_object->memberCat) ? $mw_object->memberCat : '';
		$this->member_org            = isset($mw_object->memberOrg) ? $mw_object->memberOrg : '';
		$this->start_date            = isset( $mw_object->startDate ) && $mw_object->startDate ? $mw_object->startDate : 'NA';
		$this->expiration_date       = isset( $mw_object->expirationDate ) && $mw_object->expirationDate ? $mw_object->expirationDate : 'NA';
		$this->final_expiration_date = isset( $mw_object->finalExpirationDate ) && $mw_object->finalExpirationDate ? $mw_object->finalExpirationDate : 'NA';
		$this->subref				 = isset($mw_object->id->subRef) ? $mw_object->id->subRef : '';
		$this->authcode              = $authcode;
		$this->temp					 = isset($mw_object->temp) ? $mw_object->temp : '';
	}
}
