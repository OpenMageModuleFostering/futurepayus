<?php
class FuturePay_FuturePay_IndexController extends Mage_Core_Controller_Front_Action
{
    
    //const USE_FUTUREPAY_SANDBOX = true;
    
	/**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
    
    // @debug
    public function getFPTokenAction()
    {
        var_dump(Mage::getSingleton('core/session')->getFpToken());
        exit;
    }
    
    public function storeFPTokenAction()
    {
        // store the token
        $request = $this->getRequest()->getParams();
        if (isset($request['token'])
                && strlen($request['token']) > 0) {
            Mage::getSingleton('core/session')->setFpToken($request['token']);
        }
    }
    
    public function getSignupLoginFormAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function getMerchantLoginFormAction()
    {
        //$fpData = Mage::helper('futurepay/data'); // @debug
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function getMerchantSignupFormAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function doMerchantLoginAction()
    {
        $request = $this->getRequest()->getParams();
        if (strlen($request['user_name']) > 0
                && strlen($request['password']) > 0) {
            
            // send the request to futurepay
            set_time_limit(0);
            ini_set('max_execution_time', 300); // 5 minutes, which is overkill.
            $request['user_name'] = urlencode($request['user_name']);
            $request['password'] = urlencode($request['password']);
            
            /*if (self::USE_FUTUREPAY_SANDBOX) {
                $request_host = 'demo.futurepay.com';
            } else {
                $request_host = 'api.futurepay.com';
            }*/
            
            if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
                $requestHost = 'sandbox.futurepay.com';
            } else {
                $requestHost = 'api.futurepay.com';
            }
            
            $requestUrl = "https://{$requestHost}/remote/merchant-request-key?type=retrieve"
                    . "&user_name={$_POST['user_name']}"
                    . "&password={$_POST['password']}";
            
            if (filter_var($requestUrl, FILTER_VALIDATE_URL)) {

                $ch = curl_init($requestUrl);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Magento/FuturePay Plugin v1.0');
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
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(json_encode(array(
                    'error' => 1,
                    'message' => "API endpoint URL didn't validate properly.",
                )));
            }
        }
    }
    
    public function doMerchantSignupAction()
    {
        
        $request = $this->getRequest()->getParams();
        
        set_time_limit(0);
        ini_set('max_execution_time', 300); // 5 minutes, which is overkill.

        /*if (self::USE_FUTUREPAY_SANDBOX) {
            $requestHost = 'demo.futurepay.com';
        } else {
            $requestHost = 'api.futurepay.com';
        }*/
        
        if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
            $requestHost = 'sandbox.futurepay.com';
        } else {
            $requestHost = 'api.futurepay.com';
        }

        unset($request['isAjax']);
        unset($request['form_key']);
        $request['type'] = 'signup';
        
        // remove 'futurepay_' from the beginning of form field names
        $tempRequest = $request;
        foreach ($tempRequest as $k => $v) {
            unset($request[$k]);
            $k = str_replace('futurepay_', '', $k);
            $request[$k] = $v;
        }
        
        $queryString = http_build_query($request);
        $requestUrl = "https://{$requestHost}/remote/merchant-request-key?{$queryString}";

        if (filter_var($requestUrl, FILTER_VALIDATE_URL)) {

            $ch = curl_init($requestUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Magento/FuturePay Plugin v1.0');
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
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode(array(
                'error' => 1,
                'message' => "An unknown error occured. Please try again later.",
            )));
        }
    }
    
    public function getCountryRegionsAction()
    {
        $regionList = Mage::getModel('directory/region')
            ->getResourceCollection()
            ->addCountryFilter($this->getRequest()->getParam('country'))
            ->load()
            ->toArray();
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($regionList));
    }
    
