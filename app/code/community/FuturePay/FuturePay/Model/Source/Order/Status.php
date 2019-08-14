<?php

class FuturePay_FuturePay_Model_Source_Order_Status
{

    static public function toOptionArray() {
        return array(
            '' => '',
            'pending' => Mage::helper('futurepay')->__('Pending'),
        );
    }

}
