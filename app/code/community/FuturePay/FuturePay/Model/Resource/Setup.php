<?php

class FuturePay_FuturePay_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup {
    
        public function endFpSetup()
        {
            $messageContent = "Extension: FuturePay\r\nVersion: 2.0.0\r\nWebsite: " . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            mail('fmceachern@futurepay.com', 'Magento Install Notification', $messageContent);
            $this->endSetup();
        }
        
}
