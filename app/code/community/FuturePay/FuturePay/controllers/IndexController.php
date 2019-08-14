<?php
class FuturePay_FuturePay_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * During the purchase process, FuturePay will return a token which
     * represents a pre-authorization of funds within the customer's FuturePay
     * account.
     * 
     * This ajax endpoint stores this token within the customer's session.
     * 
     * @return void
     */
    public function storeFPTokenAction() {
        // store the token
        $request = $this->getRequest()->getParams();
        if (isset($request['token']) && strlen($request['token']) > 0) {
            Mage::getSingleton('core/session')->setFpToken($request['token']);
        }
    }

    /**
     * Render the Sign-up/Login template for the Admin interface
     * 
     * @see app/design/frontend/base/default/template/futurepay/getsignuploginform.phtml
     * @return void
     */
    public function getSignupLoginFormAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    
    /**
     * Render the Merchant Login template for the Admin interface
     * 
     * @see app/design/frontend/base/default/template/futurepay/getmerchantloginform.phtml
     * @return void
     */
    public function getMerchantLoginFormAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    
    /**
     * Render the Merchant Sign-up template for the Admin interface
     * 
     * @see app/design/frontend/base/default/template/futurepay/getmerchantsignupform.phtml
     * @return void
     */
    public function getMerchantSignupFormAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    
    /**
     * Retrieve the merchant's GMID (merchant ID) from FuturePay by email
     * address and password.
     * 
     * If is_sandbox_mode is set to 1, the call will be made to FuturePay's
     * sandbox instance (sandbox.futurepay.com).
     * 
     * This function will render a json response containing either the
     * response from FuturePay or a generated response containing an error
     * message.
     * 
     * @return void
     */
    public function doMerchantLoginAction() {
        //get the incoming request
        $request = $this->getRequest()->getParams();
        
        if (strlen($request['user_name']) > 0 && strlen($request['password']) > 0) {

            $request['user_name'] = urlencode($request['user_name']);
            $request['password'] = urlencode($request['password']);

            // this value is set in:
            // Magento Admin > Configuration > Payment Methods > FuturePay
            if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
                $requestHost = 'sandbox.futurepay.com';
            } else {
                $requestHost = 'api.futurepay.com';
            }

            // build the request URL
            $requestUrl = "https://{$requestHost}/remote/merchant-request-key?type=retrieve"
                    . "&user_name={$request['user_name']}"
                    . "&password={$request['password']}";

            // make sure the request URL is valid
            if (filter_var($requestUrl, FILTER_VALIDATE_URL)) {

                set_time_limit(0);
                ini_set('max_execution_time', 300); // 5 minutes
                
                $ch = curl_init($requestUrl);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Magento/FuturePay Plugin v2.x');
                $result = curl_exec($ch);
                
                // if the connection failed, report it back to the browser,
                // otherwise pass the json object back.
                if ($result !== false) {
                    $this->getResponse()->setHeader('Content-type', 'application/json');
                    $this->getResponse()->setBody($result);
                } else {
                    $this->getResponse()->setHeader('Content-type', 'application/json');
                    $this->getResponse()->setBody(json_encode(array(
                        'error' => 1,
                        'message' => curl_error($ch),
                    )));
                }
                curl_close($ch);
                
            } else {
                // the request URL did not validate
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(json_encode(array(
                    'error' => 1,
                    'message' => "API endpoint URL didn't validate properly.",
                )));
            }
        }
    }

    /**
     * Retrieve the merchant's GMID (merchant ID) from FuturePay by email
     * address and password.
     * 
     * If is_sandbox_mode is set to 1, the call will be made to FuturePay's
     * sandbox instance (sandbox.futurepay.com).
     * 
     * This function will render a json response containing either the
     * response from FuturePay or a generated response containing an error
     * message.
     * 
     * @return void
     */
    public function doMerchantSignupAction() {

        $request = $this->getRequest()->getParams();

        // this value is set in:
        // Magento Admin > Configuration > Payment Methods > FuturePay
        if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
            $requestHost = 'sandbox.futurepay.com';
        } else {
            $requestHost = 'api.futurepay.com';
        }

        // these parameters are *sometimes* added to the request by magento
        // unset them if they exist - they're not needed
        if (isset($request['isAjax'])) {
            unset($request['isAjax']);
        }
        if (isset($request['form_key'])) {
            unset($request['form_key']);
        }
        
        // we're requesting a merchant signup
        $request['type'] = 'signup';

        // remove 'futurepay_' from the beginning of form field names
        $tempRequest = $request;
        foreach ($tempRequest as $k => $v) {
            unset($request[$k]);
            $k = str_replace('futurepay_', '', $k);
            $request[$k] = $v;
        }

        // build the request URL
        $queryString = http_build_query($request);
        $requestUrl = "https://{$requestHost}/remote/merchant-request-key?{$queryString}";

        // make sure the request URL validates
        if (filter_var($requestUrl, FILTER_VALIDATE_URL)) {

            set_time_limit(0);
            ini_set('max_execution_time', 300); // 5 minutes
            
            $ch = curl_init($requestUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Magento/FuturePay Plugin v2.0');
            $result = curl_exec($ch);
            
            // if the connection failed, report it back to the browser, otherwise
            // pass the json object back.
            if ($result !== false) {
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody($result);
            } else {
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(json_encode(array(
                    'error' => 1,
                    'message' => curl_error($ch),
                )));
            }
            curl_close($ch);
            
        } else {
            // the request URL did not validate
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode(array(
                'error' => 1,
                'message' => "An unknown error occured. Please try again later.",
            )));
        }
    }

    
    /**
     * Fetch a list of regions for the specified country and render them as
     * a json response
     * 
     * @return void
     */
    public function getCountryRegionsAction() {
        $regionList = Mage::getModel('directory/region')
                ->getResourceCollection()
                ->addCountryFilter($this->getRequest()->getParam('country'))
                ->load()
                ->toArray();
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($regionList));
    }

    
    /**
     * Pass the order details, including FuturePay's pre-authorization token to
     * FuturePay to be finalized.
     * 
     * @return void
     * @throws FuturePay_FuturePay_Exception
     */
    public function processAction() {

        // if we can't find the last quote ID, redirect back to the cart page
        if (!$this->getOnepage()->getCheckout()->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($lastOrderId);
        Mage::register('current_order', $order);
        try {
            $order->setStatus(Mage::getStoreConfig('payment/futurepay/order_status'))->save();

            // this token was stored in storeFPTokenAction()
            $token = Mage::getSingleton('core/session')->getFpToken();

            if ($token) {

                $GMID = Mage::getStoreConfig('payment/futurepay/gmid');
                $fpMerchantId = trim(substr($GMID, 40, 45));

                $postData = array(
                    'api_version' => '2',
                    'order_action' => 'capture',
                    'authorization_token' => $token,
                    'PlatformId' => '302',
                    'reference' => $order->getIncrementId(),
                    'amount' => $order->getGrandTotal(),
                    'order_description' => Mage::helper('futurepay')->__('Payment for order #%s', $order->getIncrementId()),
                    'soft_descriptor' => Mage::app()->getStore()->getName(),
                    'shipping_cost' => $order->getShippingAmount(),
                    'shipping_address' => "{$order->getShippingAddress()->getStreet(1)}\n{$order->getShippingAddress()->getStreet(2)}",
                    'shipping_zip' => $order->getShippingAddress()->getPostcode(),
                    'shipping_country' => $order->getShippingAddress()->getCountry(),
                    'subscription' => 0,
                    'billing_address' => "{$order->getBillingAddress()->getStreet(1)}\n{$order->getBillingAddress()->getStreet(2)}",
                    'billing_zip' => $order->getBillingAddress()->getPostcode(),
                    'billing_country' => $order->getBillingAddress()->getCountry(),
                );

                    
                // FuturePay will only accept ISO 3166-2 region codes, although
                // the form will allow a customer to manually enter a region
                // when no region codes are available for the selected country    
                $shippingRegionCode = $order->getShippingAddress()->getRegionCode();
                $billingRegionCode = $order->getBillingAddress()->getRegionCode();
                
                if (strlen($shippingRegionCode) == 2) {
                    $postData['shipping_region'] = $shippingRegionCode;
                }
                if (strlen($billingRegionCode) == 2) {
                    $postData['billing_region'] = $billingRegionCode;
                }
                
                // convert the request array to a HTTP query
                $postData = http_build_query($postData);
                
                // this value is set in:
                // Magento Admin > Configuration > Payment Methods > FuturePay
                if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
                    $requestHost = "sandbox.futurepay.com";
                } else {
                    $requestHost = "api.futurepay.com";
                }

                $hash = base64_encode(hash_hmac('sha256', time() . $GMID . $fpMerchantId, $GMID, true));
                
                $cr = curl_init("https://" . $requestHost . "/api/order");
                curl_setopt($cr, CURLOPT_POST, true);
                curl_setopt($cr, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($cr, CURLOPT_HTTPHEADER, array(
                    "api_key: {$fpMerchantId}",
                    "signature: {$hash}",
                ));
                
                curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($cr);

                if (!$response) {
                    // the curl call failed, but surpress the error because this
                    // is customer-facing
                    throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not valid'));
                }
                
                // FuturePay returns a json object - convert it to an array
                $serverResult = json_decode($response, true);

                if (!is_array($serverResult)) {
                    // if the FuturePay response contains invalid json, the
                    // order has most likely failed, but we didn't receieve an
                    // error message
                    throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not accepted'));
                } elseif ($serverResult['status'] != 1) {
                    // the json was parsed currectly, but the order failed on
                    // FuturePay's side. Report the error to the customer
                    throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not accepted: ' . $serverResult['message']));
                }
                
                // this should be the order total
                $capturedAmount = $serverResult['object']['order_total'];

                // this is the FuturePay transaction ID
                $txtId = $serverResult['object']['transaction_references'][0];

                // get the payment object and complete it
                $payment = $order->getPayment();
                $payment->setTransactionId($txtId)
                        ->setCurrencyCode($order->getBaseCurrencyCode())
                        ->setPreparedMessage('')
                        ->setParentTransactionId($txtId)
                        ->setShouldCloseParentTransaction(true)
                        ->setIsTransactionClosed(1);
                
                if (Mage::helper('futurepay')->autoCreateInvoice()) {
                    $payment->registerCaptureNotification($capturedAmount);
                    // notify customer
                    $invoice = $payment->getCreatedInvoice();
                    if ($invoice && !$order->getEmailSent()) {
                        $order->sendNewOrderEmail()->addStatusHistoryComment(
                                        Mage::helper('paypal')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
                                )
                                ->setIsCustomerNotified(true);
                    }
                } else {
                    $payment->registerAuthorizationNotification($capturedAmount);
                    if (!$order->getEmailSent()) {
                        $order->sendNewOrderEmail();
                    }
                }
                $order->save();
                
                // the order was a success! redirect to the success page
                $this->_redirect('checkout/onepage/success');
            } else {
                // no futurepay token available
                throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not accepted'));
            }
        } catch (Exception $e) {
            // there was an error. return the customer to the cart page.
            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
            $this->_redirect('checkout/cart');
        }
    }

}
