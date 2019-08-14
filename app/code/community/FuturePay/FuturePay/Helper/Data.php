<?php



class FuturePay_FuturePay_Helper_Data extends Mage_Core_Helper_Abstract

{

	public function isSandboxMode(){

		return Mage::getStoreConfig('payment/futurepay/is_sandbox_mode');

	}

	

	/**

	 * Get API Url

	 */

	public function getApiUrl(){

		return $this->isSandboxMode()?Mage::getStoreConfig('payment/futurepay/sandbox_checkout_url'):Mage::getStoreConfig('payment/futurepay/checkout_url');

	}

	

	

	/**

	 * Get cart integration url

	 */

	public function getCartIntegrationUrl(){

		return $this->isSandboxMode()?Mage::getStoreConfig('payment/futurepay/sandbox_cart_integration'):Mage::getStoreConfig('payment/futurepay/cart_integration');

	}

	

	/**

	 * Get order verification url

	 */

	public function getOrderVerificationUrl(){

		return $this->isSandboxMode()?Mage::getStoreConfig('payment/futurepay/sandbox_order_verification_url'):Mage::getStoreConfig('payment/futurepay/order_verification_url');

	}
	
	/**
	 *Get is Auto Create Invoice 
	 */
	
	public function autoCreateInvoice(){
		return Mage::getStoreConfig('payment/futurepay/auto_invoice');
	}

}
