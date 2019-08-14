<?php
class FuturePay_FuturePay_Model_Source_Payment_Action
{
	static public function toOptionArray()
    {
        return array(
        	'Sale'				=> 'Sale',
            'Authorization'    	=> Mage::helper('futurepay')->__('Authorization'),
        );
    }
}
