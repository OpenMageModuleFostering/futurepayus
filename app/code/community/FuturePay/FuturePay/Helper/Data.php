<?php

class FuturePay_FuturePay_Helper_Data extends Mage_Core_Helper_Abstract {

    
    /**
     * Returns 1 if FuturePay is configured for Sandbox mode, or 0 if it's not
     * 
     * @return int
     */
    public function isSandboxMode() {
        return Mage::getStoreConfig('payment/futurepay/is_sandbox_mode');
    }


    /**
     * Returns 1 if FuturePay is configured to auto-create invoices, or 0 if
     * it's not
     */
    public function autoCreateInvoice() {
        return Mage::getStoreConfig('payment/futurepay/auto_invoice');
    }

}
