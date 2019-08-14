<?php
class FuturePay_FuturePay_Model_Method_FuturePay extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'futurepay';
    protected $_formBlockType = 'futurepay/form_futurepay';
    protected $_infoBlockType = 'futurepay/info_futurepay';
    protected $_canAuthorize = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canCapture = true;
    
    public function refund(\Varien_Object $payment, $amount) {
        
        //  Setup curl request:
        if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
            $requestHost = 'sandbox.futurepay.com';
        } else {
            $requestHost = 'api.futurepay.com';
        }
        $GMID = Mage::getStoreConfig('payment/futurepay/gmid');
        $address =  'https://' . $requestHost . '/remote/merchant-returns';
        $postDataArray = array();
        $postDataArray['api_version'] = '2';
        $postDataArray['PlatformId'] = '302';
        $postDataArray['gmid'] = $GMID;
        //  Returned in the JavaScript Response from a successful purchase
        $postDataArray['order_action'] = 'refund';
        //$postDataArray['reference'] = get_object_vars($payment)['_data']['refund_transaction_id'];
        //$postDataArray['reference'] = get_object_vars($payment)['_data']['entity_id'];
        $postDataArray['reference'] = $payment->getOrder()->getIncrementId();
        
        $postDataArray['description'] = 'Retailer Refunded: #' . $payment->getOrder()->getIncrementId();
        $postDataArray['total_price'] = (float)$amount;
        //$postDataArray['amount'] = $amount;

        $postdata = http_build_query($postDataArray);

        //  Create CURL request to the server
        $hash = base64_encode(hash_hmac('sha256', time() . FP_API_KEY . FP_SECRET, FP_API_KEY, true));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $address);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($cr, CURLOPT_HTTPHEADER, array(
            'api_key: ' . FP_SECRET,
            "signature: $hash"
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //  Receive response from FuturePay
        $server_output = curl_exec ($ch);

        //  Close the connection socket
        curl_close($ch);

         //  Use the server output to handle error responses 
        //  (success response should continue through the existing workflow)
        //var_dump($server_output, $postDataArray);
        
        // they'll either return a success or error.. hopefully...

        // if there was an error, do this (faye might want it changed, but for now...):
        $responseStatus = get_object_vars(json_decode($server_output))['status'];
        
        if ($responseStatus != 'FP_REFUND_SUCCESSFUL') {
            //$fpErrorCode = 'FP0000069';
            $fpErrorMessage = $responseStatus;
            $errorMsg = $this->_getHelper()->__("Refund failed: {$fpErrorMessage}");
            Mage::throwException($errorMsg);
        }
        
        // just leave this here.. if there were no errors above, this will run
        return parent::refund($payment, $amount);
                
        
    }
            
    
    public function assignData($data)
    {
        $details = array();
        if($this->getCheckout()){
        	$details['checkout']=$this->getCheckout();
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    public function getCheckout()
    {
    	return $this->getConfigData('checkout_url');
    }
    
    
    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('futurepay/index/process');
    }		public function isAvailable($quote = null){		$countryCode = $quote->getBillingAddress()->getCountry();		$isAvailable = ($countryCode == 'US');    	return $isAvailable && parent::isAvailable($quote);    }
}
