<?php

class FuturePay_FuturePay_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup {
    
    // set this to false if you would *not* like to notify FuturePay
    const NOTIFY_FUTUREPAY_ON_INSTALL = true;
    
    
    /**
     * This function will notify FuturePay when this extension has been
     * installed. This can be disabled by setting the class constant
     * "NOTIFY_FUTUREPAY_ON_INSTALL" (above) to FALSE.
     * 
     * @return void
     */
    public function endFpSetup() {
        if (FuturePay_FuturePay_Model_Resource_Setup::NOTIFY_FUTUREPAY_ON_INSTALL) {
            
            $websiteUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $extensionVersion = Mage::getConfig()->getNode()->modules->FuturePay_FuturePay->version;
            $messageContent = <<<TXT
Extension: FuturePay
Version: {$extensionVersion}
Website: {$websiteUrl}
TXT;
            mail('fmceachern@futurepay.com', 'Magento Install Notification', $messageContent);
        }
        
        // end the default set up as 
        $this->endSetup();
    }

}
