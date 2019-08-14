<?php
/**
 * Magento Core Exception
 *
 * This class will be extended by other modules
 *
 * @category   VES
 * @package    VES_FuturePay
 */
class FuturePay_FuturePay_Exception extends Exception
{
	public function __construct($errorCode='',$message =''){
		$message = $this->getErrorMessageByCode($errorCode);
		return parent::__construct($message);
	}
	
	public function getErrorMessageByCode($errorCode){
		$errorCode = trim($errorCode);
		switch($errorCode){
			case 'FP_EXISTING_INVALID_CUSTOMER_STATUS'	: $message = Mage::helper('futurepay')->__('Your purchase was not successful because your account is not in an active state. Please contact FuturePay at <a href="mailto:support@futurepay.com">support@futurepay.com</a>.');break;
			case 'FP_INVALID_ID_REQUEST' 				: $message = Mage::helper('futurepay')->__('Error: The GMID could not be validated - either missing or not valid format - Contact FuturePay');break;
			case 'FP_INVALID_SERVER_REQUEST' 			: $message = Mage::helper('futurepay')->__('Error: Either the Merchant Server is not on our IP Whitelist or the Order Reference was Missing');break;
			case 'FP_PRE_ORDER_EXCEEDS_MAXIMUM' 		: $message = Mage::helper('futurepay')->__('FuturePay can only be used on orders under $500');break;
			case 'FP_MISSING_REFERENCE' 				: $message = Mage::helper('futurepay')->__('Reference was not detected in the Query String');break;
			case 'FP_INVALID_REFERENCE' 				: $message = Mage::helper('futurepay')->__('Reference');break;
			case 'FP_ORDER_EXISTS' 						: $message = Mage::helper('futurepay')->__('The reference exists with an order that has completed sales attached');break;
			case 'FP_MISSING_REQUIRED_FIRST_NAME' 		: $message = Mage::helper('futurepay')->__('First Name was not detected in the Query String');break;
			case 'FP_MISSING_REQUIRED_LAST_NAME' 		: $message = Mage::helper('futurepay')->__('Last Name was not detected in the Query String');break;
			case 'FP_MISSING_REQUIRED_PHONE'			: $message = Mage::helper('futurepay')->__('Phone Name was not detected in the Query String');break;
			case 'FP_MISSING_REQUIRED_CITY' 			: $message = Mage::helper('futurepay')->__('City was not detected in the Query String');break;
			case 'FP_MISSING_REQUIRED_STATE' 			: $message = Mage::helper('futurepay')->__('State was not detected in the Query String');break;
			case 'FP_MISSING_REQUIRED_ADDRESS' 			: $message = Mage::helper('futurepay')->__('Address was not detected in the Query String');break;
			case 'FP_MISSING_REQUIRED_COUNTRY' 			: $message = Mage::helper('futurepay')->__('Country was not detected in the Query String');break;
			case 'FP_COUNTRY_US_ONLY' 					: $message = Mage::helper('futurepay')->__('The Country was not US');break;
			case 'FP_MISSING_EMAIL' 					: $message = Mage::helper('futurepay')->__('Email was not detected in the Query String');break;
			case 'FP_INVALID_EMAIL_SIZE' 				: $message = Mage::helper('futurepay')->__('Email Size was greater than 85');break;
			case 'FP_INVALID_EMAIL_FORMAT' 				: $message = Mage::helper('futurepay')->__('Email Format was not valid');break;
			case 'FP_MISSING_REQUIRED_ZIP' 				: $message = Mage::helper('futurepay')->__('Zip was not detected in the Query String');break;
			case 'FP_NO_ZIP_FOUND' 						: $message = Mage::helper('futurepay')->__('The Zip code you entered for your billing address corresponds to a Military (APO/FPO/DPO), PO Box or other non-physical address. To approve your application we require a Zip code associated with a physical address in the U.S.');break;
			case 'FP_INVALID_STATE_ZIP_COMBINATION' 	: $message = Mage::helper('futurepay')->__('The Zip code you entered for your billing address corresponds to a Military (APO/FPO/DPO), PO Box or other non-physical address. To approve your application we require a Zip code associated with a physical address in the U.S.');break;
			case 'NOT_ACCEPTED' 						: $message = Mage::helper('futurepay')->__('Your FuturePay Account Could not be Created. Please check your email for further details. Please select another payment method.');break;
			case 'ORDER_TK_NOT_FOUND' 					: $message = Mage::helper('futurepay')->__('FuturePay could not confirm the Order.');break;
			default: $message = Mage::helper('futurepay')->__('There is an error occurred we cannot process your order. Code: %s',$errorCode);
		}
		return $message;
	}
}
