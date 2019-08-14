<?php

class FuturePay_FuturePay_Model_Observer
{
	public function checkTransactionStatus(){
		$transactions = Mage::getModel('sales/order_payment_transaction')->getCollection();
		foreach($transactions as $trans){
			if($trans->getOrderPaymentObject()->getMethod() == 'futurepay'){
				$txnId = $trans->getTxnId();
				$serverResult = $this->_call_fp_api_get_order_token($txnId);				var_dump($serverResult);exit;
				if($serverResult){
					$serverResult = json_decode($serverResult,true);
					$orderStatusCode = isset($serverResult['OrderStatusCode'])?$serverResult['OrderStatusCode']:false;
					/*Do something here*/
				}
			}
		}
	}
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
	
}