//	protected function callFpApiGetOrderToken(Mage_Sales_Model_Order $order) {
//	        //setup curl request:
//	        $GMID = Mage::getStoreConfig('payment/futurepay/gmid');
//	
//		  // Platform Merchant Identifier
//	        $PMID = Mage::getStoreConfig('payment/futurepay/pmid');
//	
//	        $postDataArray = array();
//	        $postDataArray['gmid'] = $GMID;
//	        $postDataArray['pmid'] = $PMID;
//	        $postDataArray['reference'] = $order->getIncrementId(); /*order id*/
//	        $billing = $order->getBillingAddress();
//			/*billing information*/
//	        $postDataArray['email'] 		= $order->getCustomerEmail();
//	        $postDataArray['first_name'] 	= $billing->getFirstname();
//	        $postDataArray['last_name'] 	= $billing->getLastname();
//	        $postDataArray['address_line_1'] 	= $billing->getStreet(1);
//	        $postDataArray['address_line_2'] 	= $billing->getStreet(2);
//	        $postDataArray['city'] 			= $billing->getCity();
//	        $postDataArray['state'] 		= $billing->getRegionCode();
//	        $postDataArray['country'] 		= $billing->getCountry();
//	        $postDataArray['zip'] 			= $billing->getPostcode();
//	        $postDataArray['phone'] 		= $billing->getTelephone();
//	        
//	        if(!$order->getIsVirtual()){
//				$shipping = $order->getShippingAddress();
//	        	$postDataArray['shipping_address_line_1'] 	= $shipping->getStreet(1);
//		        $postDataArray['shipping_address_line_2'] 	= $shipping->getStreet(2);
//		        $postDataArray['shipping_city'] 			= $shipping->getCity();
//		        $postDataArray['shipping_state'] 		= $shipping->getRegionCode();
//		        $postDataArray['shipping_country'] 		= $shipping->getCountry();
//		        $postDataArray['shipping_zip'] 			= $shipping->getPostcode();
//		       
//	        }
//			/*foreach($order->getAllVisibleItems() as $item){				
//				$postDataArray['sku'][] 	= $item->getSku();    // FEDEX SKU
//		        $postDataArray['price'][] 	= $item->getPrice();  // Shipping Cost
//		        $postDataArray['tax_amount'][] 	= $item->getTaxAmount(); // Must be zero
//		        $postDataArray['description'][] = $item->getName();
//		        $postDataArray['quantity'][] 	= intval($item->getQtyOrdered());
//			}*/
//			$postDataArray['sku'][] 	= $order->getIncrementId();    // Order Increment ID
//	        $postDataArray['price'][] 	= Mage::helper('directory')->currencyConvert($order->getBaseGrandTotal(),$order->getBaseCurrencyCode(),'USD');  // Grand Total
//	        $postDataArray['tax_amount'][] 	= 0; // Must be zero
//	        $postDataArray['description'][] = Mage::helper('futurepay')->__('Payment for order #%s',$order->getIncrementId());
//	        $postDataArray['quantity'][] 	= 1;
//	        // Continued --------------------------------
//	        $postdata = http_build_query($postDataArray);
//	
//	        //create CURL request to the server
//	        $ch = curl_init();
//	
//	        curl_setopt($ch, CURLOPT_URL,Mage::helper('futurepay')->getApiUrl());
//	        curl_setopt($ch, CURLOPT_POST, 1);
//	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
//	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//	
//	        //receive response from FuturePay
//	        $server_output = curl_exec ($ch);
//	
//	        //close the connection socket
//	        curl_close($ch);
//			// If the $server_ouput starts with FPTK followed by a hashed string
//	        // Then the call to create a pre-order has been successful
//	        // Otherwise there will be an error response code that is defined in
//	        // The error code table of this document
//	        if(strpos($server_output, "FPTK")) {
//	           // Push the token to the javascript request on the cart
//			   return $server_output;
//	        }else{
//				throw new FuturePay_FuturePay_Exception($server_output);
//			}
//			return false;
//	}
    
	public function processAction() {
        // PAGE 8 STARTS HERE
        //die();
        if (!$this->getOnepage()->getCheckout()->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($lastOrderId);
        Mage::register('current_order', $order);
        try {
            $order->setStatus(Mage::getStoreConfig('payment/futurepay/order_status'))->save();

            // this token was stored in storeFPTokenAction()
            $token = Mage::getSingleton('core/session')->getFpToken();
            
            if ($token) {
                
                //define('FP_API_KEY', Mage::getStoreConfig('payment/futurepay/gmid'));
                //define('FP_SECRET', Mage::getStoreConfig('payment/futurepay/merchant_id'));
                $GMID = Mage::getStoreConfig('payment/futurepay/gmid');
                $fpMerchantId = trim(substr($GMID, 40, 45));
                                
                //header('Content-Type: application/json');
                $postData = array(
                    'api_version' => '2',
                    'order_action' => 'capture',
                    'authorization_token' => $token,
                    'PlatformId' => '302',
                    'reference' => $order->getIncrementId(),
                    'amount' => $order->getGrandTotal(),
                    //'order_tag' => '',
                    'order_description' => Mage::helper('futurepay')->__('Payment for order #%s', $order->getIncrementId()),
                    'soft_descriptor' => Mage::app()->getStore()->getName(),
                    //'merchant_id' => '',
                    'shipping_cost' => $order->getShippingAmount(),
                    'shipping_address' => "{$order->getShippingAddress()->getStreet(1)}\n{$order->getShippingAddress()->getStreet(2)}",
                    'shipping_zip' => $order->getShippingAddress()->getPostcode(),
                    'shipping_country' => $order->getShippingAddress()->getCountry(),
                    // commented out because if we don't have it, we need to omit
                    // it
                    //'shipping_region' => $order->getShippingAddress()->getRegionCode(),
                    'subscription' => 0,
                    'billing_address' => "{$order->getBillingAddress()->getStreet(1)}\n{$order->getBillingAddress()->getStreet(2)}",
                    'billing_zip' => $order->getBillingAddress()->getPostcode(),
                    'billing_country' => $order->getBillingAddress()->getCountry(),
                    'billing_region' => $order->getBillingAddress()->getRegionCode(),
                );
                    
                $shippingRegionCode = $order->getShippingAddress()->getRegionCode();
                if (strlen($shippingRegionCode) == 2) {
                    $postData['shipping_region'] = $shippingRegionCode;
                }
                $testPostData = $postData;
                $postData = http_build_query($postData);
                
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
                    'api_key: ' . $fpMerchantId,
                    "signature: $hash"
                ));
                curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($cr);

                if (!$response)
                    throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not valid'));
                $serverResult = json_decode($response, true);

                if (!is_array($serverResult)) {
                    throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not accepted'));
                } elseif ($serverResult['status'] != 1) {
                    throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not accepted: ' . $serverResult['message']));
                }
                $capturedAmount = $serverResult['object']['order_total'];

                $txtId = $serverResult['object']['transaction_references'][0];

                
                // testing
                $payment = $order->getPayment();
                $payment->setTransactionId($txtId)
                          ->setCurrencyCode($order->getBaseCurrencyCode())
                          ->setPreparedMessage('')
                          ->setParentTransactionId($txtId)
                          ->setShouldCloseParentTransaction(true)
                          ->setIsTransactionClosed(1);
                          //->registerCaptureNotification($capturedAmount);
                      //$order->save();
                
                
                
                
                
                
                /* Save the transaction and create invoice */
//                $payment = $order->getPayment();
//                $payment->setTransactionId($txtId)
//                        ->setPreparedMessage('')
//                        ->setIsTransactionClosed(0)
                ;
                if (Mage::helper('futurepay')->autoCreateInvoice()) {
                    $payment->registerCaptureNotification($capturedAmount);
                    $order->save();
                    // notify customer
                    $invoice = $payment->getCreatedInvoice();
                    if ($invoice && !$order->getEmailSent()) {
                        $order->sendNewOrderEmail()->addStatusHistoryComment(
                                        Mage::helper('paypal')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
                                )
                                ->setIsCustomerNotified(true)
                                ->save();
                    }
                } else {
                    $payment->registerAuthorizationNotification($capturedAmount);
                    if (!$order->getEmailSent()) {
                        $order->sendNewOrderEmail();
                    }
                    $order->save();
                }

                $this->_redirect('checkout/onepage/success');
            }
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
            //$order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)->save();
            $this->_redirect('checkout/cart');
        }
    }

    /**
	 * Cancel action
	 */
	public function cancelAction(){
		$lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();
		Mage::getSingleton('checkout/session')->addError(Mage::helper('futurepay')->__('Your order has been canceled'));
		$order = Mage::getModel('sales/order')->load($lastOrderId);
		$order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)->save();
		$this->_redirect('checkout/cart');
	}
	
	/**
	 * Error action
	 */
	public function errorAction(){
		$errorCode 		= $this->getRequest()->getParam('error_code');
		$errorReason 	= $this->getRequest()->getParam('error_reason');
		if($errorCode){
			Mage::getSingleton('checkout/session')->addError(Mage::helper('futurepay')->__($errorReason));
		}else{
			Mage::getSingleton('checkout/session')->addError(Mage::helper('futurepay')->__('There is an error occurred we cannot process your order'));
		}
		/*$lastOrderId 	= $this->getOnepage()->getCheckout()->getLastOrderId();
		$order 			= Mage::getModel('sales/order')->load($lastOrderId);
		$order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)->save();*/
		$this->_redirect('checkout/cart');
	}
	/**
	 * Success action
	 */
	/*public function successAction(){
		$lastOrderId 	= $this->getOnepage()->getCheckout()->getLastOrderId();
		$order 			= Mage::getModel('sales/order')->load($lastOrderId);
		
		try{
			$transaction 	= Mage::getModel('sales/order_payment_transaction');
			$txtId 			= $this->getRequest()->getParam('transaction_id');
			if(!$txtId) throw new Exception(Mage::helper('futurepay')->__('The transaction is not valid'));
			
			/*Check if the transaction is success*/
			/*$serverResult = $this->_call_fp_api_get_order_token($txtId);
			if(!$serverResult) throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not valid'));
			
			$serverResult = json_decode($serverResult,true);
			if(!is_array($serverResult) || $serverResult['OrderStatusCode'] != 'ACCEPTED') throw new FuturePay_FuturePay_Exception(Mage::helper('futurepay')->__('The transaction is not accepted'));
			
			$capturedAmount = $serverResult['t.TotalPrice'];
			
			/*Save the transaction and create invoice*/
			/*$payment = $order->getPayment();
	        $payment->setTransactionId($txtId)
	            ->setPreparedMessage('')
	            ->setIsTransactionClosed(0)
	            ;
	        if(Mage::helper('futurepay')->autoCreateInvoice()){
	        	$payment->registerCaptureNotification($capturedAmount);
	        	$order->save();
		        // notify customer
	       		$invoice = $payment->getCreatedInvoice();
		        if ($invoice && !$order->getEmailSent()) {
		            $order->sendNewOrderEmail()->addStatusHistoryComment(
		                Mage::helper('paypal')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
		            )
		            ->setIsCustomerNotified(true)
		            ->save();
		        }
	        }else{
	        	$payment->registerAuthorizationNotification($capturedAmount);
			        if (!$order->getEmailSent()) {
		            $order->sendNewOrderEmail();
		        }
		        $order->save();
	        }
	        
	       
	
	        
        
			$result = array(
				'sucecss'	=> true,
				'url'		=> Mage::getUrl('checkout/onepage/success'),
			);
			$this->getResponse()->setBody(json_encode($result));
		}catch (Exception $e){
			Mage::getSingleton('checkout/session')->__($e->getMessage());
			$result = array(
				'sucecss'	=> false,
				'url'		=> Mage::getUrl('checkout/cart'),
				'msg'		=> $e->getMessage(),
			);
			$this->getResponse()->setBody(json_encode($result));
		}
	}*/

	protected function _call_fp_api_get_order_token($order_transaction_id) {
        // Setup curl request:
        $GMID = Mage::getStoreConfig('payment/futurepay/gmid');
        $postDataArray = array();
        $postDataArray['gmid'] = $GMID;
        // Returned in the JavaScript Response from a successful purchase
        $postDataArray['otxnid'] = $order_transaction_id;
        $postdata = http_build_query($postDataArray);
        //create CURL request to the server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,Mage::helper('futurepay')->getOrderVerificationUrl());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //receive response from FuturePay
        $server_output = curl_exec ($ch);
        
        //close the connection socket
        curl_close($ch);
	
	  return $server_output;
	}
    
    function _call_fp_refund_order($order_transaction_id) {

        //  Setup curl request:
        if (Mage::getStoreConfig('payment/futurepay/is_sandbox_mode') == 1) {
            $requestHost = 'sandbox.futurepay.com';
        } else {
            $requestHost = 'api.futurepay.com';
        }
        $GMID = Mage::getStoreConfig('payment/futurepay/gmid');
        $address =  'https://' . $requestHost . '/remote/merchant-returns';
        $postDataArray = array();
        $postDataArray['gmid'] = $GMID;
        //  Returned in the JavaScript Response from a successful purchase
        $postDataArray['reference'] = $order_transaction_id;
        $postDataArray['total_price'] = $refund_total;

        $postdata = http_build_query($postDataArray);

        //  Create CURL request to the server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $address);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //  Receive response from FuturePay
        $server_output = curl_exec ($ch);

        //  Close the connection socket
        curl_close($ch);

         //  Use the server output to handle error responses 
        //  (success response should continue through the existing workflow)
        return $server_output;

    }
	
	public function testAction(){
		Mage::getModel('futurepay/observer')->checkTransactionStatus();
	}
}
