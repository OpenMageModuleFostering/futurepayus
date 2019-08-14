<?php
/**
 * FuturePay install data script
 * 
 * @see FuturePay/Model/Resource/Setup.php
 */
$this->startSetup();



// fix for missing payment method name in sales report on previous versions
$mageCodeModelConfig = new Mage_Core_Model_Config();
$mageCodeModelConfig->saveConfig('payment/futurepay/title', 'Buy Now and Pay Later with FuturePay', 'default', 0);


$this->endFpSetup();

?>
