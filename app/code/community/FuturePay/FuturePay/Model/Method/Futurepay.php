<?php

class FuturePay_FuturePay_Model_Method_FuturePay extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'futurepay';
    protected $_formBlockType = 'futurepay/form_futurepay';
    protected $_infoBlockType = 'futurepay/info_futurepay';
    protected $_canAuthorize = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canCapture = true;

    /**
     * Perform a refund
     * 
     * @param \Varien_Object $payment
     * @param float $amount
     * @throws Mage_Core_Exception
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function refund(\Varien_Object $payment, $amount) {


        // this value is set in:
        // Magento Admin > Configuration > Payment Methods > FuturePay
        if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
            $requestHost = 'sandbox.futurepay.com';
        } else {
            $requestHost = 'api.futurepay.com';
        }

        $GMID = Mage::getStoreConfig('payment/futurepay/gmid');
        $fpMerchantId = trim(substr($GMID, 40, 45));

        $address = 'https://' . $requestHost . '/api/order';

        // build the request
        $postDataArray = array(
            'order_action' => 'refund',
            'reference' => $payment->getOrder()->getIncrementId(),
            'order_description' => 'Retailer Refunded: #' . $payment->getOrder()->getIncrementId(),
            'amount' => (float) $amount,
        );
        
        $postdata = http_build_query($postDataArray);

        // send the refund request to FuturePay
        $hash = base64_encode(hash_hmac('sha256', time() . $GMID . $fpMerchantId, $GMID, true));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $address);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Magento/FuturePay Plugin v2.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'api_key: ' . $fpMerchantId,
            'signature: ' . $hash,
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //  Receive response from FuturePay
        $serverResponse = curl_exec($ch);
        $curlError = curl_error($ch);

        curl_close($ch);
        
        // empty response typically means a curl error. report it!
        if (strlen($serverResponse) < 1) {
            // get the curl error and display to the merchant. this error isn't
            // customer-facing, so it's okay to display technical errors here
            $errorMsg = $this->_getHelper()->__("Refund failed: {$curlError}");
            Mage::throwException($errorMsg);
        } else {
            // the response from FuturePay should be a json object
            $serverResponseArray = json_decode($serverResponse, true);
            if (is_array($serverResponseArray)) {

                if ($serverResponseArray['status'] != 1) {
                    // FP is reporting an error
                    $fpErrorMessage = "{$serverResponseArray['code']} {$serverResponseArray['message']}";
                    Mage::throwException("Refund failed: {$fpErrorMessage}");
                }
                
            } else {
                // report the contents of the server's response body as an error
                // message
                Mage::throwException("Refund failed: {$serverResponse}");
            }

        }

        // after successfully requesting the refund from FuturePay, continue
        // with the default refund process
        return parent::refund($payment, $amount);
    }

    /**
     * Returns the URL to the process controller
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('futurepay/index/process');
    }

    /**
     * Returns true if FuturePay is available as a payment method. To meet this
     * criteria, the customer's billing address must be in the US.
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null) {
        $countryCode = $quote->getBillingAddress()->getCountry();
        $isAvailable = ($countryCode == 'US');
        return $isAvailable && parent::isAvailable($quote);
    }

}
